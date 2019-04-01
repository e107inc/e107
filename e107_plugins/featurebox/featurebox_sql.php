<?php exit; ?>
CREATE TABLE featurebox (
  `fb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fb_title` varchar(200) NOT NULL DEFAULT '',
  `fb_text` text NOT NULL,
  `fb_mode` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `fb_class` smallint(5) NOT NULL DEFAULT '0',
  `fb_rendertype` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `fb_template` varchar(50) NOT NULL DEFAULT '',
  `fb_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `fb_image` varchar(255) NOT NULL DEFAULT '',
  `fb_imageurl` text NOT NULL,
  `fb_category` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`fb_id`),
  KEY `fb_category` (`fb_category`)
) ENGINE=MyISAM;

CREATE TABLE featurebox_category (
  `fb_category_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `fb_category_title` varchar(200) NOT NULL DEFAULT '',
  `fb_category_icon` varchar(255) NOT NULL DEFAULT '',
  `fb_category_template` varchar(50) NOT NULL DEFAULT 'default',
  `fb_category_random` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `fb_category_class` smallint(5) NOT NULL DEFAULT '0',
  `fb_category_limit` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `fb_category_parms` text NOT NULL,
  PRIMARY KEY (`fb_category_id`),
  UNIQUE KEY `fb_category_template` (`fb_category_template`)
) ENGINE=MyISAM;