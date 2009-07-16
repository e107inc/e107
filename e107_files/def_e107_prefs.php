<?php

/*
* e107 website system
*
* Copyright (C) 2001-2008 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Default preferences
*
* $Source: /cvs_backup/e107_0.8/e107_files/def_e107_prefs.php,v $
* $Revision: 1.26 $
* $Date: 2009-07-16 10:12:26 $
* $Author: marj_nl_fr $
*
*/

if (!defined('e107_INIT')) { exit(); }

$pref = array (
  'install_date' => time(),
  'sitename' => LAN_PREF_1,
  'siteurl' => $e_HTTP,
  'sitebutton' => 'button.png',
  'sitetag' => LAN_PREF_2,
  'sitedescription' => '',
  'siteadmin' => $site_admin_user,
  'siteadminemail' => $site_admin_email,
  'sitecontactinfo' => '[b]My Company[/b]
13 My Address St.
City, State, Country
[b]Phone:[/b] 555-555-5555
[b]Email:[/b] sales@mydomain.com',
  'sitetheme' => 'jayya',
  'themecss' => 'style.css',

  // Add some new needed prefs to test the install
  // @TODO: clean me up
  'sitetheme_deflayout' => '3_column',
  'sitetheme_pref' => '',
  'admin_separate_plugins' => '',
  'sitetheme_layouts' =>
  array (
    '3_column' =>
    array ( '@attributes' => array ( 'title' => '3 Columns',
        'preview' => 'preview.jpg',
        'default' => 'true',
      ),
      0 => '',
    ),
    '2_column' =>
    array ( '@attributes' =>
      array ( 'title' => '2 Columns',
        'preview' => 'preview.jpg',
      ),
      0 => '',
    ),
  ),
  'sitetheme_custompages' => '',
  // add login_menu in the list
  'menuconfig_list' => array('login_menu' => array('name' => 'Login', 'link' => 'login_menu/config.php')),
	// end of temporary prefs

  'image_preload' => '0',
  'admintheme' => 'jayya',
  'admincss' => 'style.css',
  'adminstyle' => 'classis',
  'sitedisclaimer' => LAN_PREF_3,
  'newsposts' => '10',

  'flood_protect' => '1',
  'flood_timeout' => '5',
  'flood_time' => '30',
  'flood_hits' => '100',
  'anon_post' => '0',

  'user_reg' => '1',
  'membersonly_enabled' => '0',

  'use_coppa' => '1',
  'signcode' => '0',
  'user_reg_veri' => '1',
  'user_reg_secureveri' => '1',
  'autologinpostsignup' => '0',
  'signup_pass_len' => '4',
  'signup_maxip' => '3',
  'signup_disallow_text' => '',
  'disable_emailcheck' => 0,
  'signup_text' => '',
  'signup_text_after' => '',
  'signup_option_realname' => '1',
  'signup_option_signature' => '1',
  'signup_option_image' => '1',
  'signup_option_class' => '1',
  'signup_remote_emailcheck' => 0,

  'displayname_class' => 255,
  'displayname_maxlength' => 15,
  'loginname_maxlength' => 30,

  'profanity_filter' => '0',
  'profanity_replace' => '[censored]',
  'smiley_activate' => '',
  'log_refertype' => '1',
  'longdate' => '%A %d %B %Y - %H:%M:%S',
  'shortdate' => '%d %b : %H:%M',
  'forumdate' => '%a %b %d %Y, %I:%M%p',
  'sitelanguage' => $pref_language,
  'maintainance_flag' => '0',
  'time_offset' => '0',
  'meta_tag' => '',
  'email_notify' => '0',
  'resize_method' => 'gd2',
  'image_post' => '1',
  'image_post_class' => '0',
  'im_path' => '/usr/X11R6/bin/',
  'im_width' => '120',
  'im_height' => '100',
  'upload_enabled' => '0',
  'upload_storagetype' => '1',
  'upload_maxfilesize' => '',
  'upload_class' => '255',
  'cachestatus' => '',
  'displayrendertime' => '0',
  'displaysql' => '0',
  'displaythemeinfo' => '0',
  'timezone' => 'GMT',
  'search_restrict' => '0',
  'antiflood1' => '1',
  'antiflood_timeout' => '10',
  'autoban' => '1',
  'sitelang_init' => $pref_language,
  'linkpage_screentip' => '0',
  'wmessage_sc' => '0',
  'frontpage_force' => array(e_UC_PUBLIC => ''),
  'frontpage' => array(e_UC_PUBLIC => 'news.php'),

  'admin_alerts_ok' => '1',
  'link_replace' => '0',
  'link_text' => '',
  'logcode' => '0',
  'newsposts_archive' => '0',
  'newsposts_archive_title' => '',
  'news_cats' => '',
  'nbr_cols' => '1',
  'subnews_attach' => '',
  'subnews_resize' => '',
  'subnews_class' => '0',
  'subnews_htmlarea' => '0',
  'news_newdateheader' => '0',
  'email_text' => '',
  'useGeshi' => '0',
  'wysiwyg' => '0',
  'old_np' => '0',
  'make_clickable' => '0',
  'track_online' => '1',
  'emotepack' => 'default',
  'xup_enabled' => '1',
  'mailer' => 'php',
  'ue_upgrade' => '1',
  'search_highlight' => '1',
  'mail_pause' => '3',
  'mail_pausetime' => '4',
  'themecss' => 'canvas.css',
  'auth_method' => '',
  'post_html' => '254',
  'redirectsiteurl' => '0',
  'admin_alerts_uniquemenu' => '0',
  'admin_slidedown_subs' => '1',
  'null' => '',
  'links_new_window' => '1',
  'main_wordwrap' => '',
  'menu_wordwrap' => '',
  'php_bbcode' => '255',
  'ssl_enabled' => '0',
  'fpwcode' => '0',
  'disallowMultiLogin' => '0',
  'profanity_words' => '',
  'adminpwordchange' => '0',
  'comments_icon' => '0',
  'nested_comments' => '1',
  'allowCommentEdit' => '0',
  'admincss' => 'style.css',
  'developer' => '0',
  'download_email' => '0',
  'log_page_accesses' => '0',
  'comments_disabled' => '0',
  'memberlist_access' => '253',
  'check_updates' => '0',

  'enable_rdns' => '0',
  'enable_rdns_on_ban' => '0',
  'ban_max_online_access' => '100,200',
  'ban_retrigger' => '0',

  'multilanguage' => '0',
  'noLanguageSubs' => '0',

  'user_tracking' => 'cookie',
  'cookie_name' => 'e107cookie',
  'passwordEncoding' => 0,			// Legacy encoding
  'allowEmailLogin' => 0,			// Disabled by default
  'password_CHAP' => '0',			// Disabled by default
  'predefinedLoginName' => ''		// Allow user to define own login name by default

);

?>