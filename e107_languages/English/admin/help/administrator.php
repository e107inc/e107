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
|     $Source: /cvs_backup/e107_0.7/e107_languages/English/admin/help/administrator.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Site Admin Help";
$text = "Use this page to edit the preferences for, or delete site administrators. The administrator will only have permission to access the features that are ticked.<br /><br />
To create a new admin go to the user config page and update an existing user to admin status.";
$ns -> tablerender($caption, $text);
?>