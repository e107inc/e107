<?php

define("LANINS_001", "e107 instalacija");
define("LANINS_002", "Faza ");
define("LANINS_003", "1");
define("LANINS_004", "Odabir jezika");
define("LANINS_005", "Odaberite jezik koji æe se koristiti tokom instalacijske procedure");
define("LANINS_006", "Odaberi jezik");
define("LANINS_007", "4");
define("LANINS_008", "PHP &amp; MySQL Provjera verzije / Provjera dopuštenja datoteka");
define("LANINS_009", "Provjeri dopuštenja datoteka");
define("LANINS_010", "Datoteka nije pisiva: ");
define("LANINS_010a", "Mapa nije pisiva: ");
define("LANINS_011", "Greška");
define("LANINS_012", "MySQL funkcije ne postoje. To znaèi da MySQL PHP ekstenzija nije instalirana ili PHP instalacija nije kompajlirana sa MySQL podrškom."); // help for 012
define("LANINS_013", "Nije moguæe odrediti vašu MySQL verziju. Ovo nije kobna greška, stoga možete nastaviti sa instalacijom, no budite svjesni da zahtjeva MySQL verziju veæu od 3.23 kako bi ispravno radio.");
define("LANINS_014", "Dopuštenje datoteka");
define("LANINS_015", "PHP Verzija");
define("LANINS_016", "MySQL");
define("LANINS_017", "ISPRAVNO");
define("LANINS_018", "Pobrinite se da sve izlistane datoteke postoje te da su pisive, kako bi mogle biti promjenjene. Ovo normalno obuhvaæa CHMOD-iranje datoteka u 777, ali okruženja mogu varirati - kontaktirajte svog poslužitelja ako imate problema.");
define("LANINS_019", "Verzija PHP-a pronaðena na serveru nije u moguænosti pokretati e107. e107 zahtjeva PHP verziju minimalno 4.3.0 kako bi ispravno radio. Možete ažurirati vasu PHP verziju, ili kontaktirati poslužitelja te prijaviti problem.");
define("LANINS_020", "Nastavak instalacije");
define("LANINS_021", "2");
define("LANINS_022", "Detalji MySQL servera");
define("LANINS_023", "Molimo ovdje unesite postavke vašeg MySQL-a.Ako imate potpuna dopuštenja, možete napraviti novu bazu podataka tako da aktivirate polje. Ako nemate dopuštenja, morate napraviti bazu podataka ili koristiti postojeæu. Ako imate samo jednu bazu podataka, koristite prefiks kako bi druge skripte djelile istu bazu. Ako ne znate vase MySQL detalje, kontaktirajte vašeg web poslužitelja.");
define("LANINS_024", "MySQL Server:");
define("LANINS_025", "MySQL Korisnièko ime:");
define("LANINS_026", "MySQL Lozinka:");
define("LANINS_027", "MySQL Baza podataka:");
define("LANINS_028", "Kreiraj bazu podataka?");
define("LANINS_029", "Prefiks tabele:");
define("LANINS_030", "MySQL server koji želite da e107 koristi. Takoðer može sadržavati i broj porta npr. \"hostname:port\" ili putanju do lokalnog socket-a e.g. \":/path/to/socket\" za localhost.");
define("LANINS_031", "Korisnièko ime koje želite da e107 koristi za spajanje na Vaš MySQL server");
define("LANINS_032", "Lozinka za korisnika koju ste upravo unijeli");
define("LANINS_033", "The MySQL database you wish e107 to reside in, sometimes refered to as a schema. If the user has database create permissions you can opt to create the database automatically if it doesn't already exsist.");
define("LANINS_034", "The prefix you wish e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one database schema.");
define("LANINS_035", "Continue");
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

define("LANINS_062", "[b]Welcome to your new website![/b]
e107 has installed successfully and is now ready to accept content.<br />Your administration section is [link=e107_admin/admin.php]located here[/link], click to go there now. You will have to login using the name and password you entered during the installation process.

[b]Support[/b]
e107 Homepage: [link=http://e107.org]http://e107.org[/link], you will find the FAQ and documentation here.
Forums: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]Downloads[/b]
Plugins: [link=http://e107coders.org]http://e107coders.org[/link]
Themes: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

Thankyou for trying e107, we hope it fulfills your website needs.
(You can delete this message from your admin section.)");

define("LANINS_063", "Welcome to e107");

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