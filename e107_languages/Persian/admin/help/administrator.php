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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/administrator.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:29 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "راهنمای قسمت مدیران";
$text = "از این صفحه برای ایجاد کردن مدیر جدید یا ویرایش مدیران و تنظیم سطح  دسترسی آن ها استفاده کنید<br /><br />
برای تعریف مدیر جدید ابتدا از قسمت کاربران سایت یک کاربر را به عنوان مدیر انتخاب کنید";
$ns -> tablerender($caption, $text);
?>