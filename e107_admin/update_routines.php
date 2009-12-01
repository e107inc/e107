<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/update_routines.php,v $
|     $Revision: 1.65 $
|     $Date: 2009-12-01 20:05:52 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

// [debug=8] shows the operations on major table update

require_once('../class2.php');
require_once(e_HANDLER.'db_table_admin_class.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_e107_update.php');
// Modified update routine - combines checking and update code into one block per function
//		- reduces code size typically 30%.
//		- keeping check and update code together should improve clarity/reduce mis-types etc


// TODO: how do we handle update of multi-language tables?

// If following line uncommented, enables a test routine
// define('TEST_UPDATE',TRUE);
$update_debug = FALSE;			// TRUE gives extra messages in places
//$update_debug = TRUE;			// TRUE gives extra messages in places
if (defined('TEST_UPDATE')) $update_debug = TRUE;


if (!defined('LAN_UPDATE_8')) { define('LAN_UPDATE_8', ''); }
if (!defined('LAN_UPDATE_9')) { define('LAN_UPDATE_9', ''); }


// Determine which installed plugins have an update file - save the path and the installed version in an array
$dbupdateplugs = array();		// Array of paths to installed plugins which have a checking routine
$dbupdatep = array();			// Array of plugin upgrade actions (similar to $dbupdate)
$dbupdate = array();			// Array of core upgrade actions

global $e107cache;

if (is_readable(e_ADMIN.'ver.php'))
{
  include(e_ADMIN.'ver.php');
}


// If $dont_check_update is both defined and TRUE on entry, a check for update is done only once per 24 hours.
$dont_check_update = varset($dont_check_update, FALSE);


if ($dont_check_update === TRUE)
{
	$dont_check_update = FALSE;
	if ($tempData = $e107cache->retrieve_sys('nq_admin_updatecheck',3600, TRUE))
	{	// See when we last checked for an admin update
		list($last_time, $dont_check_update, $last_ver) = explode(',',$tempData);
		if ($last_ver != $e107info['e107_version'])
		{
			$dont_check_update = FALSE;		// Do proper check on version change
		}
	}
}

if (!$dont_check_update)
{
	if ($sql->db_Select('plugin', 'plugin_version, plugin_path', 'plugin_installflag=1'))
	{
		while ($row = $sql->db_Fetch())
		{  // Mark plugins for update which have a specific update file, or a plugin.php file to check
			if(is_readable(e_PLUGIN.$row['plugin_path'].'/'.$row['plugin_path'].'_update_check.php') || is_readable(e_PLUGIN.$row['plugin_path'].'/plugin.php'))
			{
				$dbupdateplugs[$row['plugin_path']] = $row['plugin_version'];
			}
		}
	}


	// Read in each update file - this will add an entry to the $dbupdatep array if a potential update exists
	foreach ($dbupdateplugs as $path => $ver)
	{
		$fname = e_PLUGIN.$path.'/'.$path.'_update_check.php';
		if (is_readable($fname)) include_once($fname);
	}


	// List of potential updates
	if (defined('TEST_UPDATE'))
	{
		$dbupdate['test_code'] = 'Test update routine';
	}
	$dbupdate['core_prefs'] = LAN_UPDATE_13;						// Prefs check
	$dbupdate['706_to_800'] = LAN_UPDATE_8.' .706 '.LAN_UPDATE_9.' .8';
	$dbupdate['70x_to_706'] = LAN_UPDATE_8.' .70x '.LAN_UPDATE_9.' .706';
}		// End if (!$dont_check_update)




/**
 *	Master routine to call to check for updates
 */
function update_check()
{
  global $ns, $dont_check_update, $e107info;


	$update_needed = FALSE;

	if ($dont_check_update === FALSE)
	{
		global $dbupdate, $dbupdatep, $e107cache;

		// See which core functions need update
		foreach($dbupdate as $func => $rmks)
		{
		  if (function_exists('update_'.$func))
			{
				if (!call_user_func('update_'.$func, FALSE))
				{
				  $update_needed = TRUE;
				  continue;
				}
			}
		}




		// Now check plugins
		foreach($dbupdatep as $func => $rmks)
		{
			if (function_exists('update_'.$func))
			{
				if (!call_user_func('update_'.$func, FALSE))
				{
				  $update_needed = TRUE;
				  continue;
				}
			}
		}

		$e107cache->set_sys('nq_admin_updatecheck', time().','.($update_needed ? '2,' : '1,').$e107info['e107_version'], TRUE);
	}
	else
	{
		$update_needed = ($dont_check_update == '2');
	}

	if ($update_needed === TRUE)
	{
		require_once (e_HANDLER.'form_handler.php');
		$frm = new e_form();
		$txt = "
		<form method='post' action='".e_ADMIN_ABS."e107_update.php'>
		<div>
			".ADLAN_120."
			".$frm->admin_button('e107_system_update', LAN_UPDATE, 'update')."
		</div>
		</form>
		";

		require_once (e_HANDLER.'message_handler.php');
		$emessage = &eMessage::getInstance();
		$emessage->add($txt);
	}
}



require_once(e_HANDLER.'e_upgrade_class.php');
//	$upg = new e_upgrade;
	//TODO Enable this before release!!
//	$upg->checkSiteTheme();
//	$upg->checkAllPlugins();


//--------------------------------------------
//	Check current prefs against latest list
//--------------------------------------------
function update_core_prefs($type='')
{
	global $pref, $admin_log, $e107info;
	$do_save = FALSE;
	$should = get_default_prefs();
	$accum = array();

	$just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing if an update is needed
   
	foreach ($should as $k => $v)
	{
		if ($k && !array_key_exists($k,$pref))
		{
			if ($just_check) return update_needed('Missing pref: '.$k);
			$pref[$k] = $v;
			$accum[] = $k;
			$do_save = TRUE;
		}
	}
	if ($do_save)
	{
		save_prefs();
		$admin_log->log_event('UPDATE_03',LAN_UPDATE_14.$e107info['e107_version'].'[!br!]'.implode(', ',$accum),E_LOG_INFORMATIVE,'');	// Log result of actual update
	}
	return $just_check;
}



if (defined('TEST_UPDATE'))
{
//--------------------------------------------
//	Test routine - to activate, define TEST_UPDATE
//--------------------------------------------
	function update_test_code($type='')
	{
		global $sql,$ns, $pref;
		$just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing whether an update is needed
		//--------------**************---------------
		// Add your test code in here
		//--------------**************---------------

		//--------------**************---------------
		// End of test code
		//--------------**************---------------
		return $just_check;
	}
}  // End of test routine




//--------------------------------------------
//	Upgrade later versions of 0.7.x to 0.8
//--------------------------------------------
function update_706_to_800($type='')
{
	global $sql,$ns, $pref, $admin_log, $e107info;
	$mes = e107::getMessage();

	// List of unwanted $pref values which can go
	$obs_prefs = array('frontpage_type','rss_feeds', 'log_lvcount', 'zone', 'upload_allowedfiletype', 'real', 'forum_user_customtitle',
						'utf-compatmode','frontpage_method','standards_mode','image_owner','im_quality', 'signup_option_timezone',
						'modules', 'plug_sc', 'plug_bb', 'plug_status', 'plug_latest', 'subnews_hide_news'
);

	// List of DB tables not required (includes a few from 0.6xx)
	$obs_tables = array('flood', 'headlines', 'stat_info', 'stat_counter', 'stat_last');


	// List of DB tables newly required  (defined in core_sql.php) (The existing dblog table gets renamed)
	$new_tables = array('admin_log','audit_log', 'dblog','news_rewrite', 'core_media', 'mail_recipients', 'mail_content');
	
	// List of core prefs that need to be converted from serialized to e107ArrayStorage. 
	$serialized_prefs = array("'emote'", "'menu_pref'", "'search_prefs'", "'emote_default'");


	// List of changed DB tables (defined in core_sql.php)
	// (primarily those which have changed significantly; for the odd field write some explicit code - it'll run faster)
	$changed_tables = array('user', 'dblog','admin_log', 'userclass_classes', 'banlist', 'menus',
							 'plugin', 'news', 'news_category','online', 'page', 'links');


	// List of changed DB tables from core plugins (defined in pluginname_sql.php file)
	// key = plugin directory name. Data = comma-separated list of tables to check
	// (primarily those which have changed significantly; for the odd field write some explicit code - it'll run faster)
	$pluginChangedTables = array('linkwords' => 'linkwords', 
								'featurebox' => 'featurebox',
								'links_page' => 'links_page',
								'poll' => 'polls'
								);
								
	$setCorePrefs = array( //modified prefs during upgrade. 
		'adminstyle' => 'infopanel',
		'admintheme' => 'jayya'
	);
	

	
	
	
	$do_save = TRUE;	
	
	foreach($setCorePrefs as $k=>$v)
	{
		$pref[$k] = $v;		
	}
	
		
	// List of changed menu locations. 						
	$changeMenuPaths = array(
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'sitebutton_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'compliance_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'powered_by_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'sitebutton_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'counter_menu'),
		array('oldpath'	=> 'siteinfo_menu',		'newpath' => 'siteinfo',	'menu' => 'latestnews_menu'),		
		array('oldpath'	=> 'compliance_menu',	'newpath' => 'siteinfo',	'menu' => 'compliance_menu'),
		array('oldpath'	=> 'powered_by_menu',	'newpath' => 'siteinfo',	'menu' => 'powered_by_menu'),
		array('oldpath'	=> 'sitebutton_menu',	'newpath' => 'siteinfo',	'menu' => 'sitebutton_menu'),
		array('oldpath'	=> 'counter_menu',		'newpath' => 'siteinfo',	'menu' => 'counter_menu'),
		array('oldpath'	=> 'usertheme_menu',	'newpath' => 'user_menu',	'menu' => 'usertheme_menu'),
		array('oldpath'	=> 'userlanguage_menu',	'newpath' => 'user_menu',	'menu' => 'userlanguage_menu'),
		array('oldpath'	=> 'lastseen_menu',		'newpath' => 'online',		'menu' => 'lastseen_menu')
	);			


	// List of DB tables (key) and field (value) which need changing to accommodate IPV6 addresses
	$ip_upgrade = array('comments' => 'comment_ip',
						'download_requests' => 'download_request_ip',
						'online' => 'online_ip',
						'submitnews' => 'submitnews_ip',
						'tmp' => 'tmp_ip',
						'chatbox' => 'cb_ip'
						);

	$db_parser = new db_table_admin;				// Class to read table defs and process them
	$do_save = FALSE;								// Set TRUE to update prefs when update complete
	$updateMessages = array();						// Used to log actions for the admin log

	$just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing whether an update is needed



	// Check that custompages have been imported from current theme.php file
	if(!array_key_exists('sitetheme_custompages',$pref))
	{
		$th = e107::getSingleton('themeHandler');
		$tmp = $th->getThemeInfo($pref['sitetheme']);
		if(is_array($tmp['custompages']))
		{
			if ($just_check) return update_needed();
			$pref['sitetheme_custompages'] = $tmp['custompages'];
			$do_save = TRUE;
		}
	}



	// Check notify prefs
	global $sysprefs, $eArrayStorage, $tp;
	$notify_prefs = $sysprefs -> get('notify_prefs');
	$notify_prefs = $eArrayStorage -> ReadArray($notify_prefs);

	$nt_changed = 0;
	if(vartrue($notify_prefs['event']))
	{
		foreach ($notify_prefs['event'] as $e => $d)
		{
			if (isset($d['type']))
			{
				if ($just_check) return update_needed('Notify pref: '.$e.' outdated');
				switch ($d['type'])
				{
					case 'main' :
						$notify_prefs['event'][$e]['class'] = e_UC_MAINADMIN;
						break;
					case 'class' :		// Should already have class defined
						break;
					case 'email' :
						$notify_prefs['event'][$e]['class'] = 'email';
						break;
					case 'off' :		// Need to disable
					default :
						$notify_prefs['event'][$e]['class'] = e_UC_NOBODY;		// Just disable if we don't know what else to do
				}
				$nt_changed++;
				unset($notify_prefs['event'][$e]['type']);
			}
		}
	}
	if ($nt_changed)
	{
		$s_prefs = $tp -> toDB($notify_prefs);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		// Could we use $sysprefs->set($s_prefs,'notify_prefs') instead - avoids caching problems  ????
		$status = ($sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'") === FALSE) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$message = str_replace('--COUNT--',$nt_changed,LAN_UPDATE_20);
		$mes->add($message, $status);
	}



	if (isset($pref['forum_user_customtitle']) && !isset($pref['signup_option_customtitle']))
	{
		if ($just_check) return update_needed();
		$pref['signup_option_customtitle'] = $pref['forum_user_customtitle'];
		unset($pref['forum_user_customtitle']);
		$mes->add(LAN_UPDATE_20.'customtitle', E_MESSAGE_SUCCESS);		
		$do_save = TRUE;
	}
	
	// convert all serialized core prefs to e107 ArrayStorage;
	$serialz_qry = "SUBSTRING( e107_value,1,5)!='array' AND e107_value !='' ";
    $serialz_qry .= "AND e107_name IN (".implode(",",$serialized_prefs).") ";
		if(e107::getDb()->db_Select("core", "*", $serialz_qry))
		{
			if ($just_check) return update_needed();
			while ($row = e107::getDb()->db_Fetch(MYSQL_ASSOC))
			{
				$status = e107::getDb('sql2')->db_Update('core',"e107_value=\"".convert_serialized($row['e107_value'])."\" WHERE e107_name='".$row['e107_name']."'");				
				$mes->add(LAN_UPDATE_22.$row['e107_name'], $status);
			}	
		}	
	
	//TODO de-serialize the user_prefs also. 
	
	// ++++++++ Modify Menu Paths +++++++. 
	if(varset($changeMenuPaths))
	{		
		foreach($changeMenuPaths as $val)
		{
			$qry = "SELECT menu_path FROM `#menus` WHERE menu_name = '".$val['menu']."' AND (menu_path='".$val['oldpath']."' || menu_path='".$val['oldpath']."/' ) LIMIT 1";
			if($sql->db_Select_gen($qry))
			{
				if ($just_check) return update_needed('Menu path changed required:  '.$val['menu'].' ');
				$updqry = "menu_path='".$val['newpath']."/' WHERE menu_name = '".$val['menu']."' AND (menu_path='".$val['oldpath']."' || menu_path='".$val['oldpath']."/' ) ";
				$status = $sql->db_Update('menus', $updqry) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
				$mes->add(LAN_UPDATE_23.'<b>'.$val['menu'].'</b> : '.$val['oldpath'].' => '.$val['newpath'], $status); // LAN_UPDATE_25;				
				// catch_error($sql);
			}	
		}
	}

	// Leave this one here.. just in case.. 
	//delete record for online_extended_menu (now only using one online menu)
	if($sql->db_Select('menus', '*', "menu_path='online_extended_menu' || menu_path='online_extended_menu/'"))
	{
		if ($just_check) return update_needed();

		$row=$sql->db_Fetch();

		//if online_extended is activated, we need to activate the new 'online' menu, and delete this record
		if($row['menu_location']!=0)
		{
			$status = $sql->db_Update("menus", "menu_name='online_menu', menu_path='online/' WHERE menu_path='online_extended_menu' || menu_path='online_extended_menu/' ") ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			$mes->add(LAN_UPDATE_23."<b>online_menu</b> : online/", $status); 				
		}
		else
		{	//else if the menu is not active
			//we need to delete the online_extended menu row, and change the online_menu to online
			$sql->db_Delete('menus', " menu_path='online_extended_menu' || menu_path='online_extended_menu/' ");
			// $updateMessages[] = LAN_UPXXDATE_31;
		}
		catch_error($sql);
	}

	//change menu_path for online_menu (if it still exists)
	if($sql->db_Select('menus', 'menu_path', "menu_path='online_menu' || menu_path='online_menu/'"))
	{
		if ($just_check) return update_needed();

		$status = $sql->db_Update("menus", "menu_path='online/' WHERE menu_path='online_menu' || menu_path='online_menu/' ") ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add(LAN_UPDATE_23."<b>online_menu</b> : online/", $status); 		
		catch_error($sql);
	}


//---------------------------------------------------------
//			Comments - split user field
//---------------------------------------------------------
	if($sql->db_Field('comments','comment_author'))
	{
		if ($just_check) return update_needed('Comment table author field update');

		if ((!$sql->db_Field('comments','comment_author_id'))		// Check to see whether new fields already added - maybe data copy failed part way through
			&& (!$sql->db_Select_gen("ALTER TABLE `#comments`
				ADD COLUMN comment_author_id int(10) unsigned NOT NULL default '0' AFTER `comment_author`,
				ADD COLUMN comment_author_name varchar(100) NOT NULL default '' AFTER `comment_author_id`")))
		{
			// Flag error
			// $commentMessage = LAN_UPDAXXTE_34;
			$mes->add(LAN_UPDATE_21."comments", E_MESSAGE_ERROR); 	
		}
		else
		{
			if (FALSE ===$sql->db_Update('comments',"comment_author_id=SUBSTRING_INDEX(`comment_author`,'.',1),  comment_author_name=SUBSTRING(`comment_author` FROM POSITION('.' IN `comment_author`)+1)"))
			{
				// Flag error
				$mes->add(LAN_UPDATE_21.'comments', E_MESSAGE_ERROR); 	
			}
			else
			{	// Delete superceded field - comment_author
				if (!$sql->db_Select_gen("ALTER TABLE `#comments` DROP COLUMN `comment_author`"))
				{
					// Flag error
					$mes->add(LAN_UPDATE_24.'comments - comment_author', E_MESSAGE_ERROR); 	
				}
			}
		}

		$mes->add(LAN_UPDATE_21.'comments', E_MESSAGE_SUCCESS);
	}



	//	Add index to download history
	if (FALSE !== ($temp = addIndexToTable('download_requests', 'download_request_datestamp', $just_check, $updateMessages)))
	{
		if ($just_check)
		{
			return update_needed($temp);
		}
	}

	// Extra index to tmp table
	if (FALSE !== ($temp = addIndexToTable('tmp', 'tmp_time', $just_check, $updateMessages)))
	{
		if ($just_check)
		{
			return update_needed($temp);
		}
	}

	// Extra index to rss table (if used)
	if (FALSE !== ($temp = addIndexToTable('rss', 'rss_name', $just_check, $updateMessages, TRUE)))
	{
		if ($just_check)
		{
			return update_needed($temp);
		}
	}

	// Front page prefs (logic has changed)
	if (!isset($pref['frontpage_force']))
	{	// Just set basic options; no real method of converting the existing
		if ($just_check) return update_needed('Change front page prefs');
		$pref['frontpage_force'] = array(e_UC_PUBLIC => '');
		$pref['frontpage'] = array(e_UC_PUBLIC => 'news.php');
		// $_pdateMessages[] = LAN_UPDATE_38; //FIXME
		$mes->add(LAN_UPDATE_20."frontpage",E_MESSAGE_SUCCESS);
		$do_save = TRUE;
	}


	if ($sql->db_Table_exists('newsfeed'))
	{	// Need to extend field newsfeed_url varchar(250) NOT NULL default ''
		if ($sql->db_Query("SHOW FIELDS FROM ".MPREFIX."newsfeed LIKE 'newsfeed_url'"))
		{
			$row = $sql -> db_Fetch();
			if (str_replace('varchar', 'char', strtolower($row['Type'])) != 'char(250)')
			{
				if ($just_check) return update_needed('Update newsfeed field definition');
				$status = $sql->db_Select_gen("ALTER TABLE `".MPREFIX."newsfeed` MODIFY `newsfeed_url` VARCHAR(250) NOT NULL DEFAULT '' ") ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
				$updateMessages[] = LAN_UPDATE_40; //FIXME
				$mes->add(LAN_UPDATE_21."newsfeed",$status);
			//	catch_error($sql);
			}
		}
	}


	//TODO use generic function for this update. 
	if ($sql->db_Table_exists('download'))
	{	// Need to extend field download_url varchar(255) NOT NULL default ''
		if ($sql->db_Query("SHOW FIELDS FROM ".MPREFIX."download LIKE 'download_url'"))
		{
			$row = $sql -> db_Fetch();
			if (str_replace('varchar', 'char', strtolower($row['Type'])) != 'char(255)')
			{
				if ($just_check) return update_needed('Update download table field definition');
				$sql->db_Select_gen("ALTER TABLE `#download` MODIFY `download_url` VARCHAR(255) NOT NULL DEFAULT '' ");
				$updateMessages[] = LAN_UPDATE_52;  //FIXME
				catch_error($sql);
			}
		}
	}

	//TODO use generic function for this update. 
	if ($sql->db_Table_exists('download_mirror'))
	{	// Need to extend field download_url varchar(255) NOT NULL default ''
		if ($sql->db_Select_gen("SHOW FIELDS FROM ".MPREFIX."download_mirror LIKE 'mirror_url'"))
		{
			$row = $sql -> db_Fetch();
			if (str_replace('varchar', 'char', strtolower($row['Type'])) != 'char(255)')
			{
				if ($just_check) return update_needed('Update download mirror table field definition');
				$sql->db_Select_gen("ALTER TABLE `".MPREFIX."download_mirror` MODIFY `mirror_url` VARCHAR(255) NOT NULL DEFAULT '' ");
				$updateMessages[] = LAN_UPDATE_53;  //FIXME
				catch_error($sql);
			}
		}
	}


	// Check need for user timezone before we delete the field
	if (varsettrue($pref['signup_option_timezone']))
	{
		if ($sql->db_Field('user', 'user_timezone', '', TRUE) && !$sql->db_Field('user_extended','user_timezone','',TRUE))
		{
			if ($just_check) return update_needed('Move user timezone info');
			if (!copy_user_timezone())
			{  // Error doing the transfer
				$updateMessages[] = LAN_UPDATE_42;  //FIXME
				return FALSE;
			}
			$updateMessages[] = LAN_UPDATE_41;
		}
	}


	// Tables defined in core_sql.php
	//---------------------------------
	if ($sql->db_Table_exists('dblog') && !$sql->db_Table_exists('admin_log'))
	{
		if ($just_check) return update_needed('Rename dblog to admin_log');
		$sql->db_Select_gen('ALTER TABLE `'.MPREFIX.'dblog` RENAME `'.MPREFIX.'admin_log`');
		catch_error($sql);
		$updateMessages[] = LAN_UPDATE_43;  //FIXME
	}

	
	// Next bit will be needed only by the brave souls who used an early CVS - probably delete before release
	if ($sql->db_Table_exists('rl_history') && !$sql->db_Table_exists('dblog'))
	{
		if ($just_check) return update_needed('Rename rl_history to dblog');
		$sql->db_Select_gen('ALTER TABLE `'.MPREFIX.'rl_history` RENAME `'.MPREFIX.'dblog`');
		$updateMessages[] = LAN_UPDATE_44;  //FIXME
		catch_error($sql);
	}
	  
	// New tables required (list at top. Definitions in core_sql.php)
	foreach ($new_tables as $nt)
	{
		if (!$sql->db_Table_exists($nt))
		{
			if ($just_check) return update_needed('Add table: '.$nt);
			// Get the definition
			$defs = $db_parser->get_table_def($nt,e_ADMIN.'sql/core_sql.php');
			if (count($defs)) // **** Add in table here
			{	
				$status = $sql->db_Select_gen('CREATE TABLE `'.MPREFIX.$defs[0][1].'` ('.$defs[0][2].') TYPE='.$defs[0][3]) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			//	$updateMessages[] = LAN_UPDATE_45.$defs[0][1];		
				$mes->add(LAN_UPDATE_27.$defs[0][1], $status); 		//TODO - all update messages should work like this.  But also need $updateMessages[] for admin log
				// catch_error($sql);
			}
			else
			{  // error parsing defs file
				$mes->add(LAN_UPDATE_46.$defs[0][1], E_MESSAGE_ERROR);
			}
			unset($defs);
		}
	}


	// Tables whose definition needs changing significantly
     $debugLevel = E107_DBG_SQLDETAILS;

	foreach ($changed_tables as $ct)
	{
	  $req_defs = $db_parser->get_table_def($ct,e_ADMIN."sql/core_sql.php");
	  $req_fields = $db_parser->parse_field_defs($req_defs[0][2]);					// Required definitions
	  if ($debugLevel)
	  {
	  	$mes->add("Required table structure: <br />".$db_parser->make_field_list($req_fields), E_MESSAGE_DEBUG);			
	  } 

	  if ((($actual_defs = $db_parser->get_current_table($ct)) === FALSE) || !is_array($actual_defs))			// Adds current default prefix
	  {
			$mes->add("Couldn't get table structure: ".$ct, E_MESSAGE_DEBUG);		
	  }
	  else
	  {
//		echo $db_parser->make_table_list($actual_defs);
		$actual_fields = $db_parser->parse_field_defs($actual_defs[0][2]);
		if ($debugLevel)
		{
			$mes->add("Actual table structure: <br />".$db_parser->make_field_list($actual_fields), E_MESSAGE_DEBUG);		
		} 

		$diffs = $db_parser->compare_field_lists($req_fields,$actual_fields);
		if (count($diffs[0]))
		{  // Changes needed
		  	if ($just_check) return update_needed("Field changes rqd; table: ".$ct);
		
			// Do the changes here
		  	if ($debugLevel)
		  	{
		  		$mes->add("List of changes found:<br />".$db_parser->make_changes_list($diffs), E_MESSAGE_DEBUG);		
		  	} 
		  
			$qry = 'ALTER TABLE '.MPREFIX.$ct.' '.implode(', ',$diffs[1]);
		  
			if ($debugLevel)
			{
				$mes->add("Update Query used: ".$qry, E_MESSAGE_DEBUG);	
			} 
		  
			$status = $sql->db_Select_gen($qry) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR; 
			$mes->add(LAN_UPDATE_21.$ct, $status);
		  	catch_error($sql);
		}
	  }
	}


	// Plugin tables whose definition needs changing significantly
	foreach ($pluginChangedTables as $plugName => $plugList)
	{
		if (plugInstalled($plugName))
		{
			$ttc = explode(',',$plugList);
			$mes = e107::getMessage();
			foreach ($ttc as $ct)
			{
				$sqlDefs = e_PLUGIN.$plugName.'/'.str_replace('_menu','',$plugName).'_sql.php';		// Filename containing definitions
//				echo "Looking at file: {$sqlDefs}, table {$ct}<br />";
				$req_defs = $db_parser->get_table_def($ct,$sqlDefs);
				if (!is_array($req_defs))
				{
					echo "Couldn't get definitions from file {$sqlDefs}<br />";
					continue;
				}
				$req_fields = $db_parser->parse_field_defs($req_defs[0][2]);					// Required definitions
				if (E107_DBG_SQLDETAILS)
				{
				  $message = "Required plugin table structure: <br />".$db_parser->make_field_list($req_fields);
				  
				  $mes->add($message, E_MESSAGE_DEBUG);
				  	
				} 

				if ((($actual_defs = $db_parser->get_current_table($ct)) === FALSE) || !is_array($actual_defs))			// Adds current default prefix
				{
//	    			echo "Couldn't get table structure: {$ct}<br />";
				}
				else
				{
//					echo $db_parser->make_table_list($actual_defs);
					$actual_fields = $db_parser->parse_field_defs($actual_defs[0][2]);
					if (E107_DBG_SQLDETAILS)
					{					
						$message= "Actual table structure: <br />".$db_parser->make_field_list($actual_fields);
						$mes->add($message, E_MESSAGE_DEBUG);
					} 

					$diffs = $db_parser->compare_field_lists($req_fields,$actual_fields);
					if (count($diffs[0]))
					{  // Changes needed
						if (E107_DBG_SQLDETAILS)
						{
							$message = "List of changes found:<br />".$db_parser->make_changes_list($diffs);
							$mes->add($message, E_MESSAGE_DEBUG);	
						} 
						if ($just_check) return update_needed("Field changes rqd; plugin table: ".$ct);
						// Do the changes here
						$qry = 'ALTER TABLE '.MPREFIX.$ct.' '.implode(', ',$diffs[1]);
						if (E107_DBG_SQLDETAILS)
						{
							 $message = "Update Query used: ".$qry."<br />";
							 $mes->add($message, E_MESSAGE_DEBUG);	
						}
						$sql->db_Select_gen($qry);
						$updateMessages[] = LAN_UPDATE_51.$ct;  //FIXME
						catch_error($sql);
					}
				}
			}
		}
	}

	// This has to be done after the table is upgraded
	if($sql->db_Select('plugin', 'plugin_category', "plugin_category = ''"))
	{
		if ($just_check) return update_needed('Update plugin table');
		require_once(e_HANDLER.'plugin_class.php');
		$ep = new e107plugin;
		$ep -> update_plugins_table();
	//	$_pdateMessages[] = LAN_UPDATE_XX24; 
	 //	catch_error($sql);
	}


	// Obsolete tables (list at top)
	foreach ($obs_tables as $ot)
	{
		if ($sql->db_Table_exists($ot))
		{
			if ($just_check) return update_needed("Delete table: ".$ot);
			$status = $sql->db_Select_gen('DROP TABLE `'.MPREFIX.$ot.'`') ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			$mes->add(LAN_UPDATE_25.$ot, $status);			
		}
	}


	// Tables where IP address field needs updating to accommodate IPV6
	// Set to varchar(45) - just in case something uses the IPV4 subnet (see http://en.wikipedia.org/wiki/IPV6#Notation)
	foreach ($ip_upgrade as $t => $f)
	{
	  if ($sql->db_Table_exists($t))
	  {		// Check for table - might add some core plugin tables in here
	    if ($field_info = ($sql->db_Field($t, $f, '', TRUE)))
	    {
		  if (strtolower($field_info['Type']) != 'varchar(45)')
		  {
            if ($just_check) return update_needed('Update IP address field '.$f.' in table '.$t);
			$status = $sql->db_Select_gen("ALTER TABLE `".MPREFIX.$t."` MODIFY `{$f}` VARCHAR(45) NOT NULL DEFAULT '';") ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			$mes->add(LAN_UPDATE_26.$t.' - '.$f, $status);				
			// catch_error($sql);
		  }
	    }
	    else
		{
			// Got a strange error here
		}
	  }
	}

	// Obsolete prefs (list at top)
	// Intentionally do this last - we may check some of them during the update
	$accum = array();
	foreach ($obs_prefs as $p)
	{
	  if (isset($pref[$p]))
	  {
	    if ($just_check) return update_needed('Remove obsolete prefs');
		unset($pref[$p]);
		$do_save = TRUE;
		$accum[] = $p;
	  }
	}


	if ($do_save)
	{
		save_prefs();
		$mes->add(LAN_UPDATE_50);
		$updateMessages[] = LAN_UPDATE_50.implode(', ',$accum); 	// Note for admin log
	}
	
	//FIXME grab message-stack from $mes for the log. 

	if ($just_check) return TRUE;
	$admin_log->log_event('UPDATE_01',LAN_UPDATE_14.$e107info['e107_version'].'[!br!]'.implode('[!br!]',$updateMessages),E_LOG_INFORMATIVE,'');	// Log result of actual update
	return $just_check;
}



function update_70x_to_706($type='')
{
	global $sql,$ns, $pref, $e107info, $admin_log, $emessage;

	$just_check = $type == 'do' ? FALSE : TRUE;
	if(!$sql->db_Field("plugin",5))  // not plugin_rss so just add the new one.
	{
	  if ($just_check) return update_needed();
      $sql->db_Select_gen("ALTER TABLE `".MPREFIX."plugin` ADD `plugin_addons` TEXT NOT NULL ;");
	  catch_error($sql);
	}

	//rename plugin_rss field
	if($sql->db_Field("plugin",5) == "plugin_rss")
	{
	  if ($just_check) return update_needed();
	  $sql->db_Select_gen("ALTER TABLE `".MPREFIX."plugin` CHANGE `plugin_rss` `plugin_addons` TEXT NOT NULL;");
	  catch_error($sql);
	}


	if($sql->db_Field("dblog",5) == "dblog_query")
	{
      if ($just_check) return update_needed();
	  $sql->db_Select_gen("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_query` `dblog_title` VARCHAR( 255 ) NOT NULL DEFAULT '';");
	  catch_error($sql);
	  $sql->db_Select_gen("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_remarks` `dblog_remarks` TEXT NOT NULL;");
	  catch_error($sql);
	}

	if(!$sql->db_Field("plugin","plugin_path","UNIQUE"))
	{
      if ($just_check) return update_needed();
      if(!$sql->db_Select_gen("ALTER TABLE `".MPREFIX."plugin` ADD UNIQUE (`plugin_path`);"))
	  {
		$mes = LAN_UPDATE_12." : <a href='".e_ADMIN."db.php?plugin'>".ADLAN_145."</a>.";
        //$ns -> tablerender(LAN_ERROR,$mes);
        $emessage->add($mes, E_MESSAGE_ERROR);
       	catch_error($sql);
	  }
	}

	if(!$sql->db_Field("online",6)) // online_active field
	{
	  if ($just_check) return update_needed();
	  $sql->db_Select_gen("ALTER TABLE ".MPREFIX."online ADD online_active INT(10) UNSIGNED NOT NULL DEFAULT '0'");
	  catch_error($sql);
	}

	if ($sql -> db_Query("SHOW INDEX FROM ".MPREFIX."tmp"))
	{
	  $row = $sql -> db_Fetch();
	  if (!in_array('tmp_ip', $row))
	  {
		if ($just_check) return update_needed();
		$sql->db_Select_gen("ALTER TABLE `".MPREFIX."tmp` ADD INDEX `tmp_ip` (`tmp_ip`);");
		$sql->db_Select_gen("ALTER TABLE `".MPREFIX."upload` ADD INDEX `upload_active` (`upload_active`);");
		$sql->db_Select_gen("ALTER TABLE `".MPREFIX."generic` ADD INDEX `gen_type` (`gen_type`);");
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
	if ($just_check) return TRUE;
	$admin_log->log_event('UPDATE_02',LAN_UPDATE_14.$e107info['e107_version'],E_LOG_INFORMATIVE,'');	// Log result of actual update
	return $just_check;		// TRUE if no updates needed, FALSE if updates needed and completed

}



// Carries out the copy of timezone data from the user record to an extended user field
// Return TRUE on success, FALSE on failure
function copy_user_timezone()
{
  global $sql, $sql2, $tp;
  require_once(e_HANDLER.'user_extended_class.php');
  $ue = new e107_user_extended;
  $tmp = $ue->parse_extended_xml('getfile');
  $tmp['timezone']['parms'] = $tp->toDB($tmp['timezone']['parms']);
  if(!$ue->user_extended_add($tmp['timezone']))
  {
	return FALSE;
  }

// Created the field - now copy existing data
  if ($sql->db_Select('user','user_id, user_timezone'))
  {
	while ($row = $sql->db_Fetch())
	{
	  $sql2->db_Update('user_extended',"`user_timezone`='{$row['user_timezone']}' WHERE `user_extended_id`={$row['user_id']}");
	}
  }
  return TRUE;		// All done!
}


function update_needed($message='')
{
	global $ns, $update_debug;

	$emessage = e107::getMessage();

	if ($update_debug) $emessage->add("Update: ".$message, E_MESSAGE_DEBUG);
	if(E107_DEBUG_LEVEL)
	{
		$tmp = debug_backtrace();
		//$ns->tablerender("", "<div style='text-align:center'>Update required in ".basename(__FILE__)." on line ".$tmp[0]['line']."</div>");
		$emessage->add("Update required in ".basename(__FILE__)." on line ".$tmp[0]['line']." (".$message.")", E_MESSAGE_DEBUG);
	}
	return FALSE;
}


/*
function mysql_table_exists($table)
{
  $exists = mysql_query("SELECT 1 FROM ".MPREFIX."$table LIMIT 0");
  if ($exists) return TRUE;
  return FALSE;
}
*/


// Add index to a table. Returns FALSE if not required. Returns a message if required and just checking
function addIndexToTable($target, $indexSpec, $just_check, &$updateMessages, $optionalTable=FALSE)
{
	global $sql;
	if (!$sql->db_Table_exists($target))
	{
		if ($optionalTable)
		{
			return !$just_check;		// Nothing to do it table is optional and not there
		}
		$updateMessages[] = str_replace(array('--TABLE--','--INDEX--'),array($target,$indexSpec),LAN_UPDATE_54);
		return !$just_check;		// No point carrying on - return 'nothing to do'
	}
	if ($sql->db_Select_gen("SHOW INDEX FROM ".MPREFIX.$target))
	{
		$found = FALSE;
		while ($row = $sql -> db_Fetch())
		{		// One index per field
			if (in_array($indexSpec, $row))
			{
				return !$just_check;		// Found - nothing to do
			}
		}
		// Index not found here
		if ($just_check)
		{
			return 'Required to add index to '.$target;
		}
		$sql->db_Select_gen("ALTER TABLE `".MPREFIX.$target."` ADD INDEX `".$indexSpec."` (`".$indexSpec."`);");
		$updateMessages[] = str_replace(array('--TABLE--','--INDEX--'),array($target,$indexSpec),LAN_UPDATE_37);
	}
	return FALSE;
}


/**	Check for database access errors
 *	@param reference $target - pointer to db object
 *	@return none
 */
function catch_error(&$target)
{
	if (vartrue($target->mySQLlastErrText) && E107_DEBUG_LEVEL != 0)
	{
		$tmp2 = debug_backtrace();
		$tmp = $target->mySQLlastErrText;
		echo $tmp." [ ".basename(__FILE__)." on line ".$tmp2[0]['line']."] <br />";
	}
	return;
}


function get_default_prefs()
{
	$xmlArray = e107::getSingleton('xmlClass')->loadXMLfile(e_FILE."default_install.xml",'advanced');
	$pref = e107::getSingleton('xmlClass')->e107ImportPrefs($xmlArray,'core');
	return $pref;
}

function convert_serialized($serializedData)
{
	$arrayData = unserialize($serializedData);
	return e107::getArrayStorage()->WriteArray($arrayData,FALSE);
}


?>
