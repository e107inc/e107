<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Language File
 *
 * $URL$
 * $Id$
*/

define("LAN_EURL_NAME", "Manage Site URLs");
define("LAN_EURL_NAME_CONFIG", "Profiles");
define("LAN_EURL_NAME_ALIASES", "Aliases");
define("LAN_EURL_NAME_SETTINGS", "General settings");
define("LAN_EURL_NAME_HELP", "Help");

define("LAN_EURL_EMPTY", "The list is empty");
define("LAN_EURL_LEGEND_CONFIG", "Choose URL profile per site area");
define("LAN_EURL_LEGEND_ALIASES", "Configure Base URL aliases per URL Profile");
//define("LAN_EURL_PLUGCONFIG", "Configure Plugin URLs");
define("LAN_EURL_DEFAULT", "Default");
define("LAN_EURL_PROFILE", "Profile");
//define("LAN_EURL_UDEFINED", "User Defined Config");
define("LAN_EURL_INFOALT", "Info");
//define("LAN_EURL_UDEFINED_INFO", "User defined URL configuration - overrides (disables) all custom configuration profiles. Remove the User defined configuration folder to enable the custom configuration profiles.");
define("LAN_EURL_PROFILE_INFO", "Profile info not available");
define("LAN_EURL_LOCATION", "Profile Location:");
define("LAN_EURL_LOCATION_NONE", "Config file not available");
//define("LAN_EURL_AUTOSAVE", "URL profile changes were detected. Configuration state successfully updated.");
define("LAN_EURL_FORM_HELP_DEFAULT", "Alias when in default language.");
define("LAN_EURL_FORM_HELP_ALIAS_0", "Default value is ");
define("LAN_EURL_FORM_HELP_ALIAS_1", "Alias when in ");
define("LAN_EURL_FORM_HELP_EXAMPLE", "Base URL: ");

// settings
define("LAN_EURL_SETTINGS_PATHINFO", "Remove filename from the URL");
define("LAN_EURL_SETTINGS_MAINMODULE", "Associate Root namespace");
define("LAN_EURL_SETTINGS_MAINMODULE_HELP", "Choose which site area will be connected with your base site URL. Example: When News is your root namespace http://yoursite.com/News-Item-Title will be associated with news (item view page will be resolved)");
define("LAN_EURL_SETTINGS_REDIRECT", "Redirect to System not found page");
define("LAN_EURL_SETTINGS_REDIRECT_HELP", "If set to false, not found page will be direct rendered (without browser redirect)");

//define("LAN_EURL_MODREWR_TITLE", "User Friendly URLs");
define("LAN_EURL_MODREWR_DESCR", "Removes entry script file name (rewrite.php) from your URLs. You'll need mod_rewrite installed and running on your server (Apache Web Server). After enabling this setting go to your site root folder, rename htaccess.txt to .htaccess and modifgy <em>&quot;RewriteBase&quot;</em> Directive if required.");

// navigation
define("LAN_EURL_MENU", "Site URLs");
define("LAN_EURL_MENU_CONFIG", "URL Profiles");
define("LAN_EURL_MENU_ALIASES", "Aliases");
define("LAN_EURL_MENU_SETTINGS", "Settings");
define("LAN_EURL_MENU_HELP", "Help");

define("LAN_EURL_UC", "Under Construction");


define("LAN_EURL_CORE_MAIN", "Site Root Namespace - alias not in use.");

// News
define("LAN_EURL_CORE_NEWS", "News");
define("LAN_EURL_NEWS_DEFAULT_LABEL", "Default");
define("LAN_EURL_NEWS_DEFAULT_DESCR", "Legacy direct URLs. Examples: <br />http://yoursite.com/news.php<br />http://yoursite.com/news.php?extend.1 <em>(view news item)</em>");
define("LAN_EURL_NEWS_REWRITE_LABEL", "User Friendly URLs (mod_rewrite)");
define("LAN_EURL_NEWS_REWRITE_DESCR", "Demonstrates manual link parsing and assembling.<br />Examples: <br />http://yoursite.com/news<br />http://yoursite.com/news/News Title <em>(view news item)</em>");
define("LAN_EURL_NEWS_REWRITEX_LABEL", "Extended User Friendly URLs (mod_rewrite)");
define("LAN_EURL_NEWS_REWRITEX_DESCR", "Demonstrates automated link parsing and assembling based on predefined route rules.<br />Examples: <br />http://yoursite.com/news<br />http://yoursite.com/news/News Category Name/News Title <em>(view news item)</em>");
// Downloads 
//define("LAN_EURL_CORE_DOWNLOADS", "Downloads");

// Users
define("LAN_EURL_CORE_USER", "Users");
define("LAN_EURL_USER_DEFAULT_LABEL", "Default");
define("LAN_EURL_USER_DEFAULT_DESCR", "Legacy direct URLs. Example: http://yoursite.com/user.php?id.1");
define("LAN_EURL_USER_REWRITE_LABEL", "User Friendly URLs (mod_rewrite)");
define("LAN_EURL_USER_REWRITE_DESCR", "Search engine and user friendly URLs. <br />Example: http://yoursite.com/user/UserDisplayName");

// Users
define("LAN_EURL_CORE_PAGE", "Custom Pages");
define("LAN_EURL_PAGE_DEFAULT_LABEL", "Default");
define("LAN_EURL_PAGE_DEFAULT_DESCR", "Legacy direct URLs. Example: http://yoursite.com/page.php?1");
define("LAN_EURL_PAGE_SEF_LABEL", "User Friendly URLs");
define("LAN_EURL_PAGE_SEF_DESCR", "Search engine and user friendly URLs. <br />Example: http://yoursite.com/page/Page-Name");

// Search
define("LAN_EURL_CORE_SEARCH", "Search");
define("LAN_EURL_SEARCH_DEFAULT_LABEL", "Default Search URL");
define("LAN_EURL_SEARCH_DEFAULT_DESCR", "Legacy direct URL. Example: http://yoursite.com/search.php");
define("LAN_EURL_SEARCH_REWRITE_LABEL", "User Friendly URL (mod_rewrite)");
define("LAN_EURL_SEARCH_REWRITE_DESCR", "Example: http://yoursite.com/search/");

// System
define("LAN_EURL_CORE_SYSTEM", "System");
define("LAN_EURL_SYSTEM_DEFAULT_LABEL", "Default System URLs");
define("LAN_EURL_SYSTEM_DEFAULT_DESCR", "URLs for pages like Not Found, Acess denied, etc. Example: http://yoursite.com/?route=system/error/notfound");
define("LAN_EURL_SYSTEM_REWRITE_LABEL", "User Friendly URL (mod_rewrite)");
define("LAN_EURL_SYSTEM_REWRITE_DESCR", "URLs for pages like Not Found, Acess denied, etc.<br />Example: http://yoursite.com/system/error404");

// System
define("LAN_EURL_CORE_INDEX", "Front Page");
define("LAN_EURL_CORE_INDEX_INFO", "Front Page can't have an alias.");
//define("LAN_EURL_", "");