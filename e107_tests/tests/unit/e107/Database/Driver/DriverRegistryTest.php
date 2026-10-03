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

use InvalidArgumentException;

class DriverRegistryTest extends \Test\Unit
{
	protected function _before()
	{
		require_once(e_HANDLER.'e_db_interface.php');
	}

	public function testANameThatIsAbsentOrEmptyMeansMysql()
	{
		$this->assertSame('mysql', DriverRegistry::create()->getName());
		$this->assertSame('mysql', DriverRegistry::create('')->getName());
		$this->assertSame('mysql', DriverRegistry::create(null)->getName());
	}

	public function testEveryRegisteredNameCreatesTheDriverOfThatName()
	{
		foreach(DriverRegistry::names() as $name)
		{
			$driver = DriverRegistry::create($name);

			$this->assertInstanceOf(DriverInterface::class, $driver);
			$this->assertSame($name, $driver->getName());
		}
	}

	public function testAnUnknownNameIsRefusedWithTheKnownOnesListed()
	{
		try
		{
			DriverRegistry::create('nosuchengine');
			$this->fail('An unknown driver name was accepted.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertStringContainsString('nosuchengine', $e->getMessage());
			$this->assertStringContainsString('mysql', $e->getMessage());
		}
	}

	public function testAvailableListsOnlyDriversThisPhpCanUse()
	{
		foreach(DriverRegistry::available() as $name)
		{
			$this->assertTrue(DriverRegistry::create($name)->isAvailable());
		}

		$this->assertSame(extension_loaded('pdo_mysql'), in_array('mysql', DriverRegistry::available(), true));
	}
}
