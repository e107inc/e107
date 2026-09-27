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
 * The tables pm_sql.php declares, read by the parser a plugin upgrade compares a live site against.
 */
class pm_sqlTest extends \Test\Unit
{
	/** @var db_verify */
	private $dbv;

	protected function _before()
	{
		require_once(e_HANDLER . 'db_verify_class.php');

		$this->dbv = new db_verify(false);
	}

	/**
	 * Deleting a message asks which of its sender's other messages still name each attachment, and only the sender is indexable.
	 */
	public function testPrivateMessagesAreIndexedBySender()
	{
		$columns = array();

		foreach($this->dbv->getIndex($this->declaredBody('private_msg')) as $index)
		{
			$columns[] = $index['keyname'];
		}

		self::assertContains('pm_from', $columns);
	}

	/**
	 * @param string $table
	 * @return string the column and key lines pm_sql.php declares for $table
	 */
	private function declaredBody($table)
	{
		$declared = $this->dbv->getSqlFileTables(file_get_contents(e_PLUGIN . 'pm/pm_sql.php'));
		$ordinal = array_search($table, $declared['tables'], true);

		self::assertNotFalse($ordinal, 'pm_sql.php no longer declares ' . $table);

		return $declared['data'][$ordinal];
	}
}
