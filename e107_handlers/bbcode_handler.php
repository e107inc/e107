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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/bbcode_handler.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-01-24 17:22:55 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

class e_bbcode {
	
	var $bbList;
	var $core_bb;
	
	function e_bbcode() {
		$this -> core_bb=array('b','i','img','u','center','br','color','size','code','html','flash','link','email','url','quote','left','right','blockquote');
	}

	function parseBBCodes($text,$postID) {
		global $code;
		global $postID;
		foreach($this->core_bb as $code) {
			if(strpos($text,"[$code") !== FALSE) {
				$text = preg_replace_callback("/\[{$code}([\d]*)([^\]]*)\](.*?)\[\/{$code}\\1\]/s", array($this, 'doCode'), $text);
			}
		}
			return $text;
	}
	
	function doCode($matches) {
		global $tp;
		global $code;
		global $postID;
		$parm = substr($matches[2],1);
		$code_text = $matches[3];

		if (is_array($this->bbList) && array_key_exists($code,$this->bbList)) {
			$bbcode = $this -> bbList[$code];
		} else {
			if (in_array($code,$this -> core_bb)) {
				$bbFile = e_FILE.'bbcode/'.strtolower($code).'.bb';
			} else {
				// Add code to check for plugin bbcode addition
				$this -> bbList[$code] = '';
				return FALSE;  
			}
			if (file_exists($bbFile)) {
				$bbcode = file_get_contents($bbFile);
				$this -> bbList[$code] = $bbcode;
			} else {
				return FALSE;
			}
		}
		return eval($bbcode);
	}
}
?>