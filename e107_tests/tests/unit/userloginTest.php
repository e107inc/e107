<?php


	class userloginTest extends \Codeception\Test\Unit
	{

		/** Fixture password; e107 reads any 32-character hash as PASSWORD_E107_MD5. */
		const FIXTURE_PASS = 'never-used-by-these-tests';

		/** user_join that makes the signup token an md5 of the form 0e[0-9]{30}. */
		const MAGIC_JOIN = 18194810;

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



		/**
		 * GHSA-9gr7-g6pw-5244: 'provider' arrived as the $autologin argument, which
		 * class2.php fills from $_POST, and checkUserPassword() returns true for it
		 * without looking at a password.
		 */
		public function testProviderModeIsNotSelectableThroughLogin()
		{
			$xup = 'Facebook_100000000000001';
			$this->haveProviderUser('xupvictim', $xup);

			$lg = new userlogin();

			$this->assertFalse($lg->login($xup, '', 'provider', '', true));
			$this->assertSame(array(), $lg->getUserData());
		}

		/**
		 * The mode itself still works for the OAuth callback that owns it.
		 */
		public function testLoginProviderAdmitsTheLinkedAccount()
		{
			$xup = 'Facebook_100000000000002';
			$id = $this->haveProviderUser('xupmember', $xup);

			$lg = new userlogin();

			$this->assertTrue($lg->loginProvider($xup));

			$data = $lg->getUserData();
			$this->assertSame($id, (int) $data['user_id']);
		}

		/**
		 * The flag the mode now travels on must not survive the call that set it,
		 * or the next login on the same instance inherits a password-free session.
		 */
		public function testProviderFlagDoesNotLeakIntoTheNextLogin()
		{
			$xup = 'Facebook_100000000000003';
			$this->haveProviderUser('xupleak', $xup);

			$lg = new userlogin();
			$lg->loginProvider('Facebook_no-such-identifier');

			$this->assertFalse($lg->login($xup, '', 0, '', true));
		}

		/**
		 * GHSA-c33m item 3: the force-login token was compared with !=, and PHP
		 * compares two numeric strings numerically, so a stored token of the form
		 * 0e[0-9]{30} matched any password that also reads as zero. "0e0" is not
		 * empty(), so the blank-field guard does not catch it either.
		 */
		public function testSignupTokenIsNotMatchedByANumericLookalike()
		{
			$this->haveProviderUser('xupmagic', '', self::MAGIC_JOIN);

			$token = md5('xupmagic'.md5(self::FIXTURE_PASS).self::MAGIC_JOIN);
			$this->assertTrue($token == '0e0', 'fixture no longer yields a 0e-form token');

			$lg = new userlogin();

			$this->assertFalse($lg->login('xupmagic', '0e0', 'signup', '', true));
		}

		/**
		 * @param string $name
		 * @param string $xup
		 * @param int|null $join defaults to now
		 * @return int user id
		 */
		private function haveProviderUser($name, $xup, $join = null)
		{
			$id = e107::getDb()->insert('user', array(
				'user_name'      => $name,
				'user_loginname' => $name,
				'user_login'     => $name,
				'user_email'     => $name.'@example.com',
				'user_password'  => md5(self::FIXTURE_PASS),
				'user_join'      => $join === null ? time() : $join,
				'user_ban'       => USER_VALIDATED,
				'user_class'     => '',
				'user_xup'       => $xup,
			));

			$this->assertNotEmpty($id);

			return (int) $id;
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
