<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Newsletter plugin - mailout function
 *
 *
*/


/**
 *	e107 Newsletter plugin
 *
 *	@package	e107_plugins
 *	@subpackage	newsletter
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }



/* 
Class for newsletter mailout function

Allows admins to send mail to those subscribed to one or more newsletters
*/
// These variables determine the circumstances under which this class is loaded (only used during loading, and may be overwritten later)
	$mailerIncludeWithDefault = TRUE;			// Mandatory - if false, show only when mailout for this specific plugin is enabled 
	$mailerExcludeDefault = FALSE;				// Mandatory - if TRUE, when this plugin's mailout is active, the default (core) isn't loaded

class user_mailout
{
//	protected $mailCount = 0;
//	protected $mailRead = 0;
	//public $mailerSource = 'newsletter';			// Plugin name (core mailer is special case) Must be directory for this file
	public $mailerName      = LAN_PLUGIN_NEWSLETTER_NAME;					// Text to identify the source of selector (displayed on left of admin page)
	public $mailerEnabled   = TRUE;					// Mandatory - set to FALSE to disable this plugin (e.g. due to permissions restrictions)
//	private $selectorActive = FALSE;				// Set TRUE if we've got a valid selector to start returning entries
//	private	$targets = array();						// Used to store potential recipients
//	private $ourDB;


	// Constructor
	public function __construct()
	{
		// BAD FOR PERFORMANCE
		//$this->e107 = e107::getInstance();
		//$this->adminHandler = e107::getRegistry('_mailout_admin');		// Get the mailer admin object - we want to use some of its functions
	}
  

	/**
	 * Manage Bounces. 
	 */
	public function bounce($data)
	{
		e107::getLog()->add('Newsletter Bounce', $data, E_LOG_INFORMATIVE, 'BOUNCE');	
	}


	/**
	 * @param $mode - check || process
	 * @param array $data - usually email, date, id - but dependent on unsubscribe link above.
	 */
	function unsubscribe($mode, $data=null)
	{
		if($mode == 'check') // check that a matching email,id,creation-date exists.
		{
			$ucl = intval($data['userclass']);

			return e107::getDb()->select('user','*', 'FIND_IN_SET('.$ucl.',user_class) AND user_id='.intval($data['id'])." AND user_join=".intval($data['date'])." AND user_email=\"".$data['email']."\"");
		}

	//	print_a($data);

		if($mode == 'process') // Update record. Return true on success, and false on error.
		{
			$uid = intval($data['id']);
			$ucl = intval($data['userclass']);

			return e107::getSystemUser($uid)->removeClass($ucl); // best way to remove userclass from user.

		}

	}


}



?>