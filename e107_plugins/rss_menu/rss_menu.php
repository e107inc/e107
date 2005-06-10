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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/rss_menu.php,v $
|     $Revision: 1.9 $
|     $Date: 2005-06-10 00:40:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if(!defined("e_PLUGIN")){ exit; }
global $FILES_DIRECTORY,$pref;
$path = e_PLUGIN."rss_menu/";

$des = "";

if(strstr(e_SELF, "comment.php")) {
	$type = 5;
	$des = BACKEND_MENU_L4;
}
if(strstr(e_SELF, "/forum")) {
	$type = 6;
	$des = BACKEND_MENU_L5;
}
if(strstr(e_SELF, "forum_viewtopic")) {
	$type = 7;
	$des = BACKEND_MENU_L6;
}
if(strstr(e_SELF, "chat.php")) {
	$type = 9;
	$des = BACKEND_MENU_L7;
}

if(strstr(e_SELF, "/bugtracker")) {
	$type = 10;
	$des = BACKEND_MENU_L8;
}

if(strstr(e_SELF, "download.php")) {
	$type = 12;
	$des = BACKEND_MENU_L9;
}

if(!$des) {
	$type = 1;
	$des = BACKEND_MENU_L3;
}

if(e_PAGE == "news.php" && $pref['rss_newscats']){
	$qry = explode(".",e_QUERY);
    if($qry[0] == "cat" || $qry[0] == "list"){
     $topic = $qry[1];
	}
}

$text = "<div style='text-align:center' class='smalltext'>
".$des.BACKEND_MENU_L1."<br />
<div class='spacer'><a href='".$path."rss.php?$type.1.$topic'><img src='".$path."images/rss1.png' alt='rss1.0' style='border:0' /></a></div>
<div class='spacer'><a href='".$path."rss.php?$type.2.$topic'><img src='".$path."images/rss2.png' alt='rss2.0' style='border:0' /></a></div>
<div class='spacer'><a href='".$path."rss.php?$type.3.$topic'><img src='".$path."images/rss3.png' alt='rdf' style='border:0' /></a><br /></div>
</div>";

$caption = (file_exists(THEME."images/backend_menu.png") ? "<img src='".THEME."images/backend_menu.png' alt='' style='vertical-align:middle' /> ".BACKEND_MENU_L2 : BACKEND_MENU_L2);

$ns->tablerender($caption, $text, 'backend');
?>