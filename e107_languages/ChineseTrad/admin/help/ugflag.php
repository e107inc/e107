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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/ugflag.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:49 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "如果您要升級 e107 或是暫時關閉網站請選擇欄位重要維護，這樣您的訪客將會被轉向到解釋網站關閉維修頁面. 完成後您可以取消勾選該欄位並使網站恢復正常.";

$ns -> tablerender("重要維護", $text);
?>