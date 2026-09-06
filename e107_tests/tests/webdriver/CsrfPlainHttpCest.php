<?php

/**
 * A site served over plain HTTP has to stay usable.
 *
 * The Fetch Metadata headers are appended only when the request's URL is a
 * potentially trustworthy URL, so on a plain-HTTP site served from a name that
 * is not loopback, Sec-Fetch-Site never arrives from any browser at all. The
 * recommended mode reads a token as well, and this is the origin where that is
 * the only proof anyone can offer: these tests are a real browser, on a real
 * plain-HTTP origin, getting a token and being allowed to use it.
 *
 * An operator who chooses a browser-only mode outright lands here too.
 * e_session::tokenCheckMode() softens one to the hybrid exactly where the header
 * can never arrive, which is what keeps that choice from turning into a lockout.
 *
 * This is the layer that caught the regression. The acceptance suite could not
 * have: it runs against http://localhost/e107/ on CI, and loopback is
 * potentially trustworthy, so the browser-only modes stay strict there. This
 * suite reaches the app at http://web/ in both the docker harness and CI, which
 * is a plain-HTTP origin in the ordinary sense.
 *
 * @env http
 */
class CsrfPlainHttpCest
{
	/**
	 * The fallback is only worth anything if the token is actually delivered.
	 * e_token_injector publishes one when the active mode reads one, so a token
	 * in the page is the visible proof that a visitor here has something to
	 * submit at all.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function aPlainHttpSiteIsStillGivenAToken(\WebDriverTester $I)
	{
		$I->wantTo('see that a plain-HTTP site still publishes a CSRF token');

		$I->amOnPage('/');
		$I->waitForElement('body', 10);

		$I->assertStringContainsString('name="e-token"', $I->grabPageSource(),
			'a site that cannot receive Sec-Fetch-Site has to be given the token instead');
	}

	/**
	 * The regression itself. Signing in is a POST carrying a cookie, which is
	 * precisely what the CSRF rule governs, so a mode asking for a proof this
	 * connection cannot supply locks the administrator out of their own site.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function anAdministratorCanStillSignInOverPlainHttp(\WebDriverTester $I)
	{
		$I->wantTo('sign in to the admin area over plain HTTP');

		$I->loginAsAdmin();

		// A failed sign-in re-renders the login page, which carries a token and a
		// form of its own, so asserting the marker alone can pass on the very page
		// that means failure.
		$I->dontSeeElement('input[name=authpass]');
	}
}
