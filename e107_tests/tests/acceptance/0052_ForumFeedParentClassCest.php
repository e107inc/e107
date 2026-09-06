<?php

/**
 * A forum feed has to ask the same question the forum page asks.
 *
 * A forum's view permission has two legs: the forum's own forum_class, and the
 * forum_class of the category it sits under. _getForumPermList() reads both,
 * and so do e_search.php and newforumposts_menu.php. The RSS handler reads only
 * the first, so every forum that is public in its own right inside a restricted
 * category was served to anybody who asked, with no account and no cookie.
 *
 * Four feeds are affected and all four are reachable anonymously:
 *
 *  - forumthreads and forumname carry the opening post of each topic.
 *  - forumposts carries every post, replies included.
 *  - forumtopic carries every post of one named topic, which is the sharpest
 *    of the four: a reader who knows a thread id gets the whole conversation.
 *
 * forum_stats.php has the same shape and gives up thread names, reply counts
 * and authors for the same forums.
 *
 * The fixture is the one that matters and the one P12 established: Fixture
 * Forum E carries forum_class 0, so a predicate that reads only the forum's own
 * class believes it is public. Fixture Forum D is the mirror image, restricted
 * in its own right under a public category, and it is here so that a fix
 * cannot pass by swapping one leg for the other. Fixture Forum A is public
 * through both legs and every assertion below is paired with it, because a
 * feed that had simply stopped answering would otherwise look like a fix.
 */
class ForumFeedParentClassCest
{
	/** The class Fixture Category E and Fixture Forum D are closed to. */
	const CLASS_STAFF = 202;

	/** RSS 2.0, the format rss.php builds for type 2. */
	const RSS_TYPE = 2;

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

	/** @var string a member who holds the class both restricted forums are closed to */
	private $staffReader = 'feedstaff';

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->havePluginInstalled('rss_menu');

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->ids = $I->haveForumStructure();

		// forum_stats.php divides by the reply count, so the fixture needs at
		// least one reply or the page dies of a DivisionByZeroError before it
		// reaches anything worth measuring.
		$I->haveForumPost('Reply in A', $this->ids['threadA'], $this->ids['forumA'], 1);

		$this->forumD = $I->haveForum('Fixture Forum D', 'fixture-forum-d',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 4, self::CLASS_STAFF);
		$this->threadD = $I->haveForumThread('Fixture Thread D', $this->forumD, 1);
		$I->haveForumPost('Opening post in D', $this->threadD, $this->forumD, 1);

		$categoryE = $I->haveForum('Fixture Category E', 'fixture-category-e',
			0, 0, 5, self::CLASS_STAFF);
		$this->forumE = $I->haveForum('Fixture Forum E', 'fixture-forum-e',
			$categoryE, \Helper\ForumFixture::CLASS_MOD_B, 6, 0);
		$this->threadE = $I->haveForumThread('Fixture Thread E', $this->forumE, 1);
		$I->haveForumPost('Opening post in E', $this->threadE, $this->forumE, 1);

		$I->haveForumMember($this->staffReader, '253,'.self::CLASS_STAFF);

		$this->haveFeed($I, 'forumthreads');
		$this->haveFeed($I, 'forumposts');
		$this->haveFeed($I, 'forumtopic', '*');
		$this->haveFeed($I, 'forumname', '*');

		$I->purgeForumPermCache();
		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * The fixture has to hold, or every refusal below is satisfied by a forum
	 * that was never restricted. Both halves, so "closed" is told apart from
	 * "the plugin never rendered".
	 */
	public function anAnonymousReaderCannotOpenEitherRestrictedThread(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->see('Opening post in A');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->threadD);
		$I->dontSee('Opening post in D');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->threadE);
		$I->dontSee('Opening post in E');
	}

	/**
	 * The whole-site topic feed. It carries the opening post of every topic, so
	 * what leaks is the body and not merely the title.
	 */
	public function theTopicFeedSkipsAForumUnderARestrictedCategory(AcceptanceTester $I)
	{
		$this->fetchFeed($I, 'forumthreads');

		$I->seeInSource('Opening post in A');
		$I->dontSeeInSource('Opening post in D');
		$I->dontSeeInSource('Opening post in E');
		$I->dontSeeInSource('Fixture Thread E');
	}

	/**
	 * The whole-site post feed, which carries replies as well.
	 */
	public function thePostFeedSkipsAForumUnderARestrictedCategory(AcceptanceTester $I)
	{
		$this->fetchFeed($I, 'forumposts');

		$I->seeInSource('Opening post in A');
		$I->seeInSource('Reply in A');
		$I->dontSeeInSource('Opening post in D');
		$I->dontSeeInSource('Opening post in E');
		$I->dontSeeInSource('Fixture Thread E');
	}

	/**
	 * The single-topic feed. Nothing but a thread id is needed to ask for it,
	 * and it answers with every post in the thread.
	 */
	public function theSingleTopicFeedSkipsAForumUnderARestrictedCategory(AcceptanceTester $I)
	{
		$this->fetchFeed($I, 'forumtopic', $this->ids['threadA']);
		$I->seeInSource('Opening post in A');

		$this->fetchFeed($I, 'forumtopic', $this->threadD);
		$I->dontSeeInSource('Opening post in D');

		$this->fetchFeed($I, 'forumtopic', $this->threadE);
		$I->dontSeeInSource('Opening post in E');
		$I->dontSeeInSource('Fixture Thread E');
		$I->dontSeeInSource('<item>');
	}

	/**
	 * The per-forum feed, asked for by forum id.
	 */
	public function thePerForumFeedSkipsAForumUnderARestrictedCategory(AcceptanceTester $I)
	{
		$this->fetchFeed($I, 'forumname', $this->ids['forumA']);
		$I->seeInSource('Opening post in A');

		$this->fetchFeed($I, 'forumname', $this->forumD);
		$I->dontSeeInSource('Opening post in D');

		$this->fetchFeed($I, 'forumname', $this->forumE);
		$I->dontSeeInSource('Opening post in E');
		$I->dontSeeInSource('Fixture Thread E');
	}

	/**
	 * The statistics page runs the same predicate over its two league tables,
	 * so it names the topics of a forum the caller cannot open.
	 */
	public function theStatisticsPageSkipsAForumUnderARestrictedCategory(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/forum/forum_stats.php');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->seeInSource('Fixture Thread A');
		$I->dontSeeInSource('Fixture Thread D');
		$I->dontSeeInSource('Fixture Thread E');
	}

	/**
	 * The other direction, and the one a predicate that is merely class-blind
	 * would fail. The fix replaced the predicate wholesale rather than adding a
	 * leg to it, so "the feed refuses a guest" is only half the contract: a
	 * reader who holds the restricted class must still be served both forums,
	 * or the fix is a denial of service dressed as a permission check.
	 */
	public function aReaderWhoHoldsTheRestrictedClassStillSeesBothForums(AcceptanceTester $I)
	{
		$I->loginToForum($this->staffReader);

		$this->fetchFeedAsCurrentUser($I, 'forumthreads');
		$I->seeInSource('Opening post in A');
		$I->seeInSource('Opening post in D');
		$I->seeInSource('Opening post in E');

		$this->fetchFeedAsCurrentUser($I, 'forumposts');
		$I->seeInSource('Opening post in A');
		$I->seeInSource('Opening post in D');
		$I->seeInSource('Opening post in E');

		$this->fetchFeedAsCurrentUser($I, 'forumtopic', $this->threadE);
		$I->seeInSource('Opening post in E');
	}

	/**
	 * rss.php answers a POST as readily as a GET, and e107forum::__construct()
	 * turns any request carrying fjsubmit into a redirect to whatever
	 * forumjump names, unvalidated. Reading a permission list must not enter
	 * that constructor, so this endpoint has to keep answering with a feed.
	 */
	public function theFeedDoesNotRedirectAPostedForumJump(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->sendPostRequest('/e107_plugins/rss_menu/rss.php?forumthreads.'.self::RSS_TYPE.'.', array(
			'fjsubmit'  => '1',
			'forumjump' => 'https://example.invalid/',
		));
		$I->seeNoRedirectTo('example.invalid');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();
	}

	// -----------------------------------------------------------------

	/**
	 * Publish one of the forum plugin's feeds.
	 *
	 * rss.php serves nothing it cannot find a row for in the rss table, and a
	 * stock install has none: the admin area writes them from the plugin's own
	 * config(). rss_topicid '*' is the wildcard the lookup falls back to when
	 * the request names a topic.
	 *
	 * @param AcceptanceTester $I
	 * @param string $url the feed's rss_url, e.g. 'forumthreads'
	 * @param string $topic rss_topicid
	 */
	private function haveFeed(AcceptanceTester $I, $url, $topic = '')
	{
		$I->haveInDatabase('e107_rss', array(
			'rss_name' => 'Fixture feed '.$url,
			'rss_url' => $url,
			'rss_topicid' => $topic,
			'rss_path' => 'forum',
			'rss_text' => 'fixture',
			'rss_datestamp' => time(),
			'rss_class' => 0,
			'rss_limit' => 50,
		));
	}

	/**
	 * Ask for a feed as a client with no cookie at all.
	 *
	 * The trailing dot is not decoration: rss.php explodes e_QUERY on it and
	 * reads all three segments, so a two segment query makes the page read past
	 * the end of the array before it answers.
	 *
	 * Redirects are refused rather than followed. An e107 that cannot serve a
	 * request answers with a Location of install.php, and chasing that would
	 * turn a broken feed into a green test.
	 *
	 * @param AcceptanceTester $I
	 * @param string $url
	 * @param int|string $topic
	 */
	private function fetchFeed(AcceptanceTester $I, $url, $topic = '')
	{
		$I->resetAllCookies();
		$this->fetchFeedAsCurrentUser($I, $url, $topic);
	}

	/**
	 * The same fetch, with whatever session the test is holding left alone.
	 *
	 * A 200 that omits the string under test is not on its own a pass: an empty
	 * channel and a channel with a PHP diagnostic wedged into it both satisfy
	 * that. Assert the document closes, and that nothing was emitted into it.
	 *
	 * @param AcceptanceTester $I
	 * @param string $url
	 * @param int|string $topic
	 */
	private function fetchFeedAsCurrentUser(AcceptanceTester $I, $url, $topic = '')
	{
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/rss_menu/rss.php?'.$url.'.'.self::RSS_TYPE.'.'.$topic);
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->seeInSource('</rss>');
		$I->dontSeeInSource('Warning');
		$I->dontSeeInSource('Deprecated');
		$I->dontSeeInSource('Fatal error');
	}
}
