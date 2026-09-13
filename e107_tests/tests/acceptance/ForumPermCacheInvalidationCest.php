<?php

/**
 * Restricting a forum has to take effect on the next request.
 *
 * The feeds and listings used to read f.forum_class live from the row, so a
 * class change was honoured immediately. They now read the id list
 * _getForumPermList() builds, which is cached under forum_perms with no maximum
 * age, so it survives until something clears it. forum_admin's afterUpdate()
 * called clear('forum_perms', true), and the second argument of
 * ecache::clear() is $syscache rather than $related: it deleted
 * S_forum_perms*.cache.php, which is never written, and left the C_ entry that
 * loadPermList() actually reads standing. On a site with content caching on,
 * the fix was therefore inert from the moment an admin restricted a forum.
 *
 * Nothing else in this package can see that, because every other Cest purges
 * the cache in _before(). This one deliberately does not purge between the
 * warm-up fetch and the assertion, and it changes the class through the admin
 * UI's own inline-edit route rather than by writing the row, so the hook under
 * test is the one that actually runs on a real site.
 */
class ForumPermCacheInvalidationCest
{
	/** The class the forum is restricted to, which no guest holds. */
	const CLASS_STAFF = 202;

	/** RSS 2.0, the format rss.php builds for type 2. */
	const RSS_TYPE = 2;

	const LIST_PATH = '/e107_plugins/forum/forum_admin.php?mode=main&action=list';

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->havePluginInstalled('rss_menu');

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->ids = $I->haveForumStructure();

		$I->haveInDatabase('e107_rss', array(
			'rss_name' => 'Fixture feed forumthreads',
			'rss_url' => 'forumthreads',
			'rss_topicid' => '',
			'rss_path' => 'forum',
			'rss_text' => 'fixture',
			'rss_datestamp' => time(),
			'rss_class' => 0,
			'rss_limit' => 50,
		));

		// The defect only bites when the cache is live, which is the state a
		// site with content caching switched on is in.
		$I->haveSitePref('cachestatus', 1);

		$I->purgeForumPermCache();
		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('cachestatus', null);
		$I->purgeForumPermCache();
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * Warm the guest's entry, restrict the forum through the admin area, and
	 * ask again as the same guest.
	 */
	public function restrictingAForumTakesEffectOnTheNextFeedRequest(AcceptanceTester $I)
	{
		$this->fetchFeed($I);
		$I->seeInSource('Opening post in A');

		$this->restrictForumThroughAdmin($I, $this->ids['forumA']);

		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->ids['forumA'],
			'forum_class' => self::CLASS_STAFF,
		));

		$this->fetchFeed($I);
		$I->dontSeeInSource('Opening post in A');
		$I->dontSeeInSource('Fixture Thread A');

		// The control: the sibling forum was not touched and must still be served,
		// so this is not a feed that stopped answering.
		$I->seeInSource('Opening post in B');
	}

	// -----------------------------------------------------------------

	/**
	 * Set forum_class through the admin list page's inline editor, which is the
	 * route that reaches _manageSubmit() and so afterUpdate().
	 *
	 * @param AcceptanceTester $I
	 * @param int $forumId
	 */
	private function restrictForumThroughAdmin(AcceptanceTester $I, $forumId)
	{
		$I->loginAsAdmin();

		$I->amOnPage(self::LIST_PATH);
		$source = $I->grabPageSource();

		$matches = array();
		if (!preg_match('/data-token=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('No inline-edit data-token on '.self::LIST_PATH);
		}

		$I->sendPostRequest('/e107_plugins/forum/forum_admin.php?mode=main&action=inline&id='.$forumId.'&ajax_used=1', array(
			'name'    => 'forum_class',
			'value'   => self::CLASS_STAFF,
			'pk'      => $forumId,
			'token'   => $matches[1],
			'e-token' => $I->grabFreshAdminToken(self::LIST_PATH),
		));
	}

	/**
	 * @param AcceptanceTester $I
	 */
	private function fetchFeed(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/rss_menu/rss.php?forumthreads.'.self::RSS_TYPE.'.');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();
	}
}
