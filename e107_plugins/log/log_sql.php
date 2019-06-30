CREATE TABLE logstats (
  log_uniqueid int(11) NOT NULL auto_increment,
  log_id varchar(50) NOT NULL default '',
  log_data longtext NOT NULL,
  PRIMARY KEY  (log_uniqueid),
  UNIQUE KEY log_id (log_id)
) ENGINE=MyISAM;