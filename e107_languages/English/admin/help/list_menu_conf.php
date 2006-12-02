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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/list_menu_conf.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:42 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "In this section you can configure 3 menus<br>
<b> New Articles Menu</b> <br>
Enter a number for example '5' in the first field to show the first 5 articles, leave empty to see all, You configure what the title of the link should be to the rest of the articles in the second field, when you leave this last option empty it won't create a link, for example: 'All articles'<br>
<b> Comments/Forum Menu</b> <br>
The number of comments default to 5, the number of characters default to 10000. The postfix is for if a line is too long it will cut it off and append this postfix to the end, a good choice for this is '...', check original topics if you want to see those in the overview.<br>

";
$ns -> tablerender("Menu Configuration Help", $text);
?>
