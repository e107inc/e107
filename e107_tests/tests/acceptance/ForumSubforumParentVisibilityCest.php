<?php

/**
 * A sub-forum is closed to whoever its parent forum is closed to, on its pages and in the feeds.
 */
class ForumSubforumParentVisibilityCest
{
	/** RSS 2.0, the format rss.php builds for type 2. */
	const RSS_TYPE = 2;

	/** A class the guest is not in and the staff reader is. */
	const CLASS_STAFF = 202;

	/** @var int */
	private $staffForum;

	/** @var int */
	private $sub;

	/** @var int */
	private $subThread;

	/** @var int */
	private $category;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->havePluginInstalled('rss_menu');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->category = $category = $I->haveForum('Fixture Public Category', 'fixture-public-category', 0, 0, 1, 0);

		$open = $I->haveForum('Fixture Open Forum', 'fixture-open-forum', $category, \Helper\ForumFixture::CLASS_MOD_A, 2, 0);
		$openThread = $I->haveForumThread('Fixture Open Thread', $open, 1);
		$I->haveForumPost('Opening post in the open forum Zqxsubword', $openThread, $open, 1);

		$this->staffForum = $I->haveForum('Fixture Staff Forum', 'fixture-staff-forum', $category,
			\Helper\ForumFixture::CLASS_MOD_A, 3, self::CLASS_STAFF);
		$this->sub = $I->haveForum('Fixture Everyone Sub', 'fixture-everyone-sub', $category,
			\Helper\ForumFixture::CLASS_MOD_A, 4, 0, null, null, $this->staffForum);
		$this->subThread = $I->haveForumThread('Fixture Sub Thread', $this->sub, 1);
		$I->haveForumPost('Opening post in the sub-forum Zqxsubword', $this->subThread, $this->sub, 1);

		$I->haveForumMember('staffreader', '253,'.self::CLASS_STAFF);

		foreach (array(array('forumthreads', ''), array('forumposts', ''), array('forumtopic', '*'), array('forumname', '*')) as $feed)
		{
			$I->haveInDatabase('e107_rss', array(
				'rss_name' => 'Fixture feed '.$feed[0], 'rss_url' => $feed[0], 'rss_topicid' => $feed[1],
				'rss_path' => 'forum', 'rss_text' => 'fixture', 'rss_datestamp' => time(),
				'rss_class' => 0, 'rss_limit' => 50,
			));
		}

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->purgeForumPermCache();
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	public function aGuestIsRefusedTheSubForumAndItsThreads(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();

		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->staffForum);
		$I->seeResponseCodeIsRedirection();

		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->sub);
		$I->seeResponseCodeIsRedirection();
		$I->dontSee('Fixture Sub Thread');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->subThread);
		$I->seeResponseCodeIsRedirection();
		$I->dontSee('Opening post in the sub-forum');
	}

	public function theFeedsLeaveTheSubForumOutForAGuest(AcceptanceTester $I)
	{
		$I->resetAllCookies();

		$this->fetchFeed($I, 'forumthreads', '');
		$I->seeInSource('Opening post in the open forum');
		$I->dontSeeInSource('Opening post in the sub-forum');

		$this->fetchFeed($I, 'forumposts', '');
		$I->seeInSource('Opening post in the open forum');
		$I->dontSeeInSource('Opening post in the sub-forum');

		$this->fetchFeed($I, 'forumtopic', $this->subThread);
		$I->dontSeeInSource('Opening post in the sub-forum');

		$this->fetchFeed($I, 'forumname', $this->sub);
		$I->dontSeeInSource('Opening post in the sub-forum');
	}

	public function aGuestIsRefusedASubForumOfAClosedSubForum(AcceptanceTester $I)
	{
		$deeper = $I->haveForum('Fixture Deeper Sub', 'fixture-deeper-sub', $this->category,
			\Helper\ForumFixture::CLASS_MOD_A, 5, 0, null, null, $this->sub);
		$thread = $I->haveForumThread('Fixture Deeper Thread', $deeper, 1);
		$I->haveForumPost('Opening post in the deeper sub-forum', $thread, $deeper, 1);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$thread);
		$I->seeResponseCodeIsRedirection();

		$this->fetchFeed($I, 'forumthreads', '');
		$I->seeInSource('Opening post in the open forum');
		$I->dontSeeInSource('Opening post in the deeper sub-forum');
	}

	public function aGuestIsRefusedASubForumWhoseForumSitsInAClosedCategory(AcceptanceTester $I)
	{
		$members = $I->haveForum('Fixture Members Category', 'fixture-members-category', 0, 0, 6, 253);
		$moved = $I->haveForum('Fixture Moved Forum', 'fixture-moved-forum', $members, \Helper\ForumFixture::CLASS_MOD_A, 7, 0);
		$left = $I->haveForum('Fixture Left Sub', 'fixture-left-sub', $this->category,
			\Helper\ForumFixture::CLASS_MOD_A, 8, 0, null, null, $moved);
		$thread = $I->haveForumThread('Fixture Left Thread', $left, 1);
		$I->haveForumPost('Opening post in the left sub-forum', $thread, $left, 1);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$thread);
		$I->seeResponseCodeIsRedirection();

		$this->fetchFeed($I, 'forumthreads', '');
		$I->seeInSource('Opening post in the open forum');
		$I->dontSeeInSource('Opening post in the left sub-forum');
	}

	public function theSearchLeavesTheSubForumOutForAGuest(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/search.php');

		$I->resetAllCookies();
		$I->amOnPage('/search.php?q=Zqxsubword&t=forum&r=0');

		$I->see('Fixture Open Thread');
		$I->dontSee('Fixture Sub Thread');
		$I->dontSeeInSource('Opening post in the sub-forum');
	}

	public function aSubForumKeepsItsOwnPostingClasses(AcceptanceTester $I)
	{
		$announcements = $I->haveForum('Fixture Announcements', 'fixture-announcements', $this->category,
			\Helper\ForumFixture::CLASS_MOD_A, 9, 0, 254, 254);
		$feedback = $I->haveForum('Fixture Feedback Sub', 'fixture-feedback-sub', $this->category,
			\Helper\ForumFixture::CLASS_MOD_A, 10, 0, 253, 253, $announcements);
		$I->haveForumMember('feedbackposter', '253');
		$I->purgeForumPermCache();

		$I->loginToForum('feedbackposter');

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=nt&id='.$announcements);
		$I->seeInSource('forum-post-unauthorized');

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=nt&id='.$feedback);
		$I->dontSeeInSource('forum-post-unauthorized');
		$I->seeElement('input[name=subject]');
	}

	public function aReaderOfTheParentForumStillReadsTheSubForum(AcceptanceTester $I)
	{
		$I->loginToForum('staffreader');

		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->sub);
		$I->see('Fixture Sub Thread');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->subThread);
		$I->see('Opening post in the sub-forum');

		$this->fetchFeed($I, 'forumtopic', $this->subThread);
		$I->seeInSource('Opening post in the sub-forum');
	}

	/**
	 * One feed, as whoever holds the session, which must be a whole RSS document.
	 *
	 * @param AcceptanceTester $I
	 * @param string $url rss_url
	 * @param int|string $topic
	 * @return void
	 */
	private function fetchFeed(AcceptanceTester $I, $url, $topic)
	{
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/rss_menu/rss.php?'.$url.'.'.self::RSS_TYPE.'.'.$topic);
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();
		$I->seeInSource('</rss>');
	}
}
