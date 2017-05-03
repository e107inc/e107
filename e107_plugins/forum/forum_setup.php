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
		if(!e107::getDb()->isTable('forum_thread'))
		{
			return true;
		}


		if(!e107::getDb()->field('forum_thread','thread_id'))
		{
			return true;	 // true to trigger an upgrade alert, and false to not. 	
		}

		if(e107::getDb()->field('forum_thread', 'thread_sef'))
		{
			e107::getDb()->gen("ALTER TABLE `#forum_thread` DROP `thread_sef` ");
		}

		$legacyMenuPref = e107::getConfig('menu')->getPref();
		if(isset($legacyMenuPref['newforumposts_caption']))
		{

		}

		return false;
	}
	

	function upgrade_pre($var)
	{

		$sql = e107::getDb();

		if(!$sql->isTable('forum_t') || !$sql->isEmpty('forum_thread')) // no table, so run a default plugin install procedure.
		{
			return false;
		//	e107::getSingleton('e107plugin')->refresh('forum');
		}
		else
		{
			e107::getRedirect()->go(e_PLUGIN_ABS.'forum/forum_update.php'); //Redirect upgrade to customized upgrade routine
		}
		
		//header('Location: '.e_PLUGIN.'forum/forum_update.php');
	}

	// After Automatic Upgrade Routine has completed.. run this. ;-)
	function upgrade_post($var)
	{
		$sql = e107::getDb();

		$config = e107::getPref('url_config');

		if(!empty($config['forum']))
		{
			e107::getConfig()
			->removePref('url_config/forum')
			->removePref('url_locations/forum')
			->save(false,true);

			if(file_exists(e_PLUGIN."forum/url/url.php"))
			{
				@unlink(e_PLUGIN."forum/url/url.php");
				@unlink(e_PLUGIN."forum/url/rewrite_url.php");
			}

			$bld = new eRouter;
			$bld->buildGlobalConfig();

		}





		if($sql->isEmpty('forum_thread') === true && $sql->isTable('forum_t') && $sql->isEmpty('forum_t') === false)
		{
			$mes = e107::getMessage();
			$mes->addSuccess("Migration is required. Please click 'Continue'.<br /><a class='btn btn-primary' href='".e_PLUGIN."forum/forum_update.php'>Continue</a>");
		}

	}
}
