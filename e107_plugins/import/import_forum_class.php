<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class forum_import
{
	var $error;

	var $defaults = array(
			'forum_id'					=> '',
			'forum_name'				=> '',
			'forum_description'			=> '',
			'forum_parent'				=> '0',
			'forum_sub'					=> '',
			'forum_datestamp'			=> '',
			'forum_moderators'			=> '',
			'forum_threads'				=> '0',
			'forum_replies'				=> '0',
			'forum_lastpost_user'		=> '',
			'forum_lastpost_user_anon' => '',
			'forum_lastpost_info'		=> '',
		
			'forum_class'				=> '0',
			'forum_order'				=> '0',
			'forum_postclass'			=> '',
			'forum_threadclass'			=> '',
			'forum_options'				=> ''
	);

	var $mandatory = array(
		'forum_name', 'forum_description'
	);
  
	// Constructor
	function __construct()
	{
	 // 	global $sql;
	 //   $this->pageDB = new db;	// Have our own database object to write to the table	
	}


	// Empty the  DB - not necessary
	function emptyTargetDB($inc_admin = FALSE)
	{
		 if(e107::getDb('forum')->truncate('forum'))
		 {
		 	e107::getMessage()->addDebug("Emptied Forum Table");
		 }
	}
  
  
	// Set a new default for a particular field
	function overrideDefault($key, $value)
	{
//    echo "Override: {$key} => {$value}<br />";
    	if (!isset($this->defaults[$key])) return FALSE;
		$this->defaults[$key] = $value;
	}

  
  // Returns an array with all relevant fields set to the current default
	function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * Insert data into e107 DB
	 * @param row - array of table data
	 * @return integer, boolean - error code on failure, TRUE on success
	 */
	function saveData($row)
	{	
		if(!$result = e107::getDb('forum')->insert('forum',$row))
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




class forumthread_import
{
	var $error;
	var $forumClass;

	var $defaults = array(
			'thread_id'					=> '',
			'thread_name'				=> '',
			'thread_forum_id'			=> '',
			'thread_views'				=> '0',
			'thread_active'				=> 1,
			'thread_lastpost'			=> '',
			'thread_sticky'				=> '0',
			'thread_datestamp'			=> '',
			'thread_user'				=> '',
			'thread_user_anon'			=> null,
			'thread_lastuser'			=> '',
			'thread_lastuser_anon'		=> '',
			'thread_total_replies'		=> '0',
			'thread_options'			=> '',
	);

	var $mandatory = array(
		'thread_name', 'thread_forum_id', 'thread_datestamp'
	);
  
	// Constructor
	function __construct()
	{
	 // 	global $sql;
	 //   $this->pageDB = new db;	// Have our own database object to write to the table	
	 
	}


	// Empty the  DB - not necessary
	function emptyTargetDB($inc_admin = FALSE)
	{
		if(e107::getDb('forum')->truncate('forum_thread'))
		{
			e107::getMessage()->addDebug("Emptied forum_thread Table");
		}
	}
  
  
	// Set a new default for a particular field
	function overrideDefault($key, $value)
	{
//    echo "Override: {$key} => {$value}<br />";
    	if (!isset($this->defaults[$key])) return FALSE;
		$this->defaults[$key] = $value;
	}

  
  // Returns an array with all relevant fields set to the current default
	function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * Insert data into e107 DB
	 * @param row - array of table data
	 * @return integer, boolean - error code on failure, TRUE on success
	 */
	function saveData($row)
	{	
		if(!$result = e107::getDb('forum')->insert('forum_thread',$row))
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





class forumpost_import
{
	var $error;

	var $defaults = array(
			'post_id'				=> '',
			'post_entry'			=> '',
			'post_thread'			=> '',
			'post_forum'			=> '',
			'post_status'			=> '',
			'post_datestamp'		=> '',
			'post_user'				=> '',
			'post_edit_datestamp'	=> '',
			'post_edit_user'		=> '',
			'post_ip'				=> '',
			'post_user_anon'		=> '',
			'post_attachments'		=> '',
			'post_options'			=> '',
	);

	var $mandatory = array(
		'post_thread', 'post_forum'
	);
	
	var $helperClass;

	// Constructor
	function __construct()
	{
	 // 	global $sql;
	 //   $this->pageDB = new db;	// Have our own database object to write to the table	
		if(require_once(e_PLUGIN."forum/forum_class.php"))
		{
			e107::getMessage()->addDebug("Include forum_class");
		}
		
		$this->helperClass = new e107forum();		
	}
	

	// Empty the  DB - not necessary
	function emptyTargetDB($inc_admin = FALSE)
	{
		if(e107::getDb('forum')->truncate('forum_post'))
		{
			e107::getMessage()->addDebug("Emptied forum_post Table");
		}
	}
  
  
	// Set a new default for a particular field
	function overrideDefault($key, $value)
	{
//    echo "Override: {$key} => {$value}<br />";
    	if (!isset($this->defaults[$key])) return FALSE;
		$this->defaults[$key] = $value;
	}

  
  // Returns an array with all relevant fields set to the current default
	function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * Insert data into e107 DB
	 * @param row - array of table data
	 * @return integer, boolean - error code on failure, TRUE on success
	 */
	function saveData($row)
	{	
		if(!$result = e107::getDb('forum')->insert('forum_post',$row))
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








class forumtrack_import
{
	var $error;

	var $defaults = array(
			'track_userid'				=> '',
			'track_thread'				=> ''
			
	);

	var $mandatory = array(
		'track_userid', 'track_thread'
	);
  
	// Constructor
	function __construct()
	{
	 // 	global $sql;
	 //   $this->pageDB = new db;	// Have our own database object to write to the table	
	}


	// Empty the  DB - not necessary
	function emptyTargetDB($inc_admin = FALSE)
	{
		 if(e107::getDb('forum')->truncate('forum_track'))
		 {
		 	e107::getMessage()->addDebug("Emptied forum_track Table");
		 }
	}
  
  
	// Set a new default for a particular field
	function overrideDefault($key, $value)
	{
//    echo "Override: {$key} => {$value}<br />";
    	if (!isset($this->defaults[$key])) return FALSE;
		$this->defaults[$key] = $value;
	}

  
  // Returns an array with all relevant fields set to the current default
	function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * Insert data into e107 DB
	 * @param row - array of table data
	 * @return integer, boolean - error code on failure, TRUE on success
	 */
	function saveData($row)
	{	
		if(!$result = e107::getDb('forum')->insert('forum_track',$row))
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




