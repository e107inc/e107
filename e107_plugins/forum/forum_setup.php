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
	
	/*
	 * Call During Upgrade Check. May be used to check for existance of tables etc and if not found return TRUE to call for an upgrade. 
	 * 
	 */
	function upgrade_required()
	{
		if(!e107::getDb()->field('forum_thread','thread_id'))
		{
			return true;	 // true to trigger an upgrade alert, and false to not. 	
		}
		
	}
	

	function upgrade_pre($var)
	{
		//Redirect upgrade to customized upgrade routine
		
		e107::getRedirect()->redirect(e_PLUGIN_ABS.'forum/forum_update.php');
		
		//header('Location: '.e_PLUGIN.'forum/forum_update.php');
	}

	// After Automatic Upgrade Routine has completed.. run this. ;-)
	function upgrade_post($var)
	{	
		$mes = e107::getMessage();
		$mes->addSuccess("Migration is required. Please click 'Continue'.<br /><a class='btn btn-primary' href='".e_PLUGIN."forum/forum_update.php'>Continue</a>");		
	}
}
