<?php
/*
+ ----------------------------------------------------------------------------+
|     Russian Language Pack for e107 0.7
|     $Revision: 1.6 $
|     $Date: 2006-09-14 09:25:51 $
|     $Author: verant $
+----------------------------------------------------------------------------+
*/

setlocale(LC_ALL, 'ru_RU.utf8'); //// Варианты: 'ru', 'ru_RU.UTF-8'
define("CORE_LC", 'ru');
define("CORE_LC2", 'rus'); // Варианты: 'ru'
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","Ошибка : тема отсутствует.\\n\\nЗамените используемую тему в ваших настройках (Админцентре) или загрузите на сервер файлы текущей темы.");

//v.616
define("CORE_LAN2"," \\1 написал:");// "\\1" represents the username.
define("CORE_LAN3","прикрепление файлов отключено");  //file attachment disabled

//v0.7+
define("CORE_LAN4", "Пожалуйста, удалите install.php с вашего сервера");
define("CORE_LAN5", "если вы этого не сделаете, существует потенциальный риск безопасности для вашего вебсайта");
?>