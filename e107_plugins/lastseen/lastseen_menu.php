<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     (C)Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/lastseen/lastseen_menu.php,v $
|     $Revision: 1.8 $
|     $Date: 2009-11-19 11:45:50 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit(); }

include_lan(e_PLUGIN."lastseen/languages/".e_LANGUAGE.".php");

$sql -> db_Select("user", "user_id, user_name, user_currentvisit", "ORDER BY user_currentvisit DESC LIMIT 0,10", "nowhere");
$userArray = $sql -> db_getList();

$gen = new convert;
$text = "<ul style='margin-left:15px; margin-top:0px; padding-left:0px;'>";
foreach($userArray as $user)
{
//	extract($user);
	$seen_ago = $gen -> computeLapse($user['user_currentvisit'], false, false, true, 'short');
	$lastseen = ($seen_ago ? $seen_ago : "1 ".LANDT_09)." ".LANDT_AGO; 
$text .= "<li style='list-style-type: square;'><a href='".e_BASE."user.php?id.".$user['user_id']."'>".$user['user_name']."</a><br /> [ ".$lastseen." ]</li>";
}
$text .= "</ul>";

$ns->tablerender(LSP_LAN_1, $text, 'lastseen');
?>