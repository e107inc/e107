<?php

/**
 * Checks the comment routes outside the search against what the item a comment was left on shows the visitor on its own page.
 */
class CommentRoutesParentVisibilityCest
{
	/** The class the restricted downloads are limited to, and the one the signed-in reader holds. */
	const CLASS_STAFF = 202;

	const STAFF_NAME = 'cmtroutestaff';

	const STAFF_PASSWORD = 'Fixture-Staff-Pass-1';

	/** A member outside the staff class. */
	const READER_NAME = 'cmtroutereader';

	const READER_PASSWORD = 'Fixture-Reader-Pass-1';

	const PAGE_PASSWORD = 'fixture-secret';

	/** @var int[] item id by fixture name */
	private $items = array();

	/** @var int[] comment id by fixture name */
	private $comments = array();

	/** @var string[] item title by fixture name */
	private $titles = array();

	/** @var string[] core preferences a test changed, by name, with the value to put back */
	private $prefs = array();

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('download');
		$I->havePluginInstalled('poll');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->haveNewsComment($I, 'CMTPUBLIC', 'Fixture Public News', 0, 0, 0);
		$this->haveNewsComment($I, 'CMTFUTURE', 'Fixture Future News', 0, time() + 86400, 0);
		$this->haveNewsComment($I, 'CMTEXPIRED', 'Fixture Expired News', 0, 0, time() - 60);
		$this->haveNewsComment($I, 'CMTNOBODY', 'Fixture Nobody News', '0,255', 0, 0);
		$this->haveNewsComment($I, 'CMTSTAFF', 'Fixture Staff News', self::CLASS_STAFF, 0, 0);
		$this->haveNewsComment($I, 'CMTNEWSOFF', 'Fixture Comments Off News', 0, 0, 0, 1);
		$this->haveComment($I, 'CMTBLOCKED', 0, $this->items['CMTPUBLIC'], 1);

		$open = $this->haveDownloadCategory($I, 'Fixture Open Category', 0);
		$staff = $this->haveDownloadCategory($I, 'Fixture Staff Category', self::CLASS_STAFF);
		$this->haveDownloadComment($I, 'CMTDLPUBLIC', $open, 0, 1, 1);
		$this->haveDownloadComment($I, 'CMTDLVISIBLE', $open, self::CLASS_STAFF, 1, 1);
		$this->haveDownloadComment($I, 'CMTDLINACTIVE', $open, 0, 0, 1);
		$this->haveDownloadComment($I, 'CMTDLCATEGORY', $staff, 0, 1, 1);
		$this->haveDownloadComment($I, 'CMTDLNOCOMMENTS', $open, 0, 1, 0);

		$this->havePollComment($I, 'CMTPOLL', 'Fixture Site Poll', 1);
		$this->havePollComment($I, 'CMTFORUMPOLL', 'Fixture Forum Poll', 2);

		$this->havePageComment($I, 'CMTPAGEPUBLIC', '', 1);
		$this->havePageComment($I, 'CMTPAGEPASSWORD', self::PAGE_PASSWORD, 1);
		$this->havePageComment($I, 'CMTPAGENOCOMMENTS', '', 0);
		$this->havePageComment($I, 'CMTPAGESTAFF', '', 1, self::CLASS_STAFF);

		$this->haveComment($I, 'CMTPLUGIN', 'fixtureplugin', 1);

		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		foreach(array_reverse($this->prefs, true) as $name => $value)
		{
			$I->haveSitePref($name, $value === '' ? null : $value);
		}

		$I->haveSitePref('cachestatus', null);
		$I->dropPluginProbe();
	}

	/**
	 * comment.php?comment.<table>.<id> asks what the item's own page asks, and still serves every item that page serves.
	 */
	public function theCommentPageRefusesWhatTheItemPageRefuses(AcceptanceTester $I)
	{
		$I->amOnPage('/news.php');
		$I->see('Fixture Public News');
		$I->dontSee('Fixture Future News');
		$I->dontSee('Fixture Expired News');
		$I->dontSee('Fixture Nobody News');

		$I->amOnPage('/e107_plugins/poll/oldpolls.php?'.$this->items['CMTFORUMPOLL']);
		$I->dontSeeInSource('Fixture Forum Poll');

		foreach(array('CMTDLVISIBLE', 'CMTDLINACTIVE', 'CMTDLCATEGORY') as $marker)
		{
			$this->amOnDownloadView($I, $marker);
			$I->dontSeeInSource('Fixture Download '.$marker);
		}

		$this->seeDownloadViewComment($I, 'CMTDLPUBLIC');
		$this->amOnDownloadView($I, 'CMTDLNOCOMMENTS');
		$I->seeInSource('Fixture Download CMTDLNOCOMMENTS');
		$I->dontSeeInSource('Text of CMTDLNOCOMMENTS');

		$this->seeCommentPage($I, 'news', 'CMTPUBLIC', 'Fixture Public News');
		$this->seeCommentPage($I, 'download', 'CMTDLPUBLIC', null);
		$this->seeCommentPage($I, 'poll', 'CMTPOLL', 'Fixture Site Poll');

		foreach(array('CMTFUTURE' => 'Fixture Future News', 'CMTEXPIRED' => 'Fixture Expired News',
			'CMTNOBODY' => 'Fixture Nobody News') as $marker => $title)
		{
			$this->dontSeeCommentPage($I, 'news', $marker, $title);
		}

		foreach(array('CMTDLVISIBLE', 'CMTDLINACTIVE', 'CMTDLCATEGORY', 'CMTDLNOCOMMENTS') as $marker)
		{
			$this->dontSeeCommentPage($I, 'download', $marker, 'Fixture Download '.$marker);
		}

		$this->dontSeeCommentPage($I, 'poll', 'CMTFORUMPOLL', 'Fixture Forum Poll');
	}

	/**
	 * A member of the class a download is restricted to still reads its comments on comment.php, as on the download's own page.
	 */
	public function aStaffMemberStillReadsTheCommentsTheirClassMayRead(AcceptanceTester $I)
	{
		$I->haveMember(self::STAFF_NAME, self::STAFF_PASSWORD, array('user_class' => (string) self::CLASS_STAFF));
		$I->loginAsMember(self::STAFF_NAME, self::STAFF_PASSWORD);

		foreach(array('CMTDLVISIBLE', 'CMTDLCATEGORY') as $marker)
		{
			$this->seeDownloadViewComment($I, $marker);
			$this->seeCommentPage($I, 'download', $marker, null);
		}

		foreach(array('CMTDLINACTIVE', 'CMTDLNOCOMMENTS') as $marker)
		{
			$this->dontSeeCommentPage($I, 'download', $marker, 'Fixture Download '.$marker);
		}

		foreach(array('CMTSTAFF' => 'news', 'CMTPAGESTAFF' => 'page', 'CMTDLVISIBLE' => 'download', 'CMTDLCATEGORY' => 'download') as $marker => $table)
		{
			$this->amOnReplyPage($I, $table, $marker);
			$I->seeInSource($marker);
		}
	}

	/**
	 * With the content cache on, a news item's cached comment page is not served once the item has expired.
	 */
	public function aCachedCommentPageOutlivesNoExpiredItem(AcceptanceTester $I)
	{
		$I->haveSitePref('cachestatus', 1);
		$this->seeCommentPage($I, 'news', 'CMTPUBLIC', 'Fixture Public News');

		$I->updateInDatabase('e107_news', array('news_end' => time() - 60), array('news_id' => $this->items['CMTPUBLIC']));

		$this->dontSeeCommentPage($I, 'news', 'CMTPUBLIC', 'Fixture Public News');
	}

	/**
	 * The latest-comments menu skips a comment the page itself does not show: on a password-protected page until it is unlocked, and on a page whose comments are switched off.
	 */
	public function theLatestCommentsMenuSkipsCommentsThePageWithholds(AcceptanceTester $I)
	{
		$this->amOnCustomPage($I, 'CMTPAGEPUBLIC');
		$I->seeInSource('Text of CMTPAGEPUBLIC');
		$this->amOnCustomPage($I, 'CMTPAGEPASSWORD');
		$I->dontSeeInSource('Text of CMTPAGEPASSWORD');
		$this->amOnCustomPage($I, 'CMTPAGENOCOMMENTS');
		$I->seeInSource('Body of CMTPAGENOCOMMENTS');
		$I->dontSeeInSource('Text of CMTPAGENOCOMMENTS');

		$this->haveCommentMenu($I);

		$I->amOnPage('/news.php');
		$I->seeInSource('Text of CMTPAGEPUBLIC');
		$I->dontSeeInSource('Text of CMTPAGEPASSWORD');
		$I->dontSeeInSource('Text of CMTPAGENOCOMMENTS');
	}

	/**
	 * Neither the menu nor the feed publishes a comment left on a forum poll, which only its forum thread shows.
	 */
	public function forumPollCommentsReachNeitherTheMenuNorTheFeed(AcceptanceTester $I)
	{
		$this->haveCommentMenu($I);

		$I->amOnPage('/news.php');
		$I->seeInSource('Text of CMTPOLL');
		$I->dontSeeInSource('Text of CMTFORUMPOLL');
		$I->dontSeeInSource('Fixture Forum Poll');

		$I->haveInDatabase('e107_rss', array(
			'rss_name'      => 'Fixture feed comments',
			'rss_url'       => 'comments',
			'rss_topicid'   => '',
			'rss_path'      => '',
			'rss_text'      => 'fixture',
			'rss_datestamp' => time(),
			'rss_class'     => 0,
			'rss_limit'     => 50,
		));

		$I->amOnPage('/e107_plugins/rss_menu/rss.php?comments.2.');
		$I->seeInSource('</rss>');
		$I->seeInSource('Text of CMTPOLL');
		$I->dontSeeInSource('Text of CMTFORUMPOLL');
	}

	/**
	 * comment.php?reply.<table>.<comment id>.<item id> prints the item's title, or the comment's subject, only where the item's own page shows its comments.
	 */
	public function theReplyPageRefusesWhatTheItemPageRefuses(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();

		foreach($this->refusedToEveryone() as $marker => $table)
		{
			$this->amOnReplyPage($I, $table, $marker);
			$I->dontSeeInSource($marker);

			$this->amOnReplyPage($I, $table, $marker, false);
			$I->dontSeeInSource($this->titles[$marker]);
		}

		$this->amOnReplyPage($I, 'news', 'CMTBLOCKED');
		$I->dontSeeInSource('CMTBLOCKED');

		$I->amOnPage('/comment.php?reply.news.'.$this->comments['CMTSTAFF'].'.'.$this->items['CMTPUBLIC']);
		$I->dontSeeInSource('CMTSTAFF');

		$this->haveComment($I, 'CMTOTHERTYPE', 'page', $this->items['CMTPUBLIC']);
		$I->amOnPage('/comment.php?reply.news.'.$this->comments['CMTOTHERTYPE'].'.'.$this->items['CMTPUBLIC']);
		$I->dontSeeInSource('CMTOTHERTYPE');

		foreach($this->readByEveryone() as $marker => $table)
		{
			$this->amOnReplyPage($I, $table, $marker);
			$I->seeInSource($marker);
		}

		$this->amOnReplyPage($I, 'poll', 'CMTPOLL', false);
		$I->seeInSource('Fixture Site Poll');
	}

	/**
	 * The reply form comment.php serves over AJAX carries the comment's subject only where the item's own page shows the comment.
	 */
	public function theAjaxReplyFormRefusesWhatTheItemPageRefuses(AcceptanceTester $I)
	{
		$token = $this->loginAsReader($I);

		foreach(array_keys($this->refusedToEveryone()) as $marker)
		{
			$this->askAjaxReplyForm($I, $marker, $token);
			$I->dontSeeInSource($marker);
		}

		foreach(array('CMTBLOCKED', 'CMTPLUGIN') as $marker)
		{
			$this->askAjaxReplyForm($I, $marker, $token);
			$I->dontSeeInSource($marker);
		}

		foreach(array_keys($this->readByEveryone()) as $marker)
		{
			$this->askAjaxReplyForm($I, $marker, $token);
			$I->seeInSource($marker);
		}
	}

	/**
	 * A password-protected page's comments are replied to once the visitor has given the page its password, and not before.
	 */
	public function aLockedPageIsRepliedToOnceItsPasswordIsGiven(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();
		$this->amOnReplyPage($I, 'page', 'CMTPAGEPASSWORD');
		$I->dontSeeInSource('CMTPAGEPASSWORD');

		$this->unlockPage($I, 'CMTPAGEPASSWORD');

		$this->amOnReplyPage($I, 'page', 'CMTPAGEPASSWORD');
		$I->seeInSource('CMTPAGEPASSWORD');
	}

	/**
	 * A comment on a member's profile is replied to only by a visitor the profile page shows it to, and only while profile comments are on.
	 */
	public function profileCommentsAreRepliedToWhereTheProfileIsRead(AcceptanceTester $I)
	{
		$owner = $I->haveMember('cmtrouteowner', self::READER_PASSWORD);
		$this->haveComment($I, 'CMTPROFILE', 'profile', $owner);
		$this->haveComment($I, 'CMTNOPROFILE', 'profile', $owner + 1000);
		$this->haveSitePref($I, 'profile_comments', 1);

		$I->stopFollowingRedirects();
		$this->amOnReplyPage($I, 'profile', 'CMTPROFILE');
		$I->dontSeeInSource('CMTPROFILE');

		$token = $this->loginAsReader($I);
		$this->haveComment($I, 'CMTOWNPROFILE', 'profile', $this->items[self::READER_NAME]);

		$this->askAjaxReplyForm($I, 'CMTPROFILE', $token);
		$I->seeInSource('CMTPROFILE');
		$this->askAjaxReplyForm($I, 'CMTNOPROFILE', $token);
		$I->dontSeeInSource('CMTNOPROFILE');

		$this->haveSitePref($I, 'memberlist_access', 254);
		$this->askAjaxReplyForm($I, 'CMTPROFILE', $token);
		$I->dontSeeInSource('CMTPROFILE');
		$this->askAjaxReplyForm($I, 'CMTOWNPROFILE', $token);
		$I->seeInSource('CMTOWNPROFILE');

		$this->haveSitePref($I, 'profile_comments', 0);
		$this->askAjaxReplyForm($I, 'CMTOWNPROFILE', $token);
		$I->dontSeeInSource('CMTOWNPROFILE');
	}

	/**
	 * A download or a poll is not read while its plugin is not installed, as its own page then serves nothing.
	 */
	public function anItemIsNotReadWhileItsPluginIsNotInstalled(AcceptanceTester $I)
	{
		$token = $this->loginAsReader($I);

		foreach(array('download' => 'CMTDLPUBLIC', 'poll' => 'CMTPOLL') as $plugin => $marker)
		{
			$this->askAjaxReplyForm($I, $marker, $token);
			$I->seeInSource($marker);

			$this->haveSitePref($I, 'plug_installed/'.$plugin, null);

			$this->askAjaxReplyForm($I, $marker, $token);
			$I->dontSeeInSource($marker);
		}
	}

	/**
	 * Items each refused to a guest and to a signed-in member outside the staff class, by the condition named in the marker.
	 *
	 * @return string[] comment.php's table segment by marker
	 */
	private function refusedToEveryone()
	{
		return array(
			'CMTSTAFF'          => 'news',
			'CMTFUTURE'         => 'news',
			'CMTEXPIRED'        => 'news',
			'CMTNOBODY'         => 'news',
			'CMTNEWSOFF'        => 'news',
			'CMTDLVISIBLE'      => 'download',
			'CMTDLINACTIVE'     => 'download',
			'CMTDLCATEGORY'     => 'download',
			'CMTDLNOCOMMENTS'   => 'download',
			'CMTFORUMPOLL'      => 'poll',
			'CMTPAGESTAFF'      => 'page',
			'CMTPAGEPASSWORD'   => 'page',
			'CMTPAGENOCOMMENTS' => 'page',
		);
	}

	/**
	 * @return string[] comment.php's table segment by marker
	 */
	private function readByEveryone()
	{
		return array(
			'CMTPUBLIC'     => 'news',
			'CMTDLPUBLIC'   => 'download',
			'CMTPOLL'       => 'poll',
			'CMTPAGEPUBLIC' => 'page',
		);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $table comment.php's table segment
	 * @param string $marker
	 * @param bool $withComment false asks with no comment, so the page looks the item's title up instead
	 * @return void
	 */
	private function amOnReplyPage(AcceptanceTester $I, $table, $marker, $withComment = true)
	{
		$I->amOnPage('/comment.php?reply.'.$table.'.'.($withComment ? $this->comments[$marker] : 0).'.'.$this->items[$marker]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker the comment being replied to
	 * @param string $token
	 * @return void
	 */
	private function askAjaxReplyForm(AcceptanceTester $I, $marker, $token)
	{
		$I->sendAjaxPostRequest('/comment.php?ajax_used=1&mode=reply', array(
			'itemid'  => $this->comments[$marker],
			'e-token' => $token,
		));
	}

	/**
	 * Sign in as a member outside the staff class.
	 *
	 * @param AcceptanceTester $I
	 * @return string the session's e-token, which the AJAX routes are posted with
	 */
	private function loginAsReader(AcceptanceTester $I)
	{
		$this->items[self::READER_NAME] = $I->haveMember(self::READER_NAME, self::READER_PASSWORD);
		$I->loginAsMember(self::READER_NAME, self::READER_PASSWORD);

		return $I->grabForumToken('/comment.php?comment.news.'.$this->items['CMTPUBLIC']);
	}

	/**
	 * Give a password-protected page its password, as its own form does.
	 *
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @return void
	 */
	private function unlockPage(AcceptanceTester $I, $marker)
	{
		$page = '/page.php?id='.$this->items[$marker];

		$I->sendPostRequest($page, array(
			'page_pw'        => self::PAGE_PASSWORD,
			'submit_page_pw' => 'Submit',
			'e-token'        => $I->grabForumToken($page),
		));

		$this->amOnCustomPage($I, $marker);
		$I->seeInSource('Text of '.$marker);
	}

	/**
	 * Write a core preference for this test alone; _after() puts the first value back.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param string|int|null $value null removes it
	 * @return void
	 */
	private function haveSitePref(AcceptanceTester $I, $name, $value)
	{
		$was = $I->haveSitePref($name, $value);

		if(!array_key_exists($name, $this->prefs))
		{
			$this->prefs[$name] = $was;
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function haveCommentMenu(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_menus', array(
			'menu_name'     => 'comment_menu',
			'menu_location' => 1,
			'menu_order'    => 9,
			'menu_class'    => '0',
			'menu_pages'    => '',
			'menu_path'     => 'comment_menu/',
			'menu_layout'   => '',
			'menu_parms'    => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @param string $password page_password, empty when the page is not locked
	 * @param int $comments page_comment_flag
	 * @param int $class page_class
	 * @return void
	 */
	private function havePageComment(AcceptanceTester $I, $marker, $password, $comments, $class = 0)
	{
		$this->titles[$marker] = 'Fixture Page '.$marker;
		$this->items[$marker] = $I->haveInDatabase('e107_page', array(
			'page_title'        => $this->titles[$marker],
			'page_sef'          => strtolower($marker),
			'page_text'         => 'Body of '.$marker,
			'page_author'       => 1,
			'page_datestamp'    => time() - 3600,
			'page_comment_flag' => $comments,
			'page_password'     => $password,
			'page_class'        => $class,
			'page_template'     => '',
			'page_order'        => 1,
		));

		$this->haveComment($I, $marker, 'page');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @return void
	 */
	private function amOnCustomPage(AcceptanceTester $I, $marker)
	{
		$I->amOnPage('/page.php?id='.$this->items[$marker]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $table
	 * @param string $marker
	 * @param string|null $title what the page's title carries, when it carries one
	 * @return void
	 */
	private function seeCommentPage(AcceptanceTester $I, $table, $marker, $title)
	{
		$this->amOnCommentPage($I, $table, $marker);
		$I->seeInSource('Text of '.$marker);

		if($title !== null)
		{
			$I->seeInSource($title);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $table
	 * @param string $marker
	 * @param string $title
	 * @return void
	 */
	private function dontSeeCommentPage(AcceptanceTester $I, $table, $marker, $title)
	{
		$this->amOnCommentPage($I, $table, $marker);
		$I->dontSeeInSource($marker);
		$I->dontSeeInSource($title);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $table
	 * @param string $marker
	 * @return void
	 */
	private function amOnCommentPage(AcceptanceTester $I, $table, $marker)
	{
		$I->amOnPage('/comment.php?comment.'.$table.'.'.$this->items[$marker]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @return void
	 */
	private function amOnDownloadView(AcceptanceTester $I, $marker)
	{
		$I->amOnPage('/e107_plugins/download/download.php?view.'.$this->items[$marker]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @return void
	 */
	private function seeDownloadViewComment(AcceptanceTester $I, $marker)
	{
		$this->amOnDownloadView($I, $marker);
		$I->seeInSource('Text of '.$marker);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @param string $title
	 * @param int|string $class news_class
	 * @param int $start news_start
	 * @param int $end news_end
	 * @param int $allowComments news_allow_comments, where 0 is comments on
	 * @return void
	 */
	private function haveNewsComment(AcceptanceTester $I, $marker, $title, $class, $start, $end, $allowComments = 0)
	{
		$this->titles[$marker] = $title;
		$this->items[$marker] = $I->haveInDatabase('e107_news', array(
			'news_title'            => $title,
			'news_sef'              => strtolower($marker),
			'news_body'             => 'Body of '.$title,
			'news_extended'         => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_datestamp'        => time() - 3600,
			'news_author'           => 1,
			'news_category'         => 1,
			'news_allow_comments'   => $allowComments,
			'news_start'            => $start,
			'news_end'              => $end,
			'news_class'            => $class,
			'news_render_type'      => 0,
			'news_comment_total'    => 0,
			'news_summary'          => '',
			'news_thumbnail'        => '',
			'news_sticky'           => 0,
		));

		$this->haveComment($I, $marker, 0);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $class download_category_class
	 * @return int download_category_id
	 */
	private function haveDownloadCategory(AcceptanceTester $I, $name, $class)
	{
		return $I->haveInDatabase('e107_download_category', array(
			'download_category_name'        => $name,
			'download_category_description' => 'fixture',
			'download_category_icon'        => '',
			'download_category_parent'      => 0,
			'download_category_class'       => $class,
			'download_category_order'       => 1,
			'download_category_sef'         => strtolower(str_replace(' ', '-', $name)),
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @param int $category download_category_id
	 * @param int $visible download_visible
	 * @param int $active download_active
	 * @param int $comments download_comment
	 * @return void
	 */
	private function haveDownloadComment(AcceptanceTester $I, $marker, $category, $visible, $active, $comments)
	{
		$this->titles[$marker] = 'Fixture Download '.$marker;
		$this->items[$marker] = $I->haveInDatabase('e107_download', array(
			'download_name'           => $this->titles[$marker],
			'download_url'            => 'fixture.txt',
			'download_sef'            => strtolower($marker),
			'download_author'         => 'fixture',
			'download_author_email'   => 'fixture@example.com',
			'download_author_website' => '',
			'download_description'    => 'fixture',
			'download_keywords'       => '',
			'download_filesize'       => 12,
			'download_requested'      => 0,
			'download_category'       => $category,
			'download_active'         => $active,
			'download_datestamp'      => time() - 3600,
			'download_thumb'          => '',
			'download_image'          => '',
			'download_comment'        => $comments,
			'download_class'          => 0,
			'download_mirror'         => '',
			'download_mirror_type'    => 0,
			'download_visible'        => $visible,
		));

		$this->haveComment($I, $marker, 2);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker
	 * @param string $title
	 * @param int $type poll_type: 1 a site poll, 2 a forum poll
	 * @return void
	 */
	private function havePollComment(AcceptanceTester $I, $marker, $title, $type)
	{
		$this->titles[$marker] = $title;
		$this->items[$marker] = $I->haveInDatabase('e107_polls', array(
			'poll_datestamp'       => time() - 3600,
			'poll_start_datestamp' => time() - 3600,
			'poll_end_datestamp'   => 0,
			'poll_admin_id'        => 1,
			'poll_title'           => $title,
			'poll_options'         => '',
			'poll_votes'           => '',
			'poll_ip'              => '',
			'poll_type'            => $type,
			'poll_comment'         => 1,
			'poll_allow_multiple'  => 0,
			'poll_result_type'     => 0,
			'poll_vote_userclass'  => 0,
			'poll_storage_method'  => 0,
		));

		$this->haveComment($I, $marker, 4);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker the string a leak would carry, in the comment and its subject
	 * @param string|int $type comment_type
	 * @param int|null $itemId the item it is left on, when no fixture of the same name is that item
	 * @param int $blocked comment_blocked
	 * @return void
	 */
	private function haveComment(AcceptanceTester $I, $marker, $type, $itemId = null, $blocked = 0)
	{
		if($itemId !== null)
		{
			$this->items[$marker] = $itemId;
		}

		$this->comments[$marker] = $I->haveInDatabase('e107_comments', array(
			'comment_item_id'     => $this->items[$marker],
			'comment_subject'     => $marker,
			'comment_author_id'   => 1,
			'comment_author_name' => 'fixture',
			'comment_datestamp'   => time() - 3600,
			'comment_comment'     => 'Text of '.$marker,
			'comment_blocked'     => $blocked,
			'comment_ip'          => '127.0.0.1',
			'comment_type'        => $type,
			'comment_lock'        => 0,
			'comment_share'       => 0,
		));
	}
}
