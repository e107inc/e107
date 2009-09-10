<?php 
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     ?Steve Dunstan 2001-2002
 |     http://e107.org
 |     jalist@e107.org
 |
 |     Released under the terms and conditions of the
 |     GNU General Public License (http://gnu.org).
 |
 |     $Source: /cvs_backup/e107_0.8/e107_handlers/admin_log_class.php,v $
 |     $Revision: 1.15 $
 |     $Date: 2009-09-10 19:08:36 $
 |     $Author: secretr $
 To do:
 1. Do we need to check for presence of elements of debug_backtrace() to avoid notices?
 2. Reflect possible DB structure changes once finalised
 3. Ad user audit trail
 +----------------------------------------------------------------------------+
 */

if (!defined('e107_INIT'))
{
	exit;
}

/**
 * Admin logging class.
 *
 */
class e_admin_log
{

	/**
	 * Contains default class options, plus any that are overidden by the constructor
	 *
	 * @var array
	 */
	var $_options = array('log_level'=>2, 'backtrace'=>false, );
	var $rldb = NULL; // Database used by logging routine
	
	/**
	 * Constructor. Sets up constants and overwrites default options where set.
	 *
	 * @param array $options
	 * @return e_admin_log
	 */
	function __construct($options = array())
	{
		foreach ($options as $key=>$val)
		{
			$this->_options[$key] = $val;
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
	}
	
	/**
	 * Alternative admin log entry point - compatible with legacy calls, and a bit simpler to use than the generic entry point.
	 * ($eventcode has been added - give it a reference to identify the source module, such as 'NEWS_12' or 'ECAL_03')
	 * We also log everything (unlike 0.7, where admin log and debug stuff were all mixed up together)
	 *
	 * @param string $event_title
	 * @param mixed $event_detail
	 * @param integer $event_type [optional] Log level
	 * @param unknown $event_code [optional]
	 * @return e_admin_log
	 */
	function log_event($event_title, $event_detail, $event_type = E_LOG_INFORMATIVE , $event_code = '')
	{
		global $e107,$tp;
		if ($event_code == '')
		{
			if (strlen($event_title) <= 10)
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
			$tmp = array();
			if (isset($event_detail['data']))
			{
				foreach ($event_detail as $v)
				{
					$tmp[] = $v;
				}
			}
			$event_detail = implode(', ', $tmp);
			unset($tmp);
		}
		
		if ($this->_options['backtrace'] == true)
		{
			$event_detail .= "\n\n".debug_backtrace();
		}
		$this->e_log_event($event_type, -1, $event_code, $event_title, $event_detail, FALSE, LOG_TO_ADMIN);
		
		return $this;
	}
	
	/*
	 Generic log entry point
	 -----------------------
	 Example call: (Deliberately pick separators that shouldn't be in file names)
	 e_log_event(E_LOG_NOTICE,__FILE__."|".__FUNCTION__."@".__LINE__,"ECODE","Event Title","explanatory message",FALSE,LOG_TO_ADMIN);
	 or:
	 e_log_event(E_LOG_NOTICE,debug_backtrace(),"ECODE","Event Title","explanatory message",TRUE,LOG_TO_ROLLING);
	 
	 Parameters:
	 $importance - importance of event - 0..4 or so
	 $source_call - either:	string identifying calling file/routine
	 or:		a number 0..9 identifying info to log from debug_backtrace()
	 or:		empty string, in which case first entry from debug_backtrace() logged
	 or:		an array, assumed to be from passing debug_backtrace() as a parameter, in which case relevant
	 information is extracted and the argument list from the first entry logged
	 or:		-1, in which case no information logged
	 $eventcode - abbreviation listing event type
	 $event_title - title of event - pass standard 'LAN_ERROR_nn' defines to allow language translation
	 $explain - detail of event
	 $finished - if TRUE, aborts execution
	 $target_logs - flags indicating which logs to update - if entry to be posted in several logs, add (or 'OR') their defines:
	 LOG_TO_ADMIN		- admin log
	 LOG_TO_AUDIT		- audit log
	 LOG_TO_ROLLING		- rolling log
	 */
	function e_log_event($importance, $source_call, $eventcode = "GEN", $event_title = "Untitled", $explain = "", $finished = FALSE, $target_logs = LOG_TO_AUDIT )
	{
		global $pref,$e107,$tp;
		
		list($time_usec, $time_sec) = explode(" ", microtime()); // Log event time immediately to minimise uncertainty
		$time_usec = $time_usec * 1000000;
		
		if ($this->rldb == NULL)
			$this->rldb = new db; // Better use our own db - don't know what else is going on
			
		if (is_bool($target_logs))
		{ // Handle the legacy stuff for now - some old code used a boolean to select admin or rolling logs
			$target_logs = $target_logs ? LOG_TO_ADMIN : LOG_TO_ROLLING;
		}
		
		//---------------------------------------
		// Calculations common to all logs
		//---------------------------------------
		$userid = (USER === TRUE) ? USERID : 0;
		$userstring = (USER === true ? USERNAME : "LAN_ANONYMOUS");
		$userIP = $e107->getip();
		
		$importance = $tp->toDB($importance, true, false, 'no_html');
		$eventcode = $tp->toDB($eventcode, true, false, 'no_html');
		
		if (is_array($explain))
		{
			$line = '';
			$spacer = '';
			foreach ($explain as $k=>$v)
			{
				$line .= $spacer.$k.'=>'.$v;
				$spacer = '[!br!]';
			}
			$explain = $line;
			unset($line);
		}
		$explain = mysql_real_escape_string($tp->toDB($explain, true, false, 'no_html'));
		$event_title = $tp->toDB($event_title, true, false, 'no_html');
		
		//---------------------------------------
		// 			Admin Log
		//---------------------------------------
		if ($target_logs & LOG_TO_ADMIN)
		{ // Admin log - assume all fields valid
			$qry = " 0, ".intval($time_sec).','.intval($time_usec).", '{$importance}', '{$eventcode}', {$userid}, '{$userIP}', '{$event_title}', '{$explain}' ";
			$this->rldb->db_Insert("admin_log", $qry);
		}
		
		//---------------------------------------
		// 			Audit Log
		//---------------------------------------
		// Add in audit log here
		
		//---------------------------------------
		// 			Rolling Log
		//---------------------------------------
		if (($target_logs & LOG_TO_ROLLING) && varsettrue($pref['roll_log_active']))
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
			$this->rldb->db_Insert("dblog", "0, ".intval($time_sec).', '.intval($time_usec).", '{$importance}', '{$eventcode}', {$userid}, '{$userstring}', '{$userIP}', '{$source_call}', '{$event_title}', '{$explain}' ");
			
			// Now delete any old stuff
			$this->rldb->db_Delete("dblog", "dblog_datestamp < '".intval(time() - (varset($pref['roll_log_days'], 7) * 86400))."' ");
		}
		
		if ($finished)
			exit; // Optional abort for all logs
	}
	
	//--------------------------------------
	//		USER AUDIT ENTRY
	//--------------------------------------
	// $event_code is a defined constant (see above) which specifies the event
	// $event_data is an array of data fields whose keys and values are logged (usually user data, but doesn't have to be - can add messages here)
	// $id and $u_name are left blank except for admin edits and user login, where they specify the id and login name of the 'target' user
	function user_audit($event_type, $event_data, $id = '', $u_name = '')
	{
		global $e107,$tp,$pref;
		list($time_usec, $time_sec) = explode(" ", microtime()); // Log event time immediately to minimise uncertainty
		$time_usec = $time_usec * 1000000;
		
		// See whether we should log this
		$user_logging_opts = array_flip(explode(',', varset($pref['user_audit_opts'], '')));
		if (!isset($user_logging_opts[$event_type]))
			return; // Finished if not set to log this event type
			
		if ($this->rldb == NULL)
			$this->rldb = new db; // Better use our own db - don't know what else is going on
			
		if ($id) $userid = $id;
		else $userid = (USER === TRUE) ? USERID : 0;
		if ($u_name) $userstring = $u_name;
		else $userstring = (USER === true ? USERNAME : "LAN_ANONYMOUS");
		$userIP = $e107->getip();
		$eventcode = 'USER_'.$event_type;
		
		$title = 'LAN_AUDIT_LOG_0'.$event_type; // This creates a string which will be displayed as a constant
		$spacer = '';
		$detail = '';
		foreach ($event_data as $k=>$v)
		{
			$detail .= $spacer.$k.'=>'.$v;
			$spacer = '<br />';
		}
		$this->rldb->db_Insert("audit_log", "0, ".intval($time_sec).', '.intval($time_usec).", '{$eventcode}', {$userid}, '{$userstring}', '{$userIP}', '{$title}', '{$detail}' ");
	}
	
	function get_log_events($count = 15, $offset)
	{
		global $sql;
		$count = intval($count);
		return "Not implemented yet";
	}
	
	/**
	 * Removes all events older than $days, or truncates the table if $days == false
	 *
	 * @param int $days
	 */
	function purge_log_events($days)
	{
		global $sql;
		if ($days == false)
		{ // $days is false, so truncate the log table
			$sql->db_Select_gen("TRUNCATE TABLE #dblog ");
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
	// Generic routine to log changes to an array. Only elements in $new are checked
	// Returns true if changes, false otherwise.
	// Only makes log entry if changes detected.
	// The $old array is updated with changes, but not saved anywhere
	function logArrayDiffs(&$new, &$old, $event)
	{
		$changes = array();
		foreach ($new as $k=>$v)
		{
			if ($v != $old[$k])
			{
				$old[$k] = $v;
				$changes[] = $k.'=>'.$v;
			}
		}
		if (count($changes))
		{
			$this->log_event($event, implode('[!br!]', $changes), E_LOG_INFORMATIVE, '');
			return TRUE;
		}
		return FALSE;
	}
	
	// Logs an entry with all the data from an array, one field per line.
	// If $extra is non-empty, it goes on the first line.
	// Normally data is in the format keyname=>value, one per line.
	// If the $niceName array exists and has a definition, the 'nice Name' is displayed instead of the key name
	function logArrayAll($event, $target, $extra = '', $niceNames = NULL)
	{
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
		$this->log_event($event, $logString, E_LOG_INFORMATIVE, '');
	}
	
}

?>
