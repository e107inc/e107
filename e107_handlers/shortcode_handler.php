<?php
	
/*
+ ----------------------------------------------------------------------------+
| e107 website system
|
| ©Steve Dunstan 2001-2002
| http://e107.org
| jalist@e107.org
|
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org).
|
| $Source: /cvs_backup/e107_0.7/e107_handlers/shortcode_handler.php,v $
| $Revision: 1.7 $
| $Date: 2005-01-27 19:52:29 $
| $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
class e_shortcode {
	var $scList;
	var $parseSCFiles;
	var $addedCodes;
	 
	function parseCodes($text, $useSCFiles = TRUE, $extraCodes = '') {
		$this->parseSCFiles = $useSCFiles;
		$ret = '';
		if (is_array($extraCodes)) {
			foreach($extraCodes as $sc => $code) {
				$this->scList[$sc] = $code;
			}
		}
		$tmp = explode("\n", $text);
		foreach($tmp as $line) {
			if (preg_match("/{.+?}/", $line, $match)) {
				$ret .= preg_replace_callback("/\{(.*?)\}/", array($this, 'doCode'), $line);
			} else {
				$ret .= $line;
			}
		}
		return $ret;
	}
	 
	function doCode($matches) {
		global $pref, $e107cache, $menu_pref;
		if (strpos($matches[1], '=')) {
			list($code, $parm) = explode("=", $matches[1], 2);
		} else {
			$code = $matches[1];
			$parm = '';
		}
		$parm = trim(chop($parm));
		if (is_array($this->scList) && array_key_exists($code, $this->scList)) {
			$shortcode = $this->scList[$code];
		} else {
			if ($this->parseSCFiles == TRUE) {
				if (($pos = strpos($code, ".")) != FALSE) {
					list($tmp1, $tmp2) = explode(".", $code, 2);
					$scFile = e_PLUGIN.strtolower($tmp1)."/".strtolower($tmp2).".sc";
				} else {
					$scFile = e_FILE."shortcode/".strtolower($code).".sc";
				}
				if (file_exists($scFile)) {
					$shortcode = file_get_contents($scFile);
					$this->scList[$code] = $shortcode;
				}
			}
		}
		return eval($shortcode);
	}
	 
	function parse_scbatch($fname) {
		$ret = array();
		$sc_batch = file($fname);
		$cur_sc = '';
		foreach($sc_batch as $line) {
			if (trim($line) == 'SC_END') {
				$cur_sc = '';
			}
			if ($cur_sc) {
				$ret[$cur_sc] .= $line;
			}
			if (preg_match("#^SC_BEGIN (\w*).*#", $line, $matches)) {
				$cur_sc = $matches[1];
			}
		}
		return $ret;
	}
}
	
?>