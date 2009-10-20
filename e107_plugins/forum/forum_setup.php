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
* $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_setup.php,v $
* $Revision: 1.1 $
* $Date: 2009-10-20 03:58:47 $
* $Author: e107coders $
*
*/

class forum_setup
{
	function forum_install_pre(&$var)
	{
		print_a($var);
		echo "custom install 'pre' function<br /><br />";
	}

	function forum_install_post(&$var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		
		if($sql -> db_Update("user", "user_forums='0'"))
		{
			$mes->add("Setting all user_forums to 0.", E_MESSAGE_SUCCESS);
		}
	}

	function forum_uninstall_post(&$var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		
		if($sql -> db_Update("user", "user_forums='0'"))
		{
			$mes->add("Setting all user_forums to 0.", E_MESSAGE_SUCCESS);	
		}
	}

	function forum_upgrade_post(&$var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		
		if(version_compare($var['current_plug']['plugin_version'], "1.2", "<"))
		{
			$qry = "ALTER TABLE #forum ADD forum_postclass TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;";
			$sql->db_Select_gen($qry);
		}
	}
}
