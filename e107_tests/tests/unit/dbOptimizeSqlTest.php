<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers Optimize SQL database in e107_admin/db.php by what it does to a table:
 * a MyISAM table keeps the space of deleted rows as Data_free until it is
 * optimised, so a probe table carrying some shows whether the tool reached it.
 * The probe's name sorts after every other fixture's, so a run that stops at the
 * first table it cannot optimise never reaches it.
 *
 * @see https://github.com/e107inc/e107/issues/6665
 */
class dbOptimizeSqlTest extends \Test\Unit
{
	/** SEP belongs to the admin theme, which a CLI child never loads. */
	const SEED = "define('SEP', ' &raquo; '); \$_POST['optimize_sql'] = 1; ";

	/** @var string physical name of the probe table */
	private $probe;

	/** @var string physical name of a view, which SHOW TABLES lists and OPTIMIZE TABLE refuses */
	private $view;

	/** @var string physical name of a table whose name the schema builder will not quote */
	private $oddlyNamed;

	protected function _before()
	{
		$this->requireDatabaseDriver('mysql', "it reads MyISAM's free space and the answer OPTIMIZE TABLE gives for each table");

		$this->probe = MPREFIX.'optimize_sql_zz_probe';
		$this->view = MPREFIX.'optimize_sql_view';
		$this->oddlyNamed = MPREFIX.'optimize_sql_odd$name';

		$this->dropFixtures();

		$sql = e107::getDb();
		$sql->execute('CREATE TABLE `'.$this->probe.'` (probe_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, probe_text VARCHAR(255) NOT NULL) ENGINE=MyISAM');

		foreach(array('a', 'b', 'c', 'd') as $fill)
		{
			$sql->execute('INSERT INTO `'.$this->probe.'` (probe_text) VALUES (:text)', array('text' => str_repeat($fill, 200)));
		}

		$sql->execute('DELETE FROM `'.$this->probe.'` WHERE probe_id IN (1, 3)');
	}

	protected function _after()
	{
		$this->dropFixtures();
	}

	public function testOptimisingReclaimsTheSpaceDeletedRowsLeftInTheSitesTables()
	{
		$this->renderInBootedCli('e107_admin/db.php', self::SEED);

		$this->assertSame(0, $this->freeSpace(),
			'Optimize SQL database left the space of deleted rows in '.$this->probe.', so it never optimised the table.');
	}

	public function testTheOptimisedMessageNamesTheDatabase()
	{
		$this->assertStringContainsString($this->optimizedMessage(), $this->renderInBootedCli('e107_admin/db.php', self::SEED));
	}

	public function testATableTheServerCannotOptimiseIsReportedInsteadOfSuccess()
	{
		e107::getDb()->execute('CREATE VIEW `'.$this->view.'` AS SELECT 1 AS one');

		$page = $this->renderInBootedCli('e107_admin/db.php', self::SEED);

		$this->assertStringContainsString('Table '.$this->view.' was not optimized', $page);
		$this->assertStringNotContainsString($this->optimizedMessage(), $page,
			'OPTIMIZE TABLE answered with an error row for the view, yet the page reported the database optimised.');
		$this->assertSame(0, $this->freeSpace(),
			'The view the server refused stopped the run before '.$this->probe.' was optimised.');
	}

	public function testANameTheSchemaBuilderRefusesIsReportedAndTheOtherTablesAreStillOptimised()
	{
		e107::getDb()->execute('CREATE TABLE `'.$this->oddlyNamed.'` (probe_id INT NOT NULL) ENGINE=MyISAM');

		$page = $this->renderInBootedCli('e107_admin/db.php', self::SEED);

		$this->assertStringContainsString('Table '.$this->oddlyNamed.' was not optimized', $page);
		$this->assertSame(0, $this->freeSpace(),
			'One table name the schema builder refused stopped the run before '.$this->probe.' was optimised.');
	}

	/**
	 * @return string DBLAN_11, the message for a run in which every table was optimised
	 */
	private function optimizedMessage()
	{
		$database = e107::getMySQLConfig('defaultdb');
		$this->assertNotEmpty($database, 'The site configuration names no database, so the message cannot be checked.');

		return 'MySQL database '.$database.' optimized';
	}

	private function dropFixtures()
	{
		$sql = e107::getDb();
		$sql->execute('DROP VIEW IF EXISTS `'.$this->view.'`');
		$sql->execute('DROP TABLE IF EXISTS `'.$this->probe.'`, `'.$this->oddlyNamed.'`');
	}

	/**
	 * @return int bytes the probe table holds for rows that were deleted
	 */
	private function freeSpace()
	{
		$sql = e107::getDb();
		$sql->execute('SELECT DATA_FREE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name',
			array('name' => $this->probe));
		$row = $sql->fetch();

		$this->assertNotEmpty($row, $this->probe.' is missing, so its free space cannot be read.');

		return (int) $row['DATA_FREE'];
	}
}
