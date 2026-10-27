<?php

namespace Test;

/**
 * Core preferences borrowed for the length of one test; keep this in PHP 5.6 syntax.
 */
trait CorePrefs
{
	/**
	 * Sets core preferences for one test, a null value removing one; call what it returns in a finally block.
	 *
	 * @param array $prefs preference name => value
	 * @return callable puts every preference named, and the legacy $pref global, back as they were found
	 */
	protected function withCorePrefs(array $prefs)
	{
		$config = \e107::getConfig();
		$hadGlobalPref = array_key_exists('pref', $GLOBALS);
		$savedGlobalPref = $hadGlobalPref ? $GLOBALS['pref'] : null;
		$saved = array();

		foreach($prefs as $key => $value)
		{
			$saved[$key] = $config->get($key);
			self::putCorePref($config, $key, $value);
		}

		return static function () use ($config, $saved, $hadGlobalPref, $savedGlobalPref)
		{
			foreach($saved as $key => $value)
			{
				self::putCorePref($config, $key, $value);
			}

			if($hadGlobalPref)
			{
				$GLOBALS['pref'] = $savedGlobalPref;
			}
			else
			{
				unset($GLOBALS['pref']);
			}
		};
	}

	/**
	 * Routes URLs through the given url_config locations for one test; call what it returns in a finally block.
	 *
	 * @param array $locations module => config location, e.g. 'user' => 'core/rewrite'
	 * @return callable puts the preference and the router back, and drops the router cache file built from the locations
	 */
	protected function withUrlConfig(array $locations)
	{
		$front = \e107::getUrl()->front();
		$saved = $front->getRouter();
		$restorePrefs = $this->withCorePrefs(array('url_config' => array_merge((array) \e107::getPref('url_config'), $locations)));

		$router = new \eRouter();
		$router->loadConfig(true);
		$front->setRouter($router);

		return static function () use ($restorePrefs, $front, $saved)
		{
			$front->setRouter($saved);
			$restorePrefs();
			\eRouter::clearCache();
		};
	}

	/**
	 * Keeps the stored core preferences, which an admin page a booted child opens may save with whatever it set; call what it returns in _after().
	 *
	 * @return callable puts the stored row back as it was found and clears the preference cache
	 */
	protected function keepStoredCorePrefs()
	{
		$stored = \e107::getDb()->retrieve('core', 'e107_value', "e107_name = 'SitePrefs'");

		return static function () use ($stored)
		{
			\e107::getDb()->update('core', array(
				'data'  => array('e107_value' => $stored),
				'WHERE' => "e107_name = 'SitePrefs'",
			));
			\e107::getConfig()->clearPrefCache();
		};
	}

	/**
	 * Sets one core preference, or removes it when $value is null.
	 *
	 * @param \e_pref $config
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	protected static function putCorePref($config, $key, $value)
	{
		if($value === null)
		{
			$config->remove($key);
		}
		else
		{
			$config->set($key, $value);
		}
	}
}
