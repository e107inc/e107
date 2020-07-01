<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_sessionTest extends \Codeception\Test\Unit
	{
		/** @var e_session  */
		private $sess;

		protected function _before()
		{
			try
			{
				$this->sess = $this->make('e_session');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_session object");
			}

		}

		public function testSetOption()
		{
			$opt = array(
				'lifetime'	 => 3600 ,
				'path'		 => '/',
				'domain'	 => 'test.com',
				'secure'	 => false,
				'httponly'	 => true,
				'_dummy'    => 'not here'
			);

			$this->sess->setOptions($opt);

			$newOpt = $this->sess->getOptions();

			unset($opt['_dummy']);

			$this->assertEquals($opt,$newOpt);


		}

		public function testSetGet()
		{
			$expected = '123456';

			$this->sess->set('whatever', $expected);

			$result = $this->sess->get('whatever');

			$this->assertEquals($expected, $result);


		}
/*
		public function testGetOption()
		{

		}

		public function testSetDefaultSystemConfig()
		{

		}

		public function testGet()
		{

		}

		public function testGetData()
		{

		}

		public function testSet()
		{

		}

		public function testSetData()
		{

		}

		public function testIs()
		{

		}

		public function testHas()
		{

		}

		public function testHasData()
		{

		}

		public function testClear()
		{

		}

		public function testClearData()
		{

		}

		public function testSetConfig()
		{

		}

		public function testGetNamespaceKey()
		{

		}

		public function testSetOptions()
		{

		}

		public function testInit()
		{

		}

		public function testStart()
		{

		}

		public function testSetSessionId()
		{

		}

		public function testGetSessionId()
		{

		}

		public function testGetSaveMethod()
		{

		}

		public function testSetSessionName()
		{

		}

		public function testGetSessionName()
		{

		}

		public function testValidateSessionCookie()
		{

		}

		public function testCookieDelete()
		{

		}

		public function testValidate()
		{

		}

		public function testGetValidateData()
		{

		}

		public function testGetFormToken()
		{

		}

		public function testCheckFormToken()
		{

		}

		public function testClose()
		{

		}

		public function testEnd()
		{

		}

		public function testDestroy()
		{

		}

		public function testReplaceRegistry()
		{

		}*/
	}
