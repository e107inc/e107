<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * The advanced search's author filter, from the member picked in its field to what each search area matches.
 */
class searchAuthorFilterTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Helper\SearchPage;

	const KEYWORD = 'quokkaprobe';

	const NEWS_TITLE = 'Author filter probe news';

	/** User 1's comment, stored under a name the member no longer goes by. */
	const MEMBER_COMMENT = 'quokkaprobe written by user 1';

	/** A guest's comment, signed with a name that holds user 1's current name. */
	const GUEST_COMMENT = 'quokkaprobe written by a guest';

	/** @var bool */
	private $seeded = false;

	protected function _before()
	{
		require_once(e_HANDLER.'search_class.php');
	}

	protected function _after()
	{
		if($this->seeded)
		{
			$this->runInBootedCli(
				"e107::getDb()->delete('comments', \"comment_comment LIKE '".self::KEYWORD."%'\"); "
				. "e107::getDb()->delete('news', \"news_title = '".self::NEWS_TITLE."'\"); "
			);
		}
	}

	public function testTheAuthorFieldSendsThePickedMemberUnderTheKeyTheSearchAreasRead()
	{
		$xpath = $this->searchPageXPath($this->renderSearchPage('_blank'));

		$this->assertSame(1, $xpath->query("//*[@id='search-advanced']//input[@name='author']")->length,
			'The author field must send the member it picked as author, which is what every search area reads.');
	}

	public function testASearchWithNoAuthorFindsBothComments()
	{
		$html = $this->renderCommentSearch(array('q' => self::KEYWORD, 'r' => 0));

		$this->assertStringContainsString(self::MEMBER_COMMENT, $html, $html);
		$this->assertStringContainsString(self::GUEST_COMMENT, $html, $html);
	}

	public function testACommentSearchByAMembersIdFindsWhatThatMemberWrote()
	{
		$html = $this->renderCommentSearch(array('q' => self::KEYWORD, 'r' => 0, 'adv' => 1, 'author' => '1'));

		$this->assertStringContainsString(self::MEMBER_COMMENT, $html,
			"A search filtered by user 1 must find user 1's comment, whatever name it was posted under.\n".$html);
		$this->assertStringNotContainsString(self::GUEST_COMMENT, $html,
			"A search filtered by user 1 must leave out a comment someone else signed with a similar name.\n".$html);
	}

	public function testAChatboxSearchByAMembersIdMatchesTheIdItStoresWithTheName()
	{
		require_once(e_PLUGIN.'chatbox_menu/e_search.php');
		$area = new chatbox_menu_search('');

		$this->assertStringContainsString("cb_nick LIKE '7.%'", $area->where(array('author' => '7')));
		$this->assertStringContainsString("cb_nick LIKE '%Zebedee%'", $area->where(array('author' => 'Zebedee')));
	}

	public function testADownloadSearchByAMembersIdMatchesThatMembersName()
	{
		require_once(e_PLUGIN.'download/e_search.php');
		$area = new download_search('');
		$member = e107::user(1);

		$this->assertStringContainsString("d.download_author = '".$member['user_name']."'", (string) $area->where(array('author' => '1')));
	}

	/**
	 * Renders the comment search over a news item and two comments on it, seeded in the subprocess the page runs in.
	 *
	 * @param array $get the query string, apart from the search type
	 * @return string the page as it was sent
	 */
	private function renderCommentSearch(array $get)
	{
		$comment = "'comment_pid' => 0, 'comment_subject' => '', 'comment_author_email' => '', 'comment_datestamp' => time() - 60, "
			. "'comment_blocked' => 0, 'comment_ip' => '', 'comment_type' => '0', 'comment_lock' => 0, 'comment_share' => 0, "
			. "'comment_item_id' => \$news";

		$seed = \Test\NewsSeed::php(self::NEWS_TITLE, '1');
		$seed .= "\$member = e107::user(1); ";
		$seed .= "e107::getDb()->insert('comments', array(".$comment.", "
			. "'comment_author_id' => 1, 'comment_author_name' => 'Former Name', 'comment_comment' => '".self::MEMBER_COMMENT."')); ";
		$seed .= "e107::getDb()->insert('comments', array(".$comment.", "
			. "'comment_author_id' => 0, 'comment_author_name' => 'sys'.\$member['user_name'], 'comment_comment' => '".self::GUEST_COMMENT."')); ";
		$seed .= "e107::getConfig('search')->setPref('core_handlers/comments', array('class' => '0', 'chars' => 150, 'results' => 10, "
			. "'pre_title' => 1, 'pre_title_alt' => '', 'order' => 1)); ";
		$seed .= "e107::getConfig('search')->setPref('comments_handlers', array('news' => array('id' => 0, 'class' => '0', 'dir' => 'core'))); ";
		$seed .= "e107::getConfig('search')->setPref('mysql_sort', 1); ";
		$seed .= "\$_GET = ".var_export($get, true)."; ";

		$this->seeded = true;

		return $this->renderSearchPage('comments', $seed);
	}
}
