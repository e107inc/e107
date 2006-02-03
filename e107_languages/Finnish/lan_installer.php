<?php

define("LANINS_001", "e107 Installation");


define("LANINS_002", "Stage ");
define("LANINS_003", "1");
define("LANINS_004", "Language Valitseion");
define("LANINS_005", "Please choose language to use during installation procedure");
define("LANINS_006", "Set Language");
define("LANINS_007", "4");
define("LANINS_008", "PHP &amp; MySQL Versions Check / Tiedosto Permissions Check");
define("LANINS_009", "Retest Tiedosto Permissions");
define("LANINS_010", "Tiedosto not writable: ");
define("LANINS_010a", "Folder not writable: ");
define("LANINS_011", "Virhe");
define("LANINS_012", "MySQL Functions don't seem to exist. This probably means that either the MySQL PHP Extension isn't installed or your PHP installation wasn't compiled with MySQL support."); // help for 012
define("LANINS_013", "Couldn't determine your MySQL version number. This is a non fatal error, so please continue installing, but be aware that e107 requires MySQL >= 3.23 to function correctly.");
define("LANINS_014", "Tiedosto Permissions");
define("LANINS_015", "PHP Version");
define("LANINS_016", "MySQL");
define("LANINS_017", "PASS");
define("LANINS_018", "Ensure all the listed files exist ja are writable by the server. This normally involves CHMODing them 777, but environments vary - contact your host if you have any problems.");
define("LANINS_019", "The version of PHP installed on your server isn't capable of running e107. e107 requires a PHP version of at least 4.3.0 to run correctly. Either upgrade your PHP version, or contact your host for an upgrade.");
define("LANINS_020", "Jatka Installation");
define("LANINS_021", "2");
define("LANINS_022", "MySQL Server Details");
define("LANINS_023", "Please enter your MySQL settings here.

If you have root permissions you can create a new tietokanta by ticking the box, if not you must create a tietokanta or use a pre-existing one.

If you have only one tietokanta use a prefix so that other scripts can share the same tietokanta.
If you do not know your MySQL details contact your web host.");
define("LANINS_024", "MySQL Server:");
define("LANINS_025", "MySQL Käyttäjänimi:");
define("LANINS_026", "MySQL Salasana:");
define("LANINS_027", "MySQL Database:");
define("LANINS_028", "Create Database?");
define("LANINS_029", "Table prefix:");
define("LANINS_030", "The MySQL server you would like e107 to use. It can also include a port number. e.g. \"hostnimi:port\" or a path to a local socket e.g. \":/path/to/socket\" for the localhost.");
define("LANINS_031", "The käyttäjänimi you wish e107 to use for connecting to your MySQL server");
define("LANINS_032", "The Salasana for the käyttäjä you just entered");
define("LANINS_033", "The MySQL tietokanta you wish e107 to reside in, sometimes referred to as a schema. If the käyttäjä has tietokanta create permissions you can opt to create the tietokanta automatically if it doesn't already exist.");
define("LANINS_034", "The prefix you wish e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one tietokanta schema.");
define("LANINS_035", "Jatka");
define("LANINS_036", "3");
define("LANINS_037", "MySQL Connection Verification");
define("LANINS_038", " ja Database Creation");
define("LANINS_039", "Please make sure you fill in all fields, most importantly, MySQL Server, MySQL Usernimi ja MySQL Database (These are always required by the MySQL Server)");
define("LANINS_040", "virhes");
define("LANINS_041", "e107 was unable to establish a connection to the MySQL server using the information you entered. Please return to the last page ja ensure the information is correct.");
define("LANINS_042", "Connection to the MySQL server established ja verified.");
define("LANINS_043", "Unable to create tietokanta, please ensure you have the correct permissions to create tietokantas on your server.");
define("LANINS_044", "Successfully created tietokanta.");
define("LANINS_045", "Please click on the button to proceed to next stage.");
define("LANINS_046", "5");
define("LANINS_047", "Ylläpitäjäistrator Details");
define("LANINS_048", "Päivitä Takaisin To Viimeinen Step");
define("LANINS_049", "The two passwords you entered are not the same. Please go back ja try again.");
define("LANINS_050", "XML Extension");
define("LANINS_051", "Installed");
define("LANINS_052", "Not Installed");
define("LANINS_053", "e107 .700 requires the PHP XML Extension to be installed. Please contact your host or read the information at ");
define("LANINS_054", " before continuing");
define("LANINS_055", "Install Confirmation");
define("LANINS_056", "6");
define("LANINS_057", " e107 now has all the information it needs to complete the installation.

Please click the button to create the tietokanta tables ja save all your settings.

");
define("LANINS_058", "7");
define("LANINS_060", "Unable to read the sql datafile

Please ensure the file <b>core_sql.php</b> exists in the <b>/e107_admin/sql</b> directory.");
define("LANINS_061", "e107 was unable to create all of the required tietokanta tables.
Please clear the tietokanta ja rectify any problems before trying again.");

define("LANINS_062", "[b]Welcome to your new website![/b]
e107 has installed successfully ja is now ready to accept content.<br />Your administration section is [link=e107_admin/admin.php]located here[/link], click to go there now. You will have to login using the nimi ja password you entered during the installation process.

[b]Support[/b]
e107 Homepage: [link=http://e107.org]http://e107.org[/link], you will find the FAQ ja documentation here.
Keskustelualues: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]Tiedostojen lataus[/b]
Plugins: [link=http://e107coders.org]http://e107coders.org[/link]
Teemas: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

Thankyou for trying e107, we hope it fulfills your website needs.
(You can delete this message from your admin section.)");

define("LANINS_063", "Welcome to e107");

define("LANINS_069", "e107 has been successfully installed!

For security reasons you should now set the file permissions on the <b>e107_config.php</b> file back to 644.

Also please delete install.php ja the e107_install directory from your server after you have clicked the button below
");
define("LANINS_070", "e107 was unable to save the main config file to your server.

Please ensure the <b>e107_config.php</b> file has the correct permissions");
define("LANINS_071", "Finalising Installation");

define("LANINS_072", "Ylläpitäjä Usernimi");
define("LANINS_073", "This is the nimi you will use to login into the site. If you wish to use this as your display nimi also");
define("LANINS_074", "Ylläpitäjä Display Name");
define("LANINS_075", "This is the nimi that you wish your käyttäjäs to see displayed in your profile, forums ja other areas. If you wish to use the same as your login nimi then leave this blank.");
define("LANINS_076", "Ylläpitäjä Salasana");
define("LANINS_077", "Please type the admin password you wish to use here");
define("LANINS_078", "Ylläpitäjä Salasana Confirmation");
define("LANINS_079", "Please type the admin password again for confirmation");
define("LANINS_080", "Ylläpitäjä Sähköposti");
define("LANINS_081", "Enter your sähköpostiosoite");

define("LANINS_082", "käyttäjä@yoursite.com");

?>
