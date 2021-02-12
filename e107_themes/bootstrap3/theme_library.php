<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Provides information about external libraries.
 */


/**
 * Class theme_library.
 */
class theme_library
{

	/**
	 * Provides information about external libraries.
	 */
	function config()
	{
		// TODO - bootswatch...
		return array();
	}

    /**
     * Alters library information before detection and caching takes place.
     * @param $libraries
     */
	function config_alter(&$libraries)
	{
		$bootswatch = e107::pref('theme', 'bootswatch', false);

		if(!empty($bootswatch))
		{
			// Disable Bootstrap CSS.
			unset($libraries['cdn.bootstrap']['files']['css']);
			unset($libraries['cdn.bootstrap']['variants']['dev']['files']['css']);
			unset($libraries['bootstrap']['files']['css']);
			unset($libraries['bootstrap']['variants']['dev']['files']['css']);
		}
	}

}
