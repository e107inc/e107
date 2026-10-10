<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 */
class forumMetaDescriptionTest extends \Test\Unit
{
	use \Test\ForumRows;

	const MARK = '@@e107help-meta-description@@';

	protected function _before()
	{
		$this->haveForumTables();
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testATopicDescriptionCarriesNoQuoteFromAMembersPost()
	{
		$threadId = $this->haveTopicOpenedWith('0;url=//example.com/ &quot; http-equiv=&quot;refresh&quot; x=&quot; [/&#039;b]' . str_repeat(' padding', 40));

		self::assertStringNotContainsString('"', $this->metaDescriptionOf('forum_viewtopic.php', $threadId));
	}

	public function testATopicDescriptionCarriesNoQuoteFromAPostHtmlPost()
	{
		$threadId = $this->haveTopicOpenedWith($this->postHtmlBreakout());

		self::assertStringNotContainsString('"', $this->metaDescriptionOf('forum_viewtopic.php', $threadId));
	}

	public function testAForumDescriptionCarriesNoQuote()
	{
		$forumId = $this->haveForum('e107help probe forum', $this->haveForum('e107help probe category'));
		e107::getDb()->createQueryBuilder()->update('forum')
			->set('forum_description', $this->postHtmlBreakout())
			->where('forum_id', $forumId)
			->execute();

		self::assertStringNotContainsString('"', $this->metaDescriptionOf('forum_viewforum.php', $forumId));
	}

	/**
	 * @return string stored as a post_html poster's text is saved
	 */
	private function postHtmlBreakout()
	{
		return '0;url=//example.com/&amp;[b][/b]quot; http-equiv=refresh x=' . str_repeat(' padding', 40) . ' [/right]';
	}

	/**
	 * @param string $entry the first post, as stored
	 * @return int the thread id
	 */
	private function haveTopicOpenedWith($entry)
	{
		$forumId = $this->haveForum('e107help probe forum', $this->haveForum('e107help probe category'));
		$threadId = $this->haveForumThread('e107help probe thread', $forumId);
		$this->haveForumPost($entry, $threadId, $forumId);

		return $threadId;
	}

	/**
	 * @param string $page the page file under e107_plugins/forum/
	 * @param int $id the thread or forum it is opened on
	 * @return string the page's META_DESCRIPTION
	 */
	private function metaDescriptionOf($page, $id)
	{
		$php = "register_shutdown_function(function() { echo '".self::MARK."', defined('META_DESCRIPTION') ? META_DESCRIPTION : '', '".self::MARK."'; }); ";
		$php .= "\$_REQUEST = \$_GET; ";
		$php .= "require_once('".addslashes(APP_PATH.'/e107_plugins/forum/'.$page)."'); ";

		list($output, ) = $this->bootPluginInCli('forum', '2.0', array('id' => $id), $php);
		$printed = implode("\n", $output);
		$mark = preg_quote(self::MARK, '/');

		self::assertSame(1, preg_match('/'.$mark.'(.+?)'.$mark.'/s', $printed, $match),
			"the page never defined its meta description:\n".$printed);

		return $match[1];
	}
}
