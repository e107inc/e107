<?php
/*
 * e107 website system
 * 
 * Copyright (c) 2001-2008 e107 Inc. (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $Source: /cvs_backup/e107_0.8/e107_handlers/js_manager.php,v $
 * $Revision: 1.1 $
 * $Date: 2009-09-28 19:17:59 $
 * $Author: secretr $
 * 
*/
global $pref, $eplug_admin, $THEME_JSLIB, $THEME_CORE_JSLIB;

class e_js_manager
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
	 * Singleton instance
	 * Allow class extends - override {@link getInstance()}
	 * 
	 * @var e_js_manager
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
	 * @return e_js_manager
	 */
	public static function getInstance()
	{
		if(null == self::$_instance)
		{
		    self::$_instance = new self();
		}
	  	return self::$_instance;
	}
	
	/**
	 * Get and parse core preference values
	 * @return void
	 */
	protected function _init()
	{
		$core_list = e107::getPref('e_jslib_core');
		if(!$core_list)
		{
			$core_list = array();
		}
	}
	
	/**
	 * Add Core JS library file(s) for inclusion from e_jslib routine
	 * 
	 * @param string|array $file_path relative to e107_files/jslib/ folder or array in format path - runtime location
	 * @param string $runtime_location where should be JS used
	 * @return e_js_manager
	 */
	public function coreLib($file_path, $runtime_location = 'front')
	{
		$this->addJs('core', $file_path, $runtime_location);	
		return $this;
	}
	
	/**
	 * Add Core JS library file(s) for inclusion from e_jslib routine
	 * 
	 * @param string $plugname
	 * @param string|array $file_path relative to e107_plugins/myplug/jslib/ folder or array in format path - runtime location
	 * @param string $runtime_location admin|front|all - where should be JS used
	 * @return e_js_manager
	 */
	public function pluginLib($plugname, $file_path, $runtime_location = 'front')
	{
		$this->addJs('plugin', array($plugname, $file_path), $runtime_location);		
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site header
	 * 
	 * @param string|array $file_path path shortcodes usage is prefered
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_js_manager
	 */
	public function headerFile($file_path, $zone = 5)
	{
		$this->addJs('header', $file_path, $zone);		
		return $this;
	}
	
	
	/**
	 * Add JS file(s) for inclusion in site footer
	 * 
	 * @param string|array $file_path path shortcodes usage is prefered
	 * @return e_js_manager
	 */
	public function footerFile($file_path)
	{
		$this->addJs('footer', $file_path);		
		return $this;
	}
	
	/**
	 * Add JS file(s) for inclusion in site header
	 * 
	 * @param string|array $js_content path shortcodes usage is prefered
	 * @param integer $zone 1-5 (see header.php)
	 * @return e_js_manager
	 */
	public function headerInline($js_content, $zone = 5)
	{
		$this->addJs('header_inline', $js_content, $zone);		
		return $this;
	}
	
	
	/**
	 * Add JS file(s) for inclusion in site footer
	 * 
	 * @param string|array $js_content path shortcodes usage is prefered
	 * @return e_js_manager
	 */
	public function footerInline($js_content)
	{
		$this->addJs('footer_inline', $js_content);		
		return $this;
	}
	
	/**
	 * Require JS file(s). Access could be changed to private soon so don't use this 
	 * directly. Use corresponding proxy methods instead 
	 * 
	 * @see requirePluginLib()
	 * @see requireCoreLib()
	 * @param string $type core|plugin - jslib.php, header|footer|header_inline|footer_inline - runtime
	 * @param string|array $file_path
	 * @param string|integer $runtime_location admin|front|all (jslib), 0-5 (runtime inclusion)
	 * @return 
	 */
	public function addJs($type, $file_path, $runtime_location = '')
	{
		if(is_array($file_path))
		{
			foreach ($file_path as $fp => $loc)
			{
				$this->requireLib($type, $fp, $loc);
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
				$file_path = '{e_PLUGIN}jslib/'.$file_path[0].'/'.trim($file_path[1], '/');
				$registry = &$this->_e_jslib_plugin;
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
				$registry = &$this->_runtime_footer_src;
				$runtime = true;
			break;
			
			case 'header_inline':
				$file_path = $tp->createConstants($file_path, 4);
				$zone = intval($runtime_location);
				if($zone > 5 || $zone < 1)
				{
					$zone = 5;
				}
				if(!isset($this->_runtime_header_src[$zone])) 
				{
					$this->_runtime_header_src[$zone] = array();
				}
				$registry = &$this->_runtime_header_src[$zone];
				$runtime = true;
			break;
			
			case 'footer_inline':
				$file_path = $tp->createConstants($file_path, 4);
				$registry = &$this->_runtime_footer;
				$runtime = true;
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
	 * @param string $what core|plugin|header|footer|header_inline|footer_inline
	 * @param integer $zone 1-5 - only needed when in 'header' and 'header_inline' render mode
	 * @return 
	 */
	public function renderJs($what, $zone = 5)
	{
		switch($what)
		{
			case 'core':
				$this->renderFile($this->_e_jslib_core);
				$this->_e_jslib_core = array();
			break;
		
			case 'plugin':
				$this->renderFile($this->_e_jslib_plugin);
				$this->_e_jslib_plugin = array();
			break;
			
			case 'header':
				$this->renderFile(varsettrue($this->_runtime_header[$zone], array()));
				unset($this->_runtime_header[$zone]);
			break;
			
			case 'footer':
				$this->renderInline($this->_runtime_footer);
				$this->_runtime_footer = array();
			break;
			
			case 'header_inline':
				$this->renderInline(varsettrue($this->_runtime_header_src[$zone], array()));
				unset($this->_runtime_header_src[$zone]);
			break;
			
			case 'footer_inline':
				$this->renderInline($this->_runtime_footer_src);
				$this->_runtime_footer_src = array();
			break;
		}
	}
	
	/**
	 * Render JS file array
	 * TODO - option to output <script src='$path'>
	 * 
	 * @param array $file_path_array
	 * @return 
	 */
	public function renderFile($file_path_array)
	{
		$tp = e107::getParser();
		echo "\n\n";
		foreach ($file_path_array as $path)
		{
            if (substr($path, - 4) == '.php')
            {
                include_once($tp->replaceConstants($text, ''));
                echo "\n\n";
            }
            else
            {
                echo file_get_contents($tp->replaceConstants($text, ''));
                echo "\n\n";
            }
		}
	}
	
	/**
	 * Render JS source array
	 * @param object $js_content_array
	 * @return 
	 */
	public function renderInline($js_content_array)
	{
		if(empty($js_content_array))
		{
			return '';
		}
		echo "\n\n";
		echo '<script type="text/javascript">';
		foreach ($file_path_array as $js_content)
		{
            echo $js_content;
            echo "\n\n";
		}
		echo '</script>';
		echo "\n\n";
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
	 * @return e_js_manager
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

	
}
