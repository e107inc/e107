<?php

/**
 * Cookie-mode authentication is gone and the 'Remember Me' control with it.
 *
 * A site installed under e107 2.0 to 2.2 seeded user_tracking as 'cookie' and
 * nothing ever migrated it, so the population that upgrades into this change is
 * carrying browsers that hold "<user_id>.<md5 of the stored password hash>" in
 * a cookie. Two things have to be true for them: that cookie must no longer
 * sign anybody in, and the sites must still be usable afterwards.
 *
 * The probe puts the site in cookie mode and mints the main admin's own legacy
 * cookie through e107's cookie() helper, which is what makeUserCookie() used to
 * do, so the browser here is in the state an upgraded site's visitor is in.
 */
class CookieModeRemovedCest
{
	const PROBE_FILE = 'e107_tests_cookie_mode_probe.php';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage('/'.self::PROBE_FILE.'?act=setup');
		$I->seeInSource('PROBE_OK');
		$I->seeInSource('TOKEN=1');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=teardown');
		$I->seeInSource('PROBE_OK');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function aLegacyAuthCookieSignsNobodyIn(AcceptanceTester $I)
	{
		$I->wantTo('refuse the auth cookie a cookie-mode site handed out before the upgrade');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=whoami');
		$I->seeInSource('MODE=cookie');
		$I->seeInSource('COOKIE=1');
		$I->seeInSource('UID=0');
	}

	public function logoutLeavesAnUpgradedCookieModeSiteOnAWorkingLoginForm(AcceptanceTester $I)
	{
		$I->wantTo('log out cleanly on a site that was in cookie mode before the upgrade');

		$I->amOnPage('/index.php?logout');
		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource('Fatal error');
		$I->dontSeeInSource('Uncaught');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=whoami');
		$I->seeInSource('COOKIE=0');
		$I->seeInSource('UID=0');

		$I->amOnPage('/login.php');
		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource('Fatal error');
		$I->seeElement('input', array('name' => 'username'));
		$I->seeElement('input', array('name' => 'userpass'));
	}

	public function theLoginFormOffersNoRememberMeControl(AcceptanceTester $I)
	{
		$I->wantTo('render a login form with no Remember Me checkbox on it');

		$I->amOnPage('/login.php');
		$I->seeElement('input', array('name' => 'userpass'));
		$I->dontSeeElement('input', array('name' => 'autologin'));
	}

	public function aThemeStillAskingForTheRememberMeShortcodesRendersNoBraces(AcceptanceTester $I)
	{
		$I->wantTo('leave a theme that still asks for the deleted shortcodes rendering cleanly');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=legacy_template');
		$I->seeInSource('PROBE_OK');
		$I->seeInSource('BRACES=0');
		$I->seeInSource('CHECKBOX=0');
	}

	public function anUpgradedCookieModeSiteStillSignsItsAdminIn(AcceptanceTester $I)
	{
		$I->wantTo('sign in normally on a site whose user_tracking still reads cookie');

		$I->amOnPage('/login.php');
		$I->fillField('username', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('userpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('userlogin');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=whoami');
		$I->seeInSource('MODE=cookie');
		$I->dontSeeInSource('UID=0');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0076_CookieModeRemovedCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$config = e107::getConfig('core');
$trackingPref = 'e107_tests_cookie_mode_tracking';

if($act === 'setup')
{
	if(null === $config->get($trackingPref))
	{
		$config->set($trackingPref, $config->get('user_tracking'));
	}
	$config->set('user_tracking', 'cookie')->save(false, true, false);

	$row = e107::getDb()->retrieve('user', 'user_id, user_password', 'user_id=1');
	$token = empty($row['user_password']) ? '' : $row['user_id'].'.'.md5($row['user_password']);
	cookie(e_COOKIE, $token);

	echo "TOKEN=".($token === '' ? 0 : 1)."\n";
	echo "PROBE_OK\n";
	exit;
}

if($act === 'teardown')
{
	$config->set('user_tracking', $config->get($trackingPref));
	$config->remove($trackingPref);
	$config->save(false, true, false);
	cookie(e_COOKIE, '', (time() - 2592000));

	echo "PROBE_OK\n";
	exit;
}

if($act === 'whoami')
{
	echo "MODE=".$config->get('user_tracking')."\n";
	echo "COOKIE=".(empty($_COOKIE[e_COOKIE]) ? 0 : 1)."\n";
	echo "UID=".(int) defset('USERID', 0)."\n";
	echo "PROBE_OK\n";
	exit;
}

if($act === 'legacy_template')
{
	$template = '{LOGIN_TABLE_AUTOLOGIN}{LOGIN_TABLE_AUTOLOGIN_LAN}{LOGIN_TABLE_REMEMBERME}';
	$rendered = e107::getParser()->parseTemplate($template, true, e107::getScBatch('login'));

	echo "BRACES=".(strpos($rendered, '{') === false ? 0 : 1)."\n";
	echo "CHECKBOX=".(strpos($rendered, 'autologin') === false ? 0 : 1)."\n";
	echo "RENDERED=[".str_replace(array("\r", "\n"), '', $rendered)."]\n";
	echo "PROBE_OK\n";
	exit;
}

echo "unknown action\n";
PHP;
	}
}
