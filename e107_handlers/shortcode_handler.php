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
| $Source: /cvs_backup/e107_0.8/e107_handlers/shortcode_handler.php,v $
| $Revision: 1.7 $
| $Date: 2007-06-13 02:53:21 $
| $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

function register_shortcode($code, $filename, $function, $force=false)
{
	global $e_shortcodes;
	if(!array_key_exists($code, $e_shortcodes) || $force == true)
	{
		$e_shortcodes[$code] = array('file' => $filename, 'function' => $function);
	}
}

class e_shortcode {
	var $scList;
	var $parseSCFiles;
	var $addedCodes;
	var $registered_codes;

	function e_shortcode()
	{
		global $pref, $register_sc;

		$this->shortcode_functions = array();
		if(varset($pref['shortcode_list'],'') != '')
		{
        	foreach($pref['shortcode_list'] as $path=>$namearray)
			{
				foreach($namearray as $code=>$uclass)
				{
					if($code == 'shortcode_config')
					{
						include_once(e_PLUGIN.$path.'/shortcode_config.php');
					}
					else
					{
						$code = strtoupper($code);
						$this->registered_codes[$code]['type'] = 'plugin';
                	$this->registered_codes[$code]['path'] = $path;
					}
				}
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
				$ret .= preg_replace_callback("#\{(\S[^\x02]*?\S)\}#", array($this, 'doCode'), $line);
			} else {
				$ret .= $line;
			}
		}
		return $ret;
	}

	function doCode($matches)
	{
		global $pref, $e107cache, $menu_pref, $sc_style, $parm, $sql, $e_shortcodes;

		if(strpos($matches[1], E_NL) !== false)
		{
			return $matches[0];
		}

		if (strpos($matches[1], '='))
		{
			list($code, $parm) = explode("=", $matches[1], 2);
		}
		else
		{
			$code = $matches[1];
			$parm = '';
		}
		//look for the $sc_mode
		if (strpos($code, '|'))
		{
			list($code, $sc_mode) = explode("|", $code, 2);
			$code = trim($code);
			$sc_mode = trim($sc_mode);
		}
		else
		{
			$sc_mode = '';
		}
		$parm = trim($parm);

		if (E107_DBG_BBSC)
		{
			global $db_debug;
			$sql->db_Mark_Time("SC $code");
			$db_debug->logCode(2, $code, $parm, "");
		}

		/* Check for shortcode registered with $e_shortcodes */
		if(array_key_exists($code, $e_shortcodes))
		{
			include_once($e_shortcodes[$code]['file']);
			if(function_exists($e_shortcodes[$code]['function']))
			{
				$ret = call_user_func($e_shortcodes[$code]['function'], $parm);
			}
		}
		else
		{

			if (is_array($this->scList) && array_key_exists($code, $this->scList))
			{
				$shortcode = $this->scList[$code];
			}
			else
			{
				if ($this->parseSCFiles == TRUE)
				{
					if (is_array($this -> registered_codes) && array_key_exists($code, $this->registered_codes))
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
	
			if (!isset($shortcode))
			{
			  	if(E107_DBG_BBSC) trigger_error("shortcode not found:{".$code."}", E_USER_ERROR);
			  	return $matches[0];
			}

      	if(E107_DBG_SC)
			{
		  	echo " sc= ".str_replace(e_FILE."shortcode/","",$scFile)."<br />";
			}

			if(E107_DBG_BBSC)
			{
		  	trigger_error("starting shortcode {".$code."}", E_USER_ERROR);
			}
			$ret = eval($shortcode);
		}

		if($ret != '' || is_numeric($ret))
		{

			//if $sc_mode exists, we need it to parse $sc_style
			if($sc_mode){
				$code = $code."|".$sc_mode;
			}
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
		if (E107_DBG_SC) {
			$sql->db_Mark_Time("(SC $code Done)");
		}
		return $ret;
	}

	function parse_scbatch($fname, $type = 'file')
	{
		global $e107cache, $eArrayStorage;
		$cur_shortcodes = array();
		if($type == 'file')
		{
			$batch_cachefile = "nomd5_scbatch_".md5($fname);
//			$cache_filename = $e107cache->cache_fname("nomd5_{$batchfile_md5}");
			$sc_cache = $e107cache->retrieve_sys($batch_cachefile);
			if(!$sc_cache)
			{
				$sc_batch = file($fname);
			}
			else
			{
				$cur_shortcodes = $eArrayStorage->ReadArray($sc_cache);
				$sc_batch = "";
			}
		}
		else
		{
			$sc_batch = $fname;
		}

		if($sc_batch)
		{
			$cur_sc = '';
			foreach($sc_batch as $line)
			{
				if (trim($line) == 'SC_END')
				{
					$cur_sc = '';
				}
				if ($cur_sc)
				{
					$cur_shortcodes[$cur_sc] .= $line;
				}
				if (preg_match("#^SC_BEGIN (\w*).*#", $line, $matches))
				{
					$cur_sc = $matches[1];
					$cur_shortcodes[$cur_sc] = varset($cur_shortcodes[$cur_sc],'');
				}
			}
			if($type == 'file')
			{
				$sc_cache = $eArrayStorage->WriteArray($cur_shortcodes, false);
				$e107cache->set_sys($batch_cachefile, $sc_cache);
			}
		}

		foreach(array_keys($cur_shortcodes) as $cur_sc)
		{
			if (is_array($this -> registered_codes) && array_key_exists($cur_sc, $this -> registered_codes)) {
				if ($this -> registered_codes[$cur_sc]['type'] == 'plugin') {
					$scFile = e_PLUGIN.strtolower($this -> registered_codes[$cur_sc]['path']).'/'.strtolower($cur_sc).'.sc';
				} else {
					$scFile = THEME.strtolower($cur_sc).'.sc';
				}
				if (is_readable($scFile)) {
					$cur_shortcodes[$cur_sc] = file_get_contents($scFile);
				}
			}
		}
		return $cur_shortcodes;
	}
}

?>
