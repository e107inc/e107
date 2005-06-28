<?php

define("LANINS_001", "e107 Installation");


define("LANINS_002", "Stage ");
define("LANINS_003", "1");
define("LANINS_004", "Language Selection");
define("LANINS_005", "Please choose language to use during installation procedure");
define("LANINS_006", "Set Language");
define("LANINS_007", "2");
define("LANINS_008", "PHP &amp; MySQL Versions Check / File Permissions Check");
define("LANINS_009", "Retest File Permissions");
define("LANINS_010", "File not writable: ");
define("LANINS_011", "Error");
define("LANINS_012", "MySQL Functions don't seem to exists. This probably means that either the MySQL PHP Extension isn't isntalled or isn't configured correctly."); // help for 012
define("LANINS_013", "Couldn't detirmine your MySQL version number. This could mean that your MySQL server is down, or refusing connections.");
define("LANINS_014", "File Permissions");
define("LANINS_015", "PHP Version");
define("LANINS_016", "MySQL");
define("LANINS_017", "PASS");
define("LANINS_018", "Make ensure all the listed files exist and are writable by the server. This normally involves CHMODing them 777, but environments vary - contact your host if you have any problems.");
define("LANINS_019", "The version of PHP installed on your server isn't capable of running e107. e107 requires a PHP version of at least 4.3.0 to run correctly. Either upgrade your PHP version, or contact your host for an upgrade.");
define("LANINS_020", "Continue Installation");
define("LANINS_021", "3");
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
define("LANINS_030", "The MySQL server you would like e107 to use. It can also include a port number. e.g. \"hostname:port\" or a path to a local socket e.g. \":/path/to/socket\" for the localhost.");
define("LANINS_031", "The username you wish e107 to use for connecting to your MySQL server");
define("LANINS_032", "The Password for the user you just entered");
define("LANINS_033", "The MySQL database you wish e107 to reside in, sometimes refered to as a schema. If the user has database create permissions you can opt to create the database automatically if it doesn't already exsist.");
define("LANINS_034", "The prefix you wish for e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one database schema.");
define("LANINS_035", "Continue");
define("LANINS_036", "4");
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
define("LANINS_048", "Go Back To Last Step");
define("LANINS_049", "The two passwords you entered are not the same. Please go back and try again.");
define("LANINS_050", "XML Extension");
define("LANINS_051", "Installed");
define("LANINS_052", "Not Installed");
define("LANINS_053", "e107 .700 requires the PHP XML Extension to be installed. Please contact your host or read the information at ");
define("LANINS_054", " before continuing");
define("LANINS_055", "Install Confirmation");
define("LANINS_056", "6");
define("LANINS_057", " e107 now has all the information it needs to complete the installation.

Please click the button to create the database tables and save all your settings.

");
define("LANINS_058", "7");
define("LANINS_060", "Unable to read the sql datafile

Please ensure the file <b>core_sql.php</b> exists in the <b>/e107_admin/sql</b> directory.");
define("LANINS_061", "e107 was unable to create all of the required database tables.
Please clear the database and rectify any problems before trying again.");
define("LANINS_062", "Welcome to your new website!");
define("LANINS_063", "e107 has installed successfully and is now ready to accept content.");
define("LANINS_064", "Your administration section is");
define("LANINS_065", "located here");
define("LANINS_066", "click to go there now. You will have to login using the name and password you entered during the installation process.");
define("LANINS_067", "you will find the FAQ and documentation here.");
define("LANINS_068", "Thankyou for trying e107, we hope it fulfills your website needs.\n(You can delete this message from your admin section.)\n\n<b>Please note this version of e107 is a beta version and is not intended to be used on live websites.</b>");
define("LANINS_069", "e107 has been successfully installed!

For security reasons you should now set the file permissions on the <b>e107_config.php</b> file back to 644.

Also please delete install.php and the e107_install directory from your server after you have clicked the button below
");
define("LANINS_070", "e107 was unable to save the main config file to your server.

Please ensure the <b>e107_config.php</b> file has the correct permissions");
define("LANINS_071", "Finalising Installation");

define("LANINS_072", "Admin Username");
define("LANINS_073", "This is the name you will use to login into the site. If you wish to use this as your display name also");
define("LANINS_074", "Admin Display Name");
define("LANINS_075", "This is the name that you wish your users to see displayed in your profile, forums and other areas. If you wish to use the same as your login name then leave this blank.");
define("LANINS_076", "Admin Password");
define("LANINS_077", "Please type the admin password you wish to use here");
define("LANINS_078", "Admin Password Confirmation");
define("LANINS_079", "Please type the admin password again for confirmation");
define("LANINS_080", "Admin Email");
define("LANINS_081", "Enter your email address");

define("LANINS_082", "user@yoursite.com");

?>