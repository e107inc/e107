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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/list_menu_conf.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "در این قسمت شما می توانید 3 منو را پیکربندی کنید<br>
<b>منوی مقالات جدید</b> <br>
یک شماره وارد کنید برای مثال  '5' در این صورت 5 مقاله جدید نشان داده می شوند, برای نمایش همه خالی رها کنید, <br>
<b>منوی  نظرات/انجمن</b> <br>
شماره پیش فرض نظرات 5, شماره پیش فرض تعداد حروف نمایشی 10000. .<br>

";
$ns -> tablerender("راهنمای پیکربندی منو", $text);
?>
