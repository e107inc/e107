<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/event_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-12 14:16:25 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

class e107_event {
	var $functions = array();
	var $includes = array();

	function register($eventname, $function, $include) {
		if ($include) {
			$this->includes[$eventname][] = $include;
		}
		$this->functions[$eventname][] = $function;
	}

	function trigger($eventname, $data) {
		foreach($this->includes[$eventname] as $evt_inc) {
			if (file_exists($evt_inc)) {
				include_once($evt_inc);
			}
		}
		foreach($this->functions[$eventname] as $evt_func) {
			if (function_exists($evt_func)) {
				$evt_func($data);
			}
		}
	}
}

?>
