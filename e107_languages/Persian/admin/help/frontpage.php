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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/frontpage.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:29 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "راهنمای صفحه اول سایت";
$text = "در این صفحه شما می توانید انتخاب کنید که چه قسمتی در صفحه اول سایت شما به نمایش درآید, به صورت پیش فرض قسمت اخبار به عنوان صفحه اول قرار داده می شود . شما همچنین می توانید این صفحه را به صورت 'صفحه موقت' تعریف کنید, صفحه ای که برای کاربرانی که برای دفعه اول از سایت شما بازدید می کنند نمایش داده شود.";
$ns -> tablerender($caption, $text);
?>