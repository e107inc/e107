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
$text .= wad("menus.php", "Menus", "Alter the order of your menus", "2");
$text .= wad("administrator.php", "Administrators", "Add/delete site administrators", "3");

$text .= "</tr><tr>";

$text .= wad("updateadmin.php", "Update admin password", "Change your password", "");
$text .= wad("forum.php", "Forums", "Add/Edit Forums", "5");
$text .= wad("article.php", "Articles", "Add new/edit/delete articles", "J");
$text .= wad("content.php", "Content", "Add new/edit/delete content pages", "L");
$text .= wad("review.php", "Reviews", "Add new/edit/delete reviews", "K");

$text .= "</tr><tr>";
$text .= wad("links.php", "Links", "Add new/edit/delete links", "I");
$text .= wad("link_category.php", "Link Categories", "Add new/edit/delete link categories", "8");
$text .= wad("download.php", "Downloads", "Manage Downloads", "R");
$text .= wad("download_category.php", "Download Categories", "Add new/edit/delete download categories", "Q");
$text .= wad("wmessage.php", "Welcome Message", "Set static welcome message", "M");

$text .= "</tr><tr>";
$text .= wad("filemanager.php", "File Manager", "Manage files", "6");
$text .= wad("submitnews.php", "Submitted News", "Review user submitted news items", "N");
$text .= wad("banlist.php", "Banlist", "Ban visitors by IP address", "4");
$text .= wad("users.php", "Users", "Moderate site members", "4");
$text .= wad("userclass2.php", "User Classes", "Create/edit user classes", "4");

$text .= "</tr><tr>";
$text .= wad("ugflag.php", "Maintainance", "Display custom site is down page", "9");
$text .= wad("custommenu.php", "Custom Menus", "Create custom menu items", "2");
$text .= wad("db.php","SQL","Database utilities","0");
$text .= wad("admin.php?logout", "Logout", "", "");


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
	$ns -> tablerender("<div style=\"text-align:center\">Welcome ".ADMINNAME."</div>", $text);
require_once("footer.php");
?>
