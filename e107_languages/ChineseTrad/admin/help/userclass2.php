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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/userclass2.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:49 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "會員等級問題";
$text = "您可以新增或編輯/刪除已存在的會員等級.<br/>這將會設定私人保會會員於您的特定頁面. 例如： 您可以新增等級名稱 TEST, 然後開啟一個討論區僅限於會員等級於 TEST 等級登入.";
$ns -> tablerender($caption, $text);
?>