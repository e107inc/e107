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
| $Revision: 1.27 $
| $Date: 2009-07-21 06:31:23 $
| $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

function register_shortcode($classFunc, $codes, $path='', $force=false)
{
	$e107 = e107::getInstance();
	$sc = &$e107->tp->e_sc;

	//If codes is set to true, let's go get a list of shortcode methods
	if(is_bool($codes) && $codes === true)
	{
		$codes = array();
		$tmp = get_class_methods($classFunc);
		foreach($tmp as $c)
		{
			if(strpos($c, 'sc_') === 0)
			{
				$codes[] = substr($c, 3);
			}
		}
		unset($tmp);
	}

	//We only register these shortcodes if they have not already been registered in some manner
	//ie theme or other plugin .sc files
	if(is_array($codes))
	{
		foreach($codes as $code)
		{
			$code = strtoupper($code);
			if((!$sc->isRegistered($code) || $force == true) && !$sc->isOverride($code))
			{
				$sc->registered_codes[$code] = array('type' => 'class', 'path' => $path, 'class' => $classFunc);
			}
		}
	}
	else
	{
		$codes = strtoupper($codes);
		if((!$sc->isRegistered($code) || $force == true) && !$sc->isOverride($code))
		{
			$sc->registered_codes[$codes] = array('type' => 'func', 'path' => $path, 'function' => $classFunc);
		}
	}
}

function setScVar($scName, $scVar, &$value)
{
	$e107 = e107::getInstance();
	$e107->tp->e_sc->scClasses[$scName]->$scVar = $value;
}

function initShortcodeClass($class)
{
	$e107 = e107::getInstance();
	$sc = &$e107->tp->e_sc;
	if(class_exists($class))
	{
		$sc->scClasses[$class] = new $class;
	}
}

class e_shortcode
{
	var $scList = array();						// The actual code - added by parsing files or when plugin codes encountered. Array key is the shortcode name.
	var $parseSCFiles;								// True if individual shortcode files are to be used
	var $addedCodes;									// Apparently not used
	var $registered_codes = array();	// Shortcodes added by plugins
	var $scClasses = array();					// Batch shortcode classes
	var $scOverride = array();				// Array of codes found in override/ dir

	function e_shortcode($noload=false)
	{
		global $pref, $register_sc;

		$this->parseSCFiles = true;			// Default probably never used, but make sure its defined.

		//Register any shortcode from the shortcode/override/ directory
		if(varset($pref['sc_override']))
		{
			$tmp = explode(',', $pref['sc_override']);
			foreach($tmp as $code)
			{
				$code = strtoupper(trim($code));
				$this->registered_codes[$code]['type'] = 'override';
				$this->scOverride[] = $code;
			}
		}

		// Register any shortcodes that were registered by the theme
		// $register_sc[] = 'MY_THEME_CODE'
		if(isset($register_sc) && is_array($register_sc))
		{
			foreach($register_sc as $code)
			{
				if(!$this->isRegistered($code))
				{
					$code = strtoupper($code);
					$this->registered_codes[$code]['type'] = 'theme';
				}
			}
		}

		// Register all .sc files found in plugin directories (via pref)
		if(varset($pref['shortcode_list'], '') != '')
		{
			foreach($pref['shortcode_list'] as $path => $namearray)
			{
				foreach($namearray as $code => $uclass)
				{
					if($code == 'shortcode_config')
					{
						include_once(e_PLUGIN.$path.'/shortcode_config.php');
					}
					else
					{
						$code = strtoupper($code);
						if(!$this->isRegistered($code))
						{
							$this->registered_codes[$code]['type'] = 'plugin';
							$this->registered_codes[$code]['path'] = $path;
							$this->registered_codes[$code]['perms'] = $uclass;			// Add this in
						}
					}
				}
			}
		}
	}

	function loadCoreShortcodes()
	{
		$coreBatchList = array('siteinfo_shortcodes.php', 'admin_shortcodes.php');
		foreach($coreBatchList as $cb)
		{
			include_once(e_FILE.'shortcode/batch/'.$cb);
		}
	}

	function isRegistered($code)
	{
		return in_array($code, $this->registered_codes);
	}

	function isOverride($code)
	{
		return in_array($code, $this->scOverride);
	}

	function parseCodes($text, $useSCFiles = true, $extraCodes = '')
	{
		$saveParseSCFiles = $this->parseSCFiles;		// In case of nested call
		$this->parseSCFiles = $useSCFiles;
		if(is_array($extraCodes))
		{
			foreach($extraCodes as $sc => $code)
			{
				$this->scList[$sc] = $code;
			}
		}
		$ret = preg_replace_callback('#\{(\S[^\x02]*?\S)\}#', array(&$this, 'doCode'), $text);
		$this->parseSCFiles = $saveParseSCFiles;		// Restore previous value
		return $ret;
	}

	function doCode($matches)
	{
		global $pref, $e107cache, $menu_pref, $sc_style, $parm, $sql;

		if(strpos($matches[1], E_NL) !== false) { return $matches[0]; }

		if (strpos($matches[1], '='))
		{
			list($code, $parm) = explode('=', $matches[1], 2);
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
		$parm = str_replace(array('[[', ']]'), array('{', '}'), $parm);

		if (E107_DBG_BBSC || E107_DBG_SC)
		{
			global $db_debug;
			$sql->db_Mark_Time("SC $code");
			$db_debug->logCode(2, $code, $parm, "");
		}

		if(E107_DBG_SC)
		{
			echo "<strong>";
            echo '{';
			echo $code;
			echo ($parm) ? '='.htmlentities($parm) : "";
			echo '}';
			echo "</strong>";
		   //	trigger_error('starting shortcode {'.$code.'}', E_USER_ERROR);    // no longer useful - use ?[debug=bbsc]
		}

		$scCode = '';
		$scFile = '';
		// Check to see if we've already loaded the .sc file contents
		if (array_key_exists($code, $this->scList))
		{
			$scCode = $this->scList[$code];
		}
		else
		{
			//.sc file not yet loaded, or shortcode is new function type
			if ($this->parseSCFiles == true)
			{
				if (array_key_exists($code, $this->registered_codes))
				{
					//shortcode is registered, let's proceed.
					if(isset($this->registered_codes[$code]['perms']))
					{
						if(!check_class($this->registered_codes[$code]['perms'])) { return ''; }
					}

					switch($this->registered_codes[$code]['type'])
					{
						case 'class':
							//It is batch shortcode.  Load the class and call the method
							$_class = $this->registered_codes[$code]['class'];
							$_method = 'sc_'.strtolower($code);
							if(!isset($this->scClasses[$_class]))
							{
								if(!class_exists($_class) && $this->registered_codes[$code]['path'])
								{
									include_once($this->registered_codes[$code]['path']);
								}
								$this->scClasses[$_class] = new $_class;
							}
							if(method_exists($this->scClasses[$_class], $_method))
							{
								
								$ret = $this->scClasses[$_class]->$_method($parm, $sc_mode);
							}
							else
							{
								echo $_class.'::'.$_method.' NOT FOUND!<br />';
							}
								
							break;

						case 'func':
							//It is a function, so include the file and call the function
							$_function = $this->registered_codes[$code]['function'];
							if($this->registered_codes[$code]['path'])
							{
								include_once($this->registered_codes[$code]['path'].strtolower($code).'.php');
							}
							if(function_exists($_function))
							{
								$ret = call_user_func($_function, $parm, $sc_mode);
							}
							break;

						case 'plugin':
							$scFile = e_PLUGIN.strtolower($this->registered_codes[$code]['path']).'/'.strtolower($code).'.sc';
							break;

						case 'override':
							$scFile = e_FILE.'shortcode/override/'.strtolower($code).'.sc';
							break;

						case 'theme':
							$scFile = THEME.strtolower($code).'.sc';
							break;

					}
				}
				else
				{
					// Code is not registered, let's look for .sc or .php file
					// .php file takes precedence over .sc file
					if(is_readable(e_FILE.'shortcode/'.strtolower($code).'.php'))
					{
						$_function = strtolower($code).'_shortcode';
						include_once(e_FILE.'shortcode/'.strtolower($code).'.php');
						if(function_exists($_function))
						{
							$ret = call_user_func($_function, $parm);
						}
					}
					else
					{
						$scFile = e_FILE.'shortcode/'.strtolower($code).'.sc';
					}
				}
				if ($scFile && file_exists($scFile))
				{
					$scCode = file_get_contents($scFile);
					$this->scList[$code] = $scCode;
				}
			}

			if (!isset($scCode))
			{
				if(E107_DBG_BBSC) { trigger_error('shortcode not found:{'.$code.'}', E_USER_ERROR); }
				return $matches[0];
			}

			if(E107_DBG_SC && $scFile)
			{
			  //	echo (isset($scFile)) ? "<br />sc_file= ".str_replace(e_FILE.'shortcode/', '', $scFile).'<br />' : '';
			  //	echo "<br />sc= <b>$code</b>";
			}
		}

		if($scCode)
		{
			$ret = eval($scCode);
		}

		if($ret != '' || is_numeric($ret))
		{
			//if $sc_mode exists, we need it to parse $sc_style
			if($sc_mode)
			{
				$code = $code.'|'.$sc_mode;
			}
			if(isset($sc_style) && is_array($sc_style) && array_key_exists($code, $sc_style))
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
		if (E107_DBG_SC)
		{
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
			$batch_cachefile = 'nomd5_scbatch_'.md5($fname);
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
			if (array_key_exists($cur_sc, $this -> registered_codes))
			{
				if ($this -> registered_codes[$cur_sc]['type'] == 'plugin')
				{
					$scFile = e_PLUGIN.strtolower($this -> registered_codes[$cur_sc]['path']).'/'.strtolower($cur_sc).'.sc';
				}
				else
				{
					$scFile = THEME.strtolower($cur_sc).'.sc';
				}
				if (is_readable($scFile))
				{
					$cur_shortcodes[$cur_sc] = file_get_contents($scFile);
				}
			}
		}
		return $cur_shortcodes;
	}
}
?>