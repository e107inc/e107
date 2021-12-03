<?php
/*
 * Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Language File
 *
*/

define("LAN_EURL_NAME", "Manage Site URLs");
define("LAN_EURL_NAME_CONFIG", "Profiles");
define("LAN_EURL_NAME_ALIASES", "Aliases");
define("LAN_EURL_NAME_SETTINGS", "General settings");
define("LAN_EURL_NAME_HELP", "Help");

define("LAN_EURL_EMPTY", "The list is empty");
define("LAN_EURL_LEGEND_CONFIG", "Choose URL profile per site area");
define("LAN_EURL_LEGEND_ALIASES", "Configure Base URL aliases per URL Profile");

define("LAN_EURL_DEFAULT", "Default");
define("LAN_EURL_PROFILE", "Profile");

define("LAN_EURL_INFOALT", "Info");
define("LAN_EURL_PROFILE_INFO", "Profile info not available");
define("LAN_EURL_LOCATION", "Profile Location");
define("LAN_EURL_LOCATION_NONE", "Config file not available");
define("LAN_EURL_FORM_HELP_DEFAULT", "Alias when in default language.");
define("LAN_EURL_FORM_HELP_ALIAS_0", "Default value is ");
define("LAN_EURL_FORM_HELP_ALIAS_1", "Alias when in ");
define("LAN_EURL_FORM_HELP_EXAMPLE", "Base URL");

// messages
define("LAN_EURL_ERR_ALIAS_MODULE", "Alias &quot;%1\$s&quot; can't be saved - there is a system URL profile with the same name. Please choose another alias value for system URL profile &quot;%2\$s&quot;"); // FIXME HTML IN LAN
define("LAN_EURL_SURL_UPD",    "&nbsp; SEF URLs were updated.");
define("LAN_EURL_SURL_NUPD",    "&nbsp; SEF URLs were NOT updated.");
// settings
define("LAN_EURL_SETTINGS_PATHINFO", "Remove filename from the URL");
define("LAN_EURL_SETTINGS_MAINMODULE", "Associate Root namespace");
define("LAN_EURL_SETTINGS_MAINMODULE_HELP", "Choose which site area will be connected with your base site URL. Example: When News is your root namespace https://yoursite.com/News-Item-Title will be associated with news (item view page will be resolved)");
define("LAN_EURL_SETTINGS_REDIRECT", "Redirect to System not found page");
define("LAN_EURL_SETTINGS_REDIRECT_HELP", "If set to false, not found page will be direct rendered (without browser redirect)");
define("LAN_EURL_SETTINGS_SEFTRANSLATE", "Automated SEF string creation type");
define("LAN_EURL_SETTINGS_SEFTRANSLATE_HELP", "Choose how will be assembled SEF string when it's automatically built from a Title value (e.g. in news, custom pages, etc.)");
define("LAN_EURL_SETTINGS_SEFTRTYPE_NONE", "Just secure it");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASHL", "dasherize-to-lower-case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASHC", "Dasherize-To-Camel-Case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASH", "Dasherize-with-no-case-CHANGE");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREL", "underscore_to_lower_case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREC", "Underscore_To_Camel_Case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCORE", "Underscore_with_no_case_CHANGE");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUSL", "plus+separator+to+lower+case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUSC", "Plus+Separator+To+Camel+Case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUS", "Plus+separator+with+no+case+CHANGE");

define("LAN_EURL_MODREWR_DESCR", "Removes entry script file name (index.php/) from your URLs. You'll need mod_rewrite installed and running on your server (Apache Web Server). After enabling this setting go to your site root folder, rename htaccess.txt to .htaccess and modifgy <em>&quot;RewriteBase&quot;</em> Directive if required.");

// navigation
define("LAN_EURL_MENU", "Site URLs");
define("LAN_EURL_MENU_CONFIG", "Configurations");
define("LAN_EURL_MENU_ALIASES", "Profile Aliases");
define("LAN_EURL_MENU_SETTINGS", "Settings");
define("LAN_EURL_MENU_HELP", "Help");
define("LAN_EURL_MENU_PROFILES", "Profiles");

define("LAN_EURL_UC", "Under Construction");


define("LAN_EURL_CORE_MAIN", "Site Root Namespace - alias not in use.");



define("LAN_EURL_FRIENDLY",   "Friendly");
define("LAN_EURL_LEGACY", "Legacy direct URLs.");

define("LAN_EURL_REWRITE_LABEL", "Friendly URLs");
define("LAN_EURL_REWRITE_DESCR", "Search engine and user friendly URLs.");


// News
define("LAN_EURL_CORE_NEWS", "News");
//define("LAN_EURL_NEWS_DEFAULT_LABEL", "Default");
//define("LAN_EURL_NEWS_DEFAULT_DESCR", "Legacy direct URLs.");

define("LAN_EURL_NEWS_REWRITEF_LABEL", "Full Friendly URLs (no performance and most friendly)");
define("LAN_EURL_NEWS_REWRITEF_DESCR", "");

define("LAN_EURL_NEWS_REWRITE_LABEL", "Friendly URLs without ID (no performance, more friendly)");
define("LAN_EURL_NEWS_REWRITE_DESCR", "Demonstrates manual link parsing and assembling.");

define("LAN_EURL_NEWS_REWRITEX_LABEL", "Friendly URLs with ID (performance wise)");
define("LAN_EURL_NEWS_REWRITEX_DESCR", "Demonstrates automated link parsing and assembling based on predefined route rules.");


// Downloads 
//define("LAN_EURL_CORE_DOWNLOADS", "Downloads");

// Users
define("LAN_EURL_CORE_USER", "Users");
//define("LAN_EURL_USER_DEFAULT_LABEL", "Default");
//define("LAN_EURL_USER_DEFAULT_DESCR", "Legacy direct URLs.");

define("LAN_EURL_USER_REWRITE_LABEL", "Friendly URLs");
define("LAN_EURL_USER_REWRITE_DESCR", "Search engine and user friendly URLs.");

// Users
define("LAN_EURL_CORE_PAGE", "Custom Pages");
//define("LAN_EURL_PAGE_DEFAULT_LABEL", "Default");
//define("LAN_EURL_PAGE_DEFAULT_DESCR", "Legacy direct URLs. ");

define("LAN_EURL_PAGE_SEF_LABEL", "Friendly URLs with ID (performance)");
define("LAN_EURL_PAGE_SEF_DESCR", "Search engine and user friendly URLs.");

define("LAN_EURL_PAGE_SEFNOID_LABEL", "Friendly URLs without ID (no performance, more friendly)");
define("LAN_EURL_PAGE_SEFNOID_DESCR", "Search engine and user friendly URLs.");

// Search
define("LAN_EURL_CORE_SEARCH", "Search");
define("LAN_EURL_SEARCH_DEFAULT_LABEL", "Default Search URL");
define("LAN_EURL_SEARCH_DEFAULT_DESCR", "Legacy direct URL.");
define("LAN_EURL_SEARCH_REWRITE_LABEL", "Friendly URL");
define("LAN_EURL_SEARCH_REWRITE_DESCR", "");

// System
define("LAN_EURL_CORE_SYSTEM", "System");
define("LAN_EURL_SYSTEM_DEFAULT_LABEL", "Default System URLs");
define("LAN_EURL_SYSTEM_DEFAULT_DESCR", "URLs for pages like Not Found, Access denied, etc.");

define("LAN_EURL_SYSTEM_REWRITE_LABEL", "Friendly System URLs");
define("LAN_EURL_SYSTEM_REWRITE_DESCR", "URLs for pages like Not Found, Access denied, etc.");

// System
define("LAN_EURL_CORE_INDEX", "Front Page");
define("LAN_EURL_CORE_INDEX_INFO", "Front Page can't have an alias.");
define("LAN_EURL_REBUILD",    "Rebuild");
define("LAN_EURL_REGULAR_EXPRESSION", "Regular Expression");
define("LAN_EURL_KEY", "Key");
define("LAN_EURL_TABLE", "Table");
