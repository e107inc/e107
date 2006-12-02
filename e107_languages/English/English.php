<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/English.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:38 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
setlocale(LC_ALL, 'en');
define("CORE_LC", 'en');
define("CORE_LC2", 'gb');
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","Error : theme is missing.\\n\\nChange the used themes in your preferences (admin area) or upload files of the current theme on the server.");

//v.616
define("CORE_LAN2"," \\1 wrote:");// "\\1" represents the username.
define("CORE_LAN3","file attachment disabled");

//v0.7+
define("CORE_LAN4", "Please delete install.php from your server");
define("CORE_LAN5", "if you do not there is a potential security risk to your website");

// v0.7.6
define("CORE_LAN6", "The flood protection on this site has been activated and you are warned that if you carry on requesting pages you could be banned.");
define("CORE_LAN7", "Core is attempting to restore prefs from automatic backup.");
define("CORE_LAN8", "Core Prefs Error");
define("CORE_LAN9", "Core could not restore from automatic backup. Execution halted.");
define("CORE_LAN10", "Corrupted cookie detected - logged out.");


define("LAN_WARNING", "Warning!");
define("LAN_ERROR", "Error");
define("LAN_ANONYMOUS", "Anonymous");

?>