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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/e107_import_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// This must be an incredibly pointless file! But it does allow testing of the basic plugin structure.

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box
//$import_class_names['e107_import'] = 'E107';
//$import_class_comment['e107_import'] = 'Reads 0.7 and 0.8 version files';
//$import_class_support['e107_import'] = array('users');
//$import_default_prefix['e107_import'] = 'e107_';


require_once('import_classes.php');

class e107_import extends base_import_class
{
	
	
	public $title		= 'e107';
	public $description	= 'Reads 0.7 and 0.8 version files';
	public $supported	= array('users', 'page', 'pagechapter');
	public $mprefix		= 'e107_';
	
	function init()
	{

		$this->pcontent	= intval($_POST['pcontent']);

	}


	function config()
	{
		$frm = e107::getForm();

		$present = e107::getDb()->isTable('pcontent');

		$var[0]['caption']	= "Use old 'Content Management' tables for Pages";
		$var[0]['html'] 	= $frm->radio_switch('pcontent',$present);
	//	$var[0]['help'] 	= "Change the author of the news items";

	//	$var[1]['caption']	= "Include revisions";
	//	$var[1]['html'] 	= $frm->checkbox('news_revisions',1);
	//	$var[1]['help'] 	= "Change the author of the news items";

		return $var;
	}
	
  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
	    if ($this->ourDB == NULL) return FALSE;
	    switch ($task)
		{
		    case 'users' :
		  	    $query =  "SELECT * FROM {$this->DBPrefix}user WHERE `user_id` != 1";
		        $result = $this->ourDB->db_Select_gen($query);
	
				if ($result === false) return false;
			break;

		    case 'page' :
		  	    $query =  "SELECT * FROM {$this->DBPrefix}pcontent WHERE `content_parent` > 0";
		        $result = $this->ourDB->gen($query);

				if ($result === false) return false;
			break;

		    case 'pagechapter' :
		  	    $query =  "SELECT * FROM {$this->DBPrefix}pcontent WHERE `content_parent` = '0'";
		        $result = $this->ourDB->gen($query);

				if ($result === false) return false;
			break;

	
			
		  default :
		    return FALSE;
		}
		$this->copyUserInfo = !$blank_user;
		$this->currentTask = $task;
		
		return TRUE;
  }


	/**
	 * Align source data to e107 Page Table
	 * @param $target array - default e107 target values for e107_page table.
	 * @param $source array - WordPress table data
	 */
	function copyPageData(&$target, &$source)
	{



		// 	$target['page_id']				= $source['ID']; //  auto increment
			$target['page_title']			= $source['content_heading'];
			$target['page_sef']				= eHelper::title2sef($source['content_heading'], 'dashl');
			$target['page_text']			= $this->checkHtml($source['content_text']) ;
			$target['page_chapter']		    = $source['content_parent'];
		//	$target['page_metakeys']		= '';
			$target['page_metadscr']		= $source['content_summary'];
			$target['page_datestamp']		= $source['content_datestamp'];
			$target['page_author']			= (int) $source['content_author'];
		//	$target['page_category']		= '',
			$target['page_comment_flag']	= (int) $source['content_comment'];
			$target['page_rating_flag']     = (int) $source['content_rate'];
		//	$target['page_password']		= $source['post_password'];
			$target['page_order']           = (int) $source['content_order'];
			$target['page_class']	        = (int) $source['content_class'];



		return $target;  // comment out to debug


	}


	private function checkHtml($text)
	{
		$tp = e107::getParser();
		if($tp->isHtml($text) && strpos($text,'[html]')!==0)
		{
			return "[html]".$text."[/html]";
		}

		return $text;

	}

	/**
	 * Align source data to e107 Page Table
	 * @param $target array - default e107 target values for e107_page table.
	 * @param $source array - WordPress table data
	 */
	function copyPageChapterData(&$target, &$source)
	{
		$target['chapter_id']                   = $source['content_id'];
		$target['chapter_parent']               = empty($source['content_parent']) ? 1 : (int) $source['content_parent'];
		$target['chapter_name']                 = $source['content_heading'];
		$target['chapter_sef']                  = eHelper::title2sef($source['content_heading'], 'dashl');
		$target['chapter_meta_description']     = $source['content_text'];
		$target['chapter_meta_keywords']        = '';
		// $target['chapter_manager']         = '';
		$target['chapter_icon']                 = $source['content_icon'];
		$target['chapter_order']                = 0;
		// $target['chapter_template']        = '';
		// $target['chapter_visibility']      = 0;
		// $target['chapter_fields']          = '';

		return $target;  // comment out to debug


	}





  //------------------------------------
  //	Internal functions below here
  //------------------------------------
  
  // Copy data read from the DB into the record to be returned.
	function copyUserData(&$target, &$source)
	{
		if ($this->copyUserInfo)
		{
			$target['user_id'] = $source['user_id'];
			$target['user_name'] = $source['user_name'];
			$target['user_loginname'] = $source['user_loginname'];
			$target['user_password'] = $source['user_password'];
			$target['user_email'] = $source['user_email'];
			$target['user_hideemail'] = $source['user_hideemail'];
			$target['user_join'] = $source['user_join'];
			$target['user_admin'] = $source['user_admin'];
			$target['user_lastvisit'] = $source['user_lastvisit'];
			$target['user_login'] = $source['user_login'];
			$target['user_ban'] = $source['user_ban'];
			$target['user_customtitle'] = $source['user_customtitle'];
			$target['user_sess'] = $source['user_sess'];			// Photo
			$target['user_signature'] = $source['user_signature'];
			$target['user_image'] = $source['user_image'];			// Avatar
			$target['user_currentvisit'] = $source['user_currentvisit'];
			$target['user_lastpost'] = $source['user_lastpost'];
			$target['user_chats'] = $source['user_chats'];
			$target['user_comments'] = $source['user_comments'];
		//	$target['user_forums'] = $source['user_forums'];
			$target['user_ip'] = $source['user_ip'];
			$target['user_prefs'] = $source['user_prefs'];
		 //	$target['user_viewed'] = $source['user_viewed'];
			$target['user_visits'] = $source['user_visits'];
			$target['user_class'] = $source['user_class'];
			$target['user_perms'] = $source['user_perms'];
			$target['user_xup'] = $source['user_xup'];
			$target['user_language'] = $source['user_language'];
			$target['user_country'] = $source['user_country'];
			$target['user_location'] = $source['user_location'];
			$target['user_aim'] = $source['user_aim'];
			$target['user_icq'] = $source['user_icq'];
			$target['user_yahoo'] = $source['user_yahoo'];
			$target['user_msn'] = $source['user_msn'];
			$target['user_homepage'] = $source['user_homepage'];
			$target['user_birthday'] = $source['user_birthday'];
			$target['user_timezone'] = $source['user_timezone'];
			
			return $target;
		}
	}
}


?>