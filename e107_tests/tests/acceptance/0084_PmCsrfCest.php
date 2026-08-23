<?php

/**
 * The private messenger acts on four GETs and asked nothing of them.
 *
 * pm.php takes its action out of e_QUERY and the account or message it acts on
 * out of the segment after it, so ?del.<id>.<box>, ?block.<uid>, ?unblock.<uid>
 * and ?delblocked.<uid> each reach a write from a bare URL.
 * e_session::isStateChangingRequest() answers true only for a POST, so attest()
 * returns early on a GET carrying no token at all and the framework never asks
 * that request where it came from. Whatever protects these four therefore has
 * to come from the plugin, and the plugin contributed nothing.
 *
 * ?del is the costly one: private_message::del() flags the message deleted for
 * the caller and, once the other party has done the same, runs the DELETE and
 * unlink()s every attachment, which is not recoverable from anywhere.
 * ?block is the odd one: block_add($from, $to = USERID) hardwires the victim as
 * the blocker and takes the blocked account from the query string, so a forged
 * request makes the victim block whoever the attacker names, and the one
 * account it will not block is the main administrator.
 *
 * The POST halves of the same two blocks, pm_delete_selected and
 * pm_delete_blocked_selected, were never the problem: attest() already refuses
 * a POST that brings no proof, so they are left to it.
 *
 * The controls follow the links the plugin publishes for itself rather than
 * URLs of this file's own, because a guard that refuses ordinary navigation
 * would be worse than the hole it closes.
 *
 * @see e107_plugins/pm/pm_func.php  pm_actionNeedsToken()
 */
class PmCsrfCest
{
	const PM = '/e107_plugins/pm/pm.php';

	/** A distinctive fragment of LAN_PM_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/** What class2.php answers with when e_core_session::check() turns a request away. */
	const UNAUTHORIZED = 'Unauthorized access!';

	/** @var int the account that sent the message, and the one blocking acts on */
	private $sender;

	/** @var int the account holding the session every request here rides */
	private $recipient;

	/** @var int */
	private $pmId;

	public function _before(AcceptanceTester $I)
	{
		// pm.php redirects while e107::isInstalled('pm') is false, which would
		// turn a refusal about the token into a refusal about the plugin.
		$I->havePluginInstalled('pm');

		$this->sender = $I->haveForumMember('pmcsrfsender');
		$this->recipient = $I->haveForumMember('pmcsrfrecipient');

		// pm_sent_del is already set, so deleting as the recipient takes the leg
		// that removes the row and unlinks the attachments rather than the leg
		// that only flags it, and the write is visible in one column.
		$this->pmId = $I->haveInDatabase('e107_private_msg', array(
			'pm_from' => $this->sender,
			'pm_to' => (string) $this->recipient,
			'pm_sent' => time() - 60,
			'pm_read' => 0,
			'pm_subject' => 'CSRF fixture',
			'pm_text' => 'CSRF fixture body',
			'pm_sent_del' => 1,
			'pm_read_del' => 0,
			'pm_attachments' => '',
			'pm_option' => '',
			'pm_size' => 0,
		));

		$I->logoutFromForum();
		$I->loginToForum('pmcsrfrecipient');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	public function aTokenlessGetDoesNotDeleteAPrivateMessage(AcceptanceTester $I)
	{
		$I->wantTo('refuse a delete that arrived without a token');

		$I->amOnPage(self::PM.'?del.'.$this->pmId.'.inbox');

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_private_msg', array('pm_id' => $this->pmId));
	}

	public function aTokenlessGetDoesNotBlockTheSender(AcceptanceTester $I)
	{
		$I->wantTo('refuse a block that arrived without a token');

		$I->amOnPage(self::PM.'?block.'.$this->sender);

		$I->seeInSource(self::REFUSED);
		$I->dontSeeInDatabase('e107_private_msg_block', $this->block());
	}

	public function aTokenlessGetDoesNotUnblockTheSender(AcceptanceTester $I)
	{
		$I->wantTo('refuse an unblock that arrived without a token');

		$I->haveInDatabase('e107_private_msg_block', $this->blockRow());

		$I->amOnPage(self::PM.'?unblock.'.$this->sender);

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_private_msg_block', $this->block());
	}

	public function aTokenlessGetDoesNotDeleteABlock(AcceptanceTester $I)
	{
		$I->wantTo('refuse a blocked-list delete that arrived without a token');

		$I->haveInDatabase('e107_private_msg_block', $this->blockRow());

		$I->amOnPage(self::PM.'?delblocked.'.$this->sender);

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_private_msg_block', $this->block());
	}

	/**
	 * Presence is all the plugin tests. Whether the value is the right one is
	 * attest()'s half of the division of labour, so assert that half too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('keep the framework refusing a token that does not validate');

		$I->amOnPage(self::PM.'?del.'.$this->pmId.'.inbox&e-token=not-even-close');

		$I->seeInSource(self::UNAUTHORIZED);
		$I->seeInDatabase('e107_private_msg', array('pm_id' => $this->pmId));
	}

	public function theInboxsOwnDeleteLinkStillDeletesTheMessage(AcceptanceTester $I)
	{
		$I->wantTo('keep the inbox delete button working');

		$I->amOnPage($this->publishedLink($I, self::PM.'?inbox', 'del.'.$this->pmId.'.inbox'));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInDatabase('e107_private_msg', array('pm_id' => $this->pmId));
	}

	public function theInboxsOwnBlockLinkStillBlocksTheSender(AcceptanceTester $I)
	{
		$I->wantTo('keep the inbox block button working');

		$I->amOnPage($this->publishedLink($I, self::PM.'?inbox', 'block.'.$this->sender));

		$I->dontSeeInSource(self::REFUSED);
		$I->seeInDatabase('e107_private_msg_block', $this->block());
	}

	/**
	 * The inbox publishes the unblock button in place of the block button once
	 * the sender is blocked, so the fixture has to put the block there first.
	 */
	public function theInboxsOwnUnblockLinkStillUnblocksTheSender(AcceptanceTester $I)
	{
		$I->wantTo('keep the inbox unblock button working');

		$I->haveInDatabase('e107_private_msg_block', $this->blockRow());

		$I->amOnPage($this->publishedLink($I, self::PM.'?inbox', 'unblock.'.$this->sender));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInDatabase('e107_private_msg_block', $this->block());
	}

	public function theBlockedListsOwnDeleteLinkStillRemovesTheBlock(AcceptanceTester $I)
	{
		$I->wantTo('keep the blocked-senders delete button working');

		$I->haveInDatabase('e107_private_msg_block', $this->blockRow());

		$I->amOnPage($this->publishedLink($I, self::PM.'?blocked', 'delblocked.'.$this->sender));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInDatabase('e107_private_msg_block', $this->block());
	}

	/**
	 * @return array the block this fixture is about, as a database criterion
	 */
	private function block()
	{
		return array(
			'pm_block_from' => $this->sender,
			'pm_block_to' => $this->recipient,
		);
	}

	/**
	 * @return array a row for private_msg_block, as block_add() would write it
	 */
	private function blockRow()
	{
		return $this->block() + array(
			'pm_block_datestamp' => time() - 60,
			'pm_block_count' => 0,
		);
	}

	/**
	 * The request the plugin's own page invites, token and all.
	 *
	 * The path is rebuilt from this file's own constant rather than taken from
	 * the href, because e107::url() answers with a SEF path on a site that has
	 * search-engine-friendly URLs turned on and with the plugin file on one
	 * that has not. Only the token is read off the page.
	 *
	 * @param AcceptanceTester $I
	 * @param string $page path of the page that publishes the link
	 * @param string $action query segment the link carries, e.g. "del.12.inbox"
	 * @return string
	 */
	private function publishedLink(AcceptanceTester $I, $page, $action)
	{
		$I->amOnPage($page);

		$pattern = '#\?'.preg_quote($action, '#').'(&amp;e-token=([^\'"&]+))?[\'"]#';

		if (!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The private messenger published no link for '.$action);
		}

		return self::PM.'?'.$action.(isset($matches[2]) ? '&e-token='.$matches[2] : '');
	}
}
