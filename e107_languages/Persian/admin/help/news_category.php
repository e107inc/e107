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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/news_category.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "شما می توانید اخبار را در شاخه های مختلف دسته بندی کنید, و اجازه بدهید کاربران این شاخه ها را ببینند. <br /><br />تصاویر اخبار را در  ".e_THEME."-yourtheme-/images/ یا themes/shared/newsicons/ آپلود کنید.";
$ns -> tablerender("راهنمای شاخه اخبار", $text);
?>