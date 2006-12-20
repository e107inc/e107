<?php

define("LANINS_001", "e107 安裝指引");


define("LANINS_002", "步驟");
define("LANINS_003", "1");
define("LANINS_004", "選擇語系");
define("LANINS_005", "請選擇安裝介面的語系");
define("LANINS_006", "設定語言");
define("LANINS_007", "4");
define("LANINS_008", "PHP &amp; MySQL 版本確認 / 檔案權限確認");
define("LANINS_009", "重新設定檔案權限");
define("LANINS_010", "無寫入權限的檔案: ");
define("LANINS_010a", "無寫入權限的資料夾: ");
define("LANINS_011", "發生錯誤");
define("LANINS_012", "MySQL 相關函數不存在. This probably means that either the MySQL PHP Extension isn't installed or your PHP installation wasn't compiled with MySQL support."); // help for 012
define("LANINS_013", "無法找出您的 MySQL 版本. This is a non fatal error, so please continue installing, but be aware that e107 requires MySQL >= 3.23 to function correctly.");
define("LANINS_014", "檔案權限");
define("LANINS_015", "PHP V版本");
define("LANINS_016", "MySQL");
define("LANINS_017", "密碼");
define("LANINS_018", "Ensure all the listed files exist and are writable by the server. This normally involves CHMODing them 777, but environments vary - contact your host if you have any problems.");
define("LANINS_019", "The version of PHP installed on your server isn't capable of running e107. e107 requires a PHP version of at least 4.3.0 to run correctly. Either upgrade your PHP version, or contact your host for an upgrade.");
define("LANINS_020", "繼續安裝");
define("LANINS_021", "2");
define("LANINS_022", "MySQL 伺服器細節");
define("LANINS_023", "請輸入 MySQL 設定.

If you have root permissions you can create a new database by ticking the box, if not you must create a database or use a pre-existing one.

If you have only one database use a prefix so that other scripts can share the same database.
If you do not know your MySQL details contact your web host.");
define("LANINS_024", "MySQL 伺服器:");
define("LANINS_025", "MySQL 帳號:");
define("LANINS_026", "MySQL 密碼:");
define("LANINS_027", "MySQL 資料庫:");
define("LANINS_028", "新增資料庫?");
define("LANINS_029", "資料表前置文字:");
define("LANINS_030", "The MySQL server you would like e107 to use. It can also include a port number. e.g. \"hostname:port\" or a path to a local socket e.g. \":/path/to/socket\" for the localhost.");
define("LANINS_031", "The username you wish e107 to use for connecting to your MySQL server");
define("LANINS_032", "The Password for the user you just entered");
define("LANINS_033", "The MySQL database you wish e107 to reside in, sometimes referred to as a schema. If the user has database create permissions you can opt to create the database automatically if it doesn't already exist.");
define("LANINS_034", "The prefix you wish e107 to use when creating the e107 tables. Useful for multiple installs of e107 in one database schema.");
define("LANINS_035", "繼續");
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
define("LANINS_051", "已安裝");
define("LANINS_052", "尚未安裝");
define("LANINS_053", "e107 .700 requires the PHP XML Extension to be installed. Please contact your host or read the information at ");
define("LANINS_054", " before continuing");
define("LANINS_055", "安裝確認");
define("LANINS_056", "6");
define("LANINS_057", " e107 now has all the information it needs to complete the installation.

Please click the button to create the database tables and save all your settings.

");
define("LANINS_058", "7");
define("LANINS_060", "無法讀取 sql 資料檔案

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

define("LANINS_063", "歡迎使用 e107");

define("LANINS_069", "e107 已完成安裝!

請您重新設定檔案權限 <b>e107_config.php</b> 回到 644.

Also please delete install.php and the e107_install directory from your server after you have clicked the button below
");
define("LANINS_070", "e107 was unable to save the main config file to your server.

Please ensure the <b>e107_config.php</b> file has the correct permissions");
define("LANINS_071", "最後安裝步驟");

define("LANINS_072", "管理員名稱");
define("LANINS_073", "This is the name you will use to login into the site. If you wish to use this as your display name also");
define("LANINS_074", "管理員顯示名稱");
define("LANINS_075", "This is the name that you wish your users to see displayed in your profile, forums and other areas. If you wish to use the same as your login name then leave this blank.");
define("LANINS_076", "站長密碼");
define("LANINS_077", "Please type the admin password you wish to use here");
define("LANINS_078", "Admin Password Confirmation");
define("LANINS_079", "Please type the admin password again for confirmation");
define("LANINS_080", "管理員的信件");
define("LANINS_081", "輸入您的電子信件");

define("LANINS_082", "user@yoursite.com");

// Better table creation error reporting
define("LANINS_083", "MySQL 錯誤報告:");
define("LANINS_084", "無法建立連線");
define("LANINS_085", "無法選擇資料庫:");

?>