<?php

/**
 * The comments search publishes only what the visitor could read on the item the comment was left on.
 */
class CommentSearchParentVisibilityCest
{
	/** The class the restricted items are closed to, and the one the signed-in reader holds. */
	const CLASS_STAFF = 202;

	/** The one keyword every fixture comment carries, so a single query returns them all. */
	const KEYWORD = 'quilfeather';

	const PROBE_FILE = 'e107_tests_comment_search_probe.php';

	const STAFF_NAME = 'cmtsearchstaff';

	const STAFF_PASSWORD = 'Fixture-Staff-Pass-1';

	/** Comments an anonymous visitor can reach, one per registered handler and one per class rule that admits a guest. */
	private static $reachable = array('CMTPUBLIC', 'CMTDLPUBLIC', 'CMTDLCLASS', 'CMTPAGEPUBLIC', 'CMTPOLL',
		'CMTNOTSTAFF', 'CMTDLNOTSTAFF', 'CMTDLCATNOTSTAFF', 'CMTPAGENOTSTAFF', 'CMTDLNOBODY', 'CMTPAGENOBODY');

	/** What the search must not publish to an anonymous visitor, the restricted item's own title included. One per condition, and one whose handler states none. */
	private static $refused = array('CMTSTAFF', 'Fixture Staff News', 'CMTNOBODY', 'CMTFUTURE', 'CMTEXPIRED',
		'CMTBLOCKED', 'CMTPENDING', 'CMTDLCATEGORY', 'CMTDLVISIBLE', 'CMTDLINACTIVE',
		'CMTPAGECLASS', 'CMTPAGEPASSWORD', 'CMTFORUMPOLL', 'CMTPROFILE',
		'CMTNOTGUEST', 'CMTMIXED', 'CMTMEMBERSNOTSTAFF', 'CMTDLMIXED', 'CMTDLCATMIXED', 'CMTPAGEMIXED');

	/** Comments a member of the staff class can reach, one per rule that admits their class or a signed-in reader. */
	private static $staffReachable = array('CMTPUBLIC', 'CMTSTAFF', 'CMTNOTGUEST', 'CMTMIXED',
		'CMTDLCATEGORY', 'CMTDLVISIBLE', 'CMTPAGECLASS');

	/** What the search must not publish to that member: one per rule that excludes their class, and what nobody is shown. */
	private static $staffRefused = array('CMTNOTSTAFF', 'CMTMEMBERSNOTSTAFF', 'CMTDLNOTSTAFF', 'CMTDLCATNOTSTAFF',
		'CMTPAGENOTSTAFF', 'CMTNOBODY', 'CMTBLOCKED', 'CMTPENDING');

	/** @var int */
	private $publicNews;

	/** @var int */
	private $staffNews;

	/** @var int */
	private $openCategory;

	/** @var int */
	private $staffCategory;

	/** @var int */
	private $notStaffCategory;

	/** @var int */
	private $mixedCategory;

	/** @var int */
	private $forumPoll;

	/** @var int[] page id by the marker of the comment left on it */
	private $pages = array();

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->havePluginInstalled('download');
		$I->havePluginInstalled('poll');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->publicNews = $this->haveNews($I, 'Fixture Public News', 0, 0, 0);
		$this->staffNews = $this->haveNews($I, 'Fixture Staff News', self::CLASS_STAFF, 0, 0);

		$this->haveComment($I, 'CMTPUBLIC', 0, $this->publicNews, 0);
		$this->haveComment($I, 'CMTSTAFF', 0, $this->staffNews, 0);
		$this->haveComment($I, 'CMTNOBODY', 0, $this->haveNews($I, 'Fixture Nobody News', '0,255', 0, 0), 0);
		$this->haveComment($I, 'CMTFUTURE', 0, $this->haveNews($I, 'Fixture Future News', 0, time() + 86400, 0), 0);
		$this->haveComment($I, 'CMTEXPIRED', 0, $this->haveNews($I, 'Fixture Expired News', 0, 0, time() - 60), 0);
		$this->haveComment($I, 'CMTBLOCKED', 0, $this->publicNews, 1);
		$this->haveComment($I, 'CMTPENDING', 0, $this->publicNews, 2);
		$this->haveComment($I, 'CMTNOTSTAFF', 0,
			$this->haveNews($I, 'Fixture Not Staff News', '-'.self::CLASS_STAFF, 0, 0), 0);
		$this->haveComment($I, 'CMTNOTGUEST', 0, $this->haveNews($I, 'Fixture Not Guest News', '-252', 0, 0), 0);
		$this->haveComment($I, 'CMTMIXED', 0, $this->haveNews($I, 'Fixture Everyone But Guests News', '0,-252', 0, 0), 0);
		$this->haveComment($I, 'CMTMEMBERSNOTSTAFF', 0,
			$this->haveNews($I, 'Fixture Members But Staff News', '253,-'.self::CLASS_STAFF, 0, 0), 0);

		$this->openCategory = $this->haveDownloadCategory($I, 'Fixture Open Category', 0);
		$this->staffCategory = $this->haveDownloadCategory($I, 'Fixture Staff Category', self::CLASS_STAFF);
		$this->notStaffCategory = $this->haveDownloadCategory($I, 'Fixture Not Staff Category', '-'.self::CLASS_STAFF);
		$this->mixedCategory = $this->haveDownloadCategory($I, 'Fixture Everyone But Guests Category', '0,-252');

		$this->haveComment($I, 'CMTDLPUBLIC', 2,
			$this->haveDownload($I, 'Fixture Open Download', $this->openCategory, 0, 0, 1), 0);
		$this->haveComment($I, 'CMTDLCLASS', 2,
			$this->haveDownload($I, 'Fixture Fetch Restricted Download', $this->openCategory, 0, self::CLASS_STAFF, 1), 0);
		$this->haveComment($I, 'CMTDLCATEGORY', 2,
			$this->haveDownload($I, 'Fixture Hidden Category Download', $this->staffCategory, 0, 0, 1), 0);
		$this->haveComment($I, 'CMTDLVISIBLE', 2,
			$this->haveDownload($I, 'Fixture Unlisted Download', $this->openCategory, self::CLASS_STAFF, 0, 1), 0);
		$this->haveComment($I, 'CMTDLINACTIVE', 2,
			$this->haveDownload($I, 'Fixture Inactive Download', $this->openCategory, 0, 0, 0), 0);
		$this->haveComment($I, 'CMTDLNOTSTAFF', 2,
			$this->haveDownload($I, 'Fixture Not Staff Download', $this->openCategory, '-'.self::CLASS_STAFF, 0, 1), 0);
		$this->haveComment($I, 'CMTDLMIXED', 2,
			$this->haveDownload($I, 'Fixture Everyone But Guests Download', $this->openCategory, '0,-252', 0, 1), 0);
		$this->haveComment($I, 'CMTDLNOBODY', 2,
			$this->haveDownload($I, 'Fixture Nobody Listed Download', $this->openCategory, '0,255', 0, 1), 0);
		$this->haveComment($I, 'CMTDLCATNOTSTAFF', 2,
			$this->haveDownload($I, 'Fixture Not Staff Category Download', $this->notStaffCategory, 0, 0, 1), 0);
		$this->haveComment($I, 'CMTDLCATMIXED', 2,
			$this->haveDownload($I, 'Fixture Everyone But Guests Category Download', $this->mixedCategory, 0, 0, 1), 0);

		$this->havePageComment($I, 'CMTPAGEPUBLIC', 0, '');
		$this->havePageComment($I, 'CMTPAGECLASS', self::CLASS_STAFF, '');
		$this->havePageComment($I, 'CMTPAGEPASSWORD', 0, 'fixture-secret');
		$this->havePageComment($I, 'CMTPAGENOTSTAFF', '-'.self::CLASS_STAFF, '');
		$this->havePageComment($I, 'CMTPAGEMIXED', '0,-252', '');
		$this->havePageComment($I, 'CMTPAGENOBODY', '0,255', '');

		$this->haveComment($I, 'CMTPOLL', 4, $this->havePoll($I, 'Fixture Poll', 1), 0);
		$this->forumPoll = $this->havePoll($I, 'Fixture Forum Poll', 2);
		$this->haveComment($I, 'CMTFORUMPOLL', 4, $this->forumPoll, 0);

		$this->haveComment($I, 'CMTPROFILE', 'profile', 1, 0);

		$I->probe('act=seed');

		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->probe('act=restore');
		$I->dropPluginProbe();
	}

	/**
	 * Every restricted item is refused to this visitor by its own pages, and every item a rule admits them to is not.
	 */
	public function anAnonymousReaderCannotReachAnyOfTheRestrictedContent(AcceptanceTester $I)
	{
		$I->resetAllCookies();

		$I->amOnPage('/news.php');
		$I->see('Fixture Public News');
		$I->see('Fixture Not Staff News');
		$I->dontSee('Fixture Staff News');
		$I->dontSee('Fixture Nobody News');
		$I->dontSee('Fixture Future News');
		$I->dontSee('Fixture Expired News');
		$I->dontSee('Fixture Not Guest News');
		$I->dontSee('Fixture Everyone But Guests News');
		$I->dontSee('Fixture Members But Staff News');

		$I->amOnPage('/comment.php?comment.news.'.$this->staffNews);
		$I->dontSeeInSource('CMTSTAFF');

		$I->amOnPage('/comment.php?comment.news.'.$this->publicNews);
		$I->seeInSource('CMTPUBLIC');
		$I->dontSeeInSource('CMTBLOCKED');
		$I->dontSeeInSource('CMTPENDING');

		$I->amOnPage('/e107_plugins/download/download.php?list.'.$this->openCategory);
		$I->see('Fixture Open Download');
		$I->see('Fixture Fetch Restricted Download');
		$I->dontSee('Fixture Unlisted Download');
		$I->dontSee('Fixture Inactive Download');
		$I->see('Fixture Not Staff Download');
		$I->see('Fixture Nobody Listed Download');
		$I->dontSee('Fixture Everyone But Guests Download');

		$I->amOnPage('/e107_plugins/download/download.php?list.'.$this->staffCategory);
		$I->dontSee('Fixture Hidden Category Download');

		$I->amOnPage('/e107_plugins/download/download.php?list.'.$this->notStaffCategory);
		$I->see('Fixture Not Staff Category Download');

		$I->amOnPage('/e107_plugins/download/download.php?list.'.$this->mixedCategory);
		$I->dontSee('Fixture Everyone But Guests Category Download');

		foreach(array('CMTPAGEPUBLIC', 'CMTPAGENOTSTAFF', 'CMTPAGENOBODY') as $marker)
		{
			$I->amOnPage('/page.php?id='.$this->pages[$marker]);
			$I->seeInSource('Body of '.$marker);
		}

		foreach(array('CMTPAGECLASS', 'CMTPAGEMIXED') as $marker)
		{
			$I->amOnPage('/page.php?id='.$this->pages[$marker]);
			$I->dontSeeInSource('Body of '.$marker);
		}

		$I->amOnPage('/e107_plugins/poll/oldpolls.php?'.$this->forumPoll);
		$I->dontSeeInSource('CMTFORUMPOLL');

		$I->amOnPage('/comment.php?comment.poll.'.$this->forumPoll);
		$I->dontSeeInSource('CMTFORUMPOLL');
	}

	/**
	 * A search of every area refuses each restricted comment and still finds every readable one.
	 */
	public function theCommentsSearchSkipsCommentsTheReaderCouldNotHaveRead(AcceptanceTester $I)
	{
		$I->resetAllCookies();

		$this->searchAndAssert($I, 'all', self::$reachable, self::$refused);
	}

	/**
	 * The same, with the Comments area asked for by name.
	 */
	public function theCommentsAreaAloneSkipsThemToo(AcceptanceTester $I)
	{
		$I->resetAllCookies();

		$this->searchAndAssert($I, 'comments', self::$reachable, self::$refused);
	}

	/**
	 * A member of the staff class finds what the items' own pages show them, a rule that excludes their class included.
	 */
	public function aStaffMemberFindsWhatTheirClassesMayReadAndNothingElse(AcceptanceTester $I)
	{
		$I->haveMember(self::STAFF_NAME, self::STAFF_PASSWORD, array('user_class' => (string) self::CLASS_STAFF));
		$I->loginAsMember(self::STAFF_NAME, self::STAFF_PASSWORD);

		$I->amOnPage('/news.php');
		$I->see('Fixture Staff News');
		$I->see('Fixture Not Guest News');
		$I->see('Fixture Everyone But Guests News');
		$I->dontSee('Fixture Not Staff News');
		$I->dontSee('Fixture Members But Staff News');

		$this->searchAndAssert($I, 'comments', self::$staffReachable, self::$staffRefused);
	}

	// -----------------------------------------------------------------

	/**
	 * @param AcceptanceTester $I
	 * @param string $type what $_GET['t'] holds
	 * @param string[] $reachable markers the results must carry
	 * @param string[] $refused markers the results must not carry
	 * @return void
	 */
	private function searchAndAssert(AcceptanceTester $I, $type, array $reachable, array $refused)
	{
		$I->amOnPage('/search.php?q='.self::KEYWORD.'&t='.$type.'&r=0');
		$I->seeResponseCodeIs(200);

		$source = $I->grabPageSource();
		$dropped = array();
		$published = array();

		foreach($reachable as $marker)
		{
			if(strpos($source, $marker) === false)
			{
				$dropped[] = $marker;
			}
		}

		foreach($refused as $marker)
		{
			if(strpos($source, $marker) !== false)
			{
				$published[] = $marker;
			}
		}

		$I->assertSame(array('published' => array(), 'dropped' => array()), array('published' => $published, 'dropped' => $dropped),
			'The comments search published content this visitor cannot reach, or dropped comments they may read');
	}

	// -----------------------------------------------------------------

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param int|string $class 0 is e_UC_PUBLIC
	 * @param int $start news_start, 0 for published
	 * @param int $end news_end, 0 for never
	 * @return int news id
	 */
	private function haveNews(AcceptanceTester $I, $title, $class, $start, $end)
	{
		return $I->haveInDatabase('e107_news', array(
			'news_title'          => $title,
			'news_sef'            => strtolower(str_replace(' ', '-', $title)),
			'news_body'           => 'Body of '.$title,
			'news_extended'       => '',
			'news_meta_keywords'  => '',
			'news_meta_description' => '',
			'news_datestamp'      => time() - 3600,
			'news_author'         => 1,
			'news_category'       => 1,
			'news_allow_comments' => 0,
			'news_start'          => $start,
			'news_end'            => $end,
			'news_class'          => $class,
			'news_render_type'    => 0,
			'news_comment_total'  => 0,
			'news_summary'        => '',
			'news_thumbnail'      => '',
			'news_sticky'         => 0,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param int $type poll_type
	 * @return int poll id
	 */
	private function havePoll(AcceptanceTester $I, $title, $type)
	{
		return $I->haveInDatabase('e107_polls', array(
			'poll_datestamp' => time() - 3600,
			'poll_start_datestamp' => time() - 3600,
			'poll_end_datestamp' => 0,
			'poll_admin_id' => 1,
			'poll_title' => $title,
			'poll_options' => '',
			'poll_votes' => '',
			'poll_ip' => '',
			'poll_type' => $type,
			'poll_comment' => $type === 1 ? 1 : 0,
			'poll_allow_multiple' => 0,
			'poll_result_type' => 0,
			'poll_vote_userclass' => 0,
			'poll_storage_method' => 0,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int|string $class a class rule; 0 is e_UC_PUBLIC
	 * @return int download_category_id
	 */
	private function haveDownloadCategory(AcceptanceTester $I, $name, $class)
	{
		return $I->haveInDatabase('e107_download_category', array(
			'download_category_name' => $name,
			'download_category_description' => 'fixture',
			'download_category_icon' => '',
			'download_category_parent' => 0,
			'download_category_class' => $class,
			'download_category_order' => 1,
			'download_category_sef' => strtolower(str_replace(' ', '-', $name)),
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $category download_category_id
	 * @param int|string $visible download_visible, the class rule for who may see it listed
	 * @param int $class download_class, who may fetch it
	 * @param int $active download_active
	 * @return int download_id
	 */
	private function haveDownload(AcceptanceTester $I, $name, $category, $visible, $class, $active)
	{
		return $I->haveInDatabase('e107_download', array(
			'download_name' => $name,
			'download_url' => 'fixture.txt',
			'download_sef' => strtolower(str_replace(' ', '-', $name)),
			'download_author' => 'fixture',
			'download_author_email' => 'fixture@example.com',
			'download_author_website' => '',
			'download_description' => 'fixture',
			'download_keywords' => '',
			'download_filesize' => 12,
			'download_requested' => 0,
			'download_category' => $category,
			'download_active' => $active,
			'download_datestamp' => time() - 3600,
			'download_thumb' => '',
			'download_image' => '',
			'download_comment' => 1,
			'download_class' => $class,
			'download_mirror' => '',
			'download_mirror_type' => 0,
			'download_visible' => $visible,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker the string a leak would carry
	 * @param int|string $class a class rule; 0 is e_UC_PUBLIC
	 * @param string $password page_password, empty when the page is not locked
	 * @return int page id
	 */
	private function havePageComment(AcceptanceTester $I, $marker, $class, $password)
	{
		$page = $I->haveInDatabase('e107_page', array(
			'page_title' => 'Fixture Page '.$marker,
			'page_sef' => strtolower($marker),
			'page_text' => 'Body of '.$marker,
			'page_author' => 1,
			'page_datestamp' => time() - 3600,
			'page_comment_flag' => 1,
			'page_password' => $password,
			'page_class' => $class,
			'page_template' => '',
			'page_order' => 1,
		));

		$this->haveComment($I, $marker, 'page', $page, 0);
		$this->pages[$marker] = $page;

		return $page;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker the string a leak would carry
	 * @param string|int $type comment_type
	 * @param int $itemId comment_item_id
	 * @param int $blocked 0 visible, 1 blocked, 2 awaiting approval
	 * @return int comment id
	 */
	private function haveComment(AcceptanceTester $I, $marker, $type, $itemId, $blocked)
	{
		return $I->haveInDatabase('e107_comments', array(
			'comment_item_id'    => $itemId,
			'comment_subject'    => $marker,
			'comment_author_id'  => 1,
			'comment_author_name' => 'fixture',
			'comment_datestamp'  => time() - 3600,
			'comment_comment'    => self::KEYWORD.' '.$marker,
			'comment_blocked'    => $blocked,
			'comment_ip'         => '127.0.0.1',
			'comment_type'       => $type,
			'comment_lock'       => 0,
			'comment_share'      => 0,
		));
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for CommentSearchParentVisibilityCest.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : 'read';
$search = e107::getConfig('search');

switch($act)
{
	case 'seed':
		$handlers = $search->get('comments_handlers');
		$core = $search->get('core_handlers');
		if(!is_array($search->get('e107_tests_comment_search_stash')))
		{
			$search->set('e107_tests_comment_search_stash', array('comments_handlers' => $handlers, 'core_handlers' => $core));
		}
		$handlers['page'] = array('id' => 'page', 'dir' => 'core', 'class' => '0');
		$handlers['poll'] = array('id' => 4, 'dir' => 'poll', 'class' => '0');
		$handlers['user'] = array('id' => 'profile', 'dir' => 'core', 'class' => '0');
		$search->set('comments_handlers', $handlers);
		$core['comments']['results'] = '50';
		$search->set('core_handlers', $core);
		$search->save(false, true, false);
		echo "PROBE_OK seed\n";
		break;

	case 'restore':
		$stash = $search->get('e107_tests_comment_search_stash');
		if(is_array($stash))
		{
			$search->set('comments_handlers', $stash['comments_handlers']);
			$search->set('core_handlers', $stash['core_handlers']);
		}
		$search->removePref('e107_tests_comment_search_stash')->save(false, true, false);
		echo "PROBE_OK restore\n";
		break;

	default:
		echo "PROBE_OK ".json_encode($search->getPref())."\n";
}
PHP;
	}
}
