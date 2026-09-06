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
}
