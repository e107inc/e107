<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/lan_banlist.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-12-09 16:42:23 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
define("BANLAN_1", "Ban removed.");
define("BANLAN_2", "No bans.");
define("BANLAN_3", "Existing Bans");
define("BANLAN_4", "Remove ban");
define("BANLAN_5", "Enter IP, email address, or host");
define("BANLAN_7", "Reason");
define("BANLAN_8", "Ban Address");
define("BANLAN_9", "Ban users from site by email, IP or host address");
define("BANLAN_10", "IP / Email / Reason");
define("BANLAN_11", "Auto-ban: More than 10 failed login attempts");
define("BANLAN_12", "Note: Reverse DNS is currently disabled; it must be enabled to allow banning by host.  Banning by IP and email address will still function normally.");
define("BANLAN_13", "Note: To ban a user by user name, go to the users admin page: ");
define('BANLAN_14','Ban List');
define('BANLAN_15','Options');
define('BANLAN_16','Banning');
define('BANLAN_17','Ban Date');
define('BANLAN_18','Ban expires');
define('BANLAN_19','Notes');
define('BANLAN_20','Type');
define('BANLAN_21','Never');
define('BANLAN_22','Unknown');
define('BANLAN_23','day(s)');
define('BANLAN_24','hours');
define('BANLAN_25','Add an entry');
define('BANLAN_26','Currently ');
define('BANLAN_27','Invalid characters in IP address stripped - now:');
define('BANLAN_28','Ban type');
define('BANLAN_29','Message to show');
define('BANLAN_30','Ban duration');
define('BANLAN_31','(Use an empty message if you wish the user to get a blank screen)');
define('BANLAN_32','Indefinite');
define('BANLAN_33','Settings Updated');
define('BANLAN_34','Expired');
define('BANLAN_35','');
define('BANLAN_36','');
define('BANLAN_37','');
define('BANLAN_38','');
define('BANLAN_39','');
define('BANLAN_40','');

// Ban types - block reserved 100-109 
define('BANLAN_100', 'Unknown');
define('BANLAN_101','Manual');
define('BANLAN_102','Flood');
define('BANLAN_103','Hit count');
define('BANLAN_104', 'Login failure');
define('BANLAN_105', 'Imported');
define('BANLAN_106', 'User');
define('BANLAN_107', 'Unknown');
define('BANLAN_108', 'Unknown');
define('BANLAN_109', 'Unknown');

// Detailed explanations for ban types - block reserved 110-119
define('BANLAN_110', 'Most likely a ban that was imposed before E107 was upgraded to 0.8');
define('BANLAN_111', 'Entered by an admin');
define('BANLAN_112', 'Attempts to update the site too fast');
define('BANLAN_113', 'Attempts to access the site too frequently from the same address');
define('BANLAN_114', 'Multiple failed login attempts from the same user');
define('BANLAN_115', 'Added from an external list');
define('BANLAN_116', 'IP address banned on account of user ban');
define('BANLAN_117', 'Spare reason');
define('BANLAN_118', 'Spare reason');
define('BANLAN_119', 'Spare reason');

define('BANLAN_120', 'Unknown');

?>