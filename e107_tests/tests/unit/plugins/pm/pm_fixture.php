<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * What the limit and attachment tests share: the plugin's language file, and the generic rows a test adds, taken away again once it is done.
 */
abstract class pm_fixture extends \Codeception\Test\Unit
{
	/** @var int[] the generic rows this test added */
	private $genIds = array();

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

		$this->genIds = array();
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
