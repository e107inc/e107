CREATE TABLE pm_messages (
  pm_id int(10) unsigned NOT NULL auto_increment,
  pm_from_user varchar(100) NOT NULL default '',
  pm_to_user varchar(100) NOT NULL default '',
  pm_sent_datestamp int(10) unsigned NOT NULL default '0',
  pm_rcv_datestamp int(10) unsigned NOT NULL default '0',
  pm_subject text NOT NULL,
  pm_message text NOT NULL,
  PRIMARY KEY  (pm_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE pm_blocks (
  block_id int(10) unsigned NOT NULL auto_increment,
  block_from varchar(100) NOT NULL default '',
  block_to varchar(100) NOT NULL default '',
  block_datestamp int(10) unsigned NOT NULL default '0',
  block_count int(10) NOT NULL default '0',
  PRIMARY KEY  (block_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;