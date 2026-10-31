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
 * Where {@see user_import::saveData()} writes an imported user's extended fields when the record carries no user_id of its own.
 */
class import_userTest extends \Codeception\Test\Unit
{
	const FIELD = 'user_importprobe';

	/** @var string[] */
	private $names = array('importprobe_one', 'importprobe_two');

	/** @var array the main admin's extended row before the test, empty when there is none */
	private $adminExtended;

	protected function _before()
	{
		require_once(e_PLUGIN.'import/import_user_class.php');

		e107::getDb()->gen('ALTER TABLE `'.MPREFIX.'user_extended` ADD `'.self::FIELD.'` VARCHAR(255) NULL');

		$this->adminExtended = $this->extendedRow(1);
	}

	protected function _after()
	{
		$sql = e107::getDb();

		$ids = array_filter(array_map(array($this, 'userId'), $this->names));

		if (!empty($ids))
		{
			$sql->delete('user_extended', 'user_extended_id IN ('.implode(',', $ids).')');
			$sql->delete('user', 'user_id IN ('.implode(',', $ids).')');
		}

		if ($this->adminExtended && $this->extendedRow(1) !== $this->adminExtended)
		{
			$sql->replace('user_extended', $this->adminExtended);
		}

		$sql->gen('ALTER TABLE `'.MPREFIX.'user_extended` DROP `'.self::FIELD.'`');
	}

	/**
	 * @param string $name
	 * @return int the id of the user with that name; 0 when there is none
	 */
	private function userId($name)
	{
		return (int) e107::getDb()->retrieve('user', 'user_id', "user_name = '".$name."'");
	}

	/**
	 * @param int $id
	 * @return array empty when there is no such row
	 */
	private function extendedRow($id)
	{
		return e107::getDb()->retrieve('user_extended', '*', 'user_extended_id = '.(int) $id);
	}

	public function testEachImportedUserGetsAnExtendedRowOfItsOwn()
	{
		$import = new user_import();
		$import->actualExtended[] = self::FIELD;

		foreach ($this->names as $name)
		{
			$user = $import->getDefaults();
			$user['user_name'] = $name;
			$user['user_loginname'] = $name;
			$user['user_password'] = 'not-a-hash';
			$user[self::FIELD] = $name;

			self::assertTrue($import->saveData($user), $name.' must be saved');
		}

		foreach ($this->names as $name)
		{
			$row = $this->extendedRow($this->userId($name));

			self::assertNotEmpty($row, $name.' must have an extended row');
			self::assertSame($name, $row[self::FIELD]);
		}

		self::assertSame($this->adminExtended, $this->extendedRow(1), "The main admin's extended row must be left as it was");
	}
}
