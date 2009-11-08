<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Navigation
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/ad_links.php,v $
 * $Revision: 1.20 $
 * $Date: 2009-11-08 13:21:56 $
 * $Author: e107coders $
*/

if (!defined('e107_INIT')) { exit; }

if (file_exists(THEME.'admin_images/admin_images.php')) {
	e107_require_once(THEME.'admin_images/admin_images.php');
}

// Small Category Images
if (!defined('E_16_CAT_SETT')) {
	define('E_16_CAT_SETT', e_IMAGE.'admin_images/cat_settings_16.png');
}
if (!defined('E_16_CAT_USER')) {
	define('E_16_CAT_USER', e_IMAGE.'admin_images/cat_users_16.png');
}
if (!defined('E_16_CAT_CONT')) {
	define('E_16_CAT_CONT', e_IMAGE.'admin_images/cat_content_16.png');
}
if (!defined('E_16_CAT_FILE')) {
	define('E_16_CAT_FILE', e_IMAGE.'admin_images/cat_files_16.png');
}
if (!defined('E_16_CAT_TOOL')) {
	define('E_16_CAT_TOOL', e_IMAGE.'admin_images/cat_tools_16.png');
}
if (!defined('E_16_CAT_PLUG')) {
	define('E_16_CAT_PLUG', e_IMAGE.'admin_images/cat_plugins_16.png');
}
if (!defined('E_16_CAT_MANAGE')) {
	define('E_16_CAT_MANAGE', e_IMAGE.'admin_images/manage_16.png');
}
if (!defined('E_16_CAT_MISC')) {
	define('E_16_CAT_MISC', e_IMAGE.'admin_images/settings_16.png');
}
if (!defined('E_16_CAT_ABOUT')) {
	define('E_16_CAT_ABOUT', e_IMAGE.'admin_images/info_16.png');
}

// Large Category Images
if (!defined('E_32_CAT_SETT')) {
	define('E_32_CAT_SETT', "<img class='icon S32' src='".e_IMAGE."admin_images/cat_settings_32.png' alt='' />");
}
if (!defined('E_32_CAT_USER')) {
	define('E_32_CAT_USER', "<img class='icon S32' src='".e_IMAGE."admin_images/cat_users_32.png' alt='' />");
}
if (!defined('E_32_CAT_CONT')) {
	define('E_32_CAT_CONT', "<img class='icon S32' src='".e_IMAGE."admin_images/cat_content_32.png' alt='' />");
}
if (!defined('E_32_CAT_FILE')) {
	define('E_32_CAT_FILE', "<img class='icon S32' src='".e_IMAGE."admin_images/cat_files_32.png' alt='' />");
}
if (!defined('E_32_CAT_TOOL')) {
	define('E_32_CAT_TOOL', "<img class='icon S32' src='".e_IMAGE."admin_images/cat_tools_32.png' alt='' />");
}
if (!defined('E_32_CAT_PLUG')) {
	define('E_32_CAT_PLUG', "<img class='icon S32' src='".e_IMAGE."admin_images/cat_plugins_32.png' alt='' />");
}
if (!defined('E_32_CAT_MANAGE')) {
	define('E_32_CAT_MANAGE', "<img class='icon S32' src='".e_IMAGE."admin_images/manage_32.png' alt='' />");
}
if (!defined('E_32_CAT_MISC')) {
	define('E_32_CAT_MISC', "<img class='icon S32' src='".e_IMAGE."admin_images/settings_32.png' alt='' />");
}
if (!defined('E_32_CAT_ABOUT')) {
	define('E_32_CAT_ABOUT', "<img class='icon S32' src='".e_IMAGE."admin_images/info_32.png' alt='' />");
}

// Small Nav Images
if (!defined('E_16_NAV_MAIN')) {
	define('E_16_NAV_MAIN', e_IMAGE.'admin_images/main_16.png');
}
/*
if (!defined('E_16_NAV_DOCS')) {
	define('E_16_NAV_DOCS', e_IMAGE.'admin_images/docs_16.png');
}
*/
if (!defined('E_16_NAV_LEAV')) {
	define('E_16_NAV_LEAV', e_IMAGE.'admin_images/leave_16.png');
}
if (!defined('E_16_NAV_LGOT')) {
	define('E_16_NAV_LGOT', e_IMAGE.'admin_images/logout_16.png');
}
if (!defined('E_16_NAV_ARROW')) {
	define('E_16_NAV_ARROW', e_IMAGE.'admin_images/arrow_16.png');
}
if (!defined('E_16_NAV_ARROW_OVER')) {
	define('E_16_NAV_ARROW_OVER', e_IMAGE.'admin_images/arrow_over_16.png');
}

// Large Nav Images
if (!defined('E_32_NAV_MAIN')) {
	define('E_32_NAV_MAIN', "<img class='icon S32' src='".e_IMAGE."admin_images/main_32.png' alt='' />");
}
if (!defined('E_32_NAV_DOCS')) {
	define('E_32_NAV_DOCS', "<img class='icon S32' src='".e_IMAGE."admin_images/docs_32.png' alt='' />");
}
if (!defined('E_32_NAV_LEAV')) {
	define('E_32_NAV_LEAV', "<img class='icon S32' src='".e_IMAGE."admin_images/leave_32.png' alt='' />");
}
if (!defined('E_32_NAV_LGOT')) {
	define('E_32_NAV_LGOT', "<img class='icon S32' src='".e_IMAGE."admin_images/logout_32.png' alt='' />");
}
if (!defined('E_32_NAV_ARROW')) {
	define('E_32_NAV_ARROW', "<img class='icon S32' src='".e_IMAGE."admin_images/arrow_32.png' alt='' />");
}
if (!defined('E_32_NAV_ARROW_OVER')) {
	define('E_32_NAV_ARROW_OVER', "<img class='icon S32' src='".e_IMAGE."admin_images/arrow_over_32.png' alt='' />");
}

// Small Admin Main Link Images
if (!defined('E_16_ADMIN')) {
	define('E_16_ADMIN', "<img class='icon S16' src='".e_IMAGE."admin_images/admins_16.png' alt='' />");
}
if (!defined('E_16_ADPASS')) {
	define('E_16_ADPASS', "<img class='icon S16' src='".e_IMAGE."admin_images/adminpass_16.png' alt='' />");
}
if (!defined('E_16_BANLIST')) {
	define('E_16_BANLIST', "<img class='icon S16' src='".e_IMAGE."admin_images/banlist_16.png' alt='' />");
}

if (!defined('E_16_CACHE')) {
	define('E_16_CACHE', "<img class='icon S16' src='".e_IMAGE."admin_images/cache_16.png' alt='' />");
}
if (!defined('E_16_COMMENT')) {
	define('E_16_COMMENT', "<img class='icon S16' src='".e_IMAGE."admin_images/comments_16.png' alt='' />");
}
if (!defined('E_16_CREDITS')) {
	define('E_16_CREDITS', "<img class='icon S16' src='".e_IMAGE."e107_icon_16.png' alt='' />");
}
if (!defined('E_16_CRON')) {
	define('E_16_CRON', "<img class='icon S16' src='".e_IMAGE."admin_images/cron_16.png' alt='' />");
}
if (!defined('E_16_CUST')) {
	define('E_16_CUST', "<img class='icon S16' src='".e_IMAGE."admin_images/custom_16.png' alt='' />");
}
if (!defined('E_16_CUSTOMFIELD')) {
	define('E_16_CUSTOMFIELD', "<img class='icon S16' src='".e_IMAGE."admin_images/custom_field_16.png' alt='' />");
}
if (!defined('E_16_DATAB')) {
	define('E_16_DATAB', "<img class='icon S16' src='".e_IMAGE."admin_images/database_16.png' alt='' />");
}
if (!defined('E_16_DOCS')) {
	define('E_16_DOCS', "<img class='icon S16' src='".e_IMAGE."admin_images/docs_16.png' alt='' />");
}

if (!defined('E_16_EMOTE')) {
	define('E_16_EMOTE', "<img class='icon S16' src='".e_IMAGE."admin_images/emoticons_16.png' alt='' />");
}
if (!defined('E_16_FILE')) {
	define('E_16_FILE', "<img class='icon S16' src='".e_IMAGE."admin_images/filemanager_16.png' alt='' />");
}
if (!defined('E_16_FORUM')) {
	define('E_16_FORUM', "<img class='icon S16' src='".e_IMAGE."admin_images/forums_16.png' alt='' />");
}
if (!defined('E_16_FRONT')) {
	define('E_16_FRONT', "<img class='icon S16' src='".e_IMAGE."admin_images/frontpage_16.png' alt='' />");
}
if (!defined('E_16_IMAGES')) {
	define('E_16_IMAGES', "<img class='icon S16' src='".e_IMAGE."admin_images/images_16.png' alt='' />");
}
if (!defined('E_16_INSPECT')) {
	define('E_16_INSPECT', "<img class='icon S16' src='".e_IMAGE."admin_images/fileinspector_16.png' alt='' />");
}
if (!defined('E_16_LINKS')) {
	define('E_16_LINKS', "<img class='icon S16' src='".e_IMAGE."admin_images/links_16.png' alt='' />");
}
if (!defined('E_16_WELCOME')) {
	define('E_16_WELCOME', "<img class='icon S16' src='".e_IMAGE."admin_images/welcome_16.png' alt='' />");
}
if (!defined('E_16_MAIL')) {
	define('E_16_MAIL', "<img class='icon S16' src='".e_IMAGE."admin_images/mail_16.png' alt='' />");
}
if (!defined('E_16_MAINTAIN')) {
	define('E_16_MAINTAIN', "<img class='icon S16' src='".e_IMAGE."admin_images/maintain_16.png' alt='' />");
}
if (!defined('E_16_MENUS')) {
	define('E_16_MENUS', "<img class='icon S16' src='".e_IMAGE."admin_images/menus_16.png' alt='' />");
}
if (!defined('E_16_META')) {
	define('E_16_META', "<img class='icon S16' src='".e_IMAGE."admin_images/meta_16.png' alt='' />");
}
if (!defined('E_16_NEWS')) {
	define('E_16_NEWS', "<img class='icon S16' src='".e_IMAGE."admin_images/news_16.png' alt='' />");
}
if (!defined('E_16_NEWSFEED')) {
	define('E_16_NEWSFEED', "<img class='icon S16' src='".e_IMAGE."admin_images/newsfeeds_16.png' alt='' />");
}
if (!defined('E_16_NOTIFY')) {
	define('E_16_NOTIFY', "<img class='icon S16' src='".e_IMAGE."admin_images/notify_16.png' alt='' />");
}
if (!defined('E_16_PHP')) {
	define('E_16_PHP', "<img class='icon S16' src='".e_IMAGE."admin_images/phpinfo_16.png' alt='' />");
}
if (!defined('E_16_POLLS')) {
	define('E_16_POLLS', "<img class='icon S16' src='".e_IMAGE."admin_images/polls_16.png' alt='' />");
}
if (!defined('E_16_PREFS')) {
	define('E_16_PREFS', "<img class='icon S16' src='".e_IMAGE."admin_images/prefs_16.png' alt='' />");
}
if (!defined('E_16_SEARCH')) {
	define('E_16_SEARCH', "<img class='icon S16' src='".e_IMAGE."admin_images/search_16.png' alt='' />");
}
if (!defined('E_16_UPLOADS')) {
	define('E_16_UPLOADS', "<img class='icon S16' src='".e_IMAGE."admin_images/uploads_16.png' alt='' />");
}
if (!defined('E_16_EURL')) {
	define('E_16_EURL', "<img class='icon S16' src='".e_IMAGE."admin_images/eurl_16.png' alt='' />");
}
if (!defined('E_16_USER')) {
	define('E_16_USER', "<img class='icon S16' src='".e_IMAGE."admin_images/users_16.png' alt='' />");
}
if (!defined('E_16_USER_EXTENDED')) {
	define('E_16_USER_EXTENDED', "<img class='icon S16' src='".e_IMAGE."admin_images/extended_16.png' alt='' />");
}
if (!defined('E_16_USERCLASS')) {
	define('E_16_USERCLASS', "<img class='icon S16' src='".e_IMAGE."admin_images/userclass_16.png' alt='' />");
}
if (!defined('E_16_LANGUAGE')) {
	define('E_16_LANGUAGE', "<img class='icon S16' src='".e_IMAGE."admin_images/language_16.png' alt='' />");
}

// Small Admin Other Link Images
if (!defined('E_16_PLUGIN')) {
	define('E_16_PLUGIN', "<img class='icon S16' src='".e_IMAGE."admin_images/plugins_16.png' alt='' />");
}
if (!defined('E_16_PLUGMANAGER')) {
	define('E_16_PLUGMANAGER', "<img class='icon S16' src='".e_IMAGE."admin_images/plugmanager_16.png' alt='' />");
}
if (!defined('E_16_DOCS')) {
	define('E_16_DOCS', "<img class='icon S16' src='".e_IMAGE."admin_images/docs_16.png' alt='' />");
}
if (!defined('E_16_THEMEMANAGER')) {
	define('E_16_THEMEMANAGER', "<img class='icon S16' src='".e_IMAGE."admin_images/themes_16.png' alt='' />");
}

// Small Admin Other Images
if (!defined('E_16_COMMENT')) {
	define('E_16_COMMENT', "<img class='icon S16' src='".e_IMAGE."admin_images/comments_16.png' alt='' />");
}
if (!defined('E_16_ADMINLOG')) {
	define('E_16_ADMINLOG', "<img class='icon S16' src='".e_IMAGE."admin_images/adminlogs_16.png' alt='' />");
}

if (!defined('E_16_MANAGE')) {
	define('E_16_MANAGE', "<img class='icon S16' src='".e_IMAGE."admin_images/manage_16.png' alt='' />");
}

if (!defined('E_16_CREATE')) {
	define('E_16_CREATE', "<img class='icon S16' src='".e_IMAGE."admin_images/add_16.png' alt='' />");
}

if (!defined('E_16_SETTINGS')) {
	define('E_16_SETTINGS', "<img class='icon S16' src='".e_IMAGE."admin_images/settings_16.png' alt='' />");
}

if (!defined('E_16_SYSINFO')) {
	define('E_16_SYSINFO', "<img class='icon S16' src='".e_IMAGE."admin_images/sysinfo_16.png' alt='' />");
}


// Large Admin Main Link Images
if (!defined('E_32_ADMIN')) {
	define('E_32_ADMIN', "<img class='icon S32' src='".e_IMAGE."admin_images/admins_32.png' alt='' />");
}
if (!defined('E_32_ADPASS')) {
	define('E_32_ADPASS', "<img class='icon S32' src='".e_IMAGE."admin_images/adminpass_32.png' alt='' />");
}
if (!defined('E_32_BANLIST')) {
	define('E_32_BANLIST', "<img class='icon S32' src='".e_IMAGE."admin_images/banlist_32.png' alt='' />");
}

if (!defined('E_32_CACHE')) {
	define('E_32_CACHE', "<img class='icon S32' src='".e_IMAGE."admin_images/cache_32.png' alt='' />");
}
if (!defined('E_32_CREDITS')) {
	define('E_32_CREDITS', "<img class='icon S32' src='".e_IMAGE."e107_icon_32.png' alt='' />");
}
if (!defined('E_32_CRON')) {
	define('E_32_CRON', "<img class='icon S32' src='".e_IMAGE."admin_images/cron_32.png' alt='' />");
}
if (!defined('E_32_CUST')) {
	define('E_32_CUST', "<img class='icon S32' src='".e_IMAGE."admin_images/custom_32.png' alt='' />");
}
/*if (!defined('E_32_CUSTOMFIELD')) {
	define('E_32_CUSTOMFIELD', "<img class='icon S16' src='".e_IMAGE."admin_images/custom_field_32.png' alt='' />");
}*/
if (!defined('E_32_DATAB')) {
	define('E_32_DATAB', "<img class='icon S32' src='".e_IMAGE."admin_images/database_32.png' alt='' />");
}
if (!defined('E_32_DOCS')) {
	define('E_32_DOCS', "<img class='icon S32' src='".e_IMAGE."admin_images/docs_32.png' alt='' />");
}

if (!defined('E_32_EMOTE')) {
	define('E_32_EMOTE', "<img class='icon S32' src='".e_IMAGE."admin_images/emoticons_32.png' alt='' />");
}
if (!defined('E_32_FILE')) {
	define('E_32_FILE', "<img class='icon S32' src='".e_IMAGE."admin_images/filemanager_32.png' alt='' />");
}
if (!defined('E_32_FORUM')) {
	define('E_32_FORUM', "<img class='icon S32' src='".e_IMAGE."admin_images/forums_32.png' alt='' />");
}
if (!defined('E_32_FRONT')) {
	define('E_32_FRONT', "<img class='icon S32' src='".e_IMAGE."admin_images/frontpage_32.png' alt='' />");
}
if (!defined('E_32_IMAGES')) {
	define('E_32_IMAGES', "<img class='icon S32' src='".e_IMAGE."admin_images/images_32.png' alt='' />");
}
if (!defined('E_32_INSPECT')) {
	define('E_32_INSPECT', "<img class='icon S32' src='".e_IMAGE."admin_images/fileinspector_32.png' alt='' />");
}
if (!defined('E_32_LINKS')) {
	define('E_32_LINKS', "<img class='icon S32' src='".e_IMAGE."admin_images/links_32.png' alt='' />");
}
if (!defined('E_32_WELCOME')) {
	define('E_32_WELCOME', "<img class='icon S32' src='".e_IMAGE."admin_images/welcome_32.png' alt='' />");
}
if (!defined('E_32_MAIL')) {
	define('E_32_MAIL', "<img class='icon S32' src='".e_IMAGE."admin_images/mail_32.png' alt='' />");
}
if (!defined('E_32_MAINTAIN')) {
	define('E_32_MAINTAIN', "<img class='icon S32' src='".e_IMAGE."admin_images/maintain_32.png' alt='' />");
}
if (!defined('E_32_MENUS')) {
	define('E_32_MENUS', "<img class='icon S32' src='".e_IMAGE."admin_images/menus_32.png' alt='' />");
}
if (!defined('E_32_META')) {
	define('E_32_META', "<img class='icon S32' src='".e_IMAGE."admin_images/meta_32.png' alt='' />");
}
if (!defined('E_32_NEWS')) {
	define('E_32_NEWS', "<img class='icon S32' src='".e_IMAGE."admin_images/news_32.png' alt='' />");
}
if (!defined('E_32_NEWSFEED')) {
	define('E_32_NEWSFEED', "<img class='icon S32' src='".e_IMAGE."admin_images/newsfeeds_32.png' alt='' />");
}
if (!defined('E_32_NOTIFY')) {
	define('E_32_NOTIFY', "<img class='icon S32' src='".e_IMAGE."admin_images/notify_32.png' alt='' />");
}
if (!defined('E_32_PHP')) {
	define('E_32_PHP', "<img class='icon S32' src='".e_IMAGE."admin_images/phpinfo_32.png' alt='' />");
}
if (!defined('E_32_POLLS')) {
	define('E_32_POLLS', "<img class='icon S32' src='".e_IMAGE."admin_images/polls_32.png' alt='' />");
}
if (!defined('E_32_PREFS')) {
	define('E_32_PREFS', "<img class='icon S32' src='".e_IMAGE."admin_images/prefs_32.png' alt='' />");
}
if (!defined('E_32_SEARCH')) {
	define('E_32_SEARCH', "<img class='icon S32' src='".e_IMAGE."admin_images/search_32.png' alt='' />");
}
if (!defined('E_32_UPLOADS')) {
	define('E_32_UPLOADS', "<img class='icon S32' src='".e_IMAGE."admin_images/uploads_32.png' alt='' />");
}
if (!defined('E_32_EURL')) {
	define('E_32_EURL', "<img class='icon S32' src='".e_IMAGE."admin_images/eurl_32.png' alt='' />");
}
if (!defined('E_32_USER')) {
	define('E_32_USER', "<img class='icon S32' src='".e_IMAGE."admin_images/users_32.png' alt='' />");
}
if (!defined('E_32_USER_EXTENDED')) {
	define('E_32_USER_EXTENDED', "<img class='icon S32' src='".e_IMAGE."admin_images/extended_32.png' alt='' />");
}
if (!defined('E_32_USERCLASS')) {
	define('E_32_USERCLASS', "<img class='icon S32' src='".e_IMAGE."admin_images/userclass_32.png' alt='' />");
}
if (!defined('E_32_LANGUAGE')) {
	define('E_32_LANGUAGE', "<img class='icon S32' src='".e_IMAGE."admin_images/language_32.png' alt='' />");
}

// Large Admin Other Link Images
if (!defined('E_32_PLUGIN')) {
	define('E_32_PLUGIN', "<img class='icon S32' src='".e_IMAGE."admin_images/plugins_32.png' alt='' />");
}
if (!defined('E_32_PLUGMANAGER')) {
	define('E_32_PLUGMANAGER', "<img class='icon S32' src='".e_IMAGE."admin_images/plugmanager_32.png' alt='' />");
}
if (!defined('E_32_DOCS')) {
	define('E_32_DOCS', "<img class='icon S32' src='".e_IMAGE."admin_images/docs_32.png' alt='' />");
}
if (!defined('E_32_MAIN')) {
	define('E_32_MAIN', "<img class='icon S32' src='".e_IMAGE."admin_images/main_32.png' alt='' />");
}

if (!defined('E_32_THEMEMANAGER')) {
	define('E_32_THEMEMANAGER', "<img class='icon S32' src='".e_IMAGE."admin_images/themes_32.png' alt='' />");
}

// Large Admin Other Images
if (!defined('E_32_COMMENT')) {
	define('E_32_COMMENT', "<img class='icon S32' src='".e_IMAGE."admin_images/comments_32.png' alt='' />");
}
if (!defined('E_32_ADMINLOG')) {
	define('E_32_ADMINLOG', "<img class='icon S32' src='".e_IMAGE."admin_images/adminlogs_32.png' alt='' />");
}
if (!defined('E_32_LOGOUT')) {
	define('E_32_LOGOUT', "<img class='icon S32' src='".e_IMAGE."admin_images/logout_32.png' alt='' />");
}
if (!defined('E_32_MANAGE')) {
	define('E_32_MANAGE', "<img class='icon S32' src='".e_IMAGE."admin_images/manage_32.png' alt='' />");
}
if (!defined('E_32_CREATE')) {
	define('E_32_CREATE', "<img class='icon S32' src='".e_IMAGE."admin_images/add_32.png' alt='' />");
}
if (!defined('E_32_SETTINGS')) {
	define('E_32_SETTINGS', "<img class='icon S32' src='".e_IMAGE."admin_images/settings_32.png' alt='' />");
}
if (!defined('E_32_SYSINFO')) {
	define('E_32_SYSINFO', "<img class='icon S32' src='".e_IMAGE."admin_images/sysinfo_32.png' alt='' />");
}

$e_icon_array = array(
	'main' => E_32_MAIN,
	'admin' => E_32_ADMIN,
	'admin_pass' => E_32_ADPASS,
	'banlist' => E_32_BANLIST,
	'cache' => E_32_CACHE,
	'comment' => E_32_COMMENT,
	'credits' => E_32_CREDITS,
	'cron'	=> E_32_CRON,
	'custom' => E_32_CUST,
	// 'custom_field' => E_32_CUSTOMFIELD,
	'database' => E_32_DATAB,
	'docs' => E_32_DOCS,
	//'download' => E_32_DOWNL,
	'emoticon' => E_32_EMOTE,
	'filemanage' => E_32_FILE,
	'fileinspector' => E_32_INSPECT,
	'frontpage' => E_32_FRONT,
	'image' => E_32_IMAGES,
	'language' => E_32_LANGUAGE,
	'links' => E_32_LINKS,
	'mail' => E_32_MAIL,
	'menus' => E_32_MENUS,
	'meta' => E_32_META,
	'newsfeed' => E_32_NEWSFEED,
	'news' => E_32_NEWS,
	'notify' => E_32_NOTIFY,
	'phpinfo' => E_32_PHP,
	'plug_manage' => E_32_PLUGMANAGER,
	'poll' => E_32_POLLS,
	'prefs' => E_32_PREFS,
	'search' => E_32_SEARCH,
	'syslogs' => E_32_ADMINLOG,
	'theme_manage' => E_32_THEMEMANAGER,
	'maintain' => E_32_MAINTAIN,
	'upload' => E_32_UPLOADS,
	'eurl' => E_32_EURL,
	'userclass' => E_32_USERCLASS,
	'user_extended' => E_32_USER_EXTENDED,
	'users' => E_32_USER,
	'wmessage' => E_32_WELCOME );

//FIXME array structure - see shortcodes/admin_navigation.php
$admin_cat['title'][1] = ADLAN_CL_1;
$admin_cat['id'][1] = 'setMenu';
$admin_cat['img'][1] = E_16_CAT_SETT;
$admin_cat['lrg_img'][1] = E_32_CAT_SETT;
$admin_cat['sort'][1] = true;

$admin_cat['title'][2] = ADLAN_CL_2;
$admin_cat['id'][2] = 'userMenu';
$admin_cat['img'][2] = E_16_CAT_USER;
$admin_cat['lrg_img'][2] = E_32_CAT_USER;
$admin_cat['sort'][2] = true;

$admin_cat['title'][3] = ADLAN_CL_3;
$admin_cat['id'][3] = 'contMenu';
$admin_cat['img'][3] = E_16_CAT_CONT;
$admin_cat['lrg_img'][3] = E_32_CAT_CONT;
$admin_cat['sort'][3] = true;

$admin_cat['title'][4] = ADLAN_CL_6;
$admin_cat['id'][4] = 'toolMenu';
$admin_cat['img'][4] = E_16_CAT_TOOL;
$admin_cat['lrg_img'][4] = E_32_CAT_TOOL;
$admin_cat['sort'][4] = true;

// Manage
$admin_cat['title'][5] = LAN_MANAGE;
$admin_cat['id'][5] = 'managMenu';
$admin_cat['img'][5] = E_16_CAT_MANAGE;
$admin_cat['lrg_img'][5] = E_32_CAT_MANAGE;
$admin_cat['sort'][5] = TRUE;

if(varsettrue($pref['admin_separate_plugins']))
{
	$admin_cat['title'][6] = ADLAN_CL_7;
	$admin_cat['id'][6] = 'plugMenu';
	$admin_cat['img'][6] = E_16_CAT_PLUG;
	$admin_cat['lrg_img'][6] = E_32_CAT_PLUG;
	$admin_cat['sort'][6] = false;
}
else
{
	// Misc.
	$admin_cat['title'][6] = ADLAN_CL_8;
	$admin_cat['id'][6] = 'miscMenu';
	$admin_cat['img'][6] = E_16_CAT_MISC;
	$admin_cat['lrg_img'][6] = E_32_CAT_MISC;
	$admin_cat['sort'][6] = TRUE;
}

//About menu    - No 20 -  leave space for user-categories.
$admin_cat['title'][20] = ADLAN_CL_20;
$admin_cat['id'][20] = 'aboutMenu';
$admin_cat['img'][20] = E_16_CAT_ABOUT;//E_16_NAV_DOCS
$admin_cat['lrg_img'][20] = E_32_CAT_ABOUT;
$admin_cat['sort'][20] = false;

// Info about attributes
/*
attribute 1 = link
attribute 2 = title
attribute 3 = description
attribute 4 = perms
attribute 5 = category
	1 - settings
	2 - users
	3 - content
	4 - tools   (maintenance)
	5 - plugins/misc
 	6 - manage

	7 - user-category
	8 - user-category etc.

	20 - help

attribute 6 = 16 x 16 image
attribute 7 = 32 x 32 image
*/

//FIXME array structure suitable for e_admin_menu - see shortcodes/admin_navigation.php
//TODO find out where is used $array_functions elsewhere, refactor it


$array_functions = array(
	0 => array(e_ADMIN.'administrator.php', ADLAN_8, ADLAN_9, '3', 2, E_16_ADMIN, E_32_ADMIN),
	1 => array(e_ADMIN.'updateadmin.php', ADLAN_10, ADLAN_11, '', 2, E_16_ADPASS, E_32_ADPASS),
	2 => array(e_ADMIN.'banlist.php', ADLAN_34, ADLAN_35, '4', 2, E_16_BANLIST, E_32_BANLIST),
	4 => array(e_ADMIN.'cache.php', ADLAN_74, ADLAN_75, 'C', 1, E_16_CACHE, E_32_CACHE),
	5 => array(e_ADMIN.'cpage.php', ADLAN_42, ADLAN_43, '5|J', 3, E_16_CUST, E_32_CUST),
	6 => array(e_ADMIN.'db.php', ADLAN_44, ADLAN_45, '0', 4, E_16_DATAB, E_32_DATAB),
//	7 => array(e_ADMIN.'download.php', ADLAN_24, ADLAN_25, 'R', 3, E_16_DOWNL, E_32_DOWNL),
	8 => array(e_ADMIN.'emoticon.php', ADLAN_58, ADLAN_59, 'F', 1, E_16_EMOTE, E_32_EMOTE),
	9 => array(e_ADMIN.'filemanager.php', ADLAN_30, ADLAN_31, '6', 5, E_16_FILE, E_32_FILE),
	10 => array(e_ADMIN.'frontpage.php', ADLAN_60, ADLAN_61, 'G', 1, E_16_FRONT, E_32_FRONT),
	11 => array(e_ADMIN.'image.php', LAN_MEDIAMANAGER, ADLAN_106, 'A', 5, E_16_IMAGES, E_32_IMAGES),
	12 => array(e_ADMIN.'links.php', ADLAN_138, ADLAN_139, 'I', 1, E_16_LINKS, E_32_LINKS),
	13 => array(e_ADMIN.'wmessage.php', ADLAN_28, ADLAN_29, 'M', 3, E_16_WELCOME, E_32_WELCOME),
	14 => array(e_ADMIN.'ugflag.php', ADLAN_40, ADLAN_41, '9', 4, E_16_MAINTAIN, E_32_MAINTAIN),
	15 => array(e_ADMIN.'menus.php', ADLAN_6, ADLAN_7, '2', 5, E_16_MENUS, E_32_MENUS),
	16 => array(e_ADMIN.'meta.php', ADLAN_66, ADLAN_67, 'T', 1, E_16_META, E_32_META),
	17 => array(e_ADMIN.'newspost.php', ADLAN_0, ADLAN_1, 'H|N|7', 3, E_16_NEWS, E_32_NEWS),
	18 => array(e_ADMIN.'phpinfo.php', ADLAN_68, ADLAN_69, '0', 20, E_16_PHP, E_32_PHP),
	19 => array(e_ADMIN.'prefs.php', ADLAN_4, ADLAN_5, '1', 1, E_16_PREFS, E_32_PREFS),
	20 => array(e_ADMIN.'search.php', ADLAN_142, ADLAN_143, 'X', 1, E_16_SEARCH, E_32_SEARCH),
	21 => array(e_ADMIN.'admin_log.php', ADLAN_155, ADLAN_156, 'S', 4, E_16_ADMINLOG, E_32_ADMINLOG),
	22 => array(e_ADMIN.'theme.php', ADLAN_140, ADLAN_141, '1', 5, E_16_THEMEMANAGER, E_32_THEMEMANAGER),
	23 => array(e_ADMIN.'upload.php', ADLAN_72, ADLAN_73, 'V', 3, E_16_UPLOADS, E_32_UPLOADS),
	24 => array(e_ADMIN.'users.php', ADLAN_36, ADLAN_37, '4', 2, E_16_USER, E_32_USER),
	25 => array(e_ADMIN.'userclass2.php', ADLAN_38, ADLAN_39, '4', 2, E_16_USERCLASS, E_32_USERCLASS),
	26 => array(e_ADMIN.'language.php', ADLAN_132, ADLAN_133, '0', 1, E_16_LANGUAGE, E_32_LANGUAGE),
	27 => array(e_ADMIN.'mailout.php', ADLAN_136, ADLAN_137, 'W', 2, E_16_MAIL, E_32_MAIL),
	28 => array(e_ADMIN.'users_extended.php', ADLAN_78, ADLAN_79, '4', 2, E_16_USER_EXTENDED, E_32_USER_EXTENDED),
	29 => array(e_ADMIN.'fileinspector.php', ADLAN_147, ADLAN_148, 'Y', 4, E_16_INSPECT, E_32_INSPECT),
	30 => array(e_ADMIN.'notify.php', ADLAN_149, ADLAN_150, 'O', 4, E_16_NOTIFY, E_32_NOTIFY),
	31 => array(e_ADMIN.'cron.php', ADLAN_157, ADLAN_158, 'U', 4, E_16_CRON, E_32_CRON),
	32 => array(e_ADMIN.'eurl.php', ADLAN_159, ADLAN_160, 'L', 1, E_16_EURL, E_32_EURL),
	33 => array(e_ADMIN.'plugin.php', ADLAN_98, ADLAN_99, 'Z', 5 , E_16_PLUGMANAGER, E_32_PLUGMANAGER),
	34 => array(e_ADMIN.'docs.php', ADLAN_12, ADLAN_13, '', 20, E_16_DOCS, E_32_DOCS),
// TODO System Info.
//	35 => array('#TODO', 'System Info', 'System Information', '', 20, '', ''),
	36 => array(e_ADMIN.'credits.php', LAN_CREDITS, LAN_CREDITS, '', 20, E_16_CREDITS, E_32_CREDITS),
//	37 => array(e_ADMIN.'custom_field.php', ADLAN_161, ADLAN_162, 'U', 4, E_16_CUSTOMFIELD, E_32_CUSTOMFIELD),
	38 => array(e_ADMIN.'comment.php', LAN_COMMENTMAN, LAN_COMMENTMAN, 'B', 5, E_16_COMMENT, E_32_COMMENT)
);

//FIXME  array structure suitable for e_admin_menu - see shortcodes/admin_navigation.php
/*
 * Info about sublinks array structure
 *
 * key = $array_functions key
 * attribute 1 = link
 * attribute 2 = title
 * attribute 3 = description
 * attribute 4 = perms
 * attribute 5 = category
 * attribute 6 = 16 x 16 image
 * attribute 7 = 32 x 32 image
 *
 */
$array_sub_functions = array();
$array_sub_functions[17][] = array(e_ADMIN.'newspost.php', LAN_MANAGE, ADLAN_3, 'H', 3, E_16_MANAGE, E_32_MANAGE);
$array_sub_functions[17][] = array(e_ADMIN.'newspost.php?create', LAN_CREATE, ADLAN_2, 'H', 3, E_16_CREATE, E_32_CREATE);
$array_sub_functions[17][] = array(e_ADMIN.'newspost.php?pref', LAN_PREFS, ADLAN_4, 'H', 3, E_16_SETTINGS, E_32_SETTINGS);

if(!defset('e_PAGETITLE'))
{
	foreach($array_functions as $val)
	{
	    $link = str_replace("../","",$val[0]);
		if(strpos(e_SELF,$link)!==FALSE)
		{
	    	define('e_PAGETITLE',$val[1]);
		}
	}
}


?>
