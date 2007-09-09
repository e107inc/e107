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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/links.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:46 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "輸入所有的網站連結. 連結將會新增顯示於主要的選單, 如果有其他的連結請使用友站連結外掛.
<br />
";
$ns -> tablerender("連結問題", $text);
?>