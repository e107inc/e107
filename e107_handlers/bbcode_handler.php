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
|     $Revision: 1.3 $
|     $Date: 2004-11-28 02:50:46 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class e_bbcode {
	
	var $bbList;
	var $core_bb;
	
	function e_bbcode() {
		$this -> core_bb=array('b','i','img','u','center','br','color','size','code','html','flash','link','email','url','quote','left','right','blockquote');
	}

	function parseBBCodes($text,$postID) {
		while (preg_match_all("/(\[([\w]+\d*?)(.*?)\])(.*?)(\[\/\\2\])/s", $text, $matches)) {
			for ($i=0; $i < count($matches[0]); $i++) {
				$full_text = $matches[0][$i];
				$code = preg_replace("#\d#","",$matches[2][$i]);
				$parm = substr($matches[3][$i],1);
				$code_text = $matches[4][$i];
				$replace_text = $this -> doBB($code,$parm,$code_text,$postID,$full_text);
				if ($replace_text == FALSE) {
					$replace_text = '&#091'.substr($full_text,1,-1).'&#093';
				}
				$text = str_replace($full_text,$replace_text,$text);
			}
		}
		return $text;
	}
	
	function doBB($code,$parm,$code_text,$postID,$full_text) {
		global $tp;
		if (array_key_exists($code,$this -> bbList)) {
			$bbcode = $this -> bbList[$code];
		} else {
			if (in_array($code,$this -> core_bb))
			{
				$bbFile = e_FILE.'bbcode/'.strtolower($code).'.bb';
			} else {
				// Add code to check for plugin bbcode addition
				$this -> bbList[$code] = "";
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