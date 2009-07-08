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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/online/online_menu.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-07-08 07:01:31 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

global $tp, $sc_style, $gen, $menu_pref;

include_lan(e_PLUGIN."online/languages/".e_LANGUAGE.".php");

require_once(e_PLUGIN.'online/online_shortcodes.php');

if (is_readable(THEME.'online_menu_template.php')) {
	require_once(THEME.'online_menu_template.php');
} else {
	require_once(e_PLUGIN.'online/online_menu_template.php');
}

if(!defined("e_TRACKING_DISABLED") && varsettrue($pref['track_online'])){

	if (!is_object($gen)) { $gen = new convert; }

	//update most ever online
	if ((MEMBERS_ONLINE + GUESTS_ONLINE) > ($menu_pref['most_members_online'] + $menu_pref['most_guests_online'])) {
		$menu_pref['most_members_online'] = MEMBERS_ONLINE;
		$menu_pref['most_guests_online'] = GUESTS_ONLINE;
		$menu_pref['most_online_datestamp'] = time();
		$tmp = addslashes(serialize($menu_pref));
		$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	}

	//display list of 'member viewing page'
	if($menu_pref['online_show_memberlist_extended']){
		if (MEMBERS_ONLINE) {
			global $listuserson, $pinfo, $ADMIN_DIRECTORY, $online_location_page, $oid, $oname, $online_location_page;
			$ret='';
			foreach($listuserson as $uinfo => $pinfo) {

				list($oid, $oname) = explode(".", $uinfo, 2);
				$online_location_page = substr(strrchr($pinfo, "/"), 1);
				if ($pinfo == "log.php" || $pinfo == "error.php") {
					$pinfo = "news.php";
					$online_location_page = "news.php";
				}
				if ($online_location_page == "request.php") {
					$pinfo = "download.php";
				}
				if (strstr($online_location_page, "forum")) {
					$pinfo = e_PLUGIN."forum/forum.php";
					$online_location_page = "forum.php";
				}
				if (strstr($online_location_page, "content")) {
					$pinfo = "content.php";
					$online_location_page = "content.php";
				}
				if (strstr($online_location_page, "comment")) {
					$pinfo = "comment.php";
					$online_location_page = "comment.php";
				}
				$ret .= $tp -> parseTemplate($TEMPLATE_ONLINE['ONLINE_MEMBERS_LIST_EXTENDED'], FALSE, $online_shortcodes);
			}
			global $ONLINE_MEMBERS_LIST_EXTENDED;
			$ONLINE_MEMBERS_LIST_EXTENDED = $ret;
		}
	}

	$text = $tp -> parseTemplate($TEMPLATE_ONLINE['ENABLED'], FALSE, $online_shortcodes);
}else{
	if(ADMIN){
		$text = $TEMPLATE_ONLINE['DISABLED'];
	}else{
		return;
	}
}

$img = (is_readable(THEME."images/online_menu.png") ? "<img src='".THEME_ABS."images/online_menu.png' alt='' />" : "");
$caption = $img." ".varsettrue($menu_pref['online_caption'],LAN_ONLINE_10);
$ns->tablerender($caption, $text, 'online');

?>