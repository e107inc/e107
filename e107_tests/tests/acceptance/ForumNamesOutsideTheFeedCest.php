<?php

/**
 * The who's-online page names forums and threads, and asked the wrong question.
 *
 * online.php prints the forum and thread a member is looking at. Its viewtopic
 * and viewforum branches gated on check_class($forum['forum_class']) alone, the
 * same single leg the feeds had, so a forum that is public in its own right
 * under a restricted category had its name and its thread's name printed to any
 * visitor as soon as somebody browsed it. Its _post branch carried no class test
 * on any path, so it disclosed the names of a forum restricted by its own class
 * too, which is the leg that was working everywhere else.
 *
 * The page needs a theme template to render anything at all. online.php blanks
 * $ONLINE_TABLE and its siblings (online.php:40-43) before including the
 * template, whose own assignments are guarded by !isset(), so the shipped
 * configuration renders an empty box and no theme in the tree carries an
 * online_template.php of its own. A theme that assigns them unconditionally is
 * a supported configuration and is what this fixture supplies; without it there
 * is nothing to measure, and the blanking is a separate defect that is not this
 * package's to fix.
 */
class ForumNamesOutsideTheFeedCest
{
	/** The class Fixture Category E and Fixture Forum D are closed to. */
	const CLASS_STAFF = 202;

	/** The installed front-end theme, which ships no online template of its own. */
	/**
	 * @var string the online template this Cest installs, under whichever theme
	 *             the site is actually running. Read from the application in
	 *             _before(), because the suite installs more than once.
	 */
	private $templateFile;

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int restricted in its own right, under the public category */
	private $forumD;

	/** @var int */
	private $threadD;

	/** @var int public in its own right, under a restricted category */
	private $forumE;

	/** @var int */
	private $threadE;

	/** @var int */
	private $watcher;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->ids = $I->haveForumStructure();

		$this->forumD = $I->haveForum('Fixture Forum D', 'fixture-forum-d',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 4, self::CLASS_STAFF);
		$this->threadD = $I->haveForumThread('Fixture Thread D', $this->forumD, 1);

		$categoryE = $I->haveForum('Fixture Category E', 'fixture-category-e',
			0, 0, 5, self::CLASS_STAFF);
		$this->forumE = $I->haveForum('Fixture Forum E', 'fixture-forum-e',
			$categoryE, \Helper\ForumFixture::CLASS_MOD_B, 6, 0);
		$this->threadE = $I->haveForumThread('Fixture Thread E', $this->forumE, 1);

		$this->watcher = $I->haveForumMember('onlinewatcher', '253');

		$this->templateFile = $I->grabActiveThemeDir().'online_template.php';
		$I->writeAppFile($this->templateFile, $this->templateSource());

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		if($this->templateFile !== null) { $I->deleteAppFile($this->templateFile); }
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * The viewtopic branch, which names a thread as well as a forum. Fixture
	 * Forum A is the positive control: a page that had simply stopped listing
	 * anybody would otherwise pass.
	 */
	public function theOnlinePageDoesNotNameAThreadInARestrictedForum(AcceptanceTester $I)
	{
		$this->haveWatcherAt($I, 'forum_viewtopic.php.'.$this->ids['threadA'].'.0');
		$this->fetchOnlinePage($I);
		$I->seeInSource('fixture-online');
		$I->see('Fixture Thread A');
		$I->see('Fixture Forum A');

		$this->haveWatcherAt($I, 'forum_viewtopic.php.'.$this->threadE.'.0');
		$this->fetchOnlinePage($I);
		$I->see('onlinewatcher');
		$I->dontSee('Fixture Thread E');
		$I->dontSee('Fixture Forum E');

		$this->haveWatcherAt($I, 'forum_viewtopic.php.'.$this->threadD.'.0');
		$this->fetchOnlinePage($I);
		$I->see('onlinewatcher');
		$I->dontSee('Fixture Thread D');
		$I->dontSee('Fixture Forum D');
	}

	/**
	 * The viewforum branch, which names the forum only.
	 */
	public function theOnlinePageDoesNotNameARestrictedForum(AcceptanceTester $I)
	{
		$this->haveWatcherAt($I, 'forum_viewforum.php.'.$this->ids['forumA'].'.0');
		$this->fetchOnlinePage($I);
		$I->see('Fixture Forum A');

		$this->haveWatcherAt($I, 'forum_viewforum.php.'.$this->forumE.'.0');
		$this->fetchOnlinePage($I);
		$I->see('onlinewatcher');
		$I->dontSee('Fixture Forum E');
	}

	/**
	 * The post branch, which had no class test on any path, so both restricted
	 * forums leaked through it.
	 */
	public function theOnlinePageDoesNotNameAForumSomebodyIsPostingIn(AcceptanceTester $I)
	{
		$this->haveWatcherAt($I, 'forum_post.php.'.$this->forumE.'.0');
		$this->fetchOnlinePage($I);
		$I->see('onlinewatcher');
		$I->dontSee('Fixture Forum E');
		$I->dontSee('Fixture Thread E');

		$this->haveWatcherAt($I, 'forum_post.php.'.$this->forumD.'.0');
		$this->fetchOnlinePage($I);
		$I->see('onlinewatcher');
		$I->dontSee('Fixture Forum D');
		$I->dontSee('Fixture Thread D');
	}

	// -----------------------------------------------------------------

	/**
	 * Put the member on a page, the way goOnline() records it.
	 *
	 * online.php reads the location out of the online table rather than out of a
	 * request, so the visitor being disclosed does not have to hold a session.
	 * online_pagecount has to be above zero or the row is not listed, and
	 * online_user_id has to be "id.name" or the row counts as a guest and never
	 * reaches $listuserson.
	 *
	 * @param AcceptanceTester $I
	 * @param string $page the tail of online_location, e.g. forum_viewtopic.php.7.0
	 */
	private function haveWatcherAt(AcceptanceTester $I, $page)
	{
		$I->resetFloodProtection();

		$I->haveInDatabase('e107_online', array(
			'online_timestamp' => time(),
			'online_flag'      => 0,
			'online_user_id'   => $this->watcher.'.onlinewatcher',
			'online_ip'        => '203.0.113.9',
			'online_location'  => '/e107_plugins/forum/'.$page,
			'online_pagecount' => 3,
			'online_active'    => time(),
			'online_agent'     => 'fixture',
			'online_language'  => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 */
	private function fetchOnlinePage(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/online.php');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();
	}

	/**
	 * A theme online template that assigns rather than defaults, so the page
	 * renders its user list. Deliberately minimal: the icon shortcode reaches
	 * the private-message plugin, which has nothing to do with what is measured.
	 *
	 * @return string
	 */
	private function templateSource()
	{
		return "<?php\n"
			."if (!defined('e107_INIT')) { exit; }\n"
			."\$ONLINE_TABLE_START = \"<div id='fixture-online'>\";\n"
			."\$ONLINE_TABLE = \"<div>{ONLINE_TABLE_USERNAME} :: {ONLINE_TABLE_LOCATION}</div>\";\n"
			."\$ONLINE_TABLE_END = \"</div>\";\n"
			."\$ONLINE_TABLE_MISC = \"\";\n";
	}
}
