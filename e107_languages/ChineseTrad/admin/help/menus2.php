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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/menus2.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:47 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "選單問題";
$text .= "您可以安排您的選單內容順序以及顯示位址. 使用箭頭上下移動選單內容到您覺得適合的位址.<br />
選單項目於螢幕中央是未啟動的,您可以啟動他們並選擇是當得位址.
";

$ns -> tablerender("選單問題", $text);
?>