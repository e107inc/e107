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
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:53:05 $
|     $Author: streaky $
+---------------------------------------------------------------+
*/
global $action, $sql, $id;
if ($action == "") {
	$action = "cat";
}
	
$var['cat']['text'] = LCLAN_62;
$var['cat']['link'] = e_SELF;
	
$var['create']['text'] = LCLAN_63;
$var['create']['link'] = e_SELF."?create";
	
if ($sql->db_Select("tmp", "*", "tmp_ip='submitted_link' ")) {
	$var['sn']['text'] = LCLAN_66;
	$var['sn']['link'] = e_SELF."?sn";
}
	
$var['opt']['text'] = LCLAN_67;
$var['opt']['link'] = e_SELF."?opt";
	
show_admin_menu(LCLAN_68, $action, $var);
	
if ($sql->db_Select("links_page_cat", "*")) {
	while ($row = $sql->db_Fetch()) {
		$cat_var[$row['link_category_id']]['text'] = $row['link_category_name'];
		$cat_var[$row['link_category_id']]['link'] = e_SELF."?main.view.".$row['link_category_id'];
	}
	 
	$active = ($action == 'main') ? $id :
	 FALSE;
	show_admin_menu(LCLAN_99, $active, $cat_var);
}
	
?>