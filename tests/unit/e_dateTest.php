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

		protected $dateObj;

		protected function _before()
		{
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


		//	$this->fail('end');
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

			$this->assertContains('$.fn.datetimepicker.dates["en"]', $actual);
			$this->assertContains('days: ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],', $actual);
			$this->assertContains('monthsShort: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],', $actual);
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

		}

		public function testComputeLapse()
		{

		}

		public function testStrptime()
		{

		}

		public function testConvert_date()
		{

		}

		public function testTerms()
		{

			$data = $this->dateObj->terms();

			$result = ($data[1] === 'January' && $data[12] === 'December') ? true : false;

			$this->assertTrue($result);

		//	$this->fail(print_r($result,true));
		}
	}
