<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Bulgarian/Bulgarian.php,v $
|     $Revision: 1.7 $
|     $Date: 2007-03-04 22:09:38 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
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

// v0.7.6
define("CORE_LAN6", "Защитата на сайта ще бъде включена и вие ще бъдете блокиран за тази страница ако продължите заявките към сървъра.");
define("CORE_LAN7", "Ядрото ще се опита да възтанови префикса от автоматичния резервен архив.");
define("CORE_LAN8", "Грешка в префикса на ядрото");
define("CORE_LAN9", "Ядрото не може да възтанови резервния архив. Операцията е спряна.");
define("CORE_LAN10", "Намерено е повредена бисквитка(и) - изход.");


define("LAN_WARNING", "Внимание!");
define("LAN_ERROR", "Грешка");
define("LAN_ANONYMOUS", "Анонимен");
?>