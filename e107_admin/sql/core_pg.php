<?php
/*
#
# +---------------------------------------------------------------+
# |        e107 website system
# |        /files/core_bg.php
# |
# |        ©Steve Dunstan 2001-2002
# |        http://e107.org
# |        jalist@e107.org
# |
# |        Released under the terms and conditions of the
# |        GNU General Public License (http://gnu.org).
# +---------------------------------------------------------------+
*/
header("location:../index.php");
exit;
?>

CREATE SEQUENCE banner_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE chatbox_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE comments_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE content_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE download_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE download_category_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE forum_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE forum_t_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE headlines_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE links_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE menus_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE news_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE news_category_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE plugin_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE poll_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE SEQUENCE rate_seq start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;





CREATE TABLE banlist (
  banlist_ip varchar(100) NOT NULL,
  banlist_admin int4 DEFAULT '0' NOT NULL,
  banlist_reason text NOT NULL,
  CONSTRAINT banlist_pkey PRIMARY KEY (banlist_ip)
);

CREATE TABLE banner (
  banner_id int4 NOT NULL,
  banner_clientname varchar(100) DEFAULT '' NOT NULL,
  banner_clientlogin varchar(20) DEFAULT '' NOT NULL,
  banner_clientpassword varchar(50) DEFAULT '' NOT NULL,
  banner_image varchar(150) DEFAULT '' NOT NULL,
  banner_clickurl varchar(150) DEFAULT '' NOT NULL,
  banner_impurchased int4 DEFAULT '0' NOT NULL,
  banner_startdate int4 DEFAULT '0' NOT NULL,
  banner_enddate int4 DEFAULT '0' NOT NULL,
  banner_active int2 DEFAULT '0' NOT NULL,
  banner_clicks int4 DEFAULT '0' NOT NULL,
  banner_impressions int4 DEFAULT '0' NOT NULL,
  banner_ip text NOT NULL,
  banner_campaign varchar(150) DEFAULT '' NOT NULL,
  CONSTRAINT banner_pkey PRIMARY KEY (banner_id)
);

CREATE TABLE chatbox (
  cb_id int4 NOT NULL,
  cb_nick varchar(30) DEFAULT '' NOT NULL,
  cb_message text NOT NULL,
  cb_datestamp int4 DEFAULT '0' NOT NULL,
  cb_blocked int2 DEFAULT '0' NOT NULL,
  cb_ip varchar(15) DEFAULT '' NOT NULL,
  CONSTRAINT chatbox_pkey PRIMARY KEY (cb_id)
);

CREATE TABLE comments (
  comment_id int4 DEFAULT '0' NOT NULL,
  comment_pid int4 DEFAULT '0' NOT NULL,
  comment_item_id int4 DEFAULT '0' NOT NULL,
  comment_subject varchar(100) DEFAULT '' NOT NULL,
  comment_author varchar(100) DEFAULT '' NOT NULL,
  comment_author_email varchar(200) DEFAULT '' NOT NULL,
  comment_datestamp int4 DEFAULT '0' NOT NULL,
  comment_comment text NOT NULL,
  comment_blocked int2 DEFAULT '0' NOT NULL,
  comment_ip varchar(20) DEFAULT '' NOT NULL,
  comment_type varchar(10) DEFAULT '' NOT NULL,
  CONSTRAINT comments_pkey PRIMARY KEY (comment_id)
);

CREATE TABLE content (
  content_id int4 DEFAULT '0' NOT NULL,
  content_heading text NOT NULL,
  content_subheading text NOT NULL,
  content_content text NOT NULL,
  content_parent int4 DEFAULT '0' NOT NULL,
  content_datestamp int4 DEFAULT '0' NOT NULL,
  content_author varchar(200) DEFAULT '' NOT NULL,
  content_comment int2 DEFAULT '0' NOT NULL,
  content_summary text NOT NULL,
  content_type int2 DEFAULT '0' NOT NULL,
  content_review_score int2 DEFAULT '0' NOT NULL,
  content_pe_icon int2 DEFAULT '0' NOT NULL,
  content_class int2 DEFAULT '0' NOT NULL,
  CONSTRAINT content_pkey PRIMARY KEY (content_id)
);

CREATE TABLE core (
  e107_name varchar(20) DEFAULT '' NOT NULL,
  e107_value text NOT NULL,
  CONSTRAINT core_pkey PRIMARY KEY (e107_name)
);

CREATE TABLE download (
  download_id int4 DEFAULT '0' NOT NULL,
  download_name varchar(100) DEFAULT '' UNIQUE NOT NULL,
  download_url varchar(150) DEFAULT '' NOT NULL,
  download_author varchar(100) DEFAULT '' NOT NULL,
  download_author_email varchar(200) DEFAULT '' NOT NULL,
  download_author_website varchar(200) DEFAULT '' NOT NULL,
  download_description text NOT NULL,
  download_filesize varchar(20) DEFAULT '' NOT NULL,
  download_requested int4 DEFAULT '0' NOT NULL,
  download_category int4 DEFAULT '0' NOT NULL,
  download_active int2 DEFAULT '0' NOT NULL,
  download_datestamp int4 DEFAULT '0' NOT NULL,
  download_thumb varchar(150) DEFAULT '' NOT NULL,
  download_image varchar(150) DEFAULT '' NOT NULL,
  download_comment int2 DEFAULT '0' NOT NULL,
  CONSTRAINT download_pkey PRIMARY KEY (download_id)
);

CREATE TABLE download_category (
  download_category_id int4 DEFAULT '0' NOT NULL,
  download_category_name varchar(100) DEFAULT '' NOT NULL,
  download_category_description text NOT NULL,
  download_category_icon varchar(100) DEFAULT '' NOT NULL,
  download_category_parent int4 DEFAULT '0' NOT NULL,
  download_category_class varchar(100) DEFAULT '' NOT NULL,
  CONSTRAINT download_category_pkey PRIMARY KEY (download_category_id)
);

CREATE TABLE flood (
  flood_url text NOT NULL,
  flood_time int4 DEFAULT '0' NOT NULL,
);

CREATE TABLE forum (
  forum_id int4 DEFAULT '0' NOT NULL,
  forum_name varchar(250) DEFAULT '' NOT NULL,
  forum_description text NOT NULL,
  forum_parent int4 DEFAULT '0' NOT NULL,
  forum_datestamp int4 DEFAULT '0' NOT NULL,
  forum_moderators text NOT NULL,
  forum_threads int4 DEFAULT '0' NOT NULL,
  forum_replies int4 DEFAULT '0' NOT NULL,
  forum_lastpost varchar(200) DEFAULT '' NOT NULL,
  forum_class varchar(100) DEFAULT '' NOT NULL,
  forum_order int4 DEFAULT '0' NOT NULL,
  CONSTRAINT forum_pkey PRIMARY KEY (forum_id)
);

CREATE TABLE forum_t (
  thread_id int4 DEFAULT '0' NOT NULL,
  thread_name varchar(250) DEFAULT '' NOT NULL,
  thread_thread text NOT NULL,
  thread_forum_id int4 DEFAULT '0' NOT NULL,
  thread_datestamp int4 DEFAULT '0' NOT NULL,
  thread_parent int4 DEFAULT '0' NOT NULL,
  thread_user varchar(250) DEFAULT '' NOT NULL,
  thread_views int4 DEFAULT '0' NOT NULL,
  thread_active int2 DEFAULT '0' NOT NULL,
  thread_lastpost int4 DEFAULT '0' NOT NULL,
  thread_s int2 DEFAULT '0' NOT NULL,
  CONSTRAINT forum_t_pkey PRIMARY KEY (thread_id)
);

CREATE TABLE headlines (
  headline_id int4 DEFAULT '0' NOT NULL,
  headline_url varchar(150) DEFAULT '' NOT NULL,
  headline_data text NOT NULL,
  headline_timestamp int4 DEFAULT '0' NOT NULL,
  headline_description text NOT NULL,
  headline_image varchar(100) DEFAULT '' NOT NULL,
  headline_active int2 DEFAULT '0' NOT NULL,
  CONSTRAINT headlines_pkey PRIMARY KEY (headline_id)
);

CREATE TABLE links (
  link_id int4 DEFAULT '0' NOT NULL,
  link_name varchar(100) DEFAULT '' NOT NULL,
  link_url varchar(200) DEFAULT '' NOT NULL,
  link_description text NOT NULL,
  link_button varchar(100) DEFAULT '' NOT NULL,
  link_category int2 DEFAULT '0' NOT NULL,
  link_order int4 DEFAULT '0' NOT NULL,
  link_refer int4 DEFAULT '0' NOT NULL,
  link_open int2 DEFAULT '0' NOT NULL,
  link_class int2 DEFAULT '0' NOT NULL,
  CONSTRAINT links_pkey PRIMARY KEY (link_id)
);

CREATE TABLE menus (
  menu_id int4 DEFAULT '0' NOT NULL,
  menu_name varchar(100) DEFAULT '' NOT NULL,
  menu_location int2 DEFAULT '0' NOT NULL,
  menu_order int2 DEFAULT '0' NOT NULL,
  menu_class int2 DEFAULT '0' NOT NULL,
  menu_pages text NOT NULL,
  CONSTRAINT menus_pkey PRIMARY KEY (menu_id)
);

CREATE TABLE news (
  news_id int4 DEFAULT '0' NOT NULL,
  news_title varchar(200) DEFAULT '' NOT NULL,
  news_body text NOT NULL,
  news_extended text NOT NULL,
  news_datestamp int4 DEFAULT '0' NOT NULL,
  news_author int4 DEFAULT '0' NOT NULL,
  news_category int2 DEFAULT '0' NOT NULL,
  news_allow_comments int2 DEFAULT '0' NOT NULL,
  news_start int4 DEFAULT '0' NOT NULL,
  news_end int4 DEFAULT '0' NOT NULL,
  news_class int2 DEFAULT '0' NOT NULL,
  news_render_type int2 DEFAULT '0' NOT NULL,
  news_comment_total int4 DEFAULT '0' NOT NULL,
  CONSTRAINT news_pkey PRIMARY KEY (news_id)
);

CREATE TABLE news_category (
  category_id int4 DEFAULT '0' NOT NULL,
  category_name varchar(200) DEFAULT '' NOT NULL,
  category_icon varchar(250) DEFAULT '' NOT NULL,
  CONSTRAINT news_category_pkey PRIMARY KEY (category_id)
);

CREATE TABLE online (
  online_timestamp int4 DEFAULT '0' NOT NULL,
  online_flag int2 DEFAULT '0' NOT NULL,
  online_user_id varchar(100) DEFAULT '' NOT NULL,
  online_ip varchar(15) DEFAULT '' NOT NULL,
  online_location varchar(100) DEFAULT '' NOT NULL,
  online_pagecount tinyint(3) int2 DEFAULT '0' NOT NULL,
);

CREATE TABLE plugin (
  plugin_id int4 DEFAULT '0' NOT NULL,
  plugin_name varchar(100) DEFAULT '' NOT NULL,
  plugin_version varchar(10) DEFAULT '' NOT NULL,
  plugin_path varchar(100) DEFAULT '' NOT NULL,
  plugin_installflag int2 DEFAULT '0' NOT NULL,
  CONSTRAINT plugin_pkey PRIMARY KEY (plugin_id)
);

CREATE TABLE poll (
  poll_id int4 DEFAULT '0' NOT NULL,
  poll_datestamp int4 DEFAULT '0' NOT NULL,
  poll_end_datestamp int4 DEFAULT '0' NOT NULL,
  poll_admin_id int4 DEFAULT '0' NOT NULL,
  poll_title varchar(250) DEFAULT '' NOT NULL,
  poll_option_1 varchar(250) DEFAULT '' NOT NULL,
  poll_option_2 varchar(250) DEFAULT '' NOT NULL,
  poll_option_3 varchar(250) DEFAULT '' NOT NULL,
  poll_option_4 varchar(250) DEFAULT '' NOT NULL,
  poll_option_5 varchar(250) DEFAULT '' NOT NULL,
  poll_option_6 varchar(250) DEFAULT '' NOT NULL,
  poll_option_7 varchar(250) DEFAULT '' NOT NULL,
  poll_option_8 varchar(250) DEFAULT '' NOT NULL,
  poll_option_9 varchar(250) DEFAULT '' NOT NULL,
  poll_option_10 varchar(250) DEFAULT '' NOT NULL,
  poll_votes_1 int4 DEFAULT '0' NOT NULL,
  poll_votes_2 int4 DEFAULT '0' NOT NULL,
  poll_votes_3 int4 DEFAULT '0' NOT NULL,
  poll_votes_4 int4 DEFAULT '0' NOT NULL,
  poll_votes_5 int4 DEFAULT '0' NOT NULL,
  poll_votes_6 int4 DEFAULT '0' NOT NULL,
  poll_votes_7 int4 DEFAULT '0' NOT NULL,
  poll_votes_8 int4 DEFAULT '0' NOT NULL,
  poll_votes_9 int4 DEFAULT '0' NOT NULL,
  poll_votes_10 int4 DEFAULT '0' NOT NULL,
  poll_ip text NOT NULL,
  poll_active int2 DEFAULT '0' NOT NULL,
  poll_comment int2 DEFAULT '1' NOT NULL,
  CONSTRAINT poll_pkey PRIMARY KEY (poll_id)
);

CREATE TABLE rate (
  rate_id int4 DEFAULT '0' NOT NULL,
  rate_table varchar(100) DEFAULT '' NOT NULL,
  rate_itemid int4 DEFAULT '0' NOT NULL,
  rate_rating int4 DEFAULT '0' NOT NULL,
  rate_votes int4 DEFAULT '0' NOT NULL,
  rate_voters text NOT NULL,
  CONSTRAINT rate_pkey PRIMARY KEY (rate_id)
);

