<?php

/**
 * The authentication token e_user::load() accepts is
 * "<user_id>.<md5 of the stored password hash>", read from $_SESSION. Two ways
 * in were closed:
 *
 * - the password component was compared with `==`, so any stored hash whose
 *   md5 reads as a number matched a token component that reads as the same
 *   number. md5('240610708') is '0e4620...', i.e. numeric zero, so the token
 *   "<user_id>.0e0" was accepted for such an account. It has to be spelled
 *   "0e0" rather than "0" because load() rejects an empty component first.
 * - e_session::destroy() left the token in $_SESSION and in $_COOKIE, so the
 *   request that failed session validation was still authenticated by the
 *   token the validation had just thrown away.
 *
 * The probe drives e_user::load() over HTTP because the CLI branch of load()
 * short-circuits to user 1 and never reaches the token at all.
 *
 * The third way in was the token being read out of a client cookie at all, and
 * that is covered here at the model and in 0076_CookieModeRemovedCest over
 * HTTP.
 */
class SessionAuthTokenCest
{
	const PROBE_FILE = 'e107_tests_ghsa7v5h_probe.php';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage('/'.self::PROBE_FILE.'?act=setup');
		$I->seeInSource('PROBE_OK');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=teardown');
		$I->seeInSource('PROBE_OK');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function theCorrectTokenAuthenticatesASession(AcceptanceTester $I)
	{
		$I->wantTo('log a session in with the token its own password hash produces');

		$this->seeProbeAuthenticates($I, 'valid');
	}

	public function theCorrectTokenInACookieAuthenticatesNobody(AcceptanceTester $I)
	{
		$I->wantTo('refuse a cookie carrying the token its own password hash produces');

		$this->seeProbeRefuses($I, 'valid_cookie');
	}

	public function aNumericallyEqualSessionTokenIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse a session token that only equals the password hash as a number');

		$this->seeProbeRefuses($I, 'magic');
	}

	public function aSessionThatPassesValidationStaysAuthenticated(AcceptanceTester $I)
	{
		$I->wantTo('leave a session alone when its validation data still matches');

		$this->seeProbeAuthenticates($I, 'alive');
	}

	public function aFailedValidationRefusesItsOwnRequest(AcceptanceTester $I)
	{
		$I->wantTo('refuse the request that session validation has just rejected');

		$this->seeProbeRefuses($I, 'destroyed');
	}

	public function theSessionTableDoesNotHoldTheVisitorsCookie(AcceptanceTester $I)
	{
		$I->wantTo('keep the session table from holding a value that is itself a session cookie');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=stored_key');
		$I->seeInSource('PROBE_OK');
		$I->seeInSource('LIVE_ROW=0');
		$I->seeInSource('HASHED_ROW=1');
	}

	public function aSessionStoredBeforeTheUpgradeIsAdoptedNotDropped(AcceptanceTester $I)
	{
		$I->wantTo('keep a session that predates the storage key, and re-key it in place');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=adopted');
		$I->seeInSource('PROBE_OK');
		$I->dontSeeInSource('FIXTURE=0');
		$I->seeInSource('RESULT=AUTHENTICATED');
		$I->seeInSource('LIVE_ROW=0');
		$I->seeInSource('HASHED_ROW=1');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $act probe action
	 */
	private function seeProbeAuthenticates(AcceptanceTester $I, $act)
	{
		$this->runProbe($I, $act);
		$I->seeInSource('RESULT=AUTHENTICATED');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $act probe action
	 */
	private function seeProbeRefuses(AcceptanceTester $I, $act)
	{
		$this->runProbe($I, $act);
		$I->seeInSource('RESULT=ANONYMOUS');
		$I->seeInSource('UID=0');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $act probe action
	 */
	private function runProbe(AcceptanceTester $I, $act)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act='.$act);
		$I->seeInSource('PROBE_OK');
		$I->dontSeeInSource('FIXTURE=0');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0075_SessionAuthTokenCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

// md5() of this is '0e462097431906509019562988736854', which PHP reads as the
// number zero whenever it is compared with `==` against another numeric string.
$fixturePassword = '240610708';
$fixtureName = 'ghsa7v5h_probe';

$act = isset($_GET['act']) ? $_GET['act'] : '';
$sql = e107::getDb();
$config = e107::getConfig('core');
$uidPref = 'e107_tests_ghsa7v5h_uid';
$trackingPref = 'e107_tests_ghsa7v5h_tracking';

if($act === 'setup')
{
	if(null === $config->get($trackingPref))
	{
		$config->set($trackingPref, $config->get('user_tracking'));
	}
	$sql->delete('user', "user_loginname='".$fixtureName."'");
	$uid = (int) $sql->insert('user', array(
		'user_name'      => $fixtureName,
		'user_loginname' => $fixtureName,
		'user_email'     => $fixtureName.'@example.com',
		'user_password'  => $fixturePassword,
		'user_join'      => 1262304000,
		'user_class'     => '',
		'user_admin'     => 0,
		'user_perms'     => '',
	));
	$config->set($uidPref, $uid)->set('user_tracking', 'session')->save(false, true, false);
	unset($_SESSION[e_COOKIE], $_COOKIE[e_COOKIE]);
	echo "FIXTURE=".$uid."\n";
	echo "PROBE_OK\n";
	exit;
}

if($act === 'teardown')
{
	$sql->delete('user', "user_loginname='".$fixtureName."'");
	$config->set('user_tracking', $config->get($trackingPref));
	$config->remove($uidPref);
	$config->remove($trackingPref);
	$config->save(false, true, false);
	unset($_SESSION[e_COOKIE], $_COOKIE[e_COOKIE]);
	echo "PROBE_OK\n";
	exit;
}

$uid = (int) $config->get($uidPref, 0);
echo "FIXTURE=".$uid."\n";
unset($_SESSION[e_COOKIE], $_COOKIE[e_COOKIE]);

switch($act)
{
	case 'valid':
		$_SESSION[e_COOKIE] = $uid.'.'.md5($fixturePassword);
		break;

	case 'magic':
		$_SESSION[e_COOKIE] = $uid.'.0e0';
		break;

	case 'valid_cookie':
		$config->set('user_tracking', 'cookie')->save(false, true, false);
		$_COOKIE[e_COOKIE] = $uid.'.'.md5($fixturePassword);
		break;

	case 'stored_key':
	case 'adopted':
		$_SESSION[e_COOKIE] = $uid.'.'.md5($fixturePassword);
		$live = session_id();
		session_write_close();

		// Spelled out rather than asked of the class under test, so that
		// removing the fix makes these counters disagree rather than fatal.
		$hashedKey = 'sha256$'.hash('sha256', $live);

		if($act === 'adopted')
		{
			$sql->update('session', array(
				'data' => array('session_id' => $live),
				'_FIELD_TYPES' => array('session_id' => 'str'),
				'WHERE' => "`session_id`='".$hashedKey."'",
			));

			session_start();
			$adoptedUser = new e_user();
			$adoptedId = (int) $adoptedUser->getId();
			echo "UID=".$adoptedId."\n";
			echo "RESULT=".($uid > 0 && $adoptedId === $uid ? 'AUTHENTICATED' : 'ANONYMOUS')."\n";
		}

		echo "LIVE_ROW=".(int) (bool) $sql->select('session', 'session_id', "session_id='".$live."'")."\n";
		echo "HASHED_ROW=".(int) (bool) $sql->select('session', 'session_id', "session_id='".$hashedKey."'")."\n";
		echo "PROBE_OK\n";
		exit;

	case 'alive':
		$_SESSION[e_COOKIE] = $uid.'.'.md5($fixturePassword);
		$session = e107::getSession();
		$session->set('_session_validate_data', $session->getValidateData());
		$session->validate();
		break;

	case 'destroyed':
		$_SESSION[e_COOKIE] = $uid.'.'.md5($fixturePassword);
		$session = e107::getSession();
		$session->set('_session_validate_data', array(
			'RemoteAddr'        => '198.51.100.7',
			'HttpVia'           => '',
			'HttpXForwardedFor' => '',
			'HttpUserAgent'     => '',
		));
		$session->validate();
		break;

	default:
		echo "unknown action\n";
		exit;
}

$user = new e_user();
$loaded = (int) $user->getId();
echo "UID=".$loaded."\n";
echo "RESULT=".($uid > 0 && $loaded === $uid ? 'AUTHENTICATED' : 'ANONYMOUS')."\n";
echo "PROBE_OK\n";
PHP;
	}
}
