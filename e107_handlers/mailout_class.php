<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Mailout handling - selector for 'core' users
 *

 *
*/


if (!defined('e107_INIT')) { exit; }

/* 
Class for 'core' mailout function. 
For plugins:
	- the equivalent file must be called 'e_mailout.php', and reside in the root of the plugin directory.
	- the classname must be $plugdir.'_mailout'    (e.g. 'calendar_menu_mailout')

Additional mailout sources may replicate the functions of this class under a different name, or may use inheritance.
When managing bulk emails, class calls are made for each data handler.

In general each class object must be self-contained, and use internal variables for storage

The class may use the global $e107->sql object for database access - it will effectively have exclusive use of this during the email address search phase

It is the responsibility of each class to manager permission restrictions where required.
*/

// These variables determine the circumstances under which this class is loaded (only used during loading, and may be overwritten later)
	$mailerIncludeWithDefault = TRUE;			// Mandatory - if false, show only when mailout for this specific plugin is enabled 
	$mailerExcludeDefault = TRUE;				// Mandatory - if TRUE, when this plugin's mailout is active, the default (core) isn't loaded

class core_mailout
{
	protected $mailCount = 0;
	protected $mailRead = 0;
	// protected $e107;
	//public $mailerSource = 'core';					// Plugin name (core mailer is special case) Must be directory for this file
	public $mailerName = LAN_MAILOUT_68;			// Text to identify the source of selector (displayed on left of admin page)
	public $mailerEnabled = TRUE;					// Mandatory - set to FALSE to disable this plugin (e.g. due to permissions restrictions)

	// List of fields used by selectors
	private	$selectFields = array('email_to', 
		'extended_1_name','extended_1_value',
		'extended_2_name', 'extended_2_value',
		'user_search_name', 'user_search_value',
		'last_visit_match', 'last_visit_date'
		);

	// Constructor
	public function __construct()
	{
		//FIXME Bad for Performance and causes data conflicts. 
		// $this->e107 = e107::getInstance();
		// $this->adminHandler = e107::getRegistry('_mailout_admin');		// Get the mailer admin object - we want to use some of its functions
	}
  
  
	/**
	 * Return data representing the user's selection criteria as entered in the $_POST array.
	 * 
	 * The value returned can be as simple as an array of chosen fields from the $_POST array, or it may be processed to make it more
	 * convenient to use later. (In general, at least basic sanitising should be performed)
	 * Conflicting selection criteria can also be resolved here.
	 * The returned data is stored in the DB with a saved email. (Just return an empty string or array if this is undesirable)
	 * The returned value is passed back to selectInit() and showSelect when needed.
	 *
	 * @return mixed Selection data - may be string, array or whatever suits
	 */
	public function returnSelectors()
	{
		$tp = e107::getParser();
		
		$res = array();
		foreach ($this->selectFields as $k)
		{
			if (vartrue($_POST[$k]))
			{
				$res[$k] = $tp->toDB($_POST[$k]);
			}
		}
		return $res;
	}


	/**
	 * Called to initialise data selection routine.
	 * Needs to save any queries or other information into internal variables, do initial DB queries as appropriate.
	 * Could in principle read all addresses and buffer them for later routines, if this is more convenient
	 *
	 * @param mixed $selectVals - selection criteria as returned by returnSelectors() (so format is whatever is chosen by the coder)
	 *
	 * @return int|boolean number of records available (or 1 if unknown) on success, FALSE on failure
	 */
	public function selectInit($selectVals = FALSE)
	{
		$sql = e107::getDb();

		$where = array();
		$incExtended = array();
				
		$emailTo = vartrue($selectVals['email_to'], false);
		
		switch ($emailTo)
		{
			// Build the query for the user database
			case 'all' :
				$where[] = 'u.`user_ban`=0';
				break;
			case  'admin' :
				$where[] = 'u.`user_admin`=1';
				break;	  
			case 'unverified' :
				$where[] = 'u.`user_ban`=2';
				break;
			case 'self' :
				$where[] = 'u.`user_id`='.USERID;
				break;
			default :
				if (is_numeric($selectVals['email_to']))
				{
					$where[] = "u.`user_class` REGEXP concat('(^|,)',{$selectVals['email_to']},'(,|$)')";
				}
			
		}

		if (vartrue($selectVals['extended_1_name']) && vartrue($selectVals['extended_1_value']))
		{
			$where[] = '`'.$selectVals['extended_1_name']."` = '".$selectVals['extended_1_value']."' ";
			$incExtended[] = $selectVals['extended_1_name'];
		}

		if (vartrue($selectVals['extended_2_name']) && vartrue($selectVals['extended_2_value']))
		{
			$where[] = "ue.`".$selectVals['extended_2_name']."` = '".$selectVals['extended_2_value']."' ";
			$incExtended[] = $selectVals['extended_2_name'];
		}

		if (vartrue($selectVals['user_search_name']) && vartrue($selectVals['user_search_value']))
		{
			$where[]= "u.`".$selectVals['user_search_name']."` LIKE '%".$selectVals['user_search_value']."%' ";
		}

		if (vartrue($selectVals['last_visit_match']) && vartrue($selectVals['last_visit_date']))
		{
			foreach(array(':', '-', ',') as $sep)
			{
				if (strpos($selectVals['last_visit_date'], ':'))
				{
					$tmp = explode($sep, $selectVals['last_visit_date']);
					break;
				}
			}
			$lvDate = gmmktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);	// Require dd-mm-yy for now
			if (($lvDate > 0) && ($lvDate <= time()))
			{
				switch ($selectVals['last_visit_match'])
				{
					case '<' :
					case '>' :
						$where[]= "u.`user_lastvisit`".$selectVals['last_visit_match'].$lvDate;
						break;
					case '=' :
						$where[]= "u.`user_lastvisit`>=".$lvDate;
						$where[]= "u.`user_lastvisit`<=".intval($lvDate + 86400);
						break;
				}
			}
		}

		if(empty($where) && empty($incExtended))
		{
			$this->mail_read = 0;
			$this->mail_count = 0;	
			return $this->mail_count;
		}


		$where[] = "u.`user_email` != ''";			// Ignore all records with empty email address

		
		
		// Now assemble the query from the pieces
		// Determine which fields we actually need (u.user_sess is the signup link)
		$qry = 'SELECT u.user_id, u.user_name, u.user_email, u.user_loginname, u.user_sess, u.user_lastvisit';
		if (count($incExtended))
		{
			foreach ($incExtended as $if)
			{
				$qry .= ', ue.`'.$if.'`';
			}
		}
		$qry .= " FROM `#user` AS u ";
		if (count($incExtended))
		{
			$qry .= "LEFT JOIN `#user_extended` AS ue ON ue.`user_extended_id` = u.`user_id`";
		}

		$qry .= ' WHERE '.implode(' AND ',$where).' ORDER BY u.user_name';
//		echo "Selector query: ".$qry.'<br />';
		
		e107::getMessage()->addDebug("Selector query: ".$qry);

		if (!( $this->mail_count = $sql->gen($qry))) return FALSE;
		$this->mail_read = 0;
		return $this->mail_count;
	}



	/**
	 * Return one email address to add to the recipients list. Return FALSE if no more addresses to add 
	 *
	 * @return boolean|array FALSE if no more addresses available; else an array:
	 *	'mail_recipient_id' - non-zero if a registered user, zero if a non-registered user. (Always non-zero from this class)
	 *	'mail_recipient_name' - user name
	 *	'mail_recipient_email' - email address to use
	 *	'mail_target_info' - array of info which might be substituted into email, usually using the codes defined by the editor. 
	 * 		Array key is the code within '|...|', value is the string for substitution
	 */
	public function selectAdd()
	{
		$sql = e107::getDb();
		
		if (!($row = $sql->db_Fetch())) return FALSE;
		$ret = array('mail_recipient_id' => $row['user_id'],
					 'mail_recipient_name' => $row['user_name'],		// Should this use realname?
					 'mail_recipient_email' => $row['user_email'],
					 'mail_target_info' => array(
						'USERID' => $row['user_id'],
						'DISPLAYNAME' => $row['user_name'],
						'SIGNUP_LINK' => $row['user_sess'],
						'USERNAME' => $row['user_loginname'],
						'USERLASTVISIT' => $row['user_lastvisit']
						)
					 );
		$this->mail_read++;
		return $ret;
	}


	/**
	 *	Called once all email addresses read, to do any housekeeping needed
	 *
	 *	@return none
	 */
	public function select_close()
	{	
		// Nothing to do here
	}
  

	/**
	 * Called to show current selection criteria, and optionally allow edit
	 * 
	 * @param $allow_edit is TRUE to allow user to change the selection; FALSE to just display current settings
	 * @param $selectVals is the current selection information - in the same format as returned by returnSelectors()
	 *
	 * @return Returns HTML which is displayed in a table cell. Typically we return a complete table
	 */
	public function showSelect($allow_edit = FALSE, $selectVals = FALSE)
	{
		$frm = e107::getForm();
		$sql = e107::getDb();
		$admin = e107::getRegistry('_mailout_admin');
		
		$var = array();
	
		$var[0]['caption'] 	= LAN_MAILOUT_260; // LAN_MAILOUT_03;	// User class select
		
		if ($allow_edit)
		{  
			$u_array = array('user_name'=>LAN_MAILOUT_43,'user_login'=>LAN_MAILOUT_44,'user_email'=>LAN_MAILOUT_45);
	
			$var[0]['html'] 	= $admin->userClassesTotals('email_to', varset($selectVals['email_to'], ''));								
			$var[1]['html'] 	= $frm->select('user_search_name', $u_array, varset($selectVals['user_search_name'], ''),'',TRUE)."  ".LAN_MAILOUT_47." ".$frm->text('user_search_value', varset($selectVals['user_search_value'], ''));
			//$var[2]['html'] 	= $admin->comparisonSelect('last_visit_match', varset($selectVals['last_visit_match'], ''))."  ".$frm->text('last_visit_date', varset($selectVals['last_visit_date'], 0));
			$var[2]['html'] 	= $admin->comparisonSelect('last_visit_match', varset($selectVals['last_visit_match'], ''))."  ".$admin->makeCalendar('last_visit_date', varset($selectVals['last_visit_date'], 0));
			$var[1]['caption'] 	= LAN_MAILOUT_46;   // User Search Field.
			$var[2]['caption'] 	= LAN_MAILOUT_56;	// User last visit

			$extFields			= $admin->ret_extended_field_list('extended_1_name', varset($selectVals['extended_1_name'], ''), TRUE);
			if ($extFields !== FALSE)	// Only display next bit if UEFs defined
			{
				$var[3]['html'] 	= $extFields.LAN_MAILOUT_48." ".$frm->text('extended_1_value',varset($selectVals['extended_1_value'], ''));
				$var[4]['html'] 	= $admin->ret_extended_field_list('extended_2_name', varset($selectVals['extended_2_name'], ''), TRUE).LAN_MAILOUT_48." ".$frm->text('extended_2_value',varset($selectVals['extended_2_value'],''));

				$var[3]['caption'] 	= LAN_MAILOUT_46;	// Extended user field		
				$var[4]['caption'] 	= LAN_MAILOUT_46;	// Extended user field		
			}
		}
		else // Display existing values
		{ 	
			if (!vartrue($selectVals['email_to']))
			{
				return;
			}
		
			if (is_numeric($selectVals['email_to']))
			{
				$_to = LAN_MAILOUT_23.e107::getUserClass()->uc_get_classname(intval($selectVals['email_to']));
			}
			else
			{
				$_to = $selectVals['email_to'];
			}
			
			$var_0 = $_to.'&nbsp;';
			if ($selectVals['email_to'] == 'self')
			{
				$var_0 .= '&lt;'.USEREMAIL.'&gt;';
			}
			
			$var[0]['html'] = $var_0;
			if (vartrue($selectVals['user_search_name']) && vartrue($selectVals['user_search_value']))
			{
				$var[1]['html'] = $selectVals['user_search_name'].'  '.$selectVals['user_search_value'];
				$var[1]['caption'] 	= LAN_MAILOUT_46;   // User Search Field.
			}
			if (vartrue($selectVals['last_visit_match']) && vartrue($selectVals['last_visit_date']))
			{
				$var[2]['html'] = $selectVals['last_visit_match'].' '.gmstrftime("%D-%M-%Y",$selectVals['last_visit_date']); //FIXME use e107 date function. 
				$var[2]['caption'] 	= LAN_MAILOUT_56;	// User last visit
			}
			$extFields	= $admin->ret_extended_field_list('extended_1_name', varset($selectVals['extended_1_name'], ''), TRUE);
			if ($extFields !== FALSE)
			{
				if (vartrue($selectVals['extended_1_name']) && vartrue($selectVals['extended_1_value']))
				{
					$var[3]['html'] = $selectVals['extended_1_name'].' '.$selectVals['extended_1_value'];
					$var[3]['caption'] 	= LAN_MAILOUT_46;	// Extended user field		
				}
				if (vartrue($selectVals['extended_2_name']) && vartrue($selectVals['extended_2_value']))
				{
					$var[4]['html'] = $selectVals['extended_2_name'].' '.$selectVals['extended_2_value'];
					$var[4]['caption'] 	= LAN_MAILOUT_46;	// Extended user field		
				}
			}
			
		}

		return $var;
	}
}



