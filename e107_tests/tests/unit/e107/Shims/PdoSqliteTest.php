<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

use PDO;

class PdoSqliteTest extends \Test\Unit
{
	/** @var string[] deprecations raised while a test ran */
	private $deprecations = array();

	protected function _before()
	{
		if(!extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('pdo_sqlite is not loaded');
		}

		require_once(e_HANDLER.'Shims/PdoSqlite.php');

		$this->deprecations = array();
		set_error_handler(function($number, $message)
		{
			$this->deprecations[] = $message;
			return true;
		}, E_DEPRECATED);
	}

	protected function _after()
	{
		restore_error_handler();
	}

	public function testConnectOpensTheHandleClassThatRegistersFunctions()
	{
		$pdo = PdoSqlite::connect('sqlite::memory:', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

		$this->assertInstanceOf(class_exists('Pdo\\Sqlite', false) ? 'Pdo\\Sqlite' : 'PDO', $pdo);
		$this->assertSame(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
	}

	public function testAFunctionIsCallableFromSqlWithoutADeprecation()
	{
		$pdo = PdoSqlite::connect('sqlite::memory:');

		PdoSqlite::createFunction($pdo, 'e107_shim_join', function()
		{
			return implode('-', func_get_args());
		}, -1);
		PdoSqlite::createFunction($pdo, 'e107_shim_double', function($value)
		{
			return $value * 2;
		}, 1);

		$this->assertSame('a-b-c', $pdo->query("SELECT e107_shim_join('a', 'b', 'c')")->fetchColumn());
		$this->assertSame(14, (int) $pdo->query('SELECT e107_shim_double(7)')->fetchColumn());
		$this->assertSame(array(), $this->deprecations);
	}

	public function testAnAggregateIsCallableFromSqlWithoutADeprecation()
	{
		$pdo = PdoSqlite::connect('sqlite::memory:');
		$pdo->exec('CREATE TABLE t (v INTEGER)');
		$pdo->exec('INSERT INTO t VALUES (2), (3), (4)');

		PdoSqlite::createAggregate($pdo, 'e107_shim_product', function($context, $row, $value)
		{
			return ($context === null ? 1 : $context) * $value;
		}, function($context, $rows)
		{
			return $context;
		}, 1);

		$this->assertSame(24, (int) $pdo->query('SELECT e107_shim_product(v) FROM t')->fetchColumn());
		$this->assertSame(array(), $this->deprecations);
	}

	public function testAPlainPdoHandleIsRefusedWherePhpHasPdoSqlite()
	{
		if(!class_exists('Pdo\\Sqlite', false))
		{
			$this->markTestSkipped('PHP '.PHP_VERSION.' registers functions on PDO itself');
		}

		$this->expectException('InvalidArgumentException');
		PdoSqlite::createFunction(new PDO('sqlite::memory:'), 'e107_shim_none', function() { return 1; }, 0);
	}

	public function testAPlainPdoHandleRegistersFunctionsBeforePdoSqliteExists()
	{
		if(class_exists('Pdo\\Sqlite', false))
		{
			$this->markTestSkipped('PHP '.PHP_VERSION.' has Pdo\\Sqlite');
		}

		$pdo = new PDO('sqlite::memory:');
		PdoSqlite::createFunction($pdo, 'e107_shim_one', function() { return 1; }, 0);

		$this->assertSame(1, (int) $pdo->query('SELECT e107_shim_one()')->fetchColumn());
	}

	public function testAColumnOfAResultWithRowsIsDescribed()
	{
		$pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$meta = PdoSqlite::columnMeta($pdo->query('SELECT 1 AS one'), 0);

		$this->assertSame('one', $meta['name']);
	}

	public function testAColumnOfAnEmptyResultIsDescribedOrAnsweredFalseRatherThanAnError()
	{
		$pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$meta = PdoSqlite::columnMeta($pdo->query('SELECT 1 AS one WHERE 0'), 0);

		$this->assertTrue($meta === false || $meta['name'] === 'one', var_export($meta, true));
	}
}
