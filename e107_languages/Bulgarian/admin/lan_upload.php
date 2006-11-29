<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Bulgarian/admin/lan_upload.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-11-29 15:33:56 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
define("UPLLAN_1", "Добавения файл е премахнат от списъка.");
define("UPLLAN_2", "Настройките са запазени в базата данни");
define("UPLLAN_3", "ID на добавения файл");

define("UPLLAN_5", "Добавил");
define("UPLLAN_6", "Имейл");
define("UPLLAN_7", "Сайт");
define("UPLLAN_8", "Име на файла");

define("UPLLAN_9", "Версия");
define("UPLLAN_10", "Файл");
define("UPLLAN_11", "Големина на файла");
define("UPLLAN_12", "Снимка за преглед");
define("UPLLAN_13", "Описание");
define("UPLLAN_14", "Демо изглед");

define("UPLLAN_16", "копране в новините");
define("UPLLAN_17", "премахване от листа на добавените");
define("UPLLAN_18", "Виж подробности");
define("UPLLAN_19", "В момента няма непрегледани добавени файлове");
define("UPLLAN_20", "В момента има");
define("UPLLAN_21", "непрегледани добавени файлове");
define("UPLLAN_22", "ID");
define("UPLLAN_23", "Име");
define("UPLLAN_24", "Вид на файла");
define("UPLLAN_25", "Разрешаване на публично добавяне?");
define("UPLLAN_26", "Няма да могат да се добавят файлове от потребителите ако е забранено");

define("UPLLAN_29", "Storage type");
define("UPLLAN_30", "Choose how to store uploaded files, either as normal files on server or as binary info in database<br /><b>Note</b> binary is only suitable for smaller files under approximately 500kb");
define("UPLLAN_31", "Flatfile");
define("UPLLAN_32", "Binary");
define("UPLLAN_33", "Максимална големина на файловете");
define("UPLLAN_34", "Maximum upload size in bytes - leave blank to conform to php.ini setting ( php.ini setting is");
define("UPLLAN_35", "Allowed file types");
define("UPLLAN_36", "Please enter one type per line");
define("UPLLAN_37", "Permission");
define("UPLLAN_38", "Select to allow only certain users to upload");
define("UPLLAN_39", "Submit");

define("UPLLAN_41", "Please note - file uploads are disabled from your php.ini, it will not be possible to upload files until you set it to On.");

define("UPLLAN_42", "Actions");
define("UPLLAN_43", "Uploads");
define("UPLLAN_44", "Upload");

define("UPLLAN_45", "Are you sure you want to delete the following file...");

define("UPLAN_COPYTODLM", "copy to download manager");
define("UPLAN_IS", "is ");
define("UPLAN_ARE", "are ");
define("UPLAN_COPYTODLS", "Copy to Downloads");

define("UPLLAN_48", "For security reasons allowed file types has been moved out of the database into a 
flatfile located in your admin directory. To use, rename the file e107_admin/filetypes_.php to e107_admin/filetypes.php 
and add a comma delimited list of file type extensions to it. You should not allow the upload of .html, .txt, etc., as an attacker may upload a file of this type which includes malicious javascript. You should also, of course, not allow 
the upload of .php files or any other type of executable script.");
?>
