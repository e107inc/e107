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
|     $Source: /cvs_backup/e107_0.7/e107_languages/English/admin/help/userclass2.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Use Class Help";
$text = "You can create or edit/delete existing classes from this page.<br />This is useful for restricting users to certain parts of your site. For example, you could create a class called TEST, then create a forum which only allowed users in the TEST class to access it.";
$ns -> tablerender($caption, $text);
?>