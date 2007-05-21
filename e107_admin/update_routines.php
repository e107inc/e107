<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/update_routines.php,v $
|     $Revision: 1.7 $
|     $Date: 2007-05-21 21:42:48 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");


// Modified update routine - combines checking and update code into one block per function
//		- reduces code size typically 30%.
//		- keeping check and update code together should improve clarity/reduce mis-types etc


// To do - how do we handle multi-language tables?


// This is really a 0.7.0..0.7.6 or so update
if (!$pref['displayname_maxlength'])
{
  $pref['displayname_maxlength'] = 15;
  save_prefs();
}

if (!defined("LAN_UPDATE_8")) { define("LAN_UPDATE_8", ""); }
if (!defined("LAN_UPDATE_9")) { define("LAN_UPDATE_9", ""); }


// Determine which installed plugins have an update file - save the path and the installed version in an array
$dbupdatep = array();		// Array of paths to installed plugins which have a checking routine
if ($sql->db_Select("plugin", "plugin_version, plugin_path", "plugin_installflag='1' ")) 
{
  while ($row = $sql->db_Fetch())
  {  // Mark plugins for update which have a specific update file, or a plugin.php file to check
	if(is_readable(e_PLUGIN.$row['plugin_path'].'/'.$row['plugin_path'].'_update_check.php') || is_readable(e_PLUGIN.$row['plugin_path'].'/plugin.php')) 
	{
	  $dbupdatep[$row['plugin_path']] = $row['plugin_version'];
	}
  }
}


// Read in each update file
foreach ($dbupdatep as $path => $ver)
{
  $fname = e_PLUGIN.$path.'/'.$path.'_update_check.php';
  if (is_readable($fname)) include_once($fname);
}


/*
if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'forum' AND plugin_installflag='1' ")) {
	if(file_exists(e_PLUGIN.'forum/forum_update_check.php'))
	{
		include_once(e_PLUGIN.'forum/forum_update_check.php');
	}
}
if (mysql_table_exists("stat_info") && $sql -> db_Select("plugin", "*", "plugin_path = 'log' AND plugin_installflag='1'")) {
	if(file_exists(e_PLUGIN.'log/log_update_check.php'))
	{
		include_once(e_PLUGIN.'log/log_update_check.php');
	}
}

//content
if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'content' AND plugin_installflag='1' "))
{
	if(file_exists(e_PLUGIN.'content/content_update_check.php'))
	{
		include_once(e_PLUGIN.'content/content_update_check.php');
	}
}

if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'pm' AND plugin_installflag='1' "))
{
	if(file_exists(e_PLUGIN.'pm/pm_update_check.php'))
	{
		include_once(e_PLUGIN.'pm/pm_update_check.php');
	}
}

*/


$dbupdate["706_to_800"] = LAN_UPDATE_8." .706 ".LAN_UPDATE_9." .8";
$dbupdate["70x_to_706"] = LAN_UPDATE_8." .70x ".LAN_UPDATE_9." .706";



function update_check() 
{
  global $ns, $dbupdate, $dbupdatep;
  
  $update_needed = FALSE;
  foreach($dbupdate as $func => $rmks) 
  {
	if (function_exists("update_".$func)) 
	{
	  if (!call_user_func("update_".$func, FALSE)) 
	  {
		$update_needed = TRUE;
		continue;
	  }
	}
  }

  foreach($dbupdatep as $func => $rmks) 
  {
	if (function_exists("update_".$func)) 
	{
	  if (!call_user_func("update_".$func, FALSE)) 
	  {
		$update_needed = TRUE;
		continue;
	  }
	}
  }

  if ($update_needed === TRUE) 
  {
	$txt = "<div style='text-align:center;'>".ADLAN_120;
	$txt .= "<br /><form method='post' action='".e_ADMIN."e107_update.php'>
		<input class='button' type='submit' value='".LAN_UPDATE."' />
		</form></div>";
	$ns->tablerender(LAN_UPDATE, $txt);
  }
}


//--------------------------------------------
//	Check current prefs against latest list
//--------------------------------------------
function check_core_prefs($type='')
{
  global $pref;
  $do_save = FALSE;
  $should = get_default_prefs();

  $just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing if an update is needed
  
  foreach ($should as $k => $v)
  {
    if (!array_key_exists($k,$pref))
	{
	  if ($just_check) return update_needed();
	  $pref[$k] = $v;
	  $do_save = TRUE;
	}
  }
  if ($do_save) save_prefs();
}


//--------------------------------------------
//	Upgrade later versions of 0.7.x to 0.8
//--------------------------------------------
function update_706_to_800($type='') 
{
	global $sql,$ns, $pref;
	
	// List of unwanted $pref values which can go
	$obs_prefs = array();
	$do_save = FALSE;

	$just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing if an update is needed
	
	//change menu_path for usertheme_menu
	if($sql->db_Select("menus", "menu_path", "menu_path='usertheme_menu' || menu_path='usertheme_menu/'"))
	{
	  if ($just_check) return update_needed();
	  $sql->db_Update("menus", "menu_path='user_menu/' WHERE menu_path='usertheme_menu' || menu_path='usertheme_menu/' ");
	  catch_error();
	}

	//change menu_path for userlanguage_menu
	if($sql->db_Select("menus", "menu_path", "menu_path='userlanguage_menu' || menu_path='userlanguage_menu/'"))
	{
	  if ($just_check) return update_needed();
		$sql->db_Update("menus", "menu_path='user_menu/' WHERE menu_path='userlanguage_menu' || menu_path='userlanguage_menu/' ");
		catch_error();
	}

	//change menu_path for compliance_menu
	if($sql->db_Select("menus", "menu_path", "menu_path='compliance_menu' || menu_path='compliance_menu/'"))
	{
	  if ($just_check) return update_needed();
		$sql->db_Update("menus", "menu_path='siteinfo_menu/' WHERE menu_path='compliance_menu' || menu_path='compliance_menu/' ");
		catch_error();
	}

	//change menu_path for powered_by_menu
	if($sql->db_Select("menus", "menu_path", "menu_path='powered_by_menu' || menu_path='powered_by_menu/'"))
	{
	  if ($just_check) return update_needed();
		$sql->db_Update("menus", "menu_path='siteinfo_menu/' WHERE menu_path='powered_by_menu' || menu_path='powered_by_menu/' ");
		catch_error();
	}

		//change menu_path for sitebutton_menu
	if($sql->db_Select("menus", "menu_path", "menu_path='sitebutton_menu' || menu_path='sitebutton_menu/'"))
	{
	  if ($just_check) return update_needed();
		$sql->db_Update("menus", "menu_path='siteinfo_menu/' WHERE menu_path='sitebutton_menu' || menu_path='sitebutton_menu/' ");
		catch_error();
	}

	//change menu_path for counter_menu
	if($sql->db_Select("menus", "menu_path", "menu_path='counter_menu' || menu_path='counter_menu/'"))
	{
	  if ($just_check) return update_needed();
		$sql->db_Update("menus", "menu_path='siteinfo_menu/' WHERE menu_path='counter_menu' || menu_path='counter_menu/' ");
		catch_error();
	}

	//change menu_path for lastseen_menu
	if($sql->db_Select("menus", "menu_path", "menu_path='lastseen_menu' || menu_path='lastseen_menu/'"))
	{
	  if ($just_check) return update_needed();
		$sql->db_Update("menus", "menu_path='online/' WHERE menu_path='lastseen_menu' || menu_path='lastseen_menu/' ");
		catch_error();
	}

	//delete record for online_extended_menu (now only using one online menu)
	if($sql->db_Select("menus", "*", "menu_path='online_extended_menu' || menu_path='online_extended_menu/'"))
	{
	  if ($just_check) return update_needed();

	  $row=$sql->db_Fetch();
	
	  //if online_extended is activated, we need to activate the new 'online' menu, and delete this record
	  if($row['menu_location']!=0)
	  {
		$sql->db_Update("menus", "menu_name='online_menu', menu_path='online/' WHERE menu_path='online_extended_menu' || menu_path='online_extended_menu/' ");
	  }
	  else
	  {	//else if the menu is not active
		//we need to delete the online_extended menu row, and change the online_menu to online
		$sql->db_Delete("menus", " menu_path='online_extended_menu' || menu_path='online_extended_menu/' ");
	  }
	  catch_error();
	}

	//change menu_path for online_menu (if it still exists)
	if($sql->db_Select("menus", "menu_path", "menu_path='online_menu' || menu_path='online_menu/'"))
	{
	  if ($just_check) return update_needed();
		$sql->db_Update("menus", "menu_path='online/' WHERE menu_path='online_menu' || menu_path='online_menu/' ");
		catch_error();
	}


	// Obsolete prefs (list at top)
	foreach ($obs_prefs as $p)
	{
	  if (isset($pref[$p]))
	  {
	    if ($just_check) return update_needed();
		unset($pref[$p]);
		$do_save = TRUE;
	  }
	}


	// Obsolete tables
	if (mysql_table_exists("headlines")) 
	{
	    if ($just_check) return update_needed();
		mysql_query('DROP TABLE `'.MPREFIX.'headlines`');
	}

	if (mysql_table_exists("flood")) 
	{
	    if ($just_check) return update_needed();
		mysql_query('DROP TABLE `'.MPREFIX.'flood`');
	}



	if ($do_save) save_prefs();
	
	return $just_check;
}



function update_70x_to_706($type='') 
{
	global $sql,$ns;

	$just_check = $type == 'do' ? FALSE : TRUE;
	if(!$sql->db_Field("plugin",5))  // not plugin_rss so just add the new one.
	{
	  if ($just_check) return update_needed();
      mysql_query("ALTER TABLE `".MPREFIX."plugin` ADD `plugin_addons` TEXT NOT NULL ;");
	  catch_error();
	}
	
	//rename plugin_rss field
	if($sql->db_Field("plugin",5) == "plugin_rss")
	{
	  if ($just_check) return update_needed();
	  mysql_query("ALTER TABLE `".MPREFIX."plugin` CHANGE `plugin_rss` `plugin_addons` TEXT NOT NULL;");
	  catch_error();
	}


	if($sql->db_Field("dblog",5) == "dblog_query")
	{
      if ($just_check) return update_needed();
	  mysql_query("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_query` `dblog_title` VARCHAR( 255 ) NOT NULL DEFAULT '';");
	  catch_error();
	  mysql_query("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_remarks` `dblog_remarks` TEXT NOT NULL;");
	  catch_error();
	}

	if(!$sql->db_Field("plugin","plugin_path","UNIQUE"))
	{
      if ($just_check) return update_needed();
      if(!mysql_query("ALTER TABLE `".MPREFIX."plugin` ADD UNIQUE (`plugin_path`);"))
	  {
		$mes = "<div style='text-align:center'>".LAN_UPDATE_12." : <a href='".e_ADMIN."db.php?plugin'>".ADLAN_145."</a>.</div>";
        $ns -> tablerender(LAN_ERROR,$mes);
       	catch_error();
	  }
	}

	if(!$sql->db_Field("online",6)) // online_active field
	{
	  if ($just_check) return update_needed();
	  mysql_query("ALTER TABLE ".MPREFIX."online ADD online_active INT(10) UNSIGNED NOT NULL DEFAULT '0'");
	  catch_error();
	}
		
	if ($sql -> db_Query("SHOW INDEX FROM ".MPREFIX."tmp")) 
	{
	  $row = $sql -> db_Fetch();
	  if (!in_array('tmp_ip', $row)) 
	  {
		if ($just_check) return update_needed();
		mysql_query("ALTER TABLE `".MPREFIX."tmp` ADD INDEX `tmp_ip` (`tmp_ip`);");
		mysql_query("ALTER TABLE `".MPREFIX."upload` ADD INDEX `upload_active` (`upload_active`);");
		mysql_query("ALTER TABLE `".MPREFIX."generic` ADD INDEX `gen_type` (`gen_type`);");
	  }
	}

	if (!$just_check)
	{
		// update new fields
        require_once(e_HANDLER."plugin_class.php");
		$ep = new e107plugin;
		$ep->update_plugins_table();
		$ep->save_addon_prefs();
	}

	if (!isset($pref['displayname_maxlength']))
	{
	  if ($just_check) return update_needed();
	  $pref['displayname_maxlength'] = 15;
	  save_prefs();
	}
	
	// If we get to here, in checking mode no updates are required. In update mode, all done.
	return $just_check;		// TRUE if no updates needed, FALSE if updates needed and completed

}


function update_needed()
{
	global $ns;
	if(E107_DEBUG_LEVEL)
	{
		$tmp = debug_backtrace();
		$ns->tablerender("", "<div style='text-align:center'>Update required in ".basename(__FILE__)." on line ".$tmp[0]['line']."</div>");
	}
	return FALSE;
}

function mysql_table_exists($table){
     $exists = mysql_query("SELECT 1 FROM ".MPREFIX."$table LIMIT 0");
     if ($exists) return TRUE;
     return FALSE;
}


function catch_error(){
	if (mysql_error()!='' && E107_DEBUG_LEVEL != 0) {
		$tmp2 = debug_backtrace();
		$tmp = mysql_error();
		echo $tmp." [ ".basename(__FILE__)." on line ".$tmp2[0]['line']."] <br />";
	}
	return;
}


function get_default_prefs()
{
  require_once(e_FILES."def_e107_prefs.php");
  return $pref;
}


?>
