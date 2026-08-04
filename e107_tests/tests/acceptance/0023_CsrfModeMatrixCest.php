<?php

/**
 * What each csrf_enforce mode accepts, stated outright.
 *
 * e107 can prove a POST came from this site in two ways, and the preference
 * chooses which it asks for. A security token proves the request came from a
 * document this site rendered, and works in any browser, but only protects a
 * document that was actually given one. Sec-Fetch-Site is set by the browser and
 * cannot be set by a page, so nothing has to be delivered for it to work, but a
 * browser released before it says nothing at all.
 *
 * The numbers are a menu, not a ladder, so nothing here is inferred by
 * comparison: every mode is exercised against every kind of request. The
 * combination that matters most is the one nobody chose, since csrf_enforce is
 * seeded nowhere and nearly every site in the world runs on the unset default.
 *
 * The probe is a file of this Cest's own, because it has to be reachable by a
 * guest, carry no per-file guard, and prove whether execution continued past
 * class2.php.
 */
class CsrfModeMatrixCest
{
	const PROBE_FILE = 'e107_tests_csrf_matrix_probe.php';

	const OFF             = 0;
	const TOKEN_LOG       = 1;
	const TOKEN_ENFORCE   = 2;
	const TOKEN_OR_SITE   = 3;
	const SAME_SITE       = 4;
	const SAME_ORIGIN     = 5;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		// Each test here makes a dozen or so requests in quick succession, and
		// e107 bans an address at fifty. Left alone the suite bans itself part
		// way through and every later request comes back empty. Localhost is
		// exempt, but the client address inside the container is the bridge, so
		// the counter is reset per test rather than the protection turned off.
		$I->amOnPage('/' . self::PROBE_FILE . '?csrf_matrix_reset=1');
		$I->seeInSource('RESET_DONE');

		// Present every request as a TLS terminating proxy would.
		//
		// Which proof a mode demands is only a meaningful question on an origin
		// that could carry Fetch Metadata at all. This suite is served over plain
		// HTTP, where no browser ever sends Sec-Fetch-Site and tokenCheckMode()
		// therefore softens the browser-only modes, so without this the strict
		// expectations below would be asserting against mode 3 wearing mode 4's
		// name. The header is not a fiction: it is exactly what a proxy adds, and
		// serving e107 behind one is the ordinary way a site gets HTTPS.
		$I->haveHttpHeader('X-Forwarded-Proto', 'https');
	}

	public function _after(AcceptanceTester $I)
	{
		$this->setMode($I, 'default');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * Off is the escape hatch for an operator whose site is broken and who needs
	 * it working now. It has to mean off, including for a token that is present
	 * and wrong, or it is not an escape hatch.
	 */
	public function offAcceptsEverything(AcceptanceTester $I)
	{
		$this->setMode($I, self::OFF);

		$this->post($I);
		$I->seeInSource('PROBE_REACHED');

		$this->post($I, array('e-token' => 'not-even-close'));
		$I->seeInSource('PROBE_REACHED');

		$this->post($I, array(), 'cross-site');
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * A wrong token is refused in log-only mode. The mode softens the case of a
	 * request that brought no proof, which is the one an operator is measuring;
	 * a request that brought the wrong proof is a different thing entirely.
	 */
	public function logOnlyStillRefusesAWrongToken(AcceptanceTester $I)
	{
		$this->setMode($I, self::TOKEN_LOG);

		$this->post($I);
		$I->seeInSource('PROBE_REACHED');

		$this->post($I, array('e-token' => 'not-even-close'));
		$I->seeInSource('Unauthorized access!');
	}

	/**
	 * The token modes predate Fetch Metadata and must not quietly acquire it.
	 * An operator who picked mode 2 asked for a token specifically, most likely
	 * because something in front of the site eats the header.
	 */
	public function theTokenModesIgnoreTheBrowsersWord(AcceptanceTester $I)
	{
		$this->setMode($I, self::TOKEN_ENFORCE);

		$this->post($I, array(), 'same-origin');
		$I->seeInSource('Unauthorized access!');

		$this->post($I, array('e-token' => $this->grabToken($I)));
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * The unset default. Either proof is enough, so a modern browser is covered
	 * by the header and an old one by the token, and neither is turned away.
	 *
	 * This is the property that makes the default safe to ship in a patch
	 * release: there is no request a working browser can make that it refuses
	 * and the previous release accepted.
	 */
	public function tokenOrBrowserAcceptsEitherProof(AcceptanceTester $I)
	{
		$this->setMode($I, self::TOKEN_OR_SITE);

		// Header alone, as a browser too new to need the token would send.
		$this->post($I, array(), 'same-origin');
		$I->seeInSource('PROBE_REACHED');

		// Token alone, as a browser too old to send the header would.
		$this->post($I, array('e-token' => $this->grabToken($I)));
		$I->seeInSource('PROBE_REACHED');

		// Neither is still refused.
		$this->post($I);
		$I->seeInSource('Unauthorized access!');
	}

	/**
	 * What an unset preference does on this branch, which is what nearly every
	 * site runs: the browser is asked and no token is read at all. A visitor
	 * whose browser cannot answer is turned away rather than admitted on a
	 * token, which is the deliberate difference from release/v2.3.x and the
	 * reason install.php writes a preference outright when the browser doing the
	 * installing could not answer either.
	 */
	public function theDefaultReadsTheBrowserAndNoToken(AcceptanceTester $I)
	{
		$this->setMode($I, 'default');

		$this->post($I, array(), 'same-origin');
		$I->seeInSource('PROBE_REACHED');

		// A valid token is not a substitute here. It is not even looked at.
		$this->post($I, array('e-token' => $this->grabToken($I)));
		$I->seeInSource('Unauthorized access!');

		$this->post($I);
		$I->seeInSource('Unauthorized access!');
	}

	/*
	 * There is deliberately no test here for the softening that happens when an
	 * origin cannot carry Fetch Metadata at all.
	 *
	 * It cannot be written honestly at this layer. Whether the softening applies
	 * is a property of the address the suite is served on, which differs between
	 * the two places this suite runs: the docker harness uses http://web/, and CI
	 * uses http://localhost/e107/. Secure Contexts counts loopback as potentially
	 * trustworthy, so on CI the browser-only modes stay strict and any such test
	 * would pass locally and fail there, or be quietly skipped where it matters
	 * most.
	 *
	 * It is covered where it can be stated plainly instead: e_sessionTest proves
	 * the decision for each kind of origin, and the WebDriver suite proves the
	 * end of it, that a real browser can still log in over plain HTTP to a
	 * non-loopback host. That is the regression this all came from.
	 */

	/**
	 * A page cached from before an upgrade carries a token minted by the old
	 * session, and refusing it would be the same lockout this mode replaces. The
	 * browser is allowed to overrule a stale token, but only by affirmatively
	 * placing the request at this origin.
	 */
	public function tokenOrBrowserLetsTheBrowserOverruleAStaleToken(AcceptanceTester $I)
	{
		$this->setMode($I, self::TOKEN_OR_SITE);

		$this->post($I, array('e-token' => 'minted-before-the-upgrade'), 'same-origin');
		$I->seeInSource('PROBE_REACHED');

		// Without the browser saying so, a wrong token is still a wrong token.
		$this->post($I, array('e-token' => 'minted-before-the-upgrade'));
		$I->seeInSource('Unauthorized access!');

		$this->post($I, array('e-token' => 'minted-before-the-upgrade'), 'cross-site');
		$I->seeInSource('Unauthorized access!');
	}

	/**
	 * The Fetch Metadata modes mint no token and publish none, so they must not
	 * read one either. A site running mode 4 with a token arriving from anywhere
	 * would be trusting a value it no longer maintains.
	 */
	public function theFetchMetadataModesIgnoreTokensEntirely(AcceptanceTester $I)
	{
		$token = $this->grabToken($I);

		foreach(array(self::SAME_SITE, self::SAME_ORIGIN) as $mode)
		{
			$this->setMode($I, $mode);

			$this->post($I, array('e-token' => $token));
			$I->seeInSource('Unauthorized access!');

			$this->post($I, array('e-token' => $token), 'same-origin');
			$I->seeInSource('PROBE_REACHED');
		}
	}

	/**
	 * 'same-site' covers any host under the same registrable domain, which is
	 * what a language-per-subdomain site needs. Taken at face value it would also
	 * vouch for a user-content subdomain, or one that has been taken over, so it
	 * is honoured only for a host this site is configured to serve.
	 */
	public function aSiblingHostIsAcceptedOnlyWhereWeServeIt(AcceptanceTester $I)
	{
		$this->setMode($I, self::SAME_SITE);

		$this->post($I, array(), 'same-site', $this->ourOrigin($I));
		$I->seeInSource('PROBE_REACHED');

		$this->post($I, array(), 'same-site', 'https://uploads.example.net');
		$I->seeInSource('Unauthorized access!');
	}

	/**
	 * Mode 5 is mode 4 for an operator who serves one host and wants no sibling
	 * trusted at all.
	 */
	public function theStrictModeRefusesASiblingOutright(AcceptanceTester $I)
	{
		$this->setMode($I, self::SAME_ORIGIN);

		$this->post($I, array(), 'same-site', $this->ourOrigin($I));
		$I->seeInSource('Unauthorized access!');

		$this->post($I, array(), 'same-origin');
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * The whole point of the header. 'cross-site' is what an attacker's page
	 * produces and 'none' is an address bar or a bookmark, neither of which is a
	 * document of ours submitting a form. A browser that sends nothing has said
	 * nothing, which is not the same as vouching.
	 */
	public function nothingButThisSiteIsEverVouchedFor(AcceptanceTester $I)
	{
		foreach(array(self::TOKEN_OR_SITE, self::SAME_SITE, self::SAME_ORIGIN) as $mode)
		{
			$this->setMode($I, $mode);

			foreach(array('cross-site', 'none', '') as $claim)
			{
				$this->post($I, array(), ($claim === '') ? null : $claim);
				$I->seeInSource('Unauthorized access!');
			}
		}
	}

	/**
	 * A caller that presents no cookie has no ambient authority to borrow, so it
	 * cannot be the victim of a forgery. This is what keeps a payment gateway's
	 * callback working, and it has to hold in the new modes too, since a
	 * machine-to-machine caller sends no Sec-Fetch-Site either.
	 */
	public function aCookielessPostIsLeftAloneInEveryMode(AcceptanceTester $I)
	{
		foreach(range(0, 5) as $mode)
		{
			$this->setMode($I, $mode);

			// All of them, not a list of names. hasAmbientAuthority() asks whether
			// the request carried any cookie at all, and e107's session cookie is
			// not PHPSESSID: it is named by the cookie_name preference, which the
			// installer derives per site. Naming cookies here left the session
			// cookie in place, so the request this test calls cookieless was
			// nothing of the sort, and it was refused for the right reason under a
			// test that expected otherwise.
			$I->resetAllCookies();
			$I->deleteHeader('Sec-Fetch-Site');
			$I->sendPostRequest('/' . self::PROBE_FILE, array('csrf_matrix_cookieless' => 1));

			$I->seeInSource('PROBE_REACHED');
		}
	}

	/**
	 * e107 has state-changing GETs. Plugin install and uninstall, theme install
	 * and the language operations all act on a GET, and each guards itself by
	 * testing that an e-token parameter is not empty, leaving whether it is the
	 * right one to e_core_session::check().
	 *
	 * That division of labour is invisible until a mode stops reading tokens, at
	 * which point the only thing left between those endpoints and an attacker's
	 * <img> tag is a non-empty string. The browser has to vouch instead.
	 */
	public function aStateChangingGetIsGuardedInEveryMode(AcceptanceTester $I)
	{
		foreach(array(self::TOKEN_ENFORCE, self::TOKEN_OR_SITE) as $mode)
		{
			$this->setMode($I, $mode);

			$I->amOnPage('/' . self::PROBE_FILE . '?e-token=any-non-empty-string');
			$I->seeInSource('Unauthorized access!');

			$I->amOnPage('/' . self::PROBE_FILE . '?e-token=' . $this->grabToken($I));
			$I->seeInSource('PROBE_REACHED');
		}

		foreach(array(self::SAME_SITE, self::SAME_ORIGIN) as $mode)
		{
			$this->setMode($I, $mode);

			$I->deleteHeader('Sec-Fetch-Site');
			$I->amOnPage('/' . self::PROBE_FILE . '?e-token=any-non-empty-string');
			$I->seeInSource('Unauthorized access!');

			$I->haveHttpHeader('Sec-Fetch-Site', 'same-origin');
			$I->amOnPage('/' . self::PROBE_FILE . '?e-token=any-non-empty-string');
			$I->seeInSource('PROBE_REACHED');
			$I->deleteHeader('Sec-Fetch-Site');
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $data posted fields
	 * @param string|null $secFetchSite value for the header, or null to send none
	 * @param string|null $origin value for the Origin header, or null to send none
	 * @return void
	 */
	private function post(AcceptanceTester $I, array $data = array(), $secFetchSite = null, $origin = null)
	{
		// The rule only applies to a request carrying a cookie, so pick one up
		// first. It also resets any header left over from the previous call.
		$I->deleteHeader('Sec-Fetch-Site');
		$I->deleteHeader('Origin');
		$I->amOnPage('/' . self::PROBE_FILE);

		if($secFetchSite !== null)
		{
			$I->haveHttpHeader('Sec-Fetch-Site', $secFetchSite);
		}

		if($origin !== null)
		{
			$I->haveHttpHeader('Origin', $origin);
		}

		$I->sendPostRequest('/' . self::PROBE_FILE, $data + array('csrf_matrix' => 1));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int|string $mode a csrf_enforce value, or 'default' to unset it
	 * @return void
	 */
	private function setMode(AcceptanceTester $I, $mode)
	{
		$I->deleteHeader('Sec-Fetch-Site');
		$I->deleteHeader('Origin');
		$I->amOnPage('/' . self::PROBE_FILE . '?csrf_matrix_mode=' . $mode);
		$I->seeInSource('MODE_SET');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string a token the current session will accept
	 */
	private function grabToken(AcceptanceTester $I)
	{
		$I->amOnPage('/' . self::PROBE_FILE);

		// A guest's token is a JWT on master and an md5 on release/v2.3.x, so this
		// accepts base64url and the dots that separate a JWT's three parts.
		if(!preg_match('/TOKEN:([A-Za-z0-9._-]+)/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The probe did not publish a token');
		}

		return $matches[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string an Origin naming the host the site is being served on
	 */
	private function ourOrigin(AcceptanceTester $I)
	{
		$I->amOnPage('/' . self::PROBE_FILE);

		if(!preg_match('/HOST:(\S+)/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The probe did not publish its host');
		}

		return 'http://' . $matches[1];
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0023_CsrfModeMatrixCest. Removed again in the Cest's _after().
// A GET carrying csrf_matrix_mode stores the csrf_enforce preference, so the
// POST that follows is decided by the same production path an operator uses.
// 'default' removes it, which is how the recommended setting is stored.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
if(isset($_GET['csrf_matrix_reset']))
{
	// Flood protection counts hits per address and bans at fifty. A flood ban is
	// recorded with banlist_bantype -2, and once one exists every later request
	// is answered with an empty body, so both have to go.
	e107::getDb()->delete('online');
	e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');
	echo 'RESET_DONE ';
}
if(isset($_GET['csrf_matrix_mode']))
{
	$config = e107::getConfig('core');

	if($_GET['csrf_matrix_mode'] === 'default')
	{
		$config->remove('csrf_enforce');
	}
	else
	{
		$config->set('csrf_enforce', (int) $_GET['csrf_matrix_mode']);
	}

	$config->save(false, true, false);
	echo 'MODE_SET ';
}
echo 'PROBE_REACHED TOKEN:'.defset('e_TOKEN').' HOST:'.$_SERVER['HTTP_HOST'];
PHP;
	}
}
