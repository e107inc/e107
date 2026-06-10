<?php

/**
 * PR #5695 review follow-up: the post-login "return to where you were" behaviour must
 * also cover the admin area, not just front-end class-restricted pages. A guest who
 * requests an admin page is shown the admin login form; after logging in they should
 * land back on the page they asked for instead of always being dumped on the dashboard
 * (/e107_admin/admin.php).
 */
class AdminLoginDestinationCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function returnsToRequestedAdminPageAfterLogin(AcceptanceTester $I)
	{
		$I->wantTo('Return to the requested admin page after admin login (PR #5695 review)');

		$adminPage = '/e107_admin/users.php';

		// As a guest, requesting an admin page shows the admin login form, not the page.
		$I->amOnPage($adminPage);
		$I->seeElement('input', array('name' => 'authname'));
		// The intended destination is carried through the POST as a signed hidden field.
		$I->seeElement('input', array('name' => '__logindest'));

		// Log in through the admin login form.
		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('authsubmit');

		// We land back on the originally requested admin page, not the dashboard.
		$I->seeInCurrentUrl($adminPage);
		$I->dontSeeElement('input', array('name' => 'authname'));
	}

	/**
	 * Regression: an <iframe> sub-request must never be remembered as the post-login
	 * destination. The menu manager renders its body in an iframe whose src is an
	 * admin-perms-gated URL; if that sub-request is captured, the user is returned to
	 * the bare iframe view with no way to navigate. Browsers tag the sub-request with
	 * the Fetch Metadata header Sec-Fetch-Dest: iframe, which the capture guard honours.
	 */
	public function doesNotReturnToIframeSubRequestAfterLogin(AcceptanceTester $I)
	{
		$I->wantTo('Not capture an iframe sub-request as the post-login destination');

		// Replay the browser's iframe sub-request: a GET tagged Sec-Fetch-Dest: iframe.
		$I->haveHttpHeader('Sec-Fetch-Dest', 'iframe');
		$I->amOnPage('/e107_admin/users.php');

		// The guest is bounced to the login form, but the embedded request is not
		// remembered: the signed destination cookie is never written.
		$I->seeElement('input', array('name' => 'authname'));
		$I->dontSeeCookie('e107_logindest');

		// After logging in we land on the dashboard, never back inside the iframe URL.
		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('authsubmit');

		$I->dontSeeElement('input', array('name' => 'authname'));
		$I->dontSeeInCurrentUrl('/e107_admin/users.php');
	}

	/**
	 * Belt for clients that send no Fetch Metadata: e107's own iframe/dialog request
	 * markers (here the menu manager's ?configure=) are refused by URL alone, so the
	 * iframe layout view never becomes the return destination. The destination cookie
	 * is never written for that sub-request (the bare login page that the bounce lands
	 * on may still carry its own self-referential field, which is harmless).
	 */
	public function doesNotCaptureIframeMarkerUrl(AcceptanceTester $I)
	{
		$I->wantTo('Not capture an ?configure= iframe-marker URL as the destination');

		// The menu manager's iframe body. As a guest this funnels through the same
		// redirect-to-login gate, but the ?configure= marker must block capture.
		$I->amOnPage('/e107_admin/menus.php?configure=3_column');
		$I->seeElement('input', array('name' => 'authname'));
		$I->dontSeeCookie('e107_logindest');
	}
}
