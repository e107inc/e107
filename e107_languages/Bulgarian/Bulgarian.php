<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Bulgarian/Bulgarian.php,v $
|     $Revision: 1.5 $
|     $Date: 2006-11-29 15:33:51 $
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
define("CORE_LAN6", "The flood protection on this site has been activated and you are warned that if you carry on requesting pages you could be banned.");
define("CORE_LAN7", "Core is attempting to restore prefs from automatic backup.");
define("CORE_LAN8", "Core Prefs Error");
define("CORE_LAN9", "Core could not restore from automatic backup. Execution halted.");
define("CORE_LAN10", "Намерено е повредени cookie - изход.");


define("LAN_WARNING", "Внимание!");
define("LAN_ERROR", "Грешка");
define("LAN_ANONYMOUS", "Анонимен");
?>