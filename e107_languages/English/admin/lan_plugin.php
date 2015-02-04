<?php
/*
 * Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Language File
 *
*/

// TODO LAN CLEANUP

define("EPL_ADLAN_0", "Install");
define("EPL_ADLAN_1", "Uninstall");
define("EPL_ADLAN_2", "Are you certain you want to uninstall this plugin?");
define("EPL_ADLAN_3", "Confirm uninstall");
define("EPL_ADLAN_4", "Uninstall cancelled.");
define("EPL_ADLAN_5", "The install procedure will create new preference entries.");
define("EPL_ADLAN_6", "... then click here to begin install procedure");
define("EPL_ADLAN_7", "Database tables successfully upgraded.");
define("EPL_ADLAN_8", "Preference settings successfully created.");
define("EPL_ADLAN_9", "SQL commands failed. Check to be sure all upgrade changes are ok.");
define("EPL_ADLAN_10", "Name");
define("EPL_ADLAN_11", "Version");
define("EPL_ADLAN_12", "Author");
define("EPL_ADLAN_13", "Compatible");
define("EPL_ADLAN_14", "Description");
define("EPL_ADLAN_15", "Read the README file for more information");
define("EPL_ADLAN_16", "Plugin Information");
define("EPL_ADLAN_17", "More info...");
define("EPL_ADLAN_18", "Unable to successfully create table(s) for this plugin.");
define("EPL_ADLAN_19", "Database tables successfully created.");
// define("EPL_ADLAN_20", "Preference settings successfully created."); // duplicate of EPL_ADLAN_8;

define("EPL_ADLAN_21", "Plugin is already installed.");
define("EPL_ADLAN_22", "Installed");
define("EPL_ADLAN_23", "Not installed");
define("EPL_ADLAN_24", "Upgrade available");
define("EPL_ADLAN_25", "No install required");
define("EPL_ADLAN_26", "... then click here to begin uninstall procedure");
define("EPL_ADLAN_27", "Unable to successfully delete ");
define("EPL_ADLAN_28", "Database tables successfully deleted.");
define("EPL_ADLAN_29", "Preference settings successfully deleted.");
define("EPL_ADLAN_30", "please delete it manually.");
define("EPL_ADLAN_31", "Please now delete the folder ");
define("EPL_ADLAN_32", "and all files inside it to complete the uninstall process.");
define("EPL_ADLAN_33", "Plugin successfully installed.");
define("EPL_ADLAN_34", "Plugin successfully updated.");
define("EPL_ADLAN_35", "Parser settings successfully added.");
define("EPL_ADLAN_36", "Parser code insert failed, incorrectly formatted.");

define("EPL_ADLAN_37", "Upload plugin (.zip format)");
define("EPL_ADLAN_38", "Upload Plugin");
define("EPL_ADLAN_39", "The file could not be uploaded as the ".e_PLUGIN." folder does not have the correct permissions - please change the write permissions and re-upload the file.");
define("EPL_ADLAN_40", "Admin Message");
define("EPL_ADLAN_41", "That file does not appear to be a valid .zip or .tar archive.");
define("EPL_ADLAN_42", "An error has occurred, unable to un-archive the file");
define("EPL_ADLAN_43", "Your plugin has been uploaded and add to the uninstalled plugins list."); // FIXME HTML
define("EPL_ADLAN_44", "Auto plugin upload and extraction is disabled as upload to your plugins folder is not allowed at present - if you want to be able to do this, please change the permissions on your ".e_PLUGIN." folder to allow uploads.");
define("EPL_ADLAN_45", "Your menu item has been uploaded and unzipped, to activate go to <a href='".e_ADMIN."menus.php'>your menus page</a>."); //FIXME HTML
define("EPL_ADLAN_46", "PCLZIP extract error:");
define("EPL_ADLAN_47", "PCLTAR extract error: ");
define("EPL_ADLAN_48", "code:");
define('EPL_ADLAN_49', "Tables not deleted during uninstall process by request");

// define("EPL_CANCEL", "Cancel"); use LAN_CANCEL instead !!
// define("EPL_EMAIL", "email");
define("EPL_WEBSITE", "Website");
// define("EPL_OPTIONS", "Options"); use LAN_OPTIONS instead!
define("EPL_NOINSTALL", "No install required, just activate from your menus screen. To uninstall, delete the ");
define("EPL_DIRECTORY", "directory.");
define("EPL_NOINSTALL_1", "No install required, to remove delete the ");
define("EPL_UPGRADE", "Upgrade");

define("EPL_ADLAN_50", "Comments successfully deleted");

define("EPL_ADLAN_53", "Directory not writable");
define("EPL_ADLAN_54", "Please select the options for uninstalling the plugin:");
define("EPL_ADLAN_55", "Uninstall plugin");

define("EPL_ADLAN_57", "Delete plugin tables");
define("EPL_ADLAN_58", "If the tables are not removed, the plugin can be reinstalled with no data loss.  The creation of tables during the reinstall will fail. Tables will have to be manually deleted to remove.");
define("EPL_ADLAN_59", "Delete plugin files");
define("EPL_ADLAN_60", "e107 will attempt to remove all plugin related files.");
// define("EPL_ADLAN_61", "Confirm uninstall"); // duplicated. can be deleted.
define('EPL_ADLAN_62', 'Cancel uninstall');
define('EPL_ADLAN_63', 'Uninstall:');
define('EPL_ADLAN_64', 'Folder');

define ('EPL_ADLAN_70','Required plugin not installed: ');
define ('EPL_ADLAN_71','Newer plugin version required: ');
define ('EPL_ADLAN_72',' Version: ');
define ('EPL_ADLAN_73','Required PHP extension not loaded: ');
define ('EPL_ADLAN_74','Newer PHP version required: ');
define ('EPL_ADLAN_75','Newer MySQL version required: ');
define ('EPL_ADLAN_76','Error in plugin.xml');
define ('EPL_ADLAN_77','Cannot find plugin.xml');
define ('EPL_ADLAN_78','Delete User Classes created by plugin:');
define ('EPL_ADLAN_79','Only delete these if you have not used them for other purposes.');
define ('EPL_ADLAN_80','Delete extended user fields created by plugin:');
define ('EPL_ADLAN_81','Xhtml');
define ('EPL_ADLAN_82','Icon');
define ('EPL_ADLAN_83','Notes');
define ('EPL_ADLAN_84','Install Selected');
define ('EPL_ADLAN_85','Uninstall Selected');
define ('EPL_ADLAN_86','All files removed from ');
define ('EPL_ADLAN_87','File deletion failed ');


define('LAN_UPGRADE_SUCCESSFUL', 'Upgrade Successful');
define('LAN_INSTALL_SUCCESSFUL', 'Installation Successful');
define('LAN_INSTALL_FAIL', 'Installation Failed');


?>