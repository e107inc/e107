<?php
/*
 * Copyright (C) 2008-2025 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Language File
 *
*/


return [
    'LAN_EURL_NAME' => "Manage Site URLs",
    'LAN_EURL_NAME_CONFIG' => "Profiles",
    'LAN_EURL_NAME_ALIASES' => "Aliases",
    'LAN_EURL_NAME_SETTINGS' => "General settings",
    'LAN_EURL_NAME_HELP' => "Help",
    'LAN_EURL_EMPTY' => "The list is empty",
    'LAN_EURL_LEGEND_CONFIG' => "Choose URL profile per site area",
    'LAN_EURL_LEGEND_ALIASES' => "Configure Base URL aliases per URL Profile",
    'LAN_EURL_DEFAULT' => "Default",
    'LAN_EURL_PROFILE' => "Profile",
    'LAN_EURL_INFOALT' => "Info",
    'LAN_EURL_PROFILE_INFO' => "Profile info not available",
    'LAN_EURL_LOCATION' => "Profile Location",
    'LAN_EURL_LOCATION_NONE' => "Config file not available",
    'LAN_EURL_FORM_HELP_DEFAULT' => "Alias when in default language.",
    'LAN_EURL_FORM_HELP_ALIAS_0' => "Default value is",
    'LAN_EURL_FORM_HELP_ALIAS_1' => "Alias when in",
    'LAN_EURL_FORM_HELP_EXAMPLE' => "Base URL",
    'LAN_EURL_ERR_ALIAS_MODULE' => "Alias &quot;%1\\$s&quot; can't be saved - there is a system URL profile with the same name. Please choose another alias value for system URL profile &quot;%2\$s&quot;",
    'LAN_EURL_SURL_UPD' => "&nbsp; SEF URLs were updated.",
    'LAN_EURL_SURL_NUPD' => "&nbsp; SEF URLs were NOT updated.",
    'LAN_EURL_SETTINGS_PATHINFO' => "Remove filename from the URL",
    'LAN_EURL_SETTINGS_MAINMODULE' => "Associate Root namespace",
    'LAN_EURL_SETTINGS_MAINMODULE_HELP' => "Choose which site area will be connected with your base site URL. Example: When News is your root namespace https://yoursite.com/News-Item-Title will be associated with news (item view page will be resolved)",
    'LAN_EURL_SETTINGS_REDIRECT' => "Redirect to System not found page",
    'LAN_EURL_SETTINGS_REDIRECT_HELP' => "If set to false, not found page will be direct rendered (without browser redirect)",
    'LAN_EURL_SETTINGS_SEFTRANSLATE' => "Automated SEF string creation type",
    'LAN_EURL_SETTINGS_SEFTRANSLATE_HELP' => "Choose how will be assembled SEF string when it's automatically built from a Title value (e.g. in news, custom pages, etc.)",
    'LAN_EURL_SETTINGS_SEFTRTYPE_NONE' => "Just secure it",
    'LAN_EURL_SETTINGS_SEFTRTYPE_DASHL' => "dasherize-to-lower-case",
    'LAN_EURL_SETTINGS_SEFTRTYPE_DASHC' => "Dasherize-To-Camel-Case",
    'LAN_EURL_SETTINGS_SEFTRTYPE_DASH' => "Dasherize-with-no-case-CHANGE",
    'LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREL' => "underscore_to_lower_case",
    'LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREC' => "Underscore_To_Camel_Case",
    'LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCORE' => "Underscore_with_no_case_CHANGE",
    'LAN_EURL_SETTINGS_SEFTRTYPE_PLUSL' => "plus+separator+to+lower+case",
    'LAN_EURL_SETTINGS_SEFTRTYPE_PLUSC' => "Plus+Separator+To+Camel+Case",
    'LAN_EURL_SETTINGS_SEFTRTYPE_PLUS' => "Plus+separator+with+no+case+CHANGE",
    'LAN_EURL_MODREWR_DESCR' => "Removes entry script file name (index.php/) from your URLs. You'll need mod_rewrite installed and running on your server (Apache Web Server). After enabling this setting go to your site root folder, rename htaccess.txt to .htaccess and modifgy <em>&quot;RewriteBase&quot;</em> Directive if required.",
    'LAN_EURL_MENU' => "Site URLs",
    'LAN_EURL_MENU_CONFIG' => "Configurations",
    'LAN_EURL_MENU_ALIASES' => "Profile Aliases",
    'LAN_EURL_MENU_SETTINGS' => "Settings",
    'LAN_EURL_MENU_HELP' => "Help",
    'LAN_EURL_MENU_PROFILES' => "Profiles",
    'LAN_EURL_UC' => "Under Construction",
    'LAN_EURL_CORE_MAIN' => "Site Root Namespace - alias not in use.",
    'LAN_EURL_FRIENDLY' => "Friendly",
    'LAN_EURL_LEGACY' => "Legacy direct URLs.",
    'LAN_EURL_REWRITE_LABEL' => "Friendly URLs",
    'LAN_EURL_REWRITE_DESCR' => "Search engine and user friendly URLs.",
    'LAN_EURL_CORE_NEWS' => "News",
    'LAN_EURL_NEWS_REWRITEF_LABEL' => "Full Friendly URLs (no performance and most friendly)",
    'LAN_EURL_NEWS_REWRITEF_DESCR' => "",
    'LAN_EURL_NEWS_REWRITE_LABEL' => "Friendly URLs without ID (no performance, more friendly)",
    'LAN_EURL_NEWS_REWRITE_DESCR' => "Demonstrates manual link parsing and assembling.",
    'LAN_EURL_NEWS_REWRITEX_LABEL' => "Friendly URLs with ID (performance wise)",
    'LAN_EURL_NEWS_REWRITEX_DESCR' => "Demonstrates automated link parsing and assembling based on predefined route rules.",
    'LAN_EURL_CORE_USER' => "Users",
    'LAN_EURL_USER_REWRITE_LABEL' => "Friendly URLs",
    'LAN_EURL_USER_REWRITE_DESCR' => "Search engine and user friendly URLs.",
    'LAN_EURL_CORE_PAGE' => "Custom Pages",
    'LAN_EURL_PAGE_SEF_LABEL' => "Friendly URLs with ID (performance)",
    'LAN_EURL_PAGE_SEF_DESCR' => "Search engine and user friendly URLs.",
    'LAN_EURL_PAGE_SEFNOID_LABEL' => "Friendly URLs without ID (no performance, more friendly)",
    'LAN_EURL_PAGE_SEFNOID_DESCR' => "Search engine and user friendly URLs.",
    'LAN_EURL_CORE_SEARCH' => "Search",
    'LAN_EURL_SEARCH_DEFAULT_LABEL' => "Default Search URL",
    'LAN_EURL_SEARCH_DEFAULT_DESCR' => "Legacy direct URL.",
    'LAN_EURL_SEARCH_REWRITE_LABEL' => "Friendly URL",
    'LAN_EURL_SEARCH_REWRITE_DESCR' => "",
    'LAN_EURL_CORE_SYSTEM' => "System",
    'LAN_EURL_SYSTEM_DEFAULT_LABEL' => "Default System URLs",
    'LAN_EURL_SYSTEM_DEFAULT_DESCR' => "URLs for pages like Not Found, Access denied, etc.",
    'LAN_EURL_SYSTEM_REWRITE_LABEL' => "Friendly System URLs",
    'LAN_EURL_SYSTEM_REWRITE_DESCR' => "URLs for pages like Not Found, Access denied, etc.",
    'LAN_EURL_CORE_INDEX' => "Front Page",
    'LAN_EURL_CORE_INDEX_INFO' => "Front Page can't have an alias.",
    'LAN_EURL_REBUILD' => "Rebuild",
    'LAN_EURL_REGULAR_EXPRESSION' => "Regular Expression",
    'LAN_EURL_KEY' => "Key",
    'LAN_EURL_TABLE' => "Table",
];
