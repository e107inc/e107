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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Turkish/admin/help/link_category.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-05-31 13:48:00 $
|     $Author: whoisbig $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "You can separate your links into different categories, this makes navigating the main Links page much easier and improves layout.<br /><br />Any link entered under the Main category will be displayed in your main navigation menu.";
$ns -> tablerender("Link Category Help", $text);
?>