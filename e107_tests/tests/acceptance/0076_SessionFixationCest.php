<?php

/**
 * The session identifier across the login boundary.
 *
 * e107 issues a session to every visitor, anonymous ones included, and until
 * now the identifier a visitor arrived with was the identifier their
 * authenticated session kept. Anyone who knew the value beforehand knew a
 * signed-in session's value afterwards, which is the whole of session fixation
 * bar the means of planting the value in the first place.
 *
 * It matters more here than the shape of the bug suggests, because
 * user_tracking defaults to 'session': the authentication token lives in
 * $_SESSION rather than in a cookie of its own, so the session identifier is
 * the credential and not merely a handle on one.
 *
 * The identifier is read back from the application through a probe rather than
 * from the cookie jar. e107's session cookie name and path both come from the
 * install (e_HTTP, and PHP's own session name), and a test that guessed either
 * would assert about a cookie the jar cannot find.
 *
 * @see e107_handlers/session_handler.php  e_session::regenerateId()
 * @see e107_handlers/user_handler.php  UserHandler::makeUserCookie()
 */
class SessionFixationCest
{
	const PROBE_FILE = 'e107_tests_sessionfixation_probe.php';

	/** The account the acceptance install creates; a member as well as an admin. */
	const EXPECTED_USER_ID = '1';

	const GUEST_USER_ID = '0';

	/** Written into the anonymous session and looked for again afterwards. */
	const MARKER = 'e107-tests-0076-marker';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->resetAllCookies();
		$I->startFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * Signing in must not leave the visitor on the identifier they arrived
	 * with.
	 */
	public function theSessionIdChangesWhenAVisitorSignsIn(AcceptanceTester $I)
	{
		$I->wantTo('Get a new session identifier on signing in');

		$before = $this->probe($I);
		$I->assertSame(self::GUEST_USER_ID, $before['USER_ID'],
			'The visitor was already signed in before the login, so this proves nothing.');
		$I->assertNotEmpty($before['SESSION_ID'], 'No session was issued to the anonymous visitor.');

		$this->signIn($I);

		$after = $this->probe($I);
		$I->assertSame(self::EXPECTED_USER_ID, $after['USER_ID'], 'The login did not take.');

		$I->assertNotSame($before['SESSION_ID'], $after['SESSION_ID'],
			'The session identifier the visitor carried while anonymous ('.$before['SESSION_ID'].') '
			.'is the one that carries the authenticated session.');
	}

	/**
	 * The identifier the visitor arrived with must not be an authenticated
	 * session afterwards.
	 *
	 * The assertion above compares two identifiers, and would pass for a login
	 * that issued a new one while still answering to the old one. This case
	 * presents the old one on its own. It says nothing about the delete
	 * argument, which the case below covers: the authentication token is
	 * written after the regeneration, so it never reaches the old record
	 * either way.
	 */
	public function theAnonymousSessionIdIsDeadAfterSigningIn(AcceptanceTester $I)
	{
		$I->wantTo('Find the pre-login session identifier is no longer a signed-in session');

		$before = $this->probe($I);

		$I->assertSame('session', $before['USER_TRACKING'],
			'This site keeps the authentication token in a cookie of its own, so emptying the jar '
			.'below would drop the credential rather than the session, and the case would pass '
			.'whether the identifier was retired or not.');

		$this->signIn($I);

		$after = $this->probe($I);
		$I->assertSame(self::EXPECTED_USER_ID, $after['USER_ID'], 'The login did not take.');

		$I->resetAllCookies();
		$I->setCookie($after['SESSION_NAME'], $before['SESSION_ID'],
			array('path' => $after['COOKIE_PATH']));

		$replayed = $this->probe($I);

		$I->assertSame(self::GUEST_USER_ID, $replayed['USER_ID'],
			'Presenting the pre-login session identifier ('.$before['SESSION_ID'].') on its own got a '
			.'signed-in session back.');
	}

	/**
	 * The record behind the anonymous identifier must be gone, not left beside
	 * the new one.
	 *
	 * Only data written before the sign-in can tell those apart, so this case
	 * seeds a marker while anonymous and looks for it under the old identifier
	 * afterwards.
	 */
	public function theAnonymousSessionRecordIsDeletedNotLeftBeside(AcceptanceTester $I)
	{
		$I->wantTo('Find the pre-login session record gone rather than left beside the new one');

		$this->probe($I, self::MARKER);
		$before = $this->probe($I);

		$I->assertSame(self::MARKER, $before['MARKER'],
			'The marker seeded in the anonymous session was gone by the next request, so its '
			.'absence after the sign-in would prove nothing.');

		$this->signIn($I);

		$after = $this->probe($I);
		$I->assertSame(self::EXPECTED_USER_ID, $after['USER_ID'], 'The login did not take.');
		$I->assertSame(self::MARKER, $after['MARKER'],
			'Signing in dropped the data the session was carrying, which is a different defect '
			.'from the one this case is about.');

		$I->resetAllCookies();
		$I->setCookie($after['SESSION_NAME'], $before['SESSION_ID'],
			array('path' => $after['COOKIE_PATH']));

		$I->assertSame('', $this->probe($I)['MARKER'],
			'The record behind the pre-login identifier ('.$before['SESSION_ID'].') is still there '
			.'after the sign-in, so the old identifier was left valid beside the new one.');
	}

	/**
	 * Positive control: signing in still works, and still works on the request
	 * after it.
	 *
	 * This is the assertion the change turns on. Regenerating at the wrong
	 * point in the request drops the data written either side of it, and the
	 * visible symptom is a login that appears to succeed and is gone by the
	 * next page.
	 */
	public function signingInSurvivesTheRedirectAndTheRequestAfterIt(AcceptanceTester $I)
	{
		$I->wantTo('Stay signed in across the login redirect and the next request');

		$this->signIn($I);

		$I->amOnPage('/usersettings.php');
		$I->seeElement('input', array('name' => 'loginname'));

		$I->assertSame(self::EXPECTED_USER_ID, $this->probe($I)['USER_ID'],
			'The session did not survive to a third request.');
	}

	/**
	 * Backwards-compatibility control: a visitor who does not sign in is not
	 * signed in, so the marker the cases above read means something.
	 */
	public function aVisitorWhoDoesNotSignInIsNotSignedIn(AcceptanceTester $I)
	{
		$I->wantTo('Stay anonymous without signing in');

		$I->amOnPage('/usersettings.php');
		$I->dontSeeElement('input', array('name' => 'loginname'));

		$I->assertSame(self::GUEST_USER_ID, $this->probe($I)['USER_ID']);
	}

	// -----------------------------------------------------------------
	// fixture
	// -----------------------------------------------------------------

	private function signIn(AcceptanceTester $I)
	{
		$I->amOnPage('/login.php');
		$I->fillField('username', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('userpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('userlogin');
	}

	/**
	 * @param string $mark stored in the session, and reported by every request after it
	 * @return array SESSION_NAME, SESSION_ID, COOKIE_PATH, USER_ID,
	 *         USER_TRACKING and MARKER as the application sees them
	 */
	private function probe(AcceptanceTester $I, $mark = null)
	{
		$I->amOnPage('/'.self::PROBE_FILE.(null === $mark ? '' : '?mark='.urlencode($mark)));

		$body = $I->grabPageSource();
		$read = array();

		foreach(array('SESSION_NAME', 'SESSION_ID', 'COOKIE_PATH', 'USER_ID', 'USER_TRACKING',
			'MARKER') as $key)
		{
			$matches = array();

			if(!preg_match('/^'.$key.':(.*)$/m', $body, $matches))
			{
				throw new \RuntimeException('Probe reported no '.$key.': '.trim(strip_tags($body)));
			}

			$read[$key] = trim($matches[1]);
		}

		return $read;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<PHP
<?php
// Fixture for 0076_SessionFixationCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

if(isset(\$_GET['mark']))
{
	\$_SESSION['e107_tests_probe_marker'] = (string) \$_GET['mark'];
}

echo "SESSION_NAME:", session_name(), "\\n";
echo "SESSION_ID:", session_id(), "\\n";
echo "COOKIE_PATH:", defined('e_HTTP') ? e_HTTP : '/', "\\n";
echo "USER_ID:", defined('USERID') ? (int) USERID : 0, "\\n";
echo "USER_TRACKING:", e107::getPref('user_tracking', 'session'), "\\n";
echo "MARKER:", isset(\$_SESSION['e107_tests_probe_marker'])
	? \$_SESSION['e107_tests_probe_marker'] : '', "\\n";
PHP;
	}
}
