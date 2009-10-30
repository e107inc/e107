CREATE TABLE release (
  `release_id` int(10) NOT NULL AUTO_INCREMENT,
  `release_type` varchar(10) NOT NULL,
  `release_name` varchar(50) NOT NULL,
  `release_folder` varchar(50) NOT NULL,
  `release_version` varchar(5) NOT NULL,
  `release_author` varchar(50) NOT NULL,
  `release_authorURL` varchar(255) NOT NULL,
  `release_date` int(10) NOT NULL,
  `release_compatibility` varchar(5) NOT NULL,
  `release_url` varchar(255) NOT NULL,
  PRIMARY KEY (`release_id`)
) TYPE=MyISAM;
