<?php

define('LANINS_TITLE',	'Installing e107');
define('LANINS_000',	'Invalid stage information detected! Installation halted.');
define('LANINS_001',	'Version %1$s');
define('LANINS_002',	'Installation');
define('LANINS_002a',	'(Step %1$s of 7)');
define('LANINS_003',	'1');
define('LANINS_004',	'Language Selection');
define('LANINS_004a',	'Language selected');
define('LANINS_004b',	'Language');
define('LANINS_005',	'Please choose language to use during installation procedure');
define('LANINS_006',	'Set Language');
define('LANINS_007',	'4');
define('LANINS_008',	'PHP version, mySQL version &amp; File permissions Check');
define('LANINS_008a',	'Compatibility &amp; File permissions');
define('LANINS_009',	'Retest File Permissions');
define('LANINS_010',	'File not writable: ');
define('LANINS_010a',	'Folder not writable: ');
define('LANINS_011',	'Error');
define('LANINS_012',	'MySQL Functions don\'t seem to exist. This probably means that either the MySQL PHP Extension isn\'t installed or your PHP installation wasn\'t compiled with MySQL support.'); // help for 012
define('LANINS_013',	'Couldn\'t determine your MySQL version number. This is a non fatal error, so please continue installing, but be aware that e107 requires MySQL >= 3.23 to function correctly.');
define('LANINS_014',	'File Permissions');
define('LANINS_015',	'PHP Version');
define('LANINS_016',	'MySQL');
define('LANINS_017',	'PASS');
define('LANINS_018',	'Ensure all the listed files exist and are writable by the server. This normally involves CHMODing them 777, but environments vary - contact your host if you have any problems.');
define('LANINS_019',	'The version of PHP installed on your server isn\'t capable of running e107. e107 requires a PHP version of at least 4.3.0 to run correctly. Either upgrade your PHP version, or contact your host for an upgrade.');
define('LANINS_020',	'Continue Installation');
define('LANINS_021',	'2');
define('LANINS_022',	'MySQL Server Details');
define('LANINS_022a',	'Database');
define('LANINS_023',	'Please enter your MySQL settings here.<br /><br />If you have root permissions you can create a new database by ticking the box, if not you must create a database or use a pre-existing one.<br /><br />If you have only one database use a prefix so that other scripts can share the same database.<br />If you do not know your MySQL details contact your web host.');
define('LANINS_024',	'MySQL Server:');
define('LANINS_025',	'MySQL Username:');
define('LANINS_026',	'MySQL Password:');
define('LANINS_027',	'MySQL Database:');
define('LANINS_028',	'Create Database?');
define('LANINS_029',	'Table prefix:');
define('LANINS_030',	'The MySQL server you would like e107 to use. It can also include a port number. e.g. “hostname:port” or a path to a local socket e.g. \':/path/to/socket\' for the localhost.');
define('LANINS_031',	'The username you wish e107 to use for connecting to your MySQL server');
define('LANINS_032',	'The Password for the user you just entered');
define('LANINS_033',	'The MySQL database you wish e107 to reside in, sometimes referred to as a schema. If the user has database create permissions you can opt to create the database automatically if it doesn\'t already exist.');
define('LANINS_034',	'The prefix you wish e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one database schema.');
define('LANINS_035',	'Continue');
define('LANINS_036',	'3');
define('LANINS_037',	'MySQL Connection Verification');
define('LANINS_038',	' and Database Creation');
define('LANINS_038a',	'Database Validation');
define('LANINS_039',	'Please make sure you fill in all fields, most importantly, MySQL Server, MySQL Username and MySQL Database (These are always required by the MySQL Server)');
define('LANINS_040',	'Errors');
define('LANINS_041',	'e107 was unable to establish a connection to the MySQL server using the information you entered.<br />Please ensure the information is correct!');
define('LANINS_042',	'Connection to the MySQL server established and verified.');
define('LANINS_043',	'Unable to create database, please ensure you have the correct permissions to create databases on your server.');
define('LANINS_044',	'Successfully created database.');
define('LANINS_045',	'Please click on the button to proceed to next stage.');
define('LANINS_046',	'5');
define('LANINS_047',	'Administrator Details');
define('LANINS_047a',	'Administration');
define('LANINS_048',	'Return to the previous step');
define('LANINS_049',	'The two passwords you entered are not the same! Please, go back and try again.');
define('LANINS_050',	'XML Extension');
define('LANINS_051',	'Installed');
define('LANINS_052',	'Not Installed');
define('LANINS_053',	'e107 requires the PHP XML Extension to be installed. Please contact your host or read the information at <a href="http://php.net/manual/en/ref.xml.php" target="_blank">php.net</a> before continuing');
define('LANINS_054',	'The chosen database successfully checked for existence.');
define('LANINS_055',	'Install Confirmation');
define('LANINS_055a',	'Confirm');
define('LANINS_056',	'6');
define('LANINS_057',	'e107 now has all the information it needs to complete the installation.<br /><br />Please click the button to create the database tables and save all your settings.');
define('LANINS_058',	'7');
define('LANINS_060',	'Unable to read the sql datafile<br /><br /><br />Please ensure the file <b>core_sql.php</b> exists in the <b>/e107_admin/sql</b> directory.');
define('LANINS_061',	'e107 was unable to create all of the required database tables.<br /><br />Please clear the database and rectify any problems before trying again.');
define('LANINS_062',	'');

define('LANINS_063',	'Welcome to e107');
define('LANINS_069',	'e107 has been successfully installed!<br /><br />For security reasons you should set <b>e107_config.php</b> file permissions to 644.<br /><br />Do not forget to delete install.php from your server after you click the button below.');
define('LANINS_070',	'e107 was unable to save the main config file to your server.<br /><br />Please ensure the <b>e107_config.php</b> file has the correct permissions');
define('LANINS_071',	'Finalizing Installation');
define('LANINS_071a',	'Done');
define('LANINS_071b',	'Error while Finalizing Installation');
define('LANINS_071c',	'Done with errors');
define('LANINS_072',	'Admin Username');
define('LANINS_073',	'This name will be used for website logins');
define('LANINS_074',	'Admin Display Name');
define('LANINS_075',	'This name will be shown to the users in your profile, forums and other areas. Leave this field blank if you wish to show your real username.');
define('LANINS_076',	'Admin Password');
define('LANINS_077',	'Please type the admin password you wish to use here');
define('LANINS_078',	'Admin Password Confirmation');
define('LANINS_079',	'Please type the admin password again for confirmation');
define('LANINS_080',	'Admin e-mail');
define('LANINS_081',	'Enter your e-mail address');
define('LANINS_082',	'admin@yoursite.com');
define('LANINS_083',	'mySQL Reported Error');
define('LANINS_084',	'The installer could not establish a connection to the database');
define('LANINS_085',	'The installer could not select database:');
define('LANINS_086',	'The Admin Username, Admin Password and Admin Email fields are <b>mandatory</b>! Please, enter correct information.');
define('LANINS_087',	'Misc');
define('LANINS_088',	'Home');
define('LANINS_089',	'Downloads');
define('LANINS_090',	'Members');
define('LANINS_091',	'Submit News');
define('LANINS_092',	'Contact Us');
define('LANINS_093',	'Grants access to private menu items');
define('LANINS_094',	'Example private forum class');
define('LANINS_095',	'Integrity Check');
define('LANINS_096',	'Latest Comments');
define('LANINS_097',	'[more ...]');
define('LANINS_098',	'News');
define('LANINS_099',	'e107 CMS');
define('LANINS_100',	'Latest Forum Posts');
define('LANINS_101',	'Update menu Settings');
define('LANINS_102',	'Date / Time');
define('LANINS_103',	'e107 Plugins');
define('LANINS_104',	'Checked');
define('LANINS_105',	'A database name or prefix beginning with some digits followed by \'e\' or \'E\' is not acceptable.  <br />A database name or prefix can not be empty.');
define('LANINS_106',	'WARNING - e107 cannot write to the directories and/or files listed. While this will not stop e107 installing, it will mean that certain features are not available.<br /><br />You will need to change the file permissions to use these features');
define('LANINS_107',	'e107_config.php is not an empty file');
define('LANINS_108',	'Possibly you have an existing installation');

define('LANINS_DB_UTF8_LABEL',   'Force UTF-8 Database?');
define('LANINS_DB_UTF8_CAPTION', 'MySQL Charset:');
define('LANINS_DB_UTF8_TOOLTIP', 'If set, the installation script will make the database UTF-8 compatible if possible. UTF-8 Database are required for the next major e107 version.');

// v1.0 - Be sure to re-check ALL translations above for modifications and additions. 

define('LANINS_109',	'Inititated');
define('LANINS_110',	'Complete');
define('LANINS_111',	'e107 Themes');
define('LANINS_112',	'e107 Handbook');
define('LANINS_113',	'');

define('LANINS_121',	'e107_config.php currently exists!');
define('LANINS_122',	'Possibly you have an existing installation');
define('LANINS_123',	'Debug info');
define('LANINS_124',	'Backtrace');
define('LANINS_125',	'Invalid step');
define('LANINS_125a',	'Error');

define('LANINS_WELCOME', '[b]Welcome to your new website![/b]

e107 has been installed successfully and is now ready to accept content. Your administration section is [link=e107_admin/admin.php]located here[/link], click to go there now. You will need to login using the name and password which you entered during the installation process.

[b]Support[/b]
[link=http://e107.org/]e107 Homepage[/link]
[link=http://e107.org/support]e107 Forums[/link]
[link=http://wiki.e107.org/]e107 Handbook[/link]

Thank you for trying e107, we hope it fulfills your website needs.');

define('LANINS_NEWS', '[b]Welcome![/b]
e107 is a content management system written in PHP and using the popular open source MySQL database system for content storage. It is completely free, totally customizable and in constant development.

[list][link=http://e107.org/content/Learn-all-about-e107]Everything you need to know about e107[/link]*[link=http://e107.org/content/About-Us:The-Team]Dev Team | Translators | Support Team[/link]*[link=http://wiki.e107.org/]Documentation Wiki[/link][/list]');
