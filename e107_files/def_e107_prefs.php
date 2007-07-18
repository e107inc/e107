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
|     $Source: /cvs_backup/e107_0.8/e107_files/def_e107_prefs.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-07-18 20:20:20 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

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
  'sitetheme' => 'reline',
  'themecss' => 'style.css',
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
  'use_coppa' => '1',
  'membersonly_enabled' => '0',
  'signup_pass_len' => '',
  'signup_maxip' => '3',
  'signup_disallow_text' => '',
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
  'log_lvcount' => '10',
  'meta_tag' => '',
  'user_reg_veri' => '1',
  'email_notify' => '0',
  'user_tracking' => 'cookie',
  'cookie_name' => 'e107cookie',
  'resize_method' => 'gd2',
  'im_path' => '/usr/X11R6/bin/convert',
  'im_quality' => '80',
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
  'plug_status' => 'rss_menu',
  'plug_latest' => '',
  'wmessage_sc' => '0',
  'frontpage' =>
  array (
    'all' => 'news.php',
  ),
  'signup_text' => '',
  'admin_alerts_ok' => '1',
  'link_replace' => '0',
  'link_text' => '',
  'signcode' => '0',
  'logcode' => '0',
  'signup_option_realname' => '1',
  'signup_option_signature' => '1',
  'signup_option_image' => '1',
  'signup_option_timezone' => '1',
  'signup_option_class' => '1',
  'newsposts_archive' => '0',
  'newsposts_archive_title' => '',
  'news_cats' => '',
  'nbr_cols' => '1',
  'subnews_attach' => '',
  'subnews_resize' => '',
  'subnews_class' => '0',
  'subnews_htmlarea' => '0',
  'subnews_hide_news' => '',
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
  'plug_sc' => ':featurebox',
  'auth_method' => '',
  'post_html' => '254',
  'redirectsiteurl' => '0',
  'admin_alerts_uniquemenu' => '0',
  'signup_text_after' => '',
  'null' => '',
  'links_new_window' => '1',
  'main_wordwrap' => '',
  'menu_wordwrap' => '',
  'php_bbcode' => '255',
  'ssl_enabled' => '0',
  'fpwcode' => '0',
  'user_reg_secureveri' => '1',
  'disallowMultiLogin' => '0',
  'profanity_words' => '',
  'adminpwordchange' => '0',
  'comments_icon' => '0',
  'nested_comments' => '1',
  'allowCommentEdit' => '0',
  'rss_feeds' => '1',
  'admincss' => 'style.css',
  'developer' => '0',
  'download_email' => '0',
  'comments_disabled' => '0',
  'memberlist_access' => '253',
  'check_updates' => '0'
);

?>