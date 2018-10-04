#
# Table structure for table `download`
#
CREATE TABLE download (
  download_id int(10) unsigned NOT NULL auto_increment,
  download_name varchar(255) NOT NULL default '',
  download_url varchar(255) NOT NULL default '',
  download_sef varchar(255) NOT NULL default '',
  download_author varchar(100) NOT NULL default '',
  download_author_email varchar(200) NOT NULL default '',
  download_author_website varchar(200) NOT NULL default '',
  download_description text NOT NULL,
  download_keywords text NOT NULL,
  download_filesize varchar(20) NOT NULL default '',
  download_requested int(10) unsigned NOT NULL default '0',
  download_category int(10) unsigned NOT NULL default '0',
  download_active tinyint(3) unsigned NOT NULL default '0',
  download_datestamp int(10) unsigned NOT NULL default '0',
  download_thumb text NOT NULL,
  download_image text NOT NULL,
  download_comment tinyint(3) unsigned NOT NULL default '0',
  download_class varchar(255) NOT NULL default '0',
  download_mirror text NOT NULL,
  download_mirror_type tinyint(1) unsigned NOT NULL default '0',
  download_visible varchar(255) NOT NULL default '0',
  PRIMARY KEY  (download_id),
  UNIQUE KEY download_name (download_name),
  KEY download_category (download_category)
) ENGINE=MyISAM;
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
  download_category_class varchar(255) NOT NULL default '0',
  download_category_order int(10) unsigned NOT NULL default '0',
  download_category_sef varchar(255) NOT NULL default '',
  PRIMARY KEY  (download_category_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `download_mirror`
#
CREATE TABLE download_mirror (
  mirror_id int(10) unsigned NOT NULL auto_increment,
  mirror_name varchar(200) NOT NULL default '',
  mirror_url varchar(255) NOT NULL default '',
  mirror_image varchar(200) NOT NULL default '',
  mirror_location varchar(100) NOT NULL default '',
  mirror_description text NOT NULL,
  mirror_count int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (mirror_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `download_requests`
#
CREATE TABLE download_requests (
  download_request_id int(10) unsigned NOT NULL auto_increment,
  download_request_userid int(10) unsigned NOT NULL default '0',
  download_request_ip varchar(45) NOT NULL default '',
  download_request_download_id int(10) unsigned NOT NULL default '0',
  download_request_datestamp int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (download_request_id),
  KEY download_request_userid (download_request_userid),
  KEY download_request_download_id (download_request_download_id),
  KEY download_request_datestamp (download_request_datestamp)
) ENGINE=MyISAM;
# --------------------------------------------------------