CREATE TABLE newsfeed (
  newsfeed_id int(10) unsigned NOT NULL auto_increment,
  newsfeed_name varchar(150) NOT NULL default '',
  newsfeed_url varchar(250) NOT NULL default '',
  newsfeed_data longtext NOT NULL,
  newsfeed_timestamp int(10) unsigned NOT NULL default '0',
  newsfeed_description text NOT NULL,
  newsfeed_image varchar(100) NOT NULL default '',
  newsfeed_active tinyint(1) unsigned NOT NULL default '0',
  newsfeed_updateint int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (newsfeed_id)
) ENGINE=MyISAM;
