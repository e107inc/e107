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

if(IsSet($_POST['add_link']) && check_class($pref['link_submit_class'])){
	if($_POST['link_name'] && $_POST['link_url'] && $_POST['link_description']){
		$link_name = $aj -> formtpa($_POST['link_name'], "public");
		$link_url = $aj -> formtpa($_POST['link_url'], "public");
		$link_description = $aj -> formtpa($_POST['link_description'], "public");
		$link_button = $aj -> formtpa($_POST['link_button'], "public");
		$submitted_link = $_POST['cat_name']."^".$link_name."^".$link_url."^".$link_description."^".$link_button."^".USERNAME;
		$sql -> db_Insert("tmp", "'submitted_link', '".time()."', '$submitted_link' ");
		$ns -> tablerender(LAN_99, "<div style='text-align:center'>".LAN_100."</div>");
	}else{
		message_handler("ALERT", 5);
	}
}


if(e_QUERY == "submit" && check_class($pref['link_submit_class'])){
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='width:85%' class='fborder'>
	<tr>
	<td colspan='2' style='text-align:center' class='forumheader2'>".LAN_93."</td></tr>";

	if($link_cats = $sql -> db_Select("link_category")){
		$text .= "<tr>
		<td class='forumheader3' style='width:30%'>".LAN_86."</td>
		<td class='forumheader3' style='width:70%'>
        <select name='cat_name' class='tbox'>";

        while(list($cat_id, $cat_name, $cat_description) = $sql-> db_Fetch()){
			if($cat_name != "Main"){
				$text .= "<option value='$cat_id'>".$cat_name."</option>\n";
			}
        }
        $text .= "</select>
		</td>
		</tr>";
	}

	$text .= "<tr><td class='forumheader3' style='width:30%'><u>".LAN_94."</u></td><td class='forumheader3' style='width:30%'><input class='tbox' type='text' name='link_name' size='60' value='' maxlength='100' /></td></tr><tr><td class='forumheader3' style='width:30%'><u>".LAN_95."</u></td><td class='forumheader3' style='width:30%'><input class='tbox' type='text' name='link_url' size='60' value='' maxlength='200' /></td></tr><tr><td class='forumheader3' style='width:30%'><u>".LAN_96."</u></td><td class='forumheader3' style='width:30%'><textarea class='tbox' name='link_description' cols='59' rows='3'></textarea></td></tr><tr><td class='forumheader3' style='width:30%'>".LAN_97."</td><td class='forumheader3' style='width:30%'><input class='tbox' type='text' name='link_button' size='60' value='' maxlength='200' /></td></tr><tr><td colspan='2' style='text-align:center' class='forumheader3'><span class='smalltext'>".LAN_106."</span></td></tr><tr><td colspan='2' style='text-align:center' class='forumheader'><input class='button' type='submit' name='add_link' value='".LAN_98."' /></td></tr></table></form></div>";

	$ns -> tablerender(LAN_92, $text);
	require_once(FOOTERF);
	exit;
}

if(e_QUERY == "" &&  $pref['linkpage_categories'] == 1){
	
	$caption = LAN_61;
	$sql2 = new db;
	$category_total = $sql -> db_Select("link_category", "*", "link_category_id != '1' ");
	$total_links = $sql2 -> db_Count("links", "(*)", "WHERE link_category!=1");
	
	while($row = $sql-> db_Fetch()){		
		extract($row);
		$total_links_cat = $sql2 -> db_Count("links", "(*)", " WHERE link_category=$link_category_id ");
		$text .= ($link_category_icon ? "<img src='".e_IMAGE."link_icons/$link_category_icon' alt='' style='vertical-align:middle' />" : "<img src='".THEME."images/bullet2.gif' alt='' style='vertical-align:middle' />")." \n<b><span class='captiontext'>".
		(!$total_links_cat ? $link_category_name : "<a href='links.php?cat.".$link_category_id."'>".$link_category_name."</a>")."</span></b><br />
		$link_category_description ($total_links_cat ".($total_links_cat == 1 ? LAN_65 : LAN_66)." ".LAN_64.")<br />";
	}
	$text .= "<br /><br />".LAN_102." ".($total_links == 1 ? LAN_103 : LAN_104)." ".$total_links." ".($total_links == 1 ? LAN_65 : LAN_66)." ".LAN_105." ".$category_total." ".($category_total == 1 ? LAN_63 : LAN_62).". <br /> <br /><a href='".e_BASE."links.php?cat.all'>".LAN_67."</a> ";
	$ns -> tablerender($caption, $text);
}else{

$id = e_QUERY;
$qs=explode(".",e_QUERY);
if($qs[0] == "cat"){
	$category = $qs[1];
	unset($id);
} elseif($qs[1] == "cat"){
	$category = $qs[2];
	$id=$qs[0];
}

if(isset($id)){
	$id = $qs[0];
	if($id){
		$sql -> db_Update("links", "link_refer=link_refer+1 WHERE link_id='$id' ");
		$sql -> db_Select("links", "*", "link_id='$id AND link_class!=255' ");
		$row = $sql -> db_Fetch(); extract($row);
			header("location:".$link_url);

	}
	$sql -> db_Select("link_category", "*", "link_category_id != '1' ");
}
if($category){
	if($category == "all"){		
		$sql -> db_Select("link_category", "*", "link_category_id != '1' ");
	}else{
		$sql -> db_Select("link_category", "*", "link_category_id='$category'");
	}
}

		$sql2 = new db;
		while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
		if($link_total = $sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ORDER BY link_order ")){
			unset($text);
			$link_activ = 0;
			while($row = $sql2-> db_Fetch()){
				extract($row);
				if(!$link_class || check_class($link_class)){
					$link_activ++;
					$text .= "<table class='defaulttable' cellspacing='5'>";
					// Caption
					$caption = LAN_86." $link_category_name";
					if($link_category_description != ""){
						$caption .= " <i>[$link_category_description]</i>";
					}
					// Number of links displayed
					$caption .= " (<b title=\"".(ADMIN ? LAN_Links_2 : LAN_Links_1)."\" >".$link_activ."</b>".(ADMIN ? "/<b title=\"".(ADMIN ? LAN_Links_1 : "" )."\" >".$link_total."</b>" : "").") ";
					
					// Body
					if(isset($category)){
						$link_append = "<a href='".e_SELF."?".$link_id.".cat.{$category}'>";
					} else {

					switch ($link_open) { 
					case 1:
						$link_append = "<a href='".e_SELF."?".$link_id."' onclick=\"window.open('".e_SELF."?".$link_id."'); return false;\">";
					break; 
					case 2:
					   $link_append = "<a href='".e_SELF."?".$link_id."'>";
					break;
					case 3:
					   $link_append = "<a href='".e_SELF."?".$link_id."'>";
					break;
					case 4:
						$link_append = "<a href=\"javascript:open_window('".e_SELF."?".$link_id."')\">";
					break;
					default:
					   $link_append = "<a href='".e_SELF."?".$link_id."'>";
					}

					}

					$text .= "\n<tr><td style='width:10%; vertical-align: top'>";
					if($link_button){
						$text .= (strstr($link_button, "/") ? $link_append."\n<img style='border:0' src='$link_button' alt='$link_name' /></a>" : $link_append."\n<img style='border:0' src='".e_IMAGE."link_icons/$link_button' alt='$link_name' /></a>");
					}else{
						$text .= $link_append."\n<img style='border:0' src='".e_IMAGE."generic/blankbutton.png' alt='$link_name' /></a>";
					}
					$text .= "</td>
					<td style='width:80%; vertical-align: top;'>";

					$text .=  $link_append."<b>".$link_name."</b></a>\n";

					$text .= "<i>[$link_url]</i>
					<br />
					".$aj -> tpa($link_description)."
					</td>
					<td style='text-align: right; vertical-align:top; white-space:nowrap'>
					<span class='smalltext'>[ ".LAN_88." $link_refer ]</span></td></tr>
					</table>";
				}
						}
			if($pref['link_submit'] && check_class($pref['link_submit_class'])){
				$text .= "<a href='".e_SELF."?submit'>".LAN_101."</a>";
			}
			if($link_activ > 0){$ns -> tablerender($caption, $text);}
			$link_activ = 0;
		}
	}
}




require_once(FOOTERF);
?>