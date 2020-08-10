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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/PHPFusion_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box

require_once('import_classes.php');

class PHPFusion_import extends base_import_class
{
	
	public $title		= 'PHP Fusion';
	public $description	= 'Based on v9';
	public $supported	=  array('users', 'userclass');
	public $mprefix		= 'fusion_';
	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
	    if ($this->ourDB == NULL) return FALSE;
	    switch ($task)
		{
		  case 'users' :
		    $result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}users");
			if ($result === FALSE) return FALSE;
			break;


		 case 'userclass' :
		    $result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}user_groups");
			if ($result === FALSE) return FALSE;
			break;


		  default :
		    return FALSE;
		}
		$this->copyUserInfo = !$blank_user;
		$this->currentTask = $task;
		return TRUE;
	}


  //------------------------------------
  //	Internal functions below here
  //------------------------------------
  
  // Copy data read from the DB into the record to be returned.
	  function copyUserData(&$target, &$source)
	  {
	    if($source['user_algo'] === 'sha256')
		{
			$hashType = '$5$';
		}
		else
		{
		    $hashType = '';
		}

		if ($this->copyUserInfo) $target['user_id'] = $source['user_id'];
		$target['user_name'] = $source['user_name'];
		$target['user_loginname'] = $source['user_name'];
		$target['user_password'] = $source['user_password'];
		$target['user_email'] = $source['user_email'];
		$target['user_hideemail'] = $source['user_hide_email'];
	    $target['user_image'] = $source['user_avatar'];
		$target['user_signature'] = $source['user_sig'];
		$target['user_forums'] = $source['user_posts'];
		$target['user_join'] = $source['user_joined'];
		$target['user_lastvisit'] = $source['user_lastvisit'];
		$target['user_location'] = $source['user_location'];
		$target['user_birthday'] = $source['user_birthdate'];
		$target['user_aim'] = $source['user_aim'];
		$target['user_icq'] = $source['user_icq'];
		$target['user_msn'] = $source['user_msn'];
		$target['user_yahoo'] = $source['user_yahoo'];
		$target['user_homepage'] = $source['user_web'];
		$target['user_timezone'] = $source['user_offset'];		// guess - may need conversion
		$target['user_ip'] = $source['user_ip'];
	//	$target['user_'] = $source[''];
	//	$target['user_'] = $source[''];

	//	$target['user_ban'] = ($source['user_status'] ? 2 : 0);					// Guess



		//return $target;

		$this->debug($source,$target);
	  }





		/**
	 * Align source data to e107 User Table
	 * @param $target array - default e107 target values for e107_user table.
	 * @param $source array - WordPress table data
	 */
	function copyUserClassData(&$target, &$source)
	{

		$target['userclass_id']             = $source['group_id'];
		$target['userclass_name']           = $source['group_name'];
		$target['userclass_description']    = $source['group_description'];
		$target['userclass_editclass']      = e_UC_ADMIN;
		$target['userclass_parent']         = 0;
		$target['userclass_accum']          = '';
		$target['userclass_visibility']     = e_UC_ADMIN;
		$target['userclass_type']           = '';
		$target['userclass_icon']           = '';
		$target['userclass_perms']          = '';

	//	return $target;

		$this->debug($source,$target);

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








}


