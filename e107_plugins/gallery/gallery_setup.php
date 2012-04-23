<?php
/*
* e107 website system
*
* Copyright (C) 2008-2012 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom download install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/download/download_setup.php,v $
* $Revision: 12639 $
* $Date: 2012-04-20 00:28:53 -0700 (Fri, 20 Apr 2012) $
* $Author: e107coders $
*
*/

class gallery_setup
{
	
	function install_pre($var)
	{
		// print_a($var);
		$mes = eMessage::getInstance();
		// $mes->add("custom install 'pre' function.", E_MESSAGE_SUCCESS);
	}

	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		// $mes->add("custom install 'post' function.", E_MESSAGE_SUCCESS);
	}

	function uninstall_pre($var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		// $mes->add("custom uninstall 'pre' function.", E_MESSAGE_SUCCESS);
	}


	// IMPORTANT : This function below is for modifying the CONTENT of the tables only, NOT the table-structure. 
	// To Modify the table-structure, simply modify your {plugin}_sql.php file and an update will be detected automatically. 
	/*
	 * @var $needed - true when only a check for a required update is being performed.
	 * Return: Reason the upgrade is required, otherwise set it to return FALSE. 
	 */
	function upgrade_post($needed)
	{
	
	}
}
