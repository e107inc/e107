<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

//define("ADSTAT_ON", "On");
//define("ADSTAT_OFF", "Off");
define("ADSTAT_LAN_1", "This plugin will log all visits to your site, and build detailed statistic screens based on the information gathered.");
define("ADSTAT_LAN_2", "The statistics logger has been successfully installed. To activate, please go to the config screen and click Activate.<br /><b>You must set the permissions of the e107_plugins/log/logs folder to 777 (chmod 777)</b>");
define("ADSTAT_LAN_3", "Statistic Logging");
define("ADSTAT_LAN_4", "Activate statistic logging");
define("ADSTAT_LAN_5", "Statistic types");
define("ADSTAT_LAN_6", "Browsers");
define("ADSTAT_LAN_7", "Operating Systems");
define("ADSTAT_LAN_8", "Screen resolutions / depths");
define("ADSTAT_LAN_9", "Countries/domains visited from");
define("ADSTAT_LAN_10", "Referrers");
define("ADSTAT_LAN_11", "Search queries");
define("ADSTAT_LAN_12", "Reset stats");
define("ADSTAT_LAN_13", "This will erase the all-time stats - careful! Deletes stats to the end of yesterday. There is a separate menu option to delete selected historical data"); // TODO: Condense this help field (minimize)
define("ADSTAT_LAN_14", "Page counts");
//define("ADSTAT_LAN_15", "Update Statistic Settings");
define("ADSTAT_LAN_16", "Site Statistic Settings");
//define("ADSTAT_LAN_17", "Statistic settings updated");
define("ADSTAT_LAN_18", "Allow access to main statistics page to ...");
define("ADSTAT_LAN_19", "Recent visitors");
define("ADSTAT_LAN_20", "Count admin visits");
define("ADSTAT_LAN_21", "Maximum records to display on stats page");
define("ADSTAT_LAN_22", "Run update routine");
define("ADSTAT_LAN_23", "logs from a previous version of e107 have been detected, update them here");
define("ADSTAT_LAN_24", "Go to update script");
//define("ADSTAT_LAN_25", "Selected stats reset");
define("ADSTAT_LAN_26", "Remove page entries");
define("ADSTAT_LAN_27", "If your stats have incorrect pages, you can remove them here");
define("ADSTAT_LAN_28", "Open page");
define("ADSTAT_LAN_29", "Page Name");
define("ADSTAT_LAN_30", "Check to remove");
define("ADSTAT_LAN_31", "Remove selected pages");
define("ADSTAT_LAN_32", "Page Tidy");
// define("ADSTAT_LAN_33", "Configure Statistics Logging"); see English_global.php
// define("ADSTAT_LAN_34", "Site Stats");
define ('ADSTAT_LAN_35', 'Options');
define ('ADSTAT_LAN_36', 'Data Export');
//define ('ADSTAT_LAN_37', 'Create export file');
define ('ADSTAT_LAN_38', "You must set the e107_plugins/log/logs folder to be writable");
define ('ADSTAT_LAN_39', 'Stats Logging Functions');
define ('ADSTAT_LAN_40', 'Export log data');
define ('ADSTAT_LAN_41', 'Date selection');
define ('ADSTAT_LAN_42', 'Single Day');
define ('ADSTAT_LAN_43', 'Daily for a month');
define ('ADSTAT_LAN_44', 'Monthly for a year');
define ('ADSTAT_LAN_45', 'All-time');
define ('ADSTAT_LAN_46', 'Date:');
define ('ADSTAT_LAN_47', 'Invalid date chosen');
define ('ADSTAT_LAN_48', 'Monthly and All-time');
define ('ADSTAT_LAN_49', 'All-time Only');
define ('ADSTAT_LAN_50', 'None');
define ('ADSTAT_LAN_51', 'Output Data');
define ('ADSTAT_LAN_52', 'Page Data');
define ('ADSTAT_LAN_53', 'No selection possible');
define ('ADSTAT_LAN_54', 'Invalid type selection');
define ('ADSTAT_LAN_55', 'Single quote');
define ('ADSTAT_LAN_56', 'Double quote');
define ('ADSTAT_LAN_57', 'Comma');
define ('ADSTAT_LAN_58', 'Pipe (|)');
define ('ADSTAT_LAN_59', 'CSV separator, quotes');
define ('ADSTAT_LAN_60', 'Strip site address from URLs');
define ('ADSTAT_LAN_61', '(if checked, just gives page reference)');
define ('ADSTAT_LAN_62', 'All-time (detailed)');
define ('ADSTAT_LAN_63', 'Available Datasets');
define ('ADSTAT_LAN_64', 'Database records found:');
define ('ADSTAT_LAN_65', 'DB filter string:');
define ('ADSTAT_LAN_66', 'Show Datasets');
define ('ADSTAT_LAN_67', 'Generate a CSV (Comma Separated Variable) file of historical statistics which meets the specified criteria');
define ('ADSTAT_LAN_68', 'Show the statistics database entries which actually exist and meet the selection criteria');
define ('ADSTAT_LAN_69', 'Delete historical data');
define ('ADSTAT_LAN_70', 'Delete data older than:');
define ('ADSTAT_LAN_71', 'Delete Data');
define ('ADSTAT_LAN_72', 'Confirm deletion of data older than first day of:');
//define ('ADSTAT_LAN_73', 'Confirm');
define ('ADSTAT_LAN_74', '(List of data entries which will be deleted below)');
define ('ADSTAT_LAN_75', 'Records for deletion');
define ('ADSTAT_LAN_76', 'Caution! Once deleted, the data cannot be recovered. Backup or export your database first');
define ('ADSTAT_LAN_77', 'Records deleted:');
define ('ADSTAT_LAN_78', 'Show previous month as well as current month for non-page access stats');
define ('ADSTAT_LAN_79', 'Only used if monthly stats collected');
define ('ADSTAT_LAN_80', 'The following pages were deleted:');
define ('ADSTAT_LAN_81', 'Statistics cleared:');
define ('ADSTAT_LAN_82', 'Following values now set:');
define ('ADSTAT_LAN_83', 'Following log ID entries removed:');
define ('ADSTAT_LAN_84', 'This proceedure will overwrite the log statistic summaries in your database. (raw log files are left unchanged) Once replaced, the data cannot be recovered. Please backup or export your database first');
define ('ADSTAT_LAN_85', '[x] log files have been found. Click the button below to process these files.');
define ('ADSTAT_LAN_86', 'Total Hits');
define ('ADSTAT_LAN_87', 'Rebuild Statistic Summaries');
define ('ADSTAT_LAN_88', 'Rebuild Stats'); 
define ('ADSTAT_LAN_89', 'Rebuild');  
define ('ADSTAT_LAN_90', "Data saved to database with id: [x]");
define ('ADSTAT_LAN_91', "Couldn't save data to database with id: [x]");

?>
