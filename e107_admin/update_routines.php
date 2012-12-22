<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

/**
 *	@package    e107
 *	@subpackage	admin
 *	@version 	$Id$;
 *
 *	Update routines from older e107 versions to current.
 *
 *	Also links to plugin update routines.
 *
 *	2-stage process - routines identify whether update is required, and then execute as instructed.
 */

// [debug=8] shows the operations on major table update

require_once('../class2.php');
require_once(e_HANDLER.'db_table_admin_class.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_e107_update.php');
// Modified update routine - combines checking and update code into one block per function
//		- reduces code size typically 30%.
//		- keeping check and update code together should improve clarity/reduce mis-types etc


// @todo: how do we handle update of multi-language tables?

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

$mes = e107::getMessage();
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
	/*
	if ($sql->db_Select('plugin', 'plugin_id, plugin_version, plugin_path', 'plugin_installflag=1'))
	{
		while ($row = $sql->db_Fetch())
		{  // Mark plugins for update which have a specific update file, or a plugin.php file to check
			if(is_readable(e_PLUGIN.$row['plugin_path'].'/'.$row['plugin_path'].'_update_check.php') || is_readable(e_PLUGIN.$row['plugin_path'].'/plugin.php') || is_readable(e_PLUGIN.$row['plugin_path'].'/'.$row['plugin_path'].'_setup.php'))
			{
				$dbupdateplugs[$row['plugin_path']] = $row['plugin_version'];
				//TODO - Add support for {plugins}_setup.php upgrade check and routine. 
			}
		}
	}
	*/
	
	if($dbupdateplugs = e107::getConfig('core')->get('plug_installed'))
	{
		// Read in each update file - this will add an entry to the $dbupdatep array if a potential update exists
		foreach ($dbupdateplugs as $path => $ver)
		{
			if(!is_file(e_PLUGIN.$path."/plugin.xml")) 
			{		
				$fname = e_PLUGIN.$path.'/'.$path.'_update_check.php';  // DEPRECATED - left for BC only. 
				if (is_readable($fname)) include_once($fname);
			}
			
			$fname = e_PLUGIN.$path.'/'.$path.'_setup.php';
			if (is_readable($fname))
			{
				$dbupdatep[$path] =  $path ; // ' 0.7.x forums '.LAN_UPDATE_9.' 0.8 forums';
				include_once($fname);
			} 
		}
	}

	// List of potential updates
	if (defined('TEST_UPDATE'))
	{
		$dbupdate['test_code'] = 'Test update routine';
	}
	$dbupdate['core_prefs'] = LAN_UPDATE_13;						// Prefs check
	$dbupdate['706_to_800'] = LAN_UPDATE_8.' 1.x '.LAN_UPDATE_9.' 2.0 (Must be run first)';
//	$dbupdate['70x_to_706'] = LAN_UPDATE_8.' .70x '.LAN_UPDATE_9.' .706';
}		// End if (!$dont_check_update)




/**
 *	Master routine to call to check for updates
 */
function update_check()
{
	
	$ns = e107::getRender();
	$e107cache = e107::getCache();
	$sql = e107::getDb();
	$mes = e107::getMessage();
		
	global $dont_check_update, $e107info;
	global $dbupdate, $dbupdatep, $e107cache;

	$update_needed = FALSE;

	if ($dont_check_update === FALSE)
	{
		
		foreach($dbupdate as $func => $rmks) // See which core functions need update
		{
		  if (function_exists('update_'.$func))
			{
				if (!call_user_func('update_'.$func, FALSE))
				{
				  $update_needed = TRUE;
				  break;
				}
			}
		}

		// Now check plugins - XXX DEPRECATED 
		foreach($dbupdatep as $func => $rmks)
		{
			if (function_exists('update_'.$func))
			{
				if (!call_user_func('update_'.$func, FALSE))
				{
				  $update_needed = TRUE;
				  break;
				}
			}
		}
		
		// New in v2.x
		if(e107::getPlugin()->updateRequired('boolean'))
		{
			 $update_needed = TRUE;		
		}
	

	//	$e107cache->set_sys('nq_admin_updatecheck', time().','.($update_needed ? '2,' : '1,').$e107info['e107_version'], TRUE);
	}
	else
	{
		$update_needed = ($dont_check_update == '2');
	}

	if ($update_needed === TRUE)
	{
		$frm = e107::getForm();
		
		$txt = "
		<form method='post' action='".e_ADMIN_ABS."e107_update.php'>
		<div>
			".ADLAN_120."
			".$frm->admin_button('e107_system_update', LAN_UPDATE, 'other')."
		</div>
		</form>
		";
		
		$mes->addInfo($txt);
	}
}

	
//XXX to be reworked eventually - for checking remote 'new versions' of plugins and installed theme. 
// require_once(e_HANDLER.'e_upgrade_class.php');
//	$upg = new e_upgrade;

//	$upg->checkSiteTheme();
//	$upg->checkAllPlugins();



//--------------------------------------------
//	Check current prefs against latest list
//--------------------------------------------
function update_core_prefs($type='')
{
	global $pref, $e107info;
	$admin_log = e107::getAdminLog();
	$do_save = FALSE;
	$should = get_default_prefs();

	$just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing if an update is needed
   
	foreach ($should as $k => $v)
	{
		if ($k && !array_key_exists($k,$pref))
		{
			if ($just_check) return update_needed('Missing pref: '.$k);
			$pref[$k] = $v;
			$admin_log->logMessage($k.' => '.$v, E_MESSAGE_NODISPLAY, E_MESSAGE_INFO);
			$do_save = TRUE;
		}
	}
	if ($do_save)
	{
		save_prefs();
		$admin_log->logMessage(LAN_UPDATE_14.$e107info['e107_version'], E_MESSAGE_NODISPLAY, E_MESSAGE_INFO);
		$admin_log->flushMessages('UPDATE_03',E_LOG_INFORMATIVE);
		//$admin_log->log_event('UPDATE_03',LAN_UPDATE_14.$e107info['e107_version'].'[!br!]'.implode(', ',$accum),E_LOG_INFORMATIVE,'');	// Log result of actual update
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
	global $pref, $e107info;
	global $sysprefs, $eArrayStorage;

	//$mes = new messageLog;		// Combined logging and message displaying handler
	//$mes = e107::getMessage();
	$log = e107::getAdminLog();		// Used for combined logging and message displaying
	$sql = e107::getDb();
	$sql2 = e107::getDb('sql2');
	$tp = e107::getParser();
	$ns = e107::getRender();
	
	e107::getCache()->clearAll('db');

	// List of unwanted $pref values which can go
	$obs_prefs = array('frontpage_type','rss_feeds', 'log_lvcount', 'zone', 'upload_allowedfiletype', 'real', 'forum_user_customtitle',
						'utf-compatmode','frontpage_method','standards_mode','image_owner','im_quality', 'signup_option_timezone',
						'modules', 'plug_sc', 'plug_bb', 'plug_status', 'plug_latest', 'subnews_hide_news', 'upload_storagetype'
				);

	// List of DB tables not required (includes a few from 0.6xx)
	$obs_tables = array('flood', 'headlines', 'stat_info', 'stat_counter', 'stat_last', 'session', 'preset', 'tinymce');


	// List of DB tables newly required  (defined in core_sql.php) (The existing dblog table gets renamed)
	// No Longer required. - automatically checked against core_sql.php. 
//	$new_tables = array('audit_log', 'dblog', 'news_rewrite', 'core_media', 'core_media_cat','cron', 'mail_recipients', 'mail_content');

	// List of core prefs that need to be converted from serialized to e107ArrayStorage.
	$serialized_prefs = array("'emote'", "'menu_pref'", "'search_prefs'", "'emote_default'");


	$create_dir = array(e_MEDIA,e_SYSTEM,e_CACHE,e_CACHE_CONTENT,e_CACHE_IMAGE, e_CACHE_DB, e_LOG, e_BACKUP, e_CACHE_URL, e_TEMP);
	
	foreach($create_dir as $dr)
	{
		if(!is_dir($dr))
		{
			mkdir($dr, 0755);	
		}				
	}

	// List of changed DB tables (defined in core_sql.php)
	// No Longer required. - automatically checked against core_sql.php. 
	// (primarily those which have changed significantly; for the odd field write some explicit code - it'll run faster)
	// $changed_tables = array('user', 'dblog', 'admin_log', 'userclass_classes', 'banlist', 'menus',
							 // 'plugin', 'news', 'news_category', 'online', 'page', 'links', 'comments');


	// List of changed DB tables from core plugins (defined in pluginname_sql.php file)
	// key = plugin directory name. Data = comma-separated list of tables to check
	// (primarily those which have changed significantly; for the odd field write some explicit code - it'll run faster)
	// No Longer required. - automatically checked by db-verify 
	/* $pluginChangedTables = array('linkwords' => 'linkwords',
								'featurebox' => 'featurebox',
								'links_page' => 'links_page',
								'poll' => 'polls',
								'content' => 'pcontent'
								);
	 
	 */
/*
	$setCorePrefs = array( //modified prefs during upgrade.
		'adminstyle' 		=> 'infopanel',
		'admintheme' 		=> 'bootstrap',
		'admincss'			=> 'admin_style.css',
		'resize_dimensions' => array(
			'news-image' 	=> array('w' => 250, 'h' => 250),
			'news-bbcode' 	=> array('w' => 250, 'h' => 250),
			'page-bbcode' 	=> array('w' => 250, 'h' => 250)
		)
	);
*/




	$do_save = TRUE;


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
		array('oldpath'	=> 'lastseen_menu',		'newpath' => 'online',		'menu' => 'lastseen_menu'),
		array('oldpath'	=> 'other_news_menu',	'newpath' => 'news',		'menu' => 'other_news_menu'),
		array('oldpath'	=> 'other_news_menu',	'newpath' => 'news',		'menu' => 'other_news2_menu')
		
	);


	// List of DB tables (key) and field (value) which need changing to accommodate IPV6 addresses
	$ip_upgrade = array('download_requests' => 'download_request_ip',
						'submitnews' 		=> 'submitnews_ip',
						'tmp' 				=> 'tmp_ip',
						'chatbox' 			=> 'cb_ip'
						);

	$db_parser = new db_table_admin;				// Class to read table defs and process them
	$do_save = FALSE;								// Set TRUE to update prefs when update complete
	$updateMessages = array();						// Used to log actions for the admin log - TODO: will go once all converted to new class

	$just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing whether an update is needed

	if (!$just_check)
	{
		foreach(vartrue($setCorePrefs) as $k=>$v)
		{
			$pref[$k] = $v;
		}
	}

	if (!$just_check)
	{
		$log->logMessage(LAN_UPDATE_14.$e107info['e107_version'], E_MESSAGE_NODISPLAY);
	}

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



	$statusTexts = array(E_MESSAGE_SUCCESS => 'Success', E_MESSAGE_ERROR => 'Fail', E_MESSAGE_INFO => 'Info');

	if (isset($pref['forum_user_customtitle']) && !isset($pref['signup_option_customtitle']))
	{
		if ($just_check) return update_needed();
		$pref['signup_option_customtitle'] = $pref['forum_user_customtitle'];
		unset($pref['forum_user_customtitle']);
		$log->logMessage(LAN_UPDATE_20.'customtitle', E_MESSAGE_SUCCESS);		
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
				$log->logMessage(LAN_UPDATE_22.$row['e107_name'], $status);
			}	
		}	
	
	//@TODO de-serialize the user_prefs also. 
	


	// Move the maximum online counts from menu prefs to a separate pref - 'history'
	$menuConfig = e107::getConfig('menu'); 
	if ($menuConfig->get('most_members_online') || $menuConfig->get('most_guests_online') || $menuConfig->get('most_online_datestamp'))
	{
		$status = E_MESSAGE_DEBUG;
		if ($just_check) return update_needed('Move online counts from menupref');
		$newPrefs = e107::getConfig('history');
		foreach (array('most_members_online', 'most_guests_online', 'most_online_datestamp') as $v)
		{
			if (FALSE === $newPrefs->get($v, FALSE))
			{
				if (FALSE !== $menuConfig->get($v, FALSE))
				{
					$newPrefs->set($v,$menuConfig->get($v));
				}
				else
				{
					$newPrefs->set($v, 0);
				}
			}
			$menuConfig->remove($v);
		}
		$result = $newPrefs->save(false, true, false);
		if ($result === TRUE)
		{
			$resultMessage = 'Historic member counts updated';
		}
		elseif ($result === FALSE)
		{
			$resultMessage = 'moving historic member counts';
			$status = E_MESSAGE_ERROR;
		}
		else
		{	// No change
			$resultMessage = 'Historic member counts already updated';
			$status = E_MESSAGE_INFO;
		}
		$result = $menuConfig->save(false, true, false);	// Save updated menuprefs - without the counts
		//$updateMessages[] = $statusTexts[$status].': '.$resultMessage;		// Admin log message
		$log->logMessage($resultMessage,$status);									// User message
	}



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
				$status = $sql->db_Update('menus', $updqry) ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
				$log->logMessage(LAN_UPDATE_23.'<b>'.$val['menu'].'</b> : '.$val['oldpath'].' => '.$val['newpath'], $status); // LAN_UPDATE_25;				
				// catch_error($sql);
			}	
		}
	}

	// Leave this one here.. just in case.. 
	//delete record for online_extended_menu (now only using one online menu)
	if($sql->db_Select('menus', '*', "menu_path='online_extended_menu' || menu_path='online_extended_menu/'"))
	{
		if ($just_check) return update_needed("The Menu table needs to have some paths corrected in its data.");

		$row=$sql->db_Fetch();

		//if online_extended is activated, we need to activate the new 'online' menu, and delete this record
		if($row['menu_location']!=0)
		{
			$status = $sql->db_Update('menus', "menu_name='online_menu', menu_path='online/' WHERE menu_path='online_extended_menu' || menu_path='online_extended_menu/' ") ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
			$log->logMessage(LAN_UPDATE_23."<b>online_menu</b> : online/", $status); 				
		}
		else
		{	//else if the menu is not active
			//we need to delete the online_extended menu row, and change the online_menu to online
			$sql->db_Delete('menus', " menu_path='online_extended_menu' || menu_path='online_extended_menu/' ");
			$log->logMessage(LAN_UPDATE_31, E_MESSAGE_DEBUG);
		}
		catch_error($sql);
	}

	//change menu_path for online_menu (if it still exists)
	if($sql->db_Select('menus', 'menu_path', "menu_path='online_menu' || menu_path='online_menu/'"))
	{
		if ($just_check) return update_needed();

		$status = $sql->db_Update('menus', "menu_path='online/' WHERE menu_path='online_menu' || menu_path='online_menu/' ") ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
		$log->logMessage(LAN_UPDATE_23."<b>online_menu</b> : online/", $status); 		
		catch_error($sql);
	}

	if (!$just_check)
	{	
		// Alert Admin to delete deprecated menu folders. 
		$chgPath = array();
		foreach($changeMenuPaths as $cgpArray)
		{
			if(is_dir(e_PLUGIN.$cgpArray['oldpath']))
			{
				$chgPath[] = $cgpArray['oldpath'];
			}
		}
		//TODO LAN
		
		if(count($chgPath))
		{
			e107::getMessage()->addWarning('Before continuing, please manually delete the following outdated folders from your system: ');
			array_unique($chgPath);
			asort($chgPath);
			foreach($chgPath as $cgp)
			{
				e107::getMessage()->addWarning(e_PLUGIN_ABS."<b>".$cgp."</b>");			
			}	
		}
		
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
			$log->logMessage(LAN_UPDATE_21."comments", E_MESSAGE_ERROR); 	
		}
		else
		{
			if (FALSE ===$sql->db_Update('comments',"comment_author_id=SUBSTRING_INDEX(`comment_author`,'.',1),  comment_author_name=SUBSTRING(`comment_author` FROM POSITION('.' IN `comment_author`)+1)"))
			{
				// Flag error
				$log->logMessage(LAN_UPDATE_21.'comments', E_MESSAGE_ERROR); 	
			}
			else
			{	// Delete superceded field - comment_author
				if (!$sql->db_Select_gen("ALTER TABLE `#comments` DROP COLUMN `comment_author`"))
				{
					// Flag error
					$log->logMessage(LAN_UPDATE_24.'comments - comment_author', E_MESSAGE_ERROR); 	
				}
			}
		}

		$log->logMessage(LAN_UPDATE_21.'comments', E_MESSAGE_DEBUG);
	}



	//	Add index to download history
	// Deprecated by db-verify-class
	// if (FALSE !== ($temp = addIndexToTable('download_requests', 'download_request_datestamp', $just_check, $updateMessages)))
	// {
		// if ($just_check)
		// {
			// return update_needed($temp);
		// }
	// }

	// Extra index to tmp table
	// Deprecated by db-verify-class
	// if (FALSE !== ($temp = addIndexToTable('tmp', 'tmp_time', $just_check, $updateMessages)))
	// {
		// if ($just_check)
		// {
			// return update_needed($temp);
		// }
	// }

	// Extra index to rss table (if used)
	// Deprecated by db-verify-class
	// if (FALSE !== ($temp = addIndexToTable('rss', 'rss_name', $just_check, $updateMessages, TRUE)))
	// {
		// if ($just_check)
		// {
			// return update_needed($temp);
		// }
	// }

	// Front page prefs (logic has changed)
	if (!isset($pref['frontpage_force']))
	{	// Just set basic options; no real method of converting the existing
		if ($just_check) return update_needed('Change front page prefs');
		$pref['frontpage_force'] = array(e_UC_PUBLIC => '');
		$pref['frontpage'] = array(e_UC_PUBLIC => 'news.php');
		// $_pdateMessages[] = LAN_UPDATE_38; //FIXME
		$log->logMessage(LAN_UPDATE_20."frontpage",E_MESSAGE_DEBUG);
		$do_save = TRUE;
	}


/*
 * Deprecated by db-verify-class
 * 
 
	if ($sql->db_Table_exists('newsfeed'))
	{	// Need to extend field newsfeed_url varchar(250) NOT NULL default ''
		if ($sql->db_Query("SHOW FIELDS FROM ".MPREFIX."newsfeed LIKE 'newsfeed_url'"))
		{
			$row = $sql -> db_Fetch();
			if (str_replace('varchar', 'char', strtolower($row['Type'])) != 'char(250)')
			{
				if ($just_check) return update_needed('Update newsfeed field definition');
				$status = $sql->db_Select_gen("ALTER TABLE `".MPREFIX."newsfeed` MODIFY `newsfeed_url` VARCHAR(250) NOT NULL DEFAULT '' ") ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
				//$updateMessages[] = LAN_UPDATE_40;
				$log->logMessage(LAN_UPDATE_21."newsfeed",$status);
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
				//$updateMessages[] = LAN_UPDATE_52;  //FIXME
				$log->logMessage(LAN_UPDATE_52, E_MESSAGE_SUCCESS);
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
				$log->logMessage(LAN_UPDATE_53, E_MESSAGE_SUCCESS);
				
				catch_error($sql);
			}
		}
	}

*/
	// Check need for user timezone before we delete the field
	if (varsettrue($pref['signup_option_timezone']))
	{
		if ($sql->db_Field('user', 'user_timezone', '', TRUE) && !$sql->db_Field('user_extended','user_timezone','',TRUE))
		{
			if ($just_check) return update_needed('Move user timezone info');
			if (!copy_user_timezone())
			{  // Error doing the transfer
				//$updateMessages[] = LAN_UPDATE_42; 
				$log->logMessage(LAN_UPDATE_42, E_MESSAGE_ERROR);
				return FALSE;
			}
			//$updateMessages[] = LAN_UPDATE_41;
			$log->logMessage(LAN_UPDATE_41, E_MESSAGE_DEBUG);
		}
	}


	// Tables defined in core_sql.php to be RENAMED. 
	//---------------------------------
	if ($sql->db_Table_exists('dblog') && !$sql->db_Table_exists('admin_log'))
	{
		if ($just_check) return update_needed('Rename dblog to admin_log');
		$sql->db_Select_gen('ALTER TABLE `'.MPREFIX.'dblog` RENAME `'.MPREFIX.'admin_log`');
		catch_error($sql);
		//$updateMessages[] = LAN_UPDATE_43; 
		$log->logMessage(LAN_UPDATE_43, E_MESSAGE_DEBUG);
	}

	
	// Next bit will be needed only by the brave souls who used an early CVS - probably delete before release
	if ($sql->db_Table_exists('rl_history') && !$sql->db_Table_exists('dblog'))
	{
		if ($just_check) return update_needed('Rename rl_history to dblog');
		$sql->db_Select_gen('ALTER TABLE `'.MPREFIX.'rl_history` RENAME `'.MPREFIX.'dblog`');
		//$updateMessages[] = LAN_UPDATE_44; 
		$log->logMessage(LAN_UPDATE_44, E_MESSAGE_DEBUG);
		catch_error($sql);
	}
	  
	// New tables required (list at top. Definitions in core_sql.php)
	// ALL DEPRECATED by db_verify class.. see below. 
	/*
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
				$log->logMessage(LAN_UPDATE_27.$defs[0][1], $status);
				// catch_error($sql);
			}
			else
			{  // error parsing defs file
				$log->logMessage(LAN_UPDATE_46.$defs[0][1], E_MESSAGE_ERROR);
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
	  	$log->logMessage("Required table structure: <br />".$db_parser->make_field_list($req_fields), E_MESSAGE_DEBUG);			
	  } 

	  if ((($actual_defs = $db_parser->get_current_table($ct)) === FALSE) || !is_array($actual_defs))			// Adds current default prefix
	  {
			$log->logMessage("Couldn't get table structure: ".$ct, E_MESSAGE_DEBUG);		
	  }
	  else
	  {
//		echo $db_parser->make_table_list($actual_defs);
		$actual_fields = $db_parser->parse_field_defs($actual_defs[0][2]);
		if ($debugLevel)
		{
			$log->logMessage("Actual table structure: <br />".$db_parser->make_field_list($actual_fields), E_MESSAGE_DEBUG);		
		} 

		$diffs = $db_parser->compare_field_lists($req_fields,$actual_fields);
		if (count($diffs[0]))
		{  // Changes needed
		  	if ($just_check) return update_needed("Field changes rqd; table: ".$ct);
		
			// Do the changes here
		  	if ($debugLevel)
		  	{
		  		$log->logMessage("List of changes found:<br />".$db_parser->make_changes_list($diffs), E_MESSAGE_DEBUG);		
		  	} 
		  
			$qry = 'ALTER TABLE '.MPREFIX.$ct.' '.implode(', ',$diffs[1]);
		  
			if ($debugLevel)
			{
				$log->logMessage("Update Query used: ".$qry, E_MESSAGE_DEBUG);	
			} 
		  
			$status = $sql->db_Select_gen($qry) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR; 
			$log->logMessage(LAN_UPDATE_21.$ct, $status);
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
				  
				  $log->logMessage($message, E_MESSAGE_DEBUG);
				  	
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
						$log->logMessage($message, E_MESSAGE_DEBUG);
					} 

					$diffs = $db_parser->compare_field_lists($req_fields,$actual_fields);
					if (count($diffs[0]))
					{  // Changes needed
						if (E107_DBG_SQLDETAILS)
						{
							$message = "List of changes found:<br />".$db_parser->make_changes_list($diffs);
							$log->logMessage($message, E_MESSAGE_DEBUG);	
						} 
						if ($just_check) return update_needed("Field changes rqd; plugin table: ".$ct);
						// Do the changes here
						$qry = 'ALTER TABLE '.MPREFIX.$ct.' '.implode(', ',$diffs[1]);
						if (E107_DBG_SQLDETAILS)
						{
							 $message = "Update Query used: ".$qry."<br />";
							 $log->logMessage($message, E_MESSAGE_DEBUG);	
						}
						$sql->db_Select_gen($qry);
						$updateMessages[] = LAN_UPDATE_51.$ct;  
						$log->logMessage(LAN_UPDATE_51.$ct, E_MESSAGE_SUCCESS);
						catch_error($sql);
					}
				}
			}
		}
	}

*/	

	// Obsolete tables (list at top)
	foreach ($obs_tables as $ot)
	{
		if ($sql->db_Table_exists($ot))
		{
			if ($just_check) return update_needed("Delete table: ".$ot);
			$status = $sql->db_Select_gen('DROP TABLE `'.MPREFIX.$ot.'`') ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
			$log->logMessage(LAN_UPDATE_25.$ot, $status);			
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
			$status = $sql->db_Select_gen("ALTER TABLE `".MPREFIX.$t."` MODIFY `{$f}` VARCHAR(45) NOT NULL DEFAULT '';") ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
			$log->logMessage(LAN_UPDATE_26.$t.' - '.$f, $status);				
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



	/* -------------- Upgrade Entire Table Structure - Multi-Language Supported ----------------- */
	
	require_once(e_HANDLER."db_verify_class.php");
	$dbv = new db_verify;
	$dbv->compareAll(); // core & plugins
	
	if(count($dbv->errors))
	{
		if ($just_check)
		{
			$mes = e107::getMessage();
			$mes->addDebug(print_a($dbv->errors,true));
			return update_needed("Database Tables require updating.");
		}
		
	}

	$dbv->compileResults();
	// print_a($dbv->results);
	// print_a($dbv->fixList);	
	$dbv->runFix(); // Fix entire core database structure. 

	//TODO - send notification messages to Log. 
	
	

	
	
	// --- Notify Prefs
	
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
		$status = ($sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'") !== FALSE) ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
		$message = str_replace('--COUNT--',$nt_changed,LAN_UPDATE_20);
		$log->logMessage($message, $status);
	}
	
	
	
	
	
	
		
	// ---------------  Saved emails - copy across
	
	if (!$just_check && $sql->db_Select('generic', '*', "gen_type='massmail'"))
	{
		if ($just_check) return update_needed('Copy across saved emails');
		require_once(e_HANDLER.'mail_manager_class.php');
		$mailHandler = new e107MailManager;
		$i = 0;
		while ($row = $sql->db_Fetch(MYSQL_ASSOC))
		{
			$mailRecord = array(
				'mail_create_date' => $row['gen_datestamp'],
				'mail_creator' => $row['gen_user_id'],
				'mail_title' => $row['gen_ip'],
				'mail_subject' => $row['gen_ip'],
				'mail_body' => $row['gen_chardata'],
				'mail_content_status' => MAIL_STATUS_SAVED
			);
			$mailHandler->mailtoDb($mailRecord, TRUE);
			$mailHandler->saveEmail($mailRecord, TRUE);
			$sql2->db_Delete('generic', 'gen_id='.intval($row['gen_id']));		// Delete as we go in case operation fails part way through
			$i++;
		}
		unset($mailHandler);
		$log->logMessage(str_replace('--COUNT--', $i, LAN_UPDATE_28));
	}
	
	
	

	// -------------------  Populate Plugin Table With Changes ------------------ 
	
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
	

	//-- Media-manger import --------------------------------------------------
	

	
	// Autogenerate filetypes.xml if not found. 
	if(!is_readable(e_SYSTEM."filetypes.xml"))
	{
		$data = '<?xml version="1.0" encoding="utf-8"?>
<e107Filetypes>
	<class name="253" type="zip,gz,jpg,jpeg,png,gif,xml" maxupload="2M" />
</e107Filetypes>';	
					
		file_put_contents(e_SYSTEM."filetypes.xml",$data);
	}
			

	
	$root_media = str_replace(basename(e_MEDIA)."/","",e_MEDIA);
	$user_media_dirs = array("images","avatars","files","temp","videos","icons");
	
	// check for old paths and rename. 
	if(is_dir($root_media."images") || is_dir($root_media."temp"))
	{
		foreach($user_media_dirs as $md)
		{
			@rename($root_media.$md,e_MEDIA.$md);	
		}				
	}
	
	// create sub-directories if they do not exist. 
	if(!is_dir(e_MEDIA."images") || !is_dir(e_MEDIA."temp"))
	{
		foreach($user_media_dirs as $md)
		{
			if(!is_dir(e_MEDIA.$md))
			{
				mkdir(e_MEDIA.$md);		
			}			
		}	
	}
	
	// Move Avatars to new location 
	$av1 = e107::getFile()->get_files(e_FILE.'public/avatars','.jpg|.gif|.png|.GIF|.jpeg|.JPG|.PNG');
	$av2 = e107::getFile()->get_files(e_IMAGE.'avatars','.jpg|.gif|.png|.GIF|.jpeg|.JPG|.PNG');
	
	$avatar_images = array_merge($av1,$av2);
	
	if(count($avatar_images))
	{
		if ($just_check) return update_needed('Avatar paths require updating.');
		foreach($avatar_images as $av)
		{
			@rename($av['path'].$av['fname'],e_MEDIA."avatars/".$av['fname']);			
		}	
	}
	
	// -------------------------------

	
	
	$med = e107::getMedia();
	
	// Media Category Update
	if($sql->db_Field("core_media_cat","media_cat_nick"))
	{
		$count = $sql->db_Select_gen("SELECT * FROM `#core_media_cat` WHERE media_cat_nick = '_common'  ");
		if($count ==1)
		{
			if ($just_check) return update_needed('Media-Manager Categories needs to be updated.');	
			$sql->db_Update('core_media_cat', "media_cat_owner = media_cat_nick, media_cat_category = media_cat_nick WHERE media_cat_nick REGEXP '_common|news|page|_icon_16|_icon_32|_icon_48|_icon_64' ");
			$sql->db_Update('core_media_cat', "media_cat_owner = '_icon', media_cat_category = media_cat_nick WHERE media_cat_nick REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ");
			$sql->db_Update('core_media_cat', "media_cat_owner = 'download', media_cat_category='download_image' WHERE media_cat_nick = 'download' ");
			$sql->db_Update('core_media_cat', "media_cat_owner = 'download', media_cat_category='download_thumb' WHERE media_cat_nick = 'downloadthumb' ");
			$sql->db_Update('core_media_cat', "media_cat_owner = 'news', media_cat_category='news_thumb' WHERE media_cat_nick = 'newsthumb' ");
			e107::getMessage()->addDebug("core-media-cat Categories and Ownership updated");
			if(mysql_query("ALTER TABLE `".MPREFIX."core_media_cat` DROP `media_cat_nick`"))
			{
				e107::getMessage()->addDebug("core-media-cat `media_cat_nick` field removed.");	
			}
			
	//		$query = "INSERT INTO `".MPREFIX."core_media_cat` (`media_cat_id`, `media_cat_owner`, `media_cat_category`, `media_cat_title`, `media_cat_diz`, `media_cat_class`, `media_cat_image`, `media_cat_order`) VALUES
	//		(0, 'gallery', 'gallery_1', 'Gallery 1', 'Visible to the public at /gallery.php', 0, '', 0);
	///		";
	//		
	//		if(mysql_query($query))
	//		{
	//			e107::getMessage()->addDebug("Added core-media-cat Gallery.");	
	//		}
		}
	}
	
	
	// Media Update
	$count = $sql->db_Select_gen("SELECT * FROM `#core_media` WHERE media_category = 'newsthumb' OR media_category = 'downloadthumb'  LIMIT 1 ");
	if($count ==1)
	{
		if ($just_check) return update_needed('Media-Manager Data needs to be updated.');
		$sql->db_Update('core_media', "media_category='download_image' WHERE media_category = 'download' ");
		$sql->db_Update('core_media', "media_category='download_thumb' WHERE media_category = 'downloadthumb' ");
		$sql->db_Update('core_media', "media_category='news_thumb' WHERE media_category = 'newsthumb' ");		
		e107::getMessage()->addDebug("core-media Category names updated");
	}


	// Media Update - core media and core-file. 
	$count = $sql->db_Select_gen("SELECT * FROM `#core_media` WHERE media_category = '_common' LIMIT 1 ");
	if($count ==1)
	{
		if ($just_check) return update_needed('Media-Manager Category Data needs to be updated.');
		$sql->db_Update('core_media', "media_category='_common_image' WHERE media_category = '_common' ");
		e107::getMessage()->addDebug("core-media _common Category updated");
	}
	
	
	
	// Media Update - core media and core-file. CATEGORY
	$count = $sql->db_Select_gen("SELECT * FROM `#core_media_cat` WHERE media_cat_category = '_common' LIMIT 1 ");
	if($count ==1)
	{
		if ($just_check) return update_needed('Media-Manager Category Data needs to be updated.');
		$sql->db_Update('core_media_cat', "media_cat_category='_common_image' WHERE media_cat_category = '_common' ");
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_file', '(Common Area)', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_file', 'Download Files', '', 253, '', 0);");		
		e107::getMessage()->addDebug("core-media-cat _common Category updated");
	}
		

	
	
	$count = $sql->db_Select_gen("SELECT * FROM `#core_media_cat` WHERE `media_cat_owner` = '_common' LIMIT 1 ");

	if($count != 1)
	{
		if ($just_check) return update_needed('Add Media-Manager Categories and Import existing images.');
		
		
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_image', '(Common Images)', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_file', '(Common Files)', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
	
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'news', 'news', 'News', 'Will be available in the news area. ', 253, '', 1);");
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'page', 'page', 'Custom Pages', 'Will be available in the custom pages area of admin. ', 253, '', 0);");
		
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_image', 'Download Images', '', 253, '', 0);");
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_thumb', 'Download Thumbnails', '', 253, '', 0);");
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_file', 'Download Files', '', 253, '', 0);");
				
	//	mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'gallery', 'gallery_1', 'Gallery', 'Visible to the public at /gallery.php', 0, '', 0);");
		
		mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'news', 'news_thumb', 'News Thumbnails (Legacy)', 'Legacy news thumbnails. ', 253, '', 1);");		
		
		$med->import('news_thumb', e_IMAGE.'newspost_images',"^thumb_");
		$med->import('news',e_IMAGE.'newspost_images');
		$med->import('page',e_IMAGE.'custom');
		
	}
	
	// Check for Legacy Download Images. 

	$fl = e107::getFile();
	$dl_images = $fl->get_files(e_FILE.'downloadimages');

	if(count($dl_images) && !$sql->db_Select_gen("SELECT * FROM `#core_media` WHERE `media_category` = 'download_image' "))
	{
		if ($just_check) return update_needed('Import Download Images into Media Manager');
		$med->import('download_image',e_FILE.'downloadimages');
		$med->import('download_thumb',e_FILE.'downloadthumbs');	
	}
	
	$dl_files = $fl->get_files(e_FILE.'downloads', "","standard",5); // don't use e_DOWNLOAD or a loop may occur.
	$public_files = $fl->get_files(e_FILE.'public');
	
	if((count($dl_files) || count($public_files)) && !$sql->db_Select_gen("SELECT * FROM `#core_media` WHERE `media_category` = 'download_file' "))
	{
		if ($just_check) return update_needed('Import Download and Public Files into Media Manager');
	// check for file-types;
		if (is_readable(e_ADMIN.'filetypes.php'))
		{
			$a_types = strtolower(trim(file_get_contents(e_ADMIN.'filetypes.php')));
			$srch = array("png","jpg","jpeg","gif");
			$a_types = str_replace($srch,"",$a_types); // filter-out images. 
			
		} else
		{
			$a_types = 'zip, gz, pdf';
		}
		
		$a_types = explode(',', $a_types);
		foreach ($a_types as $f_type) {
			$allowed_types[] = trim(str_replace('.', '', $f_type));
		}
				
		$fmask = '[a-zA-z0-9_-]+\.('.implode('|',$allowed_types).')$';
		$med->import('download_file',e_DOWNLOAD, $fmask);
		$med->import('_common_file',e_FILE.'public', $fmask);	
	}



			
	$count = $sql->db_Select_gen("SELECT * FROM `#core_media_cat` WHERE media_cat_owner='_icon'  ");
	
	if(!$count)
	{
		if ($just_check) return update_needed('Add icons to media-manager');
			
		$query = "INSERT INTO `".MPREFIX."core_media_cat` (`media_cat_id`, `media_cat_owner`, `media_cat_category`, `media_cat_title`, `media_cat_diz`, `media_cat_class`, `media_cat_image`, `media_cat_order`) VALUES
		(0, '_icon', '_icon_16', 'Icons 16px', 'Available where icons are used in admin. ', 253, '', 0),
		(0, '_icon', '_icon_32', 'Icons 32px', 'Available where icons are used in admin. ', 253, '', 0),
		(0, '_icon', '_icon_48', 'Icons 48px', 'Available where icons are used in admin. ', 253, '', 0),
		(0, '_icon', '_icon_64', 'Icons 64px', 'Available where icons are used in admin. ', 253, '', 0);
		";
		
		if(!mysql_query($query))
		{
			// echo "mysyql error";
		 	// error or already exists.	
		}
		
		$med->importIcons(e_PLUGIN);
		$med->importIcons(e_IMAGE."icons/");
		$med->importIcons(e_THEME.$pref['sitetheme']."/images/");
		e107::getMessage()->addDebug("Icon category added");
	}
	

	// Any other images should be imported manually via Media Manager batch-import.

	// ------------------------------------------------------------------
	

	if ($do_save)
	{
		save_prefs();
		$log->logMessage(LAN_UPDATE_50);
		$log->logMessage(implode(', ', $accum), E_MESSAGE_NODISPLAY);
		//$updateMessages[] = LAN_UPDATE_50.implode(', ',$accum); 	// Note for admin log
	}
	
	//FIXME grab message-stack from $log for the log. 

	if ($just_check) return TRUE;
	$log->flushMessages('UPDATE_01');		// Write admin log entry, update message handler
	//$admin_log->log_event('UPDATE_01',LAN_UPDATE_14.$e107info['e107_version'].'[!br!]'.implode('[!br!]',$updateMessages),E_LOG_INFORMATIVE,'');	// Log result of actual update
	return $just_check;
}

/* No Longed Used I think 
function core_media_import($cat,$epath)
{
	if(!vartrue($cat)){ return;}
	
	if(!is_readable($epath))
	{
		return;
	}
	
	$fl = e107::getFile();
	$tp = e107::getParser();
	$sql = e107::getDb();
	$mes = e107::getMessage();
	
	$fl->setFileInfo('all');
	$img_array = $fl->get_files($epath,'','',2);
	
	if(!count($img_array)){ return;}
		
	foreach($img_array as $f)
	{
		$fullpath = $tp->createConstants($f['path'].$f['fname'],1);
		
		$insert = array(
		'media_caption'		=> $f['fname'], 
		'media_description'	=> '', 
		'media_category'	=> $cat, 
		'media_datestamp'	=> $f['modified'], 
		'media_url'	=> $fullpath, 
		'media_userclass'	=> 0, 
		'media_name'	=> $f['fname'], 
		'media_author'	=> USERID, 
		'media_size'	=> $f['fsize'], 
		'media_dimensions'	=> $f['img-width']." x ".$f['img-height'], 
		'media_usedby'	=> '', 
		'media_tags'	=> '', 
		'media_type'	=> $f['mime']
		);

		if(!$sql->db_Select('core_media','media_url',"media_url = '".$fullpath."' LIMIT 1"))
		{
			if($sql->db_Insert("core_media",$insert))
			{
				$mes->add("Importing Media: ".$f['fname'], E_MESSAGE_SUCCESS); 	
			}
		}
	}	
}
*/

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
		$mesg = LAN_UPDATE_12." : <a href='".e_ADMIN."db.php?plugin'>".ADLAN_145."</a>.";
        //$ns -> tablerender(LAN_ERROR,$mes);
        $emessage->add($mesg, E_MESSAGE_ERROR);
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



/**
 *	Carries out the copy of timezone data from the user record to an extended user field
 *	@return boolean TRUE on success, FALSE on failure
 */
function copy_user_timezone()
{
	$sql = e107::getDb();
	$sql2 = e107::getDb('sql2');
	$tp = e107::getParser();

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




/**
 *	Add index to a table. Returns FALSE if not required. Returns a message if required and just checking
 *
 *	@todo - get rid of $updateMessages parameter once log/message display method finalised, call the relevant method
 */
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
	if (vartrue($target->getLastErrorText()) && E107_DEBUG_LEVEL != 0)
	{
		$tmp2 = debug_backtrace();
		$tmp = $target->getLastErrorText();
		echo $tmp." [ ".basename(__FILE__)." on line ".$tmp2[0]['line']."] <br />";
	}
	return;
}


function get_default_prefs()
{
	$xmlArray = e107::getSingleton('xmlClass')->loadXMLfile(e_CORE."xml/default_install.xml",'advanced');
	$pref = e107::getSingleton('xmlClass')->e107ImportPrefs($xmlArray,'core');
	return $pref;
}

function convert_serialized($serializedData)
{
	$arrayData = unserialize($serializedData);
	return e107::getArrayStorage()->WriteArray($arrayData,FALSE);
}


?>
