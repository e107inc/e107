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

		/**
		 * GHSA-72q5-94gw-prww. A missing preference has to read as full
		 * enforcement, because that is what a site upgrading from an older
		 * release will have.
		 */
		public function testTokenCheckModeDefaultsToEnforce()
		{
			$this::assertNull(e107::getConfig()->get('csrf_enforce'));
			$this::assertSame(e_session::TOKEN_CHECK_ENFORCE, e_session::tokenCheckMode());
		}

		/**
		 * check() compares the mode with >=, so the order is load-bearing.
		 */
		public function testTokenCheckModesAreOrdered()
		{
			$this::assertLessThan(e_session::TOKEN_CHECK_LOG, e_session::TOKEN_CHECK_OFF);
			$this::assertLessThan(e_session::TOKEN_CHECK_ENFORCE, e_session::TOKEN_CHECK_LOG);
		}

		/**
		 * The runtime override is the designated seam for a test, and for a
		 * bootstrap that knows better than the stored preference.
		 */
		public function testSetTokenCheckModeOverridesThePreference()
		{
			$previous = e_session::setTokenCheckMode(e_session::TOKEN_CHECK_LOG);

			try
			{
				$this::assertSame(e_session::TOKEN_CHECK_LOG, e_session::tokenCheckMode());

				e_session::setTokenCheckMode(e_session::TOKEN_CHECK_OFF);
				$this::assertSame(e_session::TOKEN_CHECK_OFF, e_session::tokenCheckMode());

				// null hands control back to the preference, which is unset here
				e_session::setTokenCheckMode(null);
				$this::assertSame(e_session::TOKEN_CHECK_ENFORCE, e_session::tokenCheckMode());
			}
			catch(Exception $e)
			{
				e_session::setTokenCheckMode($previous);
				throw $e;
			}

			e_session::setTokenCheckMode($previous);
		}

		/**
		 * setTokenCheckMode() hands back what it displaced, so a caller can put
		 * the previous value back without knowing what it was.
		 */
		public function testSetTokenCheckModeReturnsThePreviousOverride()
		{
			$this::assertNull(e_session::setTokenCheckMode(e_session::TOKEN_CHECK_LOG));
			$this::assertSame(e_session::TOKEN_CHECK_LOG, e_session::setTokenCheckMode(null));
			$this::assertNull(e_session::setTokenCheckMode(null));
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

		public function testClear()
		{
			$this->sess->set('clear/one', 'Test 1');
			$this->sess->set('clear/two', 'Test 2');
			$this->sess->set('clear/three', 'Test 3');

			$this->sess->clear('clear/two');

			$expected = array (
			  'one' => 'Test 1',
			  'three' => 'Test 3',
			);

			$result = $this->sess->get('clear');
			$this->assertSame($expected, $result);

		}



		public function testSetGet()
		{
			$expected = '123456';

			$this->sess->set('whatever', $expected);

			$result = $this->sess->get('whatever');

			$this->assertEquals($expected, $result);


			// Multi-dimensional array support.
			$newsess = e107::getSession('newtest');

			$newsess->set('customer', array('firstname'=>'Fred'));
			$newsess->set('customer/lastname', 'Smith');

			$expected = array (
			  'firstname' => 'Fred',
			  'lastname' => 'Smith',
			);

			$result = $newsess->get('customer');
			$this->assertSame($expected, $result);

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
