<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

use e107\Reflection\ReflectionMethod;
use e107\Reflection\ReflectionProperty;

class e_urlTest extends \Test\Unit
{

	/**
	 * Admin > Database > Preferences editor can delete url_config and url_aliases,
	 * and index.php runs e_url before anything else, so a missing preference
	 * used to take every front-end request down with a TypeError.
	 */
	public function testIsLegacyTreatsMissingLegacyPreferencesAsNoLegacyMappings()
	{
		$restore = $this->withCorePrefs(array('url_config' => null, 'url_aliases' => null));

		try
		{
			$isLegacy = new ReflectionMethod('e_url', 'isLegacy');
			self::assertFalse($isLegacy->invoke(new e_url(), 'news/list'));
		}
		finally
		{
			$restore();
		}
	}

	public function testIsLegacyStillRecognisesALegacyRequest()
	{
		$restore = $this->withCorePrefs(array('url_config' => array('news' => 'core/sef')));

		try
		{
			$isLegacy = new ReflectionMethod('e_url', 'isLegacy');
			self::assertTrue($isLegacy->invoke(new e_url(), 'news/list'));
		}
		finally
		{
			$restore();
		}
	}

	/**
	 * @dataProvider requestsUnderAWebPath
	 * @param string $webPath what e_HTTP holds
	 * @param string $requestUri what the server sets REQUEST_URI to; its part after the first "?" is the QUERY_STRING
	 * @param string $expected the path the routes are matched against
	 */
	public function testARouteIsMatchedAgainstTheRequestLessOnlyTheLeadingWebPath($webPath, $requestUri, $expected)
	{
		$marker = 'E_URL_REQUEST=';
		$parts = explode('?', $requestUri, 2);
		putenv('REQUEST_URI='.$requestUri);
		putenv('QUERY_STRING='.(isset($parts[1]) ? $parts[1] : ''));

		try
		{
			list($output) = $this->runInBootedCliDefining(array('e_HTTP' => $webPath),
				"echo \"\\n".$marker."\", json_encode((new e107\\Reflection\\ReflectionProperty('e_url', '_request'))->getValue(new e_url())), \"\\n\";",
				self::BUFFERED);
		}
		finally
		{
			putenv('REQUEST_URI');
			putenv('QUERY_STRING');
		}

		self::assertSame($expected, self::reportedAfter($output, $marker, "the child for $requestUri under $webPath"));
	}

	/**
	 * @return array[] what {@see e_urlTest::testARouteIsMatchedAgainstTheRequestLessOnlyTheLeadingWebPath()} takes
	 */
	public function requestsUnderAWebPath()
	{
		return array(
			'the root, a route'                 => array('/', '/feed/news/rss/', 'feed/news/rss/'),
			'the root, the front page'          => array('/', '/', ''),
			'a folder, a route'                 => array('/sub/', '/sub/feed/news/rss/', 'feed/news/rss/'),
			'a folder, the front page'          => array('/sub/', '/sub/', ''),
			'a folder named again in the path'  => array('/sub/', '/sub/feed/sub/rss/', 'feed/sub/rss/'),
			'a folder named again in the query' => array('/sub/', '/sub/feed/news/rss/?from=/sub/', 'feed/news/rss/?from=/sub/'),
		);
	}

	/**
	 * A plugin's sitelink is routed by its e_url config rather than by the URL
	 * the installer stored, so a redirect naming a file that is not on disk
	 * ends at the site 404 and says so to nobody but a main admin.
	 */
	public function testEveryBundledUrlConfigRedirectsToAFileThatIsOnDisk()
	{
		$read = array();
		$missing = array();

		foreach(e107::getPlug()->getCorePluginList() as $plugin)
		{
			$addon = e_PLUGIN.$plugin.'/e_url.php';
			$class = $plugin.'_url';

			if(!is_readable($addon))
			{
				continue;
			}

			include_once($addon);

			if(!class_exists($class))
			{
				$missing[] = $plugin.'/e_url.php declares no '.$class;
				continue;
			}

			$config = call_user_func(array(new $class, 'config'));

			foreach((array) $config as $key => $route)
			{
				if(empty($route['regex']))
				{
					continue;
				}

				$target = empty($route['redirect']) ? null : $this->redirectTarget($route['redirect']);

				if($target === null)
				{
					continue;
				}

				$read[] = $plugin.' ['.$key.']';

				if(!is_file($target))
				{
					$missing[] = $plugin.' ['.$key.'] '.$route['redirect'].' => '.$target;
				}
			}
		}

		sort($missing);

		self::assertContains('_blank [index]', $read, 'The route this test was written for was not read, '
			. 'so a green result here would mean nothing.');
		self::assertSame(array(), $missing, 'These bundled e_url entries do not resolve to a file on disk, '
			. 'so e_url::run() drops through to the site 404 for the routes they own.');
	}

	/**
	 * A controllers/ folder registers its plugin as a URL module on the folder
	 * alone, so {@see e_url::run()} hands the request to the old router rather
	 * than matching the plugin's own e_url.php rules, and a module with no index
	 * controller to dispatch redirects the visitor to admin.php. The other half
	 * of that registration, a url/url.php, is not walked here because reading
	 * its path defines e_CURRENT_PLUGIN for the rest of the process.
	 */
	public function testEveryBundledControllersFolderCanDispatchAnIndexController()
	{
		$dispatcher = new eDispatcher();
		$read = array();
		$undispatchable = array();

		foreach(e107::getPlug()->getCorePluginList() as $plugin)
		{
			if(!is_dir(eDispatcher::getDispatchLocationPath('plugin', $plugin)))
			{
				continue;
			}

			$read[] = $plugin;

			if(!$dispatcher->isDispatchableModule($plugin, 'index', 'plugin'))
			{
				$undispatchable[] = $plugin;
			}
		}

		self::assertContains('gallery', $read, 'No bundled controllers/ folder was read, '
			. 'so a green result here would mean nothing.');
		self::assertSame(array(), $undispatchable, 'These bundled plugins register as URL modules '
			. 'on their controllers/ folder, which shadows their e_url.php rules, and then hold no '
			. 'index controller to dispatch, so the front end redirects to admin.php.');
	}

	/**
	 * @param string $redirect an e_url config 'redirect' value
	 * @return string|null what {@see e_url::run()} passes to file_exists() for an
	 *                     empty capture, or null when a backreference picks the file
	 */
	private function redirectTarget($redirect)
	{
		$parts = explode('?', e107::getParser()->replaceConstants($redirect), 2);
		$path = preg_replace('/\$\d+$/', '', $parts[0]);

		return strpos($path, '$') === false ? $path : null;
	}

	/**
	 * A route rewrites the address after class2.php has judged its e-token, so it may pass a token on only if the request carried it.
	 */
	public function testARouteHandsOverOnlyTheTokenTheRequestCarried()
	{
		self::assertSame(array('a' => '1'), $this->routedGet('probe/a=1', array()), 'no token');
		self::assertSame(array('e-token' => 'carried', 'a' => '1'), $this->routedGet('probe/a=1&e-token=carried', array('e-token' => 'carried')), 'the carried token');
		self::assertSame(array(), $this->routedGet('probe/a=1&e-token=routed', array()), 'a token only the route has');
		self::assertSame(array('e-token' => 'carried'), $this->routedGet('probe/a=1&e-token=routed', array('e-token' => 'carried')), 'a token other than the carried one');
		self::assertSame(array(), $this->routedGet('probe/a=1&amp;e-token=routed', array()), 'a token the page reads behind &amp;');
		self::assertSame(array('e-token' => 'carried'), $this->routedGet('probe/a=1&e-token=routed&amp;e-token=carried', array('e-token' => 'carried')), 'a token only parse_str() reads, ahead of the carried one');
	}

	/**
	 * Contact, RSS and the forum index route to a page with no query string, which must reach the page without a warning.
	 */
	public function testARouteWhoseTargetHasNoQueryStringRaisesNoWarning()
	{
		self::assertSame(array(), $this->routedGet('probe/', array(), '{e_PLUGIN}routeprobe/absent.php'));
	}

	/**
	 * @return array $_GET after {@see e_url::run()} has routed $request to $redirect, a page that is not on disk, from $carried, failing the test on any error raised on the way
	 */
	private function routedGet($request, array $carried, $redirect = '{e_PLUGIN}routeprobe/absent.php?$1')
	{
		$routed = $this->route($request, $carried, array('probe' => array('regex' => '^probe/(.*)', 'redirect' => $redirect)));

		self::assertSame(array(), $routed['raised'], 'Routing '.$request.' to '.$redirect.' raised these.');

		return $routed['get'];
	}

	public function testAnAddressWithAQueryStringReachesTheRouteForItsPath()
	{
		$routed = $this->route('probe?foo=1', array('foo' => '1'), array(
			'path' => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
		));

		self::assertSame(array('routed' => true, 'include' => APP_PATH.'/page.php', 'query' => 'foo=1', 'get' => array('foo' => '1'), 'raised' => array()), $routed);
	}

	public function testARetriedRouteHandsThePageItsOwnQueryAndTheRequestsInEQuery()
	{
		$routed = $this->route('probe?news.2', array('news_2' => ''), array(
			'path' => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php?from=route'),
		));

		self::assertSame(array('routed' => true, 'include' => APP_PATH.'/page.php', 'query' => 'from=route&news.2', 'get' => array('news_2' => '', 'from' => 'route'), 'raised' => array()), $routed);
	}

	public function testARouteWhosePatternReadsTheQueryStringStillWins()
	{
		$routed = $this->route('probe?id=2', array('id' => '2'), array(
			'path'  => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
			'query' => array('regex' => '^probe\?id=(\d+)$', 'redirect' => APP_PATH.'/news.php?item=$1'),
		));

		self::assertSame(array('routed' => true, 'include' => APP_PATH.'/news.php', 'query' => 'item=2', 'get' => array('id' => '2', 'item' => '2'), 'raised' => array()), $routed);
	}

	public function testAMatchTheTokenGuardRefusesIsNotRetriedAgainstThePath()
	{
		$routed = $this->route('probe?id=2', array('id' => '2'), array(
			'path'  => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
			'query' => array('regex' => '^probe\?id=(\d+)$', 'redirect' => APP_PATH.'/news.php?item=$1&e-token=routed'),
		));

		self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => array('id' => '2'), 'raised' => array()), $routed);
	}

	public function testAMatchWhosePageIsMissingIsNotRetriedAgainstThePath()
	{
		$routed = $this->route('probe?id=2', array('id' => '2'), array(
			'path'  => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
			'query' => array('regex' => '^probe\?id=(\d+)$', 'redirect' => '{e_PLUGIN}routeprobe/absent.php?item=$1'),
		));

		self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => array('id' => '2', 'item' => '2'), 'raised' => array()), $routed);
	}

	public function testTheFrontPagesOwnQueryStringIsNotRetriedAgainstItsPath()
	{
		$routed = $this->route('', array(), array(
			'path' => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
		), array('frontpage' => array('all' => 'probe?cat=1')));

		self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => array(), 'raised' => array()), $routed);
	}

	public function testAnAddressWithNoPathOrTheIndexPathIsNotRetried()
	{
		$routes = array(
			'home'  => array('regex' => '^$', 'redirect' => APP_PATH.'/page.php', 'domain' => 'e107help.test'),
			'index' => array('regex' => '^index\.php$', 'redirect' => APP_PATH.'/page.php'),
		);

		foreach(array('?foo=1', 'index.php?foo=1') as $request)
		{
			self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => array('foo' => '1'), 'raised' => array()), $this->route($request, array('foo' => '1'), $routes), $request);
		}
	}

	public function testALegacyAddressIsLeftToTheLegacyRouterWithOrWithoutAQueryString()
	{
		foreach(array('probe' => array(), 'probe?foo=1' => array('foo' => '1')) as $request => $get)
		{
			$routed = $this->route($request, $get, array(
				'path' => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
			), array('url_config' => array('probe' => 'core/sef')));

			self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => $get, 'raised' => array()), $routed, $request);
		}
	}

	public function testAnAddressNoRouteMatchesIsLeftToTheLegacyRouter()
	{
		$routed = $this->route('elsewhere?foo=1', array('foo' => '1'), array(
			'path' => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
		));

		self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => array('foo' => '1'), 'raised' => array()), $routed);
	}

	public function testTheRetryStillRefusesATokenTheRequestDidNotCarry()
	{
		$routed = $this->route('probe?foo=1', array('foo' => '1'), array(
			'path' => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php?e-token=routed'),
		));

		self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => array('foo' => '1'), 'raised' => array()), $routed);
	}

	public function testTheRetryRefusesATokenOnlyTheAddressCarries()
	{
		$routes = array(
			'path' => array('regex' => '^probe\/?$', 'redirect' => APP_PATH.'/page.php'),
		);

		foreach(array('probe?e-token=forged' => array(), 'probe?a=1&amp;e-token=forged' => array('a' => '1', 'amp;e-token' => 'forged')) as $request => $get)
		{
			self::assertSame(array('routed' => false, 'include' => null, 'query' => null, 'get' => $get, 'raised' => array()), $this->route($request, $get, $routes), $request);
		}
	}

	/**
	 * Runs {@see e_url::run()} in a booted single-entry child, which may define the constants a route that sets its page defines.
	 *
	 * @param string $request the address less the site's web path, as the router reads it
	 * @param array<string, string> $get the request's own query string, as $_GET holds it
	 * @param array<string, array<string, string>> $routes route key => regex, redirect and any domain, all of one enabled plugin, on a host named e107help.test
	 * @param array<string, mixed> $prefs core preferences over the defaults here, which enable that plugin and map nothing to the legacy router
	 * @return array<string, mixed> what run() returned, the page it set, e_QUERY, $_GET afterwards and every error raised on the way
	 */
	private function route($request, array $get, array $routes, array $prefs = array())
	{
		$prefs += array('url_config' => array(), 'url_aliases' => array(), 'e_url_alias' => array(), 'e_url_list' => array('routeprobe' => 1));
		$marker = 'E_URL_ROUTED=';

		$php = "foreach(".var_export($prefs, true)." as \$pref => \$value) { e107::getConfig()->set(\$pref, \$value); } ";
		$php .= "\$url = new e_url(); ";
		$php .= "(new e107\\Reflection\\ReflectionProperty('e_url', '_request'))->setValue(\$url, ".var_export($request, true)."); ";
		$php .= "(new e107\\Reflection\\ReflectionProperty('e_url', '_config'))->setValue(\$url, array('routeprobe' => ".var_export($routes, true).")); ";
		$php .= "\$_SERVER['HTTP_HOST'] = 'e107help.test'; ";
		$php .= "\$_GET = ".var_export($get, true)."; ";
		$php .= "\$raised = array(); ";
		$php .= "set_error_handler(function (\$severity, \$message) use (&\$raised) { \$raised[] = \$message; return true; }); ";
		$php .= "\$routed = \$url->run(); ";
		$php .= "restore_error_handler(); ";
		$php .= "echo \"\\n".$marker."\", json_encode(array('routed' => \$routed, 'include' => \$url->getInclude(), 'query' => defined('e_QUERY') ? e_QUERY : null, 'get' => \$_GET, 'raised' => \$raised)), \"\\n\"; ";

		list($output) = $this->runInBootedCliDefining(array('e_SINGLE_ENTRY' => true), $php, self::BUFFERED);

		return self::reportedAfter($output, $marker, 'the child routing '.$request);
	}
}
