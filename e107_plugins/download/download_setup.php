<?php
/*
* e107 website system
*
* Copyright ( c ) 2001-2008 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom download install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/download/download_setup.php,v $
* $Revision: 1.1 $
* $Date: 2009-10-20 03:58:47 $
* $Author: e107coders $
*
*/

class download_setup
{
	function download_install_pre(&$var)
	{
		// print_a($var);
		$mes = eMessage::getInstance();
		// $mes->add("custom install 'pre' function.", E_MESSAGE_SUCCESS);
	}

	function download_install_post(&$var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		// $mes->add("custom install 'post' function.", E_MESSAGE_SUCCESS);
	}

	function download_uninstall_pre(&$var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		// $mes->add("custom uninstall 'pre' function.", E_MESSAGE_SUCCESS);
	}

	function download_upgrade_post(&$var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		// $mes->add("custom upgrade 'post'  function.", E_MESSAGE_SUCCESS);
		
		//if(version_compare($var['current_plug']['plugin_version'], "1.2", "<"))
		//{
		//	$qry = "ALTER TABLE #download ADD download_postclass TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;";
		//	$sql->db_Select_gen($qry);
		//}
	}
}
