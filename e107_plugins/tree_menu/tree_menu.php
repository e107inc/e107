<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/tree_menu.php
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
	if($sql2 -> db_Select("links", "*", "link_name REGEXP('submenu.".$link_name."') ORDER BY link_order")){
		$mlink_name = $link_name;
		$text .= "
		<div class='spacer'>
		<div class='button' style='width:100%; cursor: pointer; cursor: hand' onClick='expandit(this)'>&raquo; ".$link_name."</div>
		<span style=\"display:none\" style=&{head};>
		";
		
		while($row = $sql2 -> db_Fetch()){
			extract($row);
			$link_name = str_replace("submenu.".$mlink_name.".", "", $link_name);
			$text .= "&middot; ".(strstr($link_url, "http") ? setlink($link_name, $link_url, $link_class, $link_open) : setlink($link_name, e_BASE.$link_url, $link_class, $link_open))."\n<br />";
		}
		$text .= "</span>
		</div>
		";
	}else{
		$text .= "<div class='spacer'><div class='button' style='width:100%; cursor: pointer; cursor: hand'>&middot; ".
		(strstr($link_url, "http") ? setlink($link_name, $link_url, $link_class, $link_open) : setlink($link_name, e_BASE.$link_url, $link_class, $link_open))."
		</div></div>";
	}
}

$ns -> tablerender("Main Menu", $text);


function setlink($link_name, $link_url, $link_class, $link_open){
	if(!$link_class || check_class($link_class) || ($link_class==254 && USER)){
		switch ($link_open){ 
			case 1:
				$link_append = " onclick=\"window.open('$link_url'); return false;\"";
			break; 
			case 2:
				$link_append = " target=\"_parent\"";
			break;
			case 3:
				$link_append = " target=\"_top\"";
			break;
			default:
				unset($link_append);
		}
		if(!strstr($link_url, "http:")){ $link_url = e_BASE.$link_url; }
		if($link_open == 4){
			$link =  "<a style='text-decoration:none' href=\"javascript:openwindow('".$link_url."')\">".$link_name."</a>\n";
		}else{
			$link =  "<a style='text-decoration:none' href=\"".$link_url."\"".$link_append.">".$link_name."</a>\n";
		}
	}
	return $link;
}





?>