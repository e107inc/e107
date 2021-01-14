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

		public function testErrorMessages()
		{
			$result = $this->lg->test();

			foreach($result as $var)
			{
				$this->assertNotEmpty($var);
			}

		}


	}
