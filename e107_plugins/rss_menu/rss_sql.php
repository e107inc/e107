CREATE TABLE rss (
	rss_id int(10) unsigned NOT NULL auto_increment,
	rss_name varchar(255) NOT NULL default '',
	rss_url text NOT NULL,
	rss_topicid varchar(255) NOT NULL default '',
	rss_path varchar(255) NOT NULL default '',
	rss_text longtext NOT NULL,
	rss_datestamp int(10) unsigned NOT NULL default '0',
	rss_class tinyint(1) unsigned NOT NULL default '0',
	rss_limit tinyint(3) unsigned NOT NULL default '0',
	PRIMARY KEY  (rss_id),
	KEY rss_name (rss_name)
) ENGINE=MyISAM;