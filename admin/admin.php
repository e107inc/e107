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
	$ptmp = explode(".", $perms);
	if(ADMINPERMS == $ptmp[0] || ADMINPERMS == $ptmp[1] || ADMINPERMS == $ptmp[2] || ADMINPERMS == $ptmp[3]){
		$tmp = "<td style=\"text-align:center; vertical-align:top\">
<a href=\"".$link."\"><img src=\"e107.png\" alt=\"bullet\" style=\"border:0\"/></a>
<br />
<a href=\"".$link."\"><b>".$title."</b></a>
<br />
".$description."
<br /><br />
</td>";
	}else{
		$tmp = "<td style=\"text-align:center; vertical-align:top\">
<img src=\"e1073.png\" alt=\"bullet\" style=\"border:0\"/>
<br />
<b>".$title."</b>
<br />
".$description."
<br /><br />
</td>";
	}
	return $tmp;
}

$text = "<table style=\"width:95%\">
<tr>";

$text .= wad("newspost.php", "News", "Add/edit/delete news items", "0.1.2.3");
$text .= wad("news_category.php", "News Categories", "Add/edit/delete news categories", "0.1.2");
$text .= wad("prefs.php", "Preferences", "Edit Site Preferences", "0.1");
$text .= wad("menus.php", "Menus", "Alter the order of your menus", "0.1");
$text .= wad("administrator.php", "Administrators", "Add/delete site administrators", "0.1");

$text .= "</tr><tr>";

$text .= wad("updateadmin.php", "Update admin settings", "Edit your admin settings", "0.1.2.3");
$text .= wad("forum.php", "Forums", "Add/Edit Forums", "0.1");
$text .= wad("article.php", "Articles/Content/Reviews", "Add new/edit/delete articles/reviews, add new content pages", "0.1.2");
$text .= wad("links.php", "Links", "Add new/edit/delete links", "0.1.2");
$text .= wad("link_category.php", "Link Categories", "Add new/edit/delete link categories", "0.1.2");

$text .= "</tr><tr>";

$text .= wad("wmessage.php", "Welcome Message", "Set static welcome message", "0.1.2");
$text .= wad("upload.php", "Upload", "Upload file/image", "0.1.2");
$text .= wad("submitnews.php", "Submitted News", "Review user submitted news items", "0.1.2");
$text .= wad("banlist.php", "Banlist", "Ban visitors by IP address", "0.1.2");
$text .= wad("users.php", "Users", "Moderate site members", "0.1");
$text .= "</tr><tr>";

$text .= wad("ugflag.php", "Maintainance", "Display custom site is down page", "0.1.2");
$text .= wad("admin.php?logout", "Logout", "", "0.1.2.3.4");

$text .= "</tr></table>";

$ns -> tablerender("<div style=\"text-align:center\">Welcome ".ADMINNAME." (Level ".ADMINPERMS." administrator)</div>", $text);

$text = "<table style=\"width:95%\">
<tr>";

	$handle=opendir("../menus/plugins/");
	$c=0;
	while ($file = readdir($handle)){	
		if($file != "." && $file != ".."){
			$text .= "<td style=\"text-align:center; vertical-align:top\">";
			require_once("../menus/plugins/".$file);
			$text .= "</td>";
			if($c == 5){
				$text .= "</tr><tr>";
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