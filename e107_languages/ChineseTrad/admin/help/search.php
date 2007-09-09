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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/search.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "假如您的 MySql 伺服器支援您使用 
 MySql 排列模式（ 該功能比 PHP 排列模式較快）. 請前往基本設定.<br /><br />
如果您的網站有使用象形文字語言例如中文和日文 
您必須使用 PHP sort 排列模式並切換部份字元吻合功能關閉.";

$ns -> tablerender("搜尋問題", $text);
?>