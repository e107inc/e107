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
|     $Revision: 1.4 $
|     $Date: 2005-01-05 16:57:36 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

if (file_exists(THEME.'admin_images/admin_images.php')) {
	e107_require_once(THEME.'admin_images/admin_images.php');
}
	
// Large Category Images
if (!defined('LRG_IMG_CAT_SETT')) { define('LRG_IMG_CAT_SETT', "<img src='".e_IMAGE."admin_images/cat_settings.png' alt='' style='width: 27px; height: 30px' />"); }
if (!defined('LRG_IMG_CAT_USER')) { define('LRG_IMG_CAT_USER', "<img src='".e_IMAGE."admin_images/cat_user.png' alt='' style='width: 31px; height: 30px' />"); }
if (!defined('LRG_IMG_CAT_CONT')) { define('LRG_IMG_CAT_CONT', "<img src='".e_IMAGE."admin_images/cat_content.png' alt='' style='width: 34px; height: 30px' />"); }
if (!defined('LRG_IMG_CAT_COMS')) { define('LRG_IMG_CAT_COMS', "<img src='".e_IMAGE."admin_images/cat_coms.png' alt='' style='width: 23px; height: 30px' />"); }
if (!defined('LRG_IMG_CAT_FILE')) { define('LRG_IMG_CAT_FILE', "<img src='".e_IMAGE."admin_images/cat_file.png' alt='' style='width: 30px; height: 30px' />"); }
if (!defined('LRG_IMG_CAT_TOOL')) { define('LRG_IMG_CAT_TOOL', "<img src='".e_IMAGE."admin_images/cat_tools.png' alt='' style='width: 34px; height: 30px' />"); }
if (!defined('LRG_IMG_CAT_PLUG')) { define('LRG_IMG_CAT_PLUG', "<img src='".e_IMAGE."admin_images/cat_plugins.png' alt='' style='width: 29px; height: 30px' />"); }

// Small Nav Images
if (!defined('SML_IMG_NAV_SETT')) { define('SML_IMG_NAV_SETT', e_IMAGE.'admin_images/settings.png'); }
if (!defined('SML_IMG_NAV_USER')) { define('SML_IMG_NAV_USER', e_IMAGE.'admin_images/user.png'); }
if (!defined('SML_IMG_NAV_CONT')) { define('SML_IMG_NAV_CONT', e_IMAGE.'admin_images/content.png'); }
if (!defined('SML_IMG_NAV_COMS')) { define('SML_IMG_NAV_COMS', e_IMAGE.'admin_images/coms.png'); }
if (!defined('SML_IMG_NAV_FILE')) { define('SML_IMG_NAV_FILE', e_IMAGE.'admin_images/file.png'); }
if (!defined('SML_IMG_NAV_TOOL')) { define('SML_IMG_NAV_TOOL', e_IMAGE.'admin_images/tools.png'); }
if (!defined('SML_IMG_NAV_PLUG')) { define('SML_IMG_NAV_PLUG', e_IMAGE.'admin_images/plugins.png'); }
if (!defined('SML_IMG_NAV_MAIN')) { define('SML_IMG_NAV_MAIN', e_IMAGE.'admin_images/main.png'); }
if (!defined('SML_IMG_NAV_DOCS')) { define('SML_IMG_NAV_DOCS', e_IMAGE.'admin_images/docs.png'); }
if (!defined('SML_IMG_NAV_LEAV')) { define('SML_IMG_NAV_LEAV', e_IMAGE.'admin_images/leave.png'); }
if (!defined('SML_IMG_NAV_LGOT')) { define('SML_IMG_NAV_LGOT', e_IMAGE.'admin_images/logout.png'); }

// Small Admin Main Link Images
if (!defined('SML_IMG_ADMIN')) { define('SML_IMG_ADMIN', "<img src='".e_IMAGE."admin_images/admins.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_ADPASS')) { define('SML_IMG_ADPASS', "<img src='".e_IMAGE."admin_images/adminpass.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_ARTICLE')) { define('SML_IMG_ARTICLE', "<img src='".e_IMAGE."admin_images/articles.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_BANLIST')) { define('SML_IMG_BANLIST', "<img src='".e_IMAGE."admin_images/banlist.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_BANNER')) { define('SML_IMG_BANNER', "<img src='".e_IMAGE."admin_images/banners.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_CACHE')) { define('SML_IMG_CACHE', "<img src='".e_IMAGE."admin_images/cache.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_CHAT')) { define('SML_IMG_CHAT', "<img src='".e_IMAGE."admin_images/chatbox.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_CONT')) { define('SML_IMG_CONT', "<img src='".e_IMAGE."admin_images/econtent.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_CUST')) { define('SML_IMG_CUST', "<img src='".e_IMAGE."admin_images/custom.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_DATAB')) { define('SML_IMG_DATAB', "<img src='".e_IMAGE."admin_images/database.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_DOWNL')) { define('SML_IMG_DOWNL', "<img src='".e_IMAGE."admin_images/downloads.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_EMOTE')) { define('SML_IMG_EMOTE', "<img src='".e_IMAGE."admin_images/emoticons.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_FILE')) { define('SML_IMG_FILE', "<img src='".e_IMAGE."admin_images/filemanage.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_FORUM')) { define('SML_IMG_FORUM', "<img src='".e_IMAGE."admin_images/forums.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_FRONT')) { define('SML_IMG_FRONT', "<img src='".e_IMAGE."admin_images/frontpage.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_IMAGES')) { define('SML_IMG_IMAGES', "<img src='".e_IMAGE."admin_images/images.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_LINKS')) { define('SML_IMG_LINKS', "<img src='".e_IMAGE."admin_images/links.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_WELCOME')) { define('SML_IMG_WELCOME', "<img src='".e_IMAGE."admin_images/welcome.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_STATS')) { define('SML_IMG_STATS', "<img src='".e_IMAGE."admin_images/stats.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_MAINTAIN')) { define('SML_IMG_MAINTAIN', "<img src='".e_IMAGE."admin_images/maintain.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_MENUS')) { define('SML_IMG_MENUS', "<img src='".e_IMAGE."admin_images/menus.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_META')) { define('SML_IMG_META', "<img src='".e_IMAGE."admin_images/meta.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_NEWS')) { define('SML_IMG_NEWS', "<img src='".e_IMAGE."admin_images/news.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_NEWSFEED')) { define('SML_IMG_NEWSFEED', "<img src='".e_IMAGE."admin_images/newsfeeds.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_PHP')) { define('SML_IMG_PHP', "<img src='".e_IMAGE."admin_images/phpinfo.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_POLLS')) { define('SML_IMG_POLLS', "<img src='".e_IMAGE."admin_images/polls.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_PREFS')) { define('SML_IMG_PREFS', "<img src='".e_IMAGE."admin_images/prefs.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_UPLOADS')) { define('SML_IMG_UPLOADS', "<img src='".e_IMAGE."admin_images/uploads.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_REVIEW')) { define('SML_IMG_REVIEW', "<img src='".e_IMAGE."admin_images/reviews.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_USER')) { define('SML_IMG_USER', "<img src='".e_IMAGE."admin_images/users.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_USERCLASS')) { define('SML_IMG_USERCLASS', "<img src='".e_IMAGE."admin_images/userclass.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_LANGUAGE')) { define('SML_IMG_LANGUAGE', "<img src='".e_IMAGE."admin_images/language.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }

// Small Admin Other Link Images
if (!defined('SML_IMG_PLUGIN')) { define('SML_IMG_PLUGIN', "<img src='".e_IMAGE."admin_images/eplugins.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_DOCS')) { define('SML_IMG_DOCS', "<img src='".e_IMAGE."admin_images/docs.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }

// Small Admin Other Images
if (!defined('SML_IMG_COMMENT')) { define('SML_IMG_COMMENT', "<img src='".e_IMAGE."admin_images/comments.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }
if (!defined('SML_IMG_ADMINLOG')) { define('SML_IMG_ADMINLOG', "<img src='".e_IMAGE."admin_images/adminlogs.png' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 14px' />"); }

$admin_cat['title'][1] = ADLAN_CL_1;
$admin_cat['id'][1] = 'setMenu';
$admin_cat['img'][1] = SML_IMG_NAV_SETT;
$admin_cat['lrg_img'][1] = LRG_IMG_CAT_SETT;

$admin_cat['title'][2] = ADLAN_CL_2;
$admin_cat['id'][2] = 'userMenu';
$admin_cat['img'][2] = SML_IMG_NAV_USER;
$admin_cat['lrg_img'][2] = LRG_IMG_CAT_USER;

$admin_cat['title'][3] = ADLAN_CL_3;
$admin_cat['id'][3] = 'contMenu';
$admin_cat['img'][3] = SML_IMG_NAV_CONT;
$admin_cat['lrg_img'][3] = LRG_IMG_CAT_CONT;

/*
$admin_cat['title'][4] = ADLAN_CL_4;
$admin_cat['id'][4] = 'comMenu';
$admin_cat['img'][4] = SML_IMG_NAV_COMS;
$admin_cat['lrg_img'][4] = LRG_IMG_CAT_COMS;
*/

$admin_cat['title'][5] = ADLAN_CL_5;
$admin_cat['id'][5] = 'fileMenu';
$admin_cat['img'][5] = SML_IMG_NAV_FILE;
$admin_cat['lrg_img'][5] = LRG_IMG_CAT_FILE;

$admin_cat['title'][6] = ADLAN_CL_6;
$admin_cat['id'][6] = 'toolMenu';
$admin_cat['img'][6] = SML_IMG_NAV_TOOL;
$admin_cat['lrg_img'][6] = LRG_IMG_CAT_TOOL;

$admin_cat['title'][7] = ADLAN_CL_7;
$admin_cat['id'][7] = 'plugMenu';
$admin_cat['img'][7] = SML_IMG_NAV_PLUG;
$admin_cat['lrg_img'][7] = LRG_IMG_CAT_PLUG;


// Info about attributes
/*
attribute 1 = link
attribute 2 = title
attribute 3 = description
attribute 4 = perms
attribute 5 = category
*/

$array_functions = array(
	0 => array(e_ADMIN."administrator.php", ADLAN_8, ADLAN_9, "3", 2, SML_IMG_ADMIN),
	1 => array(e_ADMIN."updateadmin.php", ADLAN_10, ADLAN_11, "", 2, SML_IMG_ADPASS),
	2 => array(e_ADMIN."article.php", ADLAN_14, ADLAN_15, "J", 3, SML_IMG_ARTICLE),
	3 => array(e_ADMIN."banlist.php", ADLAN_34, ADLAN_35, "4", 2, SML_IMG_BANLIST),
	4 => array(e_ADMIN."banner.php", ADLAN_54, ADLAN_55, "D", 6, SML_IMG_BANNER),
	5 => array(e_ADMIN."cache.php", ADLAN_74, ADLAN_75, "0", 1, SML_IMG_CACHE),
	6 => array(e_ADMIN."chatbox.php", ADLAN_56, ADLAN_57, "C", 3, SML_IMG_CHAT),
	7 => array(e_ADMIN."content.php", ADLAN_16, ADLAN_17, "L", 3, SML_IMG_CONT),
	8 => array(e_ADMIN."custommenu.php", ADLAN_42, ADLAN_43, "2", 6, SML_IMG_CUST),
	9 => array(e_ADMIN."db.php",ADLAN_44, ADLAN_45,"0", 1, SML_IMG_DATAB),
	10 => array(e_ADMIN."download.php", ADLAN_24, ADLAN_25, "R", 5, SML_IMG_DOWNL),
	11 => array(e_ADMIN."emoticon.php", ADLAN_58, ADLAN_59, "F", 6, SML_IMG_EMOTE),
	12 => array(e_ADMIN."filemanager.php", ADLAN_30, ADLAN_31, "6", 5, SML_IMG_FILE),
	13 => array(e_ADMIN."forum.php", ADLAN_12, ADLAN_13, "5", 3, SML_IMG_FORUM),
	14 => array(e_ADMIN."frontpage.php", ADLAN_60, ADLAN_61, "G", 1, SML_IMG_FRONT),
	15 => array(e_ADMIN."image.php", ADLAN_105, ADLAN_106, "5", 6, SML_IMG_IMAGES),
	16 => array(e_ADMIN."links.php", ADLAN_20, ADLAN_21, "I", 3, SML_IMG_LINKS),
	17 => array(e_ADMIN."wmessage.php", ADLAN_28, ADLAN_29, "M", 3, SML_IMG_WELCOME),
	18 => array(e_ADMIN."log.php", ADLAN_64, ADLAN_65, "S", 1, SML_IMG_STATS),
	19 => array(e_ADMIN."ugflag.php", ADLAN_40, ADLAN_41, "9", 1, SML_IMG_MAINTAIN),
	20 => array(e_ADMIN."menus.php", ADLAN_6, ADLAN_7, "2", 1, SML_IMG_MENUS),
	21 => array(e_ADMIN."meta.php", ADLAN_66, ADLAN_67, "T", 1, SML_IMG_META),
	22 => array(e_ADMIN."newspost.php", ADLAN_0, ADLAN_1, "H", 3, SML_IMG_NEWS),
	23 => array(e_ADMIN."newsfeed.php", ADLAN_62, ADLAN_63, "E", 6, SML_IMG_NEWSFEED),
	24 => array(e_ADMIN."phpinfo.php", ADLAN_68, ADLAN_69, "0", 6, SML_IMG_PHP),
	25 => array(e_ADMIN."poll.php", ADLAN_70, ADLAN_71, "U", 6, SML_IMG_POLLS),
	26 => array(e_ADMIN."prefs.php", ADLAN_4, ADLAN_5, "1", 1, SML_IMG_PREFS),
	27 => array(e_ADMIN."upload.php", ADLAN_72, ADLAN_73, "V", 5, SML_IMG_UPLOADS),
	28 => array(e_ADMIN."review.php", ADLAN_18, ADLAN_19, "K", 3, SML_IMG_REVIEW),
	29 => array(e_ADMIN."users.php", ADLAN_36, ADLAN_37, "4", 2, SML_IMG_USER),
	30 => array(e_ADMIN."userclass2.php", ADLAN_38, ADLAN_39, "4", 2, SML_IMG_USERCLASS),
	31 => array(e_ADMIN."language.php", ADLAN_132, ADLAN_133, "0", 1, SML_IMG_LANGUAGE)
);
?>
