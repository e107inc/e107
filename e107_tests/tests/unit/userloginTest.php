<?php


	class userloginTest extends \Test\Unit
	{

		/** Fixture password; e107 reads any 32-character hash as PASSWORD_E107_MD5. */
		const FIXTURE_PASS = 'never-used-by-these-tests';

		/** user_join that makes the signup token an md5 of the form 0e[0-9]{30}. */
		const MAGIC_JOIN = 18194810;

		/** Session row the multi-login eviction is expected to delete. */
		const OLDER_SESSION_ID = 'older-session-6302';

		/** @var userlogin */
		protected $lg;

		/** @var string|null online_user_id seeded by a test, removed in _after whether or not the test passed */
		private $onlineUserId;

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

		protected function _after()
		{
			$db = e107::getDb();

			$db->createQueryBuilder()->delete('session')->where('session_id', self::OLDER_SESSION_ID)->execute();

			if($this->onlineUserId !== null)
			{
				$db->createQueryBuilder()->delete('online')->where('online_user_id', $this->onlineUserId)->execute();
				$this->onlineUserId = null;
			}
		}

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
					'username'      => \Helper\AdminLogin::ADMIN_USER,
					'userpass'      => \Helper\AdminLogin::ADMIN_PASS,
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
			$this->withPrefs(array('user_new_period' => 3), function() {

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

			});
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
		 * #6302: the eviction was gated on the save method pref reading 'db', so
		 * under every other setting the older session outlived the newer login.
		 */
		public function testDisallowMultiLoginDropsTheOlderSessionWhateverTheSaveMethod()
		{
			$user = $this->fixtureAdmin();
			$db = e107::getDb();

			$db->createQueryBuilder()->insert('session')->values(array(
				'session_id'      => self::OLDER_SESSION_ID,
				'session_expires' => time() + 3600,
				'session_user'    => $user['user_id'],
				'session_data'    => '',
			))->execute();

			$prefs = array('disallowMultiLogin' => 1, 'session_save_method' => 'files', 'track_online' => 0);

			$this->withPrefs($prefs, function() use ($db) {
				$this->assertTrue($this->lg->login(\Helper\AdminLogin::ADMIN_USER, \Helper\AdminLogin::ADMIN_PASS, 0, '', true));

				$survivors = $db->createQueryBuilder()->from('session')
					->where('session_id', self::OLDER_SESSION_ID)->count();

				$this->assertSame(0, $survivors);
			});
		}

		/**
		 * #6302: an online row refused the login outright instead, and every
		 * refusal fed the failed-login autoban counter.
		 */
		public function testDisallowMultiLoginAdmitsAnAlreadyOnlineUser()
		{
			$user = $this->fixtureAdmin();
			$onlineUserId = $this->onlineUserId = $user['user_id'].'.'.$user['user_name'];
			$db = e107::getDb();

			$db->createQueryBuilder()->insert('online')->values(array(
				'online_timestamp' => time(),
				'online_user_id'   => $onlineUserId,
				'online_ip'        => e107::getIpHandler()->ipEncode('203.0.113.9'),
				'online_location'  => '',
			))->execute();

			$prefs = array('disallowMultiLogin' => 1, 'session_save_method' => 'files', 'track_online' => 1);

			$this->withPrefs($prefs, function() use ($db, $onlineUserId) {
				$this->assertTrue($this->lg->login(\Helper\AdminLogin::ADMIN_USER, \Helper\AdminLogin::ADMIN_PASS, 0, '', true));

				$survivors = $db->createQueryBuilder()->from('online')
					->where('online_user_id', $onlineUserId)->count();

				$this->assertSame(0, $survivors);
			});
		}

		/**
		 * @return array user_id and user_name of the installed admin
		 */
		private function fixtureAdmin()
		{
			$user = e107::getDb()->createQueryBuilder()
				->select('user_id', 'user_name')
				->from('user')
				->where('user_loginname', \Helper\AdminLogin::ADMIN_USER)
				->fetchRow();

			$this->assertNotEmpty($user);

			return $user;
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
