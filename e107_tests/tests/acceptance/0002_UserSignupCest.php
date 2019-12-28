<?php


class UserSignupCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function testUserSignupSimulation(AcceptanceTester $I)
	{
		// first login as admin ( to simulate without emails going out).
		$I->amOnPage('/login.php');
		$I->fillField('username', 'admin');
		$I->fillField('userpass', 'admin');

		$I->click('userlogin');

		$I->see("You are seeing this message because you are currently logged in as the Main Admin");


		// Go to signup page.
		$I->amOnPage('/signup.php');
		$I->wantTo("Test user signup process");

		$I->selectOption('coppa',1);
		$I->click('newver');


		$I->see("You are currently logged in as Main Admin");
		$I->checkOption('simulation');

		// Fill the form
		$I->fillField('loginname', 'user1');
		$I->fillField('email', 'user1@domain.com');
		$I->fillField('password1', 'Password1234');
		$I->fillField('password2', 'Password1234');

		$I->click('register');

		$I->dontSee('Unauthorized access!');

	}





	// "Admin Approval Pending"
	//TODO signup under difference conditions (different prefs).. ie. admin approval required etc.

}
