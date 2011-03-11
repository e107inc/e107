CREATE TABLE private_msg (
  pm_id int(10) unsigned NOT NULL auto_increment,
  pm_from int(10) unsigned NOT NULL default '0',
  pm_to varchar(250) NOT NULL default '',
  pm_sent int(10) unsigned NOT NULL default '0',
  pm_read int(10) unsigned NOT NULL default '0',
  pm_subject text NOT NULL,
  pm_text text NOT NULL,
  pm_sent_del tinyint(1) unsigned NOT NULL default '0',
  pm_read_del tinyint(1) unsigned NOT NULL default '0',
  pm_attachments text NOT NULL,
  pm_option varchar(250) NOT NULL default '',
  pm_size int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pm_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE private_msg_block (
  pm_block_id int(10) unsigned NOT NULL auto_increment,
  pm_block_from int(10) unsigned NOT NULL default '0',
  pm_block_to int(10) unsigned NOT NULL default '0',
  pm_block_datestamp int(10) unsigned NOT NULL default '0',
  pm_block_count int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pm_block_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
