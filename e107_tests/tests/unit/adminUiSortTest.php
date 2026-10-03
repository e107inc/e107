<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Dragging rows into order numbers the page that was sorted, and every row after it follows on.
 */
class adminUiSortTest extends \Test\Unit
{
	const TABLE = 'admin_sort_probe';

	/** @var array */
	private $post;

	/** @var array */
	private $get;

	protected function _before()
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiSortProbeFixture.php');

		$this->post = $_POST;
		$this->get = $_GET;

		$db = e107::getDb();
		$db->schema()->dropTable(self::TABLE);
		$db->schema()->createTableRaw(self::TABLE, $db->createQueryBuilder()->raw(
			'probe_id int(10) unsigned NOT NULL auto_increment, probe_order int(10) unsigned NOT NULL default 0, PRIMARY KEY (probe_id)'
		));

		foreach(array(1, 2, 3, 4, 5) as $order)
		{
			$db->createQueryBuilder()->insert(self::TABLE)->values(array('probe_order' => $order))->execute();
		}
	}

	protected function _after()
	{
		$_POST = $this->post;
		$_GET = $this->get;

		e107::getDb()->schema()->dropTable(self::TABLE);
	}

	public function testTheSortedPageIsNumberedAndTheRowsAfterItFollowOn()
	{
		$_GET['from'] = 0;
		$_POST['all'] = array('row-4', 'row-2');

		$ui = new adminUiSortProbe();
		$ui->SortAjaxPage();

		$order = e107::getDb()->createQueryBuilder()->select('probe_id', 'probe_order')->from(self::TABLE)
			->orderBy('probe_order', 'ASC')->fetchPairs('probe_id', 'probe_order');

		$this->assertSame(array(4, 2), array_slice(array_map('intval', array_keys($order)), 0, 2), 'the dragged rows come first, in the order dropped');
		$this->assertSame(array('1', '2', '3', '4', '5'), array_map('strval', array_values($order)), 'one step apart, with no gaps or repeats');
	}
}
