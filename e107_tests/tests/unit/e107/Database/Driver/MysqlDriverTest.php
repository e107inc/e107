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
}
