<?php

/**
 * @file
 * Provides information about external libraries.
 */


/**
 * Class bootstrap3_library.
 */
class bootstrap3_library
{

	/**
	 * Provides information about external libraries.
	 */
	function config()
	{
		$libraries = array();

		return $libraries;
	}

	/**
	 * Alter the library information before detection and caching takes place.
	 */
	function config_alter(&$libraries)
	{
		$bootswatch = e107::pref('theme', 'bootswatch', false);

		if($bootswatch)
		{
			// Disable Bootstrap CSS.
			unset($libraries['cdn.bootstrap']['files']['css']);
			unset($libraries['cdn.bootstrap']['variants']['minified']['files']['css']);
			unset($libraries['bootstrap']['files']['css']);
			unset($libraries['bootstrap']['variants']['minified']['files']['css']);
		}
	}

}
