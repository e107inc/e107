<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin BootLoader
 *
 * $URL$
 * $Id$
*/

if (!defined('e107_INIT'))
{
	exit;
}


header('Content-type: text/html; charset=utf-8', TRUE);

### Language files
e107::coreLan('header', true);
e107::coreLan('footer', true);

// DEPRECATED - plugins should load their lans manually
// plugin autoload, will be removed in the future! 
// here mostly because of BC reasons
//if(!deftrue('e_MINIMAL'))
{
	$_globalLans = e107::pref('core', 'lan_global_list'); 
	$_plugins = e107::getPref('plug_installed');
	if(is_array($_plugins) && count($_plugins) > 0)
	{
		$_plugins = array_keys($_plugins);
		
		foreach ($_plugins as $_p) 
		{
			if(in_array($_p, $_globalLans) && defset('e_CURRENT_PLUGIN') != $_p) // filter out those with globals unless we are in a plugin folder.
			{
				continue; 	
			}
			e107::loadLanFiles($_p, 'admin');
		}
	}
}





// Get Icon constants, theme override (theme/templates/admin_icons_template.php) is allowed
include_once(e107::coreTemplatePath('admin_icons'));


if(!defset('e_ADMIN_UI') && !defset('e_PAGETITLE'))
{
	$array_functions = e107::getNav()->adminLinks('legacy'); // replacement see e107_handlers/sitelinks.php
	foreach($array_functions as $val)
	{
	    $link = str_replace("../","",$val[0]);
		if(strpos(e_SELF,$link)!==FALSE)
		{
	 //   	define('e_PAGETITLE',$val[1]);
		}
	}
}


if (!defined('ADMIN_WIDTH')) //BC Only 
{
	define('ADMIN_WIDTH', "width:100%;");
}



/**
 * Automate DB system messages DEPRECATED
 * NOTE: default value of $output parameter will be changed to false (no output by default) in the future
 *
 * @param integer|bool $update return result of db::db_Query
 * @param string $type update|insert|update
 * @param string $success forced success message
 * @param string $failed forced error message
 * @param bool $output false suppress any function output
 * @return integer|bool db::db_Query result
 */
 // TODO - This function often needs to be available BEFORE header.php is loaded. 
 
 
 //XXX DEPRECATED It has been copied to message_handler.php as addAuto();
 
function admin_updXXate($update, $type = 'update', $success = false, $failed = false, $output = true)
{
	e107::getMessage()->addDebug("Using deprecated admin_update () which has been replaced by \$mes->addAuto();"); 
	return e107::getMessage()->addAuto($update, $type, $success , $failed , $output);
}


function admin_purge_related($table, $id)
{
	$ns = e107::getRender();
	$tp = e107::getParser();
	$msg = "";
	$tp->parseTemplate("");

	// Delete any related comments
	require_once (e_HANDLER."comment_class.php");
	$_com = new comment;
	$num = $_com->delete_comments($table, $id);
	if ($num)
	{
		$msg .= $num." ".LAN_COMMENTS." ".LAN_DELETED."<br />";
	}

	// Delete any related ratings
	require_once (e_HANDLER."rate_class.php");
	$_rate = new rater;
	$num = $_rate->delete_ratings($table, $id);
	if ($num)
	{
		$msg .= LAN_RATING." ".LAN_DELETED."<br />";
	}

	if ($msg)
	{
		$ns->tablerender(LAN_DELETE, $msg);
	}
}

// legacy vars, will be removed soon
$ns = e107::getRender();
$e107_var = array();

// Left in for BC for now. 

function e_admin_menu($title, $active_page, $e107_vars, $tmpl = array(), $sub_link = false, $sortlist = false)
{
			
	global $E_ADMIN_MENU;
	if (!$tmpl)
		$tmpl = $E_ADMIN_MENU;
	
	
	return e107::getNav()->admin($title, $active_page, $e107_vars, $tmpl, $sub_link , $sortlist );
	
	
	// See e107::getNav()->admin(); 
	
	
	
	
	
	/*
	/// Search for id

	$temp = explode('--id--', $title, 2);
	$title = $temp[0];
	$id = str_replace(array(' ', '_'), '-', varset($temp[1]));

	unset($temp);

	//  SORT

	 

	if ($sortlist == TRUE)
	{
		$temp = $e107_vars;
		unset($e107_vars);
		$func_list = array();
		foreach (array_keys($temp) as $key)
		{
			$func_list[] = $temp[$key]['text'];
		}

		usort($func_list, 'strcoll');

		foreach ($func_list as $func_text)
		{
			foreach (array_keys($temp) as $key)
			{
				if ($temp[$key]['text'] == $func_text)
				{
					$e107_vars[] = $temp[$key];
				}
			}
		}
		unset($temp);
	}



	$kpost = '';
	$text = '';
	
	if ($sub_link)
	{
		$kpost = '_sub';
	}
	else
	{
		 $text = $tmpl['start'];
	}

	//FIXME - e_parse::array2sc()
	$search = array();
	$search[0] = '/\{LINK_TEXT\}(.*?)/si';
	$search[1] = '/\{LINK_URL\}(.*?)/si';
	$search[2] = '/\{ONCLICK\}(.*?)/si';
	$search[3] = '/\{SUB_HEAD\}(.*?)/si';
	$search[4] = '/\{SUB_MENU\}(.*?)/si';
	$search[5] = '/\{ID\}(.*?)/si';
	$search[6] = '/\{SUB_ID\}(.*?)/si';
	$search[7] = '/\{LINK_CLASS\}(.*?)/si';
	$search[8] = '/\{SUB_CLASS\}(.*?)/si';
	$search[9] = '/\{LINK_IMAGE\}(.*?)/si';
	
	foreach (array_keys($e107_vars) as $act)
	{
		if (isset($e107_vars[$act]['perm']) && !getperms($e107_vars[$act]['perm'])) // check perms first.
		{
			continue;
		}
		
		// check class so that e.g. e_UC_NOBODY will result no permissions granted (even for main admin)
		if (isset($e107_vars[$act]['userclass']) && !e107::getUser()->checkClass($e107_vars[$act]['userclass'], false)) // check userclass perms 
		{
			continue;
		}

		//  print_a($e107_vars[$act]);

		$replace = array();
		
		$rid = str_replace(array(' ', '_'), '-', $act).($id ? "-{$id}" : '');
		
		if (($active_page == $act && !is_numeric($act))|| (str_replace("?", "", e_PAGE.e_QUERY) == str_replace("?", "", $act)))
		{
			$temp = $tmpl['button_active'.$kpost];
		}
		else
		{
			$temp = $tmpl['button'.$kpost];
		}

	//	$temp = $tmpl['button'.$kpost];
	//	echo "ap = ".$active_page;
	//	echo " act = ".$act."<br /><br />";
	
		if($rid == 'adminhome')
		{
			$temp = $tmpl['button_other'.$kpost];	
		}

		if($rid == 'home')
		{
			$temp = $tmpl['button_home'.$kpost];	
		}
		
		if($rid == 'language')
		{
			$temp = $tmpl['button_language'.$kpost];	
		}
		
		if($rid == 'logout')
		{
			$temp = $tmpl['button_logout'.$kpost];	
		}
		
		$replace[0] = str_replace(" ", "&nbsp;", $e107_vars[$act]['text']);
		// valid URLs
		$replace[1] = str_replace(array('&amp;', '&'), array('&', '&amp;'), varsettrue($e107_vars[$act]['link'], "#{$act}"));
		$replace[2] = '';
		if (varsettrue($e107_vars[$act]['include']))
		{
			$replace[2] = $e107_vars[$act]['include'];
			//$replace[2] = $js ? " onclick=\"showhideit('".$act."');\"" : " onclick=\"document.location='".$e107_vars[$act]['link']."'; disabled=true;\"";
		}
		$replace[3] = $title;
		$replace[4] = '';
		
		$replace[5] = $id ? " id='eplug-nav-{$rid}'" : '';
		$replace[6] = $rid;
	
		$replace[7] = varset($e107_vars[$act]['link_class']);
		$replace[8] = '';
		$replace[9] = varset($e107_vars[$act]['image']);
		
		if($rid == 'logout' || $rid == 'home' || $rid == 'language')
		{
			$START_SUB = $tmpl['start_other_sub'];
		}
		else 
		{
			$START_SUB = $tmpl['start_sub'];	
		}		

		if (varsettrue($e107_vars[$act]['sub']))
		{
			$replace[6] = $id ? " id='eplug-nav-{$rid}-sub'" : '';
			$replace[7] = ' '.varset($e107_vars[$act]['link_class'], 'e-expandit');
			$replace[8] = ' '.varset($e107_vars[$act]['sub_class'], 'e-hideme e-expandme');
			$replace[4] = preg_replace($search, $replace, $START_SUB);
			$replace[4] .= e_ad/min_menu(false, $active_page, $e107_vars[$act]['sub'], $tmpl, true, (isset($e107_vars[$act]['sort']) ? $e107_vars[$act]['sort'] : $sortlist));
			$replace[4] .= $tmpl['end_sub'];
		}

		$text .= preg_replace($search, $replace, $temp);
	//	echo "<br />".$title." act=".$act;
		//print_a($e107_vars[$act]);
	}

	$text .= (!$sub_link) ? $tmpl['end'] : '';
	
	if ($sub_link || empty($title))
	{
		return $text;
	}

	$ns = e107::getRender();
	$ns->tablerender($title, $text, array('id'=>$id, 'style'=>'button_menu'));
	return '';
	  */
}

/*
 *  DEPRECATED - use e_adm/in_menu()  e107::getNav()->admin
 */
if (!function_exists('show_admin_menu'))
{
	function show_admin_menu($title, $active_page, $e107_vars, $js = FALSE, $sub_link = FALSE, $sortlist = FALSE)
	{
		
		return e107::getNav()->admin($title, $active_page, $e107_vars, false, false, $sortlist);
	}
}

if (!function_exists("parse_admin"))
{
	function parse_admin($ADMINLAYOUT)
	{
		$sc = e107::getScBatch('admin');
		$tp = e107::getParser();
		$adtmp = explode("\n", $ADMINLAYOUT);
		
		for ($a = 0; $a < count($adtmp); $a++)
		{
			if (preg_match("/{.+?}/", $adtmp[$a]))
			{
				echo $tp->parseTemplate($adtmp[$a], true, $sc);
			}
			else
			{
				echo $adtmp[$a];
			}
		}
	}
}
