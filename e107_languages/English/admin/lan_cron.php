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
define("LAN_CRON_M_SETUP", "Setup");

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

define("LAN_CRON_61", "Generate new cron token");
define("LAN_CRON_62", "Executing config function [b][x][/b]");
define("LAN_CRON_63", "Config function [b][x][/b] NOT found.");
define("LAN_CRON_64", "An administrator can automate tasks using e107 Schedule Tasks.[br]
Nothing here runs until your server calls [b]cron.php[/b] once a minute. The Setup tab shows how to arrange that, and gives you the command to copy.[br]
In the Manage tab you can edit, delete and run tasks.[br]
When you edit a task you can set the minutes, hours, days, month or day of the week on which it runs. Use * for every period, and the Active property to enable the task.[br]

Note: You are advised not to delete the standard tasks.[br]");

define("LAN_CRON_BACKUP", "Backup");
define("LAN_CRON_LOGGING", "Logging");
define("LAN_CRON_RUNNING", "Running");

define("LAN_CRON_65", "Update git theme repository");
define("LAN_CRON_66", "No git repo found");
define("LAN_CRON_67", "No git repo found in theme folder");

define("LAN_CRON_SETUP_INTRO", "Your server has to call [b]cron.php[/b] once a minute for scheduled tasks to run. Pick one of the options below, copy what it shows into your server's scheduler, and use one option only, or tasks that are due will run twice.");
define("LAN_CRON_SETUP_HTTP_TITLE", "Web request");
define("LAN_CRON_SETUP_HTTP_WHY", "Your scheduler fetches a URL every minute. It runs under the PHP version selected for this site, needs no file permissions, and works with control-panel cron jobs and external cron services alike.");
define("LAN_CRON_SETUP_CLI_TITLE", "PHP command line");
define("LAN_CRON_SETUP_CLI_WHY", "Your scheduler runs the PHP interpreter on cron.php. It runs under whichever PHP binary the command names, so keep the command in step with the site's PHP version.");
define("LAN_CRON_SETUP_SHEBANG_TITLE", "Shell script");
define("LAN_CRON_SETUP_SHEBANG_WHY", "Your scheduler runs cron.php directly and its first line picks whichever php is on the PATH. The file has to be executable, and cron's PATH is short, so it may find no php or the wrong one.");
define("LAN_CRON_SETUP_COMMAND_LABEL", "Command (paste into your control panel's cron job)");
define("LAN_CRON_SETUP_CRONTAB_LABEL", "Crontab line (runs every minute)");
define("LAN_CRON_SETUP_URL_LABEL", "URL (for external cron services such as cron-job.org or EasyCron)");
define("LAN_CRON_SETUP_WINDOWS_COMMAND_LABEL", "Command (for a Windows Task Scheduler action)");
define("LAN_CRON_SETUP_SCHTASKS_LABEL", "Create the task in one go (administrator command prompt)");
define("LAN_CRON_SETUP_RECOMMENDED", "Recommended");
define("LAN_CRON_SETUP_PANEL_HOWTO", "In cPanel, DirectAdmin or Plesk, open the cron jobs page and add a job that runs every minute with this command. Without a control panel, run [b]crontab -e[/b] and add the crontab line.");
define("LAN_CRON_SETUP_WGET_LABEL", "With wget instead of curl");
define("LAN_CRON_SETUP_HTTP_FALLBACK_NOTE", "If your server cannot fetch its own site URL (some hosts block that), use the PHP command line option instead.");
define("LAN_CRON_SETUP_PHP_FOUND", "PHP was found at [x].");
define("LAN_CRON_SETUP_PHP_NOT_FOUND", "No PHP binary could be verified, so the command assumes [b]php[/b] is on the PATH. Ask your host for the path to the PHP [x] command-line binary if it is not.");
define("LAN_CRON_SETUP_OPEN_BASEDIR_NOTE", "open_basedir prevented checking for PHP binaries.");
define("LAN_CRON_SETUP_EXECUTABLE", "cron.php is executable.");
define("LAN_CRON_SETUP_NOT_EXECUTABLE", "cron.php is not executable. Make it executable first:");
define("LAN_CRON_SETUP_REGENERATE_WARNING", "Generating a new token invalidates the command you have already set up. Copy the new one into your scheduler afterwards.");
define("LAN_CRON_REFUSED_SUMMARY", "[x] request(s) to cron.php have been refused since [y], the last at [z].");
define("LAN_CRON_REFUSED_LAST_FROM", "The last one came from [x].");
define("LAN_CRON_REFUSED_TOKEN_INCORRECT", "They carried a token that does not match.");
define("LAN_CRON_REFUSED_TOKEN_MISSING", "They carried no token.");
define("LAN_CRON_REFUSED_COPY_AGAIN", "Copy the command again from the [x] tab.");
define("LAN_CRON_NEVER_REPORTED", "No scheduled task has reported in yet. Follow the [x] tab to schedule cron.php on your server.");
define("LAN_CRON_LASTRUN_HTTP", "over HTTP");
define("LAN_CRON_LASTRUN_HTTP_FROM", "over HTTP from [x]");
define("LAN_CRON_LASTRUN_CLI", "from the command line");
define("LAN_CRON_SETUP_DETECTED_ENVIRONMENT", "Detected environment: [x]");
define("LAN_CRON_SETUP_OPEN_PANEL", "Open [x]");
define("LAN_CRON_SETUP_CONTROL_PANEL", "Control panel");
define("LAN_CRON_SETUP_SCHTASKS_ACCOUNT_NOTE", "Task Scheduler runs the command as the account you choose; use one that can read the site's files.");
define("LAN_CRON_SETUP_CURL_EXE_NOTE", "curl.exe ships with Windows 10 and later; on older systems use the PHP command line option.");
define("LAN_CRON_TOKEN_REGENERATED", "A new cron token has been generated. Update the command in your server's scheduler.");
