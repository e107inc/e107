<?php

/**
 * The forum's e_list handler leaks the same forums the feed does.
 *
 * list_new renders "latest" and "since your last visit" listings out of every
 * plugin that ships an e_list.php, and the forum's builds two queries. Both
 * filter forum_class on the thread's own forum and neither reaches the category
 * above it, so a forum that is public in its own right inside a restricted
 * category is listed to anybody: thread name, forum name, author, last poster
 * and reply count.
 *
 * It is titles rather than bodies, which is why this file exists separately
 * from the feed one and why it is worth having at all. e_list.php was not in
 * the report that opened this package; it turned up by looking for the shape
 * rather than for the files.
 *
 * The two queries need two different callers. The "recent" listing has no time
 * filter and answers a guest. The "new" listing compares thread_lastpost
 * against USERLV, which class2.php only defines for a signed-in visitor, so a
 * guest is served nothing and the query goes unmeasured unless a member asks
 * for it. That query also spells the predicate on an unqualified forum_class
 * across five joined tables, which resolves today only because one of them has
 * such a column.
 */
class ForumListParentClassCest
{
	/** The class Fixture Category E and Fixture Forum D are closed to. */
	const CLASS_STAFF = 202;

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int restricted in its own right, under the public category */
	private $forumD;

	/** @var int public in its own right, under a restricted category */
	private $forumE;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->havePluginInstalled('list_new');
		$I->havePluginPrefs('list_new', $this->listPrefs());

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->ids = $I->haveForumStructure();

		$this->forumD = $I->haveForum('Fixture Forum D', 'fixture-forum-d',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 4, self::CLASS_STAFF);
		$threadD = $I->haveForumThread('Fixture Thread D', $this->forumD, 1);
		$I->haveForumPost('Opening post in D', $threadD, $this->forumD, 1);

		$categoryE = $I->haveForum('Fixture Category E', 'fixture-category-e',
			0, 0, 5, self::CLASS_STAFF);
		$this->forumE = $I->haveForum('Fixture Forum E', 'fixture-forum-e',
			$categoryE, \Helper\ForumFixture::CLASS_MOD_B, 6, 0);
		$threadE = $I->haveForumThread('Fixture Thread E', $this->forumE, 1);
		$I->haveForumPost('Opening post in E', $threadE, $this->forumE, 1);

		$I->haveForumMember('listreader', '253');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->havePluginPrefs('list_new', array());
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * The "latest" listing, asked for with no cookie at all.
	 */
	public function theRecentListingSkipsAForumUnderARestrictedCategory(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/list_new/list.php');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->see('Fixture Thread A');
		$I->dontSee('Fixture Thread D');
		$I->dontSee('Fixture Thread E');
		$I->dontSee('Fixture Forum E');
	}

	/**
	 * The "since your last visit" listing, which only a signed-in caller can
	 * reach. The member holds e_UC_MEMBER and nothing else, so both restricted
	 * forums are closed to them exactly as they are to a guest.
	 */
	public function theNewListingSkipsAForumUnderARestrictedCategory(AcceptanceTester $I)
	{
		$I->loginToForum('listreader');

		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/list_new/list.php?new');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->see('Fixture Thread A');
		$I->dontSee('Fixture Thread D');
		$I->dontSee('Fixture Thread E');
		$I->dontSee('Fixture Forum E');
	}

	/**
	 * list.php answers a POST as readily as a GET, and e107forum::__construct()
	 * turns any request carrying fjsubmit into a redirect to whatever forumjump
	 * names, unvalidated. Reading a permission list must not enter that
	 * constructor, so this page has to keep answering with a listing.
	 */
	public function theListingDoesNotRedirectAPostedForumJump(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->sendPostRequest('/e107_plugins/list_new/list.php', array(
			'fjsubmit'  => '1',
			'forumjump' => 'https://example.invalid/',
		));
		$I->seeNoRedirectTo('example.invalid');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();
	}

	// -----------------------------------------------------------------

	/**
	 * Enough of list_new's configuration to render the forum section on both
	 * pages, and no more.
	 *
	 * Written rather than left to the plugin's own defaults because
	 * getListPrefs() builds those from a section list it has not populated
	 * yet, which on PHP 8 is a TypeError rather than an empty page.
	 *
	 * @return array
	 */
	private function listPrefs()
	{
		$prefs = array();

		foreach (array('recent_page', 'new_page') as $mode)
		{
			$prefs[$mode.'_welcometext'] = '';
			// The section counter is divided by this one.
			$prefs[$mode.'_colomn'] = '1';

			$prefs['forum_'.$mode.'_caption'] = 'Fixture forum '.$mode;
			$prefs['forum_'.$mode.'_display'] = '1';
			$prefs['forum_'.$mode.'_open'] = '1';
			$prefs['forum_'.$mode.'_author'] = '1';
			$prefs['forum_'.$mode.'_category'] = '1';
			$prefs['forum_'.$mode.'_date'] = '1';
			$prefs['forum_'.$mode.'_icon'] = '';
			$prefs['forum_'.$mode.'_amount'] = '20';
			$prefs['forum_'.$mode.'_order'] = '1';
		}

		$prefs['new_page_timelapse'] = '0';

		return $prefs;
	}
}
