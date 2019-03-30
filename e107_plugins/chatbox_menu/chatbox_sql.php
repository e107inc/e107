CREATE TABLE chatbox (
	cb_id int(10) unsigned NOT NULL auto_increment,
	cb_nick varchar(30) NOT NULL default '',
	cb_message text NOT NULL,
	cb_datestamp int(10) unsigned NOT NULL default '0',
	cb_blocked tinyint(3) unsigned NOT NULL default '0',
	cb_ip varchar(45) NOT NULL default '',
	PRIMARY KEY  (cb_id)
	) ENGINE=MyISAM;