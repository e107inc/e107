<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/admin_log_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-08-31 19:45:09 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

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
	function e_admin_log ($options){
		foreach ($options as $key => $val) {
			$this->_options[$key] = $val;
		}
		define("E_LOG_INFORMATIVE", 0);
		define("E_LOG_NOTICE", 1);
		define("E_LOG_WARNING", 2);
		define("E_LOG_FATAL", 3);
	}
	
	/**
	 * Log an event to the core table
	 *
	 * @param string $event_title
	 * @param string $event_detail
	 * @param int $event_type Log level
	 */
	function log_event ($event_title, $event_detail, $event_type = E_LOG_INFORMATIVE) {
		global $e107, $sql;
		if($event_type >= $this->_options['log_level']) {
			$time_stamp = time();
			$uid = (USER === FALSE) ? USERID : '0';
			$ip = $e107->getip();
			if($this->_options['backtrace'] == true) {
				$event_detail .= "\n\n".debug_backtrace();
			}
			$sql->db_Insert('dblog', "'', '{$event_type}', {$time_stamp}, {$uid}, '{$ip}', '{$event_title}', '{$event_detail}'");
		}
	}
}

?>