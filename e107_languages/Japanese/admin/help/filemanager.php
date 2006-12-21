<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ・teve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/admin/help/filemanager.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-21 15:43:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "You are able to manage the files in your /files directory from this page. If you are getting error message about permissions when uploading please CHMOD the directory you are atempting to upload into to 777.";
$ns -> tablerender("File Manager Help", $text);
?>