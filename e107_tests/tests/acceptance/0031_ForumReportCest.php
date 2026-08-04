<?php

/**
 * Reporting a post to the moderators.
 *
 * The form inserts a row and fires an event that mails the moderators, and it
 * had no throttle of any kind: anyone who may post could submit it as fast as
 * they could script it, with the site's own mail server doing the sending.
 * Posting has answered to the site's flood setting for as long as the plugin
 * has existed; reporting now answers to the same one, scoped to the reporter so
 * that one person cannot silence everybody else's reports.
 *
 * The handler also read USERNAME bare, and class2.php defines it only for a
 * signed-in visitor, so on a site that allows anonymous posting a guest
 * reporting a post got a fatal instead.
 */
class ForumReportCest
{
	const TOO_SOON = 'Please wait a moment before sending another report';

	/** @var array */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$I->haveForumMember('reportalice');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * The control. A report has to land, or every refusal below proves nothing.
	 */
	public function aMemberCanReportAPost(AcceptanceTester $I)
	{
		$I->loginToForum('reportalice');
		$this->report($I, 'a genuine first report');

		$I->seeInDatabase('e107_generic', array(
			'gen_type' => 'reported_post',
			'gen_chardata like' => '%a genuine first report%',
		));
	}

	/**
	 * The same reporter, straight away again. The site's flood setting is on by
	 * default with a ten second window, so the second one is inside it.
	 */
	public function reportingTwiceInARowIsRefused(AcceptanceTester $I)
	{
		$I->loginToForum('reportalice');

		$this->report($I, 'the report that gets through');
		$this->report($I, 'the report that should not');

		$I->seeInDatabase('e107_generic', array(
			'gen_chardata like' => '%the report that gets through%',
		));
		$I->dontSeeInDatabase('e107_generic', array(
			'gen_chardata like' => '%the report that should not%',
		));
		$I->see(self::TOO_SOON);
	}

	/**
	 * Forum A is open to everyone, which is what makes reporting reachable
	 * without an account, which is what made the missing USERNAME a fatal.
	 */
	public function aVisitorWithNoAccountCanReportWithoutBreakingThePage(AcceptanceTester $I)
	{
		$this->report($I, 'a report from nobody in particular');

		$I->seeResponseCodeIs(200);
		// "USERNAME" alone is too common on a rendered page to assert on.
		$I->dontSee('Undefined constant');
		$I->dontSee('Fatal error');
		$I->seeInDatabase('e107_generic', array(
			'gen_type' => 'reported_post',
			'gen_chardata like' => '%a report from nobody in particular%',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $reason
	 */
	private function report(AcceptanceTester $I, $reason)
	{
		$page = '/e107_plugins/forum/forum_post.php'
			.'?f=report&id='.$this->ids['threadA'].'&post='.$this->ids['postA'];

		$I->sendPostRequest($page, array(
			'report_thread' => 1,
			'report_add' => $reason,
			'e-token' => $I->grabForumToken($page),
		));
	}
}
