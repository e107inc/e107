<?php

/**
 * Regression tests for GHSA-72q5-94gw-prww: the global fail-closed rule.
 *
 * e_session::check() only ever rejected a token that was present and wrong, so
 * omitting the field entirely passed. A POST that carries a cookie now has to
 * present one, on the front end as well as in the admin area, and the
 * csrf_enforce preference selects refuse, log-only or off. A POST that carries
 * no cookie has no ambient authority to borrow and is left alone.
 *
 * The probe is a file of this Cest's own rather than an existing endpoint,
 * because it has to be reachable by a guest, have no per-file guard of its own,
 * and prove whether execution continued past class2.php.
 */
class CsrfFailClosedCest
{
	const PROBE_FILE = 'e107_tests_csrf_failclosed_probe.php';
	const SECRET = 'PrObEsEcReTvAlUe';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(AcceptanceTester $I)
	{
		$this->setMode($I, 2); // e_session::TOKEN_CHECK_ENFORCE, the default
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function tokenlessFrontEndPostIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a tokenless POST to a front-end endpoint that never had a guard');

		$this->acquireCookies($I);

		$I->sendPostRequest('/' . self::PROBE_FILE, array(
			'csrf_probe_enforce' => 1,
			'csrf_probe_secret'  => self::SECRET,
		));

		$I->seeInSource('Unauthorized access!');
		$I->dontSeeInSource('PROBE_REACHED');
	}

	public function aTokenlessGetIsUnaffected(AcceptanceTester $I)
	{
		$I->wantTo('Leave GET requests behaving exactly as before');

		$I->amOnPage('/' . self::PROBE_FILE);

		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * A caller that presents no cookie has no ambient authority to borrow, so it
	 * cannot be the victim of a forgery and is left alone. This is what keeps a
	 * payment gateway's IPN callback, an SSO assertion and every other
	 * machine-to-machine POST working on a site that has just been upgraded.
	 */
	public function aCookielessPostIsAllowedThrough(AcceptanceTester $I)
	{
		$I->wantTo('Leave a POST that carries no cookie alone');

		$I->sendPostRequest('/' . self::PROBE_FILE, array(
			'csrf_probe_cookieless' => 1,
		));

		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * Log-only is the measurement mode an operator runs before switching
	 * enforcement on, so the request has to be allowed through and recorded.
	 */
	public function logOnlyModeAllowsAndRecordsTheRequest(AcceptanceTester $I)
	{
		$I->wantTo('Allow and log a tokenless POST when the mode is log-only');

		$this->setMode($I, 1); // e_session::TOKEN_CHECK_LOG

		$I->sendPostRequest('/' . self::PROBE_FILE, array(
			'csrf_probe_logonly' => 1,
			'csrf_probe_secret'  => self::SECRET,
		));

		$I->seeInSource('PROBE_REACHED');
		$I->seeInDatabase('e107_admin_log', array(
			'dblog_eventcode'    => 'CSRF_01',
			'dblog_remarks like' => '%csrf_probe_logonly%',
		));
	}

	/**
	 * A refused request writes nothing. The log is there for the operator to
	 * measure with, and a row per refusal would hand anyone who can reach the
	 * site an unthrottled insert into an indexed table.
	 */
	public function aRefusedRequestIsNotLogged(AcceptanceTester $I)
	{
		$I->wantTo('Keep refused requests out of the admin log');

		$this->acquireCookies($I);

		$I->sendPostRequest('/' . self::PROBE_FILE, array(
			'csrf_probe_notlogged' => 1,
		));

		$I->seeInSource('Unauthorized access!');
		$I->dontSeeInDatabase('e107_admin_log', array(
			'dblog_remarks like' => '%csrf_probe_notlogged%',
		));
	}

	/**
	 * The log records which fields were posted, never what was in them, so a
	 * tokenless login POST cannot put a password in the admin log.
	 */
	public function theLogRecordsFieldNamesButNotValues(AcceptanceTester $I)
	{
		$I->wantTo('Keep posted values out of the CSRF log');

		$this->setMode($I, 1); // e_session::TOKEN_CHECK_LOG

		$I->sendPostRequest('/' . self::PROBE_FILE, array(
			'csrf_probe_fieldnames' => 1,
			'csrf_probe_secret'     => self::SECRET,
		));

		$I->seeInDatabase('e107_admin_log', array(
			'dblog_eventcode'    => 'CSRF_01',
			'dblog_remarks like' => '%csrf_probe_secret%',
		));
		$I->dontSeeInDatabase('e107_admin_log', array(
			'dblog_remarks like' => '%' . self::SECRET . '%',
		));
	}

	/**
	 * The rule only applies to a request that carries a cookie, so a GET has to
	 * come first to put the session cookie in the browser's jar. Codeception
	 * resets that jar between tests.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function acquireCookies(AcceptanceTester $I)
	{
		$I->amOnPage('/' . self::PROBE_FILE);
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * Store the csrf_enforce preference, and pick up the session cookie on the
	 * way, since the rule under test only applies to a request that carries one.
	 *
	 * The preference is what production reads, so driving the test through it
	 * exercises the real path. The define this replaced could only be set before
	 * class2.php, which meant the stored preference was never exercised at all.
	 *
	 * @param AcceptanceTester $I
	 * @param int $mode one of e_session's TOKEN_CHECK_* values
	 * @return void
	 */
	private function setMode(AcceptanceTester $I, $mode)
	{
		$I->amOnPage('/' . self::PROBE_FILE . '?csrf_probe_mode=' . (int) $mode);
		$I->seeInSource('PROBE_MODE_SET');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0020_CsrfFailClosedCest. Removed again in the Cest's _after().
// A GET carrying csrf_probe_mode stores the csrf_enforce preference, so the
// POST that follows is decided by the same production path an operator uses.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
if(isset($_GET['csrf_probe_mode']))
{
	e107::getConfig('core')->set('csrf_enforce', (int) $_GET['csrf_probe_mode'])->save(false, true, false);
	echo 'PROBE_MODE_SET';
}
echo 'PROBE_REACHED';
PHP;
	}
}
