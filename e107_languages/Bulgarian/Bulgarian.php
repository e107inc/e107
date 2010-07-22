<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvsroot/e107/e107_0.7/e107_languages/English/English.php,v $
|     $Revision: 83 $
|     $Date: 2006-11-27 14:47:49 +0200 (Mon, 27 Nov 2006) $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
setlocale(LC_ALL, 'bg_BG.utf8', 'bg_BG.UTF-8', 'bg_bg.utf8', 'bg');
define("CORE_LC", 'bg');
define("CORE_LC2", 'BG');

define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","Грешка : липсваща тема.\\n\\nСмени използваната тема от настройките (Администрация) или качи файловете на настоящата тема на сървъра.");

//v.616
//define("CORE_LAN2"," \\1 написа:");// "\\1" represents the username.
//define("CORE_LAN3","прикачането на файл е забранено");

//v0.7+
define("CORE_LAN4", "Моля изтрийте install.php от вашия сървър");
define("CORE_LAN5", "Ако не го направите има потенциален риск за сигурността на вашата уеб страница");

// v0.7.6
define("CORE_LAN6", "Защитата на сайта ще бъде включена и вие ще бъдете блокиран за тази страница ако продължите заявките към сървъра.");
define("CORE_LAN7", "Ядрото ще се опита да възтанови префикса от автоматичния резервен архив.");
define("CORE_LAN8", "Грешка в префикса на ядрото");
define("CORE_LAN9", "Ядрото не може да възстанови резервния архив. Операцията е спряна.");
define("CORE_LAN10", "Намерено е повредена бисквитка(и) - изход.");

// Footer
define("CORE_LAN11", "Време за изпълнение: ");
define("CORE_LAN12", " сек., ");
define("CORE_LAN13", " от тях за заявки. ");
define("CORE_LAN14", "");			// Used in 0.8
define("CORE_LAN15", "БД заявки: ");
define("CORE_LAN16", "Сървър памет: ");

// img.bb
define('CORE_LAN17', '[ картинките са забранени ]');
define('CORE_LAN18', 'Картинка: ');

define("CORE_LAN_B", "b");
define("CORE_LAN_KB", "kb");
define("CORE_LAN_MB", "Mb");
define("CORE_LAN_GB", "Gb");
define("CORE_LAN_TB", "Tb");

define("LAN_WARNING", "Внимание!");
define("LAN_ERROR", "Грешка");
define("LAN_ANONYMOUS", "Анонимен");
define("LAN_EMAIL_SUBS", "-имейл-");
?>