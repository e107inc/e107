<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/menus2.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("2")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

$tmp = explode(".", e_QUERY);
$action = $tmp[0];
$id = $tmp[1];
$position = $tmp[2];
$location = $tmp[3];


if($action == "adv"){
	$sql -> db_Select("menus", "*", "menu_id='$id' ");
	$row = $sql -> db_Fetch(); extract($row);
	$text = "<div style=\"text-align:center\">
<form  method=\"post\" action=\"".e_SELF."?sv.".$menu_id."\">\n
<table style=\"width:50%\">
<tr>
<td>
<input name=\"menu_class\" type=\"radio\" value=\"0\" ";
	if(!$menu_class){ $text .= "checked"; }
	$text .= ">Visible to all<br />
	<input name=\"menu_class\" type=\"radio\" value=\"253\" ";

	if($menu_class == 253){ $text .= "checked"; }
	$text .= ">Visible to members only<br />
	<input name=\"menu_class\" type=\"radio\" value=\"254\" ";
	if($menu_class == 254){ $text .= "checked"; }
	$text .= ">Visible to administrators only<br />";


	$sql -> db_Select("userclass_classes");
	while($row = $sql -> db_Fetch()){ 
		extract($row);
		$text .= "<input name=\"menu_class\" type=\"radio\" value=\"".$userclass_id."\"";
		if($menu_class == $userclass_id){ $text .= "checked"; }
		$text .= ">Only visible to users in ".$userclass_name." class<br />";
	}

	$text .= "</td>
</tr>
<tr>
<td style=\"text-align:center\"><br />
<input class=\"button\" type=\"submit\" name=\"class_submit\" value=\"Update Menu Class\" />
</td>
</tr>
</table>
</form>
</div>";
	$caption = "Set class for ".$menu_name;
	$ns -> tablerender($caption, $text);
}

if($action == "sv"){
	$sql -> db_Update("menus", "menu_class='".$_POST['menu_class']."' WHERE menu_id='$id' ");
	$message = "<br />Class updated<br />";
}

if($action == "move"){
	$menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
	$sql -> db_Update("menus", "menu_location='$position', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
	header("location: ".e_SELF);
}

if($action == "activate"){
	$menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
	$sql -> db_Update("menus", "menu_location='$position', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
	header("location: ".e_SELF);
}

if($action == "deac"){
	$sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_id='$id' ");
	header("location: ".e_SELF);
}

if($action == "dec"){
	$sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='$location' ");
	$sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='$id' AND menu_location='$location' ");
	header("location: ".e_SELF);
}

if($action == "inc"){
	echo "INC";
	$sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='$location' ");
	$sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='$id' AND menu_location='$location' ");
	header("location: ".e_SELF);
}

$sql2 = new db;
for($a=1; $a<=20; $a++){
	if($sql -> db_Select("menus", "*",  "menu_location='$a' ORDER BY menu_order ASC")){
		$c=1;
		while($row = $sql -> db_Fetch()){
			extract($row);
			$sql2 -> db_Update("menus", "menu_order='$c' WHERE menu_id='$menu_id' ");
			$c++;
		}
	}
}

$handle=opendir(e_BASE."menus/");
	$c=0;
	while ($file = readdir($handle)){	
		if($file != "." && $file != ".." && $file != "plugins" && $file != "index.html" && $file !=	"log_menu.php"){
			$tmp = eregi_replace(".php", "", $file);
			if(!$sql -> db_Select("menus", "*", "menu_name='$tmp'")){
				$sql -> db_Insert("menus", " 0, '$tmp', 0, 0, 0 ");
				$message = "<b>New menu installed - ".$tmp."</b><br />";
			}
			$menustr .= "&".eregi_replace(".php", "", $file);
			$c++;
		}
	}
closedir($handle);

$sql -> db_Select("menus");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
	if(!eregi($menu_name, $menustr)){
		$sql -> db_Delete("menus", "menu_name='$menu_name'");
		$message = "<b>Menu removed - ".$menu_name."</b><br />";
	}
}

$menus_used = (substr_count($HEADER, "MENU")+ substr_count($FOOTER, "MENU"));
$sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_location>'$menus_used' ");

if($message != ""){
	echo "<div style=\"text-align:center\"><b>".$message."</b></div>";
}

//	------------------------

$tmp1 = str_replace("<table", "<table border=\"1\" ", $HEADER);
$tmp2 = str_replace("<table ", "<table border=\"1\" ", $FOOTER);

parseheader($tmp1, $menus_used);

echo "<div style=\"text-align:center\">
<div style=\"font-size:14px\" class=\"fborder\"><div class=\"forumheader\"><b>Inactive Menus</b></div></div><br />
<table style=\"width:96%\" class=\"fborder\">";

$sql -> db_Select("menus", "*", "menu_location='0' ");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
	$menu_name = eregi_replace("_menu", "", $menu_name);

	echo "<tr>
<td class=\"fcaption\" style=\"text-align:center\">
<b>".$menu_name."</b>
</td>
</tr>
<td class=\"forumheader3\" style=\"text-align:center\">";
	echo "<select name=\"activate\" onChange=\"urljump(this.options[selectedIndex].value)\" class=\"tbox\">
	<option selected  value=\"0\">Activate this menu - please choose location ...</option>";
	for($a=1; $a<=$menus_used; $a++){
		echo "<option value=\"menus2.php?activate.$menu_id.$a\">Activate in Area $a</option>";
	}
	echo "</select>";
	if($menu <> $c){

}

echo "</td></tr>
<tr><td><br /></td></tr>
	";
}
echo "</table></div>";


parseheader($tmp2, $menus_used);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parseheader($LAYOUT){
	$tmp = explode("\n", $LAYOUT);
	for($c=0; $c < count($tmp); $c++){ 
		if(preg_match("/[\{|\}]/", $tmp[$c])){
			$str = checklayout($tmp[$c]);
		}else{
			echo $tmp[$c];
		}
	}
}
function checklayout($str){
	global $pref, $menus_used;
	if(strstr($str, "LOGO")){
		echo "[Logo]";
	}else if(strstr($str, "SITENAME")){
		echo "[SiteName]";
	}else if(strstr($str, "SITETAG")){
		echo "[SiteTag]";
	}else if(strstr($str, "SITELINKS")){
		echo "[SiteLinks]";
	}else if(strstr($str, "MENU")){
		$ns = new table;
		$menu = preg_replace("/\{MENU=(.*?)\}/si", "\\1", $str);
		echo "<div style=\"text-align:center; font-size:14px\" class=\"fborder\"><div class=\"forumheader\"><b>Area  ".$menu."</b></div></div><br />";
		unset($text);

		$sql9 = new db;
		$sql9 -> db_Select("menus", "*",  "menu_location='$menu' ORDER BY menu_order");
		$menu_count = $sql9 -> db_Rows();
		while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
			$menu_name = eregi_replace("_menu", "", $menu_name);
			$caption = "<div style=\"text-align:center\">".$menu_name."</div>";
			$text = "<a href=\"".e_SELF."?deac.".$menu_id."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/off.png\" alt=\"\" /> Deactivate</div></a>";
			if($menu_order != 1){
				$text .= "<a href=\"".e_SELF."?inc.".$menu_id.".".$menu_order.".".$menu."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/up.gif\" alt=\"\" /> Move Up</div></a>";
			}
			if($menu_count != $menu_order){
				$text .= "<a href=\"".e_SELF."?dec.".$menu_id.".".$menu_order.".".$menu."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/down.gif\" alt=\"\" /> Move Down</div></a>";
			}
			for($c=1; $c<=5; $c++){
				if($menu <> $c){
					$text .= "<a href=\"".e_SELF."?move.".$menu_id.".$c\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/move.png\" alt=\"\" /> Move to Area ".$c."</div></a>";
					
				}
			}

			$text .= "<a href=\"".e_SELF."?adv.".$menu_id."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/move.png\" alt=\"\" /> Visibility</div></a>";


			$ns -> tablerender($caption, $text);
			echo "<br />";
		}
		

	}else if(strstr($str, "SETSTYLE")){
		$tmp = explode("=", $str);
		$style = preg_replace("/\{SETSTYLE=(.*?)\}/si", "\\1", $str);
	}else if(strstr($str, "SITEDISCLAIMER")){
		echo "[Sitedisclaimer]";
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
require_once("footer.php");
?>