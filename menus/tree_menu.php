<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/menus/tree_menu.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// Many thanks to Lolo Irie for fixing the javascript that drives this menu item
unset($text);
$sql2 = new db;
$sql -> db_Select("links", "*", "link_category='1' AND link_name NOT REGEXP('submenu') ORDER BY link_order");
while($row = $sql -> db_Fetch()){
	extract($row);
	
		if($sql2 -> db_Select("links", "*", "link_name REGEXP('submenu.".$link_name."') ")){
			$mlink_name = $link_name;
			$text .= "
			<div class='spacer'>
			<div class='button' style='width:100%; cursor:hand' onClick='expandit(this)'>&middot; ".$link_name."</div>
			<span style=\"display:none\" style=&{head};>
			";
			while($row = $sql2 -> db_Fetch()){
				extract($row);
				$link_name = str_replace("submenu.".$mlink_name.".", "", $link_name);
				$text .= "&middot; <a href='".$link_url."'>".$link_name."</a><br />";
			}
			$text .= "</span>
			</div>
			";
		}else{
			$text .= "<div class='spacer'><div class='button' style='width:100%; cursor:hand'>&middot; <a style='text-decoration:none' href='".e_BASE.$link_url."'>".$link_name."</a></div></div>";
		}

}

$ns -> tablerender("Main Menu", $text);

?>