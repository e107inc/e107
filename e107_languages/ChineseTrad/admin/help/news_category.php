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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/news_category.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:47 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "您可以將您的新聞分配在不同的分區, 並允許訪客顯示僅該分區的新聞. <br /><br />上傳您的新圖標於 ".e_THEME."-yourtheme-/images/ 或 themes/shared/newsicons/.";
$ns -> tablerender("新聞分區問題", $text);
?>