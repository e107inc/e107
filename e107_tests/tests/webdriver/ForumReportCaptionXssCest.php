<?php

/**
 * The browser half of acceptance's ForumReportCaptionCest, on master only, as release/v2.3.x has no webdriver suite.
 */
class ForumReportCaptionXssCest
{
	/** A thread name as the new-thread form stores a member's entity text. */
	const STORED_NAME = '&lt;img src=x onerror=window.__d2=1&gt; caption probe';

	/** @var int */
	private $thread;

	public function _before(WebDriverTester $I)
	{
		$I->haveForumPluginInstalled();
		$ids = $I->haveForumStructure();

		$this->thread = $I->haveForumThread(self::STORED_NAME, $ids['forumA'], 1);
		$I->haveForumPost('A post to report', $this->thread, $ids['forumA'], 1);
		$I->haveInDatabase('e107_generic', array(
			'gen_type' => 'reported_post', 'gen_datestamp' => time(), 'gen_user_id' => 1,
			'gen_ip' => self::STORED_NAME, 'gen_intdata' => $this->thread, 'gen_chardata' => 'Reported for the caption',
		));

		$I->purgeForumPermCache();
	}

	public function _after(WebDriverTester $I)
	{
		$I->dropForumProbe();
	}

	public function openingAReportedThreadRunsNothingItsNameCarries(WebDriverTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnPage('/e107_plugins/forum/forum_admin.php?mode=report&action=list');
		$I->click('a.e-modal[href*="mode=post&action=list&id='.$this->thread.'"]');
		$I->waitForText('caption probe', 10, '.modal-caption');

		$I->executeJS('window.__d2sentinel = false;'
			.' document.body.insertAdjacentHTML("beforeend",'
			.' \'<img id="d2sentinel" src="x" onerror="window.__d2sentinel=true">\');');
		$I->waitForJS('return window.__d2sentinel === true;', 10);

		$I->assertSame('SAFE', $I->executeJS('return window.__d2 === 1 ? "RAN" : "SAFE";'));
		$I->assertSame(0, (int) $I->executeJS('return document.querySelectorAll(".modal-caption img").length;'));
		$caption = $I->executeJS('return document.querySelector(".modal-caption").textContent;');
		$I->assertSame(1, substr_count($caption, '<img src=x onerror=window.__d2=1> caption probe'), $caption);
	}
}
