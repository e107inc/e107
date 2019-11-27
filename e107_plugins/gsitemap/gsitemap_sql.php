CREATE TABLE gsitemap (
	gsitemap_id int(11) unsigned NOT NULL auto_increment,
	gsitemap_name varchar(200) NOT NULL default '',
	gsitemap_url varchar(200) NOT NULL default '',
	gsitemap_lastmod varchar(15) NOT NULL default '',
	gsitemap_freq varchar(10) NOT NULL default '',
	gsitemap_priority char(3) NOT NULL default '',
	gsitemap_cat varchar(100) NOT NULL default '',
	gsitemap_order int(3) NOT NULL default '0',
	gsitemap_img varchar(50) NOT NULL default '',
	gsitemap_active int(3) NOT NULL default '0',
	PRIMARY KEY  (gsitemap_id)
) ENGINE=MyISAM;