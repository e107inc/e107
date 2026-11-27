<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The most active topics section of top.php, on a site whose forums hold no thread the request would list.
 *
 * @see https://github.com/e107inc/e107/issues/6831
 */
class topPageTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\ForumRows;

	protected function _before()
	{
		$this->haveForumTables();
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testAVisitorWhoMayReadNoForumSendsNoFailingQuery()
	{
		$this->haveForum('e107help top probe forum', $this->haveForum('e107help top probe category', 0, e_UC_NOBODY), e_UC_NOBODY);

		$page = $this->renderActiveTopics('0.active');

		self::assertSame(0, $this->viewableForums($page),
			"the visitor may read a forum, so the empty list was never measured:\n".$page);

		self::assertLeftNoQueryError($page, "the most active topics query failed:\n".$page);
	}

	public function testANegativeOffsetOrPageSizeSendsNoFailingQuery()
	{
		$this->haveForum('e107help top probe forum', $this->haveForum('e107help top probe category'));

		foreach(array('-1.active', '0.active.forum.-1') as $query)
		{
			$page = $this->renderActiveTopics($query);

			self::assertGreaterThan(0, $this->viewableForums($page),
				"the visitor may read no forum, so on the unfixed page the empty IN () list, not the paging, would fail the query:\n".$page);

			self::assertLeftNoQueryError($page, "the most active topics query for top.php?".$query." failed:\n".$page);
		}
	}

	/**
	 * @param string $query the query string top.php is opened with
	 * @return string the page, with the number of forums the child may view and the error its last query left behind
	 */
	private function renderActiveTopics($query)
	{
		$seed = "require_once(e_PLUGIN.'forum/forum_class.php'); ";
		$seed .= "echo \"\\n@@VIEWABLE=\", count(e107forum::visibleForumIds('view')), \"@@\\n\"; ";
		$seed .= self::queryErrorProbe();

		return $this->renderInBootedCli('top.php', $seed, '', array('e_QUERY' => $query));
	}

	/**
	 * @param string $page
	 * @return int
	 */
	private function viewableForums($page)
	{
		self::assertSame(1, preg_match('/@@VIEWABLE=(\d+)@@/', $page, $match), "the child never counted its forums:\n".$page);

		return (int) $match[1];
	}
}
