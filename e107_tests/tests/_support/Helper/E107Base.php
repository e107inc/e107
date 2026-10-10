<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Twig\Environment;
use Twig\Loader\ArrayLoader;

abstract class E107Base extends Base
{
	const APP_PATH_E107_CONFIG = APP_PATH."/e107_config.php";
	const E107_MYSQL_PREFIX = 'e107_';

	/** The site folder tests/_data/e107_config.php.sample pins. */
	const INSTALL_SITE_PATH = '000000test';

	/**
	 * Write an arbitrary file into the deployed docroot.
	 *
	 * Goes through the deployer rather than file_put_contents() so it works
	 * when the app under test is remote (CI deploys over SFTP). Parent
	 * directories are created.
	 *
	 * Lives here rather than on Acceptance because the unit suite writes
	 * fixtures into the app too; both suites reach the same app the same way.
	 *
	 * A fixture that boots e107 in the docroot goes through
	 * {@see ProbeGuard::contain()} first, which refuses one that reserved no
	 * room for the guard. {@see AppFileRegistry} records the write, so the
	 * test that made it takes it back out.
	 *
	 * @param string $relative_path path relative to the app root
	 * @param string $contents
	 * @return void
	 */
	public function writeAppFile($relative_path, $contents)
	{
		AppFileRegistry::park($relative_path);
		$created = $this->deployer->writeAppFile($relative_path, ProbeGuard::contain($relative_path, $contents));
		AppFileRegistry::didWrite($relative_path, $created);
	}

	/**
	 * Remove a file from the app, whether a test or the app wrote it. A test
	 * that removes a tracked file calls {@see AppFileRegistry::park()} first.
	 *
	 * @param string $relative_path path relative to the app root
	 * @return void
	 */
	public function deleteAppFile($relative_path)
	{
		$this->deployer->unlinkAppFile($relative_path);
	}

	/**
	 * Remove a path from the app, directory or file, present or not.
	 *
	 * @param string $relative_path path relative to the app root
	 * @return void
	 */
	public function removeAppPath($relative_path)
	{
		$this->deployer->removeAppPaths(array($relative_path));
	}

	/**
	 * Empty the tables e107 counts requests in and records an auto-ban in.
	 *
	 * @return void
	 */
	public function resetFloodProtection()
	{
		try
		{
			$dbh = $this->getDbModule()->_getDbh();

			$dbh->exec('DELETE FROM `'.self::E107_MYSQL_PREFIX.'online`');
			$dbh->exec('DELETE FROM `'.self::E107_MYSQL_PREFIX.'banlist` '
				.'WHERE `banlist_bantype` IN (2, -2)');
		}
		catch (\PDOException $e)
		{
			return;
		}
	}

	public function _beforeSuite($settings = array())
	{
		parent::_beforeSuite($settings);
		$this->writeLocalE107Config();
	}

	protected function renderLocalE107Config()
	{
		$twig_loader = new ArrayLoader([
			'e107_config.php' => file_get_contents(codecept_data_dir()."/e107_config.php.sample")
		]);
		$twig = new Environment($twig_loader);

		$db = $this->getModule('\Helper\SiteDb');

		$e107_config = [];
		$e107_config['mySQLserver'] = $db->_getDbHostname();
		$e107_config['mySQLuser'] = $db->_getDbUsername();
		$e107_config['mySQLpassword'] = $db->_getDbPassword();
		$e107_config['mySQLdefaultdb'] = $db->_getDbName();
		$e107_config['mySQLprefix'] = self::E107_MYSQL_PREFIX;

		return $twig->render('e107_config.php', $e107_config);
	}

	protected function writeLocalE107Config()
	{
		file_put_contents(self::APP_PATH_E107_CONFIG, $this->renderLocalE107Config());
	}

	public function _afterSuite()
	{
		parent::_afterSuite();
		$this->workaroundOldPhpUnitPhpCodeCoverage();
	}

	/**
	 * Workaround for phpunit/php-code-coverage < 6.0.8
	 * @see https://github.com/sebastianbergmann/php-code-coverage/commit/f4181f5c0a2af0180dadaeb576c6a1a7548b54bf
	 */
	protected function workaroundOldPhpUnitPhpCodeCoverage()
	{
		$composer_installed_file = codecept_absolute_path("vendor/composer/installed.json");
		$composer_installed = json_decode(file_get_contents($composer_installed_file));
		if (isset($composer_installed->packages))
		{
			// Composer 2 format for the installed packages manifest
			$composer_installed = $composer_installed->packages;
		}
		$installed_phpunit_php_code_coverage = current(array_filter($composer_installed, function ($element)
		{
			return $element->name == 'phpunit/php-code-coverage';
		}));
		if (version_compare($installed_phpunit_php_code_coverage->version_normalized, '6.0.8', '>='))
			return;

		@mkdir(codecept_output_dir(), 0755, true);
	}
}
