<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * The site's web path that {@see e107::set_paths()} derives from the script name the server hands over.
 */
class e107WebRootPathTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** @var string */
	private $scratch = '';

	/** @var string[] the folders and files {@see e107WebRootPathTest::addToScratch()} created, in the order it created them */
	private $made = array();

	protected function _after()
	{
		foreach(array_reverse($this->made) as $path)
		{
			if(is_dir($path))
			{
				rmdir($path);
			}
			else
			{
				unlink($path);
			}
		}

		$this->made = array();
		$this->scratch = '';
	}

	public function testACleanScriptNameKeepsItsWebPath()
	{
		foreach($this->cleanRequests() as $row)
		{
			list($install, $scriptName, $scriptFolder, $expected) = $row;

			self::assertSame($expected, $this->serverPathFor($scriptName, $scriptFolder, $install),
				"the web path of the script name $scriptName changed");
		}
	}

	public function testPathInfoAfterTheScriptIsNotPartOfTheWebPath()
	{
		$pathInfos = array('/f6"x/y.php', '/f6"x/y', "/f6'x/<y>{z}/w", '/a.php/b.php/c');

		foreach($this->cleanRequests() as $row)
		{
			list($install, $scriptName, $scriptFolder, $expected) = $row;

			foreach($pathInfos as $pathInfo)
			{
				self::assertSame($expected, $this->serverPathFor($scriptName.$pathInfo, $scriptFolder, $install),
					"the path info in the script name $scriptName$pathInfo reached the web path");
			}
		}

		self::assertSame('/e107/', $this->serverPathFor('/e107/INDEX.PHP/f6"x/y'),
			'the path info after an upper-case script name reached the web path');
	}

	public function testTheRequestCannotChooseAWebPathPastTheRunningScript()
	{
		$rows = array(
			'/news.php/www/index.php'      => '/',
			'/blog/news.php/www/index.php' => '/blog/',
		);

		foreach($rows as $scriptName => $expected)
		{
			self::assertSame($expected, $this->serverPathFor($scriptName, '', 'blog/news.php/www'),
				"the folders above the install repeat the names in $scriptName, and the web path followed them");
		}
	}

	/**
	 * @dataProvider bareRequestPaths
	 * @param string $scriptName the request's path, which a front controller hands over as SCRIPT_NAME
	 * @param string $scriptFolder the front controller's folder within the install
	 * @param string $scriptFile the front controller, which SCRIPT_FILENAME names
	 * @param string $expected
	 * @param string $install where {@see e107WebRootPathTest::installAt()} lays out the install, '' for the suite's own
	 */
	public function testAWebPathOtherThanTheInstallsFolderHoldsOnlyUrlSafeCharacters($scriptName, $scriptFolder, $scriptFile, $expected, $install = '')
	{
		self::assertSame($expected, $this->serverPathFor($scriptName, $scriptFolder, $install, $scriptFile),
			"the web path of the request $scriptName run by $scriptFolder/$scriptFile");
	}

	/**
	 * @return array[] what {@see e107WebRootPathTest::testAWebPathOtherThanTheInstallsFolderHoldsOnlyUrlSafeCharacters()} takes
	 */
	public function bareRequestPaths()
	{
		return array(
			'root'                          => array('/zq9"mk/y', '', 'index.php', '/zq9%22mk/'),
			'root, every character'         => array('/zq9"\'<>{}mk/y', '', 'index.php', '/zq9%22%27%3C%3E%7B%7Dmk/'),
			'subfolder'                     => array('/sub dir/zq9"\'<>mk/y', '', 'index.php', '/sub%20dir/zq9%22%27%3C%3Emk/'),
			'admin script'                  => array('/e107_admin/zq9"\'<>mk/x/y', 'e107_admin', 'admin.php', '/e107_admin/zq9%22%27%3C%3Emk/'),
			'plugin script'                 => array('/e107_plugins/forum/zq9"\'<>mk/x/y/z', 'e107_plugins/forum', 'forum.php', '/e107_plugins/forum/zq9%22%27%3C%3Emk/'),
			'root, clean'                   => array('/zq9mk/y', '', 'index.php', '/zq9mk/'),
			'root, already percent-encoded' => array('/zq9%22mk/y', '', 'index.php', '/zq9%22mk/'),
			'root, braces already encoded'  => array('/zq9%7Bmk%7D/y', '', 'index.php', '/zq9%7Bmk%7D/'),
			'subfolder, clean'              => array('/sub dir/zq9mk/y', '', 'index.php', '/sub%20dir/zq9mk/'),
			'admin script, clean'           => array('/e107_admin/zq9mk/x/y', 'e107_admin', 'admin.php', '/e107_admin/zq9mk/'),
			'plugin script, clean'          => array('/e107_plugins/forum/zq9mk/x/y/z', 'e107_plugins/forum', 'forum.php', '/e107_plugins/forum/zq9mk/'),
			'below a folder with braces'    => array('/{site}/zq9mk/y', '', 'index.php', '/%7Bsite%7D/zq9mk/', '{site}'),
			'root, an apostrophe reference' => array('/&apos;zq9mk/y', '', 'index.php', '/%26apos%3Bzq9mk/'),
			'root, a space'                 => array('/zq9 mk/y', '', 'index.php', '/zq9%20mk/'),
			'root, a non-ASCII letter'      => array("/zq9\xC3\xA9mk/y", '', 'index.php', '/zq9%C3%A9mk/'),
		);
	}

	/**
	 * @dataProvider requestPathsAimedAtAnotherHost
	 * @param string $scriptName the request's path, which a front controller hands over as SCRIPT_NAME
	 * @param string $expected
	 */
	public function testAWebPathTakenFromTheRequestStaysOnTheSitesHost($scriptName, $expected)
	{
		self::assertSame($expected, $this->serverPathFor($scriptName, '', '', 'index.php'),
			"the web path of the request $scriptName run by index.php");
	}

	/**
	 * @return array[] what {@see e107WebRootPathTest::testAWebPathTakenFromTheRequestStaysOnTheSitesHost()} takes
	 */
	public function requestPathsAimedAtAnotherHost()
	{
		return array(
			'a backslash'                               => array('/\\zq9.invalid/x/y', '/zq9.invalid/x/'),
			'two backslashes'                           => array('/\\\\zq9.invalid/x/y', '/zq9.invalid/x/'),
			'a slash and a backslash'                   => array('//\\zq9.invalid/x/y', '/zq9.invalid/x/'),
			'a backslash and a slash'                   => array('/\\/zq9.invalid/x/y', '/zq9.invalid/x/'),
			'a backslash below a subfolder'             => array('/sub dir/\\zq9.invalid/x/y', '/sub%20dir//zq9.invalid/x/'),
			'a tab after the first slash'               => array("/\t/zq9.invalid/x/y", '/%09/zq9.invalid/x/'),
			'a line feed after the first slash'         => array("/\n/zq9.invalid/x/y", '/%0A/zq9.invalid/x/'),
			'a carriage return after the first slash'   => array("/\r/zq9.invalid/x/y", '/%0D/zq9.invalid/x/'),
			'a tab between doubled slashes'             => array("//\t//zq9.invalid/x/y", '/%09//zq9.invalid/x/'),
			'a line feed between doubled slashes'       => array("//\n//zq9.invalid/x/y", '/%0A//zq9.invalid/x/'),
			'a carriage return between doubled slashes' => array("//\r//zq9.invalid/x/y", '/%0D//zq9.invalid/x/'),
			'a slash as a named reference'              => array('/&sol;zq9.invalid/x/y', '/%26sol%3Bzq9.invalid/x/'),
			'a slash as a decimal reference'            => array('/&#47;zq9.invalid/x/y', '/%26%2347%3Bzq9.invalid/x/'),
			'a slash as a hexadecimal reference'        => array('/&#x2F;zq9.invalid/x/y', '/%26%23x2F%3Bzq9.invalid/x/'),
			'a tab as a named reference'                => array('/&Tab;/zq9.invalid/x/y', '/%26Tab%3B/zq9.invalid/x/'),
		);
	}

	/**
	 * @dataProvider requestsThroughARenamedFolder
	 * @param string $install where {@see e107WebRootPathTest::installAt()} lays out the install, '' for the suite's own
	 * @param string $scriptName SCRIPT_NAME and PHP_SELF as Apache hands them over, its folders decoded
	 * @param string $requestUri the request URI in the spelling the browser sent
	 * @param string $scriptFolder the running script's folder within the install
	 * @param array $expected e_SELF, e_REQUEST_URI and e_ADMIN_AREA
	 * @param string $header the server variable the request URI arrives in
	 */
	public function testARequestNamesTheSitesFoldersAsTheWebPathDoes($install, $scriptName, $requestUri, $scriptFolder, $expected, $header = 'REQUEST_URI')
	{
		self::assertSame($expected, $this->requestConstantsFor($install, $scriptName, $requestUri, $scriptFolder, $header),
			"the request $requestUri for the script $scriptName");
	}

	/**
	 * @return array[] what {@see e107WebRootPathTest::testARequestNamesTheSitesFoldersAsTheWebPathDoes()} takes
	 */
	public function requestsThroughARenamedFolder()
	{
		$adminAt = array(
			'e_SELF'        => 'http://localhost/al%40site/e107_admin/admin.php',
			'e_REQUEST_URI' => '/al%40site/e107_admin/admin.php?x=1',
			'e_ADMIN_AREA'  => true,
		);

		return array(
			'an @ folder, its admin page typed'           => array('', '/al@site/e107_admin/admin.php', '/al@site/e107_admin/admin.php?x=1', 'e107_admin', $adminAt),
			'an @ folder, its admin page as e107 links it' => array('', '/al@site/e107_admin/admin.php', '/al%40site/e107_admin/admin.php?x=1', 'e107_admin', $adminAt),
			'an @ folder, its admin page typed, from IIS' => array('', '/al@site/e107_admin/admin.php', '/al@site/e107_admin/admin.php?x=1', 'e107_admin', $adminAt, 'HTTP_X_REWRITE_URL'),
			'a + folder, its login page typed'            => array('', '/al+site/login.php', '/al+site/login.php', '', array(
				'e_SELF'        => 'http://localhost/al%2Bsite/login.php',
				'e_REQUEST_URI' => '/al%2Bsite/login.php',
				'e_ADMIN_AREA'  => false,
			)),
			'a + folder, a plugin admin page typed'       => array('', '/al+site/e107_plugins/forum/forum_admin.php', '/al+site/e107_plugins/forum/forum_admin.php', 'e107_plugins/forum', array(
				'e_SELF'        => 'http://localhost/al%2Bsite/e107_plugins/forum/forum_admin.php',
				'e_REQUEST_URI' => '/al%2Bsite/e107_plugins/forum/forum_admin.php',
				'e_ADMIN_AREA'  => true,
			)),
			'a folder of - . _ ~, its admin page typed'   => array('', '/al-site_2.0~x/e107_admin/admin.php', '/al-site_2.0~x/e107_admin/admin.php', 'e107_admin', array(
				'e_SELF'        => 'http://localhost/al-site_2.0~x/e107_admin/admin.php',
				'e_REQUEST_URI' => '/al-site_2.0~x/e107_admin/admin.php',
				'e_ADMIN_AREA'  => true,
			)),
			"a real folder's own apostrophe"              => array("o'brien", "/o'brien/e107_admin/admin.php", "/o'brien/e107_admin/admin.php", 'e107_admin', array(
				'e_SELF'        => "http://localhost/o'brien/e107_admin/admin.php",
				'e_REQUEST_URI' => '/o%27brien/e107_admin/admin.php',
				'e_ADMIN_AREA'  => true,
			)),
		);
	}

	/**
	 * @return array[] the install ('' for the suite's own), the script name of a request without path info, the
	 *                 running script's folder within the install, and the web path that request gets
	 */
	private function cleanRequests()
	{
		return array(
			array('', '/index.php', '', '/'),
			array('', '/e107/news.php', '', '/e107/'),
			array('e107/sub dir', '/e107/sub dir/news.php', '', '/e107/sub dir/'),
			array('', '/e107_admin/admin.php', 'e107_admin', '/'),
			array('', '/e107/e107_admin/admin.php', 'e107_admin', '/e107/'),
			array('', '/e107_plugins/forum/forum.php', 'e107_plugins/forum', '/'),
			array('sub dir', '/sub dir/e107_plugins/forum/forum.php', 'e107_plugins/forum', '/sub dir/'),
			array('', '/sites/e107.php5/index.php', '', '/sites/e107.php5/'),
			array('', '/sites/my.php.site/index.php', '', '/sites/my.php.site/'),
			array('sites/e107.php', '/sites/e107.php/news.php', '', '/sites/e107.php/'),
			array('sites/e107.php', '/sites/e107.php/e107_admin/admin.php', 'e107_admin', '/sites/e107.php/'),
			array('sites/e107.php', '/sites/e107.php/e107_plugins/forum/forum.php', 'e107_plugins/forum', '/sites/e107.php/'),
			array("o'brien", "/o'brien/news.php", '', "/o'brien/"),
			array("o'brien", "/o'brien/e107_admin/admin.php", 'e107_admin', "/o'brien/"),
			array("o'brien", "/o'brien/e107_plugins/forum/forum.php", 'e107_plugins/forum', "/o'brien/"),
			array('{site}', '/{site}/news.php', '', '/{site}/'),
			array('{site}', '/{site}/e107_admin/admin.php', 'e107_admin', '/{site}/'),
			array('{site}', '/{site}/e107_plugins/forum/forum.php', 'e107_plugins/forum', '/{site}/'),
		);
	}

	/**
	 * @param string $scriptName what the server sets SCRIPT_NAME to
	 * @param string $scriptFolder the running script's folder within the install
	 * @param string $install where {@see e107WebRootPathTest::installAt()} lays out the install, '' for the suite's own
	 * @param string $scriptFile what the server sets SCRIPT_FILENAME to in the running script's folder, which set_paths() does not read; '' to leave it unset
	 * @return string the server_path set_paths() derives from it
	 */
	private function serverPathFor($scriptName, $scriptFolder = '', $install = '', $scriptFile = '')
	{
		return $this->readAfterSetPaths($scriptName, $scriptFolder, $install, $scriptFile, '', '$e107->server_path');
	}

	/**
	 * @param string $install where {@see e107WebRootPathTest::installAt()} lays out the install, '' for the suite's own
	 * @param string $scriptName what the server sets SCRIPT_NAME and PHP_SELF to
	 * @param string $requestUri the request URI
	 * @param string $scriptFolder the running script's folder within the install
	 * @param string $header the server variable the request URI arrives in, REQUEST_URI or IIS's HTTP_X_REWRITE_URL
	 * @return array e_SELF, e_REQUEST_URI and e_ADMIN_AREA as {@see e107::set_urls()} defines them
	 */
	private function requestConstantsFor($install, $scriptName, $requestUri, $scriptFolder, $header)
	{
		$then = "\$_SERVER['PHP_SELF'] = \$_SERVER['SCRIPT_NAME']; "
			."\$_SERVER[".var_export($header, true)."] = ".self::asciiExpressionFor($requestUri)."; "
			."\$e107->set_base_path(false); "
			."\$e107->set_urls(); ";

		return $this->readAfterSetPaths($scriptName, $scriptFolder, $install, basename($scriptName), $then,
			"array('e_SELF' => e_SELF, 'e_REQUEST_URI' => e_REQUEST_URI, 'e_ADMIN_AREA' => e_ADMIN_AREA)");
	}

	/**
	 * Runs {@see e107::set_paths()} in a child process for one script name, then $then, and hands back the value of $read.
	 *
	 * @param string $scriptName what the server sets SCRIPT_NAME to
	 * @param string $scriptFolder the running script's folder within the install
	 * @param string $install where {@see e107WebRootPathTest::installAt()} lays out the install, '' for the suite's own
	 * @param string $scriptFile what the server sets SCRIPT_FILENAME to in the running script's folder; '' to leave it unset
	 * @param string $then statements the child runs once set_paths() has, with the e107 instance in $e107
	 * @param string $read a PHP expression the child evaluates last
	 * @return mixed
	 */
	private function readAfterSetPaths($scriptName, $scriptFolder, $install, $scriptFile, $then, $read)
	{
		$root = ($install === '') ? self::suiteRoot() : $this->installAt($install);
		$folder = rtrim($root.'/'.$scriptFolder, '/');

		$php = "define('e107_INIT', true); "
			."define('e_ROOT', ".var_export($folder.'/', true)."); "
			."\$_SERVER['SCRIPT_NAME'] = ".self::asciiExpressionFor($scriptName)."; "
			.(($scriptFile === '') ? '' : "\$_SERVER['SCRIPT_FILENAME'] = ".var_export($folder.'/'.$scriptFile, true)."; ")
			."\$_SERVER['HTTP_HOST'] = 'localhost'; "
			."require ".var_export(self::suiteRoot().'/e107_handlers/core_functions.php', true)."; "
			."require ".var_export($root.'/e107_handlers/e107_class.php', true)."; "
			."\$e107 = e107::getInstance(); "
			."\$e107->setDirs(array()); "
			."\$e107->set_paths(); "
			.$then
			."echo json_encode(".$read."), \"\\n\";";

		list($lines, $status) = $this->runInCli($php, '-d display_errors=1 -d log_errors=0 -d error_reporting='.(E_ALL & ~E_DEPRECATED));

		self::assertSame(0, $status, "the child exited with status $status:\n".implode("\n", $lines));
		self::assertCount(1, $lines, "the child raised a diagnostic:\n".implode("\n", $lines));

		return json_decode($lines[0], true);
	}

	/**
	 * @return string the suite's own install, without a trailing slash
	 */
	private static function suiteRoot()
	{
		return rtrim(APP_PATH, '/\\');
	}

	/**
	 * @param string $bytes
	 * @return string a PHP expression for $bytes made of ASCII alone, which escapeshellarg() passes whole in any locale
	 */
	private static function asciiExpressionFor($bytes)
	{
		return 'base64_decode('.var_export(base64_encode($bytes), true).')';
	}

	/**
	 * Lays out the entry scripts of an install under a scratch folder, with a copy of the e107 class beside them.
	 *
	 * @param string $install the install's folder under the scratch folder
	 * @return string the install's absolute path
	 */
	private function installAt($install)
	{
		if($this->scratch === '')
		{
			$this->scratch = sys_get_temp_dir().'/e107WebRootPathTest-'.uniqid('', true);
			$this->addToScratch($this->scratch);
		}

		$root = $this->scratch.'/'.$install;
		if(is_file($root.'/e107_handlers/e107_class.php'))
		{
			return $root;
		}

		$path = $this->scratch;
		foreach(explode('/', $install) as $folder)
		{
			$path .= '/'.$folder;
			$this->addToScratch($path);
		}
		foreach(array('e107_handlers', 'e107_admin', 'e107_plugins', 'e107_plugins/forum') as $folder)
		{
			$this->addToScratch($root.'/'.$folder);
		}

		foreach(array('class2.php', 'index.php', 'news.php', 'e107_admin/admin.php', 'e107_plugins/forum/forum.php') as $script)
		{
			$this->addToScratch($root.'/'.$script, '');
		}
		$this->addToScratch($root.'/e107_handlers/e107_class.php', file_get_contents(self::suiteRoot().'/e107_handlers/e107_class.php'));

		return $root;
	}

	/**
	 * Creates a folder, or a file holding $contents, for {@see e107WebRootPathTest::_after()} to remove.
	 *
	 * @param string $path
	 * @param string|null $contents null for a folder
	 * @return void
	 */
	private function addToScratch($path, $contents = null)
	{
		if(file_exists($path))
		{
			return;
		}

		$made = ($contents === null) ? mkdir($path) : (file_put_contents($path, $contents) === strlen($contents));
		self::assertTrue($made, "could not create $path");

		$this->made[] = $path;
	}
}
