<?php

/**
 * class2.php handles a login POST on every request and forwarded $_POST['autologin']
 * into userlogin::login() verbatim. Two of that argument's values are internal
 * force-login modes rather than the remember-me flag the login form posts:
 *
 *   'provider' - GHSA-9gr7-g6pw-5244. checkUserPassword() returns true for it
 *                without reading a password, and the account is matched on its
 *                user_xup column, so a linked account's provider id was enough
 *                to become that account.
 *   'signup'   - the account-activation auto-login. Its token is derived from the
 *                stored password hash, so it is not a bypass on its own, but
 *                reaching it from a request skipped the login CAPTCHA and the
 *                login-name length check.
 *
 * GHSA-m8v8-wc99-3h82 is the other half: user_xup was in userVettingInfo, so a
 * member could pick their own value for the column the provider mode matches on,
 * and that value outlived any password change.
 *
 * Signed-in state is read from usersettings.php, which serves the account's own
 * login name to a member and 301s a guest to the front page.
 */
class ProviderLoginChainCest
{
	const MEMBER_PASS = 'CorrectHorse1';
	const LINKED_XUP  = 'Facebook_100000000000001';
	const CHOSEN_XUP  = 'Facebook_attacker-chosen';

	/** @var int account with a social login already linked, used for the profile-update half */
	private $linkedId;

	/** @var int password-only account */
	private $plainId;

	public function _before(AcceptanceTester $I)
	{
		$this->linkedId = $this->haveMember($I, 'xuplinked', self::LINKED_XUP);
		$this->plainId  = $this->haveMember($I, 'xupplain', '');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
	}

	public function providerModeIsNotSelectableFromALoginPost(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a login that asks for the OAuth provider mode by hand (GHSA-9gr7-g6pw-5244)');

		$this->postLogin($I, array(
			'username'  => self::LINKED_XUP,
			'userpass'  => '',
			'autologin' => 'provider',
		));

		$this->seeSignedOut($I);
	}

	/**
	 * The signup half of the same argument. The token is derived from the stored
	 * password hash, so posting it is not a bypass on its own, but before the fix
	 * it selected the force-login branch and skipped the CAPTCHA gate.
	 */
	public function signupModeIsNotSelectableFromALoginPost(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a login that asks for the post-signup mode by hand');

		$this->postLogin($I, array(
			'username'  => 'xupplain',
			'userpass'  => $this->signupToken($I, $this->plainId),
			'autologin' => 'signup',
		));

		$this->seeSignedOut($I);
	}

	/**
	 * Backwards-compatibility control: an ordinary password login, with the
	 * remember-me flag set, still reaches the same handler and still works.
	 */
	public function anOrdinaryPasswordLoginStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('Sign in with a password and the remember-me flag set');

		$this->postLogin($I, array(
			'username'  => 'xuplinked',
			'userpass'  => self::MEMBER_PASS,
			'autologin' => '1',
		));

		$this->seeSignedInAs($I, 'xuplinked');
	}

	public function userXupIsNotAcceptedFromAProfileUpdate(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a posted user_xup on a profile update (GHSA-m8v8-wc99-3h82)');

		$this->loginAsMember($I, 'xuplinked');
		$this->saveProfile($I, $this->linkedId, array('user_xup' => self::CHOSEN_XUP));

		$I->seeInDatabase('e107_user', array('user_id' => $this->linkedId, 'user_xup' => self::LINKED_XUP));
	}

	/**
	 * Backwards-compatibility control: the same form still saves what it owns.
	 * The rejected field rides along in the same request, so a fix that threw the
	 * whole update away would fail here.
	 */
	public function aProfileUpdateStillSavesTheFieldsItOwns(AcceptanceTester $I)
	{
		$I->wantTo('Save a profile update that also carries a rejected user_xup');

		$this->loginAsMember($I, 'xupplain');
		$this->saveProfile($I, $this->plainId, array(
			'realname' => 'Legitimate Real Name',
			'user_xup' => self::CHOSEN_XUP,
		));

		$I->seeInDatabase('e107_user', array(
			'user_id'    => $this->plainId,
			'user_login' => 'Legitimate Real Name',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param string $xup
	 * @return int user id
	 */
	private function haveMember(AcceptanceTester $I, $name, $xup)
	{
		// Plain md5: UserHandler::getHashType() reads any 32 character hash as
		// PASSWORD_E107_MD5 whatever the site's configured encoding is.
		return $I->haveInDatabase('e107_user', array(
			'user_name' => $name, 'user_loginname' => $name, 'user_login' => $name,
			'user_password' => md5(self::MEMBER_PASS),
			'user_email' => $name.'@example.com',
			'user_join' => time(), 'user_ban' => 0,
			'user_lastvisit' => time() - 86400, 'user_currentvisit' => time() - 86400,
			'user_class' => '253',
			'user_admin' => 0, 'user_perms' => '',
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => $xup,
		));
	}

	/**
	 * Post the login form by hand, so a field the rendered form does not offer
	 * can be carried. Redirects are left unfollowed: a granted login answers 302
	 * and a refused one re-renders login.php, and chasing either only measures
	 * the test client.
	 *
	 * @param AcceptanceTester $I
	 * @param array $fields
	 */
	private function postLogin(AcceptanceTester $I, array $fields)
	{
		$token = $this->tokenOn($I, '/login.php');

		$I->stopFollowingRedirects();
		$I->sendPostRequest('/login.php', $fields + array(
			'userlogin' => 'Sign In',
			'e-token'   => $token,
		));
		$I->startFollowingRedirects();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 */
	private function loginAsMember(AcceptanceTester $I, $name)
	{
		$I->amOnPage('/login.php');
		$I->fillField('username', $name);
		$I->fillField('userpass', self::MEMBER_PASS);
		$I->click('userlogin');
	}

	/**
	 * Post a profile update carrying the fields usersettings.php requires plus
	 * whatever the caller is testing.
	 *
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @param array $extra
	 */
	private function saveProfile(AcceptanceTester $I, $userId, array $extra)
	{
		$I->sendPostRequest('/usersettings.php', $extra + array(
			'_uid'           => $userId,
			'email'          => $I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $userId)),
			'loginname'      => $I->grabFromDatabase('e107_user', 'user_loginname', array('user_id' => $userId)),
			'username'       => $I->grabFromDatabase('e107_user', 'user_name', array('user_id' => $userId)),
			'updatesettings' => 'Save Settings',
			'e-token'        => $this->tokenOn($I, '/usersettings.php'),
		));
	}

	/**
	 * The value e_signup::userVerify() posts as the password when it auto-logs in
	 * a freshly activated account.
	 *
	 * @param AcceptanceTester $I
	 * @param int $userId
	 * @return string
	 */
	private function signupToken(AcceptanceTester $I, $userId)
	{
		$where = array('user_id' => $userId);

		return md5(
			$I->grabFromDatabase('e107_user', 'user_name', $where)
			.$I->grabFromDatabase('e107_user', 'user_password', $where)
			.$I->grabFromDatabase('e107_user', 'user_join', $where)
		);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $page
	 * @return string
	 */
	private function tokenOn(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);

		if (!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $I->grabPageSource(), $m))
		{
			throw new \RuntimeException('No e-token published on '.$page);
		}

		return $m[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 */
	private function seeSignedInAs(AcceptanceTester $I, $name)
	{
		$I->amOnPage('/usersettings.php');
		$I->seeInCurrentUrl('/usersettings.php');
		$I->seeInField('loginname', $name);
	}

	/**
	 * @param AcceptanceTester $I
	 */
	private function seeSignedOut(AcceptanceTester $I)
	{
		$I->amOnPage('/usersettings.php');
		$I->dontSeeInCurrentUrl('/usersettings.php');
	}
}
