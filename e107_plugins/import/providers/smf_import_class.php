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
	public $description		= 'Currently does not import membergroups or more than 1 post attachment ';
	public $supported		= array('users','forum','forumthread','forumpost');
	public $mprefix			= 'smf_';
	public $sourceType 		= 'db';		
	
	function init()
	{

		
	}

	function config()
	{

		return;

		$frm = e107::getForm();

		$var[0]['caption']	= "Path to phpBB3 Attachments folder (optional)";
		$var[0]['html'] 	= $frm->text('forum_attachment_path',null,40,'size=xxlarge');
		$var[0]['help'] 	= "Relative to the root folder of your e107 installation";

		return $var;
	}

	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		if ($this->ourDB == null)
		{
			e107::getMessage()->addDebug("Unable to connext");
		    return FALSE;
		}
		
	    switch ($task)
		{
			case 'users' :
				
				// Set up Userclasses. 
				if($this->ourDB && $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}membergroups WHERE group_name = 'Jr. Member' "))
				{
					e107::getMessage()->addDebug("Userclasses Found");	
				}	
				
		    	$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}members WHERE `is_activated`=1");
				if ($result === false)
				{
					$message = $this->ourDB->getLastErrorText();
	  		        e107::getMessage()->addError($message);
					return false;
				}
			break;
				
				
 			case 'forum' :
 			    $qry = "SELECT f.*, m.id_member, m.poster_name, m.poster_time FROM {$this->DBPrefix}boards AS f LEFT JOIN {$this->DBPrefix}messages AS m ON f.id_last_msg = m.id_msg GROUP BY f.id_board ";

				$result = $this->ourDB->gen($qry);
				if ($result === false)
				{
					$message = $this->ourDB->getLastErrorText();
	  		        e107::getMessage()->addError($message);
	  		        return false;
				}
			break;
				
			case 'forumthread' :

				$qry = "SELECT t.*, m.poster_name, m.subject, m.poster_time, m.id_member, l.poster_name as lastpost_name, l.poster_time as lastpost_time, l.id_member as lastpost_user FROM {$this->DBPrefix}topics AS t
						LEFT JOIN {$this->DBPrefix}messages AS m ON t.id_first_msg = m.id_msg
						LEFT JOIN {$this->DBPrefix}messages AS l ON t.id_last_msg = l.id_msg
						GROUP BY t.id_topic";

				$result = $this->ourDB->gen($qry);
				if ($result === false) return false;

			break;
				
			case 'forumpost' :

				$qry = "SELECT m.*, a.filename, a.fileext, a.size FROM {$this->DBPrefix}messages AS m LEFT JOIN {$this->DBPrefix}attachments AS a ON m.id_msg = a.id_msg GROUP BY m.id_msg ORDER BY m.id_msg ASC ";

				$result = $this->ourDB->gen($qry);
				if ($result === false) return false;
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
		if(empty($data))
		{
			return 0;
		}

		$convert = array(
		//	1   => e_UC_ADMINMOD,
			2   => e_UC_ADMINMOD,
			3   => e_UC_MODS,
			4   => e_UC_NEWUSER,

		);

		if(!empty($convert[$data]))
		{
			return $convert[$data];
		}


		return 0;

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
		$target['user_admin']		= $this->convertAdmin($source['id_group']);
		$target['user_class']		= $this->convertUserclass($source['id_group']);
		
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
		$target['forum_sub']				= ($source['child_level'] > 1) ? $source['id_parent'] : 0;
		$target['forum_datestamp']			= time();
		$target['forum_moderators']			= "";
	
		$target['forum_threads'] 			= $source['num_topics'];
		$target['forum_replies']			= $source['num_posts'];
		$target['forum_lastpost_user']		= $source['id_member'];
		$target['forum_lastpost_user_anon']	= empty($source['id_member']) ? $source['poster_name'] : null;
		$target['forum_lastpost_info']		= $source['poster_time'].'.'.$source['id_last_msg'];
		$target['forum_class']				= e_UC_MEMBER;
		$target['forum_order']				= $source['board_order'];
		$target['forum_postclass']	        = e_UC_MEMBER;
		$target['forum_threadclass']	    = e_UC_MEMBER;
		$target['forum_options']	        = e_UC_MEMBER;
		$target['forum_sef']                = eHelper::title2sef($source['name'],'dashl');
		
		return $target;

		
	}

	
	/**
	 * $target - e107 forum_threads
	 * $source - smf topics. 
	 */
	function copyForumThreadData(&$target, &$source)
	{
		
		$target['thread_id'] 				= (int) $source['id_topic'];
		$target['thread_name'] 				= $source['subject'];
		$target['thread_forum_id'] 			= (int) $source['id_board'];
		$target['thread_views'] 			= (int) $source['num_views'];
		$target['thread_active'] 			= intval($source['locked']) === 0 ? 1 : 0;
		$target['thread_lastpost'] 			= (int) $source['lastpost_time'];
		$target['thread_sticky'] 			= (int) $source['id_sticky'];
		$target['thread_datestamp'] 		= (int) $source['poster_time'];
		$target['thread_user'] 				= (int) $source['id_member_started'];
		$target['thread_user_anon'] 		= empty($source['id_member']) ? $source['poster_name'] : null;
		$target['thread_lastuser'] 			= (int) $source['lastpost_user'];
		$target['thread_lastuser_anon'] 	= empty($source['lastpost_user']) ? $source['lastpost_name'] : null;
		$target['thread_total_replies'] 	= (int) $source['num_replies'];
		$target['thread_options'] 			= null;
	
		return $target;
		
	}

 	
	/**
	 * $target - e107_forum_post table
	 * $source -smf
	 */
	function copyForumPostData(&$target, &$source)
	{
		$target['post_id'] 					= (int)$source['id_msg'];
		$target['post_entry'] 				= $source['body'];
		$target['post_thread'] 				= (int) $source['id_topic'];
		$target['post_forum'] 				= (int) $source['id_board'];
		$target['post_status'] 				= intval($source['approved']) === 1 ?  0 : 1;
		$target['post_datestamp'] 			= (int) $source['poster_time'];
		$target['post_user'] 				= (int) $source['id_member'];
		$target['post_edit_datestamp'] 		= (int) $source['modified_time'];
		$target['post_edit_user'] 			= $source['post_edit_user'];
		$target['post_ip'] 					= e107::getIPHandler()->ipEncode($source['poster_ip']);
		$target['post_user_anon'] 			= empty($source['id_member']) ? $source['poster_name'] : null;
		$target['post_attachments'] 		= $this->processAttachments($source);
		$target['post_options'] 			= null;


		return $target;
		

	}


	/**
	 * todo copyForumPostAttachments()
	 * @param $source
	 * @return null|string
	 */


	/**
	 * @todo Support for multiple attachments.
	 * @param $source
	 * @return null|string
	 */
	private function processAttachments($source)
	{
		if(empty($source['filename']))
		{
			return null;
		}


		if($source['fileext'] == 'png' || $source['fileext'] == 'jpg' || $source['fileext'] == 'jpg')
		{
			$type = 'img';
		}
		else
		{
			$type = 'file';
		}

		$arr = array();
		$arr[$type][0] = array(
			'file'  => $source['filename'],
			'name'  => $source['filename'],
			'size'  => $source['size']
		);

		return e107::serialize($arr);

	}

	/**
	 * $target - e107_forum_track
	 * $source	-???
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


