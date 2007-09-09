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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/cache.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:43 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "快取";
$text = "如果您開啟快取將會增加網站瀏覽速度並將資料庫的相關查詢減少到最低.<br /><br /><b>重要訊息! 如果您將你擁有的風格所使用快取關閉，您所作的任何變更將不會顯示出來.</b>";
$ns -> tablerender($caption, $text);
?>