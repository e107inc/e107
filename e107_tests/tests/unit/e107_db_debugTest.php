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

            $this->dbg->active(true);
		}
/*
		public function testShowIf()
		{

		}*/

		public function testShow_Log()
		{
            $result = $this->dbg->Show_Log();
            $this->assertEmpty($result);
		}

		public function testShow_Includes()
		{
            $result = $this->dbg->Show_Includes();
            $this->assertEmpty($result);
		}

		public function testSave()
		{

		}

		public function testShow_DEPRECATED()
		{
            $result = $this->dbg->Show_DEPRECATED();
            $this->assertEmpty($result);
		}

		public function testLog()
		{
			$res = $this->dbg->log('hello world');
			$this->assertTrue($res, 'db_debug->log() method returned false.');


			$result = $this->dbg->Show_Log();
			$this->assertStringContainsString('e107_db_debugTest->testLog()',$result);

		}

		public function testShow_Performance()
		{
            $result = $this->dbg->Show_Performance();
            $this->assertEmpty($result);

		}

		public function testShow_PATH()
		{
            $result = $this->dbg->Show_PATH();
            $this->assertEmpty($result);
		}

		public function testShow_SQL_Details()
		{
            $result = $this->dbg->Show_SQL_Details();
            $this->assertEmpty($result);
		}

		public function testShow_SC_BB()
		{
            $result = $this->dbg->Show_SC_BB();
            $this->assertEmpty($result);
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



		public function testDump()
		{

		}
*/



	}
