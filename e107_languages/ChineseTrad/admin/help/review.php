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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/review.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "評論是一個類似文章但是他會顯示他自己的選單上.<br />
 多重頁面評論分隔每一頁面使用文字 [newpage], 例如 <br /><code>Test1 [newpage] Test2</code><br /> 將會新增兩個頁面評論於 'Test1' 於頁面 1 和 'Test2' 於頁面 2.";
$ns -> tablerender("評論問題", $text);
?>