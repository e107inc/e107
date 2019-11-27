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
				$this->assertTrue(false, "Couldn't load e_admin_log object");
			}

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

		public function testAddArray()
		{

		}

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

		public function testLogArrayAll()
		{

		}

		public function testFlushMessages()
		{

		}

		public function testAddError()
		{

		}

		public function testClear()
		{

		}

		public function testAdd()
		{

		}

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
