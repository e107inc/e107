<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.7/e107_languages/English/lan_install.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:11:56 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

define("INSLAN1", "Installation Stage");
define("INSLAN2", "PHP / mySQL version check / file permissions check");
define("INSLAN3", "PHP Version");
define("INSLAN4", "Fail");
define("INSLAN5", "You are running a version of PHP that is not compatible with e107");
define("INSLAN6", "Script halted.");
define("INSLAN7", "Pass");
define("INSLAN8", "mySQL Version");
define("INSLAN9", "e107 was unable to determine the mySQL version number.");
define("INSLAN10", "File permissions");
define("INSLAN11", "is not writable");
define("INSLAN12", "folder are not writable");
define("INSLAN13", "Please ensure the permissions for the files shown above have been set<br />correctly on your server, the permissions should be set to 777. To set the<br />permissions, right click on the file in your FTP client and choose Chmod or<br />Set File Permissions, then type in 777, if the dialog that appears has boxes<br />then tick all the boxes.<br /><br />Please retest after setting the file permissions.");
define("INSLAN14", "e107 installing");
define("INSLAN15", "FATAL ERROR");
define("INSLAN16", "Although it was not possible to determine the status of your mySQL installation,<br />please continue to next stage.");
define("INSLAN17", "Continue");
define("INSLAN18", "Retest File Permissions");
define("INSLAN19", "All server tests have been passed, please click on the button to proceed to next stage");
define("INSLAN20", "mySQL information");
define("INSLAN21", "Please enter your mySQL settings here.<br />If you have root permissions you can create a new database by ticking the box, if not you must<br />create a database or use a pre-existing one. <br />If you have only one database use a prefix so that other scripts can share the same database.<br />If you do not know your mySQL details contact your web host.");
define("INSLAN22", "mySQL Server");
define("INSLAN23", "mySQL Username");
define("INSLAN24", "mySQL Password");
define("INSLAN25", "mySQL Database");
define("INSLAN26", "Table prefix");
define("INSLAN27", "Error");
define("INSLAN28", "Error encountered");
define("INSLAN29", "You left required fields blank, please re-enter mySQL information");
define("INSLAN30", "e107 was unable to establish a connection to mySQL using the information you entered.<br />Please return to the last page and ensure the information is correct.");
define("INSLAN31", "mySQL check");
define("INSLAN32", "Connection to mySQL established and verified.");
define("INSLAN33", "Attempting to create database");
define("INSLAN34", "Unable to create database, please ensure you have the correct permissions to create databases on your server.");
define("INSLAN35", "Successfully created database.");
define("INSLAN36", "Please click on the button to proceed to next stage.");


define("INSLAN37", "Back to last page");
define("INSLAN38", "Administrator information");
define("INSLAN39", "Please enter your main administrator username, password and email address here.<br />These details will be used to gain access to the administration area of your website.<br />Please make sure you write your username and password down in a safe place as if they are lost they<br />cannot be retrieved.");
define("INSLAN40", "Admin Name");
define("INSLAN41", "Admin Password");
define("INSLAN42", "Confirm Password");
define("INSLAN43", "Admin Email Address");
define("INSLAN44", "You left required fields blank, please re-enter admin information.");
define("INSLAN45", "The two passwords do not match, please re-enter.");
define("INSLAN46", "doesn't appear to be a valid email address, please re-enter.");
define("INSLAN47", "All set!");
define("INSLAN48", "e107 now has all the information it needs to complete the installation.<br />Please click the button to create the database tables and save all your settings.");
define("INSLAN49", "e107 was unable to save the main config file to your server<br />Please ensure the <b>e107_config.php</b> file has the correct permissions");
define("INSLAN50", "Installation Completed!");
define("INSLAN51", "All done");
define("INSLAN52", "e107 has been successfully installed!<br />For security reasons you should now set the file permissions on the<br /><b>e107_config.php</b> file back to 644.<br />Also please delete /install.php from your server after you have clicked the button below");
define("INSLAN53", "Click here to go to your new website!");
define("INSLAN54", "Unable to read the sql datafile<br /><br />Please ensure the file <b>core_sql.php</b> exists in the <b>/e107_admin/sql</b> directory.");
define("INSLAN55", "e107 was unable to create all of the required database tables.<br />Please clear the database and rectify any problems before trying again.");
define("INSLAN56", "Welcome to your new website!");

define("INSLAN57", "e107 has installed successfully and is now ready to accept content.");
define("INSLAN58", "you will find the FAQ and documentation here.");
define("INSLAN59", "Thankyou for trying e107, we hope it fulfills your website needs.\n(You can delete this message from your admin section.)");
define("INSLAN60", "tick to create");
define("INSLAN61", "folder");
define("INSLAN62", "or");
define("INSLAN63", "File permission error");
define("INSLAN64", "This file has been generated by the installation script.");

define("INSLAN65", "e107 requires at least version 4.1.0");
define("INSLAN66", "If you are using a local server on your computer you will need to upgrade your");
define("INSLAN67", "version of PHP to continue, please see");
define("INSLAN68", "for instructions. If you are");
define("INSLAN69", "attempting to install e107 on a hosted server you will need to contact the");
define("INSLAN70", "server administrators and ask them  to upgrade PHP for you.");
define("INSLAN71", "Please rerun this script after upgrading your PHP version.");

define("INSLAN72", "This could mean that mySQL is not installed or not currently running, or");
define("INSLAN73", "you could be running a version that doesn't correctly report the version");
define("INSLAN74", "number (v5.x is known to have this problem). If the next step of the installation");
define("INSLAN75", "fails you will need to check your mySQL status.");

define("INSLAN76", "Your administration section is");
define("INSLAN77", "located here");
define("INSLAN78", "click to go there now. You will have to login using the name and password you entered during the installation process.");


?>