<?php


class CronParserTest extends \Test\Unit
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
	public function testCalcLastRan()
	{
		$lastTimeZone = date_default_timezone_get();
		date_default_timezone_set('America/Chihuahua');

		$this->cp->calcLastRan('* * * * *');

		$due = $this->cp->getLastDue();
		$now = $this->cp->getNow();

		list($date, $time) = explode('T', $due);
		list($year,$month,$day) = explode('-', $date);
		list($hour,$minute) = explode(':', $time);

		$this->assertSame($minute, $now[0]);
		$this->assertSame($hour, $now[1]);
		$this->assertSame($day, $now[2]);
		$this->assertSame($month, $now[3]);
		$this->assertSame($year, $now[5]);

		date_default_timezone_set($lastTimeZone);
	}


}
