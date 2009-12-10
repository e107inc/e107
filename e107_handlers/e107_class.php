<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/e107_class.php,v $
 * $Revision: 1.91 $
 * $Date: 2009-12-10 22:46:45 $
 * $Author: secretr $
*/

if (!defined('e107_INIT')) { exit; }

/**
 * e107 class
 *
 */
class e107
{
	/**
	 * IPV6 string for localhost - as stored in DB
	 */
	const LOCALHOST_IP = '0000:0000:0000:0000:0000:ffff:7f00:0001';

	public $server_path;

	public $e107_dirs = array();

	/**
	 * @var array  SQL connection data
	 */
	protected $e107_config_mysql_info = array();

	public $http_path;
	public $https_path;
	public $base_path;
	public $file_path;
	public $relative_base_path;
	public $_ip_cache;
	public $_host_name_cache;

	public $site_theme;

	/**
	 * @var string Current request type (http or https)
	 */
	protected $HTTP_SCHEME;

	/**
	 * Used for runtime caching of user extended struct
	 *
	 * @var array
	 * @see get_user_data()
	 */
	public $extended_struct;

	/**
	 * User login name
	 *
	 * @var string
	 * @see init_session()
	 */
	public $currentUser = '';

	/**
	 * Run once load core shortcodes
	 * while initialize SC parser
	 *
	 * @var boolean
	 */
	protected static $_sc_core_loaded = false;

	/**
	 * Singleton instance
	 * Allow class extends - override {@link getInstance()}
	 *
	 * @var e107
	 */
	protected static $_instance = null;

	/**
	 * e107 registry
	 *
	 * @var array
	 */
	private static $_registry = array();

	/**
	 * e107 core config object storage
	 *
	 * @var array
	 */
	protected static $_core_config_arr = array();

	/**
	 * e107 plugin config object storage
	 *
	 * @var array
	 */
	protected static $_plug_config_arr = array();

	/**
	 * Core handlers array
	 * For new/missing handler add
	 * 'class name' => 'path' pair
	 *
	 * Used to auto-load core handlers
	 * TODO - we really need to alphabetically re-order this!
	 *
	 * @see getSingleton()
	 * @see getObject()
	 * @var array
	 */
	protected static $_known_handlers = array (
		'db'						=> '{e_HANDLER}mysql_class.php',
		'ecache'					=> '{e_HANDLER}cache_handler.php',
		'user_class'				=> '{e_HANDLER}userclass_class.php',
		'e107_event'				=> '{e_HANDLER}event_class.php',
		'ArrayData'					=> '{e_HANDLER}arraystorage_class.php',
		'eURL'						=> '{e_HANDLER}e107Url.php',
		'e_file'					=> '{e_HANDLER}file_class.php',
		'e_admin_log'				=> '{e_HANDLER}admin_log_class.php',
		'notify'					=> '{e_HANDLER}notify_class.php',
		'e_online'					=> '{e_HANDLER}online_class.php',
		'convert'					=> '{e_HANDLER}date_handler.php',
		'e_news_item' 				=> '{e_HANDLER}news_class.php',
		'e_news_tree' 				=> '{e_HANDLER}news_class.php',
		'news' 						=> '{e_HANDLER}news_class.php',
		'e_form' 					=> '{e_HANDLER}form_handler.php',
		'e_upgrade' 				=> '{e_HANDLER}e_upgrade_class.php',
		'e_jshelper' 				=> '{e_HANDLER}js_helper.php',
		'e_menu' 					=> '{e_HANDLER}menu_class.php',
		'e107plugin' 				=> '{e_HANDLER}plugin_class.php',
		'xmlClass' 					=> '{e_HANDLER}xml_class.php',
		'e107_traffic'				=> '{e_HANDLER}traffic_class.php',
		'comment'					=> '{e_HANDLER}comment_class.php',
		'e_validator'				=> '{e_HANDLER}validator_class.php',
		'themeHandler'				=> '{e_HANDLER}theme_handler.php',
		'e_model'					=> '{e_HANDLER}model_class.php',
		'e_admin_model'				=> '{e_HANDLER}model_class.php',
		'e_admin_dispatcher' 		=> '{e_HANDLER}admin_ui.php',
		'e_admin_request' 			=> '{e_HANDLER}admin_ui.php',
		'e_admin_response' 			=> '{e_HANDLER}admin_ui.php',
		'e_admin_controller' 		=> '{e_HANDLER}admin_ui.php',
		'e_admin_controller_ui' 	=> '{e_HANDLER}admin_ui.php',
		'e_admin_ui' 				=> '{e_HANDLER}admin_ui.php',
		'e_admin_form_ui' 			=> '{e_HANDLER}admin_ui.php',
		'e_admin_icons' 			=> '{e_HANDLER}admin_ui.php',
		'DHTML_Calendar'			=> '{e_HANDLER}calendar/calendar_class.php',
		'comment'					=> '{e_HANDLER}comment_class.php',
		'e107_user_extended'		=> '{e_HANDLER}user_extended_class.php',
		'e_userperms'				=> '{e_HANDLER}user_handler.php',
		'UserHandler'				=> '{e_HANDLER}user_handler.php',
		'sitelinks'					=> '{e_HANDLER}sitelinks_class.php',
		'redirection'				=> '{e_HANDLER}redirection_class.php'
	);

	/**
	 * Overload core handlers array
	 * Format: 'core_class' => array('overload_class', 'overload_path');
	 *
	 * NOTE: to overload core singleton objects, you have to add record to
	 * $_overload_handlers before the first singleton call.
	 *
	 * Example:
	 * <code> array('e_form' => array('plugin_myplugin_form_handler' => '{e_PLUGIN}myplugin/includes/form/handler.php'));</code>
	 *
	 * Used to auto-load core handlers
	 *
	 * @var array
	 */
	protected static $_overload_handlers = array();


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
	 * Get singleton instance (php4 no more supported)
	 *
	 * @return e107
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
	 * Initialize environment path constants
	 * Public proxy to the protected method {@link _init()}
	 *
	 * @return e107
	 */
	public function initCore($e107_paths, $e107_root_path, $e107_config_mysql_info)
	{
		return $this->_init($e107_paths, $e107_root_path, $e107_config_mysql_info);
	}

	/**
	 * Resolve paths, will run only once
	 *
	 * @return e107
	 */
	protected function _init($e107_paths, $e107_root_path, $e107_config_mysql_info)
	{
		if(empty($this->e107_dirs))
		{
			// Do some security checks/cleanup, prepare the environment
			$this->prepare_request();

			// folder info
			$this->e107_dirs = $e107_paths;

			// mysql connection info
			$this->e107_config_mysql_info = $e107_config_mysql_info;

			// various constants - MAGIC_QUOTES_GPC, MPREFIX, ...
			$this->set_constants();

			// build all paths
			$this->set_paths();
			$this->file_path = $this->fix_windows_paths($e107_root_path)."/";

			// set base path, SSL is auto-detected
			$this->set_base_path(false);

			// set some core URLs (e_LOGIN/SIGNUP)
			$this->set_urls();

			// cleanup QUERY_STRING and friends, set  related constants
			$this->set_request();
		}
		return $this;
	}

	/**
	 * Initialize environment path constants while installing e107
	 *
	 * @return e107
	 */
	public function initInstall($e107_paths, $e107_root_path)
	{
		// Do some security checks/cleanup, prepare the environment
		$this->prepare_request();

		// folder info
		$this->e107_dirs = $e107_paths;

		// build all paths
		$this->set_paths();
		$this->file_path = $this->fix_windows_paths($e107_root_path)."/";

		// set base path, SSL is auto-detected
		$this->set_base_path(false);

		// set some core URLs (e_LOGIN/SIGNUP)
		$this->set_urls();

		// cleanup QUERY_STRING and friends, set  related constants
		$this->set_request();

		return $this;
	}

	/**
	 * Set mysql data
	 *
	 * @return e107
	 */
	public function initInstallSql($e107_config_mysql_info)
	{
		// mysql connection info
		$this->e107_config_mysql_info = $e107_config_mysql_info;

		// various constants - MAGIC_QUOTES_GPC, MPREFIX, ...
		$this->set_constants();

		return $this;
	}

	/**
	 * Get data from the registry
	 * Returns $default if data not found
	 * Replacement of cachevar()
	 *
	 * @param string $id
	 * @return mixed
	 */
	public static function getRegistry($id, $default = null)
	{
		if(isset(self::$_registry[$id]))
		{
			return self::$_registry[$id];
		}

		return $default;
	}

	/**
	 * Add data to the registry - replacement of getcachedvars().
	 * $id is path-like unique id bind to the passed data.
	 * If $data argument is null, $id will be removed from the registry.
	 * When removing objects from the registry, __destruct() method will be auto-executed
	 * if available
	 *
	 * Naming standards (namespaces):
	 * 'area/area_id/storage_type'<br>
	 * where <br>
	 * - area = 'core'|'plugin'|'external' (everything else)
	 * - area_id = core handler id|plugin name (depends on area)
	 * - (optional) storage_type = current data storage stack
	 *
	 * Examples:
	 * - 'core/e107/' - reserved for this class
	 * - 'core/e107/singleton/' - singleton objects repo {@link getSingleton()}
	 *
	 * @param string $id
	 * @param mixed|null $data
	 * @return void
	 */
	public static function setRegistry($id, $data = null, $allow_override = true)
	{
		if(null === $data)
		{
			if(is_object(self::$_registry[$id]) && method_exists(self::$_registry[$id], '__destruct'))
			{
				self::$_registry[$id]->__destruct();
			}
			unset(self::$_registry[$id]);
			return;
		}

		if(!$allow_override && null !== self::getRegistry($id))
		{
			return;
		}

		self::$_registry[$id] = $data;
	}

	/**
	 * Get folder name (e107_config)
	 * Replaces all $(*)_DIRECTORY globals
	 * Example: <code>$e107->getFolder('images')</code>;
	 *
	 * @param string $for
	 * @return string
	 */
	function getFolder($for)
	{
		$key = strtoupper($for).'_DIRECTORY';
		return (isset($this->e107_dirs[$key]) ? $this->e107_dirs[$key] : '');
	}

	/**
	 * Get mysql config var (e107_config.php)
	 * Replaces all $mySQL(*) globals
	 * Example: <code>$e107->getMySQLConfig('prefix');</code>
	 *
	 * @param string $for prefix|server|user|password|defaultdb
	 * @return string
	 */
	function getMySQLConfig($for)
	{
		$key = 'mySQL'.$for;
		return (isset($this->e107_config_mysql_info[$key]) ? $this->e107_config_mysql_info[$key] : '');
	}

	/**
	 * Get known handler path
	 *
	 * @param string $class_name
	 * @param boolean $parse_path [optional] parse path shortcodes
	 * @return string|null
	 */
	public static function getHandlerPath($class_name, $parse_path = true)
	{
		$ret = isset(self::$_known_handlers[$class_name]) ? self::$_known_handlers[$class_name] : null;
		if($parse_path && $ret)
		{
			$ret = self::getParser()->replaceConstants($ret);
		}

		return $ret;
	}

	/**
	 * Add handler to $_known_handlers array on runtime
	 * If class name is array, method will add it (recursion) and ignore $path argument
	 *
	 * @param array|string $class_name
	 * @param string $path [optional]
	 * @return void
	 */
	public static function addHandler($class_name, $path = '')
	{
		if(is_array($class_name))
		{
			foreach ($class_name as $cname => $path)
			{
				self::addHandler($cname, $path);
			}
			return;
		}
		if(!self::isHandler($class_name))
		{
			self::$_known_handlers[$class_name] = $path;
		}
	}

	/**
	 * Check handler presence
	 *
	 * @param string $class_name
	 * @return boolean
	 */
	public static function isHandler($class_name)
	{
		return isset(self::$_known_handlers[$class_name]);
	}

	/**
	 * Get overlod class and path (if any)
	 *
	 * @param object $class_name
	 * @param object $default_handler [optional] return data from $_known_handlers if no overload data available
	 * @param object $parse_path [optional] parse path shortcodes
	 * @return array
	 */
	public static function getHandlerOverload($class_name, $default_handler = true, $parse_path = true)
	{
		$ret = (isset(self::$_overload_handlers[$class_name]) ? self::$_overload_handlers[$class_name] : ($default_handler ? array($class_name, self::getHandlerPath($class_name, false)) : array()));
		if ($parse_path && isset($ret[1]))
		{
			$ret[1] = self::getParser()->replaceConstants($ret[1]);
		}

		return $ret;
	}

	/**
	 * Overload present handler.
	 * If class name is array, method will add it (recursion) and
	 * ignore $overload_class_name and  $overload_path arguments
	 *
	 * @param string $class_name
	 * @param string $overload_name [optional]
	 * @param string $overload_path [optional]
	 * @return void
	 */
	public static function setHandlerOverload($class_name, $overload_class_name = '', $overload_path = '')
	{
		if(is_array($class_name))
		{
			foreach ($class_name as $cname => $overload_array)
			{
				self::setHandlerOverload($cname, $overload_array[0], $overload_array[1]);
			}
			return;
		}
		if(self::isHandler($class_name) && !self::isHandlerOverloadable($class_name))
		{
			self::$_overload_handlers[$class_name] = array($overload_class_name, $overload_path);
		}
	}

	/**
	 * Check if handler is already overloaded
	 *
	 * @param string $class_name
	 * @return boolean
	 */
	public static function isHandlerOverloadable($class_name)
	{
		return isset(self::$_overload_handlers[$class_name]);
	}

	/**
	 * Retrieve singleton object
	 *
	 * @param string $class_name
	 * @param string|boolean $path optional script path
	 * @param string $regpath additional registry path
	 * @return Object
	 */
	public static function getSingleton($class_name, $path = true, $regpath = '')
	{
		$id = 'core/e107/singleton/'.$class_name.$regpath;

		//singleton object found - overload not possible
		if(self::getRegistry($id))
		{
			return self::getRegistry($id);
		}

		//auto detection + overload check
		if(is_bool($path))
		{
			//overload allowed
			if(true === $path && self::isHandlerOverloadable($class_name))
			{
				$tmp = self::getHandlerOverload($class_name);
				$class_name = $tmp[0];
				$path = $tmp[1];
			}
			//overload not allowed
			else
			{
				$path = self::getHandlerPath($class_name);
			}
		}

		if($path && is_string($path) && !class_exists($class_name, false))
		{
			e107_require_once($path); //no existence/security checks here!
			//e107_require_once() is available without class2.php. - see core_functions.php
		}
		if(class_exists($class_name, false))
		{
			e107::setRegistry($id, new $class_name());
		}

		return self::getRegistry($id);
	}

	/**
	 * Retrieve object
	 * Prepare for __autoload
	 *
	 * @param string $class_name
	 * @param mxed $arguments
	 * @param string|boolean $path optional script path
	 * @return object|null
	 */
	public static function getObject($class_name, $arguments = null, $path = true)
	{
		if(true === $path)
		{
			if(isset(self::$_known_handlers[$class_name]))
			{
				$path = self::getParser()->replaceConstants(self::$_known_handlers[$class_name]);
			}
		}

		//auto detection + overload check
		if(is_bool($path))
		{
			//overload allowed
			if(true === $path && self::isHandlerOverloadable($class_name))
			{
				$tmp = self::getHandlerOverload($class_name);
				$class_name = $tmp[0];
				$path = $tmp[1];
			}
			//overload not allowed
			else
			{
				$path = self::getHandlerPath($class_name);
			}
		}

		if($path && is_string($path) && !class_exists($class_name, false))
		{
			e107_require_once($path); //no existence/security checks here!
		}

		if(class_exists($class_name, false))
		{
			if(null !== $arguments) return  new $class_name($arguments);
			return new $class_name();
		}

		trigger_error("Class {$class_name} not found!", E_USER_ERROR);
		return null;
	}

	/**
	 * Retrieve core config handlers.
	 * List of allowed $name values (aliases) could be found
	 * in {@link e_core_pref} class
	 *
	 * @param string $name core|core_backup|emote|menu|search|notify|ipool
	 * @return e_core_pref
	 */
	public static function getConfig($name = 'core')
	{
		if(!isset(self::$_core_config_arr[$name]))
		{
			e107_require_once(e_HANDLER.'pref_class.php');
			self::$_core_config_arr[$name] = new e_core_pref($name, true);
		}

		return self::$_core_config_arr[$name];
	}

	/**
	 * Retrieve core config handler preference value or the core preference array
	 * Shorthand of  self::getConfig()->get()
	 *
	 * @see e_core_pref::get()
	 * @param string $pref_name
	 * @param mixed $default default value if preference is not found
	 * @return mixed
	 */
	public static function getPref($pref_name = '', $default = null)
	{
		return empty($pref_name) ? self::getConfig()->getPref() : self::getConfig()->get($pref_name, $default);
	}

	/**
	 * Advanced version of self::getPref(). $pref_name is parsed,
	 * so that $pref_name = 'x/y/z' will search for value pref_data[x][y][z]
	 * Shorthand of  self::getConfig()->getPref()
	 *
	 * @see e_core_pref::getPref()
	 * @param string $pref_name
	 * @param mixed $default default value if preference is not found
	 * @return mixed
	 */
	public static function findPref($pref_name, $default = null, $index = null)
	{
		return self::getConfig()->getPref($pref_name, $default, $index);
	}

	/**
	 * Retrieve plugin config handlers.
	 * Multiple plugin preference DB rows are supported
	 * Class overload is supported.
	 * Examples:
	 * - <code>e107::getPluginConfig('myplug');<code>
	 * 	 will search for e107_plugins/myplug/e_pref/myplug_pref.php which
	 * 	 should contain class 'e_plugin_myplug_pref' class (child of e_plugin_pref)
	 * - <code>e107::getPluginConfig('myplug', 'row2');<code>
	 * 	 will search for e107_plugins/myplug/e_pref/myplug_row2_pref.php which
	 * 	 should contain class 'e_plugin_myplug_row2_pref' class (child of e_plugin_pref)
	 *
	 * @param string $plug_name
	 * @param string $multi_row
	 * @param boolean $load load from DB on startup
	 * @return e_plugin_pref
	 */
	public static function getPlugConfig($plug_name, $multi_row = '', $load = true)
	{
		if(!isset(self::$_plug_config_arr[$plug_name.$multi_row]))
		{
			e107_require_once(e_HANDLER.'pref_class.php');
			$override_id = $plug_name.($multi_row ? "_{$multi_row}" : '');

			//check (once) for custom plugin pref handler
			if(is_readable(e_PLUGIN.$plug_name.'/e_pref/'.$override_id.'_pref.php'))
			{
				require_once(e_PLUGIN.$plug_name.'/e_pref/'.$override_id.'_pref.php');
				$class_name = 'e_plugin_'.$override_id.'_pref';

				//PHPVER: string parameter for is_subclass_of require PHP 5.0.3+
				if(class_exists($class_name, false) && is_subclass_of('e_plugin_pref', $class_name)) //or e_pref ?
				{
					self::$_plug_config_arr[$plug_name.$multi_row] = new $class_name($load);
					return self::$_plug_config_arr[$plug_name.$multi_row];
				}
			}

			self::$_plug_config_arr[$plug_name.$multi_row] = new e_plugin_pref($plug_name, $multi_row, $load);
		}

		return self::$_plug_config_arr[$plug_name.$multi_row];
	}

	/**
	 * Retrieve plugin preference value.
	 * Shorthand of  self::getPluginConfig()->get()
	 * NOTE: Multiple plugin preference DB rows are NOT supported
	 * This will only look for your default plugin config (empty $milti_row)
	 *
	 * @see e_plugin_pref::get()
	 * @param string $plug_name
	 * @param string $pref_name
	 * @param mixed $default default value if preference is not found
	 * @return mixed
	 */
	public static function getPlugPref($plug_name, $pref_name = '', $default = null)
	{
		return  empty($pref_name) ? self::getPlugConfig($plug_name)->getPref() : self::getPlugConfig($plug_name)->get($pref_name, $default);
	}

	/**
	 * Advanced version of self::getPlugPref(). $pref_name is parsed,
	 * so that $pref_name = 'x/y/z' will search for value pref_data[x][y][z]
	 * Shorthand of  self::getPluginConfig()->getPref()
	 *
	 * @see e_core_pref::getPref()
	 * @param string $pref_name
	 * @param mixed $default default value if preference is not found
	 * @return mixed
	 */
	public static function findPlugPref($plug_name, $pref_name, $default = null, $index = null)
	{
		return self::getPlugConfig($plug_name)->getPref($pref_name, $default, $index);
	}

	/**
	 * Get current theme preference. $pref_name is parsed,
	 * so that $pref_name = 'x/y/z' will search for value pref_data[x][y][z]
	 * Shorthand of  self::getConfig()->getPref('current_theme/sitetheme_pref/pref_name')
	 *
	 * @see e_core_pref::getPref()
	 * @param string $pref_name
	 * @param mixed $default default value if preference is not found
	 * @return mixed
	 */
	public static function getThemePref($pref_name = '', $default = null, $index = null)
	{
		return e107::getConfig()->getPref('sitetheme_pref/'.$pref_name, $default, $index);
	}

	/**
	 * Retrieve text parser singleton object
	 *
	 * @return e_parse
	 */
	public static function getParser()
	{
		return self::getSingleton('e_parse', e_HANDLER.'e_parse_class.php');
	}



	/**
	 * Retrieve sc parser singleton object
	 *
	 * @return e_shortcode
	 */
	public static function getScParser()
	{
		$sc = self::getSingleton('e_shortcode', e_HANDLER.'shortcode_handler.php');
		if(!self::$_sc_core_loaded)
		{
			$sc->loadCoreShortcodes();
			self::$_sc_core_loaded = true;
		}
		return $sc;
	}

	/**
	 * Retrieve DB singleton object based on the
	 * $instance_id
	 *
	 * @param string $instance_id
	 * @return e_db_mysql
	 */
	public static function getDb($instance_id = '')
	{
		return self::getSingleton('db', true, $instance_id);
	}

	/**
	 * Retrieve event singleton object
	 *
	 * @return ecache
	 */
	public static function getCache()
	{
		return self::getSingleton('ecache', true);
	}

	/**
	 * Retrieve user class singleton object
	 *
	 * @return user_class
	 */
	public static function getUserClass()
	{
		return self::getSingleton('user_class', true);
	}


	/**
	 * Retrieve redirection singleton object
	 *
	 * @return redirection
	 */
	public static function getRedirect()
	{
		return self::getSingleton('redirection', true);
	}


	/**
	 * Retrieve sitelinks singleton object
	 *
	 * @return user_class
	 */
	public static function getSitelinks()
	{
		return self::getSingleton('sitelinks', true);
	}


	/**
	 * Retrieve render singleton object
	 *
	 * @return e107table
	 */
	public static function getRender()
	{
		return self::getSingleton('e107table');
	}

	/**
	 * Retrieve event singleton object
	 *
	 * @return e107_event
	 */
	public static function getEvent()
	{
		return self::getSingleton('e107_event', true);
	}

	/**
	 * Retrieve user-session singleton object
	 *
	 * @return e107_event
	 */
	public static function getSession()
	{
		return self::getSingleton('UserHandler', true);
	}

	/**
	 * Retrieve array storage singleton object
	 *
	 * @return ArrayData
	 */
	public static function getArrayStorage()
	{
		return self::getSingleton('ArrayData', true);
	}

	/**
	 * Retrieve menu handler singleton object
	 *
	 * @return e_menu
	 */
	public static function getMenu()
	{
		return self::getSingleton('e_menu', true);
	}

	/**
	 * Retrieve URL singleton object
	 *
	 * @return eURL
	 */
	public static function getUrl()
	{
		return self::getSingleton('eURL', true);
	}

	/**
	 * Retrieve file handler singleton or new fresh object
	 *
	 * @param boolean $singleton default true
	 * @return e_file
	 */
	public static function getFile($singleton = true)
	{
		if($singleton)
		{
			return self::getSingleton('e_file', true);
		}
		return self::getObject('e_file', null, true);
	}

	/**
	 * Retrieve form handler singleton or new fresh object
	 *
	 * @param boolean $singleton default false
	 * @param boolean $tabindex passed to e_form when initialized as an object (not singleton)
	 * @return e_form
	 */
	public static function getForm($singleton = false, $tabindex = false)
	{
		if($singleton)
		{
			return self::getSingleton('e_form', true);
		}
		return self::getObject('e_form', $tabindex, true);
	}

	/**
	 * Retrieve admin log singleton object
	 *
	 * @return e_admin_log
	 */
	public static function getAdminLog()
	{
		return self::getSingleton('e_admin_log', true);
	}

	/**
	 * Retrieve date handler singleton object
	 *
	 * @return convert
	 */
	public static function getDateConvert()
	{
		return self::getSingleton('convert', true);
	}

	/**
	 * Retrieve notify handler singleton object
	 *
	 * @return notify
	 */
	public static function getNotify()
	{
		return self::getSingleton('notify', true);
	}

	/**
	 * Retrieve Xml handler singleton or new instance object
	 * @param mixed $singleton false - new instance, true - singleton from default registry location, 'string' - registry path
	 * @return xmlClass
	 */
	public static function getXml($singleton = true)
	{
		if($singleton)
		{
			return self::getSingleton('xmlClass', true, (true === $singleton ? '' : $singleton));
		}
		return self::getObject('xmlClass', null, true);
	}

	/**
	 * Retrieve User Extended handler singleton object
	 * @return e107_user_extended
	 */
	public static function getUserExt()
	{
		return self::getSingleton('e107_user_extended', true);
	}

	/**
	 * Retrieve online users handler singleton object
	 * @return e_online
	 */
	public static function getOnline()
	{
		return self::getSingleton('e_online', true);
	}

	/**
	 * Retrieve User Perms (admin perms) handler singleton object
	 * @return comment
	 */
	public static function getUserPerms()
	{
		return self::getSingleton('e_userperms', true);
	}


	/**
	 * Retrieve comments handler singleton object
	 * @return comment
	 */
	public static function getComment()
	{
		return self::getSingleton('comment', true);
	}

	/**
	 * Retrieve message handler singleton
	 * @return eMessage
	 */
	public static function getMessage()
	{
		static $included = false;
		if(!$included)
		{
			e107_require_once(e_HANDLER.'message_handler.php');
			$included = true;
		}
		return eMessage::getInstance();
	}

	/**
	 * Retrieve JS Manager singleton object
	 *
	 * @return e_jsmanager
	 */
	public static function getJs()
	{
		static $included = false;
		if(!$included)
		{
			e107_require_once(e_HANDLER.'js_manager.php');
			$included = true;
		}
		return e_jsmanager::getInstance();
	}

	/**
	 * Retrieve admin dispatcher instance.
	 * It's instance is self registered (for now, this could change in the future) on initialization (__construct())
	 *
	 * @see e_admin_dispatcher
	 * @return e_admin_dispatcher
	 */
	public static function getAdminUI()
	{
		return self::getRegistry('admin/ui/dispatcher');
	}



	/**
	 * Retrieves config() from addons such as e_url.php, e_cron.php, e_sitelink.php
	 * @param string $addonName eg. e_cron, e_url
	 * @param string $className [optional] (if different from addonName)
	 * @return
	 */
	public function getAddonConfig($addonName, $className = '')
	{
		$new_addon = array();

		$filename = $addonName; // e.g. 'e_cron';
		if(!$className)
		{
			$className = substr($filename, 2); // remove 'e_'
		}

		$elist = self::getPref($filename.'_list');
		if($elist)
		{
			foreach(array_keys($elist) as $key)
			{
				if(is_readable(e_PLUGIN.$key.'/'.$filename.'.php'))
				{
					include_once(e_PLUGIN.$key.'/'.$filename.'.php');

					$class_name = $key.'_'.$className;
					$array = self::callMethod($class_name, 'config');

					if($array)
					{
						$new_addon[$key] = $array;
					}

				}
			}
		}

		return $new_addon;
	}

	/**
	 * Safe way to call user methods.
	 * @param string $class_name
	 * @param string $method_name
	 * @return
	 */
	public function callMethod($class_name, $method_name)
	{
		$mes = e107::getMessage();

		if(class_exists($class_name))
		{
			$obj = new $class_name;
			if(method_exists($obj, $method_name))
			{
				$mes->debug('Executing <strong>'.$class_name.' :: '.$method_name.'()</strong>');
				return call_user_func(array($obj, $method_name));
			}
			else
			{
				$mes->debug('Function <strong>'.$class_name.' :: '.$method_name.'()</strong> NOT found.');
			}
		}
		return FALSE;
	}

	/**
	 * Get theme name or path.
	 *
	 * @param mixed $for true (default) - auto-detect (current), admin - admin theme, front - site theme
	 * @param string $path default empty string (return name only), 'abs' - absolute url path, 'rel' - relative server path
	 * @return string
	 */
	public static function getThemeInfo($for = true, $path = '')
	{
		global $user_pref; // FIXME - user model, kill user_pref global

		if(true === $for)
		{
			$for = e_ADMIN_AREA ? 'admin' : 'front';
		}
		switch($for )
		{
			case 'admin':
				$for = e107::getPref('admintheme');
			break;

			case 'front':
				$for = isset($user_pref['sitetheme']) ? $user_pref['sitetheme'] : e107::getPref('sitetheme');
			break;
		}
		if(!$path) return $for;

		switch($path)
		{
			case 'abs':
				$path = e_THEME_ABS.$for.'/';
			break;

			case 'rel':
			default:
				$path = e_THEME.$for.'/';
			break;
		}
		return $path;
	}

	/**
	 * Retrieve core template path
	 * Example: <code>echo e107::coreTemplatePath('admin_icons');</code>
	 *
	 * @see getThemeInfo()
	 * @param string $id part of the path/file name without _template.php part
	 * @param boolean $override default true
	 * @return string relative path
	 */
	public static function coreTemplatePath($id, $override = true)
	{
		$id = str_replace('..', '', $id); //simple security, '/' is allowed
		$override_path = $override ? self::getThemeInfo($override, 'rel').'templates/'.$id.'_template.php' : null;
		$default_path = e_THEME.'templates/'.$id.'_template.php';

		return ($override_path && is_readable($override_path) ? $override_path : $default_path);
	}

	/**
	 * Retrieve plugin template path
	 * Override path could be forced to front- or back-end via
	 * the $override parameter e.g. <code> e107::templatePath(plug_name, 'my', 'front')</code>
	 * Example:
	 * <code>
	 * echo e107::templatePath(plug_name, 'my');
	 * // result is something like:
	 * // e107_themes/current_theme/templates/plug_name/my_template.php
	 * // or if not found
	 * // e107_plugins/plug_name/templates/my_template.php
	 * </code>
	 *
	 * @see getThemeInfo()
	 * @param string $plug_name plugin name
	 * @param string $id part of the path/file name without _template.php part
	 * @param boolean|string $override default true
	 * @return string relative path
	 */
	public static function templatePath($plug_name, $id, $override = true)
	{
		$id = str_replace('..', '', $id); //simple security, '/' is allowed
		$plug_name = preg_replace('#[^a-z0-9_]#i', '', $plug_name); // only latin allowed, so \w not a solution since PHP5.3
		$override_path = $override ? self::getThemeInfo($override, 'rel').'templates/'.$plug_name.'/'.$id.'_template.php' : null;
		$default_path = e_PLUGIN.$plug_name.'/templates/'.$id.'_template.php';

		return ($override_path && is_readable($override_path) ? $override_path : $default_path);
	}

	/**
	 * Get core template. Use this method for templates, which are following the
	 * new template standards:
	 * - template variables naming conventions
	 * - one array variable per template only
	 * - theme override is made now by current_theme/templates/ folder
	 *
	 * <br><br>Results are cached (depending on $id and $override so it's safe to use
	 * this method e.g. in loop for retrieving a template string. If template (or template key) is not
	 * found, <b>NULL</b> is returned.<br><br>
	 *
	 * Example usage: <code>e107::getCoreTemplate('user', 'short_start');</code>
	 * Will search for:
	 * - e107_themes/current_frontend_theme/templates/user_template.php (if $override is true)
	 * - e107_themes/templates/user_template.php (if override not found or $override is false)
	 * - $USER_TEMPLATE array which contains all user templates
	 * - $USER_TEMPLATE['short_start'] (if key is null, $USER_TEMPLATE will be returned)
	 *
	 * @param string $id - file prefix, e.g. user for user_template.php
	 * @param string|null $key
	 * @param boolean $override see {@link getThemeInfo())
	 * @param boolean $merge merge theme with core templates, default is false
	 * @return string|array
	 */
	public static function getCoreTemplate($id, $key = null, $override = true, $merge = false)
	{
		$reg_path = 'core/e107/templates/'.$id.($override ? '/ext' : '');
		$path = self::coreTemplatePath($id, $override);
		$id = str_replace('/', '_', $id);
		$ret = self::_getTemplate($id, $key, $reg_path, $path);
		if(!$merge || !$override || !is_array($ret))
		{
			return $ret;
		}
		
		// merge
		$reg_path = 'core/e107/templates/'.$id;
		$path = self::coreTemplatePath($id, false);
		$id = str_replace('/', '_', $id);
		$ret_core = self::_getTemplate($id, $key, $reg_path, $path);
		
		return (is_array($ret_core) ? array_merge($ret_core, $ret) : $ret);
	}

	/**
	 * Get plugin template. Use this method for plugin templates, which are following the
	 * new template standards:
	 * - template variables naming conventions
	 * - one array variable per template only
	 * - theme override is made now by current_theme/templates/plugin_name/ folder
	 *
	 * <br><br>Results are cached (depending on $id and $override so it's safe to use
	 * this method e.g. in loop for retrieving a template string. If template (or template key) is not
	 * found, <b>NULL</b> is returned.<br><br>
	 *
	 * Example usage: <code>e107::getTemplate('user', 'short_start');</code>
	 * Will search for:
	 * - e107_themes/current_frontend_theme/templates/user_template.php (if $override is true)
	 * - e107_themes/templates/user_template.php (if override not found or $override is false)
	 * - $USER_TEMPLATE array which contains all user templates
	 * - $USER_TEMPLATE['short_start'] (if key is null, $USER_TEMPLATE will be returned)
	 *
	 * @param string $plug_name
	 * @param string $id - file prefix, e.g. calendar for calendar_template.php
	 * @param string|null $key
	 * @param boolean $override see {@link getThemeInfo())
	 * @param boolean $merge merge theme with plugin templates, default is false
	 * @return string|array
	 */
	public static function getTemplate($plug_name, $id, $key = null, $override = true, $merge = false)
	{
		$reg_path = 'plugin/'.$plug_name.'/templates/'.$id.($override ? '/ext' : '');
		$path = self::templatePath($plug_name, $id, $override);
		$id = str_replace('/', '_', $id);
		$ret = self::_getTemplate($id, $key, $reg_path, $path);
		if(!$merge || !$override || !is_array($ret))
		{
			return $ret;
		}
		
		// merge
		$reg_path = 'plugin/'.$plug_name.'/templates/'.$id;
		$path = self::templatePath($plug_name, $id, false);
		$id = str_replace('/', '_', $id);
		$ret_plug = self::_getTemplate($id, $key, $reg_path, $path);
		
		return (is_array($ret_plug) ? array_merge($ret_plug, $ret) : $ret);
	}

	/**
	 * Return a list of available template IDs for a plugin(eg. $MYTEMPLATE['my_id'] -> array('id' => 'My Id'))
	 * @param string $plugin_name
	 * @param string $template_id [optional] if different from $plugin_name;
	 * @param mixed $where true - current theme, 'admin' - admin theme, 'front' (default)  - front theme
	 * @param boolean $merge merge theme with core/plugin layouts, default is false
	 * @return array
	 */
	public static function getLayouts($plugin_name, $template_id = '', $where = 'front', $filter_mask = '', $merge = false)
	{
		if(!$plugin_name) // Core template
		{
			$tmp = self::getCoreTemplate($template_id, null, $where, $merge);
		}
		else // Plugin template
		{
			$id = (!$template_id) ? $plugin_name : $template_id;
			$tmp = self::getTemplate($plugin_name, $id, null, $where, $merge);
		}

		$templates = array(); 
		if(!$filter_mask) 
		{
			$filter_mask = array();
		}
		elseif(!is_array($filter_mask))
		{
			$filter_mask = array($filter_mask); 
		}
		foreach($tmp as $key => $val)
		{
			// Special key INFO in format aray('layout' => array(info))
			if($key == '__INFO__')
			{
				continue;
			}
			$match = true;
			if($filter_mask)
			{
				$match = false; 
				foreach ($filter_mask as $mask)
				{
					if(preg_match($mask, $key)) //e.g. retrieve only keys starting with 'layout_'
					{
						$match = true;
						break;
					}
				}
				if(!$match) continue;
			}
			if(isset($tmp['__INFO__'][$key]))
			{
				$templates[$key] = defset($tmp['__INFO__'][$key]['title'], $tmp['__INFO__'][$key]['title']);
				continue;
			}
			$templates[$key] = implode(' ', array_map('ucfirst', explode('_', $key))); //TODO add LANS?
		}
		return $templates;
	}

	/**
	 * More abstsract template loader, used
	 * internal in {@link getTemplate()} and {@link getCoreTemplate()} methods
	 *
	 * @param string $id
	 * @param string|null $key
	 * @param string $reg_path
	 * @param string $default_path
	 * @param string $override_path
	 * @return string|array
	 */
	public static function _getTemplate($id, $key, $reg_path, $path)
	{
		$regPath = $reg_path;
		$var = strtoupper($id).'_TEMPLATE';
		
		if(null === self::getRegistry($regPath))
		{
			(deftrue('E107_DEBUG_LEVEL') ? include_once($path) : @include_once($path));
			self::setRegistry($regPath, (isset($$var) ? $$var : array()));
		}

		if(!$key)
		{
			return self::getRegistry($regPath);
		}
		$ret = self::getRegistry($regPath);
		return ($ret && is_array($ret) && isset($ret[$key]) ? $ret[$key] : (is_array($ret) ? array() : ''));
	}

	/**
	 * Load language file, replacement of include_lan()
	 *
	 * @param string $path
	 * @param boolean $force
	 * @return string
	 */
	public static function includeLan($path, $force = false)
	{
		if (!is_readable($path))
		{
			if (self::getPref('noLanguageSubs') || (e_LANGUAGE == 'English'))
			{
				return FALSE;
			}
			$path = str_replace(e_LANGUAGE, 'English', $path);
		}
		$ret = ($force) ? include($path) : include_once($path);
		return (isset($ret)) ? $ret : "";
	}

	/**
	 * Routine looks in standard paths for language files associated with a plugin or
	 * theme - primarily for core routines, which won't know for sure where the author has put them.
	 * $unitName is the name (directory path) of the plugin or theme
	 * $type determines what is to be loaded:
	 * - 'runtime' - the standard runtime language file for a plugin
	 * - 'admin' - the standard admin language file for a plugin
	 * - 'theme' - the standard language file for a plugin (these are usually pretty small, so one is enough)
	 * Otherwise, $type is treated as part of a filename within the plugin's language directory,
	 * prefixed with the current language.
	 * Returns FALSE on failure (not found).
	 * Returns the include_once error return if there is one
	 * Otherwise returns an empty string.
	 * Note - if the code knows precisely where the language file is located, use {@link getLan()}
	 * $pref['noLanguageSubs'] can be set TRUE to prevent searching for the English files if
	 * the files for the current site language don't exist.
	 *
	 * @param string $unitName
	 * @param string $type predefined types are runtime|admin|theme
	 * @return boolean|string
	 */
	public static function loadLanFiles($unitName, $type='runtime')
	{
		global $pref;
		switch ($type)
		{
			case 'runtime' :
				$searchPath[1] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'_'.$unitName.'.php';
				$searchPath[2] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'/'.$unitName.'.php';
				$searchPath[3] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'.php'; // menu language file.
				break;
			case 'admin' :
				$searchPath[1] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'_admin_'.$unitName.'.php';
				$searchPath[2] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'/'.'admin_'.$unitName.'.php';
				$searchPath[3] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'/admin/'.e_LANGUAGE.'.php';
				break;
			case 'theme' :
				$searchPath[1] = e_THEME.$unitName.'/languages/'.e_LANGUAGE.'_'.$unitName.'.php';
				$searchPath[2] = e_THEME.$unitName.'/languages/'.e_LANGUAGE.'/'.$unitName.'.php';
				break;
			default :
				$searchPath[1] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'_'.$type.'.php';
				$searchPath[2] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'/'.$type.'.php';
		}
		foreach ($searchPath as $s)			// Look for files in current language first - should usually be found
		{
			if (is_readable($s))
			{
				$ret = include_once($s);
				return (isset($ret)) ? $ret : "";
			}
		}
		if (varsettrue($pref['noLanguageSubs']) || (e_LANGUAGE == 'English'))
		{
			return FALSE;		// No point looking for the English files twice
		}

		foreach ($searchPath as $s)			// Now look for the English files
		{
			$s = str_replace(e_LANGUAGE, 'English', $s);
			if (is_readable($s))
			{
				$ret = include_once($s);
				return (isset($ret)) ? $ret : "";
			}
		}
		return FALSE;		// Nothing found
	}

	/**
	 * Prepare e107 environment
	 * This is done before e107_dirs initilization and [TODO] config include
	 * @return e107
	 */
	public function prepare_request()
	{
		// TODO - better ajax detection method (headers when possible)
		define('e_AJAX_REQUEST', isset($_REQUEST['ajax_used']));
		unset($_REQUEST['ajax_used']); // removed because it's auto-appended from JS (AJAX), could break something...

		//$GLOBALS['_E107'] - minimal mode - here because of the e_AJAX_REQUEST
		if(isset($GLOBALS['_E107']['minimal']) || e_AJAX_REQUEST)
		{
			$_e107vars = array('forceuserupdate', 'online', 'theme', 'menus', 'prunetmp');
			foreach($_e107vars as $v)
			{
				$noname = 'no_'.$v;
				if(!isset($GLOBALS['_E107'][$v]))
				{
					$GLOBALS['_E107'][$noname] = 1;
				}
				unset($GLOBALS['_E107'][$v]);
			}
		}

		// remove ajax_used=1 from query string to avoid SELF problems, ajax should always be detected via e_AJAX_REQUEST constant
		$_SERVER['QUERY_STRING'] = str_replace(array('ajax_used=1', '&&'), array('', '&'), $_SERVER['QUERY_STRING']);

		// e107 uses relative url's, which are broken by "pretty" URL's. So for now we don't support / after .php
		if(($pos = strpos($_SERVER['PHP_SELF'], '.php/')) !== false) // redirect bad URLs to the correct one.
		{
			$new_url = substr($_SERVER['PHP_SELF'], 0, $pos+4);
			$new_loc = ($_SERVER['QUERY_STRING']) ? $new_url.'?'.$_SERVER['QUERY_STRING'] : $new_url;
			header('Location: '.$new_loc);
			exit();
		}
		// If url contains a .php in it, PHP_SELF is set wrong (imho), affecting all paths.  We need to 'fix' it if it does.
		$_SERVER['PHP_SELF'] = (($pos = strpos($_SERVER['PHP_SELF'], '.php')) !== false ? substr($_SERVER['PHP_SELF'], 0, $pos+4) : $_SERVER['PHP_SELF']);

		// setup some php options
		e107::ini_set('magic_quotes_runtime',     0);
		e107::ini_set('magic_quotes_sybase',      0);
		e107::ini_set('arg_separator.output',     '&amp;');
		e107::ini_set('session.use_only_cookies', 1);
		e107::ini_set('session.use_trans_sid',    0);

		//  Ensure thet '.' is the first part of the include path
		$inc_path = explode(PATH_SEPARATOR, ini_get('include_path'));
		if($inc_path[0] != '.')
		{
			array_unshift($inc_path, '.');
			$inc_path = implode(PATH_SEPARATOR, $inc_path);
			e107_ini_set('include_path', $inc_path);
		}
		unset($inc_path);

		return $this;
	}

	/**
	 * Set base system path
	 * @return e107
	 */
	public function set_base_path($force = null)
	{
		$ssl_enabled = (null !== $force) ? $force : $this->isSecure();//(self::getPref('ssl_enabled') == 1);
		$this->base_path = $ssl_enabled ?  $this->https_path : $this->http_path;
		return $this;
	}

	/**
	 * Set various system environment constants
	 * @return e107
	 */
	public function set_constants()
	{
		define('MAGIC_QUOTES_GPC', (ini_get('magic_quotes_gpc') ? true : false));

		define('MPREFIX', $this->getMySQLConfig('prefix')); // mysql prefix

		define('CHARSET', 'utf-8'); // set CHARSET for backward compatibility

		// Define the domain name and subdomain name.
		if($_SERVER['HTTP_HOST'] && is_numeric(str_replace(".","",$_SERVER['HTTP_HOST'])))
		{
			$srvtmp = '';  // Host is an IP address.
		}
		else
		{
			$srvtmp = explode('.',str_replace('www.', '', $_SERVER['HTTP_HOST']));
		}

		define('e_SUBDOMAIN', (count($srvtmp)>2 && $srvtmp[2] ? $srvtmp[0] : false)); // needs to be available to e107_config.

		if(e_SUBDOMAIN)
		{
		   	unset($srvtmp[0]);
		}

		define('e_DOMAIN',(count($srvtmp) > 1 ? (implode('.', $srvtmp)) : false)); // if it's an IP it must be set to false.

		define('e_UC_PUBLIC', 0);
		define('e_UC_MAINADMIN', 250);
		define('e_UC_READONLY', 251);
		define('e_UC_GUEST', 252);
		define('e_UC_MEMBER', 253);
		define('e_UC_ADMIN', 254);
		define('e_UC_NOBODY', 255);

		return $this;
	}

	/**
	 * Set all environment vars and constants
	 * FIXME - remove globals
	 * @return e107
	 */
	public function set_paths()
	{
		global $DOWNLOADS_DIRECTORY, $ADMIN_DIRECTORY, $IMAGES_DIRECTORY, $THEMES_DIRECTORY, $PLUGINS_DIRECTORY,
		$FILES_DIRECTORY, $HANDLERS_DIRECTORY, $LANGUAGES_DIRECTORY, $HELP_DIRECTORY, $CACHE_DIRECTORY,
		$UPLOADS_DIRECTORY,$_E107, $MEDIA_DIRECTORY;

	//	global $NEWSIMAGES_DIRECTORY, $CUSTIMAGES_DIRECTORY;

		// ssl_enabled pref not needed anymore, scheme is auto-detected
		$this->HTTP_SCHEME = 'http';
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		{
			$this->HTTP_SCHEME =  'https';
		}

		$path = ""; $i = 0;

		if(!isset($_E107['cli']))
		{
			while (!file_exists("{$path}class2.php"))
			{
				$path .= "../";
				$i++;
			}
		}
		if($_SERVER['PHP_SELF'] == "") { $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME']; }

		$http_path = dirname($_SERVER['PHP_SELF']);
		$http_path = explode("/", $http_path);
		$http_path = array_reverse($http_path);
		$j = 0;
		while ($j < $i)
		{
			unset($http_path[$j]);
			$j++;
		}
		$http_path = array_reverse($http_path);
		$this->server_path = implode("/", $http_path)."/";
		$this->server_path = $this->fix_windows_paths($this->server_path);

		if ($this->server_path == "//")
		{
			$this->server_path = "/";
		}

		// Absolute file-path of directory containing class2.php
//		define("e_ROOT", realpath(dirname(__FILE__)."/../")."/");
		define("e_ROOT", realpath(dirname(__FILE__)."/../"));			// Specified format gives trailing slash already (at least on Windows)

		$this->relative_base_path = (!isset($_E107['cli'])) ? $path : e_ROOT;
		$this->http_path = "http://{$_SERVER['HTTP_HOST']}{$this->server_path}";
		$this->https_path = "https://{$_SERVER['HTTP_HOST']}{$this->server_path}";
		$this->file_path = $path;

		if(!defined("e_HTTP") || !defined("e_ADMIN") )
		{
			define("e_HTTP", $this->server_path);
		  	define("e_BASE", $this->relative_base_path);

//
// HTTP relative paths
//

			if(!varset($MEDIA_DIRECTORY)) // BC/Upgrade Fix.
			{
				$MEDIA_DIRECTORY = 'e107_media/';
			}

			define("e_ADMIN", e_BASE.$ADMIN_DIRECTORY);
			define("e_IMAGE", e_BASE.$IMAGES_DIRECTORY);
			define("e_THEME", e_BASE.$THEMES_DIRECTORY);
			define("e_PLUGIN", e_BASE.$PLUGINS_DIRECTORY);
			define("e_FILE", e_BASE.$FILES_DIRECTORY);
			define("e_HANDLER", e_BASE.$HANDLERS_DIRECTORY);
			define("e_LANGUAGEDIR", e_BASE.$LANGUAGES_DIRECTORY);
			define("e_DOCS", e_BASE.$HELP_DIRECTORY);
			//FIXME need another name
			define("e_MEDIA", e_BASE.$MEDIA_DIRECTORY);
//
// HTTP absolute paths
//
			define("e_ADMIN_ABS", e_HTTP.$ADMIN_DIRECTORY);
			define("e_IMAGE_ABS", e_HTTP.$IMAGES_DIRECTORY);
			define("e_THEME_ABS", e_HTTP.$THEMES_DIRECTORY);
			define("e_PLUGIN_ABS", e_HTTP.$PLUGINS_DIRECTORY);
			define("e_FILE_ABS", e_HTTP.$FILES_DIRECTORY);
			define("e_HANDLER_ABS", e_HTTP.$HANDLERS_DIRECTORY);
			define("e_LANGUAGEDIR_ABS", e_HTTP.$LANGUAGES_DIRECTORY);
			//FIXME Shouldn't be available
			define("e_MEDIA_ABS", e_HTTP.$MEDIA_DIRECTORY);

			if(isset($_SERVER['DOCUMENT_ROOT']))
			{
			  	define("e_DOCROOT", $_SERVER['DOCUMENT_ROOT']."/");
			}
			else
			{
			  	define("e_DOCROOT", false);
			}

			define("e_DOCS_ABS", e_HTTP.$HELP_DIRECTORY);

			if($CACHE_DIRECTORY)
			{
            	define("e_CACHE", e_BASE.$CACHE_DIRECTORY);
			}
			else
			{
            	//FIXME need another name
							define("e_CACHE", e_MEDIA."cache/");
			}
/*

			if($NEWSIMAGES_DIRECTORY)
			{
            	define("e_NEWSIMAGE", e_BASE.$NEWSIMAGES_DIRECTORY);
				define("e_NEWSIMAGE_ABS", e_HTTP.$NEWSIMAGES_DIRECTORY);
			}
			else
			{
            	define("e_NEWSIMAGE", e_IMAGE."newspost_images/");
				define("e_NEWSIMAGE_ABS", e_HTTP.$IMAGES_DIRECTORY."newspost_images/");
			}

			if($CUSTIMAGES_DIRECTORY)
			{
            	define("e_CUSTIMAGE", e_BASE.$CUSTIMAGES_DIRECTORY);
			}
			else
			{
            	define("e_CUSTIMAGE", e_IMAGE."custom/");
			}
*/
			if ($DOWNLOADS_DIRECTORY{0} == "/")
			{
				define("e_DOWNLOAD", $DOWNLOADS_DIRECTORY);
			}
			else
			{
				define("e_DOWNLOAD", e_BASE.$DOWNLOADS_DIRECTORY);
			}

			if(!$UPLOADS_DIRECTORY)
			{
            	$UPLOADS_DIRECTORY = $FILES_DIRECTORY."public/";
			}

			if ($UPLOADS_DIRECTORY{0} == "/")
			{
				define("e_UPLOAD", $UPLOADS_DIRECTORY);
			}
			else
			{
				define("e_UPLOAD", e_BASE.$UPLOADS_DIRECTORY);
				define("e_UPLOAD_ABS", e_HTTP.$UPLOADS_DIRECTORY);
			}
		}
		return $this;
	}

	/**
	 * Fix Windows server path
	 *
	 * @param string $path resolved server path
	 * @return string fixed path
	 */
	function fix_windows_paths($path)
	{
		$fixed_path = str_replace(array('\\\\', '\\'), array('/', '/'), $path);
		$fixed_path = (substr($fixed_path, 1, 2) == ":/" ? substr($fixed_path, 2) : $fixed_path);
		return $fixed_path;
	}

	/**
	 * Define e_PAGE, e_SELF, e_ADMIN_AREA and USER_AREA;
	 * The following files are assumed to use admin theme:
	 * 1. Any file in the admin directory (check for non-plugin added to avoid mismatches)
	 * 2. any plugin file starting with 'admin_'
	 * 3. any plugin file in a folder called admin/
	 * 4. any file that specifies $eplug_admin = TRUE; or ADMIN_AREA = TRUE;
	 * NOTE: USER_AREA = true; will force e_ADMIN_AREA to FALSE
	 * @return e107
	 */
	public function set_urls()
	{
		//global $PLUGINS_DIRECTORY,$ADMIN_DIRECTORY, $eplug_admin;
		$PLUGINS_DIRECTORY = $this->getFolder('plugins');
		$ADMIN_DIRECTORY = $this->getFolder('admin');
		$eplug_admin = vartrue($GLOBALS['eplug_admin'], false);

		$page = substr(strrchr($_SERVER['PHP_SELF'], '/'), 1);

		define('e_PAGE', $page);
		define('e_SELF', $this->HTTP_SCHEME . '://' . $_SERVER['HTTP_HOST'] . ($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME']));

		define('e_SIGNUP', e_BASE.(file_exists(e_BASE.'customsignup.php') ? 'customsignup.php' : 'signup.php'));
		define('e_LOGIN', e_BASE.(file_exists(e_BASE.'customlogin.php') ? 'customlogin.php' : 'login.php'));



		// e_SELF has the full HTML path
		$inAdminDir = FALSE;
		$isPluginDir = strpos(e_SELF,'/'.$PLUGINS_DIRECTORY) !== FALSE;		// True if we're in a plugin
		$e107Path = str_replace($this->base_path, '', e_SELF);				// Knock off the initial bits

		if	(
			 (!$isPluginDir && strpos($e107Path, $ADMIN_DIRECTORY) === 0 ) 									// Core admin directory
			  || ($isPluginDir && (strpos(e_PAGE,'admin_') === 0 || strpos($e107Path, 'admin/') !== FALSE)) // Plugin admin file or directory
			  || (varsettrue($eplug_admin) || defsettrue('ADMIN_AREA'))		// Admin forced
			)
		{
			$inAdminDir = TRUE;
		}

		// This should avoid further checks - NOTE: used in js_manager.php
		define('e_ADMIN_AREA', ($inAdminDir  && !deftrue('USER_AREA'))); //Force USER_AREA added

		define('ADMINDIR', $ADMIN_DIRECTORY);

		define('SITEURLBASE', $this->HTTP_SCHEME.'://'.$_SERVER['HTTP_HOST']);
		define('SITEURL', SITEURLBASE.e_HTTP);

		return $this;
	}

	/**
	 * Set request related constants
	 * @return e107
	 */
	public function set_request()
	{
		$inArray = array("'", ';', '/**/', '/UNION/', '/SELECT/', 'AS ');
		if (strpos($_SERVER['PHP_SELF'], 'trackback') === false)
		{
			foreach($inArray as $res)
			{
				if(stristr($_SERVER['QUERY_STRING'], $res))
				 {
					die('Access denied.');
				}
			}
		}

		if (strpos($_SERVER['QUERY_STRING'], ']') && preg_match("#\[(.*?)](.*)#", $_SERVER['QUERY_STRING'], $matches))
		{
			define('e_MENU', $matches[1]);
			$e_QUERY = $matches[2];
			if(strlen(e_MENU) == 2) // language code ie. [fr]
			{
		        require_once(e_HANDLER."language_class.php");
				$slng = new language;
				define('e_LANCODE', true);
				$_GET['elan'] = $slng->convert(e_MENU);
			}
		}
		else
		{
			define('e_MENU', '');
			$e_QUERY = $_SERVER['QUERY_STRING'];
		  	define('e_LANCODE', '');
		}

		$e_QUERY = str_replace("&","&amp;", self::getParser()->post_toForm($e_QUERY));
		define('e_QUERY', $e_QUERY);

		define('e_TBQS', $_SERVER['QUERY_STRING']);
		$_SERVER['QUERY_STRING'] = e_QUERY;
	}

	/**
	 * Check if current request is secure (https)
	 * @return
	 */
	public function isSecure()
	{
		return ($this->HTTP_SCHEME === 'https');
	}

	/**
	 * Check if current user is banned
	 *
	 * Generates the queries to interrogate the ban list, then calls $this->check_ban().
	 * If the user is banned, $check_ban() never returns - so a return from this routine indicates a non-banned user.
	 *
	 * @return void
	 */
	public function ban()
	{
		global $sql, $pref;
		$ban_count = $sql->db_Count('banlist');
		if($ban_count)
		{
			$vals = array();
			$ip = $this->getip(); // This will be in normalised IPV6 form
			if($ip!='x.x.x.x')
			{
				$vals[] = $ip; // Always look for exact match
				if(strpos($ip, '0000:0000:0000:0000:0000:ffff:')===0)
				{ // It's an IPV4 address
					$vals[] = substr($ip, 0, -2).'*';
					$vals[] = substr($ip, 0, -4).'*';
					$vals[] = substr($ip, 0, -7).'*'; // Knock off colon as well here
				}
				else
				{ // Its an IPV6 address - ban in blocks of 16 bits
					$vals[] = substr($ip, 0, -4).'*';
					$vals[] = substr($ip, 0, -9).'*';
					$vals[] = substr($ip, 0, -14).'*';
				}
			}
			if(varsettrue($pref['enable_rdns']))
			{
				$tmp = array_reverse(explode('.', $this->get_host_name(getenv('REMOTE_ADDR'))));
				$line = '';
				//		  $vals[] = $addr;
				foreach($tmp as $e)
				{
					$line = '.'.$e.$line;
					$vals[] = '*'.$line;
				}
			}
			if((defined('USEREMAIL')&&USEREMAIL))
			{
				$vals[] = USEREMAIL;
			}
			if(($ip!='127.0.0.1')&&count($vals))
			{
				$match = "`banlist_ip`='".implode("' OR `banlist_ip`='", $vals)."'";
				$this->check_ban($match);
			}
		}
	}

	/**
	 * Check the banlist table. $query is used to determine the match.
	 * If $do_return, will always return with ban status - TRUE for OK, FALSE for banned.
	 * If return permitted, will never display a message for a banned user; otherwise will display any message then exit
	 * XXX - clean up
	 *
	 * @param string $query
	 * @param boolean $show_error
	 * @param boolean $do_return
	 * @return boolean
	 */
	public function check_ban($query, $show_error = TRUE, $do_return = FALSE)
	{
		global $sql, $tp, $pref, $admin_log;
		//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Check for Ban",$query,FALSE,LOG_TO_ROLLING);
		if($sql->db_Select('banlist', '*', $query.' ORDER BY `banlist_bantype` DESC'))
		{
			// Any whitelist entries will be first - so we can answer based on the first DB record read
			define('BAN_TYPE_WHITELIST', 100); // Entry for whitelist
			$row = $sql->db_Fetch();
			if($row['banlist_bantype']>=BAN_TYPE_WHITELIST)
			{
				//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Whitelist hit",$query,FALSE,LOG_TO_ROLLING);
				return TRUE;
			}
			// Found banlist entry in table here
			if(($row['banlist_banexpires']>0)&&($row['banlist_banexpires']<time()))
			{ // Ban has expired - delete from DB
				$sql->db_Delete('banlist', $query);
				return TRUE;
			}
			if(varsettrue($pref['ban_retrigger'])&&varsettrue($pref['ban_durations'][$row['banlist_bantype']]))
			{ // May need to retrigger ban period
				$sql->db_Update('banlist', "`banlist_banexpires`=".intval(time()+($pref['ban_durations'][$row['banlist_bantype']]*60*60)), "WHERE `banlist_ip`='{$row['banlist_ip']}'");
				//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Retrigger Ban",$row['banlist_ip'],FALSE,LOG_TO_ROLLING);
			}
			//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Active Ban",$query,FALSE,LOG_TO_ROLLING);
			if($show_error)
				header("HTTP/1.1 403 Forbidden", true);
			if(isset($pref['ban_messages']))
			{ // May want to display a message
				// Ban still current here
				if($do_return)
					return FALSE;
				echo $tp->toHTML(varsettrue($pref['ban_messages'][$row['banlist_bantype']])); // Show message if one set
			}
			$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, 'BAN_03', 'LAN_AUDIT_LOG_003', $query, FALSE, LOG_TO_ROLLING);
			exit();
		}
		//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","No ban found",$query,FALSE,LOG_TO_ROLLING);
		return TRUE; // Email address OK
	}


	/**
	 * Add an entry to the banlist. $bantype = 1 for manual, 2 for flooding, 4 for multiple logins
	 * Returns TRUE if ban accepted.
	 * Returns FALSE if ban not accepted (i.e. because on whitelist, or invalid IP specified)
	 * FIXME - remove $admin_log global, add admin_log method getter instead
	 *
	 * @param string $bantype
	 * @param string $ban_message
	 * @param string $ban_ip
	 * @param integer $ban_user
	 * @param string $ban_notes
	 *
	 * @return boolean check result
	 */
	public function add_ban($bantype, $ban_message = '', $ban_ip = '', $ban_user = 0, $ban_notes = '')
	{
		global $sql, $pref, $e107, $admin_log;

		if(!$ban_message)
		{
			$ban_message = 'No explanation given';
		}
		if(!$ban_ip)
		{
			$ban_ip = $this->getip();
		}
		$ban_ip = preg_replace('/[^\w@\.]*/', '', urldecode($ban_ip)); // Make sure no special characters
		if(!$ban_ip)
		{
			return FALSE;
		}
		// See if the address is in the whitelist
		if($sql->db_Select('banlist', '*', '`banlist_bantype` >= '.BAN_TYPE_WHITELIST))
		{ // Got a whitelist entry for this
			$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "BANLIST_11", 'LAN_AL_BANLIST_11', $ban_ip, FALSE, LOG_TO_ROLLING);
			return FALSE;
		}
		if(varsettrue($pref['enable_rdns_on_ban']))
		{
			$ban_message .= 'Host: '.$e107->get_host_name($ban_ip);
		}
		// Add using an array - handles DB changes better
		$sql->db_Insert('banlist', array('banlist_ip' => $ban_ip , 'banlist_bantype' => $bantype , 'banlist_datestamp' => time() , 'banlist_banexpires' => (varsettrue($pref['ban_durations'][$bantype]) ? time()+($pref['ban_durations'][$bantype]*60*60) : 0) , 'banlist_admin' => $ban_user , 'banlist_reason' => $ban_message , 'banlist_notes' => $ban_notes));
		return TRUE;
	}

	/**
	 * Get the current user's IP address
	 * returns the address in internal 'normalised' IPV6 format - so most code should continue to work provided the DB Field is big enougn
	 *
	 * @return string
	 */
	public function getip()
	{
		if(!$this->_ip_cache)
		{
			if(getenv('HTTP_X_FORWARDED_FOR'))
			{
				$ip = $_SERVER['REMOTE_ADDR'];
				$ip3 = array();
				if(preg_match('/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', getenv('HTTP_X_FORWARDED_FOR'), $ip3))
				{
					$ip2 = array(
						'#^0\..*#' , '#^127\..*#' , // Local loopbacks
						'#^192\.168\..*#' , // RFC1918 - Private Network
						'#^172\.(?:1[6789]|2\d|3[01])\..*#' , // RFC1918 - Private network
						'#^10\..*#' , // RFC1918 - Private Network
						'#^169\.254\..*#' , // RFC3330 - Link-local, auto-DHCP
						'#^2(?:2[456789]|[345][0-9])\..*#'
					); // Single check for Class D and Class E

					$ip = preg_replace($ip2, $ip, $ip3[1]);
				}
			}
			else
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			if($ip == "")
			{
				$ip = "x.x.x.x";
			}
			$this->_ip_cache = $this->ipEncode($ip); // Normalise for storage
		}
		return $this->_ip_cache;
	}

	/**
	 * Encode an IP address to internal representation. Returns string if successful; FALSE on error
	 * Default separates fields with ':'; set $div='' to produce a 32-char packed hex string
	 *
	 * @param string $ip
	 * @param string $div divider
	 * @return string encoded IP
	 */
	public function ipEncode($ip, $div = ':')
	{
		$ret = '';
		$divider = '';
		if(strpos($ip, ':')!==FALSE)
		{ // Its IPV6 (could have an IP4 'tail')
			if(strpos($ip, '.')!==FALSE)
			{ // IPV4 'tail' to deal with
				$temp = strrpos($ip, ':')+1;
				$ipa = explode('.', substr($ip, $temp));
				$ip = substr($ip, 0, $temp).sprintf('%02x%02x:%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
			}
			// Now 'normalise' the address
			$temp = explode(':', $ip);
			$s = 8-count($temp); // One element will of course be the blank
			foreach($temp as $f)
			{
				if($f=='')
				{
					$ret .= $divider.'0000'; // Always put in one set of zeros for the blank
					$divider = $div;
					if($s>0)
					{
						$ret .= str_repeat($div.'0000', $s);
						$s = 0;
					}
				}
				else
				{
					$ret .= $divider.sprintf('%04x', hexdec($f));
					$divider = $div;
				}
			}
			return $ret;
		}
		if(strpos($ip, '.')!==FALSE)
		{ // Its IPV4
			$ipa = explode('.', $ip);
			$temp = sprintf('%02x%02x%s%02x%02x', $ipa[0], $ipa[1], $div, $ipa[2], $ipa[3]);
			return str_repeat('0000'.$div, 5).'ffff'.$div.$temp;
		}
		return FALSE; // Unknown
	}

	/**
	 * Takes an encoded IP address - returns a displayable one
	 * Set $IP4Legacy TRUE to display 'old' (IPv4) addresses in the familiar dotted format,
	 * FALSE to display in standard IPV6 format
	 * Should handle most things that can be thrown at it.
	 *
	 * @param string $ip encoded IP
	 * @param boolean $IP4Legacy
	 * @return string decoded IP
	 */
	public function ipDecode($ip, $IP4Legacy = TRUE)
	{
		if (strstr($ip,'.'))
		{
			if ($IP4Legacy) return $ip;			// Assume its unencoded IPV4
			$ipa = explode('.', $ip);
			$ip = '0:0:0:0:0:ffff:'.sprintf('%02x%02x:%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
		}
		if (strstr($ip,'::')) return $ip;			// Assume its a compressed IPV6 address already
		if ((strlen($ip) == 8) && !strstr($ip,':'))
		{	// Assume a 'legacy' IPV4 encoding
			$ip = '0:0:0:0:0:ffff:'.implode(':',str_split($ip,4));		// Turn it into standard IPV6
		}
		elseif ((strlen($ip) == 32) && !strstr($ip,':'))
		{  // Assume a compressed hex IPV6
			$ip = implode(':',str_split($ip,4));
		}
		if (!strstr($ip,':')) return FALSE;			// Return on problem - no ':'!
		$temp = explode(':',$ip);
		$z = 0;		// State of the 'zero manager' - 0 = not started, 1 = running, 2 = done
		$ret = '';
		$zc = 0;			// Count zero fields (not always required)
		foreach ($temp as $t)
		{
			$v = hexdec($t);
			if (($v != 0) || ($z == 2))
			{
				if ($z == 1)
				{ // Just finished a run of zeros
					$z++;
					$ret .= ':';
				}
				if ($ret) $ret .= ':';
				$ret .= sprintf('%x',$v);				// Drop leading zeros
			}
			else
			{  // Zero field
				$z = 1;
				$zc++;
			}
		}
		if ($z == 1)
		{  // Need to add trailing zeros, or double colon
			if ($zc > 1) $ret .= '::'; else $ret .= ':0';
		}
		if ($IP4Legacy && (substr($ret,0,7) == '::ffff:'))
		{
			$temp = explode(':',substr($ret,7));		// Should give us two 16-bit hex values
			$z = array();
			foreach ($temp as $t)
			{
				$zc = hexdec($t);
				$z[] = intval($zc / 256);		// intval needed to avoid small rounding error
				$z[] = $zc % 256;
			}
			$ret = implode('.',$z);
		}

		return $ret;
	}

	/**
	 * Given a string which may be IP address, email address etc, tries to work out what it is
	 *
	 * @param string $string
	 * @return string ip|email|url|ftp|unknown
	 */
	public function whatIsThis($string)
	{
		if (strstr($string,'@')) return 'email';		// Email address
		if (strstr($string,'http://')) return 'url';
		if (strstr($string,'ftp://')) return 'ftp';
		$string = strtolower($string);
		if (str_replace(' ', '', strtr($string,'0123456789abcdef.:*', '                   ')) == '')	// Delete all characters found in ipv4 or ipv6 addresses, plus wildcards
		{
			return 'ip';
		}
		return 'unknown';
	}

	/**
	 * Retrieve & cache host name
	 *
	 * @param string $ip_address
	 * @return string host name
	 */
	public function get_host_name($ip_address)
	{
		if(!$this->_host_name_cache[$ip_address])
		{
			$this->_host_name_cache[$ip_address] = gethostbyaddr($ip_address);
		}
		return $this->_host_name_cache[$ip_address];
	}

	/**
	 * Return a memory value formatted helpfully
	 * $dp overrides the number of decimal places displayed - realistically, only 0..3 are sensible
	 * FIXME e107->parseMemorySize() START
	 * - maybe we are in need of General Helper handler, this + the above ban/ip related methods
	 * are not fitting e107 class logic anymore
	 * - change access to public static - more useful
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
	public function parseMemorySize($size, $dp = 2)
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
	public function get_memory_usage($separator = '/')
	{
		$ret = array();
		if(function_exists("memory_get_usage"))
		{
	      $ret[] = $this->parseMemorySize(memory_get_usage());
		  // With PHP>=5.2.0, can show peak usage as well
	      if (function_exists("memory_get_peak_usage")) $ret[] = $this->parseMemorySize(memory_get_peak_usage(TRUE));
		}
		else
		{
		  $ret[] = 'Unknown';
		}

		return (null !== $separator ? implode($separator, $ret) : $ret);
	}

	/**
	 * Check if plugin is installed
	 * @param string $plugname
	 * @return boolean
	 */
	public static function isInstalled($plugname)
	{
		// Could add more checks here later if appropriate
		return self::getConfig()->isData('plug_installed/'.$plugname);
	}

	/**
	 * Safe way to set ini var
	 * @param string $var
	 * @param string $value
	 * @return
	 */
	public static function ini_set($var, $value)
	{
		if (function_exists('ini_set'))
		{
			return ini_set($var, $value);
		}
		return false;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'tp':
				$ret = e107::getParser();
			break;

			case 'sql':
				$ret = e107::getDb();
			break;

			case 'ecache':
				$ret = e107::getCache();
			break;

			case 'arrayStorage':
				$ret = e107::getArrayStorage();
			break;

			case 'e_event':
				$ret = e107::getEvent();
			break;

			case 'ns':
				$ret = e107::getRender();
			break;

			case 'url':
				$ret = e107::getUrl();
			break;

			case 'admin_log':
				$ret = e107::getAdminLog();
			break;

			case 'override':
				$ret = e107::getSingleton('override', e_HANDLER.'override_class.php');
			break;

			case 'notify':
				$ret = e107::getNotify();
			break;

			case 'e_online':
				$ret = e107::getOnline();
			break;

			case 'user_class':
				$ret = e107::getUserClass();
			break;

			default:
				trigger_error('$e107->$'.$name.' not defined', E_USER_WARNING);
				return null;
			break;
		}

		$this->{$name} = $ret;
		return $ret;
	}
}
?>