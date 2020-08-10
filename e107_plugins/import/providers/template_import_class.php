<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2017 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


require_once('import_classes.php');

/**
* @usage replace 'template' with the name of the other cms and rename this file.
*/
class template_import extends base_import_class
{
	public $title		= 'Template';
	public $description	= 'Import Users, News, Content and Links';
	public $supported	= array('users','news','page','links'); // import methods which are completed. 
	public $mprefix		= 'template_';	// default prefix used by other CMS.

	private $myparam    = false; // custom param. 

	
	function init()
	{
		// check $_POST; from config() if required. 
		
		$this->myparam	= intval($_POST['news_author']);
		
	} 	
	
	function config()
	{

		$frm = e107::getForm();
		
		$mylist = array(1=>'Param 1', 2=>'Param 2');
		
		$var[0]['caption']	= "Optional Parameter";
		$var[0]['html'] 	= $frm->select('myparam',$mylist);
		$var[0]['help'] 	= "Change the author of the news items";
		
	//	$var[1]['caption']	= "Include revisions";
	//	$var[1]['html'] 	= $frm->checkbox('news_revisions',1);
	//	$var[1]['help'] 	= "Change the author of the news items";

		return $var;
	}	
			

	/**
	 * Set up a query for the specified task.
	 * @param $task
	 * @param bool|false $blank_user
	 * @return bool TRUE on success. false on error
	 */
	function setupQuery($task, $blank_user=false)
	{
    	if ($this->ourDB == NULL) return false;
		
    	switch ($task)
		{
	  		case 'users' :
	  			$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			
			break;

			case 'userclass' :
				$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;
		
			case 'news' :
				$query =  "SELECT * FROM {$this->DBPrefix}mytable ORDER BY my_id";
				$result = $this->ourDB->gen($query);
				if ($result === false) return false;
			break;

			case 'newschapter' :
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
	 * Align source data to e107 User Table 
	 * @param $target array - default e107 target values for e107_user table. 
	 * @param $source array - WordPress table data
	 */
	function copyUserData(&$target, &$source)
	{
		$target['user_name'] 		    = $source[''];
		$target['user_loginname'] 	    = $source[''];
		$target['user_password']    	= $source[''];
		$target['user_email'] 		    = $source[''];
		$target['user_hideemail'] 	    = $source[''];
		$target['user_join'] 		    = $source[''];
		$target['user_admin'] 		    = $source[''];
		$target['user_lastvisit'] 	    = $source[''];
		$target['user_login'] 		    = $source[''];
		$target['user_ban'] 		    = $source[''];
		$target['user_customtitle']     = $source[''];
		$target['user_sess'] 		    = $source[''];	// Photo
		$target['user_signature'] 	    = $source[''];
		$target['user_image'] 		    = $source[''];	// Avatar
		$target['user_currentvisit']    = $source[''];
		$target['user_lastpost'] 	    = $source[''];
		$target['user_chats'] 		    = $source[''];
		$target['user_comments'] 	    = $source[''];
	
		$target['user_ip'] 			    = $source[''];
		$target['user_prefs'] 		    = $source[''];
		$target['user_visits'] 		    = $source[''];
		$target['user_class'] 		    = $source[''];
		$target['user_perms'] 		    = $source[''];
		$target['user_xup'] 		    = $source[''];
		$target['user_language'] 	    = $source[''];
		$target['user_country'] 	    = $source[''];
		$target['user_location'] 	    = $source[''];
		$target['user_aim'] 		    = $source[''];
		$target['user_icq'] 		    = $source[''];
		$target['user_yahoo'] 		    = $source[''];
		$target['user_msn'] 		    = $source[''];
		$target['user_homepage'] 	    = $source[''];
		$target['user_birthday'] 	    = $source[''];
		$target['user_timezone'] 	    = $source[''];

		$this->debug($source,$target);
		
		//return $target;
	}

	/**
	 * Align source data to e107 User Table
	 * @param $target array - default e107 target values for e107_user table.
	 * @param $source array - WordPress table data
	 */
	function copyUserClassData(&$target, &$source)
	{

		$target['userclass_id']             = $source[''];
		$target['userclass_name']           = $source[''];
		$target['userclass_description']    = $source[''];
		$target['userclass_editclass']      = $source[''];
		$target['userclass_parent']         = $source[''];
		$target['userclass_accum']          = $source[''];
		$target['userclass_visibility']     = $source[''];
		$target['userclass_type']           = $source[''];
		$target['userclass_icon']           = $source[''];
		$target['userclass_perms']          = $source[''];

		return $target;

	}


	/**
	 * Align source data with e107 News Table
	 * @param $target array - default e107 target values for e107_news table.
	 * @param $source array - other cms table data
	 */
	function copyNewsData(&$target, &$source)
	{

		$target['news_id']				    = $source['']; // leave empty to auto-increment.
		$target['news_title']				= $source[''];
		$target['news_sef']					= $source[''];
		$target['news_body']				= $source['']; // wrap in [html] tags if required.
		$target['news_extended']			= $source['']; // wrap in [html] tags if required.
		$target['news_meta_keywords']		= $source[''];
		$target['news_meta_description']	= $source[''];
		$target['news_datestamp']			= $source['datestamp'];
		$target['news_author']				= $source[''];
		$target['news_category']			= $source[''];
		$target['news_allow_comments']		= $source[''];
		$target['news_start']				= $source[''];
		$target['news_end']					= $source[''];
		$target['news_class']				= $source[''];
		$target['news_render_type']			= $source[''];
		$target['news_comment_total']		= $source[''];
		$target['news_summary']				= $source[''];
		$target['news_thumbnail']			= $source[''];
		$target['news_sticky']				= $source[''];

		return $target;  // comment out to debug

	//	$this->renderDebug($source,$target);


	}


	/**
	 * Align source data with e107 News Table
	 * @param $target array - default e107 target values for e107_news table.
	 * @param $source array - other cms table data
	 */
	function copyNewsCategoryData(&$target, &$source)
	{
		$target['category_id']                  = $source[''];
		$target['category_name']                = $source[''];
		$target['category_sef']                 = $source[''];
		$target['category_meta_description']    = $source[''];
		$target['category_meta_keywords']       = $source[''];
		$target['category_manager']			    = $source[''];
		$target['category_icon']                = $source[''];
		$target['category_order']               = $source[''];

		return $target;  // comment out to debug

	}



	/**
	 * Align source data to e107 Page Table
	 * @param $target array - default e107 target values for e107_page table.
	 * @param $source array - other cms table data
	 */
	function copyPageData(&$target, &$source)
	{


		$target['page_id']				    = $source[''];
		$target['page_title']			    = $source[''];
		$target['page_sef']				    = $source[''];
		$target['page_text']			    = $source[''];
		$target['page_metakeys']		    = $source[''];
		$target['page_metadscr']		    = $source[''];
		$target['page_datestamp']		    = $source[''];
		$target['page_author']			    = $source[''];
		$target['page_category']		    = $source[''];
		$target['page_comment_flag']	    = $source[''];
		$target['page_password']		    = $source[''];
		$target['page_class']	            = $source[''];

		return $target;  // comment out to debug

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

		return $target;


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

		return $target;

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


		return $target;

		$this->debug($source,$target);

	}

	/**
	 * Align source data to e107 Links Table 
	 * @param $target array - default e107 target values for e107_links table. 
	 * @param $source array - WordPress table data
	 */
	function copyLinksData(&$target, &$source)
	{

		/* e107 Link Targets.
		0 => LCLAN_20, // 0 = same window
		1 => LCLAN_23, // new window
		4 => LCLAN_24, // 4 = miniwindow  600x400
		5 => LINKLAN_1 // 5 = miniwindow  800x600
		*/
		
		$target['link_id']			    = $source[''];	  // leave blank to auto-increment
		$target['link_name']			= $source[''];
		$target['link_url']				= $source[''];
		$target['link_description']		= $source['']; // wrap with [html] [/html] if necessary.
		$target['link_button']			= $source['']; // image file.
		$target['link_category']		= $source['']; // integer
		$target['link_order']			= $source['']; // integer
		$target['link_parent']			= $source['']; // integer
		$target['link_open']			= $source['']; // link_target
		$target['link_class']			= $source[''];
		$target['link_sefurl']			= $source[''];




		return $target;  // comment out to debug

		$this->debug($source,$target);

		
	}


	/**
	 * Custom Method if needed.
	 * @param $text
	 * @return string
	 */
	private function convertText($text)
	{
		//$text = e107::getParser()->toDb($text);
		return $text;
					
		$text 		= html_entity_decode($text,ENT_QUOTES,'UTF-8');

		$detected 	= mb_detect_encoding($text); // 'ISO-8859-1'
		$text 		= iconv($detected,'UTF-8',$text);

		

		return $text;
	}

	


}


