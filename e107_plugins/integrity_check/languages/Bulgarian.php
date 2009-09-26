<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File
|     Bulgarian Language Pack for e107 Version 0.7
|     Copyright T? 2005 - Bulgarian e107
|     http://www.e107bg.org
|     Encoding: utf-8
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Bulgarian.php,v $
|     $Revision: 1.7 $
|     $Date: 2009-09-26 15:22:17 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
*/

define("Integ_01", "Записването е успешно");
define("Integ_02", "Записването е неуспешно");
define("Integ_03", "Липсващи Файлове:");
define("Integ_04", "CRC-Грешки:");
define("Integ_05", "Невъзможно е отварянето на Файл...");
define("Integ_06", "Проверка надеждността на файл");
define("Integ_07", "Няма налични файлове");
define("Integ_08", "Провери наджедността");
define("Integ_09", "Създай sfv-файл");
define("Integ_10", "Маркираната директория <u>няма</u> да бъде записана в crc-файл.");
define("Integ_11", "Име на Файл:");
define("Integ_12", "Създай sfv файл");
define("Integ_13", "Проверка на наджедността");
define("Integ_14", "Създаването на SFV е невъзможно - директория ".e_PLUGIN."integrity_check/<b>{output}</b> няма права за писане. Моля изпълни chmod 777 за рази директория!");
define("Integ_15", "Всички файлове бяха проверени и са o.k.!");
define("Integ_16", "Няма core-crc-файл");
define("Integ_17", "Няма модул-crc-файлове");
define("Integ_18", "Създай Модул-CRC-Файл");
define("Integ_19", "Core-Checksum-Файлове");
define("Integ_20", "Модул-Checksum-Файлове");
define("Integ_21", "Изберете модул, за който искате да създардете crc-файл.");
define("Integ_22", "Използвай gzip");
define("Integ_23", "Провери само инсталираните теми");
define("Integ_24", "Администрация - Начало");
define("Integ_25", "Излез от Администрация");
define("Integ_26", "Зареди сайта с нормален header");
define("Integ_27", "USE THE FILE INSPECTOR FOR CHECKING CORE FILES");

// define("Integ_29", "<br /><br /><b>*<u>CRC-ERRORS:</u></b><br />These are checksum errors and there are two possible reasons for this:<br />-You changed something within the mentioned file, so it isn't longer the same as the original.<br />-The mentioned file is corrupt, you should reupload it!");
// language file should contain NO html.

define("Integ_30", "За по-малко натоварване на процесора на сървъра, можете да направите проверката в 1 до 10 стъпки.");
define("Integ_31", "Стъпки: ");
define("Integ_32", "Намерен е файл с име <b>log_crc.txt</b> във вашата crc папка. Моля изтрийте го! (Или презаредете страницата)");
define("Integ_33", "Намерен е файл с име <b>log_miss.txt</b> във вашата crc папка. Моля изтрийте го! (Или презаредете страницата)");
define("Integ_34", "Crc папката няма права за писане!");
define("Integ_35", "Поради следната(-ите) причина(и) ви е позволено да изберете само <b>една</b> стъпка:");
define("Integ_36", "Кликнете тук ако не искате да чакате 5 секунди за следващата стъпка:");
define("Integ_37", "Кликни ме");
define("Integ_38", "Други <u><i>{counts}</i></u> редове за четене...");
define("Integ_39", "Моля изтрийте файл:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />Той никога не е бил предвиден за публикуване...");

?>