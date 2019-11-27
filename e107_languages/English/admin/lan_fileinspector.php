<?php
/*
 * Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Language File
 *
*/

define("FC_LAN_1", "File Inspector");
//define("FC_LAN_2", "Scan Options");//LAN_OPTIONS
//define("FC_LAN_3", "Show");
//define("FC_LAN_4", "All");//LAN_ALL
define("FC_LAN_5", "Core Files");
define("FC_LAN_6", "Integrity Fail Only");
define("FC_LAN_7", "Non Core Files");
define("FC_LAN_8", "Check Integrity Of Core Files");
//define("FC_LAN_9", "On");//LAN_YES - consistent with prefs
//define("FC_LAN_10", "Off");//LAN_NO
define("FC_LAN_11", "Scan Now");
//define("FC_LAN_12", "None");//LAN_NONE
define("FC_LAN_13", "Missing Core Files");
define("FC_LAN_14", "Display results as");
define("FC_LAN_15", "Directory Tree");
//define("FC_LAN_16", "List");//LAN_LIST
define("FC_LAN_17", "String Matching");
define("FC_LAN_18", "Regular expression");
define("FC_LAN_19", "Show line numbers");
define("FC_LAN_20", "Show matched lines");
define("FC_LAN_21", "Old Core Files");
//define("FC_LAN_22", "Highlight matched text");//not used
define("FC_LAN_23", "Exclude Language-Files");
define("FC_LAN_24", "Core Folder (Integrity Pass)");
define("FC_LAN_25", "Core Folder (Integrity Fail)");
define("FC_LAN_26", "Core Folder (Missing)");
define("FC_LAN_27", "Core Folder (Old)");
define("FC_LAN_28", "Non-core Folder");
define("FC_LAN_29", "Core File (Integrity Pass)");
define("FC_LAN_30", "Core File (Unchecked)");
define("FC_LAN_31", "Core File (Integrity Fail)");
define("FC_LAN_32", "Core File (Missing)");
define("FC_LAN_33", "Core File (Old)");
define("FC_LAN_34", "Core File (Incalculable)");
define("FC_LAN_35", "Known Security issue");
define("FC_LAN_36", "Non-core file");
define("FC_LAN_37", "File Key");

define("FR_LAN_1", "Scanning");
define("FR_LAN_2", "Scan Results");
define("FR_LAN_3", "Overview");
define("FR_LAN_4", "Core files");
define("FR_LAN_5", "Non core files");
define("FR_LAN_6", "Total files");
define("FR_LAN_7", "Integrity Check");
define("FR_LAN_8", "Core files passed");
define("FR_LAN_9", "Core files failed");
define("FR_LAN_10", "Possible reasons for files to fail");
define("FR_LAN_11", "The file is corrupted");
define("FR_LAN_12", "This could be for a number of reasons such as the file being corrupted in the zip, got corrupted during 
extraction or got corrupted during file upload via FTP. You should try re-uploading the file to your server 
and re-run the scan to see if this resolves the error.");
define("FR_LAN_13", "The file is out of date");
define("FR_LAN_14", "If the file is from an older release of e107 to the version you are 
running then it will fail the integrity check. Make sure you have uploaded the newest version of this file.");
define("FR_LAN_15", "The file has been edited");
define("FR_LAN_16", "If you have edited this file in any way it will not pass the integrity check. If you
intentionally edited this file then you need not worry and can ignore this integrity check fail. If however
the file was edited by someone else without authorisation you may want to re-upload the proper version of
this file from the e107 zip.");
define("FR_LAN_17", "If you are an SVN user");
define("FR_LAN_18", "If you run checkouts of the e107 SVN on your site instead of the official e107 stable 
releases, then you will discover files have failed integrity check because they have been edited by a dev 
after the latest core image snapshot was created.");
define("FR_LAN_19", "files failed");
define("FR_LAN_20", "All files passed");
//define("FR_LAN_21", "none");//NOT USED
define("FR_LAN_22", "Missing core files");
define("FR_LAN_23", "No matches found.");
define("FR_LAN_24", "Old core files");
define("FR_LAN_25", "Integrity incalculable");

define("FR_LAN_26", "Warning! Known Insecurity Detected!");
define("FR_LAN_27", "There are files on your server that are known to be exploitable and must be removed immediately.");
define("FR_LAN_28", "Known insecure files");

//define("FR_LAN_29", "Total files matched");//not used
//define("FR_LAN_30", "Total lines matched");//not used
//define("FR_LAN_31", "Missing complete plugin folder");//not used
define("FR_LAN_32", "You need to run a scan first!");

define("FS_LAN_1", "Create Snapshot");
define("FS_LAN_2", "Absolute path of root directory to create image from");
define("FS_LAN_3", "Create snapshot for plugin: (Your plugin will be listed when a writable e_inspect.php file exists in your plugins root directory.)");
define("FS_LAN_4", "Select...");
define("FS_LAN_5", "Create snapshot of current or deprecated files");
define("FS_LAN_6", "Current");
define("FS_LAN_7", "Deprecated");
define("FS_LAN_8", "Create Snapshot");
define("FS_LAN_9", "Snapshot");
define("FS_LAN_10", "Snapshot Created");
define("FS_LAN_11", "The snapshot was successfully created.");
define("FS_LAN_12", "Return To Main Page");
?>