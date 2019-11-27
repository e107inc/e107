<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Mailout - mail database API and utility routines
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_handlers/redirection_class.php $
 * $Id: redirection_class.php 11922 2010-10-27 11:31:18Z secretr $
 * $Revision: 12125 $
*/

/**
 * 
 *	@package     e107
 *	@subpackage	e107_handlers
 *	@version 	$Id: mail_manager_class.php 12125 2011-04-08 05:11:38Z e107coders $;
 *
 *	@todo - consider whether to extract links in text-only emails
 *	@todo - support separate template for the text part of emails

This class isolates the caller from the underlying database used to buffer and send emails.
Also includes a number of useful routines

This is the 'day to day' module - there's an admin class which extends this one.

There are two parts to the database:
	a) Email body (including attachments etc)
	b) Target recipients - potentially including target-specific values to substitute

There is an option to override the style information sent if the email is to include 
theme-related information. Create file 'emailstyle.css' in the current theme directory, and this
will be included in preference to the current theme style.



Event Triggers generated
------------------------
	mailbounce - when an email bounce is received
	maildone - when the sending of a complete bulk email is complete (also does 'Notify' event)


Database tables
---------------
mail_recipients			- Details of individual recipients (targets) of an email
	mail_target_id		Unique ID for this target/email combination
	mail_recipient_id	User ID (if registered user), else zero
	mail_recipient_email Email address of recipient
	mail_recipient_name	Name of recipient
	mail_status			Status of this  entry - see define() statements below
	mail_detail_id		Email body link
	mail_send_date		Earliest date/time when email may be sent. Once mail sent, actual time/date of sending (or time of failure to send)
	mail_target_info	Array of target-specific info for substitution into email. Key is the code in the email body, value is the substitution

mail_content			- Details of the email to be sent to a number of people
	mail_source_id
	mail_content_status	Overall status of mailshot record - See define() statements below
	mail_togo_count		Number of recipients to go
	mail_sent_count		Number of successful sends (including bounces)
	mail_fail_count		Number of unsuccessful sends
	mail_bounce_count	Number of bounced emails 
	mail_start_send		Time/date of sending first email
	mail_end_send		Time/date of sending last email
	mail_create_date
	mail_creator		User ID
	mail_create_app		ID string for application/plugin creating mail
	mail_e107_priority	Our internal priority - generally high for single emails, low for bulk emails
	mail_notify_complete Notify options when email complete
	mail_last_date		Don't send after this date/time
	mail_title			A description of the mailout - not sent
	mail_subject		Subject line
	mail_body			Body text - the 'raw' text as entered/specified by the user
	mail_body_templated	Complete body text after applying the template, but before any variable substitutions
	mail_other			Evaluates to an array of misc info - cc, bcc, attachments etc

mail_other constituents:
	mail_sender_email	Sender's email address
	mail_sender_name	Sender's name
	mail_copy_to		Any recipients to copy
	mail_bcopy_to		Any recipients to BCC
	mail_attach			Comma-separated list of attachments
	mail_send_style		Send style -  HTML, text, template name etc
	mail_selectors		Details of the selection criteria used for recipients (Only used internally)
	mail_include_images	TRUE if to embed images, FALSE to add link to them
	mail_body_alt		If non-empty, use for alternate email text (generally the 'plain text' alternative)
	mail_overrides		If non-empty, any overrides for the mailer, set by the template



Within internal arrays, a flat structure is adopted, with 'mail_other' merged with the rest of the 'mail_content' values.
Variables relating to DB values all begin 'mail_' - others are internal (volatile) control variables

*/

if (!defined('e107_INIT')) { exit; }

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_mailout.php');		// May be needed by anything loading this class

define('MAIL_STATUS_SENT', 0);			// Mail sent. Email handler happy, but may have bounced (or may be yet to bounce)
define('MAIL_STATUS_BOUNCED', 1);
define('MAIL_STATUS_CANCELLED', 2);
define('MAIL_STATUS_PARTIAL', 3);		// A run which was abandoned - errors, out of time etc
define('MAIL_STATUS_FAILED', 5);		// Failure on initial send - rejected by selected email handler
										// This must be the numerically highest 'processing complete' code
define('MAIL_STATUS_PENDING', 10);		// Mail which is in the sending list (even if outside valid sending window)
										// This must be the numerically lowest 'not sent' code
										// E107_EMAIL_MAX_TRIES values used in here for retry counting
define('MAIL_STATUS_MAX_ACTIVE', 19);	// Highest allowable 'not sent or processed' code
define('MAIL_STATUS_SAVED', 20);		// Identifies an email which is just saved (or in process of update)
define('MAIL_STATUS_HELD',21);			// Held pending release
define('MAIL_STATUS_TEMP', 22);			// Tags entries which aren't yet in any list


class e107MailManager
{
	const	E107_EMAIL_PRIORITY_LOW = 1;		// 'E107' priorities, to determine what to do next.
	const	E107_EMAIL_PRIORITY_MED = 3;		// Distinct from the priority which can be assigned to the...
	const	E107_EMAIL_PRIORITY_HIGH = 5;		// actual email when sending. Use LOW or MED for bulk mail, HIGH for individual emails.
	
	const	E107_EMAIL_MAX_TRIES = 3;			// Maximum number of tries by us (mail server may do more)
												// - max allowable value is MAIL_STATUS_MAX_ACTIVE - MAIL_STATUS_PENDING 

	private		$debugMode = false;
	protected	$e107;
	protected	$db = NULL;					// Use our own database object - this one for reading data
	protected	$db2 = NULL;				// Use our own database object - this one for updates
	protected	$queryActive = FALSE;		// Keeps track of unused records in currently active query
	protected	$mailCounters = array();	// Counters to track adding recipients
	protected	$queryCount = array();		// Stores total number of records if SQL_CALC_ROWS is used (index = db object #)
	protected	$currentBatchInfo = array();	// Used during batch send to hold info about current mailout
	protected	$currentMailBody = '';			// Buffers current mail body
	protected	$currentTextBody = '';			// Alternative text body (if required)

	protected	$mailer = NULL;				// Mailer class when required
	protected	$mailOverrides = FALSE;		// Any overrides to be passed to the mailer


	// Array defines DB types to be used
	protected	$dbTypes = array(
		'mail_recipients' => array
		(
			'mail_target_id'  	=> 'int',
			'mail_recipient_id' => 'int',
			'mail_recipient_email' => 'todb',
			'mail_recipient_name' => 'todb',
			'mail_status' 		=> 'int',
			'mail_detail_id' 	=> 'int',
			'mail_send_date' 	=> 'int',
			'mail_target_info'	=> 'string'			// Don't want entities here!
		),
		'mail_content' => array(
			'mail_source_id' 	=> 'int',
			'mail_content_status' => 'int',
			'mail_total_count' 	=> 'int',
			'mail_togo_count' 	=> 'int',
			'mail_sent_count' 	=> 'int',
			'mail_fail_count' 	=> 'int',
			'mail_bounce_count' => 'int',
			'mail_start_send' 	=> 'int',
			'mail_end_send' 	=> 'int',
			'mail_create_date' 	=> 'int',
			'mail_creator' 		=> 'int',
			'mail_create_app' 	=> 'todb',
			'mail_e107_priority' => 'int',
			'mail_notify_complete' => 'int',
			'mail_last_date' 	=> 'int',
			'mail_title' 		=> 'todb',
			'mail_subject' 		=> 'todb',
			'mail_body' 		=> 'todb',
			'mail_body_templated' => 'todb',
			'mail_other' 		=> 'string',		// Don't want entities here!
			'mail_media'        => 'string'
		)
	);
	
	// Array defines defaults for 'NOT NULL' fields where a default can't be set in the field definition
	protected	$dbNull = array('mail_recipients' => array
		(
			'mail_target_info' => ''
		),
		'mail_content' => array(
			'mail_body' => '',
			'mail_body_templated' => '',
			'mail_other' => ''
		)
	);

	// List of fields which are combined into the 'mail_other' field of the email
	protected	$dbOther = array(
					'mail_sender_email' => 1,
					'mail_sender_name'	=> 1,
					'mail_copy_to'		=> 1,
					'mail_bcopy_to'		=> 1,
					'mail_attach'		=> 1,
					'mail_send_style'	=> 1,			// HTML, text, template name etc
					'mail_selectors'	=> 1,			// Only used internally
					'mail_include_images' => 1,			// Used to determine whether to embed images, or link to them
					'mail_body_alt'		=> 1,			// If non-empty, use for alternate email text (generally the 'plain text' alternative)
					'mail_overrides'	=> 1
		);
	
	// List of fields which are the status counts of an email, and their titles
	protected	$mailCountFields = array(
			'mail_togo_count' 	=> LAN_MAILOUT_83,
			'mail_sent_count' 	=> LAN_MAILOUT_82,
			'mail_fail_count' 	=> LAN_MAILOUT_128,
			'mail_bounce_count' => LAN_MAILOUT_144,
		);

	/**
	 * Constructor
	 * 
	 *
	 * @return void
	 */
	public function __construct($overrides = array())
	{
		$this->e107 = e107::getInstance();

		$pref = e107::pref('core');

		$bulkmailer = (!empty($pref['bulkmailer'])) ? $pref['bulkmailer'] : $pref['mailer'];

	//	if($overrides === false)
	//	{
			$overrides['mailer'] = $bulkmailer;
	//	}

		$this->mailOverrides = $overrides;
		
		if(deftrue('e_DEBUG_BULKMAIL'))
		{
			$this->debugMode = true;
		}
		
		if($this->debugMode === true)
		{
			e107::getMessage()->addWarning('Debug Mode is active. Emailing will only be simulated!');	
		}
		
		
	}


	/**
	 * Generate an array of data which can be passed directly to the DB routines.
	 * Only valid DB fields are copied
	 * Combining/splitting of fields is done as necessary
	 * (This is essentially the translation between internal storage format and db storage format. If
	 * the DB format changes, only this routine and its counterpart should need changing)
	 *
	 * @param $data - array of email-related data in internal format
	 * @param $addMissing - if TRUE, undefined fields are added
	 *
	 * @return void
	 */
	public function mailToDb(&$data, $addMissing = false)
	{
		$res = array();
		$res1 = array();
		// Generate the 'mail_other' array first
		foreach ($this->dbOther as $f => $v)
		{
			if (isset($data[$f]))
			{
				$res1[$f] = $data[$f];
			}
			elseif ($addMissing)
			{
				$res1[$f] = '';
			}
		}
		
		// Now do the main email array
		foreach ($this->dbTypes['mail_content'] as $f => $v)
		{
			if (isset($data[$f]))
			{
				$res[$f] = $data[$f];
			}
			elseif ($addMissing)
			{
				$res[$f] = '';
			}
		}

		$res['mail_other'] = e107::serialize($res1,false);	// Ready to write to DB

		if (!empty($res['mail_media']))
		{
			$res['mail_media'] = e107::serialize($res['mail_media']);
		}

		return $res;
	}


	/**
	 * Given an array (row) of data retrieved from the DB table, converts to internal format.
	 * Combining/splitting of fields is done as necessary
	 * (This is essentially the translation between internal storage format and db storage format. If
	 * the DB format changes, only this routine and its counterpart should need changing)
	 *
	 * @param $data - array of DB-sourced email-related data
	 * @param $addMissing - if TRUE, undefined fields are added
	 *
	 * @return array of data
	 */
	public function dbToMail(&$data, $addMissing = FALSE)
	{
		$res = array();
		
		foreach ($this->dbTypes['mail_content'] as $f => $v)
		{
			if (isset($data[$f]))
			{
				$res[$f] = $data[$f];
			}
			elseif ($addMissing)
			{
				$res[$f] = '';
			}
		}
		if (isset($data['mail_other']))
		{
			
			$tmp = e107::unserialize(str_replace('\\\'', '\'',$data['mail_other']));	// May have escaped data
			if (is_array($tmp))
			{
				$res = array_merge($res,$tmp);
			}
			else
			{
				$res['Array_ERROR'] = 'No array found';
			}
			unset($res['mail_other']);
		}
		if ($addMissing)
		{
			foreach ($this->dbOther as $f => $v)
			{
				$res[$f] = '';
			}
		}
		
		if (isset($data['mail_media']))
		{
			$res['mail_media'] = e107::unserialize($data['mail_media']);	
		}
		
		return $res;
	}



	/**
	 * Generate an array of mail recipient data which can be passed directly to the DB routines.
	 * Only valid DB fields are copied
	 * Combining/splitting of fields is done as necessary
	 * (This is essentially the translation between internal storage format and db storage format. If
	 * the DB format changes, only this routine and its counterpart should need changing)
	 *
	 * @param $data - array of email target-related data in internal format
	 * @param $addMissing - if TRUE, undefined fields are added
	 *
	 * @return void
	 */
	public function targetToDb(&$data, $addMissing = FALSE)
	{	// Direct correspondence at present (apart from needing to convert potential array $data['mail_target_info']) - but could change
		$res = array();
		foreach ($this->dbTypes['mail_recipients'] as $f => $v)
		{
			if (isset($data[$f]))
			{
				$res[$f] = $data[$f];
			}
			elseif ($addMissing)
			{
				$res[$f] = '';
			}
		}
		if (isset($data['mail_target_info']) && is_array($data['mail_target_info']))
		{
			$tmp = e107::serialize($data['mail_target_info'], TRUE);
			$res['mail_target_info'] = $tmp;
		}
		return $res;
	}



	/**
	 * Given an array (row) of data retrieved from the DB table, converts to internal format.
	 * Combining/splitting of fields is done as necessary
	 * (This is essentially the translation between internal storage format and db storage format. If
	 * the DB format changes, only this routine and its counterpart should need changing)
	 *
	 * @param $data - array of DB-sourced target-related data
	 * @param $addMissing - if TRUE, undefined fields are added
	 *
	 * @return void
	 */
	public function dbToTarget(&$data, $addMissing = FALSE)
	{	// Direct correspondence at present - but could change
		$res = array();
		foreach ($this->dbTypes['mail_recipients'] as $f => $v)
		{
			if (isset($data[$f]))
			{
				$res[$f] = $data[$f];
			}
			elseif ($addMissing)
			{
				$res[$f] = '';
			}
		}
		if (isset($data['mail_target_info']))
		{
			$tmp = e107::unserialize($data['mail_target_info']);
			$res['mail_target_info'] = $tmp;
		}
		return $res;
	}




	/**
	 * Given an array (row) of data retrieved from the DB table, converts to internal format.
	 * Combining/splitting of fields is done as necessary
	 * This version intended for 'Joined' reads which have both recipient and content data
	 *
	 * @param $data - array of DB-sourced target-related data
	 * @param $addMissing - if TRUE, undefined fields are added
	 *
	 * @return array
	 */
	public function dbToBoth(&$data, $addMissing = FALSE)
	{	
		$res = array();
		$oneToOne = array_merge($this->dbTypes['mail_content'], $this->dbTypes['mail_recipients']);		// List of valid elements


		// Start with simple 'one to one' fields
		foreach ($oneToOne as $f => $v)
		{
			if (isset($data[$f]))
			{
				$res[$f] = $data[$f];
			}
			elseif ($addMissing)
			{
				$res[$f] = '';
			}
		}

		// Now array fields
		if (isset($data['mail_other']))
		{
			$tmp = e107::unserialize(str_replace('\\\'', '\'',$data['mail_other']));	// May have escaped data
			if (is_array($tmp))
			{
				$res = array_merge($res,$tmp);
			}
			unset($res['mail_other']);
		}
		elseif ($addMissing)
		{
			foreach ($this->dbOther as $f => $v)
			{
				$res[$f] = '';
			}
		}
		if (isset($data['mail_target_info']))
		{
			$clean = stripslashes($data['mail_target_info']);
			$tmp = e107::unserialize($clean);	// May have escaped data

			$res['mail_target_info'] = $tmp;
		}
		
		if (isset($data['mail_media']))
		{
			$res['mail_media'] = e107::unserialize($data['mail_media']);	
		}
		
		return $res;
	}




	/**
	 * Set the internal debug/logging level
	 *
	 * @return void
	 */
	public function controlDebug($level = 0)
	{
		$this->debugMode = $level;
	}



	/**
	 *	Internal function to create a db object for our use if none exists
	 */
	protected function checkDB($which = 1)
	{
		if (($which == 1) && ($this->db == null))
		{
			$this->db = e107::getDb('mail1');
		}
		if (($which == 2) && ($this->db2 == null))
		{
			$this->db2 = e107::getDb('mail2');;
		}
	}


	/**
	 * Internal function to create a mailer object for our use if none exists
	 */
	protected function checkMailer()
	{
		if ($this->mailer != NULL) return;
		if (!class_exists('e107Email'))
		{
			require_once(e_HANDLER.'mail.php');
		}
		$this->mailer = new e107Email($this->mailOverrides);
	}



	/** 
	 *	Set the override values for the mailer object.
	 *
	 *	@param array $overrides - see mail.php for details of accepted values
	 *
	 *	@return boolean TRUE if accepted, FALSE if rejected
	 */
	public function setMailOverrides($overrides)
	{
		if ($this->mailer != NULL) return FALSE;		// Mailer already created - it's too late!
		$this->mailOverrides = $overrides;
	}




	/**
	 * Convert numeric representation of mail status to a text string
	 * 
	 * @param integer $status - numeric value of status
	 * @return string text value
	 */
	public function statusToText($status)
	{
		switch (intval($status))
		{
			case MAIL_STATUS_SENT :
				return LAN_MAILOUT_211;
			case MAIL_STATUS_BOUNCED :
				return LAN_MAILOUT_213;
			case MAIL_STATUS_CANCELLED :
				return LAN_MAILOUT_218;
			case MAIL_STATUS_PARTIAL :
				return LAN_MAILOUT_219;
			case MAIL_STATUS_FAILED :
				return LAN_MAILOUT_212;
			case MAIL_STATUS_PENDING :
				return LAN_MAILOUT_214;
			case MAIL_STATUS_SAVED :
				return LAN_MAILOUT_215;
			case MAIL_STATUS_HELD :
				return LAN_MAILOUT_217;
			default :
				if (($status > MAIL_STATUS_PENDING) && ($status <= MAIL_STATUS_ACTIVE)) return LAN_MAILOUT_214;
		}
		return LAN_MAILOUT_216.' ('.$status.')';		// General coding error
	}



	/**
	 * Select the next $count emails in the send queue
	 * $count gives the maximum number. '*' does 'select all'
	 * @return boolean|handle Returns FALSE on error.
	 * 		 Returns a 'handle' on success (actually the ID in the DB of the email)
	 */
	public function selectEmails($count = 1)
	{
		if (is_numeric($count))
		{
			if ($count < 1) $count = 1;
			$count = ' LIMIT '.$count;
		}
		else
		{
			$count = '';
		}
		$this->checkDB(1);			// Make sure DB object created
		$query = "SELECT mt.*, ms.* FROM `#mail_recipients` AS mt
							LEFT JOIN `#mail_content` AS ms ON mt.`mail_detail_id` = ms.`mail_source_id`
							WHERE ms.`mail_content_status` = ".MAIL_STATUS_PENDING." 
							AND mt.`mail_status` >= ".MAIL_STATUS_PENDING." 
							AND mt.`mail_status` <= ".MAIL_STATUS_MAX_ACTIVE." 
							AND mt.`mail_send_date` <= ".time()." 
							AND (ms.`mail_last_date` >= ".time()." OR ms.`mail_last_date`=0)
							ORDER BY ms.`mail_e107_priority` DESC, mt.mail_target_id ASC {$count}";
//		echo $query.'<br />';
		$result = $this->db->gen($query);
		
		if ($result !== FALSE)
		{
			$this->queryActive = $result;		// Note number of emails to go
		}
		return $result;
	}


	/**
	 * Get next email from selection (usually from selectEmails() )
	 * @return Returns array of email data if available - FALSE if no further data, no active query, or other error
	 */
	public function getNextEmail()
	{
		if (!$this->queryActive)
		{
			return false;
		}
		if ($result = $this->db->fetch())
		{
			$this->queryActive--;
			return $this->dbToBoth($result);
		}
		else
		{
			$this->queryActive = false;		// Make sure no further attempts to read emails
			return false;
		}
	}


	/**
	 * Call to see whether any emails left to try in current selection
	 * @return Returns number left unread in query - FALSE if no active query
	 */
	public function emailsToGo()
	{
		return $this->queryActive;			// Just return saved number
	}


	/**
	 * Call to send next email from selection
	 * 
	 * @return Returns TRUE if successful, FALSE on fail (or no more to go)
	 *
	 *	@todo Could maybe save parsed page in cache if more than one email to go
	 */
	public function sendNextEmail()
	{
		$counterList = array('mail_source_id','mail_togo_count', 'mail_sent_count', 'mail_fail_count', 'mail_start_send');

		if (($email = $this->getNextEmail()) === false)
		{
			return false;
		}


		/**
		 *	The $email variable has all the email data in 'flat' form, including that of the current recipient.
		 *	field $email['mail_target_info'] has variable substitution information relating to the current recipient
		 */
		if (count($this->currentBatchInfo))
		{
			//print_a($this->currentBatchInfo);
			if ($this->currentBatchInfo['mail_source_id'] != $email['mail_source_id'])
			{	// New email body etc started
				//echo "New email body: {$this->currentBatchInfo['mail_source_id']} != {$email['mail_source_id']}<br />";
				$this->currentBatchInfo = array();		// New source email - clear stored info
				$this->currentMailBody = '';			// ...and clear cache for message body
				$this->currentTextBody = '';
			}
		}
		if (count($this->currentBatchInfo) == 0)
		{
			//echo "First email of batch: {$email['mail_source_id']}<br />";
			foreach ($counterList as $k)
			{
				$this->currentBatchInfo[$k] = $email[$k];		// This copies across all the counts
			}
		}

		if (($this->currentBatchInfo['mail_sent_count'] > 0) || ($this->currentBatchInfo['mail_fail_count'] > 0))
		{	// Only send these on first email - otherwise someone could get inundated!
			unset($email['mail_copy_to']);
			unset($email['mail_bcopy_to']);
		}

		$targetData = array();		// Arrays for updated data

		$this->checkMailer();		// Make sure we have a mailer object to play with

		if ($this->currentBatchInfo['mail_start_send'] == 0)
		{
			$this->currentBatchInfo['mail_start_send'] = time();			// Log when we started processing this email
		}

		if (!$this->currentMailBody)
		{
			if (!empty($email['mail_body_templated']))
			{
				$this->currentMailBody = $email['mail_body_templated'];
			}
			else
			{
				$this->currentMailBody = $email['mail_body'];
			}

			$this->currentTextBody = $email['mail_body_alt'];		// May be null
		}


		$mailToSend = $this->makeEmailBlock($email);			// Substitute mail-specific variables, attachments etc



		if($this->debugMode)
		{

			echo "<h3>Preview</h3>";
			$preview = $this->mailer->preview($mailToSend);
			echo $preview;
			echo "<h3>Preview (HTML)</h3>";
			print_a($preview);
			$logName = "mailout_simulation_".$email['mail_source_id'];
			e107::getLog()->addDebug("Sending Email to <".$email['mail_recipient_name']."> ".$email['mail_recipient_email'])->toFile($logName,'Mailout Simulation Log',true);	
			$result = true;


			$this->mailer->setDebug(true);
			echo "<h2>SendEmail()->Body</h2>";
			print_a($this->mailer->Body);
			echo "<h2>SendEmail()->AltBody</h2>";
			print_a($this->mailer->AltBody);
			echo "<h1>_________________________________________________________________________</h1>";
			return;


		}


		$result = $this->mailer->sendEmail($email['mail_recipient_email'], $email['mail_recipient_name'], $mailToSend, TRUE);


		if($this->debugMode)
		{
			return true;
		}

		// Try and send
		

//		return;			// ************************************************** Temporarily stop DB being updated when line active *****************************
		
		$addons = array_keys($email['mail_selectors']); // trigger e_mailout.php addons. 'sent' method. 
	
		foreach($addons as $plug)
		{
			if($plug === 'core')
			{
				continue;
			}
			
			if($cls = e107::getAddon($plug,'e_mailout'))
			{
				$email['status'] = $result;
				
				if(e107::callMethod($cls, 'sent', $email) === false)
				{
					e107::getAdminLog()->add($plug.' sent process failed', $email, E_LOG_FATAL, 'SENT');	
				}
			}		
		}
		// --------------------------
		
		
		
		$this->checkDB(2);			// Make sure DB object created

		// Now update email status in DB. We just create new arrays of changed data
		if ($result === TRUE)
		{	// Success!
			$targetData['mail_status'] = MAIL_STATUS_SENT;
			$targetData['mail_send_date'] = time();
			$this->currentBatchInfo['mail_togo_count']--;
			$this->currentBatchInfo['mail_sent_count']++;
		}
		else
		{	// Failure
		// If fail and still retries, downgrade priority
			if ($targetData['mail_status'] > MAIL_STATUS_PENDING)
			{
				$targetData['mail_status'] = max($targetData['mail_status'] - 1, MAIL_STATUS_PENDING);		// One off retry count
				$targetData['mail_e107_priority'] = max($email['mail_e107_priority'] - 1, 1); 	// Downgrade priority to avoid clag-ups
			}
			else
			{
				$targetData['mail_status'] = MAIL_STATUS_FAILED;
				$this->currentBatchInfo['mail_togo_count'] = max($this->currentBatchInfo['mail_togo_count'] - 1, 0);
				$this->currentBatchInfo['mail_fail_count']++;
				$targetData['mail_send_date'] = time();
			}
		}
		
		if (isset($this->currentBatchInfo['mail_togo_count']) && ($this->currentBatchInfo['mail_togo_count'] == 0))
		{
			$this->currentBatchInfo['mail_end_send'] = time();
			$this->currentBatchInfo['mail_content_status'] = MAIL_STATUS_SENT;
		}

		// Update DB record, mail record with status (if changed). Must use different sql object
		if (count($targetData))
		{
			//print_a($targetData);
			$this->db2->update('mail_recipients', array('data' => $targetData, '_FIELD_TYPES' => $this->dbTypes['mail_recipients'], 'WHERE' => '`mail_target_id` = '.intval($email['mail_target_id'])));
		}
		
		if (count($this->currentBatchInfo))
		{
			//print_a($this->currentBatchInfo);
			$this->db2->update('mail_content', array('data' => $this->currentBatchInfo, 
														'_FIELD_TYPES' => $this->dbTypes['mail_content'], 
														'WHERE' => '`mail_source_id` = '.intval($email['mail_source_id'])));
		}

		if (($this->currentBatchInfo['mail_togo_count'] == 0) && ($email['mail_notify_complete'] > 0)) // Need to notify completion
		{	
			$email = array_merge($email, $this->currentBatchInfo);		// This should ensure the counters are up to date
			$mailInfo = LAN_MAILOUT_247.'<br />'.LAN_TITLE.': '.$email['mail_title'].'<br />'.LAN_MAILOUT_248.$this->statusToText($email['mail_content_status']).'<br />';
			$mailInfo .= '<br />'.LAN_MAILOUT_249.'<br />';
			foreach ($this->mailCountFields as $f => $t)
			{
				$mailInfo .= $t.' => '.$email[$f].'<br />';
			}
			$mailInfo .= LAN_MAILOUT_250;
			$message = array(				// Use same structure for email and notify
					'mail_subject' => LAN_MAILOUT_244.$email['mail_subject'],
					'mail_body' => $mailInfo.'<br />'
				);

			if ($email['mail_notify_complete'] & 1) // Notify email initiator
			{	
				if ($this->db2->select('user', 'user_name, user_email', '`user_id`='.intval($email['mail_creator'])))
				{
					$row = $this->db2->fetch();
					e107::getEmail()->sendEmail($row['user_name'], $row['user_email'], $message,FALSE);
				}
			}
			if ($email['mail_notify_complete'] & 2) // Do e107 notify
			{	
				require_once(e_HANDLER."notify_class.php");
			//	notify_maildone($message); // FIXME
			}
			e107::getEvent()->trigger('maildone', $email);
		}

		return $result;
	}



	/**
	 *	Given an email block, creates an array of data compatible with PHPMailer, including any necessary substitutions
	 * $eml['subject']
		$eml['sender_email']	- 'From' email address
		$eml['sender_name']		- 'From' name
		$eml['replyto']			- Optional 'reply to' field
		$eml['replytonames']	- Name(s) corresponding to 'reply to' field  - only used if 'replyto' used
		$eml['send_html']		- if TRUE, includes HTML part in messages (only those added after this flag)
		$eml['add_html_header'] - if TRUE, adds the 2-line DOCTYPE declaration to the front of the HTML part (but doesn't add <head>...</head>)
		$eml['body']			- message body. May be HTML or text. Added according to the current state of the HTML enable flag
		$eml['attach']			- string if one file, array of filenames if one or more.
		$eml['copy_to']			- comma-separated list of cc addresses.
		$eml['cc_names']  		- comma-separated list of cc names. Optional, used only if $eml['copy_to'] specified
		$eml['bcopy_to']		- comma-separated list
		$eml['bcc_names'] 		- comma-separated list of bcc names. Optional, used only if $eml['copy_to'] specified
		$eml['bouncepath']		- Sender field (used for bounces)
		$eml['returnreceipt']	- email address for notification of receipt (reading)
		$eml['inline_images']	- array of files for inline images
		$eml['priority']		- Email priority (1 = High, 3 = Normal, 5 = low)
		$eml['e107_header']		- Adds specific 'X-e107-id:' header
		$eml['extra_header']	- additional headers (format is name: value
		$eml['wordwrap']		- Set wordwrap value
		$eml['split']			- If true, sends an individual email to each recipient
		$eml['template']		- template to use. 'default'
		$eml['shortcodes']		- array of shortcode values. eg. array('MY_SHORTCODE'=>'12345');
	 */
	protected function makeEmailBlock($email)
	{
		$mailSubsInfo = array(
	 		'subject'       => 'mail_subject',
			'sender_email'  => 'mail_sender_email',
			'sender_name'   => 'mail_sender_name',
			// 'email_replyto'		- Optional 'reply to' field 
			// 'email_replytonames'	- Name(s) corresponding to 'reply to' field  - only used if 'replyto' used
			'copy_to'	    => 'mail_copy_to', 		// - comma-separated list of cc addresses.
			//'email_cc_names' - comma-separated list of cc names. Optional, used only if $eml['email_copy_to'] specified
			'bcopy_to'      => 'mail_bcopy_to',
			// 'email_bcc_names' - comma-separated list of bcc names. Optional, used only if $eml['email_copy_to'] specified
			//'bouncepath'		- Sender field (used for bounces)
			//'returnreceipt'	- email address for notification of receipt (reading)
			//'email_inline_images'	- array of files for inline images
			//'priority'		- Email priority (1 = High, 3 = Normal, 5 = low)
			//'extra_header'	- additional headers (format is name: value
			//'wordwrap'		- Set wordwrap value
			//'split'			- If true, sends an individual email to each recipient
			'template'		=> 'mail_send_style', // required
			'shortcodes'	=> 'mail_target_info', // required
			'e107_header'   => 'mail_recipient_id'

			);




		$result = array();


		if (!isset($email['mail_source_id'])) $email['mail_source_id'] = 0;
		if (!isset($email['mail_target_id'])) $email['mail_target_id'] = 0;
		if (!isset($email['mail_recipient_id'])) $email['mail_recipient_id'] = 0;





		foreach ($mailSubsInfo as $k => $v)
		{
			if (isset($email[$v]))
			{
				$result[$k] = $email[$v];
				//unset($email[$v]);
			}
		}


		// Do any substitutions
		$search = array();
		$replace = array();
		foreach ($email['mail_target_info'] as $k => $v)
		{
			$search[] = '|'.$k.'|';
			$replace[] = $v;
		}

		$result['email_body'] = str_replace($search, $replace, $this->currentMailBody);

		if ($this->currentTextBody)
		{
			$result['mail_body_alt'] = str_replace($search, $replace, $this->currentTextBody);
		}

		$result['send_html'] = ($email['mail_send_style'] != 'textonly');
		$result['add_html_header'] = FALSE;				// We look after our own headers


		
		// Set up any extra mailer parameters that need it
		if (!vartrue($email['e107_header']))
		{
			$temp = intval($email['mail_recipient_id']).'/'.intval($email['mail_source_id']).'/'.intval($email['mail_target_id']).'/';
			$result['e107_header'] = $temp.md5($temp);		// Set up an ID
		}
		
		if (isset($email['mail_attach']) && (trim($email['mail_attach']) || is_array($email['mail_attach'])))
		{
			$tp = e107::getParser();
			
			if (is_array($email['mail_attach']))
			{
				foreach ($email['mail_attach'] as $k => $v)
				{
					$result['email_attach'][$k] = $tp->replaceConstants($v);
				}
			}
			else
			{
				$result['email_attach'] = $tp->replaceConstants(trim($email['mail_attach']));
			}
		}
		
		if (isset($email['mail_overrides']) && is_array($email['mail_overrides']))
		{
			 $result = array_merge($result, $email['mail_overrides']);
		}
		
	//	$title = "<h4>".__METHOD__." Line: ".__LINE__."</h4>";
	//	e107::getAdminLog()->addDebug($title.print_a($email,true),true);
		
		if(!empty($email['mail_media']))
		{
			$result['media'] = $email['mail_media'];
		}
				
	//	$title2 = "<h4>".__METHOD__." Line: ".__LINE__."</h4>";
	//	e107::getAdminLog()->addDebug($title2.print_a($result,true),true);
	
		$result['shortcodes']['MAILREF'] = $email['mail_source_id'];

		if($this->debugMode)
		{
			echo "<h3>makeEmailBlock() : Incoming</h3>";
			print_a($email);

			echo "<h3>makeEmailBlock(): Outgoing</h3>";
			print_a($result);
		}

		return $result;
	}



	/**
	 * Call to do a number of 'units' of email processing - from a cron job, for example
	 * Each 'unit' sends one email from the queue - potentially it could do some other task.
	 * @param $limit - number of units of work to do - zero to clear the queue (or do maximum allowed by a hard-coded limit)
	 * @param $pauseCount - pause after so many emails
	 * @param $pauseTime - time in seconds to pause after 'pauseCount' number of emails. 
	 * @return None
	 */
	public function doEmailTask($limit = 0, $pauseCount=null, $pauseTime=1)
	{
		if ($count = $this->selectEmails($limit))
		{
			$c=1;
			while ($count > 0)
			{
				$this->sendNextEmail();
				$count--;
				
				if(!empty($pauseCount) && ($c === $pauseCount))
				{
					sleep($pauseTime);
					$c=1;
				}
					
			}
			if ($this->mailer)
			{
				$this->mailer->allSent();		// Tidy up on completion
			}
		}
		else 
		{

			// e107::getAdminLog()->addDebug("Couldn't select emails", true);
		}
	}



	/**
	 * Saves an email to the DB
	 * @param $emailData
	 * @param $isNew - TRUE if a new email, FALSE if editing
	 *
	 *
	 * @return mail ID for success, FALSE on error
	 */
	public function saveEmail($emailData, $isNew = FALSE)
	{
		$this->checkDB(2);						// Make sure we have a DB object to use

		$dbData = $this->mailToDB($emailData, FALSE);		// Convert array formats
	//	print_a($dbData);


		if ($isNew === true)
		{
			unset($dbData['mail_source_id']);				// Just in case - there are circumstances where might be set
			$result = $this->db2->insert('mail_content', array('data' => $dbData, 
														'_FIELD_TYPES' => $this->dbTypes['mail_content'], 												'_NOTNULL' => $this->dbNull['mail_content']));
		}
		else
		{
			if (isset($dbData['mail_source_id']))
			{
				$result = $this->db2->update('mail_content', array('data' => $dbData, 
																	'_FIELD_TYPES' => $this->dbTypes['mail_content'], 
																	'WHERE' => '`mail_source_id` = '.intval($dbData['mail_source_id'])));
				if ($result !== FALSE) { $result = $dbData['mail_source_id']; }
			}
			else
			{
				echo "Programming bungle! No mail_source_id in function saveEmail()<br />";
				$result = FALSE;
			}
		}
		return $result;
	}


	/**
	 * Retrieve an email from the DB
	 * @param $mailID - number for email (assumed to be integral)
	 * @param $addMissing - if TRUE, any unset fields are added
	 *
	 * @return FALSE on error. Array of data on success.
	 */
	public function retrieveEmail($mailID, $addMissing = FALSE)
	{
		if (!is_numeric($mailID) || ($mailID == 0))
		{
			return FALSE;
		}
		$this->checkDB(2);						// Make sure we have a DB object to use
		if ($this->db2->select('mail_content', '*', '`mail_source_id`='.$mailID) === FALSE)
		{
			return FALSE;
		}
		$mailData = $this->db2->fetch();
		return $this->dbToMail($mailData, $addMissing);				// Convert to 'flat array' format
	}


	/**
	 * Delete an email from the DB, including (potential) recipients
	 * @param $mailID - number for email (assumed to be integral)
	 * @param $actions - allows selection of which DB to delete from
	 *
	 * @return FALSE on code error. Array of results on success.
	 */
	public function deleteEmail($mailID, $actions='all')
	{
		$result = array();
		if ($actions == 'all') $actions = 'content,recipients';
		$actArray = explode(',', $actions);
		
		if (!is_numeric($mailID) || ($mailID == 0))
		{
			return FALSE;
		}

		$this->checkDB(2);						// Make sure we have a DB object to use
		
		if (isset($actArray['content']))
		{
			$result['content'] = $this->db2->delete('mail_content', '`mail_source_id`='.$mailID);
		}
		if (isset($actArray['recipients']))
		{
			$result['recipients'] = $this->db2->delete('mail_recipients', '`mail_detail_id`='.$mailID);
		}
		
		return $result;
	}



	/**
	 * Initialise a set of counters prior to adding 
	 * @param $handle - as returned by makeEmail()
	 * @return none
	 */
	public function mailInitCounters($handle)
	{
		$this->mailCounters[$handle] = array('add' => 0, 'dups' => 0, 'dberr' => 0);
	}



	/**
	 * Add a recipient to the DB, provide that email not already on the list. 
	 * @param $handle - as returned by makeEmail()
	 * @param $mailRecip is an array of relevant info
	 * @param $priority - 'E107' priority for email (different to the priority included in the email)
	 * @return mixed - FALSE if error
	 *                 'dup' if duplicate of existing email
	 *                 integer - number of email recipient in DB
	 */
	public function mailAddNoDup($handle, $mailRecip, $initStatus = MAIL_STATUS_TEMP, $priority = self::E107_EMAIL_PRIORITY_LOW)
	{

		if (($handle <= 0) || !is_numeric($handle)) return FALSE;
		if (!isset($this->mailCounters[$handle])) return 'nocounter';

		$this->checkDB(1);			// Make sure DB object created

		if(empty($mailRecip['mail_recipient_email']))
		{
			e107::getMessage()->addError("Empty Recipient Email");
			return false;
		}


		$result = $this->db->select('mail_recipients', 'mail_target_id', "`mail_detail_id`={$handle} AND `mail_recipient_email`='{$mailRecip['mail_recipient_email']}'");


		if ($result === false)
		{
			return false;
		}
		elseif ($result != 0)
		{
			$this->mailCounters[$handle]['dups']++;
			return 'dup';
		}
		$mailRecip['mail_status'] = $initStatus;
		$mailRecip['mail_detail_id'] = $handle;
		$mailRecip['mail_send_date'] = time();

		$data = $this->targetToDb($mailRecip);
							// Convert internal types
		if ($this->db->insert('mail_recipients', array('data' => $data, '_FIELD_TYPES' => $this->dbTypes['mail_recipients'])))
		{
			$this->mailCounters[$handle]['add']++;
		}
		else
		{
			$this->mailCounters[$handle]['dberr']++;
			return FALSE;
		}
	}


	/**
	 * Update the mail record with the number of recipients as per counters
	 * @param $handle - as returned by makeEmail()
	 * @return mixed - FALSE if error
	 *					- number set into counter if success
	 */
	public function mailUpdateCounters($handle)
	{
		if (($handle <= 0) || !is_numeric($handle)) return FALSE;
		if (!isset($this->mailCounters[$handle])) return 'nocounter';
		$this->checkDB(2);			// Make sure DB object created
		
		
		
		
		
		$query = '`mail_togo_count`='.intval($this->mailCounters[$handle]['add']).' WHERE `mail_source_id`='.$handle;
		if ($this->db2->db_Update('mail_content', $query))
		{
			return $this->mailCounters[$handle]['add'];
		}
		return FALSE;
	}
	
	
	public function updateCounter($id, $type, $count)
	{
		if(empty($id) || empty($type))
		{
			return false; 	
		}
		
		$update = array(
			'mail_'.$type.'_count'	=> intval($count),
			'WHERE'					=> "mail_source_id=".intval($id)
		);
		
		return e107::getDb('mail')->update('mail_content', $update) ? $count : false;
	}
	
	
	
	/**
	 * Retrieve the counters for a mail record
	 * @param $handle - as returned by makeEmail()
	 * @return boolean - FALSE if error
	 *					- array of counters if success
	 */
	public function mailRetrieveCounters($handle)
	{
		if (isset($this->mailCounters[$handle]))
		{
			return $this->mailCounters[$handle];
		}
		return FALSE;
	}



	/**
	 * Update status for email, including all recipient entries (called once all recipients added)
	 * @param int $handle - as returned by makeEmail()
	 * @param $hold boolean - TRUE to set status to held, false to release for sending
	 * @param $notify - value to set in the mail_notify_complete field:
	 *			0 - no action on run complete
	 *			1 - notify admin who sent email only
	 *			2 - notify through e107 notify system only
	 *			3 - notify both
	 * @param $firstTime int - only valid if $hold === FALSE - earliest time/date when email may be sent
	 * @param $lastTime int - only valid if $hold === FALSE - latest time/date when email may be sent
	 * @return boolean TRUE on no errors, FALSE on errors
	 */
	public function activateEmail($handle, $hold = FALSE, $notify = 0, $firstTime = 0, $lastTime = 0)
	{
		if (($handle <= 0) || !is_numeric($handle)) return FALSE;
		$this->checkDB(1);			// Make sure DB object created
		$ft = '';
		$lt = '';
		if (!$hold)
		{		// Sending email - set sensible first and last times
			if ($lastTime < (time() + 3600))				// Force at least an hour to send emails
			{
				if ($firstTime < time())
				{
					$lastTime = time() + 86400;			// Standard delay - 24 hours
				}
				else
				{
					$lastTime = $firstTime + 86400;
				}
			}
			if ($firstTime > 0) $ft = ', `mail_send_date` = '.$firstTime;
			$lt = ', `mail_end_send` = '.$lastTime;
		}
		$query = '';
		if (!$hold) $query = '`mail_creator` = '.USERID.', `mail_create_date` = '.time().', ';		// Update when we send - might be someone different
		$query .= '`mail_notify_complete`='.intval($notify).', `mail_content_status` = '.($hold ? MAIL_STATUS_HELD : MAIL_STATUS_PENDING).$lt.' WHERE `mail_source_id` = '.intval($handle);
		//	echo "Update mail body: {$query}<br />";
		// Set status of email body first
		
		if (!$this->db->update('mail_content',$query))
		{
			e107::getLog()->e_log_event(10,-1,'MAIL','Activate/hold mail','mail_content: '.$query.'[!br!]Fail: '.$this->db->mySQLlastErrText,FALSE,LOG_TO_ROLLING);
			return FALSE;
		}
		
		// Now set status of individual emails
		$query = '`mail_status` = '.($hold ? MAIL_STATUS_HELD : (MAIL_STATUS_PENDING + e107MailManager::E107_EMAIL_MAX_TRIES)).$ft.' WHERE `mail_detail_id` = '.intval($handle);
		//	echo "Update individual emails: {$query}<br />";
		if (FALSE === $this->db->update('mail_recipients',$query))
		{
			e107::getLog()->e_log_event(10,-1,'MAIL','Activate/hold mail','mail_recipient: '.$query.'[!br!]Fail: '.$this->db->mySQLlastErrText,FALSE,LOG_TO_ROLLING);
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * Cancel sending of an email, including marking all unsent recipient entries
	 * $handle - as returned by makeEmail()
	 * @return boolean - TRUE on success, FALSE on failure
	 */
	public function cancelEmail($handle)
	{
		if (($handle <= 0) || !is_numeric($handle)) return FALSE;
		$this->checkDB(1);			// Make sure DB object created
		// Set status of individual emails first, so we can get a count
		if (FALSE === ($count = $this->db->update('mail_recipients','`mail_status` = '.MAIL_STATUS_CANCELLED.' WHERE `mail_detail_id` = '.intval($handle).' AND `mail_status` >'.MAIL_STATUS_FAILED)))
		{
			return FALSE;
		}
		// Now do status of email body - no emails to go, add those not sent to fail count
		if (!$this->db->update('mail_content','`mail_content_status` = '.MAIL_STATUS_PARTIAL.', `mail_togo_count`=0, `mail_fail_count` = `mail_fail_count` + '.intval($count).' WHERE `mail_source_id` = '.intval($handle)))
		{
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * Put email on hold, including marking all unsent recipient entries
	 * @param integer $handle - as returned by makeEmail()
	 * @return boolean - TRUE on success, FALSE on failure
	 */
	public function holdEmail($handle)
	{
		if (($handle <= 0) || !is_numeric($handle)) return FALSE;
		$this->checkDB(1);			// Make sure DB object created
		// Set status of individual emails first, so we can get a count
		if (FALSE === ($count = $this->db->update('mail_recipients','`mail_status` = '.MAIL_STATUS_HELD.' WHERE `mail_detail_id` = '.intval($handle).' AND `mail_status` >'.MAIL_STATUS_FAILED)))
		{
			return FALSE;
		}
		if ($count == 0) return TRUE;		// If zero count, must have held email just as queue being emptied, so don't touch main status

		if (!$this->db->update('mail_content','`mail_content_status` = '.MAIL_STATUS_HELD.' WHERE `mail_source_id` = '.intval($handle)))
		{
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * Handle a bounce report. 
	 * @param string $bounceString - the string from header X-e107-id
	 * @param string $emailAddress - optional email address string for checks
	 * @return boolean - TRUE on success, FALSE on failure
	 */
	public function markBounce($bounceString, $emailAddress = '')
	{
	
		$bounceString = trim($bounceString);
	
		$bounceInfo 	= array('mail_bounce_string' => $bounceString, 'mail_recipient_email' => $emailAddress);		// Ready for event data
		$errors 		= array();						// Log all errors, at least until proven
		$vals 			= explode('/', $bounceString);		// Should get one or four fields

		if($this->debugMode)
		{
			echo "<h4>Bounce String</h4>";
			print_a($bounceString);
			echo "<h4>Vals</h4>";
			print_a($vals);
		}
		
		if (!is_numeric($vals[0])) 				// Email recipient user id number (may be zero)
		{
			$errors[] = 'Bad user ID: '.$vals[0];
		}
		
		$uid = intval($vals[0]);				// User ID (zero is valid)
		
		if (count($vals) == 4) // Admin->Mailout format. 
		{
			
			if (!is_numeric($vals[1])) 		// Email body record number
			{
				$errors[] = 'Bad body record: '.$vals[1];
			}
			
			if (!is_numeric($vals[2])) 		// Email recipient table record number
			{
				$errors[] = 'Bad recipient record: '.$vals[2];
			}
			
			$vals[0] = intval($vals[0]);
			$vals[1] = intval($vals[1]);
			$vals[2] = intval($vals[2]);
			$vals[3] = trim($vals[3]);


			$hash = ($vals[0].'/'.$vals[1].'/'.$vals[2].'/');
			
			if (md5($hash) != $vals[3]) // 'Extended' ID has md5 validation
			{		
				$errors[] = 'Bad md5';
				$errors[] = print_r($vals,true);
				$errors[] = 'hash:'.md5($hash);
			}
			
			if (empty($errors))
			{	
				$this->checkDB(1); // Look up in mailer DB if no errors so far
				
				if (false === ($this->db->gen(
					"SELECT mr.`mail_recipient_id`, mr.`mail_recipient_email`, mr.`mail_recipient_name`, mr.mail_target_info, 
					mc.mail_create_date, mc.mail_start_send, mc.mail_end_send, mc.`mail_title`, mc.`mail_subject`, mc.`mail_creator`, mc.`mail_other` FROM `#mail_recipients` AS mr 
					LEFT JOIN `#mail_content` as mc ON mr.`mail_detail_id` = mc.`mail_source_id`
						WHERE mr.`mail_target_id` = {$vals[2]} AND mc.`mail_source_id` = {$vals[1]}")))
				{	// Invalid mailer record
					$errors[] = 'Not found in DB: '.$vals[1].'/'.$vals[2];
				}
				
				$row = $this->db->fetch();
				
				$row = $this->dbToBoth($row);
				
				$bounceInfo = $row;

				if ($emailAddress && ($emailAddress != $row['mail_recipient_email'])) // Email address mismatch
				{	
					$errors[] = 'Email address mismatch: '.$emailAddress.'/'.$row['mail_recipient_email'];
				}
				
				if ($uid != $row['mail_recipient_id']) 	// User ID mismatch
				{
					$errors[] = 'User ID mismatch: '.$uid.'/'.$row['mail_recipient_id'];
				}
				
				if (count($errors) == 0) // All passed - can update mailout databases
				{
					$bounceInfo['mail_source_id'] 		= $vals[1];
					$bounceInfo['mail_target_id'] 		= $vals[2];
					$bounceInfo['mail_recipient_id'] 	= $uid;
					$bounceInfo['mail_recipient_name'] 	= $row['mail_recipient_name'];		
						
						
					if(!$this->db->update('mail_content', '`mail_bounce_count` = `mail_bounce_count` + 1 WHERE `mail_source_id` = '.$vals[1]))
					{
						e107::getAdminLog()->add('Unable to increment bounce-count on mail_source_id='.$vals[1],$bounceInfo, E_LOG_FATAL, 'BOUNCE', LOG_TO_ROLLING);
					}
					
					
					if(!$this->db->update('mail_recipients', '`mail_status` = '.MAIL_STATUS_BOUNCED.' WHERE `mail_target_id` = '.$vals[2]))
					{
						e107::getAdminLog()->add('Unable to update recipient mail_status to bounce on mail_target_id = '.$vals[2],$bounceInfo, E_LOG_FATAL, 'BOUNCE', LOG_TO_ROLLING);
					}
				
					$addons = array_keys($row['mail_selectors']); // trigger e_mailout.php addons. 'bounce' method. 
					foreach($addons as $plug)
					{
						if($plug == 'core')
						{
							require_once(e_HANDLER.'user_handler.php');
							if($err = userHandler::userStatusUpdate('bounce', $uid, $emailAddress));
							{
								$errors[] = $err;
							}	
							
						}
						else 
						{
							if($cls = e107::getAddon($plug,'e_mailout'))
							{
								if(e107::callMethod($cls, 'bounce', $bounceInfo)===false)
								{
									e107::getAdminLog()->add($plug.' bounce process failed',$bounceInfo, E_LOG_FATAL, 'BOUNCE',LOG_TO_ROLLING);	
								}
							}
							
						}	
					}
				}
				
				
			//	echo e107::getMessage()->render();
			//	print_a($bounceInfo);
				
				
			}
		}
		elseif ((count($vals) != 1) && (count($vals) != 4)) // invalid e107-id header. 
		{
			$errors[] = 'Bad element count: '.count($vals);
		}
		elseif (!empty($uid) || !empty($emailAddress)) // Update the user table for user_id = $uid;
		{	
			// require_once(e_HANDLER.'user_handler.php');
			$err = e107::getUserSession()->userStatusUpdate('bounce', $uid, $emailAddress);
			if($err)
			{
				$errors[] = $err;
			}
		}

		if (!empty($errors))
		{
			$logErrors =$bounceInfo;
			$logErrors['user_id'] = $uid;
			$logErrors['mailshot'] = $vals[1];
			$logErrors['mailshot_recipient'] = $vals[2];
			$logErrors['errors'] = $errors;
			$logErrors['email'] = $emailAddress;
			$logErrors['bounceString'] = $bounceString;
			$logString = $bounceString.' ('.$emailAddress.')[!br!]'.implode('[!br!]',$errors).implode('[!br!]',$bounceInfo);
		//	e107::getAdminLog()->e_log_event(10,-1,'BOUNCE','Bounce receive error',$logString, FALSE,LOG_TO_ROLLING);
			e107::getAdminLog()->add('Bounce receive error',$logErrors, E_LOG_WARNING, 'BOUNCE', LOG_TO_ROLLING);
			return $errors;
		}
		else 
		{
			//	e107::getAdminLog()->e_log_event(10,-1,'BOUNCE','Bounce received/logged',$bounceInfo, FALSE,LOG_TO_ROLLING);
			e107::getAdminLog()->add('Bounce received/logged',$bounceInfo, E_LOG_INFORMATIVE, 'BOUNCE',LOG_TO_ROLLING);	
		}
		
		
		e107::getEvent()->trigger('mailbounce', $bounceInfo);
		
		return false;
	}



	/**
	 * Does a query to select one or more emails for which status is required.
	 * @param $start - sets the offset of the first email to return based on the search criteria
	 * @param $count - sets the maximum number of emails to return
	 * @param $fields - allows selection of which db fields are returned in each result
	 * @param $filters - array contains filter/selection criteria - basically setting limits on each field
	 * @return Returns number of records found (maximum $count); FALSE on error
	 */
	public function selectEmailStatus($start = 0, $count = 0, $fields = '*', $filters = FALSE, $orderField = 'mail_source_id', $sortOrder = 'asc')
	{
		$this->checkDB(1);			// Make sure DB object created
		if (!is_array($filters) && $filters)
		{	// Assume a textual email type
			switch ($filters)
			{
				case 'pending' :
					$filters = array('`mail_content_status` = '.MAIL_STATUS_PENDING);
					break;
				case 'held' :
					$filters = array('`mail_content_status` = '.MAIL_STATUS_HELD);
					break;
				case 'pendingheld' :
					$filters = array('((`mail_content_status` = '.MAIL_STATUS_PENDING.') OR (`mail_content_status` = '.MAIL_STATUS_HELD.'))');
					break;
				case 'sent' :
					$filters = array('`mail_content_status` = '.MAIL_STATUS_SENT);
					break;
				case 'allcomplete' :
					$filters = array('((`mail_content_status` = '.MAIL_STATUS_SENT.') OR (`mail_content_status` = '.MAIL_STATUS_PARTIAL.') OR (`mail_content_status` = '.MAIL_STATUS_CANCELLED.'))');
					break;
				case 'failed' :
					$filters = array('`mail_content_status` = '.MAIL_STATUS_FAILED);
					break;
				case 'saved' :
					$filters = array('`mail_content_status` = '.MAIL_STATUS_SAVED);
					break;
			}
		}
		if (!is_array($filters))
		{
			$filters = array(); 
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS {$fields} FROM `#mail_content`";
		if (count($filters))
		{
			$query .= ' WHERE '.implode (' AND ', $filters);
		}
		if ($orderField)
		{
			$query .= " ORDER BY `{$orderField}`";
		}
		if ($sortOrder)
		{
			$sortOrder = strtoupper($sortOrder);
			$query .= ($sortOrder == 'DESC') ? ' DESC' : ' ASC';
		}
		if ($count)
		{
			$query .= " LIMIT {$start}, {$count}";
		}
		//echo "{$start}, {$count} Mail query: {$query}<br />";
		$result = $this->db->gen($query);
		if ($result !== FALSE)
		{
			$this->queryCount[1] = $this->db->total_results;			// Save number of records found
		}
		else
		{
			$this->queryCount[1] = 0;
		}
		return $result;
	}


	/**
	 * Returns the total number of records matching the search done in the most recent call to selectEmailStatus()
	 * @return integer - number of emails matching criteria
	 */
	public function getEmailCount()
	{
		return $this->queryCount[1];
	}



	/**
	 * Returns the detail of the next email which satisfies the query done in selectEmailStatus()
	 * @return Returns an array of data relating to a single email if available (in 'flat' format). FALSE on no data or error
	 */
	public function getNextEmailStatus()
	{
		$result = $this->db->db_Fetch();
		if (is_array($result)) { return $this->dbToMail($result); }
		return FALSE;
	}



	/**
	 * Does a query to select from the list of email targets which have been used
	 * @param $start - sets the offset of the first email to return based on the search criteria
	 * @param $count - sets the maximum number of emails to return
	 * @param $fields - allows selection of which db fields are returned in each result
	 * @param $filters - array contains filter/selection criteria
	 *				'handle=nn' picks out a specific email
	 * @return Returns number of records found; FALSE on error
	 */
	public function selectTargetStatus($handle, $start = 0, $count = 0, $fields = '*', $filters = FALSE, $orderField = 'mail_target_id', $sortOrder = 'asc')
	{
		$handle = intval($handle);
		if ($filters === FALSE) { $filters = array(); }		// Might not need this line
		
		$this->checkDB(2);			// Make sure DB object created

		// TODO: Implement filters if needed
		$query = "SELECT SQL_CALC_FOUND_ROWS {$fields} FROM `#mail_recipients` WHERE `mail_detail_id`={$handle}";
		if ($orderField)
		{
			$query .= " ORDER BY `{$orderField}`";
		}
		if ($sortOrder)
		{
			$sortOrder = strtoupper($sortOrder);
			$query .= ($sortOrder == 'DESC') ? ' DESC' : ' ASC';
		}
		if ($count)
		{
			$query .= " LIMIT {$start}, {$count}";
		}
//		echo "{$start}, {$count} Target query: {$query}<br />";
		$result = $this->db2->gen($query);
		if ($result !== FALSE)
		{
			$this->queryCount[2] = $this->db2->total_results;			// Save number of records found
		}
		else
		{
			$this->queryCount[2] = 0;
		}
//		echo "Result: {$result}.  Total: {$this->queryCount[2]}<br />";
		return $result;
	}


	/**
	 * Returns the total number of records matching the search done in the most recent call to selectTargetStatus()
	 * @return integer - number of emails matching criteria
	 */
	public function getTargetCount()
	{
		return $this->queryCount[2];
	}



	/**
	 * Returns the detail of the next recipient which satisfies the query done in selectTargetStatus()
	 * @return Returns an array of data relating to a single email if available (in 'flat' format). FALSE on no data or error
	 */
	public function getNextTargetStatus()
	{
		$result = $this->db2->db_Fetch();
		if (is_array($result)) { return $this->dbToTarget($result); }
		return FALSE;
	}



//-----------------------------------------------------
//		Function call to send a templated email
//-----------------------------------------------------

/**
 *	Send an email to any number of recipients, using a template
 *
 *	The template may contain normal shortcodes, which must already have been loaded. @see e107_themes/email_template.php
 *
 *	The template (or other body text) may also contain field names in the form |USER_NAME| (as used in the bulk mailer edit page). These are
 *	filled in from $templateData - field name corresponds to the array index name (case-sensitive)
 *
 *	The template definition may contain an array $template['email_overrides'] of values which override normal mailer settings.
 *
 *	The template definition MUST contain a template variable $template['email_body']
 *
 *	In general, any template definition which isn't overridden uses the default which should be specified in e_THEME.'templates/email_templates.php'
 *
 *	There is a presumption that the email is being templated because it contains HTML, although this isn't mandatory.
 *
 *	Any language string constants required in the template must be defined either by loading the requisite language file prior to calling this
 *	routine, or by loading them in the template file.
 *
 *	@param array|string $templateName - if a string, the name of the template - information is loaded from theme and default templates.
 *					- if an array, template data as returned by gettemplateInfo() (and defined in the template files)
 *			- if empty, sends a simple email using the default template (much as the original sendemail() function in mail.php)
 *	@param array $emailData - defines the email information (generally as the 'mail_content' and 'mail_other' info above):
 *					$emailData = array(
						'mail_create_app' => 'notify',
						'mail_title' => 'NOTIFY',
						'mail_subject' => $subject,
						'mail_sender_email' => $pref['siteadminemail'],
						'mail_sender_name'	=> $pref['siteadmin'],
						'mail_send_style'	=> 'textonly',
						'mail_notify_complete' => 0,			// NEVER notify when this email sent!!!!!
						'mail_body' => $message
					);
 *	@param array|string $recipientData - if a string, its the email address of a single recipient.
 *		- if an array, each entry is the data for a single recipient, as the 'mail_recipients' definition above
 *									$recipientData = array('mail_recipient_id' => $row['user_id'],
											 'mail_recipient_name' => $row['user_name'],
											 'mail_recipient_email' => $row['user_email']
											 );	
 *						....and other data as appropriate
 *	@param boolean|array $options - any additional parameters to be passed to the mailer - as accepted by arraySet method.
 *			These parameters will override any defaults, and any set in the template
 *	if ($options['mail_force_queue'] is TRUE, the mail will be added to the queue regardless of the number of recipients
 *
 *	@return boolean TRUE if either added to queue, or sent, successfully (does NOT indicate receipt). FALSE on any error
 *		(Note that with a small number of recipients FALSE indicates that one or more emails weren't sent - some may have been sent successfully)
 */

	public function sendEmails($templateName, $emailData, $recipientData, $options = false)
	{
		$log = e107::getAdminLog();
		$log->addDebug(print_r($emailData, true),true);
		$log->addDebug(print_r($recipientData, true),true);
		$log->toFile('mail_manager','Mail Manager Log', true);

		if (!is_array($emailData)) 
		{
			return false;
		}
		
		if (!is_array($recipientData))
		{
			$recipientData = array(array('mail_recipient_email' => $recipientData, 'mail_recipient_name' => $recipientData));
		}

		$emailData['mail_content_status'] = MAIL_STATUS_TEMP;

		if ($templateName == '')
		{
			$templateName = varset($emailData['mail_send_style'], 'textonly');		// Safest default if nothing specified
		}

		$templateName = trim($templateName);
		if ($templateName == '') return false;

		$this->currentMailBody 				= $emailData['mail_body'];			// In case we send immediately
		$this->currentTextBody 				= strip_tags($emailData['mail_body']);

		//		$emailData['mail_body_templated'] 	= $ourTemplate->mainBodyText;
		//		$emailData['mail_body_alt'] 		= $ourTemplate->altBodyText;
		
		if (!isset($emailData['mail_overrides']))
		{
			// $emailData['mail_overrides'] = $ourTemplate->lastTemplateData['email_overrides'];
		}
		
		if(!empty($emailData['template'])) // Quick Fix for new email template standards. 
		{
			$this->currentMailBody = $emailData['mail_body'];
			unset($emailData['mail_body_templated']);
			
			if($this->debugMode)
			{
				echo "<h4>".$emailData['template']." Template detected</h4>";
			}
		}
		

		if (is_array($options) && isset($options['mail_force_queue']))
		{
			$forceQueue = $options['mail_force_queue'];
			unset($options['mail_force_queue']);
		}

		if($this->debugMode)
		{
			 
			echo "<h4>".__CLASS__." :: ".__METHOD__." - Line ".__LINE__."</h4>";
			print_a($emailData);
			print_a($recipientData);	

		}

		if ((count($recipientData) <= 5) && !$forceQueue)	// Arbitrary upper limit for sending multiple emails immediately
		{
			if ($this->mailer == NULL)
			{
				e107_require_once(e_HANDLER.'mail.php');
				$this->mailer = new e107Email($options);
			}
			$tempResult = TRUE;
			$eCount = 0;
			
			// @TODO: Generate alt text etc

			

			foreach ($recipientData as $recip)
			{
				// Fill in other bits of email
			//	$emailData['mail_target_info'] = $recip	;
				$merged     = array_merge($emailData,$recip);
				$mailToSend = $this->makeEmailBlock($merged);			// Substitute mail-specific variables, attachments etc
/*
				echo "<h2>MERGED</h2>";
				print_a($merged);
				echo "<h2>RETURNED</h2>";
				print_a($mailToSend);
				echo "<hr />";
				continue;

		*/
				if (false == $this->mailer->sendEmail($recip['mail_recipient_email'], $recip['mail_recipient_name'], $mailToSend, true))
				{
					$tempResult = FALSE;
					if($this->debugMode)
					{
						echo "<h4>Failed to send to: ".$recip['mail_recipient_email']." [". $recip['mail_recipient_name'] ."]</h4>";
						print_a($mailToSend);
					}
				}
				else
				{	// Success here
					if($this->debugMode)
					{
						echo "<h4>Mail Sent successfully to: ".$recip['mail_recipient_email']." [". $recip['mail_recipient_name'] ."]</h4>";
						print_a($mailToSend);
					}
					if ($eCount == 0)
					{	// Only send these on first email - otherwise someone could get inundated!
						unset($emailData['mail_copy_to']);
						unset($emailData['mail_bcopy_to']);
					}
					$eCount++;		// Count number of successful emails sent
				}
			}
			return $tempResult;
		}


		// ----------- Too many recipients to send at once - add to the emailing queue ---------------- //


		// @TODO - handle any other relevant $options fields
		$emailData['mail_total_count'] = count($recipientData);

		$result = $this->saveEmail($emailData, TRUE);

		if ($result === FALSE)
		{
			// TODO: Handle error
			return FALSE;			// Probably nothing else we can do
		}
		elseif (is_numeric($result))
		{
			$mailMainID = $emailData['mail_source_id'] = $result;
		}
		else
		{
			// TODO: Handle strange error
			return FALSE;			// Probably nothing else we can do
		}
		$this->mailInitCounters($mailMainID);			// Initialise counters for emails added

		// Now add email addresses to the list
		foreach ($recipientData as $email)
		{
			$result = $this->mailAddNoDup($mailMainID, $email, MAIL_STATUS_TEMP);
		}
		$this->mailUpdateCounters($mailMainID);			// Update the counters
		$counters = $this->mailRetrieveCounters($mailMainID);		// Retrieve the counters
		if ($counters['add'] == 0)
		{
			$this->deleteEmail($mailMainID);			// Probably a fault, but precautionary - delete email 
			// Don't treat as an error if no recipients
		}
		else
		{
			$this->activateEmail($mailMainID, FALSE);					// Actually mark the email for sending
		}
		return TRUE;
	}

}


?>