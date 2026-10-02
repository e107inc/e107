<?php

/**
 * CHAP login is discontinued (#6653): with password_CHAP left on from before, no login form sends CHAP hooks and the admin logs in with the password as typed.
 */
class ChapLoginDiscontinuedCest
{
	/** "CHAP, plaintext fallback", the setting that left the admin login unable to submit (#6652). */
	const CHAP_WITH_FALLBACK = 1;

	/** @var string */
	private $chapWas = '';

	public function _before(AcceptanceTester $I)
	{
		$this->chapWas = $I->haveSitePref('password_CHAP', self::CHAP_WITH_FALLBACK);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('password_CHAP', $this->chapWas === '' ? null : $this->chapWas);
	}

	public function theAdminLoginPageSendsNoChapHooks(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->seeElement('#admin-login');
		$this->dontSeeChapHooks($I);
	}

	public function theSiteLoginPageSendsNoChapHooks(AcceptanceTester $I)
	{
		$I->amOnPage('/login.php');
		$I->seeElement('#login-page');
		$this->dontSeeChapHooks($I);
	}

	/** The bundled theme's signin dropdown renders a password field here for a guest. */
	public function theFrontPageSendsNoChapHooks(AcceptanceTester $I)
	{
		$I->amOnPage('/');
		$I->seeElement("input[name='userpass']");
		$this->dontSeeChapHooks($I);
	}

	public function theAdminLogsInWithThePasswordAsTyped(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('authsubmit');
		$I->see("Admin's Control Panel");
	}

	private function dontSeeChapHooks(AcceptanceTester $I)
	{
		$I->dontSeeInSource('chap_script.js');
		$I->dontSeeInSource('getChallenge');
		$I->dontSeeInSource('hashLoginPassword');
		$I->dontSeeElement('input[name=hashchallenge]');
	}
}
