<?php
require_once("class2.php");

$id = $_SERVER['QUERY_STRING'];

if($id != ""){
	$sql -> db_Update("links", "link_refer=link_refer+1 WHERE link_id='$id' ");
	$sql -> db_Select("links", "*", "link_id='$id' ");
	list($link_id, $link_name, $link_url) = $sql-> db_Fetch();
	header("location:".$link_url);
}

require_once(HEADERF);

$ns -> tablerender("<div style=\"text-align:center\">Links</div>", "");
$sql -> db_Select("link_category", "*", "link_category_id != '1' ");
$sql2 = new db;
while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
	if($sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ")){
		unset($text);
		while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_order, $link_refer) = $sql2-> db_Fetch()){
			$text .= "<table style=\"width:95%\" cellspacing=\"5\">";
			$caption = LAN_86." $link_category_name";
			if($link_category_description != ""){
				$caption .= " <i>[$link_category_description]</i>";
			}
			$text .= "\n<tr><td style=\"width:95px; vertical-align: top\">";
			if($link_button != ""){
				$text .= "<a href=\"".$_SERVER['PHP_SELF']."?$link_id\"><img style=\"border:0\" src=\"$link_button\" alt=\"".LAN_87." $link_name\" /></a>";
			}else{
				$text .= "<a href=\"".$_SERVER['PHP_SELF']."?$link_id\"><img style=\"border:0\" src=\"themes/shared/generic/blankbutton.png\" alt=\"".LAN_87." $link_name\" /></a>";
			}
			$text .= "</td>
			<td style=\"vertical-align: top;\">
			<a href=\"".$_SERVER['PHP_SELF']."?$link_id\">
			<b>
			<span class=\"defaultblacktext\">$link_name</span>
			</b>
			</a>
			<i>[$link_url]</i>
			<br />
			$link_description
			</td>
			<td style=\"text-align: right; vertical-align:top; width:80px\">
			<span class=\"smalltext\">[ ".LAN_88." $link_refer ]</span></td></tr>";
			if(ADMIN == TRUE && getperms("I")){
				$text .= "<tr><td colspan=\"3\" class=\"smalltext\">".LAN_89."[ <a href=\"admin/links.php?edit.".$link_id."\">".LAN_68."</a> ] [ <a href=\"admin/links.php?delete.".$link_id."\">".LAN_69."</a> ][ <a href=\"admin/links.php?add.".$link_category."\">".LAN_90."</a> ][ <a href=\"admin/link_category.php\">".LAN_91."</a> ]</td></tr>";
			}
			$text .= "</table>";
		}
		$ns -> tablerender($caption, $text);
	}
}


/*
$links_total = $sql -> db_Count("links", "(*)", "WHERE link_category !='1'");
if($links_total == 0){
	echo "<div style=\"text-align:center\">".LAN_85."</div>";
}else{

	$counter = 2;
		while($sql -> db_Select("links", "*", "link_category ='$counter' ")){
			
			$text = "<table style=\"width:95%\" cellspacing=\"5\">";

			$sql2 = new db;
			while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_order, $link_refer) = $sql-> db_Fetch()){
				
				
				$sql2 -> db_Select("link_category", "*", "link_category_id='$link_category' ");
				list($link_category_id, $link_category_name, $link_category_description) = $sql2-> db_Fetch();
				$caption = LAN_86." $link_category_name";
				if($link_category_description != ""){
					$caption .= " <i>[$link_category_description]</i>";
				}
				$text .= "\n<tr>
<td style=\"width:95px; vertical-align: top\">";
				if($link_button != ""){
					$text .= "<a href=\"".$_SERVER['PHP_SELF']."?$link_id\"><img style=\"border:0\" src=\"$link_button\" alt=\"".LAN_87." $link_name\" /></a>";
				}else{
					$text .= "<a href=\"".$_SERVER['PHP_SELF']."?$link_id\"><img style=\"border:0\" src=\"themes/shared/generic/blankbutton.png\" alt=\"".LAN_87." $link_name\" /></a>";
				}
				$text .= "</td>
<td style=\"vertical-align: top;\">
<a href=\"".$_SERVER['PHP_SELF']."?$link_id\"><b><span class=\"defaultblacktext\">$link_name</span></b></a> <i>[$link_url]</i>
<br />
$link_description
</td>				
<td style=\"text-align: right; vertical-align:top; width:80px\">
<span class=\"smalltext\">
[ ".LAN_88." $link_refer ]
</span>
</td>
</tr>
";
				if(ADMIN == TRUE && ADMINPERMS <=2){
					$text .= "<tr><td colspan=\"3\" class=\"smalltext\">".LAN_89."
[ <a href=\"admin/links.php?edit.".$link_id."\">".LAN_68."</a> ] 
[ <a href=\"admin/links.php?delete.".$link_id."\">".LAN_69."</a> ]
[ <a href=\"admin/links.php?add.".$link_category."\">".LAN_90."</a> ]
[ <a href=\"admin/link_category.php\">".LAN_91."</a> ]
</td></tr>";
				}
			}
			$text .= "</table>";
			$ns -> tablerender($caption, $text);
			$counter++;
		}
}
*/
require_once(FOOTERF);
?>