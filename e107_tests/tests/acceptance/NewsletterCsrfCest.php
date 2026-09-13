<?php

/**
 * Newsletter administration unsubscribes an arbitrary account from an
 * arbitrary newsletter on a bare GET.
 *
 * admin_config.php splits e_QUERY on dots, so ?remove.<newsletter>.<user>
 * reaches remove_subscribers(), which rewrites the newsletter's subscriber
 * column with that account taken out of it. The permission on the page is
 * getperms('P'), and in cross-site request forgery the victim is the one who
 * holds it: the forged request arrives on the administrator's own session and
 * the gate opens for it.
 *
 * The subscriber list is one chr(1) separated column rather than a row per
 * subscriber, so what a removal leaves behind is exact and the assertions here
 * are made against the whole column rather than against a count.
 *
 * The control follows the delete button the subscribers page publishes for
 * itself, because a guard that refuses ordinary admin navigation would be
 * worse than the hole it closes.
 *
 * @see e107_plugins/newsletter/admin_config.php  newsletter::remove_subscribers()
 */
class NewsletterCsrfCest
{
	const ADMIN = '/e107_plugins/newsletter/admin_config.php';

	/** A distinctive fragment of NLLAN_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/** @var int */
	private $newsletterId;

	/** @var int the subscriber every request here tries to remove */
	private $target;

	/** @var int a second subscriber, so the page renders and the list survives */
	private $bystander;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('newsletter');

		$this->target = $I->haveForumMember('nlcsrftarget');
		$this->bystander = $I->haveForumMember('nlcsrfbystander');

		$this->newsletterId = $I->haveInDatabase('e107_newsletter', array(
			'newsletter_datestamp' => time() - 60,
			'newsletter_title' => 'CSRF fixture',
			'newsletter_text' => 'CSRF fixture body',
			'newsletter_header' => '',
			'newsletter_footer' => '',
			'newsletter_subscribers' => $this->subscribers(),
			'newsletter_parent' => 0,
			'newsletter_flag' => 0,
			'newsletter_issue' => '',
		));

		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	public function aTokenlessGetDoesNotUnsubscribeAnAccount(AcceptanceTester $I)
	{
		$I->wantTo('refuse an unsubscribe that arrived without a token');

		$I->amOnPage(self::ADMIN.'?remove.'.$this->newsletterId.'.'.$this->target);

		$I->seeInSource(self::REFUSED);
		$I->seeInDatabase('e107_newsletter', array(
			'newsletter_id' => $this->newsletterId,
			'newsletter_subscribers' => $this->subscribers(),
		));
	}

	/**
	 * Presence is all the endpoint tests. Whether the value is the right one is
	 * e_core_session::check()'s half, so assert that half too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('keep the framework refusing a token that does not validate');

		$I->amOnPage(self::ADMIN.'?remove.'.$this->newsletterId.'.'.$this->target
			.'&e-token=not-even-close');

		$I->seeInSource('Unauthorized access!');
		$I->seeInDatabase('e107_newsletter', array(
			'newsletter_id' => $this->newsletterId,
			'newsletter_subscribers' => $this->subscribers(),
		));
	}

	public function theSubscriberListsOwnDeleteLinkStillUnsubscribes(AcceptanceTester $I)
	{
		$I->wantTo('keep the subscribers page delete button working');

		$action = 'remove.'.$this->newsletterId.'.'.$this->target;

		$I->amOnPage(self::ADMIN.'?vs.'.$this->newsletterId);

		$pattern = '#\?'.preg_quote($action, '#').'(&amp;e-token=([^\'"&]+))?[\'"]#';

		if (!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The subscribers page published no link for '.$action);
		}

		$I->amOnPage(self::ADMIN.'?'.$action.(isset($matches[2]) ? '&e-token='.$matches[2] : ''));

		$I->dontSeeInSource(self::REFUSED);
		$I->seeInDatabase('e107_newsletter', array(
			'newsletter_id' => $this->newsletterId,
			'newsletter_subscribers' => chr(1).$this->bystander,
		));
	}

	/**
	 * @return string the column as the plugin writes it: a leading separator
	 *         and then one account id per separator
	 */
	private function subscribers()
	{
		return chr(1).$this->target.chr(1).$this->bystander;
	}
}
