<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_admin_logTest extends \Codeception\Test\Unit
	{

		/** @var e_admin_log */
		protected $log;


		protected function _before()
		{

			try
			{
				$this->log = $this->make('e_admin_log');
			}
			catch(Exception $e)
			{
				$this::fail("Couldn't load e_admin_log object");
			}

			$this->log->__construct();
		}
/*
		public function testAddSuccess()
		{

		}

		public function testAddDebug()
		{

		}

		public function testLogError()
		{

		}

		public function testLogSuccess()
		{

		}

		public function testUser_audit()
		{

		}
*/
		public function testAddArray()
		{
			$arr = array('one'=>'two', 'three'=>'four');
			$this->log->addArray($arr)->save('ADD_ARRAY');

			$result = $this->log->getLastLog();

			$this::assertNotEmpty($result['dblog_eventcode']);
			$this::assertSame('ADD_ARRAY', $result['dblog_eventcode']);
			$this::assertSame("Array[!br!]([!br!]    [one] =&gt; two[!br!]    [three] =&gt; four[!br!])[!br!]",$result['dblog_remarks']);

		}

		public function testSetUser()
		{
			$arr = array('one'=>'two', 'three'=>'four');

			$this->log->addArray($arr)->setUser(5, 'testuser')->save('ADD_W_USER');

			$result = $this->log->getLastLog();

			$this::assertNotEmpty($result['dblog_eventcode']);
			$this::assertSame('ADD_W_USER', $result['dblog_eventcode']);
			$this::assertEquals(5, $result['dblog_user_id']);



		}
/*
		public function testLogMessage()
		{

		}

		public function testAddWarning()
		{

		}

		public function testPurge_log_events()
		{

		}

		public function testE_log_event()
		{

		}

		public function testSave()
		{

		}
*/
		public function testLogArrayAll()
		{
			$arr = array('one'=>'test', 'two'=>'testing');
			$this->log->logArrayAll('TEST',$arr);

			$result = $this->log->getLastLog();

			$this::assertNotEmpty($result['dblog_eventcode']);

			$this::assertSame('TEST', $result['dblog_eventcode']);
			$this::assertSame("Array[!br!]([!br!]    [one] =&gt; test[!br!]    [two] =&gt; testing[!br!])[!br!]", $result['dblog_remarks']);

		}
/*
		public function testFlushMessages()
		{

		}

		public function testAddError()
		{

		}

		public function testClear()
		{

		}
*/
		public function testAdd()
		{
			// add to admin_log
			$this->log->add('testAdd Title', "testAdd Message", E_LOG_INFORMATIVE, 'TEST_ADD');
			$result = $this->log->getLastLog();

			$this::assertNotEmpty($result['dblog_eventcode']);

			$this::assertSame('TEST_ADD', $result['dblog_eventcode']);
			$this::assertSame("testAdd Message", $result['dblog_remarks']);
			$this::assertSame('testAdd Title', $result['dblog_title']);

			// add to rolling log (dblog)
			$this->log->rollingLog(true);
			$this->log->add('Rolling Title', "Rolling Message", E_LOG_INFORMATIVE, 'TEST_ROLL',  LOG_TO_ROLLING);
			$result = $this->log->getLastLog(LOG_TO_ROLLING);

			$this::assertNotEmpty($result['dblog_eventcode']);
			$this::assertSame('TEST_ROLL', $result['dblog_eventcode']);
			$this::assertSame("Rolling Message", $result['dblog_remarks']);

			$this->log->rollingLog(false);

		}
/*
		public function testToFile()
		{

		}

		public function testSetCurrentPlugin()
		{

		}

		public function testLogArrayDiffs()
		{

		}

		public function testLog_event()
		{

		}
*/



	}
