<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/phpbb2_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

require_once('import_classes.php');

class phpbb3_import extends base_import_class
{

	public $title		= 'phpBB Version 3';
	public $description	= 'Import phpBB3 Users and Forums';
	public $supported	=  array('users','forum','forumthread','forumpost','forumtrack');
	public $mprefix		= 'phpbb_';

	var $catcount = 0;				// Counts forum IDs
	var $id_map = array();			// Map of PHPBB forum IDs ==> E107 forum IDs
  
  
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
  // If $blank_user is true, certain cross-referencing user info is to be zeroed
	function setupQuery($task, $blank_user=FALSE)
	{
    	if ($this->ourDB == NULL) return FALSE;
		
	    switch ($task)
		{
		  	case 'users' :
				$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}users ORDER BY user_id ASC ");
				if ($result === FALSE) return FALSE;
			break;
			
		  	case 'forum' :
				$result = $this->ourDB->gen("SELECT * FROM `{$this->DBPrefix}forums`");
				if ($result === FALSE) return FALSE;	  
			break;
				
			case 'forumthread' :
				$result = $this->ourDB->gen("SELECT * FROM `{$this->DBPrefix}topics`");
				if ($result === FALSE) return FALSE;	  
			break;
				
			case 'forumpost' :
				$result = $this->ourDB->gen("SELECT * FROM `{$this->DBPrefix}posts`");
				if ($result === FALSE) return FALSE;	  
			break;				

			case 'forumtrack' :
				$result = $this->ourDB->gen("SELECT * FROM `{$this->DBPrefix}forums_track`");
				if ($result === FALSE) return FALSE;	  
			break;

				
		  	case 'polls' :
		    	return FALSE;
			break;  
			
		  	case 'news' :
				  return FALSE;	
			break;
		  
			  
		  	default :
		    return FALSE;
		}

		$this->copyUserInfo = true; // !$blank_user;
		$this->currentTask = $task;
		return TRUE;
	}

  
	/**
	 * Convert salted password to e107 style (they use the same basic coding)
	 */
	function convertPassword($password)
	{
		if ((substr($password ,0,3) == '$H$') && (strlen($password) == 34)) 
		{ 
			return substr_replace($password, '$E$',0,3);
		}
		else // Probably an old md5 password
		{  
			return $password; 
		}	
		
	}
	
	function convertBirthday($date)
	{
		$tp = e107::getParser();
		
		if(trim($date) == '')
		{
			return;	
		}
		
		list($d,$m,$y) = explode("-",$date);
		return $tp->leadingZeros($y,4)."-".$tp->leadingZeros($m,2)."-".$tp->leadingZeros($d,2);
	}
	  
  //------------------------------------
  //	Internal functions below here
  //------------------------------------
  

  /**
   * Copy data read from the DB into the record to be returned.
   * $target - e107_users table
   * $source - phpbb_user table : https://wiki.phpbb.com/Table.phpbb_users
   */
	function copyUserData(&$target, &$source)
	{
	// if ($this->copyUserInfo)
		$target['user_id'] 				= $source['user_id'];
		$target['user_name'] 			= $source['username'];
		$target['user_loginname'] 		= $source['username'];
		$target['user_password']		= $this->convertPassword($source['user_password']);
		$target['user_email'] 			= $source['user_email'];
		$target['user_signature'] 		= $this->proc_bb($source['user_sig'],'phpbb,bblower');
		$target['user_image'] 			= $source['user_avatar'];
		$target['user_hideemail'] 		= $source['user_allow_viewemail'];
		$target['user_join'] 			= $source['user_regdate'];
		$target['user_lastvisit'] 		= $source['user_lastvisit'];
		$target['user_currentvisit']	= 0;
		$target['user_admin'] 			= 0; //  $source['user_level'];
		$target['user_lastpost']		= $source['user_lastpost_time']; 
		$target['user_chats']			= '';
		$target['user_comments']		= '';
		$target['user_ip']				= $source['user_ip'];
		$target['user_ban']				= $source['user_type'];
		$target['user_prefs']			= '';
		$target['user_visits']			= '';
		$target['user_admin']			= 0;
		$target['user_login']			= '';
		$target['user_class']			= '';
		$target['user_perms']			= '';
		$target['user_realm']			= '';
		$target['user_pwchange']		= $source['user_passchg'];
		$target['user_xup']				= '';
	
		// Extended Fields. 
				
		$target['user_plugin_forum_viewed'] = 0;
		$target['user_plugin_forum_posts']	= $source['user_posts'];
		$target['user_timezone'] 			= $source['user_timezone'];		// source is decimal(5,2)
		$target['user_language'] 			= e107::getLanguage()->convert($source['user_lang']);	// convert from 2-letter to full. 
		$target['user_location'] 			= $source['user_from'];
		$target['user_icq'] 				= $source['user_icq'];
		$target['user_aim'] 				= $source['user_aim'];
		$target['user_yahoo'] 				= $source['user_yim'];
		$target['user_msn'] 				= $source['user_msnm'];
		$target['user_homepage'] 			= $source['user_website'];
		$target['user_birthday']			= $this->convertBirthday($source['user_birthday']);
		$target['user_occupation']			= $source['user_occ'];
		$target['user_interests']			= $source['user_interests'];
		

		return $target;

	}

 
 	/**
	 * $target - e107_forum table
	 * $source - phpbb_forums table : https://wiki.phpbb.com/Table.phpbb_forums
	 */
	function copyForumData(&$target, &$source)
	{
		$target['forum_id'] 				= $source['forum_id'];
		$target['forum_name'] 				= $source['forum_name'];
		$target['forum_description'] 		= $source['forum_desc'];
		$target['forum_parent']				= $source['parent_id'];
		$target['forum_sub']				= "";
		$target['forum_datestamp']			= time();
		$target['forum_moderators']			= "";
	
		$target['forum_threads'] 			= $source['forum_topics'];
		$target['forum_replies']			= "";
		$target['forum_lastpost_user']		= $source['forum_last_poster_id'];
		$target['forum_lastpost_user_anon']	= $source['forum_last_poster_name'];
		$target['forum_lastpost_info']		= $source['forum_last_post_time'];
		//	$target['forum_class']				= "";
		// $target['forum_order']	
		// $target['forum_postclass']	
		// $target['forum_threadclass']	
		// $target['forum_options']	
	
	
		return $target;
	}

	
	/**
	 * $target - e107 forum_threads
	 * $source - phpbb_topics : https://wiki.phpbb.com/Table.phpbb_topics
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
	}

 	
	/**
	 * $target - e107_forum_post table
	 * $source - phpbb_posts table : https://wiki.phpbb.com/Table.phpbb_posts
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





















// ---------------------------------------------- OLD ---------------------------------



  

  function convertForumParent(&$target, &$source)
  {
	$this->catcount++;
	$this->id_map[$source['cat_id']] = $this->catcount;
    $target['forum_id'] = $this->catcount;			// Create new IDs for parent forums
    $target['forum_name'] = $source['cat_title'];
    $target['forum_order'] = $source['cat_order'];
    $target['forum_description'] = $source['cat_desc'];
    $target['forum_moderators'] = e_UC_ADMIN;
//    $target['forum_'] = $source[''];
//    $target['forum_'] = $source[''];



  }
  
  /**
   * $target - e107 table
   * $source - phpbb3 table 
   */
  function convertForum(&$target, &$source, $catid)
  {
	$this->catcount++;
	$this->id_map[$source['forum_id']] = $this->catcount;
    $target['forum_id'] = $this->catcount;
    $target['forum_parent'] = $this->id_map[$source['cat_id']];		// Map to the new E107 ID, rather than directly use the one from the DB
    $target['forum_name'] = $source['forum_name'];
    $target['forum_description'] = $source['forum_desc'];
    $target['forum_order'] = $source['forum_order'];
    $target['forum_threads'] = $source['forum_topics'];
    $target['forum_replies'] = $source['forum_posts'];
    $target['forum_moderators'] = e_UC_ADMIN;
//    $target['forum_'] = $source[''];
  }
}
/*
e107 
thread_id
thread_name
thread_forum_id
thread_views
thread_active
thread_lastpost
thread_sticky
thread_datestamp
thread_user
thread_user_anon
thread_lastuser
thread_lastuser_anon
thread_total_replies
thread_options
 * 

/*
Historical info for conversion below here

function convertParents($catid)
{
	$parentArray = array(
		array("srcdata" => "cat_id", "e107" => "forum_id", "type" => "INT", "value" => $catid),
		array("srcdata" => "cat_title", "e107" => "forum_name", "type" => "STRING"),
		array("srcdata" => "cat_order", "e107" => "forum_order", "type" => "INT"),
		array("srcdata" => "cat_desc", "e107" => "forum_description", "type" => "STRING"),
        array("srcdata" => "null", "e107" => "forum_moderators", "type" => "INT", "value" => 254)
	);
	return $parentArray;
}

function convertForums($catid)
{
	$forumArray = array(
		array("srcdata" => "forum_id", "e107" => "forum_id", "type" => "INT"),
		array("srcdata" => "cat_id", "e107" => "forum_parent", "type" => "STRING", "value" => $catid),
		array("srcdata" => "forum_name", "e107" => "forum_name", "type" => "STRING"),
		array("srcdata" => "forum_desc", "e107" => "forum_description", "type" => "STRING"),
		array("srcdata" => "forum_order", "e107" => "forum_order", "type" => "INT"),
		array("srcdata" => "forum_topics", "e107" => "forum_threads", "type" => "INT"),
		array("srcdata" => "forum_posts", "e107" => "forum_replies", "type" => "INT"),
		array("srcdata" => "null", "e107" => "forum_moderators", "type" => "INT", "value" => 254)
	);
	return $forumArray;
}


//function convertTopics($poster)
function convertTopics()
{
	$topicArray = array(
		array("srcdata" => "forum_id", "e107" => "thread_forum_id", "type" => "INT"),
		array("srcdata" => "topic_title", "e107" => "thread_name", "type" => "STRING"),
		array("srcdata" => "post_text", "e107" => "thread_thread", "type" => "STRING", "default" => "", "sproc" => "usebb,phpbb,bblower"),
		array("srcdata" => "topic_poster", "e107" => "thread_user", "type" => "STRING"),
		array("srcdata" => "null", "e107" => "thread_active", "type" => "INT", "value" => 1),
		array("srcdata" => "topic_time", "e107" => "thread_datestamp", "type" => "INT"),
		array("srcdata" => "topic_views", "e107" => "thread_views", "type" => "INT"),
		array("srcdata" => "topic_replies", "e107" => "thread_total_replies", "type" => "INT"),
		array("srcdata" => "null", "e107" => "thread_parent", "type" => "INT", "value" => 0),
	);
	return $topicArray;
}




function convertForumPosts($parent_id, $poster)
{
	$postArray = array(
		array("srcdata" => "post_text", "e107" => "thread_thread", "type" => "STRING", "default" => "", "sproc" => "usebb,phpbb,bblower"),
		array("srcdata" => "forum_id", "e107" => "thread_forum_id", "type" => "INT"),
		array("srcdata" => "post_time", "e107" => "thread_datestamp", "type" => "INT"),
		array("srcdata" => "topic_views", "e107" => "thread_views", "type" => "INT"),
		array("srcdata" => "post_time", "e107" => "thread_lastpost", "type" => "INT"),
		array("srcdata" => "poster_id", "e107" => "thread_user", "type" => "STRING"),
		array("srcdata" => "post_subject", "e107" => "thread_name", "type" => "STRING"),
		array("srcdata" => "null", "e107" => "thread_parent", "type" => "INT", "value" => $parent_id),
	);
	return $postArray;
}


/*
-- --------------------------------------------------------
PHPBB uses three tables to record a poll. Looks wildly different to E107!
-- 
-- Table structure for table `_phpbb_vote_desc`
CREATE TABLE `_phpbb_vote_desc` (
  `vote_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_text` text NOT NULL,
  `vote_start` int(11) NOT NULL default '0',
  `vote_length` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;


-- 
-- Table structure for table `_phpbb_vote_results`
CREATE TABLE `_phpbb_vote_results` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_option_id` tinyint(4) unsigned NOT NULL default '0',
  `vote_option_text` varchar(255) NOT NULL default '',
  `vote_result` int(11) NOT NULL default '0',
  KEY `vote_option_id` (`vote_option_id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- 
-- Table structure for table `_phpbb_vote_voters`
CREATE TABLE `_phpbb_vote_voters` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_user_id` mediumint(8) NOT NULL default '0',
  `vote_user_ip` char(8) NOT NULL default '',
  KEY `vote_id` (`vote_id`),
  KEY `vote_user_id` (`vote_user_id`),
  KEY `vote_user_ip` (`vote_user_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

*/


/*

//-----------------------------------------------------------
// ### get phpbb categories and insert them as forum parents
//-----------------------------------------------------------

mysql_query("TRUNCATE TABLE {$mySQLprefix}forum", $e107Connection);


$phpbb_res = mysql_query("SELECT * FROM {$phpbb2Prefix}categories", $phpbbConnection);
if(!$phpbb_res)
{
	goError("Error! Unable to access ".$phpbb2Prefix."categories table.");
}

$catcount = 500;
while($parent = mysql_fetch_array($phpbb_res))
{

	$parentArray = convertParents($catcount);
	
	$query = createQuery($parentArray, $parent, $mySQLprefix."forum");	
	echo (mysql_query($query, $e107Connection) ? "Successfully inserted parent: ".$parent['cat_id'].": ".$parent['cat_title'] : "Unable to insert parent: ".$parent['cat_id'].": ".$parent['cat_title']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
	flush();

	$phpbb_res2 = mysql_query("SELECT * FROM {$phpbb2Prefix}forums WHERE cat_id = ".$parent['cat_id'], $phpbbConnection);
	if($phpbb_res2)
	{
		while($forum = mysql_fetch_array($phpbb_res2))
		{
			$forumArray = convertForums($catcount);
			$query = createQuery($forumArray, $forum, $mySQLprefix."forum");
			echo (mysql_query($query, $e107Connection) ? "Successfully inserted forum: ".$parent['cat_id'].": ".$parent['cat_title'] : "Unable to insert forum: ".$parent['cat_id'].": ".$parent['cat_title']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
			flush();
		}
	}
	else
	{
		echo "Didn't find any forums for parent '".$parent['cat_title']."'<br />";
	}
	$catcount ++;
}


//------------------------------------------------------
//          Read in forum topics
//------------------------------------------------------

mysql_query("TRUNCATE TABLE {$mySQLprefix}forum_t", $e107Connection);
mysql_query("TRUNCATE TABLE {$mySQLprefix}polls", $e107Connection);

$query = "SELECT * FROM {$phpbb2Prefix}topics
LEFT JOIN {$phpbb2Prefix}posts_text ON ({$phpbb2Prefix}topics.topic_title = {$phpbb2Prefix}posts_text.post_subject)
LEFT JOIN {$phpbb2Prefix}posts ON ({$phpbb2Prefix}posts.post_id = {$phpbb2Prefix}posts_text.post_id)
ORDER BY topic_time ASC";

$phpbb_res = mysql_query($query, $phpbbConnection);
if(!$phpbb_res)
{
	goError("Error! Unable to access ".$phpbb2Prefix."topics table.");
}
while($topic = mysql_fetch_array($phpbb_res))
{

	//echo "<pre>"; print_r($topic); echo "</pre>";

	if($topic['topic_vote'])
	{
		// poll attached to this topic ...
		$topic['topic_title'] = "[poll] ".$topic['topic_title'];
		$query = "SELECT * FROM {$phpbb2Prefix}vote_desc WHERE topic_id=".$topic['topic_id'];
		$phpbb_res3 = mysql_query($query, $phpbbConnection);
		$pollQ = mysql_fetch_array($phpbb_res3);

		$query = "SELECT * FROM {$phpbb2Prefix}vote_results WHERE vote_id=".$pollQ['vote_id'];
		$phpbb_res3 = mysql_query($query, $phpbbConnection);
		$options = "";
		$votes = "";
		while($pollO = mysql_fetch_array($phpbb_res3))
		{
			$options .= $pollO['vote_option_text'].chr(1);
			$votes .= $pollO['vote_result'].chr(1);
		}

		extract($pollQ);
		$vote_text = $tp->toDB($vote_text);		// McFly added 25/5/06
        $options = $tp->toDB($options);			// McFly added 25/5/06
		$query = "INSERT INTO ".$mySQLprefix."polls VALUES ('0', {$vote_start}, {$vote_start}, 0, 0, '{$vote_text}', '{$options}', '{$votes}', '', 2, 0, 0, 0, 255, 0)";
		echo (mysql_query($query, $e107Connection) ? "Poll successfully inserted" : "Unable to insert poll ({$query})")."<br />";
	}


	if($topic['topic_poster'] == 2)
	{
		$topic['topic_poster'] = 1;
	}

	if($topic['topic_poster'] == -1)
	{
		$poster = ($topic['post_username'] ? $topic['post_username'] : "Anonymous");
		$topic['topic_poster'] = "0.".$poster; 		// McFly moved, edited 25/5/06
	}

	$topicArray = convertTopics();					// McFly edited 25/5/06
	$query = createQuery($topicArray, $topic, $mySQLprefix."forum_t");

	if(!mysql_query($query, $e107Connection))
	{
		echo "Unable to insert topic: ".$topic['topic_id']."<br />";
		flush();
	}
	else
	{
		echo "Successfully inserted topic: ".$topic['topic_id']."<br />";
		flush();
		$parent_id = mysql_insert_id();
		$topic_id = $topic['topic_id'];

		//echo "PARENT: $parent_id, TOPIC: $topic_id<br />"; 

// Not checking post_subject might work better
		$query = "SELECT * FROM {$phpbb2Prefix}posts LEFT JOIN {$phpbb2Prefix}posts_text ON ({$phpbb2Prefix}posts.post_id = {$phpbb2Prefix}posts_text.post_id) WHERE topic_id='{$topic_id}' ORDER BY post_time DESC";
//		$query = "SELECT * FROM {$phpbb2Prefix}posts LEFT JOIN {$phpbb2Prefix}posts_text ON ({$phpbb2Prefix}posts.post_id = {$phpbb2Prefix}posts_text.post_id) WHERE topic_id='{$topic_id}' AND post_subject = '' ORDER BY post_time DESC";
		$phpbb_res2 = mysql_query($query, $phpbbConnection);
		if(!$phpbb_res2)
		{
			goError("Error! Unable to access ".$phpbb2Prefix."posts / ".$phpbb2Prefix."posts_text table.");
		}
		while($post = mysql_fetch_array($phpbb_res2))
		{
			
			if($post['poster_id'] == 2)
			{
				$post['poster_id'] = 1;
			}
			if($post['poster_id'] == -1)
			{
				$poster = ($post['post_username'] ? $post['post_username'] : "Anonymous");
				$post['poster_id'] = "0.".$poster;		// McFly moved, edited 25/5/06
			}
	

			$postArray = convertForumPosts($parent_id, $poster);
			$query = createQuery($postArray, $post, $mySQLprefix."forum_t",$mapdata);	
			echo (mysql_query($query, $e107Connection) ? "Successfully inserted thread: ".$post['post_id'] : "Unable to insert thread: ".$parent['cat_id'].": ".$parent['cat_title']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
			flush();
		}
	}
}

*/

?>
