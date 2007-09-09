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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/list_menu_conf.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:46 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "在這邊您可以設定 3 個選單<br>
<b> 新文章選單</b> <br>
假設輸入數字 '5' 於第一個欄位將會顯示出 5 篇文章, 保留空白將會看到全部,您設定的文章剩餘連結標題為第二欄位, 當您保留於最新選項空白他將部會新增連結, 例如: '全部文章'<br>
<b> 評論/討論區選單</b> <br>
預設的評論數目為 5, 預設的字元為 10000. 如果字太多的話將會截斷並顯示字尾設定, 通常選擇為 '...', 假如您要看到大概的內容請選擇原始主題.<br>

";
$ns -> tablerender("選單設定問題", $text);
?>
