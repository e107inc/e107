CREATE TABLE trackback (
  `trackback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trackback_pid` int(10) unsigned NOT NULL DEFAULT '0',
  `trackback_title` varchar(200) NOT NULL DEFAULT '',
  `trackback_excerpt` varchar(250) NOT NULL DEFAULT '',
  `trackback_url` varchar(150) NOT NULL DEFAULT '',
  `trackback_blogname` varchar(150) NOT NULL DEFAULT '',
   PRIMARY KEY  (`trackback_id`),
   KEY `trackback_pid` (`trackback_pid`)
) ENGINE=MyISAM;
