<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Maintenance
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/mailout_class.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-15 17:38:04 $
 * $Author: e107steved $
 *
*/


if (!defined('e107_INIT')) { exit; }

/* 
Class for 'core' mailout function. 
For plugins:
	- the equivalent file must be called 'e_mailout.php', and reside in the root of the plugin directory.
	- the classname must be 'mailout_'.$plugdir    (e.g. 'mailout_calendar_menu')

Additional mailout sources may replicate the functions of this class under a different name, or may use inheritance.
When managing bulk emails, class calls are made for each data handler.

In general each class object must be self-contained, and use internal variables for storage

The class may use the global $e107->sql object for database access - it will effectively have exclusive use of this during the email address search phase

It is the responsibility of each class to manager permission restrictions where required.

TODO:
	1. accept varying date formats for last visit
	2. Use XHTML calendar for last visit
	3. Sort classes for table cells

*/
// These variables determine the circumstances under which this class is loaded (only used during loading, and may be overwritten later)
	$mailerIncludeWithDefault = TRUE;			// Mandatory - if false, show only when mailout for this specific plugin is enabled 
	$mailerExcludeDefault = TRUE;				// Mandatory - if TRUE, when this plugin's mailout is active, the default (core) isn't loaded

class mailout_core
{
	protected $mailCount = 0;
	protected $mailRead = 0;
	protected $e107;
	public $mailerSource = 'core';					// Plugin name (core mailer is special case) Must be directory for this file
	public $mailerName = LAN_MAILOUT_68;			// Text to identify the source of selector (displayed on left of admin page)
	public $mailerEnabled = TRUE;					// Mandatory - set to FALSE to disable this plugin (e.g. due to permissions restrictions)
	protected $adminHandler = NULL;					// Filled in with the name of the admin handler on creation

	// List of fields used by selectors
	private	$selectFields = array('email_to', 
		'extended_1_name','extended_1_value',
		'extended_2_name', 'extended_2_value',
		'user_search_name', 'user_search_value',
		'last_visit_match', 'last_visit_date'
		);

	// Constructor
	public function __construct(&$mailerAdminHandler = NULL)
	{
		$this->e107 = e107::getInstance();
		if ($mailerAdminHandler == NULL)
		{
			global $mailAdmin;
			$mailerAdminHandler = $mailAdmin;
		}
		$this->adminHandler = $mailerAdminHandler;
	}
  
  
	/**
	 * Return data representing the user's selection criteria as entered in the $_POST array.
	 * 
	 * This is stored in the DB with a saved email. (Just return an empty string or array if this is undesirable)
	 * The returned value is passed back to selectInit() and showSelect when needed.
	 *
	 * @return Selection data - may be string, array or whatever suits
	 */
	public function returnSelectors()
	{
		$res = array();
		foreach ($this->selectFields as $k)
		{
			if (varsettrue($_POST[$k]))
			{
				$res[$k] = $this->e107->tp->toDB($_POST[$k]);
			}
		}
		return $res;
	}


	/**
	 * Called to initialise data selection routine.
	 * Needs to save any queries or other information into internal variables, do initial DB queries as appropriate.
	 * Could in principle read all addresses and buffer them for later routines, if this is more convenient
	 *
	 * @param $selectVals - array of selection criteria as returned by returnSelectors()
	 *
	 * @return Return number of records available (or 1 if unknown) on success, FALSE on failure
	 */
	public function selectInit($selectVals = FALSE)
	{
		$where = array();
		$incExtended = array();
		if ($selectVals === FALSE)
		{
			$selectVals = array('email_to' => 'all');
		}
		switch (varset($selectVals['email_to'], 'all'))
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
				$where[] = "u.`user_ban`=0";
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
		if (!( $this->mail_count = $this->e107->sql->db_Select_gen($qry))) return FALSE;
		$this->mail_read = 0;
		return $this->mail_count;
	}



	/**
	 * Return an email address to add to the recipients list. Return FALSE if no more addresses to add 
	 *
	 * @return FALSE if no more addresses available; else an array:
	 *	'mail_recipient_id' - non-zero if a registered user, zero if a non-registered user. (Always non-zero from this class)
	 *	'mail_recipient_name' - user name
	 *	'mail_recipient_email' - email address to use
	 *	'mail_target_info' - array of info which might be substituted into email, usually using the codes defined by the editor. 
	 * 		Array key is the code within '|...|', value is the string for substitution
	 */
	public function selectAdd()
	{
		if (!($row = $this->e107->sql->db_Fetch(MYSQL_ASSOC))) return FALSE;
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


	// Called once all email addresses read, to do any housekeeping needed
	public function select_close()
	{	
		// Nothing to do here
	}
  

	// Called to show current selection criteria, and optionally allow edit
	// 
	// 
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
		$ret = "<table style='width:95%'>";

		if ($allow_edit)
		{  
			// User class select
			$ret .= "<tr>
				<td class='forumheader3'>".LAN_MAILOUT_03.": </td>
				<td class='forumheader3'>
				".$this->adminHandler->userClassesTotals('email_to', varset($selectVals['email_to'], ''))."</td>
				</tr>";
		
			// User Search Field.
			$u_array = array('user_name'=>LAN_MAILOUT_43,'user_login'=>LAN_MAILOUT_44,'user_email'=>LAN_MAILOUT_45);
			$ret .= "
				<tr>
					<td style='width:35%' class='forumheader3'>".LAN_MAILOUT_46."
					<select name='user_search_name' class='tbox'>
					<option value=''>&nbsp;</option>";

			foreach ($u_array as $key=>$val)
			{
				$selected = '';
				if (isset($selectVals['user_search_name']) && ($selectVals['user_search_name'] == $v)) { $selected = " selected='selected'"; }
				$ret .= "<option value='{$key}' >".$val."</option>\n";
			}
			$ret .= "
				</select> ".LAN_MAILOUT_47." </td>
				<td style='width:65%' class='forumheader3'>
				<input type='text' name='user_search_value' class='tbox' style='width:80%' value='".varset($selectVals['user_search_value'])."' />
				</td></tr>
				";

			// User last visit
			$ret .= "
				<tr><td class='forumheader3'>".LAN_MAILOUT_56.' '.$this->adminHandler->comparisonSelect('last_visit_match', $selectVals['last_visit_match'])." </td>
				<td class='forumheader3'>
				<input type='text' name='last_visit_date' class='tbox' style='width:30%' value='".varset($selectVals['last_visit_date'], '')."' />
				</td></tr>";
			

			// Extended user fields
			$ret .= "
				<tr><td class='forumheader3'>".LAN_MAILOUT_46.$this->adminHandler->ret_extended_field_list('extended_1_name', varset($selectVals['extended_1_name'], ''), TRUE).LAN_MAILOUT_48." </td>
				<td class='forumheader3'>
				<input type='text' name='extended_1_value' class='tbox' style='width:80%' value='".varset($selectVals['extended_1_value'], '')."' />
				</td></tr>
				<tr><td class='forumheader3'>".LAN_MAILOUT_46.$this->adminHandler->ret_extended_field_list('extended_2_name', varset($selectVals['extended_2_name'], ''), TRUE).LAN_MAILOUT_48." </td>
				<td class='forumheader3'>
				<input type='text' name='extended_2_value' class='tbox' style='width:80%' value='".varset($selectVals['extended_2_value'], '')."' />
				</td></tr>
				";
		}
		else
		{ 	// Display existing values
			if(is_numeric($selectVals['email_to']))
			{
				$this->e107->sql->db_Select('userclass_classes', 'userclass_name', "userclass_id = ".intval($selectVals['email_to']));
				$row = $this->e107->sql->db_Fetch();
				$_to = LAN_MAILOUT_23.$row['userclass_name'];
			}
			else
			{
				$_to = $selectVals['email_to'];
			}
			$ret .= "<tr>
					<td class='forumheader3'>".LAN_MAILOUT_03."</td>
					<td class='forumheader3'>".$_to."&nbsp;";
			if($selectVals['email_to'] == "self")
			{
				$text .= "&lt;".USEREMAIL."&gt;";
			}
			$ret .= "</td></tr>";


			if (vartrue($selectVals['user_search_name']) && vartrue($selectVals['user_search_value']))
			{
				$ret .= "
				<tr>
					<td class='forumheader3'>".$selectVals['user_search_name']."</td>
					<td class='forumheader3'>".varset($selectVals['user_search_value'])."&nbsp;</td>
				</tr>";
			}

			if (vartrue($selectVals['last_visit_match']) && vartrue($selectVals['last_visit_date']))
			{
				$ret .= "
				  <tr>
					<td class='forumheader3'>".LAN_MAILOUT_56."</td>
					<td class='forumheader3'>".$selectVals['last_visit_match'].' '.gmstrtotime("%D-%M-%Y",$selectVals['last_visit_date'])."&nbsp;</td>
				  </tr>";
			}

			if (vartrue($selectVals['extended_1_name']) && vartrue($selectVals['extended_1_value']))
			{
				$ret .= "
				  <tr>
					<td class='forumheader3'>".$selectVals['extended_1_name']."</td>
					<td class='forumheader3'>".$selectVals['extended_1_value']."&nbsp;</td>
				  </tr>";
			}
			if (vartrue($selectVals['extended_2_name']) && vartrue($selectVals['extended_2_value']))
			{
				$ret .= "
				  <tr>
					<td class='forumheader3'>".$selectVals['extended_2_name']."</td>
					<td class='forumheader3'>".$selectVals['extended_2_value']."&nbsp;</td>
				  </tr>";
			}
		}

		return $ret.'</table>';
	}
}



?>