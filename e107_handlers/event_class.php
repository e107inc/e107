<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/event_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

class e107_event {
	var $functions = array();
	var $includes = array();

	function register($eventname, $include, $function) {
		if ($include) {
			$this->includes[$eventname][] = $include;
		}
		$this->functions[$eventname][] = $function;
	}

	function trigger($eventname, $data) {
		foreach($this->includes[$eventname] as $evt_inc){
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
