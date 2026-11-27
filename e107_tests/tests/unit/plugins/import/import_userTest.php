<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Database\Schema\Column;

/**
 * Where {@see user_import::saveData()} writes an imported user's extended fields when the record carries no user_id of its own.
 */
class import_userTest extends \Test\Unit
{
	const FIELD = 'user_importprobe';

	/** @var string[] */
	private $names = array('importprobe_one', 'importprobe_two');

	/** @var array|false the main admin's extended row before the test */
	private $adminExtended;

	protected function _before()
	{
		require_once(e_PLUGIN.'import/import_user_class.php');

		e107::getDb()->schema()->addColumn('user_extended', self::FIELD, Column::define('VARCHAR', 255)->nullable());

		$this->adminExtended = $this->extendedRow(1);
	}

	protected function _after()
	{
		$sql = e107::getDb();

		$ids = array_filter(array_map(array($this, 'userId'), $this->names));

		if (!empty($ids))
		{
			$sql->createQueryBuilder()->delete('user_extended')->whereIn('user_extended_id', $ids)->execute();
			$sql->createQueryBuilder()->delete('user')->whereIn('user_id', $ids)->execute();
		}

		if ($this->adminExtended)
		{
			$sql->createQueryBuilder()->replace('user_extended')->values($this->adminExtended)->execute();
		}

		$sql->schema()->dropColumn('user_extended', self::FIELD);
	}

	/**
	 * @param string $name
	 * @return int the id of the user with that name; 0 when there is none
	 */
	private function userId($name)
	{
		return (int) e107::getDb()->createQueryBuilder()->select('user_id')->from('user')->where('user_name', $name)->fetchOne();
	}

	/**
	 * @param int $id
	 * @return array|false
	 */
	private function extendedRow($id)
	{
		return e107::getDb()->createQueryBuilder()->select('*')->from('user_extended')->where('user_extended_id', $id)->fetchRow();
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
