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
 * The path the session cookie is scoped to, read for each spelling {@see e107::set_paths()} gives the site's web path.
 */
class e107WebPathCookieTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/**
	 * @dataProvider webPaths
	 * @param string $webPath what e_HTTP holds
	 * @param string $expected the folders that the web path and the same path with its folders decoded both begin with
	 */
	public function testTheSessionCookieIsSentForEverySpellingOfTheWebPath($webPath, $expected)
	{
		self::assertSame($expected, $this->sessionCookiePathFor($webPath), "the session cookie's path for the web path $webPath");
	}

	/**
	 * @return array[] what {@see e107WebPathCookieTest::testTheSessionCookieIsSentForEverySpellingOfTheWebPath()} takes
	 */
	public function webPaths()
	{
		return array(
			'the root'                          => array('/', '/'),
			'a folder'                          => array('/e107/', '/e107/'),
			'a folder of - . _ ~'               => array('/al-site_2.0~x/', '/al-site_2.0~x/'),
			"a real folder's own apostrophe"    => array("/o'brien/", "/o'brien/"),
			'an encoded @'                      => array('/al%40site/', '/'),
			'an encoded +'                      => array('/al%2Bsite/', '/'),
			'an encoded @ below a folder'       => array('/blog/al%40site/', '/blog/'),
			'an encoded folder below another'   => array('/sub%20dir/zq9%22mk/', '/'),
			'an encoded semicolon'              => array('/a%3Bb/', '/'),
			'an encoded comma'                  => array('/a%2Cb/', '/'),
			'an encoded tab'                    => array('/a%09b/', '/'),
		);
	}

	/**
	 * @param string $webPath what e_HTTP holds
	 * @return string the path PHP is told to send the session cookie with once {@see e_session::start()} has run
	 */
	private function sessionCookiePathFor($webPath)
	{
		$root = rtrim(APP_PATH, '/\\');

		$php = "define('e107_INIT', true); "
			."define('e_HANDLER', ".var_export($root.'/e107_handlers/', true)."); "
			."define('e_HTTP', ".var_export($webPath, true)."); "
			."require ".var_export($root.'/e107_handlers/core_functions.php', true)."; "
			."require ".var_export($root.'/e107_handlers/e107_class.php', true)."; "
			."require ".var_export($root.'/e107_handlers/session_handler.php', true)."; "
			."\$session = new e_session(); "
			."\$session->start(); "
			."\$params = session_get_cookie_params(); "
			."session_destroy(); "
			."echo json_encode(\$params['path']), \"\\n\";";

		list($lines, $status) = $this->runInCli($php, '-d display_errors=1 -d log_errors=0 -d error_reporting='.(E_ALL & ~E_DEPRECATED));

		self::assertSame(0, $status, "the probe exited with status $status:\n".implode("\n", $lines));
		self::assertCount(1, $lines, "the probe raised a diagnostic:\n".implode("\n", $lines));

		return json_decode($lines[0]);
	}
}
