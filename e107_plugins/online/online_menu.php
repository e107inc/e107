<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/online/online_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

if (!defined('e107_INIT')) { exit; }

//global $pref;
global $menu_pref;

$tp = e107::getParser();

include_lan(e_PLUGIN.'online/languages/'.e_LANGUAGE.'.php');

require_once(e_PLUGIN.'online/online_shortcodes.php');

if (is_readable(THEME.'online_menu_template.php')) 
{
	require(THEME.'online_menu_template.php');
} 
else 
{
	require(e_PLUGIN.'online/online_menu_template.php');
}

//if(!defined('e_TRACKING_DISABLED') && varsettrue($pref['track_online']))
if(!defined('e_TRACKING_DISABLED'))
{
	//display list of 'member viewing page'
	if (e107::getConfig('menu')->get('online_show_memberlist_extended'))
	{
		if (MEMBERS_ONLINE) 
		{
			global $listuserson;
			$ret='';
			foreach($listuserson as $uinfo => $pinfo) 
			{
				$online_location_page = str_replace('.php', '', substr(strrchr($pinfo, '/'), 1));
				if ($pinfo == 'log.php' || $pinfo == 'error.php') 
				{
					$pinfo = 'news.php';
					$online_location_page = 'news';
				}
				elseif ($online_location_page == 'request.php') 
				{
					$pinfo = 'download.php';
					$online_location_page = 'news';
				}
				elseif (strstr($online_location_page, 'forum')) 
				{
					$pinfo = e_PLUGIN.'forum/forum.php';
					$online_location_page = 'forum';
				}
				elseif (strstr($online_location_page, 'content')) 
				{
					$pinfo = 'content.php';
					$online_location_page = 'content';
				}
				elseif (strstr($online_location_page, 'comment')) 
				{
					$pinfo = 'comment.php';
					$online_location_page = 'comment';
				}
				list($oid, $oname) = explode('.', $uinfo, 2);
				setScVar('online_shortcodes', 'currentMember', array('oid' => $oid, 'oname' => $oname, 'page' => $online_location_page, 'pinfo' => $pinfo));
				$ret .= $tp->parseTemplate($TEMPLATE_ONLINE['ONLINE_MEMBERS_LIST_EXTENDED'], TRUE);
			}
			setScVar('online_shortcodes', 'onlineMembersList', $ret);
		}
	}

	$text = $tp->parseTemplate($TEMPLATE_ONLINE['ENABLED'], TRUE);
}
else
{
	if (ADMIN)
	{
		$text = $tp->parseTemplate($TEMPLATE_ONLINE['DISABLED'], TRUE);
	}
	else
	{
		return;
	}
}

$img = (is_readable(THEME.'images/online_menu.png') ? "<img src='".THEME_ABS."images/online_menu.png' alt='' />" : '');
$caption = $img.' '.varsettrue($menu_pref['online_caption'],LAN_ONLINE_10);
$ns->tablerender($caption, $text, 'online');

?>