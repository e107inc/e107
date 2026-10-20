<?php

/**
 * The reported posts list names each thread in a modal caption that the admin script inserts as text.
 */
class ForumReportCaptionCest
{
	/** A subject a member typed as entity text, which the new-thread form stores as it is. */
	const SUBJECT = '&lt;img src=x onerror=alert(1)&gt; caption probe';

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$I->haveSitePrefUntilAfter('antiflood1', 0);
		$I->haveForumMember('captionmember');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
		$I->dropForumProbe();
	}

	/**
	 * A member's thread, once reported, is named in the caption as the text it was typed as.
	 */
	public function aReportedThreadIsNamedInTheCaptionAsText(AcceptanceTester $I)
	{
		$I->loginToForum('captionmember');
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=nt&id='.$this->ids['forumA']);
		$I->submitForm('#dataform', array('subject' => self::SUBJECT, 'post' => 'A post to report'), 'newthread');
		$thread = (int) $I->grabFromDatabase('e107_forum_thread', 'thread_id', array('thread_name like' => '%caption probe'));
		$post = (int) $I->grabFromDatabase('e107_forum_post', 'post_id', array('post_thread' => $thread));

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=report&id='.$thread.'&post='.$post);
		$I->submitForm('#forum-report-thread', array('report_add' => 'Reported for the caption'), 'report_thread');
		$I->seeInDatabase('e107_generic', array('gen_type' => 'reported_post', 'gen_intdata' => $thread));
		$I->logoutFromForum();

		$I->loginAsAdmin();
		$I->amOnPage('/e107_plugins/forum/forum_admin.php?mode=report&action=list');

		$this->seeCaptionAsText($I, $thread, '<img src=x onerror=alert(1)> caption probe');
	}

	/**
	 * A report whose stored name holds markup is listed and captioned as text.
	 */
	public function aReportHoldingMarkupIsListedAndCaptionedAsText(AcceptanceTester $I)
	{
		$thread = $I->haveForumThread('Report probe', $this->ids['forumA'], 1);
		$I->haveInDatabase('e107_generic', array(
			'gen_type' => 'reported_post', 'gen_datestamp' => time(), 'gen_user_id' => 1,
			'gen_ip' => '<name-markup>raw probe</name-markup>', 'gen_intdata' => $thread, 'gen_chardata' => 'Reported',
		));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_plugins/forum/forum_admin.php?mode=report&action=list');

		$I->dontSeeInSource('<name-markup>');
		$this->seeCaptionAsText($I, $thread, '<name-markup>raw probe</name-markup>');
	}

	/**
	 * The caption of a report's view button, as the admin script reads it, is "Topic: " and $text, the name as it is shown.
	 *
	 * @param AcceptanceTester $I
	 * @param int $thread
	 * @param string $text
	 */
	private function seeCaptionAsText(AcceptanceTester $I, $thread, $text)
	{
		$caption = $I->grabAttributeFrom('a.e-modal[href*="mode=post&action=list&id='.$thread.'"]', 'data-modal-caption');

		$I->assertSame('Topic: '.$text, $caption);
	}
}
