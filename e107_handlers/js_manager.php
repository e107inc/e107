<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * JS Manager.
 */


/**
 * Class e_jsmanager.
 */
class e_jsmanager
{
	/**
	 * Supported Libraries (Front-End) - loaded on demand. 
	 */
	protected $_libraries = array(
		'prototype'	=> array(), // TODO remove prototype completely.
		'jquery'	=> array(),
	);

	/**
	 * Dynamic List of files to be cached.
	 * @var array
	 */
	protected $_cache_list = array();



	protected $_core_prefs = array();

	/**
	 * Array to store JavaScript options will be rendered in footer as JSON object.
	 *
	 * @var array
	 */
	protected $_e_js_settings = array(
		'basePath' => e_HTTP,
	);
	
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
     * Current Header zone (under development)
     *
     * @var array
     */
    protected $_zone_header = 0;

    /**
     * Current Footer zone (under development)
     *
     * @var array
     */
    protected $_zone_footer = 0;

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
     * Index of all registered JS/CSS files - for faster searching
     *
     * @var array
     */
    protected $_index_all = array();

   /**
     * Registered link tags files by type (core|theme|plugin|other)
     *
     * @var array
     */
	protected $_e_link = array();


    /**
     * Registered CSS files by type (core|theme|plugin|other)
     *
     * @var array
     */
    protected $_e_css = array();

    /**
     * Inline CSS
     *
     * @var array
     */
    protected $_e_css_src = array();


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
	protected $_browser_cache_id = 0;

	/**
	 * @var array
	 */
	protected $_lastModified = array();

	/**
	 * Singleton instance
	 * Allow class extends - override {@link getInstance()}
	 *
	 * @var e_jsmanager
	 */
	protected static $_instance = null;
	
	/**
	 * Current Framework Dependency
	 *
	 * @var string null | prototype | jquery
	 */
	protected $_dependence = null;
	
	/**
	 * Loaded Framework Dependency
	 *
	 * @var array
	 */
	protected $_dependenceLoaded = array();


	protected $_cache_enabled = false;


	protected $_sep = '#|#';

	/**
	 * Constructor
	 *
	 * Use {@link getInstance()}, direct instantiating
	 * is not possible for signleton objects
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

		if($this->isInAdmin()) // Admin Area.
		{
			e107::library('load', 'jquery');
			// jQuery Once is used in e107.behaviors.
			e107::library('load', 'jquery.once');
			e107::library('load', 'jquery.ui');
		}
		else // Front-End.
		{
			e107::library('load', 'jquery');
			// jQuery Once is used in e107.behaviors.
			e107::library('load', 'jquery.once');
		}

		// TODO
		// jQuery is the only JS framework, and it is always loaded. So remove
		// unnecessary code here below.

		$customJqueryUrls = e107::getPref('library-jquery-urls');
		$this->_cache_enabled = e107::getPref('jscsscachestatus',false);
		
		if(vartrue($customJqueryUrls) && $this->_in_admin === false)
		{
			$this->_libraries['jquery'] = explode("\n", $customJqueryUrls);	
		}

		// Try to load browser cache id from core preferences
		$this->setCacheId(e107::getPref('e_jslib_browser_cache', 0));

		// Load stored in preferences core lib paths ASAP
		$this->_core_prefs = e107::getPref('e_jslib_core');
		$core = array();

		if(is_array($this->_core_prefs))
		{
			foreach($this->_core_prefs as $id=>$vis)
			{
				$this->_dependence = $id;

				if(!$this->libDisabled($id,$vis))
				{
					if(vartrue($this->_libraries[$id]))
					{
						foreach($this->_libraries[$id] as $path)
						{
							$core[$path] = $vis;
						}		
					}
					
				}
	
			}
		}
		$this->_dependence = null;
	
		if($vis != 'auto')
		{
			$this->checkLibDependence(null, $core);
		}

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
		$this->themeLib($theme_libs);
	}

	/**
	 * Add Core CSS file for inclusion in site header, shorthand of headerFile() method
	 *
	 * @param string|array $file_path relative to {e_JS} folder
	 * @param string $media any valid media attribute string - http://www.w3schools.com/TAGS/att_link_media.asp
	 * @return e_jsmanager
	 */
	public function coreCSS($file_path, $media = 'all', $preComment = '', $postComment = '')
	{
		$this->addJs('core_css', $file_path, $media);
		return $this;
	}

	/**
	 * Add Plugin CSS file(s) for inclusion in site header
	 *
	 * @param string $plugname
	 * @param string|array $file_path relative to e107_plugins/myplug/ folder or array in format 'path - media'
	 * @param string $media any valid media attribute string - http://www.w3schools.com/TAGS/att_link_media.asp
	 * @return e_jsmanager
	 */
	public function pluginCSS($plugname, $file_path, $media = 'all', $preComment = '', $postComment = '')
	{
		if(is_array($file_path))
		{
			foreach ($file_path as $fpath => $media_attr)
			{
				$this->addJs('plugin_css', $plugname.':'.$fpath, $media_attr, $preComment, $postComment);
			}
			return $this;
		}
		$this->addJs('plugin_css', $plugname.':'.$file_path, $media, $preComment, $postComment);
		return $this;
	}

	/**
	 * Add Theme CSS file(s) for inclusion in site header
	 *
	 * @param string|array $file_path relative to e107_themes/current_theme/ folder
	 * @param string $media any valid media attribute string - http://www.w3schools.com/TAGS/att_link_media.asp
	 * @return e_jsmanager
	 */
	public function themeCSS($file_path, $media = 'all', $preComment = '', $postComment = '')
	{
		$this->addJs('theme_css', $file_path, $media, $preComment, $postComment);
		return $this;
	}

	/**
	 * Add CSS file(s) for inclusion in site header in the 'library' category.
	 *
	 * @param string|array $file_path path, shortcodes usage is prefered
	 * @param string $media any valid media attribute string - http://www.w3schools.com/TAGS/att_link_media.asp
	 * @return e_jsmanager
	 */
	public function libraryCSS($file_path, $media = 'all', $preComment = '', $postComment = '')
	{
		$this->addJs('library_css', $file_path, $media, $preComment, $postComment);
		return $this;
	}



	/**
	 * Add CSS file(s) for inclusion in site header
	 *
	 * @param string|array $file_path path, shortcodes usage is prefered
	 * @param string $media any valid media attribute string - http://www.w3schools.com/TAGS/att_link_media.asp
	 * @return e_jsmanager
	 */
	public function otherCSS($file_path, $media = 'all', $preComment = '', $postComment = '')
	{
		$this->addJs('other_css', $file_path, $media, $preComment, $postComment);
		return $this;
	}

	/**
	 * Add CSS code to site header
	 *
	 * @param string|array $js_content
	 * @param string $media (not implemented yet) any valid media attribute string - http://www.w3schools.com/TAGS/att_link_media.asp
	 * @return e_jsmanager
	 */
	public function inlineCSS($css_content, $media = 'all')
	{
		$this->addJs('inline_css', $css_content, $media);
		return $this;
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
	public function themeLib($file_path, $runtime_location = 'front')
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
				$this->tryHeaderFile('{e_WEB_JS}'.trim($fpath, '/'), $z);
			}
			return $this;
		}
		$this->tryHeaderFile('{e_WEB_JS}'.trim($file_path, '/'), $zone);
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
	public function headerFile($file_path, $zone = 5, $pre = '', $post = '')
	{
		$this->addJs('header', $file_path, $zone, $pre, $post);
		return $this;
	}

	/**
	 * Add Core JS file for inclusion in site header, shorthand of headerFile() method
	 *
	 * @param string $file_path relative to {e_JS} folder
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_jsmanager
	 */
	public function headerCore($file_path, $zone = 2, $pre = '', $post = '')
	{
		$this->headerFile('{e_WEB_JS}'.trim($file_path, '/'), $zone, $pre, $post);
		return $this;
	}

	/**
	 * Add Theme JS file for inclusion in site header, shorthand of headerFile() method
	 *
	 * @param string $file_path relative to theme root folder
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_jsmanager
	 */
	public function headerTheme($file_path, $zone = 5, $pre = '', $post = '')
	{
		$this->headerFile(THEME.trim($file_path, '/'), $zone, $pre, $post);
		return $this;
	}


	/**
	 * Add Theme JS file for inclusion in site footer (preferred), shorthand of footerFile() method
	 *
	 * @param string $file_path relative to theme root folder
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_jsmanager
	 */
	public function footerTheme($file_path, $zone = 5, $pre = '', $post = '')
	{
		$this->footerFile(THEME.trim($file_path, '/'), $zone, $pre, $post);
		return $this;
	}

	/**
	 * Add Plugin JS file for inclusion in site header, shorthand of headerFile() method
	 *
	 * @param string $plugname
	 * @param string $file_path relative to plugin root folder
	 * @param integer $zone 1-5 (see header.php) - REMOVED, actually we need to prevent zone change
	 * @return e_jsmanager
	 */
	public function headerPlugin($plugname, $file_path, $pre, $post)
	{
		$this->headerFile('{e_PLUGIN}'.$plugname.'/'.trim($file_path, '/'), 2, $pre, $post);	// Zone 2 - after libraries
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
	public function footerFile($file_path, $priority = 5, $pre = '', $post = '')
	{
		$this->addJs('footer', $file_path, $priority, $pre, $post);
		return $this;
	}

	/**
	 * Add JS code to site header
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
	 * Add JS code to site site header if possible, else
	 * use {@link footerInline()}
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
	 * Add JS settings to site footer
	 *
	 * @param array $js_settings
	 * @return e_jsmanager
	 */
	public function jsSettings(array $js_settings)
	{
		$this->addJs('settings', $js_settings);
		return $this;
	}
	
	
	function setDependency($dep)
	{
		$this->_dependence = $dep;		
	}
	
	public function resetDependency()
	{
		$this->_dependence = null;
	}
	
	/**
	 * Return TRUE if the library is disabled. ie. prototype or jquery. 
	 * FIXME - remove $type & $loc
	 */
	public function libDisabled($type = null, $loc = null)
	{
		if($type == 'core' && ($loc == 'none'))
		{
			return true;
		}
		
		if($this->_dependence != null && isset($this->_libraries[$this->_dependence]))
		{			
			$status = $this->_core_prefs[$this->_dependence];
			
			switch ($status)
			{
				case 'auto':
				case 'all':
					return false;	
				break;
				
				case 'admin':
					return ($this->isInAdmin()) ? false : true;	
				break;
				
				case 'front':
					return ($this->isInAdmin()) ? true : false;	
				break;
				
				case 'none':
					return true;	
				break;
				
				default:
					return true;
				break;
			}
		}

		return false;
		
	}
	
	public function checkLibDependence($rlocation, $libs = null)
	{
		// Load Required Library (prototype | jquery)
		// called from addJs(), make isDisabled checks for smart runtime library detection
		if($rlocation && $libs === null && $this->_dependence != null && isset($this->_libraries[$this->_dependence]) && !isset($this->_dependenceLoaded[$this->_dependence][$rlocation])) // load framework
		{
			if($this->libDisabled()) 
			{
				$this->_dependenceLoaded[$this->_dependence][$rlocation] = array();
				return;
			}
			
			foreach($this->_libraries[$this->_dependence] as $inc)
			{
				if(strpos($inc,".css")!==false)
				{
					if(strpos($inc,"://")!==false) // cdn 
					{
						$this->addJs('other_css', $inc, 'all', '<!-- AutoLoad -->');	
					}
					else
					{
						$this->addJs('core_css', $inc, 'all', '<!-- AutoLoad -->');
					}
				}
				else
				{
					$this->addJs('core', $inc, $rlocation, '<!-- AutoLoad -->');
				}
				$this->_dependenceLoaded[$this->_dependence][$rlocation][] = $inc;
			}
			return $this;
		}
		// called on init time, isDisabled checks already done, just add stuff
		if($rlocation === null && is_array($libs))
		{
			foreach ($libs as $inc => $rlocation) 
			{
				if(isset($this->_dependenceLoaded[$this->_dependence][$rlocation]) && in_array($inc, $this->_dependenceLoaded[$this->_dependence][$rlocation]))
				{
					continue;
				}
				if(strpos($inc,".css")!==false)
				{
					if(strpos($inc,"://")!==false) // cdn 
					{
						$this->addJs('other_css', $inc, 'all', '<!-- AutoLoad -->');	
					}
					else
					{
						$this->addJs('core_css', $inc, 'all', '<!-- AutoLoad -->');
					}
				}
				else
				{
					$this->addJs('core', $inc, $rlocation, '<!-- AutoLoad -->');
				}
				$this->_dependenceLoaded[$this->_dependence][$rlocation][] = $inc;
			}
		}
	}


	/**
	 * Add a <link> tag to the head.
	 * @param array $attributes key>value pairs
	 * @example addLink(array('rel'=>'prefetch', 'href'=>THEME.'images/browsers.png'));
	 */
	public function addLink($attributes=array())
	{
		if(!empty($attributes))
		{
			$this->_e_link[] = $attributes;
		}
	}


	/**
	 * Render all link tags. (other than css)
	 * @return null
	 */
	public function renderLinks()
	{

		if(empty($this->_e_link))
		{
			return null;
		}

		$text = '';

		foreach($this->_e_link as $v)
		{
			if(!empty($v['type']))
			{
				if($v['type'] == 'text/css' || $v['rel'] == 'stylesheet') // not for this purpose. use e107::css();
				{
					continue;
				}
			}


			$text .= "\n<link";
			foreach($v as $key=>$val)
			{
				if(!empty($val))
				{
					$text .= " ".$key."=\"".$val."\"";
				}
			}
			$text .= " />";

		}

		echo $text;
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
	 * @param string $type core|plugin - jslib.php, header|footer|header_inline|footer_inline|core_css|plugin_css|theme_css|other_css|inline_css - runtime
	 * @param string|array $file_path
	 * @param string|integer $runtime_location admin|front|all|none (jslib), 0-5 (runtime inclusion), 'media' attribute (CSS)
	 * @return object $this
	 */
	protected function addJs($type, $file_path, $runtime_location = '', $pre = '', $post = '')
	{
		// TODO FIXME - remove JS framework dependency from front-end and backend. 
		// ie. no JS errors when prototype.js is completely disabled. 
		// no JS error with only 'e107 Core Minimum' is enabled. 
		// e107 Core Minimum should function independently of framework. 
		// ie. e107 Core Minimum: JS similar to e107 v1.0 should be loaded  "e_js.php" (no framwork dependency) 
		// with basic functions like SyncWithServerTime() and expandit(), externalLinks() etc. 

		if(empty($file_path))
		{
			return $this;
		}
		
		// prevent loop of death
		if($pre != '<!-- AutoLoad -->') 
		{
			$rlocation = $runtime_location;
			if(is_numeric($runtime_location)) $rlocation = $this->isInAdmin() ? 'admin' : 'front';
			
			$this->checkLibDependence($rlocation);
			
			
			// FIXME - better performance - executed on every addJs call - BAD
			//libraries handled only by checkLibDependence()
			if(!is_array($file_path))
			{
				foreach ($this->_libraries as $l) 
				{
					if(in_array($file_path, $l)) 
					{
						return $this;
					} 
				}
			}
		}

		// if($type == 'core' && !is_array($file_path) && substr($file_path,0,4)=='http' ) // Core using CDN. 
		// {
			// $type = 'header';
			// $runtime_location = 1;
		// }
		
		// Possibly no longer needed. 
		// FIXME - this could break something after CSS support was added, move it to separate method(s), recursion by type!
		// Causes the css error on jquery-ui as a css file is loaded as a js. 
		 
		if(is_array($file_path) && $type != 'settings')
		{
		// 	print_a($file_path);
			foreach ($file_path as $fp => $loc)
			{
				if(is_numeric($fp))
				{
					$fp = $loc;
					$loc = $runtime_location;
				}
				
				$type = (strpos($fp,".css")!==false && $type == 'core') ? "core_css" : $type;
				
							
				 $this->addJs($type, $fp, $loc);
			}
			return $this;
		}
		
		if($this->libDisabled($type,$runtime_location))
		{
			//echo $this->_dependence." :: DISABLED<br />";
			// echo $this->_dependence."::".$file_path." : DISABLED<br />";		
			return $this;
			
		}
		else
		{
			// echo $this->_dependence." :: ENABLED<br />";
			 // echo $this->_dependence."::".$file_path." : DISABLED<br />";		
		}
		
		

		$tp = e107::getParser();
		$runtime = false;
		switch($type)
		{
			case 'core':
				// added direct CDN support
				$file_path = (strpos($file_path, 'http') !== 0 && strpos($file_path, '//') !== 0 ? '{e_WEB_JS}'.trim($file_path, '/') : $file_path).$this->_sep.$pre.$this->_sep.$post;
				$registry = &$this->_e_jslib_core;
			break;

			case 'plugin':
				$file_path = explode(':', $file_path);
				$file_path = '{e_PLUGIN}'.$file_path[0].'/'.trim($file_path[1], '/').$this->_sep.$pre.$this->_sep.$post;
				$registry = &$this->_e_jslib_plugin;
			break;

			case 'theme':
				$file_path = '{e_THEME}'.$this->getCurrentTheme().'/'.trim($file_path, '/').$this->_sep.$pre.$this->_sep.$post;
				//echo "file-Path = ".$file_path;
				$registry = &$this->_e_jslib_theme;
			break;

			case 'core_css': //FIXME - core CSS should point to new e_WEB/css; add one more case - js_css -> e_WEB/jslib/
				// added direct CDN support
				$file_path = $runtime_location.$this->_sep.(strpos($file_path, 'http') !== 0 && strpos($file_path, '//') !== 0 ? '{e_WEB_JS}'.trim($file_path, '/') : $file_path).$this->_sep.$pre.$this->_sep.$post;
				if(!isset($this->_e_css['core'])) $this->_e_css['core'] = array();
				$registry = &$this->_e_css['core'];
				$runtime = true;
			break;

			case 'plugin_css':
				$pfile_path = explode(':', $file_path,2);
				$plugfile_path = $runtime_location.$this->_sep.'{e_PLUGIN}'.$pfile_path[0].'/'.trim($pfile_path[1], '/').$this->_sep.$pre.$this->_sep.$post;

				// allow for URLs to be attributed to plugins. (loads after theme css in admin area header)
				$file_path = ((strpos($pfile_path[1], 'http') !== 0 && strpos($pfile_path[1], '//') !== 0)) ? $plugfile_path : 'all'.$this->_sep.$pfile_path[1].$this->_sep.$pre.$this->_sep.$post;;
				if(!isset($this->_e_css['plugin'])) $this->_e_css['plugin'] = array();
				$registry = &$this->_e_css['plugin'];
				$runtime = true;

			break;

			case 'theme_css':
				$file_path = $runtime_location.$this->_sep.'{e_THEME}'.$this->getCurrentTheme().'/'.trim($file_path, '/').$this->_sep.$pre.$this->_sep.$post;
				if(!isset($this->_e_css['theme'])) $this->_e_css['theme'] = array();
				$registry = &$this->_e_css['theme'];
				$runtime = true;
			break;

			case 'other_css':
				$file_path = $runtime_location.$this->_sep.$tp->createConstants($file_path, 'mix').$this->_sep.$pre.$this->_sep.$post;
				if(!isset($this->_e_css['other'])) $this->_e_css['other'] = array();
				$registry = &$this->_e_css['other'];
				$runtime = true;
			break;

			case 'library_css':
				$file_path = $runtime_location.$this->_sep.$tp->createConstants($file_path, 'mix').$this->_sep.$pre.$this->_sep.$post;
				// 	e107::getDebug()->log($file_path);
				if(!isset($this->_e_css['library'])) $this->_e_css['library'] = array();
				$registry = &$this->_e_css['library'];
				$runtime = true;
			break;

			case 'inline_css': // no zones, TODO - media?
				$this->_e_css_src[] = $file_path;
				return $this;
				break;
			break;


			case 'header':
				$file_path = $tp->createConstants($file_path, 'mix').$this->_sep.$pre.$this->_sep.$post;
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
				$file_path = $tp->createConstants($file_path, 'mix').$this->_sep.$pre.$this->_sep.$post;
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
				$this->_runtime_footer_src[$zone][] = $file_path;
				return $this;
			break;

			case 'settings':
				$this->_e_js_settings = $this->arrayMergeDeepArray(array($this->_e_js_settings, $file_path));
				return $this;
			break;

			default:
				return $this;
			break;
		}

		if(in_array($file_path, $this->_index_all) || (!$runtime && $runtime_location != 'all' && $runtime_location != $this->getCurrentLocation()))
		{
			return $this;
		}

		$this->_index_all[] = $file_path;
		$registry[] = $file_path;

		return $this;
	}

	/**
	 * Render registered JS
	 *
	 * @param string $mod core|plugin|theme|header|footer|header_inline|footer_inline|core_css|plugin_css|theme_css|other_css|inline_css
	 * @param integer $zone 1-5 - only used when in 'header','footer','header_inline' and 'footer_inline' render mod
	 * @param boolean $external external file calls, only used when NOT in 'header_inline' and 'footer_inline' render mod
	 * @param boolean $return
	 * @return string JS content - only if $return is true
	 */
	public function renderJs($mod, $zone = null, $external = true, $return = false)
	{
		if($return)
		{
			ob_start();
		}

		switch($mod)
		{
			case 'settings':
				$tp = e107::getParser();
				$json = $tp->toJSON($this->_e_js_settings);
				echo "<script>\n";
				echo "var e107 = e107 || {'settings': {}, 'behaviors': {}};\n";
				echo "jQuery.extend(e107.settings, " . $json . ");\n";
				echo "</script>\n";
			break;

			case 'framework': // CDN frameworks - rendered before consolidation script (if enabled)
				$fw = array();
				foreach ($this->_libraries as $lib) 
				{
					foreach ($lib as $path) 
					{
						$erase = array_search($path, $this->_e_jslib_core);
						if($erase !== false && strpos($path, 'http') === 0)
						{
							unset($this->_e_jslib_core[$erase]);
							$fw[] = $path;
						}
					}
				}
				$this->renderFile($fw, $external, 'CDN Framework', $mod, false);
			break;
			
			case 'core': //e_jslib
				$this->setLastModfied($mod, $this->renderFile($this->_e_jslib_core, $external, 'Core libraries', $mod));
				$this->_e_jslib_core = array();
			break;

			case 'plugin': //e_jslib
				/*foreach($this->_e_jslib_plugin as $plugname => $paths)
				{
					$this->setLastModfied($mod, $this->renderFile($paths, $external, $plugname.' libraries'));
				}*/
				$this->setLastModfied($mod, $this->renderFile($this->_e_jslib_plugin, $external, 'Plugin libraries', $mod));
				$this->_e_jslib_plugin = array();
			break;

			case 'theme': //e_jslib
				$this->setLastModfied($mod, $this->renderFile($this->_e_jslib_theme, $external, 'Theme libraries', $mod));
				$this->_e_jslib_theme = array();
			break;

			case 'header':
				$this->renderFile(vartrue($this->_runtime_header[$zone], array()), $external, 'Header JS include - zone #'.$zone, $mod);
				unset($this->_runtime_header[$zone]);
			break;

			case 'core_css': //e_jslib
				$this->renderFile(varset($this->_e_css['core'], array()), $external, 'Core CSS', $mod, false);
				unset($this->_e_css['core']);
			break;

			case 'plugin_css': //e_jslib
				$this->renderFile(varset($this->_e_css['plugin'], array()), $external, 'Plugin CSS', $mod, false);
				unset($this->_e_css['plugin']);
			break;

			case 'theme_css': //e_jslib
				$this->renderFile(varset($this->_e_css['theme'], array()), $external, 'Theme CSS', $mod, false);
				unset($this->_e_css['theme']);
			break;

			case 'other_css':
				$this->renderFile(varset($this->_e_css['other'], array()), $external, 'Other CSS', $mod, false);
				unset($this->_e_css['other']);
			break;

			case 'library_css':
				$this->renderFile(varset($this->_e_css['library'], array()), $external, 'Library CSS', $mod, false);
				unset($this->_e_css['library']);
			break;

			case 'inline_css':
				$this->renderInline($this->_e_css_src, 'Inline CSS', 'css');
				$this->_e_css_src = array();
			break;


			case 'footer':
				if(true === $zone)
				{
					ksort($this->_runtime_footer, SORT_NUMERIC);
					foreach ($this->_runtime_footer as $priority => $path_array)
					{
						$this->renderFile($path_array, $external, 'Footer JS include - priority #'.$priority, $mod);
					}
					$this->_runtime_footer = array();
				}
				else
				{
					$this->renderFile(vartrue($this->_runtime_footer[$zone], array()), $external, 'Footer JS include - priority #'.$zone, $mod);
					unset($this->_runtime_footer[$zone]);
				}
			break;

			case 'header_inline':
				$this->renderInline(vartrue($this->_runtime_header_src[$zone], array()), 'Header JS - zone #'.$zone);
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
					$this->renderInline(vartrue($this->_runtime_footer_src[$zone], array()), 'Footer JS - priority #'.$zone);
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
	 * Merges multiple arrays, recursively, and returns the merged array.
	 */
	public function arrayMergeDeepArray($arrays) {
		$result = array();

		foreach ($arrays as $array) {
			foreach ($array as $key => $value) {
				// Renumber integer keys as array_merge_recursive() does. Note that PHP
				// automatically converts array keys that are integer strings (e.g., '1')
				// to integers.
				if (is_integer($key)) {
					$result[] = $value;
				}
				// Recurse when both values are arrays.
				elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
					$result[$key] = $this->arrayMergeDeepArray(array($result[$key], $value));
				}
				// Otherwise, use the latter value, overriding any previous value.
				else {
					$result[$key] = $value;
				}
			}
		}

		return $result;
	}


	/**
	 * Render JS/CSS file array
	 *
	 * @param array $file_path_array
	 * @param string|boolean $external if true - external js file calls, if js|css - external js|css file calls, else output file contents
	 * @param string $label added as comment if non-empty
	 * @return void
	 */
	public function renderFile($file_path_array, $external = false, $label = '', $mod = null, $checkModified = true)
	{
		if(empty($file_path_array))
		{
			return '';
		}


		$tp = e107::getParser();
		echo "\n";
		if($label && E107_DEBUG_LEVEL > 0) 
		{
			echo $external ? "<!-- [JSManager] ".$label." -->\n" : "/* [JSManager] ".$label." */\n\n";
			e107::getDb()->db_Mark_Time("Load JS/CSS: ".$label);
		}




		$lmodified = 0;
		foreach ($file_path_array as $path)
		{
            if (substr($path, - 4) == '.php')
            {
            	if('css' === $external)
				{
					$path = explode($this->_sep, $path, 4);
					$media = $path[0] ? $path[0] : 'all';
					// support of IE checks
					$pre = varset($path[2]) ? $path[2]."\n" : '';
					$post = varset($path[3]) ? "\n".$path[3] : '';
					$path = $path[1];
					if(strpos($path, 'http') !== 0)
					{
						$path = $tp->replaceConstants($path, 'abs').'?external=1'; // &amp;'.$this->getCacheId();
						$path = $this->url($path);
					}
					
					echo $pre.'<link rel="stylesheet" media="'.$media.'" type="text/css" href="'.$path.'" />'.$post;
					echo "\n";
				//	$this->cacheList['css'][] = $path;
					continue;
				}
				elseif($external) //true or 'js'
				{
					if(strpos($path, 'http') === 0 || strpos($path, '//') === 0) continue; // not allowed
					
					$path = explode($this->_sep, $path, 3);
					$pre = varset($path[1], '');
					if($pre) $pre .= "\n";
					$post = varset($path[2], '');
					if($post) $post = "\n".$post;
					$path = $path[0];
					
					$path = $tp->replaceConstants($path, 'abs').'?external=1'; // &amp;'.$this->getCacheId();
					$path = $this->url($path);
					echo $pre.'<script type="text/javascript" src="'.$path.'"></script>'.$post;
					echo "\n";
					continue;
				}

				$path = $tp->replaceConstants($path, '');
				if($checkModified) $lmodified = max($lmodified, filemtime($path));
                include_once($path);
                echo "\n";
            }
            else
            {
            	// CDN fix, ignore URLs inside consolidation script, render as external scripts
            	$isExternal = false;
				if(strpos($path, 'http') === 0 || strpos($path, '//') === 0)
				{
					if($external !== 'css') $isExternal = true;
				}





				            	
				if('css' === $external)
				{
					$path = explode($this->_sep, $path, 4);



					$media = $path[0];
					// support of IE checks
					$pre = varset($path[2]) ? $path[2]."\n" : '';
					$post = varset($path[3]) ? "\n".$path[3] : '';
					$path = $path[1];

					$insertID ='';

					// issue #3390 Fix for protocol-less path
					if(strpos($path, 'http') !== 0 && strpos($path, '//') !== 0) // local file.
					{

						if($label === 'Theme CSS') // add an id for local theme stylesheets. 
						{
							$insertID = 'id="stylesheet-'. eHelper::secureIdAttr(str_replace(array('{e_THEME}','.css'),'',$path)).'"' ;
						}

						if($this->addCache($external,$path) === true) // if cache enabled, then skip and continue.
						{
							continue;
						}
						$path = $tp->replaceConstants($path, 'abs'); // .'?'.$this->getCacheId();
						$path = $this->url($path);
					}
					elseif($this->isValidUrl($path) === false)
					{
						continue;
					}


					echo $pre.'<link '.$insertID.' rel="stylesheet" media="'.$media.'" property="stylesheet" type="text/css" href="'.$path.'" />'.$post;
					echo "\n";

					continue;
				}

				$path = explode($this->_sep, $path, 4);
				$pre = varset($path[1], '');
				if($pre) $pre .= "\n";
				$post = varset($path[2], '');
				if($post) $post = "\n".$post;
				$inline = isset($path[3]) ? $path[3] : '';
				if($inline) $inline = " ".$inline;
				$path = $path[0];

	            if(!$isExternal && $this->addCache('js',$path)===true)
	            {
		            continue;
	            }


            	if($external)
				{
					// Never use CacheID on a CDN script, always render if it's CDN
					if(!$isExternal) 
					{
						// don't render non CDN libs as external script calls when script consolidation is enabled
						if($mod === 'core' || $mod === 'plugin' || $mod === 'theme')
						{
							if(!e107::getPref('e_jslib_nocombine')) continue;
						}

						$path = $tp->replaceConstants($path, 'abs'); //.'?'.$this->getCacheId();
						$path = $this->url($path, 'js');
					}

					if($isExternal === true && $this->isValidUrl($path) == false)
					{
						continue;
					}

					echo $pre.'<script type="text/javascript" src="'.$path.'"'.$inline.'></script>'.$post;
					echo "\n";
					continue;
				}





				// never try to consolidate external scripts
				if($isExternal) continue;
				$path = $tp->replaceConstants($path, '');
				if($checkModified) $lmodified = max($lmodified, filemtime($path));
                echo file_get_contents($path);
                echo "\n";
            }
		}


		return $lmodified;
	}


	/**
	 * Return the URL while checking for staticUrl configuration.
	 * @param      $path
	 * @param bool $cacheId
	 * @return mixed|string
	 */
	private function url($path, $cacheId = true)
	{

		if(/*(e_MOD_REWRITE_STATIC === true || defined('e_HTTP_STATIC')) &&*/ $this->isInAdmin() !== true)
		{

		/*	$srch = array(
				e_PLUGIN_ABS,
				e_THEME_ABS,
				e_WEB_ABS
			);


			$http = deftrue('e_HTTP_STATIC', e_HTTP);

			$base = (e_MOD_REWRITE_STATIC === true) ? 'static/'.$this->getCacheId().'/' : '';

			$repl = array(
				$http.$base.e107::getFolder('plugins'),
				$http.$base.e107::getFolder('themes'),
				$http.$base.e107::getFolder('web')
			);

			$folder = str_replace($srch,$repl,$path);

			if(e_MOD_REWRITE_STATIC === true)
			{
				return trim($folder);
			}

			$path = $folder;*/

			$path = e107::getParser()->staticUrl($path);
		}


		if(!defined('e_HTTP_STATIC'))
		{
			if(strpos($path,'?')!==false)
			{
				$path .= "&amp;".$this->getCacheId();
			}
			else
			{
				$path .= "?".$this->getCacheId();
			}
		}

		return $path;

	}



	/**
	 * Check CDN Url is valid.
	 * Experimental.
	 * @param $url
	 * @return resource
	 */
	private function isValidUrl($url)
	{
		return true;
		/*


		$connected = e107::getFile()->isValidURL($url);

		if($connected == false)
		{
		//	echo "<br />Skipping: ".$url ." : ".$port;
			e107::getDebug()->log("Couldn't reach ".$url);
		}

		return $connected;
		*/
	}


	/**
	 * @param $type string css|js
	 * @param $path
	 * @return bool
	 */
	private function addCache($type,$path)
	{
		if($this->_cache_enabled != true  || $this->isInAdmin() || substr($path,0,2) == '//' || strpos($path, 'wysiwyg.php')!==false )
		{
			return false;
		}

		if(e_REQUEST_HTTP == e_ADMIN_ABS."menus.php") // disabled in menu-manager.
		{
			return false;
		}


		$localPath = e107::getParser()->replaceConstants($path);
		$this->_cache_list[$type][] = $localPath;

		return true;
	}




	/**
	 * Render Cached JS or CSS file.
	 * @param $type
	 */
	public function renderCached($type)
	{
		if($this->_cache_enabled != true || $this->isInAdmin() || deftrue('e_MENUMANAGER_ACTIVE'))
		{
			return false;
		}

		if(!empty($this->_cache_list[$type]))
		{
			$content = '';
			$cacheId = $this->getCacheFileId($this->_cache_list[$type]);

			$fileName = $cacheId.".".$type;
			$saveFilePath = e_WEB.'cache/'.$fileName;

			if(!is_readable($saveFilePath))
			{

				foreach($this->_cache_list[$type] as $k=>$path)
				{
					$content .= "/* File: ".str_replace("../",'',$path)." */\n";
					$content .= $this->getCacheFileContent($path, $type);
					$content .= "\n\n";
				}

				if(!@file_put_contents($saveFilePath, $content))
				{
					e107::getMessage()->addDebug("Couldn't save js/css cache file: ".$saveFilePath);
				}

			}

			echo "\n\n<!-- Cached ".$type." -->\n";

			if($type == 'js')
			{
				echo "<script type='text/javascript' src='".$this->url(e_WEB_ABS."cache/".$fileName,'js','cache')."'></script>\n\n";
			}
			else
			{
				echo "<link type='text/css' href='".$this->url(e_WEB_ABS."cache/".$fileName,'cache')."' rel='stylesheet' property='stylesheet'  />\n\n";
				if(!empty($this->_cache_list['css_inline']))
				{
					echo $this->_cache_list['css_inline'];
					unset($this->_cache_list['css_inline']);
				}
			}

			// Remove from list, anything we have added.
			foreach($this->_cache_list[$type] as $k=>$path)
			{
				unset($this->_cache_list[$type][$k]);
			}


		}



	}


	/**
	 * Get js/css file to be cached and update url links.
	 * @param $path string
	 * @param $type string (js|css)
	 * @return mixed|string
	 */
	private function getCacheFileContent($path, $type)
	{
		$content = @file_get_contents($path);

		if($type == 'js')
		{
			return $this->compress($content, 'js');
		}

		// Correct relative paths in css files.
		preg_match_all('/url\([\'"]?([^\'"\) ]*)[\'"]?\)/',$content, $match);
		$newpath = array();

		if(empty($match[0]))
		{
			return $this->compress($content, 'css');
		}

		$path = str_replace("../",'',$path);

		$basePath = dirname($path)."/";

		$tp = e107::getParser();

		foreach($match[1] as $k=>$v)
		{
			if(strpos($v,'data:') === 0 || strpos($v,'http') === 0)
			{
				unset($match[0][$k]);
				continue;
			}

			$http = $tp->staticUrl(null, array('full'=>1)); // returns SITEURL or Static URL if enabled.
			$path = $this->normalizePath($basePath.$v);
			$dir = "url(".$http.$path.")";

			$newpath[$k] = $dir;
		}

		$result = str_replace($match[0], $newpath, $content);

		return $this->compress($result, 'css');
	}


	/**
	 * Normalize a path.
	 * Replacement for realpath (move to core functions?)
	 * It will _only_ normalize the path and resolve indirections (.. and .)
	 * Normalization includes:
	 * - directiory separator is always /
	 * - there is never a trailing directory separator
	 * @param  $path
	 * @return String
	 */
	private function normalizePath($path)
	{
	    $parts = preg_split(":[\\\/]:", $path); // split on known directory separators

	    // resolve relative paths
	    for ($i = 0; $i < count($parts); $i +=1)
	    {
	        if ($parts[$i] === "..")   // resolve ..
	        {
	            if ($i === 0)
	            {
	                throw new Exception("Cannot resolve path, path seems invalid: `" . $path . "`");
	            }

	            unset($parts[$i - 1]);
	            unset($parts[$i]);
	            $parts = array_values($parts);
	            $i -= 2;
	        }
	        elseif ($parts[$i] === ".")   // resolve .
	        {
	            unset($parts[$i]);
	            $parts = array_values($parts);
	            $i -= 1;
	        }

	        if ($i > 0 && $parts[$i] === "")  // remove empty parts
	        {
	            unset($parts[$i]);
	            $parts = array_values($parts);
	        }
	    }

	    return implode("/", $parts);
	}



	/**
	 * Minify JS/CSS for output
	 * @param string $minify
	 * @param string $type (js|css)
	 * @return string
	 */
	private function compress($minify, $type = 'js' )
    {

        if($type == 'js')
        {
            return e107::minify($minify);
        }

		// css

		/* remove comments */
    	$minify = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $minify );

        /* remove tabs, spaces, newlines, etc. */
    	$minify = str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $minify );
		$minify = str_replace("}","} ",$minify);

        return $minify;
    }


	/**
	 * Generate a Cache-File ID from path list.
	 * @param array $paths
	 * @return string
	 */
	private function getCacheFileId($paths)
	{
		$id = '';
		foreach($paths as $p)
		{
			$id .= str_replace("../","",$p);
		}

		return hash('crc32', $id) ;

	}

	/**
	 * Render JS/CSS source array
	 *
	 * @param array $js_content_array
	 * @param string $label added as comment if non-empty
	 * @return void
	 */
	function renderInline($content_array, $label = '', $type = 'js')
	{
		if(empty($content_array))
		{
			return '';
		}

		$content_array = array_unique($content_array); //TODO quick fix, we need better control!
		echo "\n";

		$raw = array();

		if($type == 'js') // support for raw html as inline code. (eg. google/bing/yahoo analytics)
		{
			$script = array();
			foreach($content_array as $code)
			{
				$start = substr($code,0,7);
				if($start == '<script' || $start == '<iframe')
				{
					$raw[] = $code;	
				}
				else 
				{
					$script[] = $code;	
				}
			}
			
			$content_array = $script;
		}


		switch ($type)
		{
			case 'js':
				if($label && E107_DEBUG_LEVEL > 0) 
				{
					echo "<!-- [JSManager] ".$label." -->\n";
				}
				echo '<script type="text/javascript">';
				echo "\n//<![CDATA[\n";
				echo implode("\n\n", $content_array);
				echo "\n//]]>\n";
				echo '</script>';
				echo "\n";
				
				if(!empty($raw))
				{
					if($label) //TODO - print comments only if site debug is on
					{
						echo "\n<!-- [JSManager] (Raw) ".$label." -->\n";
					}
					echo implode("\n\n", $raw);	
					echo "\n\n";
				}
			break;

			case 'css':
				$text = '';
				if($label && E107_DEBUG_LEVEL > 0) 
				{
					$text .= "<!-- [CSSManager] ".$label." -->\n";
				}
				$text .= '<style rel="stylesheet" type="text/css" property="stylesheet">';
				$text .= implode("\n\n", $content_array);
				$text .= '</style>';
				$text .= "\n";

				if($this->_cache_enabled != true || $this->isInAdmin() || deftrue('e_MENUMANAGER_ACTIVE'))
				{
					echo $text;
				}
				else
				{
					$this->_cache_list['css_inline'] = $text;
				}


			break;
		}
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
		// XXX - USERTHEME is defined only on user session init
		return ($this->isInAdmin() ? e107::getPref('admintheme') : deftrue('USERTHEME', e107::getPref('sitetheme')));
	}

	/**
	 * Get browser cache id
	 *
	 * @return integer
	 */
	public function getCacheId()
	{
		return $this->_browser_cache_id;
	}

	/**
	 * Set browser cache id
	 *
	 * @return e_jsmanager
	 */
	public function setCacheId($cacheid)
	{
		$this->_browser_cache_id = intval($cacheid);
		return $this;
	}

	/**
	 * Set last modification timestamp for given namespace
	 *
	 * @param string $what
	 * @param integer $when [optional]
	 * @return e_jsmanager
	 */
	public function setLastModfied($what, $when = 0)
	{
		$this->_lastModified[$what] = $when;
		return $this;
	}

	/**
	 * Get last modification timestamp for given namespace
	 *
	 * @param string $what
	 * @return integer
	 */
	public function getLastModfied($what)
	{
		return (isset($this->_lastModified[$what]) ? $this->_lastModified[$what] : 0);
	}

	public function addLibPref($mod, $array_newlib)
	{

		if(!$array_newlib || !is_array($array_newlib))
		{
			return $this;
		}
		$core = e107::getConfig();
		$plugname = '';
		if(strpos($mod, 'plugin:') === 0)
		{
			$plugname = str_replace('plugin:', '', $mod);
			$mod = 'plugin';
		}

		switch($mod)
		{
			case 'core':
			case 'theme':
				$key = 'e_jslib_'.$mod;
			break;

			case 'plugin':
				$key = 'e_jslib_plugin/'.$plugname;
			break;

			default:
				return $this;
			break;
		}


		$libs = $core->getPref($key);
		if(!$libs) $libs = array();
		foreach ($array_newlib as $path => $location)
		{
			$path = trim($path, '/');

			if(!$path) continue;

			$newlocation = $location == 'all' || (varset($libs[$path]) && $libs[$path] != $location) ? 'all' : $location;
			$libs[$path] = $newlocation;
		}

		$core->setPref($key, $libs);
		return $this;
	}

	public function removeLibPref($mod, $array_removelib)
	{

		if(!$array_removelib || !is_array($array_removelib))
		{
			return $this;
		}
		$core = e107::getConfig();
		$plugname = '';
		if(strpos($mod, 'plugin:') === 0)
		{
			$plugname = str_replace('plugin:', '', $mod);
			$mod = 'plugin';
		}

		switch($mod)
		{
			case 'core':
			case 'theme':
				$key = 'e_jslib_'.$mod;
			break;

			case 'plugin':
				$key = 'e_jslib_plugin/'.$plugname;
			break;

			default:
				return $this;
			break;
		}


		$libs = $core->getPref($key);
		if(!$libs) $libs = array();
		foreach ($array_removelib as $path => $location)
		{
			$path = trim($path, '/');
			if(!$path) continue;

			unset($libs[$path]);
		}

		$core->setPref($key, $libs);
		return $this;
	}
	
	/**
	 * Get current object data
	 * @return array
	 */
	public function getData()
	{
		$data = get_class_vars(__CLASS__);
		unset($data['_instance'], $data['_in_admin']);
		$kdata = array_keys($data);
		$instance = self::getInstance();
		$data = array();
		foreach ($kdata as $prop) 
		{
			$data[$prop] = $this->$prop;
		}
		return $data;
	}
	
	/**
	 * Set all current object data
	 * @param $data
	 * @return e_jsmanager
	 */
	public function setData($data)
	{
		if(!is_array($data)) return $this;
		foreach ($data as $prop => $val) 
		{
			if('_instance' == $prop || '_in_admin' == $prop) continue;
			$this->$prop = $val;
		}
		return $this;
	}

}
