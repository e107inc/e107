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

require_once(HEADERF);
if(e_QUERY == "" &&  $pref['linkpage_categories'] == 1) {
	$caption = LAN_61;
	$sql2 = new db;
	$category_total = $sql -> db_Select("link_category", "*", "link_category_id != '1' ");
	$total_links = $sql2 -> db_Count("links", "(*)", "WHERE link_category!=1");
	
	while($row = $sql-> db_Fetch()){		
		extract($row);
		$total_links_cat = $sql2 -> db_Count("links", "(*)", " WHERE link_category=$link_category_id ");
		$text .="<img src='".THEME."images/bullet2.gif' alt='bullet'> ".
		
		(!$total_links_cat ? $link_category_name : "<a href='links.php?cat.".$link_category_id."'>".$link_category_name."</a>")."
		
		$link_category_description ($total_links_cat ".($total_links_cat == 1 ? LAN_65 : LAN_66)." ".LAN_64.")<br />";
	}
	$text .= "<br /><br />There ".($total_links == 1 ? "is" : "are")." ".$total_links." ".($total_links == 1 ? LAN_65 : LAN_66)." total in ".$category_total." ".($category_total == 1 ? LAN_63 : LAN_62).". <br /> <br /><a href='".e_BASE."links.php?cat.all'>".LAN_67."</a> ";
	$ns -> tablerender($caption, $text);
	require_once(FOOTERF);

}else{

if(eregi("cat", e_QUERY)){
	$qs = explode(".", e_QUERY);
	$category = $qs[1];	
	if($category == "all"){		
		$sql -> db_Select("link_category", "*", "link_category_id != '1' ");
	}else{
	$sql -> db_Select("link_category", "*", "link_category_id='$category'");
	}
}else{
	$id = e_QUERY;
	if($id != ""){
		$sql -> db_Update("links", "link_refer=link_refer+1 WHERE link_id='$id' ");
		$sql -> db_Select("links", "*", "link_id='$id AND link_class!=255' ");
		list($link_id, $link_name, $link_url) = $sql-> db_Fetch();
		header("location:".$link_url);
			}
			$sql -> db_Select("link_category", "*", "link_category_id != '1' ");
				}
		$sql2 = new db;
		while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
		if($sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ORDER BY link_order ")){
			unset($text);
			while($row = $sql2-> db_Fetch()){
				extract($row);
				if(!$link_class || check_class($link_class)){
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
						$text .= $link_append."\n<img style='border:0' src='$link_button' alt='$link_name' /></a>";
					}else{
						$text .= $link_append."\n<img style='border:0' src='".e_IMAGE."generic/blankbutton.png' alt='$link_name' /></a>";
					}
					$text .= "</td>
					<td style='width:80%; vertical-align: top;'>";

					$text .=  $link_append."<b>".$link_name."</b></a>\n";

					$text .= "<i>[$link_url]</i>
					<br />
					$link_description
					</td>
					<td style='text-align: right; vertical-align:top; white-space:nowrap'>
					<span class='smalltext'>[ ".LAN_88." $link_refer ]</span></td></tr>";
					if(ADMIN == TRUE && getperms("I")){
						$text .= "<tr><td colspan='3' class='smalltext'>".LAN_89."[ <a href='".e_ADMIN."links.php?edit.".$link_id."'>".LAN_68."</a> ] [ <a href='".e_ADMIN."links.php?delete.".$link_id."'>".LAN_69."</a> ][ <a href='".e_ADMIN."links.php?add.".$link_category."'>".LAN_90."</a> ][ <a href='".e_ADMIN."link_category.php'>".LAN_91."</a> ]</td></tr>";
					}
					$text .= "</table>";
				}
			}
			$ns -> tablerender($caption, $text);
		}
	}
}
require_once(FOOTERF);
?>