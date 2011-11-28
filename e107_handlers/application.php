<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Single Entry Point
 *
 * $URL$
 * $Id$
*/

/**
 * Currently this file contains all classes required for single entry point functionallity
 * They will be separated in different files in a proper way (soon)
 */

/**
 * e107 Front controller
 */
class eFront
{
	/**
	 * Singleton instance
	 * @var eFront
	 */
	private static $_instance;
	
	/**
	 * @var eDispatcher
	 */
	protected $_dispatcher;
	
	/**
	 * @var eRequest
	 */
	protected $_request;
	
	/**
	 * @var eRouter
	 */
	protected $_router;
	
	/**
	 * @var string path to file to include - the old deprecated way of delivering content
	 */
	protected static $_legacy = '';
	
	/**
	 * Constructor
	 */
	private function __construct()
	{
	}
	
	/**
	 * Cloning not allowed
	 *
	 */
	private function __clone()
	{
	}
	
	/**
	 * Singleton implementation
	 * @return eFront
	 */
	public static function instance()
	{
		if(null == self::$_instance)
		{
		    self::$_instance = new self();
		}
	  	return self::$_instance;
	}
	
	/**
	 * Dispatch
	 */
	public function dispatch(eRequest $request, eResponse $response, eDispatcher $dispatcher)
	{
		
		// set dispatched status false
		$request->setDispatched(false);

		$router = $this->getRouter();
		$router->route($request); // route current request

		$c = 0;
		// dispatch loop
		do 
		{
			$c++;
			if($c > 100)
			{
				throw new eException("Too much dispatch loops", 1);
			}
			
			$request->setDispatched(true);
			if((bool) self::isLegacy()) return; 
			
			try 
			{
				$dispatcher->dispatch($request, $response);
			}
			catch(eException $e)
			{
				echo /*$request->getRoute().' - '.*/$e->getMessage();
				exit;
			}
			
			// check forward to legacy module
			if(!$request->isDispatched())
			{
				$router->checkLegacy($request);
			}

			
		} while (!$request->isDispatched());
	}
	
	/**
	 * Dispatch
	 */
	public function run()
	{
		$request = new eRequest();
		$this->setRequest($request);
		
		$dispatcher = new eDispatcher();
		$this->setDispatcher($dispatcher);
		
		$router = new eRouter();
		$this->setRouter($router);
		
		$response = new eResponse();
		$this->setResponse($response);
		
		$this->dispatch($request, $response, $dispatcher);
	}
	
	/**
	 * Application instance (e107 class)
	 * @return e107
	 */
	public static function app()
	{
		return e107::getInstance();
	}
	
	/**
	 * Get dispatcher instance
	 * @return eDispatcher
	 */
	public function getDispatcher()
	{
		return $this->_dispatcher;
	}
	
	/**
	 * Set dispatcher
	 * @param eDispatcher $dispatcher
	 * @return eFront
	 */
	public function setDispatcher(eDispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
		return $this;
	}
	
	/**
	 * Get request instance
	 * @return eRequest
	 */
	public function getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Set request
	 * @param eRequest $request
	 * @return eFront
	 */
	public function setRequest(eRequest $request)
	{
		$this->_request = $request;
		return $this;
	}
	
	/**
	 * Get response instance
	 * @return eResponse
	 */
	public function getResponse()
	{
		return $this->_response;
	}
	
	/**
	 * Set response
	 * @param eResponse $response
	 * @return eFront
	 */
	public function setResponse(eResponse $response)
	{
		$this->_response = $response;
		return $this;
	}
	
	/**
	 * Get router instance
	 * @return eRouter
	 */
	public function getRouter()
	{
		return $this->_router;
	}
	
	/**
	 * Set router instance
	 * @return eFront
	 */
	public function setRouter(eRouter $router)
	{
		$this->_router = $router;
		return $this;
	}
	
	/**
	 * Set/get legacy status of the current request
	 * @param boolean $status
	 * @return boolean
	 */
	public static function isLegacy($status = null)
	{
		if(null !== $status) 
		{
			if($status[0] === '{')
			{
				$status = e107::getParser()->replaceConstants($status);
			} 
			self::$_legacy = $status;
		}
		return self::$_legacy;
	}
}

/**
 * e107 Dispatcher
 * It decides how to dispatch the request.
 */
class eDispatcher
{
	protected static $_configObjects = array();

	public function dispatch(eRequest $request = null, eResponse $response = null)
	{
		$controllerName = $request->getControllerName();
		$moduleName = $request->getModuleName();
		$className = $this->isDispatchable($request, false);
		
		
		// dispatch based on rule settings
		if(!$className)
		{
			throw new eException("Invalid controller '".$request->getControllerName()."'");
			
		}
		
		$controller = new $className($request, $response);
		if(!($controller instanceof eController))
		{
			throw new eException("Controller $controller is not an instance of eController");
		}
		
		$request->setDispatched(true);
		$actionName = $request->getActionMethodName();
		
		ob_start();
		
		$controller->dispatch($actionName);
		
		$content = ob_get_contents();
		ob_end_clean();
		
		$response->appendBody($content);
		unset($controller);
	}
	
	/**
	 * Get path to the e_url handler
	 * @param string $module 
	 * @param string $location plugin|core|override
	 * @param boolean $sc
	 * @return string path
	 */
	public static function getConfigPath($module, $location, $sc = false)
	{
		$tmp = explode('/', $location);
		$custom = '';
		$location = $tmp[0];
		if(isset($tmp[1]) && !empty($tmp[1])) 
		{
			$custom = $tmp[1].'/';
		}
		unset($tmp);
		if($module !== '*') $module .= '/';
		
		switch ($location) 
		{
			case 'plugin':
				if($custom) $custom = 'url/'.$custom;
				return $sc ? '{e_PLUGIN}'.$module.$custom.'e_url.php' : e_PLUGIN.$module.$custom.'e_url.php';
			break;
			
			//TODO - discuss
			case 'core':
				if($module === '*') return $sc ? '{e_CORE}url/' : e_CORE.'url/';
				return $sc ? '{e_CORE}url/'.$module.$custom.'e_url.php' : e_CORE.'url/'.$module.$custom.'e_url.php';
			break;
			
			// TODO - discuss
			case 'override':
				if($module === '*') return $sc ? '{e_CORE}override/url/'  : e_CORE.'override/url/' ;
				return $sc ? '{e_CORE}override/url/'.$module.$custom.'url.php'  : e_CORE.'override/url/'.$module.$custom.'e_url.php' ;
			break;
			
			default:
				return null;
			break;
		}
	}
	
	/**
	 * Get path to url configuration subfolders
	 * @param string $module 
	 * @param string $location plugin|core|override
	 * @param boolean $sc
	 * @return string path
	 */
	public static function getConfigLocationPath($module, $location, $sc = false)
	{
		switch ($location) 
		{
			case 'plugin':
				return $sc ? '{e_PLUGIN}'.$module.'/url/' : e_PLUGIN.$module.'/url/';
			break;
			
			//TODO - discuss
			case 'core':
				return $sc ? '{e_CORE}url/'.$module.'/' : e_CORE.'url/'.$module.'/';
			break;
			
			// TODO - discuss
			case 'override':
				return $sc ? '{e_CORE}override/url/'.$module.'/'  : e_CORE.'override/url/'.$module.'/';
			break;
			
			default:
				return null;
			break;
		}
	}
	
	/**
	 * Get dispatch system path
	 * @param string $location plugin|core|override
	 * @param string $plugin required only when $location is plugin
	 * @param boolean $sc
	 * @return string path
	 */
	public static function getDispatchLocationPath($location, $plugin = null, $sc = false)
	{	
		switch ($location) 
		{
			case 'plugin':
				if(!$plugin) return null;
				$location = $tmp[1];
				return $sc ? '{e_PLUGIN}'.$plugin.'/controllers/' : e_PLUGIN.$plugin.'/controllers/';
			break;
			
			case 'core':
				return $sc ? '{e_CORE}controllers/' : e_CORE.'controllers/';
			break;
			
			case 'override':
				return $sc ? '{e_CORE}override/controllers/' : e_CORE.'override/controllers/';
			break;
			
			default:
				return null;
			break;
		}
	}
	
	/**
	 * Get full dispatch system path
	 * @param string $module 
	 * @param string $location plugin|core|override
	 * @param boolean $sc
	 * @return string path
	 */
	public static function getDispatchPath($module, $location, $sc = false)
	{	
		switch ($location) 
		{
			case 'plugin':
				return $sc ? '{e_PLUGIN}'.$module.'/controllers/' : e_PLUGIN.$module.'/controllers/';
			break;
			
			case 'core':
				return $sc ? '{e_CORE}controllers/'.$module.'/' : e_CORE.'controllers/'.$module.'/';
			break;
			
			case 'override':
				return $sc ? '{e_CORE}override/controllers/'.$module.'/' : e_CORE.'override/controllers/'.$module.'/';
			break;
			
			default:
				return null;
			break;
		}
	}
	
	/**
	 * Get include path to a given module/controller
	 * 
	 * @param string $module valid module name
	 * @param string $controller controller name
	 * @param string $location core|plugin|override
	 * @param boolean $sc return relative (false) OR shortcode (true) path
	 * @return string controller path
	 */
	public static function getControllerPath($module, $controller, $location = null, $sc = false)
	{
		if(null === $location) $location = self::getDispatchLocation($module);
		
		return ($location ? self::getDispatchPath($module, $location, $sc).$controller.'.php': null);
	}
	
	/**
	 * Get class name of a given module/controller
	 * 
	 * @param string $module valid module name
	 * @param string $controllerName controller name
	 * @param string $location core|plugin|override
	 * @return string controller path
	 */
	public static function getControllerClass($module, $controllerName, $location = null)
	{
		if(null === $location) $location = self::getDispatchLocation($module);
		
		return ($location ? $location.'_'.$module.'_'.$controllerName.'_controller' : null);
	}
	

	/**
	 * Get controller object
	 * 
	 * @param eRequest $request
	 * @param boolean $checkOverride whether to check the override location
	 * @return eController null if not dispatchable
	 */
	public function getController(eRequest $request, $checkOverride = true)
	{
		$class_name = $this->isDispatchable($request, true, $checkOverride);
		if(!$class_name) return null;
		
		return new $class_name();
	}
	
	/**
	 * Check if given module/controller is dispatchable
	 * @param string $module valid module name
	 * @param string $controllerName controller name
	 * @param string $location core|plugin|override
	 * @param boolean $checkOverride whether to check the override location
	 * @return string class name OR false if not dispatchable
	 */
	public function isDispatchableModule($module, $controllerName, $location, $checkOverride = false)
	{
		$path = self::getControllerPath($module, $controllerName, 'override', false); 
		if($checkOverride || $location == 'override')
		{
			$class_name = self::getControllerClass($module, $controllerName, $location);
			if($class_name && !class_exists($class_name, false) && is_readable($path)) include_once($path);
			
			if($class_name && class_exists($class_name, false)) return $class_name;
		}
		
		if($location !== 'override')
		{
			$path = self::getControllerPath($module, $controllerName, $location, false); 
			
			$class_name = self::getControllerClass($module, $controllerName, $location);
			if(!$class_name) return false;
			
			if(!class_exists($class_name, false) && is_readable($path)) include_once($path);
			
			if(class_exists($class_name, false)) return $class_name;
		}
		return false;
	}
	
	/**
	 * Automated version of self::isDispatchableModule()
	 * @param eRequest $request 
	 * @param boolean $checkReflection deep check - proper subclassing, action
	 * @param boolean $checkOverride try override controller folder first
	 * @return mixed class name OR false if not dispatchable
	 */
	public function isDispatchable(eRequest $request, $checkReflection = false, $checkOverride = true)
	{
		$location = self::getDispatchLocation($request->getModule());
		
		$controllerName = $request->getControllerName();
		$moduleName = $request->getModuleName();
		
		// dispatch based on rule settings
		if($location)
		{
			$className = $this->isDispatchableModule($moduleName, $controllerName, $location, $checkOverride);
		}
		else 
		{
			# Disable plugin check for routes with no config info - prevent calling of non-installed plugins
			# We may allow this for plugins which don't have plugin.xml in the future 
			// $className = $this->isDispatchableModule($moduleName, $controllerName, 'plugin', $checkOverride);
			// if(!$className)  
			$className = $this->isDispatchableModule($moduleName, $controllerName, 'core', $checkOverride);
		}
		
		if(empty($className)) return false;
		elseif(!$checkReflection) return $className;
		
		$rfl = new ReflectionClass($className);
		$method = $request->getActionMethodName();
		if($rfl->isSubclassOf('eController') && $rfl->hasMethod($method) && $rfl->getMethod($method)->isPublic() && !$rfl->getMethod($method)->isStatic())
			return $className;
		
		return false;
	}
	
	/**
	 * Get class name of a given module config
	 * 
	 * @param string $module valid module name
	 * @param string $location core|plugin|override[/custom]
	 * @return string controller path
	 */
	public static function getConfigClassName($module, $location)
	{
		$tmp = explode('/', $location);
		$custom = '';
		$location = $tmp[0];
		if(isset($tmp[1]) && !empty($tmp[1])) 
		{
			$custom = $tmp[1].'_';
		}
		unset($tmp);
		$module .= '_';
		
		// we need to prepend location to avoid namespace colisions
		return $location.'_'.$module.$custom.'url';
	}
	
	/**
	 * Get config object for a module
	 * 
	 * @param string $module valid module name
	 * @param string $location core|plugin|override[/custom]
	 * @return eUrlConfig
	 */
	public static function getConfigObject($module, $location = null)
	{
		if(null === $location)
		{
			$location = self::getModuleLocation($module);
			if(!$location) return null;
		}
		$reg = $module.'/'.$location;
		if(isset(self::$_configObjects[$reg])) return self::$_configObjects[$reg];
		$className = self::getConfigClassName($module, $location); 
		if(!class_exists($className, false))
		{
			$path = self::getConfigPath($module, $location, false);
			if(!is_readable($path)) return null;
			include_once($path);
			
			if(!class_exists($className, false)) return null;
		}
		$obj = new $className();
		$obj->init();
		self::$_configObjects[$reg] = $obj;
		$obj = null;
		
		return self::$_configObjects[$reg];
	}
	
	/**
	 * Auto discover module location from stored in core prefs data
	 * @param string $module
	 */
	public static function getModuleLocation($module)
	{
		// FIXME - based on url_module detection - real location, not override!!!
		//retrieve from prefs
		return e107::findPref('url_config/'.$module, '');
	}
	
	/**
	 * Auto discover module location from stored in core prefs data
	 * @param string $module
	 */
	public static function getDispatchLocation($module)
	{
		//retrieve from prefs
		$location = self::getModuleLocation($module);
		if(!$location) return null;
		
		if(($pos = strpos($location, '/'))) //can't be 0
		{
			return substr($location, 0, $pos);
		}
		return $location;
	}
}

/**
 * URL manager - parse and create URLs based on rules set
 * Inspired by Yii Framework UrlManager <www.yiiframework.com>
 */
class eRouter
{
	/**
	 * Configuration array containing all available syste routes and route object configuration values
	 * @var array
	 */
	protected $_rules = array();
	
	/**
	 * List of all system wide available aliases
	 * This includes multi-lingual configurations as well
	 * @var array
	 */
	protected $_aliases = array();
	
	/**
	 * Cache for rule objects
	 * @var array
	 */
	protected $_parsedRules = array(); // array of rule objects
	
	/**
	 * Global config values per rule set
	 * @var array
	 */
	protected $_globalConfig = array(); 
	
	/**
	 * Module name which is used for site main namespace
	 * Example mysite.com/news/News Item => converted to mysite.com/News Item
	 * NOTE: Could be moved to rules config
	 * 
	 * @var string 
	 */
	protected $_mainNsModule = '';
	
	/**
	 * Default URL suffix - to be added to end of all urls (e.g. '.html')
	 * This value can be overridden per rule item
	 * NOTE could be moved to rules config only
	 * @var string
	 */
	public $urlSuffix = '';
	
	/**
	 * @var string  GET variable name for route. Defaults to 'route'.
	 */
	public $routeVar = 'route';
	
	/**
	 * @var string
	 */
	const FORMAT_GET = 'get';
	
	/**
	 * @var string
	 */
	const FORMAT_PATH = 'path';
	
	/**
	 * @var string
	 */
	private $_urlFormat = self::FORMAT_GET;
	
	/**
	 * Not found route
	 * @var string
	 */
	public $notFoundRoute = 'system/error/notfound';
	
	protected $_defaultAssembleOptions = array('full' => false, 'amp' => '&amp;', 'equal' => '=', 'encode' => true);
	
	/**
	 * Not found URL - used when system route not found and 'url_error_redirect' core pref is true
	 * TODO - user friendly URL ('/system/404') when system config is ready ('/system/404')
	 * @var string
	 */
	public $notFoundUrl = 'system/error/notfound?type=routeError';
	
	public function __construct()
	{
		$this->_init();
	}
	
	/**
	 * Init object
	 * @return void 
	 */
	protected function _init()
	{
		// Gather all rules, add-on info, cache, module for main namespace etc 
		$this->setMainModule(e107::getPref('url_main_module', ''));
		$this->_loadConfig()
			->setAliases();
	}
	
	/**
	 * Set module for default namespace
	 * @param string $module
	 * @return eRouter
	 */
	public function setMainModule($module)
	{
		if(!$module) return $this;
		
		$this->_mainNsModule = $module;
		return $this;
	}
	
	/**
	 * Get main url namespace module
	 */
	public function getMainModule()
	{
		return $this->_mainNsModule;
	}
	
	/**
	 * Check if given module is the main module
	 */
	public function isMainModule($module)
	{
		return ($this->_mainNsModule === $module);
	}
	
	
	/**
	 * @return string get|path
	 */
	public function getUrlFormat()
	{
		return $this->_urlFormat;
	}
	
	/**
	 * Load config and url rules, if not available - build it on the fly
	 * @return eRouter
	 */
	protected function _loadConfig()
	{
		if(!is_readable(e_SYSTEM.'url/config.php')) $config = $this->buildGlobalConfig();
		else $config = include(e_SYSTEM.'url/config.php');
		
		if(!$config) $config = array();
		
		$rules = array();
		
		foreach ($config as $module => $c) 
		{
			$rules[$module] = $c['rules'];
			unset($config[$module]['rules']);
			$config[$module] = $config[$module]['config'];
		}
		$this->_globalConfig = $config;
		$this->setRuleSets($rules); 
		
		return $this;
	}
	
	public static function clearCache()
	{
		@unlink(e_SYSTEM.'url/config.php');
	}
	
	/**
	 * Build unified config.php
	 */
	public function buildGlobalConfig($save = true)
	{
		$active = e107::getPref('url_config', array());
		
		$config = array();
		foreach ($active as $module => $location) 
		{
			$_config = array();
			$obj = eDispatcher::getConfigObject($module, $location);
			$path = eDispatcher::getConfigPath($module, $location, true);
			
			if(null !== $obj)
			{
				$_config = $obj->config();
				$_config['config']['configPath'] = $path;
				$_config['config']['configClass'] = eDispatcher::getConfigClassName($module, $location);
			}
			if(!isset($_config['config'])) $_config['config'] = array();
			
			$_config['config']['location'] = $location;
			if(!isset($_config['config']['format']) || !in_array($_config['config']['format'], array(self::FORMAT_GET, self::FORMAT_PATH)))
			{
				$_config['config']['format'] = self::FORMAT_GET;
			}

			if(!isset($_config['rules'])) $_config['rules'] = array();
			
			foreach ($_config['rules'] as $pattern => $rule) 
			{
				if(!is_array($rule))
				{
					$_config['rules'][$pattern] = array($rule);
				}
			}
			
			$config[$module] = $_config;
		}
		
		if($save)
		{
			$fileContent = '<?php'."\n### Auto-generated - DO NOT EDIT ### \nreturn ";
			$fileContent .= trim(var_export($config, true)).';';
			
			file_put_contents(e_SYSTEM.'url/config.php', $fileContent);
		}
		return $config;
	}

	
	/**
	 * Detect all available system url modules, used as a map on administration configuration path
	 * and required (same structure) {@link from eDispatcher::adminBuildConfig())
	 * This is a very liberal detection, as it doesn't require config file.
	 * It goes through both config and dispatch locations and registers directory tree as modules
	 * The only exception are plugins - if plugin requires install (plugin.xml) and it is not installed,
	 * it won't be registered
	 * Another important thing is - core has always higher priority, as plugins are not allowed to 
	 * directly override core modules. At this moment, core modules could be overloaded only via override configs (e107_core/override/url/) 
	 * and controllers (e107_core/override/controllers) 
	 * This array is stored as url_modules core preference 
	 * 
	 * @param string $type possible values are all|plugin|core|override
	 * @return array available system url modules stored as url_modules core preference
	 */	
	public static function adminReadModules($type = 'all')
	{
		$f = e107::getFile();
		$ret = array('core' => array(), 'plugin' => array(), 'override' => array());
		
		if($type == 'all' || $type = 'core')
		{
			$location = eDispatcher::getDispatchLocationPath('core');
			// search for controllers first
			$ret['core'] = $f->get_dirs($location);
			
			// merge with configs
			$configArray = $f->get_dirs(eDispatcher::getConfigPath('*', 'core'));
			foreach ($configArray as $config) 
			{
				if(!in_array($config, $ret['core']))
				{
					$ret['core'][] = $config;
				}
			}
			sort($ret['core']);
		}
		
		if($type == 'all' || $type = 'plugin')
		{
			$plugins = $f->get_dirs(e_PLUGIN);
			foreach ($plugins as $plugin) 
			{
				// DON'T ALLOW PLUGINS TO OVERRIDE CORE!!! 
				// This will be possible in the future under some other, more controllable form
				if(in_array($plugin, $ret['core'])) continue;
				
				$location = eDispatcher::getDispatchLocationPath('plugin', $plugin);
				$config = eDispatcher::getConfigPath($plugin, 'plugin');
				
				if(e107::isInstalled($plugin))
				{
					if(is_dir($location) || is_readable($config))
					{
						$ret['plugin'][] = $plugin;
					}
				}
				
				// Register only those who don't need install and may be dispatchable
				if((!is_readable(e_PLUGIN.$plugin.'/plugin.php') && !is_readable(e_PLUGIN.$plugin.'/plugin.xml')))
				{
					if(is_dir($location) || is_readable($config))
					{
						$ret['plugin'][] = $plugin;
					}
				}
			}
			sort($ret['plugin']);
		}
		
		if($type == 'all' || $type = 'override')
		{
			// search for controllers first
			$location = eDispatcher::getDispatchLocationPath('override');
			$ret['override'] = $f->get_dirs($location);
			
			// merge with configs
			$configArray = $f->get_dirs(eDispatcher::getConfigPath('*', 'override'));
			foreach ($configArray as $config) 
			{
				if(!in_array($config, $ret['override']))
				{
					$ret['override'][] = $config;
				}
			}
			sort($ret['override']);
		}
		
		return $ret;
	}

	/**
	 * Rebuild configuration array, stored as url_config core preference
	 * More strict detection compared to {@link eDispatcher::adminReadModules()}
	 * Current flat array containing config locations per module are rebuilt so that new 
	 * modules are registered, missing modules - removed. Additionally fallback to the default location 
	 * is done if current user defined location is not readable
	 * @see eDispatcher::adminReadModules()
	 * @param array current configuration array (url_config core preference like)
	 * @param array available URL modules as detected by {@link eDispatcher::adminReadModules()} and stored as url_modules core preference value
	 * @return array new url_config array
	 */
	public static function adminBuildConfig($current, $adminReadModules = null)
	{
		if(null === $adminReadModules) $adminReadModules = self::adminReadModules();
		
		$ret = array();
		$all = array_unique(array_merge($adminReadModules['core'], $adminReadModules['plugin'], $adminReadModules['override']));
		foreach ($all as $module) 
		{
			if(isset($current[$module]))
			{
				// current contains custom (readable) config location e.g. news => core/rewrite
				if(strpos($current[$module], '/') !== false && is_readable(eDispatcher::getConfigPath($module, $current[$module])))
				{
					$ret[$module] = $current[$module];
					continue;
				}
				
				// in all other cases additional re-check will be made - see below
			}
			
			if(in_array($module, $adminReadModules['override']))
			{
				// core check
				if(in_array($module, $adminReadModules['core']))
				{
					$mustHave = is_readable(eDispatcher::getConfigPath($module, 'core'));
					$has = is_readable(eDispatcher::getConfigPath($module, 'override'));
					
					// No matter if it must have, it has e_url config
					if($has) $ret[$module] = 'override';
					// It must have but it doesn't have e_url config, fallback
					elseif($mustHave && !$has) $ret[$module] = 'core';
					// Rest is always core as controller override is done on run time
					else $ret[$module] = 'core';
				}
				// plugin check
				elseif(in_array($module, $adminReadModules['plugin']))
				{
					$mustHave = is_readable(eDispatcher::getConfigPath($module, 'plugin'));
					$has = is_readable(eDispatcher::getConfigPath($module, 'override'));
					
					// No matter if it must have, it has e_url config
					if($has) $ret[$module] = 'override';
					// It must have but it doesn't have e_url config, fallback
					elseif($mustHave && !$has) $ret[$module] = 'plugin';
					// Rest is always plugin as config is most important, controller override check is done on run time
					else $ret[$module] = 'plugin';
				}
				// standalone override module
				else
				{
					$ret[$module] = 'override';
				}
				
			}
			// default core location 
			elseif(in_array($module, $adminReadModules['core']))
			{
				$ret[$module] = 'core';
			}
			// default plugin location
			elseif(in_array($module, $adminReadModules['plugin']))
			{
				$ret[$module] = 'plugin';
			}
		}
		return $ret;
	}

	/**
	 * Detect available config locations (readable check), based on available url_modules {@link eDispatcher::adminReadModules()} core preference arrays 
	 * Used to rebuild url_locations core preference value
	 * @see eDispatcher::adminBuildConfig()
	 * @see eDispatcher::adminReadModules()
	 * @param array $available {@link eDispatcher::adminReadModules()} stored as url_modules core preference
	 * @return array available config locations, stored as url_locations core preference
	 */
	public static function adminBuildLocations($available = null)
	{
		$ret = array();
		if(null === $available) $available = self::adminReadModules();
		
		$fl = e107::getFile();
		
		// Core
		foreach ($available['core'] as $module) 
		{
			// Default module
			$ret[$module] = array('core');
			
			// read sub-locations
			$path = eDispatcher::getConfigLocationPath($module, 'core');
			$sub = $fl->get_dirs($path);
			
			if($sub)
			{
				foreach ($sub as $moduleSub)
				{
					// auto-override: override available (controller or url config), check for config
					if(in_array($module, $available['override']) && is_readable(eDispatcher::getConfigPath($module, 'override/'.$moduleSub)))
					{
						$ret[$module][] = 'override/'.$moduleSub;
					}
					// no override available, register the core location
					elseif(is_readable(eDispatcher::getConfigPath($module, 'core/'.$moduleSub))) 
					{
						$ret[$module][] = 'core/'.$moduleSub;
					}
				} 
			}
		}
		
		
		// Plugins
		foreach ($available['plugin'] as $module) 
		{
			// Default module
			$ret[$module] = array('plugin');
			
			// read sub-locations
			$path = eDispatcher::getConfigLocationPath($module, 'plugin');
			$sub = $fl->get_dirs($path);
			
			if($sub)
			{
				foreach ($sub as $moduleSub)
				{
					// auto-override: override available (controller or url config), check for config
					if(in_array($module, $available['override']) && is_readable(eDispatcher::getConfigPath($module, 'override/'.$moduleSub)))
					{
						$ret[$module][] = 'override/'.$moduleSub;
					}
					// no override available, register the core location
					elseif(is_readable(eDispatcher::getConfigPath($module, 'plugin/'.$moduleSub))) 
					{
						$ret[$module][] = 'plugin/'.$moduleSub;
					}
				} 
			}
		}

		// Go through all overrides, register those who don't belong to core & plugins as standalone core modules
		foreach ($available['override'] as $module) 
		{
			// either it is a core/plugin module or e_url.php is not readable - continue
			if(in_array($module, $available['core']) || in_array($module, $available['plugin']))
			{
				continue;
			}
			
			// Default module
			$ret[$module] = array('override');
			
			// read sub-locations
			$path = eDispatcher::getConfigLocationPath($module, 'override');
			$sub = $fl->get_dirs($path);
			
			if($sub)
			{
				foreach ($sub as $moduleSub)
				{
					if(is_readable(eDispatcher::getConfigPath($module, 'override/'.$moduleSub))) 
					{
						$ret[$module][] = 'override/'.$moduleSub;
					}
				} 
			}
		}
		
		return $ret;
	}

	/**
	 * Match current aliases against currently available module and languages 
	 * @param array $currentAliases url_aliases core preference
	 * @param array $currentConfig url_config core preference
	 * @return array cleaned aliases
	 */
	public static function adminSyncAliases($currentAliases, $currentConfig)
	{
		if(empty($currentAliases)) return array();
		
		$modules = array_keys($currentConfig);
		
		// remove non existing languages
		$lng = e107::getLanguage();
		$lanList = $lng->installed();
		foreach ($currentAliases as $lanCode => $aliases) 
		{
			$lanName = $lng->convert($lanCode);
			if(!$lanName || !in_array($lanName, $lanList))
			{
				unset($currentAliases[$lanCode]);
				continue;
			}
			
			// remove non-existing modules
			foreach ($aliases as $alias => $module) 
			{
				if(!isset($currentConfig[$module])) unset($currentAliases[$lanCode][$alias]);
			}
		}
		
		return $currentAliases;
	}

	/**
	 * Retrieve global configuration array for a single or all modules
	 * @param string $module system module
	 * @return array configuration
	 */
	public function getConfig($module = null)
	{
		if(null === $module) return $this->_globalConfig;
		
		return isset($this->_globalConfig[$module]) ? $this->_globalConfig[$module] : array();	
	}
	
	/**
	 * Get system name of a module by its alias
	 * Returns null if $alias is not an existing alias
	 * @param string $alias
	 * @param string $lan optional language alias check. Example $lan = 'bg' (search for Bulgarian aliases)
	 * @return string module
	 */
	public function getModuleFromAlias($alias, $lan = null)
	{
		if($lan) return e107::findPref('url_aliases/'.$lan.'/'.$alias, null);
		return (isset($this->_aliases[$alias]) ? $this->_aliases[$alias] : null);
	}
	
	/**
	 * Get alias name for a module
	 * Returns null if module doesn't have an alias
	 * @param string $module
	 * @param string $lan optional language alias check. Example $lan = 'bg' (search for Bulgarian aliases)
	 * @return string alias 
	 */
	public function getAliasFromModule($module, $lan = null)
	{
		if($lan) 
		{
			$aliases = e107::findPref('url_aliases/'.$lan, array());
			return (in_array($module, $aliases) ? array_search($module, $aliases) : null);
		}
		return (in_array($module, $this->_aliases) ? array_search($module, $this->_aliases) : null);
	}
	
	/**
	 * Check if alias exists
	 * @param string $alias
	 * @param string $lan optional language alias. Example $lan = 'bg' (search for Bulgarian aliases)
	 * @return boolean
	 */
	public function isAlias($alias, $lan = null)
	{
		if($lan) 
		{
			$aliases = e107::findPref('url_aliases/'.$lan, array());
			return isset($aliases[$alias]);
		}
		return isset($this->_aliases[$alias]);
	}
	
	/**
	 * Check if there is an alias for provided module
	 * @param string $module
	 * @param string $lan optional language alias check. Example $lan = 'bg' (search for Bulgarian aliases)
	 * @return boolean
	 */
	public function hasAlias($module, $lan = null)
	{
		if($lan) 
		{
			$aliases = e107::findPref('url_aliases/'.$lan, array());
			return in_array($module, $aliases);
		}
		return in_array($module, $this->_aliases);
	}
	
	/**
	 * Get all available module aliases
	 * @param string $lan optional language alias check. Example $lan = 'bg' (search for Bulgarian aliases)
	 * @return array
	 */
	public function getAliases($lanCode = null)
	{
		if($lan) 
		{
			return e107::findPref('url_aliases/'.$lan, array());
		}
		return $this->_aliases;
	}
	
	/**
	 * Set module aliases
	 * @param array $aliases
	 * @return eRouter
	 */
	public function setAliases($aliases = null)
	{
		if(null === $aliases)
		{
			$lanCode = e107::getLanguage()->convert(e_LANGUAGE); 
			
			$aliases = e107::findPref('url_aliases/'.$lanCode, array());
			// __REMOVE__ Temporary test data
			/*
			$aliases = array(
							'Blog' => 'news',
							'People' => 'user',
							'Myplug' => 'test'
						);*/
			
		}
		$this->_aliases = $aliases;
		
		return $this;
	}
	
	/**
	 * Check if provided module is present in the rules config
	 * @param string module
	 * @return boolean
	 */
	public function isModule($module)
	{
		return isset($this->_globalConfig[$module]);
	}
	
	/**
	 * Check if the passed value is valid module or module alias, returns system module
	 * or null on failure
	 * @param string $module
	 * @param boolean $strict check for existence if true
	 * @return string module
	 */
	public function retrieveModule($module, $strict = true)
	{
		if($this->isAlias($module)) 
			$module = $this->getModuleFromAlias($module);
		
		if($strict && (!$module || !$this->isModule($module))) 
			return null;

		return $module;
	}
	
	/**
	 * Set rule config for this instance
	 * @param array $rules
	 * @return void
	 */
	public function setRuleSets($rules)
	{
		$this->_rules = $rules;
	}
	
	/**
	 * Retrieve rule set for a module
	 * @param string $module
	 */
	public function getRuleSet($module)
	{
		return (isset($this->_rules[$module]) ? $this->_rules[$module] : array());
	}
	
	/**
	 * Get all rule sets
	 */
	public function getRuleSets()
	{
		return $this->_rules;
	}
	
	/**
	 * Retrive array of eUrlRule objects for given module
	 */
	public function getRules($module)
	{
		return $this->_processRules($module);
	}
	
	/**
	 * Process rule set array, create rule objects
	 * TODO - rule cache
	 * @param string $module
	 * @return array processed rule set
	 */
	protected function _processRules($module)
	{
		if(!$this->isModule($module)) return array();
		
		if(!isset($this->_parsedRules[$module]))
		{
			$rules = $this->getRuleSet($module);
			$config = $this->getConfig($module);
			$this->_parsedRules[$module] = array();
			$map = array('urlSuffix' => 'urlSuffix', 'legacy' => 'legacy', 'legacyQuery' => 'legacyQuery', 'mapVars' => 'mapVars', 'allowVars' => 'allowVars');
			foreach ($rules as $pattern => $set) 
			{
				foreach ($map as $key => $value) 
				{
					if(!isset($set[$value]) && isset($config[$key]))
					{
						$set[$value] = $config[$key];
					}
				}
				$this->_parsedRules[$module][$pattern] = $this->createRule($set, $pattern);
			}
		}
		return $this->_parsedRules[$module];
	}

	/**
	 * Create rule object
	 * 
	 * @param string $route
	 * @param string|array $pattern
	 * @param boolean $cache
	 * @return eUrlRule
	 */
	protected function createRule($route, $pattern = null, $cache = false)
	{
		return new eUrlRule($route, $pattern, $cache);
	}

	/**
	 * Route current request
	 * @param eRequest $request
	 * @return boolean
	 */
	public function route(eRequest $request, $checkOnly = false)
	{
		$request->routed = false;
		
		if(isset($_GET[$this->routeVar]))
		{
			$rawPathInfo = $_GET[$this->routeVar];
			unset($_GET[$this->routeVar]);
			$this->_urlFormat = self::FORMAT_GET;
		}
		else 
		{
			$rawPathInfo = rawurldecode($request->getPathInfo());
			$this->_urlFormat = self::FORMAT_PATH;
		}
		
		// Switch to main url namespace
		if(!$rawPathInfo)
		{
			// XXX show site index page possible only when rewrite.php is moved to index.php
			// most probably we'll route to system/index/index where front page settings will be detected and front page will be rendered
		}
		
		// max number of parts is actually 4 - module/controller/action/[additional/pathinfo/vars], here for reference only
		$parts = $rawPathInfo ? explode('/', $rawPathInfo, 4) : array();
		
		// find module - check aliases
		$module = $this->retrieveModule($parts[0]);
		$mainSwitch = false;
		
		// no module found, switch to Main module (pref) if available
		if(null === $module && $this->getMainModule() && $this->isModule($this->getMainModule()))
		{
			$module = $this->getMainModule();
			$rawPathInfo = $module.'/'.$rawPathInfo;
			array_unshift($parts, $module);
			$mainSwitch = true;
		}
		
		$request->routePathInfo = $rawPathInfo;
		
		// valid module
		if(null !== $module)
		{
			// we have valid module
			$config = $this->getConfig($module); 
			
			// Don't allow single entry if required by module config
			if(vartrue($config['noSingleEntry']))
			{
				$request->setRoute($this->notFoundRoute); 
				$request->addRouteHistory($rawPathInfo);
				return false;
			}
			
			// URL format - the one set by current config overrides the auto-detection
			$format = isset($config['format']) && $config['format'] ? $config['format'] : $this->getUrlFormat();
			
			// set legacy state
			eFront::isLegacy(varset($config['legacy']));
			
			//remove leading module, unnecessary overhead while matching
			array_shift($parts);
			$rawPathInfo = $parts ? implode('/', $parts) : '';
			$pathInfo = $this->removeUrlSuffix($rawPathInfo, $this->urlSuffix);
			
			// retrieve rules if any and if needed
			$rules = $format == self::FORMAT_PATH ? $this->getRules($module) : array();
			
			// Further parsing may still be needed
			if(empty($rawPathInfo))
			{
				$rawPathInfo = $pathInfo;
			}
			
			// parse callback
			if(vartrue($config['selfParse']))
			{
				// controller/action[/additional/parms]
				
				$route = $this->configCallback($module, 'parse', array($rawPathInfo, $_GET, $request, $this, $config), $config['location']);
			}
			// default module route
			elseif($format == self::FORMAT_GET || !$rules)
			{
				$route = $pathInfo;
			}
			// rules available - try to match an Url Rule
			elseif($rules)
			{
				foreach ($rules as $rule) 
				{
					$route = $rule->parseUrl($this, $request, $pathInfo, $rawPathInfo);
					if($route !== false)
					{
						eFront::isLegacy($rule->legacy); // legacy include override
						
						if($rule->parseCallback)
						{
							$this->configCallback($module, $rule->parseCallback, array($request), $config['location']);
						}
						// parse legacy query string if any		
						if(null !== $rule->legacyQuery)
						{
							$obj = eDispatcher::getConfigObject($module, $config['location']);
							// eUrlConfig::legacyQueryString set as legacy string by default in eUrlConfig::legacy() method
							$vars = new e_vars($request->getRequestParams());
							$vars->module = $module;
							$vars->controller = $request->getController();
							$vars->action = $request->getAction();
							if($rule->allowVars)
							{
								foreach ($rule->allowVars as $key => $value) 
								{
									if(isset($_GET[$key]) && !$request->isRequestParam($key))
									{
										// sanitize
										$vars->$key = preg_replace('/[^\d\w]/', '', $_GET[$key]); 
									}
								}
							}
							$obj->legacyQueryString = e107::getParser()->simpleParse($rule->legacyQuery, $vars, '0');
							unset($vars, $obj);
						}
						break;
					}
				}
			}
			
			// append module to be registered in the request object
			if(false !== $route)
			{
				// don't modify  - request directly modified by config callback
				if(!$request->routed) 
				{
					if(eFront::isLegacy()) $this->configCallback($module, 'legacy', array($route, $request), $config['location']);
					$route = $module.'/'.$route;
				}
			}
			// No route found, we didn't switched to main module auto-magically
			elseif(!$mainSwitch && vartrue($config['errorRoute']))
			{
				$route = !$checkOnly ? $module.'/'.$config['errorRoute'] : false;
			}

		}

		// final fallback
		if(!$route)
		{
			if($request->routed)
			{
				$route = $request->getRoute();
			}
			
			if(!$route)
			{
				$route = $this->notFoundRoute;
				eFront::isLegacy(''); // reset legacy - not found route isn't legacy call
				if($checkOnly) return false;
				## Global redirect on error option
				if(e107::getPref('url_error_redirect', false) && $this->notFoundUrl)
				{
					$redirect = $this->assemble($this->notFoundUrl, '', 'encode=0&full=1');
					//echo $redirect; exit;
					e107::getRedirect()->redirect($redirect);
				}
			}
		}
		
		$request->setRoute($route);
		$request->addRouteHistory($route);
		return true;
	}

	/**
	 * And more BC
	 * Checks and does some addtional logic if registered module is of type legacy
	 * @param eRequest $request
	 * @return void
	 */
	public function checkLegacy(eRequest $request)
	{
		$module = $request->getModule();
		$rules = $this->getRules($module);
		
		// Simple match current request
		if($rules)
		{
			foreach ($rules as $value) 
			{
				$route = $rules->route;
				if($route == $request->getController().'/'.$request->getAction())
				{
					$config = $rules->getData();
				}
			}
		}
		else $config = $module ? $router->getConfig() : array();
		
		// Modify legacy query string. NOTE - parseCallback not called here - forwarding controller should set all request parameters proper!
		if(isset($config['legacyQuery']))
		{
			$obj = eDispatcher::getConfigObject($module, $config['location']);
			// eUrlConfig::legacyQueryString set as legacy string by default in eUrlConfig::legacy() method
			$vars = new e_vars($request->getRequestParams());
			$vars->module = $module;
			$vars->controller = $request->getController();
			$vars->action = $request->getAction();
			if(vartrue($config['allowVars']))
			{
				foreach ($config['allowVars'] as $key => $value) 
				{
					if(isset($_GET[$key]) && !$request->isRequestParam($key))
					{
						// sanitize
						$vars->$key = preg_replace('/[^\d\w]/', '', $_GET[$key]); 
					}
				}
			}
			$obj->legacyQueryString = e107::getParser()->simpleParse($config['legacyQuery'], $vars, '0');
			unset($vars, $obj);
		}
		
		if(vartrue($config['legacy'])) 
		{
			eFront::isLegacy($config['legacy']);
			$this->configCallback($module, 'legacy', array($request->getController().'/'.$request->getAction(), $request, 'dispatch'), $config['location']);
		}
	}

	/**
	 * Convenient way to call config methods
	 */
	public function configCallback($module, $callBack, $params, $location)
	{
		if(null == $location) $location = eDispatcher::getModuleLocation($module);
		if(!$module || !($obj = eDispatcher::getConfigObject($module, $location))) return false;
		
		return call_user_func_array(array($obj, $callBack), $params);
	}
	
	/**
	 * Assemble system URL 
	 * Examples:
	 * <?php
	 * $router->assemble('/'); // index page URL e.g. / or /site_folder/
	 * $router->assemble('news/view/item?id=1'); // depends on current news config, possible return value is /news/1
	 * $router->assemble('*', 'id=1'); // use current request info - /module/controller/action?id=1
	 * $router->assemble('* /* /newaction'); // (NO EMPTY SPACES) change only current action - /module/controller/newaction
	 * $newsItem = array('news_id' => 1, 'news_sef' => 'My Title', ...); // as retrieved from DB
	 * $router->assemble('news/view/item', $newsItem); // All unused key=>values will be removed and NOT appended as GET vars
	 * 
	 * @param string $route
	 * @param array $params
	 * @param array $options {@see eRouter::$_defaultAssembleOptions}
	 */
	public function assemble($route, $params = array(), $options = array())
	{
		// TODO - url options
		$request = eFront::instance()->getRequest();
		if(is_string($options)) parse_str($options, $options);
		$options = array_merge($this->_defaultAssembleOptions, $options);
		$base = ($options['full'] ? SITEURLBASE : '').$request->getBasePath();
		
		$anc = '';
		
		if(is_string($params)) parse_str($params, $params);
		if(isset($params['#']))
		{
			$anc = '#'.$params['#'];
			usnet($params['#']);
		}
		
		# Optional convenient masks for creating system URL's
		if($route === '/' || empty($route))
		{
			if($params) 
			{
				$params = $this->createPathInfo($params, $options);
				return $base.'?'.$params;
			}
			return $base;
		}
		elseif(strpos($route, '?') !== false)
		{
			$tmp = explode('?', $route, 2);
			$route = $tmp[0];
			parse_str($tmp[1], $params);
			unset($tmp);
		}
		
		if($route === '*')
		{
			$route = $route = explode('/', $request->getRoute());
		}
		elseif(strpos($route, '*') !== false)
		{
			$route = explode('/', $route, 3);
			if($route[0] === '*') $route[0] = $request->getModule();
			if(isset($route[1]) && $route[1] === '*') $route[1] = $request->getController();
		}
		else 
		{
			$route = explode('/', $route, 3);
		}
		
		// we don't know anything about this route, just build it blind
		if(!$this->isModule($route[0]))
		{
			if($params) 
			{
				$params = $this->createPathInfo($params, $options);
				return $base.implode('/', $route).'?'.$params;
			}
			return $base.implode('/', $route);
		}
		
		# fill in index when needed
		switch (count($route)) 
		{
			case 1:
				$route[1] = 'index';
				$route[2] = 'index';
			break;
			case 2:
				$route[2] = 'index';
			break;
		}
		
		# aliases
		$module = $route[0];
		$config = $this->getConfig($module);

		$alias = $this->hasAlias($module, vartrue($options['lan'], null)) ? $this->getAliasFromModule($module, vartrue($options['lan'], null)) : $module;
		$route[0] = $alias;
		
		$format = isset($config['format']) && $config['format'] ? $config['format'] : self::FORMAT_GET;
		
		// Fix base url for legacy links
		if($config['noSingleEntry']) $base = $options['full'] ? SITEURL : e_HTTP;
		
		// TODO - main module - don't include it in the return URL
		
		// Create by config callback
		if(vartrue($config['selfCreate']))
		{
			$tmp = $this->configCallback($module, 'create', array(array($route[1], $route[2]), $params, $options), $config['location']); 
			
			if(empty($tmp)) return '#not-found';
			
			if(is_array($tmp))
			{
				$route = $tmp[0];
				$params = $tmp[1];
			
				if(!$this->isMainModule($module)) array_unshift($route, $alias);
				if($options['encode']) $route = array_map('rawurlencode', $route);
			}
			else 
			{	
				// relative url returned
				return $base.$tmp.$anc;
			}	
			unset($tmp);
			
			if($format === self::FORMAT_GET)
			{
				$params[$this->routeVar] = implode('/', $route);
				$route = array();
			}
			if($params) 
			{

				$params = $this->createPathInfo($params, $options);
				return $base.implode('/', $route).'?'.$params.$anc;
			}
			
			return $base.implode('/', $route).$anc;
		}
		
		
		// System URL create routine
		$rules = $this->getRules($module);
		if($format !== self::FORMAT_GET && !empty($rules))
		{
			foreach ($rules as $rule)
			{
				if (($url = $rule->createUrl($this, array($route[1], $route[2]), $params, $options)) !== false) return $base.($this->isMainModule($module) ? '' : $alias.'/').$url.$anc;
			}
		}

		// default - module/controller/action
		if($this->isMainModule($module)) unset($route[0]);
		if($route[2] == 'index')
		{
			unset($route[2]);
			if($route[1] == 'index') unset($route[1]);
		}
		
		# Modify params if required
		if($params) 
		{
			if($config['mapVars'])
			{
				foreach ($config['mapVars'] as $srcKey => $dstKey)
				{
					if (isset($params[$srcKey]))
					{
						$params[$dstKey] = $params[$srcKey];
						unset($params[$srcKey]);
					}
				}	
			}
			
			if($config['allowVars'])
			{
				$copy = $params;
				$params = array();
				foreach ($config['allowVars'] as $key)
				{
					$params[$key] = $copy[$key];
				}
				unset($copy);
			}
			
			if($format === self::FORMAT_GET)
			{
				$copy = $params;
				$params = array();
				$params[$this->routeVar] = implode('/', $route);
				foreach ($copy as $key => $value) 
				{
					$params[$key] = $value;
				}
				unset($copy);
				$route = array();
			}
			$params = $this->createPathInfo($params, $options);
			return $base.implode('/', $route).'?'.$params.$anc;
		}
		
		return $format === self::FORMAT_GET ? $base.'?'.$this->routeVar.'='.implode('/', $route).$anc : $base.implode('/', $route).$anc;
	}
	
	/**
	 * Alias of assemble()
	 */
	public function url($route, $params = array())
	{
		return $this->assemble($route, $params);
	}
	
	/**
	 * Creates a path info based on the given parameters.
	 * @param array $params list of GET parameters
	 * @param string $equal the separator between name and value
	 * @param string $ampersand the separator between name-value pairs
	 * @param boolean $encode apply rawurlencode to the key/value pairs
	 * @param string $key this is used internally for recursive calls
	 *
	 * @return string the created path info
	 */
	public function createPathInfo($params, $options, $key = null)
	{
		$pairs = array();
		$equal = $options['equal'];
		$encode = $options['encode'];
		$ampersand = !$encode && $options['amp'] == '&amp;' ? '&' : $options['amp'];
		foreach ($params as $k => $v)
		{
			if (null !== $key) $k = $key.'['.$k.']';

			if (is_array($v)) $pairs[] = $this->createPathInfo($v, $options, $k);
			else 
			{
				if($encode)
				{
					$k = rawurlencode($k);
					$v = rawurlencode($v);
				}
				$pairs[] = $k.$equal.$v;
			}
		}
		return implode($ampersand, $pairs);
	}
	
	/**
	 * Parses a path info into URL segments
	 * Be sure to not use non-unique chars for equal and ampersand signs, or you'll break your URLs
	 *
	 * @param eRequest $request
	 * @param string $pathInfo path info
	 * @param string $equal
	 * @param string $ampersand
	 */
	public function parsePathInfo($pathInfo, $equal = '/', $ampersand = '/')
	{
		if ('' === $pathInfo) return;
		
		if ($equal != $ampersand) $pathInfo = str_replace($equal, $ampersand, $pathInfo);
		$segs = explode($ampersand, $pathInfo.$ampersand);
		
		$segs = explode('/', $pathInfo);
		$ret = array();
		
		for ($i = 0, $n = count($segs); $i < $n - 1; $i += 2)
		{
			$key = $segs[$i];
			if ('' === $key) continue;
			$value = $segs[$i + 1]; 
			// array support
			if (($pos = strpos($key, '[')) !== false && ($pos2 = strpos($key, ']', $pos + 1)) !== false)
			{
				$name = substr($key, 0, $pos);
				// numerical array
				if ($pos2 === $pos + 1)
					$ret[$name][] = $value;
				// associative array
				else
				{
					$key = substr($key, $pos + 1, $pos2 - $pos - 1);
					$ret[$name][$key] = $value;
				}
			}
			else 
			{
				$ret[$key] = $value;
				
			}
		}
		return $ret;
	}
	
	/**
	 * Removes the URL suffix from path info.
	 * @param string $pathInfo path info part in the URL
	 * @param string $urlSuffix the URL suffix to be removed
	 *
	 * @return string path info with URL suffix removed.
	 */
	public function removeUrlSuffix($pathInfo, $urlSuffix)
	{
		if ('' !== $urlSuffix && substr($pathInfo, -strlen($urlSuffix)) === $urlSuffix) return substr($pathInfo, 0, -strlen($urlSuffix));
		else return $pathInfo;
	}
}

class eException extends Exception
{
	
}

/**
 * Based on Yii Framework UrlRule handler <www.yiiframework.com>
 */
class eUrlRule
{
	/**
	 *
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page.
	 * Defaults to null, meaning using the value of {@link cl_shop_core_url::urlSuffix}.
	 *
	 * @var string the URL suffix used for this rule.
	 */
	public $urlSuffix;

	/**
	 * When this rule is used to parse the incoming request, the values declared in this property
	 * will be injected into $_GET.
	 *
	 * @var array the default GET parameters (name=>value) that this rule provides.
	 */
	public $defaultParams = array();

	/**
	 * @var string module/controller/action
	 */
	public $route;

	/**
	 * @var array the mapping from route param name to token name (e.g. _r1=><1>)
	 */
	public $references = array();

	/**
	 * @var string the pattern used to match route
	 */
	public $routePattern;

	/**
	 * @var string regular expression used to parse a URL
	 */
	public $pattern;

	/**
	 * @var string template used to construct a URL
	 */
	public $template;

	/**
	 * @var array list of parameters (name=>regular expression)
	 */
	public $params = array();

	/**
	 * @var boolean whether the URL allows additional parameters at the end of the path info.
	 */
	public $append;
	
	/**
	 * @var array list of SourceKey=>DestinationKey associations
	 */
	public $mapVars = array();
	
	/**
	 * Numerical array of allowed parameter keys. If set, everything else will be wiped out from the passed parameter array
	 * @var array
	 */
	public $allowVars = array();
	
	/**
	 * Method member of module config object, to be called after successful request parsing
	 * @var string
	 */
	public $parseCallback;
	
	/**
	 * Shortcode path to the old entry point e.g. '{e_BASE}news.php'
	 * @var string
	 */
	public $legacy;
	
	/**
	 * Template used for automated recognition of legacy QueryString (parsed via simpleParser with values of retrieved requestParameters)
	 * @var string
	 */
	public $legacyQuery;

	/**
	 * Constructor.
	 * @param string $route the route of the URL (controller/action)
	 * @param string $pattern the pattern for matching the URL
	 */
	public function __construct($route, $pattern, $fromCache = false)
	{
		if (is_array($route))
		{
			if ($fromCache && !$pattern)
			{
				$this->setData($route);
				return;
			}
			
			$this->setData($route);
			if($this->defaultParams && is_string($this->defaultParams))
			{
				parse_str($this->defaultParams, $this->defaultParams);
			}
			$route = $this->route = $route[0];
		}
		else $this->route = $route;

		$tr2['/'] = $tr['/'] = '\\/';

		if (strpos($route, '<') !== false && preg_match_all('/<(\w+)>/', $route, $matches2))
		{
			foreach ($matches2[1] as $name) $this->references[$name] = "<$name>";
		}

		if (preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches))
		{
			$tokens = array_combine($matches[1], $matches[2]);
			foreach ($tokens as $name => $value)
			{
				if ($value === '') $value = '[^\/]+';
				$tr["<$name>"] = "(?P<$name>$value)";
				if (isset($this->references[$name])) $tr2["<$name>"] = $tr["<$name>"];
				else $this->params[$name] = $value;
			}
		}
		
		$p = rtrim($pattern, '*');
		$this->append = $p !== $pattern;
		$p = trim($p, '/');
		$this->template = preg_replace('/<(\w+):?.*?>/', '<$1>', $p);
		$this->pattern = '/^'.strtr($this->template, $tr).'\/?';
		if ($this->append) $this->pattern .= '/u';
		else $this->pattern .= '$/u';

		if ($this->references !== array()) $this->routePattern = '/^'.strtr($this->route, $tr2).'$/u';
	}

	public function getData()
	{
		$vars = array_keys(get_class_vars(__CLASS__));
		$data = array();
		foreach ($vars as $prop)
		{
			$data[$prop] = $this->$prop;
		}
		return $data;
	}

	protected function setData($data)
	{
		if (!is_array($data)) return;
		$vars = array_keys(get_class_vars(__CLASS__));
		
		foreach ($vars as $prop)
		{
			if (!isset($data[$prop])) continue;
			$this->$prop = $data[$prop];
		}
	}

	/**
	 * Creates a URL based on this rule.
	 * TODO - more clear logic and flexibility by building the query string
	 * 
	 * @param eRouter $manager the router/manager
	 * @param string $route the route
	 * @param array $params list of parameters
	 * @param array $options
	 * @return mixed the constructed URL or false on error
	 */
	public function createUrl($manager, $route, $params, $options)
	{
		$case = 'i';
		$ampersand = $options['amp'];
		$encode = vartrue($options['encode']);
		
		if(is_array($route)) $route = implode('/', $route);
		
		$tr = array();
		if ($route !== $this->route)
		{
			if ($this->routePattern !== null && preg_match($this->routePattern.$case, $route, $matches))
			{
				foreach ($this->references as $key => $name) $tr[$name] = $matches[$key];
			}
			else return false;
		}
		
		foreach ($this->mapVars as $srcKey => $dstKey)
		{
			if (isset($params[$srcKey])/* && !isset($params[$dstKey])*/)
			{
				$params[$dstKey] = $params[$srcKey];
				unset($params[$srcKey]);
			}
		}	
		
		// disallow everything but valid URL parameters
		if($this->allowVars === false) $this->allowVars = array_keys($this->params);
		
		if($this->allowVars)
		{
			$copy = $params;
			$params = array();
			$this->allowVars = array_merge($this->allowVars, array_keys($this->params));
			foreach ($this->allowVars as $key)
			{
				$params[$key] = $copy[$key];
			}
			unset($copy);
		}

		foreach ($this->defaultParams as $key => $value)
		{
			if (isset($params[$key]))
			{
				if ($params[$key] == $value) unset($params[$key]);
				else return false;
			}
		}

		foreach ($this->params as $key => $value) if (!isset($params[$key])) return false;

		foreach ($this->params as $key => $value)
		{
			$tr["<$key>"] = $params[$key];
			unset($params[$key]);
		}

		$suffix = $this->urlSuffix === null ? $manager->urlSuffix : $this->urlSuffix;

		$url = strtr($this->template, $tr);

		if (empty($params)) return $url !== '' ? $url.$suffix : $url;

		// apppend not supported, maybe in the future...?
		if ($this->append) $url .= '/'.$manager->createPathInfo($params, '/', '/').$suffix;
		else
		{
			if ($url !== '') $url = $url.$suffix;

			$options['equal'] = '=';
			$url .= '?'.$manager->createPathInfo($params, $options);
		}

		return $url;
	}

	/**
	 * Parases a URL based on this rule.
	 * @param eRouter $manager the router/URL manager
	 * @param eRequest $request the request object
	 * @param string $pathInfo path info part of the URL
	 * @param string $rawPathInfo path info that contains the potential URL suffix
	 * @return mixed the route that consists of the controller ID and action ID or false on error
	 */
	public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
	{
		$case = 'i';	# 'i' = insensitive
		
		if ($this->urlSuffix !== null)	$pathInfo = $manager->removeUrlSuffix($rawPathInfo, $this->urlSuffix);
		
		$pathInfo = rtrim($pathInfo, '/').'/';
		
		if (preg_match($this->pattern.$case, $pathInfo, $matches))
		{
			foreach ($this->defaultParams as $name => $value)
			{
				//if (!isset($_GET[$name])) $_REQUEST[$name] = $_GET[$name] = $value;
				if (!$request->isRequestParam($name)) $request->setRequestParam($name, $value);
			}
			$tr = array();
			foreach ($matches as $key => $value)
			{
				if (isset($this->references[$key])) $tr[$this->references[$key]] = $value;
				elseif (isset($this->params[$key])) 
				{
					//$_REQUEST[$key] = $_GET[$key] = $value;
					$request->setRequestParam($key, $value);
				}
			}
			
			if ($pathInfo !== $matches[0])	# Additional GET params exist
			{
				$manager->parsePathInfo($request, ltrim(substr($pathInfo, strlen($matches[0])), '/'));
			}
			
			return (null !== $this->routePattern ? strtr($this->route, $tr) : $this->route);
		}
		else return false;
	}
	
}

abstract class eUrlConfig
{
	/**
	 * Registered by parse method legacy query string
	 */
	public $legacyQueryString = null;
	
	/**
	 * User defined initialization
	 */
	public function init() {}
	
	/**
	 * Retrieve module config options (including url rules if any)
	 * Return array is called once and cached, so runtime changes are not an option
	 * @return array
	 */
	abstract public function config();
	
	/**
	 * Create URL callback, called only when config option selfParse is set to true
	 * Expected return array format:
	 * <code>
	 * array(
	 * 	array(part1, part2, part3),
	 * 	array(parm1 => val1, parm2 => val2),
	 * );
	 * </code>
	 * @param array $route parts
	 * @param array $params
	 * @return array|string numerical of type (routeParts, GET Params)| string route or false on error
	 */
	public function create($route, $params = array(), $options = array()) {}
	
	/**
	 * Parse URL callback, called only when config option selfCreate is set to true
	 * TODO - register variable eURLConfig::currentConfig while initializing the object, remove from method arguments 
	 * @param string $pathInfo
	 * @param array $params request parameters
	 * @param eRequest $request
	 * @param eRouter $router
	 * @param array $config
	 * @return string route or false on error
	 */
	public function parse($pathInfo, $params = array(), eRequest $request = null, eRouter $router = null, $config = array()) { return false; }
	
	/**
	 * Legacy callback, used called when config option legacy is not empty
	 * By default it sets legacy query string to $legacyQueryString value (normaly assigned inside of the parse method)
	 * @param string $resolvedRoute
	 * @param eRequest $request
	 * @param string $callType 'route' - called once, when parsing the request, 'dispatch' - called inside the dispatch loop (in case of controller _forward)
	 * @param void
	 */
	public function legacy($resolvedRoute, eRequest $request, $callType = 'route') 
	{
		if($this->legacyQueryString !== null) 
		{
			$request->setLegacyQstring($this->legacyQueryString);
			$request->setLegacyPage();
		}
	}
	
	/**
	 * Developed mainly for legacy modules.
	 * It should be manually triggered inside of old entry point. The idea is 
	 * to avoid multiple URL addresses having same content (bad SEO practice)
	 * FIXME - under construction
	 */
	public function forward() {}
	
	/**
	 * Admin interface callback, returns array with all required from administration data
	 * Return array structure:
	 * <code>
	 * <?php
	 * return array(
	 *   'labels' => array(
	 *   	'name' => 'Module name',
	 * 		'label' => 'Profile Label',
	 * 		'description' => 'Additional profile info, exmples etc.',
	 * 	 ),
	 * 	 'form' => array(), // awaiting future development
	 * 	 'callbacks' => array(), // awaiting future development
	 * );
	 * </code>
	 */
	public function admin() { return array(); }
	
	/**
	 * Admin submit hook
	 * FIXME - under construction
	 */
	public function submit() {}
	
	/**
	 * Admin interface help messages, labels and titles
	 * FIXME - under construction
	 */
	public function help() {}
	
	
}

/**
 * Controller base class, actions are extending it
 * 
 */
class eController
{
	protected $_request;
	protected $_response;
	
	public function __construct(eRequest $request, eResponse $response = null)
	{
		$this->setRequest($request)
			->setResponse($response)
			->init();
	}
	
	public function init() {}
	
	public function preAction() {}
	public function postAction() {}
	
	/**
	 * @param eRequest $request
	 * @return eController
	 */
	public function setRequest($request)
	{
		$this->_request = $request;
		return $this;
	}
	
	/**
	 * @return eRequest
	 */
	public function getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * @param eResponse $response
	 * @return eController
	 */
	public function setResponse($response)
	{
		$this->_response = $response;
		return $this;
	}
	
	/**
	 * @return eResponse
	 */
	public function getResponse()
	{
		return $this->_response;
	}
	
	public function addBody($content)
	{
		$this->getResponse()->appendBody($content);
		return $this;
	}
	
	public function addMetaDescription($description)
	{
		$this->getResponse()->addMetaDescription($description);
		return $this;
	}
	
	public function addTitle($title, $meta = true)
	{
		$this->getResponse()->appendTitle($title);
		if($meta) $this->addMetaTitle($title);
		return $this;
	}
	
	
	public function addMetaTitle($title)
	{
		$this->getResponse()->addMetaTitle($title);
		return $this;
	}
	
	public function dispatch($actionMethodName)
	{
		$this->preAction();
		$request = $this->getRequest();
		
		if($request->isDispatched())
		{
			// more legacy :/
			$request->setLegacyQstring();
			$request->setLegacyPage();
			
			if(method_exists($this, $actionMethodName)) 
			{
				$this->$actionMethodName();
				$this->postAction();
			}
			else 
			{
				//TODO not found method by controller or default one
				$action = substr($actionMethodName, 6);
				throw new eException('Action "'.$action.'" does not exist');
			}
		}
	}
	
	public function run(eRequest $request = null, eResponse $response = null)
	{
		if(null === $request) $request = $this->getRequest();
		else $this->setRequest($request);
		
		if(null === $response) $response = $this->getResponse();
		else $this->setResponse($response);
		
		$action = $request->getActionMethodName();
		
		$request->setDispatched(true);
		$this->dispatch($action);
		
		return $this->getResponse();
	}
	
	protected function _redirect($url, $createURL = false, $code = null)
	{
		$redirect = e107::getRedirect();
		if($createURL)
		{
			$url = eFront::instance()->getRouter()->assemble($url, '', 'encode=0');
		}
		if(strpos($url, 'http://') !== 0 && strpos($url, 'http://') !== 0)
		{
			$url = $url[0] == '/' ? SITEURLBASE.$url : SITEURL.$url;
		}
		$redirect->redirect($url, true, $code);
	}
	
	protected function _forward($route, $params = array())
	{
		$request = $this->getRequest();
		
		if(is_string($params))
		{
			parse_str($params, $params);
		}
		
		$oldRoute = $request->getRoute();
		$route = explode('/', trim($route, '/'));
		
		switch (count($route)) {
			case 3:
				if($route[2] !== '*') $request->setModule($route[2]);
				if($route[1] !== '*') $request->setController($route[1]);
				$request->setAction($route[0]);
			break;
			
			case 2:
				if($route[1] !== '*') $request->setController($route[1]);
				$request->setAction($route[0]);
			break;
			
			case 1:
				$request->setAction($route[0]);
			break;
			
			default:
				return;
			break;
		}
		
		$request->addRouteHistory($oldRoute);
		
		if($params) $request->setRequestParams($params);
		$request->setDispatched(false);
	}
	
    /**
     * @param  string $methodName
     * @param  array $args
     * @return void
     * @throws eException
     */
    public function __call($methodName, $args)
    {
        if ('action' == substr($methodName, 0, 6)) 
        {
            $action = substr($methodName, 6);
            throw new eException('Action "'.$action.'" does not exist', 2404);
        }

        throw new eException('Method "'.$methodName.'" does not exist', 3404);
    }
}

/**
 * Request handler
 * 
 */
class eRequest
{
	/**
	 * @var string
	 */
	protected $_module;
	
	/**
	 * @var string
	 */
	protected $_controller;
	
	/**
	 * @var string
	 */
	protected $_action;
	
	/**
	 * Request status
	 * @var boolean
	 */
	protected $_dispatched = false;
	
	/**
	 * @var array
	 */
	protected $_requestParams = array();
	
	/**
	 * @var string
	 */
	protected $_basePath;
	
	/**
	 * @var string
	 */
	protected $_pathInfo;
	
	/**
	 * Pathinfo string used for initial system routing
	 */
	public $routePathInfo;
	
	/**
	 * @var array
	 */
	protected $_routeHistory = array();
	
	/**
	 * @var boolean if request is already routed - generally set by callbacks to notify router about route changes
	 */
	public $routed = false;
	
	/**
	 * Name of the bootstrap file
	 * @var string
	 */
	public $singleEntry = 'rewrite.php';

	/**
	 * Request constructor
	 */
	public function __construct($route = null)
	{
		if(null !== $route) 
		{
			$this->setRoute($route);
			$this->routed = true;
		}
	}

	/**
	 * Get system base path
	 * @return string
	 */
	public function getBasePath()
	{
		if(null == $this->_basePath) 
		{
			$this->_basePath = e_HTTP;
			if(!e107::getPref('url_disable_pathinfo')) $this->_basePath .= $this->singleEntry.'/';
		}
		
		return $this->_basePath;
	}
	
	/**
	 * Set system base path
	 * @param string $basePath
	 * @return eRequest
	 */
	public function setBasePath($basePath)
	{
		$this->_basePath = $basePath;
		return $this;
	}
	
	/**
	 * Get path info
	 * If not set, it'll be auto-retrieved
	 * @return string path info
	 */
	public function getPathInfo()
	{
		if(null == $this->_pathInfo)
		{

			if($this->getBasePath() == e_REQUEST_HTTP) 
				$this->_pathInfo = ''; // map to indexRoute
				
			else 
				$this->_pathInfo = substr(e_REQUEST_HTTP, strlen($this->getBasePath()));
		}
		
		return $this->_pathInfo;
	}
	
	/**
	 * Override path info
	 * @param string $pathInfo
	 * @return eRequest
	 */
	public function setPathInfo($pathInfo)
	{
		$this->_pathInfo = $pathInfo;
		return $this;
	}
	
	/**
	 * Get current controller string
	 * @return string
	 */
	public function getController()
	{
		return $this->_controller;
	}
	
	/**
	 * Get current controller name
	 * Example: requested controller-name or 'controller name' -> converted to controller_name
	 * @return string
	 */
	public function getControllerName()
	{
		return eHelper::underscore($this->_controller);
	}
	
	/**
	 * Set current controller name
	 * Example: controller_name OR 'controller name' -> converted to controller-name
	 * Always sanitized
	 * @param string $controller
	 * @return eRequest
	 */
	public function setController($controller)
	{
		$this->_controller = strtolower(eHelper::dasherize($this->sanitize($controller)));
		return $this;
	}
	
	/**
	 * Get current module string
	 * @return string
	 */
	public function getModule()
	{
		return $this->_module;
	}
	
	/**
	 * Get current module name
	 * Example: module-name OR 'module name' -> converted to module_name
	 * @return string
	 */
	public function getModuleName()
	{
		return eHelper::underscore($this->_module);
	}
	
	/**
	 * Set current module name
	 * Example: module_name OR 'module name' -> converted to module-name
	 * Always sanitized
	 * @param string $module
	 * @return eRequest
	 */
	public function setModule($module)
	{
		$this->_module = strtolower(eHelper::dasherize($this->sanitize($module)));
		return $this;
	}
	
	/**
	 * Get current action string
	 * @return string
	 */
	public function getAction()
	{
		return $this->_action;
	}
	
	/**
	 * Get current action name
	 * Example: action-name OR 'action name' OR action_name -> converted to ActionName
	 * @return string
	 */
	public function getActionName()
	{
		return eHelper::camelize($this->_action, true);
	}
	
	/**
	 * Get current action method name
	 * Example: action-name OR 'action name' OR action_name -> converted to actionActionName
	 * @return string
	 */
	public function getActionMethodName()
	{
		return 'action'.eHelper::camelize($this->_action, true);
	}
	
	/**
	 * Set current action name
	 * Example: action_name OR 'action name' OR Action_Name OR 'Action Name' -> converted to ation-name
	 * Always sanitized
	 * @param string $action
	 * @return eRequest
	 */
	public function setAction($action)
	{
		$this->_action = strtolower(eHelper::dasherize($this->sanitize($action)));
		return $this;
	}
	
	/**
	 * Get current route string/array -> module/controller/action
	 * @param boolean $array
	 * @return string|array route
	 */
	public function getRoute($array = false)
	{
		if(!$this->getModule()) 
		{
			$route = array('index', 'index', 'index');
		}	
		else
		{
			$route = array(
				$this->getModule(),
				$this->getController() ? $this->getController() : 'index',
				$this->getAction() ? $this->getAction() : 'index',
			);
		}
		return ($array ? $route : implode('/', $route));
	}
	
	/**
	 * Set current route
	 * @param string $route module/controller/action
	 * @return eRequest
	 */
	public function setRoute($route)
	{
		return $this->initFromRoute($route);
	}
	
	/**
	 * System routing track, used in controllers forwarder
	 * @param string $route
	 */
	public function addRouteHistory($route)
	{
		$this->_routeHistory[] = $route;
	}
	
	/**
	 * Retrieve route from history track
	 * Based on $source we can retrieve 
	 * - array of all history records
	 * - 'first' route record
	 * - 'last' route record
	 * - history record by its index number
	 * @param mixed $source
	 * @return string|array
	 */
	public function getRouteHistory($source = null)
	{
		if(null === $source) return $this->_routeHistory;
		
		if(!$this->_routeHistory) return null;
		elseif('last' === $source)
		{
			return $this->_routeHistory[count($this->_routeHistory) -1]; 
		}
		elseif('first' === $source)
		{
			return $this->_routeHistory[0]; 
		}
		elseif(is_int($source))
		{
			return isset($this->_routeHistory[$source]) ? $this->_routeHistory[$source] : null;
		}
		return null;
	}
	
	/**
	 * Search route history for the given $route
	 * 
	 * @param string $route
	 * @return integer route index or false if not found
	 */
	public function findRouteHistory($route)
	{
		return array_search($route, $this->_routeHistory);
	}
	
	/**
	 * Populate module, controller and action from route string
	 * @param string $route
	 * @return array route data
	 */
	public function initFromRoute($route)
	{
		$route = trim($route, '/');
		if(!$route) 
		{
			$route = 'index/index/index';
		}		
		$parts = explode('/', $route);
		$this->setModule($parts[0])
			->setController(vartrue($parts[1], 'index'))
			->setAction(vartrue($parts[2], 'index'));
			
		return $this->getRoute(true);
	}

	/**
	 * Get request parameter
	 * @param string $key
	 * @param string $default value if key not set
	 * @return mixed value
	 */
	public function getRequestParam($key, $default = null)
	{
		return (isset($this->_requestParams[$key]) ? $this->_requestParams[$key] : $default);
	}
	
	/**
	 * Check if request parameter exists
	 * @param string $key
	 * @return boolean
	 */
	public function isRequestParam($key)
	{
		return isset($this->_requestParams[$key]);
	}
	
	/**
	 * Get request parameters array
	 * @return array value
	 */
	public function getRequestParams()
	{
		return $this->_requestParams;
	}
	
	/**
	 * Set request parameter
	 * @param string $key
	 * @param mixed $value
	 * @return eRequest
	 */
	public function setRequestParam($key, $value)
	{
		$this->_requestParams[$key] = $value;
		return $this;
	}
	
	/**
	 * Set request parameters
	 * @param array $params
	 * @return eRequest
	 */
	public function setRequestParams($params)
	{
		$this->_requestParams = $params;
		return $this;
	}
	
	/**
	 * Populate current request parameters (_GET scope)
	 * @return eRequest
	 */
	public function populateRequestParams()
	{
		$rp = $this->getRequestParams();
		foreach ($rp as $key => $value) 
		{
			$_GET[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * More BC
	 */
	public function setLegacyQstring($qstring = null)
	{
		if(defined('e_QUERY')) return;
		
		if(null === $qstring)
		{
			$qstring = self::getQueryString();
		}
		
		define("e_SELF", e_REQUEST_SELF);
		define("e_QUERY", $qstring);
		$_SERVER['QUERY_STRING'] = e_QUERY;	
	}
	
	/**
	 * And More BC :/
	 */
	public function setLegacyPage($page = null)
	{
		if(defined('e_PAGE')) return;
		if(null === $page)
		{
			$page = eFront::isLegacy();
		}
		if(!$page) 
		{
			define('e_PAGE', 'rewrite.php');
		}
		else define('e_PAGE', basename(str_replace(array('{', '}'), '/', $page)));
	}
	
	/**
	 * And More from the same - BC :/
	 */
	public static function getQueryString()
	{
		$qstring = '';
		if($_SERVER['QUERY_STRING'])
		{
			$qstring = str_replace(array('{', '}', '%7B', '%7b', '%7D', '%7d'), '', rawurldecode($_SERVER['QUERY_STRING']));
		}
		$qstring = str_replace('&', '&amp;', e107::getParser()->post_toForm($qstring));
		return $qstring;
	}
	
	/**
	 * Basic sanitize method for module, controller and action input values
	 * @param string $str string to be sanitized
	 * @param string $pattern optional replace pattern
	 * @param string $replace optional replace string, defaults to dash
	 */
	public function sanitize($str, $pattern='', $replace='-')
	{
		if (!$pattern) $pattern = '/[^\w\pL-]/u';
		
		return preg_replace($pattern, $replace, $str);
	}
	
	/**
	 * Set dispatched status of the request
	 * @param boolean $mod
	 * @return eRequest
	 */
	public function setDispatched($mod)
	{
		$this->_dispatched = $mod ? true : false;
		return $this;
	}
	
	/**
	 * Get dispatched status of the request
	 * @return boolean
	 */
	public function isDispatched()
	{
		return $this->_dispatched;
	}
}

class eResponse
{
	protected $_body = array('default' => '');
	protected $_title = array('default' => array());
	protected $_e_PAGETITLE = array();
	protected $_META_DESCRIPTION = array();
	protected $_META_KEYWORDS = array();
	protected $_render_mod = array('default' => 'default');
	protected $_meta_title_separator = ' - ';
	protected $_title_separator = ' &raquo; ';
	protected $_content_type = 'html';
	protected $_content_type_arr =  array(
		'html' => 'text/html',
		'css' => 'text/css',
		'xml' => 'text/xml',
		'json' => 'application/json',
		'js' => 'application/javascript',
		'rss' => 'application/rss+xml',
		'soap' => 'application/soap+xml',
	);
	
	protected $_params = array(
		'render' => true,
		'meta' => false,
	);
	
	public function setParam($key, $value)
	{
		$this->_params[$key] = $value;
		return $this;
	}
	
	public function setParams($params)
	{
		$this->_params = $params;
		return $this;
	}
	
	public function getParam($key, $default = null)
	{
		return (isset($this->_params[$key]) ? $this->_params[$key] : $default);
	}
	
	public function addContentType($typeName, $mediaType)
	{
		$this->_content_type_arr[$typeName] = $mediaType;
		return $this;
	}
	
	public function getContentType()
	{
		return $this->_content_type;
	}
	
	public function getContentMediaType($typeName)
	{
		if(isset($this->_content_type_arr[$typeName]))
			return $this->_content_type_arr[$typeName];
	}
	
	public function setContentType($typeName)
	{
		$this->_content_type = $typeName;
	}
	
	public function sendContentType()
	{
		$ctypeStr = $this->getContentMediaType($this->getContentType());
		if($ctypeStr)
		{
			header('Content-type: '.$this->getContentMediaType($this->getContentType()).'; charset=utf-8', TRUE);
		}
		return $this;
	}
	
	/**
	 * Append content
	 * @param str $body
	 * @param str $ns namespace
	 * @return eResponse
	 */
	public function appendBody($body, $ns = 'default')
	{
		if(!isset($this->_body[$ns]))
		{
			$this->_body[$ns] = '';
		}
		$this->_body[$ns] .= $body;
		
		return $this;
	}
	
	/**
	 * Set content
	 * @param str $body
	 * @param str $ns namespace
	 * @return eResponse
	 */
	public function setBody($body, $ns = 'default')
	{
		$this->_body[$ns] = $body;
		return $this;
	}
	
	/**
	 * Prepend content
	 * @param str $body
	 * @param str $ns namespace
	 * @return eResponse
	 */
	function prependBody($body, $ns = 'default')
	{
		if(!isset($this->_body[$ns]))
		{
			$this->_body[$ns] = '';
		}
		$this->_body[$ns] = $content.$this->_body[$ns];
		
		return $this;
	}
	
	/**
	 * Get content
	 * @param str $ns
	 * @return string
	 */
	public function getBody($ns = 'default')
	{
		if(!isset($this->_body[$ns]))
		{
			$this->_body[$ns] = '';
		}
		return $this->_body[$ns];
	}
	
	/**
	 * @param str $title
	 * @param str $ns
	 * @return eResponse
	 */
	function setTitle($title, $ns = 'default')
	{

		if(!is_string($ns) || empty($ns))
		{
			$this->_title['default'] = array((string) $title);
		}
		else
		{
			$this->_title[$ns] = array((string) $title);
		}
		return $this;
	}

	/**
	 * @param str $title
	 * @param str $ns
	 * @return eResponse
	 */
	function appendTitle($title, $ns = 'default')
	{
		if(empty($title))
		{
			return $this;
		}
		if(!is_string($ns) || empty($ns))
		{
			$ns = 'default';
		}
		elseif(!isset($this->_title[$ns]))
		{
			$this->_title[$ns] = array();
		}
		$this->_title[$ns][] = (string) $title;
		return $this;
	}

	/**
	 * @param str $title
	 * @param str $ns
	 * @return eResponse
	 */
	function prependTitle($title, $ns = 'default')
	{
		if(empty($title))
		{
			return $this;
		}
		if(!is_string($ns) || empty($ns))
		{
			$ns = 'default';
		}
		elseif(!isset($this->_title[$ns]))
		{
			$this->_title[$ns] = array();
		}
		array_unshift($this->_title[$ns], $title);
		return $this;
	}

	/**
	 * Assemble title
	 * @param str $ns
	 * @param bool $reset
	 */
	function getTitle($ns = 'default', $reset = false)
	{
		if(!is_string($ns) || empty($ns))
		{
			$ret = implode($this->_title_separator, $this->_title['default']);
			if($reset)
				$this->_title['default'] = '';
		}
		elseif(isset($this->_title[$ns]))
		{
			$ret = implode($this->_title_separator, $this->_title[$ns]);
			if($reset)
				unset($this->_title[$ns]);
		}
		else
		{
			$ret = '';
		}
		return $ret;
	}
	
	/**
	 *
	 * @param string $render_mod
	 * @param string $ns
	 * @return eResponse
	 */
	function setRenderMod($render_mod, $ns = 'default')
	{
		if(!is_string($ns) || empty($ns))
		{
			return $this;
		}
		$this->_render_mod[$ns] = (string) $ns;
		return $this;
	}

	/**
	 * Retrieve render mod
	 * @param string $ns
	 */
	function getRenderMod($ns = 'default')
	{
		if(!is_string($ns) || empty($ns))
		{
			$ns = 'default';
		}
		return vartrue($this->_render_mod[$ns], null);
	}

	/**
	 * Add meta title, description and keywords
	 *
	 * @param string $meta property name
	 * @param string $content meta content
	 * @return eResponse
	 */
	function addMetaData($meta, $content)
	{
		$meta = '_' . $meta;
		if(isset($this->$meta) && !empty($content))
		{
			$content = str_replace(array('&amp;', '"', "'"), array('&', '', ''), $content);
			$this->{$meta}[] = htmlspecialchars((string) $content, ENT_QUOTES, 'UTF-8');
		}
		return $this;
	}
	
	/**
	 * Get meta title, description and keywords
	 *
	 * @param string $meta property name
	 * @return string
	 */
	function getMetaData($meta, $separator = '')
	{
		$meta = '_' . $meta;
		if(isset($this->$meta) && !empty($this->$meta))
		{
			return implode($separator, $this->$meta);
		}
		return '';
	}

	/**
	 * @param string $title
	 * @return eResponse
	 */
	function addMetaTitle($title)
	{
		return $this->addMetaData('e_PAGETITLE', $title);
	}

	function getMetaTitle()
	{
		return $this->getMetaData('e_PAGETITLE', $this->_meta_title_separator);
	}

	/**
	 * @param string $description
	 * @return eResponse
	 */
	function addMetaDescription($description)
	{
		return $this->addMetaData('META_DESCRIPTION', $description);
	}

	function getMetaDescription()
	{
		return $this->getMetaData('META_DESCRIPTION');
	}

	/**
	 * @param string $keywords
	 * @return eResponse
	 */
	function addMetaKeywords($keywords)
	{
		return $this->addMetaData('META_KEYWORDS', $keywords);
	}

	function getMetaKeywords()
	{
		return $this->getMetaData('META_KEYWORDS', ',');
	}

	/**
	 * Send e107 meta-data
	 * @return eResponse
	 */
	function sendMeta()
	{
		//HEADERF already included or meta content already sent
		if(e_AJAX_REQUEST || defined('USER_AREA') || defined('e_PAGETITLE'))
			return $this;

		if(!defined('e_PAGETITLE') && !empty($this->_e_PAGETITLE))
		{
			define('e_PAGETITLE', $this->getMetaTitle());
		}
		if(!defined('META_DESCRIPTION') && !empty($this->_META_DESCRIPTION))
		{
			define('META_DESCRIPTION', $this->getMetaDescription());
		}
		if(!defined('META_KEYWORDS') && !empty($this->_META_KEYWORDS))
		{
			define('META_KEYWORDS', $this->getMetaKeywords());
		}
		return $this;
	}

	/**
	 * Send Response Output - default method
	 * TODO - ajax send, using js_manager
	 * @param string $ns namespace/segment
	 * @param bool $return
	 * @param bool $render_message append system messages
	 */
	function send($ns = null, $return = true, $render_message = true)
	{
		$content = $this->getBody($ns, true);
		$render = $this->getParam('render');
		$meta = $this->getParam('meta');
		
		$this->sendContentType();

		if($render_message)
		{
			$content = eMessage::getInstance()->render().$content;
		}

		if($meta)
		{
			$this->sendMeta();
		}

		//render disabled by the controller
		if(!$this->getRenderMod($ns))
		{
			$render = false;
		}

		if($render)
		{
			$render = e107::getRender();
			if($return)
			{
				return $render->tablerender($this->getTitle($ns, true), $content, $this->getRenderMod($ns), true);
			}
			else
			{
				$render->tablerender($this->getTitle($ns, true), $content, $this->getRenderMod($ns));
				return '';
			}
		}
		elseif($return)
		{
			return $content;
		}
		else
		{
			print $content;
			return '';
		}
	}
	
	/**
	 * JS manager 
	 * @return e_jsmanager
	 */
	function getJs()
	{
		return e107::getJs();
	}
}

/**
 * We move all generic helper functionallity here - a lot of candidates in e107 class
 * 
 */
class eHelper
{
	/**
	 * Return a memory value formatted helpfully
	 * $dp overrides the number of decimal places displayed - realistically, only 0..3 are sensible
	 * FIXME e107->parseMemorySize() START
	 * - move here all e107 class ban/ip related methods
	 * - out of (integer) range case?
	 * 32 bit systems range: -2147483648 to 2147483647
	 * 64 bit systems range: -9223372036854775808 9223372036854775807
	 * {@link http://www.php.net/intval}
	 * FIXME e107->parseMemorySize() END
	 *
	 * @param integer $size
	 * @param integer $dp
	 * @return string formatted size
	 */
	public static function parseMemorySize($size, $dp = 2)
	{
		if (!$size) { $size = 0; }
		if ($size < 4096)
		{	// Fairly arbitrary limit below which we always return number of bytes
			return number_format($size, 0).CORE_LAN_B;
		}

		$size = $size / 1024;
		$memunit = CORE_LAN_KB;

		if ($size > 1024)
		{ /* 1.002 mb, etc */
			$size = $size / 1024;
			$memunit = CORE_LAN_MB;
		}
		if ($size > 1024)
		{ /* show in GB if >1GB */
			$size = $size / 1024;
			$memunit = CORE_LAN_GB;
		}
		if ($size > 1024)
		{ /* show in TB if >1TB */
			$size = $size / 1024;
			$memunit = CORE_LAN_TB;
		}
		return (number_format($size, $dp).$memunit);
	}
	
	public static function camelize($str, $all = false, $space = '')
	{
		// clever recursion o.O
		if($all) return self::camelize('-'.$str, false, $space);
		
		$tmp = explode('-', str_replace('_', '-', strtolower($str)));
		return trim(implode($space, array_map('ucfirst', $tmp)), $space);
	}
	
	public static function labelize($str, $space = ' ')
	{
		return self::camelize($str, true, ' ');
	}
	
	public static function dasherize($str)
	{
		return str_replace(array('_', ' '), '-', $str);
	}

	public static function underscore($str)
	{
		return str_replace(array('-', ' '), '_', $str);
	}
	
	/**
	 * Parse generic shortcode parameter string
	 * Format expected: {SC=key=val&key1=val1...}
	 * Escape strings: \& => &
	 *
	 * @param string $parmstr
	 * @return array associative param array
	 */
	public static function scParams($parm)
	{
		if (!$parm) return array();
		if (!is_array($parm))
		{
			$parm = str_replace('\&', '%%__amp__%%', $parm);
			$parm = str_replace('&amp;', '&', $parm); // clean when it comes from the DB
			parse_str($parm, $parm);
			foreach ($parm as $k => $v)
			{
				$parm[str_replace('%%__amp__%%', '&', $k)] = str_replace('%%__amp__%%', '\&', $v);
			}
		}

		return $parm;
	}
	
	/**
	 * Parse shortcode parameter string of type 'dual parameters' - advanced, more complex and slower(!) case
	 * Format expected: {SC=name|key=val&key1=val1...}
	 * Escape strings: \| => | , \& => & and \&amp; => &amp;
	 * Return array is formatted like this: 
	 * 1 => string|array (depends on $name2array value) containing first set of parameters; 
	 * 2 => array containing second set of parameters;
	 * 3 => string containing second set of parameters;
	 * 
	 * @param string $parmstr
	 * @param boolean $first2array If true, first key (1) of the returned array will be parsed to array as well
	 * @return array 
	 */
	public static function scDualParams($parmstr, $first2array = false)
	{
		if (!$parmstr) return array(1 => '', 2 => array(), 3 => '');
		if (is_array($parmstr)) return $parmstr;

		$parmstr = str_replace('&amp;', '&', $parmstr); // clean when it comes from the DB
		$parm = explode('|', str_replace(array('\|', '\&amp;', '\&'), array('%%__pipe__%%', '%%__ampamp__%%', '%%__amp__%%'), $parmstr), 2);

		$multi = str_replace('%%__pipe__%%', '|', $parm[0]);
		if ($first2array)
		{
			parse_str($multi, $multi);
			foreach ($multi as $k => $v)
			{
				$multi[str_replace(array('%%__ampamp__%%', '%%__amp__%%'), array('&amp;', '&'), $k)] = str_replace(array('%%__ampamp__%%', '%%__amp__%%'), array('&amp;', '&'), $v);
			}
		}

		if (varset($parm[1]))
		{
			// second paramater as a string - allow to be further passed to shortcodes
			$parmstr = str_replace(array('%%__pipe__%%', '%%__ampamp__%%', '%%__amp__%%'), array('\|', '\&amp;', '\&'), $parm[1]);
			parse_str(str_replace('%%__pipe__%%', '|', $parm[1]), $params);
			foreach ($params as $k => $v)
			{
				$params[str_replace(array('%%__ampamp__%%', '%%__amp__%%'), array('&amp;', '&'), $k)] = str_replace(array('%%__ampamp__%%', '%%__amp__%%'), array('&amp;', '&'), $v);
			}
		}
		else
		{
			$parmstr = '';
			$params = array();
		}

		return array(1 => $multi, 2 => $params, 3 => $parmstr);
	}
}