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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/administrator.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:42 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }


$caption = "網站管理說明";
$text = "使用此頁面來新增, 或刪除管理員. 僅管理員擁有權限進入此頁面.";
$ns -> tablerender($caption, $text);
?>