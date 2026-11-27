<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The delete button on each row of the Custom Pages Overview list, in e107_admin/cpage.php.
 *
 * @see https://github.com/e107inc/e107/issues/6434
 */
class cpageOverviewDeleteTest extends \Test\Unit
{
	use \Test\ScriptClassPages;
	use \Test\PageRows;

	const TITLE = 'cpageOverviewDeleteTest';

	const SCRIPT = 'e107_admin/cpage.php';

	const OVERVIEW = 'mode=overview&action=list';

	const LISTENER = "function cpageOverviewDeleteProbe(\$data, \$event) { echo '@@'.\$event.'@@'; } e107::getEvent()->register('admin_page_deleted', 'cpageOverviewDeleteProbe'); ";

	const FAILING_DELETE = "function cpageOverviewDeleteFailure(\$data, \$event) { e107::getAdminUI()->getController()->getTreeModel()->setModelTable('cpageOverviewDeleteTest_missing'); } e107::getEvent()->register('admin_page_delete', 'cpageOverviewDeleteFailure'); ";

	/** @var int */
	private $page = 0;

	protected function _before()
	{
		\Test\CoreTable::create('admin_history');

		$this->page = $this->havePage(self::TITLE, array(
			'page_text' => 'The page body the delete takes.',
			'menu_name' => self::TITLE,
		));
	}

	protected function _after()
	{
		$this->removePages();
		e107::getDb()->createQueryBuilder()->delete('menus')->where('menu_name', self::TITLE)->execute();

		\Test\CoreTable::drop('admin_history');
	}

	public function testTheOverviewDeleteButtonPostsTheListDeleteTrigger()
	{
		$page = $this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, self::OVERVIEW);

		self::assertSame(1, preg_match("/<button[^>]*name=(['\"])etrigger_delete\[" . $this->page . "\]\\1[^>]*>/", $page, $button), "the row has no delete button posting the list delete trigger:\n" . $page);
		self::assertStringContainsString('action delete btn btn-danger', $button[0]);
		self::assertStringNotContainsString('menu_delete[', $page);
	}

	public function testTheOverviewDeleteRemovesThePageItsPlacementAndArchivesIt()
	{
		$this->placeInAMenu();

		$out = $this->deleteThroughTheOverview(self::LISTENER);

		self::assertFalse($this->pageExists(), "the page is still there:\n" . $out);
		self::assertSame(0, $this->placements());
		self::assertSame(1, (int) e107::getDb()->createQueryBuilder()->from('admin_history')
			->where('history_table', 'page')->where('history_record_id', $this->page)->where('history_action', 'delete')->count());
		self::assertStringContainsString('@@admin_page_deleted@@', $out);
	}

	public function testDeletingAPageWithNoMenuPlacementStillReportsTheDelete()
	{
		$out = $this->deleteThroughTheOverview(self::LISTENER);

		self::assertFalse($this->pageExists(), "the page is still there:\n" . $out);
		self::assertStringContainsString('@@admin_page_deleted@@', $out);
	}

	public function testADeleteThatFailsLeavesThePageInItsMenu()
	{
		$this->placeInAMenu();

		$out = $this->deleteThroughTheOverview(self::FAILING_DELETE);

		self::assertTrue($this->pageExists(), "the delete did not fail:\n" . $out);
		self::assertSame(1, $this->placements());
		self::assertStringContainsString('SQL Delete Error', $out, 'the delete error is shown');
	}

	/**
	 * @return bool
	 */
	private function pageExists()
	{
		return (bool) e107::getDb()->createQueryBuilder()->from('page')->where('page_id', $this->page)->count();
	}

	/**
	 * @param string $seed PHP run before the page
	 * @return string the Overview list after its delete button for the seeded page was pressed
	 */
	private function deleteThroughTheOverview($seed)
	{
		return $this->openAsScriptClass(e_UC_NOBODY, self::SCRIPT, self::OVERVIEW, array('etrigger_delete' => array($this->page => $this->page)), $seed);
	}

	/**
	 * @return void
	 */
	private function placeInAMenu()
	{
		e107::getDb()->createQueryBuilder()->insert('menus')->insertGetId(array(
			'menu_name'     => self::TITLE,
			'menu_location' => 1,
			'menu_order'    => 1,
			'menu_class'    => '0',
			'menu_pages'    => '',
			'menu_path'     => (string) $this->page,
			'menu_layout'   => '',
			'menu_parms'    => '',
		));
	}

	/**
	 * @return int how many menu placements the seeded page has
	 */
	private function placements()
	{
		return (int) e107::getDb()->createQueryBuilder()->from('menus')->where('menu_path', (string) $this->page)->count();
	}
}
