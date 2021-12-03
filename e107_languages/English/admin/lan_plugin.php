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
//define("EPL_ADLAN_12", "Author"); //LAN_AUTHOR
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
define("EPL_ADLAN_49", "Tables not deleted during uninstall process by request");

// define("EPL_CANCEL", "Cancel"); use LAN_CANCEL instead !!
// define("EPL_EMAIL", "email");
define("EPL_WEBSITE", "Website");
// define("EPL_OPTIONS", "Options"); use LAN_OPTIONS instead!
define("EPL_NOINSTALL", "No install required, just activate from your menus screen. To uninstall, delete the ");
define("EPL_DIRECTORY", "directory.");
define("EPL_NOINSTALL_1", "No install required, to remove delete the ");
define("EPL_UPGRADE", "Upgrade to:");

define("EPL_ADLAN_50", "Comments successfully deleted");

define("EPL_ADLAN_53", "Directory not writable");
define("EPL_ADLAN_54", "Please select the options for uninstalling the plugin:");
define("EPL_ADLAN_55", "Uninstall plugin");

define("EPL_ADLAN_57", "Delete plugin tables");
define("EPL_ADLAN_58", "If the tables are not removed, the plugin can be reinstalled with no data loss.  The creation of tables during the reinstall will fail. Tables will have to be manually deleted to remove.");
define("EPL_ADLAN_59", "Delete plugin files");
define("EPL_ADLAN_60", "e107 will attempt to remove all plugin related files.");
// define("EPL_ADLAN_61", "Confirm uninstall"); // duplicated. can be deleted.
define("EPL_ADLAN_62", "Cancel uninstall");
define("EPL_ADLAN_63", "Uninstall:");
define("EPL_ADLAN_64", "Folder");
define ("EPL_ADLAN_70","Required plugin not installed: ");
define ("EPL_ADLAN_71","Newer plugin version required: ");
define ("EPL_ADLAN_72"," Version: ");
define ("EPL_ADLAN_73","Required PHP extension not loaded: ");
define ("EPL_ADLAN_74","Newer PHP version required: ");
define ("EPL_ADLAN_75","Newer MySQL version required: ");
define ("EPL_ADLAN_76","Error in plugin.xml");
define ("EPL_ADLAN_77","Cannot find plugin.xml");
define ("EPL_ADLAN_78","Delete User Classes created by plugin:");
define ("EPL_ADLAN_79","Only delete these if you have not used them for other purposes.");
define ("EPL_ADLAN_80","Delete extended user fields created by plugin:");
define ("EPL_ADLAN_81","Xhtml");
define ("EPL_ADLAN_82","Icon");
define ("EPL_ADLAN_83","Notes");
define ("EPL_ADLAN_84","Install Selected");
define ("EPL_ADLAN_85","Uninstall Selected");
define ("EPL_ADLAN_86","All files removed from ");
define ("EPL_ADLAN_87","File deletion failed ");

define ("EPL_ADLAN_88","Made for v2");
define ("EPL_ADLAN_89","Search Online");
define ("EPL_ADLAN_90","cURL is currently required to use this feature. Contact your webhosting provider to enable cURL");
define ("EPL_ADLAN_91","Featured");
define ("EPL_ADLAN_92","Buy");
define ("EPL_ADLAN_93","Free");
define ("EPL_ADLAN_94","Connecting...");
define ("EPL_ADLAN_95","Unable to continue");
define ("EPL_ADLAN_96","eg. https://website.com/some-plugin.zip");
define ("EPL_ADLAN_97","There was a problem extracting the .zip file to your plugin directory.");
define ("EPL_ADLAN_98","Unknown file:");
define ("EPL_ADLAN_99","Error messages above this line");
define ("EPL_ADLAN_100","click here to install some");
define ("EPL_ADLAN_101","No plugins installed - [x].");
define ("EPL_ADLAN_102","This Wizard will build an admin area for your plugin and generate a plugin.xml meta file. Before you start:");
define ("EPL_ADLAN_103","Create a new writable folder in the [x] directory eg. [b]myplugin[/b]");
// define ('EPL_ADLAN_104',"If your plugin will use sql tables, create a new file in this folder and name it the same as the directory but with [b]_sql.php[/b] as a sufix eg. [b]myplugin_sql.php[/b]");
define ("EPL_ADLAN_105","Create your table using phpMyAdmin in the same database as e107 and with the same table prefix. eg. [b]e107_myplugin[/b]");
define ("EPL_ADLAN_106","Select your plugin's folder to begin.");
define ("EPL_ADLAN_107","Build an admin-area and xml file for:");
define ("EPL_ADLAN_108","Check language files:");
define ("EPL_ADLAN_109","Basic Info.");
// define ('EPL_ADLAN_110',"Preferences");
// define ('EPL_ADLAN_111',"Generate");// LAN_GENERATE
define ("EPL_ADLAN_112","Review all fields and modify if necessary.");
define ("EPL_ADLAN_113","Review ALL tabs before clicking 'Generate'.");
define ("EPL_ADLAN_114","Plugin Builder");
define ("EPL_ADLAN_115","Step 2");

define ("EPL_ADLAN_116","Text Box");
define ("EPL_ADLAN_117","Text Box (number)");
define ("EPL_ADLAN_118","Text Box (url)");
define ("EPL_ADLAN_119","Text Area");
define ("EPL_ADLAN_120","Rich-Text Area");
define ("EPL_ADLAN_121","True/False");
define ("EPL_ADLAN_122","Custom Function");
define ("EPL_ADLAN_123","Image");
define ("EPL_ADLAN_124","DropDown");
define ("EPL_ADLAN_125","DropDown (userclasses)");
define ("EPL_ADLAN_126","DropDown (languages)");
define ("EPL_ADLAN_127","Icon");
define ("EPL_ADLAN_128","File");

define ("EPL_ADLAN_129","Preference Name");
define ("EPL_ADLAN_130","Default Value");
define ("EPL_ADLAN_131","Field Type...");
define ("EPL_ADLAN_132","[x] has been generated");
define ("EPL_ADLAN_133","[x] is missing!");
define ("EPL_ADLAN_134","Please create [b][x][/b] in your plugin directory with the following content: [y]");
define ("EPL_ADLAN_135","The name of your plugin. (Must be written in English)");
define ("EPL_ADLAN_136","If you have a language file, enter the LAN_XXX value for the plugin's name");
define ("EPL_ADLAN_137","Creation date of your plugin");
define ("EPL_ADLAN_138","The version of your plugin. Format: x.x or x.x.x");
define ("EPL_ADLAN_139","Compatible with this version of e107");
define ("EPL_ADLAN_140","Author Name");
define ("EPL_ADLAN_141","Author Website URL");
define ("EPL_ADLAN_142","A short one-line description of the plugin");
define ("EPL_ADLAN_143","(Must be written in English)");
define ("EPL_ADLAN_144","Keyword/Tag for this plugin");
define ("EPL_ADLAN_145","A full description of the plugin");
define ("EPL_ADLAN_146","What category of plugin is this?");

// Categories
define ("EPL_ADLAN_147","settings");
define ("EPL_ADLAN_148","users");
define ("EPL_ADLAN_149","content");
define ("EPL_ADLAN_150","tools");
define ("EPL_ADLAN_151","manage");
define ("EPL_ADLAN_152","misc");
define ("EPL_ADLAN_153","menu");
define ("EPL_ADLAN_154","about");

define ("EPL_ADLAN_155","Saved:");
define ("EPL_ADLAN_156","Couldn't Save:");

define ("EPL_ADLAN_157","Main Area");
define ("EPL_ADLAN_158","Categories");
define ("EPL_ADLAN_159","Other 1");
define ("EPL_ADLAN_160","Other 2");
define ("EPL_ADLAN_161","Other 3");
define ("EPL_ADLAN_162","Other 4");
define ("EPL_ADLAN_163","Exclude this table");

//FIXME TODO Excessive duplicate terms below.
define ("EPL_ADLAN_164","Field");
define ("EPL_ADLAN_165","Caption");
define ("EPL_ADLAN_166","Type");
define ("EPL_ADLAN_167","Data");
define ("EPL_ADLAN_168","Width");
define ("EPL_ADLAN_169","Batch");
define ("EPL_ADLAN_170","Filter");
define ("EPL_ADLAN_171","Inline");
define ("EPL_ADLAN_172","Validate");
define ("EPL_ADLAN_173","Display");
define ("EPL_ADLAN_174","HelpTip");
define ("EPL_ADLAN_175","ReadParms");
define ("EPL_ADLAN_176","WriteParms");
define ("EPL_ADLAN_177","Field is required to be filled");
define ("EPL_ADLAN_178","Displayed by Default");

// date, datetime
define ("EPL_ADLAN_179","Text Box");
define ("EPL_ADLAN_180","Hidden");

// int, tinyint, bigint, smallint
define ("EPL_ADLAN_181","True/False");
define ("EPL_ADLAN_182","Text Box (number)");
define ("EPL_ADLAN_183","DropDown");
define ("EPL_ADLAN_184","DropDown (userclasses)");
//define ('EPL_ADLAN_185',"Date");//LAN_DATE
define ("EPL_ADLAN_186","Custom Function");
define ("EPL_ADLAN_187","Hidden");
define ("EPL_ADLAN_188","User");

// decimal
define ("EPL_ADLAN_189","Text Box");
define ("EPL_ADLAN_190","DropDown");
define ("EPL_ADLAN_191","Custom Function");
define ("EPL_ADLAN_192","Hidden");

// varchar, tinytext
define ("EPL_ADLAN_193","Text Box");
define ("EPL_ADLAN_194","Text Box (url)");
define ("EPL_ADLAN_195","Text Box (email)");
define ("EPL_ADLAN_196","Text Box (ip)");
define ("EPL_ADLAN_197","Text Box (number)");
define ("EPL_ADLAN_198","Text Box (password)");
define ("EPL_ADLAN_199","Text Box (keywords)");
define ("EPL_ADLAN_200","DropDown");
define ("EPL_ADLAN_201","DropDown (userclasses)");
define ("EPL_ADLAN_202","DropDown (languages)");
define ("EPL_ADLAN_203","Icon");
define ("EPL_ADLAN_204","Image");
define ("EPL_ADLAN_205","File");
define ("EPL_ADLAN_206","Custom Function");
define ("EPL_ADLAN_207","Hidden");

// text, mediumtext, longtext
define ("EPL_ADLAN_208","Text Area");
define ("EPL_ADLAN_209","Rich-Text Area");
define ("EPL_ADLAN_210","Text Box");
define ("EPL_ADLAN_211","Text Box (keywords)");
define ("EPL_ADLAN_212","Custom Function");
define ("EPL_ADLAN_213","Image (string)");
define ("EPL_ADLAN_214","Images (array)");
define ("EPL_ADLAN_215","Hidden");

define ("EPL_ADLAN_216","Click Here");
define ("EPL_ADLAN_217","[x] to vist your generated admin area");
define ("EPL_ADLAN_218","Could not write to [x]");
define ("EPL_ADLAN_219","No Files have been created. Please Copy &amp; Paste the code below into your files.");

define ("EPL_ADLAN_220","Find Plugins");
define ("EPL_ADLAN_221","Language-File Check");

define ("EPL_ADLAN_222","Plugin Files");
define ("EPL_ADLAN_223","Used");
define ("EPL_ADLAN_224","Unused");
define ("EPL_ADLAN_225","Unsure");

define ("EPL_ADLAN_226","Plugin Language-File Check");

define ("EPL_ADLAN_227","Scan for Changes");
define ("EPL_ADLAN_228","Plugin folders are scanned every [x] minutes for changes. Click the button below to scan now.");
define ("EPL_ADLAN_229","Refresh");
define ("EPL_ADLAN_230", "Downloading and Installing: ");
define ("EPL_ADLAN_231", "Remove icons from Media-Manager");
define ("EPL_ADLAN_232", "Create Files");

define ("EPL_ADLAN_233", "Adding Link:"); 
define ("EPL_ADLAN_234", "Removing Link:"); 
define ("EPL_ADLAN_235", "Automated download not possible.");
define ("EPL_ADLAN_236", "Please Download Manually");
define ("EPL_ADLAN_237", "Download");
define ("EPL_ADLAN_238","Installation Complete!");
define ("EPL_ADLAN_239","Adding Table:");
define ("EPL_ADLAN_240","Removing Table:");
define ("EPL_ADLAN_241","Adding Pref:"); 
define ("EPL_ADLAN_242","Removing Pref:");
define ("EPL_ADLAN_243","Updating Pref:");
define ("EPL_ADLAN_244","Only 5 Media Categories are permitted during installation.");

define ("EPL_ADLAN_245","Adding Media Category: [x]");  
define ("EPL_ADLAN_246","Deleting All Media Categories owned by : [x]"); 
define ("EPL_ADLAN_247","Updates to be Installed");

define ("EPL_ADLAN_249","Adding Extended Field: ");  
define ("EPL_ADLAN_250","Removing Extended Field: ");  
define ("EPL_ADLAN_251","Extended Field left in place: ");  
define ("EPL_ADLAN_252","Perm: ");

define("EPL_ADLAN_253", "Completed");

define ("LAN_RELEASED", "Released");
define ("LAN_REPAIR_PLUGIN_SETTINGS", "Repair plugin settings");
define ("LAN_SYNC_WITH_GIT_REPO", "Sync with Git Repo");
define ("LAN_ADDONS", "Addons");

define("LAN_UPGRADE_SUCCESSFUL", "Upgrade successful");
define("LAN_INSTALL_SUCCESSFUL", "Installation successful");
define("LAN_INSTALL_FAIL", "Installation failed!");
define("LAN_UNINSTALL_FAIL", "Unable to uninstall!");
define("LAN_PLUGIN_IS_USED", "[x] plugin is used by:");

define("EPL_ADLAN_254", "This will check your plugin's language files for errors and common or duplicate LAN definitions. ");
define("EPL_ADLAN_255", "Overwrite Files");
define("EPL_ADLAN_256", "Skipped [x] (already exists)");

define ("EPL_ADLAN_257","Readonly");


