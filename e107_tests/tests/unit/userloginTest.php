<?php


	class userloginTest extends \Codeception\Test\Unit
	{
		use \Test\BootedCli;
		use \Test\CorePrefs;

		/** A child's php arguments that hold its output back, so the notices its boot prints send no headers and its session settings stay changeable. */
		const BUFFERED = '-d output_buffering=On';

		/** The session a sign-in in a child runs as. */
		const SESSION_ID = 'userlogintestsession0123456789ab';

		/** A session the same account signed in with before. */
		const EARLIER_ID = 'userlogintestearlier0123456789ab';

		/** @var string|null online_user_id seeded by a test */
		private $onlineUserId;

		/** @var string|null session directory a test made */
		private $sessionDirectory;

		/** @var mixed */
		private $saveMethodWas;

		/** @var bool */
		private $saveMethodChanged = false;

		protected function _after()
		{
			$db = e107::getDb();

			if(null !== $this->onlineUserId)
			{
				$db->delete('online', "online_user_id='".$db->escape($this->onlineUserId)."'");
			}

			if($this->saveMethodChanged)
			{
				$config = e107::getConfig();
				null === $this->saveMethodWas ? $config->remove('session_save_method') : $config->set('session_save_method', $this->saveMethodWas);
				$config->save(false, true, false);
			}

			$db->delete('session', "session_id IN ('".e_session_db::storageKey(self::SESSION_ID)."', '".e_session_db::storageKey(self::EARLIER_ID)."')");

			if(null !== $this->sessionDirectory)
			{
				foreach((array) glob($this->sessionDirectory.'/*') as $file)
				{
					unlink($file);
				}

				rmdir($this->sessionDirectory);
			}
		}

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

		/**
		 * #6302: with file sessions and Track Online on, an online row used to refuse the sign-in outright, and every refusal fed the failed-login autoban counter.
		 */
		public function testDisallowMultiLoginAdmitsAnAccountAlreadyOnline()
		{
			$admin = $this->fixtureAdmin();
			$this->onlineUserId = $admin['user_id'].'.'.$admin['user_name'];

			e107::getDb()->insert('online', array(
				'online_timestamp' => time(),
				'online_user_id'   => $this->onlineUserId,
				'online_ip'        => e107::getIPHandler()->ipEncode('203.0.113.9'),
				'online_location'  => '',
			));

			$restore = $this->withCorePrefs(array('disallowMultiLogin' => 1, 'session_save_method' => 'files', 'track_online' => 1));

			try
			{
				$this->assertTrue($this->lg->login('e107', 'e107', 0, '', true));
			}
			finally
			{
				$restore();
			}
		}

		/**
		 * #6302: on file sessions the preference did nothing, or refused the sign-in; now the sign-in ends the session the account signed in with before.
		 */
		public function testDisallowMultiLoginEndsTheAccountsEarlierFileSession()
		{
			$dir = $this->sessionDirectory = sys_get_temp_dir().'/e107-multilogin-'.getmypid().'-'.mt_rand();
			mkdir($dir, 0700);

			$php = "session_write_close(); ini_set('session.use_cookies', '0'); ";
			$php .= $this->signInInChild(self::EARLIER_ID)."session_write_close(); ";
			$php .= $this->signInInChild(self::SESSION_ID);
			$php .= "fwrite(STDERR, '@@'.var_export(\$signedIn, true).'@@'); session_destroy();";
			list($output) = $this->runInBootedCli($php, self::BUFFERED.' -d session.save_path='.escapeshellarg($dir));

			$this->assertStringContainsString('@@true@@', implode("\n", $output));
			$this->assertFalse(file_exists($dir.'/sess_'.self::EARLIER_ID), 'the earlier sign-in is signed out');
		}

		/**
		 * Guards the database store, which ended the earlier session before this change as well.
		 */
		public function testDisallowMultiLoginEndsTheAccountsEarlierDatabaseSession()
		{
			$config = e107::getConfig();
			$this->saveMethodWas = $config->get('session_save_method');
			$this->saveMethodChanged = true;
			$config->set('session_save_method', 'db')->save(false, true, false);

			e107::getDb()->insert('session', array(
				'session_id'      => e_session_db::storageKey(self::EARLIER_ID),
				'session_expires' => time() + 600,
				'session_user'    => 1,
				'session_data'    => '',
			));

			$php = "session_write_close(); ini_set('session.use_cookies', '0'); ";
			$php .= $this->signInInChild(self::SESSION_ID);
			$php .= "fwrite(STDERR, '@@'.var_export(\$signedIn, true).'@@'); ";
			list($output) = $this->runInBootedCli($php, self::BUFFERED);

			$db = e107::getDb();
			$this->assertStringContainsString('@@true@@', implode("\n", $output));
			$this->assertSame(0, (int) $db->count('session', '(*)', "session_id='".e_session_db::storageKey(self::EARLIER_ID)."'"));
			$this->assertSame(1, (int) $db->retrieve('session', 'session_user', "session_id='".e_session_db::storageKey(self::SESSION_ID)."'"), 'the session that signed in is stamped with its account');
		}

		/**
		 * PHP for a booted child: start the session as $sessionId, mark it signed in as the admin the way a web sign-in leaves it, and sign in with "Disallow multiple logins" on; leaves the result in $signedIn.
		 *
		 * @param string $sessionId
		 * @return string
		 */
		private function signInInChild($sessionId)
		{
			$php = "session_id('".$sessionId."'); session_start(); ";
			$php .= "e107::getConfig()->set('disallowMultiLogin', 1)->set('track_online', 0); ";
			$php .= "\$_SESSION[e_COOKIE] = '1.".md5('signed-in')."'; ";
			$php .= "require_once(e_HANDLER.'login.php'); \$lg = new userlogin(); ";
			$php .= "\$signedIn = \$lg->login('e107', 'e107', 0, '', true); ";

			return $php;
		}

		/**
		 * @return array user_id and user_name of the installed admin
		 */
		private function fixtureAdmin()
		{
			$user = e107::getDb()->retrieve('user', 'user_id, user_name', "user_loginname='e107'");

			$this->assertNotEmpty($user);

			return $user;
		}


	}
