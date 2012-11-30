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
$_plugins = e107::getPref('plug_installed');
if(is_array($_plugins) && count($_plugins) > 0)
{
	$_plugins = array_keys($_plugins);
	foreach ($_plugins as $_p) 
	{
		e107::loadLanFiles($_p, 'admin');
	}
}

// Get Icon constants, theme override (theme/templates/admin_icons_template.php) is allowed
include_once(e107::coreTemplatePath('admin_icons'));

require_once (e_ADMIN.'ad_links.php'); //FIXME - see 'FIXME' in sc_admin_navigation

if (!defined('ADMIN_WIDTH'))
{
	define('ADMIN_WIDTH', "width: 95%");
}

// Wysiwyg JS support on or off.
// your code should run off e_WYSIWYG
if (e107::getPref('wysiwyg', false) ) // posts bbcode by default. 
{
	define("e_WYSIWYG", TRUE);
}
else
{
	define("e_WYSIWYG", FALSE);
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
 
 
 // It has been copied to message_handler.php as autoMessage();
 
function admin_update($update, $type = 'update', $success = false, $failed = false, $output = true)
{
	require_once (e_HANDLER."message_handler.php");
	$emessage = e107::getMessage();

	if (($type == 'update' && $update) || ($type == 'insert' && $update !== false))
	{
		$emessage->add(($success ? $success : ($type == 'update' ? LAN_UPDATED : LAN_CREATED)), E_MESSAGE_SUCCESS);
	}
	elseif ($type == 'delete' && $update)
	{
		$emessage->add(($success ? $success : LAN_DELETED), E_MESSAGE_SUCCESS);
	}
	elseif (!mysql_errno())
	{
		if ($type == 'update')
		{
			$emessage->add(LAN_NO_CHANGE.' '.LAN_TRY_AGAIN, E_MESSAGE_INFO);
		}
		elseif ($type == 'delete')
		{
			$emessage->add(LAN_DELETED_FAILED.' '.LAN_TRY_AGAIN, E_MESSAGE_INFO);
		}
	}
	else
	{
		switch ($type)
		{
			case 'insert':
				$msg = LAN_CREATED_FAILED;
			break;
			case 'delete':
				$msg = LAN_DELETED_FAILED;
			break;
			default:
				$msg = LAN_UPDATED_FAILED;
			break;
		}

		$text = ($failed ? $failed : $msg." - ".LAN_TRY_AGAIN)."<br />".LAN_ERROR." ".mysql_errno().": ".mysql_error();
		$emessage->add($text, E_MESSAGE_ERROR);
	}
	
	$emessage->addInfo("Using deprecated admin_update() which has been replaced by \$mes->autoMessage();"); 

	if ($output) echo $emessage->render();
	return $update;
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
		$msg .= $num." ".ADLAN_114." ".LAN_DELETED."<br />";
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

/**
 * Build admin menus - addmin menus are now supporting unlimitted number of submenus
 * TODO - add this to a handler for use on front-end as well (tree, sitelinks.sc replacement)
 *
 * $e107_vars structure:
 * $e107_vars['action']['text'] -> link title
 * $e107_vars['action']['link'] -> if empty '#action' will be added as href attribute
 * $e107_vars['action']['image'] -> (new) image tag
 * $e107_vars['action']['perm'] -> permissions via getperms()
 * $e107_vars['action']['userclass'] -> user class permissions via check_class()
 * $e107_vars['action']['include'] -> additional <a> tag attributes
 * $e107_vars['action']['sub'] -> (new) array, exactly the same as $e107_vars' first level e.g. $e107_vars['action']['sub']['action2']['link']...
 * $e107_vars['action']['sort'] -> (new) used only if found in 'sub' array - passed as last parameter (recursive call)
 * $e107_vars['action']['link_class'] -> (new) additional link class
 * $e107_vars['action']['sub_class'] -> (new) additional class used only when sublinks are being parsed
 *
 * @param string $title
 * @param string $active_page
 * @param array $e107_vars
 * @param array $tmpl
 * @param array $sub_link
 * @param bool $sortlist
 * @return string parsed admin menu (or empty string if title is empty)
 */
function e_admin_menu($title, $active_page, $e107_vars, $tmpl = array(), $sub_link = false, $sortlist = false)
{
		
	global $E_ADMIN_MENU;
	if (!$tmpl)
		$tmpl = $E_ADMIN_MENU;

	/*
	 * Search for id
	 */
	$temp = explode('--id--', $title, 2);
	$title = $temp[0];
	$id = str_replace(array(' ', '_'), '-', varset($temp[1]));

	unset($temp);

	/*
	 * SORT
	 */
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
			$replace[4] .= e_admin_menu(false, $active_page, $e107_vars[$act]['sub'], $tmpl, true, (isset($e107_vars[$act]['sort']) ? $e107_vars[$act]['sort'] : $sortlist));
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
}

/*
 *  DEPRECATED - use e_admin_menu()
 */
if (!function_exists('show_admin_menu'))
{
	function show_admin_menu($title, $active_page, $e107_vars, $js = FALSE, $sub_link = FALSE, $sortlist = FALSE)
	{
		
		return e_admin_menu($title, $active_page, $e107_vars, false, false, $sortlist);
	}
}

if (!function_exists("parse_admin"))
{
	function parse_admin($ADMINLAYOUT)
	{
		global $tp;
		$adtmp = explode("\n", $ADMINLAYOUT);
		for ($a = 0; $a < count($adtmp); $a++)
		{
			if (preg_match("/{.+?}/", $adtmp[$a]))
			{
				echo $tp->parseTemplate($adtmp[$a]);
			}
			else
			{
				echo $adtmp[$a];
			}
		}
	}
}
