<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2025 e107 Inc (e107.org)
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


return [
    'LANINS_001' => "e107 Installation",
    'LANINS_002' => "Step",
    'LANINS_003' => "1",
    'LANINS_004' => "Language Selection",
    'LANINS_005' => "Please choose the language to use during installation",
    'LANINS_007' => "4",
    'LANINS_008' => "PHP and MySQL Versions Check / File Permissions Check",
    'LANINS_009' => "Retest File Permissions",
    'LANINS_010' => "File not writable:",
    'LANINS_010a' => "Folder not writable:",
    'LANINS_012' => "MySQL Functions don't seem to exist. This probably means that either the MySQL PHP Extension isn't installed or your PHP installation wasn't compiled with MySQL support.",
    'LANINS_013' => "Couldn't determine your MySQL version number. This is a non fatal error, so please continue installing, but be aware that e107 requires MySQL >= 3.23 to function correctly.",
    'LANINS_014' => "File Permissions",
    'LANINS_015' => "PHP Version",
    'LANINS_017' => "PASS",
    'LANINS_018' => "Ensure all the listed files exist and are writable by the server. This normally involves CHMODing them 777, but environments vary - contact your host if you have any problems.",
    'LANINS_019' => "The version of PHP installed on your server isn't capable of running e107. e107 requires a PHP version of at least \".MIN_PHP_VERSION.\" to run correctly. Either upgrade your PHP version, or contact your host for an upgrade.",
    'LANINS_021' => "2",
    'LANINS_022' => "MySQL Server Details",
    'LANINS_023' => "Please enter your MySQL settings here.
If you have root permissions you can create a new database by ticking the box, if not you must create a database or use a pre-existing one.

If you have only one database use a prefix so that other scripts can share the same database.

If you do not know your MySQL details contact your web host.",
    'LANINS_024' => "MySQL Server:",
    'LANINS_025' => "MySQL Username:",
    'LANINS_026' => "MySQL Password:",
    'LANINS_027' => "MySQL Database:",
    'LANINS_028' => "Create Database?",
    'LANINS_029' => "Table prefix:",
    'LANINS_030' => "The MySQL server you would like e107 to use. It can also include a port number. e.g. 'hostname:port' or a path to a local socket e.g. \\\":/path/to/socket\\\" for the localhost.",
    'LANINS_031' => "The username you wish e107 to use to connect to your MySQL server",
    'LANINS_032' => "The Password for the user you just entered. Must not contain single or double quotes.",
    'LANINS_033' => "The MySQL database you wish e107 to reside in, sometimes referred to as a schema. Must begin with a letter. If the user has database create permissions you can opt to create the database automatically if it doesn't already exist.",
    'LANINS_034' => "The prefix you wish e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one database schema.",
    'LANINS_036' => "3",
    'LANINS_037' => "MySQL Connection Verification",
    'LANINS_038' => "and Database Creation",
    'LANINS_039' => "Please make sure you fill in all fields, most importantly, MySQL Server, MySQL Username and MySQL Database (These are always required by the MySQL Server)",
    'LANINS_040' => "Errors",
    'LANINS_041' => "e107 was unable to establish a connection to the MySQL server using the information you entered. Please return to the last page and ensure the information is correct.",
    'LANINS_042' => "Connection to the MySQL server established and verified.",
    'LANINS_043' => "Unable to create database, please ensure you have the correct permissions to create databases on your server.",
    'LANINS_044' => "Successfully created database.",
    'LANINS_045' => "Please click on the button to proceed to next stage.",
    'LANINS_046' => "5",
    'LANINS_047' => "Administrator Details",
    'LANINS_048' => "EXIF extension",
    'LANINS_049' => "The two passwords you entered are not the same. Please go back and try again.",
    'LANINS_050' => "XML extension",
    'LANINS_051' => "Installed",
    'LANINS_052' => "Not Installed",
    'LANINS_055' => "Install Confirmation",
    'LANINS_056' => "6",
    'LANINS_057' => "e107 now has all the information it needs to complete the installation.
Please click the button to create the database tables and save all your settings.",
    'LANINS_058' => "7",
    'LANINS_060' => "Unable to read the sql datafile
Please ensure the file [b]core_sql.php[/b] exists in the [b]/e107_core/sql[/b] directory.",
    'LANINS_061' => "e107 was unable to create all of the required database tables.
Please clear the database and rectify any problems before trying again.",
    'LANINS_069' => "e107 has been successfully installed!
For security reasons you should now set the file permissions on the [b]e107_config.php[/b] file back to 644.

Also please delete install.php from your server after you have clicked the button below.",
    'LANINS_070' => "e107 was unable to save the main config file to your server.
Please ensure the [b]e107_config.php[/b] file has the correct permissions",
    'LANINS_071' => "Installation Complete",
    'LANINS_072' => "Admin Username",
    'LANINS_073' => "This is the name you will use to login into the site. If you wish to use this as your display name also",
    'LANINS_074' => "Admin Display Name",
    'LANINS_076' => "Admin Password",
    'LANINS_077' => "Please type the admin password you wish to use here",
    'LANINS_078' => "Admin Password Confirmation",
    'LANINS_079' => "Please type the admin password again for confirmation",
    'LANINS_080' => "Admin Email",
    'LANINS_081' => "Enter your email address",
    'LANINS_083' => "MySQL Reported Error:",
    'LANINS_084' => "The installer could not establish a connection to the database",
    'LANINS_085' => "The installer could not select database:",
    'LANINS_086' => "Admin Username, Admin Password and Admin Email are required fields. Please return to the last page and ensure the information is correctly entered.",
    'LANINS_105' => "A database name or prefix beginning with some digits followed by 'e' or 'E' is not acceptable",
    'LANINS_106' => "WARNING - e107 cannot write to the directories and/or files listed. While this will not stop e107 installing, it will mean that certain features are not available.
You will need to change the file permissions to use these features",
    'LANINS_107' => "Website Name",
    'LANINS_108' => "My Website",
    'LANINS_109' => "Website Theme",
    'LANINS_111' => "Include Content/Configuration",
    'LANINS_112' => "Quickly reproduce the look of the theme preview or demo. (If Available)",
    'LANINS_113' => "Please enter a website name",
    'LANINS_114' => "Please select a theme",
    'LANINS_115' => "Theme Name",
    'LANINS_116' => "Theme Type",
    'LANINS_117' => "Website Preferences",
    'LANINS_118' => "Install Plugins",
    'LANINS_119' => "Install all plugins that the theme may require.",
    'LANINS_120' => "8",
    'LANINS_121' => "e107_config.php is not an empty file",
    'LANINS_122' => "You might have an existing installation",
    'LANINS_123' => "Optional: Your public name or alias. Leave blank to use the user name",
    'LANINS_124' => "Please choose a password of at least 8 characters",
    'LANINS_125' => "e107 has been installed successfully!",
    'LANINS_126' => "For security reasons you should now set the file permissions on the e107_config.php file back to 644.",
    'LANINS_127' => "The database [x] already exists. Overwrite it? (any existing data will be lost)",
    'LANINS_128' => "Overwrite",
    'LANINS_129' => "Database not found.",
    'LANINS_134' => "Installation",
    'LANINS_135' => "of",
    'LANINS_136' => "Deleted existing database",
    'LANINS_137' => "Found existing database",
    'LANINS_141' => "Please fill in the form below with your MySQL details. If you do not know this information, please contact your hosting provider. You may hover over each field for additional information.",
    'LANINS_142' => "IMPORTANT: Please rename e107.htaccess to .htaccess",
    'LANINS_144' => "IMPORTANT: Please copy and paste the contents of the [b]e107.htaccess[/b] into your [b].htaccess[/b] file. Please take care NOT to overwrite any existing data that may be in it.",
    'LANINS_145' => "e107 v2.x requires the PHP [x] to be installed. Please contact your host or read the information at [y] before continuing.",
    'LANINS_146' => "Admin-area Skin",
    'LANINS_147' => "Administration",
];
