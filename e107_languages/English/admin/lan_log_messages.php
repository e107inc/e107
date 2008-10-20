<?php
// e107 Language File.
// $Id: lan_log_messages.php,v 1.14 2008-10-20 21:52:38 e107steved Exp $

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
define('LAN_ADMIN_LOG_002', 'Admin log - delete old data');
define('LAN_ADMIN_LOG_003', 'User Audit log - delete old data');

// User edits
//-----------
define('LAN_AL_USET_01', 'Admin edited user data');
define('LAN_AL_USET_02', 'User added by Admin');
define('LAN_AL_USET_03', 'User options updated');
define('LAN_AL_USET_04', 'Users pruned');
define('LAN_AL_USET_05', 'User banned');
define('LAN_AL_USET_06', 'User unbanned');
define('LAN_AL_USET_07', 'User deleted');
define('LAN_AL_USET_08', 'User made admin');
define('LAN_AL_USET_09', 'User admin status revoked');
define('LAN_AL_USET_10', 'User approved');
define('LAN_AL_USET_11', 'Resend validation email');
define('LAN_AL_USET_12', 'Resend all validation emails');
define('LAN_AL_USET_13', 'Bounced emails deleted');
define('LAN_AL_USET_14', 'Class membership updated');

// Userclass events
//------------------
define('LAN_AL_UCLASS_00',"Unknown userclass-related event");
define('LAN_AL_UCLASS_01',"Userclass created");
define('LAN_AL_UCLASS_02',"Userclass deleted");
define('LAN_AL_UCLASS_03',"Userclass edited");
define('LAN_AL_UCLASS_04',"Class membership updated");
define('LAN_AL_UCLASS_05',"Initial userclass settings edited");
define('LAN_AL_UCLASS_06',"Class membership emptied");

// Banlist events
//----------------
define('LAN_AL_BANLIST_00','Unknown ban-related event');
define('LAN_AL_BANLIST_01','Manual ban added');
define('LAN_AL_BANLIST_02','Ban deleted');
define('LAN_AL_BANLIST_03','Ban time changed');
define('LAN_AL_BANLIST_04','Whitelist entry added');
define('LAN_AL_BANLIST_05','Whitelist entry deleted');
define('LAN_AL_BANLIST_06','Banlist exported');
define('LAN_AL_BANLIST_07','Banlist imported');
define('LAN_AL_BANLIST_08','Banlist options updated');
define('LAN_AL_BANLIST_09','Banlist entry edited');
define('LAN_AL_BANLIST_10','Whitelist entry edited');
define('LAN_AL_BANLIST_11','Whitelist hit for ban entry');


// Comment-related events
//-----------------------
define('LAN_AL_COMMENT_01', 'Comment(s) deleted');

// Rolling log events
//-------------------
define('LAN_ROLL_LOG_01','Empty username and/or password');
define('LAN_ROLL_LOG_02','Incorrect image code entered');
define('LAN_ROLL_LOG_03','Invalid username/password combination');
define('LAN_ROLL_LOG_04','Invalid username entered');
define('LAN_ROLL_LOG_05','Login attempt by user not fully signed up');
define('LAN_ROLL_LOG_06','Login blocked by event trigger handler');
define('LAN_ROLL_LOG_07','Multiple logins from same address');
define('LAN_ROLL_LOG_08','Excessive username length');
define('LAN_ROLL_LOG_09','Banned user attempted login');
define('LAN_ROLL_LOG_10','Login fail - reason unknown');
define('LAN_ROLL_LOG_11','Admin login fail');

// Prefs events
//-------------
define('LAN_AL_PREFS_01', 'Preferences changed');


// Front Page events
//------------------
define('LAN_AL_FRONTPG_00', 'Unknown front page-related event');
define('LAN_AL_FRONTPG_01', 'Rules order changed');
define('LAN_AL_FRONTPG_02', 'Rule added');
define('LAN_AL_FRONTPG_03', 'Rule edited');
define('LAN_AL_FRONTPG_04', 'Rule deleted');
define('LAN_AL_FRONTPG_05', '');
define('LAN_AL_FRONTPG_06', '');


// User theme admin
//-----------------
define('LAN_AL_UTHEME_00', 'Unknown user theme related event');
define('LAN_AL_UTHEME_01', 'User theme settings changed');
define('LAN_AL_UTHEME_02', '');


// Update routines
//----------------
define('LAN_AL_UPDATE_00','Unknown software update related event');
define('LAN_AL_UPDATE_01','Update from 0.7 to 0.8 executed');
define('LAN_AL_UPDATE_02','Update from 0.7.x to 0.7.6 executed');
define('LAN_AL_UPDATE_03','Missing prefs added');


// Administrator routines
//-----------------------
define('LAN_AL_ADMIN_00','Unknown administrator event');
define('LAN_AL_ADMIN_01','Update admin permissions');
define('LAN_AL_ADMIN_02','Admin rights removed');
define('LAN_AL_ADMIN_03','');

?>
