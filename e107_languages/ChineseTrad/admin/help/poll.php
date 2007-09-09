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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/poll.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "您可以設定投票或是問卷, 只要輸入標題跟選項, 預覽覺得可以以後請勾選他啟動.<br /><br />
瀏覽投票, 請確認選單頁面和外掛 poll_menu 已經被啟動.";

$ns -> tablerender("投票問題", $text);
?>