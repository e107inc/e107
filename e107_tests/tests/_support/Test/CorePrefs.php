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
