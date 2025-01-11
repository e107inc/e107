CREATE TABLE polls (
  poll_id int(10) unsigned NOT NULL auto_increment,
  poll_datestamp int(10) unsigned NOT NULL default '0',
  poll_start_datestamp int(10) unsigned NOT NULL default '0',
  poll_end_datestamp int(10) unsigned NOT NULL default '0',
  poll_admin_id int(10) unsigned NOT NULL default '0',
  poll_title varchar(250) NOT NULL default '',
  poll_options text NOT NULL,
  poll_votes text NOT NULL,
  poll_ip text NOT NULL,
  poll_type tinyint(1) unsigned NOT NULL default '0',
  poll_comment tinyint(1) unsigned NOT NULL default '1',
  poll_allow_multiple tinyint(1) unsigned NOT NULL default '0',
  poll_result_type tinyint(2) unsigned NOT NULL default '0',
  poll_vote_userclass smallint(5) unsigned NOT NULL default '0',
  poll_storage_method tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (poll_id)
) ENGINE=MyISAM;