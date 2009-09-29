<?php
/*
 * e107 website system
 * 
 * Copyright (c) 2001-2008 e107 Inc. (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $Source: /cvs_backup/e107_0.8/e107_handlers/js_manager.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-09-29 17:40:55 $
 * $Author: secretr $
 * 
*/
global $pref, $eplug_admin, $THEME_JSLIB, $THEME_CORE_JSLIB;

class e_jsmanager
{
    /**
     * Core JS library files, loaded via e_jslib.php
     *
     * @var array
     */
    protected $_e_jslib_core = array();
	
    /**
     * Plugin JS library files, loaded via e_jslib.php
     *
     * @var array
     */
    protected $_e_jslib_plugin = array();
	
    /**
     * Theme JS library files, loaded via e_jslib.php
     *
     * @var array
     */
    protected $_e_jslib_theme = array();
	
    /**
     * JS files array - loaded in page header
     *
     * @var array
     */
    protected $_runtime_header = array();
	
    /**
     * JS code array - loaded in page header
     * after all registered JS header files
     *
     * @var array
     */
    protected $_runtime_header_src = array();
	
    /**
     * JS files array - loaded in page footer
     *
     * @var array
     */
    protected $_runtime_footer = array();
	
    /**
     * JS code array - loaded in page footer
     *
     * @var array
     */
    protected $_runtime_footer_src = array();
	
    /**
     * All registered JS files
     *
     * @var array
     */
    protected $_js_all = array();	
	
    /**
     * Runtime location
     *
     * @var boolean
     */
    protected $_in_admin = false;
	
	/**
	 * Browser cache id
	 * 
	 * @var integer
	 */
	protected $_cache_id = 0;
    
	/**
	 * Singleton instance
	 * Allow class extends - override {@link getInstance()}
	 * 
	 * @var e_jsmanager
	 */
	protected static $_instance = null;
    
	/**
	 * Constructor
	 * 
	 * Use {@link getInstance()}, direct instantiating 
	 * is not possible for signleton objects
	 *
	 * @return void
	 */
	protected function __construct()
	{
	}

	/**
	 * Cloning is not allowed
	 *
	 */
	private function __clone()
	{
	}
	
	/**
	 * Get singleton instance
	 *
	 * @return e_jsmanager
	 */
	public static function getInstance()
	{
		if(null === self::$_instance)
		{
		    self::$_instance = new self();
			self::$_instance->_init();
		}
	  	return self::$_instance;
	}
	
	/**
	 * Get and parse core preference values (if available)
	 * 
	 * @return void
	 */
	protected function _init()
	{
		// Try to auto-detect runtime location
		$this->setInAdmin(defset('e_ADMIN_AREA', false)); 
		
		// Try to load browser cache id from core preferences
		$this->setCacheId(e107::getPref('e_jslib_browser_cache', 0));
		
		// Load stored in preferences core lib paths ASAP
		$core_libs = e107::getPref('e_jslib_core');
		if(!$core_libs)
		{
			$core_libs = array();
		}
		$this->coreLib($core_libs);
		
		// Load stored in preferences plugin lib paths ASAP
		$plug_libs = e107::getPref('e_jslib_plugin');
		if(!$plug_libs)
		{
			$plug_libs = array();
		}
		foreach ($plug_libs as $plugname => $lib_paths)
		{
			$this->pluginLib($plugname, $lib_paths);
		}
		
		// Load stored in preferences theme lib paths ASAP
		// TODO - decide if THEME should directly use themeLib() or
		// we store paths in 'e_jslib_theme' on theme installation only (theme.xml)!
		$theme_libs = e107::getPref('e_jslib_theme');
		if(!$theme_libs)
		{
			$theme_libs = array();
		}
		$this->themeLib($lib_paths);
	}
	
	/**
	 * Add Core JS library file(s) for inclusion from e_jslib routine
	 * 
	 * @param string|array $file_path relative to e107_files/jslib/ folder or array in format 'path - runtime location'
	 * @param string $runtime_location  admin|front|all - where should be JS used
	 * @return e_jsmanager
	 */
	protected function coreLib($file_path, $runtime_location = 'front')
	{
		$this->addJs('core', $file_path, $runtime_location);	
		return $this;
	}
	
	/**
	 * Add Plugin JS library file(s) for inclusion from e_jslib routine
	 * 
	 * @param string $plugname
	 * @param string|array $file_path relative to e107_plugins/myplug/ folder or array in format 'path - runtime location'
	 * @param string $runtime_location admin|front|all - where should be JS used
	 * @return e_jsmanager
	 */
	protected function pluginLib($plugname, $file_path, $runtime_location = 'front')
	{
		if(is_array($file_path))
		{
			foreach ($file_path as $fpath => $rlocation)
			{
				$this->addJs('plugin', $plugname.':'.$fpath, $rlocation);
			}
			return $this;
		}
		$this->addJs('plugin', $plugname.':'.$file_path, $runtime_location);		
		return $this;
	}
	
	/**
	 * Add Theme JS library file(s) for inclusion from e_jslib routine
	 * 
	 * @param string|array $file_path relative to e107_themes/current_theme/ folder or array in format 'path - runtime location'
	 * @param string $runtime_location admin|front|all - where should be JS used
	 * @return e_jsmanager
	 */
	protected function themeLib($file_path, $runtime_location = 'front')
	{
		$this->addJs('theme', $file_path, $runtime_location);		
		return $this;
	}
	
	/**
	 * Add Core JS library file(s) for inclusion in site header or site footer (in this order) if not 
	 * already loaded by e_jslib routine. This should avoid dependency problems.
	 * Extremely useful for shortcodes and menus. 
	 * 
	 * @param string $file_path relative to e107_files/jslib/ folder
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_jsmanager
	 */
	public function requireCoreLib($file_path, $zone = 2)
	{
		if(is_array($file_path))
		{
			foreach ($file_path as $fpath => $z)
			{
				$this->tryHeaderFile('{e_FILE}jslib/'.trim($fpath, '/'), $z);
			}
			return $this;
		}
		$this->tryHeaderFile('{e_FILE}jslib/'.trim($file_path, '/'), $zone);
		return $this;
	}
	
	/**
	 * Add Plugin JS library file(s) for inclusion in site header if not 
	 * already loaded by e_jslib routine. This should avoid dependency problems.
	 * 
	 * @param string $plugname
	 * @param string $file_path relative to e107_plugins/myplug/ folder
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_jsmanager
	 */
	public function requirePluginLib($plugname, $file_path, $zone = 5)
	{
		if(is_array($file_path))
		{
			foreach ($file_path as $fpath => $z)
			{
				$this->tryHeaderFile('{e_PLUGIN}'.$plugname.'/'.trim($fpath, '/'), $z);
			}
			return $this;
		}
		$this->tryHeaderFile('{e_PLUGIN}'.$plugname.'/'.trim($file_path, '/'), $zone);
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site header
	 * 
	 * @param string|array $file_path path shortcodes usage is prefered
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_jsmanager
	 */
	public function headerFile($file_path, $zone = 5)
	{
		$this->addJs('header', $file_path, $zone);		
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site header if possible, else 
	 * use {@link footerFile()}
	 * 
	 * @param string|array $file_path path shortcodes usage is prefered
	 * @param integer $zone 1-5 (see header.php and footer.php)
	 * @return e_jsmanager
	 */
	public function tryHeaderFile($file_path, $zone = 5)
	{
		if(!defined('HEADER_INIT'))
		{
			$this->headerFile($file_path, $zone);
			return $this;
		}
		
		$this->footerFile($file_path, $zone);
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site footer
	 * 
	 * @param string|array $file_path path shortcodes usage is prefered
	 * @param integer $priority 1-5 (see footer.php)
	 * @return e_jsmanager
	 */
	public function footerFile($file_path, $priority = 5)
	{
		$this->addJs('footer', $file_path, $priority);		
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site header
	 * 
	 * @param string|array $js_content
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_jsmanager
	 */
	public function headerInline($js_content, $zone = 5)
	{
		$this->addJs('header_inline', $js_content, $zone);		
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site header if possible, else 
	 * use {@link footerFile()}
	 * 
	 * @param string $js_content
	 * @param integer $zone 1-5 (see header.php and footer.php)
	 * @return e_jsmanager
	 */
	public function tryHeaderInline($js_content, $zone = 5)
	{
		if(!defined('HEADER_INIT'))
		{
			$this->headerInline($js_content, $zone);
			return $this;
		}
		
		$this->footerInline($js_content, $zone);
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site footer
	 * 
	 * @param string|array $js_content path shortcodes usage is prefered
	 * @param integer $priority 1-5 (see footer.php)
	 * @return e_jsmanager
	 */
	public function footerInline($js_content, $priority = 5)
	{
		$this->addJs('footer_inline', $js_content, $priority);		
		return $this;
	}
	
	/**
	 * Require JS file(s). Used by corresponding public proxy methods.
	 * 
	 * @see themeLib()
	 * @see pluginLib()
	 * @see coreLib()
	 * @see headerFile()
	 * @see footerFile()
	 * @see headerInline()
	 * @see footerInline()
	 * @param string $type core|plugin - jslib.php, header|footer|header_inline|footer_inline - runtime
	 * @param string|array $file_path
	 * @param string|integer $runtime_location admin|front|all (jslib), 0-5 (runtime inclusion)
	 * @return 
	 */
	protected function addJs($type, $file_path, $runtime_location = '')
	{
		if(empty($file_path))
		{
			return $this;
		}
		if(is_array($file_path))
		{
			foreach ($file_path as $fp => $loc)
			{
				$this->addJs($type, $fp, $loc);
			}
			return $this;
		}
		
		$tp = e107::getParser();
		$runtime = false;
		switch($type)
		{
			case 'core':
				$file_path = '{e_FILE}jslib/'.trim($file_path, '/');
				$registry = &$this->_e_jslib_core;
			break;
		
			case 'plugin':
				$file_path = explode(':', $file_path);
				$file_path = '{e_PLUGIN}'.$file_path[0].'/'.trim($file_path[1], '/');
				$registry = &$this->_e_jslib_plugin;
			break;
			
			case 'theme':
				$file_path = '{e_THEME}'.$this->getCurrentTheme().'/'.trim($file_path, '/');
				$registry = &$this->_e_jslib_theme;
			break;
			
			case 'header':
				$file_path = $tp->createConstants($file_path, 4);
				$zone = intval($runtime_location);
				if($zone > 5 || $zone < 1)
				{
					$zone = 5;
				}
				if(!isset($this->_runtime_header[$zone])) 
				{
					$this->_runtime_header[$zone] = array();
				}
				$registry = &$this->_runtime_header[$zone];
				$runtime = true;
			break;
			
			case 'footer':
				$file_path = $tp->createConstants($file_path, 4);
				$zone = intval($runtime_location);
				if($zone > 5 || $zone < 1)
				{
					$zone = 5;
				}
				if(!isset($this->_runtime_footer[$zone])) 
				{
					$this->_runtime_footer[$zone] = array();
				}
				$registry = &$this->_runtime_footer[$zone];
				$runtime = true;
			break;
			
			case 'header_inline':
				$zone = intval($runtime_location);
				if($zone > 5 || $zone < 1)
				{
					$zone = 5;
				}
				//$this->_js_all[] = $file_path;
				$this->_runtime_header_src[$zone][] = $file_path;
				return $this;
				break;
			break;
			
			case 'footer_inline':
				$zone = intval($runtime_location);
				if($zone > 5 || $zone < 1)
				{
					$zone = 5;
				}
				//$this->_js_all[] = $file_path;
				$this->_runtime_footer_src[$zone][] = $file_path;
				return $this;
			break;
			
			default:
				return $this;
			break;
		}

		if(in_array($file_path, $this->_js_all) || (!$runtime && $runtime_location != 'all' && $runtime_location != $this->getCurrentLocation()))
		{
			return $this;
		}
		
		$this->_js_all[] = $file_path;
		$registry[] = $file_path;
		
		return $this;
	}
	
	/**
	 * Render registered JS
	 * 
	 * @param string $mod core|plugin|theme|header|footer|header_inline|footer_inline
	 * @param integer $zone 1-5 - only used when in 'header','footer','header_inline' and 'footer_inline' render mod
	 * @param boolean $external exrernal file calls, only used when NOT in 'header_inline' and 'footer_inline' render mod
	 * @param boolean $return
	 * @return string JS content - only if $return is true
	 */
	public function renderJs($mod, $zone, $external = true, $return = false)
	{
		if($return)
		{
			ob_start();
		}
		
		switch($mod)
		{
			case 'core':
				$this->renderFile($this->_e_jslib_core, $external, 'Core libraries');
				$this->_e_jslib_core = array();
			break;
		
			case 'plugin':
				foreach($this->_e_jslib_plugin as $plugname => $paths)
				{
					$this->renderFile($paths, $external, $plugname.' libraries');
				}
				$this->_e_jslib_plugin = array();
			break;
			
			case 'theme':
				$this->renderFile($this->_e_jslib_theme, $external, 'Theme libraries');
				$this->_e_jslib_theme = array();
			break;
			
			case 'header':
				$this->renderFile(varsettrue($this->_runtime_header[$zone], array()), $external, 'Header JS include - zone #'.$zone);
				unset($this->_runtime_header[$zone]);
			break;
			
			case 'footer':
				if(true === $zone)
				{
					ksort($this->_runtime_footer, SORT_NUMERIC);
					foreach ($this->_runtime_footer as $priority => $path_array)
					{
						$this->renderFile($path_array, $external, 'Footer JS include - priority #'.$priority);
					}
					$this->_runtime_footer = array();
				}
				else
				{
					$this->renderFile(varsettrue($this->_runtime_footer[$zone], array()), $external, 'Footer JS include - priority #'.$zone);
					unset($this->_runtime_footer[$zone]);
				}
			break;
			
			case 'header_inline':
				$this->renderInline(varsettrue($this->_runtime_header_src[$zone], array()), 'Header JS - zone #'.$zone);
				unset($this->_runtime_header_src[$zone]);
			break;
			
			case 'footer_inline':
				if(true === $zone)
				{
					ksort($this->_runtime_footer_src, SORT_NUMERIC);
					foreach ($this->_runtime_footer_src as $priority => $src_array)
					{
						$this->renderInline($src_array, 'Footer JS - priority #'.$priority);
					}
					$this->_runtime_footer_src = array();
				}
				else
				{
					$this->renderInline(varsettrue($this->_runtime_footer_src[$zone], array()), 'Footer JS - priority #'.$zone);
					unset($this->_runtime_footer_src[$zone]);
				}
			break;
		}
		
		if($return)
		{
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}
	
	/**
	 * Render JS file array
	 * TODO - option to output <script src='$path'>
	 * 
	 * @param array $file_path_array
	 * @param boolean $external if true - external file calls, else get file contents
	 * @param string $label added as comment if non-empty
	 * @return void
	 */
	public function renderFile($file_path_array, $external = false, $label = '')
	{
		if(empty($file_path_array))
		{
			return '';
		}
		$tp = e107::getParser();
		echo "\n";
		if($external && $label) //TODO - print comments only if site debug is on
		{
			echo "<!-- [JSManager] ".$label." -->\n";
		}
		foreach ($file_path_array as $path)
		{
            if (substr($path, - 4) == '.php')
            {
            	if($external)
				{
					echo '<script type="text/javascript" src="'.$tp->replaceConstants($path, 'abs').'?external=1&amp;cacheid='.$this->getCacheId().'"></script>';
					echo "\n";
					continue;
				}
                include_once($tp->replaceConstants($path, ''));
                echo "\n";
            }
            else
            {
            	if($external)
				{
					echo '<script type="text/javascript" src="'.$tp->replaceConstants($path, 'abs').'?'.$this->getCacheId().'"></script>';
					echo "\n";
					continue;
				}
                echo file_get_contents($tp->replaceConstants($path, ''));
                echo "\n";
            }
		}
	}
	
	/**
	 * Render JS source array
	 * 
	 * @param array $js_content_array
	 * @param string $label added as comment if non-empty
	 * @return void
	 */
	public function renderInline($js_content_array, $label = '')
	{
		if(empty($js_content_array))
		{
			return '';
		}
		echo "\n";
		if($label) //TODO - print comments only if site debug is on
		{
			echo "<!-- [JSManager] ".$label." -->\n";
		}
		echo '<script type="text/javascript">';
		echo "\n//<![CDATA[\n";
		echo implode("\n\n", $js_content_array);
		echo "\n//]]>\n";
		echo '</script>';
		echo "\n";
	}
	
	/**
	 * Returns true if currently running in
	 * administration area.
	 * 
	 * @return boolean
	 */
	public function isInAdmin()
	{
		return $this->_in_admin;
	}
	
	/**
	 * Set current script location
	 * 
	 * @param object $is true - back-end, false - front-end
	 * @return e_jsmanager
	 */
	public function setInAdmin($is)
	{
		$this->_in_admin = (boolean) $is;
		return $this;
	}
	
	/**
	 * Get current location as a string (admin|front)
	 * 
	 * @return string
	 */
	public function getCurrentLocation()
	{
		return ($this->isInAdmin() ? 'admin' : 'front');
	}

	/**
	 * Get current theme name
	 * 
	 * @return string
	 */
	public function getCurrentTheme()
	{
		return ($this->isInAdmin() ? e107::getPref('admintheme') : e107::getPref('sitetheme'));
	}
	
	/**
	 * Get browser cache id
	 * 
	 * @return integer
	 */
	public function getCacheId()
	{
		return $this->_cache_id;
	}
	
	/**
	 * Set browser cache id
	 * 
	 * @return e_jsmanager
	 */
	public function setCacheId($cacheid)
	{
		$this->_cache_id = $cacheid;
		return $this;
	}
}
