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

class e_urlTest extends \Codeception\Test\Unit
{
	use \Test\CorePrefs;

	/**
	 * Admin > Database > Preferences editor can delete url_config and url_aliases,
	 * and index.php runs e_url before anything else, so a missing preference used
	 * to take the front end down with array_keys() on null.
	 *
	 * Worth knowing when reaching for an acceptance test instead: e_url::run()
	 * guards the isLegacy() call with three short-circuiting conditions, so '/'
	 * and '/index.php' never reach it. Only a request with a path, which is any
	 * SEF address, gets there.
	 */
	public function testIsLegacyTreatsMissingLegacyPreferencesAsNoLegacyMappings()
	{
		$restore = $this->withCorePrefs(array('url_config' => null, 'url_aliases' => null));

		try
		{
			$isLegacy = new ReflectionMethod('e_url', 'isLegacy');
			self::assertFalse($isLegacy->invoke(new e_url()));
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
			$url = new e_url();
			$request = new ReflectionProperty('e_url', '_request');
			$request->setValue($url, 'news/list');

			$isLegacy = new ReflectionMethod('e_url', 'isLegacy');
			self::assertTrue($isLegacy->invoke($url));
		}
		finally
		{
			$restore();
		}
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

		foreach(e107::getPlug()->getDetected() as $plugin)
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
		$restore = $this->withCorePrefs(array('url_config' => null, 'url_aliases' => null, 'e_url_list' => array('routeprobe' => 1)));
		$get = $_GET;

		try
		{
			self::assertSame(array('a' => '1'), $this->routedGet('probe/a=1', array()), 'no token');
			self::assertSame(array('e-token' => 'carried', 'a' => '1'), $this->routedGet('probe/a=1&e-token=carried', array('e-token' => 'carried')), 'the carried token');
			self::assertSame(array(), $this->routedGet('probe/a=1&e-token=routed', array()), 'a token only the route has');
			self::assertSame(array('e-token' => 'carried'), $this->routedGet('probe/a=1&e-token=routed', array('e-token' => 'carried')), 'a token other than the carried one');
			self::assertSame(array(), $this->routedGet('probe/a=1&amp;e-token=routed', array()), 'a token the page reads behind &amp;');
			self::assertSame(array('e-token' => 'carried'), $this->routedGet('probe/a=1&e-token=routed&amp;e-token=carried', array('e-token' => 'carried')), 'a token only parse_str() reads, ahead of the carried one');
		}
		finally
		{
			$_GET = $get;
			$restore();
		}
	}

	/**
	 * @return array $_GET after e_url::run() has routed $request, through a route whose page is not on disk, from $carried
	 */
	private function routedGet($request, array $carried)
	{
		$_GET = $carried;
		$url = new e_url();
		(new ReflectionProperty('e_url', '_request'))->setValue($url, $request);
		(new ReflectionProperty('e_url', '_config'))->setValue($url, array('routeprobe' => array('probe' => array(
			'regex'    => '^probe/(.*)',
			'redirect' => '{e_PLUGIN}routeprobe/absent.php?$1',
		))));
		$url->run();

		return $_GET;
	}
}
