<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/ad_links.php,v $
|     $Revision: 1.18 $
|     $Date: 2005-02-01 06:08:00 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
	
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
	
// Large Category Images
if (!defined('E_32_CAT_SETT')) {
	define('E_32_CAT_SETT', "<img src='".e_IMAGE."admin_images/cat_settings_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_CAT_USER')) {
	define('E_32_CAT_USER', "<img src='".e_IMAGE."admin_images/cat_users_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_CAT_CONT')) {
	define('E_32_CAT_CONT', "<img src='".e_IMAGE."admin_images/cat_content_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_CAT_FILE')) {
	define('E_32_CAT_FILE', "<img src='".e_IMAGE."admin_images/cat_files_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_CAT_TOOL')) {
	define('E_32_CAT_TOOL', "<img src='".e_IMAGE."admin_images/cat_tools_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_CAT_PLUG')) {
	define('E_32_CAT_PLUG', "<img src='".e_IMAGE."admin_images/cat_plugins_32.png' alt='' style='width: 32px; height: 32px' />");
}
	
// Small Nav Images
if (!defined('E_16_NAV_MAIN')) {
	define('E_16_NAV_MAIN', e_IMAGE.'admin_images/main_16.png');
}
if (!defined('E_16_NAV_DOCS')) {
	define('E_16_NAV_DOCS', e_IMAGE.'admin_images/docs_16.png');
}
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
	define('E_32_NAV_MAIN', "<img src='".e_IMAGE."'admin_images/main_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_NAV_DOCS')) {
	define('E_32_NAV_DOCS', "<img src='".e_IMAGE."'admin_images/docs_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_NAV_LEAV')) {
	define('E_32_NAV_LEAV', "<img src='".e_IMAGE."'admin_images/leave_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_NAV_LGOT')) {
	define('E_32_NAV_LGOT', "<img src='".e_IMAGE."'admin_images/logout_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_NAV_ARROW')) {
	define('E_32_NAV_ARROW', "<img src='".e_IMAGE."'admin_images/arrow_32.png' alt='' style='width: 32px; height: 32px' />");
}
if (!defined('E_32_NAV_ARROW_OVER')) {
	define('E_32_NAV_ARROW_OVER', "<img src='".e_IMAGE."'admin_images/arrow_over_32.png' alt='' style='width: 32px; height: 32px' />");
}
	
// Small Admin Main Link Images
if (!defined('E_16_ADMIN')) {
	define('E_16_ADMIN', "<img src='".e_IMAGE."admin_images/admins_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_ADPASS')) {
	define('E_16_ADPASS', "<img src='".e_IMAGE."admin_images/adminpass_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_ARTICLE')) {
	define('E_16_ARTICLE', "<img src='".e_IMAGE."admin_images/articles_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_BANLIST')) {
	define('E_16_BANLIST', "<img src='".e_IMAGE."admin_images/banlist_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_BANNER')) {
	define('E_16_BANNER', "<img src='".e_IMAGE."admin_images/banners_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_CACHE')) {
	define('E_16_CACHE', "<img src='".e_IMAGE."admin_images/cache_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_CONT')) {
	define('E_16_CONT', "<img src='".e_IMAGE."admin_images/content_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_CUST')) {
	define('E_16_CUST', "<img src='".e_IMAGE."admin_images/custom_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_DATAB')) {
	define('E_16_DATAB', "<img src='".e_IMAGE."admin_images/database_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_DOWNL')) {
	define('E_16_DOWNL', "<img src='".e_IMAGE."admin_images/downloads_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_EMOTE')) {
	define('E_16_EMOTE', "<img src='".e_IMAGE."admin_images/emoticons_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_FILE')) {
	define('E_16_FILE', "<img src='".e_IMAGE."admin_images/filemanager_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_FORUM')) {
	define('E_16_FORUM', "<img src='".e_IMAGE."admin_images/forums_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_FRONT')) {
	define('E_16_FRONT', "<img src='".e_IMAGE."admin_images/frontpage_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_IMAGES')) {
	define('E_16_IMAGES', "<img src='".e_IMAGE."admin_images/images_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_LINKS')) {
	define('E_16_LINKS', "<img src='".e_IMAGE."admin_images/links_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_WELCOME')) {
	define('E_16_WELCOME', "<img src='".e_IMAGE."admin_images/welcome_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_STATS')) {
	define('E_16_STATS', "<img src='".e_IMAGE."admin_images/stats_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_MAIL')) {
	define('E_16_MAIL', "<img src='".e_IMAGE."admin_images/mail_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_MAINTAIN')) {
	define('E_16_MAINTAIN', "<img src='".e_IMAGE."admin_images/maintain_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_MENUS')) {
	define('E_16_MENUS', "<img src='".e_IMAGE."admin_images/menus_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_META')) {
	define('E_16_META', "<img src='".e_IMAGE."admin_images/meta_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_NEWS')) {
	define('E_16_NEWS', "<img src='".e_IMAGE."admin_images/news_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_NEWSFEED')) {
	define('E_16_NEWSFEED', "<img src='".e_IMAGE."admin_images/newsfeeds_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_PHP')) {
	define('E_16_PHP', "<img src='".e_IMAGE."admin_images/phpinfo_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_POLLS')) {
	define('E_16_POLLS', "<img src='".e_IMAGE."admin_images/polls_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_PREFS')) {
	define('E_16_PREFS', "<img src='".e_IMAGE."admin_images/prefs_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_UPLOADS')) {
	define('E_16_UPLOADS', "<img src='".e_IMAGE."admin_images/uploads_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_REVIEW')) {
	define('E_16_REVIEW', "<img src='".e_IMAGE."admin_images/reviews_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_USER')) {
	define('E_16_USER', "<img src='".e_IMAGE."admin_images/users_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_USERCLASS')) {
	define('E_16_USERCLASS', "<img src='".e_IMAGE."admin_images/userclass_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_LANGUAGE')) {
	define('E_16_LANGUAGE', "<img src='".e_IMAGE."admin_images/language_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
	
// Small Admin Other Link Images
if (!defined('E_16_PLUGIN')) {
	define('E_16_PLUGIN', "<img src='".e_IMAGE."admin_images/plugins_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_PLUGMANAGER')) {
	define('E_16_PLUGMANAGER', "<img src='".e_IMAGE."admin_images/plugmanager_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_DOCS')) {
	define('E_16_DOCS', "<img src='".e_IMAGE."admin_images/docs_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
	
// Small Admin Other Images
if (!defined('E_16_COMMENT')) {
	define('E_16_COMMENT', "<img src='".e_IMAGE."admin_images/comments_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
if (!defined('E_16_ADMINLOG')) {
	define('E_16_ADMINLOG', "<img src='".e_IMAGE."admin_images/adminlogs_16.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
}
	
// Large Admin Main Link Images
if (!defined('E_32_ADMIN')) {
	define('E_32_ADMIN', "<img src='".e_IMAGE."admin_images/admins_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_ADPASS')) {
	define('E_32_ADPASS', "<img src='".e_IMAGE."admin_images/adminpass_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_ARTICLE')) {
	define('E_32_ARTICLE', "<img src='".e_IMAGE."admin_images/articles_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_BANLIST')) {
	define('E_32_BANLIST', "<img src='".e_IMAGE."admin_images/banlist_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_BANNER')) {
	define('E_32_BANNER', "<img src='".e_IMAGE."admin_images/banners_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_CACHE')) {
	define('E_32_CACHE', "<img src='".e_IMAGE."admin_images/cache_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_CONT')) {
	define('E_32_CONT', "<img src='".e_IMAGE."admin_images/content_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_CUST')) {
	define('E_32_CUST', "<img src='".e_IMAGE."admin_images/custom_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_DATAB')) {
	define('E_32_DATAB', "<img src='".e_IMAGE."admin_images/database_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_DOWNL')) {
	define('E_32_DOWNL', "<img src='".e_IMAGE."admin_images/downloads_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_EMOTE')) {
	define('E_32_EMOTE', "<img src='".e_IMAGE."admin_images/emoticons_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_FILE')) {
	define('E_32_FILE', "<img src='".e_IMAGE."admin_images/filemanager_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_FORUM')) {
	define('E_32_FORUM', "<img src='".e_IMAGE."admin_images/forums_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_FRONT')) {
	define('E_32_FRONT', "<img src='".e_IMAGE."admin_images/frontpage_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_IMAGES')) {
	define('E_32_IMAGES', "<img src='".e_IMAGE."admin_images/images_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_LINKS')) {
	define('E_32_LINKS', "<img src='".e_IMAGE."admin_images/links_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_WELCOME')) {
	define('E_32_WELCOME', "<img src='".e_IMAGE."admin_images/welcome_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_STATS')) {
	define('E_32_STATS', "<img src='".e_IMAGE."admin_images/stats_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_MAIL')) {
	define('E_32_MAIL', "<img src='".e_IMAGE."admin_images/mail_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_MAINTAIN')) {
	define('E_32_MAINTAIN', "<img src='".e_IMAGE."admin_images/maintain_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_MENUS')) {
	define('E_32_MENUS', "<img src='".e_IMAGE."admin_images/menus_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_META')) {
	define('E_32_META', "<img src='".e_IMAGE."admin_images/meta_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_NEWS')) {
	define('E_32_NEWS', "<img src='".e_IMAGE."admin_images/news_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_NEWSFEED')) {
	define('E_32_NEWSFEED', "<img src='".e_IMAGE."admin_images/newsfeeds_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_PHP')) {
	define('E_32_PHP', "<img src='".e_IMAGE."admin_images/phpinfo_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_POLLS')) {
	define('E_32_POLLS', "<img src='".e_IMAGE."admin_images/polls_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_PREFS')) {
	define('E_32_PREFS', "<img src='".e_IMAGE."admin_images/prefs_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_UPLOADS')) {
	define('E_32_UPLOADS', "<img src='".e_IMAGE."admin_images/uploads_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_REVIEW')) {
	define('E_32_REVIEW', "<img src='".e_IMAGE."admin_images/reviews_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_USER')) {
	define('E_32_USER', "<img src='".e_IMAGE."admin_images/users_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_USERCLASS')) {
	define('E_32_USERCLASS', "<img src='".e_IMAGE."admin_images/userclass_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_LANGUAGE')) {
	define('E_32_LANGUAGE', "<img src='".e_IMAGE."admin_images/language_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
	
// Large Admin Other Link Images
if (!defined('E_32_PLUGIN')) {
	define('E_32_PLUGIN', "<img src='".e_IMAGE."admin_images/plugins_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_PLUGMANAGER')) {
	define('E_32_PLUGMANAGER', "<img src='".e_IMAGE."admin_images/plugmanager_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_DOCS')) {
	define('E_32_DOCS', "<img src='".e_IMAGE."admin_images/docs_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_MAIN')) {
	define('E_32_MAIN', "<img src='".e_IMAGE."admin_images/main_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
	
// Large Admin Other Images
if (!defined('E_32_COMMENT')) {
	define('E_32_COMMENT', "<img src='".e_IMAGE."admin_images/comments_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_ADMINLOG')) {
	define('E_32_ADMINLOG', "<img src='".e_IMAGE."admin_images/adminlogs_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
if (!defined('E_32_LOGOUT')) {
	define('E_32_LOGOUT', "<img src='".e_IMAGE."admin_images/logout_32.png' alt='' style='border:0px; width: 32px; height: 32px' />");
}
	
$e_icon_array = array(
'main' => E_32_MAIN,
	'admin' => E_32_ADMIN,
	'admin_pass' => E_32_ADPASS,
	'article' => E_32_ARTICLE,
	'banlist' => E_32_BANLIST,
	'banner' => E_32_BANNER,
	'cache' => E_32_CACHE,
	'content' => E_32_CONT,
	'custom' => E_32_CUST,
	'database' => E_32_DATAB,
	'docs' => E_32_DOCS,
	'download' => E_32_DOWNL,
	'emoticon' => E_32_EMOTE,
	'filemanage' => E_32_FILE,
	'frontpage' => E_32_FRONT,
	'image' => E_32_IMAGES,
	'language' => E_32_LANGUAGE,
	'links' => E_32_LINKS,
	'log' => E_32_STATS,
	'mail' => E_32_MAIL,
	'menus' => E_32_MENUS,
	'meta' => E_32_META,
	'newsfeed' => E_32_NEWSFEED,
	'news' => E_32_NEWS,
	'phpinfo' => E_32_PHP,
	'plug_manage' => E_32_PLUGMANAGER,
	'poll' => E_32_POLLS,
	'prefs' => E_32_PREFS,
	'review' => E_32_REVIEW,
	'maintain' => E_32_MAINTAIN,
	'upload' => E_32_UPLOADS,
	'userclass' => E_32_USERCLASS,
	'users' => E_32_USER,
	'wmessage' => E_32_WELCOME );
	
$admin_cat['title'][1] = ADLAN_CL_1;
$admin_cat['id'][1] = 'setMenu';
$admin_cat['img'][1] = E_16_CAT_SETT;
$admin_cat['lrg_img'][1] = E_32_CAT_SETT;
	
$admin_cat['title'][2] = ADLAN_CL_2;
$admin_cat['id'][2] = 'userMenu';
$admin_cat['img'][2] = E_16_CAT_USER;
$admin_cat['lrg_img'][2] = E_32_CAT_USER;
	
$admin_cat['title'][3] = ADLAN_CL_3;
$admin_cat['id'][3] = 'contMenu';
$admin_cat['img'][3] = E_16_CAT_CONT;
$admin_cat['lrg_img'][3] = E_32_CAT_CONT;
	
$admin_cat['title'][5] = ADLAN_CL_5;
$admin_cat['id'][5] = 'fileMenu';
$admin_cat['img'][5] = E_16_CAT_FILE;
$admin_cat['lrg_img'][5] = E_32_CAT_FILE;
	
$admin_cat['title'][6] = ADLAN_CL_6;
$admin_cat['id'][6] = 'toolMenu';
$admin_cat['img'][6] = E_16_CAT_TOOL;
$admin_cat['lrg_img'][6] = E_32_CAT_TOOL;
	
$admin_cat['title'][7] = ADLAN_CL_7;
$admin_cat['id'][7] = 'plugMenu';
$admin_cat['img'][7] = E_16_CAT_PLUG;
$admin_cat['lrg_img'][7] = E_32_CAT_PLUG;
	
	
// Info about attributes
/*
attribute 1 = link
attribute 2 = title
attribute 3 = description
attribute 4 = perms
attribute 5 = category
attribute 6 = 16 x 16 image
attribute 7 = 32 x 32 image
*/
	
$array_functions = array(
0 => array(e_ADMIN."administrator.php", ADLAN_8, ADLAN_9, "3", 2, E_16_ADMIN, E_32_ADMIN),
	1 => array(e_ADMIN."updateadmin.php", ADLAN_10, ADLAN_11, "", 2, E_16_ADPASS, E_32_ADPASS),
	2 => array(e_ADMIN."article.php", ADLAN_14, ADLAN_15, "J", 3, E_16_ARTICLE, E_32_ARTICLE),
	3 => array(e_ADMIN."banlist.php", ADLAN_34, ADLAN_35, "4", 2, E_16_BANLIST, E_32_BANLIST),
	4 => array(e_ADMIN."banner.php", ADLAN_54, ADLAN_55, "D", 6, E_16_BANNER, E_32_BANNER),
	5 => array(e_ADMIN."cache.php", ADLAN_74, ADLAN_75, "0", 1, E_16_CACHE, E_32_CACHE),
	6 => array(e_ADMIN."content.php", ADLAN_16, ADLAN_17, "L", 3, E_16_CONT, E_32_CONT),
	7 => array(e_ADMIN."custommenu.php", ADLAN_42, ADLAN_43, "2", 6, E_16_CUST, E_32_CUST),
	8 => array(e_ADMIN."db.php", ADLAN_44, ADLAN_45, "0", 1, E_16_DATAB, E_32_DATAB),
	9 => array(e_ADMIN."download.php", ADLAN_24, ADLAN_25, "R", 5, E_16_DOWNL, E_32_DOWNL),
	10 => array(e_ADMIN."emoticon.php", ADLAN_58, ADLAN_59, "F", 6, E_16_EMOTE, E_32_EMOTE),
	11 => array(e_ADMIN."filemanager.php", ADLAN_30, ADLAN_31, "6", 5, E_16_FILE, E_32_FILE),
	12 => array(e_ADMIN."frontpage.php", ADLAN_60, ADLAN_61, "G", 1, E_16_FRONT, E_32_FRONT),
	13 => array(e_ADMIN."image.php", ADLAN_105, ADLAN_106, "5", 6, E_16_IMAGES, E_32_IMAGES),
	14 => array(e_ADMIN."links.php", ADLAN_138, ADLAN_139, "I", 1, E_16_LINKS, E_32_LINKS),
	15 => array(e_ADMIN."wmessage.php", ADLAN_28, ADLAN_29, "M", 3, E_16_WELCOME, E_32_WELCOME),
	16 => array(e_ADMIN."log.php", ADLAN_64, ADLAN_65, "S", 1, E_16_STATS, E_32_STATS),
	17 => array(e_ADMIN."ugflag.php", ADLAN_40, ADLAN_41, "9", 1, E_16_MAINTAIN, E_32_MAINTAIN),
	18 => array(e_ADMIN."menus.php", ADLAN_6, ADLAN_7, "2", 1, E_16_MENUS, E_32_MENUS),
	19 => array(e_ADMIN."meta.php", ADLAN_66, ADLAN_67, "T", 1, E_16_META, E_32_META),
	20 => array(e_ADMIN."newspost.php", ADLAN_0, ADLAN_1, "H", 3, E_16_NEWS, E_32_NEWS),
	21 => array(e_ADMIN."newsfeed.php", ADLAN_62, ADLAN_63, "E", 6, E_16_NEWSFEED, E_32_NEWSFEED),
	22 => array(e_ADMIN."phpinfo.php", ADLAN_68, ADLAN_69, "0", 6, E_16_PHP, E_32_PHP),
	23 => array(e_ADMIN."poll.php", ADLAN_70, ADLAN_71, "U", 6, E_16_POLLS, E_32_POLLS),
	24 => array(e_ADMIN."prefs.php", ADLAN_4, ADLAN_5, "1", 1, E_16_PREFS, E_32_PREFS),
	25 => array(e_ADMIN."upload.php", ADLAN_72, ADLAN_73, "V", 5, E_16_UPLOADS, E_32_UPLOADS),
	26 => array(e_ADMIN."review.php", ADLAN_18, ADLAN_19, "K", 3, E_16_REVIEW, E_32_REVIEW),
	27 => array(e_ADMIN."users.php", ADLAN_36, ADLAN_37, "4", 2, E_16_USER, E_32_USER),
	28 => array(e_ADMIN."userclass2.php", ADLAN_38, ADLAN_39, "4", 2, E_16_USERCLASS, E_32_USERCLASS),
	29 => array(e_ADMIN."language.php", ADLAN_132, ADLAN_133, "0", 1, E_16_LANGUAGE, E_32_LANGUAGE),
	30 => array(e_ADMIN."mailout.php", ADLAN_136, ADLAN_137, "W", 2, E_16_MAIL, E_32_MAIL),
	);
?>