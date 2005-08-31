<?php

class e_admin_log {
	
	var $_options = array(
		'log_level' => 2,
		'backtrace' => false,
	);

	function e_admin_log ($options){
		foreach ($options as $key => $val) {
			$this->_options[$key] = $val;
		}
		define("E_LOG_INFORMATIVE", 0);
		define("E_LOG_NOTICE", 1);
		define("E_LOG_WARNING", 2);
		define("E_LOG_FATAL", 3);
	}
	
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