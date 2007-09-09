<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     咎teve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/filemanager.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "您可以管理 /files 目錄檔案. 假如有錯誤訊息是有關於權限， 請上傳時變更 CHMOD 該目錄於 777.";
$ns -> tablerender("檔案管理問題", $text);
?>