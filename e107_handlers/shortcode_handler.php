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
| $Revision: 1.5 $
| $Date: 2004-12-11 01:35:14 $
| $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class e_shortcode
{
	var $scList;
	var $parseSCFiles;
	var $addedCodes;

	function parseCodes($text,$useSCFiles=TRUE,$extraCodes="") {
		$this->parseSCFiles = $useSCFiles;
		$ret='';
		if (is_array($extraCodes)) {
			foreach($extraCodes as $sc => $code) {
				$this->scList[$sc] = $code;
			}
		}
		$tmp = explode("\n", $text);
		foreach($tmp as $line) {
			if(preg_match("/{.+?}/", $line,$match)) {
				$ret .= preg_replace_callback("/\{(.*?)\}/", array($this, 'doCode'), $line);
			} else {
				$ret .= $line;
			}
		}
		return $ret;
	}

	function doCode($matches) {
		global $pref, $e107cache, $menu_pref;
		if(strpos($matches[1],'=')) {
			list($code,$parm) = explode("=",$matches[1],2);
		} else {
			$code = $matches[1];
			$parm = '';
		}
		$parm=trim(chop($parm));
		if(is_array($this->scList) && array_key_exists($code,$this->scList)) {
			$shortcode = $this -> scList[$code];
		} else {
			if($this->parseSCFiles == TRUE) {
				if(($pos = strpos($code,".")) != FALSE) {
					list($tmp1,$tmp2) = explode(".",$code,2);
					$scFile = e_PLUGIN.strtolower($tmp1)."/".strtolower($tmp2).".sc";
				} else {
					$scFile = e_FILE."shortcode/".strtolower($code).".sc";
				}
				if(file_exists($scFile)) {
					$shortcode = file_get_contents($scFile);
					$this->scList[$code] = $shortcode;
				}
			}
		}
		return eval($shortcode);
	}
}

?>