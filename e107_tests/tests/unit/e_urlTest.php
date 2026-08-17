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
}
