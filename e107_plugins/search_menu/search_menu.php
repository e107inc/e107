<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/search_menu/search_menu.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-03-24 16:52:09 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
if(!defined("e_PLUGIN")){ exit; }
@include(e_PLUGIN."search_menu/languages/".e_LANGUAGE.".php");
if (strstr(e_PAGE, "news.php")) {
	 $page = 0;
} elseif(strstr(e_PAGE, "comment.php")) {
	 $page = 1;
} elseif(strstr(e_PAGE, "content.php") && strstr(e_QUERY, "content")) {
	 $page = 2;
} elseif(strstr(e_PAGE, "content.php") && strstr(e_QUERY, "review")) {
	 $page = 3;
} elseif(strstr(e_PAGE, "content.php") && strstr(e_QUERY, "content")) {
	 $page = 4;
} elseif(strstr(e_PAGE, "chat.php")) {
	 $page = 5;
} elseif(strstr(e_PAGE, "links.php")) {
	 $page = 6;
} elseif(strstr(e_PAGE, "forum")) {
	 $page = 7;
} elseif(strstr(e_PAGE, "user.php") || strstr(e_PAGE, "usersettings.php")) {
	 $page = 8;
} elseif(strstr(e_PAGE, "download.php")) {
	 $page = 9;
} else {
	 $page = 99;
}

if (isset($custom_query[1]) && $custom_query[1] != '') {
	$image_file = ($custom_query[1] != 'default') ? $custom_query[1] : e_PLUGIN.'search_menu/images/search.png';
	$width = $custom_query[2] ? $custom_query[2] : '16';
	$height = $custom_query[3] ? $custom_query[3] : '16';
	$search_button = "<input type='image' src='".$image_file."' value='".LAN_180."' style='width: ".$width."px; height: ".$height."px; border: 0px; vertical-align: middle' name='s' />";
} else {
	$search_button = "<input class='button search' type='submit' name='s' value='".LAN_180."' />";
}
$text = "<form method='get' action='".e_BASE."search.php'>
	<p>
	<input class='tbox search' type='text' name='q' size='20' value='' maxlength='50' />
	<input type='hidden' name='r' value='0' />
	".$search_button."
	</p>
	</form>";
if (isset($searchflat) && $searchflat) {
	echo $text;
} else {
	$ns->tablerender(LAN_180." ".SITENAME, "<div style='text-align:center'>".$text."</div>", 'search');
}
?>