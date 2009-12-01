<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Maintenance
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/e_mailout.php,v $
 * $Revision: 1.7 $
 * $Date: 2009-12-01 20:05:54 $
 * $Author: e107steved $
 *
*/


if (!defined('e107_INIT')) { exit(); }


include_lan(e_PLUGIN.'/calendar_menu/languages/'.e_LANGUAGE.'_mailer.php');

/* 
Class for event calendar mailout function

Allows admins to send mail to those subscribed to calendar events
*/
// These variables determine the circumstances under which this class is loaded (only used during loading, and may be overwritten later)
	$mailerIncludeWithDefault = TRUE;			// Mandatory - if false, show only when mailout for this specific plugin is enabled 
	$mailerExcludeDefault = FALSE;				// Mandatory - if TRUE, when this plugin's mailout is active, the default (core) isn't loaded

class calendar_menu_mailout
{
	protected $mailCount = 0;
	protected $mailRead = 0;
	protected $e107;
	public $mailerSource = 'calendar_menu';			// Plugin name (core mailer is special case) Must be directory for this file
	public $mailerName = LAN_EC_MAIL_01;			// Text to identify the source of selector (displayed on left of admin page)
	public $mailerEnabled = TRUE;					// Mandatory - set to FALSE to disable this plugin (e.g. due to permissions restrictions)
	protected $adminHandler = NULL;					// Filled in with the name of the admin handler on creation
	private $selectorActive = FALSE;				// Set TRUE if we've got a valid selector to start returning entries


	// Constructor
	public function __construct()
	{
		$this->e107 = e107::getInstance();
		$this->adminHandler = e107::getRegistry('_mailout_admin');		// Get the mailer admin object - we want to use some of its functions
	}
  
  
	/**
	 * Return data representing the user's selection criteria as entered in the $_POST array.
	 * 
	 * This is stored in the DB with a saved email. (Just return an empty string or array if this is undesirable)
	 * The returned value is passed back to selectInit() and showSelect when needed.
	 *
	 * @return string Selection data - comma-separated list of category IDs
	 */
	public function returnSelectors()
	{
		$res = array();
		if (is_array($_POST['ec_category_sel']))
		{
			foreach ($_POST['ec_category_sel'] as $k => $v)
			{
				$res[] = intval($v);
			}
		}
		return implode(',',$res);
	}


	/**
	 * Called to initialise data selection routine.
	 * Needs to save any queries or other information into internal variables, do initial DB queries as appropriate.
	 * Could in principle read all addresses and buffer them for later routines, if this is more convenient
	 *
	 * @param string $selectVals - array of selection criteria as returned by returnSelectors()
	 *
	 * @return integer Return number of records available (or 1 if unknown) on success, FALSE on failure
	 */
	public function selectInit($selectVals = FALSE)
	{
		if (($selectVals === FALSE) || ($selectVals == ''))
		{
			return 0;				// No valid selector - so no valid records
		}

		$where = array();
		$qry = 'SELECT u.user_id, u.user_name, u.user_email, u.user_loginname, u.user_sess, u.user_lastvisit FROM `#event_subs` AS es';
		$qry .= ' LEFT JOIN `#user` AS u ON es.`event_subid` = u.`user_id` WHERE es.`event_cat` IN (\''.$selectVals.'\') AND u.`user_id` IS NOT NULL';
		$qry .= ' GROUP BY u.`user_id`';
//		echo "Selector query: ".$qry.'<br />';
		if (!( $this->mail_count = $this->e107->sql->db_Select_gen($qry))) return FALSE;
		$this->selectorActive = TRUE;
		$this->mail_read = 0;
		return $this->mail_count;
	}



	/**
	 * Return an email address to add to the recipients list. Return FALSE if no more addresses to add 
	 *
	 * @return array|boolean FALSE if no more addresses available; else an array:
	 *	'mail_recipient_id' - non-zero if a registered user, zero if a non-registered user. (Always non-zero from this class)
	 *	'mail_recipient_name' - user name
	 *	'mail_recipient_email' - email address to use
	 *	'mail_target_info' - array of info which might be substituted into email, usually using the codes defined by the editor. 
	 * 		Array key is the code within '|...|', value is the string for substitution
	 */
	public function selectAdd()
	{
		if (!$this->selectorActive) return FALSE;
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
	 * @param boolean $allow_edit is TRUE to allow user to change the selection; FALSE to just display current settings
	 * @param string $selectVals is the current selection information - in the same format as returned by returnSelectors()
	 *
	 * @return string Returns HTML which is displayed in a table cell. Typically we return a complete table
	 */
	public function showSelect($allow_edit = FALSE, $selectVals = FALSE)
	{
		$ret = "<table style='width:95%'>";
		$selects = array_flip(explode(',', $selectVals));

		if ($this->e107->sql->db_Select('event_cat', 'event_cat_id, event_cat_name', "event_cat_name != 'Default'"))
		{
			while ($row = $this->e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$checked = (isset($selects[$row['event_cat_id']])) ? " checked='checked'" : '';
				if ($allow_edit)
				{
					$ret .= "<tr><td><input type='checkbox' name='ec_category_sel[]' value='{$row['event_cat_id']}' {$checked}/></td><td>
						".$row['event_cat_name']."</td></tr>";
				}
				elseif($checked)
				{
					$ret .= "<tr><td>".LAN_EC_MAIL_03."</td><td>
						".$row['event_cat_name']."</td></tr>";
				}
			}
		}
		else
		{
			$ret .= "<tr><td colspan='2'>".LAN_EC_MAIL_02.'</td></tr>';
		}
		return $ret.'</table>';
	}
}



?>