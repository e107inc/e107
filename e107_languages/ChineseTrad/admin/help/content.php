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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/content.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:43 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "您可以新增一般頁面於您的網站中. 該新增頁面的連結將會顯示在主要的選單盒中. 假如您新增頁面名稱 'Test', 一個連結稱為 'Test' 將會於您發表後顯示於連結盒中.<br />
如果您希望您的內容可以附上說明, 請輸入於 頁面頂部欄中.";
$ns -> tablerender("內容問題", $text);
?>