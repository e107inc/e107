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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/override_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-22 23:11:12 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class override {
	var $functions = array();
	var $includes = array();
	
	function override_function($override, $function, $include) {
		if ($include) {
			$this->includes[$override] = $include;
		} else if (isset($this->includes[$override])) {
			unset($this->includes[$override]);
		}
		$this->functions[$override] = $function;
	}
	
	function override_check($override) {
		if (isset($this->includes[$override])) {
			if (file_exists($this->includes[$override])) {
				include_once($this->includes[$override]);
			}
			if (function_exists($this->functions[$override])) {
				return $this->functions[$override];
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
}

?>
