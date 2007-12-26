<?php
// e107 Language File.
// $Id: lan_log_messages.php,v 1.3 2007-12-26 13:21:34 e107steved Exp $

/*
The definitions in this file are for standard 'explanatory' messages which might be entered
into any of the system logs. They are in three groups with different prefixes:
	LAN_ADMIN_LOG_nnn - the admin log (records intentional actions by admins)
	LAN_AUDIT_LOG_nnn - the audit log (records actions, generally intentional, by users)
	LAN_ROLL_LOG_nnn - the rolling log (records extraneous events, debugging etc)
*/


// User audit trail events. For messages 11-30, the last 2 digits must match the define for the event type in the admin log
define('LAN_AUDIT_LOG_001', "Access by banned user");
define('LAN_AUDIT_LOG_002', "Flood protection activated");
define('LAN_AUDIT_LOG_003', 'Access from banned IP Address');
define('LAN_AUDIT_LOG_004', "");
define('LAN_AUDIT_LOG_005', "");
define('LAN_AUDIT_LOG_006', "User changed password");
define('LAN_AUDIT_LOG_007', "User changed email address");
define('LAN_AUDIT_LOG_008', "");
define('LAN_AUDIT_LOG_009', "");
define('LAN_AUDIT_LOG_010', 'User data changed by admin');
define('LAN_AUDIT_LOG_011', 'User signed up');
define('LAN_AUDIT_LOG_012', 'User confirmed registration');
define('LAN_AUDIT_LOG_013', 'User logged in');
define('LAN_AUDIT_LOG_014', 'User logged out');
define('LAN_AUDIT_LOG_015', 'User changed display name');
define('LAN_AUDIT_LOG_016', 'User changed password');
define('LAN_AUDIT_LOG_017', 'User changed email address');
define('LAN_AUDIT_LOG_018', '');
define('LAN_AUDIT_LOG_019', 'User changed settings');
define('LAN_AUDIT_LOG_020', "");


// Admin log events
//-----------------
define('LAN_ADMIN_LOG_001', 'Admin edited user data');
define('LAN_ADMIN_LOG_002', '');
define('LAN_ADMIN_LOG_003', '');
define('LAN_ADMIN_LOG_004', '');
define('LAN_ADMIN_LOG_005', '');
define('LAN_ADMIN_LOG_006', '');
define('LAN_ADMIN_LOG_007', '');
define('LAN_ADMIN_LOG_008', '');
define('LAN_ADMIN_LOG_009', '');
define('LAN_ADMIN_LOG_010', '');
define('LAN_ADMIN_LOG_011', '');

define('AL_UC_LAN_00',"Unknown userclass-related event");
define('AL_UC_LAN_01',"Userclass created");
define('AL_UC_LAN_02',"Userclass deleted");
define('AL_UC_LAN_03',"Userclass edited");
define('AL_UC_LAN_04',"Class membership updated");
define('AL_UC_LAN_05',"Initial userclass settings edited");
define('AL_UC_LAN_06',"Class membership emptied");

define('AL_BAN_LAN_00','Unknown ban-related event');
define('AL_BAN_LAN_01','Manual ban added');
define('AL_BAN_LAN_02','Ban deleted');
define('AL_BAN_LAN_03','Ban time changed');
define('AL_BAN_LAN_04','Whitelist entry added');
define('AL_BAN_LAN_05','Whitelist entry deleted');
define('AL_BAN_LAN_06','Banlist exported');
define('AL_BAN_LAN_07','Banlist imported');
define('AL_BAN_LAN_08','Banlist options updated');
define('AL_BAN_LAN_09','Banlist entry edited');
define('AL_BAN_LAN_10','Whitelist entry edited');
define('AL_BAN_LAN_11','Whitelist hit for ban entry');

// Rolling log events
//-------------------
define('LAN_ROLL_LOG_001', "Access by banned user");
define('LAN_ROLL_LOG_002', "Flood protection activated");
define('LAN_ROLL_LOG_003', "Invalid page access");
define('LAN_ROLL_LOG_004', "");
define('LAN_ROLL_LOG_005', "");
define('LAN_ROLL_LOG_006', "");
define('LAN_ROLL_LOG_007', "");
define('LAN_ROLL_LOG_008', "");
define('LAN_ROLL_LOG_009', "");
define('LAN_ROLL_LOG_010', "");
define('LAN_ROLL_LOG_011', "");
define('LAN_ROLL_LOG_012', "");
define('LAN_ROLL_LOG_013', "");

?>
