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
|     $Revision: 1.4 $
|     $Date: 2006-11-28 21:46:22 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
/*
if (!setlocale(LC_ALL, 'bgr_BGR.utf8')) {
   setlocale(LC_ALL, 'bgr_BGR');
} */
setlocale(LC_ALL, 'bg_BG.utf8');
define("CORE_LC", 'bg');
define("CORE_LC2", 'BG');
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","Грешка : липсваща тема.\\n\\nСмени използваната тема от настройките (Администрация) или качи файловете на настоящата тема на сървъра.");

//v.616
define("CORE_LAN2"," \\1 написа:");// "\\1" represents the username.
define("CORE_LAN3","прикачането на файл е забранено");

//v0.7+
define("CORE_LAN4", "Моля изтрийте install.php от вашия сървър");
define("CORE_LAN5", "Ако не го направите има потенциален риск за сигурността на вашата уеб страница");
?>