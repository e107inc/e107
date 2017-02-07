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
	public $description		= 'Supports users only';
	public $supported		= array('users'); // add news and page to test.
	public $mprefix			= 'nuke_';

	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		if ($this->ourDB == NULL) return FALSE;
		switch ($task)
		{
		  case 'users' :
		    $result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}users WHERE `user_active`=1");
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
		$target['user_hideemail']   = $source['user_viewemail'];
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

		return $target;


	// Php Nuke Field Reference.
        $source['user_id'];
		$source['name'];
		$source['username'];
		$source['user_email'];
		$source['femail'];
		$source['user_website'];
		$source['user_avatar'];
		$source['user_regdate'];
		$source['user_icq'];
		$source['user_occ'];
		$source['user_from'];
		$source['user_interests'];
		$source['user_sig'];
		$source['user_viewemail'];
		$source['user_theme'];
		$source['user_aim'];
		$source['user_yim'];
		$source['user_msnm'];
		$source['user_password'];
		$source['storynum'];
		$source['umode'];
		$source['uorder'];
		$source['thold'];
		$source['noscore'];
		$source['bio'];
		$source['ublockon'];
		$source['ublock'];
		$source['theme'];
		$source['commentmax'];
		$source['counter'];
		$source['newsletter'];
		$source['user_posts'];
		$source['user_attachsig'];
		$source['user_rank'];
		$source['user_level'];
		$source['broadcast'];
		$source['popmeson'];
		$source['user_active'];
		$source['user_session_time'];
		$source['user_session_page'];
		$source['user_lastvisit'];
		$source['user_timezone'];
		$source['user_style'];
		$source['user_lang'];
		$source['user_dateformat'];
		$source['user_new_privmsg'];
		$source['user_unread_privmsg'];
		$source['user_last_privmsg'];
		$source['user_emailtime'];
		$source['user_allowhtml'];
		$source['user_allowbbcode'];
		$source['user_allowsmile'];
		$source['user_allowavatar'];
		$source['user_allow_pm'];
		$source['user_allow_viewonline'];
		$source['user_notify'];
		$source['user_notify_pm'];
		$source['user_popup_pm'];
		$source['user_avatar_type'];
		$source['user_sig_bbcode_uid'];
		$source['user_actkey'];
		$source['user_newpasswd'];
		$source['points'];
		$source['last_ip'];
		$source['karma'];




	    // old data.



		if ($this->copyUserInfo) $target['user_id'] = $source['user_id'];
		$target['user_name'] = $source['username'];
		$target['user_loginname'] = $source['username'];
		$target['user_loginname'] = $source['name'];
		$target['user_password'] = $source['user_password'];
		$target['user_join'] = strtotime($source['user_regdate']);
		$target['user_email'] = $source['user_email'];
		$target['user_hideemail'] = $source['user_viewemail'];
	    $target['user_image'] = $source['user_avatar'];
		$target['user_signature'] = $source['user_sig'];
		$target['user_forums'] = $source['user_posts'];
		$target['user_lastvisit'] = $source['user_lastvisit'];
	    $target['user_image'] = $source['user_avatar'];

		$target['user_timezone'] = $source['user_timezone'];		// source is decimal(5,2)
		$target['user_language'] = $source['user_lang'];			// May need conversion
		$target['user_location'] = $source['user_from'];
		$target['user_icq'] = $source['user_icq'];
		$target['user_aim'] = $source['user_aim'];
		$target['user_yahoo'] = $source['user_yim'];
		$target['user_msn'] = $source['user_msnm'];
		$target['user_homepage'] = $source['user_website'];
		$target['user_ip'] = $source['last_ip'];
	//	$target['user_'] = $source[''];

	//	$source['user_rank'];
	//	$target['user_admin'] = ($source['user_level'] == 1) ? 1 : 0;		// Guess
	//	if ($target['user_admin'] != 0) $target['user_perms'] = '0.';
	//	$target['user_ban'] = ($source['ublockon'] ? 2 : 0);					// Guess

  }




		/**
	 * Align source data with e107 News Table
	 * @param $target array - default e107 target values for e107_news table.
	 * @param $source array - RSS data
	 */
	function copyNewsData(&$target, &$source)
	{


	//	$target['news_title']					= '';
	//	$target['news_sef']					    = '';
	//	$target['news_body']					= "[html]something[/html]";
		//	$target['news_extended']			= '';
		//$target['news_meta_keywords']			= implode(",",$keywords);
		//	$target['news_meta_description']	= '';
	//		$target['news_datestamp']			= strtotime($source['pubDate'][0]);
		//	$target['news_author']				= $source['post_author'];
		//	$target['news_category']			= '';
		//	$target['news_allow_comments']		= ($source['comment_status']=='open') ? 1 : 0;
		//	$target['news_start']				= '';
		//	$target['news_end']					= '';
		///	$target['news_class']				= '';
		//	$target['news_render_type']			= '';
		//	$target['news_comment_total']		= $source['comment_count'];
		//	$target['news_summary']				= $source['post_excerpt'];
		//	$target['news_thumbnail']			= '';
		//	$target['news_sticky']				= '';

		return $target;  // comment out to debug

	//	$this->renderDebug($source,$target);


	}



	/**
	 * Align source data to e107 Page Table
	 * @param $target array - default e107 target values for e107_page table.
	 * @param $source array - WordPress table data
	 */
	function copyPageData(&$target, &$source)
	{


	// 	$target['page_id']				= $source['ID']; //  auto increment
	//	$target['page_title']			= $source['post_title']);
	//	$target['page_sef']				= $source['post_name'];
	//	$target['page_text']			= (vartrue($source['post_content'])) ? "[html]".$source['post_content']."[/html]" : "";
	//	$target['page_metakeys']		= '';
	//	$target['page_metadscr']		= '';
	//	$target['page_datestamp']		= strtotime($source['post_date']);
	//	$target['page_author']			= $source['post_author'];
	//	$target['page_category']		= '',
	//	$target['page_comment_flag']	= ($source['comment_status']=='open') ? 1 : 0;
	//	$target['page_password']		= $source['post_password'];
	//	$target['page_class']	 = e_UC_ADMIN;

		return $target;  // comment out to debug


	}



}


?>
