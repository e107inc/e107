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

	/** @var int[] item id by fixture name */
	private $items = array();

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('download');
		$I->havePluginInstalled('poll');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->haveNewsComment($I, 'CMTPUBLIC', 'Fixture Public News', 0, 0, 0);
		$this->haveNewsComment($I, 'CMTFUTURE', 'Fixture Future News', 0, time() + 86400, 0);
		$this->haveNewsComment($I, 'CMTEXPIRED', 'Fixture Expired News', 0, 0, time() - 60);
		$this->haveNewsComment($I, 'CMTNOBODY', 'Fixture Nobody News', '0,255', 0, 0);

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
		$this->havePageComment($I, 'CMTPAGEPASSWORD', 'fixture-secret', 1);
		$this->havePageComment($I, 'CMTPAGENOCOMMENTS', '', 0);

		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
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
	 * @return void
	 */
	private function havePageComment(AcceptanceTester $I, $marker, $password, $comments)
	{
		$this->items[$marker] = $I->haveInDatabase('e107_page', array(
			'page_title'        => 'Fixture Page '.$marker,
			'page_sef'          => strtolower($marker),
			'page_text'         => 'Body of '.$marker,
			'page_author'       => 1,
			'page_datestamp'    => time() - 3600,
			'page_comment_flag' => $comments,
			'page_password'     => $password,
			'page_class'        => 0,
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
	 * @return void
	 */
	private function haveNewsComment(AcceptanceTester $I, $marker, $title, $class, $start, $end)
	{
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
			'news_allow_comments'   => 0,
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
		$this->items[$marker] = $I->haveInDatabase('e107_download', array(
			'download_name'           => 'Fixture Download '.$marker,
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
	 * @return void
	 */
	private function haveComment(AcceptanceTester $I, $marker, $type)
	{
		$I->haveInDatabase('e107_comments', array(
			'comment_item_id'     => $this->items[$marker],
			'comment_subject'     => $marker,
			'comment_author_id'   => 1,
			'comment_author_name' => 'fixture',
			'comment_datestamp'   => time() - 3600,
			'comment_comment'     => 'Text of '.$marker,
			'comment_blocked'     => 0,
			'comment_ip'          => '127.0.0.1',
			'comment_type'        => $type,
			'comment_lock'        => 0,
			'comment_share'       => 0,
		));
	}
}
