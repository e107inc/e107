<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

/**
 * Admin logging class.
 *
 */
class e_admin_log {

	/**
	 * Contains default class options, plus any that are overidden by the constructor
	 *
	 * @var array
	 */
	var $_options = array(
		'log_level' => 2,
		'backtrace' => false,
	);

	/**
	 * Constructor. Sets up constants and overwrites default options where set.
	 *
	 * @param array $options
	 * @return e_admin_log
	 */
	function e_admin_log ($options = array()){
		foreach ($options as $key => $val) {
			$this->_options[$key] = $val;
		}

		/**
		 * Minmal Log Level, including really minor stuff
		 *
		 */

		define("E_LOG_INFORMATIVE", 0);

		/**
		 * More important than informative, but less important than notice
		 *
		 */
		define("E_LOG_NOTICE", 1);

		/**
		 * Not anything serious, but important information
		 *
		 */
		define("E_LOG_WARNING", 2);

		/**
		 * An event so bad your site ceased execution.
		 *
		 */
		define("E_LOG_FATAL", 3);

		/*
		 * 	Plugin Information.
		 */

		define("E_LOG_PLUGIN", 4);
	}

	/**
	 * Log an event to the core table
	 *
	 * @param string $event_title
	 * @param string $event_detail
	 * @param int $event_type Log level
	 */
	function log_event ($event_title, $event_detail, $event_type = E_LOG_INFORMATIVE) {
		global $e107, $sql, $tp;
		if($event_type >= $this->_options['log_level']) {
			$event_title = $tp -> toDB($event_title, true,false,'no_html');
			$event_detail = $tp -> toDB($event_detail, true,false,'no_html');
			$event_type = $tp -> toDB($event_type, true,false,'no_html');
			$time_stamp = time();
			$uid = defined('USERID') ? USERID : '0';
			$ip = $e107->getip();
			if($this->_options['backtrace'] == true) {
				$event_detail .= "\n\n".debug_backtrace();
			}
			if(!$sql->db_Insert('dblog', "'', '{$event_type}', {$time_stamp}, {$uid}, '{$ip}', '{$event_title}', '{$event_detail}' "))
			{
				trigger_error("Couldn't insert into dblog table",E_USER_ERROR);
			}
			
		}
	}

	function get_log_events($count = 15, $offset) {
		global $sql;
		$count = intval($count);
	}

	/**
	 * Removes all events older than $days, or truncates the table if $days == false
	 *
	 * @param int $days
	 */
	function purge_log_events($days) {
		global $sql;
		if($days == false) {
			// $days is false, so truncate the log table
			$sql->db_Select_gen("TRUNCATE TABLE #dblog ");
		} else {
			// $days is set, so remove all entries older than that.
			$days = intval($days);
			$mintime = $days * 24 * 60 * 60;
			$time = time() - $mintime;
			$sql->db_Delete("dblog", "WHERE `dblog_datestamp` < {$time}", true);
		}
	}
}

?>
