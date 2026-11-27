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
 * The users query {@see drupal_import::setupQuery()} runs against a Drupal 8 database, here two tables of the test database standing in for one.
 */
class drupal_importTest extends \Codeception\Test\Unit
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

		$sql = e107::getDb();
		$sql->gen('CREATE TABLE `'.MPREFIX.self::PREFIX.'users` (`uid` INT(10) UNSIGNED NOT NULL)');
		$sql->gen('CREATE TABLE `'.MPREFIX.self::PREFIX.'users_field_data` (`uid` INT(10) UNSIGNED NOT NULL, `status` TINYINT(4) NOT NULL, `name` VARCHAR(60) NOT NULL)');
		$sql->gen('INSERT INTO `'.MPREFIX.self::PREFIX.'users` VALUES (1), (2), (3)');
		$sql->gen('INSERT INTO `'.MPREFIX.self::PREFIX."users_field_data` VALUES (1, 1, 'admin'), (2, 1, 'active'), (3, 0, 'blocked')");

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
