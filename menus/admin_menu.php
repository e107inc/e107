<?php
if(ADMIN == TRUE){
	$amtext = "<select name=\"activate\" onChange=\"urljump(this.options[selectedIndex].value)\" class=\"tbox\">
	<option>Select ...</option>\n";
	$amtext .= wad("admin/newspost.php", "News", "H");
	$amtext .= wad("admin/news_category.php", "News Categories", "7");
	$amtext .= wad("admin/prefs.php", "Preferences", "Site Prefs", "1");
	$amtext .= wad("admin/menus.php", "Menus", "2");
	$amtext .= wad("admin/administrator.php", "Administrators", "3");

	$amtext .= wad("admin/updateadmin.php", "Admin settings", "");
	$amtext .= wad("admin/forum.php", "Forums", "5");
	$amtext .= wad("admin/article.php", "Articles", "J");
	$amtext .= wad("admin/content.php", "Content", "L");
	$amtext .= wad("admin/review.php", "Reviews", "K");

	$amtext .= wad("admin/links.php", "Links", "I");
	$amtext .= wad("admin/link_category.php", "Link Categories", "8");
	$amtext .= wad("admin/wmessage.php", "Welcome Message", "M");
	$amtext .= wad("admin/upload.php", "Upload", "6");
	$amtext .= wad("admin/submitnews.php", "Submitted News", "N");

	$amtext .= wad("admin/banlist.php", "Banlist", "4");
	$amtext .= wad("admin/users.php", "Users", "4");
	$amtext .= wad("admin/ugflag.php", "Maintainance", "9");
	$amtext .= wad("admin/admin.php?logout", "Logout", "");
	$amtext .= "</select>";
	$ns -> tablerender("Admin", $amtext);
}
function wad($url, $urlname, $perms){
	global $amtext;
	if(getperms($perms)){
		return "<option value=\"".$url."\">".$urlname."</option>";
	}
}
?>