<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


require_once('import_classes.php');

class smf_import extends base_import_class
{
	
	public $title			= 'SMF v2.x (Simple Machines Forum)';
	public $description		= 'Supports users only';
	public $supported		= array('users');
	public $mprefix			= 'smf_';
	public $sourceType 		= 'db';		
	
	function init()
	{
	
		
	} 	
	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		if ($this->ourDB == NULL) return FALSE;
		
	    switch ($task)
		{
			case 'users' :
				
				// Set up Userclasses. 
				if($this->ourDB && $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}membergroups WHERE group_name = 'Jr. Member' "))
				{
					e107::getMessage()->addDebug("Userclasses Found");	
				}	
				
		    	$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}members WHERE `is_activated`=1");
				if ($result === FALSE) return FALSE;
			break;
				
				
 			case 'forum' :
				$result = $this->ourDB->gen("SELECT * FROM `{$this->DBPrefix}boards`");
				if ($result === FALSE) return FALSE;	  
			break;
				
			case 'forumthread' :
				$result = $this->ourDB->gen("SELECT t.*,m.* FROM `{$this->DBPrefix}topics` AS t LEFT JOIN `{$this->DBPrefix}messages` AS m ON t.id_first_msg = m.id_msg GROUP BY t.id_topic");
				if ($result === FALSE) return FALSE;	  
			break;
				
			case 'forumpost' :
				//$result = $this->ourDB->gen("SELECT * FROM `{$this->DBPrefix}posts`");
				//if ($result === FALSE) return FALSE;	  
			break;				

			case 'forumtrack' :
				//$result = $this->ourDB->gen("SELECT * FROM `{$this->DBPrefix}forums_track`");
				//if ($result === FALSE) return FALSE;	  
			break;
				
			default :
		    return FALSE;
		}
		
		$this->copyUserInfo = false;
		$this->currentTask = $task;
		return TRUE;
	}

	
	
	function convertUserclass($data)
	{
		
		if($data == 1)
		{
			
		}
		
		/*
		1	Administrator		#FF0000	-1	0	5#staradmin.gif	1	0	-2
		2	Global Moderator		#0000FF	-1	0	5#stargmod.gif	0	0	-2
		3	Moderator			-1	0	5#starmod.gif	0	0	-2
		4	Newbie			0	0	1#star.gif	0	0	-2
		5	Jr. Member			50	0	2#star.gif	0	0	-2
		6	Full Member			100	0	3#star.gif	0	0	-2
		7	Sr. Member			250	0	4#star.gif	0	0	-2
		8	Hero Member			500	0	5#star.gif	0	0	-2	
		*/
	}	
	
	function convertAdmin($data)
	{
		
		if($data == 1)
		{
			return 1;	
		}	
		
	}

  //------------------------------------
  //	Internal functions below here
  //------------------------------------
  
  // Copy data read from the DB into the record to be returned.
	function copyUserData(&$target, &$source)
	{
		if ($this->copyUserInfo)
		{
			 $target['user_id'] = 0; // $source['id_member'];
		}
		
		$target['user_name'] 		= $source['real_name'];
		$target['user_login'] 		= $source['member_name'];
		$target['user_loginname'] 	= $source['memberName'];
		$target['user_password'] 	= $source['passwd'];				// Check - could be plaintext
		$target['user_email'] 		= $source['email_address'];
		$target['user_hideemail'] 	= $source['hide_email'];
	    $target['user_image'] 		= $source['avatar'];
		$target['user_signature'] 	= $source['signature'];

		$target['user_chats'] 		= $source['instant_messages'];
		$target['user_join'] 		= $source['date_registered'];
		$target['user_lastvisit'] 	= $source['last_login'];

		$target['user_location'] 	= $source['location'];
		$target['user_icq'] 		= $source['icq'];
		$target['user_aim'] 		= $source['aim'];
		$target['user_yahoo'] 		= $source['yim'];
		$target['user_msn'] 		= $source['msn'];
		$target['user_timezone'] 	= $source['time_offset'];			// Probably needs formatting
		$target['user_customtitle'] = $source['usertitle'];
		$target['user_ip'] 			= $source['member_ip'];
		$target['user_homepage']	= $source['website_url'];
		$target['user_birthday']	= $source['birthdate'];
		$target['user_admin']		= $this->convertadmin($source['id_group']);
		$target['user_class']		= $this->convertadmin($source['id_group']);
		
		$target['user_plugin_forum_viewed'] = 0;
		$target['user_plugin_forum_posts']	= $source['posts'];
		
	//    $target['user_language'] = $source['lngfile'];			// Guess to verify
		return $target;
		}



 	/**
	 * $target - e107_forum table
	 * $source - smf table boards  
	 */
	function copyForumData(&$target, &$source)
	{
		
		$target['forum_id'] 				= $source['id_board'];
		$target['forum_name'] 				= $source['name'];
		$target['forum_description'] 		= $source['description'];
		$target['forum_parent']				= $source['id_parent'];
		$target['forum_sub']				= "";
		$target['forum_datestamp']			= time();
		$target['forum_moderators']			= "";
	
		$target['forum_threads'] 			= $source['num_topics'];
		$target['forum_replies']			= $source['num_posts'];
		$target['forum_lastpost_user']		= '';
		$target['forum_lastpost_user_anon']	= '';
		$target['forum_lastpost_info']		= '';
		//	$target['forum_class']				= "";
		$target['forum_order']				= $source['board_order'];
		// $target['forum_postclass']	
		// $target['forum_threadclass']	
		// $target['forum_options']	
		
		return $target;
		
		
		/* 

			 CREATE TABLE {$db_prefix}boards (
		  id_board smallint(5) unsigned NOT NULL auto_increment,
		  id_cat tinyint(4) unsigned NOT NULL default '0',
		  child_level tinyint(4) unsigned NOT NULL default '0',
		  id_parent smallint(5) unsigned NOT NULL default '0',
		  board_order smallint(5) NOT NULL default '0',
		  id_last_msg int(10) unsigned NOT NULL default '0',
		  id_msg_updated int(10) unsigned NOT NULL default '0',
		  member_groups varchar(255) NOT NULL default '-1,0',
		  id_profile smallint(5) unsigned NOT NULL default '1',
		  name varchar(255) NOT NULL default '',
		  description text NOT NULL,
		  num_topics mediumint(8) unsigned NOT NULL default '0',
		  num_posts mediumint(8) unsigned NOT NULL default '0',
		  count_posts tinyint(4) NOT NULL default '0',
		  id_theme tinyint(4) unsigned NOT NULL default '0',
		  override_theme tinyint(4) unsigned NOT NULL default '0',
		  unapproved_posts smallint(5) NOT NULL default '0',
		  unapproved_topics smallint(5) NOT NULL default '0',
		  redirect varchar(255) NOT NULL default '',
		  PRIMARY KEY (id_board),
		  UNIQUE categories (id_cat, id_board),
		  KEY id_parent (id_parent),
		  KEY id_msg_updated (id_msg_updated),
		  KEY member_groups (member_groups(48))
		) ENGINE=MyISAM;
	 * */
	
		
	}

	
	/**
	 * $target - e107 forum_threads
	 * $source - smf topics. 
	 */
	function copyForumThreadData(&$target, &$source)
	{
		
		$target['thread_id'] 				= $source['topic_id'];
		$target['thread_name'] 				= $source['topic_title'];
		$target['thread_forum_id'] 			= $source['forum_id'];
		$target['thread_views'] 			= $source['topic_views'];
	//	$target['thread_active'] 			= $source['topic_status'];
		$target['thread_lastpost'] 			= $source['topic_last_post_id'];
		$target['thread_sticky'] 			= $source['topic_time_limit'];
		$target['thread_datestamp'] 		= $source['topic_time'];
		$target['thread_user'] 				= $source['topic_poster'];
		$target['thread_user_anon'] 		= $source['topic_first_poster_name'];
		$target['thread_lastuser'] 			= $source['topic_last_poster_id'];
		$target['thread_lastuser_anon'] 	= $source['topic_last_poster_name'];
		$target['thread_total_replies'] 	= $source['topic_replies'];
	//	$target['thread_options'] 			= $source['topic_'];
	
		return $target;
		
		/*
		  CREATE TABLE {$db_prefix}topics (
		  id_topic mediumint(8) unsigned NOT NULL auto_increment,
		  is_sticky tinyint(4) NOT NULL default '0',
		  id_board smallint(5) unsigned NOT NULL default '0',
		  id_first_msg int(10) unsigned NOT NULL default '0',
		  id_last_msg int(10) unsigned NOT NULL default '0',
		  id_member_started mediumint(8) unsigned NOT NULL default '0',
		  id_member_updated mediumint(8) unsigned NOT NULL default '0',
		  id_poll mediumint(8) unsigned NOT NULL default '0',
		  id_previous_board smallint(5) NOT NULL default '0',
		  id_previous_topic mediumint(8) NOT NULL default '0',
		  num_replies int(10) unsigned NOT NULL default '0',
		  num_views int(10) unsigned NOT NULL default '0',
		  locked tinyint(4) NOT NULL default '0',
		  unapproved_posts smallint(5) NOT NULL default '0',
		  approved tinyint(3) NOT NULL default '1',
		  PRIMARY KEY (id_topic),
		  UNIQUE last_message (id_last_msg, id_board),
		  UNIQUE first_message (id_first_msg, id_board),
		  UNIQUE poll (id_poll, id_topic),
		  KEY is_sticky (is_sticky),
		  KEY approved (approved),
		  KEY id_board (id_board),
		  KEY member_started (id_member_started, id_board),
		  KEY last_message_sticky (id_board, is_sticky, id_last_msg),
		  KEY board_news (id_board, id_first_msg)
		) ENGINE=MyISAM;
		 */
		
		
	}

 	
	/**
	 * $target - e107_forum_post table
	 * $source -smf //TODO
	 */
	function copyForumPostData(&$target, &$source)
	{
		$target['post_id'] 					= $source['post_id'];
		$target['post_entry'] 				= $source['post_text'];
		$target['post_thread'] 				= $source['topic_id'];
		$target['post_forum'] 				= $source['forum_id'];
	//	$target['post_status'] 				= $source[''];
		$target['post_datestamp'] 			= $source['post_time'];
		$target['post_user'] 				= $source['poster_id'];
		$target['post_edit_datestamp'] 		= $source['post_edit_time'];
		$target['post_edit_user'] 			= $source['post_edit_user'];
		$target['post_ip'] 					= $source['poster_ip'];
	//	$target['post_user_anon'] 			= $source[''];
	//	$target['post_attachments'] 		= $source[''];
	//	$target['post_options'] 			= $source[''];
		
		
		return $target;
		
		
		/*CREATE TABLE {$db_prefix}messages (
		  id_msg int(10) unsigned NOT NULL auto_increment,
		  id_topic mediumint(8) unsigned NOT NULL default '0',
		  id_board smallint(5) unsigned NOT NULL default '0',
		  poster_time int(10) unsigned NOT NULL default '0',
		  id_member mediumint(8) unsigned NOT NULL default '0',
		  id_msg_modified int(10) unsigned NOT NULL default '0',
		  subject varchar(255) NOT NULL default '',
		  poster_name varchar(255) NOT NULL default '',
		  poster_email varchar(255) NOT NULL default '',
		  poster_ip varchar(255) NOT NULL default '',
		  smileys_enabled tinyint(4) NOT NULL default '1',
		  modified_time int(10) unsigned NOT NULL default '0',
		  modified_name varchar(255) NOT NULL default '',
		  body text NOT NULL,
		  icon varchar(16) NOT NULL default 'xx',
		  approved tinyint(3) NOT NULL default '1',
		  PRIMARY KEY (id_msg),
		  UNIQUE topic (id_topic, id_msg),
		  UNIQUE id_board (id_board, id_msg),
		  UNIQUE id_member (id_member, id_msg),
		  KEY approved (approved),
		  KEY ip_index (poster_ip(15), id_topic),
		  KEY participation (id_member, id_topic),
		  KEY show_posts (id_member, id_board),
		  KEY id_topic (id_topic),
		  KEY id_member_msg (id_member, approved, id_msg),
		  KEY current_topic (id_topic, id_msg, id_member, approved),
		  KEY related_ip (id_member, poster_ip, id_msg)
		) ENGINE=MyISAM;
		 * 
		
		INSERT INTO {$db_prefix}messages
			(id_msg, id_msg_modified, id_topic, id_board, poster_time, subject, poster_name, poster_email, poster_ip, modified_name, body, icon)
		VALUES (1, 1, 1, 1, UNIX_TIMESTAMP(), '{$default_topic_subject}', 'Simple Machines', 'info@simplemachines.org', '127.0.0.1', '', '{$default_topic_message}', 'xx');
		 
		 */

	}



	/**
	 * $target - e107_forum_track
	 * $source	- phpbb_forums_track : https://wiki.phpbb.com/Table.phpbb_forums_track
	 */
	function copyForumTrackData(&$target, &$source)
	{
		$target['track_userid'] = $source['user_id'];
		$target['track_thread'] = $source['forum_id'];

		return $target;
	}


/*
CREATE TABLE {$db_prefix}polls (
  id_poll mediumint(8) unsigned NOT NULL auto_increment,
  question varchar(255) NOT NULL default '',
  voting_locked tinyint(1) NOT NULL default '0',
  max_votes tinyint(3) unsigned NOT NULL default '1',
  expire_time int(10) unsigned NOT NULL default '0',
  hide_results tinyint(3) unsigned NOT NULL default '0',
  change_vote tinyint(3) unsigned NOT NULL default '0',
  guest_vote tinyint(3) unsigned NOT NULL default '0',
  num_guest_voters int(10) unsigned NOT NULL default '0',
  reset_poll int(10) unsigned NOT NULL default '0',
  id_member mediumint(8) NOT NULL default '0',
  poster_name varchar(255) NOT NULL default '',
  PRIMARY KEY (id_poll)
) ENGINE=MyISAM;
 * 
 * 
 * 
 

 * 
 * INSERT INTO {$db_prefix}membergroups
	(id_group, group_name, description, online_color, min_posts, stars, group_type)
VALUES (1, '{$default_administrator_group}', '', '#FF0000', -1, '5#staradmin.gif', 1),
	(2, '{$default_global_moderator_group}', '', '#0000FF', -1, '5#stargmod.gif', 0),
	(3, '{$default_moderator_group}', '', '', -1, '5#starmod.gif', 0),
	(4, '{$default_newbie_group}', '', '', 0, '1#star.gif', 0),
	(5, '{$default_junior_group}', '', '', 50, '2#star.gif', 0),
	(6, '{$default_full_group}', '', '', 100, '3#star.gif', 0),
	(7, '{$default_senior_group}', '', '', 250, '4#star.gif', 0),
	(8, '{$default_hero_group}', '', '', 500, '5#star.gif', 0);
# --------------------------------------------------------

 * 

 * 
 *  * 

CREATE TABLE {$db_prefix}membergroups (
  id_group smallint(5) unsigned NOT NULL auto_increment,
  group_name varchar(80) NOT NULL default '',
  description text NOT NULL,
  online_color varchar(20) NOT NULL default '',
  min_posts mediumint(9) NOT NULL default '-1',
  max_messages smallint(5) unsigned NOT NULL default '0',
  stars varchar(255) NOT NULL default '',
  group_type tinyint(3) NOT NULL default '0',
  hidden tinyint(3) NOT NULL default '0',
  id_parent smallint(5) NOT NULL default '-2',
  PRIMARY KEY (id_group),
  KEY min_posts (min_posts)
) ENGINE=MyISAM; 

CREATE TABLE {$db_prefix}members (
  id_member mediumint(8) unsigned NOT NULL auto_increment,
  member_name varchar(80) NOT NULL default '',
  date_registered int(10) unsigned NOT NULL default '0',
  posts mediumint(8) unsigned NOT NULL default '0',
  id_group smallint(5) unsigned NOT NULL default '0',
  lngfile varchar(255) NOT NULL default '',
  last_login int(10) unsigned NOT NULL default '0',
  real_name varchar(255) NOT NULL default '',
  instant_messages smallint(5) NOT NULL default 0,
  unread_messages smallint(5) NOT NULL default 0,
  new_pm tinyint(3) unsigned NOT NULL default '0',
  buddy_list text NOT NULL,
  pm_ignore_list varchar(255) NOT NULL default '',
  pm_prefs mediumint(8) NOT NULL default '0',
  mod_prefs varchar(20) NOT NULL default '',
  message_labels text NOT NULL,
  passwd varchar(64) NOT NULL default '',
  openid_uri text NOT NULL,
  email_address varchar(255) NOT NULL default '',
  personal_text varchar(255) NOT NULL default '',
  gender tinyint(4) unsigned NOT NULL default '0',
  birthdate date NOT NULL default '0001-01-01',
  website_title varchar(255) NOT NULL default '',
  website_url varchar(255) NOT NULL default '',
  location varchar(255) NOT NULL default '',
  icq varchar(255) NOT NULL default '',
  aim varchar(255) NOT NULL default '',
  yim varchar(32) NOT NULL default '',
  msn varchar(255) NOT NULL default '',
  hide_email tinyint(4) NOT NULL default '0',
  show_online tinyint(4) NOT NULL default '1',
  time_format varchar(80) NOT NULL default '',
  signature text NOT NULL,
  time_offset float NOT NULL default '0',
  avatar varchar(255) NOT NULL default '',
  pm_email_notify tinyint(4) NOT NULL default '0',
  karma_bad smallint(5) unsigned NOT NULL default '0',
  karma_good smallint(5) unsigned NOT NULL default '0',
  usertitle varchar(255) NOT NULL default '',
  notify_announcements tinyint(4) NOT NULL default '1',
  notify_regularity tinyint(4) NOT NULL default '1',
  notify_send_body tinyint(4) NOT NULL default '0',
  notify_types tinyint(4) NOT NULL default '2',
  member_ip varchar(255) NOT NULL default '',
  member_ip2 varchar(255) NOT NULL default '',
  secret_question varchar(255) NOT NULL default '',
  secret_answer varchar(64) NOT NULL default '',
  id_theme tinyint(4) unsigned NOT NULL default '0',
  is_activated tinyint(3) unsigned NOT NULL default '1',
  validation_code varchar(10) NOT NULL default '',
  id_msg_last_visit int(10) unsigned NOT NULL default '0',
  additional_groups varchar(255) NOT NULL default '',
  smiley_set varchar(48) NOT NULL default '',
  id_post_group smallint(5) unsigned NOT NULL default '0',
  total_time_logged_in int(10) unsigned NOT NULL default '0',
  password_salt varchar(255) NOT NULL default '',
  ignore_boards text NOT NULL,
  warning tinyint(4) NOT NULL default '0',
  passwd_flood varchar(12) NOT NULL default '',
  pm_receive_from tinyint(4) unsigned NOT NULL default '1',
  PRIMARY KEY (id_member),
  KEY member_name (member_name),
  KEY real_name (real_name),
  KEY date_registered (date_registered),
  KEY id_group (id_group),
  KEY birthdate (birthdate),
  KEY posts (posts),
  KEY last_login (last_login),
  KEY lngfile (lngfile(30)),
  KEY id_post_group (id_post_group),
  KEY warning (warning),
  KEY total_time_logged_in (total_time_logged_in),
  KEY id_theme (id_theme)
) ENGINE=MyISAM;

*/





}


?>