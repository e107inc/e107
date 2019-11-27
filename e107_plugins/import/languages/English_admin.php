<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/languages/English_admin_import.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */


/**
 *	e107 Import plugin
 *
 *	@package	e107_plugins
 *	@subpackage	import
 *	@version 	$Id$;
*/

if (!defined("e107_INIT")) { exit; }

//define("LAN_CONTINUE","Continue");		Now global definition

// define("LAN_CONVERT_01","Import into e107");
// define("LAN_CONVERT_02","Import data from Wordpress, Joomla, Drupal, Blogspot, RSS and other formats.");
define("LAN_CONVERT_03","You must start with a clean E107 database, other than the main admin user (ID=1)");
define("LAN_CONVERT_04","Field(s) left blank, please go back and re-enter values.");
define("LAN_CONVERT_05","Running this script may empty many of your E107 tables - make sure you have a full backup before continuing!");
define("LAN_CONVERT_06","Import data type");
define("LAN_CONVERT_07","CSV Format Specification");
define("LAN_CONVERT_08","Existing database");
define("LAN_CONVERT_09","Connection details for source database");
define("LAN_CONVERT_10","Passwords in source file are not encrypted");
define("LAN_CONVERT_11","Source data details");
define("LAN_CONVERT_12","Basic username and password");
define("LAN_CONVERT_13","CSV File");
define("LAN_CONVERT_14","Format of import database");
define("LAN_CONVERT_15","No import converters available");
define("LAN_CONVERT_16","Initial user class(es)");
define("LAN_CONVERT_17","Password in CSV file is not already encrypted");
define("LAN_CONVERT_18","(Password must be stored with MD5 encryption)");
define("LAN_CONVERT_19","Database Host");
define("LAN_CONVERT_20","Database Username");
define("LAN_CONVERT_21","Database Password");
define("LAN_CONVERT_22","Database Name");
define("LAN_CONVERT_23","Database Table Prefix");
define("LAN_CONVERT_24","Areas to import");
define("LAN_CONVERT_25","Users");
define("LAN_CONVERT_26","Forum Definitions");
define("LAN_CONVERT_27","Polls");
define("LAN_CONVERT_28","News");
define("LAN_CONVERT_29","Database import completed");
define("LAN_CONVERT_30","Import routine Information");
define("LAN_CONVERT_31","CSV data file does not exist, or invalid permissions");
define("LAN_CONVERT_32","Error reading CSV data file");
define("LAN_CONVERT_33","Error in CSV data line ");
define("LAN_CONVERT_34","Error: [x] while writing to user database, line ");
define("LAN_CONVERT_35","CSV import completed. [x] read, [y] users added, [z] errors");
define("LAN_CONVERT_36","Filename for CSV data");
define("LAN_CONVERT_37","Invalid format specification for import type");
define("LAN_CONVERT_38","Delete existing data");
define("LAN_CONVERT_39","(If you don't, the posters of imported data will be shown as 'Anonymous')");
define("LAN_CONVERT_40","Existing data deleted");
define("LAN_CONVERT_41","Required database access field is empty");
define("LAN_CONVERT_42","Error in definition file - required class does not exist");
define("LAN_CONVERT_43","Error connecting to source database");
define("LAN_CONVERT_44","Query setup error for ");
define("LAN_CONVERT_45","Cannot read import code file");
define("LAN_CONVERT_46","Error: [x] while writing to [y] database, line ");
define("LAN_CONVERT_47","Batch [w] import completed. [x] read, [y] added, [z] errors");
define("LAN_CONVERT_48","Forum posts");
define("LAN_CONVERT_49","Drupal");
define("LAN_CONVERT_50","Basic import");
define("LAN_CONVERT_51","The version of targeted Drupal.");
define("LAN_CONVERT_52","Drupal Version");
define("LAN_CONVERT_53","The base URL of Drupal website (e.g., http://mydrupalsite.com).");
define("LAN_CONVERT_54","Drupal Base URL");
define("LAN_CONVERT_55","The base URL path (i.e., directory) of the Drupal installation (e.g., /drupal/).");
define("LAN_CONVERT_56","Drupal Base Path");
define("LAN_CONVERT_57", "No error");
define("LAN_CONVERT_58", "Can't change main admin data");
define("LAN_CONVERT_59", "invalid field passed");
define("LAN_CONVERT_60", "Mandatory field not set");
define("LAN_CONVERT_61", "User already exists");
define("LAN_CONVERT_62", "Invalid characters in user or login name");
define("LAN_CONVERT_63", "Error saving extended user fields");
define("LAN_CONVERT_64", "Select");
define("LAN_CONVERT_65", "Pages");
define("LAN_CONVERT_66", "Page Chapters");
define("LAN_CONVERT_67", "Links");
define("LAN_CONVERT_68", "Media");
define("LAN_CONVERT_69", "Forum");
define("LAN_CONVERT_70", "Forum Topics/Threads");
define("LAN_CONVERT_71", "Forum Posts");
define("LAN_CONVERT_72", "Forum Track");
define("LAN_CONVERT_73", "Userclasses");
define("LAN_CONVERT_74", "News Categories");
