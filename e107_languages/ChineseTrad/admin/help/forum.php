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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/forum.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "討論區 求助";
$text = "<b>基本設定</b><br />
使用該視窗新增或編輯您的討論區<br />
<br />
<b>母版面</b><br />
將會顯示位於他底下的版面, 可能會有相同的外觀或使選單給訪客瀏覽.
<br /><br />
<b>瀏覽權限</b>
<br />
您可以設定您的討論區僅接受特定的訪客. 只要您設定 '權限' 給訪客，並勾選 
特定的會員等級. 您可以分別設定版面或是分區的權限.
<br /><br />
<b>版主</b>
<br />
將要擔任版主的管理員給他討論區的權限.
<br /><br />
<b>會員等級</b>
<br />
設定會員等級. 假如圖片欄位被填上去了, 該圖片將會被使用,使用等級名稱請輸入等級名稱並確定圖片欄位是空白的.<br />此為會員需要的門檻點數這將會促使會員去贏得下一個階級會員.";
$ns -> tablerender($caption, $text);
unset($text);
?>