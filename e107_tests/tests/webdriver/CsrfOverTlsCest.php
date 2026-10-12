<?php

/**
 * The browser check, over the only scheme on which it can exist.
 *
 * The recommended mode accepts either proof: the browser saying the request came
 * from this site, or a valid token. Over HTTPS the browser half is live, because
 * the Fetch Metadata headers are appended only to a potentially trustworthy
 * origin, and CsrfPlainHttpCest covers the scheme where they never arrive.
 *
 * What is worth asserting in a real browser is that the token half is still
 * delivered even here, where the browser can answer. It is not there for this
 * browser; it is there for the one that cannot send Sec-Fetch-Site at all, and a
 * page that stopped carrying it would refuse those visitors with nothing to fall
 * back on.
 *
 * @env tls
 */
class CsrfOverTlsCest
{
	const PROBE = 'e107_tests_csrf_tls_probe.php';

	/**
	 * The fallback has to survive the case that does not need it. A token
	 * published only where Fetch Metadata is missing would be a token decided by
	 * the request rather than by the mode, and a cached page would carry the
	 * wrong answer.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function aTokenIsPublishedEvenWhereTheBrowserCanVouch(\WebDriverTester $I)
	{
		$I->wantTo('see that a site on HTTPS still publishes a CSRF token');

		$I->amOnPage('/');
		$I->waitForElement('body', 10);

		$I->assertStringContainsString('name="e-token"', $I->grabPageSource(),
			'the recommended mode reads a token, so every page has to carry one');
	}

	/**
	 * And the writes go through. A form submitted from a document this site
	 * served is a same-origin request carrying a token it was just handed, which
	 * satisfies either half, so a browser that cannot sign in here means the
	 * mode is refusing something no attacker could have produced.
	 *
	 * @group sign-in
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function anAdministratorCanSignInOverTls(\WebDriverTester $I)
	{
		$I->wantTo('sign in to the admin area over HTTPS');

		$I->loginAsAdmin();

		// A failed sign-in re-renders the login page, which is a page too, so the
		// marker alone can pass on the very response that means failure.
		$I->dontSeeElement('input[name=authpass]');
	}

	/**
	 * An image on this site is placed here by whoever wrote the markup, such as
	 * a member's avatar, so Chrome's word that it is same-origin must not excuse
	 * the wrong token it carries when an administrator views the page.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function anImageOnThisSiteCannotActOnAWrongToken(\WebDriverTester $I)
	{
		$I->wantTo('see a same-origin image carrying a wrong token refused');

		$I->amSignedInAs();
		$I->haveProbe(self::PROBE, $this->probeSource());
		$I->amOnProbe('page=1');
		$I->waitForJS('return Array.prototype.every.call(document.images, function(i) { return i.complete; });', 10);

		// Without this, a probe that served no image at all would pass too.
		$I->assertSame(1, (int) $I->executeJS('return document.getElementById("valid-token").naturalWidth;'));
		$I->assertSame(0, (int) $I->executeJS('return document.getElementById("wrong-token").naturalWidth;'));
		$I->assertStringContainsString('Unauthorized access!', $this->frameText($I));
	}

	/**
	 * The browser-only modes read no token, and core opens token-marked pages
	 * in its own modal frame there, so a frame of this origin is let through
	 * while an image still is not.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function aFrameOfThisSiteMayCarryAnyTokenInABrowserOnlyMode(\WebDriverTester $I)
	{
		$I->wantTo('see a same-origin frame let through in mode 4, and an image still refused');

		$I->amSignedInAs();
		$I->haveProbe(self::PROBE, $this->probeSource());
		$I->amOnProbe('mode=4');

		try
		{
			$I->amOnProbe('page=1');
			$I->waitForJS('return Array.prototype.every.call(document.images, function(i) { return i.complete; });', 10);

			$I->assertSame('PROBE_REACHED', $this->frameText($I));
			$I->assertSame(0, (int) $I->executeJS('return document.getElementById("wrong-token").naturalWidth;'));
		}
		finally
		{
			$I->amOnProbe('mode=default');
		}
	}

	/**
	 * A page cached before its token went stale still has to work when the
	 * administrator follows a link from it, or this is the lockout the browser
	 * check was brought in to end.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function aLinkTheAdministratorFollowsMayCarryAStaleToken(\WebDriverTester $I)
	{
		$I->wantTo('follow a link carrying a stale token');

		$I->amSignedInAs();
		$I->haveProbe(self::PROBE, $this->probeSource());
		$I->amOnProbe('page=1');
		$I->click('#stale-link');
		$I->waitForText('PROBE_REACHED', 10);
	}

	/**
	 * @param \WebDriverTester $I
	 * @return string what the probe page's frame shows once it has loaded
	 */
	private function frameText(\WebDriverTester $I)
	{
		$frame = 'document.getElementById("modal").contentDocument';
		$I->waitForJS('return '.$frame.' && '.$frame.'.readyState === "complete" && '.$frame.'.body.textContent.length > 0;', 10);

		return trim((string) $I->executeJS('return '.$frame.'.body.textContent;'));
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for CsrfOverTlsCest: a same-origin image, link and frame to itself, each carrying an e-token.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
if(isset($_GET['pixel']))
{
	header('Content-Type: image/gif');
	echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
	exit;
}
if(isset($_GET['reached']))
{
	echo 'PROBE_REACHED';
	exit;
}
if(isset($_GET['mode']))
{
	$config = e107::getConfig('core');
	if($_GET['mode'] === 'default')
	{
		$config->remove('csrf_enforce');
	}
	else
	{
		$config->set('csrf_enforce', (int) $_GET['mode']);
	}
	$config->save(false, true, false);
	echo 'MODE_SET';
	exit;
}
$tp = e107::getParser();
$self = basename(__FILE__).'?probe='.rawurlencode($_GET['probe']);
echo "<!doctype html><title>probe</title>";
echo "<img id='valid-token' src='".$tp->toAttribute($self.'&pixel=1&e-token='.rawurlencode(defset('e_TOKEN')))."' alt='' />";
echo "<img id='wrong-token' src='".$tp->toAttribute($self.'&pixel=2&e-token=not-the-token')."' alt='' />";
echo "<a id='stale-link' href='".$tp->toAttribute($self.'&reached=1&e-token=minted-before-the-upgrade')."'>follow</a>";
echo "<iframe id='modal' src='".$tp->toAttribute($self.'&reached=1&e-token=not-the-token')."'></iframe>";
PHP;
	}
}
