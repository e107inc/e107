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
| $Revision: 1.17 $
| $Date: 2005-03-19 03:01:33 $
| $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
	
class e_shortcode {
	var $scList;
	var $parseSCFiles;
	var $addedCodes;
	var $registered_codes;

	function e_shortcode()
	{
		global $pref, $register_sc;
		if($pref['plug_sc'] != '')
		{
			$tmp = explode(',',$pref['plug_sc']);
			foreach($tmp as $val)
			{
				list($code, $path) = explode(':',$val);
				$this->registered_codes[$code]['type'] = 'plugin';
				$this->registered_codes[$code]['path'] = $path;
			}
		}
		if(isset($register_sc) && is_array($register_sc))
		{
			foreach($register_sc as $code)
			{
				$this->registered_codes[$code]['type'] = 'theme';
			}
		}
	}
		 
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
	 
	function doCode($matches)
	{
		global $pref, $e107cache, $menu_pref, $sc_style;
		if (strpos($matches[1], '='))
		{
			list($code, $parm) = explode("=", $matches[1], 2);
		}
		else
		{
			$code = $matches[1];
			$parm = '';
		}
		$parm = trim(chop($parm));

		if (E107_DEBUG_LEVEL)
		{
			global $db_debug;
			$db_debug->logCode(2, $code, $parm, "");
		}

		if (is_array($this->scList) && array_key_exists($code, $this->scList))
		{
			$shortcode = $this->scList[$code];
		}
		else
		{
			if ($this->parseSCFiles == TRUE) 
			{
				if (array_key_exists($code, $this->registered_codes))
				{
					if($this->registered_codes[$code]['type'] == 'plugin')
					{
						$scFile = e_PLUGIN.strtolower($this->registered_codes[$code]['path']).'/'.strtolower($code).'.sc';
					}
					else
					{
						$scFile = THEME.strtolower($code).'.sc';
					}
				}
				else
				{
						$scFile = e_FILE."shortcode/".strtolower($code).".sc";
				}
				if (file_exists($scFile)) {
					$shortcode = file_get_contents($scFile);
					$this->scList[$code] = $shortcode;
				}
			}
		}
		$ret = (isset($shortcode) ? eval($shortcode) : "");
		if($ret != '')
		{
			if(isset($sc_style) && is_array($sc_style) && array_key_exists($code,$sc_style))
			{
				if(isset($sc_style[$code]['pre']))
				{
					$ret = $sc_style[$code]['pre'].$ret;
				}
				if(isset($sc_style[$code]['post']))
				{
					$ret = $ret.$sc_style[$code]['post'];
				}
			}
		}
		return $ret;
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
				$ret[$cur_sc]='';
			}
		}
		return $ret;
	}
}
	
?>