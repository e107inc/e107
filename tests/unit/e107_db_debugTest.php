<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 2/10/2019
	 * Time: 2:21 PM
	 */


	class e107_db_debugTest extends \Codeception\Test\Unit
	{

		/** @var e107_db_debug */
		protected $dbg;

		protected function _beforeSuite()
		{
			define('E107_DBG_BASIC', true);
		}

		protected function _before()
		{

			try
			{
				$this->dbg = $this->make('e107_db_debug');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107_db_debug object");
			}

		}
/*
		public function testShowIf()
		{

		}

		public function testShow_Log()
		{

		}

		public function testShow_Includes()
		{

		}

		public function testSave()
		{

		}

		public function testShow_DEPRECATED()
		{

		}*/

		public function testLog()
		{

			 // fails , already defined?

			$res = $this->dbg->log('hello world');

			$this->assertTrue($res, 'db_debug->log() method returned false.');


			$result = $this->dbg->Show_Log();

			// var_dump($result);
		}
/*
		public function testLogCode()
		{

		}

		public function testLogDeprecated()
		{

		}

		public function test__construct()
		{

		}

		public function testE107_db_debug()
		{

		}

		public function testShow_SQL_Details()
		{

		}

		public function testShow_SC_BB()
		{

		}

		public function testShow_All()
		{

		}

		public function testCountLabel()
		{

		}

		public function testMark_Time()
		{

		}

		public function testMark_Query()
		{

		}

		public function testShow_Performance()
		{

		}

		public function testShow_PATH()
		{

		}

		public function testDump()
		{

		}
*/



	}
