<?php
if(ADMIN == TRUE){
	$amtext = "<select name=\"activate\" onChange=\"urljump(this.options[selectedIndex].value)\" class=\"tbox\">
	<option>Select ...</option>\n";
	$amtext .= wad(e_HTTP."admin/newspost.php", "News", "H");
	$amtext .= wad(e_HTTP."admin/news_category.php", "News Categories", "7");
	$amtext .= wad(e_HTTP."admin/prefs.php", "Preferences", "Site Prefs", "1");
	$amtext .= wad(e_HTTP."admin/menus.php", "Menus", "2");
	$amtext .= wad(e_HTTP."admin/administrator.php", "Administrators", "3");

	$amtext .= wad(e_HTTP."admin/updateadmin.php", "Admin settings", "");
	$amtext .= wad(e_HTTP."admin/forum.php", "Forums", "5");
	$amtext .= wad(e_HTTP."admin/article.php", "Articles", "J");
	$amtext .= wad(e_HTTP."admin/content.php", "Content", "L");
	$amtext .= wad(e_HTTP."admin/review.php", "Reviews", "K");

	$amtext .= wad(e_HTTP."admin/links.php", "Links", "I");
	$amtext .= wad(e_HTTP."admin/link_category.php", "Link Categories", "8");
	$amtext .= wad(e_HTTP."admin/wmessage.php", "Welcome Message", "M");
	$amtext .= wad(e_HTTP."admin/upload.php", "Upload", "6");
	$amtext .= wad(e_HTTP."admin/submitnews.php", "Submitted News", "N");

	$amtext .= wad(e_HTTP."admin/banlist.php", "Banlist", "4");
	$amtext .= wad(e_HTTP."admin/users.php", "Users", "4");
	$amtext .= wad(e_HTTP."admin/ugflag.php", "Maintainance", "9");
	$amtext .= wad(e_HTTP."admin/admin.php?logout", "Logout", "");
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