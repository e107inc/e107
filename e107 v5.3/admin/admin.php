<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/admin.php														|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
require_once("auth.php");

function wad($link, $title, $description, $perms){

	if(getperms($perms)){
		$tmp = "<td style=\"text-align:center; vertical-align:top\"><a href=\"".$link."\"><img src=\"e107.png\" alt=\"bullet\" style=\"border:0\"/></a><br /><a href=\"".$link."\"><b>".$title."</b></a><br />".$description."<br /><br /></td>";
	}else{
		$tmp = "<td style=\"text-align:center; vertical-align:top\"><img src=\"e1073.png\" alt=\"bullet\" style=\"border:0\"/><br /><b>".$title."</b><br />".$description."<br /><br /></td>";
	}
	return $tmp;
}

$text = "<table style=\"width:95%\">
<tr>";

$text .= wad("newspost.php", "News", "Add/edit/delete news items", "H");
$text .= wad("news_category.php", "News Categories", "Add/edit/delete news categories", "7");
$text .= wad("prefs.php", "Preferences", "Edit Site Preferences", "1");
$text .= wad("menus.php", "Menus", "Alter the order of your menus", "2");
$text .= wad("administrator.php", "Administrators", "Add/delete site administrators", "3");

$text .= "</tr><tr>";

$text .= wad("updateadmin.php", "Update admin settings", "Edit your admin settings", "");
$text .= wad("forum.php", "Forums", "Add/Edit Forums", "5");
$text .= wad("article.php", "Articles", "Add new/edit/delete articles", "J");
$text .= wad("content.php", "Content", "Add new/edit/delete content pages", "L");
$text .= wad("review.php", "Reviews", "Add new/edit/delete reviews", "K");

$text .= "</tr><tr>";
$text .= wad("links.php", "Links", "Add new/edit/delete links", "I");
$text .= wad("link_category.php", "Link Categories", "Add new/edit/delete link categories", "8");
$text .= wad("wmessage.php", "Welcome Message", "Set static welcome message", "M");
$text .= wad("upload.php", "Upload", "Upload file/image", "6");
$text .= wad("submitnews.php", "Submitted News", "Review user submitted news items", "N");

$text .= "</tr><tr>";

$text .= wad("banlist.php", "Banlist", "Ban visitors by IP address", "4");
$text .= wad("users.php", "Users", "Moderate site members", "4");
$text .= wad("ugflag.php", "Maintainance", "Display custom site is down page", "9");
$text .= wad("admin.php?logout", "Logout", "", "");
$text .= "</tr><tr>";



$text .= "</tr></table>";

$ns -> tablerender("<div style=\"text-align:center\">Welcome ".ADMINNAME."</div>", $text);

$text = "<table style=\"width:95%\">
<tr>";

	$handle=opendir("../menus/plugins/");
	$c=0;
	while ($file = readdir($handle)){	
		if($file != "." && $file != ".."){
			require_once("../menus/plugins/".$file);
			if($c == 4){
				$text .= "</tr><tr>";
				$c=0;
			}
			$c++;
		}
	}
	$text .= "</tr></table>";
	closedir($handle);
	if($text != ""){
		$ns -> tablerender("<div style=\"text-align:center\">Plugins</div>", "<div style=\"text-align:center\">".$text."</div>");
	}else{
		$ns -> tablerender("<div style=\"text-align:center\">Plugins</div>", "No plugins loaded.");
	}

require_once("footer.php");
?>