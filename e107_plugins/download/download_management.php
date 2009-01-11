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
* $Source: /cvs_backup/e107_0.8/e107_plugins/download/download_management.php,v $
* $Revision: 1.1 $
* $Date: 2009-01-11 02:59:10 $
* $Author: bugrain $
*
*/

class download_management
{
	function download_install_pre(&$var)
	{
		print_a($var);
		echo "custom install 'pre' function<br /><br />";
	}

	function download_install_post(&$var)
	{
		global $sql;
		echo "custom install 'post' function<br /><br />";
	}

	function download_uninstatll(&$var)
	{
		global $sql;
		echo "custom uninstall function<br /><br />";
	}

	function download_upgrade(&$var)
	{
		global $sql;
		echo "custom upgrade function<br /><br />";
		//if(version_compare($var['current_plug']['plugin_version'], "1.2", "<"))
		//{
		//	$qry = "ALTER TABLE #download ADD download_postclass TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;";
		//	$sql->db_Select_gen($qry);
		//}
	}
}
