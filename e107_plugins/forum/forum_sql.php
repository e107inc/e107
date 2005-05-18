CREATE TABLE forum (
	forum_id int(10) unsigned NOT NULL auto_increment,
	forum_name varchar(250) NOT NULL default '',
	forum_description text NOT NULL,
	forum_parent int(10) unsigned NOT NULL default '0',
	forum_datestamp int(10) unsigned NOT NULL default '0',
	forum_moderators text NOT NULL,
	forum_threads int(10) unsigned NOT NULL default '0',
	forum_replies int(10) unsigned NOT NULL default '0',
	forum_lastpost_user varchar(200) NOT NULL default '',
	forum_lastpost_info varchar(40) NOT NULL default '',
	forum_class varchar(100) NOT NULL default '',
	forum_order int(10) unsigned NOT NULL default '0',
	forum_postclass tinyint(3) unsigned NOT NULL default '0',
	PRIMARY KEY  (forum_id)
	) TYPE=MyISAM AUTO_INCREMENT=1

CREATE TABLE forum_t (
	thread_id int(10) unsigned NOT NULL auto_increment,
	thread_name varchar(250) NOT NULL default '',
	thread_thread text NOT NULL,
	thread_forum_id int(10) unsigned NOT NULL default '0',
	thread_datestamp int(10) unsigned NOT NULL default '0',
	thread_parent int(10) unsigned NOT NULL default '0',
	thread_user varchar(250) NOT NULL default '',
	thread_views int(10) unsigned NOT NULL default '0',
	thread_active tinyint(3) unsigned NOT NULL default '0',
	thread_lastpost int(10) unsigned NOT NULL default '0',
	thread_s tinyint(1) unsigned NOT NULL default '0',
	thread_edit_datestamp int(10) unsigned NOT NULL default '0',
	thread_lastuser varchar(30) NOT NULL default '',
	thread_total_replies int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (thread_id),
	KEY thread_parent (thread_parent),
	KEY thread_datestamp (thread_datestamp),
	KEY thread_forum_id (thread_forum_id)
	) TYPE=MyISAM AUTO_INCREMENT=1;

