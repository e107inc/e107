<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

class UsortTest extends \Codeception\Test\Unit
{
	public function testUsortDefault()
	{
		$this->runUsortImplementation(array(InternalShims::class, 'usort'));
	}

	public function testUsortDefaultLegacy()
	{
		$this->runUsortImplementation(array(\eShims::class, 'usort'));
	}

	public function testUsortAlt()
	{
		$this->runUsortImplementation(array(InternalShims::class, 'usort_alt'));
	}

	protected function runUsortImplementation($implementation)
	{
		$this->assertUsortSorts($implementation);
		$this->assertUsortIsStable($implementation);
		$this->assertUsortReindexes($implementation);
		$this->assertUsortHandlesEmptyArray($implementation);
	}

	protected function assertUsortSorts($implementation)
	{
		$array = array(3, 1, 2);
		$result = call_user_func_array($implementation, array(&$array, function ($a, $b)
		{
			return $a - $b;
		}));

		$this->assertTrue($result);
		$this->assertSame(array(1, 2, 3), $array);
	}

	protected function assertUsortIsStable($implementation)
	{
		$array = array(
			array('key' => 'b', 'id' => 1),
			array('key' => 'a', 'id' => 2),
			array('key' => 'b', 'id' => 3),
			array('key' => 'a', 'id' => 4),
			array('key' => 'b', 'id' => 5),
		);
		call_user_func_array($implementation, array(&$array, function ($a, $b)
		{
			return strcmp($a['key'], $b['key']);
		}));

		$this->assertSame(array(2, 4, 1, 3, 5), array_column($array, 'id'));
	}

	protected function assertUsortReindexes($implementation)
	{
		$array = array('zulu' => 'z', 'alpha' => 'a', 'mike' => 'm');
		call_user_func_array($implementation, array(&$array, 'strcmp'));

		$this->assertSame(array(0, 1, 2), array_keys($array));
		$this->assertSame(array('a', 'm', 'z'), $array);
	}

	protected function assertUsortHandlesEmptyArray($implementation)
	{
		$array = array();
		$result = call_user_func_array($implementation, array(&$array, 'strcmp'));

		$this->assertTrue($result);
		$this->assertSame(array(), $array);
	}
}
