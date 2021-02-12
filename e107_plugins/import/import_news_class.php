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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/import_user_class.php,v $
 * $Revision: 11315 $
 * $Date: 2010-02-10 10:18:01 -0800 (Wed, 10 Feb 2010) $
 * $Author: secretr $
 */

/*
Class intended to simplify importing of user information from outside.
It ensures that each user record has appropriate defaults

To use:
	1. Create one instance of the class
	2. Call emptyUserDB() to delete existing users
	3. If necessary, call overrideDefault() as necessary to modify the defaults
	4. For each record:
		a) Call getDefaults() to get a record with all the defaults filled in
		b) Update the record from the source database
		c) Call saveUser($userRecord) to write the record to the DB
*/

class news_import
{
	var $newsDB = NULL;
	var $blockMainAdmin = TRUE;
	var $error;

	var $default = array(
			//'news_id'				=> '', // auto-increment
			'news_title'			=> '',
			'news_sef'				=> '',
			'news_body'				=> '',
			'news_extended'			=> '',
			'news_meta_keywords'	=> '',
			'news_meta_description'	=> '',
			'news_datestamp'		=> '',
			'news_author'			=> '1',
			'news_category'			=> '1',
			'news_allow_comments'	=> '0',
			'news_start'			=> '0',
			'news_end'				=> '0',
			'news_class'			=> '0',
			'news_render_type'		=> '0',
			'news_comment_total'	=> '0',
			'news_summary'			=> '',
			'news_thumbnail'		=> '',
			'news_sticky'			=> '0'
	);

	/* Fields which must be set up by the caller.  */
	var $mandatory = array( 
			'news_title',
			'news_datestamp',
			'news_author'
	);
  
	// Constructor
	function __construct()
	{
	    $this->newsDB = e107::getDb('news');	// Have our own database object to write to the news table
	}


	// Empty the news DB
	function emptyTargetDB($inc_admin = FALSE)
	{
		 $this->newsDB->truncate('news');
	}
  
  
	// Set a new default for a particular field
	function overrideDefault($key, $value)
	{
//    echo "Override: {$key} => {$value}<br />";
    	if (!isset($this->default[$key])) return FALSE;
		$this->default[$key] = $value;
	}

  
  // Returns an array with all relevant fields set to the current default
	function getDefaults()
	{
		return $this->default;
	}



	/**
	 * Insert data into e107 DB
	 * @param row - array of table data
	 * @return integer, boolean - error code on failure, TRUE on success
	 */
	function saveData($row)
	{
		if(!$result = $this->newsDB->insert('news',$row))
		{
	     	return 4;
		}
	
		//if ($result === FALSE) return 6;
	
		return TRUE;
	}
 

 
	function getErrorText($errnum)    // these errors are presumptuous and misleading. especially '4' .
	{
		$errorTexts = array(
	    	0 => LAN_CONVERT_57, 
	    	1 => LAN_CONVERT_58, 
	    	2 => LAN_CONVERT_59,
			3 => LAN_CONVERT_60, 
			4 => LAN_CONVERT_61, 
			5 => LAN_CONVERT_62,
			6 => LAN_CONVERT_63
		);
			
		if (isset($errorTexts[$errnum])) return $errorTexts[$errnum];
		
		return 'Unknown: '.$errnum;
	
	}
  
  
  
}



class newscategory_import
{
	var $newsDB = NULL;
	var $blockMainAdmin = TRUE;
	var $error;

	var $default = array(

	//		'category_id'			=> '',  // auto-increment
			'category_name'			        => '',
			'category_sef'			        => '',
			'category_meta_description'		=> '',
			'category_meta_keywords'		=> '',
			'category_manager'			    => e_UC_ADMIN,
			'category_icon'			        => '',
			'category_order'			    => 0,
	);

	/* Fields which must be set up by the caller.  */
	var $mandatory = array(
			'category_id',
			'category_name',
	);

	// Constructor
	function __construct()
	{
	    $this->newsDB = e107::getDb('newscat');		// Have our own database object to write to the news table
	}


	// Empty the news DB
	function emptyTargetDB($inc_admin = FALSE)
	{
		 $this->newsDB->truncate('news_category');
	}


	// Set a new default for a particular field
	function overrideDefault($key, $value)
	{
//    echo "Override: {$key} => {$value}<br />";
    	if (!isset($this->default[$key])) return FALSE;
		$this->default[$key] = $value;
	}


  // Returns an array with all relevant fields set to the current default
	function getDefaults()
	{
		return $this->default;
	}



	/**
	 * Insert data into e107 DB
	 * @param row - array of table data
	 * @return integer, boolean - error code on failure, TRUE on success
	 */
	function saveData($row)
	{
		if(!$result = $this->newsDB->insert('news_category',$row))
		{
	     	return 4;
		}


		return true;
	}



	function getErrorText($errnum)    // these errors are presumptuous and misleading. especially '4' .
	{
		return $this->newsDB->getLastErrorText();
	}



}



