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
 * The request path that {@see e107::set_urls()} turns into e_REQUEST_URI and e_REQUEST_HTTP, and the sign-in form that posts to it.
 */
class e107RequestPathTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const PAGE = 'E107-REQUEST-PATH:';

	/**
	 * @dataProvider requestPathsAimedAtAnotherHost
	 * @param string $requestUri what the server sets REQUEST_URI to
	 */
	public function testARequestPathStaysOnTheSitesHost($requestUri)
	{
		$page = $this->renderFor($requestUri);
		$request = self::shown($requestUri);

		foreach(array('e_REQUEST_URI' => $page['uri'], 'e_REQUEST_HTTP' => $page['http']) as $constant => $value)
		{
			self::assertSame('localhost', self::hostOf($value), "$constant of the request $request names another host");
			self::assertSame('localhost', self::hostOf(self::decoded($value)),
				"$constant of the request $request names another host when printed unescaped in an attribute");
		}

		self::assertSame('localhost', self::hostOf(self::actionOf($page['form'])), "the sign-in form on the page at $request posts to another host");
	}

	/**
	 * @dataProvider cleanRequests
	 * @param string $requestUri what the server sets REQUEST_URI to
	 * @param string $path the request's path, without its query
	 */
	public function testACleanRequestKeepsItsPath($requestUri, $path)
	{
		$page = $this->renderFor($requestUri);

		self::assertSame($requestUri, $page['uri'], "e_REQUEST_URI of the request $requestUri changed");
		self::assertSame($path, $page['http'], "e_REQUEST_HTTP of the request $requestUri changed");
		self::assertSame('<form method="post" action="'.$path.'" accept-charset="UTF-8">', $page['form'],
			"the sign-in form on the page at $requestUri changed");
	}

	/**
	 * @dataProvider requestPathsHoldingAnAmpersand
	 * @param string $requestUri what the server sets REQUEST_URI to, a path without a query
	 */
	public function testTheSignInFormPostsToThePathRequested($requestUri)
	{
		$page = $this->renderFor($requestUri);

		self::assertSame($requestUri, $page['http'], "e_REQUEST_HTTP of the request $requestUri changed");
		self::assertSame($requestUri, self::actionOf($page['form']), "the sign-in form on the page at $requestUri posts to another path");
	}

	/**
	 * @return array[] what {@see e107RequestPathTest::testARequestPathStaysOnTheSitesHost()} takes
	 */
	public function requestPathsAimedAtAnotherHost()
	{
		return array(
			'two slashes'                                 => array('//zq9.invalid/x'),
			'two slashes and the host alone'              => array('//zq9.invalid/'),
			'two slashes before a script name'            => array('//zq9.invalid/news.php'),
			'a slash and a backslash'                     => array('/\\zq9.invalid/x'),
			'a reference to a slash'                      => array('/&sol;zq9.invalid/x'),
			'a reference to a backslash'                  => array('/&bsol;zq9.invalid/x'),
			'a reference to a tab between two slashes'    => array('/&Tab;/zq9.invalid/x'),
			'a tab between two slashes'                   => array("/\t/zq9.invalid/x"),
			'a line feed between two slashes'             => array("/\n/zq9.invalid/x"),
			'a carriage return between two slashes'       => array("/\r/zq9.invalid/x"),
			'a menu marker before a slash'                => array('/[en]/zq9.invalid/y?[en]'),
			'a menu marker before a reference to a slash' => array('/[en]&sol;zq9.invalid/y?[en]'),
		);
	}

	/**
	 * @return array[] what {@see e107RequestPathTest::testACleanRequestKeepsItsPath()} takes
	 */
	public function cleanRequests()
	{
		return array(
			'the front page'               => array('/', '/'),
			'a script'                     => array('/news.php', '/news.php'),
			'a path no route answers'      => array('/zq9mk/x', '/zq9mk/x'),
			'a query of two fields'        => array('/news.php?a=1&b=2', '/news.php'),
			'two slashes after the script' => array('/news.php//zq9.invalid/x', '/news.php//zq9.invalid/x'),
		);
	}

	/**
	 * @return array[] what {@see e107RequestPathTest::testTheSignInFormPostsToThePathRequested()} takes
	 */
	public function requestPathsHoldingAnAmpersand()
	{
		return array(
			'an ampersand'                      => array('/zq9mk/a&b'),
			'a reference to a slash further in' => array('/zq9mk/&sol;x'),
		);
	}

	/**
	 * @param string $requestUri what the server sets REQUEST_URI to; its part after the first "?" is the QUERY_STRING
	 * @return array e_REQUEST_URI, e_REQUEST_HTTP and the start tag of the guest's sign-in form, from a child booted for that request
	 */
	private function renderFor($requestUri)
	{
		$php = "require_once(e_PLUGIN.'signin/e_shortcode.php'); \$signin = new signin_shortcodes(); \$html = \$signin->sc_signin(); "
			."preg_match_all('/<form\\b[^>]*>(?:(?!<\\/form>).)*?name=.userpass./s', \$html, \$forms); "
			."echo \"\\n".self::PAGE."\", json_encode(array('uri' => e_REQUEST_URI, 'http' => e_REQUEST_HTTP, "
			."'forms' => array_map(function (\$form) { return substr(\$form, 0, strpos(\$form, '>') + 1); }, \$forms[0]))), \"\\n\";";

		$request = self::shown($requestUri);
		$parts = explode('?', $requestUri, 2);
		putenv('REQUEST_URI='.$requestUri);
		putenv('QUERY_STRING='.(isset($parts[1]) ? $parts[1] : ''));

		try
		{
			list($output) = $this->runInBootedCli($php);
		}
		finally
		{
			putenv('REQUEST_URI');
			putenv('QUERY_STRING');
		}

		$lines = preg_grep('/^'.preg_quote(self::PAGE, '/').'/', $output);
		self::assertCount(1, $lines, "the child for $request reported no page:\n".implode("\n", $output));

		$page = json_decode((string) substr(reset($lines), strlen(self::PAGE)), true);
		self::assertCount(1, $page['forms'], "the page at $request holds no single sign-in form:\n".implode("\n", $output));
		$page['form'] = $page['forms'][0];

		return $page;
	}

	/**
	 * @param string $requestUri what the server sets REQUEST_URI to
	 * @return string the request as a failure message prints it, with a tab or a line break spelt \t, \n or \r
	 */
	private static function shown($requestUri)
	{
		return addcslashes($requestUri, "\0..\37");
	}

	/**
	 * @param string $tag a form's start tag
	 * @return string its action, as a browser decodes the attribute
	 */
	private static function actionOf($tag)
	{
		self::assertSame(1, preg_match('/\saction="([^"]*)"/', $tag, $match), "no action in $tag");

		return self::decoded($match[1]);
	}

	/**
	 * @param string $value an attribute value as the page holds it
	 * @return string the value a browser reads from it, character references decoded
	 */
	private static function decoded($value)
	{
		return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	/**
	 * @param string $url a URL on a page of the site at http://localhost/
	 * @return string the host a browser resolves it to, which trims controls and spaces, drops tabs and newlines and reads a backslash as a slash
	 */
	private static function hostOf($url)
	{
		$url = str_replace(array("\t", "\n", "\r", '\\'), array('', '', '', '/'), trim($url, "\x00..\x20"));
		$host = parse_url((strpos($url, '//') === 0) ? 'http:'.$url : $url, PHP_URL_HOST);

		return is_string($host) ? $host : 'localhost';
	}
}
