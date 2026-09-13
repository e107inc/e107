<?php

/**
 * The download feed has the same hole as the forum feed.
 *
 * A download is readable when its own download_class allows it AND the category
 * it sits in allows it through download_category_class. request.php checks both
 * before it hands over a byte, e_search.php and download/e_list.php both filter
 * on both, and download_class.php joins the parent category into every listing
 * it builds. e_rss.php filters download_class alone, so a public file in a
 * members-only category is announced, described and categorised to anybody who
 * asks for the feed.
 *
 * There is a third column. download_visible is the one admin labels
 * "Visibility" and the one download.php's own listings filter on; the feed and
 * download/e_list.php filtered download_class instead, so an item hidden by
 * visibility alone was announced by both. request.php checks two of the three
 * and ignores download_visible entirely, which is why the route the feed has to
 * agree with is download.php's listing rather than the byte route.
 *
 * The fixture mirrors the forum one: a public download in a restricted category
 * is what the missing parent leg discloses, a restricted download in a public
 * category is the leg that already worked and must keep working, a download
 * hidden by download_visible alone is the third column, and a wholly public
 * download is the positive control that stops a feed which simply stopped
 * answering from passing as a fix.
 */
class DownloadFeedParentClassCest
{
	/** The class the restricted category and the restricted download are closed to. */
	const CLASS_STAFF = 202;

	/** RSS 2.0, the format rss.php builds for type 2. */
	const RSS_TYPE = 2;

	/** @var int */
	private $publicCategory;

	/** @var int */
	private $staffCategory;

	/** @var int */
	private $publicId;

	/** @var int */
	private $secretId;

	/** @var string a member who holds the class every restricted fixture is closed to */
	private $staffReader = 'downloadstaff';

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('download');
		$I->havePluginInstalled('rss_menu');

		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->publicCategory = $this->haveCategory($I, 'Fixture Public Category', 0);
		$this->staffCategory = $this->haveCategory($I, 'Fixture Staff Category', self::CLASS_STAFF);

		$this->publicId = $this->haveDownload($I, 'Fixture Public Download', $this->publicCategory, 0, 0);
		$this->haveDownload($I, 'Fixture Closed Download', $this->publicCategory, self::CLASS_STAFF, self::CLASS_STAFF);
		$this->secretId = $this->haveDownload($I, 'Fixture Secret Download', $this->staffCategory, 0, 0);
		$this->haveDownload($I, 'Fixture Hidden Download', $this->publicCategory, 0, self::CLASS_STAFF);

		$I->haveForumMember($this->staffReader, '253,'.self::CLASS_STAFF);

		$I->havePluginInstalled('list_new');
		$I->havePluginPrefs('list_new', $this->listPrefs());

		$I->haveInDatabase('e107_rss', array(
			'rss_name' => 'Fixture feed download',
			'rss_url' => 'download',
			'rss_topicid' => '',
			'rss_path' => 'download',
			'rss_text' => 'fixture',
			'rss_datestamp' => time(),
			'rss_class' => 0,
			'rss_limit' => 50,
		));

		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->havePluginPrefs('list_new', array());
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * The fixture has to hold. download.php's own listing is the route the feed
	 * has to agree with, and it refuses all three restricted files.
	 */
	public function anAnonymousReaderCannotSeeAnyRestrictedDownload(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/download/download.php?list.'.$this->staffCategory);
		$I->dontSee('Fixture Secret Download');

		$I->amOnPage('/e107_plugins/download/download.php?list.'.$this->publicCategory);
		$I->see('Fixture Public Download');
		$I->dontSee('Fixture Closed Download');
		$I->dontSee('Fixture Hidden Download');
	}

	/**
	 * The byte route, which is the model the category and item legs were copied
	 * from. It refuses a download in a category the caller cannot open and
	 * serves one that is public through both of the columns it reads.
	 *
	 * It does not read download_visible, so the hidden fixture is deliberately
	 * not asserted here: that column is enforced by the listings alone, and
	 * closing it in the byte route belongs with the download plugin's own
	 * package rather than with a feed predicate.
	 */
	public function theByteRouteRefusesADownloadInARestrictedCategory(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();

		$I->amOnPage('/e107_plugins/download/request.php?'.$this->secretId);
		$I->seeResponseCodeIsRedirection();

		$I->amOnPage('/e107_plugins/download/request.php?'.$this->publicId);
		$I->seeResponseCodeIsSuccessful();

		$I->startFollowingRedirects();
	}

	/**
	 * The defect. A file whose own class is e_UC_PUBLIC, sitting in a category
	 * nobody outside the class may open, is published in full.
	 */
	public function theDownloadFeedSkipsAnItemInARestrictedCategory(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/rss_menu/rss.php?download.'.self::RSS_TYPE.'.');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->seeInSource('Fixture Public Download');
		$I->dontSeeInSource('Fixture Closed Download');
		$I->dontSeeInSource('Fixture Secret Download');
		$I->dontSeeInSource('Fixture Hidden Download');
		$I->dontSeeInSource('Fixture Staff Category');
	}

	/**
	 * download/e_list.php is the feed's twin: the same three columns decide the
	 * same question, and it filtered the same two of them.
	 */
	public function theRecentListingSkipsTheSameItems(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/list_new/list.php');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->see('Fixture Public Download');
		$I->dontSee('Fixture Closed Download');
		$I->dontSee('Fixture Secret Download');
		$I->dontSee('Fixture Hidden Download');
	}

	/**
	 * The other direction. A member who holds the class is entitled to all four
	 * and must still be served them, or the predicate is merely blind rather
	 * than permission-aware.
	 */
	public function aReaderWhoHoldsTheStaffClassStillSeesEveryDownload(AcceptanceTester $I)
	{
		$I->loginToForum($this->staffReader);

		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/rss_menu/rss.php?download.'.self::RSS_TYPE.'.');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->seeInSource('Fixture Public Download');
		$I->seeInSource('Fixture Closed Download');
		$I->seeInSource('Fixture Secret Download');
		$I->seeInSource('Fixture Hidden Download');
	}

	// -----------------------------------------------------------------

	/**
	 * @param string $name
	 * @return string a value for the sef columns, which carry unique keys
	 */
	private function sef($name)
	{
		return strtolower(str_replace(' ', '-', $name));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $class 0 is e_UC_PUBLIC
	 * @return int category id
	 */
	private function haveCategory(AcceptanceTester $I, $name, $class)
	{
		return $I->haveInDatabase('e107_download_category', array(
			'download_category_name' => $name,
			'download_category_description' => 'fixture',
			'download_category_icon' => '',
			'download_category_parent' => 0,
			'download_category_class' => $class,
			'download_category_order' => 1,
			'download_category_sef' => $this->sef($name),
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name download_name carries a unique key
	 * @param int $category
	 * @param int $class download_class, 0 is e_UC_PUBLIC
	 * @param int $visible download_visible, the column admin labels "Visibility"
	 * @return int download id
	 */
	private function haveDownload(AcceptanceTester $I, $name, $category, $class, $visible)
	{
		return $I->haveInDatabase('e107_download', array(
			'download_name' => $name,
			'download_url' => 'fixture.txt',
			'download_sef' => $this->sef($name),
			'download_author' => 'fixture',
			'download_author_email' => 'fixture@example.com',
			'download_author_website' => '',
			'download_description' => 'Body of '.$name,
			'download_keywords' => '',
			'download_filesize' => 12,
			'download_requested' => 0,
			'download_category' => $category,
			'download_active' => 1,
			'download_datestamp' => time() - 3600,
			'download_thumb' => '',
			'download_image' => '',
			'download_comment' => 0,
			'download_class' => $class,
			'download_mirror' => '',
			'download_mirror_type' => 0,
			'download_visible' => $visible,
		));
	}

	/**
	 * Enough of list_new's configuration to render the download section, and no
	 * more. Mirrors 0042's, which explains why it is written out rather than
	 * left to the plugin's own defaults.
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

			$prefs['download_'.$mode.'_caption'] = 'Fixture download '.$mode;
			$prefs['download_'.$mode.'_display'] = '1';
			$prefs['download_'.$mode.'_open'] = '1';
			$prefs['download_'.$mode.'_author'] = '1';
			$prefs['download_'.$mode.'_category'] = '1';
			$prefs['download_'.$mode.'_date'] = '1';
			$prefs['download_'.$mode.'_icon'] = '';
			$prefs['download_'.$mode.'_amount'] = '20';
			$prefs['download_'.$mode.'_order'] = '1';
		}

		$prefs['new_page_timelapse'] = '0';

		return $prefs;
	}
}
