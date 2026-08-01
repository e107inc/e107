<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends E107Base
{
	/**
	 * Copy of db_verify's storage engine aliases, so a table this helper creates
	 * uses the same engine e107 would have chosen for it.
	 *
	 * Duplicated rather than read from the handler because the acceptance suite
	 * runs outside the application and cannot boot it. helperAcceptanceTest
	 * compares this against the handler's own map, so the two cannot drift.
	 *
	 * @see db_verify::$storageEnginePreferenceMap
	 */
	const STORAGE_ENGINE_PREFERENCE = [
		'MyISAM' => ['InnoDB', 'Aria', 'Maria', 'MyISAM'],
		'Aria'   => ['Aria', 'Maria', 'MyISAM'],
		'InnoDB' => ['InnoDB', 'XtraDB'],
		'XtraDB' => ['XtraDB', 'InnoDB'],
	];

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

		$tables = self::parseCreateTableStatements(file_get_contents($sqlFile));

		if (!$tables)
		{
			throw new \RuntimeException("No CREATE TABLE statements found in $sqlFile");
		}

		$available = $this->availableStorageEngines($dbh);

		foreach ($tables as $table)
		{
			$name = $prefix.$table['name'];
			$engine = self::intendedStorageEngine($table['engine'], $available);

			if ($engine === false)
			{
				throw new \RuntimeException(
					"No storage engine on this server can stand in for {$table['engine']}, wanted by `$name`");
			}

			$dbh->exec("CREATE TABLE IF NOT EXISTS `$name` ({$table['body']}) ENGINE=$engine");
		}
	}

	/**
	 * Every engine the server reports, unfiltered.
	 *
	 * db_verify::getAvailableStorageEngines() takes every row of SHOW ENGINES
	 * without looking at the support column, so this does too. Filtering here
	 * would make the helper pick a different engine from the one e107 picks.
	 *
	 * @param \PDO $dbh
	 * @return array
	 */
	private function availableStorageEngines(\PDO $dbh)
	{
		$engines = array();

		foreach ($dbh->query('SHOW ENGINES') as $row)
		{
			$engines[] = $row['Engine'];
		}

		return $engines;
	}

	/**
	 * Resolve a declared engine the way e107 resolves it at install time.
	 *
	 * A schema saying MyISAM does not get MyISAM. db_verify treats the declared
	 * engine as a request and satisfies it with the first entry of
	 * STORAGE_ENGINE_PREFERENCE the server actually has, so on any current MySQL
	 * or MariaDB the bundled MyISAM schemas are installed as InnoDB. Creating
	 * the test's copy as MyISAM would give it no transactions and different
	 * FULLTEXT behaviour from the table the plugin manager builds.
	 *
	 * Mirrors {@see db_verify::getIntendedStorageEngine()}; helperAcceptanceTest
	 * asserts the two preference maps stay identical.
	 *
	 * @param string $declared engine named in the plugin's SQL
	 * @param array $available engines the server reports
	 * @return string|false
	 */
	public static function intendedStorageEngine($declared, array $available)
	{
		if (strtoupper($declared) === 'MYISAM')
		{
			$declared = 'MyISAM';
		}
		elseif (strtoupper($declared) === 'INNODB')
		{
			$declared = 'InnoDB';
		}

		if (!array_key_exists($declared, self::STORAGE_ENGINE_PREFERENCE))
		{
			return in_array($declared, $available) ? $declared : false;
		}

		$fit = array_intersect(self::STORAGE_ENGINE_PREFERENCE[$declared], $available);

		return current($fit);
	}

	/**
	 * Pull every CREATE TABLE out of a plugin's shipped SQL.
	 *
	 * Statements look like: CREATE TABLE <name> ( ... ) ENGINE=... [options];
	 * The tail has to run to the semicolon rather than stop at the engine name,
	 * because forum, hero, linkwords and pm all carry table options after it
	 * (AUTO_INCREMENT, DEFAULT CHARSET). Anchoring at the engine let the lazy
	 * body run on to the next statement that did end there: forum returned a
	 * single match spanning all four of its tables, and the other three matched
	 * nothing, so havePluginTables() threw for them.
	 *
	 * The engine is captured rather than assumed. hero ships InnoDB, and forcing
	 * MyISAM would have given the table under test different semantics from the
	 * one the plugin installs.
	 *
	 * @param string $sql contents of a <plugin>_sql.php
	 * @return array list of ['name' => string, 'body' => string, 'engine' => string]
	 */
	public static function parseCreateTableStatements($sql)
	{
		$found = preg_match_all(
			'/CREATE\s+TABLE\s+`?(\w+)`?\s*\((.*?)\)\s*(?:ENGINE|TYPE)\s*=\s*(\w+)[^;]*;/is',
			$sql, $matches, PREG_SET_ORDER);

		if (!$found)
		{
			return array();
		}

		$tables = array();

		foreach ($matches as $match)
		{
			$tables[] = array('name' => $match[1], 'body' => $match[2], 'engine' => $match[3]);
		}

		return $tables;
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
