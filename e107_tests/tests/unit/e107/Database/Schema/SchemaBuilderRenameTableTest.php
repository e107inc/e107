<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema;

use e107;
use e107\Database\Schema\Declared\DeclaredTable;

/**
 * {@see SchemaBuilder::renameTable()} against the suite's own database, on whatever engine it runs.
 */
class SchemaBuilderRenameTableTest extends \Test\Unit
{
	const BODY = 'probe_id int(10) NOT NULL, probe_name varchar(20) NOT NULL, PRIMARY KEY (probe_id), KEY probe_name (probe_name)';

	protected function _before()
	{
		$this->dropProbes();
	}

	protected function _after()
	{
		$this->dropProbes();
	}

	public function testARenamedTableTakesItsIndexesSoTheOldNameCanBeBuiltAgain()
	{
		$schema = e107::getDb()->schema();

		$this->assertNotFalse($schema->createDeclaredTable(new DeclaredTable('core', 'rename_probe', self::BODY)));
		$this->assertNotFalse($schema->renameTable('rename_probe', 'rename_probe_moved'), e107::getDb()->getLastErrorText());
		$this->assertNotFalse($schema->createDeclaredTable(new DeclaredTable('core', 'rename_probe', self::BODY)), e107::getDb()->getLastErrorText());
		$this->assertNotFalse($schema->table('rename_probe_moved')->dropIndex('probe_name')->execute(), e107::getDb()->getLastErrorText());

		$this->assertSame(array('PRIMARY'), $this->indexNames('rename_probe_moved'));
		$this->assertSame(array('PRIMARY', 'probe_name'), $this->indexNames('rename_probe'));
	}

	public function testRenamingATableThatIsNotThereFails()
	{
		$this->assertFalse(e107::getDb()->schema()->renameTable('rename_probe', 'rename_probe_moved'));
	}

	/**
	 * @param string $table
	 * @return string[] sorted
	 */
	private function indexNames($table)
	{
		$names = array();

		foreach(e107::getDb()->schema()->getIndexes($table) as $row)
		{
			$names[$row['Key_name']] = true;
		}

		$names = array_keys($names);
		sort($names);

		return $names;
	}

	/**
	 * @return void
	 */
	private function dropProbes()
	{
		e107::getDb()->schema()->dropTable('rename_probe');
		e107::getDb()->schema()->dropTable('rename_probe_moved');
	}
}
