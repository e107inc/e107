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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/userclass2.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "راهنمای رتبه کاربران";
$text = "شما می توانید در این قسمت رتبه جدید اضافه کنید یا رتبه های موجود را ویرایش/حذف کنید <br />این ابزاری مفید برای محدود کردن کاربران از قسمتی از سایت شماست. برای مثال, شما رتبه ای با نام 'انجمن' ایجاد کنید, سپس یک انجمن ایجاد کنید و فقط اجازه استفاده برای آن را به رتبه انجمن بدهید .";
$ns -> tablerender($caption, $text);
?>