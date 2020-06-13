<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_signup_classTest extends \Codeception\Test\Unit
	{

		/** @var e_signup */
		protected $sup;

		protected function _before()
		{
			require_once(e_HANDLER."e_signup_class.php");
			try
			{
				$this->sup = $this->make('e_signup');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_signup object");
			}

			$this->sup->__construct();

		}


		public function testRenderEmailPreview()
		{

		}

		public function test__construct()
		{

		}

		public function testRender_after_signup()
		{

		}

		public function testProcessActivationLink()
		{
			$sess = '1234567890';
			$insert = array(
				'user_id'   => 0,
				'user_name' => 'e_signup_class',
				'user_loginname'    => 'e_signup',
				'user_email'        => 'test@test.com',
				'user_sess' => $sess,
				'user_ban' => 1,
			);

			$num = e107::getDb()->insert('user', $insert);

			$this->assertGreaterThan(0,$num);



			$result = $this->sup->processActivationLink('activate.'.$num.'.'.$sess);
			$this->assertEquals('success', $result);

			$result = $this->sup->processActivationLink('activate.'.$num.'.'.$sess);
			$this->assertEquals('exists', $result);

			$result = $this->sup->processActivationLink('activate.999.'.$sess);
			$this->assertEquals('invalid', $result);

			$this->sup->processActivationLink('activate.999.'.$sess.".fr");
			$this->assertEquals("Privacy Policy", LAN_SIGNUP_122, "Language file failed to load.");


		}



	}
