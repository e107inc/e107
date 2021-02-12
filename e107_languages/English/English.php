<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * English language file - generic terms and system LAN
 *
*/
setlocale(LC_ALL, 'en_GB.UTF-8', 'en_GB.utf8', 'eng_eng.utf8', 'en');
define("CORE_LC", "en");
define("CORE_LC2", "gb");
// define("TEXTDIRECTION","rtl");
// deprecated: all language packs must be UTF-8
//define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1", "Error : theme is missing.\\n\\nChange the used themes in your preferences (admin area) or upload files for the current theme to the server.");
//obsolete define("CORE_LAN2'," \\1 wrote:");// "\\1" represents the username.
//obsolete define("CORE_LAN3", "file attachment disabled");				// Not used in 0.8
define("CORE_LAN4", "Please delete install.php from your server");
define("CORE_LAN5", "if you do not there is a potential security risk to your website");
define("CORE_LAN6", "The flood protection on this site has been activated and you are warned that if you carry on requesting pages you could be banned.");
define("CORE_LAN7", "Core is attempting to restore prefs from automatic backup.");
define("CORE_LAN8", "Core Prefs Error");
define("CORE_LAN9", "Core could not restore from automatic backup. Execution halted.");
define("CORE_LAN10", "Corrupted cookie detected - logged out.");

// Footer
define("CORE_LAN11", "Render time: ");
define("CORE_LAN12", " sec (");
define("CORE_LAN13", "% of that for queries) ");
define("CORE_LAN14", "%2.3f cpu sec (%2.2f%% load, %2.3f startup). Clock: ");
define("CORE_LAN15", "DB queries: ");
define("CORE_LAN16", "Memory: ");

// img.bb
define("CORE_LAN17", "[ image disabled ]");
define("CORE_LAN18", "Image: ");

define("CORE_LAN_B", "B");
define("CORE_LAN_KB", "kB");
define("CORE_LAN_MB", "MB");
define("CORE_LAN_GB", "GB");
define("CORE_LAN_TB", "TB");

define("EMESSLAN_TITLE_INFO", "System Information");
define("EMESSLAN_TITLE_ERROR", "Error");
define("EMESSLAN_TITLE_SUCCESS", "Success");
define("EMESSLAN_TITLE_WARNING", "Warning");
define("EMESSLAN_TITLE_DEBUG", "System Debug");

define("LAN_NO_PERMISSIONS", "You do not have permission to view this page.");
define("LAN_EDIT","Edit");
define("LAN_DELETE","Delete");
define("LAN_DEFAULT","Default");
define("LAN_MORE", "More..");
define("LAN_LESS", "..Less");
define("LAN_READ_MORE", "Read more..");
define("LAN_GOPAGE", "Go to page");
define("LAN_GOTOPAGEX", "Go to page [x]");
define("LAN_GO", "Go");
define("LAN_SUBMIT", "Submit");
define("LAN_NONE", "None");
define("LAN_WARNING", "Warning!");
define("LAN_ERROR", "Error");
define("LAN_ANONYMOUS", "Anonymous");
define("LAN_EMAIL_SUBS", "-email-");
define("LAN_ACTIVE","Active");
define("LAN_YES", "Yes");
define("LAN_NO", "No");
define("LAN_OK", "OK");
define("LAN_ACTIONS", "Actions");
define("LAN_THANK_YOU", "Thank you");
define("LAN_CONTINUE", "Continue");
define("LAN_ENTER", "Enter");
define("LAN_ENTER_CODE", "Enter code");
define("LAN_INVALID_CODE", "Incorrect code entered.");
define("LAN_SEARCH", "Search");
define("LAN_VIEW", "View");
define("LAN_CLICK_TO_VIEW", "Click to View");//TODO elsewhere
define("LAN_SORT", "Sort");
define("LAN_ORDER_BY", "Order By");
define("LAN_ASCENDING", "Ascending");
define("LAN_DESCENDING", "Descending");
define("LAN_SHARE", "Share");
define("LAN_BACK", "Back");
define("LAN_NAME", "Name");
define("LAN_DESCRIPTION", "Description");
define("LAN_CANCEL","Cancel");
define("LAN_DATE","Date");
define("LAN_DATE_POSTED", "Date posted");
define("LAN_POSTED_BY", "Posted by");
define("LAN_JSCONFIRM","Are you sure?");
define("LAN_IP","IP");
define("LAN_IP_ADDRESS","IP Address");
define("LAN_AUTHOR","Author");
define("LAN_CATEGORY", "Category");
define("LAN_CATEGORIES", "Categories");
define("LAN_GUEST", "Guest");
define("LAN_NEXT", "Next");
define("LAN_PREVIOUS", "Previous");
define("LAN_LOGIN", "Login");
define("LAN_LOGOUT", "Logout");
define("LAN_VERIFY", "Verify");
define("LAN_SETTINGS", "Settings");
define("LAN_PASSWORD", "Password");
define("LAN_INCORRECT_PASSWORD", "Incorrect Password");
define("LAN_TYPE", "Type");
define("LAN_SCREENSHOT", "Screenshot");
define("LAN_FILE", "File");
define("LAN_YOUTUBE_VIDEO", "Youtube Video");
define("LAN_YOUTUBE_PLAYLIST", "Youtube Playlist");
define("LAN_FILETYPES", "Filetypes");
define("LAN_FILE_NOT_FOUND", "File Not Found");
define("LAN_FILES","Files"); 
define("LAN_SIZE", "Size");
define("LAN_VERSION", "Version");
define("LAN_DOWNLOAD", "Download");
define("LAN_DOWNLOAD_NO_PERMISSION", "File not found or you have no permission to download this file!");
define("LAN_WEBSITE", "Website");
define("LAN_COMMENTS", "Comments");
define("LAN_LOCATION", "Location");
define("LAN_NO_RECORDS_FOUND","No Records Found");
define("LAN_RATING", "Rating");
define("LAN_IMAGE","Image");
define("LAN_ABOUT", "About");
define("LAN_TITLE", "Title");
define("LAN_MESSAGE", "Message");
define("LAN_USER", "User");
define("LAN_EMAIL","Email address");
define("LAN_WROTE", "wrote"); // as in John wrote.."  ";
define("LAN_RE_ORDER", "Re-order");
define("LAN_RELATED", "Related");
define("LAN_CLOSE", "Close");
define("LAN_EXPAND", "Expand");
define("LAN_LIST", "List");
define("LAN_DATESTAMP","Date stamp");
define("LAN_SUBJECT","Subject");

define("LAN_ENTER_USRNAME_EMAIL", "Please enter your username or email"); // admin php hover field admin name
define("LAN_PWD_REQUIRED", "Password is required"); // admin php hover field admin password
define("LAN_SHOW", "Show");
define("LAN_GENERATE", "Generate");
define("LAN_SUMMARY", "Summary");  // TODO   more files use summary replace
define("LAN_REQUIRED_BLANK", "Required field(s) were left blank.");
define("LAN_PLEASEWAIT", "Please Wait");
define("LAN_CHOOSE_FILE", "Choose a file");

define("LAN_REQUIRED", "Required");

define("LAN_DEVELOPERMODE_CHECK", "[b]Developer mode is currently enabled. Use this mode only when developing![/b] [br]Please disable developer mode when using your website in live production. When developer mode is enabled, sensitive information may be shown to the public!");
