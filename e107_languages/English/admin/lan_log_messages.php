<?php
/*
 * Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Language File
 *
*/

/*
The definitions in this file are for standard "explanatory" messages which might be entered
into any of the system logs. They are in three groups with different prefixes:
	LAN_ADMIN_LOG_nnn - the admin log (records intentional actions by admins)
	LAN_AUDIT_LOG_nnn - the audit log (records actions, generally intentional, by users)
	LAN_ROLL_LOG_nnn - the rolling log (records extraneous events, debugging etc)
*/


// User audit trail events. For messages 11-30, the last 2 digits must match the define for the event type in the admin log class file
define("LAN_AUDIT_LOG_001", "Access by banned user");
define("LAN_AUDIT_LOG_002", "Flood protection activated");
define("LAN_AUDIT_LOG_003", "Access from banned IP Address");
define("LAN_AUDIT_LOG_004", "");
define("LAN_AUDIT_LOG_005", "");
define("LAN_AUDIT_LOG_006", "User changed password");
define("LAN_AUDIT_LOG_007", "User changed email address");
define("LAN_AUDIT_LOG_008", "");
define("LAN_AUDIT_LOG_009", "");
define("LAN_AUDIT_LOG_010", "User data changed by admin");
define("LAN_AUDIT_LOG_011", "User signed up");
define("LAN_AUDIT_LOG_012", "User confirmed registration");
define("LAN_AUDIT_LOG_013", "User login");
define("LAN_AUDIT_LOG_014", "User logout");
define("LAN_AUDIT_LOG_015", "User changed display name");
define("LAN_AUDIT_LOG_016", "User changed password");
define("LAN_AUDIT_LOG_017", "User changed email address");
define("LAN_AUDIT_LOG_018", "User password reset");
define("LAN_AUDIT_LOG_019", "User changed settings");
define("LAN_AUDIT_LOG_020", "User added by admin");
define("LAN_AUDIT_LOG_021", "User email bounce");
define("LAN_AUDIT_LOG_022", "User banned");
define("LAN_AUDIT_LOG_023", "User bounce reset");
define("LAN_AUDIT_LOG_024", "User temporary status");


// Admin log events
//-----------------
define("LAN_AL_ADLOG_01", "Admin log - prefs updated");
define("LAN_AL_ADLOG_02", "Admin log - delete old data");
define("LAN_AL_ADLOG_03", "User Audit log - delete old data");
define("LAN_AL_ADLOG_04", "User audit options updated");
define("LAN_AL_ADLOG_05", "");

// User edits
//-----------
define("LAN_AL_USET_01", "Admin edited user data");
define("LAN_AL_USET_02", "User added by Admin");
define("LAN_AL_USET_03", "User options updated");
define("LAN_AL_USET_04", "Users pruned");
define("LAN_AL_USET_05", "User banned");
define("LAN_AL_USET_06", "User unbanned");
define("LAN_AL_USET_07", "User deleted");
define("LAN_AL_USET_08", "User made admin");
define("LAN_AL_USET_09", "User admin status revoked");
define("LAN_AL_USET_10", "User approved");
define("LAN_AL_USET_11", "Resend validation email");
define("LAN_AL_USET_12", "Resend all validation emails");
define("LAN_AL_USET_13", "Bounced emails deleted");
define("LAN_AL_USET_14", "Class membership updated");
define("LAN_AL_USET_15", "Signup refused");				// Too many users at same IP address

// Userclass events
//------------------
define("LAN_AL_UCLASS_00", "Unknown userclass-related event");
define("LAN_AL_UCLASS_01", "Userclass created");
define("LAN_AL_UCLASS_02", "Userclass deleted");
define("LAN_AL_UCLASS_03", "Userclass edited");
define("LAN_AL_UCLASS_04", "Class membership updated");
define("LAN_AL_UCLASS_05", "Initial userclass settings edited");
define("LAN_AL_UCLASS_06", "Class membership emptied");

// Banlist events
//----------------
define("LAN_AL_BANLIST_00", "Unknown ban-related event");
define("LAN_AL_BANLIST_01", "Manual ban added");
define("LAN_AL_BANLIST_02", "Ban deleted");
define("LAN_AL_BANLIST_03", "Ban time changed");
define("LAN_AL_BANLIST_04", "Whitelist entry added");
define("LAN_AL_BANLIST_05", "Whitelist entry deleted");
define("LAN_AL_BANLIST_06", "Banlist exported");
define("LAN_AL_BANLIST_07", "Banlist imported");
define("LAN_AL_BANLIST_08", "Banlist options updated");
define("LAN_AL_BANLIST_09", "Banlist entry edited");
define("LAN_AL_BANLIST_10", "Whitelist entry edited");
define("LAN_AL_BANLIST_11", "Whitelist hit for ban entry");
define("LAN_AL_BANLIST_12", "Expired bans cleared");


// Comment-related events
//-----------------------
define("LAN_AL_COMMENT_01", "Comment(s) deleted");

// Rolling log events
//-------------------
define("LAN_ROLL_LOG_01", "Empty username and/or password");
define("LAN_ROLL_LOG_02", "Incorrect image code entered");
define("LAN_ROLL_LOG_03", "Invalid username/password combination");
define("LAN_ROLL_LOG_04", "Invalid username entered");
define("LAN_ROLL_LOG_05", "Login attempt by user not fully signed up");
define("LAN_ROLL_LOG_06", "Login blocked by event trigger handler");
define("LAN_ROLL_LOG_07", "Multiple logins from same address");
define("LAN_ROLL_LOG_08", "Excessive username length");
define("LAN_ROLL_LOG_09", "Banned user attempted login");
define("LAN_ROLL_LOG_10", "Login fail - reason unknown");
define("LAN_ROLL_LOG_11", "Admin login fail");

// Prefs events
//-------------
define("LAN_AL_PREFS_01", "Preferences changed");
define("LAN_AL_PREFS_02", "New Preferences created");
define("LAN_AL_PREFS_03", "Error saving prefs");


// Front Page events
//------------------
define("LAN_AL_FRONTPG_00", "Unknown front page-related event");
define("LAN_AL_FRONTPG_01", "Rules order changed");
define("LAN_AL_FRONTPG_02", "Rule added");
define("LAN_AL_FRONTPG_03", "Rule edited");
define("LAN_AL_FRONTPG_04", "Rule deleted");
define("LAN_AL_FRONTPG_05", "");
define("LAN_AL_FRONTPG_06", "");


// User theme admin
//-----------------
define("LAN_AL_UTHEME_00", "Unknown user theme related event");
define("LAN_AL_UTHEME_01", "User theme settings changed");
define("LAN_AL_UTHEME_02", "");


// Update routines
//----------------
define("LAN_AL_UPDATE_00", "Unknown software update related event");
define("LAN_AL_UPDATE_01", "Update from 1.0 to 2.0 executed");
define("LAN_AL_UPDATE_02", "Update from 0.7.x to 0.7.6 executed");
define("LAN_AL_UPDATE_03", "Missing prefs added");


// Administrator routines
//-----------------------
define("LAN_AL_ADMIN_00", "Unknown administrator event");
define("LAN_AL_ADMIN_01", "Update admin permissions");
define("LAN_AL_ADMIN_02", "Admin rights removed");
define("LAN_AL_ADMIN_03", "");

// Maintenance mode
//-----------------
define("LAN_AL_MAINT_00", "Unknown maintenance message");
define("LAN_AL_MAINT_01", "Maintenance mode set");
define("LAN_AL_MAINT_02", "Maintenance mode cleared");


// Sitelinks routines
//-------------------
define("LAN_AL_SLINKS_00", "Unknown sitelinks message");
define("LAN_AL_SLINKS_01", "Sublinks generated");
define("LAN_AL_SLINKS_02", "Sitelink moved up");
define("LAN_AL_SLINKS_03", "Sitelink moved down");
define("LAN_AL_SLINKS_04", "Sitelink order updated");
define("LAN_AL_SLINKS_05", "Sitelinks options updated");
define("LAN_AL_SLINKS_06", "Sitelink deleted");
define("LAN_AL_SLINKS_07", "Sitelink submitted");
define("LAN_AL_SLINKS_08", "Sitelink updated");


// Theme manager routines
//-----------------------
define("LAN_AL_THEME_00", "Unknown theme-related message");
define("LAN_AL_THEME_01", "Site theme updated");
define("LAN_AL_THEME_02", "Admin theme updated");
define("LAN_AL_THEME_03", "Image preload/site CSS updated");
define("LAN_AL_THEME_04", "Admin style/CSS updated");
define("LAN_AL_THEME_05", "");


// Cache control routines
//-----------------------
define("LAN_AL_CACHE_00", "Unknown cache-control message");
define("LAN_AL_CACHE_01", "Cache settings updated");
define("LAN_AL_CACHE_02", "System cache emptied");
define("LAN_AL_CACHE_03", "Content cache emptied");
define("LAN_AL_CACHE_04", "");


// Emote admin
//------------
define("LAN_AL_EMOTE_00", "Unknown emote-related message");
define("LAN_AL_EMOTE_01", "Active emote pack changed");
define("LAN_AL_EMOTE_02", "Emotes activated");
define("LAN_AL_EMOTE_03", "Emotes deactivated");


// Welcome message
//----------------
define("LAN_AL_WELCOME_00", "Unknown welcome-related message");
define("LAN_AL_WELCOME_01", "Welcome message created");
define("LAN_AL_WELCOME_02", "Welcome message updated");
define("LAN_AL_WELCOME_03", "Welcome message deleted");
define("LAN_AL_WELCOME_04", "Welcome message options changed");
define("LAN_AL_WELCOME_05", "");


// Admin Password
//---------------
define("LAN_AL_ADMINPW_01", "Admin password changed");
define("LAN_AL_ADMINPW_02", "Admin password rehashed");

// Banners Admin
//--------------
define("LAN_AL_BANNER_00", "Unknown banner-related message");
define("LAN_AL_BANNER_01", "Banner menu update");
define("LAN_AL_BANNER_02", "Banner created");
define("LAN_AL_BANNER_03", "Banner updated");
define("LAN_AL_BANNER_04", "Banner deleted");
define("LAN_AL_BANNER_05", "Banner configuration updated");
define("LAN_AL_BANNER_06", "");

// Image management
//-----------------
define("LAN_AL_IMALAN_00", "Unknown image-related message");
define("LAN_AL_IMALAN_01", "Avatar deleted");
define("LAN_AL_IMALAN_02", "All avatars and photos deleted");
define("LAN_AL_IMALAN_03", "Avatar deleted");
define("LAN_AL_IMALAN_04", "Settings updated");
define("LAN_AL_IMALAN_05", "");
define("LAN_AL_IMALAN_06", "");

// Language management
//--------------------
define("LAN_AL_LANG_00", "Unknown language-related message");
define("LAN_AL_LANG_01", "Language prefs changed");
define("LAN_AL_LANG_02", "Language tables deleted");
define("LAN_AL_LANG_03", "Language tables created");
define("LAN_AL_LANG_04", "Language zip created");
define("LAN_AL_LANG_05", "");

// Meta Tags
//----------
define("LAN_AL_META_01", "Meta tags updated");

// Downloads
//----------
/*
define("LAN_AL_DOWNL_01", "Download options changed");
define("LAN_AL_DOWNL_02", "Download category created");
define("LAN_AL_DOWNL_03", "Download category updated");
define("LAN_AL_DOWNL_04", "Download category deleted");
define("LAN_AL_DOWNL_05", "Download created");
define("LAN_AL_DOWNL_06", "Download updated");
define("LAN_AL_DOWNL_07", "Download deleted");
define("LAN_AL_DOWNL_08", "Download category order updated");
define("LAN_AL_DOWNL_09", "Download limit added");
define("LAN_AL_DOWNL_10", "Download limit edited");
define("LAN_AL_DOWNL_11", "Download limit deleted");
define("LAN_AL_DOWNL_12", "Download mirror added");
define("LAN_AL_DOWNL_13", "Download mirror updated");
define("LAN_AL_DOWNL_14", "Download mirror deleted");
define("LAN_AL_DOWNL_15", "");
*/

// Custom Pages/Menus
//-------------------
define("LAN_AL_CPAGE_01", "Custom page/menu added");
define("LAN_AL_CPAGE_02", "Custom page/menu updated");
define("LAN_AL_CPAGE_03", "Custom page/menu deleted");
define("LAN_AL_CPAGE_04", "Custom page/menu settings updated");

// Extended User Fields
//---------------------
define("LAN_AL_EUF_01", "EUF moved up");
define("LAN_AL_EUF_02", "EUF moved down");
define("LAN_AL_EUF_03", "EUF category moved up");
define("LAN_AL_EUF_04", "EUF category moved down");
define("LAN_AL_EUF_05", "Extended User Field added");
define("LAN_AL_EUF_06", "Extended User Field updated");
define("LAN_AL_EUF_07", "Extended User Field deleted");
define("LAN_AL_EUF_08", "EUF category added");
define("LAN_AL_EUF_09", "EUF category updated");
define("LAN_AL_EUF_10", "EUF category deleted");
define("LAN_AL_EUF_11", "Extended user fields activated");
define("LAN_AL_EUF_12", "Extended user fields deactivated");

// Menus
//------
define("LAN_AL_MENU_01", "Menu activated");
define("LAN_AL_MENU_02", "Menu - set visibility");
define("LAN_AL_MENU_03", "Menu - change area");
define("LAN_AL_MENU_04", "Menu deactivated");
define("LAN_AL_MENU_05", "Menu - move to top");
define("LAN_AL_MENU_06", "Menu - move to bottom");
define("LAN_AL_MENU_07", "Menu - move up");
define("LAN_AL_MENU_08", "Menu - move down");
define("LAN_AL_MENU_09", "");

// Public Uploads
//---------------
define("LAN_AL_UPLOAD_01", "Uploaded file deleted");
define("LAN_AL_UPLOAD_02", "Upload prefs changed");

// Search
//-------
define("LAN_AL_SEARCH_01", "Search settings updated");
define("LAN_AL_SEARCH_02", "Search prefs updated");
define("LAN_AL_SEARCH_03", "Search params auto-update");
define("LAN_AL_SEARCH_04", "Searchable areas updated");
define("LAN_AL_SEARCH_05", "Search handler settings updated");
define("LAN_AL_SEARCH_06", "");

// Notify
//-------
define("LAN_AL_NOTIFY_01", "Notify settings updated");

// News
//-----
define("LAN_AL_NEWS_01", "News item deleted");
define("LAN_AL_NEWS_02", "News category deleted");
define("LAN_AL_NEWS_03", "Submitted news deleted");
define("LAN_AL_NEWS_04", "News category created");
define("LAN_AL_NEWS_05", "News category updated");
define("LAN_AL_NEWS_06", "News preferences updated");
define("LAN_AL_NEWS_07", "Submitted news authorised");
define("LAN_AL_NEWS_08", "News item added");
define("LAN_AL_NEWS_09", "News item updated");
define("LAN_AL_NEWS_10", "News category rewrite changed");
define("LAN_AL_NEWS_11", "News category rewrite deleted");
define("LAN_AL_NEWS_12", "News rewrite changed");
define("LAN_AL_NEWS_13", "News rewrite deleted");



// File Manager
//-------------
define("LAN_AL_FILEMAN_01", "File(s) deleted");
define("LAN_AL_FILEMAN_02", "File(s) moved");
define("LAN_AL_FILEMAN_03", "File(s) uploaded");
define("LAN_AL_FILEMAN_04", "");

// Mail
//-----
define("LAN_AL_MAIL_01", "Test email sent");
define("LAN_AL_MAIL_02", "Mailshot created");
define("LAN_AL_MAIL_03", "Mail settings updated");
define("LAN_AL_MAIL_04", "Mailshot details deleted");
define("LAN_AL_MAIL_05", "Mail Database tidy");
define("LAN_AL_MAIL_06", "Mailout activated");
define("LAN_AL_MAIL_07", "");

// Plugin Manager
//---------------
define("LAN_AL_PLUGMAN_01", "Plugin installed");
define("LAN_AL_PLUGMAN_02", "Plugin updated");
define("LAN_AL_PLUGMAN_03", "Plugin uninstalled");
define("LAN_AL_PLUGMAN_04", "Plugin refreshed");

// URL Manager
//---------------
define("LAN_AL_EURL_01", "Site URL configuration changed");

// Sundry Pseudo-plugins - technically they"re plugins, but not worth the file overhead of treating them separately
//----------------------
define("LAN_AL_MISC_01", "Tree menu settings updated");
define("LAN_AL_MISC_02", "Online menu settings updated");
define("LAN_AL_MISC_03", "Login menu settings updated");
define("LAN_AL_MISC_04", "Comment menu settings updated");
define("LAN_AL_MISC_05", "Clock menu settings updated");
define("LAN_AL_MISC_06", "Blog calendar menu settings updated");
//define("LAN_AL_MISC_07", "");


define("LAN_AL_PING_01", "Ping to service");

define("LAN_AL_ADMINUI_01", "Admin-UI DB Table Insert: [x]");
define("LAN_AL_ADMINUI_02", "Admin-UI DB Table Update: [x]");
define("LAN_AL_ADMINUI_03", "Admin-UI DB Table Delete: [x]");
define("LAN_AL_ADMINUI_04", "Admin-UI DB Error: [x]");

define("LAN_AL_BACKUP", "Database backup");

define("LAN_AL_MEDIA_01", "Media Upload");

define("LAN_AL_USET_100", "Admin logged in as another user");
define("LAN_AL_USET_101", "Admin logged out as another user");
