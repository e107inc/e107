<?php


class InstallCest
{
	public function _before(AcceptanceTester $I)
	{
		$I->unlinkE107ConfigFromTestEnvironment();
		$this->dropAllDbTables($I);
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

	public function installDefault(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 with default settings");
		$this->installe107($I);
		$this->checkAdminButtonWelcomeMessage($I);
		$this->testNoUpdatesRequired($I);
		$this->checkTinyMceIsInstalled($I);

	}

	public function installBootstrap3(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 with bootstrap3");
		$this->installe107($I, array('sitetheme'=>'bootstrap3'));
		$this->checkAdminButtonWelcomeMessage($I);
		$this->testNoUpdatesRequired($I);
		$this->checkTinyMceIsInstalled($I);

		// Check install.xml Custom Fields in Page table.
		$I->amOnPage('/page.php?id=4');
		$I->see("22 Aug 2018");
		$I->see("United States");
		$I->see("Blue");

	}

/*	public function installLandingZero(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 with landingzero");
		$this->installe107($I, array('sitetheme'=>'landingzero'));
		$this->checkAdminButtonWelcomeMessage($I);
		$this->testNoUpdatesRequired($I);
		$this->checkTinyMceIsInstalled($I);

	}*/

	private function installe107(AcceptanceTester $I, $params = array())
	{
		// Step 1

		$I->amOnPage('/install.php');
		$I->selectOption("language", 'English');
		$I->click('start');

		// Step 2

		$I->see("MySQL Server Details", 'h3');

		$db = $I->getDbModule();

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

		if(!empty($params['sitetheme']))
		{
			$I->selectOption('sitetheme', $params['sitetheme']);
		}

		$I->click('submit');

		// Step 7

		$I->see("Install Confirmation", 'h3');

		$I->click('submit');

		// Step 8

		$I->see("Installation Complete", 'h3');


		$I->amOnPage('/index.php');

		if(!empty($params['sitetheme']))
		{
			$I->seeInSource('e107_themes/'.$params['sitetheme']);
		}

	}

	private function loginToAdmin(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', 'admin');
		$I->fillField('authpass', 'admin');
		$I->click('authsubmit');
		$I->dontSeeInSource('Unauthorized access!');
	}

	private function testNoUpdatesRequired(AcceptanceTester $I)
	{
		// first Login
		$this->loginToAdmin($I);

		$I->amOnPage('/e107_admin/e107_update.php?[debug=basic+]');
		$I->wantTo("Check there are no updates required after install");

		$I->dontSee("Update", 'button span');
	}


	private function checkTinyMceIsInstalled(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource('TinyMce4');
		$I->amOnPage('/e107_plugins/tinymce4/admin_config.php');
		$I->see("Paste as text by default");
	}


	private function checkAdminButtonWelcomeMessage(AcceptanceTester $I)
	{
		$I->seeInSource('btn-large " href="e107_admin/admin.php">Go to Admin area</a>');

	}

	/**
	 * @param AcceptanceTester $I
	 */
	private function dropAllDbTables(AcceptanceTester $I)
	{
		$db = $I->getDbModule();
		$db->_cleanup();
	}

}
