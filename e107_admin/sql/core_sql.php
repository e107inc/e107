<?php
header("location:../index.php");
exit;
?>
#
# +---------------------------------------------------------------+
# |	e107 website system
# |	/files/sql.php
# |
# |	©Steve Dunstan 2001-2002
# |	http://e107.org
# |	jalist@e107.org
# |
# |	Released under the terms and conditions of the
# |	GNU General Public License (http://gnu.org).
# +---------------------------------------------------------------+
# Database : <variable>
# --------------------------------------------------------

#
# Table structure for table `banlist`
#

CREATE TABLE banlist (
  banlist_ip varchar(100) NOT NULL default '',
  banlist_admin smallint(5) unsigned NOT NULL default '0',
  banlist_reason tinytext NOT NULL,
  PRIMARY KEY  (banlist_ip)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `banner`
#

CREATE TABLE banner (
  banner_id int(10) unsigned NOT NULL auto_increment,
  banner_clientname varchar(100) NOT NULL default '',
  banner_clientlogin varchar(20) NOT NULL default '',
  banner_clientpassword varchar(50) NOT NULL default '',
  banner_image varchar(150) NOT NULL default '',
  banner_clickurl varchar(150) NOT NULL default '',
  banner_impurchased int(10) unsigned NOT NULL default '0',
  banner_startdate int(10) unsigned NOT NULL default '0',
  banner_enddate int(10) unsigned NOT NULL default '0',
  banner_active tinyint(1) unsigned NOT NULL default '0',
  banner_clicks int(10) unsigned NOT NULL default '0',
  banner_impressions int(10) unsigned NOT NULL default '0',
  banner_ip text NOT NULL,
  banner_campaign varchar(150) NOT NULL default '',
  PRIMARY KEY  (banner_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `cache`
#

CREATE TABLE cache (
  cache_url varchar(200) NOT NULL default '',
  cache_datestamp int(10) unsigned NOT NULL default '0',
  cache_data longtext NOT NULL
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `chatbox`
#

CREATE TABLE chatbox (
  cb_id int(10) unsigned NOT NULL auto_increment,
  cb_nick varchar(30) NOT NULL default '',
  cb_message text NOT NULL,
  cb_datestamp int(10) unsigned NOT NULL default '0',
  cb_blocked tinyint(3) unsigned NOT NULL default '0',
  cb_ip varchar(15) NOT NULL default '',
  PRIMARY KEY  (cb_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `comments`
#

CREATE TABLE comments (
  comment_id int(10) unsigned NOT NULL auto_increment,
  comment_pid int(10) unsigned NOT NULL default '0',
  comment_item_id int(10) unsigned NOT NULL default '0',
  comment_subject varchar(100) NOT NULL default '',
  comment_author varchar(100) NOT NULL default '',
  comment_author_email varchar(200) NOT NULL default '',
  comment_datestamp int(10) unsigned NOT NULL default '0',
  comment_comment text NOT NULL,
  comment_blocked tinyint(3) unsigned NOT NULL default '0',
  comment_ip varchar(20) NOT NULL default '',
  comment_type varchar(10) NOT NULL default '0',
  PRIMARY KEY  (comment_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `content`
#

CREATE TABLE content (
  content_id int(10) unsigned NOT NULL auto_increment,
  content_heading tinytext NOT NULL,
  content_subheading tinytext NOT NULL,
  content_content text NOT NULL,
  content_parent int(10) unsigned NOT NULL default '0',
  content_datestamp int(10) unsigned NOT NULL default '0',
  content_author varchar(200) NOT NULL default '',
  content_comment tinyint(3) unsigned NOT NULL default '0',
  content_summary text NOT NULL,
  content_type tinyint(3) unsigned NOT NULL default '0',
  content_review_score tinyint(3) unsigned NOT NULL default '0',
  content_pe_icon tinyint(1) unsigned NOT NULL default '0',
  content_class tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (content_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `core`
#

CREATE TABLE core (
  e107_name varchar(20) NOT NULL default '',
  e107_value text NOT NULL,
  PRIMARY KEY  (e107_name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `download`
#

CREATE TABLE download (
  download_id int(10) unsigned NOT NULL auto_increment,
  download_name varchar(100) NOT NULL default '',
  download_url varchar(150) NOT NULL default '',
  download_author varchar(100) NOT NULL default '',
  download_author_email varchar(200) NOT NULL default '',
  download_author_website varchar(200) NOT NULL default '',
  download_description text NOT NULL,
  download_filesize varchar(20) NOT NULL default '',
  download_requested int(10) unsigned NOT NULL default '0',
  download_category int(10) unsigned NOT NULL default '0',
  download_active tinyint(3) unsigned NOT NULL default '0',
  download_datestamp int(10) unsigned NOT NULL default '0',
  download_thumb varchar(150) NOT NULL default '',
  download_image varchar(150) NOT NULL default '',
  download_comment tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (download_id),
  UNIQUE KEY download_name (download_name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `download_category`
#

CREATE TABLE download_category (
  download_category_id int(10) unsigned NOT NULL auto_increment,
  download_category_name varchar(100) NOT NULL default '',
  download_category_description text NOT NULL,
  download_category_icon varchar(100) NOT NULL default '',
  download_category_parent int(10) unsigned NOT NULL default '0',
  download_category_class varchar(100) NOT NULL default '',
  PRIMARY KEY  (download_category_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `flood`
#

CREATE TABLE flood (
  flood_url text NOT NULL,
  flood_time int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `forum`
#

CREATE TABLE forum (
  forum_id int(10) unsigned NOT NULL auto_increment,
  forum_name varchar(250) NOT NULL default '',
  forum_description text NOT NULL,
  forum_parent int(10) unsigned NOT NULL default '0',
  forum_datestamp int(10) unsigned NOT NULL default '0',
  forum_moderators text NOT NULL,
  forum_threads int(10) unsigned NOT NULL default '0',
  forum_replies int(10) unsigned NOT NULL default '0',
  forum_lastpost varchar(200) NOT NULL default '',
  forum_class varchar(100) NOT NULL default '',
  forum_order int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (forum_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `forum_t`
#

CREATE TABLE forum_t (
  thread_id int(10) unsigned NOT NULL auto_increment,
  thread_name varchar(250) NOT NULL default '',
  thread_thread text NOT NULL,
  thread_forum_id int(10) unsigned NOT NULL default '0',
  thread_datestamp int(10) unsigned NOT NULL default '0',
  thread_parent int(10) unsigned NOT NULL default '0',
  thread_user varchar(250) NOT NULL default '',
  thread_views int(10) unsigned NOT NULL default '0',
  thread_active tinyint(3) unsigned NOT NULL default '0',
  thread_lastpost int(10) unsigned NOT NULL default '0',
  thread_s tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (thread_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `headlines`
#

CREATE TABLE headlines (
  headline_id int(10) unsigned NOT NULL auto_increment,
  headline_url varchar(150) NOT NULL default '',
  headline_data text NOT NULL,
  headline_timestamp int(10) unsigned NOT NULL default '0',
  headline_description text NOT NULL,
  headline_image varchar(100) NOT NULL default '',
  headline_active tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (headline_id)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `link_category`
#

CREATE TABLE link_category (
  link_category_id int(10) unsigned NOT NULL auto_increment,
  link_category_name varchar(100) NOT NULL default '',
  link_category_description varchar(250) NOT NULL default '',
  link_category_icon varchar(100) NOT NULL default '',
  PRIMARY KEY  (link_category_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `links`
#

CREATE TABLE links (
  link_id int(10) unsigned NOT NULL auto_increment,
  link_name varchar(100) NOT NULL default '',
  link_url varchar(200) NOT NULL default '',
  link_description text NOT NULL,
  link_button varchar(100) NOT NULL default '',
  link_category tinyint(3) unsigned NOT NULL default '0',
  link_order int(10) unsigned NOT NULL default '0',
  link_refer int(10) unsigned NOT NULL default '0',
  link_open tinyint(1) unsigned NOT NULL default '0',
  link_class tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (link_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `menus`
#

CREATE TABLE menus (
  menu_id int(10) unsigned NOT NULL auto_increment,
  menu_name varchar(100) NOT NULL default '',
  menu_location tinyint(3) unsigned NOT NULL default '0',
  menu_order tinyint(3) unsigned NOT NULL default '0',
  menu_class tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (menu_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `news`
#

CREATE TABLE news (
  news_id int(10) unsigned NOT NULL auto_increment,
  news_title varchar(200) NOT NULL default '',
  news_body text NOT NULL,
  news_extended text NOT NULL,
  news_datestamp int(10) unsigned NOT NULL default '0',
  news_author int(10) unsigned NOT NULL default '0',
  news_category tinyint(3) unsigned NOT NULL default '0',
  news_allow_comments tinyint(3) unsigned NOT NULL default '0',
  news_start int(10) unsigned NOT NULL default '0',
  news_end int(10) unsigned NOT NULL default '0',
  news_class tinyint(3) unsigned NOT NULL default '0',
  news_render_type tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (news_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `news_category`
#

CREATE TABLE news_category (
  category_id int(10) unsigned NOT NULL auto_increment,
  category_name varchar(200) NOT NULL default '',
  category_icon varchar(250) NOT NULL default '',
  PRIMARY KEY  (category_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `online`
#

CREATE TABLE online (
  online_timestamp int(10) unsigned NOT NULL default '0',
  online_flag tinyint(3) unsigned NOT NULL default '0',
  online_user_id varchar(100) NOT NULL default '',
  online_ip varchar(15) NOT NULL default '',
  online_location varchar(100) NOT NULL default '',
  online_pagecount tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;
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
  PRIMARY KEY  (plugin_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `poll`
#

CREATE TABLE poll (
  poll_id int(10) unsigned NOT NULL auto_increment,
  poll_datestamp int(10) unsigned NOT NULL default '0',
  poll_end_datestamp int(10) unsigned NOT NULL default '0',
  poll_admin_id int(10) unsigned NOT NULL default '0',
  poll_title varchar(250) NOT NULL default '',
  poll_option_1 varchar(250) NOT NULL default '',
  poll_option_2 varchar(250) NOT NULL default '',
  poll_option_3 varchar(250) NOT NULL default '',
  poll_option_4 varchar(250) NOT NULL default '',
  poll_option_5 varchar(250) NOT NULL default '',
  poll_option_6 varchar(250) NOT NULL default '',
  poll_option_7 varchar(250) NOT NULL default '',
  poll_option_8 varchar(250) NOT NULL default '',
  poll_option_9 varchar(250) NOT NULL default '',
  poll_option_10 varchar(250) NOT NULL default '',
  poll_votes_1 int(10) unsigned NOT NULL default '0',
  poll_votes_2 int(10) unsigned NOT NULL default '0',
  poll_votes_3 int(10) unsigned NOT NULL default '0',
  poll_votes_4 int(10) unsigned NOT NULL default '0',
  poll_votes_5 int(10) unsigned NOT NULL default '0',
  poll_votes_6 int(10) unsigned NOT NULL default '0',
  poll_votes_7 int(10) unsigned NOT NULL default '0',
  poll_votes_8 int(10) unsigned NOT NULL default '0',
  poll_votes_9 int(10) unsigned NOT NULL default '0',
  poll_votes_10 int(10) unsigned NOT NULL default '0',
  poll_ip text NOT NULL,
  poll_active tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (poll_id)
) TYPE=MyISAM;
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
  PRIMARY KEY  (rate_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `rbinary`
#

CREATE TABLE rbinary (
  binary_id int(10) unsigned NOT NULL auto_increment,
  binary_name varchar(200) NOT NULL default '',
  binary_filetype varchar(100) NOT NULL default '',
  binary_data longblob NOT NULL,
  PRIMARY KEY  (binary_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `session`
#

CREATE TABLE session (
  session_id varchar(32) NOT NULL default '',
  session_expire int(10) unsigned NOT NULL default '0',
  session_datestamp int(10) unsigned NOT NULL default '0',
  session_ip varchar(200) NOT NULL default '',
  session_data text NOT NULL
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `stat_counter`
#

CREATE TABLE stat_counter (
  counter_date date NOT NULL default '0000-00-00',
  counter_url varchar(100) NOT NULL default '',
  counter_unique int(10) unsigned NOT NULL default '0',
  counter_total int(10) unsigned NOT NULL default '0',
  counter_ip text NOT NULL
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `stat_info`
#

CREATE TABLE stat_info (
  info_name text NOT NULL,
  info_count int(10) unsigned NOT NULL default '0',
  info_type tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `stat_last`
#

CREATE TABLE stat_last (
  stat_last_date int(11) unsigned NOT NULL default '0',
  stat_last_info text NOT NULL
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `submitnews`
#

CREATE TABLE submitnews (
  submitnews_id int(10) unsigned NOT NULL auto_increment,
  submitnews_name varchar(100) NOT NULL default '',
  submitnews_email varchar(100) NOT NULL default '',
  submitnews_title varchar(200) NOT NULL default '',
  submitnews_category tinyint(3) unsigned NOT NULL default '0',
  submitnews_item text NOT NULL,
  submitnews_datestamp int(10) unsigned NOT NULL default '0',
  submitnews_ip varchar(15) NOT NULL default '',
  submitnews_auth tinyint(3) unsigned NOT NULL default '0',
  submitnews_file varchar(100) NOT NULL default '',
  PRIMARY KEY  (submitnews_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `tmp`
#

CREATE TABLE tmp (
  tmp_ip varchar(20) NOT NULL default '',
  tmp_time int(10) unsigned NOT NULL default '0',
  tmp_info text NOT NULL
) TYPE=MyISAM;
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
  upload_file varchar(100) NOT NULL default '',
  upload_ss varchar(100) NOT NULL default '',
  upload_description text NOT NULL,
  upload_demo varchar(100) NOT NULL default '',
  upload_filesize int(10) unsigned NOT NULL default '0',
  upload_active tinyint(3) unsigned NOT NULL default '0',
  upload_category tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (upload_id)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `user`
#

CREATE TABLE user (
  user_id int(10) unsigned NOT NULL auto_increment,
  user_name varchar(100) NOT NULL default '',
  user_customtitle varchar(100) NOT NULL default '',
  user_password varchar(32) NOT NULL default '',
  user_sess varchar(32) NOT NULL default '',
  user_email varchar(100) NOT NULL default '',
  user_homepage varchar(150) NOT NULL default '',
  user_icq varchar(10) NOT NULL default '',
  user_aim varchar(100) NOT NULL default '',
  user_msn varchar(100) NOT NULL default '',
  user_location varchar(150) NOT NULL default '',
  user_birthday date NOT NULL default '0000-00-00',
  user_signature text NOT NULL,
  user_image varchar(100) NOT NULL default '',
  user_timezone char(3) NOT NULL default '',
  user_hideemail tinyint(3) unsigned NOT NULL default '0',
  user_join int(10) unsigned NOT NULL default '0',
  user_lastvisit int(10) unsigned NOT NULL default '0',
  user_currentvisit int(10) unsigned NOT NULL default '0',
  user_lastpost int(10) unsigned NOT NULL default '0',
  user_chats int(10) unsigned NOT NULL default '0',
  user_comments int(10) unsigned NOT NULL default '0',
  user_forums int(10) unsigned NOT NULL default '0',
  user_ip varchar(20) NOT NULL default '',
  user_ban tinyint(3) unsigned NOT NULL default '0',
  user_prefs text NOT NULL,
  user_new text NOT NULL,
  user_viewed text NOT NULL,
  user_visits int(10) unsigned NOT NULL default '0',
  user_admin tinyint(3) unsigned NOT NULL default '0',
  user_login varchar(100) NOT NULL default '',
  user_class text NOT NULL,
  user_perms text NOT NULL,
  user_realm text NOT NULL,
  user_pwchange int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (user_id),
  UNIQUE KEY user_name (user_name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `userclass_classes`
#

CREATE TABLE userclass_classes (
  userclass_id tinyint(3) unsigned NOT NULL default '0',
  userclass_name varchar(100) NOT NULL default '',
  userclass_description varchar(250) NOT NULL default '',
  userclass_editclass tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (userclass_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `wmessage`
#

CREATE TABLE wmessage (
  wm_id tinyint(3) unsigned NOT NULL default '0',
  wm_text text NOT NULL,
  wm_active tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `parser`
#

CREATE TABLE parser (
  parser_id int(10) unsigned NOT NULL auto_increment,
  parser_pluginname varchar(100) NOT NULL default '',
  parser_regexp varchar(100) NOT NULL default '',
  PRIMARY KEY  (parser_id),
  UNIQUE KEY parser_regexp (parser_regexp)
) TYPE=MyISAM;
# --------------------------------------------------------
