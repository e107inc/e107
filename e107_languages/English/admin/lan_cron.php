<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

if (!defined("PAGE_NAME")) { define("PAGE_NAME", "Schedule Tasks"); }

// Menu
define("LAN_CRON_M_02", "Refresh");

// Table heading

define("LAN_CRON_2", "Function");
define("LAN_CRON_3", "Tab");
define("LAN_CRON_4", "Last-run");

// Default crons
define("LAN_CRON_01_1", "Test Email");
define("LAN_CRON_01_2", "Send a test email to [eml]."); // [eml] is automatically replaced by head admin e-mail address.
define("LAN_CRON_01_3", "Recommended to test the scheduling system.");

define("LAN_CRON_02_1", "Mail Queue");
define("LAN_CRON_02_2", "Process mail queue.");

define("LAN_CRON_03_1", "Mail Bounce Check");
define("LAN_CRON_03_2", "Check for bounced emails.");

define("LAN_CRON_04_1", "Ban Retrigger Check");
define("LAN_CRON_04_2", "Process bounce retriggers.");
define("LAN_CRON_04_3", "Only needed if retriggering of bans enabled.");

define("LAN_CRON_05_1", "Database Backup");
define("LAN_CRON_05_2", "Backup the system database to");

define('LAN_CRON_06_1', "Process Ban Trigger");

// Error and info messages
define("LAN_CRON_6", "Couldn't Import Prefs");
define("LAN_CRON_7", "Couldn't Import Timing Settings");
define("LAN_CRON_8", "Imported Timing Settings for");

define("LAN_CRON_9", "[x] minutes and [y] seconds ago."); // [x] and [y] are automatically replaced. 
define("LAN_CRON_10", "[y] seconds ago.");

define("LAN_CRON_11", "Active Crons");
define("LAN_CRON_12", "Last cron refresh");
define("LAN_CRON_13", "Please be sure cron.php is executable.");
define("LAN_CRON_14", "Please CHMOD /cron.php to 755.");

define("LAN_CRON_15", "Use the following Cron Command");
define("LAN_CRON_16", "Using your server control panel (eg. cPanel, DirectAdmin, Plesk etc.) please create a crontab to run this command on your server every minute.");

// leave some room for additions/changes

// Info for checkCoreUpdate cron
define("LAN_CRON_20_1", "Check for e107 Update");
define("LAN_CRON_20_2", "Check e107.org for Core updates"); // [eml] is automatically replaced by head admin e-mail address.
define("LAN_CRON_20_3", "Recommended to keep system up to date.");
define("LAN_CRON_20_4", "Update this Git repository");
define("LAN_CRON_20_5", "Update this e107 installation with the very latest files from github.");
define("LAN_CRON_20_6", "Recommended for developers only.");
//define("LAN_CRON_20_7", "Warning!");//LAN_WARNING
define("LAN_CRON_20_8", "May cause site instability!");

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
define("LAN_CRON_55", "Database Backup Failed");
define("LAN_CRON_56", "Database Backup Complete");

define("LAN_CRON_60", "Go to cPanel");
define("LAN_CRON_61", "Generate new cron token");
define("LAN_CRON_62", "Executing config function [b][x][/b]");
define("LAN_CRON_63", "Config function [b][x][/b] NOT found.");
define("LAN_CRON_64", "An administrator can automate tasks using e107 Schedule Tasks. [br]
In the Manage Tab, you can edit, delete and run tasks. [br]
When you edit a task you can set the minutes, hours, days, month or day of the week you want the task to run. Use * to run for each period. Use the Active property to Enabled the Task.[br]
Note: You are advised not to delete standard jobs.[br]
");

define("LAN_CRON_BACKUP", "Backup");
define("LAN_CRON_LOGGING", "Logging");
define("LAN_CRON_RUNNING", "Running");

define("LAN_CRON_65", "Update git theme repository");
define("LAN_CRON_66", "No git repo found");
define("LAN_CRON_67", "No git repo found in theme folder");
