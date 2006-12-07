<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Hungarian/admin/help/news_category.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-12-07 21:49:18 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Elkülönítheted a híreket különböző kategóriákba, engedélyezheted a látogatóknak csak azon a kategóriának a híreinek megjelenítését. <br /><br />Töltsd fel a hír ikonokat vagy a ".e_THEME."-yourtheme-/images/ vagy a themes/shared/newsicons/ mappába.";
$ns -> tablerender("Hír Kategória - Súgó", $text);
?>
