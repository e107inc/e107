<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The forum half of userposts.php, listing the CLI child's own posts (user 1) where there are none to show.
 *
 * @see https://github.com/e107inc/e107/discussions/5900
 */
class userpostsPageTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\ForumRows;

	protected function _before()
	{
		e107::coreLan('userposts');

		$this->haveForumTables();
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testAMemberWithNoPostTheVisitorMayReadIsShownNoPostsFromAnEmptyResult()
	{
		$this->havePostIn(e_UC_NOBODY);

		$this->assertNoPostsFromAnEmptyResult($this->renderForumPosts(''));
	}

	public function testASearchThatMatchesNothingIsShownNoPostsFromAnEmptyResult()
	{
		$this->havePostIn(e_UC_PUBLIC);

		$this->assertNoPostsFromAnEmptyResult($this->renderForumPosts('e107help words in no post'));
	}

	/**
	 * @param int $class the user class that may read the forum the post is in
	 * @return void
	 */
	private function havePostIn($class)
	{
		$category = $this->haveForum('e107help userposts probe category', 0, $class);
		$forumId = $this->haveForum('e107help userposts probe forum', $category, $class);
		$threadId = $this->haveForumThread('e107help userposts probe thread', $forumId);
		$this->haveForumPost('e107help userposts probe post', $threadId, $forumId);
	}

	/**
	 * @param string $search what the visitor searched their posts for, '' for no search
	 * @return string the page, then the error the posts query left behind as @@QUERY-ERROR=...@@
	 */
	private function renderForumPosts($search)
	{
		$seed = "error_reporting(E_ALL); ";
		$seed .= "register_shutdown_function(function() { echo \"\\n@@QUERY-ERROR=\", e107::getDb('posts')->getLastErrorText(), \"@@\\n\"; }); ";

		if($search !== '')
		{
			$seed .= "\$_POST['f_query'] = ".var_export($search, true)."; \$_POST['fsearch'] = 1; ";
		}

		return $this->renderInBootedCli('userposts.php', $seed, '', array('e_QUERY' => '0.forums.1'));
	}

	/**
	 * @param string $page
	 * @return void
	 */
	private function assertNoPostsFromAnEmptyResult($page)
	{
		self::assertStringContainsString("<span class='mediumtext'>".UP_LAN_8.'</span>', $page,
			"the page never reached the branch for an empty result, so nothing was measured:\n".$page);

		self::assertStringContainsString('@@QUERY-ERROR=@@', $page,
			"the posts query failed rather than found nothing:\n".$page);

		self::assertReadNoUndefinedVariable('ftext', $page,
			"the empty result is appended to a variable nothing set:\n".$page);
	}
}
