CREATE TABLE forum (
  `forum_id` int(10) unsigned NOT NULL auto_increment,
  `forum_name` varchar(250) NOT NULL default '',
  `forum_description` text,
  `forum_parent` int(10) unsigned NOT NULL default '0',
  `forum_sub` int(10) unsigned NOT NULL default '0',
  `forum_datestamp` int(10) unsigned NOT NULL default '0',
  `forum_moderators` tinyint(3) unsigned NOT NULL default '0',
  `forum_threads` int(10) unsigned NOT NULL default '0',
  `forum_replies` int(10) unsigned NOT NULL default '0',
  `forum_lastpost_user` int(10) unsigned default NULL,
  `forum_lastpost_info` varchar(40) default NULL,
  `forum_class` tinyint(3) unsigned NOT NULL default '0',
  `forum_order` int(10) unsigned NOT NULL default '0',
  `forum_postclass` tinyint(3) unsigned NOT NULL default '0',
  `forum_threadclass` tinyint(3) unsigned NOT NULL default '0',
  `forum_options` text,
  PRIMARY KEY  (`forum_id`),
  KEY `forum_parent` (`forum_parent`),
  KEY `forum_sub` (`forum_sub`)
) Type=MyISAM AUTO_INCREMENT=1;

CREATE TABLE forum_thread (
  `thread_id` int(10) unsigned NOT NULL auto_increment,
  `thread_name` varchar(250) NOT NULL default '',
  `thread_thread` text NOT NULL,
  `thread_forum_id` int(10) unsigned NOT NULL default '0',
  `thread_views` int(10) unsigned NOT NULL default '0',
  `thread_active` tinyint(3) unsigned NOT NULL default '0',
  `thread_lastpost` int(10) unsigned NOT NULL default '0',
  `thread_s` tinyint(1) unsigned NOT NULL default '0',
  `thread_lastuser` int(10) unsigned default NULL,
  `thread_total_replies` int(10) unsigned NOT NULL default '0',
  `thread_options` text,
  PRIMARY KEY  (`thread_id`),
  KEY `thread_forum_id` (`thread_forum_id`),
  KEY `thread_s` (`thread_s`),
  KEY `thread_lastpost` (`thread_lastpost`)
) Type=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE forum_post (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `post_entry` text NOT NULL,
  `post_thread` int(10) unsigned default NULL,
  `post_forum` int(10) unsigned default NULL,
  `post_datestamp` int(10) unsigned NOT NULL default '0',
  `post_user` int(10) unsigned NOT NULL,
  `post_edit_datestamp` int(10) unsigned NOT NULL default '0',
  `post_edit_user` int(10) unsigned NOT NULL,
  `post_ip` varchar(45) NOT NULL,
  `post_attachments` text default NULL,
  `post_options` text,
  PRIMARY KEY  (`post_id`),
  KEY `post_ip` (`post_ip`),
  KEY `post_thread` (`post_thread`),
  KEY `post_forum` (`post_forum`),
  KEY `post_datestamp` (`post_datestamp`),
  KEY `post_user` (`post_user`)
) Type=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE forum_track (
  `track_userid` int(10) unsigned NOT NULL,
  `track_thread` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`track_userid`,`track_thread`),
  KEY `track_userid` (`track_userid`),
  KEY `track_thread` (`track_thread`)
) Type=MyISAM;

