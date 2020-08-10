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
	public $sourceType 	= 'db';	
	
	var $catcount = 0;				// Counts forum IDs
	var $id_map = array();			// Map of PHPBB forum IDs ==> E107 forum IDs
	
	private $forum_attachments = array();
	private $forum_attachment_path = null;
	private $forum_moderator_class = false;
	var $helperClass; // forum class. 
 
 
	function init()
	{
		$this->forum_attachment_path	= vartrue(trim($_POST['forum_attachment_path'],"/" ), false);
		
		if($data = e107::getDb('phpbb')->retrieve('userclass_classes','userclass_id',"userclass_name='FORUM_MODERATOR' "))
		{
			$this->forum_moderator_class = $data;
		}
		
	} 
 
 
  
	function config()
	{
		$frm = e107::getForm();
		
		$var[0]['caption']	= "Path to phpBB3 Attachments folder (optional)";
		$var[0]['html'] 	= $frm->text('forum_attachment_path',null,40,'size=xxlarge');
		$var[0]['help'] 	= "Relative to the root folder of your e107 installation";

		return $var;
	}


	function help()
	{
		return "some help text";	
		
	}
  
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
				$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}forums");
				if ($result === FALSE) return FALSE;	  
			break;
				
			case 'forumthread' :
				$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}topics");
				
				if ($result === FALSE) return FALSE;	  
			break;
				
			case 'forumpost' :
								
				if($this->ourDB->gen("SELECT * FROM {$this->DBPrefix}attachments"))
				{
					while($row = $this->ourDB->fetch())
					{
						$id = $row['post_msg_id'];
						$key = $row['physical_filename'];
						$this->forum_attachments[$id][$key] = $row['real_filename'];	
					}
				}
				$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}posts");
				if ($result === FALSE) return FALSE;	  
			break;				

			case 'forumtrack' :
				$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}forums_track");
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
	  
	  
	function convertUserclass($perm='')
	{
		if($perm == '')
		{
			return; 	
		}
				
		$conv = array(1 => e_UC_GUEST, 4 => e_UC_MODS, 6 => e_UC_BOTS, 7 => e_UC_NEWUSER);
		
		if($this->forum_moderator_class !== false)
		{
			$conv[4] = $this->forum_moderator_class;
			$conv[5] = $this->forum_moderator_class;	
		}
		
		
		return vartrue($conv[$perm]) ? $conv[$perm] : "";
		/*
		 * 	1		GUESTS
			2		REGISTERED
			3		REGISTERED_COPPA
			4		GLOBAL_MODERATORS
			5		ADMINISTRATORS
			6		BOTS
			7		NEWLY_REGISTERED
		 * 
		 */
	}  
	  
	function convertUserBan($data)
	{
		if($data == 3) // founder in phpbb3, but 'bounced' in e107. 
		{
			return;	
		}	
		else
		{
			return $data;	
		}
		
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
		$target['user_signature'] 		= $this->convertText($source['user_sig']);
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
		$target['user_ban']				= $this->convertUserBan($source['user_type']);
		$target['user_prefs']			= '';
		$target['user_visits']			= '';
		$target['user_admin']			= ($source['group_id'] == 5 || $source['user_type']==3) ? 1 : 0 ;  //user_type == 3 is 'founder'
		$target['user_login']			= '';
		$target['user_class']			= $this->convertUserclass($source['group_id']);
		$target['user_perms']			= ($source['user_type']==3) ? '0' : '';
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
		$target['forum_replies']			= $source['forum_posts'];
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
		$target['thread_lastpost'] 			= $source['topic_last_post_time'];  	
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
		$target['post_entry'] 				= $this->convertText($source['post_text']);
		$target['post_thread'] 				= $source['topic_id'];
		$target['post_forum'] 				= $source['forum_id'];
	//	$target['post_status'] 				= $source[''];
		$target['post_datestamp'] 			= $source['post_time'];
		$target['post_user'] 				= $source['poster_id'];
		$target['post_edit_datestamp'] 		= $source['post_edit_time'];
		$target['post_edit_user'] 			= $source['post_edit_user'];
		$target['post_ip'] 					= $source['poster_ip'];
	//	$target['post_user_anon'] 			= $source[''];
		$target['post_attachments'] 		= $this->convertAttachment($source);
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

	
	
	function convertAttachment($row)
	{
		
		if($row['post_attachment'] != 1)
		{
			return;
		}
		
		$id = $row['post_id'];
				
		if(isset($this->forum_attachments[$id]))
		{
			$attach = array();
			
			$forum = $this->helperClass; // e107_plugins/forum/forum_class.php 
			
			if($folder = $forum->getAttachmentPath($row['poster_id'],true)) // get Path and create Folder if needed. 
			{
				e107::getMessage()->addDebug("Created Attachment Folder: ".$folder );
			}
			else
			{
				e107::getMessage()->addError("Couldn't find/create attachment folder for user-id: ".$row['poster_id'] );	
			}
			
			foreach($this->forum_attachments[$id] as $file => $name)
			{
				
				if(preg_match('#.JPG|.jpg|.gif|.png|.PNG|.GIF|.jpeg|.JPEG$#',$name))
				{
					$attach['img'][] = $file;	
				}
				else 
				{
					$attach['file'][] = $file;
				}	
				
				if($this->forum_attachment_path) // if path entered - then move the files. 
				{
					$oldpath = e_BASE.$this->forum_attachment_path."/".$file;
					$newpath = $folder.$file;
					
					if(rename($oldpath,$newpath))
					{
						e107::getMessage()->addDebug("Renamed file from <b>{$oldpath}</b> to <b>{$newpath}</b>" );	
					}
					else
					{
						e107::getMessage()->addError("Couldn't rename file from <b>{$oldpath}</b> to <b>{$newpath}</b>" );	
					}	
					
				}
				
			}	
			
		}

		
		return e107::serialize($attach); // set attachments 	
	}



	function convertText($text)
	{		
		$text = preg_replace('#<!-- s(\S*) --><img([^>]*)><!-- s(\S*) -->#','$1',$text);	 					// Smilies to text
		$text = preg_replace('#\[img:([^\]]*)]([^\[]*)\[/img:([^\]]*)]#', '[img]$2[/img]', $text); 				// Image Bbcodes. 
		$text = preg_replace('#<!-- m --><a class="postlink" href="([^>]*)">([^<]*)</a><!-- m -->#','[link=$1]$2[/link]',$text);	 	// links
		$text = preg_replace('#<!-- w --><a class="postlink" href="([^>]*)">([^<]*)</a><!-- w -->#','[link=$1]$2[/link]',$text);	 	// links
		
		$text = preg_replace('#\[attachment([^\]]*)]([^\[]*)\[/attachment:([^\]]*)]#','',$text);

		if(preg_match('#\[/url:([^\]]*)]#',$text, $match)) // strip bbcode hash. 
		{
			$hash = $match[1];
			$text = str_replace($hash,'',$text);
		}
		
		$text 		= html_entity_decode($text,ENT_NOQUOTES,'UTF-8');

		$detected 	= mb_detect_encoding($text); // 'ISO-8859-1'
		$text 		= iconv($detected,'UTF-8',$text);

		return $text;
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
 */ 



