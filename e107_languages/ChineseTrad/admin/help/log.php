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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/log.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:47 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "啟動網站統計登入頁面. 他將會簡短顯示推薦連結的主要的網址, 例如： 'jalist.com' 將會替代 'http://jalist.com/links.php' ";

$ns -> tablerender("登入問題", $text);
?>