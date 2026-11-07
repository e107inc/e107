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
		return $this->keepStoredPrefs('SitePrefs', \e107::getConfig());
	}

	/**
	 * Keeps one stored preference row, which a booted child may save; call what it returns once the test is done.
	 *
	 * @param string $name the row's e107_name, e.g. 'plugin_pm'
	 * @param \e_pref $config the preference object that caches the row
	 * @return callable puts the row back as it was found, removes it when there was none, and clears the preference cache
	 */
	protected function keepStoredPrefs($name, $config)
	{
		$stored = self::storedPrefsValue($name);

		return static function () use ($name, $config, $stored)
		{
			$bind = array('name' => array('value' => $name, 'type' => \PDO::PARAM_STR));

			if($stored === null)
			{
				$query = array('PREPARE' => 'DELETE FROM `'.MPREFIX.'core` WHERE `e107_name` = :name', 'BIND' => $bind);
			}
			else
			{
				$bind['value'] = array('value' => $stored, 'type' => \PDO::PARAM_STR);
				$query = array('PREPARE' => 'UPDATE `'.MPREFIX.'core` SET `e107_value` = :value WHERE `e107_name` = :name', 'BIND' => $bind);
			}

			\e107::getDb()->db_Query($query, null, 'db_Update');
			$config->clearPrefCache();
		};
	}

	/**
	 * @param string $name the row's e107_name, e.g. 'plugin_pm'
	 * @return string|null the stored preferences as the database holds them, null when there is no row
	 */
	protected static function storedPrefsValue($name)
	{
		$sql = \e107::getDb();
		$row = $sql->select('core', 'e107_value', 'e107_name = :name', array('name' => $name)) ? $sql->fetch() : array();

		return empty($row) ? null : $row['e107_value'];
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
