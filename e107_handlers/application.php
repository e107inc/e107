<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/


/**
 * Class e_url
 * New v2.1.6
 */
class e_url
{

	private static $_instance;

	private $_request       = null;

	private $_config        = array();

	private $_include       = null;

	private $_rootnamespace = null;

	private $_alias         = array();

	private $_legacy        = array();

	private $_legacyAliases = array();


	/**
	 * e_url constructor.
	 */
	function __construct()
	{
		$this->_request         = (e_HTTP === '/') ? ltrim(e_REQUEST_URI,'/') : str_replace(e_HTTP,'', e_REQUEST_URI) ;

		$this->_config          = e107::getUrlConfig();
		$this->_alias           = e107::getPref('e_url_alias');

		$this->_rootnamespace   = e107::getPref('url_main_module');
		$this->_legacy          = e107::getPref('url_config');
		$this->_legacyAliases   = e107::getPref('url_aliases');


		$this->setRootNamespace();

	}

	/**
	 * Detect older e_url system.
	 * @return bool
	 */
	private function isLegacy()
	{

		$arr = (!empty($this->_legacyAliases[e_LAN])) ?  array_merge($this->_legacy,$this->_legacyAliases[e_LAN]) : $this->_legacy;

		$list = array_keys($arr);

		foreach($list as $leg)
		{
			if(strpos($this->_request,$leg.'/') === 0 || $this->_request === $leg)
			{
				return true;
			}

		}

		return false;
	}


	/**
	 * @return string
	 */
	public function getInclude()
	{
		return $this->_include;
	}



	private function setRootNamespace()
	{

		$plugs = array_keys($this->_config);

		if(!empty($this->_rootnamespace) && in_array($this->_rootnamespace,$plugs)) // Move rootnamespace check to the end of the list.
		{
			$v = $this->_config[$this->_rootnamespace];
			unset($this->_config[$this->_rootnamespace]);
			$this->_config[$this->_rootnamespace] = $v;
		}

	}


	public function run()
	{
		$pref = e107::getPref();
		$tp = e107::getParser();

		if(empty($this->_config) || empty($this->_request) || $this->_request === 'index.php' || $this->isLegacy() === true)
		{
			return false;
		}

		$replaceAlias = array('{alias}\/?','{alias}/?','{alias}\/','{alias}/',);

		foreach($this->_config as $plug=>$cfg)
		{
			if(empty($pref['e_url_list'][$plug])) // disabled.
			{
				e107::getDebug()->log('e_URL for <b>'.$plug.'</b> is disabled.');
				continue;
			}

			foreach($cfg as $k=>$v)
			{

				if(empty($v['regex']))
				{
				//	e107::getMessage()->addDebug("Skipping empty regex: <b>".$k."</b>");
					continue;
				}


				if(!empty($v['alias']))
				{
					$alias = (!empty($this->_alias[e_LAN][$plug][$k])) ? $this->_alias[e_LAN][$plug][$k] : $v['alias'];
				//	e107::getMessage()->addDebug("e_url alias found: <b>".$alias."</b>");
					if(!empty($this->_rootnamespace) && $this->_rootnamespace === $plug)
					{
						$v['regex'] = str_replace($replaceAlias, '', $v['regex']);
					}
					else
					{

						$v['regex'] = str_replace('{alias}', $alias, $v['regex']);
					}
				}


				$regex = '#'.$v['regex'].'#';

				if(empty($v['redirect']))
				{
					continue;
				}


				$newLocation = preg_replace($regex, $v['redirect'], $this->_request);

				if($newLocation != $this->_request)
				{
					$redirect = e107::getParser()->replaceConstants($newLocation);
					list($file,$query) = explode("?", $redirect,2);

					$get = array();
					if(!empty($query))
					{
						// issue #3171 fix double ampersand in case of wrong query definition
						$query = str_replace('&&', '&', $query);
						parse_str($query,$get);
					}


					foreach($get as $gk=>$gv)
					{
						$_GET[$gk] = $gv;
					}

					e107::getDebug()->log('e_URL in <b>'.$plug.'</b> with key: <b>'.$k.'</b> matched <b>'.$v['regex'].'</b> and included: <b>'.$file.'</b> with $_GET: '.print_a($_GET,true),1);

					if(file_exists($file))
					{
						define('e_CURRENT_PLUGIN', $plug);
						define('e_QUERY', str_replace('&&', '&', $query)); // do not add to e107_class.php
						define('e_URL_LEGACY', $redirect);

						$this->_include= $file;
						return true;
					//	exit;
					}
					elseif(getperms('0'))
					{

						echo "<div class='alert alert-warning'>";
						echo "<h3>SEF Debug Info</h3>";
						echo "File missing: ".$file;
						echo "<br />Matched key: <b>".$k."</b>";
						print_a($v);
						echo "</div>";

					}

				}
			}

		}




	}


	/**
	 * Singleton implementation
	 * @return e_url
	 */
	public static function instance()
	{
		if(null == self::$_instance)
		{
		    self::$_instance = new self();
		}
	  	return self::$_instance;
	}



}




 
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


	protected $_response;
	
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
	public function dispatch(eRequest $request = null, eResponse $response = null, eDispatcher $dispatcher = null)
	{
		if(null === $request)
		{
			if(null === $this->getRequest()) 
			{
				$request = new eRequest();
				$this->setRequest($request);
			}
			else $request = $this->getRequest();
		}
		elseif(null === $this->getRequest()) $this->setRequest($request);
		
		if(null === $response)
		{
			if(null === $this->getResponse()) 
			{
				$response = new eResponse();
				$this->setResponse($response);
			}
			else $response = $this->getResponse();
		}
		elseif(null === $this->getRequest()) $this->setRequest($request);
		
		
		if(null === $dispatcher)
		{
			if(null === $this->getDispatcher()) 
			{
				$dispatcher = new eDispatcher();
				$this->setDispatcher($dispatcher);
			}
			else $dispatcher = $this->getDispatcher();
		}
		elseif(null === $this->getDispatcher()) $this->setDispatcher($dispatcher);
		
		
		// set dispatched status true, required for checkLegacy()
		$request->setDispatched(true);

		$router = $this->getRouter();

		// If current request not already routed outside the dispatch method, route it
		if(!$request->routed) $router->route($request); 

		$c = 0;
		// dispatch loop
		do 
		{
			$c++;
			if($c > 100)
			{
				throw new eException("Too much dispatch loops", 1);
			}
			
			// dispatched status true on first loop
			$router->checkLegacy($request);
			
			// dispatched by default - don't allow legacy to alter dispatch status
			$request->setDispatched(true);
			
			// legacy mod - return control to the bootstrap
			if(self::isLegacy()) 
			{
				return; 
			}
			
			// for the good players - dispatch loop - no more BC!
			try 
			{
				$dispatcher->dispatch($request, $response);
			}
			catch(eException $e)
			{
				echo $request->getRoute().' - '.$e->getMessage();
				exit;
			}

			
		} while (!$request->isDispatched());
	}
	
	/**
	 * Init all objects required for request dispatching
	 * @return eFront
	 */
	public function init()
	{
		$request = new eRequest();
		$this->setRequest($request);
		
		$dispatcher = new eDispatcher();
		$this->setDispatcher($dispatcher);
		
		$router = new eRouter();
		$this->setRouter($router);
		
		/** @var eResponse $response */
		$response = e107::getSingleton('eResponse');
		$this->setResponse($response);
		
		return $this;
	}
	
	/**
	 * Dispatch
	 * @param string|eRequest $route
	 */
	public function run($route = null)
	{
		if($route) 
		{
			if(is_object($route) && ($route instanceof eRequest)) $this->setRequest($route);
			elseif(null !== $route && null !== $this->getRequest()) $this->getRequest()->setRoute($route);
		}
		try 
		{
			$this->dispatch();
		}
		catch(eException $e)
		{
			echo $e->getMessage();
			exit;
		}
		
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
			if(!empty($status[0]) && ($status[0] === '{'))
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
			if($controllerName == 'index') // v2.x upgrade has not been run yet. 
			{
				e107::getRedirect()->redirect(e_ADMIN."admin.php");	
			}
			
			throw new eException("Invalid controller '".$controllerName."'");
		}
		
		$controller = new $className($request, $response);
		if(!($controller instanceof eController))
		{
			throw new eException("Controller $controller is not an instance of eController");
		}
		
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
			$custom = $tmp[1].'_';
		}
		unset($tmp);
		if($module !== '*') $module .= '/';
		
		switch ($location) 
		{
			case 'plugin':
				//if($custom) $custom = 'url/'.$custom;
				if(!defined('e_CURRENT_PLUGIN'))
				{
					define('e_CURRENT_PLUGIN', rtrim($module,'/')); // TODO Move to a better location.
				}
				return $sc ? '{e_PLUGIN}'.$module.'url/'.$custom.'url.php' : e_PLUGIN.$module.'url/'.$custom.'url.php';
			break;

			case 'core':
				if($module === '*') return $sc ? '{e_CORE}url/' : e_CORE.'url/';
				return $sc ? '{e_CORE}url/'.$module.$custom.'url.php' : e_CORE.'url/'.$module.$custom.'url.php';
			break;

			case 'override':
				if($module === '*') return $sc ? '{e_CORE}override/url/'  : e_CORE.'override/url/' ;
				return $sc ? '{e_CORE}override/url/'.$module.$custom.'url.php'  : e_CORE.'override/url/'.$module.$custom.'url.php' ;
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

			case 'core':
				return $sc ? '{e_CORE}url/'.$module.'/' : e_CORE.'url/'.$module.'/';
			break;

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
		if($checkOverride || $location == 'override')
		{
			$path = self::getControllerPath($module, $controllerName, 'override', false); 
			
			$class_name = self::getControllerClass($module, $controllerName, 'override');
			if($class_name && !class_exists($class_name, false) && is_readable($path)) include_once($path);
			
			if($class_name && class_exists($class_name, false)) return $class_name;
		}
		
		// fallback to original dispatch location if any
		if($location === 'override')
		{
			// check for real location
			if(($location = eDispatcher::getModuleRealLocation($module)) === null) return false;
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
		$location = self::getDispatchLocation($request->getModuleName());
		
		$controllerName = $request->getControllerName();
		$moduleName = $request->getModuleName();
		$className = false;
		
		// dispatch based on url_config preference value, if config location is override and there is no
		// override controller, additional check against real controller location will be made
		if($location)
		{
			$className = $this->isDispatchableModule($moduleName, $controllerName, $location, $checkOverride);
		}
		//else 
		//{
			# Disable plugin check for routes with no config info - prevent calling of non-installed plugins
			# We may allow this for plugins which don't have plugin.xml in the future 
			// $className = $this->isDispatchableModule($moduleName, $controllerName, 'plugin', $checkOverride);
			// if(!$className)  
			//$className = $this->isDispatchableModule($moduleName, $controllerName, 'core', $checkOverride);
		//}
		
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
			$location = self::getModuleConfigLocation($module);
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
	 * @return mixed
	 */
	public static function getModuleConfigLocation($module)
	{
		//retrieve from config prefs
		return e107::findPref('url_config/'.$module, '');
	}

	/**
	 * Auto discover module location from stored in core prefs data
	 * @param string $module
	 * @return mixed|null|string
	 */
	public static function getDispatchLocation($module)
	{
		//retrieve from prefs
		$location = self::getModuleConfigLocation($module);
		if(!$location) return null;
		
		if(($pos = strpos($location, '/'))) //can't be 0
		{
			return substr($location, 0, $pos);
		}
		return $location;
	}
	
	
	/**
	 * Auto discover module real location (and not currently set from url adminsitration) from stored in core prefs data
	 * @param string $module
	 */
	public static function getModuleRealLocation($module)
	{
		//retrieve from prefs
		$searchArray = e107::findPref('url_modules');
		if(!$searchArray) return null;
		
		$search = array('core', 'plugin', 'override');
		
		foreach ($search as $location) 
		{
			$_searchArray = vartrue($searchArray[$location], array());
			if(in_array($module, $_searchArray)) return $location;
		}
		return null;
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
	private $_urlFormat = self::FORMAT_PATH;
	
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
	public $notFoundUrl = 'system/error/404?type=routeError';
	
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
		$this->_loadConfig()
			->setAliases();
		// we need config first as setter does some checks if module can be set as main
		$this->setMainModule(e107::getPref('url_main_module', ''));
	}
	
	/**
	 * Set module for default namespace
	 * @param string $module
	 * @return eRouter
	 */
	public function setMainModule($module)
	{
		if(!$module || !$this->isModule($module) || !$this->getConfigValue($module, 'allowMain')) return $this;
		$this->_mainNsModule = $module;
		return $this;
	}
	
	/**
	 * Get main url namespace module
	 * @return string
	 */
	public function getMainModule()
	{
		return $this->_mainNsModule;
	}
	
	/**
	 * Check if given module is the main module
	 * @param string $module
	 * @return boolean
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
		if(!is_readable(e_CACHE_URL.'config.php')) $config = $this->buildGlobalConfig();
		else $config = include(e_CACHE_URL.'config.php');
		
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
		if(file_exists(e_CACHE_URL.'config.php'))
		{
			@unlink(e_CACHE_URL.'config.php');	
		}			
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
				$_config['config']['format'] = $this->getUrlFormat();
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
			
			file_put_contents(e_CACHE_URL.'config.php', $fileContent);
		}
		return $config;
	}

	/**
	 * Retrieve config array from a given system path
	 * @param string $path
	 * @param string $location core|plugin|override
	 */
	public static function adminReadConfigs($path, $location = null)
	{
		$file = e107::getFile(false);
		$ret = array();
		
		$file->mode = 'fname';
		$files = $file->setFileInfo('fname')
			->get_files($path, '^([a-z_]{1,}_)?url\.php$');
			
		
		foreach ($files as $file) 
		{
			if(null === $location)
			{
				$c = eRouter::file2config($file, $location);
				if($c) $ret[] = $c;
				continue;
			}
			$ret[] = eRouter::file2config($file, $location);
		}
		return $ret;
	}

	/**
	 * Convert filename to configuration string
	 * @param string $filename
	 * @param string $location core|plugin|override
	 */
	public static function file2config($filename, $location = '')
	{
		if($filename == 'url.php') return $location;
		if($location) $location .= '/';
		return $location.substr($filename, 0, strrpos($filename, '_'));
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
					continue;
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
			
			// remove not installed plugin locations, possible only for 'all' type
			if($type == 'all')
			{
				foreach ($ret['override'] as $i => $l) 
				{
					// it's a plugin override, but not listed in current plugin array - remove
					if(in_array($l, $plugins) && !in_array($l, $ret['plugin']))
					{
						unset($ret['override'][$i]);
					}
				}
			}
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
			//$sub = $fl->get_dirs($path);
			$sub = eRouter::adminReadConfigs($path);
			
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
			//$sub = $fl->get_dirs($path);
			$sub = eRouter::adminReadConfigs($path);
			
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
			//$sub = $fl->get_dirs($path);
			$sub = eRouter::adminReadConfigs($path);
			
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
		
		if(is_array($currentAliases))
		{
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
	 * Retrieve single value from a module global configuration array
	 * @param string $module system module
	 * @return array configuration
	 */
	public function getConfigValue($module, $key, $default = null)
	{
		return isset($this->_globalConfig[$module]) && isset($this->_globalConfig[$module][$key]) ? $this->_globalConfig[$module][$key] : $default;	
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
		if($lanCode)
		{
			return e107::findPref('url_aliases/'.$lanCode, array());
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
			$map = array('urlSuffix' => 'urlSuffix', 'legacy' => 'legacy', 'legacyQuery' => 'legacyQuery', 'mapVars' => 'mapVars', 'allowVars' => 'allowVars', 'matchValue' => 'matchValue');
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


	private function _debug($label,$val=null, $line=null)
	{
		if(!deftrue('e_DEBUG_SEF'))
		{
			return false;
		}

		e107::getDebug()->log("<h3>SEF: ".$label . " <small>".basename(__FILE__)." (".$line.")</small></h3>".print_a($val,true));
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
			//$this->_urlFormat = self::FORMAT_PATH;
		}
		
		// Route to front page - index/index/index route
		if(!$rawPathInfo && (!$this->getMainModule() || empty($_GET)))
		{
			// front page settings will be detected and front page will be rendered
			$request->setRoute('index/index/index');
			$request->addRouteHistory($rawPathInfo);
			$request->routed = true;
			return true;
		}

		// max number of parts is actually 4 - module/controller/action/[additional/pathinfo/vars], here for reference only
		$parts = $rawPathInfo ? explode('/', $rawPathInfo, 4) : array();

		$this->_debug('parts',$parts,  __LINE__);

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

		$this->_debug('module',$module,  __LINE__);
		$this->_debug('rawPathInfo',$rawPathInfo,  __LINE__);

		
		// valid module
		if(null !== $module)
		{
			// we have valid module
			$config = $this->getConfig($module);

			$this->_debug('config',$module,  __LINE__);
			
			// set legacy state
			eFront::isLegacy(varset($config['legacy']));
			
			// Don't allow single entry if required by module config
			if(vartrue($config['noSingleEntry']))
			{
				$request->routed = true;
				if(!eFront::isLegacy())
				{
					$request->setRoute($this->notFoundRoute); 
					return false;
				}
				// legacy entry point - include it later in the bootstrap, legacy query string will be set to current
				$request->addRouteHistory($rawPathInfo);
				return true;
			}
			
			// URL format - the one set by current config overrides the auto-detection
			$format = isset($config['format']) && $config['format'] ? $config['format'] : $this->getUrlFormat();
			
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
				if(vartrue($config['urlSuffix'])) $rawPathInfo = $this->removeUrlSuffix($rawPathInfo, $config['urlSuffix']);
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
			//	$this->_debug('rules',$rules,  __LINE__);

				foreach ($rules as $rule) 
				{
					$route = $rule->parseUrl($this, $request, $pathInfo, $rawPathInfo);



					if($route !== false)
					{
						eFront::isLegacy($rule->legacy); // legacy include override

						$this->_debug('rule->legacy',$rule->legacy,  __LINE__);
						$this->_debug('rule->parseCallback',$rule->parseCallback,  __LINE__);

						if($rule->parseCallback)
						{
							$this->configCallback($module, $rule->parseCallback, array($request), $config['location']);
						}

						// parse legacy query string if any
						$this->_debug('rule->legacyQuery',$rule->legacyQuery,  __LINE__);

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
								foreach ($rule->allowVars as $key) 
								{
									if(isset($_GET[$key]) && !$request->isRequestParam($key))
									{
										// sanitize
										$vars->$key = preg_replace('/[^\d\w\-]/', '', $_GET[$key]); 
									}
								}
							}
							$obj->legacyQueryString = e107::getParser()->simpleParse($rule->legacyQuery, $vars, '0');

							$this->_debug('obj->legacyQueryString',$obj->legacyQueryString,  __LINE__);
							unset($vars, $obj);
						}
						break;
					}
				}
			}
			
			// append module to be registered in the request object
			if(false !== $route)
			{
				// don't modify if true - request directly modified by config callback
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
		if(!vartrue($route))
		{
			if($request->routed)
			{
				$route = $request->getRoute();
			}
			
			if(!$route)
			{
				$route = $this->notFoundRoute;
				eFront::isLegacy(''); // reset legacy - not found route isn't legacy call
				$request->routed = true;
				if($checkOnly) return false;
				## Global redirect on error option
				if(e107::getPref('url_error_redirect', false) && $this->notFoundUrl)
				{
					$redirect = $this->assemble($this->notFoundUrl, '', 'encode=0&full=1');
					//echo $redirect; exit;
					e107::getRedirect()->redirect($redirect, true, 404);
				}
			}
		}

		$this->_debug('route',$route,  __LINE__);

		$request->setRoute($route);
		$request->addRouteHistory($route);
		$request->routed = true;
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

		// forward from controller to a legacy module - bad stuff
		if(!$request->isDispatched() && $this->getConfigValue($module, 'legacy'))
		{
			eFront::isLegacy($this->getConfigValue($module, 'legacy'));
			
			$url = $this->assemble($request->getRoute(), $request->getRequestParams());
			$request->setRequestInfo($url)->setPathInfo(null)->setRoute(null);

			$_GET = $request->getRequestParams();
			$_SERVER['QUERY_STRING'] = http_build_query($request->getRequestParams(), null, '&');
			
			// Infinite loop impossible, as dispatcher will break because of the registered legacy path
			$this->route($request);
		}
	}

	/**
	 * Convenient way to call config methods
	 */
	public function configCallback($module, $callBack, $params, $location)
	{
		if(null == $location) $location = eDispatcher::getModuleConfigLocation($module);
		if(!$module || !($obj = eDispatcher::getConfigObject($module, $location))) return false;
		
		return call_user_func_array(array($obj, $callBack), $params);
	}
	
	/**
	 * Convert assembled url to shortcode
	 * 
	 * @param string $route
	 * @param array $params
	 * @param array $options {@see eRouter::$_defaultAssembleOptions}
	 */
	public function assembleSc($route, $params = array(), $options = array())
	{
		//if(is_string($options)) parse_str($options, $options);
		$url = $this->assemble($route, $params, $options);
		return e107::getParser()->createConstants($url, 'mix');
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
			unset($params['#']);
		}
		
		// Config independent - Deny parameter keys, useful for directly denying sensitive data e.g. password db fields
		if(isset($options['deny']))
		{
			$list = array_map('trim', explode(',', $options['deny']));
			foreach ($list as $value) 
			{
				unset($params[$value]);
			}
			unset($list);
		}
		
		// Config independent - allow parameter keys, useful to directly allow data (and not to rely on config allowVars) e.g. when retrieved from db
		if(isset($options['allow']))
		{
			$list = array_map('trim', explode(',', $options['allow']));
			$_params = $params;
			$params = array();
			foreach ($list as $value) 
			{
				if(isset($_params[$value])) $params[$value] = $_params[$value];
			}
			unset($list, $_params);
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
		
		# fill in index when needed - XXX not needed, may be removed soon
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
		if($options['encode']) $alias = rawurlencode($alias);
		
		$format = isset($config['format']) && $config['format'] ? $config['format'] : self::FORMAT_GET;
		
		$urlSuffix = '';
		
		// Fix base url for legacy links
		if(vartrue($config['noSingleEntry'])) $base = $options['full'] ? SITEURL : e_HTTP;
		elseif(self::FORMAT_GET !== $config['format'])
		{
			$urlSuffix = $this->urlSuffix;
			if(isset($config['urlSuffix'])) $urlSuffix = $config['urlSuffix'];
		} 
		
		// Create by config callback
		if(vartrue($config['selfCreate']))
		{
			$tmp = $this->configCallback($module, 'create', array(array($route[1], $route[2]), $params, $options), $config['location']); 
			
			if(empty($tmp)) return '#not-found';
			
			if(is_array($tmp))
			{
				$route = $tmp[0];
				$params = $tmp[1];
				
				if($options['encode']) $route = array_map('rawurlencode', $route);
				$route = implode('/', $route);
			
				if(!$route) 
				{
					$urlSuffix = '';
					if(!$this->isMainModule($module)) $route = $alias;
				}
				elseif (!$this->isMainModule($module)) 
				{
					$route = $alias.'/'.$route;
				}
				
			}
			else 
			{	
				// relative url returned
				return $base.$tmp.$anc;
			}	
			unset($tmp);
			
			if($format === self::FORMAT_GET)
			{
				$params[$this->routeVar] = $route;
				$route = '';
			}
			
			if($params) 
			{
				$params = $this->createPathInfo($params, $options);
				return $base.$route.$urlSuffix.'?'.$params.$anc;
			}

			return $base.$route.$urlSuffix.$anc;
		}
		
		
		// System URL create routine
		$rules = $this->getRules($module);
		if($format !== self::FORMAT_GET && !empty($rules))
		{
			foreach ($rules as $k => $rule)
			{
				if (($url = $rule->createUrl($this, array($route[1], $route[2]), $params, $options)) !== false)
				{
					 return $base.rtrim(($this->isMainModule($module) ? '' : $alias.'/').$url, '/').$anc;
				}
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
			if(varset($config['mapVars']))
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
			
			// false means - no vars are allowed, nothing to preserve here
			if(varset($config['allowVars']) === false) $params = array();
			// default empty array value - try to guess what's allowed - mapVars is the best possible candidate
			elseif(empty($config['allowVars']) && !empty($config['mapVars'])) $params = array_unique(array_values($config['mapVars']));
			// disallow everything but valid URL parameters
			if(!empty($config['allowVars']))
			{
				$copy = $params;
				$params = array();
				foreach ($config['allowVars'] as $key)
				{
					if(isset($copy[$key])) $params[$key] = $copy[$key];
				}
				unset($copy);
			}
			
			if($format === self::FORMAT_GET)
			{
				$urlSuffix = '';
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
			$route = implode('/', $route);
			if(!$route || $route == $alias) $urlSuffix = '';
			return $base.$route.$urlSuffix.'?'.$params.$anc;
		}
		$route = implode('/', $route);
		if(!$route || $route == $alias) $urlSuffix = '';
		
		
		return $format === self::FORMAT_GET ? $base.'?'.$this->routeVar.'='.$route.$anc : $base.$route.$urlSuffix.$anc;
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
	 * XXX - maybe we can switch to http_build_query(), should be able to do everything we need in a much better way
	 * 
	 * @param array $params list of GET parameters
	 * @param array $options rawurlencode, equal, encode and amp settings
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
			if (null !== $key) $k = $key.'['.rawurlencode($k).']';

			if (is_array($v)) $pairs[] = $this->createPathInfo($v, $options, $k);
			else 
			{
				if(null === $v)
				{
					if($encode)
					{
						$k = null !== $key ? $k : rawurlencode($k);
					}
					$pairs[] = $k;
					continue;
				}
				if($encode)
				{
					$k =  null !== $key ? $k : rawurlencode($k);
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
	 * Should be values matched vs route patterns when assembling URLs
	 * Warning SLOW when true!!!
	 * @var mixed true or 1 for preg_match (extremely slower), or 'empty' for only empty check (better) 
	 */
	public $matchValue;
	
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
	 * Core regex templates
	 * Example usage - route <var:{number}> will result in 
	 * @var array
	 */
	public $regexTemplates = array(
		'az'					=> '[A-Za-z]+', // NOTE - it won't match non-latin word characters!
		'alphanum'  			=> '[\w\pL]+',
		'sefsecure' 			=> '[\w\pL\s\-+.,]+',
		'secure' 				=> '[^\/\'"\\<%]+',
		'number' 				=> '[\d]+',
		'username' 				=> '[\w\pL.\-\s!,]+', // TODO - should equal to username pattern, sync it
		'azOptional'			=> '[A-Za-z]{0,}',
		'alphanumOptional'  	=> '[\w\pL]{0,}',
		'sefsecureOptional' 	=> '[\w\pL\s\-+.,]{0,}',
		'secureOptional' 		=> '[^\/\'"\\<%]{0,}',
		'numberOptional' 		=> '[\d]{0,}',
		'usernameOptional' 		=> '[\w\pL.\-\s!,]{0,}', // TODO - should equal to username pattern, sync it
	);
	
	/**
	 * User defined regex templates
	 * @var array
	 */
	public $varTemplates = array(); 
	
	/**
	 * All regex templates
	 * @var e_var
	 */
	protected $_regexTemplates;
	

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
				$this->_regexTemplates = new e_vars($this->regexTemplates);
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
		
		if($this->varTemplates)
		{
			// don't override core regex templates
			$this->regexTemplates = array_merge($this->varTemplates, $this->regexTemplates); 
			$this->varTemplates = array();
		}
		$this->_regexTemplates = new e_vars($this->regexTemplates);

		if (preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches))
		{
			$tokens = array_combine($matches[1], $matches[2]);
			$tp = e107::getParser();
			foreach ($tokens as $name => $value)
			{
				if ($value === '') $value = '[^\/]+';
				elseif($value[0] == '{')
				{
					$value = $tp->simpleParse($value, $this->_regexTemplates, '[^\/]+');
				}
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
		
		// map vars first
		foreach ($this->mapVars as $srcKey => $dstKey)
		{
			if (isset($params[$srcKey])/* && !isset($params[$dstKey])*/)
			{
				$params[$dstKey] = $params[$srcKey];
				unset($params[$srcKey]);
			}
		}	
			
		// false means - no vars are allowed, preserve only route vars
		if($this->allowVars === false) $this->allowVars = array_keys($this->params);
		// empty array (default) - everything is allowed
		
		// disallow everything but valid URL parameters
		if(!empty($this->allowVars))
		{
			$copy = $params;
			$params = array();
			$this->allowVars = array_unique(array_merge($this->allowVars, array_keys($this->params)));
			foreach ($this->allowVars as $key)
			{
				if(isset($copy[$key])) $params[$key] = $copy[$key];
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
		
		if($this->matchValue)
		{
			
			if('empty' !== $this->matchValue)
			{
				foreach($this->params as $key=>$value)
				{
					if(!preg_match('/'.$value.'/'.$case,$params[$key]))
						return false;
				}
			}
			else
			{
				foreach($this->params as $key=>$value)
				{
					if(empty($params[$key]) )
						return false;
				}
			}
		}

		$tp = e107::getParser();
		$urlFormat = e107::getConfig()->get('url_sef_translate');

		foreach ($this->params as $key => $value)
		{
			// FIX - non-latin URLs proper encoded
			$tr["<$key>"] = rawurlencode($params[$key]); //todo transliterate non-latin
		//	$tr["<$key>"] = eHelper::title2sef($tp->toASCII($params[$key]), $urlFormat); // enabled to test.
			unset($params[$key]);
		}
		
		$suffix = $this->urlSuffix === null ? $manager->urlSuffix : $this->urlSuffix;
		
		// XXX TODO Find better place for this check which will affect all types of SEF URL configurations. (@see news/sef_noid_url.php for duplicate)



		
		if($urlFormat == 'dashl' || $urlFormat == 'underscorel' || $urlFormat == 'plusl') // convert template to lowercase when using lowercase SEF URL format.  
		{
			$this->template = strtolower($this->template);	
		}
		
		$url = strtr($this->template, $tr);

		// Work-around fix for lowercase username
		if($urlFormat == 'dashl' && $this->route == 'profile/view')
		{
			$url = str_replace('%20','-', strtolower($url));
		}

		if(empty($params))
		{
			 return $url !== '' ? $url.$suffix : $url;
		}

		// apppend not supported, maybe in the future...?
		if ($this->append) $url .= '/'.$manager->createPathInfo($params, '/', '/').$suffix;
		else
		{
			if ($url !== '') $url = $url.$suffix;

			$options['equal'] = '=';
			$url .= '?'.$manager->createPathInfo($params, $options);
		}
	

		return rtrim($url, '/');
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
		// pathInfo is decoded, pattern could be encoded - required for proper url assemble (e.g. cyrillic chars)
		if (preg_match(rawurldecode($this->pattern).$case, $pathInfo, $matches))
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
	public function parse($pathInfo, $params = array(), eRequest $request = null, eRouter $router = null, $config = array())
	{
		return false;
	}
	
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
	
	/**
	 * Custom init, always called in the constructor, no matter what is the request dispatch status
	 */
	public function init() {}
	
	/**
	 * Custom shutdown, always called after the controller dispatch, no matter what is the request dispatch status
	 */
	public function shutdown() {}
	
	/**
	 * Pre-action callback, fired only if dispatch status is still true and action method is found
	 */
	public function preAction() {}
	
	/**
	 * Post-action callback, fired only if dispatch status is still true and action method is found
	 */
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
	
	/**
	 * Add document title
	 * @param string $title
	 * @param boolean $meta auto-add it as meta-title
	 * @return eResponse
	 */
	public function addTitle($title, $meta = true)
	{
		$this->getResponse()->appendTitle($title);
		if($meta) $this->addMetaTitle(strip_tags($title));
		return $this;
	}
	
	
	public function addMetaTitle($title)
	{
		$this->getResponse()->addMetaTitle($title);
		return $this;
	}
	
	public function dispatch($actionMethodName)
	{
		$request = $this->getRequest();
		$content = '';
		
		// init() could modify the dispatch status
		if($request->isDispatched())
		{		
			if(method_exists($this, $actionMethodName)) 
			{
				$this->preAction();
				// TODO request userParams() to store private data - check for noPopulate param here
				if($request->isDispatched())
				{
					$request->populateRequestParams();
					
					// allow return output
					$content = $this->$actionMethodName();
					if(!empty($content)) $this->addBody($content);
					
					if($request->isDispatched())
					{
						$this->postAction();
					}
				}
			}
			else 
			{
				//TODO not found method by controller or default one
				$action = substr($actionMethodName, 6);
				throw new eException('Action "'.$action.'" does not exist');
			}
		}
		$this->shutdown();
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
		if(strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
		{
			$url = $url[0] == '/' ? SITEURLBASE.$url : SITEURL.$url;
		}
		$redirect->redirect($url, true, $code);
	}
	
	/**
	 * System forward
	 * @param string $route
	 * @param array $params
	 */
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
				if($route[0] !== '*') $request->setModule($route[0]);
				if($route[1] !== '*') $request->setController($route[1]);
				$request->setAction($route[2]);
			break;
			
			case 2:
				if($route[1] !== '*') $request->setController($route[0]);
				$request->setAction($route[1]);
			break;
			
			case 1:
				$request->setAction($route[0]);
			break;
			
			default:
				return;
			break;
		}
		
		$request->addRouteHistory($oldRoute);
		
		if(false !== $params) $request->setRequestParams($params);
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
 * @package e107
 * @subpackage e107_handlers
 * @version $Id$
 *
 * Base front-end controller
 */

class eControllerFront extends eController
{
	/**
	 * Plugin name - used to check if plugin is installed
	 * Set this only if plugin requires installation
	 * @var string
	 */
	protected $plugin = null;
	
	/**
	 * Default controller access
	 * @var integer
	 */
	protected $userclass = e_UC_PUBLIC;
	
	/**
	 * Generic 404 page URL (redirect), SITEURL will be added
	 * @var string
	 */
	protected $e404 = '404.html';
	
	/**
	 * Generic 403 page URL (redirect), SITEURL will be added
	 * @var string
	 */
	protected $e403 = '403.html';
	
	/**
	 * Generic 404 route URL (forward)
	 * @var string
	 */
	protected $e404route = 'index/not-found';
	
	/**
	 * Generic 403 route URL (forward)
	 * @var string
	 */
	protected $e403route = 'index/access-denied';
	
	/**
	 * View renderer objects
	 * @var array
	 */
	protected $_validator;
	
	/**
	 * Per action access
	 * Format 'action' => userclass
	 * @var array
	 */
	protected $access = array();
	
	/**
	 * User input filter (_GET)
	 * Format 'action' => array(var => validationArray)
	 * @var array
	 */
	protected $filter = array();
	
	/**
	 * Base constructor - set 404/403 locations
	 */
	public function __construct(eRequest $request, eResponse $response = null)
	{
		parent::__construct($request, $response);
		$this->_init();
	}
	
	/**
	 * Base init, called after the public init() - handle access restrictions
	 * The base init() method is able to change controller variables on the fly (e.g. access, filters, etc)
	 */
	final protected function _init()
	{
		// plugin check
		if(null !== $this->plugin)
		{
			if(!e107::isInstalled($this->plugin))
			{
				$this->forward403();
				return;
			}
		}
		
		// global controller restriction
		if(!e107::getUser()->checkClass($this->userclass, false))
		{
			$this->forward403();
			return;
		}
		
		// by action access
		if(!$this->checkActionPermissions()) exit;
		
		// _GET input validation
		$this->validateInput();
		
		// Set Render mode to module-controller-action, override possible within the action
		$this->getResponse()->setRenderMod(str_replace('/', '-', $this->getRequest()->getRoute()));
	}
	
	/**
	 * Check persmission for current action
	 * @return boolean
	 */
	protected function checkActionPermissions()
	{
		// per action restrictions
		$action = $this->getRequest()->getAction();
		if(isset($this->access[$action]) && !e107::getUser()->checkClass($this->access[$action], false))
		{
			$this->forward403();
			return false;
		}
		return true;
	}
	
	public function redirect404()
	{
		e107::getRedirect()->redirect(SITEURL.$this->e404);
	}
	
	public function redirect403()
	{
		e107::getRedirect()->redirect(SITEURL.$this->e403);
	}
	
	public function forward404()
	{
		$this->_forward($this->e404route);
	}
	
	public function forward403()
	{
		$this->_forward($this->e403route);
	}
	
	/**
	 * Controller validator object
	 * @return e_validator
	 */
	public function getValidator()
	{
		if(null === $this->_validator)
		{
			$this->_validator = new e_validator('controller');
		}
		
		return $this->_validator;
	}
	
	/**
	 * Register request parameters based on current $filter data (_GET only)
	 * Additional security layer
	 */
	public function validateInput()
	{
		$validator = $this->getValidator(); 
		$request = $this->getRequest();
		if(empty($this->filter) || !isset($this->filter[$request->getAction()])) return;
		$validator->setRules($this->filter[$request->getAction()])
			->validate($_GET);
		
		$validData = $validator->getValidData();
		
		foreach ($validData as $key => $value) 
		{
			if(!$request->isRequestParam($key)) $request->setRequestParam($key, $value);
		}
		$validator->clearValidateMessages();
	}
	
	/**
	 * System error message proxy
	 * @param string $message
	 * @param boolean $session
	 */
	public function messageError($message, $session = false)
	{
		return e107::getMessage()->addError($message, 'default', $session);
	}
	
	/**
	 * System success message proxy
	 * @param string $message
	 * @param boolean $session
	 */
	public function messageSuccess($message, $session = false)
	{
		return e107::getMessage()->addSuccess($message, 'default', $session);
	}
	
	/**
	 * System warning message proxy
	 * @param string $message
	 * @param boolean $session
	 */
	public function messageWarning($message, $session = false)
	{
		return e107::getMessage()->addWarning($message, 'default', $session);
	}
	
	/**
	 * System debug message proxy
	 * @param string $message
	 * @param boolean $session
	 */
	public function messageDebug($message, $session = false)
	{
		return e107::getMessage()->addDebug($message, 'default', $session);
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
	 * @var string
	 */
	protected $_requestInfo;
	
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
	public $singleEntry = 'index.php';

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
			if($this->getBasePath() == $this->getRequestInfo()) 
				$this->_pathInfo = ''; // map to indexRoute
				
			else 
				$this->_pathInfo = substr($this->getRequestInfo(), strlen($this->getBasePath()));
			
			if($this->_pathInfo && trim($this->_pathInfo, '/') == trim($this->singleEntry, '/')) $this->_pathInfo = '';
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
	 * @return string request info
	 */
	public function getRequestInfo()
	{
		if(null === $this->_requestInfo)
		{
			$this->_requestInfo = e_REQUEST_HTTP;
		}
		return $this->_requestInfo;
	}
	
	
	/**
	 * Override request info
	 * @param string $pathInfo
	 * @return eRequest
	 */
	public function setRequestInfo($requestInfo)
	{
		$this->_requestInfo = $requestInfo;
		return $this;
	}
	
	/**
	 * Quick front page check 
	 */
	public static function isFrontPage($entryScript = 'index.php', $currentPathInfo = e_REQUEST_HTTP)
	{
		$basePath = e_HTTP;
		if(!e107::getPref('url_disable_pathinfo')) $basePath .= $entryScript.'/';
		
		return ($basePath == $currentPathInfo);
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
		if(null === $route) 
		{
			$this->_module = null;
			$this->_controller = null;
			$this->_action = null;
		}
		return $this->initFromRoute($route);
	}
	
	/**
	 * System routing track, used in controllers forwarder
	 * @param string $route
	 * @return eRequest
	 */
	public function addRouteHistory($route)
	{
		$this->_routeHistory[] = $route;
		return $this;
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
			
		return $this;//->getRoute(true);
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
	 * @param string $qstring
	 * @return eRequest
	 */
	public function setLegacyQstring($qstring = null)
	{
		if(defined('e_QUERY')) return $this;
		
		if(null === $qstring)
		{
			$qstring = self::getQueryString();
		}

		if(!defined('e_SELF'))
		{
			define("e_SELF", e_REQUEST_SELF);
		}

		if(!defined('e_QUERY'))
		{
			define("e_QUERY", $qstring);
		}

		$_SERVER['QUERY_STRING'] = e_QUERY;	
		
		if(strpos(e_QUERY,"=")!==false ) // Fix for legacyQuery using $_GET ie. ?x=y&z=1 etc. 
		{
			parse_str(str_replace(array('&amp;'), array('&'), e_QUERY),$tmp);
			foreach($tmp as $key=>$value)
			{
				$_GET[$key] = $value;	
			}
		}
		
		return $this;
	}
	
	/**
	 * And More BC :/
	 * @param string $page
	 * @return eRequest
	 */
	public function setLegacyPage($page = null)
	{
		if(defined('e_PAGE')) return $this;
		if(null === $page)
		{
			$page = eFront::isLegacy();
		}
		if(!$page) 
		{
			define('e_PAGE', $this->singleEntry);
		}
		else define('e_PAGE', basename(str_replace(array('{', '}'), '/', $page)));
		return $this;
	}
	
	/**
	 * And More from the same - BC :/
	 * @return string
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
	protected $_meta_name_only = array('keywords', 'viewport'); // Keep FB happy.
	protected $_meta_property_only = array('article:section', 'article:tag'); // Keep FB happy.
	protected $_meta = array();
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
		'jsonNoTitle' => false,
		'jsonRender' => false,
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
	
	public function isParam($key)
	{
		return isset($this->_params[$key]);
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
	
	/**
	 * @return eResponse
	 */
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
	 * @return eResponse
	 */
	public function addHeader($header, $override = false, $responseCode = null)
	{
		header($header, $override, $responseCode);
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
	 * @param $name
	 * @param $content
	 * @return $this
	 */
	public function setMeta($name, $content)
	{
		foreach($this->_meta as $k=>$v)
		{
			if($v['name'] === $name)
			{
				$this->_meta[$k]['content'] = $content;
			}
		}

		return $this;

	}


	/**
	 * Removes a Meta tag by name/property.
	 *
	 * @param string $name
	 *   'name' or 'property' for the meta tag we want to remove.
	 *
	 * @return eResponse $this
	 */
	public function removeMeta($name)
	{
		foreach($this->_meta as $k=>$v)
		{
			// Meta tags like: <meta content="..." name="description" />
			if(isset($v['name']) && $v['name'] === $name)
			{
				unset($this->_meta[$k]);
				continue;
			}

			// Meta tags like: <meta content="..." property="og:title" />
			if(isset($v['property']) && $v['property'] === $name)
			{
				unset($this->_meta[$k]);
			}
		}

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
	 * @param string $ns
	 * @param boolean $reset
	 * @return string
	 */
	public function getBody($ns = 'default', $reset = false)
	{
		if(!isset($this->_body[$ns]))
		{
			$this->_body[$ns] = '';
		}
		$ret = $this->_body[$ns];
		if($reset) unset($this->_body[$ns]);
		
		return $ret;
	}
	
	/**
	 * @param string $title
	 * @param string $ns
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
	 * @param string $title
	 * @param string $ns
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
	 * @param string $title
	 * @param string $ns
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
	 * @param string $ns
	 * @param bool $reset
	 * @return string
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
	 * @param mixed $ns
	 * @return eResponse
	 */
	function setRenderMod($render_mod, $ns = 'default')
	{
		$this->_render_mod[$ns] = $render_mod;
		return $this;
	}

	/**
	 * Retrieve render mod
	 * @param mixed $ns
     * @return mixed
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
	 * Generic meta information 
	 * Example usage: 
	 * addMeta('og:title', 'My Title');
	 * addMeta(null, 30, array('http-equiv' => 'refresh'));
	 * addMeta(null, null, array('http-equiv' => 'refresh', 'content' => 30)); // same as above
	 * @param string $name 'name' attribute value, or null to avoid it
	 * @param string $content 'content' attribute value, or null to avoid it
	 * @param array $extended format 'attribute_name' => 'value'
	 * @return eResponse
	 */
	public function addMeta($name = null, $content = null, $extended = array())
	{
		if(empty($content)){ return $this; } // content is required, otherwise ignore. 
		
		//TODO need an option that allows subsequent entries to overwrite existing ones. 
		//ie. 'description' and 'keywords' should never be duplicated, but overwritten by plugins and other non-pref-based meta data. 
		
		$attr = array();
				
		if(null !== $name)
		{
		//	$key = (substr($name,0,3) == 'og:') ? 'property' : 'name';
		//	$attr[$key] = $name;
			if(!in_array($name, $this->_meta_name_only))
			{
				$attr['property'] = $name;  // giving both should be valid and avoid issues with FB and others.
			}

			if(!in_array($name, $this->_meta_property_only))
			{
				$attr['name'] = $name;
			}
		}



		if(null !== $content) $attr['content'] = $content;
		if(!empty($extended)) 
		{
			if(!empty($attr))  $attr = array_merge($attr, $extended);
			else $attr = $extended;
		}
		
		if(!empty($attr)) $this->_meta[] = $attr;
		return $this;
	}
	
	/**
	 * Render meta tags, registered via addMeta() method
	 * @return string
	 */
	public function renderMeta()
	{
		$attrData = '';

		e107::getEvent()->trigger('system_meta_pre', $this->_meta);

		foreach ($this->_meta as $attr)
		{
			$attrData .= '<meta';
			foreach ($attr as $p => $v) 
			{
				$attrData .= ' '.preg_replace('/[^\w\-]/', '', $p).'="'.str_replace(array('"', '<'), '', $v).'"';
			}
			$attrData .= ' />'."\n";
		}

		return $attrData;
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
	 * Return an array of all meta data
	 * @return array
	 */
	function getMeta()
	{
		return $this->_meta;
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
	 * @return null|string
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
	 * Send AJAX Json Response Output - default method
	 * It's fully compatible with the core dialog.js
	 * @param array $override override output associative array (header, body and footer keys)
	 * @param string $ns namespace/segment
	 * @param bool $render_message append system messages
	 */
	function sendJson($override = array(), $ns = null, $render_message = true)
	{
		if(!$ns) $ns = 'default';
		
		$content = $this->getBody($ns, true);
		// separate render parameter for json response, false by default
		$render = $this->getParam('jsonRender');
		if($render_message)
		{
			$content = eMessage::getInstance()->render().$content;
		}

		//render disabled by the controller
		if(!$this->getRenderMod($ns))
		{
			$render = false;
		}
		
		
		$title = '';
		if(!$this->getParam('jsonNoTitle'))
		{
			$titleArray = $this->_title;
			$title = isset($titleArray[$ns]) ? array_pop($titleArray[$ns]) : '';
		} 

		if($render)
		{
			$render = e107::getRender();
			$content = $render->tablerender($this->getTitle($ns, true), $content, $this->getRenderMod($ns), true);
		}

		$jshelper = e107::getJshelper();
		$override = array_merge(array(
			'header' => $title,
			'body' => $content,
			'footer' => $statusText,
		), $override);
		echo $jshelper->buildJsonResponse($override);
		$jshelper->sendJsonResponse(null);
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
	protected static $_classRegEx = '#[^\w\s\-]#';
	protected static $_idRegEx = '#[^\w\-]#';
	protected static $_styleRegEx = '#[^\w\s\-\.;:!]#';
	
	public static function secureClassAttr($string)
	{
		return preg_replace(self::$_classRegEx, '', $string);
	}
	
	public static function secureIdAttr($string)
	{
		$string = str_replace(array('/','_'),'-',$string);
		return preg_replace(self::$_idRegEx, '', $string);
	}
	
	public static function secureStyleAttr($string)
	{
		return preg_replace(self::$_styleRegEx, '', $string);
	}
	
	public static function buildAttr($safeArray)
	{
		return http_build_query($safeArray, null, '&');
	}
	
	public static function formatMetaTitle($title)
	{
		$title = trim(str_replace(array('"', "'"), '', strip_tags(e107::getParser()->toHTML($title, TRUE))));
		return trim(preg_replace('/[\s,]+/', ' ', str_replace('_', ' ', $title)));
	}
	
	public static function secureSef($sef)
	{
		return trim(preg_replace('/[^\w\pL\s\-+.,]+/u', '', strip_tags(e107::getParser()->toHTML($sef, TRUE))));
	}
	
	public static function formatMetaKeys($keywordString)
	{
		$keywordString = preg_replace('/[^\w\pL\s\-.,+]/u', '', strip_tags(e107::getParser()->toHTML($keywordString, TRUE)));
		return trim(preg_replace('/[\s]?,[\s]?/', ',', str_replace('_', ' ', $keywordString)));
	}
	
	public static function formatMetaDescription($descrString)
	{
		$descrString = preg_replace('/[\r]*\n[\r]*/', ' ', trim(str_replace(array('"', "'"), '', strip_tags(e107::getParser()->toHTML($descrString, TRUE)))));
		return trim(preg_replace('/[\s]+/', ' ', str_replace('_', ' ', $descrString)));
	}

	/**
	 * Convert title to valid SEF URL string
	 * Type ending with 'l' stands for 'to lowercase', ending with 'c' - 'to camel case'
	 * @param string $title
	 * @param string $type dashl|dashc|dash|underscorel|underscorec|underscore|plusl|plusc|plus|none
	 * @return mixed|string
	 */
	public static function title2sef($title, $type = null)
	{
		/*$char_map = array(
			// Latin
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
			'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
			'ß' => 'ss',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
			'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
			'ÿ' => 'y',
			// Latin symbols
			'©' => '(c)',
			// Greek
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y',
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			// Turkish
			'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
			// Russian
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',
			// Ukrainian
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
			'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
			// Czech
			'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
			'Ž' => 'Z',
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z',
			// Polish
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
			'Ż' => 'Z',
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z',
			// Latvian
			'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
			'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'š' => 's', 'ū' => 'u', 'ž' => 'z'
		);*/

		$tp = e107::getParser();

		// issue #3245: strip all html and bbcode before processing
		$title = $tp->toText($title);

		$title = $tp->toASCII($title);

		$title = str_replace(array('/',' ',","),' ',$title);
		$title = str_replace(array("&","(",")"),'',$title);
		$title = preg_replace('/[^\w\d\pL\s.-]/u', '', strip_tags(e107::getParser()->toHTML($title, TRUE)));
		$title = trim(preg_replace('/[\s]+/', ' ', str_replace('_', ' ', $title)));
		$title = str_replace(array(' - ',' -','- ','--'),'-',$title); // cleanup to avoid ---

		$words = str_word_count($title,1, '1234567890');

		$limited = array_slice($words, 0, 14); // Limit number of words to 14. - any more and it ain't friendly.

		$title = implode(" ",$limited);

		if(null === $type)
		{
			$type = e107::getPref('url_sef_translate'); 
		}

		switch ($type) 
		{
			case 'dashl': //dasherize, to lower case
				return self::dasherize($tp->ustrtolower($title));
			break;
			
			case 'dashc': //dasherize, camel case
				return self::dasherize(self::camelize($title, true, ' '));
			break;
			
			case 'dash': //dasherize
				return self::dasherize($title);
			break;
			
			case 'underscorel': ///underscore, to lower case
				return self::underscore($tp->ustrtolower($title));
			break;
			
			case 'underscorec': ///underscore, camel case
				return self::underscore(self::camelize($title, true, ' '));
			break;
			
			case 'underscore': ///underscore
				return self::underscore($title);
			break;
			
			case 'plusl': ///plus separator, to lower case
				return str_replace(' ', '+', $tp->ustrtolower($title));
			break;
			
			case 'plusc': ///plus separator, to lower case
				return str_replace(' ', '+', self::camelize($title, true, ' '));
			break;
			
			case 'plus': ///plus separator
				return str_replace(' ', '+', $title);
			break;
			
			case 'none':
			default:
				return $title;
			break;
		}
	}
	
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
	
	/**
	 * Get the current memory usage of the code
	 * If $separator argument is null, raw data (array) will be returned
	 *
	 * @param null|string $separator
	 * @return string|array memory usage
	 */
	public static function getMemoryUsage($separator = '/')
	{
		$ret = array();
		if(function_exists("memory_get_usage"))
		{
	      $ret[] = eHelper::parseMemorySize(memory_get_usage());
		  // With PHP>=5.2.0, can show peak usage as well
	      if (function_exists("memory_get_peak_usage")) $ret[] = eHelper::parseMemorySize(memory_get_peak_usage(TRUE));
		}
		else
		{
		  $ret[] = 'Unknown';
		}

		return (null !== $separator ? implode($separator, $ret) : $ret);
	}
	
	public static function camelize($str, $all = false, $space = '')
	{
		// clever recursion o.O
		if($all) return self::camelize('-'.$str, false, $space);
		
		$tmp = explode('-', str_replace(array('_', ' '), '-', e107::getParser()->ustrtolower($str)));
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
