<?php
// e107 Language File.
// $Id: lan_log_messages.php,v 1.5 2008-01-01 12:38:05 e107steved Exp $

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
define('LAN_AUDIT_LOG_018', 'User password reset');
define('LAN_AUDIT_LOG_019', 'User changed settings');
define('LAN_AUDIT_LOG_020', 'User added by admin');


// Admin log events
//-----------------
define('LAN_ADMIN_LOG_001', 'Admin edited user data');
define('LAN_ADMIN_LOG_002', 'Admin log - delete old data');
define('LAN_ADMIN_LOG_003', 'User Audit log - delete old data');
define('LAN_ADMIN_LOG_004', 'User added by Admin');
define('LAN_ADMIN_LOG_005', 'User options updated');
define('LAN_ADMIN_LOG_006', 'Users pruned');
define('LAN_ADMIN_LOG_007', 'User banned');
define('LAN_ADMIN_LOG_008', 'User unbanned');
define('LAN_ADMIN_LOG_009', 'User deleted');
define('LAN_ADMIN_LOG_010', 'User made admin');
define('LAN_ADMIN_LOG_011', 'User admin status revoked');
define('LAN_ADMIN_LOG_012', 'User approved');
define('LAN_ADMIN_LOG_013', 'Resend validation email');
define('LAN_ADMIN_LOG_014', 'Resend all validation emails');
define('LAN_ADMIN_LOG_015', 'Bounced emails deleted');
define('LAN_ADMIN_LOG_016', '');
define('LAN_ADMIN_LOG_017', '');
define('LAN_ADMIN_LOG_018', '');
define('LAN_ADMIN_LOG_019', '');

// Userclass events
//------------------
define('AL_UC_LAN_00',"Unknown userclass-related event");
define('AL_UC_LAN_01',"Userclass created");
define('AL_UC_LAN_02',"Userclass deleted");
define('AL_UC_LAN_03',"Userclass edited");
define('AL_UC_LAN_04',"Class membership updated");
define('AL_UC_LAN_05',"Initial userclass settings edited");
define('AL_UC_LAN_06',"Class membership emptied");

// Banlist events
//----------------
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
define('LAN_ROLL_LOG_01','Empty username and/or password');
define('LAN_ROLL_LOG_02','Incorrect image code entered');
define('LAN_ROLL_LOG_03','Invalid username/password combination');
define('LAN_ROLL_LOG_04','Invalid username entered');
define('LAN_ROLL_LOG_05','Login attempt by user not fully signed up');
define('LAN_ROLL_LOG_06','Login blocked by event trigger handler');
define('LAN_ROLL_LOG_07','Multiple logins from same address');
define('LAN_ROLL_LOG_08','');
define('LAN_ROLL_LOG_09','');
define('LAN_ROLL_LOG_10','');


?>
