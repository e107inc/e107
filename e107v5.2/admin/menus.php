<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/menusort.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// if changing from a theme with farright to theme without, anything in farright column is not shown ...


require_once("../class2.php");

$tmp = explode(".", $_SERVER['QUERY_STRING']);
$action = $tmp[0];
$id = $tmp[1];
$position = $tmp[2];
$location = $tmp[3];

if($action == "deac"){
	$sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_id='$id' ");
	header("location:".$_SERVER['PHP_SELF']);
}

if($action == "act"){
	$menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
	$sql -> db_Update("menus", "menu_location='$position', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
	header("location:".$_SERVER['PHP_SELF']);
}

if($action == "dec"){
	$sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='$location' ");
	$sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='$id' AND menu_location='$location' ");
	header("location:".$_SERVER['PHP_SELF']);
}

if($action == "inc"){
	$sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='$location' ");
	$sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='$id' AND menu_location='$location' ");
	header("location:".$_SERVER['PHP_SELF']);
}

if($action == "move"){
	$menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
	$sql -> db_Update("menus", "menu_location='$position', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
	header("location:".$_SERVER['PHP_SELF']);
}

if(!getperms("2")){ header("location:../index.php"); }

require_once("auth.php");


$handle=opendir("../menus/");
	$c=0;
	while ($file = readdir($handle)){	
		if($file != "." && $file != ".." && $file != "plugins" && $file != "index.html" && $file !=	"log_menu.php"){
			$tmp = eregi_replace(".php", "", $file);
			if(!$sql -> db_Select("menus", "*", "menu_name='$tmp'")){
				$sql -> db_Insert("menus", " 0, '$tmp', 0, 0 ");
			}
			$menustr .= "&".eregi_replace(".php", "", $file);
			$c++;
		}
	}
closedir($handle);

$sql -> db_Select("menus");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
	if(eregi($menu_name, $menustr)){
	}else{
		$sql -> db_Delete("menus", "menu_name='$menu_name'");
	}
}

$ns -> tablerender("<div style=\"text-align:center\">Menus</div>", "");
echo "<br />";

$menus_used = explode(".", $columns);

for($count=0;$count<=5;$count++){
	if($menus_used[$count] == 0){
		$sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_location='".($count+1)."' ");
	}
}

echo "<table style=\"width:100%\" border=\"1\">
<tr>";
if($menus_used[2]){
	echo "<td style=\"width:15%; vertical-align:top; text-align:center\">
	FarLeft Menu";

	$count = 1;
	$sql -> db_Select("menus", "*", "menu_location='3' ORDER BY menu_order");
	while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
		$menu_name = eregi_replace("_menu", "", $menu_name);
		$text = "<div class=\"mediumtext\"><u><b>".$menu_name."</u></b></div><br />
		<a href=\"".$_SERVER['PHP_SELF']."?deac.".$menu_id."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/off.gif\" alt=\"\" /> Deactivate</div></a>";
		if($menu_order != 1){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?inc.".$menu_id.".".$menu_order.".3\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/up.gif\" alt=\"\" /> Move Up</div></a>";
		}
		if($menu_count != $menu_order){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?dec.".$menu_id.".".$menu_order.".3\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/down.gif\" alt=\"\" /> Move Down</div></a>";
		}
		if($menus_used[0]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".1\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to Left menu</div></a>";
		}
		if($menus_used[1]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".2\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to Rightmenu</div></a>";
		}
		if($menus_used[4]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".5\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to Centermenu</div></a>";
		}
		if($menus_used[3]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".4\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to FarRightmenu</div></a>";
		}
		
		$text .= "<br />";
		$ns -> tablerender("Menu ".$count, $text);
		$count++;
	}
	echo "</td>";
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

if($menus_used[0]){
	$menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='1' ");
	echo "<td style=\"width:15%; vertical-align:top; text-align:center\">
	Left Menu";
	$count = 1;
	$sql -> db_Select("menus", "*", "menu_location='1' ORDER BY menu_order");
	while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
		$menu_name = eregi_replace("_menu", "", $menu_name);
		$text = "<div class=\"mediumtext\"><u><b>".$menu_name."</u></b></div><br />
		<a href=\"".$_SERVER['PHP_SELF']."?deac.".$menu_id."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/off.gif\" alt=\"\" /> Deactivate</div></a>";
		if($menu_order != 1){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?inc.".$menu_id.".".$menu_order.".1\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/up.gif\" alt=\"\" /> Move Up</div></a>";
		}
		if($menu_count != $menu_order){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?dec.".$menu_id.".".$menu_order.".1\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/down.gif\" alt=\"\" /> Move Down</div></a>";
		}
		if($menus_used[2]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".3\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to FarLeft menu</div></a>";
		}
		if($menus_used[1]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".2\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to Rightmenu</div></a>";
		}
		if($menus_used[4]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".5\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to Centermenu</div></a>";
		}
		if($menus_used[3]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".4\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to FarRightmenu</div></a>";
		}
		
		$text .= "<br />";
		$ns -> tablerender("Menu ".$count, $text);
		$count++;
	}
	echo "</td>";
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

if($menus_used[4]){
	echo "<td style=\"width:15%; vertical-align:top; text-align:center\">
	Center Menu";
	$count = 1;
	$sql -> db_Select("menus", "*", "menu_location='5' ORDER BY menu_order ");
	while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
		$menu_name = eregi_replace("_menu", "", $menu_name);
		$text = "<div class=\"mediumtext\"><u><b>".$menu_name."</u></b></div><br />
		<a href=\"".$_SERVER['PHP_SELF']."?deac.".$menu_id."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/off.gif\" alt=\"\" /> Deactivate</div></a>";
		if($menu_order != 1){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?inc.".$menu_id.".".$menu_order.".5\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/up.gif\" alt=\"\" /> Move Up</div></a>";
		}
		if($menu_count != $menu_order){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?dec.".$menu_id.".".$menu_order.".5\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/down.gif\" alt=\"\" /> Move Down</div></a>";
		}
		if($menus_used[2]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".3\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to FarLeft menu</div></a>";
		}
		if($menus_used[1]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".2\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to Rightmenu</div></a>";
		}
		if($menus_used[0]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".1\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to Leftmenu</div></a>";
		}
		if($menus_used[3]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".4\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to FarRightmenu</div></a>";
		}
		
		$text .= "<br />";
		$ns -> tablerender("Menu ".$count, $text);
		$count++;
	}
	echo "</td>";
}

unset($text);
echo "<td style=\"width:50%; vertical-align:top; text-align:center\">Main Column";
$sql -> db_Select("menus", "*", "menu_location='0' ");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
	$menu_name = eregi_replace("_menu", "", $menu_name);
	$text .= "<div class=\"mediumtext\"><u><b>".$menu_name."</u></b></div>
	<a href=\"".$_SERVER['PHP_SELF']."?act.".$menu_id.".1\">[activate in leftmenu]
	<a href=\"".$_SERVER['PHP_SELF']."?act.".$menu_id.".2\">[activate in rightmenu]<br /><br />
	";
}
$ns -> tablerender("<div style=\"text-align:center\">Inactive Menus</div>", $text);


echo "</td>";

if($menus_used[1]){
	echo "<td style=\"width:15%; vertical-align:top; text-align:center\">
	Right Menu";
	$count = 1;
	$sql -> db_Select("menus", "*", "menu_location='2' ORDER BY menu_order ");
	while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
		$menu_name = eregi_replace("_menu", "", $menu_name);
		$text = "<div class=\"mediumtext\"><u><b>".$menu_name."</u></b></div><br />
		<a href=\"".$_SERVER['PHP_SELF']."?deac.".$menu_id."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/off.gif\" alt=\"\" /> Deactivate</div></a>";
		if($menu_order != 1){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?inc.".$menu_id.".".$menu_order.".2\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/up.gif\" alt=\"\" /> Move Up</div></a>";
		}
		if($menu_count != $menu_order){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?dec.".$menu_id.".".$menu_order.".2\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/down.gif\" alt=\"\" /> Move Down</div></a>";
		}
		if($menus_used[2]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".3\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to FarLeft menu</div></a>";
		}
		if($menus_used[0]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".1\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to Leftmenu</div></a>";
		}
		if($menus_used[4]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".5\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to Centermenu</div></a>";
		}
		if($menus_used[3]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".4\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/right.gif\" alt=\"\" /> Move to FarRightmenu</div></a>";
		}
		
		$text .= "<br />";
		$ns -> tablerender("Menu ".$count, $text);
		$count++;
	}
	echo "</td>";
}

if($menus_used[3]){
	echo "<td style=\"width:15%; vertical-align:top; text-align:center\">
	FarRight Menu";
	$count = 1;
	$sql -> db_Select("menus", "*", "menu_location='4' ORDER BY menu_order ");
	while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
		$menu_name = eregi_replace("_menu", "", $menu_name);
		$text = "<div class=\"mediumtext\"><u><b>".$menu_name."</u></b></div><br />
		<a href=\"".$_SERVER['PHP_SELF']."?deac.".$menu_id."\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/off.gif\" alt=\"\" /> Deactivate</div></a>";
		if($menu_order != 1){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?inc.".$menu_id.".".$menu_order.".4\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/up.gif\" alt=\"\" /> Move Up</div></a>";
		}
		if($menu_count != $menu_order){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?dec.".$menu_id.".".$menu_order.".4\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/down.gif\" alt=\"\" /> Move Down</div></a>";
		}
		if($menus_used[2]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".3\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to FarLeft menu</div></a>";
		}
		if($menus_used[1]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".2\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to Rightmenu</div></a>";
		}
		if($menus_used[4]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".5\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to Centermenu</div></a>";
		}
		if($menus_used[0]){
			$text .= "<a href=\"".$_SERVER['PHP_SELF']."?move.".$menu_id.".1\"><div class=\"smallblacktext\"><img style=\"border:0\" src=\"../themes/shared/generic/left.gif\" alt=\"\" /> Move to Leftmenu</div></a>";
		}
		
		$text .= "<br />";
		$ns -> tablerender("Menu ".$count, $text);
		$count++;
	}
	echo "</td>";
}


echo "</tr></table>";





require_once("footer.php");
?>