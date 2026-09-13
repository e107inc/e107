<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_user_modelTest extends \Test\Unit
	{

		/** @var e_user_model */
		protected $usr;

		protected function _before()
		{

			try
			{
				$this->usr = $this->make('e_user_model');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_user_model object");
			}

			$this->usr->load(1); // load user_id  = 1.

		}


		public function testGetClassList()
		{
			$result = $this->usr->getClassList();
			$this->assertContains(e_UC_MEMBER, $result);
			$this->assertContains(e_UC_ADMIN, $result);
			$this->assertContains(e_UC_MAINADMIN, $result);

			$result = $this->usr->getClassList(true);
			$result = array_map('intval', explode(',', $result));
			$this->assertContains(e_UC_MEMBER, $result);
			$this->assertContains(e_UC_ADMIN, $result);
			$this->assertContains(e_UC_MAINADMIN, $result);
		}


		public function testGetName()
		{
			$result = $this->usr->getName();
			$this->assertEquals(\Helper\AdminLogin::ADMIN_USER, $result);
		}

		public function testRandomKey()
		{
			$obj = $this->usr;

			$result = $obj::randomKey();

			$this->assertEquals(32,strlen($result));

		}

		public function testIsBot()
		{
			$result = $this->usr->isBot();
			$this->assertFalse($result);
		}
		/**
		 * @see https://github.com/e107inc/e107/issues/4236
		 */
		public function testUserLoginWrongCredentialsNotUser()
		{
			$user = e107::getUser();
			$user->login(\Helper\AdminLogin::ADMIN_USER, "DefinitelyTheWrongPassword");

			$this->assertFalse($user->isUser());
			$this->assertEmpty($user->getData());
		}

		public function testUserLoginFailureDoesNotTriggerUserLoginEvent()
		{
			$originalEventHandler = e107::getRegistry('core/e107/singleton/e107_event');
			$mockEventHandler = $this->createMock(e107_event::class);
			$mockEventHandler->expects($this->never())->method('trigger');
			e107::setRegistry('core/e107/singleton/e107_event', $mockEventHandler);

			try
			{
				$user = e107::getUser();
				$user->login(\Helper\AdminLogin::ADMIN_USER, "DefinitelyTheWrongPassword");

				e107::getEvent();
			}
			finally
			{
				e107::setRegistry('core/e107/singleton/e107_event', $originalEventHandler);
			}
		}

		/**
		 * load() registers the model under core/e107/user/<id> so that later
		 * lookups reuse it. destroy() has to release that entry, or the
		 * emptied model is handed to every caller for the rest of the request.
		 */
		public function testDestroyReleasesTheRegisteredUser()
		{
			$uid = 1;
			e107::setRegistry('core/e107/user/'.$uid, null);

			$user = e107::getSystemUser($uid, false);
			$this->assertEquals($uid, $user->getId(), 'Precondition: user 1 loads.');
			$this->assertNotNull(e107::getRegistry('core/e107/user/'.$uid),
				'Precondition: loading registers the model.');

			$user->destroy();

			$this->assertNull(e107::getRegistry('core/e107/user/'.$uid),
				'destroy() must release the model it registered.');

			$fresh = e107::getSystemUser($uid, false);
			$this->assertEquals($uid, $fresh->getId(),
				'The next lookup must load the user again, not hand back the emptied model.');

			e107::setRegistry('core/e107/user/'.$uid, null);
		}

		/**
		 * A confirmation value that is not a string is refused before it is cast.
		 *
		 * Every caller passes the request field straight through, so a field
		 * posted as an array reached the cast and raised a warning where the
		 * comparison before it was silent.
		 */
		public function testCheckAdminPwchangeTokenRefusesAnArrayWithoutAWarning()
		{
			$raised = array();

			set_error_handler(function($errno, $errstr) use (&$raised) {
				$raised[] = $errstr;

				return true;
			});

			try
			{
				$accepted = $this->usr->checkAdminPwchangeToken(array());
			}
			finally
			{
				restore_error_handler();
			}

			$this->assertFalse($accepted, 'An array was accepted as the confirmation value.');
			$this->assertSame(array(), $raised,
				'checkAdminPwchangeToken() raised '.implode('; ', $raised).' for a value posted as an '
				.'array, so the cast ran before the type was checked.');
		}
	}
