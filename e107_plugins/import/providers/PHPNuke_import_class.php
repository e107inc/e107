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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/PHPNuke_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box

require_once('import_classes.php');

class PHPNuke_import extends base_import_class
{
	
	
	public $title			= 'PHP Nuke 8.2';
	public $description		= '';
	public $supported		= array('users', 'news', 'newscategory'); // add news and page to test.
	public $mprefix			= 'nuke_';

	
  // Set up a query for the specified task.
  // Returns TRUE on success. false on error
	function setupQuery($task, $blank_user=false)
	{
		if ($this->ourDB == NULL) return false;
		switch ($task)
		{
		  case 'users' :
		    $result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}users WHERE `user_active`=1");
			if ($result === false) return false;
			break;

			case 'news' :
				$query =  "SELECT *, UNIX_TIMESTAMP(time) as datestamp FROM {$this->DBPrefix}stories  ORDER BY sid";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;

			case 'newscategory' :
				$query =  "SELECT * FROM {$this->DBPrefix}topics  ORDER BY topicid";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;


			case 'userclass' :
				$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;

			case 'page' :
				$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;

			case 'pagechapter' :
				$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;

			case 'media' :
				$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;

			case 'links':
			 	$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;


		    case 'forum' :
	    		$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
	    	break;

		  	case 'forumthread' :
	    		$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
	    	break;

	  		case 'forumpost' :
	    		$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
	    	break;

		  	case 'forumtrack' :
	    		$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
	    	break;

		  default :
		    return false;
		}

		$this->copyUserInfo = !$blank_user;
		$this->currentTask = $task;
		return TRUE;
	}


  //------------------------------------
  //	Internal functions below here
  //------------------------------------


	/**
	 * Copy User Data to e107.
	 * @param $target e107 user table
	 * @param $source PHPNuke user table
	 * @return mixed
	 */
	function copyUserData(&$target, &$source)
	{


		$target['user_id']          = $source['user_id'];
		$target['user_name']        = $source['name'];
		$target['user_loginname']   = $source['username'];
		$target['user_password']    = $source['user_password']; //MD5
		$target['user_join']        = strtotime($source['user_regdate']);
		$target['user_email']       = $source['user_email'];
		$target['user_hideemail']   = !$source['user_viewemail'];
	    $target['user_image']       = $source['user_avatar'];
		$target['user_signature']   = $source['user_sig'];
		$target['user_forums']      = $source['user_posts'];
		$target['user_lastvisit']   = $source['user_lastvisit'];
	    $target['user_image']       = $source['user_avatar'];

		// Extended fields.

		$target['user_timezone']    = $source['user_timezone'];		// source is decimal(5,2)
		$target['user_language']    = $source['user_lang'];			// May need conversion
		$target['user_location']    = $source['user_from'];
		$target['user_icq']         = $source['user_icq'];
		$target['user_aim']         = $source['user_aim'];
		$target['user_yahoo']       = $source['user_yim'];
		$target['user_msn']         = $source['user_msnm'];
		$target['user_homepage']    = $source['user_website'];
		$target['user_ip']          = $source['last_ip'];

		return $target; // comment out to debug.

		$this->debug($source,$target);







  }




	/**
	 * Align source data with e107 News Table
	 * @param $target array - default e107 target values for e107_news table.
	 * @param $source array - other cms table data
	 */
	function copyNewsData(&$target, &$source)
	{

		$target['news_id']				    = (int) $source['sid'];
		$target['news_title']				= $source['title'];
		$target['news_sef']					= '';
		$target['news_body']				= "[html]".$source['hometext']."[/html]";
		$target['news_extended']			= "[html]".$source['bodytext']."[/html]";
		$target['news_meta_keywords']		= '';
		$target['news_meta_description']	= '';
		$target['news_datestamp']			= $source['datestamp'];
		$target['news_author']				= $source[''];
		$target['news_category']			= (int) $source['topic'];
		$target['news_allow_comments']		= (int) $source['acomm'];
		$target['news_start']				= '';
		$target['news_end']					= '';
	//	$target['news_class']				= '';
	//	$target['news_render_type']			= '';
		$target['news_comment_total']		= $source['comments'];
//		$target['news_summary']				= $source[''];
		$target['news_thumbnail']			= '';
		$target['news_sticky']				= '';

		return $target;  // comment out to debug.

		$this->debug($source,$target);

	}


	/**
	 * Align source data with e107 News Table
	 * @param $target array - default e107 target values for e107_news table.
	 * @param $source array - other cms table data
	 */
	function copyNewsCategoryData(&$target, &$source)
	{
		$target['category_id']                  = (int) $source['topicid'];
		$target['category_name']                = $source['topictext'];
		$target['category_sef']                 = eHelper::title2sef($source['topicname'],'dashl');
	//	$target['category_meta_description']    = $source[''];
	//	$target['category_meta_keywords']       = $source[''];
		$target['category_manager']			    = e_UC_ADMIN;
		$target['category_icon']                = $source['topicimage'];
	//	$target['category_order']               = $source[''];

		return $target;  // comment out to debug.

		$this->debug($source,$target);

	}



	/**
	 * Align source data to e107 Page Table
	 * @param $target array - default e107 target values for e107_page table.
	 * @param $source array - other cms table data
	 */
	function copyPageData(&$target, &$source)
	{


		$target['page_id']				= $source[''];
		$target['page_title']			= $source[''];
		$target['page_sef']				= $source[''];
		$target['page_text']			= $source[''];
		$target['page_metakeys']		= $source[''];
		$target['page_metadscr']		= $source[''];
		$target['page_datestamp']		= $source[''];
		$target['page_author']			= $source[''];
		$target['page_category']		= $source[''];
		$target['page_comment_flag']	= $source[''];
		$target['page_password']		= $source[''];
		$target['page_class']	        = $source[''];

	//	return $target;  // comment out to debug.

		$this->debug($source,$target);

	}




 	/**
	 * $target - e107_forum table
	 * $source - other cms
	 */
	function copyForumData(&$target, &$source)
	{

		$target['forum_id'] 				= $source[''];
		$target['forum_name'] 				= $source[''];
		$target['forum_description'] 		= $source[''];
		$target['forum_parent']				= $source[''];
		$target['forum_sub']				= $source[''];
		$target['forum_datestamp']			= $source[''];
		$target['forum_moderators']			= $source[''];

		$target['forum_threads'] 			= $source[''];
		$target['forum_replies']			= $source[''];
		$target['forum_lastpost_user']		= $source[''];
		$target['forum_lastpost_user_anon']	= $source[''];
		$target['forum_lastpost_info']		= $source[''];
		$target['forum_class']				= $source[''];
		$target['forum_order']				= $source[''];
		$target['forum_postclass']	        = $source[''];
		$target['forum_threadclass']	    = $source[''];
		$target['forum_options']	        = $source[''];
		$target['forum_sef']                = $source[''];

	//	return $target; // comment out to debug.

		$this->debug($source,$target);


	}


	/**
	 * $target - e107 forum_threads
	 * $source - other cms
	 */
	function copyForumThreadData(&$target, &$source)
	{

		$target['thread_id'] 				= $source[''];
		$target['thread_name'] 				= $source[''];
		$target['thread_forum_id'] 			= $source[''];
		$target['thread_views'] 			= $source[''];
		$target['thread_active'] 			= $source[''];
		$target['thread_lastpost'] 			= $source[''];
		$target['thread_sticky'] 			= $source[''];
		$target['thread_datestamp'] 		= $source[''];
		$target['thread_user'] 				= $source[''];
		$target['thread_user_anon'] 		= $source[''];
		$target['thread_lastuser'] 			= $source[''];
		$target['thread_lastuser_anon'] 	= $source[''];
		$target['thread_total_replies'] 	= $source[''];
		$target['thread_options'] 			= $source[''];

	//	return $target; // comment out to debug.

		$this->debug($source,$target);

	}


	/**
	 * $target - e107_forum_post table
	 * $source - other cms
	 */
	function copyForumPostData(&$target, &$source)
	{

		$target['post_id'] 					= $source[''];
		$target['post_entry'] 				= $source[''];
		$target['post_thread'] 				= $source[''];
		$target['post_forum'] 				= $source[''];
		$target['post_status'] 				= $source[''];
		$target['post_datestamp'] 			= $source[''];
		$target['post_user'] 				= $source[''];
		$target['post_edit_datestamp'] 		= $source[''];
		$target['post_edit_user'] 			= $source[''];
		$target['post_ip'] 					= $source[''];
		$target['post_user_anon'] 			= $source[''];
		$target['post_attachments'] 		= $source[''];
		$target['post_options'] 			= $source[''];


	//	return $target; // comment out to debug.

		$this->debug($source,$target);
	}






}



