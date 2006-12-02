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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/menus2.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:43 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Menu Help";
$text .= "You can arrange where and in which order your menu items are displayed from here. Use the arrows to move the menus up and down until you are satisfied with their positioning.<br />
The menu items in the middle of the screen are de-activated; you can activate these by choosing a location to put them in.
";

$ns -> tablerender("Menus Help", $text);
?>