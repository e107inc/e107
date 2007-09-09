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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/chatbox.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:43 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "設定聊天基本設定.<br />假如替代連結盒子被勾選, 所有的連結將會被替代為您預設的文字, 該項功能可以停止過長了連結導致顯示的困難. 隱藏字元將會自動隱藏文字超過預設長度的文字.";

$ns -> tablerender("聊天室", $text);
?>