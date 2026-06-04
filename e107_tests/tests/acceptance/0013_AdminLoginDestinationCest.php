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
}
