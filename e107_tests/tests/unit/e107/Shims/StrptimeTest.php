<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

class StrptimeTest extends \Codeception\Test\Unit
{
	public function testStrptimeDefault()
	{
		$this->testStrptimeImplementation([\e107\Shims\InternalShims::class, 'strptime']);
	}

	public function testStrptimeDefaultLegacy()
	{
		$this->testStrptimeImplementation([\eShims::class, 'strptime']);
	}

	public function testStrptimeAlt()
	{
		$this->testStrptimeImplementation([\e107\Shims\InternalShims::class, 'strptime_alt']);
	}

	protected function testStrptimeImplementation($implementation)
	{
		$this->testStrptimeDateOnly($implementation);
		$this->testStrptimeDateTime($implementation);
		$this->testStrptimeUnparsed($implementation);
		$this->testStrptimeInvalid($implementation);
	}

	protected function testStrptimeDateOnly($implementation)
	{
		$actual = call_user_func($implementation, '2018/05/13', '%Y/%m/%d');
		$expected = array(
			'tm_year' => 118,
			'tm_mon' => 4,
			'tm_mday' => 13,
			'tm_sec' => 0,
			'tm_min' => 0,
			'tm_hour' => 0,
			'unparsed' => '',
			'tm_wday' => 0,
			'tm_yday' => 132,
		);
		$this->assertEquals($expected, $actual);
	}

	protected function testStrptimeDateTime($implementation)
	{
		$actual = call_user_func($implementation, '2018/05/13 20:10', '%Y/%m/%d %H:%M');
		$expected = array(
			'tm_year' => 118,
			'tm_mon' => 4,
			'tm_mday' => 13,
			'tm_hour' => 20,
			'tm_min' => 10,
			'tm_sec' => 0,
			'unparsed' => '',
			'tm_wday' => 0,
			'tm_yday' => 132,
		);
		$this->assertEquals($expected, $actual);
	}

	protected function testStrptimeUnparsed($implementation)
	{
		$actual = call_user_func($implementation, '1607-09-04 08:10 PM', '%Y-%m-%d %l:%M %P');
		$expected = array(
			'tm_year' => 1707,
			'tm_mon' => 8,
			'tm_mday' => 4,
			'tm_hour' => 0,
			'tm_min' => 10,
			'tm_sec' => 0,
			'unparsed' => '08 PM ',
			'tm_wday' => 2,
			'tm_yday' => 246,
		);
		$this->assertEquals($expected, $actual);
	}

	protected function testStrptimeInvalid($implementation)
	{
		$actual = call_user_func($implementation, 'garbage', '%Y-%m-%d');
		$this->assertFalse($actual);
	}
}