<?php

/**
 * P6 item 5. The comments feed at e107_plugins/rss_menu/rss.php:288-293 reads
 *
 *   select('*')->from('comments')->where('comment_blocked', 0)
 *
 * and nothing else. A comment belongs to something: a news item, a download, a
 * poll. Whether that parent may be seen, and whether it has been published yet,
 * are properties of the parent, and the feed never joins to it. Comments on an
 * unpublished or class-restricted news item are therefore served to an
 * anonymous reader, subject and body.
 *
 * The parent's own page applies both predicates, which is what makes this a
 * leak rather than a design choice: the same text is refused at news.php and
 * handed over at rss.php.
 */
class RssCommentsFeedCest
{
	const RESET_FILE = 'e107_tests_p6_rss_reset.php';

	/** @var string */
	private $suffix;

	public function _before(AcceptanceTester $I)
	{
		$this->suffix = uniqid('', false);

		$I->writeAppFile(self::RESET_FILE, $this->resetSource());
		$I->amOnPage('/'.self::RESET_FILE);
		$I->seeInSource('RESET_DONE');

		// The comments feed is one the admin importer offers
		// (rss_menu/admin_prefs.php:207-214); only the news feed is installed by
		// default, so add the row the importer would have written.
		$I->haveInDatabase('e107_rss', array(
			'rss_name'      => 'Comments',
			'rss_url'       => 'comments',
			'rss_topicid'   => '',
			'rss_path'      => 'comments',
			'rss_text'      => 'The rss feed of the comments',
			'rss_datestamp' => time(),
			'rss_class'     => 0,
			'rss_limit'     => 9,
		));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginInstall('download');
		$I->dropPluginProbe();
		$I->deleteAppFile(self::RESET_FILE);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param string $class
	 * @param int $start
	 * @return int news id
	 */
	private function seedNews(AcceptanceTester $I, $title, $class, $start)
	{
		return $I->haveInDatabase('e107_news', array(
			'news_title'            => $title,
			'news_sef'              => '',
			'news_body'             => 'body of '.$title,
			'news_extended'         => '',
			'news_meta_title'       => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_datestamp'        => time() - 3600,
			'news_author'           => 1,
			'news_category'         => 0,
			'news_start'            => $start,
			'news_end'              => 0,
			'news_class'            => $class,
			'news_render_type'      => '0',
			'news_summary'          => '',
			'news_thumbnail'        => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param string $class page_class
	 * @return int page id
	 */
	private function seedPage(AcceptanceTester $I, $title, $class)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title'     => $title,
			'page_sef'       => '',
			'page_chapter'   => 0,
			'page_text'      => 'body of '.$title,
			'page_author'    => 1,
			'page_datestamp' => time() - 3600,
			'page_password'  => '',
			'page_class'     => $class,
			'page_template'  => '',
			'menu_name'      => '',
			'menu_title'     => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param int $visible download_visible
	 * @param int $class download_class
	 * @return int download id
	 */
	private function seedDownload(AcceptanceTester $I, $name, $visible, $class)
	{
		$categoryId = $I->haveInDatabase('e107_download_category', array(
			'download_category_name'        => 'P6 rss '.$name,
			'download_category_description' => '',
			'download_category_icon'        => '',
			'download_category_parent'      => 0,
			'download_category_class'       => 0,
			'download_category_order'       => 0,
			'download_category_sef'         => 'p6-rss-'.$this->suffix,
		));

		return $I->haveInDatabase('e107_download', array(
			'download_name'        => $name,
			'download_url'         => '',
			'download_sef'         => '',
			'download_author'      => '',
			'download_description' => '',
			'download_keywords'    => '',
			'download_filesize'    => 0,
			'download_requested'   => 0,
			'download_category'    => $categoryId,
			'download_active'      => 1,
			'download_datestamp'   => time(),
			'download_thumb'       => '',
			'download_image'       => '',
			'download_comment'     => 0,
			'download_class'       => $class,
			'download_mirror'      => '',
			'download_mirror_type' => 0,
			'download_visible'     => $visible,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $newsId
	 * @param string $marker
	 * @param string $type comment_type, as comment::getCommentType() stores it
	 * @return void
	 */
	private function seedComment(AcceptanceTester $I, $newsId, $marker, $type = '0')
	{
		$I->haveInDatabase('e107_comments', array(
			'comment_pid'          => 0,
			'comment_item_id'      => $newsId,
			'comment_subject'      => 'SUBJ'.$marker,
			'comment_author_id'    => 1,
			'comment_author_name'  => '1.admin',
			'comment_author_email' => '',
			'comment_datestamp'    => time(),
			'comment_comment'      => 'BODY'.$marker,
			'comment_blocked'      => 0,
			'comment_ip'           => '',
			'comment_type'         => $type,
			'comment_lock'         => 0,
			'comment_share'        => 0,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function fetchFeed(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/rss_menu/rss.php?comments.2');
		$I->seeResponseCodeIs(200);
	}

	public function theFeedDoesNotCarryCommentsOnARestrictedItem(AcceptanceTester $I)
	{
		$I->wantTo('keep comments on a class-restricted news item out of the anonymous feed');

		$marker = 'RESTRICTED'.$this->suffix;
		$newsId = $this->seedNews($I, 'P6 restricted news '.$this->suffix, '254', 0);
		$this->seedComment($I, $newsId, $marker);

		$this->fetchFeed($I);

		$I->dontSeeInSource('SUBJ'.$marker);
		$I->dontSeeInSource('BODY'.$marker);
	}

	public function theFeedDoesNotCarryCommentsOnAnUnpublishedItem(AcceptanceTester $I)
	{
		$I->wantTo('keep comments on a news item that has not gone live out of the anonymous feed');

		$marker = 'FUTURE'.$this->suffix;
		$newsId = $this->seedNews($I, 'P6 future news '.$this->suffix, '0', time() + 86400);
		$this->seedComment($I, $newsId, $marker);

		$this->fetchFeed($I);

		$I->dontSeeInSource('SUBJ'.$marker);
		$I->dontSeeInSource('BODY'.$marker);
	}

	/**
	 * Positive control. A feed that dropped every comment would satisfy both
	 * tests above.
	 */
	public function theFeedStillCarriesCommentsOnAPublicItem(AcceptanceTester $I)
	{
		$I->wantTo('keep serving comments on a published, public news item');

		$marker = 'PUBLIC'.$this->suffix;
		$newsId = $this->seedNews($I, 'P6 public news '.$this->suffix, '0', 0);
		$this->seedComment($I, $newsId, $marker);

		$this->fetchFeed($I);

		$I->seeInSource('<channel>');
		$I->seeInSource('SUBJ'.$marker);
		$I->seeInSource('BODY'.$marker);
	}

	/**
	 * download_visible is the column that decides who sees a download listed,
	 * and a feed is a listing. download_class, which the feed also honours, is
	 * the permission to fetch the file once it has been found.
	 */
	public function theFeedDoesNotCarryCommentsOnADownloadNobodyMaySee(AcceptanceTester $I)
	{
		$I->wantTo('keep comments on a members-only download out of the anonymous feed');

		$I->havePluginInstalled('download');

		$marker = 'DLHIDDEN'.$this->suffix;
		$id = $this->seedDownload($I, 'P6 hidden download '.$this->suffix, 254, 0);
		$this->seedComment($I, $id, $marker, '2');

		$this->fetchFeed($I);

		$I->dontSeeInSource('SUBJ'.$marker);
		$I->dontSeeInSource('BODY'.$marker);
	}

	/**
	 * Positive control for the branch above: without it, the download branch
	 * could be serving nothing at all and the test would still pass.
	 */
	public function theFeedStillCarriesCommentsOnAVisibleDownload(AcceptanceTester $I)
	{
		$I->wantTo('keep serving comments on a download anyone may see');

		$I->havePluginInstalled('download');

		$marker = 'DLPUBLIC'.$this->suffix;
		$id = $this->seedDownload($I, 'P6 public download '.$this->suffix, 0, 0);
		$this->seedComment($I, $id, $marker, '2');

		$this->fetchFeed($I);

		$I->seeInSource('SUBJ'.$marker);
	}

	/**
	 * Custom pages are commentable in core, so their comments belong in the feed
	 * on the same terms as everything else: whoever may read the page.
	 */
	public function theFeedCarriesCommentsOnAPublicPageButNotARestrictedOne(AcceptanceTester $I)
	{
		$I->wantTo('serve page comments on the same terms as the page itself');

		$open = 'PAGEOPEN'.$this->suffix;
		$shut = 'PAGESHUT'.$this->suffix;

		$this->seedComment($I, $this->seedPage($I, 'P6 open page '.$this->suffix, '0'), $open, 'page');
		$this->seedComment($I, $this->seedPage($I, 'P6 shut page '.$this->suffix, '254'), $shut, 'page');

		$this->fetchFeed($I);

		$I->seeInSource('SUBJ'.$open);
		$I->dontSeeInSource('SUBJ'.$shut);
	}

	/**
	 * A type the feed has no visibility rule for is not served at all. Stated as
	 * a test so that narrowing the list further, or widening it without a rule,
	 * is a decision somebody has to make on purpose.
	 */
	public function theFeedCarriesNoCommentOfATypeItHasNoRuleFor(AcceptanceTester $I)
	{
		$I->wantTo('keep comment types the feed cannot check out of the feed');

		$marker = 'PROFILE'.$this->suffix;
		$this->seedComment($I, 1, $marker, 'profile');

		$this->fetchFeed($I);

		$I->dontSeeInSource('SUBJ'.$marker);
	}

	/**
	 * The shared reader behind the bundled 'latest comments' menu, list_new and
	 * userposts. It applies the parent's userclass already; what it did not ask
	 * was whether the parent has been published.
	 */
	public function theSharedCommentReaderDropsCommentsOnAnUnpublishedItem(AcceptanceTester $I)
	{
		$I->wantTo('keep comments on a news item that has not gone live out of the latest-comments menu');

		$marker = 'READERSHUT'.$this->suffix;

		$this->seedComment($I, $this->seedNews($I, 'P6 reader future '.$this->suffix, '0', time() + 86400), $marker);

		$I->amOnPage('/'.self::RESET_FILE.'?act=comments');

		$I->dontSeeInSource('SUBJ'.$marker);
	}

	/**
	 * Positive control for the reader. One comment per test, not two: the reader
	 * returns only the first row of its own result set, because the query it
	 * runs per row to find the parent discards the set it is iterating. That is
	 * a defect of its own, filed separately, and a test that seeded two comments
	 * would measure it rather than the predicate.
	 */
	public function theSharedCommentReaderStillServesCommentsOnAPublishedItem(AcceptanceTester $I)
	{
		$I->wantTo('keep the latest-comments menu serving comments on a published item');

		$marker = 'READEROPEN'.$this->suffix;

		$this->seedComment($I, $this->seedNews($I, 'P6 reader live '.$this->suffix, '0', 0), $marker);

		$I->amOnPage('/'.self::RESET_FILE.'?act=comments');

		$I->seeInSource('SUBJ'.$marker);
	}

	/**
	 * Second control: the feed the installer ships must keep working, so a
	 * change to the comments branch cannot be made by breaking rss.php.
	 */
	public function theNewsFeedStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('keep serving the news feed the installer ships');

		$I->amOnPage('/e107_plugins/rss_menu/rss.php?news.2');

		$I->seeResponseCodeIs(200);
		$I->seeInSource('<channel>');
		$I->seeInSource('<item>');
	}

	/**
	 * @return string
	 */
	private function resetSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0037_RssCommentsFeedCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');
// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

if(isset($_GET['act']) && $_GET['act'] === 'comments')
{
	// The reader behind comment_menu, list_new and userposts, asked the way
	// comment_menu.php asks it.
	require_once(e_HANDLER.'comment_class.php');
	foreach((array) e107::getComment()->getCommentData(20) as $row)
	{
		echo $row['comment_subject']."\n";
	}
}

echo 'RESET_DONE';
PHP;
	}
}
