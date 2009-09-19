CREATE TABLE featurebox (
  fb_id int(10) unsigned NOT NULL auto_increment,
  fb_title varchar(200) NOT NULL default '',
  fb_text text NOT NULL,
  fb_mode tinyint(3) unsigned NOT NULL default '0',
  fb_class smallint(5) unsigned NOT NULL default '0',
  fb_rendertype tinyint(1) unsigned NOT NULL default '0',
  fb_template varchar(50) NOT NULL default '',
  PRIMARY KEY  (fb_id)
) TYPE=MyISAM;
