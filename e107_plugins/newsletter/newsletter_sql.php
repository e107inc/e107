CREATE TABLE newsletter (
  newsletter_id int(10) unsigned NOT NULL auto_increment,
  newsletter_datestamp int(10) unsigned NOT NULL,
  newsletter_title varchar(200) NOT NULL,
  newsletter_text text NOT NULL,
  newsletter_header text NOT NULL,
  newsletter_footer text NOT NULL,
  newsletter_subscribers text NOT NULL,
  newsletter_parent int(11) NOT NULL,
  newsletter_flag tinyint(4) NOT NULL,
  newsletter_issue varchar(100) NOT NULL,
  PRIMARY KEY  (newsletter_id)
) ENGINE=MyISAM;