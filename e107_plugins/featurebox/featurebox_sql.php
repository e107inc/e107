CREATE TABLE featurebox (
  fb_id int(10) unsigned NOT NULL auto_increment,
  fb_title varchar(200) NOT NULL default '',
  fb_text text NOT NULL,
  fb_mode tinyint(3) unsigned NOT NULL default '0',
  fb_class smallint(5) unsigned NOT NULL default '0',
  fb_rendertype tinyint(1) unsigned NOT NULL default '0',
  fb_template varchar(50) NOT NULL default '',
  fb_order tinyint(3) unsigned NOT NULL default '0',
  fb_image varchar(255) NOT NULL default '',
  fb_imageurl varchar(255) NOT NULL default '',
  fb_category tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (fb_id)
) TYPE=MyISAM;

CREATE TABLE featurebox_cat (
  fb_cat_id int(10) unsigned NOT NULL auto_increment,
  fb_cat_title varchar(200) NOT NULL default '',
  fb_cat_class int(3) unsigned default '0',
  fb_cat_order int(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (fb_cat_id)
) TYPE=MyISAM;