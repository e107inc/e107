<?php

namespace Helper;

/** Members the browser can log in as, seeded straight into e107_user and removed by the Db module after the test. */
class MemberFixture extends AppFixture
{
	/**
	 * @param string $name login name, display name and the local part of the address
	 * @param string $password stored as md5, which UserHandler reads as PASSWORD_E107_MD5
	 * @param array $row columns to set differently
	 * @return int user_id
	 */
	public function haveMember($name, $password, array $row = array())
	{
		return $this->db()->haveInDatabase('e107_user', array_merge(array(
			'user_name'         => $name,
			'user_loginname'    => $name,
			'user_login'        => $name,
			'user_password'     => md5($password),
			'user_email'        => $name.'@example.com',
			'user_join'         => time(),
			'user_ban'          => 0,
			'user_lastvisit'    => time() - 86400,
			'user_currentvisit' => time() - 86400,
			'user_class'        => '253',
			'user_admin'        => 0,
			'user_perms'        => '',
			'user_prefs'        => '',
			'user_signature'    => '',
			'user_realm'        => '',
			'user_xup'          => '',
		), $row));
	}

	/**
	 * Log in through the front-end form from a fresh cookie jar, and prove it by reading the member's own settings page.
	 *
	 * @param string $name login name
	 * @param string $password
	 * @return void
	 */
	public function loginAsMember($name, $password)
	{
		$this->app()->resetAllCookies();

		$browser = $this->browser();
		$browser->amOnPage('/login.php');
		$browser->fillField('username', $name);
		$browser->fillField('userpass', $password);
		$browser->click('userlogin');

		$browser->amOnPage('/usersettings.php');
		$browser->seeInSource($name);
	}
}
