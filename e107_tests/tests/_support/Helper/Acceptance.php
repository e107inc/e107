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

	/**
	 * Dropped into the docroot for as long as a plugin install is needed.
	 * Registered in Extension\WorkspaceCleanup so a crashed run does not leave
	 * it there.
	 */
	const PLUGIN_PROBE_FILE = 'e107_tests_plugin_install_probe.php';

	protected $deployer_components = ['db', 'fs'];

	/** @var bool */
	private $pluginProbeWritten = false;

	/** @var array plugin folders this suite has already installed */
	private $pluginsInstalled = [];

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
	 * @param array $files entries as PHP presents them in $_FILES, e.g.
	 *   ['file_userfile' => [['tmp_name' => '/tmp/x', 'name' => 'x.pdf', 'type' => 'application/pdf']]].
	 *   A non-empty list makes the request multipart/form-data.
	 * @return void
	 */
	public function sendPostRequest($uri, array $params = [], array $files = [])
	{
		$this->getModule('PhpBrowser')->_request('POST', $uri, $params, $files);
	}

	/**
	 * Send a multipart POST carrying uploaded files.
	 *
	 * attachFile() needs a rendered file input to hang off, and e107 renders
	 * several of them only when a preference says so, so a test about what the
	 * upload handler does with a file would first have to rewrite the site's
	 * configuration to make the field appear. The field is not what is under
	 * test; the bytes PHP puts in $_FILES are.
	 *
	 * @param string $uri
	 * @param array $params ordinary form fields
	 * @param array $files as $_FILES would hold them, keyed by input name, each
	 *                     entry an array of name/type/error/size/tmp_name
	 * @return void
	 */
	public function sendPostRequestWithFiles($uri, array $params = [], array $files = [])
	{
		$this->getModule('PhpBrowser')->_request('POST', $uri, $params, $files);
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
	 * The status code of the last response.
	 *
	 * PhpBrowser offers seeResponseCodeIs() and nothing that hands the code
	 * back, so a test that has to compare two responses to each other rather
	 * than to a literal has nowhere to read it from. Pair with
	 * $I->stopFollowingRedirects(), or the code returned is the one at the end
	 * of the redirect chain.
	 *
	 * @return int
	 */
	public function grabResponseCode()
	{
		return $this->getModule('PhpBrowser')->client->getInternalResponse()->getStatusCode();
	}

	/**
	 * The bytes of the last response, exactly as they arrived.
	 *
	 * grabPageSource() goes through the page's own accessor, which is fine for
	 * a document and wrong for anything else: a test about an image, a
	 * downloaded file or a truncated error page needs the octets rather than a
	 * view of them.
	 *
	 * @return string
	 */
	public function grabResponseBody()
	{
		return (string) $this->getModule('PhpBrowser')->client->getInternalResponse()->getContent();
	}

	/**
	 * A response header, or '' when the response did not carry one.
	 *
	 * Codeception 5's PhpBrowser has no header accessor of any kind, so a test
	 * about a security header has nowhere to read one from.
	 *
	 * @param string $name header name, case-insensitive
	 * @return string
	 */
	public function grabHttpHeader($name)
	{
		$response = $this->getModule('PhpBrowser')->client->getInternalResponse();

		return (string) $response->getHeader($name);
	}

	/**
	 * Assert the last response carried $name, and that its value is exactly
	 * $value when one is given.
	 *
	 * Exact rather than a substring test: every call site in the security suite
	 * passes a complete header value, and a substring test would let
	 * "attachment; filename=secret.txt.html" satisfy an assertion written for
	 * "attachment; filename=secret.txt".
	 *
	 * @param string      $name
	 * @param string|null $value
	 * @return void
	 */
	public function seeHttpHeader($name, $value = null)
	{
		$actual = $this->grabHttpHeader($name);

		\PHPUnit\Framework\Assert::assertNotSame('', $actual,
			"Response is missing the $name header.");

		if ($value !== null)
		{
			\PHPUnit\Framework\Assert::assertSame($value, $actual,
				"Response header $name is \"$actual\", expected \"$value\".");
		}
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
	 * Assert a table is absent from the schema.
	 *
	 * Codeception's Db module asserts about rows, so seeNumRecords(0, $table)
	 * is the closest it offers and it passes just as readily on an empty table
	 * that is still there. Proving an uninstall dropped something needs a
	 * question about the schema instead.
	 *
	 * @param string $table fully prefixed table name
	 * @return void
	 */
	public function dontSeeTableInDatabase($table)
	{
		$dbh = $this->getModule('\Helper\DelayedDb')->_getDbh();

		$statement = $dbh->prepare('SHOW TABLES LIKE ?');
		$statement->execute([$table]);

		\PHPUnit\Framework\Assert::assertFalse(
			$statement->fetchColumn(), "Table `$table` still exists.");
	}

	/**
	 * Create a bundled plugin's tables from its own <plugin>_sql.php.
	 *
	 * A fresh install only creates the core schema, so a test needing a plugin
	 * table would otherwise have to drive the whole plugin manager. Reading the
	 * plugin's shipped SQL keeps the schema honest without that detour.
	 * Existing tables are left alone.
	 *
	 * Tables alone do not make a plugin reachable. Reach for
	 * {@see havePluginInstalled()} instead whenever the page under test is one
	 * the plugin serves.
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
	 * Install a bundled plugin the way the plugin manager installs it.
	 *
	 * Prefer this to havePluginTables() whenever the page under test belongs to
	 * the plugin. Tables are not what a plugin's front end asks for: it opens
	 * with e107::isInstalled(), which is a single lookup of the plug_installed
	 * core preference, and havePluginTables() never writes that. A request to a
	 * plugin nobody installed is therefore turned away because the plugin is
	 * absent, which is indistinguishable from the authorisation refusal a
	 * security test believes it has just proved.
	 *
	 * Driven through the application's own installer rather than written by
	 * hand. install_plugin_xml() sets plugin_installflag, the plug_installed
	 * preference, the tables, admin and site links, user classes, extended
	 * fields, plugin preferences and search indexes, and an imitation of that
	 * would drift the first time the plugin manager changed.
	 *
	 * Costs one request and browses to the probe, so call it before navigating
	 * to the page under test rather than after. Repeat calls are free.
	 *
	 * @param string $plugin plugin folder name, e.g. 'poll'
	 * @return void
	 */
	public function havePluginInstalled($plugin)
	{
		if (!empty($this->pluginsInstalled[$plugin]))
		{
			return;
		}

		$this->runPluginProbe('install', $plugin);
		$this->pluginsInstalled[$plugin] = true;
	}

	/**
	 * Uninstall a plugin and drop its tables, leaving the state a fresh install
	 * leaves. Safe to call for a plugin that was never installed.
	 *
	 * @param string $plugin plugin folder name
	 * @return void
	 */
	public function dropPluginInstall($plugin)
	{
		$this->runPluginProbe('uninstall', $plugin);
		unset($this->pluginsInstalled[$plugin]);
	}

	/**
	 * Remove the probe from the docroot. Call from a Cest's _after().
	 *
	 * @return void
	 */
	public function dropPluginProbe()
	{
		if (!$this->pluginProbeWritten)
		{
			return;
		}

		$this->deleteAppFile(self::PLUGIN_PROBE_FILE);
		$this->pluginProbeWritten = false;
	}

	/**
	 * @param string $act install|uninstall
	 * @param string $plugin plugin folder name
	 * @return string probe output
	 */
	private function runPluginProbe($act, $plugin)
	{
		if (!$this->pluginProbeWritten)
		{
			$this->writeAppFile(self::PLUGIN_PROBE_FILE, self::pluginProbeSource());
			$this->pluginProbeWritten = true;
		}

		$browser = $this->getModule('PhpBrowser');
		$browser->amOnPage('/'.self::PLUGIN_PROBE_FILE.'?act='.$act.'&plugin='.urlencode($plugin));

		$body = $browser->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException(
				"Plugin probe failed for \"$act $plugin\": ".trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * @return string
	 */
	private static function pluginProbeSource()
	{
		return <<<'PHP'
<?php
// Fixture for Helper\Acceptance::havePluginInstalled(). Removed in dropPluginProbe().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$folder = isset($_GET['plugin']) ? preg_replace('/[^\w-]/', '', $_GET['plugin']) : '';

if($folder === '' || !is_readable(e_PLUGIN.$folder.'/plugin.xml'))
{
	echo 'no plugin.xml for "'.$folder."\"\n";
	exit;
}

/**
 * What e107::isInstalled() will answer on the next request.
 *
 * Not e107::isInstalled() itself. That reads plug_installed through a path, and
 * e_model memoises every path it resolves without invalidating the memo when an
 * ancestor key is rewritten. install_plugin_xml() rewrites the whole of
 * plug_installed, so one call before an uninstall makes every call after it in
 * the same request go on answering true.
 *
 * @param string $folder
 * @return bool
 */
function e107_test_plugin_installed($folder)
{
	$installed = e107::getConfig('core')->get('plug_installed');

	return is_array($installed) && isset($installed[$folder]);
}

$plugin = e107::getPlugin();

// install_plugin_xml() resolves a folder to a plugin_id through the plugin
// table, and update_plugins_table() is what puts a folder in it.
if(!e107::getDb()->createQueryBuilder()->from('plugin')->where('plugin_path', $folder)->count())
{
	$plugin->update_plugins_table('update');
}

$changed = false;

switch($act)
{
	case 'install':
		if(!e107_test_plugin_installed($folder))
		{
			$plugin->install_plugin_xml($folder, 'install');
			$changed = true;
		}
		break;

	case 'uninstall':
		if(e107_test_plugin_installed($folder))
		{
			$plugin->install_plugin_xml($folder, 'uninstall', array('delete_tables' => true));
			$changed = true;
		}
		break;

	default:
		echo "unknown action\n";
		exit;
}

if($changed)
{
	// Belt and braces. Both of these have been commented in and out of
	// install_plugin_xml() over the years, and repeating one it already did
	// costs this request nothing.
	e107::getPlug()->clearCache()->buildAddonPrefLists();

	foreach(glob(e_CACHE_CONTENT.'S_Config_*.cache.php') ?: array() as $file)
	{
		@unlink($file);
	}
}

$installed = e107_test_plugin_installed($folder);

if($installed === ($act === 'install'))
{
	echo "PROBE_OK ".$folder." installed=".($installed ? 1 : 0)."\n";
}
else
{
	echo 'could not '.$act.' "'.$folder."\"\n";
}
PHP;
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
