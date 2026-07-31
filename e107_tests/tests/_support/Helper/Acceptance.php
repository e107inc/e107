<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends E107Base
{
	protected $deployer_components = ['db', 'fs'];

	/**
	 * Send a plain (non-AJAX) POST request, preserving the browser session.
	 *
	 * InnerBrowser::sendAjaxPostRequest() sets the X-Requested-With header,
	 * which makes e107 define e_AJAX_REQUEST and route admin-ui dispatch to
	 * *Ajax* action methods. Tests posting to ordinary admin form routes
	 * need an unmarked POST instead.
	 *
	 * @param string $uri
	 * @param array $params
	 * @return void
	 */
	public function sendPostRequest($uri, array $params = [])
	{
		$this->getModule('PhpBrowser')->_request('POST', $uri, $params);
	}

	/**
	 * Empty the cookie jar.
	 *
	 * e_core_session::hasAmbientAuthority() asks whether a request carried any
	 * cookie at all, so a test about a cookieless request has to clear all of
	 * them. Naming them is not an option: e107's session cookie is not
	 * PHPSESSID, it is named by the cookie_name preference, which the installer
	 * derives per site. Codeception offers resetCookie() by name and nothing
	 * that empties the jar, so reach for it on the BrowserKit client.
	 *
	 * @return void
	 */
	public function resetAllCookies()
	{
		$this->getModule('PhpBrowser')->client->getCookieJar()->clear();
	}

	/**
	 * Assert the last response did not issue a Location redirect to a URL
	 * containing $needle.
	 *
	 * Codeception 5's PhpBrowser exposes no seeHttpHeader, so read the Location
	 * header straight off the BrowserKit client. Pair with
	 * $I->stopFollowingRedirects() so the redirect response is captured here
	 * rather than chased to the (possibly off-site) target.
	 *
	 * @param string $needle
	 * @return void
	 */
	public function seeNoRedirectTo($needle)
	{
		$response = $this->getModule('PhpBrowser')->client->getInternalResponse();
		$location = (string) $response->getHeader('Location');
		\PHPUnit\Framework\Assert::assertStringNotContainsString(
			$needle, $location, "Response must not redirect to: $needle");
	}

	/**
	 * Clear the installer's resume cookie at the path it was actually set on.
	 *
	 * The installer scopes e107install_state to e_HTTP (the app's base path,
	 * e.g. /e107/), but Codeception's resetCookie() defaults to "/" and so
	 * leaves the app-path cookie in the jar. Derive the base path from the
	 * suite URL and expire the cookie there (and at "/").
	 *
	 * @return void
	 */
	public function resetInstallStateCookie()
	{
		$browser = $this->getModule('PhpBrowser');
		$base = parse_url((string) $browser->_getConfig('url'), PHP_URL_PATH);
		if (!is_string($base) || $base === '')
		{
			$base = '/';
		}

		// Clear the base path with and without a trailing slash, plus "/", so
		// the jar entry is removed however e_HTTP and the suite URL normalise it.
		$paths = ['/'];
		if ($base !== '/')
		{
			$paths[] = '/'.trim($base, '/');
			$paths[] = '/'.trim($base, '/').'/';
		}
		foreach (array_unique($paths) as $path)
		{
			$browser->resetCookie('e107install_state', ['path' => $path]);
		}
	}

	/**
	 * Write an arbitrary file into the deployed docroot.
	 *
	 * Goes through the deployer rather than file_put_contents() so it works
	 * when the app under test is remote (CI deploys over SFTP). Parent
	 * directories are created.
	 *
	 * @param string $relative_path path relative to the app root
	 * @param string $contents
	 * @return void
	 */
	public function writeAppFile($relative_path, $contents)
	{
		$this->deployer->writeAppFile($relative_path, $contents);
	}

	/**
	 * Remove a file previously written by writeAppFile().
	 *
	 * @param string $relative_path path relative to the app root
	 * @return void
	 */
	public function deleteAppFile($relative_path)
	{
		$this->deployer->unlinkAppFile($relative_path);
	}

	/**
	 * Create a bundled plugin's tables from its own <plugin>_sql.php.
	 *
	 * A fresh install only creates the core schema, so a test needing a plugin
	 * table would otherwise have to drive the whole plugin manager. Reading the
	 * plugin's shipped SQL keeps the schema honest without that detour.
	 * Existing tables are left alone.
	 *
	 * @param string $plugin plugin folder name, e.g. 'download'
	 * @param string $prefix table prefix used by the site under test
	 * @return void
	 */
	public function havePluginTables($plugin, $prefix = 'e107_')
	{
		$sqlFile = APP_PATH."/e107_plugins/$plugin/{$plugin}_sql.php";
		if (!is_readable($sqlFile))
		{
			throw new \RuntimeException("No SQL file for plugin \"$plugin\" at $sqlFile");
		}

		$dbh = $this->getModule('\Helper\DelayedDb')->_getDbh();

		// Statements look like: CREATE TABLE <name> ( ... ) ENGINE=...;
		$found = preg_match_all(
			'/CREATE\s+TABLE\s+`?(\w+)`?\s*\((.*?)\)\s*(ENGINE|TYPE)\s*=\s*\w+\s*;/is',
			file_get_contents($sqlFile), $matches, PREG_SET_ORDER);

		if (!$found)
		{
			throw new \RuntimeException("No CREATE TABLE statements found in $sqlFile");
		}

		foreach ($matches as $match)
		{
			$table = $prefix.$match[1];
			$dbh->exec("CREATE TABLE IF NOT EXISTS `$table` ({$match[2]}) ENGINE=MyISAM");
		}
	}

	protected function writeLocalE107Config()
	{
		// Noop
		// Acceptance tests will install the app themselves
	}

	public function unlinkE107ConfigFromTestEnvironment()
	{
		$this->deployer->unlinkAppFile("e107_config.php");
	}

	public function writeE107ConfigToTestEnvironment($contents)
	{
		$this->deployer->writeAppFile("e107_config.php", $contents);
	}
}
