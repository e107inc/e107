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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/templates/newsfeed_menu_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-28 18:23:56 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

$truncate = 100;
$truncate_string = "...[more]";
$items = 10;

$NEWSFEED_MENU_START = "
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader3' style='text-align: center; margin-left: auto; margin-right: auto;'>{FEEDIMAGE} {FEEDTITLE}</td>
</tr>
<tr>
<td class='forumheader'>\n";

$NEWSFEED_MENU = "
<b>&raquo;</b>{FEEDITEMLINK}<br /><span class='smalltext'>{FEEDITEMTEXT}</span><br />\n";


$NEWSFEED_MENU_END = "
</td>
</tr>

<tr>
<td class='forumheader3' style='text-align: right;'><span class='smalltext'>{FEEDLASTBUILDDATE}<br />{LINKTOMAIN}</td>
</tr>

</table>\n";


?>