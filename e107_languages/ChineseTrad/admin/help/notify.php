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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/notify.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "當e107網站遇到事件發生時寄信通知.<br /><br />
例如：設定 'IP 封鎖於flooding攻擊網站' 通知會員等級 '管理員' 則當遇到floodind攻擊時
會寄信通管理員.<br /><br />
另外一個例子, 設定 '新聞發表於管理員' 設定會員等級為 '會員' 當您發表新聞時，
您所有會員將會收到此信件.<br /><br />
如果您喜歡電子信件通知選擇一個電子信箱- 選擇 'Email' 選項和
輸入一個電子信箱的欄位.";

$ns -> tablerender("寄信通知", $text);
?>