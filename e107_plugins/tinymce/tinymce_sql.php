CREATE TABLE tinymce (
  `tinymce_id` int(5) NOT NULL AUTO_INCREMENT,
  `tinymce_name` varchar(255) NOT NULL,
  `tinymce_userclass` varchar(255) NOT NULL,
  `tinymce_plugins` text NOT NULL,
  `tinymce_buttons1` varchar(255) NOT NULL,
  `tinymce_buttons2` varchar(255) NOT NULL,
  `tinymce_buttons3` varchar(255) NOT NULL,
  `tinymce_buttons4` varchar(255) NOT NULL,
  `tinymce_custom` text NOT NULL,
  `tinymce_prefs` text NOT NULL,
  PRIMARY KEY (`tinymce_id`)
) ENGINE=MyISAM;


