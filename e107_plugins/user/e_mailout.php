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



if (!defined('e107_INIT')) { exit; }


/* 
Class for user mailout function

Allows admins to send mail to those subscribed to one or more newsletters
*/
// These variables determine the circumstances under which this class is loaded (only used during loading, and may be overwritten later)
	$mailerIncludeWithDefault = true;			// Mandatory - if false, show only when mailout for this specific plugin is enabled
	$mailerExcludeDefault = false;				// Mandatory - if TRUE, when this plugin's mailout is active, the default (core) isn't loaded

class user_mailout
{
	public $mailerSource    = 'user';	// Plugin name (core mailer is special case) Must be directory for this file
	public $mailerName      = LAN_MAILOUT_68;	// Text to identify the source of selector (displayed on left of admin page)
	public $mailerEnabled   = true;		// Mandatory - set to FALSE to disable this plugin (e.g. due to permissions restrictions)

	protected $mailCount = 0;
	protected $mailRead = 0;

	// List of fields used by selectors
	private	$selectFields = array('email_to',
		'extended_1_name','extended_1_value',
		'extended_2_name', 'extended_2_value',
		'user_search_name', 'user_search_value',
		'last_visit_match', 'last_visit_date'
		);



	/**
	 * Manage Bounces. 
	 */
	public function bounce($data)
	{
		e107::getLog()->add('User Bounce', $data, E_LOG_INFORMATIVE, 'BOUNCE');
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

		if (!empty($selectVals['last_visit_match']) && !empty($selectVals['last_visit_date']))
		{
			$lvDate = $selectVals['last_visit_date'];

			e107::getDebug()->log(date('r',$lvDate));

			if (($lvDate > 0) && ($lvDate <= time()))
			{
				switch ($selectVals['last_visit_match'])
				{
					case '<' :
					case '>' :
						$where[]= "u.`user_lastvisit` ".$selectVals['last_visit_match']." ".$lvDate;
						break;
					case '=' :
						$where[]= "u.`user_lastvisit` >= ".$lvDate;
						$where[]= "u.`user_lastvisit` <= ".intval($lvDate + 86400);
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

		if (!( $this->mailCount = $sql->gen($qry))) return FALSE;

		e107::getDebug()->log($this->mailCount);

		$this->mail_read = 0;
		return $this->mailCount;
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

		if (!($row = $sql->fetch())) return FALSE;
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

		$var[0]['caption'] 	= LAN_USERCLASS;	// User class select

		if ($allow_edit)
		{
			$u_array = array('user_name'=>LAN_MAILOUT_43,'user_login'=>LAN_MAILOUT_44,'user_email'=>LAN_MAILOUT_45);

			$var[0]['html'] 	= $admin->userClassesTotals('email_to', varset($selectVals['email_to'], ''));
			$var[1]['html'] 	= $frm->select('user_search_name', $u_array, varset($selectVals['user_search_name'], ''),'',TRUE)."  ".LAN_MAILOUT_47." ".$frm->text('user_search_value', varset($selectVals['user_search_value'], ''));
			//$var[2]['html'] 	= $admin->comparisonSelect('last_visit_match', varset($selectVals['last_visit_match'], ''))."  ".$frm->text('last_visit_date', varset($selectVals['last_visit_date'], 0));
			$var[2]['html'] 	= $admin->comparisonSelect('last_visit_match', varset($selectVals['last_visit_match'], ''))."  ".e107::getForm()->datepicker('last_visit_date', varset($selectVals['last_visit_date'], 0), array('type'=>'datetime'));
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
				return null;
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
				$var[2]['html'] = $selectVals['last_visit_match'].' '.e107::getParser()->toDate($selectVals['last_visit_date'],'long'); //FIXME use e107 date function.
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

	/**
	 * Manage Sent. 
	 */
	public function sent($data) // trigerred when email sent from queue.
	{
		if($data['status'] == 1) // Successfully sent
		{
			// e107::getLog()->add($this->mailerSource . ' email sent', $data, E_LOG_INFORMATIVE, 'SENT');		
			return true;
		}
		else // Failed 
		{
			// e107::getLog()->add($this->mailerSource . ' email not sent', $data, E_LOG_FATAL, 'SENT');		
			return false;
		}
	}



}



