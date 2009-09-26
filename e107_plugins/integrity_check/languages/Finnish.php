<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language Tiedosto.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Finnish.php,v $
|     $Revision: 1.5 $
|     $Date: 2009-09-26 15:20:56 $
|     $Kirjoittaja: streaky $
+----------------------------------------------------------------------------+
*/
    
define("Integ_01", "Saving successful");
define("Integ_02", "Saving failed");
define("Integ_03", "Missing Tiedostoa:");
define("Integ_04", "CRC-virhes:");
define("Integ_05", "Not able to open Tiedosto...");
define("Integ_06", "Check file-integrity");
define("Integ_07", "No files available");
define("Integ_08", "Check integrity");
define("Integ_09", "Create sfv-file");
define("Integ_10", "The selected folder will <u>not</u> be saved within the crc-file.");
define("Integ_11", "Tiedostonimi:");
define("Integ_12", "Create sfv file");
define("Integ_13", "Integrity-checking");
define("Integ_14", "SFV-Creation not possible, because the folder ".e_PLUGIN."integrity_check/<b>{output}</b> is not writable. Please chmod this folder to 777!");
define("Integ_15", "All files have been valittu ja are o.k.!");
define("Integ_16", "No core-crc-file available");
define("Integ_17", "No plugin-crc-files available");
define("Integ_18", "Create Plugin-CRC-Tiedosto");
define("Integ_19", "Core-Checksum-Tiedostoa");
define("Integ_20", "Plugin-Checksum-Tiedostoa");
define("Integ_21", "Valitse the plugin you want to create a crc-file for.");
define("Integ_22", "Use gzip");
define("Integ_23", "Only check installed themes");
define("Integ_24", "Ylläpitäjä Front Page");
define("Integ_25", "Leave Ylläpitotoiminnot");
define("Integ_26", "Load Site with normal header");
    
// define("Integ_29", "<br /><br /><b>*<u>CRC-ERRORS:</u></b><br />These are checksum errors ja there are two possible reasons for this:<br />-You changed something within the mentioned file, so it isn't longer the same as the original.<br />-The mentioned file is corrupt, you should reupload it!");
// language file should contain NO html. 

define("Integ_30", "For less cpu-usage , you can do the checking in 1 - 10 steps.");
define("Integ_31", "Steps: ");
define("Integ_32", "There is a file nimid <b>log_crc.txt</b> in your crc-folder. Please delete! (Or try refreshing)");
define("Integ_33", "There is a file nimid <b>log_miss.txt</b> in your crc-folder. Please delete! (Or try refreshing)");
define("Integ_34", "Your Crc-folder is not writable!");
define("Integ_35", "Because of the following reason(s) you are only allowed to select <b>one</b> step:");
define("Integ_36", "Click here, if you don't want to wait 5 Seconds till the next step:");
define("Integ_37", "Click me");
define("Integ_38", "Muut <u><i>{counts}</i></u> lines to do...");
define("Integ_39", "Please delete the file:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />It is outdated ja never meant for public release...");
    
?>
