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

if (is_readable(THEME.'templates/online/online_menu_template.php')) 
{
	require(THEME.'templates/online/online_menu_template.php');
} 
elseif (is_readable(THEME.'online_menu_template.php')) 
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
		//	global $listuserson;
			
			$listuserson = e107::getOnline()->userList();
			
			$ret='';
			foreach($listuserson as $uinfo => $row) 
			{
				if($row['user_active'] != 1)
				{
					continue;
				}
				$pinfo = $row['user_location'];
				
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
				
				
				$data = array(
					'oid' 	=> $row['user_id'], 
					'oname' =>$row['user_name'], 
					'page' 	=> $online_location_page, 
					'pinfo' => $pinfo,
					'oimage' => $row['user_image']
				);
				
				setScVar('online_shortcodes', 'currentMember', $data);
				$ret .= $tp->parseTemplate($ONLINE_TEMPLATE['online_members_list_extended'], TRUE);
			}
			setScVar('online_shortcodes', 'onlineMembersList', $ret);
		}
	}

	$text = $tp->parseTemplate($ONLINE_TEMPLATE['enabled'], TRUE);
}
else
{
	if (ADMIN)
	{
		$text = $tp->parseTemplate($ONLINE_TEMPLATE['disabled'], TRUE);
	}
	else
	{
		return;
	}
}

$img = (is_readable(THEME.'images/online_menu.png') ? "<img src='".THEME_ABS."images/online_menu.png' alt='' />" : '');

$caption = $img.' '.vartrue($menu_pref['online_caption'],LAN_ONLINE_10);

if (getperms('1')) 
{
	$path = e_PLUGIN_ABS."online/config.php";
	$caption .= "<a class='pull-right' href='".$path."' title='Configure'><i class='icon-cog'></i></a>";
}


$ns->tablerender($caption, $text, 'online_extended');

?>