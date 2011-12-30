<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Event calendar plugin - cron task
 *
 * $URL$
 * $Id$
*/

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }


include_lan(e_PLUGIN.'/calendar_menu/languages/English_mailer.php');

class calendar_menu_cron // include plugin-folder in the name.
{
	private $logRequirement = 0;			// Flag to determine logging level
	private $debugLevel = 0;				// Used for internal debugging
	private $logHandle = NULL;
	private	$ecalClass;						// Calendar library routines
	private $e107;
	private $defaultMessage = array();		// Used where nothing special defined
	private $startTime;						// Start date for processing
	private	$mailManager;
	private	$ourDB;							// Used for some things


	public function __construct()
	{
		$this->e107 = e107::getInstance();
		//$this->debugLevel = 2;
	}



	/**
	 * Cron configuration
	 *
	 * Defines one or more cron tasks to be performed
	 *
	 * @return array of task arrays
	 */
	public function config()
	{
		$cron = array();
		$cron[] = array('name' => LAN_EC_MAIL_04, 'category' => 'plugin', 'function' => 'processSubs', 'description' => LAN_EC_MAIL_05);
		return $cron;
	}
	
	
	private function checkDB()
	{
		if ($this->ourDB == NULL)
		{
			$this->ourDB = new db;
		}
	}

	
	/**
	 * Logging routine - writes lines to a text file
	 *
	 * Auto-opens log file (if necessary) on first call
	 * 
	 * @param string $logText - body of text to write
	 * @param boolean $closeAfter - if TRUE, log file closed before exit; otherwise left open
	 *
	 * @return none
	 */
	function logLine($logText, $closeAfter = FALSE, $addTimeDate = FALSE)
	{
		if ($this->logRequirement == 0) return;

		$logFilename = e_LOG.'calendar_mail.txt';
		if ($this->logHandle == NULL)
		{
			if (!($this->logHandle = fopen($logFilename, "a"))) 
			{ // Problem creating file?
				echo "File open failed!<br />";
				$this->logRequirement = 0; 
				return; 
			}
		}
	  
		if (fwrite($this->logHandle,($addTimeDate ? date('D j M Y G:i:s').': ' : '').$logText."\r\n") == FALSE) 
		{
			$this->logRequirement = 0; 
			echo 'File write failed!<br />';
		}
	  
		if ($closeAfter)
		{
			fclose($this->logHandle);
			$this->logHandle = NULL;
		}
	}

	
	
	/**
	 * Called to process the calendar menu subscriptions list - the cron task must be set to call us once/day (typically at about 0100)
	 * 
	 * Emails are added to the queue.
	 * Various events are logged in a text file
	 *
	 * @return none
	 */
	public function processSubs()
	{
		global $pref;

		require_once(e_PLUGIN.'calendar_menu/ecal_class.php');
		$this->ecalClass = new ecal_class;

		e107::getScParser();
		require_once(e_PLUGIN.'calendar_menu/calendar_shortcodes.php');
		if (is_readable(THEME.'ec_mailout_template.php')) 
		{  // Has to be require
			require(THEME.'ec_mailout_template.php');
		}
		else 
		{
			require(e_PLUGIN.'calendar_menu/ec_mailout_template.php');
		}


		$this->startTime = mktime(0, 0, 0, date('n'), date('d'), date('Y'));	// Date for start processing
		setScVar('event_calendar_shortcodes', 'ecalClass', &$this->ecalClass);			// Give shortcode a pointer to calendar class


		$this->logRequirement = varset($pref['eventpost_emaillog'], 0);
		if ($this->debugLevel >= 2) $this->logRequirement = 2;		// Force full logging if debug


		// Start of the 'real' code
		$this->logLine("\r\n\r\n".LAN_EC_MAIL_06.date('D j M Y G:i:s'));


		// Start with the 'in advance' emails
		$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class>0) and 
		event_cat_last < " . intval($this->startTime) . " and 
		event_cat_ahead > 0 and 
		event_start >= (" . intval($this->startTime) . "+(86400*(event_cat_ahead))) and
		event_start <  (" . intval($this->startTime) . "+(86400*(event_cat_ahead+1))) and
		find_in_set(event_cat_notify,'1,3,5,7')";

		$this->sendMailshot($cal_args, 'Advance',1, $calendar_shortcodes);


		$insertString = 'event_cat_today < '.intval($this->startTime).' and';
		if ($this->debugLevel > 0) $insertString = '';		// Allows us to so a mailshot every call of cron tick

		// then for today
		$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class>0) and 
		{$insertString} event_start >= (" . intval($this->startTime) . ") and
		event_start <  (86400+" . intval($this->startTime) . ") and
		find_in_set(event_cat_notify,'2,3,6,7')";

		$this->sendMailshot($cal_args, 'today',2, $calendar_shortcodes);


		// Finally do 'day before' emails (its an alternative to 'today' emails)
		$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class>0) and 
		{$insertString} event_start >= (" . intval($this->startTime) ." + 86400 ) and
		event_start <  (" . intval($this->startTime) ." + 172800) and
		find_in_set(event_cat_notify,'4,5,6,7')";

		$this->sendMailshot($cal_args, 'tomorrow',2, $calendar_shortcodes);


		$this->logLine(' .. Run completed',TRUE, TRUE);
		return TRUE;
	}


	// Function called to load in default templates (messages) if required - only accesses database once
	function loadDefaultMessages()
	{
		if (($this->defaultMessage[1] != '') && ($this->defaultMessage[2] != '')) return;
		$this->checkDB();
		if ($this->ourDB->db_Select('event_cat', 'event_cat_msg1,event_cat_msg2', "event_cat_name = '".EC_DEFAULT_CATEGORY."' "))
		{ 
			if ($row = $this->ourDB->db_Fetch())
			{
				$this->defaultMessage[1] = $row['event_cat_msg1'];
				$this->defaultMessage[2] = $row['event_cat_msg2'];
			}
		}
		// Put in generic message rather than nothing - will help flag omission
		if ($this->defaultMessage[1] == '') $this->defaultMessage[1] = EC_LAN_146;
		if ($this->defaultMessage[2] == '') $this->defaultMessage[2] = EC_LAN_147;
	}


	private function checkMailManager()
	{
		if ($this->mailManager == NULL)
		{
			require_once(e_HANDLER .'mail_manager_class.php');
			$this->mailManager = new e107MailManager();
		}
	}


	/*
	  Function to actually send a mailshot
	*/
	function sendMailshot($cal_query, $shot_type, $msg_num, $calendar_shortcodes)
	{
		global $pref;
	  
		if ($this->logRequirement > 1)
		{
			$this->logLine(' Starting emails for '.$shot_type, FALSE, TRUE);
			if ($this->debugLevel >= 2) $this->logLine("\r\n    Query is: ".$cal_query);
		}

		if  ($num_cat_proc = $this->e107->sql->db_Select_gen($cal_query))
		{  // Got at least one event to process here
			if ($this->logRequirement > 1)
				$this->logLine(' - '.$num_cat_proc.' categories found to process');

			$this->checkDB();		// Make sure we've got another DB object
			while ($thisevent = $this->e107->sql->db_Fetch())
			{  // Process one event at a time

				$this->logLine('    Processing event: '.$event_title);
				setScVar('event_calendar_shortcodes', 'event', $thisevent);			// Save current values in shortcode

				// Note that event processed, and generate the email
				if ($msg_num == 1)
				{
					$this->ourDB->db_Update('event_cat', 'event_cat_last='.time().' where event_cat_id='.intval($event_cat_id));
					$cal_msg = $thisevent['event_cat_msg1'];
				}
				else
				{
					$this->ourDB->db_Update('event_cat', 'event_cat_today='.time().' where event_cat_id='.intval($event_cat_id));
					$cal_msg = $thisevent['event_cat_msg2'];
				}

				if (trim($cal_msg) == '') 
				{
					$this->loadDefaultMessages();
					$cal_msg = $this->defaultMessage[$msg_num];
				}


				// Parsing the template here means we can't use USER-related shortcodes
				// Main ones which are relevant: MAIL_DATE_START, MAIL_TIME_START, MAIL_DATE_END,
				//	MAIL_TIME_END, MAIL_TITLE, MAIL_DETAILS, MAIL_CATEGORY, MAIL_LOCATION, 
				//	MAIL_CONTACT, MAIL_THREAD (maybe). Also MAIL_LINK, MAIL_SHORT_DATE 
				// Best to strip entities here rather than at entry - handles old events as well
				// Note that certain user-related substitutions will work, however - |USERID|, |USERNAME|, |DISPLAYNAME|
				$cal_title = html_entity_decode($this->e107->tp->parseTemplate($pref['eventpost_mailsubject'], TRUE),ENT_QUOTES,CHARSET);
				$cal_msg = html_entity_decode($this->e107->tp->parseTemplate($cal_msg, TRUE),ENT_QUOTES,CHARSET);
			  
				// Four cases for the query:
				//	1. No forced mailshots - based on event_subs table only									Need INNER JOIN
				//	2. Forced mailshot to members - send to all users (don't care about subscriptions)		Don't need JOIN
				// 	3. Forced mailshot to group of members - based on user table only						Don't need JOIN
				//	4. Forced mailshot to group, plus optional subscriptions - use the lot!    				Need LEFT JOIN
				// (Always block sent to banned members)
				$manual_subs = (isset($pref['eventpost_asubs']) && ($pref['eventpost_asubs'] == '1'));
				$subs_fields = '';
				$subs_join = '';
				$whereClause = '';
				$group_clause = '';
			  
				if ($event_cat_force_class != e_UC_MEMBER)
				{  // Cases 1, 3, 4 (basic query does for case 2)
					if ((!$thisevent['event_cat_force_class']) || ($manual_subs))
					{  // Cases 1 & 4 - need to join with event_subs database
						$subs_fields = ", es.* ";
						if ($thisevent['event_cat_force_class']) $subs_join = 'LEFT'; else $subs_join = 'INNER';
						$subs_join   .= ' join `#event_subs` AS es on u.`user_id`=es.`event_userid` ';
						$whereClause = ' es.`event_cat`='.intval($thisevent['event_category']).' ';
						$group_clause = ' GROUP BY u.`user_id`';
					}

					if ($event_cat_force_class)
					{  // cases 3 and 4 - ... and check for involuntary subscribers
						if ($whereClause) $whereClause .= ' OR ';
						if ($thisevent['event_cat_force_class'] == e_UC_ADMIN)
						{
							$whereClause .= '(u.`user_admin` = 1 )';
						}
						else
						{
							$whereClause .= "find_in_set('".intval($thisevent['event_cat_force_class'])."', u.`user_class`)";
							$group_clause = ' GROUP BY u.`user_id`';
						}
					}

					if ($whereClause) $whereClause = ' AND ('.$whereClause.' ) ';
				}   // End of cases 1, 3, 4
			  
				$cal_emilargs = "SELECT u.`user_id`, u.`user_class`, u.`user_email`, u.`user_name`, u.`user_ban`, u.`user_admin`{$subs_fields}
				  from `#user` AS u {$subs_join}
					  WHERE u.`user_ban` = 0 {$whereClause} {$group_clause}";
				  

				if ($this->debugLevel >= 2)
				{
					$this->logLine("\r\n    Email selection query is: ".$cal_emilargs);
				}
				if ($num_shots = $this->ourDB->db_Select_gen($cal_emilargs))
				{
					$this->logLine(' - '.$num_shots.' emails found to send');

					// Definitely got some emails to send here
					$this->checkMailManager();			// Make sure our mail manager is loaded

					// Start by adding the email details
					$email = array(
						'mail_create_app' => 'calendar_menu',
						'mail_title' => str_replace('--REF--', intval(time()/3600), LAN_EC_MAIL_07),
						'mail_subject' => $cal_title,
						'mail_body' => $cal_msg,
						'mail_sender_email' => $pref['eventpost_mailaddress'],
						'mail_sender_name' => $pref['eventpost_mailfrom'],
						'mail_send_style' => 'textonly'
					);
					if (FALSE === ($mailMainID = $this->mailManager->saveEmail($email, TRUE)))
					{
						$this->logLine('Error adding mail body to database - run abandoned');
						break;
					}
					$this->mailManager->mailInitCounters($mailMainID);			// Initialise counters for emails added


					// Now loop through adding users
					while ($row = $this->ourDB->db_Fetch())
					{
						if ($this->debugLevel == 0)
						{
							$recipient = array(
								'mail_recipient_id' => $row['user_id'],
								'mail_recipient_name' => $row['user_name'],
								'mail_recipient_email' => $row['user_email'],
								'mail_target_info' => array(					// Adding this info means it could be substituted
									'USERID' => $row['user_id'],
									'DISPLAYNAME' => $row['user_name'],
									'USERNAME' => $row['user_loginname']
									)
							);
							$result = $this->mailManager->mailAddNoDup($mailMainID, $recipient, MAIL_STATUS_TEMP);
							if ($result === FALSE)
							{
								$this->logLine("Error adding recipient {$row['user_id']}");
							}
						}
						else
						{
							$send_result = " **DEBUG**";
						}
						if ($this->logRequirement > 1)
						{
							$this->logLine('      Send to '.$user_id.':'.$user_email.' Name: '.$user_name.' Result = '.$send_result);
						}
					}
					$this->mailManager->mailUpdateCounters($mailMainID);			// Save counters to DB
					if ($this->mailManager->activateEmail($mailMainID, FALSE, time() + 80000) === TRUE)
					{
						$this->logLine("Email {$mailMainID} activated");
					}
					else
					{
						$this->logLine("Error activating email {$mailMainID}");
					}
				} 
				elseif ($num_cat === FALSE)
				{
					$this->logLine('  User read error for '.$shot_type.': '.$this->ourDB->$mySQLlastErrNum.':'.$this->ourDB->$mySQLlastErrText);
				}
				elseif ($this->logRequirement > 1)
				{
					$this->logLine('  - no users found.');
				}
			} // while

			if ($this->logRequirement > 1)
			{
				$this->logLine('  Completed emails for '.$shot_type.' at '.date('D j M Y G:i:s'));
			}
		}
		elseif ($num_cat === FALSE)
		{
			$this->logLine('  DB read error for '.$shot_type.': '.$this->e107->sql->$mySQLlastErrNum.':'.$this->e107->sql->$mySQLlastErrText);
		}
		elseif ($this->logRequirement > 1)
		{
			$this->logLine('  - no records found.');
		}
	}
}



?>