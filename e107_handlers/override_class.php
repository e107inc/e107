<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/override_class.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-11-12 15:11:16 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

/*
 * USAGE
 * In user code, to override an existing function...
 *
 * $override->override_function('original_func_name','mynew_function_name',['optional_include_file_from_root']);
 *
 * In e107 code...
 * if ($over_func_name = $override->override_check('original_func_name')) {
 *	$result=call_user_func($over_func_name, params...);
 * }
 *
 */
 
class override {
	var $functions = array();
	var $includes = array();
	 
	function override_function($override, $function, $include) {
		if ($include) {
			$this->includes[$override] = $include;
		}
		else if (isset($this->includes[$override])) {
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
				return false;
			}
		} else {
			return false;
		}
	}
}
	
?>