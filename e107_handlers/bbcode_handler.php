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
|     $Revision: 1.21 $
|     $Date: 2005-03-13 10:59:53 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
class e_bbcode {
	 
	var $bbList;
	var $bbLocation;
	 
	function e_bbcode()
	{
		global $pref;
		$core_bb = array(
			'b', 'i', 'img', 'u', 'center', 
			'br', 'color', 'size', 'code', 
			'html', 'flash', 'link', 'email', 
			'url', 'quote', 'left', 'right', 
			'blockquote', 'justify', 'file', 'stream'
			);
		foreach($core_bb as $c)
		{
			$this->bbLocation[$c] = 'core';
		}
		if(isset($pref['plug_bb']) && $pref['plug_bb'] != '')
		{
			$tmp = explode(',',$pref['plug_bb']);
			foreach($tmp as $val)
			{
				list($code, $location) = explode(':',$val);
				$this->bbLocation[$code] = $location;
			}
		}
		$this->bbLocation = array_diff($this->bbLocation,array(''));
	}
	 
	function parseBBCodes($text, $p_ID) {
		global $code;
		global $postID;
		$postID = $p_ID;
		$done = FALSE;
		$i=0;
		while (!$done) {
			$done = TRUE;
			$i++;
			foreach(array_keys($this->bbLocation) as $code) {
				if ($code && ($pos = strpos($text, "[$code")) !== FALSE) {
					$text = preg_replace_callback("/\[({$code}([a-zA-Z]*))([\d]*?)([^\]]*)\](.*?)\[\/{$code}\\2\\3\]/s", array($this, 'doCode'), $text);
					$done = FALSE;
					if($text{$pos} == "[")
					{
						$text = str_replace("[".$code, "&#091;".$code, $text);
					}
				}
			}
			if($i > 200) {
				echo "An error has been detected in the bbcode process, it entered an infinite loop!  Exiting...";
				exit;
			}
		}
		return $text;
	}
	 
	function doCode($matches) {
		global $tp;
		global $postID;
		global $full_text;
		global $code_text;
		global $code;
		global $parm;
		
		$code = $matches[1];

		if (E107_DEBUG_LEVEL)
		{
			global $db_debug;
			$db_debug->logCode(1, $code, $parm, $postID);
		}

		$parm = substr($matches[4], 1);
		$code_text = $matches[5];
		$full_text = $matches[0];
		
		if (is_array($this->bbList) && array_key_exists($code, $this->bbList)) {
			$bbcode = $this->bbList[$code];
		} else {
			if ($this->bbLocation[$code] == 'core') {
				$bbFile = e_FILE.'bbcode/'.strtolower($code).'.bb';
			} else {
				// Add code to check for plugin bbcode addition
				$bbFile = e_PLUGIN.$this->bbLocation[$code].'/'.strtolower($code).'.bb';
			}
			if (file_exists($bbFile)) {
				$bbcode = file_get_contents($bbFile);
				$this->bbList[$code] = $bbcode;
			} else {
				$this->bbList[$code] = '';
				return FALSE;
			}
		}
		return eval($bbcode);
	}
}
?>