<?php

require_once(e_HANDLER."userclass_class.php");

$lan_file=e_PLUGIN."admin_menu/languages/".e_LANGUAGE.".php";
if(file_exists($lan_file)){
	require_once($lan_file);
} else {
	require_once(e_PLUGIN."admin_menu/languages/English.php");
}
if(ADMIN == TRUE){
	$amtext = "<div style='text-align:center'>
	<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>
	<option>".ADMIN_MENU_L1."</option>\n";
	$amtext .= wad(e_ADMIN."newspost.php", ADMIN_MENU_L3, "H");
	$amtext .= wad(e_ADMIN."newspost.php?cat", ADMIN_MENU_L4, "7");
	$amtext .= wad(e_ADMIN."prefs.php", ADMIN_MENU_L5, "Site Prefs", "1");
	$amtext .= wad(e_ADMIN."menus.php", ADMIN_MENU_L6, "2");
	$amtext .= wad(e_ADMIN."administrator.php", ADMIN_MENU_L7, "3");

	$amtext .= wad(e_ADMIN."updateadmin.php", ADMIN_MENU_L8, "");
	$amtext .= wad(e_ADMIN."forum.php", ADMIN_MENU_L9, "5");
	$amtext .= wad(e_ADMIN."article.php", ADMIN_MENU_L10, "J");
	$amtext .= wad(e_ADMIN."content.php", ADMIN_MENU_L11, "L");
	$amtext .= wad(e_ADMIN."review.php", ADMIN_MENU_L12, "K");

	$amtext .= wad(e_ADMIN."links.php", ADMIN_MENU_L13, "I");
	$amtext .= wad(e_ADMIN."link_category.php", ADMIN_MENU_L14, "8");
	$amtext .= wad(e_ADMIN."wmessage.php", ADMIN_MENU_L15, "M");
	$amtext .= wad(e_ADMIN."upload.php", ADMIN_MENU_L16, "6");
	$amtext .= wad(e_ADMIN."submitnews.php", ADMIN_MENU_L17, "N");

	$amtext .= wad(e_ADMIN."banlist.php", ADMIN_MENU_L18, "4");
	$amtext .= wad(e_ADMIN."users.php", ADMIN_MENU_L19, "4");
	$amtext .= wad(e_ADMIN."ugflag.php", ADMIN_MENU_L20, "9");
	$amtext .= wad(e_ADMIN."admin.php?logout", ADMIN_MENU_L21, "");
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