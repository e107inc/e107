<?php


class InstallCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function installWelcomePageContainsExpectedContent(AcceptanceTester $I)
	{
		$I->amOnPage('/install.php');
		$I->see("e107 Installation :: Step 1");
		$I->see("Language Selection");
	}

	public function installStep1ToStep2(AcceptanceTester $I)
	{
		$I->amOnPage('/install.php');
		$I->wantTo("Verify Proceed to Step 2 of the Installation");
		$I->selectOption("language", 'English');
		$I->click('start');
		$I->see("MySQL Server Details", 'h3');
	}

	public function installStep2ToStep3(AcceptanceTester $I)
	{
		$I->amOnPage('/install.php');
		$I->wantTo("Verify Proceed to Step 3 of the Installation");
		$db = $I->getHelperDb();
		$this->installStep1ToStep2($I);

		$I->fillField('server',     $db->_getDbHostname());
		$I->fillField('name',       $db->_getDbUsername());
		$I->fillField('password',   $db->_getDbPassword());
		$I->fillField('db',         $db->_getDbName());

		$I->uncheckOption('createdb');
		$I->click('submit');

		$I->see("MySQL Connection Verification", 'h3');

		// ....
	}

	public function installStep3ToStep4(AcceptanceTester $I)
	{

		$I->amOnPage('/install.php');
		$I->wantTo("Verify Proceed to Step 4 of the Installation");

		$this->installStep2ToStep3($I);

		//   $I->see("Connection to the MySQL server established and verified");
		$I->see("Found existing database");

		$I->click('submit');

		$I->see("PHP and MySQL Versions Check / File Permissions Check");

	}

	public function installStep4ToStep5(AcceptanceTester $I) // TODO Fails due to e107_config.php being present.
	{

		$I->amOnPage('/install.php');
		$I->wantTo("Verify Proceed to Step 5 of the Installation");

		$this->installStep3ToStep4($I);

		$I->canSee('You might have an existing installation'); //XXX Triggered if e107_config.php is not empty

		$I->click('continue_install');

		$I->see("Administration", 'h3');
	}

	public function installStep5ToStep6(AcceptanceTester $I)
	{

		$I->amOnPage('/install.php');
		$I->wantTo("Verify Proceed to Step 6 of the Installation");
		$this->installStep4ToStep5($I);

		$I->fillField('u_name',     'admin');
		$I->fillField('d_name',     'admin');
		$I->fillField('pass1',      'admin');
		$I->fillField('pass2',      'admin');
		$I->fillField('email',      'admin@admin.com');

		$I->click('submit');

		$I->see("Website Preferences", 'h3');
	}

	public function installStep6ToStep7(AcceptanceTester $I)
	{

		$I->amOnPage('/install.php');
		$I->wantTo("Verify Proceed to Step 7 of the Installation");
		$this->installStep5ToStep6($I);

		$I->fillField('sitename',     'Test Site');

		$I->click('submit');

		$I->see("Install Confirmation", 'h3');
	}

	public function installStep7ToStep8(AcceptanceTester $I)
	{

		$I->amOnPage('/install.php');
		$I->wantTo("Verify Proceed to Step 8 of the Installation");
		$this->installStep6ToStep7($I);

		$I->see("Install Confirmation", 'h3');

		$I->click('submit');

		$I->see("Install Confirmation", 'h3');
	}
}
