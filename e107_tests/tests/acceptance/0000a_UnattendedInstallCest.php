<?php

class UnattendedInstallCest
{
	const ADMIN_USER     = 'unattendedadmin';
	const ADMIN_PASSWORD = 'unattendedpass';
	const ADMIN_DISPLAY  = 'Unattended Admin';
	const ADMIN_EMAIL    = 'unattended@admin.com';
	const SITENAME       = 'UnattendedInstallTest';
	const SITETHEME      = 'bootstrap5';
	const SITE_PATH      = '000000test';
	const MYSQL_PREFIX   = 'e107_';

	public function _before(AcceptanceTester $I)
	{
		$I->unlinkE107ConfigFromTestEnvironment();
		$this->dropAllAppTables($I);
		$this->wipeSiteState();
	}

	public function _after(AcceptanceTester $I)
	{
	}

	private function dropAllAppTables(AcceptanceTester $I)
	{
		$dbh = $I->getDbModule()->_getDbh();
		$dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
		$tables = $dbh->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE '%TABLE'")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($tables as $table)
		{
			$dbh->exec('DROP TABLE `'.$table.'`');
		}
		$dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
	}

	public function unattendedInstallWithV24ArrayConfig(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 unattended with a v2.4 array-format e107_config.php");

		$this->writeArrayConfig($I);
		$this->visitUnattendedInstallUrl($I);
		$this->assertInstallSucceeded($I);
		$this->assertConfigStillValid($I);
	}

	public function unattendedInstallWithLegacyGlobalsConfig(AcceptanceTester $I)
	{
		$I->wantTo("Install e107 unattended with a legacy globals-format e107_config.php");

		$this->writeLegacyConfig($I);
		$this->visitUnattendedInstallUrl($I);
		$this->assertInstallSucceeded($I);
	}

	public function unattendedInstallRejectsWrongCredentials(AcceptanceTester $I)
	{
		$I->wantTo("Reject create_tables_unattended when the URL credentials don't match the config");

		$this->writeArrayConfig($I);
		$I->amOnPage('/install.php?create_tables=1&username=wrong&password=wrong');
		$this->assertUnattendedAdminAbsent($I);
	}

	public function unattendedInstallRejectsMissingConfig(AcceptanceTester $I)
	{
		$I->wantTo("Reject create_tables_unattended when no e107_config.php is present");

		$I->amOnPage('/install.php?create_tables=1&username=any&password=any');
		$this->assertUnattendedAdminAbsent($I);
	}

	private function writeArrayConfig(AcceptanceTester $I)
	{
		$db = $I->getDbModule();
		$contents = "<?php\nreturn "
			.var_export([
				'database' => [
					'server'   => $db->_getDbHostname(),
					'user'     => $db->_getDbUsername(),
					'password' => $db->_getDbPassword(),
					'db'       => $db->_getDbName(),
					'prefix'   => self::MYSQL_PREFIX,
					'charset'  => 'utf8mb4',
				],
				'paths' => [
					'admin'     => 'e107_admin/',
					'files'     => 'e107_files/',
					'images'    => 'e107_images/',
					'themes'    => 'e107_themes/',
					'plugins'   => 'e107_plugins/',
					'handlers'  => 'e107_handlers/',
					'languages' => 'e107_languages/',
					'help'      => 'e107_docs/help/',
					'media'     => 'e107_media/',
					'system'    => 'e107_system/',
				],
				'other' => [
					'site_path' => self::SITE_PATH,
				],
			], true).";\n";
		file_put_contents(APP_PATH.'/e107_config.php', $contents);
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
		file_put_contents(APP_PATH.'/e107_config.php', $contents);
	}

	private function visitUnattendedInstallUrl(AcceptanceTester $I)
	{
		$db = $I->getDbModule();
		$params = http_build_query([
			'create_tables'  => 1,
			'username'       => $db->_getDbUsername(),
			'password'       => $db->_getDbPassword(),
			'admin_user'     => self::ADMIN_USER,
			'admin_password' => self::ADMIN_PASSWORD,
			'admin_display'  => self::ADMIN_DISPLAY,
			'admin_email'    => self::ADMIN_EMAIL,
			'sitename'       => self::SITENAME,
			'theme'          => self::SITETHEME,
			'language'       => 'English',
			'gen'            => 1,
			'plugins'        => 1,
		]);
		$I->amOnPage('/install.php?'.$params);
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

	private function assertConfigStillValid(AcceptanceTester $I)
	{
		$contents = file_get_contents(APP_PATH.'/e107_config.php');
		$I->assertNotEmpty($contents, 'e107_config.php should not have been emptied by the unattended install.');
		$I->assertStringContainsString("'database'", $contents, 'e107_config.php should still carry the v2.4 array shape.');
		$I->assertStringContainsString("'paths'", $contents);
		$I->assertStringContainsString("'other'", $contents);
	}

	private function assertUnattendedAdminAbsent(AcceptanceTester $I)
	{
		$dbh = $I->getDbModule()->_getDbh();
		$tables = $dbh->query("SHOW TABLES LIKE '".self::MYSQL_PREFIX."user'")->fetchAll(PDO::FETCH_COLUMN);
		$I->assertEmpty($tables, 'e107_user table should not have been created when install was rejected.');
	}

	private function wipeSiteState()
	{
		foreach (['e107_system', 'e107_media'] as $top)
		{
			$dir = APP_PATH.'/'.$top.'/'.self::SITE_PATH;
			if (is_dir($dir))
			{
				$this->rrmdir($dir);
			}
		}
	}

	private function rrmdir($path)
	{
		if (!is_dir($path))
		{
			return;
		}
		$entries = scandir($path);
		foreach ($entries as $entry)
		{
			if ($entry === '.' || $entry === '..')
			{
				continue;
			}
			$full = $path.'/'.$entry;
			if (is_dir($full) && !is_link($full))
			{
				$this->rrmdir($full);
			}
			else
			{
				@unlink($full);
			}
		}
		@rmdir($path);
	}
}
