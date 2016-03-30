<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Custom install/uninstall/update routines.
 */


/**
 * Class gallery_setup.
 */
class gallery_setup
{

	/**
	 * This function is called before plugin table has been created by the
	 * [PLUGIN]_sql.php file.
	 *
	 * @param array $var
	 */
	function install_pre($var)
	{

	}

	/**
	 * This function is called after plugin table has been created by the
	 * [PLUGIN]_sql.php file.
	 *
	 * @param array $var
	 */
	function install_post($var)
	{

	}

	function uninstall_pre($var)
	{

	}

	function uninstall_options()
	{
	}

	function uninstall_post($var)
	{
	}

	/**
	 * Trigger an upgrade alert or not.
	 *
	 * @param array $var
	 *
	 * @return bool
	 *  True to trigger an upgrade alert, and false to not.
	 */
	function upgrade_required($var)
	{
		return false;
	}

	function upgrade_pre($var)
	{
	}

	function upgrade_post($var)
	{

	}

}
