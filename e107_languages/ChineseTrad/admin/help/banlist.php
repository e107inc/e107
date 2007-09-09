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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/banlist.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:43 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "封鎖網站會員";
$text = "您可以在這邊封鎖網站會員.<br />
輸入完整的IP或是範圍. 甚至您可以輸入電子信箱停止該會員註冊.<br /><br />
<b>IP 位址封鎖:</b><br />
輸入 IP 位址 123.123.123.123 將會禁止從該IP位址來的會員瀏覽您的網站.<br />
輸入 IP 位址 123.123.123.* 將會禁止從這IP位址範圍來的會員瀏覽您的網站.<br /><br />
<b>電子信箱封鎖</b><br />
輸入電子信箱 foo@bar.com 將會禁止任何人使用該電子信箱註冊.<br />
輸入電子信箱 *@bar.com 將會禁止任何人使用相關的電子信箱註冊.<br /><br />
<b>使用會員名稱封鎖</b><br />
此像操作僅限於管理控制台.";
$ns -> tablerender($caption, $text);
?>