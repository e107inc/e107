<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/links.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
$id = e_QUERY;

if($id != ""){
	$sql -> db_Update("links", "link_refer=link_refer+1 WHERE link_id='$id' ");
	$sql -> db_Select("links", "*", "link_id='$id' ");
	list($link_id, $link_name, $link_url) = $sql-> db_Fetch();
	header("location:".$link_url);
	exit;
}

require_once(HEADERF);

$ns -> tablerender("<div class='centre'>Links</div>", "");
$sql -> db_Select("link_category", "*", "link_category_name != 'Main' AND link_category_name != 'Main_Sub'");
$sql2 = new db;
while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
	if($sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ORDER BY link_order ")){
		unset($text);
		while($row = $sql2-> db_Fetch()){
			extract($row);
			$text .= "<table class='defaulttable' cellspacing='5'>";
			$caption = LAN_86." $link_category_name";
			if($link_category_description != ""){
				$caption .= " <i>[$link_category_description]</i>";
			}

			switch ($link_open) { 
			case 1:
				$link_append = "<a href='".e_SELF."?".$link_id."' target='_blank'>";
			break; 
			case 2:
			   $link_append = "<a href='".e_SELF."?".$link_id."' target='_parent'>";
			break;
			case 3:
			   $link_append = "<a href='".e_SELF."?".$link_id."' target='_top'>";
			break;
			case 4:
				$link_append = "<a href='javascript:openwindow('".e_SELF."?".$link_id."')'>";
			break;
			default:
			   $link_append = "<a href='".e_SELF."?".$link_id."'>";
			}

			$text .= "\n<tr><td style='width:10%; vertical-align: top'>";
			if($link_button != ""){
				$text .= $link_append."\n<img style='border:0' src='$link_button' alt='".LAN_87." $link_name' /></a>";
			}else{
				$text .= $link_append."\n<img style='border:0' src='themes/shared/generic/blankbutton.png' alt='".LAN_87." $link_name' /></a>";
			}
			$text .= "</td>
			<td style='width:80%; vertical-align: top;'>";

			$text .=  $link_append."<b>".$link_name."</b></a>\n";

			$text .= "<i>[$link_url]</i>
			<br />
			$link_description
			</td>
			<td style='text-align: right; vertical-align:top; width:10%'>
			<span class='smalltext'>[ ".LAN_88." $link_refer ]</span></td></tr>";
			if(ADMIN == TRUE && getperms("I")){
				$text .= "<tr><td colspan='3' class='smalltext'>".LAN_89."[ <a href='".e_ADMIN."links.php?edit.".$link_id."'>".LAN_68."</a> ] [ <a href='".e_ADMIN."links.php?delete.".$link_id."'>".LAN_69."</a> ][ <a href='".e_ADMIN."links.php?add.".$link_category."'>".LAN_90."</a> ][ <a href='".e_ADMIN."link_category.php'>".LAN_91."</a> ]</td></tr>";
			}
			$text .= "</table>";
		}
		$ns -> tablerender($caption, $text);
	}
}

require_once(FOOTERF);
?>