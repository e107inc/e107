<?php
/*
 * Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Language File
 *
*/

// define("BANLAN_1", "Ban removed.");
// define("BANLAN_2", "No bans in list.");
// define("BANLAN_3", "Existing Bans");
// define("BANLAN_4", "Remove ban");
define("BANLAN_5", "Enter IP, email address, or host");
define("BANLAN_7", "Reason");
// define("BANLAN_8", "Ban Address");
define("BANLAN_9", "Ban users from site by email, IP or host address");
define("BANLAN_10", "IP / Email / Reason");
define("BANLAN_11", "Auto-ban: More than 10 failed login attempts");
// define("BANLAN_12", "Note: Reverse DNS is currently disabled; it must be enabled to allow banning by host.  Banning by IP and email address will still function normally.");
// define("BANLAN_13", "Note: To ban a user by user name, go to the users admin page: ");
// define("BANLAN_14", "Ban List");
define("BANLAN_15", "Messages/Ban Periods");
define("BANLAN_16", "Banning");
// define("BANLAN_17", "Ban Date");
// define("BANLAN_18", "Ban expires");
define("BANLAN_19", "Notes");
// define("BANLAN_20", "Type");
//define("BANLAN_21", "Never");
// define("BANLAN_22", "Unknown");
define("BANLAN_23", "day(s)");
define("BANLAN_24", "hours");
// define("BANLAN_25", "Add to Banlist");
// define("BANLAN_26", "Currently ");
// define("BANLAN_27", "Invalid characters in IP address stripped - now:");
define("BANLAN_28", "Ban type");
define("BANLAN_29", "Message to show to banned user");
define("BANLAN_30", "Ban duration");
define("BANLAN_31", "(Use an empty message if you wish the user to get a blank screen)");
define("BANLAN_32", "Indefinite");
//define("BANLAN_33", "Settings Updated");
define("BANLAN_34", "Expired");
define("BANLAN_35", "Import/Export");
define("BANLAN_36", "Export Types");
define("BANLAN_37", "Field Separator");
define("BANLAN_38", "Quote (round each value)");
// define("BANLAN_39", "Export");
define("BANLAN_40", "Banlist Export");
define("BANLAN_41", "Banlist Import");
define("BANLAN_42", "Import Choices");
define("BANLAN_43", "Replace all existing imported bans");
define("BANLAN_44", "Use expiry date/time from import");
// define("BANLAN_45", "Import");
define("BANLAN_46", "Import File:");
define("BANLAN_47", "File upload error");
define("BANLAN_48", "Deleted [y] expired ban list entries");
define("BANLAN_49", "CSV import: Unbalanced quotes in line ");
define("BANLAN_50", "CSV import: Error writing banlist record at line ");
define("BANLAN_51", "CSV import: Success, [y] lines imported from file ");
define("BANLAN_52", "Whitelist");
define("BANLAN_53", "Add to Whitelist");
define("BANLAN_54", "No entries in whitelist");
define("BANLAN_55", "Entry Date");
define("BANLAN_56", "IP/Email, User");
define("BANLAN_57", "User");
define("BANLAN_58", "Add users to the whitelist");
define("BANLAN_59", "Edit existing whitelist entry");
define("BANLAN_60", "Edit existing banlist entry");
define("BANLAN_61", "Existing Whitelist entries");
// define("BANLAN_62", "Options");
define("BANLAN_63", "Use reverse DNS to allow host banning");
define("BANLAN_64", "Reverse DNS accesses when adding ban");
define("BANLAN_65", "Turning this option on will allow you to ban users by hostname, rather then just IP or email address.  <br />NOTE: This may affect pageload times on some hosts, or if a server isn't responding");
define("BANLAN_66", "When a ban occurs, this option adds the domain of the banned address to the reason");
define("BANLAN_67", "Set maximum access rate");
define("BANLAN_68", "This determines the maximum number of site accesses in a 5-minute period");
define("BANLAN_69", "for members");
define("BANLAN_70", "for guests");
define("BANLAN_71", "Retrigger ban period");
define("BANLAN_72", "Ban Options");
define("BANLAN_73", "This will restart the ban period if a banned user accesses the site");
define("BANLAN_74", "Banlist Maintenance");
define("BANLAN_75", "Remove expired bans from list");
define("BANLAN_76", "Execute");
define("BANLAN_77", "Messages/Ban Periods");
define("BANLAN_78", "Hit count exceeded ([x] requests within allotted time)");
define("BANLAN_79", "CSV Export format:");
define("BANLAN_80", "CSV Import format:");
define("BANLAN_81", "Ban Action Log");
define("BANLAN_82", "No entries in Ban Action Log");
define("BANLAN_83", "Date/Time");
define("BANLAN_84", "IP Address");
define("BANLAN_85", "Additional information");
define("BANLAN_86", "Ban-related events");
define("BANLAN_87", "Total [y] entries in list");
define("BANLAN_88", "Empty Ban Action Log");
define("BANLAN_89", "Log File Deleted");
define("BANLAN_90", "Error deleting log file");
define("BANLAN_91", "Date/time format for ban log");
define("BANLAN_92", "See the strftime function page at php.net");
define("BANLAN_93", "");

// Ban types - block reserved 100-109
define("BANLAN_100", "Unknown");
define("BANLAN_101", "Manual");
define("BANLAN_102", "Flood");
define("BANLAN_103", "Hit count");
define("BANLAN_104", "Login failure");
define("BANLAN_105", "Imported");
define("BANLAN_106", "User");
define("BANLAN_107", "Unknown");
define("BANLAN_108", "Unknown");
define("BANLAN_109", "Old");

// Detailed explanations for ban types - block reserved 110-119
define("BANLAN_110", "Most likely a ban that was imposed before e107 was upgraded from 0.7.x");
define("BANLAN_111", "Entered by an admin");
define("BANLAN_112", "Attempts to update the site too fast");
define("BANLAN_113", "Attempts to access the site too frequently from the same address");
define("BANLAN_114", "Multiple failed login attempts from the same user");
define("BANLAN_115", "Added from an external list");
define("BANLAN_116", "IP address banned on account of user ban");
define("BANLAN_117", "Spare reason");
define("BANLAN_118", "Spare reason");
define("BANLAN_119", "Indicates an import error - previously imported bans");

define("BANLAN_120", "Whitelist entry");
define("BANLAN_121", "Blacklist entry");
define("BANLAN_122", "Blacklist");
define("BANLAN_123", "Add to Blacklist");
define("BANLAN_124", "Expires");   // not ban_lan_34
define("BANLAN_125", "Use my IP");
define("BANLAN_126", "IP / Email");
define("BANLAN_127", "Delete all [x] failed logins from database");


