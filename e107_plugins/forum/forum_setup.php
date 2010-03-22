<?php
/*
* e107 website system
*
* Copyright 2008-2010 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Forum plugin version tools
*
* $URL$
* $Id$
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

	function upgrade_pre($var)
	{
		//Redirect upgrade to customized upgrade routine
		header('Location: '.e_PLUGIN.'forum/forum_update.php');
		exit;
	}

	function upgrade_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
	}
}
