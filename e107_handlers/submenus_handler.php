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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/submenus_handler.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:52:29 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
	
// Function to create automatically submenu links
// Return message
function create_submenu() {
	global $sub_url, $sub_name, $sub_getcat, $sub_getcatfield, $sub_getcatsql, $sub_suburl;
	$sql = new db;
	$sql2 = new db;
	// Check main link
	$mess = "";
	if (!$sql->db_Select("links", "link_id,link_name,link_url", "link_category='1' AND link_name NOT REGEXP('submenu') AND (link_url='".$sub_url."' OR link_url='".SITEURL.$sub_url."')")) {
		$sql2->db_Insert("links", "0, '".$sub_name."', '".$sub_url."', '', '', '1', '1', '0', '0', '0'");
		$mainlinkname = $sub_name;
		$mess .= LAN_MENGEN_25."<b>".$sub_name."</b><br />";
	} else {
		$row = $sql->db_Fetch();
		$mess .= LAN_MENGEN_26."<b>".$row[1]."</b>".LAN_MENGEN_27."<br />";
		$mainlinkname = $row[1];
	}
	// Get news categories
	$sql->db_Select($sub_getcat, $sub_getcatfield, $sub_getcatsql);
	$cnt_lc = 0;
	while ($row = $sql->db_Fetch()) {
		extract($row);
		$id_news[] = $row[0];
		$label_news[] = $row[1];
		$cnt_lc++;
	}
	$mess .= "<br />";
	// Upgrade existing links
	if ($sql->db_Select("links", "link_id, link_name, link_url", "link_category='1' AND link_name NOT REGEXP('submenu') AND link_url LIKE '%news.php?cat%'")) {
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$sql2->db_Update("links", "link_name='submenu.".$mainlinkname.".".$row[1]."' WHERE link_id='".$row[0]."'");
			$mess .= LAN_MENGEN_26."<b>".$row[1]."</b>".LAN_MENGEN_28.$mainlinkname."<br />";
		}
	}
	 
	$mess .= "<br />";
	// Create new links
	for($i = 0; $i < $cnt_lc; $i++) {
		if ($sql->db_Select("links", "link_id,link_name,link_url", "link_category='1' AND link_name REGEXP('submenu') AND link_url LIKE'%".$sub_suburl.$id_news[$i]."%' ORDER BY link_order")) {
			while ($row = $sql->db_Fetch()) {
				extract($row);
				//$mess = LAN_MENGEN_10;
				$namelink = explode(".", $row[1]);
				$name_link = str_replace("submenu.".$namelink[1].".", "", $row[1]);
				$mess .= "<b>".$name_link."</b>".LAN_MENGEN_29."<br />";
			}
		} else {
			$sql2->db_Insert("links", "0, 'submenu.".$mainlinkname.".".$label_news[$i]."', '".$sub_suburl.$id_news[$i]."', '', '', '1', '".$i."', '0', '0', '0'");
			$mess .= LAN_MENGEN_26."<b>".$label_news[$i]."</b>".LAN_MENGEN_30."<b>".$mainlinkname."</b>.".LAN_MENGEN_31."<br />";
		}
	}
	$mess .= "<br /><br /><hr />";
	return $mess;
}
	
// Function to create specific submenu links
// Return message
function create_customsubmenu() {
	 
}
	
// Function to delete submenu links
// Return message
function delete_submenu() {
	global $sub_suburl, $sub_delall;
	$sql = new db;
	$sql2 = new db;
	$mess = "";
	if ($sub_delall == 1) {
		if ($sql->db_Select("links", "link_id, link_name", "link_category='1' AND link_name REGEXP('submenu')")) {
			while ($row = $sql->db_Fetch()) {
				extract($row);
				$namelink = explode(".", $row[1]);
				$name_link = str_replace("submenu.".$namelink[1].".", "", $row[1]);
				$sql2->db_Delete("links", "link_id='".$row[0]."'");
				$mess .= LAN_MENGEN_26."<b>".$name_link."</b>".LAN_MENGEN_32."<br />";
			}
		} else {
			$mess .= LAN_MENGEN_33;
		}
	} else {
		if ($sql->db_Select("links", "link_id, link_name", "link_category='1' AND link_name REGEXP('submenu') AND link_url LIKE'%".$sub_suburl."%'")) {
			while ($row = $sql->db_Fetch()) {
				extract($row);
				$namelink = explode(".", $row[1]);
				$name_link = str_replace("submenu.".$namelink[1].".", "", $row[1]);
				$sql2->db_Delete("links", "link_id='".$row[0]."'");
				$mess .= LAN_MENGEN_26."<b>".$name_link."</b>".LAN_MENGEN_32."<br />";
			}
		} else {
			$mess .= LAN_MENGEN_33;
		}
	}
	$mess .= "<br /><br /><hr />";
	return $mess;
}
	
	
?>