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

		/** The password the fixture account is created with. */
		const RESEND_PASSWORD = 'resend-fixture-password';

		/** @var e_signup */
		protected $sup;

		/** @var array preference values as they were before a test changed them */
		protected $restorePrefs = array();

		protected function _before()
		{
			require_once(e_HANDLER."user_handler.php");
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

		protected function _after()
		{
			if(empty($this->restorePrefs))
			{
				return;
			}

			$config = e107::getConfig();

			foreach($this->restorePrefs as $key => $value)
			{
				$config->set($key, $value);
			}

			$config->save(false, true);

			$this->restorePrefs = array();
		}


/*		public function testRenderEmailPreview()
		{

		}

		public function test__construct()
		{

		}

		public function testRender_after_signup()
		{

		}*/

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


		/**
		 * The resend form checked the password itself, so a guess reached none
		 * of the counting userlogin does for the login form.
		 */
		public function testAWrongResendPasswordGoesThroughTheLoginFailureFunnel()
		{
			$name = 'resendvictim';
			$email = $name.'@example.com';

			$this->haveUnactivatedUser($name, $email);
			$this->havePrefs(array('user_reg_veri' => 1, 'roll_log_active' => 1));

			$before = $this->failureNotesFor($name);

			$out = $this->runInBootedCli($this->resendRequest($name, 'not-the-password', 'moved-'.$email));

			$this->assertNotFalse(strpos($out, LAN_INCORRECT_PASSWORD),
				'the resend form never reached its wrong-password branch: '.$out);

			$this->assertSame($before + 1, $this->failureNotesFor($name),
				'the wrong password was not put through the failure funnel userlogin counts');

			$this->assertSame($email, e107::getDb()->retrieve('user', 'user_email', "user_loginname = '".$name."'"),
				'the wrong password still moved the account to another address');
		}


		/**
		 * @param string $name
		 * @param string $email
		 * @return int user id
		 */
		private function haveUnactivatedUser($name, $email)
		{
			$id = e107::getDb()->insert('user', array(
				'user_name'      => $name,
				'user_loginname' => $name,
				'user_login'     => $name,
				'user_email'     => $email,
				'user_password'  => md5(self::RESEND_PASSWORD),
				'user_join'      => time(),
				'user_ban'       => USER_REGISTERED_NOT_VALIDATED,
				'user_class'     => '',
				'user_sess'      => 'resend-fixture-session',
			));

			$this->assertNotEmpty($id, 'could not write the account this test needs');

			return (int) $id;
		}


		/**
		 * @param array $prefs set for this test and restored after it
		 * @return void
		 */
		private function havePrefs($prefs)
		{
			$config = e107::getConfig();

			foreach($prefs as $key => $value)
			{
				$this->restorePrefs[$key] = e107::getPref($key);
				$config->set($key, $value);
			}

			$config->save(false, true);
		}


		/**
		 * @param string $name
		 * @return int rolling-log notes userlogin has written about $name
		 */
		private function failureNotesFor($name)
		{
			$sql = e107::getDb();
			$sql->select('dblog', 'dblog_remarks', "dblog_eventcode = 'LOGIN'");

			$found = 0;

			while($row = $sql->fetch())
			{
				if(strpos($row['dblog_remarks'], $name) !== false)
				{
					$found++;
				}
			}

			return $found;
		}


		/**
		 * @param string $name what the visitor typed in the identifier field
		 * @param string $password what the visitor typed in the password field
		 * @param string $newEmail the address the visitor asks the account to be moved to
		 * @return string PHP that posts the resend form, for a booted CLI request
		 */
		private function resendRequest($name, $password, $newEmail)
		{
			$post = array(
				'submit_resend'   => 1,
				'resend_email'    => $name,
				'resend_newemail' => $newEmail,
				'resend_password' => $password,
			);

			$php  = '$userMethods = e107::getUserSession(); ';
			$php .= '$_POST = '.var_export($post, true).'; ';
			$php .= "require_once('".addslashes(APP_PATH.'/e107_handlers/e_signup_class.php')."'); ";
			$php .= '$signup = new e_signup(); $signup->run("resend");';

			return $php;
		}


		/**
		 * Runs $php in a subprocess that has booted class2.php in CLI mode.
		 *
		 * @param string $php
		 * @return string stdout and stderr interleaved
		 */
		private function runInBootedCli($php)
		{
			$boot  = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
			$boot .= "\$_E107 = array('cli' => true); ";
			$boot .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";

			$output = array();
			$status = 0;
			exec(sprintf('timeout 60 php -r %s 2>&1', escapeshellarg($boot.$php)), $output, $status);

			$this->assertNotSame(124, $status, 'the subprocess wedged, so nothing was measured');

			return implode("\n", $output);
		}



	}
