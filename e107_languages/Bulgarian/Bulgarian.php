<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File
|     Bulgarian Language Pack for e107 Version 0.7
|     Copyright © 2005 - Bulgarian e107
|     http://www.e107bg.org
|     Encoding: utf-8
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Bulgarian/Bulgarian.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-07-19 19:46:14 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
//Bulgarian locale fix
if (!setlocale(LC_ALL, 'bg_BG.utf-8')) {
   setlocale(LC_ALL, "bgr_BGR.utf-8");
}

define("CORE_LC", '');
define("CORE_LC2", '');
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","Грешка : липсваща тема.\\n\\nСмени използваната тема от настройките (Администрация) или качи файловете на настоящата тема на сървъра.");

//v.616
define("CORE_LAN2"," \\1 написа:");// "\\1" represents the username.
define("CORE_LAN3","прикачането на файл е забранено");
?>