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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/download.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "請上傳檔案到 ".e_FILE."download資料夾中, 檔案圖片則是到 ".e_FILE."downloadimages 資料夾而短文圖片則是到 ".e_FILE."downloadthumbs 資料夾.
<br /><br />
提供檔案下載, 請先新增源頭分類, 然後新增分區於該源頭內, 您將可以新增檔案下載.";
$ns -> tablerender("下載問題", $text);
?>