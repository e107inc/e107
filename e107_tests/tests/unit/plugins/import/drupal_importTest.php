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
 * The users query {@see drupal_import::setupQuery()} runs against a Drupal 8 database, here two tables of the test database standing in for one.
 */
class drupal_importTest extends \Test\Unit
{
	const PREFIX = 'drupal8probe_';

	const SOURCE_DB = 'core/e107/singleton/dbourDB';

	/** @var drupal_import */
	private $drupal;

	/** @var e_db|null the registry's source connection before the test */
	private $sourceDb;

	protected function _before()
	{
		e107::lan('import', true);

		require_once(e_PLUGIN.'import/providers/drupal_import_class.php');

		$this->sourceDb = e107::getRegistry(self::SOURCE_DB);
		e107::setRegistry(self::SOURCE_DB, null);

		$schema = e107::getDb()->schema();
		$schema->createTable(self::PREFIX.'users', array(
			'uid' => Column::define('INT', 10)->unsigned()->notNull(),
		));
		$schema->createTable(self::PREFIX.'users_field_data', array(
			'uid'    => Column::define('INT', 10)->unsigned()->notNull(),
			'status' => Column::define('TINYINT', 4)->notNull(),
			'name'   => Column::define('VARCHAR', 60)->notNull(),
		));

		$sql = e107::getDb();
		$sql->createQueryBuilder()->insert(self::PREFIX.'users')->values(array(
			array('uid' => 1),
			array('uid' => 2),
			array('uid' => 3),
		))->execute();
		$sql->createQueryBuilder()->insert(self::PREFIX.'users_field_data')->values(array(
			array('uid' => 1, 'status' => 1, 'name' => 'admin'),
			array('uid' => 2, 'status' => 1, 'name' => 'active'),
			array('uid' => 3, 'status' => 0, 'name' => 'blocked'),
		))->execute();

		$_POST = array('version' => 8);

		$this->drupal = new drupal_import();
		$this->drupal->init();
		$this->drupal->database(e107::getMySQLConfig('defaultdb'), MPREFIX.self::PREFIX);
	}

	protected function _after()
	{
		$_POST = array();

		$sql = e107::getDb();
		$sql->dropTable(self::PREFIX.'users');
		$sql->dropTable(self::PREFIX.'users_field_data');

		e107::setRegistry(self::SOURCE_DB, $this->sourceDb);
	}

	public function testTheUsersQueryReadsTheActiveMembersOtherThanTheFirst()
	{
		self::assertTrue($this->drupal->setupQuery('users', false), 'The Drupal 8 users query must run');

		$row = $this->drupal->ourDB->fetch();

		self::assertSame('active', $row['name']);
		self::assertFalse($this->drupal->ourDB->fetch(), 'Only the one active member after uid 1 may be read');
	}
}
