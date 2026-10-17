<?php

/**
 * A member's bbcode inside a [table] block must not run as script on the topic page.
 *
 * e_parse::toHTML() rendered the bbcode inside a [table], [hide] or [html] block before it
 * restored entity-encoded quotes, so a quote the image handler had encoded was
 * turned back into a raw one that closed the src attribute and opened a live
 * event handler. An ordinary member's forum reply reaches that path with
 * bbcode on, so the handler fired for everyone who opened the topic, an
 * administrator included.
 *
 * The payload wraps an image whose address carries a quote in a [table]. The
 * address resolves to a real file, so the only thing under test is whether the
 * quote stays encoded.
 */
class ForumBBcodeTableEntityCest
{
	/** The quote-carrying image address that broke out before the fix. */
	const STORED_TABLE_PAYLOAD = "[table][img]{e_IMAGE}generic/blank.gif' autofocus onfocus='window.__probe=1[/img][/table] weekly notes";

	/** The same address in an [html] block whose tags toDB() does not strip, which took the same path. */
	const STORED_HTML_PAYLOAD = "[html ][img]{e_IMAGE}generic/blank.gif' autofocus onfocus='window.__probe=2[/img][/HTML] html notes";

	/** A benign reply, so a fix that stopped rendering bbcode would fail here too. */
	const BENIGN_PAYLOAD = '[b]ordinary reply[/b]';

	/** @var array */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$I->haveForumMember('tablealice');
		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('tablealice');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	/**
	 * The control: a member can reply, and ordinary bbcode still renders, or a
	 * fix that refused the reply or stopped parsing would turn this file green.
	 */
	public function aMemberReplyStillRendersOrdinaryBBcode(AcceptanceTester $I)
	{
		$this->reply($I, self::BENIGN_PAYLOAD);

		$I->seeInDatabase('e107_forum_post', array(
			'post_thread'     => $this->ids['threadA'],
			'post_entry like' => '%[b]ordinary reply[/b]%',
		));

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->seeElement('strong.bbcode');
	}

	/**
	 * The defect: the stored [table] payload must not put a live handler on the
	 * topic page served to a guest.
	 */
	public function aTableBBcodeReplyDoesNotRunScriptOnTheTopicPage(AcceptanceTester $I)
	{
		$this->reply($I, self::STORED_TABLE_PAYLOAD);

		$I->logoutFromForum();
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		// The reply is on the page: its text proves the row rendered.
		$I->seeInSource('weekly notes');
		// The quote the image handler encoded stays encoded, so it cannot close
		// src='...' and open a handler.
		$I->dontSeeElement('[onfocus]');
		$I->dontSeeInSource("onfocus='window.__probe=1");
	}

	/**
	 * The [html] block took the same render-then-restore path, and a member's storage keeps its tags when they are not the exact lowercase pair.
	 */
	public function anHtmlBBcodeReplyDoesNotRunScriptOnTheTopicPage(AcceptanceTester $I)
	{
		$this->reply($I, self::STORED_HTML_PAYLOAD);

		$I->logoutFromForum();
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->seeInSource('html notes');
		$I->dontSeeElement('[onfocus]');
		$I->dontSeeInSource("onfocus='window.__probe=2");
	}

	private function reply(AcceptanceTester $I, $body)
	{
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA']);
		$I->submitForm('#dataform', array('post' => $body), 'reply');
	}
}
