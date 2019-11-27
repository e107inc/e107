CREATE TABLE faqs (
  faq_id int(10) unsigned NOT NULL auto_increment,
  faq_parent int(10) unsigned NOT NULL default '0',
  faq_question text NOT NULL,
  faq_answer text NOT NULL,
  faq_comment tinyint(1) unsigned NOT NULL default '0',
  faq_datestamp int(10) unsigned NOT NULL default '0',
  faq_author int(10) unsigned default NULL,
  faq_author_ip varchar(45) NOT NULL default '',
  faq_tags varchar(255)  NOT NULL default '',
  faq_order int(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (faq_id)
) ENGINE=MyISAM;

CREATE TABLE faqs_info (
  faq_info_id int(10) unsigned NOT NULL auto_increment,
  faq_info_title text NOT NULL,
  faq_info_about text NOT NULL,
  faq_info_parent int(10) unsigned default '0',
  faq_info_class int(5) default '0',
  faq_info_order tinyint(3) unsigned NOT NULL default '0',
  faq_info_icon varchar(255) NOT NULL default '',
  faq_info_metad varchar(255) NOT NULL default '',
  faq_info_metak varchar(255) NOT NULL default '',
  faq_info_sef varchar(255) NOT NULL default '',
  PRIMARY KEY  (faq_info_id)
) ENGINE=MyISAM;

