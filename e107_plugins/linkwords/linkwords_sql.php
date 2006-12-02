CREATE TABLE linkwords (
linkword_id INT(10) UNSIGNED NOT NULL auto_increment,
linkword_active tinyint(1) unsigned NOT NULL default '0',
linkword_word varchar(100) NOT NULL default '',
linkword_link varchar(150) NOT NULL default '',
PRIMARY KEY  (linkword_id)
) TYPE=MyISAM AUTO_INCREMENT=1;