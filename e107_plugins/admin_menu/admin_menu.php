<?php
if(ADMIN == TRUE){
	$amtext = "<div style='text-align:center'>
	<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>
	<option>".ADMIN_MENU_L1."</option>\n";
	$amtext .= wad(e_ADMIN."newspost.php", "News", "H");
	$amtext .= wad(e_ADMIN."news_category.php", "News Categories", "7");
	$amtext .= wad(e_ADMIN."prefs.php", "Preferences", "Site Prefs", "1");
	$amtext .= wad(e_ADMIN."menus.php", "Menus", "2");
	$amtext .= wad(e_ADMIN."administrator.php", "Administrators", "3");

	$amtext .= wad(e_ADMIN."updateadmin.php", "Admin settings", "");
	$amtext .= wad(e_ADMIN."forum.php", "Forums", "5");
	$amtext .= wad(e_ADMIN."article.php", "Articles", "J");
	$amtext .= wad(e_ADMIN."content.php", "Content", "L");
	$amtext .= wad(e_ADMIN."review.php", "Reviews", "K");

	$amtext .= wad(e_ADMIN."links.php", "Links", "I");
	$amtext .= wad(e_ADMIN."link_category.php", "Link Categories", "8");
	$amtext .= wad(e_ADMIN."wmessage.php", "Welcome Message", "M");
	$amtext .= wad(e_ADMIN."upload.php", "Upload", "6");
	$amtext .= wad(e_ADMIN."submitnews.php", "Submitted News", "N");

	$amtext .= wad(e_ADMIN."banlist.php", "Banlist", "4");
	$amtext .= wad(e_ADMIN."users.php", "Users", "4");
	$amtext .= wad(e_ADMIN."ugflag.php", "Maintainance", "9");
	$amtext .= wad(e_ADMIN."admin.php?logout", "Logout", "");
	$amtext .= "</select>
	</div>";
	$ns -> tablerender(ADMIN_MENU_L2, $amtext);
}
function wad($url, $urlname, $perms){
	global $amtext;
	if(getperms($perms)){
		return "<option value='".$url."'>".$urlname."</option>";
	}
}
?>