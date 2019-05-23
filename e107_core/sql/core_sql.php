<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Core SQL
 *
*/

header("location:../index.php");
exit;
?>
#
# +---------------------------------------------------------------+
# |        e107 website system
# |
# |        Copyright (C) 2008-2015 e107 Inc (e107.org)
# |        http://e107.org
# |
# |        Released under the terms and conditions of the
# |        GNU General Public License (http://gnu.org).
# +---------------------------------------------------------------+
# Database : <variable>
# --------------------------------------------------------

#
# Table structure for table `admin_log` - admin/moderator actions
#
CREATE TABLE admin_log (
  dblog_id int(10) unsigned NOT NULL auto_increment,
  dblog_datestamp int(10) unsigned NOT NULL default '0',
  dblog_microtime int(10) unsigned NOT NULL default '0',
  dblog_type tinyint(3) NOT NULL default '0',
  dblog_eventcode varchar(10) NOT NULL default '',
  dblog_user_id int(10) unsigned NOT NULL default '0',
  dblog_ip varchar(45) NOT NULL default '',
  dblog_title varchar(255) NOT NULL default '',
  dblog_remarks text NOT NULL,
  PRIMARY KEY  (dblog_id),
  KEY dblog_datestamp (dblog_datestamp)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `audit_log` - user audit trail
#
CREATE TABLE audit_log (
  dblog_id int(10) unsigned NOT NULL auto_increment,
  dblog_datestamp int(10) unsigned NOT NULL default '0',
  dblog_microtime int(10) unsigned NOT NULL default '0',
  dblog_eventcode varchar(10) NOT NULL default '',
  dblog_user_id int(10) unsigned NOT NULL default '0',
  dblog_user_name varchar(100) NOT NULL default '',
  dblog_ip varchar(45) NOT NULL default '',
  dblog_title varchar(255) NOT NULL default '',
  dblog_remarks text NOT NULL,
  PRIMARY KEY  (dblog_id),
  KEY dblog_datestamp (dblog_datestamp)
) ENGINE=MyISAM;
# --------------------------------------------------------


#
# Table structure for table `banlist`
#

CREATE TABLE banlist (
  banlist_id int(10) unsigned NOT NULL auto_increment,
  banlist_ip varchar(100) NOT NULL default '',
  banlist_bantype tinyint(3) signed NOT NULL default '0',
  banlist_datestamp int(10) unsigned NOT NULL default '0',
  banlist_banexpires int(10) unsigned NOT NULL default '0',
  banlist_admin smallint(5) unsigned NOT NULL default '0',
  banlist_reason tinytext NOT NULL,
  banlist_notes tinytext NOT NULL,
  PRIMARY KEY  (banlist_id),
  KEY banlist_ip (banlist_ip),
  KEY banlist_datestamp (banlist_datestamp),
  KEY banlist_banexpires (banlist_banexpires)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `comments`
#

CREATE TABLE comments (
  comment_id int(10) unsigned NOT NULL auto_increment,
  comment_pid int(10) unsigned NOT NULL default '0',
  comment_item_id int(10) unsigned NOT NULL default '0',
  comment_subject varchar(100) NOT NULL default '',
  comment_author_id int(10) unsigned NOT NULL default '0',
  comment_author_name varchar(100) NOT NULL default '',
  comment_author_email varchar(200) NOT NULL default '',
  comment_datestamp int(10) unsigned NOT NULL default '0',
  comment_comment text NOT NULL,
  comment_blocked tinyint(3) unsigned NOT NULL default '0',
  comment_ip varchar(45) NOT NULL default '',
  comment_type varchar(20) NOT NULL default '0',
  comment_lock tinyint(1) unsigned NOT NULL default '0',
  comment_share tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (comment_id),
  KEY comment_blocked (comment_blocked),
  KEY comment_author_id (comment_author_id) 
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `core`
#
CREATE TABLE core (
  e107_name varchar(100) NOT NULL default '',
  e107_value text NOT NULL,
  PRIMARY KEY  (e107_name)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `core_media` - media manager
#

CREATE TABLE core_media (
  media_id int(10) unsigned NOT NULL auto_increment,
  media_type varchar(50) NOT NULL default '',
  media_name varchar(255) NOT NULL default '',
  media_caption varchar(255) NOT NULL default '',
  media_description varchar(255) NOT NULL default '',
  media_category varchar(255) NOT NULL default '',
  media_datestamp int(10) unsigned NOT NULL default '0',
  media_author int(10) unsigned NOT NULL default '0',
  media_url varchar(255) NOT NULL default '',
  media_size int(20) unsigned NOT NULL default '0',
  media_dimensions varchar(25) NOT NULL default '',
  media_userclass varchar(255) NOT NULL default '',
  media_usedby text NOT NULL,
  media_tags text NOT NULL,
  PRIMARY KEY (media_id),
  UNIQUE KEY media_url (media_url)
) ENGINE=MyISAM;

CREATE TABLE core_media_cat (
  media_cat_id int(10) unsigned NOT NULL auto_increment,
  media_cat_owner varchar(255) NOT NULL default '',
  media_cat_category varchar(255) NOT NULL default '',
  media_cat_title text NOT NULL,
  media_cat_sef varchar(255) NOT NULL default '',
  media_cat_diz text NOT NULL,
  media_cat_class int(5) default '0',
  media_cat_image varchar(255) NOT NULL default '',
  media_cat_order int(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (media_cat_id),
  UNIQUE KEY media_cat_category (media_cat_category)
) ENGINE=MyISAM;


CREATE TABLE cron (
 cron_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 cron_name VARCHAR(50) NOT NULL,
 cron_category VARCHAR(20) NOT NULL,
 cron_description VARCHAR(255) NOT NULL,
 cron_function VARCHAR(50) NOT NULL,
 cron_tab VARCHAR(255) NOT NULL,
 cron_lastrun INT(13) UNSIGNED NOT NULL,
 cron_active INT(1) UNSIGNED NOT NULL,
 PRIMARY KEY (cron_id),
 UNIQUE KEY cron_function (cron_function)
) ENGINE = MYISAM;


# Table structure for table `dblog` - db/debug/rolling
#

CREATE TABLE dblog (
  dblog_id int(10) unsigned NOT NULL auto_increment,
  dblog_datestamp int(10) unsigned NOT NULL default '0',
  dblog_microtime int(10) unsigned NOT NULL default '0',
  dblog_type tinyint(3) NOT NULL default '0',
  dblog_eventcode varchar(10) NOT NULL default '',
  dblog_user_id int(10) unsigned NOT NULL default '0',
  dblog_user_name varchar(100) NOT NULL default '',
  dblog_ip varchar(45) NOT NULL default '',
  dblog_caller varchar(255) NOT NULL default '',
  dblog_title varchar(255) NOT NULL default '',
  dblog_remarks text NOT NULL,
  PRIMARY KEY  (dblog_id),
  KEY dblog_datestamp (dblog_datestamp)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `generic` (includes Welcome Messages)
#
CREATE TABLE generic (
  gen_id int(10) unsigned NOT NULL auto_increment,
  gen_type varchar(80) NOT NULL default '',
  gen_datestamp int(10) unsigned NOT NULL default '0',
  gen_user_id int(10) unsigned NOT NULL default '0',
  gen_ip varchar(80) NOT NULL default '',
  gen_intdata int(10) unsigned NOT NULL default '0',
  gen_chardata text NOT NULL,
  PRIMARY KEY  (gen_id),
  KEY gen_type (gen_type)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `links` (navigation)
#

CREATE TABLE links (
  link_id int(10) unsigned NOT NULL auto_increment,
  link_name varchar(100) NOT NULL default '',
  link_url varchar(200) NOT NULL default '',
  link_description text NOT NULL,
  link_button varchar(100) NOT NULL default '',
  link_category tinyint(3) unsigned NOT NULL default '0',
  link_order int(10) unsigned NOT NULL default '0',
  link_parent int(10) unsigned NOT NULL default '0',
  link_open tinyint(1) unsigned NOT NULL default '0',
  link_class varchar(255) NOT NULL default '0',
  link_function varchar(100) NOT NULL default '',
  link_sefurl varchar(255) NOT NULL,
  link_owner varchar(50) NOT NULL default '',
  PRIMARY KEY  (link_id)
) ENGINE=MyISAM;

# --------------------------------------------------------



#
# Table structure for mailing-related tables
#
CREATE TABLE mail_recipients (
	mail_target_id int(10) unsigned NOT NULL auto_increment,
	mail_recipient_id int(10) unsigned NOT NULL default '0',
	mail_recipient_email varchar(80) NOT NULL default '',
	mail_recipient_name varchar(80) NOT NULL default '',
	mail_status tinyint(1) unsigned NOT NULL default '0',
	mail_detail_id int(10) unsigned NOT NULL default '0',
	mail_send_date int(10) unsigned NOT NULL default '0',
	mail_target_info text,
	PRIMARY KEY (mail_target_id),
	KEY mail_status (mail_status),
	KEY mail_detail_id (mail_detail_id)
) ENGINE=MyISAM;

CREATE TABLE mail_content (
	mail_source_id int(10) unsigned NOT NULL auto_increment,
	mail_content_status tinyint(1) unsigned NOT NULL default '0',
	mail_total_count int(10) unsigned NOT NULL default '0',
	mail_togo_count int(10) unsigned NOT NULL default '0',
	mail_sent_count int(10) unsigned NOT NULL default '0',
	mail_fail_count int(10) unsigned NOT NULL default '0',
	mail_bounce_count int(10) unsigned NOT NULL default '0',
	mail_start_send int(10) unsigned NOT NULL default '0',
	mail_end_send int(10) unsigned NOT NULL default '0',
	mail_create_date int(10) unsigned NOT NULL default '0',
	mail_creator int(10) unsigned NOT NULL default '0',
	mail_create_app varchar(20) NOT NULL default '',
	mail_e107_priority tinyint(1) unsigned NOT NULL default '0',
	mail_notify_complete tinyint(1) unsigned NOT NULL default '0',
	mail_last_date int(10) unsigned NOT NULL default '0',
	mail_title varchar(100) NOT NULL default '',
	mail_subject varchar(100) NOT NULL default '',
	mail_body text,
	mail_body_templated text,
	mail_other text,
	mail_media text,
	PRIMARY KEY (mail_source_id),
	KEY mail_content_status (mail_content_status)
) ENGINE=MyISAM;


#
# Table structure for table `menus`
#

CREATE TABLE menus (
  menu_id int(10) unsigned NOT NULL auto_increment,
  menu_name varchar(100) NOT NULL default '',
  menu_location tinyint(3) unsigned NOT NULL default '0',
  menu_order tinyint(3) unsigned NOT NULL default '0',
  menu_class varchar(255) NOT NULL default '0',
  menu_pages text NOT NULL,
  menu_path varchar(100) NOT NULL default '',
  menu_layout varchar(100) NOT NULL default '',
  menu_parms text NOT NULL,
  PRIMARY KEY  (menu_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `news`
#

CREATE TABLE news (
  news_id int(10) unsigned NOT NULL auto_increment,
  news_title varchar(255) NOT NULL default '',
  news_sef varchar(200) NOT NULL default '',
  news_body longtext NOT NULL,
  news_extended longtext NOT NULL,
  news_meta_keywords  varchar(255) NOT NULL default '',
  news_meta_description text NOT NULL,
  news_datestamp int(10) unsigned NOT NULL default '0',
  news_author int(10) unsigned NOT NULL default '0',
  news_category tinyint(3) unsigned NOT NULL default '0',
  news_allow_comments tinyint(3) unsigned NOT NULL default '0',
  news_start int(10) unsigned NOT NULL default '0',
  news_end int(10) unsigned NOT NULL default '0',
  news_class varchar(255) NOT NULL default '0',
  news_render_type varchar(20) NOT NULL default '0',
  news_comment_total int(10) unsigned NOT NULL default '0',
  news_summary text NOT NULL,
  news_thumbnail text NOT NULL,
  news_sticky tinyint(3) unsigned NOT NULL default '0',
  news_template varchar(50) default NULL,
  PRIMARY KEY  (news_id),
  KEY news_category  (news_category),
  KEY news_start_end (news_start,news_end),
  KEY news_datestamp (news_datestamp),
  KEY news_sticky  (news_sticky),
  KEY news_render_type  (news_render_type),
  KEY news_class (news_class)
) ENGINE=MyISAM;


# --------------------------------------------------------

#
# Table structure for table `news_category`
#

CREATE TABLE news_category (
  category_id tinyint(3) unsigned NOT NULL auto_increment,
  category_name varchar(200) NOT NULL default '',
  category_sef varchar(200) NOT NULL default '',
  category_meta_description text NOT NULL,
  category_meta_keywords  varchar(255) NOT NULL default '',
  category_manager tinyint(3) unsigned NOT NULL default '254',
  category_icon varchar(250) NOT NULL default '',
  category_order tinyint(3) unsigned NOT NULL default '0',
  category_template varchar(50) default NULL,
  PRIMARY KEY  (category_id),
  KEY category_order (category_order)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `online`
#

CREATE TABLE online (
  online_timestamp int(10) unsigned NOT NULL default '0',
  online_flag tinyint(3) unsigned NOT NULL default '0',
  online_user_id varchar(100) NOT NULL default '',
  online_ip varchar(45) NOT NULL default '',
  online_location text NOT NULL,        
  online_pagecount tinyint(3) unsigned NOT NULL default '0',
  online_active int(10) unsigned NOT NULL default '0',
  online_agent varchar(255) NOT NULL default '',
  online_language varchar(2) NOT NULL default '',
  KEY online_ip (online_ip),
  KEY online_ip_user_id (online_ip, online_user_id),
  KEY online_timestamp (online_timestamp)
) ENGINE=InnoDB;
# --------------------------------------------------------

#
# Table structure for table `page`
#

CREATE TABLE page (
  page_id int(10) unsigned NOT NULL auto_increment,
  page_title varchar(250) NOT NULL default '',
  page_sef varchar (250) NOT NULL default '',
  page_chapter int(10) unsigned NOT NULL default '0',
  page_metakeys varchar (250) NOT NULL default '',
  page_metadscr mediumtext,
  page_text mediumtext,
  page_author int(10) unsigned NOT NULL default '0',
  page_datestamp int(10) unsigned NOT NULL default '0',
  page_rating_flag tinyint(1) unsigned NOT NULL default '0',
  page_comment_flag tinyint(1) unsigned NOT NULL default '0',
  page_password varchar(50) NOT NULL default '',
  page_class varchar(250) NOT NULL default '0',
  page_ip_restrict text,
  page_template varchar(50) NOT NULL default '',
  page_order int(4) unsigned NOT NULL default '9999',
  page_fields mediumtext,
  menu_name varchar(50) default '',
  menu_title varchar(250) NOT NULL default '',  
  menu_text mediumtext,
  menu_image varchar(250) NOT NULL default '',
  menu_icon varchar(250) NOT NULL default '',
  menu_template varchar(50) NOT NULL default '',
  menu_class varchar(250) NOT NULL default '0',
  menu_button_url varchar(250) NOT NULL default '', 
  menu_button_text varchar(250) NOT NULL default '',   
  
  PRIMARY KEY  (page_id)
) ENGINE=MyISAM;
# --------------------------------------------------------


#
# Table structure for table `page_chapters`
#

CREATE TABLE page_chapters (
  chapter_id int(4) unsigned NOT NULL auto_increment,
  chapter_parent int(4) unsigned NOT NULL default '0',
  chapter_name varchar(200) NOT NULL default '',
  chapter_sef varchar(200) NOT NULL default '',
  chapter_meta_description text NOT NULL,
  chapter_meta_keywords  varchar(255) NOT NULL default '',
  chapter_manager tinyint(3) unsigned NOT NULL default '254',
  chapter_icon varchar(250) NOT NULL default '',
  chapter_image varchar(250) NOT NULL default '',
  chapter_order int(6) unsigned NOT NULL default '0',
  chapter_template varchar(50) NOT NULL default '',
  chapter_visibility tinyint(3) unsigned NOT NULL default '0',
  chapter_fields mediumtext,
  PRIMARY KEY  (chapter_id),
  KEY chapter_order (chapter_order)
) ENGINE=MyISAM;
# --------------------------------------------------------



#
# Table structure for table `plugin`
#

CREATE TABLE plugin (
  plugin_id int(10) unsigned NOT NULL auto_increment,
  plugin_name varchar(100) NOT NULL default '',
  plugin_version varchar(10) NOT NULL default '',
  plugin_path varchar(100) NOT NULL default '',
  plugin_installflag tinyint(1) unsigned NOT NULL default '0',
  plugin_addons text NOT NULL,
  plugin_category varchar(100) NOT NULL default '',
  PRIMARY KEY  (plugin_id),
  UNIQUE KEY plugin_path (plugin_path)
) ENGINE=MyISAM;

# --------------------------------------------------------
#
# Table structure for table `rate`
#

CREATE TABLE rate (
  rate_id int(10) unsigned NOT NULL auto_increment,
  rate_table varchar(100) NOT NULL default '',
  rate_itemid int(10) unsigned NOT NULL default '0',
  rate_rating int(10) unsigned NOT NULL default '0',
  rate_votes int(10) unsigned NOT NULL default '0',
  rate_voters text NOT NULL,
  rate_up int(10) unsigned NOT NULL default '0',
  rate_down int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (rate_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `submitnews`
#

CREATE TABLE submitnews (
  submitnews_id int(10) unsigned NOT NULL auto_increment,
  submitnews_name varchar(100) NOT NULL default '',
  submitnews_email varchar(100) NOT NULL default '',
  submitnews_user int(10) unsigned NOT NULL default '0',
  submitnews_title varchar(200) NOT NULL default '',
  submitnews_category tinyint(3) unsigned NOT NULL default '0',
  submitnews_item text NOT NULL,
  submitnews_datestamp int(10) unsigned NOT NULL default '0',
  submitnews_ip varchar(45) NOT NULL default '',
  submitnews_auth tinyint(3) unsigned NOT NULL default '0',
  submitnews_file text NOT NULL,
  submitnews_keywords  varchar(255) NOT NULL default '',
  submitnews_description text,
  submitnews_summary text,
  submitnews_media text,
  PRIMARY KEY  (submitnews_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `tmp`
#

CREATE TABLE tmp (
  tmp_ip varchar(45) NOT NULL default '',
  tmp_time int(10) unsigned NOT NULL default '0',
  tmp_info text NOT NULL,
  KEY tmp_ip (tmp_ip),
  KEY tmp_time (tmp_time)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `upload`
#

CREATE TABLE upload (
  upload_id int(10) unsigned NOT NULL auto_increment,
  upload_poster varchar(100) NOT NULL default '',
  upload_email varchar(100) NOT NULL default '',
  upload_website varchar(100) NOT NULL default '',
  upload_datestamp int(10) unsigned NOT NULL default '0',
  upload_name varchar(100) NOT NULL default '',
  upload_version varchar(10) NOT NULL default '',
  upload_file varchar(180) NOT NULL default '',
  upload_ss varchar(100) NOT NULL default '',
  upload_description text NOT NULL,
  upload_demo varchar(100) NOT NULL default '',
  upload_filesize int(10) unsigned NOT NULL default '0',
  upload_active tinyint(3) unsigned NOT NULL default '0',
  upload_category tinyint(3) unsigned NOT NULL default '0',
  upload_owner varchar(50) NOT NULL default '',
  PRIMARY KEY  (upload_id),
  KEY upload_active (upload_active)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `user`
#

CREATE TABLE user (
  user_id int(10) unsigned NOT NULL auto_increment,
  user_name varchar(100) NOT NULL default '',
  user_loginname varchar(100) NOT NULL default '',
  user_customtitle varchar(100) NOT NULL default '',
  user_password varchar(255) NOT NULL default '',
  user_sess varchar(100) NOT NULL default '',
  user_email varchar(100) NOT NULL default '',
  user_signature text NOT NULL,
  user_image varchar(255) NOT NULL default '',
  user_hideemail tinyint(3) unsigned NOT NULL default '0',
  user_join int(10) unsigned NOT NULL default '0',
  user_lastvisit int(10) unsigned NOT NULL default '0',
  user_currentvisit int(10) unsigned NOT NULL default '0',
  user_lastpost int(10) unsigned NOT NULL default '0',
  user_chats int(10) unsigned NOT NULL default '0',
  user_comments int(10) unsigned NOT NULL default '0',
  user_ip varchar(45) NOT NULL default '',
  user_ban tinyint(3) unsigned NOT NULL default '0',
  user_prefs text NOT NULL,
  user_visits int(10) unsigned NOT NULL default '0',
  user_admin tinyint(3) unsigned NOT NULL default '0',
  user_login varchar(100) NOT NULL default '',
  user_class text NOT NULL,
  user_perms text NOT NULL,
  user_realm text NOT NULL,
  user_pwchange int(10) unsigned NOT NULL default '0',
  user_xup text,
  PRIMARY KEY  (user_id),
  UNIQUE KEY user_name (user_name),
  UNIQUE KEY user_loginname (user_loginname),
  KEY join_ban_index (user_join,user_ban)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `userclass_classes`
#
CREATE TABLE userclass_classes (
  userclass_id smallint(5) unsigned NOT NULL default '0',
  userclass_name varchar(100) NOT NULL default '',
  userclass_description varchar(250) NOT NULL default '',
  userclass_editclass smallint(5) unsigned NOT NULL default '0',
  userclass_parent smallint(5) unsigned NOT NULL default '0',
  userclass_accum varchar(250) NOT NULL default '',
  userclass_visibility smallint(5) signed NOT NULL default '0',
  userclass_type tinyint(1) unsigned NOT NULL default '0',
  userclass_icon varchar(250) NOT NULL default '',
  userclass_perms text NOT NULL,
  PRIMARY KEY  (userclass_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `e107_user_extended`
#

CREATE TABLE user_extended (
  user_extended_id int(10) unsigned NOT NULL default '0',
  user_hidden_fields text,
  PRIMARY KEY  (user_extended_id)
) ENGINE=MyISAM;
# --------------------------------------------------------


#
# Table structure for table `e107_user_extended_struct`
#

CREATE TABLE user_extended_struct (
  user_extended_struct_id int(10) unsigned NOT NULL auto_increment,
  user_extended_struct_name varchar(255) NOT NULL default '',
  user_extended_struct_text varchar(255) NOT NULL default '',
  user_extended_struct_type tinyint(3) unsigned NOT NULL default '0',
  user_extended_struct_parms varchar(255) NOT NULL default '',
  user_extended_struct_values text NOT NULL,
  user_extended_struct_default varchar(255) NOT NULL default '',
  user_extended_struct_read tinyint(3) unsigned NOT NULL default '0',
  user_extended_struct_write tinyint(3) unsigned NOT NULL default '0',
  user_extended_struct_required tinyint(3) unsigned NOT NULL default '0',
  user_extended_struct_signup tinyint(3) unsigned NOT NULL default '0',
  user_extended_struct_applicable tinyint(3) unsigned NOT NULL default '0',
  user_extended_struct_order int(10) unsigned NOT NULL default '0',
  user_extended_struct_parent int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (user_extended_struct_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

