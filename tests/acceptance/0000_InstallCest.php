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

	public function installRun(AcceptanceTester $I)
	{
		$I->wantTo("Install e107");

		// Step 1

		$I->amOnPage('/install.php');
		$I->selectOption("language", 'English');
		$I->click('start');

		// Step 2

		$I->see("MySQL Server Details", 'h3');

		$db = $I->getHelperDb();

		$I->fillField('server',     $db->_getDbHostname());
		$I->fillField('name',       $db->_getDbUsername());
		$I->fillField('password',   $db->_getDbPassword());
		$I->fillField('db',         $db->_getDbName());

		$I->uncheckOption('createdb');
		$I->click('submit');

		// Step 3

		$I->see("MySQL Connection Verification", 'h3');
		$I->see("Connection to the MySQL server established and verified");
		$I->see("Found existing database");

		$I->click('submit');

		// Step 4

		$I->see("PHP and MySQL Versions Check / File Permissions Check");

		try
		{
			$I->see('You might have an existing installation'); //XXX Triggered if e107_config.php is not empty
		}
		catch (Exception $e)
		{
			$I->dontSee('You might have an existing installation');
		}

		$I->click('continue_install');

		// Step 5

		$I->see("Administration", 'h3');

		$I->fillField('u_name',     'admin');
		$I->fillField('d_name',     'admin');
		$I->fillField('pass1',      'admin');
		$I->fillField('pass2',      'admin');
		$I->fillField('email',      'admin@admin.com');

		$I->click('submit');

		// Step 6

		$I->see("Website Preferences", 'h3');
		$I->fillField('sitename',     'Test Site');

		$I->click('submit');

		// Step 7

		$I->see("Install Confirmation", 'h3');

		$I->click('submit');

		// Step 8

		$I->see("Installation Complete", 'h3');
	}
}
