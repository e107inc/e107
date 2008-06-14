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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/custommenu.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:29 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "با استفاده از این قسمت شما می توانید منو ها و صفحات دلخواه و مورد نظر خود را در سایت ایجاد کنید.<br /><br /><b>نکته های مهم</b><br />
- برای استفاده از امکانات این قسمت باید سطح دسترسی پوشه های e107_plugins/custom/ و e107_plugins/custompages/ را بر روی 777 تنظیم کنید.
<br />
- شما می توانید برای ساخت صفحات و منو های سفارش خود از دستورات HTML هم استفاده کنید
<br /><br />
<i>نام فایل منو/صفحه</i> : نام صفحه یا منوی سفارشی شما هست, منو به صورت 'name.php' در پوشه ".e_PLUGIN."custom/ ذخیره می شود.<br />
 و صفحات به صورت name.php' در پوشه ".e_PLUGIN."custompages/ ذخیره می شوند<br /><br />
<i>عنوان اصلی منو/صفحه</i>: این قسمت در قسمت عنوان منو یا صفحه نمایش داده می شود.<br /><br />
<i>متن منو/صفحه</i>: نوشته ها کد ها یا تصاویری که بدنه اصلی و ظاهر منو یا صفحه مورد نظر شما را تشکیل می دهند.";

$ns -> tablerender(CUSLAN_18, $text);
?>