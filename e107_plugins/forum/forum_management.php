<?php
/*
* e107 website system
*
* Copyright ( c ) 2001-2008 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom forum install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_management.php,v $
* $Revision: 1.3 $
* $Date: 2009-08-08 07:11:50 $
* $Author: marj_nl_fr $
*
*/

class forum_management
{
	function forum_install_pre(&$var)
	{
		print_a($var);
		echo "custom install 'pre' function<br /><br />";
	}

	function forum_install_post(&$var)
	{
		global $sql;
		echo "Setting all user_forums to 0 <br />";
		$sql -> db_Update("user", "user_forums='0'");
	}

	function forum_uninstall(&$var)
	{
		global $sql;
		$sql -> db_Update("user", "user_forums='0'");
	}

	function forum_upgrade(&$var)
	{
		global $sql;
		if(version_compare($var['current_plug']['plugin_version'], "1.2", "<"))
		{
			$qry = "ALTER TABLE #forum ADD forum_postclass TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;";
			$sql->db_Select_gen($qry);
		}
	}
}
