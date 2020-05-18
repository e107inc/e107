<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_dateTest extends \Codeception\Test\Unit
	{
		/** @var e_date  */
		protected $dateObj;

		protected function _before()
		{
			// Expected values made using the C locale
			setlocale(LC_TIME, 'C');
			date_default_timezone_set('UTC');

			try
			{
				$this->dateObj = $this->make('e_date');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_date object");
			}

		}


		public function testToMask()
		{

			$array = array(

			'%Y'	=> 'yyyy',	// jquery-ui docs say 'yy' but yy produces '13' instead of '2013'
			'%d'	=> 'dd',
			'%m'	=> 'mm',
			'%B'	=> 'MM', 	// Full month name, based on the locale
			'%A'	=> 'DD', 	// A full textual representation of the day

			'%I'	=> 'HH',	// Two digit representation of the hour in 12-hour format
			'%H'	=> 'hh',	// 24 hour format - leading zero
			'%y'	=> 'yy',
			'%M'	=> 'ii',	// Two digit representation of the minute
			'%S'	=> 'ss',	// Two digit representation of the second

			'%a'	=> 'D', 	// An abbreviated textual representation of the day
			'%b'	=> 'M', 	// Abbreviated month name, based on the locale
			'%h'	=> 'M', 	// Abbreviated month name, based on the locale (an alias of %b)

			'%l'	=> 'H',		// 12 hour format - no leading zero



			'%p'	=> 'P',	//	%p	UPPER-CASE 'AM' or 'PM' based on the given time
			'%P'	=> 'p',		// %P	lower-case 'am' or 'pm' based on the given time


	//		'%T' 	=> 'hh:mm:ss',
	//		'%r' 	=> "hh:mmm:ss TT" // 12 hour format
			);


			$keys = array_keys($array);
		//	$values = array_values($array);

			$old = implode(" ",$keys);


			$new = $this->dateObj->toMask($old);

			$expected = "yyyy dd mm MM DD HH hh yy ii ss D M M H P p";
			$this->assertEquals($expected,$new);


			$expected = "%Y %d %m %B %A %I %H %y %M %S %a %b %b %l %p %P";
			$actual = $this->dateObj->toMask($new, true);

			$this->assertEquals($expected, $actual);



			$unix = strtotime('December 21, 2012 3:45pm');
			$strftime = "%A, %d %b, %Y %I:%M %p"; // expected Friday, 21 Dec, 2012 03:45 PM
			$expected = "Friday, 21 Dec, 2012 03:45 PM";

			// test strtotime mask (default)
			$actual = $this->dateObj->convert_date($unix, $strftime);
			$this->assertEquals($expected, $actual);

			// test DateTimePicker mask
			$datepicker = $this->dateObj->toMask($strftime);
			$actual2 = $this->dateObj->convert_date($unix, $datepicker);
			$this->assertEquals($expected, $actual2);

			// test DateTime mask
			$dateTime= $this->dateObj->toMask($strftime, 'DateTime');
			$d = new DateTime('@'.$unix);
			$actual3 =  $d->format($dateTime);
			$this->assertEquals($expected, $actual3);


		}

		public function testSupported()
		{
			$this->dateObj->supported(); // dumps info
		}

		public function testIsValidTimezone()
		{
			// should exists
			$result = $this->dateObj->isValidTimezone('Europe/Berlin');
			$this->assertTrue($result);

			// should not exist
			$result = $this->dateObj->isValidTimezone('Europe/Bonn');
			$this->assertFalse($result);
		}

		public function testBuildDateLocale()
		{
			$actual = $this->dateObj->buildDateLocale();

			$this->assertStringContainsString('$.fn.datetimepicker.dates["en"]', $actual);
			$this->assertStringContainsString('days: ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],', $actual);
			$this->assertStringContainsString('monthsShort: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],', $actual);
		}

		public function testToTime()
		{
			// This tests fail on my machine.
			// strptime substracts a month which results in the wrong time // BUG?

			$actual = $this->dateObj->toTime('2018/05/13', '%Y/%m/%d');
			$expected = mktime(0, 0,0,5, 13, 2018);
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->toTime('2018/05/13 20:10', '%Y/%m/%d %H:%M');
			$expected = mktime(20, 10,0,5, 13, 2018);
			$this->assertEquals($expected, $actual);
		}

		public function testDecodeDateTime()
		{
			$actual = $this->dateObj->decodeDateTime('09122003', 'date', 'dmy', false);
			$expected = mktime(0, 0,0,12, 9, 2003);
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->decodeDateTime('153045', 'time', 'dmy', false);
			$expected = mktime(15, 30,45,0, 0, 0);
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->decodeDateTime('09122003 153045', 'datetime', 'dmy', false);
			$expected = mktime(15, 30,45,12, 9, 2003);
			$this->assertEquals($expected, $actual);
		}

		public function testComputeLapse()
		{
			$older = mktime(15, 30,45,12, 9, 2002);
			$newer = mktime(14, 20,40,12, 11, 2003);
			$actual = $this->dateObj->computeLapse($older, $newer, false, true, 'long');
			$expected = '1 year, 1 day, 22 hours, 49 minutes, 55 seconds ago';
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->computeLapse($older, $newer, false, true, 'short');
			$expected = '1 year ago';
			$this->assertEquals($expected, $actual);

            $time = time();
			$newer = strtotime("+2 weeks", $time);
			$actual = $this->dateObj->computeLapse($newer, $time, false, true, 'short');
			$expected = 'in 2 weeks';
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->computeLapse($newer, $time, true, true, 'short');
			$this->assertEquals(array(0=>'2 weeks'), $actual);

			$newer = strtotime("+10 seconds", $time);
			$actual = $this->dateObj->computeLapse($newer, $time, false, true, 'long');
			$this->assertEquals("Just now", $actual);

			// XXX Improve output
		/*	$newer = strtotime("18 months ago");
			$actual = $this->dateObj->computeLapse($newer, time(), false, true, 'short');
			$expected = '18 months ago';

			$this->assertEquals($expected, $actual);*/


		}

		/**
		 *
		 */
		public function testStrptime()
		{

			$actual = $this->dateObj->strptime('2018/05/13', '%Y/%m/%d');
			$expected = array(
				'tm_year' => 118,
				'tm_mon' => 4,
				'tm_mday' => 13,
				'tm_sec' => 0,
				'tm_min' => 0,
				'tm_hour' => 0,
				'unparsed' => '',
				'tm_fmon' => 'May',
				'tm_amon' => 'May',
				'tm_wday' => 0,
				'tm_yday' => 132,
			);
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->strptime('2018/05/13 20:10', '%Y/%m/%d %H:%M');
			$expected = array(
				'tm_year' => 118,
				'tm_mon' => 4,
				'tm_mday' => 13,
				'tm_hour' => 20,
				'tm_min' => 10,
				'tm_sec' => 0,
				'unparsed' => '',
				'tm_amon' => 'May',
				'tm_fmon' => 'May',
				'tm_wday' => 0,
				'tm_yday' => 132,
			);
			$this->assertEquals($expected, $actual);

		}

		public function testConvert_date()
		{
			// will probably fail on windows
			$actual = $this->dateObj->convert_date(mktime(12, 45, 03, 2, 5, 2018), 'long');
			$expected = 'Monday 05 February 2018 - 12:45:03';
			$this->assertEquals($expected, $actual);

			$actual = $this->dateObj->convert_date(mktime(12, 45, 03, 2, 5, 2018), 'inputtime');
			$expected = '12:45 PM';
			$this->assertEquals($expected, $actual);
		}

		public function testTerms()
		{

			$tests = array(
				0   => array('day-shortest', 'We'),
				1   => array('day-short', 'Wed'),
				2   => array('day', 'Wednesday'),
				3   => array('month', 'February'),
				4   => array('month-short', 'Feb'),
			);

			foreach($tests as $var)
			{
				list($input, $expected) = $var;
				$data = $this->dateObj->terms($input);
				$this->assertEquals($expected, $data[2]);
			}

		}







	}
