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

if (!defined('e107_INIT'))
{
	exit;
}

/**
 *
 * @package     e107
 * @subpackage	e107_handlers
 * @version     $Id$
 * @author      e107inc
 *
 * e_parse_shortcode - shortcode parser/manager, former e_shortcode
 * e_shortcode - abstract batch class
 */

	/**
	 * @deprecated
	 * @param $classFunc
	 * @param $codes
	 * @param string $path
	 * @param bool $force
	 * @return e_parse_shortcode
	 */
	function register_shortcode($classFunc, $codes, $path = '', $force = false)
	{
		return e107::getScParser()->registerShortcode($classFunc, $codes, $path, $force);
	}

	/**
	 *  @deprecated
	 * @param $className
	 * @param $scVarName
	 * @param $value
	 * @return e_parse_shortcode
	 */
	function setScVar($className, $scVarName, $value)
	{
		return e107::getScParser()->setScVar($className, $scVarName, $value);
	}

	/**
	 *  @deprecated FIXME: to be removed (once event calendar changed)
	 * @param $className
	 * @param $scFuncName
	 * @param string $param
	 * @return bool|mixed
	 */
	function callScFunc($className, $scFuncName, $param = '')
	{
		return e107::getScParser()->callScFunc($className, $scFuncName, $param);
	}

	/**
	 *  @deprecated FIXME: to be removed
	 * @param $class
	 * @param bool $force
	 * @param null $eVars
	 * @return e_shortcode
	 */
	function initShortcodeClass($class, $force = false, $eVars = null)
	{
		return e107::getScParser()->initShortcodeClass($class,  $force);
	}



class e_parse_shortcode
{
	protected $scList               = array();  // The actual code - added by parsing files or when plugin codes encountered. Array key is the shortcode name.
	protected $parseSCFiles         = true;     // True if individual shortcode files are to be used
	protected $addedCodes           = null;     // Pointer to a class or array to be used on a single call
	protected $registered_codes     = array();  // Shortcodes added by plugins TODO make it private
	protected $scClasses            = array();  // Batch shortcode classes - TODO make it private
	protected $scOverride           = array();  // Array of codes found in override/shortcodes dir
	protected $scBatchOverride      = array();  // Array of codes found in override/shortcodes/batch dir
	protected $ignoreCodes          = array();  // Shortcodes to be ignored and remain unchanged. (ie. {THEME}, {e_PLUGIN} etc. )
	protected $addonOverride        = array();  // Overrides coming from e_shortcode.php
	private   $legacyBatch          = array();  // List of legacy batch file codes. eg. using SC_BEGIN etc.
	private   $legacyBatchFile       = null;
	private   $debug_legacy         = array();

	/** @var e_vars  */
	protected $eVars                = null;
	protected $wrappers             = array();  // Wrappers array for the current parsing cycle, see contact_template.php and $CONTACT_WRAPPER variable
	protected $wrapper              = null;     // current wrapper being processed.
	protected $wrapperDebugDone     = array();  // Flag to avoid repetition of debug info.
	protected $sc_style             = array();  // Former $sc_style global variable. Internally used - performance reasons
	protected $editableCodes        = array(); // Array of editable shortcode data.
	protected $editableActive       = false;
	protected $nowrap               = false;

	function __construct()
	{
		// $_SESSION['editable'] = array();

		$this->ignoreCodes = e107::getParser()->getUrlConstants(); // ignore all URL shortcodes. ie. {e_PLUGIN}

		if(e_ADMIN_AREA !== true)
		{
			$this->loadOverrideShortcodes();
			$this->loadThemeShortcodes();
		}

		$this->loadPluginShortcodes();
		$this->loadPluginSCFiles();

		
		//$this->loadCoreShortcodes(); DEPRECATED

		$editableActivePref = e107::getPref('inline_editing',255);

		if(check_class($editableActivePref))
		{
			$this->editableActive = true;
		}

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
		if ($codes === true)
		{
			$codes = array();
			$tmp = get_class_methods($classFunc);
			foreach ($tmp as $c)
			{
				if (strpos($c, 'sc_') === 0)
				{
					$codes[] = substr($c, 3);
				}
			}
			unset($tmp);
		}

		//Register object feature
		$classObj = null;
		if (is_object($classFunc))
		{
			$classObj = $classFunc;
			$classFunc = get_class($classObj);

		}

		//We only register these shortcodes if they have not already been registered in some manner
		//ie theme or other plugin .sc files
		if (is_array($codes))
		{
			foreach ($codes as $code)
			{
				$code = strtoupper($code);
				if ((!$this->isRegistered($code) || $force == true) && !$this->isOverride($code))
				{
					$this->registered_codes[$code] = array('type' => 'class', 'path' => $path, 'class' => $classFunc);
				}
			}

			//register object if required
			if (null !== $classObj && (!$this->isScClass($classFunc) || $force == true))
			{
				$this->scClasses[$classFunc] = $classObj;
			}
		}
		else
		{
			$codes = strtoupper($codes);
			if ((!$this->isRegistered($codes) || $force == true) && !$this->isOverride($codes))
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
		if ($this->isScClass($className))
		{
			// new way - batch should extend e_shortcode class
			if (method_exists($this->scClasses[$className], 'setScVar'))
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
	 * @return mixed|boolean - NULL if class/method doesn't exist; otherwise whatever the function returns.
	 */
	public function callScFunc($className, $scFuncName, $param = '')
	{
		if ($this->isScClass($className))
		{
			return method_exists($this->scClasses[$className], $scFuncName) ? call_user_func(array($this->scClasses[$className], $scFuncName), $param) : null;
		}
		return null;
	}

	/**
	 * same as e_parse_shortcode::callScFunc(), but passes the last argument (array)
	 * to the called method as multiple arguments
	 *
	 * @param string $className
	 * @param string $scFuncName
	 * @param array $args
	 * @return bool|mixed - NULL if class/method doesn't exist; otherwise whatever the function returns.
	 * @internal param array $param - arguments passed to function
	 *
	 */
	protected function callScFuncA($className, $scFuncName, $args = array())
	{
		if ($this->isScClass($className))
		{
			// avoid warnings
			return method_exists($this->scClasses[$className], $scFuncName) ? call_user_func_array(array($this->scClasses[$className], $scFuncName), $args) : null;
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
	public function initShortcodeClass($class, $force = false)
	{
		if (class_exists($class, false) && ($force || !$this->isScClass($class)))
		{
			$this->scClasses[$class] = new $class();

			if(method_exists($this->scClasses[$class], 'init'))
			{
				$this->scClasses[$class]->init();
			}

			if(!empty($this->scClasses[$class]->override))
			{
				$methods = get_class_methods($class);
				foreach($methods as $meth)
				{
					if(substr($meth,0,3) == 'sc_')
					{
						$this->addonOverride[$meth] = $class;


					}
				}
			}

			return $this->scClasses[$class];
		}

		return null;
	}

	/**
	 * Get registered SC object
	 * Normally you would use the proxy of this method - e107::getScBatch()
	 * Global File Override ClassName/Path examples:
	 * 1. Core signup shortcodes
	 *    - Origin ClassName: signup_shortcodes
	 *    - Origin Location: core/shortcodes/batch/signup_shortcodes.php
	 *    - File Override ClassName: override_signup_shortcodes
	 *    - File Override Location: core/override/shortcodes/batch/signup_shortcodes.php
	 *
	 * 2. Plugin 'gallery' global shortcode batch (e_shortcode.php)
	 *    - Origin ClassName: gallery_shortcodes //FIXME Should be gallery_shortcode? (more below)
	 *    - Origin Location: plugins/gallery/e_shortcode.php
	 *    - File Override ClassName: override_gallery_shortcodes
	 *    - File Override Location: core/override/shortcodes/batch/gallery_shortcodes.php
	 *
	 * 3. Plugin 'forum' regular shortcode batch
	 *    - Origin ClassName: plugin_forum_view_shortcodes //FIXME Should be forum_shortcodes? (more below)
	 *    - Origin Location: plugins/forum/shortcodes/batch/view_shortcodes.php
	 *    - File Override ClassName: override_plugin_forum_view_shortcodes
	 *    - File Override Location: core/override/shortcodes/batch/forum_view_shortcodes.php
	 *
	 * <code><?php
	 * // simple use
	 * e107::getScParser()->getScObject('news_shortcodes'); // For Globally Registered shortcodes, including plugins using e_shortcode.php
	 *
	 * // plugin override - e107_plugins/myplug/shortcodes/batch/news_shortcodes.php -> class plugin_myplug_news_shortcodes
	 * e107::getScParser()->getScObject('news_shortcodes', 'myplug', true);
	 *
	 * // more complex plugin override
	 * // e107_plugins/myplug/shortcodes/batch/news2_shortcodes.php -> class plugin_myplug_news2_shortcodes
	 * e107::getScParser()->getScObject('news_shortcodes', 'myplug', 'news2_shortcodes');
	 * </code>
	 * @param string $className
	 * @param null $pluginName
	 * @param string $overrideClass if true, $className is used
	 * @return e_shortcode
	 * @internal param string $plugName if true className is used., if string, string value is used.
	 */
	public function getScObject($className, $pluginName = null, $overrideClass = null)
	{
		
		/* FIXME Discuss Generic plugin Class naming. (excluding specific calls with $overrideClass. 
		// Defaults should be: 
			e_shortcode.php 			 = {plugin}_shortcode 
		  	{plugin}_shortcodes.php 	= {plugin}_shortcodes
		*/

		$path = null;
		$manualCall = false;
		
		if(trim($className)==""){ return null; }

		$_class_fname = $className;
		if($pluginName === true) //XXX When called manually by a plugin, not e_shortcode.php  eg. $sc = e107::getScBatch('faqs',TRUE); for faqs_shortcode.php with class faqs_shortcode
		{
			// @todo FAQs is incorrect and should use plugin_ as a prefix.
			$pluginName = str_replace("_shortcodes","",$className);	
			$manualCall = true; 	
		}
		elseif(is_string($pluginName)) //@fixme - stuck with it.
		{
			$className = 'plugin_'.$pluginName.'_'.str_replace('/', '_', $className);
		}

		$globalOverride = $this->isBatchOverride(str_replace('plugin_', '', $className));
		
		// forced override
		if($overrideClass)
		{
			if(true === $overrideClass)
			{
				$overrideClass = $className;
			}
			// e.g. class plugin_myplug_news_shortcodes
			
			if($pluginName != null)
			{
				$_class_fname = $overrideClass;
				$className = 'plugin_'.$pluginName.'_'.str_replace('/', '_', $overrideClass);
			}
			else
			{
				$className = $overrideClass;	
			}
		}
		
		
		if($className == '_theme__shortcodes') // Check for theme shortcode batch.  - @see header_default.php //XXX Discuss. 
		{
			$className = 'theme_shortcodes';
			$path = THEME.'theme_shortcodes.php';			
		}
		elseif(!$pluginName)
		{
			if(!$globalOverride)
			{
				$path = e_CORE.'shortcodes/batch/'.$_class_fname.'.php';
			}
			else 
			{
				$path = e_CORE.'override/shortcodes/batch/'.$_class_fname.'.php';
				$className = 'override_'.$className;
			}
		}
		else
		{

			if(!$globalOverride)
			{				
				// do nothing if it's e_shortcode batch global
				if($pluginName.'_shortcodes' !== $className || $manualCall == true) // manual call by plugin, not e_shortcode.php
				{			
					// BC  - required. 
					$pathBC = e_PLUGIN.$pluginName.'/';
					$path = (is_readable($pathBC.$_class_fname.'.php') ? $pathBC : e_PLUGIN.$pluginName.'/shortcodes/batch/').$_class_fname.'.php';
				}
			}
			else 
			{
				$path = e_CORE.'override/shortcodes/batch/'.$pluginName.'_'.$_class_fname.'.php';
				$className = 'override_'.$className;
			}
		}

		


		
		// Includes global Shortcode Classes (e_shortcode.php) or already loaded batch 
		if ($this->isScClass($className)) 
		{
			return $this->scClasses[$className];
		}
		
		// If it already exists - don't include it again. 
		if (class_exists($className, false)) // don't allow __autoload()
		{
			// $this->registerClassMethods($className, $path); // XXX Global registration should happen separately - here we want only the object. 
			 $this->scClasses[$className] = new $className(); 
			return $this->scClasses[$className];
		}
		
		if (is_readable($path))
		{
			require_once($path);
			//e107::getDebug()->log("Loading Class '".$className."' in <b>".$path."</b>");

			if (class_exists($className, false)) // don't allow __autoload()
			{
				// register instance directly to allow override
				 $this->scClasses[$className] = new $className(); 
				// $this->registerClassMethods($className, $path);  // XXX Global registration should happen separately - here we want only the object. 
				return $this->scClasses[$className];
			}
			elseif(class_exists("plugin_".$className,false)) // v2.1.7 = Fix path issues. getScObject('myplugin',true) should load: plugin_myplugin_shortcodes class.
			{
				$className = "plugin_".$className;
				$this->scClasses[$className] = new $className();
				return $this->scClasses[$className];
			}
			elseif(E107_DBG_BBSC || E107_DBG_SC)
			{
			// 	echo "<h3>Couldn't Find Class '".$className."' in <b>".$path."</b></h3>";
				echo "<div class='alert alert-danger'>Couldn't Load: <b>".$path."</b> with class-name:<b> {$className}</b> and pluginName <b>{$pluginName}</b></div>";
			}
			else
			{
				e107::getDebug()->log("Couldn't Find Class '".$className."' OR 'plugin_".$className."'in <b>".$path."</b>");
			}
		}
		else
		{
			e107::getDebug()->log("File not available: <i>".$path."</i>. Couldn't Find Class '".$className."' in <b>".$path."</b>");
		}

	//	e107::getDebug()->log( "<div class='alert alert-danger'>Couldn't Load: <b>".$path."</b> with class-name:<b> {$className}</b> and pluginName <b>{$pluginName}</b></div>");


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
		if (e107::getPref('sc_override'))
		{
			$tmp = explode(',', e107::getPref('sc_override'));
			foreach ($tmp as $code)
			{
				$code = strtoupper(trim($code));
				$this->registered_codes[$code]['type'] = 'override';
				$this->registered_codes[$code]['path'] = e_CORE.'override/shortcodes/single/';
				$this->registered_codes[$code]['function'] = 'override_'.strtolower($code).'_shortcode'; 
				$this->scOverride[] = $code;
			}
		}
		if (e107::getPref('sc_batch_override'))
		{
			$tmp = explode(',', e107::getPref('sc_batch_override'));
			foreach ($tmp as $code)
			{
				//$code = strtoupper(trim($code));
				//$this->registered_codes[$code]['type'] = 'override';
				$this->scBatchOverride[] = $code;
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
	public function loadThemeShortcodes($theme=null)
	{
		global $register_sc;

		$themePath = ($theme === null) ? THEME : e_THEME.$theme.'/';

		if(file_exists($themePath."theme_shortcodes.php"))
		{
			$classFunc = 'theme_shortcodes';
			$path = $themePath."theme_shortcodes.php";
			include_once($path);
			$this->registerClassMethods($classFunc, $path, false);
		}

	
		if (isset($register_sc) && is_array($register_sc)) // legacy load.
		{
			foreach ($register_sc as $code)
			{
				if (!$this->isRegistered($code))
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
		$prefl = e107::getPref('shortcode_legacy_list');

		// new shortcodes - functions, shortcode/single/*.php
		if ($pref)
		{
			foreach ($pref as $path => $namearray)
			{
				foreach ($namearray as $code => $uclass)
				{
					$code = strtoupper($code);
					if (!$this->isRegistered($code))
					{
						if($this->isOverride($code))
						{
							$this->registered_codes[$code]['type'] = 'override';
							$this->registered_codes[$code]['function'] = 'override_'.strtolower($code).'_shortcode';
							$this->registered_codes[$code]['path'] = e_CORE.'override/shortcodes/single/';
							$this->registered_codes[$code]['perms'] = $uclass; 
							continue;
						}
						$this->registered_codes[$code]['type'] = 'plugin';
						$this->registered_codes[$code]['function'] = strtolower($code).'_shortcode';
						$this->registered_codes[$code]['path'] = e_PLUGIN.$path.'/shortcodes/single/';
						$this->registered_codes[$code]['perms'] = $uclass; 
					}
				}
			}
		}

		// legacy .sc - plugin root
		if ($prefl)
		{
			foreach ($prefl as $path => $namearray)
			{
				foreach ($namearray as $code => $uclass)
				{
					// XXX old? investigate
					if ($code == 'shortcode_config')
					{
						include_once(e_PLUGIN.$path.'/shortcode_config.php');
					}
					else
					{
						$code = strtoupper($code); 
						if (!$this->isRegistered($code))
						{
							$this->registered_codes[$code]['type'] = 'plugin_legacy';
							$this->registered_codes[$code]['path'] = $path;
							$this->registered_codes[$code]['perms'] = $uclass; 
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

		if (!$pref)
		{
			return $this;
		}
		
		foreach ($pref as $key => $val)
		{
			$globalOverride = $this->isBatchOverride($key.'_shortcodes');
			if($globalOverride)
			{
				$path = e_CORE.'override/shortcodes/batch/'.$key.'_shortcodes.php';
				$classFunc = 'override_'.$key.'_shortcodes';
			}
			else
			{
				$path = e_PLUGIN.$key.'/e_shortcode.php';
				$classFunc = $key.'_shortcodes';
			}
			
			if (!include_once($path))
			{
				// try to switch back to the batch origin in case it's an override
				if($globalOverride)
				{
					$path = e_PLUGIN.$key.'/e_shortcode.php';
					$classFunc = $key.'_shortcodes';
					if (!include_once($path))
					{
						continue;
					}
				}
				else continue;
			}
			
			$this->registerClassMethods($classFunc, $path, false);
		}
		return $this;
	}

	/**
	 * Common Auto-Register function for class methods.
	 * @param $class
	 * @param $path
	 * @param bool $force
	 * @return e_parse_shortcode
	 */
	protected function registerClassMethods($class, $path, $force = false)
	{
		$tmp = get_class_methods($class);
		$className = is_object($class) ? get_class($class) : $class;

		foreach ($tmp as $c)
		{
			if (strpos($c, 'sc_') === 0)
			{
				$sc_func = substr($c, 3);
				$code = strtoupper($sc_func);
				if ($force || !$this->isRegistered($code))
				{
					$this->registered_codes[$code] = array('type' => 'class', 'path' => $path, 'class' => $className);
					$this->initShortcodeClass($className);
				//	if (class_exists($className, false))
				//	{
					//	$this->scClasses[$className] = new $className(); // Required. Test with e107::getScBatch($className)	
					//	echo "CLASS=:".$className;			
				//	}
				}
			}
		}
		

		return $this;
	}



	function isRegistered($code)
	{
		return array_key_exists($code, $this->registered_codes);
	}



	public function resetScClass($className, $object)
	{
		if(null === $object)
		{
			unset($this->scClasses[$className]);
		}
		elseif ($this->isScClass($className))
		{
			$this->scClasses[$className] = $object;
		}
		return $this;
	}

	function isScClass($className)
	{
		return isset($this->scClasses[$className]);
	}

	function isOverride($code)
	{
		return in_array($code, $this->scOverride);
	}

	function isBatchOverride($name)
	{
		return in_array($name, $this->scBatchOverride);
	}

	/**
	 *	Parse the shortcodes in some text
	 *
	 *	@param string $text - the text containing the shortcodes
	 *	@param boolean $useSCFiles - if TRUE, all currently registered shortcodes can be used.
	 *								- if FALSE, only those passed are used.
	 *	@param array|object|null $extraCodes - if passed, defines additional shortcodes:
	 *			- if an object or an array, the shortcodes defined by the class of the object are available for this parsing only.
	 *	@param array|null $eVars - if defined, details values to be substituted for shortcodes. Array key (lower case) is shortcode name (upper case)
	 *
	 *	@return string with shortcodes substituted
	 */
	function parseCodes($text, $useSCFiles = true, $extraCodes = null, $eVars = null)
	{
		global $sc_style; //legacy, will be removed soon, use the non-global $SC_STYLE instead
		
		$saveParseSCFiles = $this->parseSCFiles; // In case of nested call
		$this->parseSCFiles = $useSCFiles;
		$saveVars = $this->eVars; // In case of nested call
		$saveCodes = $this->addedCodes;
		$this->eVars = $eVars;
		$this->addedCodes = NULL;
		
		// former $sc_style - do it once here and not on every doCode loop - performance

		$this->sc_style = e107::scStyle(); 	//FIXME - BC Problems and conflicts.

		/* --------- BUG TEST Scenario -------------- 
		 * Front-end Theme: Bootstrap
		 * MENU-1 contains '{NAVIGATION=side}' on top and chatbox_menu below
		 * URL to use: /usersettings.php - 'Signature' input should be enabled. 
		 * Expected Result: 
		 * 		1) {NAVIGATION=side} wrapped with $SC_WRAPPER ie. enclosed in box. 
		 * 		2) Internal styling of chatbox_class not to be damaged by what happens globally ie. the text 'Display name' should not appear in the chatbox
		 * 		3) Usersettings Signature box to appear wrapped in BC $sc_style pre/post - ie. should appear at the bottom of the html table.(not at the top) 
		 * 		4) Existing Chatbox Styling (v1.x) not broken (ie. test with v1 theme). 		
		 *  	- All of the above to occur without changes to usersetting_template.php - since its logic is that of v1.x templates. 
		 * 
		 * Things that may help: 
		 * Modify e107::getScBatch() so that it never registers shortcodes globally;  ie. no overriding of existing shortcodes with it, as it is a replacement for non-global shortcode declaration in v1 
		 * ONLY globally register shortcodes when they are declared in e_shortcode.php - this is consistent with the logic of e_xxxx which affect e107 Outside of the plugin/sript. (gallery plugin follows this logic)
		 * 
		 */

		if(isset($sc_style) && is_array($sc_style))
		{
			$this->sc_style = array_merge($sc_style, $this->sc_style); // XXX Commenting this out will fix #2 above. 
		}

		//object support
		
		if (is_object($extraCodes))
		{

			$this->addedCodes = &$extraCodes;

		//	e107::getDebug()->log("Codes".print_a($this->addedCodes,true));
			
			// TEMPLATEID_WRAPPER support - see contact template
			// must be registered in e_shortcode object (batch) via () method before parsing
			// Do it only once per parsing cylcle and not on every doCode() loop - performance
			if(method_exists($this->addedCodes, 'wrapper'))
			{
				// $cname = get_class($this->addedCodes);

				$tmpWrap = e107::templateWrapper($this->addedCodes->wrapper());
				if(!empty($tmpWrap)) // FIX for #3 above.
				{
					$this->wrappers = array_merge($this->wrappers,$tmpWrap);
					$this->wrapper = $this->addedCodes->getWrapperID();

				}
				elseif(E107_DBG_BBSC)
				{
					$this->wrapper = $this->addedCodes->getWrapperID();
				//	e107::getMessage()->addDebug("Wrapper Empty: ".$this->addedCodes->wrapper());
				}

			}

			if(method_exists($this->addedCodes, 'editable'))
			{
				$this->editableCodes = $this->addedCodes->editable();

				if(isset($this->editableCodes['perms']) && getperms($this->editableCodes['perms']))
				{
					// TODO use Library Manager...
					e107::js('footer', '{e_WEB}js/jquery.contenteditable.js', 'jquery', 2);

					$_SESSION['editable'][e_TOKEN] = $this->editableCodes;

					e107::js('footer-inline', '$(".e-editable-front").each(function ()
					{

						var sc   = $(this).attr("data-edit-sc");
						var id      = $(this).attr("data-edit-id");
						var token    = "'.e_TOKEN.'";
						var box     = $(this).parent("div, span"); 
						var container = this; 

						$(this).contentEditable({
							"placeholder" : "",
							
							  "onFocusIn" : function(element){
							    var $input = $("<span id=\"e-editable-front-controls\"><span class=\"e-editable-front-save\" ><i class=\"fa fa-fw fa-save\"></i></span><span class=\"e-editable-front-cancel\" ><i class=\"fa fa-fw fa-ban\"></i></span></span>"); 
							   $input.appendTo($(box)).hide().fadeIn(300);
								$(container).addClass("active");
				             
				            },
							"onFocusOut" : function(element){
				            //   $(".e-editable-front-save").remove();
				            }
				            
				          
						});
						
						
						$(box).on("click",".e-editable-front-cancel",function () 
						{
					        console.log("Cancelled");
					        $(container).removeClass("active");
					        $("#e-editable-front-controls").fadeOut(300, function() { $("#e-editable-front-controls").remove(); });	 
						}); 
						
						$(box).on("click",".e-editable-front-save",function () 
						{
							$("#e-editable-front-controls").html("<i class=\"fa fa-fw fa-spin fa-spinner\"></i>");
						
					        $(container).removeClass("active");
					        
					        var edited_content = $(container).html();

							$.post("'.e_WEB_ABS.'js/inline.php",{ content : edited_content, sc: sc, id: id, token: token }, function (data)
							{
								console.log(data);
								try
								{
									var d = $.parseJSON(data);
								} 
								catch(e)
								{
									// Not JSON.
								//	return;
								}

								console.log(d);
									 
								if(d.msg)
								{
	
									if(d.status == "ok")
									{
										$("#e-editable-front-controls").html("<i class=\"fa fa-fw fa-check\"></i>");	
									}
											
									if(d.status == "error")
									{
										$("#e-editable-front-controls").html("<i class=\"fa fa-fw fa-cross\"></i>");	
									}			
										
								}	
								else
								{
									$("#e-editable-front-controls").html("<i class=\"fa fa-fw fa-cross\"></i>");	
								}
								
								$("#e-editable-front-controls").fadeOut(2000, function() { $(this).remove(); });	 
	
							}) 
					        
						}); 
										
						

					});
					
				




					');
				}
			}


			/*
			$classname = get_class($extraCodes);

			//register once
			if (!$this->isScClass($classname))
			{
				$this->registerShortcode($extraCodes, true);		// Register class if not already registered
			}

			//always overwrite object
			$this->scClasses[$classname] = $extraCodes;
			*/

			// auto-register eVars if possible - call it manually?
			// $this->callScFunc($classname, 'setParserVars', $this->eVars);
		}
		elseif (is_array($extraCodes)) // Array value contains the contents of a .sc file which is then parsed. ie. return " whatever "; 
		{
			$this->addedCodes = &$extraCodes;

			if(isset($extraCodes['_WRAPPER_']))
			{
				$tmpWrap = e107::templateWrapper($extraCodes['_WRAPPER_']);
				$this->wrappers = array_merge($this->wrappers,$tmpWrap);
				$this->wrapper = $extraCodes['_WRAPPER_'];
				unset($extraCodes['_WRAPPER_']);
			}

			/*
			foreach ($extraCodes as $sc => $code)
			{
				$this->scList[$sc] = $code;
			}
			*/
			
		//	print_a($this);
		}


		$ret = preg_replace_callback('#\{([A-Z][^\x02]*?\S)\}#', array(&$this, 'doCode'), $text); // must always start with uppercase letter
		// $ret = preg_replace_callback('#\{(\S[^\x02]*?\S)\}#', array(&$this, 'doCode'), $text);
		$this->parseSCFiles = $saveParseSCFiles; // Restore previous value
		$this->addedCodes = $saveCodes;
		$this->eVars = $saveVars; // restore eVars
		$this->debug_legacy = null;
	

			//	$this->sc_style = array();	 //XXX Adding this will also fix #2 above. 

		
		return $ret;
	}


	/**
	 * Callback looks up and substitutes a shortcode
	 * @param $matches
	 * @return bool|int|mixed|string
	 */
	function doCode($matches)
	{
		// print_a($matches);

		if(in_array($matches[0],$this->ignoreCodes)) // Ignore all {e_PLUGIN}, {THEME} etc. otherwise it will just return blank for these items. 
		{
			return $matches[0];	
		}

		// XXX remove all globals, $sc_style removed
		global $pref, $e107cache, $menu_pref, $parm, $sql;
		
		$parmArray          = false;
		$fullShortcodeKey   = null;
		$noDebugLog            = false;

		if ($this->eVars)
		{
			if ($this->eVars->isVar($matches[1]))
			{
				$match1 = $matches[1]; // php7 fix.
			//	e107::getDebug()->log("Using eVars ".$match1);
				return $this->eVars->$match1;
			}
		}
		if (strpos($matches[1], E_NL) !== false)
		{
			return $matches[0];
		}


		if(preg_match('/^([A-Z_]*\d?):(.*)/', $matches[1], $newMatch))
		{
			$fullShortcodeKey = $newMatch[0];
			$code = $newMatch[1];
			$parmStr = trim($newMatch[2]);
			// fix for #3161: htmlized shortcode parameters ...
			$parmStr = str_ireplace('&amp;', '&', $parmStr);
			$debugParm = $parmStr;
			parse_str($parmStr,$parm);
			$parmArray = true;
		}
		elseif (strpos($matches[1], '='))
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
		
		if($parmArray == false)
		{
			$parm = trim($parm);
			$parm = str_replace(array('[[', ']]'), array('{', '}'), $parm);
		}

		if(empty($parm))
		{
			$parm = array();
		}

		
		if (E107_DBG_BBSC || E107_DBG_SC || E107_DBG_TIMEDETAILS)
		{
			$sql->db_Mark_Time("SC ".$code);
		}

		if (E107_DBG_SC)
		{
			
			$dbg = "<strong>";
			$dbg .= '{';
			$dbg .= $code;
			$dbg .=($parm) ? '='.htmlentities($parm) : "";
			$dbg .= '}';
			$dbg .= "</strong>";
		//	echo $dbg;
			return $dbg;
			//	trigger_error('starting shortcode {'.$code.'}', E_USER_ERROR);    // no longer useful - use ?[debug=bbsc]
		}

		$scCode = '';
		$scFile = '';
		$_path = '';
		$ret = '';
		$_method = 'sc_'.strtolower($code);


		// Display e_shortcode.php override info.
		if((E107_DBG_BBSC || E107_DBG_SC) && isset($this->addonOverride[$_method]) && is_object($this->addedCodes) && method_exists($this->addedCodes, $_method))
		{
			$debugArr = array('class_original'=>get_class($this->addedCodes), 'class_override'=>$this->addonOverride[$_method], 'function'=>$_method);
			e107::getDebug()->logCode(4, $code, null, print_a($debugArr,true));
			$noDebugLog = true;
		}


		if (!isset($this->addonOverride[$_method]) && is_object($this->addedCodes) && method_exists($this->addedCodes, $_method)) //It is class-based batch shortcode.  Class already loaded; call the method
		{
			
			$ret = $this->addedCodes->$_method($parm, $sc_mode);

			if(E107_DBG_BBSC || E107_DBG_SC || E107_DBG_TIMEDETAILS)
			{
				$_class = get_class($this->addedCodes); // "(class loaded)"; // debug. 
				$_function = $_method;
				$_path = "(already loaded)";

			}
		}
		elseif(is_array($this->addedCodes) && array_key_exists($code, $this->addedCodes)) // Its array-based shortcode. Load the code for evaluation later.
		{

		//	$ret = $this->addedCodes[$code];

		//$this->legacyBatch
			if(in_array($code,$this->legacyBatch))
			{
				$_type = 'legacy batch';
				$_path = $this->legacyBatchFile;
				$scCode =  $this->addedCodes[$code];
			}
			else
			{
				$_type = 'array';
				$_path = "(direct to parser)";
				$ret = $this->addedCodes[$code];

			}



		}
		elseif (array_key_exists($code, $this->scList)) // Check to see if we've already loaded the .sc file contents
		{
			
			$scCode = $this->scList[$code];
			$_path = "(loaded earlier)"; // debug. 
		}
		else
		{
			//.sc file not yet loaded, or shortcode is new function type
			if ($this->parseSCFiles == true)
			{
				
				if (array_key_exists($code, $this->registered_codes))
				{
					//shortcode is registered, let's proceed.
					if (isset($this->registered_codes[$code]['perms']))
					{
						if (!check_class($this->registered_codes[$code]['perms']))
						{
							return '';
						}
					}

					switch ($this->registered_codes[$code]['type'])
					{
						case 'class':
							//It is batch shortcode.  Load the class and call the method
							$_class 	= $this->registered_codes[$code]['class'];
							$_method 	= 'sc_'.strtolower($code);

							if (!$this->isScClass($_class))
							{
								if (!class_exists($_class) && $this->registered_codes[$code]['path'])
								{
									include_once($this->registered_codes[$code]['path']);
								}
								$this->initShortcodeClass($_class, false);
								if(!$this->isScClass($_class))
								{
									return '';
								}

								// egister passed eVars object on init - call it manually?
								// $this->callScFunc($_class, 'setVars', $this->var);
							}

							// FIXME - register passed eVars object - BAD solution - called on EVERY sc method call
							// XXX - removal candidate - I really think it should be done manually (outside the parser)
							// via e107::getScBatch(name)->setParserVars($eVars);
							// $this->callScFunc($_class, 'setParserVars', $this->eVars);
							$wrapper = $this->callScFunc($_class, 'wrapper', null);

							$ret = $this->callScFuncA($_class, $_method, array($parm, $sc_mode));
							
							/*if (method_exists($this->scClasses[$_class], $_method))
							{
								$ret = $this->scClasses[$_class]->$_method($parm, $sc_mode);
							}
							else
							{
								echo $_class.'::'.$_method.' NOT FOUND!<br />';
							}*/

							break;
						
						case 'override':
						case 'func':
						case 'plugin':
							//It is a function, so include the file and call the function
							$_function = $this->registered_codes[$code]['function'];
							if (!function_exists($_function) && $this->registered_codes[$code]['path'])
							{
								include_once($this->registered_codes[$code]['path'].strtolower($code).'.php');

							}
							
							if (function_exists($_function))
							{
								$ret = call_user_func($_function, $parm, $sc_mode);
							}
							break;

						case 'plugin_legacy':
							$scFile = e_PLUGIN.strtolower($this->registered_codes[$code]['path']).'/'.strtolower($code).'.sc';
							break;

						// case 'override':
							// $scFile = e_CORE.'override/shortcodes/'.strtolower($code).'.sc';
							// break;

						case 'theme':
							$scFile = THEME.strtolower($code).'.sc';
							break;

					}
				}
				else
				{
					// Code is not registered, let's look for .sc or .php file
					// .php file takes precedence over .sc file
					if (is_readable(e_CORE.'shortcodes/single/'.strtolower($code).'.php'))
					{
						$_function = strtolower($code).'_shortcode';
						$_class = strtolower($code);
						$_path = e_CORE.'shortcodes/single/'.strtolower($code).'.php';

						include_once(e_CORE.'shortcodes/single/'.strtolower($code).'.php');

						if (class_exists($_class, false)) // prevent __autoload - performance
						{
							// SecretR - fix array(parm, sc_mode) causing parm to become an array, see issue 424
							$ret = call_user_func(array($_class, $_function), $parm, $sc_mode);
						}
						elseif (function_exists($_function))
						{
							$ret = call_user_func($_function, $parm, $sc_mode);
						}
					}
					else
					{
						$scFile = e_CORE.'shortcodes/single/'.strtolower($code).'.sc';
						$_path = $scFile;
					}
				}

				if(!empty($scFile))
				{
					$_type = 'file';

					if(file_exists($scFile))
					{
						$scCode = file_get_contents($scFile);
						$this->scList[$code] = $scCode;
						$_path = $scFile;

					}
					else
					{
						$_path .= $scFile." MISSING!";
					}
				}

			}

			if (!isset($scCode))
			{
				if (E107_DBG_BBSC)
				{
					trigger_error('shortcode not found:{'.$code.'}', E_USER_ERROR);
				}
				return $matches[0];
			}

		//	if (E107_DBG_SC && $scFile)
		//	{
				//	echo (isset($scFile)) ? "<br />sc_file= ".str_replace(e_CORE.'shortcodes/single/', '', $scFile).'<br />' : '';
				//	echo "<br />sc= <b>$code</b>";
		//	}
		}

		if ($scCode) // legacy shortode to be evaluated.
		{

			try
			{
			    $ret = @eval($scCode);
			}
			catch (Throwable $t) {    // Executed only in PHP 7, will not match in PHP 5.x

				$error              = $this->debug_legacy;
				$error['code']      = $code;
				$error['problem']   = $scCode;

                e107::getDebug()->logCode(-2, $code, null, print_a($error,true));

			}

			if($ret === false && E107_DEBUG_LEVEL > 0 ) // Error in Code.
			{

				$error              = $this->debug_legacy;
				$error['code']      = $code;
				$error['problem']   = $scCode;
				$error['error']     = error_get_last();

                e107::getDebug()->logCode(-2, $code, null, print_a($error,true));

			}
		}

		if(E107_DBG_BBSC && $this->wrapperDebugDone[$this->wrapper]==false && !empty($this->wrapper))
		{
			list($wrapTmpl, $wrapID1, $wrapID2) = explode('/',$this->wrapper,3);

			$wrapActive = strtoupper($wrapTmpl)."_WRAPPER";

			if(!empty($wrapID1))
			{
				$wrapActive .= "['".$wrapID1."']";
			}

			if(!empty($wrapID2))
			{
				$wrapActive .= "['".$wrapID2."']";
			}

		//	e107::getMessage()->addDebug("Active Wrapper: \$".$wrapActive);
			$this->wrapperDebugDone[$this->wrapper] = true;
			global $db_debug;

			$db_debug->logCode(3, $this->wrapper,null,  '<pre>To use, add to the file '.$wrapTmpl.'_template.php:<br />$'.$wrapActive.'[\'SHORTCODE_NAME\'] = "(html before){---}(html after)";</pre>');
		}

		if (isset($ret) && ($ret != '' || is_numeric($ret)))
		{
			$ret = $this->makeEditable($ret, $code);
			if (!$this->nowrap == $code)
			{
				$ret = $this->makeWrapper($ret, $code, $fullShortcodeKey, $sc_mode);
			}
		}


		if (E107_DBG_SC || E107_DBG_TIMEDETAILS)
		{
		//	$sql->db_Mark_Time("(After SC {$code})");
		}
		
		if (($noDebugLog != true) && (E107_DBG_BBSC || E107_DBG_SC || E107_DBG_TIMEDETAILS))
		{
			global $db_debug;
			
			$other = array();

			if(!empty($_type))
			{
				$other['type'] = $_type;
			}
			
			if(!empty($_class))
			{
				$other['class'] = $_class;
			}
			if(!empty($_function))
			{
				$other['function'] = $_function;	
			}
			if(!empty($_path))
			{
				$other['path'] = str_replace('../','',$_path);		
			}

			if($this->debug_legacy)
			{
				$other = $this->debug_legacy;
			}

			if(!empty($this->wrappers[$code]))
			{
				$other['wrapper'] = str_replace(array("\n","\t")," ",$this->wrappers[$code]);
			}
			elseif(!empty($this->wrappers[$fullShortcodeKey]) )
			{
				$other['wrapper'] = $this->wrappers[$fullShortcodeKey];
			}


			$info = (isset($this->registered_codes[$code])) ? print_a($this->registered_codes[$code],true) : print_a($other,true);
			
			$tmp = isset($debugParm) ? $debugParm : $parm;

			$db_debug->logCode(2, $code, $tmp, $info);


			
		}




		return isset($ret) ? $ret: '';

	}


	/**
	 * Add Wrapper to Shortcode (when detected)
	 * @param mixed $ret
	 * @param string $code
	 * @param string $fullShortcodeKey
	 * @param string $sc_mode
	 * // Wrapper support - see contact_template.php
	 * @return string
	 */
	private function makeWrapper($ret, $code, $fullShortcodeKey, $sc_mode)
	{
		$pre = $post = '';

		if(!empty($fullShortcodeKey) && !empty($this->wrappers[$fullShortcodeKey]) ) // eg: $NEWS_WRAPPER['view']['item']['NEWSIMAGE: item=1']
		{
			list($pre, $post) = explode("{---}", $this->wrappers[$fullShortcodeKey], 2);
		}
		elseif(isset($this->wrappers[$code]) && !empty($this->wrappers[$code])) // eg: $NEWS_WRAPPER['view']['item']['NEWSIMAGE']
		{
			list($pre, $post) = explode("{---}", $this->wrappers[$code], 2);
		}
		else
		{
			//if $sc_mode exists, we need it to parse $sc_style
			if($sc_mode)
			{
				$code = $code.'|'.$sc_mode;
			}

			if (is_array($this->sc_style) && array_key_exists($code, $this->sc_style))
			{

				// old way - pre/post keys
				if(is_array($this->sc_style[$code]))
				{
					if (isset($this->sc_style[$code]['pre']))
					{
						$pre = $this->sc_style[$code]['pre'];
					}
					if (isset($this->sc_style[$code]['post']))
					{
						$post = $this->sc_style[$code]['post'];
					}
				}
				else // new way - same format as wrapper
				{
					list($pre, $post) = explode("{---}", $this->sc_style[$code], 2);
				}

			}

		}

		if(strpos($pre, '{') !== false) // shortcode found in wrapper
		{
			$this->nowrap = $code;
			$pre = $this->parseCodes($pre, true, $this->addedCodes);
			$this->nowrap = false;
		}

		if(strpos($post, '{') !== false) // shortcode found in wrapper
		{
			$this->nowrap = $code;
			$post = $this->parseCodes($post, true, $this->addedCodes);
			$this->nowrap = false;
		}

		return $pre.$ret.$post;

	}


	/**
	 * Add Editable Container to shortcode (when detected)
	 * @param string $text
	 * @param string $code
	 * @return string
	 */
	private function makeEditable($text, $code)
	{
		if($this->editableActive === false)
		{
			return $text; // unchanged.
		}


		$lcode = strtolower($code);

		if(empty($code)
		|| empty($this->editableCodes)
		|| empty($this->editableCodes['shortcodes'][$lcode])
		|| !isset($this->editableCodes['perms'])
		|| empty($this->editableCodes['table'])
		|| empty($this->editableCodes['pid'])
		|| !getperms($this->editableCodes['perms'])
		|| empty($this->editableCodes['shortcodes'][$lcode]['field'])
		)
		{

			return $text;
		}

		if(!empty($this->editableCodes['vars']))
		{
			$varID = $this->editableCodes['vars'];
			$var = $this->addedCodes->getScVar($varID);
		}
		else
		{
			$var = $this->addedCodes->getVars();
		}


		$container  = empty($this->editableCodes['shortcodes'][$lcode]['container']) ? 'span' : $this->editableCodes['shortcodes'][$lcode]['container'];
		$pid        = $this->editableCodes['pid'];
		$id         = intval($var[$pid]);

		$attributes = "title='".LAN_EDIT."' contenteditable='true' class='e-editable-front' data-edit-id='".$id."' data-edit-sc='".$lcode."' ";

		$ret = ($container == 'div') ?  "<div>" :  "<span>";
		$ret .= ($container == 'div') ? "<div ".$attributes." >".$text."</div>" : "<span  ".$attributes."  >".$text."</span>";

	//	$ret .= "<span class='input-group-btn'>";
	//	$ret .= '<span id="'.$lcode."-".$id.'" class="e-editable-front-save" ><i class="fa fa-fw fa-save"></i></span>';
	//	$ret .= "</span>";
		$ret .= ($container == 'div') ?  "</div>" :  "</span>";

		return $ret;

	}


	/**
	 * @param $fname
	 * @param string $type
	 * @return array
	 */
	function parse_scbatch($fname, $type = 'file')
	{
	//	global $e107cache, $eArrayStorage;
		$cur_shortcodes = array();
		if ($type == 'file')
		{
			$batch_cachefile = 'nomd5_scbatch_'.md5($fname);
			//			$cache_filename = $e107cache->cache_fname("nomd5_{$batchfile_md5}");
			$sc_cache = e107::getCache()->retrieve_sys($batch_cachefile);
			if (!$sc_cache)
			{
				$sc_batch = file($fname);
			}
			else
			{
				$cur_shortcodes = e107::unserialize($sc_cache);
				$sc_batch = "";
			}
		}
		else
		{
			$sc_batch = $fname;
		}
		
		$this->debug_legacy = array('type'=>$type, 'path'=> str_replace(e_ROOT,"",$fname));

		if ($sc_batch)
		{
			$cur_sc = '';
			foreach ($sc_batch as $line)
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
					$cur_shortcodes[$cur_sc] = varset($cur_shortcodes[$cur_sc], '');
				}
			}
			if ($type == 'file')
			{
				$sc_cache = e107::serialize($cur_shortcodes, false);
				e107::getCache()->set_sys($batch_cachefile, $sc_cache);
			}
		}

		foreach (array_keys($cur_shortcodes) as $cur_sc)
		{
			if (array_key_exists($cur_sc, $this->registered_codes))
			{
				if ($this->registered_codes[$cur_sc]['type'] == 'plugin')
				{
					$scFile = e_PLUGIN.strtolower($this->registered_codes[$cur_sc]['path']).'/'.strtolower($cur_sc).'.sc';
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


		$this->legacyBatch = array_keys($cur_shortcodes);
		$this->legacyBatchFile = str_replace(e_ROOT, '', $fname);

		return $cur_shortcodes;
	}
}

class e_shortcode
{
	/**
	 * Stores passed to shortcode handler array data
	 * Usage: $this->var['someKey']
	 * Assigned via setVars() and addVars() methods
	 * @var array
	 */
	protected $var = array(); // value available to each shortcode. 
	
	protected $mode = 'view'; // or edit. Used within shortcodes for form elements vs values only.   

	protected $wrapper = null; // holds template/key value of the currently used wrapper (if any) - see contact_template.php for an example. 	

	protected $override = false;

	protected $editable = null;
	/**
	 * Storage for shortcode values
	 * @var e_vars
	 */
	protected $scVars = null;

	/**
	 * e_shortcode constructor.
	 */
	public function __construct($eVars = null)
	{
		$this->scVars = !empty($eVars) ? $eVars : new e_vars();
	}
	
	/**
	 * Startup code for child class
	 */
	public function init() {}


	/**
	 * Breadcrumb calculations should occur in here.
	 */
	public function breadcrumb() {}


	/**
	 * Sets wrapper id (to be retrieved from the registry while parsing)
	 * Example e107::getScBatch('contact')->wrapper('contact/form');
	 * which results in using the $CONTACT_WRAPPER['form'] wrapper in the parsing phase
	 * Template cannot be loaded via include, only by getTemplate or getCoreTemplate
	 * e107::getScBatch() must be used also.
	 * @param string $id
	 * @return $this|null
	 */
	public function wrapper($id = null)
	{
		if(null === $id) return $this->wrapper;
		
		if(false === $id) $id = null;
		$this->wrapper = $id;

		return $this;
	}


	public function getWrapperID()
	{
		return $this->wrapper;
	}


	public function editable($data=null)
	{
		if(null === $data) return $this->editable;
		$this->editable = $data;

		return $this;
	}


	/**
	 * Set external array data to be used in the batch
	 * Use setVars() - preferred. 
	 *  //XXX will soon become private. Use setVars() instead. 
	 * @param array $eVars
	 * @return e_shortcode
	 */
	public function setParserVars($eVars)
	{
		$this->var = $eVars;
		return $this;
	}

	/**
	 * Alias of setParserVars - Preferred use by Plugins.
	 * Sets the value of $sc->var
	 * @param $eVars
	 * @return e_shortcode
	 */
	public function setVars($eVars) // Alias of setParserVars();
	{
		return $this->setParserVars($eVars);	
	}
	
	/**
	 * Add array to current parser array data
	 * @param array $array
	 * @return e_shortcode
	 */
	public function addParserVars($array) 
	{
		if(!is_array($array)) return $this;
		$this->var = array_merge($this->var, $array);
		return $this;
	}
	
	/**
	 * Alias of addParserVars()
	 * @param array $array
	 * @return e_shortcode
	 */
	public function addVars($array) 
	{
		return $this->addParserVars($array);
	}

	/**
	 * Get external simple parser object
	 *
	 * @return array
	 */
	public function getParserVars()
	{
		return $this->var;
	}

	/**
	 * Alias of getParserVars()
	 *
	 * @return array
	 */
	public function getVars()
	{
		return $this->getParserVars();
	}
	
	/**
	 * Batch mod
	 * @param string mod
	 * @return e_shortcode
	 */
	public function setMode($mode)
	{
		$this->mode = ($mode == 'edit') ? 'edit' : 'view';
		return $this;			
	}
	
	/**
	 * Add shortcode value
	 * <code>e107::getScBatch('class_name')->setScVar('some_property', $some_value);</code>
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return e_shortcode
	 */
	public function setScVar($name, $value)
	{
		$this->scVars->$name = $value;
		return $this;
	}
	
	/**
	 * Add shortcode values
	 * <code>e107::getScBatch('class_name')->addScVars(array('some_property', $some_value));</code>
	 *
	 * @param array $vars
	 * @return e_shortcode
	 */
	public function addScVars($vars)
	{
		$this->scVars->addVars($vars);
		return $this;
	}

	/**
	 * Retrieve shortcode value
	 * <code>$some_value = e107::getScBatch('class_name')->getScVar('some_property');</code>
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getScVar($name)
	{
		return $this->scVars->$name;
	}
	
	/**
	 * Retrieve all shortcode values
	 * <code>$some_value = e107::getScBatch('class_name')->getScVars();</code>
	 *
	 * @return mixed
	 */
	public function getScVars()
	{
		return $this->scVars->getVars();
	}

	/**
	 * Check if shortcode variable is set
	 * <code>if(e107::getScBatch('class_name')->issetScVar('some_property'))
	 * {
	 * 		//do something
	 * }</code>
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function issetScVar($name)
	{
		return isset($this->scVars->$name);
	}

	/**
	 * Unset shortcode value
	 * <code>e107::getScBatch('class_name')->unsetScVar('some_property');</code>
	 *
	 * @param string $name
	 * @return e_shortcode
	 */
	public function unsetScVar($name)
	{
		$this->scVars->$name = null;
		unset($this->scVars->$name);
		return $this;
	}
	
	/**
	 * Empty scvar object data
	 * @return e_shortcode
	 */
	public function emptyScVars()
	{
		$this->scVars->emptyVars();
		return $this;
	}

	/**
	 * Magic setter - bind to eVars object
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->setScVar($name, $value);
	}

	/**
	 * Magic getter - bind to eVars object
	 *
	 * @param string $name
	 * @return mixed value or null if key not found
	 */
	public function __get($name)
	{
		return $this->getScVar($name);
	}

	/**
	 * Magic method - bind to eVars object
	 * NOTE: works on PHP 5.1.0+
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		return $this->issetScVar($name);
	}

	/**
	 * Magic method - bind to eVars object
	 * NOTE: works on PHP 5.1.0+
	 *
	 * @param string $name
	 */
	public function __unset($name)
	{
		$this->unsetScVar($name);
	}
}
