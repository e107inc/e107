<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/lan_cron.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined("PAGE_NAME")) { define("PAGE_NAME", "Schedule Tasks"); }

// Menu
define("LAN_CRON_M_01", "Manage"); 
define("LAN_CRON_M_02", "Refresh"); 

// Table heading
define("LAN_CRON_1", "Name");
define("LAN_CRON_2", "Function");
define("LAN_CRON_3", "Tab");
define("LAN_CRON_4", "Last-run");
define("LAN_CRON_5", "Active");

// Default crons
define("LAN_CRON_01_1", "Test Email");
define("LAN_CRON_01_2", "Send a test email to [eml]."); // [eml] is automatically replaced by head admin e-mail address.
define("LAN_CRON_01_3", "Recommended to test the scheduling system.");

define("LAN_CRON_02_1", "Mail Queue");
define("LAN_CRON_02_2", "Process mail queue.");

define("LAN_CRON_03_1", "Mail Bounce Check");
define("LAN_CRON_03_2", "'Check for bounced emails.");

define("LAN_CRON_04_1", "Ban Retrigger Check");
define("LAN_CRON_04_2", "Process bounce retriggers.");
define("LAN_CRON_04_3", "Only needed if retriggering of bans enabled.");

define("LAN_CRON_05_1", "Database Backup");
define("LAN_CRON_05_2", "Backup the system database to");


// Error and info messages
define("LAN_CRON_6", "Couldn't Import Prefs");
define("LAN_CRON_7", "Couldn't Import Timing Settings");
define("LAN_CRON_8", "Imported Timing Settings for");
define("LAN_CRON_9", "Imported");

define("LAN_CRON_10", "[x] minutes and [y] seconds ago."); // [x] and [y] are automatically replaced. 
define("LAN_CRON_11", "[y] seconds ago.");

define("LAN_CRON_12", "Active Crons");
define("LAN_CRON_13", "Last cron refresh");
define("LAN_CRON_14", "Please be sure cron.php is executable.");
define("LAN_CRON_15", "Please CHMOD /cron.php to 755.");

define("LAN_CRON_16", "Use the following Cron Command");
define("LAN_CRON_17", "Using your server control panel (eg. cPanel, DirectAdmin, Plesk etc.) please create a crontab to run this command on your server every minute.");

// leave some room for additions/changes

define("LAN_CRON_30", "Every Minute");
define("LAN_CRON_31", "Every Other Minute");
define("LAN_CRON_32", "Every 5 Minutes");
define("LAN_CRON_33", "Every 10 minutes");
define("LAN_CRON_34", "Every 15 minutes");
define("LAN_CRON_35", "Every 30 minutes");

define("LAN_CRON_36", "Every Hour");
define("LAN_CRON_37", "Every Other Hour");
define("LAN_CRON_38", "Every 3 Hours");
define("LAN_CRON_39", "Every 6 Hours");

define("LAN_CRON_40", "Every Day");
define("LAN_CRON_41", "Every Month");
define("LAN_CRON_42", "Every Week Day");

define("LAN_CRON_50", "Minute(s):");
define("LAN_CRON_51", "Hour(s):");
define("LAN_CRON_52", "Day(s):");
define("LAN_CRON_53", "Month(s):");
define("LAN_CRON_54", "Weekday(s):");
define("LAN_CRON_55", "Active");

define("LAN_CRON_BACKUP", "Backup");
define("LAN_CRON_LOGGING", "Logging");
define("LAN_CRON_RUNNING", "Running")

?>