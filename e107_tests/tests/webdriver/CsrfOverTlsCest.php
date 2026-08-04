<?php

/**
 * The browser check, over the only scheme on which it can exist.
 *
 * This branch recommends mode 4: the browser is asked where the request came
 * from, and no token is read, minted or published at all. That is only a
 * coherent policy where the browser actually answers, which means HTTPS, since
 * the Fetch Metadata headers are appended only to a potentially trustworthy
 * origin.
 *
 * Over plain HTTP the same preference softens to the hybrid, which
 * CsrfPlainHttpCest covers. Together the two say the whole thing: strict where
 * the browser can vouch, usable where it cannot.
 *
 * @env tls
 */
class CsrfOverTlsCest
{
	/**
	 * The saving this mode is for. A token that is never published is a token
	 * that can never be missing from a page, which is the entire class of fault
	 * behind the v2.3.10 lockout.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function noTokenIsPublishedWhenTheBrowserIsAskedInstead(\WebDriverTester $I)
	{
		$I->wantTo('see that a site on HTTPS publishes no CSRF token at all');

		$I->amOnPage('/');
		$I->waitForElement('body', 10);

		$I->assertStringNotContainsString('name="e-token"', $I->grabPageSource(),
			'the browser check reads no token, so none should be published');
	}

	/**
	 * And the writes still go through, on the browser's word alone. A form
	 * submitted from a document this site served is a same-origin request, which
	 * is exactly what Sec-Fetch-Site reports and what no other site can cause.
	 *
	 * @param \WebDriverTester $I
	 * @return void
	 */
	public function anAdministratorCanSignInOnTheBrowsersWordAlone(\WebDriverTester $I)
	{
		$I->wantTo('sign in to the admin area with no token in play');

		$I->loginAsAdmin();

		// A failed sign-in re-renders the login page, which is a page too, so the
		// marker alone can pass on the very response that means failure.
		$I->dontSeeElement('input[name=authpass]');
	}
}
