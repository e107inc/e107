<?php

/**
 * Discussion #6005: the post-login destination captured when a visitor is bounced off
 * an administration page must not be handed to whoever logs in next on that browser.
 *
 * The bounce writes a sealed destination cookie that outlives the request, and the
 * front-end login consumed it without asking whether the account that just
 * authenticated may go there, so an ordinary member landed on the admin login form
 * while being logged in perfectly well. Alex-e107nl met it after logging out of an
 * admin account and back in as a member on a site whose members are imported by
 * alt_auth; the alt_auth part is incidental.
 */
class LoginDestinationAuthzCest
{
	const MEMBER      = 'destmember';
	const MEMBER_PASS = 'x107member';

	/** @var string an admin page no ordinary member may see */
	private $adminPage = '/e107_admin/users.php';

	public function _before(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_user', array(
			'user_name'      => self::MEMBER,
			'user_loginname' => self::MEMBER,
			'user_login'     => self::MEMBER,
			'user_password'  => md5(self::MEMBER_PASS),
			'user_email'     => self::MEMBER . '@example.com',
			'user_join'      => time(),
			'user_ban'       => 0,
			'user_class'     => '',
			'user_admin'     => 0,
			'user_perms'     => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
			'user_xup'       => '',
		));
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function doesNotSendAMemberToAnAdminOnlyDestination(AcceptanceTester $I)
	{
		$I->wantTo('Not send an ordinary member to an admin destination captured before they logged in');

		$this->bounceOffTheAdminPage($I);
		$this->logInOnTheFrontEnd($I, self::MEMBER, self::MEMBER_PASS);

		$I->dontSeeInCurrentUrl($this->adminPage);
		$I->dontSeeElement('input', array('name' => 'authname'));
	}

	/**
	 * The other half of the same rule: an administrator asked for that page and must
	 * still be returned to it, which is the behaviour issue #5694 exists to provide.
	 */
	public function stillReturnsAnAdminToAnAdminOnlyDestination(AcceptanceTester $I)
	{
		$I->wantTo('Return an administrator to the admin destination captured before they logged in');

		$this->bounceOffTheAdminPage($I);
		$this->logInOnTheFrontEnd($I, \Helper\AdminLogin::ADMIN_USER, \Helper\AdminLogin::ADMIN_PASS);

		$I->seeInCurrentUrl($this->adminPage);
		$I->dontSeeElement('input', array('name' => 'authname'));
	}

	/**
	 * The sequence as reported: an administrator signs out from inside the admin area,
	 * which returns them to the page they were on and captures it on the way through
	 * the bounce, and the next person to log in on that browser inherits it.
	 */
	public function doesNotInheritTheDestinationOfALoggedOutAdmin(AcceptanceTester $I)
	{
		$I->wantTo('Not hand a logged-out administrator\'s destination to the next member who logs in');

		$I->loginAsAdmin();
		$token = $I->grabFreshAdminToken($this->adminPage);
		$I->amOnPage($this->adminPage . '?logout&e-token=' . $token);

		$I->seeElement('input', array('name' => 'authname'));

		$this->logInOnTheFrontEnd($I, self::MEMBER, self::MEMBER_PASS);

		$I->dontSeeInCurrentUrl($this->adminPage);
		$I->dontSeeElement('input', array('name' => 'authname'));
	}

	/**
	 * The third seam that spends a destination: login.php redirects a visitor who is
	 * already logged in. The destination outlives the login that refused it, so this
	 * is where a member meets it again on their next visit to the login page.
	 */
	public function doesNotSpendAnAdminOnlyDestinationOnTheLoginPage(AcceptanceTester $I)
	{
		$I->wantTo('Not spend an admin destination on a member who revisits the login page');

		$this->bounceOffTheAdminPage($I);
		$this->logInOnTheFrontEnd($I, self::MEMBER, self::MEMBER_PASS);

		$I->amOnPage('/login.php');

		$I->dontSeeInCurrentUrl($this->adminPage);
		$I->dontSeeElement('input', array('name' => 'authname'));
	}

	/**
	 * Not every admin page bounces. credits.php, docs.php and message.php require
	 * auth.php directly with no getperms() gate ahead of them, so the admin login
	 * form is rendered at the URL itself and its own hidden field is the only place
	 * the destination exists. A member who signs in there must not be sent back to
	 * it either.
	 */
	public function doesNotSendAMemberBackToAnUngatedAdminEntryPoint(AcceptanceTester $I)
	{
		$I->wantTo('Not return a member to an admin page that renders the login form in place');

		$I->amOnPage('/e107_admin/credits.php');
		$I->seeElement('input', array('name' => 'authname'));
		$I->seeElement('input', array('name' => '__logindest'));

		$I->fillField('authname', self::MEMBER);
		$I->fillField('authpass', self::MEMBER_PASS);
		$I->click('authsubmit');

		$I->dontSeeInCurrentUrl('/e107_admin/credits.php');
	}

	/**
	 * Ask for an admin page as a guest. The request is refused and the page is
	 * remembered as where to return to after a successful admin login.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function bounceOffTheAdminPage(AcceptanceTester $I)
	{
		$I->amOnPage($this->adminPage);
		$I->seeElement('input', array('name' => 'authname'));
		$I->seeSiteCookie('e107_logindest');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $user
	 * @param string $pass
	 * @return void
	 */
	private function logInOnTheFrontEnd(AcceptanceTester $I, $user, $pass)
	{
		$I->amOnPage('/login.php');
		$I->fillField('username', $user);
		$I->fillField('userpass', $pass);
		$I->click('userlogin');
	}
}
