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
+----------------------------------------------------------------------------+
*/

class e_shortcode
{
	var $scList;

	function parseCodes($text,$parseSCFiles=TRUE,$extraCodes="")
	{
		if(is_array($extraCodes))
		{
			foreach($extraCodes as $sc => $code)
			{
				$this -> $scList[$sc] = $code;
			}
		}
		$tmp = explode("\n", $text);
		foreach($tmp as $line)
		{
			if(preg_match("/{.+?}/", $line,$match))
			{
				$ret .= preg_replace_callback("/\{(.*?)\}/", array($this, 'doCode'), $line);
			}
			else
			{
				$ret .= $line;
			}
		}
		return $ret;
	}

	function doCode($matches)
	{
		if(($pos = strpos($matches[1],"=")) != FALSE)
		{
			$code = substr($matches[1],0,$pos);
			$parm = substr($matches[1],$pos+1);
		}
		else 
		{
			$code = $matches[1];
			$parm = "";
		}
		
		if(array_key_exists($code,$this -> scList))
		{
			$shortcode = $this -> scList[$code];
		}
		else
		{
			if($parseSCFiles)
			{
				if(($pos = strpos($code,".")) != FALSE)
				{
					list($tmp1,$tmp2) = explode(".",$code,2);
					$scFile = e_PLUGIN.strtolower($tmp1)."/".strtolower($tmp2).".sc";
				}
				else
				{
					$scFile = e_FILE."shortcode/".strtolower($code).".sc";
				}
				if(file_exists($scFile))
				{
					$shortcode = file_get_contents($scFile);
					$this -> scList[$code] = $shortcode;
				}
			}
		}
		return eval($shortcode);
	}
}

?>