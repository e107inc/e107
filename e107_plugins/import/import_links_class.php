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

class links_import
{
	var $pageDB = NULL;
	var $blockMainAdmin = TRUE;
	var $error;

	var $defaults = array(
		//	'link_id'			=> '',
			'link_name'			=> '',
			'link_url'			=> '',
			'link_description'	=> '',
			'link_button'		=> '',
			'link_category'		=> 4,
			'link_order'		=> '',
			'link_parent'		=> '0',
			'link_open'			=> '0',
			'link_class'		=> '0',
			'link_function'		=> '',
			'link_sefurl'		=> ''

	);

	// Fields which must be set up by the caller.  
	var $mandatory = array(
		'link_name', 'link_url'
	);
  
	// Constructor
	function __construct()
	{
	  	global $sql;
	    $this->pageDB = new db;	// Have our own database object to write to the table	
	}


	// Empty the  DB - not necessary
	function emptyTargetDB($inc_admin = FALSE)
	{
		// $this->pageDB->db_Delete('page');
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
		if(!$result = $this->pageDB->db_Insert('links',$row))
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



