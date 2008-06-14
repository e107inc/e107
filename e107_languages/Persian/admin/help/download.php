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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/download.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:29 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "فایل های خود را در پوشه   ".e_FILE."downloads , تصاویر خود را در پوشه ".e_FILE."downloadimages و تصاویر بند انگشتی خود را در پوشه  ".e_FILE."downloadthumbs آپلود کنید.
<br /><br />
برای ثبت یک دانلود, اول یک شاخه اصلی ایجاد کنید, سپس در آن شاخه اصلی یک شاحه ایجاد کنید, سپس شما می توانید فایل ها را برای دانلود قرار دهید .";
$ns -> tablerender("راهنمایی قسمت دانلود", $text);
?>