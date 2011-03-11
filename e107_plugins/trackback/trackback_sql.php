CREATE TABLE trackback (
  `trackback_id` int(10) unsigned NOT NULL auto_increment,
  `trackback_pid` int(10) unsigned NOT NULL default '0',
  `trackback_title` varchar(200) NOT NULL default '',
  `trackback_excerpt` varchar(250) NOT NULL default '',
  `trackback_url` varchar(150) NOT NULL default '',
  `trackback_blogname` varchar(150) NOT NULL default '',
   PRIMARY KEY  (`trackback_id`),
   KEY `trackback_pid` (`trackback_pid`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
