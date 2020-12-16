CREATE TABLE `hero` (
  `hero_id` int(10) NOT NULL AUTO_INCREMENT,
  `hero_title` varchar(255) NOT NULL,
  `hero_description` varchar(255) NOT NULL,
  `hero_image` varchar(255) NOT NULL,
  `hero_bullets` text NOT NULL,
  `hero_button1` text NOT NULL,
  `hero_button2` text NOT NULL,
  `hero_order` tinyint(3) unsigned NOT NULL default '0',
  `hero_class` int(5) default '0',
  PRIMARY KEY  (hero_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;