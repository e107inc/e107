<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Shortcode handler
 *
 * $URL$
 * $Id$
*/

if (!defined('e107_INIT')) { exit; }

/**
 * FIXME: to be removed
 */
function register_shortcode($classFunc, $codes, $path='', $force = false)
{
	return e107::getScParser()->registerShortcode($classFunc, $codes, $path, $force);
}

/**
 * FIXME: to be removed
 */
function setScVar($className, $scVarName, $value)
{
	return e107::getScParser()->setScVar($className, $scVarName, $value);
}


/**
 * FIXME: to be removed
 */
function callScFunc($className, $scFuncName, $param = '')
{
	return e107::getScParser()->callScFunc($className, $scFuncName, $param);
}

/**
 * FIXME: to be removed
 */
function initShortcodeClass($class, $force = false, $eVars = null)
{
	return e107::getScParser()->initShortcodeClass($class, $eVars, $force);
}


class e_parse_shortcode
{
	protected $scList = array();						// The actual code - added by parsing files or when plugin codes encountered. Array key is the shortcode name.
	protected $parseSCFiles;							// True if individual shortcode files are to be used
	protected $addedCodes;								// Apparently not used
	protected $registered_codes = array();				// Shortcodes added by plugins TODO make it private
	protected $scClasses = array();					// Batch shortcode classes - TODO make it private
	protected $scOverride = array();						// Array of codes found in override/ dir
	protected $eVars = '';

	function __construct()
	{
		$this->parseSCFiles = true;	// Default probably never used, but make sure its defined.

		$this->loadOverrideShortcodes();
		$this->loadThemeShortcodes();
		$this->loadPluginShortcodes();
		$this->loadPluginSCFiles();
		$this->loadCoreShortcodes(); 
	}
	
	/**
	 * Register shortcode
	 * $classFunc could be function name, class name or object
	 * $code could be 'true' when class name/object is passed to automate the
	 * registration of shortcode methods
	 *
	 * @param mixed $classFunc
	 * @param mixed $codes
	 * @param string $path
	 * @param boolean $force override
	 * @return e_parse_shortcode
	 */
	function registerShortcode($classFunc, $codes, $path = '', $force = false)
	{
		//If codes is set to true, let's go get a list of shortcode methods
		if($codes === true) 
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
	
		//Register object feature
		$classObj = null;
		if(is_object($classFunc))
		{
			$classObj = $classFunc;
			$classFunc = get_class($classObj);
	
		}
	
		//We only register these shortcodes if they have not already been registered in some manner
		//ie theme or other plugin .sc files
		if(is_array($codes))
		{
			foreach($codes as $code)
			{
				$code = strtoupper($code);
				if((!$this->isRegistered($code) || $force == true) && !$this->isOverride($code))
				{
					$this->registered_codes[$code] = array('type' => 'class', 'path' => $path, 'class' => $classFunc);
				}
			}
	
			//register object if required
			if(null !== $classObj && (!isset($this->scClasses[$classFunc]) || $force == true))
			{
				$this->scClasses[$classFunc] = $classObj;
			}
		}
		else
		{
			$codes = strtoupper($codes);
			if((!$this->isRegistered($code) || $force == true) && !$this->isOverride($code))
			{
				$this->registered_codes[$codes] = array('type' => 'func', 'path' => $path, 'function' => $classFunc);
			}
		}
		return $this;
	}
	
	/**
	 * Add value to already registered SC object
	 *
	 * @param string $className
	 * @param string $scVarName
	 * @param mixed $value
	 * @return e_parse_shortcode
	 */
	public function setScVar($className, $scVarName, $value)
	{
		if(isset($this->scClasses[$className]))
		{
			// new way - batch should extend e_shortcode class
			if(method_exists($this->scClasses[$className], 'setScVar'))
			{
				$this->scClasses[$className]->setScVar($scVarName, $value);
			}
			else // Old - DEPRECATED
			{
				$this->scClasses[$className]->$scVarName = $value;
			}
		}
		return $this;
	}
	
	/**
	 * Call function on an already registered SC object
	 *
	 * @param string $className
	 * @param string $scFuncName
	 * @param mixed $param - passed to function
	 *
	 * @return mixed|boolean - NULL if class doesn't exist; otherwise whatever the function returns.
	 */
	public function callScFunc($className, $scFuncName, $param = '')
	{
		if(isset($this->scClasses[$className]))
		{
			return call_user_func(array($this->scClasses[$className], $scFuncName), $param);
		}
		return null;
	}
	
	/**
	 * Create shortcode object - don't forget you still can use e_shortcode.php
	 *
	 * @param string $class
	 * @param boolean $force
	 * @return e_shortcode
	 */
	public function initShortcodeClass($class, $eVars = null, $force = false)
	{
		if(class_exists($class, false) && ($force || !isset($this->scClasses[$class])))
		{
			$this->scClasses[$class] = new $class($eVars);
			return $this->scClasses[$class];
		}
		return null;
	}
	
	/**
	 * Get registered SC object
	 *
	 * @param string $className
	 * @return e_shortcode
	 */
	public function getScObject($className)
	{
		if(isset($this->scClasses[$className]))
		{
			return call_user_func(array($this->scClasses[$className], $scFuncName), $param);
		}
		// TODO - throw exception?
		return null;
	}

	/**
	 * Register any shortcode from the override/shortcodes/ directory
	 *
	 * @return e_parse_shortcode
	 */
	protected function loadOverrideShortcodes()
	{
		if(e107::getPref('sc_override'))
		{
			$tmp = explode(',', e107::getPref('sc_override'));
			foreach($tmp as $code)
			{
				$code = strtoupper(trim($code));
				$this->registered_codes[$code]['type'] = 'override';
				$this->scOverride[] = $code;
			}
		}
		return $this;
	}

	/**
	 * Register any shortcodes that were registered by the theme
	 * $register_sc[] = 'MY_THEME_CODE'
	 *
	 * @return e_parse_shortcode
	 */
	protected function loadThemeShortcodes()
	{
		global $register_sc;

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
		return $this;
	}


	/**
	 * Register all .sc files found in plugin directories (via pref)
	 *
	 * @return e_parse_shortcode
	 */
	protected function loadPluginSCFiles()
	{
		$pref = e107::getPref('shortcode_list');

		if($pref)
		{
			foreach($pref as $path => $namearray)
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
							$this->registered_codes[$code]['perms'] = $uclass; // XXX how we get this?
						}
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Register Plugin Shortcode Batch files (e_shortcode.php) for use site-wide.
	 * Equivalent to multiple .sc files in the plugin's folder.
	 *
	 * @return e_parse_shortcode
	 */
	protected function loadPluginShortcodes()
	{
		$pref = e107::getPref('e_shortcode_list');

		if(!$pref)
		{
			return $this;
		}

		foreach($pref as $key=>$val)
		{
			$path = e_PLUGIN.$key.'/e_shortcode.php';
			$classFunc = $key.'_shortcodes';
			if(!include_once($path))
			{
				continue;
			}

			$this->registerClassMethods($classFunc, $path);
		}
		return $this;
	}

	/**
	 * Common Auto-Register function for class methods.
	 *
	 */
	protected function registerClassMethods($classFunc, $path)
	{
		//$this->scClasses[$classFunc] = new $classFunc;

		$tmp = get_class_methods($classFunc);
		foreach($tmp as $c)
		{
			if(strpos($c, 'sc_') === 0)
			{
				$sc_func = substr($c, 3);
				$code = strtoupper($sc_func);
				if(!$this->isRegistered($code))
				{
					$this->registered_codes[$code] = array('type' => 'class', 'path' => $path, 'class' => $classFunc);
				}
			}
		}
	}

	/**
	 * Register Core Shortcode Batches.
	 * FIXME - currently loaded all the time (even on front-end)
	 *
	 * @return void
	 */
	function loadCoreShortcodes()
	{
		$coreBatchList = array('admin_shortcodes');

		foreach($coreBatchList as $cb)
		{
			$path = e_CORE.'shortcodes/batch/'.$cb.".php";
			if(include_once($path))
			{
				$this->registerClassMethods($cb, $path);
			}
		}
	}

	function isRegistered($code)
	{
		return in_array($code, $this->registered_codes);
	}

	public function resetScClass($className, $object)
	{
		if($this->isScClass($className))
		{
			$this->scClasses[$className] = $object;
		}
	}

	function isScClass($className)
	{
		return isset($this->scClasses[$className]);
	}

	function isOverride($code)
	{
		return in_array($code, $this->scOverride);
	}

	function parseCodes($text, $useSCFiles = true, $extraCodes = '', $eVars = null)
	{
		$saveParseSCFiles = $this->parseSCFiles;		// In case of nested call
		$this->parseSCFiles = $useSCFiles;

		$this->eVars = null;
		if(is_object($eVars)) {
			$this->eVars = $eVars;
		}

		//object support
		if(is_object($extraCodes))
		{
			$classname = get_class($extraCodes);

			//register once
			if(!$this->isScClass($classname))
			{
				$this->registerShortcode($extraCodes, true);
			}

			//always overwrite object
			$this->scClasses[$classname] = $extraCodes;
		}
		elseif(is_array($extraCodes))
		{
			foreach($extraCodes as $sc => $code)
			{
				$this->scList[$sc] = $code;
			}
		}
		$ret = preg_replace_callback('#\{(\S[^\x02]*?\S)\}#', array(&$this, 'doCode'), $text);
		$this->parseSCFiles = $saveParseSCFiles;		// Restore previous value
		$this->eVars = null; // reset eVars
		return $ret;
	}

	function doCode($matches)
	{
		global $pref, $e107cache, $menu_pref, $sc_style, $parm, $sql;

		if($this->eVars) {
			if($this->eVars->$matches[1]) {
				return $this->eVars->$matches[1];
			}
		}
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
								$this->initShortcodeClass($_class, $this->eVars, false);
								//$this->scClasses[$_class] = new $_class;
								// make eVars available to the SC object members
								//$this->scClasses[$_class]->eVars = $this->eVars; 
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
							$scFile = e_CORE.'override/shortcodes/'.strtolower($code).'.sc';
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
					if(is_readable(e_CORE.'shortcodes/single/'.strtolower($code).'.php'))
					{
						$_function = strtolower($code).'_shortcode';
						$_class = strtolower($code);

						include_once(e_CORE.'shortcodes/single/'.strtolower($code).'.php');

						if(class_exists($_class, false)) // prevent __autoload - performance
						{
							$ret = call_user_func(array($_class,$_function), $parm);
						}
						elseif(function_exists($_function))
						{
							$ret = call_user_func($_function, $parm);
						}
					}
					else
					{
						$scFile = e_CORE.'shortcodes/single/'.strtolower($code).'.sc';
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
			  //	echo (isset($scFile)) ? "<br />sc_file= ".str_replace(e_CORE.'shortcodes/single/', '', $scFile).'<br />' : '';
			  //	echo "<br />sc= <b>$code</b>";
			}
		}

		if($scCode)
		{
			$ret = eval($scCode);
		}

		if(isset($ret) && ($ret != '' || is_numeric($ret)))
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
			$sql->db_Mark_Time("(SC {$code} Done)");
		}
		return isset($ret) ? $ret : '';
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
				if (preg_match('#^SC_BEGIN (\w*).*#', $line, $matches))
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

class e_shortcode
{
	/**
	 * Stores passed to shortcode handler simple parser object
	 * @var e_vars
	 */
	protected $eVars = null; 
	
	/**
	 * Storage for external values
	 * @var e_vars
	 */
	protected $scVars = null;
	
	public function __construct($eVars = null)
	{
		$this->eVars = $eVars;
		$this->scVars = new e_vars();
	}
	
	/**
	 * Add shortcode value
	 * <code>e107::getScObject('class_name')->setScVar('some_property', $some_value);</code>
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return e_parse_shortcode
	 */
	public function setScVar($name, $value)
	{
		$this->scVars->$name = $value;
		return $this;
	}
	
	/**
	 * Retrieve shortcode value
	 * code>$some_value = e107::getScObject('class_name')->getScVar('some_property');</code>
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getScVar($name)
	{
		return $this->scVars->$name;
	}
}