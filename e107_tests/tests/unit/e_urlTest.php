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
		$restore = $this->withoutCorePrefs(array('url_config', 'url_aliases'));

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
		$config = e107::getConfig();
		$saved = $config->get('url_config');
		$config->set('url_config', array('news' => 'core/sef'));

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
			$config->set('url_config', $saved);
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
	 * @param string[] $keys
	 * @return callable puts every removed preference back as it was found
	 */
	private function withoutCorePrefs(array $keys)
	{
		$config = e107::getConfig();
		$saved = array();

		foreach($keys as $key)
		{
			$saved[$key] = $config->get($key);
			$config->remove($key);
		}

		return static function () use ($config, $saved)
		{
			foreach($saved as $key => $value)
			{
				$config->set($key, $value);
			}
		};
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
}
