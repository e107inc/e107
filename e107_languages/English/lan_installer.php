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
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/lan_installer.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

define("LANINS_001", "e107 Installation");


define("LANINS_002", "Step ");
define("LANINS_003", "1");
define("LANINS_004", "Language Selection");
define("LANINS_005", "Please choose the language to use during installation");
// define("LANINS_006", "Set Language");
define("LANINS_007", "4");
define("LANINS_008", "PHP and MySQL Versions Check / File Permissions Check");
define("LANINS_009", "Retest File Permissions");
define("LANINS_010", "File not writable: ");
define("LANINS_010a", "Folder not writable: ");
// define("LANINS_011", "Error"); new > LAN_ERROR
define("LANINS_012", "MySQL Functions don't seem to exist. This probably means that either the MySQL PHP Extension isn't installed or your PHP installation wasn't compiled with MySQL support."); // help for 012
define("LANINS_013", "Couldn't determine your MySQL version number. This is a non fatal error, so please continue installing, but be aware that e107 requires MySQL >= 3.23 to function correctly.");
define("LANINS_014", "File Permissions");
define("LANINS_015", "PHP Version");
// define("LANINS_016", "MySQL");
define("LANINS_017", "PASS");
define("LANINS_018", "Ensure all the listed files exist and are writable by the server. This normally involves CHMODing them 777, but environments vary - contact your host if you have any problems.");
define("LANINS_019", "The version of PHP installed on your server isn't capable of running e107. e107 requires a PHP version of at least ".MIN_PHP_VERSION." to run correctly. Either upgrade your PHP version, or contact your host for an upgrade.");
// define("LANINS_020", "Continue Installation"); //LAN_CONTINUE
define("LANINS_021", "2");
define("LANINS_022", "MySQL Server Details");
define("LANINS_023", "Please enter your MySQL settings here.

If you have root permissions you can create a new database by ticking the box, if not you must create a database or use a pre-existing one.

If you have only one database use a prefix so that other scripts can share the same database.
If you do not know your MySQL details contact your web host.");
define("LANINS_024", "MySQL Server:");
define("LANINS_025", "MySQL Username:");
define("LANINS_026", "MySQL Password:");
define("LANINS_027", "MySQL Database:");
define("LANINS_028", "Create Database?");
define("LANINS_029", "Table prefix:");
define("LANINS_030", "The MySQL server you would like e107 to use. It can also include a port number. e.g. 'hostname:port' or a path to a local socket e.g. \":/path/to/socket\" for the localhost.");
define("LANINS_031", "The username you wish e107 to use to connect to your MySQL server");
define("LANINS_032", "The Password for the user you just entered. Must not contain single or double quotes.");
define("LANINS_033", "The MySQL database you wish e107 to reside in, sometimes referred to as a schema. Must begin with a letter. If the user has database create permissions you can opt to create the database automatically if it doesn't already exist.");
define("LANINS_034", "The prefix you wish e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one database schema.");
// define("LANINS_035", "Continue"); // LAN_CONTINUE
define("LANINS_036", "3");
define("LANINS_037", "MySQL Connection Verification");
define("LANINS_038", " and Database Creation");
define("LANINS_039", "Please make sure you fill in all fields, most importantly, MySQL Server, MySQL Username and MySQL Database (These are always required by the MySQL Server)");
define("LANINS_040", "Errors");
define("LANINS_041", "e107 was unable to establish a connection to the MySQL server using the information you entered. Please return to the last page and ensure the information is correct.");
define("LANINS_042", "Connection to the MySQL server established and verified.");
define("LANINS_043", "Unable to create database, please ensure you have the correct permissions to create databases on your server.");
define("LANINS_044", "Successfully created database.");
define("LANINS_045", "Please click on the button to proceed to next stage.");
define("LANINS_046", "5");
define("LANINS_047", "Administrator Details");
define("LANINS_048", "EXIF extension");
define("LANINS_049", "The two passwords you entered are not the same. Please go back and try again.");
define("LANINS_050", "XML extension");
define("LANINS_051", "Installed");
define("LANINS_052", "Not Installed");
// define("LANINS_053", "e107 v2.x requires the PHP XML Extension to be installed. Please contact your host or read the information at [x] before continuing");
// define("LANINS_054", "e107 v2.x requires the PHP EXIF Extension to be installed. Please contact your host or read the information at [x] before continuing");
define("LANINS_055", "Install Confirmation");
define("LANINS_056", "6");
define("LANINS_057", " e107 now has all the information it needs to complete the installation.

Please click the button to create the database tables and save all your settings.

");
define("LANINS_058", "7");
define("LANINS_060", "Unable to read the sql datafile

Please ensure the file [b]core_sql.php[/b] exists in the [b]/e107_core/sql[/b] directory.");
define("LANINS_061", "e107 was unable to create all of the required database tables.
Please clear the database and rectify any problems before trying again.");


// define("LANINS_063", "Welcome to e107");

define("LANINS_069", "e107 has been successfully installed!

For security reasons you should now set the file permissions on the [b]e107_config.php[/b] file back to 644.

Also please delete install.php from your server after you have clicked the button below.
");
define("LANINS_070", "e107 was unable to save the main config file to your server.

Please ensure the [b]e107_config.php[/b] file has the correct permissions");
define("LANINS_071", "Installation Complete");

define("LANINS_072", "Admin Username");
define("LANINS_073", "This is the name you will use to login into the site. If you wish to use this as your display name also");
define("LANINS_074", "Admin Display Name");
// define("LANINS_075", "This is the name that you wish your users to see displayed in your profile, forums and other areas. If you wish to use the same as your username then leave this blank.");
define("LANINS_076", "Admin Password");
define("LANINS_077", "Please type the admin password you wish to use here");
define("LANINS_078", "Admin Password Confirmation");
define("LANINS_079", "Please type the admin password again for confirmation");
define("LANINS_080", "Admin Email");
define("LANINS_081", "Enter your email address");

// define("LANINS_082", "user@yoursite.com");

// Better table creation error reporting
define("LANINS_083", "MySQL Reported Error:");
define("LANINS_084", "The installer could not establish a connection to the database");
define("LANINS_085", "The installer could not select database:");

define("LANINS_086", "Admin Username, Admin Password and Admin Email are required fields. Please return to the last page and ensure the information is correctly entered.");

// define("LANINS_087", "Misc");
// define("LANINS_088", "Home");
// define("LANINS_089", "Downloads");
// define("LANINS_090", "Members");
// define("LANINS_091", "Submit News");
// define("LANINS_092", "Contact Us");
// define("LANINS_093", "Grants access to private menu items");
// define("LANINS_094", "Example private forum class");
// define("LANINS_095", "Integrity Check");

// define("LANINS_096", 'Latest Comments');
// define("LANINS_097", '[more ...]');
// define("LANINS_098", 'Articles');
// define("LANINS_099", 'Articles Front Page ...');
// define("LANINS_100", 'Latest Forum Posts');
// define("LANINS_101", 'Update menu Settings');
// define("LANINS_102", 'Date / Time');
// define("LANINS_103", 'Reviews');
// define("LANINS_104", 'Review Front Page ...');

define("LANINS_105", "A database name or prefix beginning with some digits followed by 'e' or 'E' is not acceptable");
define("LANINS_106", "WARNING - e107 cannot write to the directories and/or files listed. While this will not stop e107 installing, it will mean that certain features are not available.
				You will need to change the file permissions to use these features");
				
define("LANINS_107", "Website Name");
define("LANINS_108", "My Website");
define("LANINS_109", "Website Theme");
// define("LANINS_110", "");
define("LANINS_111", "Include Content/Configuration");
define("LANINS_112", "Quickly reproduce the look of the theme preview or demo. (If Available)");
define("LANINS_113", "Please enter a website name");
define("LANINS_114", "Please select a theme");
define("LANINS_115", "Theme Name");
define("LANINS_116", "Theme Type");
define("LANINS_117", "Website Preferences");
define("LANINS_118", "Install Plugins");
define("LANINS_119", "Install all plugins that the theme may require.");
define("LANINS_120", "8");
define("LANINS_121", "e107_config.php is not an empty file");
define("LANINS_122", "You might have an existing installation");
define("LANINS_123", "Optional: Your public name or alias. Leave blank to use the user name");
define("LANINS_124", "Please choose a password of at least 8 characters");
define("LANINS_125", "e107 has been installed successfully!");
define("LANINS_126", "For security reasons you should now set the file permissions on the e107_config.php file back to 644.");
define("LANINS_127", "The database [x] already exists. Overwrite it? (any existing data will be lost)"); 
define("LANINS_128", "Overwrite");
define("LANINS_129", "Database not found.");

define("LANINS_134", "Installation");
define("LANINS_135", "of");   //single time use installer only as in .1 of 8  not replacing by LAN_SEARCH_12
define("LANINS_136", "Deleted existing database");
define("LANINS_137", "Found existing database");
// define("LANINS_138", "Version");

define("LANINS_141", "Please fill in the form below with your MySQL details. If you do not know this information, please contact your hosting provider. You may hover over each field for additional information.");
define("LANINS_142", "IMPORTANT: Please rename e107.htaccess to .htaccess");
define("LANINS_144", "IMPORTANT: Please copy and paste the contents of the [b]e107.htaccess[/b] into your [b].htaccess[/b] file. Please take care NOT to overwrite any existing data that may be in it.");
define("LANINS_145", "e107 v2.x requires the PHP [x] to be installed. Please contact your host or read the information at [y] before continuing.");

define("LANINS_146", "Admin-area Skin");
define("LANINS_147", "Administration");

