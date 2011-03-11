CREATE TABLE tagwords (
	`tag_id` int(10) unsigned NOT NULL auto_increment,
	`tag_type` varchar(100) NOT NULL default '',
	`tag_itemid` int(10) unsigned NOT NULL default '0',
	`tag_word` varchar(255) NOT NULL default '',
	PRIMARY KEY (`tag_id`),
	KEY `tag_word` (`tag_word`)
) ENGINE=MyISAM AUTO_INCREMENT=1;