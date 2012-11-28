CREATE TABLE blank (
  `blank_id` int(10) NOT NULL AUTO_INCREMENT,
  `blank_icon` varchar(255) NOT NULL,
  `blank_type` varchar(10) NOT NULL,
  `blank_name` varchar(50) NOT NULL,
  `blank_folder` varchar(50) NOT NULL,
  `blank_version` varchar(5) NOT NULL,
  `blank_author` varchar(50) NOT NULL,
  `blank_authorURL` varchar(255) NOT NULL,
  `blank_date` int(10) NOT NULL,
  `blank_compatibility` varchar(5) NOT NULL,
  `blank_url` varchar(255) NOT NULL,

  PRIMARY KEY (`blank_id`)
) ENGINE=MyISAM;
