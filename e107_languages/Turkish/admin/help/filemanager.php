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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Turkish/admin/help/filemanager.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-05-31 13:47:59 $
|     $Author: whoisbig $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "You are able to manage the files in your /files directory from this page. If you are getting an error message about permissions when uploading please CHMOD the directory you are attempting to upload into to 777.";
$ns -> tablerender("File Manager Help", $text);
?>