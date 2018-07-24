<?php


class AdminLoginCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function testAdminLogin(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->wantTo("Test the admin area login process");
		$I->see("Admin Area");
		$I->see("login");

		$I->fillField('authname', 'admin');
		$I->fillField('authpass', 'admin');

		$I->click('authsubmit');

		$I->see("Admin's Control Panel");

		$I->dontSeeInSource('Unauthorized access!');

		$I->see("Latest");

		$I->see("Status");

	}





}
