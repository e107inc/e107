<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Log Handler
 * 
 * USAGE: 
 * 
 * @example Log and Add to Message Handler: e107::getAdminLog()->addSuccess("Successfully executed")->save('PREF_01');
 * @example Log Only: e107::getAdminLog()->addSuccess("Successfully executed",false)->save('PREF_01');
 * @example Log Array Diff: e107::getAdminLog()->addArray($array1, $array2)->save('PREF_01');
 * @example Log Array Diff and Add to Message Handler: e107::getAdminLog()->addArray($array1, $array2, E_MESSAGE_ERROR )->save('PREF_01');
 *
*/

if (!defined('e107_INIT'))
{
	exit;
}

define('LOG_MESSAGE_NODISPLAY', 	'nodisplay');

/**
 *	Admin logging class.
 *
 *	@package	e107
 *	@subpackage	e107_handlers
 *	@version 	$Id$;
 *  @author 	e107steved
 */
class e_admin_log
{

	/**
	 * Contains default class options, plus any that are overidden by the constructor
	 *
	 * @var array
	 */
	protected	$_options = array('log_level'=>2, 'backtrace'=>false, );

	protected	$rldb = NULL; // Database used by logging routine
	
	
	
	protected 	$logFile = null;
	/**
	 * Log messages
	 * @var array
	 */
	protected $_messages;
	
	
	protected $_allMessages; // similar to $_messages except it is never flushed.


	protected $_current_plugin = null;
	

	/**
	 * Constructor. Sets up constants and overwrites default options where set.
	 *
	 * @param array $options
	 * @return none
	 */
	public function __construct($options = array())
	{
		if(!empty($options))
		{
			foreach ($options as $key=>$val)
			{
				$this->_options[$key] = $val;
			}
		}

		define("E_LOG_INFORMATIVE", 0); // Minimal Log Level, including really minor stuff
		define("E_LOG_NOTICE", 1); // More important than informative, but less important than notice
		define("E_LOG_WARNING", 2); // Not anything serious, but important information
		define("E_LOG_FATAL", 3); //  An event so bad your site ceased execution.
		define("E_LOG_PLUGIN", 4); // Plugin information

		// Logging actions
		define("LOG_TO_ADMIN", 1);
		define("LOG_TO_AUDIT", 2);
		define("LOG_TO_ROLLING", 4);

		// User audit logging (intentionally start at 10 - stick to 2 digits)
		// The last two digits must match that for the corresponding log message
		define('USER_AUDIT_ADMIN', 10); // User data changed by admin
		define('USER_AUDIT_SIGNUP', 11); // User signed up
		define('USER_AUDIT_EMAILACK', 12); // User responded to registration email
		define('USER_AUDIT_LOGIN', 13); // User logged in
		define('USER_AUDIT_LOGOUT', 14); // User logged out
		define('USER_AUDIT_NEW_DN', 15); // User changed display name
		define('USER_AUDIT_NEW_PW', 16); // User changed password
		define('USER_AUDIT_NEW_EML', 17); // User changed email
		define('USER_AUDIT_PW_RES', 18); // Password reset/resent activation email
		define('USER_AUDIT_NEW_SET', 19); // User changed other settings
		define('USER_AUDIT_ADD_ADMIN', 20); // User added by admin
		define('USER_AUDIT_MAIL_BOUNCE', 21); // User mail bounce
		define('USER_AUDIT_BANNED', 22); // User banned
		define('USER_AUDIT_BOUNCE_RESET', 23); // User bounce reset
		define('USER_AUDIT_TEMP_ACCOUNT', 24); // User temporary account

		// Init E_MESSAGE_* constants if not already done
		// e107::getMessage(); - just include, message handler is creating session in construct
		// it breaks stuff (see class2 - language detection and comments)
		require_once(e_HANDLER.'message_handler.php');
		$this->_messages = array();
		$this->_allMessages = array();
	
	}

	/**
	 * @DEPRECATED
	 * BC Alias of add(); 
	 */
	public function log_event($event_title, $event_detail, $event_type = E_LOG_INFORMATIVE , $event_code = '')
	{
		return $this->add($event_title, $event_detail, $event_type, $event_code);	
	}


	/**
	 * Save all logs in the queue to the database and render any unhidden messages with the message handler.
	 * @see alias flushMessages() method below.
	 * @param string $logTitle - title for log entry eg. 'PREF_01'
	 * @param int $logImportance [optional] default E_LOG_INFORMATIVE - passed directly to admin log
	 * @param string $logEventCode [optional] - passed directly to admin log
	 * @param string $mstack [optional] message stack passed to message handler
	 * @param int LOG_TO_ADMIN|LOG_TO_ROLLING|LOG_TO_AUDIT
	 * @return \e_admin_log
	 */
	public function save($logTitle, $logImportance = E_LOG_INFORMATIVE, $logEventCode = '', $mstack = false, $target = LOG_TO_ADMIN)
	{
		return $this->flushMessages($logTitle, $logImportance, $logEventCode, $mstack, $target);
	}
	
	
	

	/**
	 * Add and Save an event into the admin, rolling or user log. 
	 * @param string $event_title
	 * @param mixed $event_details
	 * @param integer $event_type [optional] Log level eg. E_LOG_INFORMATIVE, E_LOG_NOTICE, E_LOG_WARNING, E_LOG_FATAL
	 * @param string $event_code [optional] - eg. 'BOUNCE'
	 * @param integer $target [optional]  LOG_TO_ADMIN, LOG_TO_AUDIT, LOG_TO_ROLLING
	 * @param array $user - user to attribute the log to. array('user_id'=>2, 'user_name'=>'whoever');
	 * @return e_admin_log
	 * 
	 * Alternative admin log entry point - compatible with legacy calls, and a bit simpler to use than the generic entry point.
	 * ($eventcode has been added - give it a reference to identify the source module, such as 'NEWS_12' or 'ECAL_03')
	 * We also log everything (unlike 0.7, where admin log and debug stuff were all mixed up together)
	 *
	 * For multi-lingual logging (where the event title is shown in the language of the current user), LAN defines may be used in the title
	 *
	 * For generic calls, leave $event_code as empty, and specify a constant string STRING_nn of less than 10 characters for the event title
	 * Typically the 'STRING' part of the name defines the area originating the log event, and the 'nn' is a numeric code
	 * This is stored as 'LAN_AL_STRING_NN', and must be defined in a language file which is loaded during log display.
	 *

	 */
	public function add($event_title, $event_detail, $event_type = E_LOG_INFORMATIVE , $event_code = '', $target = LOG_TO_ADMIN, $userData=null )
	{
		if ($event_code == '')
		{
			if (strlen($event_title) <= 12)
			{ // Assume the title is actually a reference to the event
				$event_code = $event_title;
				$event_title = 'LAN_AL_'.$event_title;
			}
			else
			{
				$event_code = 'ADMIN';
			}
		}
		//SecretR - now supports DB array as event_detail (see e.g. db::db_Insert())
		if (is_array($event_detail))
		{
			// handled inside e_log_event(); 
			
			/*
			$tmp = array();
				if (isset($event_detail['data']))
				{
					$event_detail = $event_detail['data'];
				}
				foreach ($event_detail as $k => $v)
				{
					$tmp[] = $k.'=>'.$v;
				}
				$event_detail = implode("[!br!]\n", $tmp);
				unset($tmp);
			*/
			
		}
		else
		{
			// auto-format long details - TODO - shrink details on administration log page, expand/show in DHTML window full details.  
			$event_detail = str_replace("\n", "[!br!]", $event_detail);
		}

		if ($this->_options['backtrace'] == true)
		{
			$event_detail .= "\n\n".debug_backtrace(false);
		}
		
		
		$this->e_log_event($event_type, -1, $event_code, $event_title, $event_detail, FALSE, $target, $userData);

		return $this;
	}

	/**
	 * Alias for deprecated e_log_event
	 * @param        $importance
	 * @param        $source_call
	 * @param string $eventcode
	 * @param string $event_title
	 * @param string $explain
	 * @param bool   $finished
	 * @param int    $target_logs
	 * @param null   $userData
	 * @return none
	 */
	public function addEvent($importance, $source_call, $eventcode = "GEN", $event_title = "Untitled", $explain = "", $finished = FALSE, $target_logs = LOG_TO_AUDIT, $userData=null )
	{
		return $this->e_log_event($importance, $source_call, $eventcode, $event_title, $explain, $finished, $target_logs, $userData);

	}

	/**
	 Generic log entry point
	 -----------------------
	 Example call: (Deliberately pick separators that shouldn't be in file names)
	 e_log_event(E_LOG_NOTICE,__FILE__."|".__FUNCTION__."@".__LINE__,"ECODE","Event Title","explanatory message",FALSE,LOG_TO_ADMIN);
	 or:
	 e_log_event(E_LOG_NOTICE,debug_backtrace(),"ECODE","Event Title","explanatory message",TRUE,LOG_TO_ROLLING);
	 *
	 *	@param int $importance - importance of event - 0..4 or so
	 *	@param mixed $source_call - either:	string identifying calling file/routine
	 *		or:		a number 0..9 identifying info to log from debug_backtrace()
	 *		or:		empty string, in which case first entry from debug_backtrace() logged
	 *		or:		an array, assumed to be from passing debug_backtrace() as a parameter, in which case relevant
	 *				 information is extracted and the argument list from the first entry logged
	 *		or:		-1, in which case no information logged
	 *	@param string $eventcode - abbreviation listing event type
	 *	@param string $event_title - title of event - pass standard 'LAN_ERROR_nn' defines to allow language translation
	 *	@param string $explain - detail of event
	 *	@param bool $finished - if TRUE, aborts execution
	 *	@param int $target_logs - flags indicating which logs to update - if entry to be posted in several logs, add (or 'OR') their defines:
	 *		 LOG_TO_ADMIN		- admin log
	 *		 LOG_TO_AUDIT		- audit log
	 *		 LOG_TO_ROLLING		- rolling log
	 * @param array $userData - attribute user to log entry. array('user_id'=>2, 'user_name'=>'whatever');
	 *	@return none

	 * @todo - check microtime() call
	 * @deprecated - use add() method instead or addEvent() as a direct replacement.
	 */
	public function e_log_event($importance, $source_call, $eventcode = "GEN", $event_title = "Untitled", $explain = "", $finished = FALSE, $target_logs = LOG_TO_AUDIT, $userData=null )
	{
		$e107 = e107::getInstance();
		$pref = e107::getPref();
		$tp = e107::getParser();

		list($time_usec, $time_sec) = explode(" ", microtime(FALSE)); // Log event time immediately to minimise uncertainty
		$time_usec = $time_usec * 1000000;

		if ($this->rldb == NULL)
			$this->rldb = e107::getDb('adminlog'); // Better use our own db - don't know what else is going on

		if (is_bool($target_logs))
		{ // Handle the legacy stuff for now - some old code used a boolean to select admin or rolling logs
			$target_logs = $target_logs ? LOG_TO_ADMIN : LOG_TO_ROLLING;
		}

		//---------------------------------------
		// Calculations common to all logs
		//---------------------------------------

		$userid 		= deftrue('USER') ? USERID : 0;
		$userstring 	= deftrue('USER') ? USERNAME : 'LAN_ANONYMOUS';
		$userIP 		= e107::getIPHandler()->getIP(FALSE);

		if(!empty($userData['user_id']))
		{
			$userid = $userData['user_id'];
		}

		if(!empty($userData['user_name']))
		{
			$userstring  = $userData['user_name'];
		}

		if(!empty($userData['user_ip']))
		{
			$userIP  = $userData['user_ip'];
		}

		$importance 	= $tp->toDB($importance, true, false, 'no_html');
		$eventcode 		= $tp->toDB($eventcode, true, false, 'no_html');

		if (is_array($explain))
		{
			/*
			$line = '';
			$spacer = '';
			foreach ($explain as $k=>$v)
			{
				$line .= $spacer.$k.'=>'.$v;
				$spacer = '[!br!]';
			}
			$explain = $line;
			unset($line);
			*/
			$explain = str_replace("\n",'[!br!]',print_r($explain,true));
			
		}
		
		
		$explain = e107::getDb()->escape($tp->toDB($explain, true, false, 'no_html'));
		$event_title = $tp->toDB($event_title, true, false, 'no_html');

		//---------------------------------------
		// 			Admin Log
		//---------------------------------------
		if ($target_logs & LOG_TO_ADMIN) // Admin log - assume all fields valid
		{ 
		//	$qry = " null, ".intval($time_sec).','.intval($time_usec).", '{$importance}', '{$eventcode}', {$userid}, '{$userIP}', '{$event_title}', '{$explain}' ";
			
			$adminLogInsert = array(
				'dblog_id'			=> null,
				'dblog_type'		=> $importance,
				'dblog_eventcode'	=> $eventcode,
				'dblog_datestamp'	=> time(),
				'dblog_microtime'	=> intval($time_usec),
				'dblog_user_id'		=> $userid,
				'dblog_ip'			=> $userIP,
				'dblog_title'		=> $event_title,
				'dblog_remarks'		=> $explain
			);
			
			$this->rldb->insert("admin_log", $adminLogInsert);
		}

		//---------------------------------------
		// 			Audit Log
		//---------------------------------------
		// Add in audit log here

		//---------------------------------------
		// 			Rolling Log
		//---------------------------------------
		if (($target_logs & LOG_TO_ROLLING) && vartrue($pref['roll_log_active']))
		{ //	Rolling log

			// 	Process source_call info
			//---------------------------------------
			if (is_numeric($source_call) && ($source_call >= 0))
			{
				$back_count = 1;
				$i = 0;
				if (is_numeric($source_call) || ($source_call == ''))
				{
					$back_count = $source_call + 1;
					$source_call = debug_backtrace();
					$i = 1; // Don't want to print the entry parameters to this function - we know all that!
				}
			}

			if (is_array($source_call))
			{ // Print the debug_backtrace() array
				while ($i < $back_count)
				{
					$source_call[$i]['file'] = $e107->fix_windows_paths($source_call[$i]['file']); // Needed for Windoze hosts.
					$source_call[$i]['file'] = str_replace($e107->file_path, "", $source_call[$i]['file']); // We really just want a e107 root-relative path. Strip out the root bit
					$tmp = $source_call[$i]['file']."|".$source_call[$i]['class'].$source_call[$i]['type'].$source_call[$i]['function']."@".$source_call[$i]['line'];
					foreach ($source_call[$i]['args'] as $k=>$v)
					{ // Add in the arguments
						$explain .= "[!br!]".$k."=".$v;
					}
					$i++;
					if ($i < $back_count)
						$explain .= "[!br!]-------------------";
					if (!isset($tmp1))
						$tmp1 = $tmp; // Pick off the immediate caller as the source
				}
				if (isset($tmp1)) $source_call = $tmp1;
				else $source_call = 'Root level';
			}
			else
			{
				$source_call = $e107->fix_windows_paths($source_call); // Needed for Windoze hosts.
				$source_call = str_replace($e107->file_path, "", $source_call); // We really just want a e107 root-relative path. Strip out the root bit
				$source_call = $tp->toDB($source_call, true, false, 'no_html');
			}
			// else $source_call is a string

			// Save new rolling log record
			$this->rldb->insert("dblog", "0, ".intval($time_sec).', '.intval($time_usec).", '{$importance}', '{$eventcode}', {$userid}, '{$userstring}', '{$userIP}', '{$source_call}', '{$event_title}', '{$explain}' ");

			// Now delete any old stuff
			if(!empty($pref['roll_log_days']))
			{
				$days = intval($pref['roll_log_days']);
				$this->rldb->delete("dblog", "dblog_datestamp < '".intval(time() - ($days * 86400))."' ");
			}
		}

		if ($finished)
			exit; // Optional abort for all logs
	}

	public function setCurrentPlugin($plugdir)
	{
		$this->_current_plugin = $plugdir;

		return $this;
	}

	/**--------------------------------------
	 *		USER AUDIT ENTRY
	 *--------------------------------------
	 *	Log user-related events
	 *	@param int $event_code is a defined constant (see above) which specifies the event
	 *	@param array $event_data is an array of data fields whose keys and values are logged (usually user data, but doesn't have to be - can add messages here)
	 *	@param int $id
	 *	@param string $u_name
	 *		both $id and $u_name are left blank except for admin edits and user login, where they specify the id and login name of the 'target' user
	 *
	 *	@return bool
	 */
	function user_audit($event_type, $event_data, $id = '', $u_name = '')
	{
		list($time_usec, $time_sec) = explode(" ", microtime()); // Log event time immediately to minimise uncertainty

		$time_usec = $time_usec * 1000000;

		if(!is_numeric($event_type))
		{
			$title = "User Audit Event-Type Failure: ";
			$title .= (string) $event_type;
			$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,4);
			$debug[0] = e_REQUEST_URI;

			$this->e_log_event(4, $debug[1]['file']."|".$debug[1]['function']."@".$debug[1]['line'], "USERAUDIT", $title, $debug, FALSE);
			return false;
		}

		// See whether we should log this
		$user_logging_opts = e107::getConfig()->get('user_audit_opts');
		
		if (!isset($user_logging_opts[$event_type]))  // Finished if not set to log this event type
		{
			return false;
		}

		if(empty($event_data))
		{
			$backt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,4);
			$event_data = $backt;
		}


		if($this->rldb == null)
		{
			$this->rldb = e107::getDb('rldb'); // Better use our own db - don't know what else is going on
		}

		if(!empty($id))
		{
			 $userid = $id;
		}
		else
		{
			 $userid = (USER === true) ? USERID : 0;
		}

		if(!empty($u_name))
		{
			 $userstring = $u_name;
		}
		else
		{
			$userstring = (USER === true ? USERNAME : "LAN_ANONYMOUS");
		}

		$userIP = e107::getIPHandler()->getIP(false);

		$eventcode = 'USER_'.$event_type;

		$title = 'LAN_AUDIT_LOG_0'.$event_type; // This creates a string which will be displayed as a constant

		$insertQry = array(
			'dblog_id'          => 0,
			'dblog_datestamp'   => intval($time_sec),
			'dblog_microtime'   => intval($time_usec),
			'dblog_eventcode'   => $eventcode,
			'dblog_user_id'     => $userid,
			'dblog_user_name'   => $userstring,
			'dblog_ip'          => $userIP,
			'dblog_title'       => $title,
			'dblog_remarks'     => print_r($event_data,true),
		);

		if($this->rldb->insert("audit_log", $insertQry))
		{
			return true;
		}

		return false;
	}


	/* Legacy function probably not needed
	function get_log_events($count = 15, $offset)
	{
		global $sql;
		$count = intval($count);
		return "Not implemented yet";
	}
	*/



	/**
	 * Removes all events older than $days, or truncates the table if $days == false
	 *
	 * @param integer|false $days
	 * @return void
	 */
	public function purge_log_events($days)
	{
		global $sql;
		if ($days == false)
		{ // $days is false, so truncate the log table
			$sql->gen("TRUNCATE TABLE #dblog ");
		}
		else
		{ // $days is set, so remove all entries older than that.
			$days = intval($days);
			$mintime = $days * 24 * 60 * 60;
			$time = time() - $mintime;
			$sql->db_Delete("dblog", "WHERE `dblog_datestamp` < {$time}", true);
		}
	}

	//--------------------------------------
	//		HELPER ROUTINES
	//--------------------------------------
	/**
	 *	Generic routine to log changes to an array. Only elements in $new are checked
	 *
	 *	@param array $new - most recent data being saved
	 *	@param array $old existing data - array is updated with changes, but not saved anywhere
	 *	@param string $event - LAN define or string used as title in log
	 *
	 *	@return bool true if changes found and logged, false otherwise.
	 */
	function logArrayDiffs($new, $old, $event, $logNow = true)
	{
		// $changes = array();
				
		$changes = array_diff_recursive($new,$old);
		
		if (count($changes))
		{
			if($logNow) 
			{
				$this->add($event, print_r($changes,true), E_LOG_INFORMATIVE, '');
			}
			else
			{
				$this->logMessage($changes, LOG_MESSAGE_NODISPLAY, E_MESSAGE_INFO);
			}

			return TRUE;
		}
		
		return FALSE;
	}


	/**
	 *	Logs an entry with all the data from an array, one field per line.
	 *  @deprecated 
	 *	@param string $event - LAN define or string used as title in log
	 *	@param array $target - data to be logged
	 *	@param string $extra - if non-empty, it goes on the first line.
	 *	@param array $niceNames - Normally data is logged in the format keyname=>value, one per line.
	 *		If the $niceName array exists and has a definition, the 'nice Name' is displayed instead of the key name
	 *
	 *	@return none
	 */
	public function logArrayAll($event, $target, $extra = '', $niceNames = NULL)
	{
		
		if($extra == '' && $niceNames == null)
		{
			return $this->add($event, $target, E_LOG_INFORMATIVE, '');	// supports arrays
			
		}
		
		
		$logString = '';
		if ($extra)
		{
			$logString = $extra.'[!br!]';
		}
		$spacer = '';
		$checkNice = ($niceNames != NULL) && is_array($niceNames);
		foreach ($target as $k=>$v)
		{
			if ($checkNice && isset($niceNames[$k]['niceName']))
			{
				$logString .= $spacer.$niceNames[$k]['niceName'].'=>'.$v;
			}
			else
			{
				$logString .= $spacer.$k.'=>'.$v;
			}
			$spacer = '[!br!]';
		}
		$this->add($event, $logString, E_LOG_INFORMATIVE, '');
	}

	/**
	 *	The next two routines accept and buffers messages which are destined for both admin log and message handler
	 */

	/**
	 *	Add a message to the queue
	 *
	 *	@param string|array $text - the message text for logging/display
	 *	@param int $type - the 'importance' of the message. E_MESSAGE_SUCCESS|E_MESSAGE_ERROR|E_MESSAGE_INFO|E_MESSAGE_DEBUG|E_MESSAGE_NODISPLAY
	 *				(Values as used in message handler, apart from the last, which causes the message to not be passed to the message handler
	 *	@param boolean|int $logLevel - TRUE to give same importance as for message display. FALSE to not log.
	 *										one of the values specified for $mesLevel to determine the prefix attached to the log message
	 *  @param boolean $session add session message
	 *
	 *	@return e_admin_log
	 */
	public function logMessage($text, $type = '', $logLevel = TRUE, $session = FALSE)
	{
		
		if(is_array($text))
		{
			$text = print_r($text,true);	
		}
		elseif(empty($text))
		{
			$bt = debug_backtrace(true);
			e107::getMessage()->addDebug("Log Message was empty: ".print_a($bt[1],true));
			return $this;	// changing to text will break chained methods. 
		} 
		
		if(!$type) $type = E_MESSAGE_INFO;
		if($logLevel === TRUE) $logLevel = $type;
		
		$logArray = array('message' => $text, 'dislevel' => $type, 'loglevel' => $logLevel, 'session' => $session, 'time'=>time());
		
		$this->_messages[] = $logArray;
		$this->_allMessages[] = $logArray;
		
		return $this;
	}



	/**
	 * @DEPRECATED
	 * BC Alias for addSuccess(); 
	 */
	public function logSuccess($text, $message = true, $session = false)
	{
		return $this->addSuccess($text,$message,$session);	
	}



	/**
	 * @DEPRECATED
	 * BC Alias for addError(); 
	 */
	public function logError($text, $message = true, $session = false)
	{
		return $this->addError($text,$message,$session);	
	}


	/**
	 * Add a success message to the log queue
	 *
	 * @param string|array $text
	 * @param boolean $message if true - register with eMessage handler
	 * @param boolean $session add session message
	 * @return e_admin_log
	 */
	public function addSuccess($text, $message = true, $session = false)
	{
		return $this->logMessage($text, ($message ? E_MESSAGE_SUCCESS : LOG_MESSAGE_NODISPLAY), E_MESSAGE_SUCCESS, $session);
	}


	/**
	 * Add an error message to the log queue
	 *
	 * @param string $text
	 * @param boolean $message if true (default) - register with eMessage handler, set to false to hide. 
	 * @param boolean $session add session message
	 * @return e_admin_log
	 */
	public function addError($text, $message = true, $session = false)
	{
		return $this->logMessage($text, ($message ? E_MESSAGE_ERROR : LOG_MESSAGE_NODISPLAY), E_MESSAGE_ERROR, $session);
	}


	/**
	 * Add an Debug message to the log queue
	 *
	 * @param string $text
	 * @param boolean $message if true (default) - register with eMessage handler, set to false to hide . 
	 * @param boolean $session add session message
	 * @return e_admin_log
	 */
	public function addDebug($text, $message = true, $session = false)
	{
		return $this->logMessage($text, ($message ? E_MESSAGE_DEBUG : LOG_MESSAGE_NODISPLAY), E_MESSAGE_DEBUG, $session);
	}


	/**
	 * Add an Warning message to the log queue
	 *
	 * @param string $text
	 * @param boolean $message if true (default) - register with eMessage handler, set to false to hide. 
	 * @param boolean $session add session message
	 * @return e_admin_log
	 */
	public function addWarning($text, $message = true, $session = false)
	{
		return $this->logMessage($text, ($message ? E_MESSAGE_WARNING : LOG_MESSAGE_NODISPLAY), E_MESSAGE_WARNING, $session);
	}
	
	
	/**
	 * Add an array to the log queue
	 * @param $array
	 * @param $oldArray (optional) - when included, only the changes between the arrays is saved. 
	 * @param $type (optional) default: LOG_MESSAGE_NODISPLAY. or E_MESSAGE_WARNING, E_MESSAGE_DEBUG, E_MESSAGE_SUCCESS
	 */
	public function addArray($array, $oldArray= null, $type = LOG_MESSAGE_NODISPLAY , $session = false)
	{
		if(is_array($oldArray)) 
		{
			$text = array_diff_recursive($array,$oldArray); // Located in core_functions.php 
			if(count($text) < 1)
			{
				$text = "No differences found";	
			}
			
		}
		else
		{
			$text = $array;	
		}
			
		return $this->logMessage($text, $type, $type, $session);	
	}

	/**
	 *	Empty the messages - pass to both admin log and message handler
	 *
	 *	@param string $logTitle - title for log entry
	 *	@param int $logImportance - passed directly to admin log
	 *	@param string $logEventCode - passed directly to admin log
	 *	@param string $mstack [optional] message stack passed to message handler
	 *	@return e_admin_log
	 */
	public function flushMessages($logTitle, $logImportance = E_LOG_INFORMATIVE, $logEventCode = '', $mstack = false, $target =LOG_TO_ADMIN)
	{
		$mes = e107::getMessage();
				
		$resultTypes = array(E_MESSAGE_SUCCESS => 'Success', E_MESSAGE_ERROR => 'Fail');	// Add LANS here. Could add other codes
		$separator = '';
		$logString = '';
		foreach ($this->_messages as $m)
		{
			if ($m['loglevel'] !== FALSE)
			{
				$logString .= $separator;
				if ($m['loglevel'] == LOG_MESSAGE_NODISPLAY) { $logString .= '  '; }		// Indent supplementary messages
			// Not sure about next line - might want to log the <br /> as text, rather than it forcing a newline
				$logString .= strip_tags(str_replace(array('<br>', '<br/>', '<br />'), '[!br!]', $m['message']));
				if (isset($resultTypes[$m['loglevel']]))
				{
					$logString .= ' - '.$resultTypes[$m['loglevel']];
				}
				$separator = '[!br!]';
			}
			if ($m['dislevel'] != LOG_MESSAGE_NODISPLAY)
			{
				if($mstack) 
				{
					$mes->addStack($m['message'], $mstack, $m['dislevel'], $m['session']);
					// move to main stack OUTSIDE if needed 
				}
				else $mes->add($m['message'], $m['dislevel'], $m['session']);
			}
		}
		$this->add($logTitle, $logString, $logImportance, $logEventCode, $target);
		$this->_messages = array();		// Clear the memory for reuse

		return $this;
	}





	/**
	 * Clear all messages in 'memory'. 
	 */
	public function clear()
	{
		$this->_messages = array();	
		
		return $this;		
	}

	
	/**
	 * Save Message stack to File. 
	 */
	private function saveToFile($logTitle='', $append=false, $opts = array())
	{
		if($this->logFile == null)
		{
			 return;
		}
				
		if(count($this->_allMessages))
		{
			$head = "  e107 CMS Log file : ".$logTitle."   ".date('Y-m-d_H-i-s')."\n";
			$head .= "-------------------------------------------------------------------------------------------\n\n";		
		}
		else 
		{
			return; 	
		}		

		$text = '';

		foreach($this->_allMessages as $m)
		{
			$text .= date('Y-m-d H:i:s', $m['time'])."  \t".str_pad($m['loglevel'],10," ",STR_PAD_RIGHT)."\t".strip_tags($m['message'])."\n";
		}
		
		$date = ($append == true) ? date('Y-m-d') : date('Y-m-d_H-i-s').'_'.crc32($text);


		
		$dir = e_LOG;

		if(empty($this->_current_plugin))
		{
			$this->_current_plugin = deftrue('e_CURRENT_PLUGIN');
		}

		if(!empty($this->_current_plugin)) // If it's a plugin, create a subfolder.
		{
			$dir = e_LOG.$this->_current_plugin."/";
			
			if(!is_dir($dir))
			{
				mkdir($dir,0755);	
			}	
		}
		
		$fileName = $dir.$date."_".$this->logFile.".log";

		if(!empty($opts['filename']))
		{
			$fileName = $dir.basename($opts['filename']);
		}
		
		if($append == true)
		{
			$app = FILE_APPEND;
			if(!file_exists($fileName))
			{
				$text = $head . $text;	
			}
		}
		else 
		{
			$app = null;
			$text = $head . $text;	
		}
				
		if(file_put_contents($fileName, $text, $app))
		{
			$this->_allMessages = array();
			$this->_current_plugin = null;
			return $this->logFile;
		}
		elseif(getperms('0') && E107_DEBUG_LEVEL > 0)
		{
			e107::getMessage()->addDebug("Couldn't Save to Log File: ".$fileName);
		}	

		$this->_current_plugin = null;

		return false;
	}	
	



	/**
	 * Set and save accumulated log to a file. 
	 * Use addDebug(), addError() or addSuccess() prior to executing.  
	 * @param string name without the extension. (ie. date prefix and .log suffix will be added automatically)
	 * @param string Title for use inside the Log file
	 * @param boolean true = append to file, false = new file each save. 
	 */
	public function toFile($name, $logTitle='',$append=false, $opts=array())
	{

		$this->logFile	= $name;
		$file = $this->saveToFile($logTitle,$append,$opts);

		$this->logFile = null;
		return $file;
	}


}
