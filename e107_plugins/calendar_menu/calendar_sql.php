CREATE TABLE event (
	event_id int(11) unsigned NOT NULL auto_increment,
	event_start int(10) NOT NULL default '0',
	event_end int(10) NOT NULL default '0',
	event_allday tinyint(1) unsigned NOT NULL default '0',
	event_recurring tinyint(1) unsigned NOT NULL default '0',
	event_datestamp int(10) unsigned NOT NULL default '0',
	event_title varchar(200) NOT NULL default '',
	event_location text NOT NULL,
	event_details text NOT NULL,
	event_author varchar(100) NOT NULL default '',
	event_contact varchar(200) NOT NULL default '',
	event_category smallint(5) unsigned NOT NULL default '0',
	event_thread varchar(100) NOT NULL default '',
	event_rec_m tinyint(2) unsigned NOT NULL default '0',
	event_rec_y tinyint(2) unsigned NOT NULL default '0',
	PRIMARY KEY  (event_id)
	) TYPE=MyISAM;,
	CREATE TABLE event_cat (
	event_cat_id smallint(5) unsigned NOT NULL auto_increment,
	event_cat_name varchar(100) NOT NULL default '',
	event_cat_icon varchar(100) NOT NULL default '',
	event_cat_class int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (event_cat_id)
	) TYPE=MyISAM;
