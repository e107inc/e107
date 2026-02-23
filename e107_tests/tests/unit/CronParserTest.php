<?php


class CronParserTest extends \Codeception\Test\Unit
{

	/** @var CronParser */
	protected $cp;

	protected function _before()
	{
		require_once(e_HANDLER."cron_class.php");

		try
		{
			$this->cp = $this->make('CronParser');
		}

		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}

	}
/*
	public function test_getLastMinute()
	{

	}

	public function test_getHoursArray()
	{

	}

	public function test_prevMonth()
	{

	}

	public function test_getLastDay()
	{

	}

	public function test_getLastMonth()
	{

	}

	public function testGetDays()
	{

	}

	public function test_getMinutesArray()
	{

	}

	public function testGetLastRanUnix()
	{

	}

	public function testDaysinmonth()
	{

	}

	public function test_getMonthsArray()
	{

	}
*/
	public function testCalcLastRan()
	{
		$lastTimeZone = date_default_timezone_get();
		date_default_timezone_set('America/Chihuahua');

		$this->cp->calcLastRan('* * * * *');

		$due = $this->cp->getLastDue();
		$now = $this->cp->getNow();

		list($date, $time) = explode('T', (string) $due);
		list($year,$month,$day) = explode('-', $date);
		list($hour,$minute) = explode(':', $time);

		$this->assertSame($minute, $now[0]);
		$this->assertSame($hour, $now[1]);
		$this->assertSame($day, $now[2]);
		$this->assertSame($month, $now[3]);
		$this->assertSame($year, $now[5]);

		date_default_timezone_set($lastTimeZone);
	}
/*
	public function testGetLastRan()
	{

	}

	public function test_prevDay()
	{

	}

	public function testDebug()
	{

	}

	public function test_sanitize()
	{

	}

	public function testGetDebug()
	{

	}

	public function test_getLastHour()
	{

	}

	public function test_getDaysArray()
	{

	}

	public function test_prevHour()
	{

	}

	public function testExpand_ranges()
	{

	}

	*/


}
