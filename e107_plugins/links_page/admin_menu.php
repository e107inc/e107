<?php
/*
+---------------------------------------------------------------+
|     Links Page v1.0
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/admin_menu.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-20 13:36:44 $
|     $Author: lisa_ $
+---------------------------------------------------------------+
*/
global $qs, $sql;
if ($qs[0] == "") {
	$act = "cat";
}else{
	$act = $qs[0];
	if(isset($qs[1])){
		if($qs[1] == "create"){
			$act .= ".create";
		}
		if($qs[1] == "edit"){
			$act .= "";
		}
		if($qs[1] == "view"){
			$act .= "";
		}
	}
}

$var['cat']['text'] = LCLAN_ADMINMENU_2;
$var['cat']['link'] = e_SELF;

$var['cat.create']['text'] = LCLAN_ADMINMENU_3;
$var['cat.create']['link'] = e_SELF."?cat.create";

$var['link']['text'] = LCLAN_ADMINMENU_4;
$var['link']['link'] = e_SELF."?link";

$var['link.create']['text'] = LCLAN_ADMINMENU_5;
$var['link.create']['link'] = e_SELF."?link.create";
	
if ($tot = $sql->db_Select("tmp", "*", "tmp_ip='submitted_link' ")) {
	$var['sn']['text'] = LCLAN_ADMINMENU_7." (".$tot.")";
	$var['sn']['link'] = e_SELF."?sn";
}
	
$var['opt']['text'] = LCLAN_ADMINMENU_6;
$var['opt']['link'] = e_SELF."?opt";
	
show_admin_menu(LCLAN_ADMINMENU_1, $act, $var);
	
if ($sql->db_Select("links_page_cat", "*")) {
	while ($row = $sql->db_Fetch()) {
		$cat_var[$row['link_category_id']]['text'] = $row['link_category_name'];
		$cat_var[$row['link_category_id']]['link'] = e_SELF."?link.view.".$row['link_category_id'];
	}
	 
	$active = ($qs[0] == 'link') ? $id : FALSE;
	show_admin_menu(LCLAN_ADMINMENU_8, $active, $cat_var);
}
	
?>