<?php
/*
+ ----------------------------------------------------------------------------+
|     Russian Language Pack for e107 0.7
|     $Revision: 1.14 $
|     $Date: 2009-09-26 15:53:33 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
*/

setlocale(LC_ALL, 'ru_RU.utf8'); //// Варианты: Кодировка UTF-8: 'ru', 'ru_RU.UTF-8'. Кодировка win-1251: 'ru', 'ru_RU.cp1251'
define("CORE_LC", 'ru');
define("CORE_LC2", 'rus'); // Варианты: 'ru'
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");  // Варианты: Кодировка UTF-8: 'utf-8'. Кодировка win-1251: "windows-1251" // for a true multi-language site. :)
define("CORE_LAN1","Ошибка : тема отсутствует.\\n\\nЗамените используемую тему в ваших настройках (Админцентре) или загрузите на сервер файлы текущей темы.");

//v.616
//obsolete define("CORE_LAN2"," \\1 написал:");// "\\1" represents the username.
//obsolete define("CORE_LAN3","прикрепление файлов отключено");  //file attachment disabled

//v0.7+
define("CORE_LAN4", "Пожалуйста, удалите install.php с вашего сервера");
define("CORE_LAN5", "если вы этого не сделаете, существует потенциальный риск безопасности для вашего веб-сайта");

// v0.7.6
define("CORE_LAN6", "На этом сайте активирована защита от флуда и вы предупреждаетесь, что будете внесены в список запрещенных пользователей, в случае продолжения запроса страниц.");
define("CORE_LAN7", "Делается попытка восстановить настройки ядра из автоматически созданного архива.");
define("CORE_LAN8", "Ошибка настроек ядра");
define("CORE_LAN9", "Ядро не может быть восстановлено из автоматически созданного архива. Выполнение прервано.");
define("CORE_LAN10", "Обнаружен испорченный cookie - выполнен выход.");

// Footer
define("CORE_LAN11", "Время генерации: "); //Render time: 
define("CORE_LAN12", " сек., ");
define("CORE_LAN13", " из этого заняли запросы. "); //of that for queries. 
define("CORE_LAN14", "");			// Used in 0.8
define("CORE_LAN15", "Запросов БД: ");
define("CORE_LAN16", "Использовано памяти: ");

// img.bb
define('CORE_LAN17', '[ изображения отключены ]');
define('CORE_LAN18', 'Изображение: ');

define("CORE_LAN_B", "Б");
define("CORE_LAN_KB", "кБ");
define("CORE_LAN_MB", "МБ");
define("CORE_LAN_GB", "ГБ");
define("CORE_LAN_TB", "ТБ");

define("LAN_WARNING", "Внимание!"); //Warning!
define("LAN_ERROR", "Ошибка");

define("LAN_ANONYMOUS", "Анонимный");
define("LAN_EMAIL_SUBS", "-e-mail-"); //-email-

?>