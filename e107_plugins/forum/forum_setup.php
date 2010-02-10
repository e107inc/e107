<?php
/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom forum install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_setup.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

class forum_setup
{
	function install_pre($var)
	{
		// print_a($var);
		// echo "custom install 'pre' function<br /><br />";
	}

	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
		/*if($sql -> db_Update("user", "user_forums='0'")) // deprecated in 0.8
		{
			$mes->add("Setting all user_forums to 0.", E_MESSAGE_SUCCESS);
		}*/
	}

	function uninstall_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
/*		if($sql -> db_Update("user", "user_forums='0'")) // deprecated in 0.8
		{
			$mes->add("Setting all user_forums to 0.", E_MESSAGE_SUCCESS);	
		}*/
	}

	function upgrade_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
		if(version_compare($var['current_plug']['plugin_version'], "1.2", "<"))
		{
			$qry = "ALTER TABLE #forum ADD forum_postclass TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;";
			$sql->db_Select_gen($qry);
		}
	}
}
