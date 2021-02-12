<?php


	class userloginTest extends \Codeception\Test\Unit
	{

		/** @var userlogin */
		protected $lg;

		protected function _before()
		{

			try
			{
				/** @var userlogin lg */
				$this->lg = $this->make('userlogin');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

			$this->lg->__construct();

		}
/*
		public function testGetUserData()
		{

		}

		public function testGetLookupQuery()
		{

		}
*/
		public function testLogin()
		{
			$tests = array(
				0 => array(
					'username'      => 'invalid_user',
					'userpass'      => '',
					'autologin'     => 0,
					'noredirect'    => true, 
					'response'      => '',
					'_expected_'    => false
				),
				1 => array(
					'username'      => 'e107',
					'userpass'      => 'e107',
					'autologin'     => 0,
					'noredirect'    => true,
					'response'      => '',
					'_expected_'    => true
				),
			);
			
			foreach($tests as $var)
			{
				$result = $this->lg->login($var['username'], $var['userpass'], $var['autologin'], $var['response'], $var['noredirect']);
				$this->assertSame($var['_expected_'], $result);
			}

		}

		public function testLoginNewUser()
		{

				e107::getConfig()->set('user_new_period', 3)->save(false,true); // set new user period to 3 days.

				$insert = array(
					'user_name'			=> 'newuser',
					'user_email'		=> 'newuser@newuser.com',
					'user_loginname'	=> 'newuser',
					'user_password'		=> md5('newuser'),
					'user_login'		=> 'newuser',
					'user_join'			=> strtotime('5 days ago'),
					'user_class'        => e_UC_NEWUSER.',3,'.e_UC_MODS,

				);

				$newid = e107::getDb()->insert('user',$insert);
				$this->assertNotEmpty($newid);

				$result = $this->lg->login('newuser', 'newuser', 0, '', true);
				$this->assertTrue($result);

				$class = e107::getDb()->retrieve('user', 'user_class', "user_id = ".$newid);

				$this->assertSame("3,248", $class); // new user class was removed!


		}



		public function testErrorMessages()
		{
			$result = $this->lg->test();

			foreach($result as $var)
			{
				$this->assertNotEmpty($var);
			}

		}


	}
