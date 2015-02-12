<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */
// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables


require_once('import_classes.php');

class drupal_import extends base_import_class
{
	
	public $title		= 'Drupal';
	public $description	= 'Basic import';
	public $supported	= array('users'); // array('users', 'news','page','links'); //XXX Modify to enable copyNewsData() etc. 
	public $mprefix		= false;
	
	private $version 	= null;
	private $baseUrl 	= null;
	private $basePath 	= null; 
	

	function init()
	{
		if(!empty($_POST['version']))
		{
			$this->version	= $_POST['version'];	
		}
		
		if(!empty($_POST['baseUrl']))
		{
			$this->baseUrl	=$_POST['baseUrl'];	
		}
		
		if(!empty($_POST['basePath']))
		{
			$this->basePath	= $_POST['basePath']; 	
		}
		
		if(!empty($_POST))
		{
			e107::getMessage()->addDebug(print_a($_POST, true)); 
		}
	}
	
	
	
	function config()
	{
		$frm = e107::getForm();
		
		$var[0]['caption']	= "Drupal Version";
		$var[0]['html'] 	= $frm->text('version', $this->version, 50, 'required=1'); 

		$var[1]['caption']	= "Drupal Base URL";
		$var[1]['html'] 	=$frm->text('baseUrl', $this->baseUrl, 50, 'required=1'); 

		$var[2]['caption']	= "Drupal Base Path";
		$var[2]['html'] 	=$frm->text('basePath', $this->basePath, 50, 'required=1'); 

		return $var;
	}
		
	
  // Set up a query for the specified task.
  // Returns TRUE on success. false on error
  // If $blank_user is true, certain cross-referencing user info is to be zeroed
	function setupQuery($task, $blank_user=false)
	{
	    if ($this->ourDB == NULL) return false;
		
		switch ($task)
		{
			case 'users' :
		    	$result = $this->ourDB->gen("SELECT * FROM {$this->DBPrefix}users WHERE `status`=1");
				if ($result === false) return false;
			break;
		
			case 'news' :
		    	return false;
		  	break; 
			
		  	case 'page' :
		    	return false;
			break;
			
			case 'links' :
		    	return false;
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
  
  // Copy data read from the DB into the record to be returned.
	function copyUserData(&$target, &$source) // http://drupal.org/files/er_db_schema_drupal_7.png
	{
		if ($this->copyUserInfo)
		{
			$target['user_id'] 			= $source['uid'];
			$target['user_name'] 		= $source['name'];
			$target['user_loginname'] 	= $source['name'];
			$target['user_password'] 	= $source['pass'];
			$target['user_email'] 		= $source['mail'];
			$target['user_signature'] 	= $source['signature'];
			$target['user_join'] 		= $source['created'];			
			$target['user_lastvisit'] 	= $source['login'];		// Could use $source['access']
		    $target['user_image'] 		= $source['picture'];
			// $source['init'] is email address used to sign up from
		    $target['user_timezone'] 	= $source['timezone'];		// May need conversion varchar(8)
		    $target['user_language'] 	= $source['language'];		// May need conversion varchar(12)
		    
			return $target;
		}
	}
	



	
	/**
	 * Example Copy News. 
	 * @param $target array - default e107 target values for e107_page table. 
	 * @param $source array - Drupal  table data
	 */
	function copyNewsData(&$target, &$source)
	{
		$target =  array(
			'news_id' 				=> 1,
		  	'news_title' 			=> 'Welcome to e107',
		  	'news_sef' 				=> 'welcome-to-e107',
		  	'news_body' 			=> '[html]<p>Welcome to your new website!</p>[/html]',
		  	'news_extended' 		=> '',
			'news_meta_keywords' 	=> '',
			'news_meta_description' => '',
			'news_datestamp' 		=> '1355612400', // time()
			'news_author' 			=> 1,
			'news_category' 		=> 1,
			'news_allow_comments' 	=> 0,
			'news_start' 			=> 0, // time()
			'news_end' 				=> 0, // time()
			'news_class' 			=> 0,
			'news_render_type' 		=> 0,
			'news_comment_total' 	=> 1,
			'news_summary' 			=> 'summary text',
			'news_thumbnail' 		=> '', // full path with {e_MEDIA_IMAGE} constant. 
			'news_sticky' 			=> 0
		);	
		
		return $target; 
	}	
	
	
	
	
	
	/**
	 * Example copy e107 Page Table 
	 * @param $target array - default e107 target values for e107_page table. 
	 * @param $source array - Drupal  table data
	 */
	function copyPageData(&$target, &$source)
	{
		$target = array(
			'page_id' 			=> 1,
			'page_title' 		=> 'string',
			'page_sef' 			=> 'string',
			'page_chapter' 		=> 0,
			'page_metakeys' 	=> 'string',
			'page_metadscr' 	=> '',
			'page_text' 		=> '',
			'page_author' 		=> 0, // e107 user_id
			'page_datestamp' 	=> '1371420000', // time()
			'page_rating_flag' 	=> 0,
			'page_comment_flag' => 0, // boolean
			'page_password' 	=> '', // plain text
			'page_class' 		=> 0, // e107 userclass
			'page_ip_restrict' 	=> '',
			'page_template' 	=> 'default',
			'page_order' 		=> 0,
			'menu_name' 		=> 'jumbotron-menu-1', // no spaces, all lowercase
			'menu_title' 		=> 'string',
			'menu_text' 		=> '',
			'menu_image' 		=> '',
			'menu_icon' 		=> '',
			'menu_template' 	=> 'button',
			'menu_class' 		=> 0,
			'menu_button_url' 	=> '',
			'menu_button_text' 	=> ''
		); 
		
		return $target;  
		
	}
	
	
	
	
	
	
	
}


?>
