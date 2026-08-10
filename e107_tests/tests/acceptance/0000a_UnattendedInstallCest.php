<?php

class UnattendedInstallCest
{
	const ADMIN_USER     = \Helper\AdminLogin::ADMIN_USER;
	const ADMIN_PASSWORD = \Helper\AdminLogin::ADMIN_PASS;
	const ADMIN_DISPLAY  = \Helper\Acceptance::INSTALL_ADMIN_DISPLAY;
	const ADMIN_EMAIL    = \Helper\Acceptance::INSTALL_ADMIN_EMAIL;
	const SITENAME       = \Helper\Acceptance::INSTALL_SITENAME;
	const SITETHEME      = \Helper\Acceptance::INSTALL_SITETHEME;
	const SITE_PATH      = \Helper\Acceptance::INSTALL_SITE_PATH;
	const MYSQL_PREFIX   = \Helper\E107Base::E107_MYSQL_PREFIX;

	public function _before(AcceptanceTester $I)
	{
		$I->unlinkE107ConfigFromTestEnvironment();
		$I->dropAllAppTables();
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// Test order matters: the "rejects" cases leave the database empty,
	// so they run first. The successful-install cases run last so that
	// subsequent Cests (AdminLoginCest, UserSignupCest, ...) can rely on
	// a fully installed app with admin/admin credentials.

	public function unattendedInstallRejectsMissingConfig(AcceptanceTester $I)
	{
		$I->wantTo("Reject create_tables_unattended when no e107_config.php is present");

		$I->amOnPage('/install.php?create_tables=1&username=any&password=any');
		$this->assertUnattendedAdminAbsent($I);
	}

	public function unattendedInstallRejectsWrongCredentials(AcceptanceTester $I)
	{
		$I->wantTo("Reject create_tables_unattended when the URL credentials don't match the config");

		$I->haveE107ArrayConfig();
		$I->amOnPage('/install.php?create_tables=1&username=wrong&password=wrong');
		$this->assertUnattendedAdminAbsent($I);
	}

	public function unattendedSecondAttemptIsRefused(AcceptanceTester $I)
	{
		$I->wantTo("Refuse a repeat create_tables_unattended once the database already holds an e107 install");

		// First unattended install succeeds and populates the database.
		$I->haveE107ArrayConfig();
		$I->visitUnattendedInstall();
		$this->assertInstallSucceeded($I);

		// Drop the admin user table, then replay the exact same install URL.
		$dbh = $I->getDbModule()->_getDbh();
		$dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
		$dbh->exec('DROP TABLE `'.self::MYSQL_PREFIX.'user`');
		$dbh->exec('SET FOREIGN_KEY_CHECKS=1;');

		$I->visitUnattendedInstall();

		// The remaining schema must make the replay a no-op: the credential check
		// is never reached, so the user table is not recreated and the site cannot
		// be silently re-provisioned/taken over.
		$this->assertUnattendedAdminAbsent($I);
	}

	public function unattendedInstallWithLegacyGlobalsConfig(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 unattended with a legacy globals-format e107_config.php");

		$this->writeLegacyConfig($I);
		$I->visitUnattendedInstall();
		$this->assertInstallSucceeded($I);
	}

	public function unattendedInstallWithV24ArrayConfig(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 unattended with a v2.4 array-format e107_config.php");

		$I->haveE107ArrayConfig();
		$I->visitUnattendedInstall();
		$this->assertInstallSucceeded($I);
	}

	private function writeLegacyConfig(AcceptanceTester $I)
	{
		$db = $I->getDbModule();
		$server   = addslashes($db->_getDbHostname());
		$user     = addslashes($db->_getDbUsername());
		$password = addslashes($db->_getDbPassword());
		$database = addslashes($db->_getDbName());
		$prefix   = addslashes(self::MYSQL_PREFIX);
		$sitePath = addslashes(self::SITE_PATH);
		$contents = <<<PHP
<?php
\$mySQLserver     = '$server';
\$mySQLuser       = '$user';
\$mySQLpassword   = '$password';
\$mySQLdefaultdb  = '$database';
\$mySQLprefix     = '$prefix';
\$ADMIN_DIRECTORY     = 'e107_admin/';
\$IMAGES_DIRECTORY    = 'e107_images/';
\$THEMES_DIRECTORY    = 'e107_themes/';
\$PLUGINS_DIRECTORY   = 'e107_plugins/';
\$FILES_DIRECTORY     = 'e107_files/';
\$HANDLERS_DIRECTORY  = 'e107_handlers/';
\$LANGUAGES_DIRECTORY = 'e107_languages/';
\$HELP_DIRECTORY      = 'e107_docs/help/';
\$MEDIA_DIRECTORY     = 'e107_media/';
\$SYSTEM_DIRECTORY    = 'e107_system/';
\$E107_CONFIG = ['site_path' => '$sitePath'];
PHP;
		$I->writeE107ConfigToTestEnvironment($contents);
	}

	private function assertInstallSucceeded(AcceptanceTester $I)
	{
		$db = $I->getDbModule();

		$I->seeInDatabase(self::MYSQL_PREFIX.'user', [
			'user_id'        => 1,
			'user_loginname' => self::ADMIN_USER,
			'user_admin'     => 1,
			'user_perms'     => '0',
			'user_email'     => self::ADMIN_EMAIL,
		]);

		$I->seeInDatabase(self::MYSQL_PREFIX.'core', [
			'e107_name' => 'SitePrefs',
		]);

		$prefs = $db->grabFromDatabase(self::MYSQL_PREFIX.'core', 'e107_value', ['e107_name' => 'SitePrefs']);
		$I->assertNotEmpty($prefs, 'SitePrefs row should carry serialized prefs.');
		$I->assertStringContainsString(
			"'sitename' => '".self::SITENAME."'",
			$prefs,
			"SitePrefs should record the unattended-install sitename."
		);
		$I->assertStringContainsString(
			"'sitetheme' => '".self::SITETHEME."'",
			$prefs,
			"SitePrefs should record the unattended-install theme."
		);

		$installedPlugins = $db->grabNumRecords(self::MYSQL_PREFIX.'plugin', ['plugin_installflag' => 1]);
		$I->assertGreaterThan(
			0,
			$installedPlugins,
			'Expected at least one plugin to be flagged installed after unattended install.'
		);
	}

	private function assertUnattendedAdminAbsent(AcceptanceTester $I)
	{
		$dbh = $I->getDbModule()->_getDbh();
		$tables = $dbh->query("SHOW TABLES LIKE '".self::MYSQL_PREFIX."user'")->fetchAll(PDO::FETCH_COLUMN);
		$I->assertEmpty($tables, 'e107_user table should not have been created when install was rejected.');
	}
}
