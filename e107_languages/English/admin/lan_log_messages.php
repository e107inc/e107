<?php
// e107 Language File.
// $Id: lan_log_messages.php,v 1.27 2008-12-06 16:41:29 e107steved Exp $

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

// Maintenance mode
//-----------------
define('LAN_AL_MAINT_00','Unknown maintenance message');
define('LAN_AL_MAINT_01','Maintenance mode set');
define('LAN_AL_MAINT_02','Maintenance mode cleared');


// Sitelinks routines
//-------------------
define('LAN_AL_SLINKS_00','Unknown sitelinks message');
define('LAN_AL_SLINKS_01','Sublinks generated');
define('LAN_AL_SLINKS_02','Sitelink moved up');
define('LAN_AL_SLINKS_03','Sitelink moved down');
define('LAN_AL_SLINKS_04','Sitelink order updated');
define('LAN_AL_SLINKS_05','Sitelinks options updated');
define('LAN_AL_SLINKS_06','Sitelink deleted');
define('LAN_AL_SLINKS_07','Sitelink submitted');
define('LAN_AL_SLINKS_08','Sitelink updated');


// Theme manager routines
//-----------------------
define('LAN_AL_THEME_00','Unknown theme-related message');
define('LAN_AL_THEME_01','Site theme updated');
define('LAN_AL_THEME_02','Admin theme updated');
define('LAN_AL_THEME_03','Image preload/site CSS updated');
define('LAN_AL_THEME_04','Admin style/CSS updated');
define('LAN_AL_THEME_05','');


// Cache control routines
//-----------------------
define('LAN_AL_CACHE_00','Unknown cache-control message');
define('LAN_AL_CACHE_01','Cache settings updated');
define('LAN_AL_CACHE_02','System cache emptied');
define('LAN_AL_CACHE_03','Content cache emptied');
define('LAN_AL_CACHE_04','');


// Emote admin
//------------
define('LAN_AL_EMOTE_00','Unknown emote-related message');
define('LAN_AL_EMOTE_01','Active emote pack changed');
define('LAN_AL_EMOTE_02','Emotes activated');
define('LAN_AL_EMOTE_03','Emotes deactivated');


// Welcome message
//----------------
define('LAN_AL_WELCOME_00','Unknown welcome-related message');
define('LAN_AL_WELCOME_01','Welcome message created');
define('LAN_AL_WELCOME_02','Welcome message updated');
define('LAN_AL_WELCOME_03','Welcome message deleted');
define('LAN_AL_WELCOME_04','Welcome message options changed');
define('LAN_AL_WELCOME_05','');


// Admin Password
//---------------
define('LAN_AL_ADMINPW_01','Admin password changed');


// Banners Admin
//--------------
define('LAN_AL_BANNER_00','Unknown banner-related message');
define('LAN_AL_BANNER_01','Banner menu update');
define('LAN_AL_BANNER_02','Banner created');
define('LAN_AL_BANNER_03','Banner updated');
define('LAN_AL_BANNER_04','Banner deleted');
define('LAN_AL_BANNER_05','');

// Image management
//-----------------
define('LAN_AL_IMALAN_00','Unknown image-related message');
define('LAN_AL_IMALAN_01','Avatar deleted');
define('LAN_AL_IMALAN_02','All avatars and photos deleted');
define('LAN_AL_IMALAN_03','Avatar deleted');
define('LAN_AL_IMALAN_04','Settings updated');
define('LAN_AL_IMALAN_05','');
define('LAN_AL_IMALAN_06','');

// Language management
//--------------------
define('LAN_AL_LANG_00', 'Unknown language-related message');
define('LAN_AL_LANG_01', 'Language prefs changed');
define('LAN_AL_LANG_02', 'Language tables deleted');
define('LAN_AL_LANG_03', 'Language tables created');
define('LAN_AL_LANG_04', 'Language zip created');
define('LAN_AL_LANG_05', '');

// Meta Tags
//----------
define('LAN_AL_META_01', 'Meta tags updated');

// Downloads
//----------
define('LAN_AL_DOWNL_01', 'Download options changed');
define('LAN_AL_DOWNL_02', 'Download category created');
define('LAN_AL_DOWNL_03', 'Download category updated');
define('LAN_AL_DOWNL_04', 'Download category deleted');
define('LAN_AL_DOWNL_05', 'Download created');
define('LAN_AL_DOWNL_06', 'Download updated');
define('LAN_AL_DOWNL_07', 'Download deleted');
define('LAN_AL_DOWNL_08', 'Download category order updated');
define('LAN_AL_DOWNL_09', 'Download limit added');
define('LAN_AL_DOWNL_10', 'Download limit edited');
define('LAN_AL_DOWNL_11', 'Download limit deleted');
define('LAN_AL_DOWNL_12', 'Download mirror added');
define('LAN_AL_DOWNL_13', 'Download mirror updated');
define('LAN_AL_DOWNL_14', 'Download mirror deleted');
define('LAN_AL_DOWNL_15', '');

// Custom Pages/Menus
//-------------------
define('LAN_AL_CPAGE_01','Custom page/menu added');
define('LAN_AL_CPAGE_02','Custom page/menu updated');
define('LAN_AL_CPAGE_03','Custom page/menu deleted');
define('LAN_AL_CPAGE_04','Custom page/menu settings updated');

?>
