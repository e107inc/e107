<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
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
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_e107_update.php');
// Modified update routine - combines checking and update code into one block per function
//		- reduces code size typically 30%.
//		- keeping check and update code together should improve clarity/reduce mis-types etc


// @todo: how do we handle update of multi-language tables?

// If following line uncommented, enables a test routine
// define('TEST_UPDATE',TRUE);
$update_debug = TRUE;			// TRUE gives extra messages in places
//$update_debug = TRUE;			// TRUE gives extra messages in places
if (defined('TEST_UPDATE')) $update_debug = TRUE;


//if (!defined('LAN_UPDATE_8')) { define('LAN_UPDATE_8', ''); }
//if (!defined('LAN_UPDATE_9')) { define('LAN_UPDATE_9', ''); }


// Determine which installed plugins have an update file - save the path and the installed version in an array
$dbupdateplugs = array();		// Array of paths to installed plugins which have a checking routine
$dbupdatep = array();			// Array of plugin upgrade actions (similar to $dbupdate)
$dbupdate = array();			// Array of core upgrade actions

global $e107cache;

if(is_readable(e_ADMIN.'ver.php'))
{
  include(e_ADMIN.'ver.php');
}

$mes = e107::getMessage();
/*
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
*/

$dont_check_update = false;

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
	
	// set 'master' to true to prevent other upgrades from running before it is complete.

	$LAN_UPDATE_4 = deftrue('LAN_UPDATE_4',"Update from [x] to [y]"); // in case language-pack hasn't been upgraded.
	$LAN_UPDATE_5 = deftrue('LAN_UPDATE_5', "Core database structure");

//	$dbupdate['218_to_219'] = array('master'=>false, 'title'=> e107::getParser()->lanVars($LAN_UPDATE_4, array('2.1.8','2.1.9')), 'message'=> null, 'hide_when_complete'=>true);

//	$dbupdate['217_to_218'] = array('master'=>false, 'title'=> e107::getParser()->lanVars($LAN_UPDATE_4, array('2.1.7','2.1.8')), 'message'=> null, 'hide_when_complete'=>true);

	$dbupdate['20x_to_220'] = array('master'=>false, 'title'=> e107::getParser()->lanVars($LAN_UPDATE_4, array('2.x','2.2.0')), 'message'=> null, 'hide_when_complete'=>false);
	
	$dbupdate['706_to_800'] = array('master'=>true, 'title'=> e107::getParser()->lanVars($LAN_UPDATE_4, array('1.x','2.0')), 'message'=> LAN_UPDATE_29, 'hide_when_complete'=>true);


	// always run these last.
	$dbupdate['core_database'] = array('master'=>false, 'title'=> $LAN_UPDATE_5);
	$dbupdate['core_prefs'] = array('master'=>true, 'title'=> LAN_UPDATE_13);						// Prefs check



//	$dbupdate['70x_to_706'] = LAN_UPDATE_8.' .70x '.LAN_UPDATE_9.' .706';
}		// End if (!$dont_check_update)



// New in v2.x  ------------------------------------------------

class e107Update
{
	var $core = array();
	var $updates = 0;
	var $disabled = 0;
	
	
	function __construct($core=null)
	{
		$mes = e107::getMessage();
		
		$this->core = $core;

		if(varset($_POST['update_core']) && is_array($_POST['update_core']))
		{
			$func = key($_POST['update_core']);
			$this->updateCore($func);
		}	
		
		if(varset($_POST['update']) && is_array($_POST['update'])) // Do plugin updates
		{ 
			$func = key($_POST['update']);
			$this->updatePlugin($func);
		}	

			//	$dbv =  e107::getSingleton('db_verify', e_HANDLER."db_verify_class.php");

	//	$dbv->clearCache();

		
		$this->renderForm();	
	}
	
	
	
	
	function updateCore($func='')
	{
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$sql = e107::getDb();


	//	foreach($this->core as $func => $data)
	//	{
			if(function_exists('update_'.$func)) // Legacy Method. 
			{
				$installed = call_user_func("update_".$func);
				//?! (LAN_UPDATE == $_POST[$func])
				if(vartrue($_POST['update_core'][$func]) && !$installed)
				{
					if(function_exists("update_".$func))
					{
						// $message = LAN_UPDATE_7." ".$func;
						$message = $tp->lanVars(LAN_UPDATE_7, $this->core[$func]['title']);
						$error = call_user_func("update_".$func, "do");
						
						if($error != '')
						{
							$mes->add($message, E_MESSAGE_ERROR);
							$mes->add($error, E_MESSAGE_ERROR);
						}
						else
						{
							 $mes->add($message, E_MESSAGE_SUCCESS);
						}
					}
				}	
			}
			else 
			{
				$mes->addDebug("could not run 'update_".$func);
			}

		//}
		
	}
	
	
	
	function updatePlugin($path)
	{
		e107::getPlugin()->install_plugin_xml($path, 'upgrade');
		// e107::getPlugin()->save_addon_prefs(); // Rebuild addon prefs.
		e107::getMessage()->reset(E_MESSAGE_INFO); 
		e107::getMessage()->addSuccess(LAN_UPDATED." : ".$path);
		
	}
	
	
	
	function plugins()
	{
		if(!$list = e107::getPlugin()->updateRequired())
		{
			return false;
		}

		$frm = e107::getForm();

		$tp = e107::getParser();

		$text = "";

		uksort($list, "strnatcasecmp");

		foreach($list as $path=>$val)
		{
			$name = !empty($val['@attributes']['lan']) ? $tp->toHtml($val['@attributes']['lan'],false,'TITLE') : $val['@attributes']['name'];

			$text .= "<tr>
					<td>".$name."</td>
					<td>".$frm->admin_button('update['.$path.']', LAN_UPDATE, 'warning', '', 'disabled='.$this->disabled)."</td>
					</tr>";			
		}
		
		return $text;	
	}
	
	
	
	
	function core()
	{
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$sql = e107::getDb();
		
		$text = "";

		
		
		foreach($this->core as $func => $data)
		{
			$text2 = '';

			if(function_exists("update_".$func))
			{

				if(call_user_func("update_".$func))
				{
					if(empty($data['hide_when_complete']))
					{
						$text2 .= "<td>".$data['title']."</td>";
						$text2 .= "<td>".ADMIN_TRUE_ICON."</td>";
					}
				}
				else
				{
					$text2 .= "<td>".$data['title']."</td>";

					if(vartrue($data['message']))
					{
						$mes->addInfo($data['message']);	
					}
					
					$this->updates ++;
					
					$text2 .= "<td>".$frm->admin_button('update_core['.$func.']', LAN_UPDATE, 'warning', '', "id=e-{$func}&disabled=".$this->disabled)."</td>";

					if($data['master'] == true)
					{
						$this->disabled = 1;	
					}
				}

				if(!empty($text2))
				{
					$text .= "<tr>".$text2."</tr>\n";
				}

			}	
		}
		
		return $text;
	}
	
	
	
	function renderForm()
	{
		$ns = e107::getRender();
		$mes = e107::getMessage();
		
		$caption = LAN_UPDATE;
		$text = "
		<form method='post' action='".e_ADMIN."e107_update.php'>
			<fieldset id='core-e107-update'>
			<legend>{$caption}</legend>
				<table class='table adminlist'>
					<colgroup>
						<col style='width: 60%' />
						<col style='width: 40%' />
					</colgroup>
					<thead>
						<tr>
							<th>".LAN_UPDATE_55."</th>
							<th class='last'>".LAN_UPDATE_2."</th>
						</tr>
					</thead>
					<tbody>
		";
	
		$text .= $this->core();
		$text .= $this->plugins();
	
		$text .= "
					</tbody>
				</table>
			</fieldset>
		</form>
			";
	
	
		$ns->tablerender(LAN_UPDATES,$mes->render() . $text);
	
	}
	

}

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
		$dbUpdatesPref = array();

		$skip = e107::getPref('db_updates');

		foreach($dbupdate as $func => $rmks) // See which core functions need update
		{

			if(!empty($skip[$func]) && (!deftrue('e_DEBUG') || E107_DBG_TIMEDETAILS)) // skip version checking when debug is off and check already done.
			{
				continue;
			}

			if(function_exists('update_' . $func))
			{

				$sql->db_Mark_Time('Check Core Update_' . $func . ' ');
				if(!call_user_func('update_' . $func, false))
				{
					$dbUpdatesPref[$func] = 0;
					$update_needed = true;
					break;
				}
				elseif(strpos($func, 'core_') !==0) // skip the pref and table check.
				{
					$dbUpdatesPref[$func] = 1;

				}
			}

		}

		e107::getConfig()->set('db_updates', $dbUpdatesPref)->save(false,true,false);


		// Now check plugins - XXX DEPRECATED 
		foreach($dbupdatep as $func => $rmks)
		{
			if(function_exists('update_' . $func))
			{
				//	$sql->db_Mark_Time('Check Core Update_'.$func.' ');
				if(!call_user_func('update_' . $func, false))
				{
					$update_needed = true;
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

	return $update_needed;
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
	global $e107info; // $pref,  $pref must be kept as global 
	
	$pref = e107::getConfig('core', true, true)->getPref();
	$admin_log = e107::getAdminLog();
	$do_save = FALSE;
	$should = get_default_prefs();

	$just_check = $type == 'do' ? FALSE : TRUE;		// TRUE if we're just seeing if an update is needed
   
	foreach ($should as $k => $v)
	{
		if ($k && !array_key_exists($k,$pref))
		{
			if ($just_check) return update_needed('Missing pref: '.$k);
		//	$pref[$k] = $v;
			e107::getConfig()->set($k,$v);
			$admin_log->logMessage($k.' => '.$v, E_MESSAGE_NODISPLAY, E_MESSAGE_INFO);
			$do_save = TRUE;
		}
	}
	if ($do_save)
	{
		//save_prefs();
		e107::getConfig('core')->save(false,true);
		$admin_log->logMessage(LAN_UPDATE_14.$e107info['e107_version'], E_MESSAGE_NODISPLAY, E_MESSAGE_INFO);
		$admin_log->flushMessages('UPDATE_03',E_LOG_INFORMATIVE);
		//e107::getLog()->add('UPDATE_03',LAN_UPDATE_14.$e107info['e107_version'].'[!br!]'.implode(', ',$accum),E_LOG_INFORMATIVE,'');	// Log result of actual update
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

// generic database structure update.
function update_core_database($type = '')
{
	$just_check = ($type == 'do') ? FALSE : TRUE;
//	require_once(e_HANDLER."db_verify_class.php");
//	$dbv = new db_verify;

	/** @var db_verify $dbv */
	$dbv =  e107::getSingleton('db_verify', e_HANDLER."db_verify_class.php");

	$log = e107::getAdminLog();

	if($plugUpgradeReq = e107::getPlugin()->updateRequired())
	{
		$exclude =  array_keys($plugUpgradeReq); // search xxxxx_setup.php and check for 'upgrade_required()' == true.
		asort($exclude);
	}
	else
	{
		$exclude = false;
	}

	$dbv->compareAll($exclude); // core & plugins, but not plugins calling for an update with xxxxx_setup.php


	if($dbv->errors())
	{
		if ($just_check)
		{
			$mes = e107::getMessage();
		//	$mes->addDebug(print_a($dbv->errors,true));
			$log->addDebug(print_a($dbv->errors,true));
			$tables = implode(", ", array_keys($dbv->errors));
			return update_needed("Database Tables require updating: <b>".$tables."</b>");
		}

		$dbv->compileResults();
		$dbv->runFix(); // Fix entire core database structure and plugins too.


	}


	return $just_check;
}

/*
	function update_218_to_219($type='')
	{
		$sql = e107::getDb();
		$just_check = ($type == 'do') ? false : true;

		// add common video and audio media categories if missing.
		$count = $sql->select("core_media_cat","*","media_cat_category = '_common_video' LIMIT 1 ");

		if(!$count)
		{
			if ($just_check) return update_needed('Media-Manager is missing the video and audio categories and needs to be updated.');

			$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_video', '(Common Videos)', '', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
			$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_audio', '(Common Audio)', '', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
		}



		return $just_check;
	}*/




	/**
	 * @param string $type
	 * @return bool true = no update required, and false if update required.
	 */
/*	 function update_217_to_218($type='')
	{
		$just_check = ($type == 'do') ? false : true;

		$e_user_list = e107::getPref('e_user_list');

			e107::getPlug()->clearCache()->buildAddonPrefLists();
			if(empty($e_user_list['user'])) // check e107_plugins/user/e_user.php is registered.
			{
				if($just_check)
				{
					return update_needed("user/e_user.php need to be registered"); // NO LAN.
				}

			}


		// Make sure, that the pref "post_script" contains one of the allowed userclasses
		// Close possible security hole
		if (!array_key_exists(e107::getPref('post_script'), e107::getUserClass()->uc_required_class_list('nobody,admin,main,classes,no-excludes', true)))
		{
			if ($just_check)
			{
				return update_needed("Pref 'Class which can post < script > and similar tags' contains an invalid value"); // NO LAN.
			}
			else
			{
				e107::getConfig()->setPref('post_script', 255)->save(false, true);
			}
		}


		return $just_check;



	}*/



	/**
	 * @param string $type
	 * @return bool true = no update required, and false if update required.
	 */
	 function update_20x_to_220($type='')
	{

		$sql = e107::getDb();
		$log = e107::getLog();
		$just_check = ($type == 'do') ? false : true;
		$pref = e107::getPref();


		if(!$sql->select('core_media_cat', 'media_cat_id', "media_cat_category = '_icon_svg' LIMIT 1"))
		{
			if($just_check)
			{
				return update_needed("Missing Media-category for SVG");
			}

			$query = "INSERT INTO `#core_media_cat` (media_cat_id, media_cat_owner, media_cat_category, media_cat_title, media_cat_sef, media_cat_diz, media_cat_class, media_cat_image, media_cat_order) VALUES (NULL, '_icon', '_icon_svg', 'Icons SVG', '', 'Available where icons are used in admin.', '253', '', '0');";

			$sql->gen($query);

		}

		if(isset($pref['e_header_list']['social']))
		{
			if($just_check)
			{
				return update_needed("Social Plugin Needs to be refreshed. ");
			}
			
			e107::getPlugin()->refresh('social');
		}


		if(empty($pref['themecss'])) // FIX
		{
			if($just_check)
			{
				return update_needed("Theme CSS pref value is blank.");
			}

			e107::getConfig()->set('themecss','style.css')->save(false,true,false);
		}




		$e_user_list = e107::getPref('e_user_list');

			e107::getPlug()->clearCache()->buildAddonPrefLists();
			if(empty($e_user_list['user'])) // check e107_plugins/user/e_user.php is registered.
			{
				if($just_check)
				{
					return update_needed("user/e_user.php need to be registered"); // NO LAN.
				}

			}


		// Make sure, that the pref "post_script" contains one of the allowed userclasses
		// Close possible security hole
		if (!array_key_exists(e107::getPref('post_script'), e107::getUserClass()->uc_required_class_list('nobody,admin,main,classes,no-excludes', true)))
		{
			if ($just_check)
			{
				return update_needed("Pref 'Class which can post < script > and similar tags' contains an invalid value"); // NO LAN.
			}
			else
			{
				e107::getConfig()->setPref('post_script', 255)->save(false, true);
			}
		}


		// add common video and audio media categories if missing.
		$count = $sql->select("core_media_cat","*","media_cat_category = '_common_video' LIMIT 1 ");

		if(!$count)
		{
			if ($just_check) return update_needed('Media-Manager is missing the video and audio categories and needs to be updated.');

			$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_video', '(Common Videos)', '', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
			$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_audio', '(Common Audio)', '', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
		}



		return $just_check;





	}


//--------------------------------------------
//	Upgrade later versions of 0.7.x to 0.8
//--------------------------------------------
function update_706_to_800($type='')
{

	global $pref, $e107info;
	global $sysprefs, $eArrayStorage;

	//$mes = new messageLog;		// Combined logging and message displaying handler
	//$mes = e107::getMessage();
	$log 	= e107::getAdminLog();		// Used for combined logging and message displaying
	$sql 	= e107::getDb();
	$sql2 	= e107::getDb('sql2');
	$tp 	= e107::getParser();
	$ns 	= e107::getRender();

	e107::getCache()->clearAll('db');
	e107::getCache()->clear_sys('Config');

	e107::getMessage()->setUnique();

	// List of unwanted $pref values which can go
	$obs_prefs = array('frontpage_type','rss_feeds', 'log_lvcount', 'zone', 'upload_allowedfiletype', 'real', 'forum_user_customtitle',
						'utf-compatmode','frontpage_method','standards_mode','image_owner','im_quality', 'signup_option_timezone',
						'modules', 'plug_sc', 'plug_bb', 'plug_status', 'plug_latest', 'subnews_hide_news', 'upload_storagetype',
						'signup_remote_emailcheck'

				);

	// List of DB tables not required (includes a few from 0.6xx)
	$obs_tables = array('flood',  'stat_info', 'stat_counter', 'stat_last', 'session', 'preset', 'tinymce');


	// List of DB tables newly required  (defined in core_sql.php) (The existing dblog table gets renamed)
	// No Longer required. - automatically checked against core_sql.php. 
//	$new_tables = array('audit_log', 'dblog', 'news_rewrite', 'core_media', 'core_media_cat','cron', 'mail_recipients', 'mail_content');

	// List of core prefs that need to be converted from serialized to e107ArrayStorage.
	$serialized_prefs = array("'emote'", "'menu_pref'", "'search_prefs'", "'emote_default'", "'pm_prefs'");




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
		array('oldpath'	=> 'usertheme_menu',	'newpath' => 'user',		'menu' => 'usertheme_menu'),
		array('oldpath'	=> 'userlanguage_menu',	'newpath' => 'user',		'menu' => 'userlanguage_menu'),
		array('oldpath'	=> 'lastseen_menu',		'newpath' => 'online',		'menu' => 'lastseen_menu'),
		array('oldpath'	=> 'other_news_menu',	'newpath' => 'news',		'menu' => 'other_news_menu'),
		array('oldpath'	=> 'other_news_menu',	'newpath' => 'news',		'menu' => 'other_news2_menu'),
		array('oldpath'	=> 'user_menu',			'newpath' => 'user',		'menu' => 'usertheme_menu'),
		array('oldpath'	=> 'user_menu',			'newpath' => 'user',		'menu' => 'userlanguage_menu'),
		array('oldpath'	=> 'poll_menu',			'newpath' => 'poll',		'menu' => 'poll_menu'),
		array('oldpath'	=> 'banner_menu',		'newpath' => 'banner',		'menu' => 'banner_menu'),
		array('oldpath'	=> 'online_menu',		'newpath' => 'online',		'menu' => 'online_menu'),
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

	$just_check = ($type == 'do') ? FALSE : TRUE;		// TRUE if we're just seeing whether an update is needed

//	if (!$just_check)
//	{
	//	foreach(vartrue($setCorePrefs) as $k=>$v)
	//	{
	//		$pref[$k] = $v;
	//	}
//	}

	if (!$just_check)
	{
		$log->logMessage(LAN_UPDATE_14.$e107info['e107_version'], E_MESSAGE_NODISPLAY);
	}







	$statusTexts = array(E_MESSAGE_SUCCESS => 'Success', E_MESSAGE_ERROR => 'Fail', E_MESSAGE_INFO => 'Info');



	if($pref['admintheme'] == 'bootstrap')//TODO Force an admin theme update or not?
	{
		if ($just_check) return update_needed('pref: Admin theme upgrade to bootstrap3 ');

		$pref['admintheme'] = 'bootstrap3';
		$pref['admincss']    = 'admin_dark.css';

		$do_save = true;
	}
	
	// convert all serialized core prefs to e107 ArrayStorage;
	$serialz_qry = "SUBSTRING( e107_value,1,5)!='array' AND e107_value !='' ";
    $serialz_qry .= "AND e107_name IN (".implode(",",$serialized_prefs).") ";
	if(e107::getDb()->select("core", "*", $serialz_qry))
	{
		if($just_check) return update_needed('Convert serialized core prefs');
		while ($row = e107::getDb()->fetch())
		{

			$status = e107::getDb('sql2')->update('core',"e107_value=\"".convert_serialized($row['e107_value'])."\" WHERE e107_name='".$row['e107_name']."'") ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			
			$log->addDebug(LAN_UPDATE_22.$row['e107_name'].": ". $status);
		}	
	}	
	
	
	if(e107::getDb()->select("core", "*", "e107_name='pm_prefs' LIMIT 1"))
	{
		if ($just_check) return update_needed('Rename the pm prefs');	
		e107::getDb()->update("core",  "e107_name='plugin_pm' WHERE e107_name = 'pm_prefs'");
	}
	
	
	//@TODO de-serialize the user_prefs also. 
	
	
	// Banlist
	
	if(!$sql->field('banlist','banlist_id'))
	{
		if ($just_check) return update_needed('Banlist table requires updating.');	
		$sql->gen("ALTER TABLE #banlist DROP PRIMARY KEY");
		$sql->gen("ALTER TABLE `#banlist` ADD `banlist_id` INT( 11 ) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
	}
	
	
	
	


	// Move the maximum online counts from menu prefs to a separate pref - 'history'
	e107::getCache()->clear_sys('Config');
	$menuConfig = e107::getConfig('menu',true,true); 
	
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
			$result = $menuConfig->save(false, true, false); // Only re-save if successul. 	
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
		// $result = $menuConfig->save(false, true, false);	// Save updated menuprefs - without the counts - don't delete them if it fails. 
		//$updateMessages[] = $statusTexts[$status].': '.$resultMessage;		// Admin log message
		$log->logMessage($resultMessage,$status);									// User message
	}

	 

	// ++++++++ Modify Menu Paths +++++++. 
	if(varset($changeMenuPaths))
	{		
		foreach($changeMenuPaths as $val)
		{
			$qry = "SELECT menu_path FROM `#menus` WHERE menu_name = '".$val['menu']."' AND (menu_path='".$val['oldpath']."' || menu_path='".$val['oldpath']."/' ) LIMIT 1";
			if($sql->gen($qry))
			{
				if ($just_check) return update_needed('Menu path changed required:  '.$val['menu'].' ');
				$updqry = "menu_path='".$val['newpath']."/' WHERE menu_name = '".$val['menu']."' AND (menu_path='".$val['oldpath']."' || menu_path='".$val['oldpath']."/' ) ";
				$status = $sql->update('menus', $updqry) ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
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
			$status = $sql->update('menus', "menu_name='online_menu', menu_path='online/' WHERE menu_path='online_extended_menu' || menu_path='online_extended_menu/' ") ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
			$log->logMessage(LAN_UPDATE_23."<b>online_menu</b> : online/", $status); 				
		}
		else
		{	//else if the menu is not active
			//we need to delete the online_extended menu row, and change the online_menu to online
			$sql->delete('menus', " menu_path='online_extended_menu' || menu_path='online_extended_menu/' ");
			$log->logMessage(LAN_UPDATE_31, E_MESSAGE_DEBUG);
		}
		catch_error($sql);
	}

	//change menu_path for online_menu (if it still exists)
	if($sql->db_Select('menus', 'menu_path', "menu_path='online_menu' || menu_path='online_menu/'"))
	{
		if ($just_check) return update_needed('change menu_path for online menu');

		$status = $sql->update('menus', "menu_path='online/' WHERE menu_path='online_menu' || menu_path='online_menu/' ") ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
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
				if(!in_array($cgpArray['oldpath'],$chgPath))
				{
					$chgPath[] = $cgpArray['oldpath'];	
				}
			}
		}

		if(count($chgPath))
		{
			$log->addWarning(LAN_UPDATE_57.' ');
			array_unique($chgPath);
			asort($chgPath);
			foreach($chgPath as $cgp)
			{
				$log->addWarning(e_PLUGIN_ABS."<b>".$cgp."</b>");			
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
			&& (!$sql->gen("ALTER TABLE `#comments`
				ADD COLUMN comment_author_id int(10) unsigned NOT NULL default '0' AFTER `comment_author`,
				ADD COLUMN comment_author_name varchar(100) NOT NULL default '' AFTER `comment_author_id`")))
		{
			// Flag error
			// $commentMessage = LAN_UPDAXXTE_34;
			$log->logMessage(LAN_UPDATE_21."comments", E_MESSAGE_ERROR); 	
		}
		else
		{
			if (FALSE ===$sql->update('comments',"comment_author_id=SUBSTRING_INDEX(`comment_author`,'.',1),  comment_author_name=SUBSTRING(`comment_author` FROM POSITION('.' IN `comment_author`)+1)"))
			{
				// Flag error
				$log->logMessage(LAN_UPDATE_21.'comments', E_MESSAGE_ERROR); 	
			}
			else
			{	// Delete superceded field - comment_author
				if (!$sql->gen("ALTER TABLE `#comments` DROP COLUMN `comment_author`"))
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
	if (!isset($pref['frontpage_force'])) // Just set basic options; no real method of converting the existing
	{	
		if ($just_check) return update_needed('Change front page prefs');
		$pref['frontpage_force'] = array(e_UC_PUBLIC => '');
		
		$fpdef = vartrue($pref['frontpage']['all']) == 'index.php' ? 'index.php' : 'news.php';
		
		$pref['frontpage'] = array(e_UC_PUBLIC => $fpdef);
		// $_pdateMessages[] = LAN_UPDATE_38; //FIXME
		$log->logMessage(LAN_UPDATE_20."frontpage",E_MESSAGE_DEBUG);

		e107::getConfig()->add('frontpage_force', $pref['frontpage_force']);
		e107::getConfig()->add('frontpage', $pref['frontpage']);
		unset($pref['frontpage_force'], $pref['frontpage']);
		$do_save = TRUE;
	}

	// Check need for user timezone before we delete the field
//	if (vartrue($pref['signup_option_timezone']))
	{
		if ($sql->field('user', 'user_timezone')===true && $sql->field('user_extended','user_timezone')===false)
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
	
	
	// Next bit will be needed only by the brave souls who used an early CVS - probably delete before release
	if ($sql->isTable('rl_history') && !$sql->isTable('dblog'))
	{
		if ($just_check) return update_needed('Rename rl_history to dblog');
		$sql->gen('ALTER TABLE `'.MPREFIX.'rl_history` RENAME `'.MPREFIX.'dblog`');
		//$updateMessages[] = LAN_UPDATE_44; 
		$log->logMessage(LAN_UPDATE_44, E_MESSAGE_DEBUG);
		catch_error($sql);
	}	
	
	
	
	//---------------------------------
	if ($sql->isTable('dblog') && !$sql->isTable('admin_log'))
	{
		if ($just_check) return update_needed('Rename dblog to admin_log');
		$sql->gen('ALTER TABLE `'.MPREFIX.'dblog` RENAME `'.MPREFIX.'admin_log`');
		catch_error($sql);
		//$updateMessages[] = LAN_UPDATE_43; 
		$log->logMessage(LAN_UPDATE_43, E_MESSAGE_DEBUG);
	}


	if($sql->isTable('forum_t') && $sql->isEmpty('forum') && $sql->isEmpty('forum_t'))
	{
		if ($just_check) return update_needed('Empty forum tables need to be removed.');
		$obs_tables[] = 'forum_t';
		$obs_tables[] = 'forum';

	}
	  

	// Obsolete tables (list at top)
	$sql->mySQLtableList = false; // clear the cached table list. 
	foreach ($obs_tables as $ot)
	{
		if ($sql->isTable($ot))
		{
			if ($just_check) return update_needed("Delete table: ".$ot);
			
			$status = $sql->gen('DROP TABLE `'.MPREFIX.$ot.'`') ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
			$log->logMessage(LAN_UPDATE_25.$ot, $status);			
		}
	}

	
	// Tables where IP address field needs updating to accommodate IPV6
	// Set to varchar(45) - just in case something uses the IPV4 subnet (see http://en.wikipedia.org/wiki/IPV6#Notation)
	foreach ($ip_upgrade as $t => $f)
	{
	  if ($sql->isTable($t))
	  {		// Check for table - might add some core plugin tables in here
	    if ($field_info = ($sql->db_Field($t, $f, '', TRUE)))
	    {
		  if (strtolower($field_info['Type']) != 'varchar(45)')
		  {
            if ($just_check) return update_needed('Update IP address field '.$f.' in table '.$t);
			$status = $sql->gen("ALTER TABLE `".MPREFIX.$t."` MODIFY `{$f}` VARCHAR(45) NOT NULL DEFAULT '';") ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
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
		e107::getConfig()->remove($p);
		$do_save = true;
		$log->addDebug('Removed obsolete pref: '.$p);
	//	$accum[] = $p;
	  }
	}





	/* -------------- Upgrade Entire Table Structure - Multi-Language Supported ----------------- */
	// ONLY ever add fields, never deletes. 
	
//	require_once(e_HANDLER."db_verify_class.php");
//	$dbv = new db_verify;
	$dbv = e107::getSingleton('db_verify', e_HANDLER."db_verify_class.php");
	
	if($plugUpgradeReq = e107::getPlugin()->updateRequired())
	{
		$exclude =  array_keys($plugUpgradeReq); // search xxxxx_setup.php and check for 'upgrade_required()' == true. 
		asort($exclude);
	}
	else 
	{
		$exclude = false;	
	}
	
	$dbv->compareAll($exclude); // core & plugins, but not plugins calling for an update with xxxxx_setup.php 	
	
	if(count($dbv->errors))
	{
		if ($just_check)
		{
			$mes = e107::getMessage();
		//	$mes->addDebug(print_a($dbv->errors,true));
			$log->addDebug(print_a($dbv->errors,true));
		//	return update_needed("Database Tables require updating."); //
		}
		else
		{
			$dbv->compileResults();
			$dbv->runFix(); // Fix entire core database structure and plugins too.
		}
	}
	
	// print_a($dbv->results);
	// print_a($dbv->fixList);	


	//TODO - send notification messages to Log. 


	if($sql->field('page','page_theme') && $sql->gen("SELECT * FROM `#page` WHERE page_theme != '' AND menu_title = '' LIMIT 1"))
	{
		if ($just_check)
		{
			return update_needed("Pages/Menus Table requires updating.");	
		}
		
		if($sql->update('page',"menu_name = page_theme, menu_title = page_title, menu_text = page_text, menu_template='default', page_title = '', page_text = '' WHERE page_theme !='' AND menu_title = '' AND menu_text IS NULL "))
		{
			$sql->gen("ALTER TABLE `#page` DROP page_theme ");
			$mes = e107::getMessage();
			$log->addDebug("Successfully updated pages/menus table to new format. ");
		}
		else
		{
			$log->addDebug("FAILED to update pages/menus table to new format. ");
			//$sql->gen("ALTER TABLE `#page` DROP page_theme ");
		}
	
	}
	
	if($sql->field('plugin','plugin_releaseUrl'))
	{
		if ($just_check) return update_needed('plugin_releaseUrl is deprecated and needs to be removed. ');
		if($sql->gen("ALTER TABLE `#plugin` DROP `plugin_releaseUrl`"))
		{
			$log->addDebug("Successfully removed plugin_releaseUrl. ");
		}
		
	}
	

	// --- Notify Prefs
	
//	$notify_prefs = $sysprefs -> get('notify_prefs');
//	$notify_prefs = $eArrayStorage -> ReadArray($notify_prefs);
	e107::getCache()->clear_sys('Config');

	$notify_prefs = e107::getConfig('notify',true,true)->getPref();

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
				$notify_prefs['event'][$e]['legacy'] = 1;
				unset($notify_prefs['event'][$e]['type']);
			}
		}
	}
	
	if ($nt_changed)
	{
		$s_prefs = $tp -> toDB($notify_prefs);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		// Could we use $sysprefs->set($s_prefs,'notify_prefs') instead - avoids caching problems  ????
		$status = ($sql -> update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'") !== FALSE) ? E_MESSAGE_DEBUG : E_MESSAGE_ERROR;
		$message = str_replace('[x]',$nt_changed,LAN_UPDATE_20);
		$log->logMessage($message, $status);
	}




	if (isset($pref['forum_user_customtitle']) && !isset($pref['signup_option_customtitle']))
	{
		if ($just_check) return update_needed('pref: forum_user_customtitle needs to be renamed');
		//	$pref['signup_option_customtitle'] = $pref['forum_user_customtitle'];
		e107::getConfig()->add('signup_option_customtitle', $pref['forum_user_customtitle']);
		e107::getConfig()->remove('forum_user_customtitle');

		$log->logMessage(LAN_UPDATE_20.'customtitle', E_MESSAGE_SUCCESS);
		$do_save = TRUE;
	}
	
		
	// ---------------  Saved emails - copy across
	
	if (!$just_check && $sql->select('generic', '*', "gen_type='massmail'"))
	{
		if ($just_check) return update_needed('Copy across saved emails');
		require_once(e_HANDLER.'mail_manager_class.php');
		$mailHandler = new e107MailManager;
		$i = 0;
		while ($row = $sql->fetch())
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
			$sql2->delete('generic', 'gen_id='.intval($row['gen_id']));		// Delete as we go in case operation fails part way through
			$i++;
		}
		unset($mailHandler);
		$log->logMessage(str_replace('[x]', $i, LAN_UPDATE_28));
	}
	
	
	 	

	// -------------------  Populate Plugin Table With Changes ------------------ 
	
	if (!isset($pref['shortcode_legacy_list']))
	{
	  	if ($just_check) return update_needed('Legacy shortcode conversion');
	 	// Reset, legacy and new shortcode list will be generated in plugin update routine
	 // 	$pref['shortcode_legacy_list'] = array();
	// 	$pref['shortcode_list'] = array();

	 	e107::getConfig()->add('shortcode_legacy_list', array());
		e107::getConfig()->set('shortcode_list', array());
		e107::getConfig()->save(false,true,false);

	  	$ep = e107::getPlugin();
		$ep->update_plugins_table($mode); // scan for e_xxx changes and save to plugin table.
		$ep->save_addon_prefs($mode); // generate global e_xxx_list prefs from plugin table.
	}
	
	 	
	
	// This has to be done after the table is upgraded
	if($sql->select('plugin', 'plugin_category', "plugin_category = ''"))
	{
		if ($just_check) return update_needed('Update plugin table');
		require_once(e_HANDLER.'plugin_class.php');
		$ep = new e107plugin;
		$ep->update_plugins_table('update');
	//	$_pdateMessages[] = LAN_UPDATE_XX24; 
	 //	catch_error($sql);
	}
	

	//-- Media-manger import --------------------------------------------------
	
	 
	
	// Autogenerate filetypes.xml if not found. 
	if(!is_readable(e_SYSTEM."filetypes.xml"))
	{
		$data = '<?xml version="1.0" encoding="utf-8"?>
<e107Filetypes>
	<class name="253" type="zip,gz,jpg,jpeg,png,gif,xml,pdf" maxupload="2M" />
</e107Filetypes>';	
					
		file_put_contents(e_SYSTEM."filetypes.xml",$data);
	}
			

	
	$root_media = str_replace(basename(e_MEDIA)."/","",e_MEDIA);
	$user_media_dirs = array("images","avatars", "avatars/default", "avatars/upload", "files","temp","videos","icons");
	
	// check for old paths and rename. 
	if(is_dir($root_media."images") || is_dir($root_media."temp"))
	{
		foreach($user_media_dirs as $md)
		{
			@rename($root_media.$md,e_MEDIA.$md);	
		}				
	}
	
	// create sub-directories if they do not exist. 
	if(!is_dir(e_MEDIA."images") || !is_dir(e_MEDIA."temp") || !is_dir(e_AVATAR_UPLOAD) || !is_dir(e_AVATAR_DEFAULT) )
	{
		foreach($user_media_dirs as $md)
		{
			if(!is_dir(e_MEDIA.$md))
			{
				if(mkdir(e_MEDIA.$md)===false)
				{
					e107::getMessage()->addWarning("Unable to create ".e_MEDIA.$md.".");
				}
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
			$apath = (strstr($av['path'],'public/')) ? e_AVATAR_UPLOAD : e_AVATAR_DEFAULT;
			
			if(rename($av['path'].$av['fname'], $apath. $av['fname'])===false)
			{
				e107::getMessage()->addWarning("Unable to more ".$av['path'].$av['fname']." to ".$apath. $av['fname'].". Please move manually.");
			}
		}	
	}
	
	// -------------------------------

	if (!e107::isInstalled('download') && $sql->gen("SELECT * FROM #links WHERE link_url LIKE 'download.php%' AND link_class != '".e_UC_NOBODY."' LIMIT 1"))
	{
		if ($just_check) return update_needed('Download Plugin needs to be installed.');	
	//	e107::getSingleton('e107plugin')->install('download',array('nolinks'=>true));
		e107::getSingleton('e107plugin')->refresh('download');
	}



	if (!e107::isInstalled('banner') && $sql->isTable('banner'))
	{
		if ($just_check) return update_needed('Banner Table found, but plugin not installed. Needs to be refreshed.');	
		e107::getSingleton('e107plugin')->refresh('banner');
	}
	
	// ---------------------------------
	
		
	$med = e107::getMedia();
	
	// Media Category Update
	if($sql->db_Field("core_media_cat","media_cat_nick"))
	{
		$count = $sql->gen("SELECT * FROM `#core_media_cat` WHERE media_cat_nick = '_common'  ");
		if($count ==1)
		{
			if ($just_check) return update_needed('Media-Manager Categories needs to be updated.');	
			$sql->update('core_media_cat', "media_cat_owner = media_cat_nick, media_cat_category = media_cat_nick WHERE media_cat_nick REGEXP '_common|news|page|_icon_16|_icon_32|_icon_48|_icon_64' ");
			$sql->update('core_media_cat', "media_cat_owner = '_icon', media_cat_category = media_cat_nick WHERE media_cat_nick REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ");
			$sql->update('core_media_cat', "media_cat_owner = 'download', media_cat_category='download_image' WHERE media_cat_nick = 'download' ");
			$sql->update('core_media_cat', "media_cat_owner = 'download', media_cat_category='download_thumb' WHERE media_cat_nick = 'downloadthumb' ");
			$sql->update('core_media_cat', "media_cat_owner = 'news', media_cat_category='news_thumb' WHERE media_cat_nick = 'newsthumb' ");
			$log->addDebug("core-media-cat Categories and Ownership updated");
			if($sql->gen("ALTER TABLE `".MPREFIX."core_media_cat` DROP `media_cat_nick`"))
			{
				$log->addDebug("core-media-cat `media_cat_nick` field removed.");	
			}
			
	//		$query = "INSERT INTO `".MPREFIX."core_media_cat` (`media_cat_id`, `media_cat_owner`, `media_cat_category`, `media_cat_title`, `media_cat_diz`, `media_cat_class`, `media_cat_image`, `media_cat_order`) VALUES
	//		(0, 'gallery', 'gallery_1', 'Gallery 1', 'Visible to the public at /gallery.php', 0, '', 0);
	///		";
	//		
	//		if(mysql_query($query))
	//		{
	//			$log->addDebug("Added core-media-cat Gallery.");	
	//		}
		}
	}
	 	
	
	// Media Update
	$count = $sql->gen("SELECT * FROM `#core_media` WHERE media_category = 'newsthumb' OR media_category = 'downloadthumb'  LIMIT 1 ");
	if($count ==1)
	{
		if ($just_check) return update_needed('Media-Manager Data needs to be updated.');
		$sql->update('core_media', "media_category='download_image' WHERE media_category = 'download' ");
		$sql->update('core_media', "media_category='download_thumb' WHERE media_category = 'downloadthumb' ");
		$sql->update('core_media', "media_category='news_thumb' WHERE media_category = 'newsthumb' ");		
		$log->addDebug("core-media Category names updated");
	}


	// Media Update - core media and core-file.
	/*
	$count = $sql->gen("SELECT * FROM `#core_media` WHERE media_category = '_common' LIMIT 1 ");
	if($count ==1)
	{
		if ($just_check) return update_needed('Media-Manager Category Data needs to be updated.');
		$sql->update('core_media', "media_category='_common_image' WHERE media_category = '_common' ");
		$log->addDebug("core-media _common Category updated");
	}
	*/
	
	
	// Media Update - core media and core-file. CATEGORY
	$count = $sql->gen("SELECT * FROM `#core_media_cat` WHERE media_cat_category = '_common' LIMIT 1 ");
	if($count ==1)
	{
		if ($just_check) return update_needed('Media-Manager Category Data needs to be updated.');
		$sql->update('core_media_cat', "media_cat_category='_common_image' WHERE media_cat_category = '_common' ");
		$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_file', '(Common Area)', 'Media in this category will be available in all areas of admin. ', 253, '', 0);");
		$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_file', 'Download Files', '', 253, '', 0);");		
		$log->addDebug("core-media-cat _common Category updated");
	}
			
	$count = $sql->gen("SELECT * FROM `#core_media_cat` WHERE `media_cat_owner` = '_common' LIMIT 1 ");

	if($count != 1)
	{
		if ($just_check) return update_needed('Add Media-Manager Categories and Import existing images.');
		
		$e107_core_media_cat = array(
		  	array('media_cat_id'=>0,'media_cat_owner'=>'_common','media_cat_category'=>'_common_image','media_cat_title'=>'(Common Images)','media_cat_sef'=>'','media_cat_diz'=>'Media in this category will be available in all areas of admin.','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'0'),
		  	array('media_cat_id'=>0,'media_cat_owner'=>'_common','media_cat_category'=>'_common_file','media_cat_title'=>'(Common Files)','media_cat_sef'=>'','media_cat_diz'=>'Media in this category will be available in all areas of admin.','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'0'),
		 	array('media_cat_id'=>0,'media_cat_owner'=>'news','media_cat_category'=>'news','media_cat_title'=>'News','media_cat_sef'=>'','media_cat_diz'=>'Will be available in the news area.','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'1'),
		 	array('media_cat_id'=>0,'media_cat_owner'=>'page','media_cat_category'=>'page','media_cat_title'=>'Custom Pages','media_cat_sef'=>'','media_cat_diz'=>'Will be available in the custom pages area of admin.','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'0'),
		  	array('media_cat_id'=>0,'media_cat_owner'=>'download','media_cat_category'=>'download_image','media_cat_title'=>'Download Images','media_cat_sef'=>'','media_cat_diz'=>'','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'0'),
		  	array('media_cat_id'=>0,'media_cat_owner'=>'download','media_cat_category'=>'download_thumb','media_cat_title'=>'Download Thumbnails','media_cat_sef'=>'','media_cat_diz'=>'','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'0'),
		  	array('media_cat_id'=>0,'media_cat_owner'=>'download','media_cat_category'=>'download_file','media_cat_title'=>'Download Files','media_cat_sef'=>'','media_cat_diz'=>'','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'0'),
		  	array('media_cat_id'=>0,'media_cat_owner'=>'news','media_cat_category'=>'news_thumb','media_cat_title'=>'News Thumbnails (Legacy)','media_cat_sef'=>'','media_cat_diz'=>'Legacy news thumbnails.','media_cat_class'=>'253','media_cat_image'=>'','media_cat_order'=>'1'),
		);
		
		
		foreach($e107_core_media_cat as $insert)
		{
			$sql->insert('core_media_cat', $insert);	
		}
		
		
		
		
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_image', '(Common Images)', '', 'Media in this category will be available in all areas of admin. ', 253, '', 1);");
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, '_common', '_common_file', '(Common Files)', '', 'Media in this category will be available in all areas of admin. ', 253, '', 2);");
	
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'news', 'news', 'News', '', 'Will be available in the news area. ', 253, '', 3);");
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'page', 'page', 'Custom Pages', '', 'Will be available in the custom pages area of admin. ', 253, '', 4);");
		
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_image','', 'Download Images', '', 253, '', 5);");
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_thumb', '', 'Download Thumbnails', '', 253, '', 6);");
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'download', 'download_file', '', 'Download Files', '', 253, '', 7);");
				
	//	mysql_query("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'gallery', 'gallery_1', 'Gallery', 'Visible to the public at /gallery.php', 0, '', 0);");
		
	//	$sql->gen("INSERT INTO `".MPREFIX."core_media_cat` VALUES(0, 'news', 'news_thumb', 'News Thumbnails (Legacy)', '', 'Legacy news thumbnails. ', 253, '', 8);");		
		
		$med->import('news_thumb', e_IMAGE.'newspost_images',"^thumb_");
		$med->import('news',e_IMAGE.'newspost_images');
		$med->import('page',e_IMAGE.'custom');
		
	}
	else 
	{
//		$log->addDebug("Media COUNT was ".$count. " LINE: ".__LINE__);
	}
	
	// Check for Legacy Download Images. 

	$fl = e107::getFile();
	$dl_images = $fl->get_files(e_FILE.'downloadimages');

	if(count($dl_images) && !$sql->gen("SELECT * FROM `#core_media` WHERE `media_category` = 'download_image' "))
	{
		if ($just_check) return update_needed('Import Download Images into Media Manager');
		$med->import('download_image',e_FILE.'downloadimages');
		$med->import('download_thumb',e_FILE.'downloadthumbs');	
	}
	
	$dl_files = $fl->get_files(e_FILE.'downloads', "","standard",5); // don't use e_DOWNLOAD or a loop may occur.

	
	$publicFilter = array('_FT', '^thumbs\.db$','^Thumbs\.db$','.*\._$','^\.htaccess$','^\.cvsignore$','^\.ftpquota$','^index\.html$','^null\.txt$','\.bak$','^.tmp'); // Default file filter (regex format)
//	$publicFilter = array(1);
	$public_files = $fl->get_files(e_FILE.'public','',$publicFilter);
	
	if((count($dl_files) || count($public_files)) && !$sql->gen("SELECT * FROM `#core_media` WHERE `media_category` = 'download_file' OR  `media_category` = '_common_file' "))
	{
		if ($just_check) return update_needed('Import '.count($dl_files).' Download File(s) and '.count($public_files).' Public File(s) into Media Manager');

		if($sql->gen("SELECT download_url FROM `#download` "))
		{
			$allowed_types = array();
			
			while($row = $sql->fetch())
			{
				$ext = strrchr($row['download_url'], "."); 
				$suffix = ltrim($ext,".");

				if(!isset($allowed_types[$suffix]))
				{
					$allowed_types[$suffix] = $suffix;		
				}
				
			}
			
			$allowed_types = array_unique($allowed_types);
		}		
		else
		{
			$allowed_types = array('zip','gz','pdf');	
		}
		
		$fmask = '[a-zA-Z0-9_.-]+\.('.implode('|',$allowed_types).')$';

		$med->import('download_file',e_DOWNLOAD, $fmask);

		// add found Public file-types.
		foreach($public_files as $v)
		{
			$ext = strrchr($v['fname'], ".");
			$suffix = ltrim($ext,".");
			if(!isset($allowed_types[$suffix]))
			{
				$allowed_types[$suffix] = $suffix;
			}
		}

		$publicFmask = '[a-zA-Z0-9_.-]+\.('.implode('|',$allowed_types).')$';
		$med->import('_common_file', e_FILE.'public', $publicFmask);
	}


	 
			
	$count = $sql->gen("SELECT * FROM `#core_media_cat` WHERE media_cat_owner='_icon'  ");
	
	if(!$count)
	{
		if ($just_check) return update_needed('Add icons to media-manager');
			
		$query = "INSERT INTO `".MPREFIX."core_media_cat` (`media_cat_id`, `media_cat_owner`, `media_cat_category`, `media_cat_title`, `media_cat_diz`, `media_cat_class`, `media_cat_image`, `media_cat_order`) VALUES
		(0, '_icon', '_icon_16', 'Icons 16px', 'Available where icons are used in admin. ', 253, '', 0),
		(0, '_icon', '_icon_32', 'Icons 32px', 'Available where icons are used in admin. ', 253, '', 0),
		(0, '_icon', '_icon_48', 'Icons 48px', 'Available where icons are used in admin. ', 253, '', 0),
		(0, '_icon', '_icon_64', 'Icons 64px', 'Available where icons are used in admin. ', 253, '', 0);
		";
		
		if(!$sql->gen($query))
		{
			// echo "mysyql error";
		 	// error or already exists.	
		}
		
		$med->importIcons(e_PLUGIN);
		$med->importIcons(e_IMAGE."icons/");
		$med->importIcons(e_THEME.$pref['sitetheme']."/images/");
		$log->addDebug("Icon category added");
	}
	
	// Search Clean up ----------------------------------
	
	$searchPref = e107::getConfig('search');

	if($searchPref->getPref('core_handlers/news'))
	{
		if ($just_check) return update_needed('Core search handlers need to be updated.');
		$searchPref->removePref('core_handlers/news')->save(false,true,false);
	}

	if($searchPref->getPref('core_handlers/downloads'))
	{
		if ($just_check) return update_needed('Core search handlers need to be updated.');
		$searchPref->removePref('core_handlers/downloads')->save(false,true,false);
	}

	if($searchPref->getPref('core_handlers/pages'))
	{
		if ($just_check) return update_needed('Core search handlers need to be updated.');
		$searchPref->removePref('core_handlers/pages')->save(false,true,false);
		e107::getSingleton('e107plugin')->refresh('page');
	}
	
	// Clean up news keywords. - remove spaces between commas.
	if($sql->select('news', 'news_id', "news_meta_keywords LIKE '%, %' LIMIT 1"))
	{
		if ($just_check) return update_needed('News keywords contain spaces between commas and needs to be updated. ');
		$sql->update('news', "news_meta_keywords = REPLACE(news_meta_keywords, ', ', ',')");
	}
	
	
	
	
	// Any other images should be imported manually via Media Manager batch-import.

	// ------------------------------------------------------------------

	// Check that custompages have been imported from current theme.php file

	
	
	if (!$just_check)  // Running the Upgrade Process. 
	{
			
		if(!is_array($pref['sitetheme_layouts']) || !vartrue($pref['sitetheme_deflayout']))
		{
			$th = e107::getSingleton('themeHandler');
			$tmp = $th->getThemeInfo($pref['sitetheme']);
			if($th->setTheme($pref['sitetheme'], false))
			{
				$log->addDebug("Updated SiteTheme prefs");
			}
			else
			{
				$log->addDebug("Couldn't update SiteTheme prefs");	
			}
		}
		
		$log->toFile('upgrade_v1_to_v2'); 
		
		
		if ($do_save)
		{
		//	save_prefs();
			e107::getConfig()->setPref($pref)->save(false,true,false);
		//	$log->logMessage(LAN_UPDATE_50);
		//	$log->logMessage(implode(', ', $accum), E_MESSAGE_NODISPLAY);
			//$updateMessages[] = LAN_UPDATE_50.implode(', ',$accum); 	// Note for admin log
		}

		$log->flushMessages('UPDATE_01');		// Write admin log entry, update message handler
		
	}
	else 
	{
		$log->toFile('upgrade_v1_to_v2_check'); 
		
	}
	

	


	
	
	//FIXME grab message-stack from $log for the log. 

	//if ($just_check) return TRUE;

	
	
	
	
	//e107::getLog()->add('UPDATE_01',LAN_UPDATE_14.$e107info['e107_version'].'[!br!]'.implode('[!br!]',$updateMessages),E_LOG_INFORMATIVE,'');	// Log result of actual update
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
      $sql->gen("ALTER TABLE `".MPREFIX."plugin` ADD `plugin_addons` TEXT NOT NULL ;");
	  catch_error($sql);
	}

	//rename plugin_rss field
	if($sql->db_Field("plugin",5) == "plugin_rss")
	{
	  if ($just_check) return update_needed();
	  $sql->gen("ALTER TABLE `".MPREFIX."plugin` CHANGE `plugin_rss` `plugin_addons` TEXT NOT NULL;");
	  catch_error($sql);
	}


	if($sql->db_Field("dblog",5) == "dblog_query")
	{
      if ($just_check) return update_needed();
	  $sql->gen("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_query` `dblog_title` VARCHAR( 255 ) NOT NULL DEFAULT '';");
	  catch_error($sql);
	  $sql->gen("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_remarks` `dblog_remarks` TEXT NOT NULL;");
	  catch_error($sql);
	}

	if(!$sql->db_Field("plugin","plugin_path","UNIQUE"))
	{
      if ($just_check) return update_needed();
      if(!$sql->gen("ALTER TABLE `".MPREFIX."plugin` ADD UNIQUE (`plugin_path`);"))
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
	  $sql->gen("ALTER TABLE ".MPREFIX."online ADD online_active INT(10) UNSIGNED NOT NULL DEFAULT '0'");
	  catch_error($sql);
	}

	if ($sql -> db_Query("SHOW INDEX FROM ".MPREFIX."tmp"))
	{
	  $row = $sql -> db_Fetch();
	  if (!in_array('tmp_ip', $row))
	  {
		if ($just_check) return update_needed();
		$sql->gen("ALTER TABLE `".MPREFIX."tmp` ADD INDEX `tmp_ip` (`tmp_ip`);");
		$sql->gen("ALTER TABLE `".MPREFIX."upload` ADD INDEX `upload_active` (`upload_active`);");
		$sql->gen("ALTER TABLE `".MPREFIX."generic` ADD INDEX `gen_type` (`gen_type`);");
	  }
	}

	if (!$just_check)
	{
		// update new fields
        require_once(e_HANDLER."plugin_class.php");
		$ep = new e107plugin;
		$ep->update_plugins_table('update');
		$ep->save_addon_prefs('update');
	}

	if (!isset($pref['displayname_maxlength']))
	{
	  if ($just_check) return update_needed();
	  $pref['displayname_maxlength'] = 15;
	  save_prefs();
	}


	// If we get to here, in checking mode no updates are required. In update mode, all done.
	if ($just_check) return TRUE;
	e107::getLog()->add('UPDATE_02',LAN_UPDATE_14.$e107info['e107_version'],E_LOG_INFORMATIVE,'');	// Log result of actual update
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

	// require_once(e_HANDLER.'user_extended_class.php');
	$ue = e107::getUserExt();
	$tmp = $ue->parse_extended_xml('getfile');

	$tmp['timezone']['parms'] = $tp->toDB($tmp['timezone']['parms']);

	if(!$ue->user_extended_add($tmp['timezone']))
	{
		e107::getMessage()->addError("Unable to add user_timezone field to user_extended table.");
		return false;
	}

	if($sql->field('user_extended', 'user_timezone')===false)
	{
		e107::getMessage()->addError("user_timezone field missing from user_extended table.");
		return false;
	}

	e107::getMessage()->addDebug("Line:".__LINE__);
	// Created the field - now copy existing data
	if ($sql->db_Select('user','user_id, user_timezone'))
	{
		while ($row = $sql->db_Fetch())
		{
			$sql2->update('user_extended',"`user_timezone`='{$row['user_timezone']}' WHERE `user_extended_id`={$row['user_id']}");
		}
	}
	return true;		// All done!
}




function update_needed($message='')
{

	if(E107_DEBUG_LEVEL)
	{
		$tmp = debug_backtrace();
		//$ns->tablerender("", "<div style='text-align:center'>Update required in ".basename(__FILE__)." on line ".$tmp[0]['line']."</div>");
		e107::getMessage()->add("Update required in ".basename(__FILE__)." on line ".$tmp[0]['line']." (".$message.")", E_MESSAGE_DEBUG);
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
	if (!$sql->isTable($target))
	{
		if ($optionalTable)
		{
			return !$just_check;		// Nothing to do it table is optional and not there
		}
		$updateMessages[] = str_replace(array('[y]','[x]'),array($target,$indexSpec),LAN_UPDATE_54);
		return !$just_check;		// No point carrying on - return 'nothing to do'
	}
	if ($sql->gen("SHOW INDEX FROM ".MPREFIX.$target))
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
		$sql->gen("ALTER TABLE `".MPREFIX.$target."` ADD INDEX `".$indexSpec."` (`".$indexSpec."`);");
		$updateMessages[] = str_replace(array('[y]','[x]'),array($target,$indexSpec),LAN_UPDATE_37);
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
	e107::getDebug()->log("Retrieving default prefs from xml file");
	$xmlArray = e107::getSingleton('xmlClass')->loadXMLfile(e_CORE."xml/default_install.xml",'advanced');
	$pref = e107::getSingleton('xmlClass')->e107ImportPrefs($xmlArray,'core');
	return $pref;
}

function convert_serialized($serializedData, $type='')
{
	$arrayData = unserialize($serializedData);
	$data = e107::serialize($arrayData,FALSE);
	return $data;
}

function theme_foot()
{
	global $pref;

	if(!empty($_POST['update_core']['706_to_800']))
	{
		$data = array('name'=>SITENAME, 'theme'=>$pref['sitetheme'], 'language'=>e_LANGUAGE, 'url'=>SITEURL, 'type'=>'upgrade');
		$base = base64_encode(http_build_query($data, null, '&'));
		$url = "https://e107.org/e-install/".$base;
		return "<img src='".$url."' style='width:1px; height:1px;border:0' />";
	}

}

?>
