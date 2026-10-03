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

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	/*public function installWelcomePageContainsExpectedContent(AcceptanceTester $I)
	{
		$I->amOnPage('/install.php');
		$I->see("Installation :  Step 1 of 8");
		$I->see("Language Selection");
	}*/

	public function installDefault(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 with default settings"); // bootstrap5.
		$this->installe107($I);
		$this->checkAdminButtonWelcomeMessage($I);
		$this->testNoUpdatesRequired($I);
		$this->checkTinyMceIsInstalled($I);
	//	$this->checkBootstrap5Navigation($I);

	}
/*
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

	}*/

	public function installVoux(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 with Voux theme");
		$this->installe107($I, array('sitetheme'=>'voux'));
		$this->checkAdminButtonWelcomeMessage($I);
		$this->testNoUpdatesRequired($I);
		$this->checkTinyMceIsInstalled($I);
	}

	public function installedSiteBlocksTheInstaller(AcceptanceTester $I)
	{
		$I->wantTo("Block the interactive installer once the site is installed (fail closed on e107_config.php)");

		$this->installe107($I);

		// A completed install must refuse to run the wizard again; reinstalling
		// requires removing e107_config.php from the filesystem.
		$I->amOnPage('/install.php');
		$I->see('already installed');
		$I->dontSee('Language Selection');

		// The unattended entry must also be refused (the existing install blocks it).
		$db = $I->getDbModule();
		$I->amOnPage('/install.php?create_tables=1&username='.urlencode($db->_getDbUsername()).'&password='.urlencode($db->_getDbPassword()));
		$I->dontSee('Language Selection');
	}

	public function installResumesFromPastedState(AcceptanceTester $I)
	{
		$I->wantTo("Resume a locked install by pasting the saved state after the session is lost");

		// Stage 1 -> 2 mints the provisioning lock and signs the wizard state.
		$I->amOnPage('/install.php');
		$I->selectOption('language', 'English');
		$I->click('start');
		$I->see('MySQL Server Details', 'h3');

		$savedState = $I->grabValueFrom('input[name=previous_steps]');
		$I->assertNotEmpty($savedState, 'Stage 2 must hand back signed wizard state.');

		// Lose the session: drop the convenience cookie so the next request has no
		// valid state and is gated behind the paste prompt.
		$I->resetInstallStateCookie();
		$I->amOnPage('/install.php');
		$I->see('already in progress');

		// A tampered blob must not unlock the gate.
		$tampered = substr($savedState, 0, -1).($savedState[strlen($savedState) - 1] === 'a' ? 'b' : 'a');
		$I->fillField(['name' => 'previous_steps'], $tampered);
		$I->click('start');
		$I->see('already in progress');

		// The genuine saved state resumes the wizard at the database step.
		$I->fillField(['name' => 'previous_steps'], $savedState);
		$I->click('start');
		$I->see('MySQL Server Details', 'h3');
	}

	public function installCookieAutoResumeAvoidsGate(AcceptanceTester $I)
	{
		$I->wantTo("Auto-resume from the cookie instead of gating when a lock already exists");

		$I->amOnPage('/install.php');
		$I->selectOption('language', 'English');
		$I->click('start');
		$I->see('MySQL Server Details', 'h3');

		// Cookie present: a bare GET must not gate.
		$I->amOnPage('/install.php');
		$I->dontSee('already in progress');

		// Cookie gone: the same bare GET falls back to the paste gate.
		$I->resetInstallStateCookie();
		$I->amOnPage('/install.php');
		$I->see('already in progress');
	}

	public function installOffersADatabaseFileNamedWith128RandomBits(AcceptanceTester $I)
	{
		$I->wantTo('Offer a database file named with 128 random bits, which the site folder hash, a public 40-bit check of the name, cannot narrow to a feasible search');

		$this->startInstall($I);
		$I->assertMatchesRegularExpression('~^e107_system/e107_[0-9a-f]{32}\.sqlite$~', $I->grabAttributeFrom('#db', 'data-default-file'));
	}

	public function installRefusesADatabaseFileTheWebServerCouldRun(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a database file the web server would run or read as configuration, and a stream wrapper');

		$I->requireDatabaseDriver('sqlite', 'a database server has no database file');
		$this->startInstall($I);

		foreach(array('e107_system/site.php', 'e107_system/site.PHTML', 'e107_system/site.php.sqlite', 'e107_system/.htaccess', 'e107_system/.user.ini', 'e107_system/site.php::$DATA', 'e107_system/site.php:', 'e107_system/site<b.sqlite', 'phar://e107_system/site.sqlite', 'php://memory', 'e107_system/') as $file)
		{
			$this->submitDatabaseStep($I, $file, true);
			$I->see('cannot be a stream wrapper', '.alert');
			$I->dontSee('SQLite is available');
		}
	}

	public function installTakesADatabaseFileByAnyPath(AcceptanceTester $I)
	{
		$I->wantTo('Create the database file at an absolute path outside the e107 folder, and at one with no extension');

		$I->requireDatabaseDriver('sqlite', 'a database server has no database file');

		foreach(array(sys_get_temp_dir().'/e107_install_'.bin2hex(random_bytes(8)).'.db3', sys_get_temp_dir().'/e107_install_'.bin2hex(random_bytes(8))) as $file)
		{
			try
			{
				$this->startInstall($I);
				$this->submitDatabaseStep($I, $file, true);
				$I->see('Successfully created database');
				$I->assertFileExists($file);
			}
			finally
			{
				@unlink($file);
			}
		}
	}

	public function installCreatesTheDatabaseFileItOffers(AcceptanceTester $I)
	{
		$I->wantTo('Create the database file at the path the form offers, which is relative to the e107 folder');

		$I->requireDatabaseDriver('sqlite', 'a database server has no database file');
		$this->startInstall($I);
		$file = $I->grabAttributeFrom('#db', 'data-default-file');

		try
		{
			$this->submitDatabaseStep($I, $file, true);
			$I->see('SQLite is available on this server');
			$I->see('Successfully created database');
			$I->dontSee('SQLite Reported Error');
		}
		finally
		{
			$I->removeAppPath($file);
		}
	}

	public function installNamesTheEngineThatCouldNotUseTheDatabaseFile(AcceptanceTester $I)
	{
		$I->wantTo('Report a file SQLite cannot take for the database as an SQLite error, under a line that claims only what held before the file was tried');

		$I->requireDatabaseDriver('sqlite', 'a database server has no database file');
		$this->startInstall($I);
		$this->submitDatabaseStep($I, 'README.md', true);

		$I->see('SQLite is available on this server');
		$I->see('SQLite Reported Error');
		$I->see('The database file "README.md" already exists');
		$I->dontSee('MySQL Reported Error');
	}

	public function installWarnsWhenTheBrowserCanDownloadADatabaseFileGivenByAnAbsolutePath(AcceptanceTester $I)
	{
		$I->wantTo('Probe whether the browser can download a database file inside the e107 folder that was given by its absolute path');

		$I->requireDatabaseDriver('sqlite', 'a database server has no database file');
		$database = $I->getDbModule()->_getDbName();

		try
		{
			$this->installe107($I, array('db' => $database));
		}
		finally
		{
			$I->removeAppPath('e107_system/'.\E107Preparer::siteHash($database));
			$I->removeAppPath('e107_media/'.\E107Preparer::siteHash($database));
		}
	}

	public function installErrorPageDoesNotLeakProvisioningToken(AcceptanceTester $I)
	{
		$I->wantTo("Keep the provisioning token and credentials out of the installer error/debug output");

		// Stage 1 -> 2 mints the provisioning token and loads it for the next request.
		$I->amOnPage('/install.php');
		$I->selectOption('language', 'English');
		$I->click('start');
		$I->see('MySQL Server Details', 'h3');

		// Force an out-of-range stage so the server reaches the error/debug render
		// path while a token is loaded. The stage-2 form already carries the valid
		// signed state in its hidden previous_steps field.
		$I->submitForm('#versions', ['stage' => 999], 'submit');

		// The error path must render only the structured error, never the e_install
		// object: it holds the private $token (the HMAC signing key) and the
		// submitted credentials, none of which may leak into the response.
		$I->see('makes no sense');                  // confirms the error path was hit
		$I->dontSeeInSource('e_install Object');    // print_r() dump of $this
		$I->dontSeeInSource(':e_install:private');  // print_r() private-property marker
	}

	private function installe107(AcceptanceTester $I, $params = array())
	{
		// Step 1

		$I->amOnPage('/install.php');
		$I->selectOption("language", 'English');
		$I->click('start');

		// Step 2

		$I->see("MySQL Server Details", 'h3');

		$sqlite = ($I->getDbModule()->_getDbDriver() === 'sqlite');
		$this->submitDatabaseStep($I, !empty($params['db']) ? $params['db'] : $I->appDatabaseName(), false);

		// Step 3

		$I->see("MySQL Connection Verification", 'h3');
		$I->see($sqlite ? "SQLite is available on this server" : "Connection to the MySQL server established and verified");
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

		$I->fillField('u_name',     \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('d_name',     \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('pass1',      \Helper\AdminLogin::ADMIN_PASS);
		$I->fillField('pass2',      \Helper\AdminLogin::ADMIN_PASS);
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

		if($sqlite)
		{
			$I->assertStringEndsWith('/'.$I->appDatabaseName(), $I->grabAttributeFrom('#db-file-exposed', 'data-url'));
		}
		else
		{
			$I->dontSeeElement('#db-file-exposed');
		}

		// Leave the installer the way a visitor does, by operating its own
		// control. This step used to jump straight to /index.php with
		// amOnPage(), which never exercised the hand-over and so never noticed
		// that it was a POST the finished site refuses.
		$I->click('#submit');

		$I->dontSee('Unauthorized access!');
		$I->seeInCurrentUrl('index.php');

		if(!empty($params['sitetheme']))
		{
			$I->seeInSource('e107_themes/'.$params['sitetheme']);
		}

	}

	/**
	 * Fill in the database step for the suite's own engine, and submit it.
	 *
	 * @param string $database
	 * @param bool $create tick Create Database
	 * @return void
	 */
	private function submitDatabaseStep(AcceptanceTester $I, $database, $create)
	{
		$db = $I->getDbModule();

		try
		{
			$I->selectOption('driver', $db->_getDbDriver());
		}
		catch (Exception $e)
		{
			// A PHP with one engine is offered no choice: the form names it in a hidden field.
			$I->seeInSource("name='driver' value='".$db->_getDbDriver()."'");
		}

		if($db->_getDbDriver() !== 'sqlite')
		{
			$I->fillField('server',     $db->_getDbHostname());
			$I->fillField('name',       $db->_getDbUsername());
			$I->fillField('password',   $db->_getDbPassword());
		}

		$I->fillField('db', $database);

		if($create)
		{
			$I->checkOption('createdb');
		}
		else
		{
			$I->uncheckOption('createdb');
		}

		$I->click('submit');
	}

	private function startInstall(AcceptanceTester $I)
	{
		$I->amOnPage('/install.php');
		$I->selectOption('language', 'English');
		$I->click('start');
		$I->see('MySQL Server Details', 'h3');
	}

	private function loginToAdmin(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
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
/*
	private function checkBootstrap5Navigation(AcceptanceTester $I)
	{
		$I->amOnPage('/article-1');
		$I->see('Chapter 1');

		$I->amOnPage('/gallery/gallery-1');
		$I->see('horse');

		$I->amOnPage('/terms-of-use');
		$I->see('Terms of Use','.breadcrumb-item');
	}*/


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
