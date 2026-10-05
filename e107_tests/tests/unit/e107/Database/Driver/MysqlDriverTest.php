<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Driver;

use e107\Database\Platform\MysqlPlatform;
use Exception;
use PDOException;

class MysqlDriverTest extends \Test\Unit
{
	/** @var MysqlDriver */
	private $driver;

	protected function _before()
	{
		require_once(e_HANDLER.'e_db_interface.php');
		$this->driver = new MysqlDriver();
	}

	public function testItDescribesAServerEngine()
	{
		$this->assertSame('mysql', $this->driver->getName());
		$this->assertTrue($this->driver->requiresServer());
		$this->assertSame(extension_loaded('pdo_mysql'), $this->driver->isAvailable());
		$this->assertNotEmpty($this->driver->getLabel());
		$this->assertTrue(version_compare($this->driver->getMinimumServerVersion(), '4.1.2', '>='));
	}

	/**
	 * Creating, adopting and dropping a database are the statements the installer has always sent, and a refusal
	 * comes back carrying the server's own words.
	 */
	public function testTheDatabaseLifecycleIsTheInstallersStatements()
	{
		$sent = array();
		$connection = $this->makeEmpty(\e107\Database\ConnectionInterface::class, array(
			'execute' => function($statement) use (&$sent) { $sent[] = $statement; return (strpos($statement, 'DROP') === 0) ? false : 1; },
			'getLastErrorText' => 'Access denied',
		));

		$this->driver->createDatabase($connection, 'site`db');
		$this->driver->adoptDatabase($connection, 'site');

		try
		{
			$this->driver->dropDatabase($connection, 'site');
			$this->fail('a refused DROP DATABASE has to be reported');
		}
		catch(\RuntimeException $e)
		{
			$this->assertSame('Access denied', $e->getMessage());
		}

		$this->assertSame(array(
			'CREATE DATABASE `site``db` CHARACTER SET `utf8mb4` ',
			'ALTER DATABASE `site` CHARACTER SET `utf8mb4` ',
			'DROP DATABASE `site` ',
		), $sent);
	}

	public function testItSpeaksTheMysqlDialect()
	{
		$this->assertInstanceOf(MysqlPlatform::class, $this->driver->createPlatform());
	}

	public function testTheSessionStatementsAreTheOnesE107HasAlwaysSent()
	{
		$this->assertSame('SET NAMES `utf8mb4`', $this->driver->getCharsetStatement('utf8mb4'));
		$this->assertSame(array("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';"), $this->driver->getSessionStatements());
	}

	public function testASecondDatabaseIsReachedThroughAQualifiedPrefix()
	{
		$this->assertSame('`other`.e107_', $this->driver->qualifyPrefix(null, 'other', 'e107_'));
		$this->assertSame('`we``ird`.e107_', $this->driver->qualifyPrefix(null, 'we`ird', 'e107_'));
	}

	public function testTheErrorNumberComesFromErrorInfoThenFromAnIntegerCode()
	{
		$withInfo = new PDOException('Duplicate entry');
		$withInfo->errorInfo = array('23000', 1062, 'Duplicate entry');
		$this->assertSame(1062, $this->driver->errorNumber($withInfo));

		// php-src bug #64705: a refused connection carried the errno only in an integer code
		$this->assertSame(1045, $this->driver->errorNumber(new Exception('Access denied', 1045)));

		// a SQLSTATE in the code is a string of digits, never a driver number
		$sqlstate = new PDOException('General error');
		$property = new \ReflectionProperty(Exception::class, 'code');
		$property->setAccessible(true);
		$property->setValue($sqlstate, 'HY000');
		$this->assertSame(-1, $this->driver->errorNumber($sqlstate));

		$this->assertSame(-1, $this->driver->errorNumber(new Exception('no number')));
	}

	public function testTheLockStatementBindsEveryValue()
	{
		$sent = array();
		$connection = $this->makeEmpty(\e107\Database\ConnectionInterface::class, array(
			'execute' => function($statement, $params = array()) use (&$sent) { $sent[] = array($statement, $params); return 1; },
			'fetch' => array('answer' => '1'),
		));

		$this->assertTrue($this->driver->acquireLock($connection, 'e107_import', 5));
		$this->assertTrue($this->driver->releaseLock($connection, 'e107_import'));
		$this->assertSame(array(
			array('SELECT GET_LOCK(:name, :timeout) AS answer', array('name' => 'e107_import', 'timeout' => 5)),
			array('SELECT RELEASE_LOCK(:name) AS answer', array('name' => 'e107_import')),
		), $sent);
	}
}
