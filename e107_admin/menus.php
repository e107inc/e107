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
|     $Source: /cvs_backup/e107_0.7/e107_admin/menus.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-02-03 16:49:27 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("2")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'menus';
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."file_class.php");
$frm = new form;
	
if (isset($_POST['custom_select'])) {
	header("location:".e_SELF."?".$_POST['custom_select']);
	exit;
}
	
if (e_QUERY == '' || e_QUERY == 'default_layout') {
	$menus_header = $HEADER;
	$menus_footer = $FOOTER;
}
else if (e_QUERY == 'custom_layout') {
	$menus_header = $CUSTOMHEADER ? $CUSTOMHEADER :
	 $HEADER;
	$menus_footer = $CUSTOMFOOTER ? $CUSTOMFOOTER :
	 $FOOTER;
}
else if (e_QUERY == 'newsheader_layout') {
	$menus_header = $NEWSHEADER ? $NEWSHEADER :
	 $HEADER;
	$menus_footer = $FOOTER;
} else {
	$menus_header = $CUSTOMHEADER[e_QUERY] ? $CUSTOMHEADER[e_QUERY] :
	 $HEADER;
	$menus_footer = $CUSTOMFOOTER[e_QUERY] ? $CUSTOMFOOTER[e_QUERY] :
	 $FOOTER;
}
	
$layouts_str = $HEADER.$FOOTER;
if ($NEWSHEADER) {
	$layouts_str .= $NEWSHEADER;
}
	
if ($CUSTOMPAGES) {
	if (is_array($CUSTOMPAGES)) {
		foreach ($CUSTOMPAGES as $custom_extract_key => $custom_extract_value) {
			if ($CUSTOMHEADER[$custom_extract_key]) {
				$layouts_str .= $CUSTOMHEADER[$custom_extract_key];
			}
			if ($CUSTOMFOOTER[$custom_extract_key]) {
				$layouts_str .= $CUSTOMFOOTER[$custom_extract_key];
			}
		}
	} else {
		if ($CUSTOMHEADER) {
			$layouts_str .= $CUSTOMHEADER;
		}
		if ($CUSTOMFOOTER) {
			$layouts_str .= $CUSTOMFOOTER;
		}
	}
}
	
$menu_array = parseheader($layouts_str, 'check');
sort($menu_array, SORT_NUMERIC);
	
$menu_check = 'set';
foreach ($menu_array as $menu_value) {
	if ($menu_value != $menu_check) {
		$menu_areas[] = $menu_value;
	}
	$menu_check = $menu_value;
}
	
foreach ($_POST['menuAct'] as $k => $v) {
	if (trim($v)) {
		$id = $k;
		list($menu_act, $location, $position, $newloc) = explode(".", $_POST['menuAct'][$k]);
	}
}
	
if ($menu_act == 'config') {
	header("location:".SITEURL.$PLUGINS_DIRECTORY.$location."/{$position}.php");
	exit;
}
	
if ($menu_act == "adv") {
	require_once(e_HANDLER."userclass_class.php");
	$sql->db_Select("menus", "*", "menu_id='$id' ");
	$row = $sql->db_Fetch();
	 extract($row);
	$listtype = substr($menu_pages, 0, 1);
	$menu_pages = substr($menu_pages, 2);
	$menu_pages = preg_replace("#\|#", "\n", $menu_pages);
	$text = "<div style='text-align:center'>
		<form  method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='width:100%'>
		<tr>
		<td>
		<input type='hidden' name='menuAct[$menu_id]' value='sv.$menu_id' />";
	$text .= MENLAN_4." ";
	$text .= r_userclass('menu_class', $menu_class, "off", "public,member,guest,admin,classes,nobody");
	$text .= "</td>
		</tr>";
	$text .= "<tr><td><br />";
	$checked = ($listtype == 1) ? " checked='checked' " :
	 "";
	$text .= "<input type='radio' {$checked} name='listtype' value='1' /> ".MENLAN_26."<br />";
	$checked = ($listtype == 2) ? " checked='checked' " :
	 "";
	$text .= "<input type='radio' {$checked} name='listtype' value='2' /> ".MENLAN_27."<br /><br />".MENLAN_28."<br />";
	$text .= "<textarea name='pagelist' cols='60' rows='10' class='tbox'>$menu_pages</textarea>";
	$text .= "
		<tr>
		<td style=\"text-align:center\"><br />
		<input class=\"button\" type=\"submit\" name=\"class_submit\" value=\"".MENLAN_6."\" />
		</td>
		</tr>
		</table>
		</form>
		</div>";
	$caption = MENLAN_7." ".$menu_name;
	$ns->tablerender($caption, $text);
}
	
unset($message);
	
if ($menu_act == "sv") {
	$pagelist = explode("\r\n", $_POST['pagelist']);
	for ($i = 0 ; $i < count($pagelist) ; $i++) {
		$pagelist[$i] = trim($pagelist[$i]);
	}
	$plist = implode("|", $pagelist);
	$pageparms = $_POST['listtype'].'-'.$plist;
	$pageparms = preg_replace("#\|$#", "", $pageparms);
	$pageparms = (trim($_POST['pagelist']) == '') ? '' :
	 $pageparms;
	$sql->db_Update("menus", "menu_class='".$_POST['menu_class']."', menu_pages='{$pageparms}' WHERE menu_id='$id' ");
	$message = "<br />".MENLAN_8."<br />";
}
	
if ($menu_act == "move") {
	$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location='$position' ");
	$sql->db_Update("menus", "menu_location='$newloc', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
}
	
if ($menu_act == 'activate') {
	$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location='$position' ");
	$sql->db_Update("menus", "menu_location='$location', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
}
	
if ($menu_act == "deac") {
	$sql->db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_id='$id' ");
}
	
if ($menu_act == "bot") {
	$menu_count = $sql->db_Count("menus", "(*)", " WHERE menu_location='$location' ");
	$sql->db_Update("menus", "menu_order=".($menu_count+1)." WHERE menu_order='$position' AND menu_location='$location' ");
	$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location='$location' AND menu_order > $position");
}
	
if ($menu_act == "top") {
	$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_location='$location' AND menu_order < $position");
	$sql->db_Update("menus", "menu_order=0 WHERE menu_id='$id' ");
}
	
if ($menu_act == "dec") {
	$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='$location' ");
	$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='$id' AND menu_location='$location' ");
}
	
if ($menu_act == "inc") {
	$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='$location' ");
	$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='$id' AND menu_location='$location' ");
}
	
$efile = new e_file;
$fileList = $efile->get_files(e_PLUGIN,"#_menu\.php$#",'standard',2);
$customList = $efile->get_files(e_PLUGIN.'custom',"#\.php$#",'standard',1);

foreach($customList as $custom)
{
	$custom['path'] = str_replace(e_PLUGIN,"",$custom['path']);
	$custom['fname'] = str_replace(".php","",$custom['fname']);

	if (!$sql->db_Count("menus", "(*)", "WHERE menu_name='{$custom['fname']}'")) {
		$sql->db_Insert("menus", " 0, '{$custom['fname']}', 0, 0, 0, '', 'custom'");
		$message .= "<b>".MENLAN_9." - ".$custom['fname']."</b><br />";
	}
	$menustr .= "&".$custom['fname'];
}

foreach($fileList as $file) {
	list($parent_dir) = explode('/',str_replace(e_PLUGIN,"",$file['path']));
	$file['path'] = str_replace(e_PLUGIN,"",$file['path']);
	$file['fname'] = str_replace(".php","",$file['fname']);
	if (!$sql->db_Count("menus", "(*)", "WHERE menu_name='{$file['fname']}'")) {
		if (file_exists(e_PLUGIN.$parent_dir."/plugin.php")) {
			@include(e_PLUGIN.$parent_dir."/plugin.php");
			if ($sql->db_Select("plugin", "*", "plugin_path='".$eplug_folder."' AND plugin_installflag='1' ")) {
				$sql->db_Insert("menus", " 0, '{$file['fname']}', 0, 0, 0, '' ,'{$file['path']}'");
				$message .= "<b>".MENLAN_10." - ".$file['fname']."</b><br />";
			}
		} else {
			$sql->db_Insert("menus", " 0, '{$file['fname']}', 0, 0, 0, '' ,'{$file['path']}'");
			$message .= "<b>".MENLAN_10." - ".$file['fname']."</b><br />";
		}
	}
	$menustr .= "&".str_replace(".php", "", $file['fname']);
}	

$sql2 = new db;
foreach ($menu_areas as $menu_act) {
	if ($sql->db_Select("menus", "*", "menu_location='$menu_act' ORDER BY menu_order ASC")) {
		$c = 1;
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$sql2->db_Update("menus", "menu_order='$c' WHERE menu_id='$menu_id' ");
			$c++;
		}
	}
}
	
$sql->db_Select("menus");
while (list($menu_id, $menu_name, $menu_location, $menu_order) = $sql->db_Fetch()) {
	if (!eregi($menu_name, $menustr)) {
		$sql2->db_Delete("menus", "menu_name='$menu_name'");
		$message .= "<b>".MENLAN_11." - ".$menu_name."</b><br />";
	}
}
	
foreach ($menu_areas as $menu_act) {
	$menus_sql[] = "menu_location!='".$menu_act."'";
}
	
$menus_query = implode(' && ', $menus_sql);
$sql->db_Update("menus", "menu_location='0', menu_order='0' WHERE ".$menus_query);
	
if ($message != "") {
	echo "<div style='text-align:center'><b>".$message."</b></div><br /><br />";
}
	
if ($CUSTOMPAGES) {
	if ($menu_act != 'adv') {
		$text = "<form  method='post' action='".e_SELF."'><div style='width: 100%'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td class='forumheader3' style='width: 90%'>
			".MENLAN_30."
			</td>
			<td class='forumheader3' style='width: 10%; text-align: center;'>";
		 
		$text .= $frm->form_select_open('custom_select', 'onchange="this.form.submit()"');
		 
		if (e_QUERY == '' || e_QUERY == 'default_layout') {
			$text .= $frm->form_option(MENLAN_31, 'selected', 'default_layout');
		} else {
			$text .= $frm->form_option(MENLAN_31, FALSE, 'default_layout');
		}
		 
		if ($NEWSHEADER) {
			if (e_QUERY == 'newsheader_layout') {
				$text .= $frm->form_option(MENLAN_32, 'selected', 'newsheader_layout');
			} else {
				$text .= $frm->form_option(MENLAN_32, FALSE, 'newsheader_layout');
			}
		}
		 
		if ($CUSTOMPAGES) {
			if (is_array($CUSTOMPAGES)) {
				foreach ($CUSTOMPAGES as $custom_pages_key => $custom_pages_value) {
					if (e_QUERY == $custom_pages_key) {
						$text .= $frm->form_option($custom_pages_key, 'selected', $custom_pages_key);
					} else {
						$text .= $frm->form_option($custom_pages_key, FALSE, $custom_pages_key);
					}
				}
			} else {
				if (e_QUERY == 'custom_layout') {
					$text .= $frm->form_option(MENLAN_33, 'selected', 'custom_layout');
				} else {
					$text .= $frm->form_option(MENLAN_33, FALSE, 'custom_layout');
				}
			}
		}
		 
		$text .= $frm->form_select_close();
		 
		$text .= "</td>
			</tr>
			</table></div>
			</form>";
		 
		$ns->tablerender(MENLAN_29, $text);
	}
}
	
parseheader($menus_header);
	
echo "<div style='text-align:center'>";
echo "<div style='font-size:14px' class='fborder'><div class='forumheader'><b>".MENLAN_22."</b></div></div><br />";
echo $frm->form_open("post", e_SELF."?".e_QUERY, "menuActivation");
echo "<table style='width:96%' class='fborder'>";
	
$sql->db_Select("menus", "*", "menu_location='0' ORDER BY menu_name ");
while (list($menu_id, $menu_name, $menu_location, $menu_order) = $sql->db_Fetch()) {
	$text = "";
	$menu_name = eregi_replace("_menu", "", $menu_name);
	 
	echo "<tr>
		<td class=\"fcaption\" style=\"text-align:center\">
		<b>".$menu_name."</b>
		</td>
		</tr>
		<tr>
		<td class=\"forumheader3\" style=\"text-align:center\">";
	 
	$text .= "<div>
		<select id='menuAct_$menu_id' name='menuAct[$menu_id]' class='tbox' onchange='this.form.submit()' >";
	$text .= $frm->form_option(MENLAN_12." ...", TRUE, " ");
	 
	foreach ($menu_areas as $menu_act) {
		$text .= $frm->form_option(MENLAN_13." ".$menu_act, "", "activate.".$menu_act);
	}
	$text .= $frm->form_select_close()."</div>";
	echo $text;
	echo "</td></tr>
		<tr><td><br /></td></tr>
		";
}
echo "</table>";
echo $frm->form_close();
echo "</div>";
	
parseheader($menus_footer);
	
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parseheader($LAYOUT, $check = FALSE) {
	$tmp = explode("\n", $LAYOUT);
	for ($c = 0; $c < count($tmp); $c++) {
		if (preg_match("/[\{|\}]/", $tmp[$c])) {
			if ($check) {
				if (strstr($tmp[$c], "MENU")) {
					$str[] = preg_replace("/\{MENU=(.*?)\}/si", "\\1", $tmp[$c]);
				}
			} else {
				checklayout($tmp[$c]);
			}
		} else {
			if (!$check) {
				echo $tmp[$c];
			}
		}
	}
	if ($check) {
		return $str;
	}
}
	
function checklayout($str) {
	global $pref, $menu_areas;
	global $frm;
	 
	if (strstr($str, "LOGO")) {
		echo "[Logo]";
	}
	else if(strstr($str, "SITENAME")) {
		echo "[SiteName]";
	}
	else if (strstr($str, "SITETAG")) {
		echo "[SiteTag]";
	}
	else if (strstr($str, "SITELINKS")) {
		echo "[SiteLinks]";
	}
	else if (strstr($str, "MENU")) {
		$ns = new e107table;
		$menu = preg_replace("/\{MENU=(.*?)\}/si", "\\1", $str);
		echo "<div style=\"text-align:center; font-size:14px\" class=\"fborder\"><div class=\"forumheader\"><b>".MENLAN_14."  ".$menu."</b></div></div><br />";
		$text = "&nbsp;";
		$sql9 = new db;
		if ($sql9->db_Count("menus", "(*)", " WHERE menu_location='$menu' ")) {
			unset($text);
			echo $frm->form_open("post", e_SELF."?".e_QUERY, "frm_menu_".intval($menu));
			 
			$sql9->db_Select("menus", "*", "menu_location='$menu' ORDER BY menu_order");
			$menu_count = $sql9->db_Rows();
			while (list($menu_id, $menu_name, $menu_location, $menu_order, $menu_class, $menu_pages, $menu_path) = $sql9->db_Fetch()) {
				$menu_name = eregi_replace("_menu", "", $menu_name);
				$vis = ($menu_class || strlen($menu_pages) > 1) ? " <span style='color:red'>*</span> " :
				 "";
				$caption = "<div style=\"text-align:center\">{$menu_name}{$vis}</div>";
				$menu_info = "{$menu_location}.{$menu_order}";
				 
				$text = "";
				$conf = '';
				$config_path = e_PLUGIN.$menu_path.'/'.$eplug_conffile;
				if (file_exists(e_PLUGIN."{$menu_path}/{$menu_name}.config.php"))
				{
					$conf = "config.{$menu_path}.{$menu_name}";
				}
				if($conf == '' && file_exists(e_PLUGIN."{$menu_path}/config.php"))
				{
					$conf = "config.{$menu_path}.config";
				}

				$text .= "<select id='menuAct_$menu_id' name='menuAct[$menu_id]' class='tbox' onchange='this.form.submit()' >";
				$text .= $frm->form_option(MENLAN_25, TRUE, " ");
				$text .= $frm->form_option(MENLAN_15, "", "deac.{$menu_info}");
				 
				if ($conf) {
					$text .= $frm->form_option(MENLAN_16, "", $conf);
				}
				 
				if ($menu_order != 1) {
					$text .= $frm->form_option(MENLAN_17, "", "inc.{$menu_info}");
					$text .= $frm->form_option(MENLAN_24, "", "top.{$menu_info}");
				}
				if ($menu_count != $menu_order) {
					$text .= $frm->form_option(MENLAN_18, "", "dec.{$menu_info}");
					$text .= $frm->form_option(MENLAN_23, "", "bot.{$menu_info}");
				}
				foreach ($menu_areas as $menu_act) {
					if ($menu != $menu_act) {
						$text .= $frm->form_option(MENLAN_19." ".$menu_act, "", "move.{$menu_info}.".$menu_act);
					}
				}
				$text .= $frm->form_option(MENLAN_20, "", "adv.{$menu_info}");
				$text .= $frm->form_select_close();
				$ns->tablerender($caption, $text);
				echo "<div><br /></div>";
			}
			echo $frm->form_close();
		}
	}
	else if (strstr($str, "SETSTYLE")) {
		$tmp = explode("=", $str);
		$style = preg_replace("/\{SETSTYLE=(.*?)\}/si", "\\1", $str);
	}
	else if (strstr($str, "SITEDISCLAIMER")) {
		echo "[Sitedisclaimer]";
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
require_once("footer.php");
?>