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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/newsfeed.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:47 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "您可以放置其他網站上的 RSS 並顯示於您的網站上.<br />請輸入完整連結(ie http://e107.org/news.xml). 您也可以新增您自己喜歡的圖片取得原有了. 您可以選擇啟動或是關閉啟動.<br /><br />想要觀看您網站上頭條, 請確認 headlines_menu 是啟動的.";

$ns -> tablerender("網站頭條", $text);
?>