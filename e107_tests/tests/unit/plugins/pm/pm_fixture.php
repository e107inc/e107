<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * What the limit and attachment tests share: the plugin's language file, and the plugin tables and generic rows a test adds, taken away again once it is done.
 */
abstract class pm_fixture extends \Codeception\Test\Unit
{
	/** @var int[] the generic rows this test added */
	private $genIds = array();

	/** @var string[] the plugin tables this test created */
	private $tables = array();

	protected function _before()
	{
		e107::includeLan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');
	}

	protected function _after()
	{
		foreach($this->genIds as $id)
		{
			e107::getDb()->delete('generic', 'gen_id = '.(int) $id);
		}

		foreach($this->tables as $table)
		{
			\Test\CoreTable::drop($table);
		}

		$this->genIds = array();
		$this->tables = array();
	}

	/**
	 * Creates a table pm_sql.php declares, unless the database already has it.
	 *
	 * @param string $name without the table prefix
	 * @return void
	 */
	protected function requireTable($name)
	{
		if(\Test\CoreTable::createDeclared($name, e_PLUGIN.'pm/pm_sql.php'))
		{
			$this->tables[] = $name;
		}
	}

	/**
	 * @param array $row column => value
	 * @return int the row's gen_id
	 */
	protected function addGenericRow(array $row)
	{
		$id = (int) e107::getDb()->insert('generic', $row);

		self::assertNotEmpty($id, 'Could not seed a generic row.');

		$this->genIds[] = $id;

		return $id;
	}
}
