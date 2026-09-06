<?php

/**
 * The comments feed is served by the same script the forum and download feeds
 * are, and it applied no visibility predicate at all.
 *
 * rss.php builds content type 5 itself rather than through a plugin's
 * e_rss.php: it selects every row of #comments whose comment_blocked is 0 and
 * writes comment_subject and comment_comment straight into the channel. A
 * comment carries no class of its own, so the only thing that decides whether
 * it may be published is the item it was left on, and nothing asked. Comments
 * on members-only news and on downloads in restricted categories were
 * published verbatim to anybody who asked for rss.php?comments.2.
 *
 * The rss row itself is no protection: rss_class is a tri-state enable flag
 * rather than a userclass, and the fixture below sets it to 0 exactly as the
 * plugin's own config() does.
 */
class CommentFeedParentClassCest
{
	/** The class the restricted news item and download category are closed to. */
	const CLASS_STAFF = 202;

	/** RSS 2.0, the format rss.php builds for type 2. */
	const RSS_TYPE = 2;

	/** @var int */
	private $staffCategory;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('rss_menu');
		$I->havePluginInstalled('download');

		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$publicNews = $this->haveNews($I, 'Fixture Public News', 0);
		$staffNews = $this->haveNews($I, 'Fixture Staff News', self::CLASS_STAFF);

		$this->staffCategory = $I->haveInDatabase('e107_download_category', array(
			'download_category_name' => 'Fixture Staff Category',
			'download_category_description' => 'fixture',
			'download_category_icon' => '',
			'download_category_parent' => 0,
			'download_category_class' => self::CLASS_STAFF,
			'download_category_order' => 1,
			'download_category_sef' => 'fixture-staff-category',
		));

		$secretDownload = $I->haveInDatabase('e107_download', array(
			'download_name' => 'Fixture Secret Download',
			'download_url' => 'fixture.txt',
			'download_sef' => 'fixture-secret-download',
			'download_author' => 'fixture',
			'download_author_email' => 'fixture@example.com',
			'download_author_website' => '',
			'download_description' => 'fixture',
			'download_keywords' => '',
			'download_filesize' => 12,
			'download_requested' => 0,
			'download_category' => $this->staffCategory,
			'download_active' => 1,
			'download_datestamp' => time() - 3600,
			'download_thumb' => '',
			'download_image' => '',
			'download_comment' => 0,
			'download_class' => 0,
			'download_mirror' => '',
			'download_mirror_type' => 0,
			'download_visible' => 0,
		));

		$this->haveComment($I, 'Comment on public news', 0, $publicNews);
		$this->haveComment($I, 'Comment on staff news', 0, $staffNews);
		$this->haveComment($I, 'Comment on secret download', 2, $secretDownload);
		// A type with no route back to a parent, so no permission question can
		// be asked about it and it must not be published either.
		$this->haveComment($I, 'Comment on something unknown', 'fixture_unknown', 1);

		$I->haveInDatabase('e107_rss', array(
			'rss_name' => 'Fixture feed comments',
			'rss_url' => 'comments',
			'rss_topicid' => '',
			'rss_path' => '',
			'rss_text' => 'fixture',
			'rss_datestamp' => time(),
			'rss_class' => 0,
			'rss_limit' => 50,
		));

		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
		$I->dropPluginProbe();
	}

	/**
	 * The fixture has to hold, or the refusals below are satisfied by items
	 * that were never restricted.
	 */
	public function anAnonymousReaderCannotReachEitherRestrictedItem(AcceptanceTester $I)
	{
		$I->amOnPage('/news.php');
		$I->see('Fixture Public News');
		$I->dontSee('Fixture Staff News');

		$I->amOnPage('/e107_plugins/download/download.php?list.'.$this->staffCategory);
		$I->dontSee('Fixture Secret Download');
	}

	/**
	 * The defect. The public comment is the positive control, because a feed
	 * that had simply stopped answering would otherwise pass as a fix.
	 */
	public function theCommentFeedSkipsCommentsOnItemsTheReaderCannotOpen(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/rss_menu/rss.php?comments.'.self::RSS_TYPE.'.');
		$I->seeResponseCodeIs(200);
		$I->startFollowingRedirects();

		$I->seeInSource('</rss>');
		$I->seeInSource('Comment on public news');
		$I->dontSeeInSource('Comment on staff news');
		$I->dontSeeInSource('Comment on secret download');
		$I->dontSeeInSource('Comment on something unknown');
	}

	// -----------------------------------------------------------------

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param int $class 0 is e_UC_PUBLIC
	 * @return int news id
	 */
	private function haveNews(AcceptanceTester $I, $title, $class)
	{
		return $I->haveInDatabase('e107_news', array(
			'news_title' => $title,
			'news_sef' => strtolower(str_replace(' ', '-', $title)),
			'news_body' => 'Body of '.$title,
			'news_extended' => '',
			'news_meta_keywords' => '',
			'news_meta_description' => '',
			'news_datestamp' => time() - 3600,
			'news_author' => 1,
			'news_category' => 1,
			'news_allow_comments' => 0,
			'news_start' => 0,
			'news_end' => 0,
			'news_class' => $class,
			'news_render_type' => 0,
			'news_comment_total' => 0,
			'news_summary' => '',
			'news_thumbnail' => '',
			'news_sticky' => 0,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $subject the string the feed would leak
	 * @param string|int $type comment_type
	 * @param int $itemId comment_item_id
	 * @return int comment id
	 */
	private function haveComment(AcceptanceTester $I, $subject, $type, $itemId)
	{
		return $I->haveInDatabase('e107_comments', array(
			'comment_item_id' => $itemId,
			'comment_subject' => $subject,
			'comment_author_id' => 1,
			'comment_author_name' => 'fixture',
			'comment_datestamp' => time() - 3600,
			'comment_comment' => 'Body of '.$subject,
			'comment_blocked' => 0,
			'comment_ip' => '127.0.0.1',
			'comment_type' => $type,
			'comment_lock' => 0,
			'comment_share' => 0,
		));
	}
}
