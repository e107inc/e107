<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/admin.php
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
require_once("auth.php");

function wad($link, $title, $description, $perms){

	if(getperms($perms)){
		$tmp = "<td style=\"text-align:center; vertical-align:top; width:20%\"><a href=\"".$link."\"><img src=\"../themes/shared/generic/e107.gif\" alt=\"bullet\" style=\"border:0\"/></a><br /><a href=\"".$link."\"><b>".$title."</b></a><br />".$description."<br /><br /></td>\n\n";
	}else{
		$tmp = "<td style=\"text-align:center; vertical-align:top\"><img src=\"../themes/shared/generic/e1073.gif\" alt=\"bullet\" style=\"border:0\"/><br /><b>".$title."</b><br />".$description."<br /><br /></td>";
	}
	return $tmp;
}

$text = "<table style=\"width:95%\">
<tr>";

$text .= wad("newspost.php", ADLAN_0, ADLAN_1, "H");
$text .= wad("news_category.php", ADLAN_2, ADLAN_3, "7");
$text .= wad("prefs.php", ADLAN_4, ADLAN_5, "1");
$text .= wad("menus.php", ADLAN_6, ADLAN_7, "2");
$text .= wad("administrator.php", ADLAN_8, ADLAN_9, "3");

$text .= "</tr><tr>";

$text .= wad("updateadmin.php", ADLAN_10, ADLAN_11, "");
$text .= wad("forum.php", ADLAN_12, ADLAN_13, "5");
$text .= wad("article.php", ADLAN_14, ADLAN_15, "J");
$text .= wad("content.php", ADLAN_16, ADLAN_17, "L");
$text .= wad("review.php", ADLAN_18, ADLAN_19, "K");

$text .= "</tr><tr>";
$text .= wad("links.php", ADLAN_20, ADLAN_21, "I");
$text .= wad("link_category.php", ADLAN_22, ADLAN_23, "8");
$text .= wad("download.php", ADLAN_24, ADLAN_25, "R");
$text .= wad("download_category.php", ADLAN_26, ADLAN_27, "Q");
$text .= wad("wmessage.php", ADLAN_28, ADLAN_29, "M");

$text .= "</tr><tr>";
$text .= wad("filemanager.php", ADLAN_30, ADLAN_31, "6");
$text .= wad("submitnews.php", ADLAN_32, ADLAN_33, "N");
$text .= wad("banlist.php", ADLAN_34, ADLAN_35, "4");
$text .= wad("users.php", ADLAN_36, ADLAN_37, "4");
$text .= wad("userclass2.php", ADLAN_38, ADLAN_39, "4");

$text .= "</tr><tr>";
$text .= wad("ugflag.php", ADLAN_40, ADLAN_41, "9");
$text .= wad("custommenu.php", ADLAN_42, ADLAN_43, "2");
$text .= wad("db.php",ADLAN_44, ADLAN_45,"0");
$text .= wad("admin.php?logout", ADLAN_46, "", "");


$text .= "</tr>";

$text .= "<tr>
<td colspan=\"5\">
<div style=\"text-align:center\">
<div class=\"border\"><div class=\"caption\">Plugins</div></div></div>
<br />
</td>
<tr>";

	$handle=opendir(e_BASE."menus/plugins/");
	$c=1;
	while ($file = readdir($handle)){	
		if($file != "." && $file != ".."){
			require_once(e_BASE."menus/plugins/".$file);
			if($c == 5){
				$text .= "</tr><tr>";
				$c=0;
			}
			$c++;
		}
	}
	$text .= "</tr></table>";
	closedir($handle);
	$ns -> tablerender("<div style=\"text-align:center\">".ADLAN_47." ".ADMINNAME."</div>", $text);
require_once("footer.php");
?>
