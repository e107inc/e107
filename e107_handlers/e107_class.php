<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $URL$
 * $Id$
*/

if (!defined('e107_INIT')) { exit; }


/**
 *
 * @package     e107
 * @category	e107_handlers
 * @version     $Id$
 * @author      e107inc
 *
 *	e107_class - core class with many system-related methods
 */

class e107
{
	/**
	 * IPV6 string for localhost - as stored in DB
	 */
	const LOCALHOST_IP = '0000:0000:0000:0000:0000:ffff:7f00:0001';
	const LOCALHOST_IP2 = '0000:0000:0000:0000:0000:0000:0000:0001';

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
	public $site_path;
	public $relative_base_path;
	public $_ip_cache;
	public $_host_name_cache;

	public $site_theme; // class2 -> check valid theme
	public $http_theme_dir; // class2 -> check valid theme

	/**
	 * Contains reference to global $_E107 array
	 * Assignment is done inside prepare_request() method
	 *
	 * @var array
	 */
	protected $_E107 = array();

	/**
	 * @var string Current request type (http or https)
	 */
	protected $HTTP_SCHEME;

	/**
	 * Used for runtime caching of user extended struct
	 *
	 * @var array
	 * @see e107::user()
	 */
	public $extended_struct;

	/**
	 * User login name
	 *
	 * @var array
	 * @see init_session()
	 */
	public $currentUser;

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
	 * e107 theme config object storage
	 *
	 * @var array
	 */
	protected static $_theme_config_arr = array();



	/**
	 * e107 e107::css() on/off flag.
	 *
	 * @var bool
	 */
	protected static $_css_enabled = true;

	/**
	 * e107  e107::js() on/off flag.
	 *
	 * @var bool
	 */
	protected static $_js_enabled = true;




	protected static $_breadcrumb = array();

	/**
	 * Core handlers array
	 * For new/missing handler add
	 * 'class name' => 'path' pair
	 *
	 * Used to auto-load core/plugin handlers
	 * NOTE: aplhabetically sorted! (by class name)
	 *
	 * @see addHandler()
	 * @see setHandlerOverload()
	 * @see getSingleton()
	 * @see getObject()
	 * @var array
	 */
	protected static $_known_handlers = array(
		'UserHandler'                    => '{e_HANDLER}user_handler.php',
		'comment'                        => '{e_HANDLER}comment_class.php',
		'e_date'                         => '{e_HANDLER}date_handler.php',
		'convert'                        => '{e_HANDLER}date_handler.php', // BC Fix.
		'db'                             => '{e_HANDLER}e_db_pdo_class.php',
	//	'db'                             => '{e_HANDLER}mysql_class.php',
		'e107Email'                      => '{e_HANDLER}mail.php',
		'e107_event'                     => '{e_HANDLER}event_class.php',
		'e107_db_debug'                  => '{e_HANDLER}db_debug_class.php',
		'e107_traffic'                   => '{e_HANDLER}traffic_class.php',
		'e107_user_extended'             => '{e_HANDLER}user_extended_class.php',
		'e107plugin'                     => '{e_HANDLER}plugin_class.php',
		'e_chart'                        => '{e_HANDLER}chart_class.php',
		'e_core_session'                 => '{e_HANDLER}session_handler.php',
		'e_admin_controller'             => '{e_HANDLER}admin_ui.php',
		'e_admin_controller_ui'          => '{e_HANDLER}admin_ui.php',
		'e_admin_dispatcher'             => '{e_HANDLER}admin_ui.php',
		'e_admin_form_ui'                => '{e_HANDLER}admin_ui.php',
		'e_admin_log'                    => '{e_HANDLER}admin_log_class.php',
		'e_front_model'                  => '{e_HANDLER}model_class.php',
		'e_admin_model'                  => '{e_HANDLER}model_class.php',
		'e_admin_request'                => '{e_HANDLER}admin_ui.php',
		'e_admin_response'               => '{e_HANDLER}admin_ui.php',
		'e_admin_ui'                     => '{e_HANDLER}admin_ui.php',
		'e_ajax_class'                   => '{e_HANDLER}e_ajax_class.php',
		'e_array'                        => '{e_HANDLER}core_functions.php', // Old ArrayStorage.
		'e_bbcode'                       => '{e_HANDLER}bbcode_handler.php',
		'e_bb_base'                      => '{e_HANDLER}bbcode_handler.php',
		'e_customfields'                 => '{e_HANDLER}e_customfields_class.php',
		'e_file'                         => '{e_HANDLER}file_class.php',
		'e_form'                         => '{e_HANDLER}form_handler.php',
		'e_jshelper'                     => '{e_HANDLER}js_helper.php',
		'e_media'                        => '{e_HANDLER}media_class.php',
		'e_menu'                         => '{e_HANDLER}menu_class.php',
		'e_model'                        => '{e_HANDLER}model_class.php',
		'e_navigation'                   => '{e_HANDLER}sitelinks_class.php',
		'e_news_item'                    => '{e_HANDLER}news_class.php',
		'e_news_tree'                    => '{e_HANDLER}news_class.php',
		'e_news_category_tree'           => '{e_HANDLER}news_class.php',
		'e_object'                       => '{e_HANDLER}model_class.php',
		'e_online'                       => '{e_HANDLER}online_class.php',
		'e_parse'                        => '{e_HANDLER}e_parse_class.php',
		'e_parser'                       => '{e_HANDLER}e_parse_class.php',
		'e_parse_shortcode'              => '{e_HANDLER}shortcode_handler.php',
		'e_plugin'                       => '{e_HANDLER}plugin_class.php',
		'e_ranks'                        => '{e_HANDLER}e_ranks_class.php',
		'e_shortcode'                    => '{e_HANDLER}shortcode_handler.php',
		'e_system_user'                  => '{e_HANDLER}user_model.php',
		'e_theme'                        => '{e_HANDLER}theme_handler.php',
		'e_upgrade'                      => '{e_HANDLER}e_upgrade_class.php',
		'e_user_model'                   => '{e_HANDLER}user_model.php',
		'e_user'                         => '{e_HANDLER}user_model.php',
		'e_user_extended_structure_tree' => '{e_HANDLER}user_model.php',
		'e_userperms'                    => '{e_HANDLER}user_handler.php',
		'e_validator'                    => '{e_HANDLER}validator_class.php',
		'e_vars'                         => '{e_HANDLER}model_class.php',
		'e_url'                          => '{e_HANDLER}application.php',
		'ecache'                         => '{e_HANDLER}cache_handler.php',
		'eController'                    => '{e_HANDLER}application.php',
		'eDispatcher'                    => '{e_HANDLER}application.php',
		'eException'                     => '{e_HANDLER}application.php',
		'eFront'                         => '{e_HANDLER}application.php',
		'eHelper'                        => '{e_HANDLER}application.php',
		'eIPHandler'                     => '{e_HANDLER}iphandler_class.php',
		'email_validation_class'         => '{e_HANDLER}mail_validation_class.php',
		'eMessage'                       => '{e_HANDLER}message_handler.php',
		'eRequest'                       => '{e_HANDLER}application.php',
		'eResponse'                      => '{e_HANDLER}application.php',
		'eRouter'                        => '{e_HANDLER}application.php',
		'eShims'                         => '{e_HANDLER}Shims/eShims.php',
		'eUrl'                           => '{e_HANDLER}e107Url.php',
		'eUrlConfig'                     => '{e_HANDLER}application.php',
		'eUrlRule'                       => '{e_HANDLER}application.php',
		'Hybrid_Auth'                    => '{e_HANDLER}hybridauth/Hybrid/Auth.php',
		'language'                       => '{e_HANDLER}language_class.php',
		'news'                           => '{e_HANDLER}news_class.php',
		'notify'                         => '{e_HANDLER}notify_class.php',
		'override'                       => '{e_HANDLER}override_class.php',
		'rater'                          => '{e_HANDLER}rate_class.php',
		'redirection'                    => '{e_HANDLER}redirection_class.php',
		'secure_image'                   => '{e_HANDLER}secure_img_handler.php',
		'sitelinks'                      => '{e_HANDLER}sitelinks_class.php',
		'themeHandler'                   => '{e_HANDLER}theme_handler.php',
		'user_class'                     => '{e_HANDLER}userclass_class.php',
		'user_class_admin'               => '{e_HANDLER}userclass_class.php',
		'userlogin'                      => '{e_HANDLER}login.php',
		'validatorClass'                 => '{e_HANDLER}validator_class.php',
		'xmlClass'                       => '{e_HANDLER}xml_class.php',
		'e107MailManager'                => '{e_HANDLER}mail_manager_class.php',
		'e_library_manager'              => '{e_HANDLER}library_manager.php',
		'error_page'                     => '{e_HANDLER}error_page_class.php',
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
	 * is not possible for singleton objects
	 *
	 */
	protected function __construct()
	{
		// FIXME registered shutdown functions not executed after the $page output in footer - investigate
		// Currently manually called in front-end/admin footer
		//register_shutdown_function(array($this, 'destruct'));
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
	 * @param $e107_paths
	 * @param $e107_root_path
	 * @param $e107_config_mysql_info
	 * @param array $e107_config_override
	 * @return e107
	 */
	public function initCore($e107_paths, $e107_root_path, $e107_config_mysql_info, $e107_config_override = array())
	{

		return $this->_init($e107_paths, $e107_root_path, $e107_config_mysql_info, $e107_config_override);
	}

	/**
	 * Initialize environment path constants while installing e107
	 *
	 * @param $e107_paths
	 * @param $e107_root_path
	 * @param array $e107_config_override
	 * @return e107
	 */
	public function initInstall($e107_paths, $e107_root_path, $e107_config_override = array())
	{

		$e107_config = 'e107_config.php';
		if (!file_exists($e107_config))  // prevent blank-page with missing file during install.
		{
			if(file_put_contents($e107_config, '')===false)
			{
				return false;
			}
		}

		// Do some security checks/cleanup, prepare the environment
		$this->prepare_request();

		//generated from mysql data at stage 5 of install.
		$this->site_path = isset($e107_config_override['site_path']) ? $e107_config_override['site_path'] : "[hash]"; // placeholder

		// folder info
		//$this->e107_dirs = $e107_paths;
		$this->setDirs($e107_paths, $e107_config_override);

		// build all paths
		$this->set_paths();
		$this->file_path = $this->fix_windows_paths($e107_root_path)."/";

		// set base path, SSL is auto-detected
		$this->set_base_path();

		// cleanup QUERY_STRING and friends, set  related constants
		$this->set_request();

		// set some core URLs (e_LOGIN/SIGNUP)
		$this->set_urls();

		return $this;
	}

	/**
	 * Resolve paths, will run only once
	 *
	 * @param $e107_paths
	 * @param $e107_root_path
	 * @param $e107_config_mysql_info
	 * @param array $e107_config_override
	 * @return e107
	 */
	protected function _init($e107_paths, $e107_root_path, $e107_config_mysql_info, $e107_config_override = array())
	{
		if(!empty($this->e107_dirs)) return $this;

		// Do some security checks/cleanup, prepare the environment
		$this->prepare_request();

		// mysql connection info
		$this->e107_config_mysql_info = $e107_config_mysql_info;

		// unique folder for e_MEDIA - support for multiple websites from single-install. Must be set before setDirs()
	/*	if (!empty($e107_config_override['site_path']))
		{
			// $E107_CONFIG['site_path']
			$this->site_path = $e107_config_override['site_path'];
		}*/

		if(empty($e107_config_override['site_path']))
		{
			$this->site_path = $this->makeSiteHash($e107_config_mysql_info['mySQLdefaultdb'], $e107_config_mysql_info['mySQLprefix']);
		}

		// Set default folder (and override paths) if missing from e107_config.php
		$this->setDirs($e107_paths, $e107_config_override);

		// various constants - MAGIC_QUOTES_GPC, MPREFIX, ...
		$this->set_constants();

		// build all paths
		$this->set_paths();
		$this->file_path = $this->fix_windows_paths($e107_root_path);

		// set base path, SSL is auto-detected
		$this->set_base_path();

		// cleanup QUERY_STRING and friends, set  related constants
		$this->set_request();

		// set some core URLs (e_LOGIN/SIGNUP)
		$this->set_urls();

		if(!is_dir(e_SYSTEM))
		{
			mkdir(e_SYSTEM, 0755);
		}

		if(!is_dir(e_CACHE_IMAGE))
		{
			mkdir(e_CACHE_IMAGE, 0755);
		}

		// Prepare essential directories.
		$this->prepareDirs();

		return $this;
	}

	/**
	 * Create a unique hash for each database configuration (multi-site support).
	 */
	function makeSiteHash($db, $prefix) // also used by install.
	{
		return substr(md5($db . "." . $prefix), 0, 10);
	}

	/**
	 * Set system folders and override paths
	 * $e107_paths is the 'compact' version of e107_config folder vars ($ADMIN_DIRECTORY, $IMAGES_DIRECTORY, etc)
	 * $e107_config_override is the new override method - it can do it for all server and http paths via
	 * the newly introduced $E107_CONFIG array.
	 *
	 * Overriding just replace _DIRECTORY with _SERVER or _HTTP:
	 * - override server path example:
	 * <code>$E107_CONFIG['SYSTEM_SERVER'] = '/home/user/system/';</code>
	 *
	 * - override http path example:
	 * <code>$E107_CONFIG['MEDIA_VIDEOS_HTTP'] = 'http://static.mydomain.com/videos/';</code>
	 *
	 * @param array $e107_dirs Override folder instructions (*_DIRECTORY vars - e107_config.php)
	 * @param array $e107_config_override Override path insructions ($E107_CONFIG array - e107_config.php)
	 * @return e107
	 */
	public function setDirs($e107_dirs, $e107_config_override = array())
	{
		if(!empty($e107_config_override['site_path'])) // $E107_CONFIG['site_path']
		{
			$this->site_path = $e107_config_override['site_path'];
		}

		$override = array_merge((array) $e107_dirs, (array) $e107_config_override);

		// override all
		$this->e107_dirs = array_merge($this->defaultDirs($override), $override);

		// Required for e_MEDIA_BASE, e_SYSTEM_BASE (free of site path constants);
	//	$this->e107_dirs['MEDIA_BASE_DIRECTORY'] = $this->e107_dirs['MEDIA_DIRECTORY'];
	//	$this->e107_dirs['SYSTEM_BASE_DIRECTORY'] = $this->e107_dirs['SYSTEM_BASE_DIRECTORY'];

		// FIXME - remove this condition because:
		// $this->site_path is appended to MEDIA_DIRECTORY in defaultDirs(), which is called above.
		if(strpos($this->e107_dirs['MEDIA_DIRECTORY'],$this->site_path) === false)
		{
			$this->e107_dirs['MEDIA_DIRECTORY'] .= $this->site_path."/"; // multisite support.
		}

		// FIXME - remove this condition because:
		// $this->site_path is appended to SYSTEM_DIRECTORY in defaultDirs(), which is called above.
		if(strpos($this->e107_dirs['SYSTEM_DIRECTORY'],$this->site_path) === false)
		{
			$this->e107_dirs['SYSTEM_DIRECTORY'] .= $this->site_path."/"; // multisite support.
		}

		// FIXME Quick fix - override base cache folder for legacy configs (e.g. e107_files/cache), discuss
		if(strpos($this->e107_dirs['CACHE_DIRECTORY'], $this->site_path) === false)
		{
			$this->e107_dirs['CACHE_DIRECTORY'] = $this->e107_dirs['SYSTEM_DIRECTORY']."cache/"; // multisite support.
		}

		return $this;
	}

	/**
	 * Prepares essential directories.
	 */
	public function prepareDirs()
	{
		$file = e107::getFile();

		// Essential directories which should be created and writable.
		$essential_directories = array(
			'MEDIA_DIRECTORY',
			'SYSTEM_DIRECTORY',
			'CACHE_DIRECTORY',

			'CACHE_CONTENT_DIRECTORY',
			'CACHE_IMAGE_DIRECTORY',
			'CACHE_DB_DIRECTORY',
			'CACHE_URL_DIRECTORY',

			'LOGS_DIRECTORY',
			'BACKUP_DIRECTORY',
			'TEMP_DIRECTORY',
			'IMPORT_DIRECTORY',
		);

		// Create directories which don't exist.
		foreach($essential_directories as $directory)
		{
			if (!isset($this->e107_dirs[$directory])) {
				continue;
			}

			$path = e_ROOT . $this->e107_dirs[$directory];
			$file->prepareDirectory($path, FILE_CREATE_DIRECTORY);
		}
	}

	/**
	 * Get default e107 folders, root folders can be overridden by passed override array
	 *
	 * @param array $override_root
	 * @param boolean $return_root
	 * @return array
	 */
	public function defaultDirs($override_root = array(), $return_root = false)
	{
		$ret = array_merge(array(
			'ADMIN_DIRECTORY' 		=> 'e107_admin/',
			'IMAGES_DIRECTORY' 		=> 'e107_images/',
			'THEMES_DIRECTORY' 		=> 'e107_themes/',
			'PLUGINS_DIRECTORY' 	=> 'e107_plugins/',
			'FILES_DIRECTORY' 		=> 'e107_files/', // DEPRECATED!!!
			'HANDLERS_DIRECTORY' 	=> 'e107_handlers/',
			'LANGUAGES_DIRECTORY' 	=> 'e107_languages/',
			'DOCS_DIRECTORY' 		=> 'e107_docs/',
			'MEDIA_DIRECTORY' 		=> 'e107_media/',
			'SYSTEM_DIRECTORY' 		=> 'e107_system/',
			'CORE_DIRECTORY' 		=> 'e107_core/',
			'WEB_DIRECTORY' 		=> 'e107_web/',
		), (array) $override_root);

		$ret['MEDIA_BASE_DIRECTORY'] = $ret['MEDIA_DIRECTORY'];
		$ret['SYSTEM_BASE_DIRECTORY'] = $ret['SYSTEM_DIRECTORY'];
		$ret['MEDIA_DIRECTORY'] 	.= $this->site_path."/"; // multisite support.
		$ret['SYSTEM_DIRECTORY'] 	.= $this->site_path."/"; // multisite support.

		if($return_root) return $ret;

		$ret['HELP_DIRECTORY'] 				= $ret['DOCS_DIRECTORY'].'help/';

		$ret['MEDIA_IMAGES_DIRECTORY'] 		= $ret['MEDIA_DIRECTORY'].'images/';
		$ret['MEDIA_ICONS_DIRECTORY'] 		= $ret['MEDIA_DIRECTORY'].'icons/';

		$ret['MEDIA_VIDEOS_DIRECTORY'] 		= $ret['MEDIA_DIRECTORY'].'videos/';
		$ret['MEDIA_FILES_DIRECTORY'] 		= $ret['MEDIA_DIRECTORY'].'files/';
		$ret['MEDIA_UPLOAD_DIRECTORY'] 		= $ret['SYSTEM_DIRECTORY'].'temp/'; // security measure. Media is public, system is private.
		$ret['AVATARS_DIRECTORY'] 			= $ret['MEDIA_DIRECTORY'].'avatars/';

		$ret['WEB_JS_DIRECTORY'] 			= $ret['WEB_DIRECTORY'].'js/';
	//	$ret['WEB_JS_DIRECTORY'] 			= $ret['FILES_DIRECTORY'].'jslib/';


		$ret['WEB_CSS_DIRECTORY'] 			= $ret['WEB_DIRECTORY'].'css/';
		$ret['WEB_IMAGES_DIRECTORY'] 		= $ret['WEB_DIRECTORY'].'images/';
	//	$ret['WEB_PACKS_DIRECTORY'] 		= $ret['WEB_DIRECTORY'].'packages/';

		$ret['DOWNLOADS_DIRECTORY']			= $ret['MEDIA_FILES_DIRECTORY'];
		$ret['UPLOADS_DIRECTORY'] 			= $ret['MEDIA_UPLOAD_DIRECTORY'];

		$ret['CACHE_DIRECTORY'] 			= $ret['SYSTEM_DIRECTORY'].'cache/';
		$ret['CACHE_CONTENT_DIRECTORY'] 	= $ret['CACHE_DIRECTORY'].'content/';

		if(defined('e_MEDIA_STATIC')) // experimental - subject to change.
		{
			$ret['CACHE_IMAGE_DIRECTORY'] 	= $ret['MEDIA_IMAGES_DIRECTORY'].'cache/';
		}
		else
		{
			$ret['CACHE_IMAGE_DIRECTORY'] 	= $ret['CACHE_DIRECTORY'].'images/';
		}

		$ret['CACHE_DB_DIRECTORY'] 			= $ret['CACHE_DIRECTORY'].'db/';
		$ret['CACHE_URL_DIRECTORY'] 		= $ret['CACHE_DIRECTORY'].'url/';

		$ret['AVATARS_UPLOAD_DIRECTORY'] 	= $ret['AVATARS_DIRECTORY'].'upload/';
		$ret['AVATARS_DEFAULT_DIRECTORY'] 	= $ret['AVATARS_DIRECTORY'].'default/';

		$ret['LOGS_DIRECTORY'] 				= $ret['SYSTEM_DIRECTORY'].'logs/';
		$ret['BACKUP_DIRECTORY'] 			= $ret['SYSTEM_DIRECTORY'].'backup/';
		$ret['TEMP_DIRECTORY'] 				= $ret['SYSTEM_DIRECTORY'].'temp/';
		$ret['IMPORT_DIRECTORY'] 			= $ret['SYSTEM_DIRECTORY'].'import/';

		return $ret;
	}

	/**
	 * Set mysql data
	 *
	 * @param $e107_config_mysql_info
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
	 * @param null $default
	 * @return mixed
	 */
	public static function getRegistry($id, $default = null)
	{
		if(isset(self::$_registry[$id]))
		{
			return self::$_registry[$id];
		}

		if($id === '_all_')
		{
			return self::$_registry;
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
	 * @param bool $allow_override
	 */
	public static function setRegistry($id, $data = null, $allow_override = true)
	{
		if(null === $data)
		{
			if(isset(self::$_registry[$id]) && is_object(self::$_registry[$id]) && method_exists(self::$_registry[$id], '__destruct'))
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
	public static function getFolder($for)
	{
		$key = strtoupper($for).'_DIRECTORY';
		$self = self::getInstance();
		return (isset($self->e107_dirs[$key]) ? $self->e107_dirs[$key] : '');
	}

	/**
	 * Get value from $_E107 config array
	 * Note: will always return false if called before prepare_request() method!
	 *
	 * @param string $key
	 * @return boolean
	 */
	public static function getE107($key = null)
	{
		$self = self::getInstance();
		if(null === $key) return $self->_E107;
		return (isset($self->_E107[$key]) && $self->_E107[$key] ? true : false);
	}

	/**
	 * Convenient proxy to $_E107 getter - check if
	 * the system is currently running in cli mode
	 * Note: will always return false if called before prepare_request() method!
	 *
	 * @return boolean
	 */
	public static function isCli()
	{
		return self::getE107('cli');
	}

	/**
	 * Get mysql config var (e107_config.php)
	 * Replaces all $mySQL(*) globals
	 * Example: <code>$e107->getMySQLConfig('prefix');</code>
	 *
	 * @param string $for prefix|server|user|password|defaultdb - leave blank for full array.
	 * @return string|array
	 */
	public static function getMySQLConfig($for='')
	{
		$key = 'mySQL'.$for;
		$self = self::getInstance();

		if($for == '')
		{
			return 	$self->e107_config_mysql_info;
		}

		return (isset($self->e107_config_mysql_info[$key]) ? $self->e107_config_mysql_info[$key] : '');
	}


	/**
	 * Return a unique path based on database used. ie. multi-site support from single install.
	 *
	 * @return string
	 * @author
	 */
	function getSitePath()
	{
		$self = self::getInstance();
		return $self->site_path;
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
	 * @param string $class_name
	 * @param bool|object $default_handler [optional] return data from $_known_handlers if no overload data available
	 * @param bool|object $parse_path [optional] parse path shortcodes
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
	 * @param string $overload_class_name [optional]
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
	public static function getSingleton($class_name, $path = true, $regpath = '',$vars=null)
	{

		$id = 'core/e107/singleton/'.$class_name.$regpath;

		if(!empty($vars))
		{
			$id .= '/';
			$id .= is_array($vars) ? crc32(serialize($vars)): crc32($vars);
		}

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
			global $e107_debug, $_E107;

			if(($e107_debug || !empty($_E107['debug']) || (defined('e_DEBUG') && e_DEBUG === true) ))
			{
				require_once($path);
			}
			else
			{
				@require_once($path);
			}

			// remove the need for external function.
			//e107_require_once() is available without class2.php. - see core_functions.php
		}
		if(class_exists($class_name, false))
		{
			self::setRegistry($id, new $class_name($vars));
		}

		return self::getRegistry($id);
	}

	/**
	 * Retrieve object
	 * Prepare for __autoload
	 *
	 * @param string $class_name
	 * @param mixed $arguments
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
	 * @param string $name core|core_backup|emote|menu|search|notify
	 * @param bool $load
	 * @param bool $refresh
	 * @return e_core_pref
	 */
	public static function getConfig($name = 'core', $load = true, $refresh=false)
	{

		if(isset(self::$_plug_config_arr[$name])) //FIXME Load pluginPref Object instead - Not quite working with calendar_menu.
		{
			return self::getPlugConfig($name);
		}

		if(!isset(self::$_core_config_arr[$name]) || ($refresh == true)) // required by update_routines to clear out earlier values.
		{
			e107_require_once(e_HANDLER.'pref_class.php');
			self::$_core_config_arr[$name] = new e_core_pref($name, $load);

			if($name === 'core') // prevent loop between pref and cache handlers.
			{
				self::getCache()->UserCacheActive = self::getPref('cachestatus');
				self::getCache()->SystemCacheActive = self::getPref('syscachestatus');
			}
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
	 * @param null $index
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
	 * - <code>e107::getPluginConfig('myplug');</code>
	 * 	 will search for e107_plugins/myplug/e_pref/myplug_pref.php which
	 * 	 should contain class 'e_plugin_myplug_pref' class (child of e_plugin_pref)
	 * - <code>e107::getPluginConfig('myplug', 'row2');</code>
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
	 * Retrieve the global LAN for a specific plugin.
	 * @param $dir
	 * @param string $type
	 * @return mixed
	 */
	public static function getPlugLan($dir, $type='name')
	{
		$lan = "LAN_PLUGIN_".strtoupper($dir)."_".strtoupper($type);

		return defset($lan,false);
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
	 * @param $plug_name
	 * @param string $pref_name
	 * @param mixed $default default value if preference is not found
	 * @param null $index
	 * @return mixed
	 */
	public static function findPlugPref($plug_name, $pref_name, $default = null, $index = null)
	{
		return self::getPlugConfig($plug_name)->getPref($pref_name, $default, $index);
	}


	/**
	 * Retrieve theme config handlers.
	 * Multiple theme preference DB rows are supported
	 * Class overload is supported.
	 * Examples:
	 * - <code>e107::getTHemeConfig('mytheme');</code>
	 * 	 will search for e107_plugins/myplug/e_pref/myplug_pref.php which
	 * 	 should contain class 'e_plugin_myplug_pref' class (child of e_plugin_pref)
	 * - <code>e107::getPluginConfig('myplug', 'row2');</code>
	 * 	 will search for e107_plugins/myplug/e_pref/myplug_row2_pref.php which
	 * 	 should contain class 'e_plugin_myplug_row2_pref' class (child of e_plugin_pref)
	 *
	 * @param string $theme_name
	 * @param string $multi_row
	 * @param boolean $load load from DB on startup
	 * @return e_plugin_pref
	 */
	public static function getThemeConfig($theme_name=null, $multi_row = '', $load = true)
	{

		if(empty($theme_name))
		{
			$theme_name = self::getPref('sitetheme');
		}

		if(!isset(self::$_theme_config_arr[$theme_name.$multi_row]))
		{
			e107_require_once(e_HANDLER.'pref_class.php');

			self::$_theme_config_arr[$theme_name.$multi_row] = new e_theme_pref($theme_name, $multi_row, $load);
		}

		return self::$_theme_config_arr[$theme_name.$multi_row];
	}




	/**
	 * Get current theme preference. $pref_name is parsed,
	 * so that $pref_name = 'x/y/z' will search for value pref_data[x][y][z]
	 * Shorthand of  self::getConfig()->getPref('current_theme/sitetheme_pref/pref_name')
	 *
	 * @see e_core_pref::getPref()
	 * @param string $pref_name
	 * @param mixed $default default value if preference is not found
	 * @param null $index
	 * @return mixed
	 */
	public static function getThemePref($pref_name = '', $default = null, $index = null)
	{
		// new storage method in it's own core table row. eg. theme_bootstrap3
		$theme_name = self::getPref('sitetheme');

		if(self::getThemeConfig($theme_name)->hasData() === true)
		{
			return  empty($pref_name) ? self::getThemeConfig($theme_name)->getPref() : self::getThemeConfig($theme_name)->get($pref_name, $default);
		}

		// old storage method in core prefs.

		$legacy_pref_name = ($pref_name) ? $pref_name = '/'.$pref_name : '';
		$tprefs = self::getConfig()->getPref('sitetheme_pref'.$legacy_pref_name, $default, $index);

		return !empty($tprefs) ? $tprefs : $default;

	}

	/**
	 * Set current theme preference. $pref_name is parsed,
	 * so that $pref_name = 'x/y/z' will set value pref_data[x][y][z]
	 *
	 * @param string|array $pref_name
	 * @param mixed $pref_value
	 * @return e_pref
	 */
	public static function setThemePref($pref_name, $pref_value = null)
	{
		if(is_array($pref_name)) return self::getConfig()->set('sitetheme_pref', $pref_name);
		return self::getConfig()->updatePref('sitetheme_pref/'.$pref_name, $pref_value, false);
	}


	public static function getThemeGlyphs()
	{

		$custom = self::getConfig()->getPref('sitetheme_glyphicons', false);
		$theme = self::getConfig()->getPref('sitetheme', false);

		$arr = array();

		if(!empty($custom))
		{

			foreach($custom as $glyphConfig)
			{

				if(substr($glyphConfig['path'],0,4) !== 'http')
				{
					$glyphConfig['path'] = e_THEME."$theme/".$glyphConfig['path'];
				}

				$arr[] = $glyphConfig;

				if(E107_DBG_INCLUDES)
				{
					e107::getDebug()->log("Loading Glyph Icons: ".print_a($glyphConfig,true));
				}
			}

		}

		return $arr;

	}




	/**
	 * Retrieve text parser singleton object
	 *
	 * @return e_parse
	 */
	public static function getParser()
	{
		return self::getSingleton('e_parse', e_HANDLER.'e_parse_class.php'); //WARNING - don't change this - infinite loop!!!
	}

	/**
	 * Retrieve sc parser singleton object
	 *
	 * @return e_parse_shortcode
	 */
	public static function getScParser()
	{
		return self::getSingleton('e_parse_shortcode', true);
	}


	/**
	 * Retrieve secure_image singleton object
	 *
	 * @return secure_image
	 */
	public static function getSecureImg()
	{
		return self::getSingleton('secure_image', true); // more flexible.
		// return self::getObject('secure_image');
	}

	/**
	 * Retrieve registered sc object (batch) by class name
	 * Note - '_shortcodes' part of the class/override is added by the method
	 * Override is possible only if class is not already instantiated by shortcode parser
	 *
	 * <code><?php
	 *
	 * // Core news shortcodes (news_shortcodes.php using class news_shortcodes )
	 * e107::getScObject('news');
	 *
	 * // Core page shortcodes (page_shortcodes.php.php with class cpage_shortcode)
	 * e107::getScObject('page', null,'cpage');
	 *
	 * // object of plugin_myplugin_my_shortcodes class -> myplugin/shortcodes/batch/my_shortcodes.php
	 * e107::getScObject('my', 'myplugin');
	 *
	 * // news override - plugin_myplugin_news_shortcodes extends news_shortcodes -> myplugin/shortcodes/batch/news_shortcodes.php
	 * e107::getScObject('news', 'myplugin', true);
	 *
	 * // news override - plugin_myplugin_mynews_shortcodes extends news_shortcodes -> myplugin/shortcodes/batch/mynews_shortcodes.php
	 * e107::getScObject('news', 'myplugin', 'mynews');
	 * </code>
	 *
	 * @param string $className
	 * @param string $pluginName
	 * @param string|true $overrideClass
	 * @return e_shortcode
	 */
	public static function getScBatch($className, $pluginName = null, $overrideClass = null)
	{
		if(is_string($overrideClass)) $overrideClass .= '_shortcodes';
		return self::getScParser()->getScObject($className.'_shortcodes', $pluginName, $overrideClass);
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
	 * Retrieve cache singleton object
	 *
	 * @return ecache
	 */
	public static function getCache()
	{
		return self::getSingleton('ecache', true);
	}

	/**
	 * Retrieve bbcode singleton object
	 *
	 * @return e_bbcode
	 */
	public static function getBB()
	{
		return self::getSingleton('e_bbcode', true);
	}

	/**
	 * Retrieve user-session singleton object
	 *
	 * @return UserHandler
	 */
	public static function getUserSession()
	{
		return self::getSingleton('UserHandler', true);
	}

	/**
	 * Retrieve core session singleton object(s)
	 *
	 * @param null $namespace
	 * @return e_core_session
	 */
	public static function getSession($namespace = null)
	{
		$id = 'core/e107/session/'.(null === $namespace ? 'e107' : $namespace);
		if(self::getRegistry($id))
		{
			return self::getRegistry($id);
		}
		$session = self::getObject('e_core_session', array('namespace' => $namespace), true);
		self::setRegistry($id, $session);
		return $session;
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
	 * Retrieve rater singleton object
	 *
	 * @return rater
	 */
	public static function getRate()
	{
		return self::getSingleton('rater', true);
	}

	/**
	 * Retrieve sitelinks singleton object
	 *
	 * @return sitelinks
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
	 * Retrieve e107Email singleton object
	 *
	 * @return e107Email
	 */
	public static function getEmail($overrides=null)
	{
		return self::getSingleton('e107Email', true, null, $overrides);
	}


	/**
	 * Retrieve e107Email mail mailer object.
	 *
	 * @return e107MailManager
	 */
	public static function getBulkEmail()
	{
		return self::getSingleton('e107MailManager', true);
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
	 * Retrieve array storage singleton object
	 *
	 * @return e_array
	 */
	public static function getArrayStorage()
	{
		return self::getSingleton('e_array', true);
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
	 * Retrieve e_theme singleton object
	 * @return e_theme
	 */
	public static function getTheme($themedir='front', $clearCache=false)
	{

		if(!defined('E107_INSTALL'))
		{
			if($themedir === 'front')
			{
				$themedir= self::getPref('sitetheme');
			}

			if($themedir === 'admin')
			{
				$themedir = self::getPref('admintheme');
			}
		}

		// Get the currently used theme.
		if ($themedir == 'current')
		{
			// If we are in the admin area.
			if (deftrue('e_ADMIN_AREA', false))
			{
				$themedir = self::getPref('admintheme');
			}
			else
			{
				$themedir= self::getPref('sitetheme');
			}
		}

	//	e107::getDb()->db_Mark_time('start e_theme');
		/** @var e_theme $ret */
		$ret = self::getSingleton('e_theme', true, null, array('themedir'=> $themedir, 'force'=> $clearCache));

	//	e107::getDb()->db_Mark_time('end e_theme');
	/*	echo "<pre>";
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		echo "</pre>";*/

		return $ret;
	}



	/**
	 * Retrieve URL singleton object
	 *
	 * @return eURL
	 */
	public static function getUrl()
	{
		return self::getSingleton('eUrl', true);
	}

	/**
	 * Retrieve file handler singleton or new fresh object
	 *
	 * @param boolean $singleton default true
	 * @return e_file
	 */
	public static function getFile($singleton = false)
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
	 * @Deprecated - use e107::getLog();
	 * @return e_admin_log
	 */
	public static function getAdminLog()
	{
		return self::getSingleton('e_admin_log', true);
	}

	/**
	 * Retrieve admin log singleton object
	 *
	 * @return e_admin_log
	 */
	public static function getLog()
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
		return self::getSingleton('e_date', true);
	}

	/**
	 * Retrieve date handler singleton object - preferred method.
	 *
	 * @return convert
	 */
	public static function getDate()
	{
		return self::getSingleton('e_date', true);
	}


    /**
     * Retrieve date handler singleton object - preferred method.
     *
     * @return e107_db_debug
     */
    public static function getDebug()
    {
        return self::getSingleton('e107_db_debug', true);
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
	 * Retrieve override handler singleton object
	 *
	 * @return override
	 */
	public static function getOverride()
	{
		return self::getSingleton('override', true);
	}



	/**
	 * Retrieve Language handler singleton object
	 *
	 * @return language
	 */
	public static function getLanguage()
	{
		return self::getSingleton('language', true);
	}

	/**
	 * Retrieve IP/ban handler singleton object
	 *
	 * @return eIPHandler
	 */
	public static function getIPHandler()
	{
		return self::getSingleton('eIPHandler', true);
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
	 * Retrieve HybridAuth object
	 *
	 * @return object
	 */
	public static function getHybridAuth($config = null)
	{
		if(null === $config)
		{
			$config = array(
				'base_url' => self::getUrl()->create('system/xup/endpoint', array(), array('full' => true)),
				'providers' => self::getPref('social_login', array()),
				'debug_mode' => false,
				'debug_file' => ''
			);
		}
		return new Hybrid_Auth($config);
	}

	/**
	 * Retrieve userclass singleton object
	 *
	 * @return user_class
	 */
	public static function getUserClass()
	{
		return self::getSingleton('user_class', true);
	}

	/**
	 * Retrieve user model object.
	 *
	 * @param integer $user_id target user
	 * @param boolean $checkIfCurrent if tru user_id will be compared to current user, if there is a match
	 * 	current user object will be returned
	 * @return e_system_user
	 */
	public static function getSystemUser($user_id, $checkIfCurrent = true)
	{
		if($checkIfCurrent && $user_id && $user_id === self::getUser()->getId())
		{
			return self::getUser();
		}

		if(!$user_id) return self::getObject('e_system_user');

		$user = self::getRegistry('core/e107/user/'.$user_id);
		if(null === $user)
		{
			$user = self::getObject('e_system_user');
			if($user_id) $user->load($user_id); // self registered on load
		}
		return $user;
	}

	/**
	 * Simple replacement for deprecated get_user_data(). e107::user();
	 * @param $uid integer user_id or leave empty for currently logged in user.
	 * @return array of user data
	 */
	public static function user($uid=null)
	{
		$uid = intval($uid);

		if(empty($uid)){ return false; }

		$user = self::getSystemUser($uid, true);
		$var = array();
		if($user)
		{
			$var = $user->getUserData();
		}

		return $var;
	}


  /**
    * Return a string containg exported array data. - preferred.
    *
    * @param array $ArrayData array to be stored
    * @param bool|string $mode true = var_export with addedslashes, false = var_export (default), 'json' = json encoded
    * @return array|string
    */
    public static function serialize($ArrayData, $mode = false)
    {
		return self::getArrayStorage()->serialize($ArrayData, $mode);
    }

	  /**
    * Returns an array from stored array data.
    *
    * @param string $ArrayData
    * @return array stored data
    */
    public static function unserialize($ArrayData)
    {
    	if(empty($ArrayData))
		{
			return array();
		}

		return self::getArrayStorage()->unserialize($ArrayData);
    }


	/**
	 * Retrieve current user model object.
	 *
	 * @return e_user
	 */
	public static function getUser()
	{
		$user = self::getRegistry('core/e107/current_user');
		if(null === $user)
		{
			$user = self::getObject('e_user');
			self::setRegistry('core/e107/current_user', $user);
		}
		return $user;
	}


	/**
	 * Retrieve front or admin Model.
	 * @param string $type
	 * @return object e_front_model or e_admin_model;
	 */
	public static function getModel($type='front')
	{
		if($type === 'front')
		{
			return self::getObject('e_front_model');
		}

		return self::getObject('e_admin_model');
	}

	/**
	 * Retrieve user model object.
	 *
	 * @param integer $user_id target user
	 * @return e_user_extended_structure_tree
	 */
	public static function getUserStructure()
	{
		return self::getSingleton('e_user_extended_structure_tree', true);
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
	 * Retrieve User Perms (admin perms) handler singleton object
	 * @return e_userperms
	 */
	public static function getUserPerms()
	{
		return self::getSingleton('e_userperms', true);
	}

	/**
	 * Retrieve online users handler singleton object
	 * @return e_ranks
	 */
	public static function getRank()
	{
		return self::getSingleton('e_ranks', true);
	}

	/**
	 * Retrieve plugin handler singleton object
	 * @return e107plugin
	 */
	public static function getPlugin()
	{
		return self::getSingleton('e107plugin', true);
	}



	/**
	 * Retrieve plugin class singleton object
	 * @return e_plugin
	 */
	public static function getPlug()
	{
		return self::getSingleton('e_plugin', true);
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
	 * Retrieve chart handler singleton object
	 * @return e_chart
	 */
	public static function getChart()
	{
		return self::getObject('e_chart', null, true);
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
	 * Retrieve comments handler singleton object
	 * @return e_customfields
	 */
	public static function getCustomFields()
	{
		return self::getSingleton('e_customfields', true);
	}

	/**
	 * Retrieve Media handler singleton object
	 * @return e_media
	 */
	public static function getMedia()
	{
		return self::getSingleton('e_media', true);
	}

	/**
	 * Retrieve Navigation Menu handler singleton object
	 * @return e_navigation
	 */
	public static function getNav()
	{
		return self::getSingleton('e_navigation', true);
	}

	/**
	 * Retrieve message handler singleton
	 * @return eMessage
	 */
	public static function getMessage()
	{
		// static $included = false;
		// if(!$included)
		// {
			// e107_require_once(e_HANDLER.'message_handler.php');
			// $included = true;
		// }
		// return eMessage::getInstance();
		return self::getSingleton('eMessage', true);
	}

	/**
	 * Retrieve ajax singleton object
	 *
	 * @return e_ajax_class
	 */
	public static function getAjax()
	{
		return self::getSingleton('e_ajax_class', true);
	}

	/**
	 * Retrieve Library Manager singleton object (internal use only. Use e107::library())
	 *
	 * @return e_library_manager
	 */
	public static function getLibrary()
	{
		return self::getSingleton('e_library_manager', true);
	}

	/**
	 * Library Common Public Function.
	 *
	 * @param string $action
	 *  - 'detect': Tries to detect a library and its installed version.
	 *  - 'load': Loads a library.
	 * @param string $library
	 *  The name of the library to detect/load.
	 * @param string $variant
	 *   (Optional for 'load') The name of the variant to load. Note that only one variant of a library can be loaded
	 *   within a single request. The variant that has been passed first is used; different variant names in subsequent
	 *   calls are ignored.
	 *
	 * @return array|boolean
	 *  - In case of 'detect': An associative array containing registered information for the library specified by
	 *    $name, or FALSE if the library $name is not registered.
	 *  - In case of 'load': An associative array of the library information.
	 *  - In case of 'info': An associative array containing registered information for all libraries, the registered
	 *    information for the library specified by $name, or FALSE if the library $name is not registered.
	 */
	public static function library($action = '', $library = null, $variant = null)
	{
		$libraryHandler = self::getLibrary();

		switch($action)
		{
			case 'detect':
				return $libraryHandler->detect($library);
				break;

			case 'load':
				$cdn = (bool) self::getPref('e_jslib_cdn', true);
				$debug = (bool) deftrue('e_DEBUG');
				$admin = (bool) defset('e_ADMIN_AREA', false);

				// Try to detect and load CDN version.
				if(!$admin && $cdn && strpos($library, 'cdn.') !== 0)
				{
					$lib = $libraryHandler->detect('cdn.' . $library);

					// If CDN version is available.
					if($lib && !empty($lib['installed']))
					{
						// If a variant is specified, we need to check if it's installed.
						if(!empty($variant) && !empty($lib['variants'][$variant]['installed']))
						{
							// Load CDN version with the variant.
							return $libraryHandler->load('cdn.' . $library, $variant);
						}

						// If CDN version is available, but no variant is specified,
						// and debug mode is on, try to load 'debug' variant.
						if(empty($variant) && $debug && !empty($lib['variants']['dev']['installed']))
						{
							// Load CDN version with 'debug' variant.
							return $libraryHandler->load('cdn.' . $library, 'dev');
						}

						// Load CDN version without variant.
						return $libraryHandler->load('cdn.' . $library, $variant);
					}
				}

				// If no variant is specified, and CDN version is not available, and debug mode is on.
				if(empty($variant) && $debug)
				{
					$lib = $libraryHandler->detect($library);

					// If 'debug' variant is available.
					if($lib && !empty($lib['variants']['dev']['installed']))
					{
						// Load library with 'debug' variant.
						return $libraryHandler->load($library, 'dev');
					}
				}

				return $libraryHandler->load($library, $variant);
				break;

			case 'info':
				return $libraryHandler->info($library);
				break;
		}
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


	public static function set($type=null, $val=true)
	{
		if($type === 'js_enabled')
		{
			self::$_js_enabled = (bool) $val;
		}

		if($type === 'css_enabled')
		{
			self::$_css_enabled = (bool) $val;
		}
	}



	/**
	 * JS Common Public Function. Prefered is shortcode script path
	 * @param string $type core|theme|footer|inline|footer-inline|url or any existing plugin_name
	 * @param string|array $data depends on the type - path/url or inline js source
	 * @param integer $zone [optional] leave it null for default zone
	 * @param string $dep dependence :  null | prototype | jquery
	 */
	public static function js($type, $data, $dep = null, $zone = null, $pre = '', $post = '')
	{
		if(self::$_js_enabled === false)
		{
			return null;
		}

		$jshandler = self::getJs();
		$jshandler->setDependency($dep);

		switch ($type)
		{
			case 'settings':
				$jshandler->jsSettings($data);
			break;

			case 'core':
				// data is e.g. 'core/tabs.js'
				if(null !== $zone) $jshandler->requireCoreLib($data, $zone);
				else $jshandler->requireCoreLib($data);
			break;

			case 'bootstrap': //TODO Eventually add own method and render for bootstrap.
				if(null !== $zone) $jshandler->requireCoreLib('bootstrap/js/'.$data, $zone);
				else $jshandler->requireCoreLib('bootstrap/js/'.$data);
			break;

			case 'theme':
				// data is e.g. 'jslib/mytheme.js'
				if(null !== $zone) $jshandler->headerTheme($data, $zone, $pre, $post);
				else $jshandler->footerTheme($data, 5, $pre, $post);
			break;

			case 'inline':
				// data is JS source (without script tags)
				if(null !== $zone) $jshandler->headerInline($data, $zone);
				else $jshandler->headerInline($data);
			break;

			case 'footer-inline':
				// data is JS source (without script tags)
				if(null !== $zone) $jshandler->footerInline($data, $zone);
				else $jshandler->footerInline($data);
			break;

			case 'url':
				// data is e.g. 'http://cdn.somesite.com/some.js'
				if(null !== $zone) $jshandler->headerFile($data, $zone, $pre, $post);
				else $jshandler->headerFile($data, 5, $pre, $post);
			break;

			case 'footer':
				// data is e.g. '{e_PLUGIN}myplugin/jslib/myplug.js'
				if(null !== $zone) $jshandler->footerFile($data, $zone, $pre, $post);
				else $jshandler->footerFile($data, 5, $pre, $post);
			break;

			// $type is plugin name
			default:
				// data is e.g. 'jslib/myplug.js'
				if(!self::isInstalled($type)) return;
				if(null !== $zone) $jshandler->requirePluginLib($type, $data, $zone);
				else $jshandler->requirePluginLib($type, $data);
			break;
		}

		$jshandler->resetDependency();
	}


	/**
	 * Add a <link> tag to the head of the html document.
	 * @param array $attributes
	 * @example e107::link(array('rel'=>"dns-prefetch", "href" => "http://example-domain.com/"));
	 */
	public static function link($attributes=array())
	{
		self::getJs()->addLink($attributes);
	}




	/**
	 * CSS Common Public Function. Prefered is shortcode script path
	 * @param string $type core|theme|footer|inline|footer-inline|url or any existing plugin_name
	 * @param string $data depends on the type - path/url or inline js source
	 * @param null $dep
	 * @param string $media any valid media attribute string - http://www.w3schools.com/TAGS/att_link_media.asp
	 * @param string $preComment possible comment e.g. <!--[if lt IE 7]>
	 * @param string $postComment possible comment e.g. <![endif]-->
	 * @param null $dependence
	 */
	public static function css($type, $data, $dep = null, $media = 'all', $preComment = '', $postComment = '', $dependence = null)
	{

	/*	if((strpos($data,'bootstrap.css')!==false || strpos($data,'bootstrap.min.css')!==false) && !defined("BOOTSTRAP")) // detect bootstrap is enabled. - used in nextprev.sc and forum currently.
		{
			define("BOOTSTRAP", true);
		}*/

		if(self::$_css_enabled === false)
		{
			return null;
		}

		$jshandler = self::getJs();
		$jshandler->setDependency($dep);

		if(strpos($data,'http')===0)
		{
			$type = 'url';
		}

		switch ($type)
		{
			case 'core':
				// data is path relative to e_FILE/jslib/
				$jshandler->coreCSS($data, $media, $preComment, $postComment);
			break;

			case 'bootstrap':
				// data is path relative to e_FILE/jslib/
				$jshandler->coreCSS('bootstrap/css/'.$data, $media, $preComment, $postComment);
			break;

			case 'theme':
				// data is path relative to current theme
				$jshandler->themeCSS($data, $media, $preComment, $postComment);
			break;

			case 'inline':
				// data is CSS source (without style tags)
				$jshandler->inlineCSS($data, $media);
			break;

			case 'url':
				// data is e.g. 'http://cdn.somesite.com/some.css'
				$jshandler->otherCSS($data, $media, $preComment, $postComment);
			break;

			// $type is plugin name
			default:
				// data is e.g. 'css/myplug.css'
				if(self::isInstalled($type)) $jshandler->pluginCSS($type, $data, $media, $preComment, $postComment);
			break;
		}
		$jshandler->resetDependency();
	}


	/**
	 * Throw log/info/warnings/errors to the Chrome/Firefox Console.
	 * @param $name
	 * @param null $var
	 * @param string $type
	 */
	public static function debug($name, $var = null, $type = 'log')
	{

	    $nl = "\r\n";
	//	echo "alert('hi');";
		$text = '';

	    switch($type) {
	        case 'log':
	           $text .= 'console.log("'.$name.'");'.$nl;
	        break;
	        case 'info':
	           $text .= 'console.info("'.$name.'");'.$nl;
	        break;
	        case 'warning':
	           $text .= 'console.warn("'.$name.'");'.$nl;
	        break;
	        case 'error':
	           $text .= 'console.error("'.$name.'");'.$nl;
	        break;
	    }

	    if (!empty($var))
	    {
	        if (is_object($var) || is_array($var))
	        {
	            $object = json_encode($var);

	           $text .= 'var object'.preg_replace('~[^A-Z|0-9]~i',"_",$name).' = \''.str_replace("'","\'",$object).'\';'.$nl;
	           $text .= 'var val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).' = eval("(" + object'.preg_replace('~[^A-Z|0-9]~i',"_",$name).' + ")" );'.$nl;

	            switch($type)
	            {
	                case 'log':
	                   $text .= 'console.debug(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.$nl;
	                break;
	                case 'info':
	                   $text .= 'console.info(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.$nl;
	                break;
	                case 'warning':
	                   $text .= 'console.warn(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.$nl;
	                break;
	                case 'error':
	                   $text .= 'console.error(val'.preg_replace('~[^A-Z|0-9]~i',"_",$name).');'.$nl;
	                break;
	            }
	        }
	        else
	        {
	            switch($type)
	            {
	                case 'log':
	                   $text .= 'console.debug("'.str_replace('"','\\"',$var).'");'.$nl;
	                break;
	                case 'info':
	                   $text .= 'console.info("'.str_replace('"','\\"',$var).'");'.$nl;
	                break;
	                case 'warning':
	                   $text .= 'console.warn("'.str_replace('"','\\"',$var).'");'.$nl;
	                break;
	                case 'error':
	                   $text .= 'console.error("'.str_replace('"','\\"',$var).'");'.$nl;
	                break;
	            }
	        }
	    }


		self::js('footer-inline', $text);

	}









	/**
	 * Retrieve JS Helper object
	 *
	 * @param boolean|string $singleton if true return singleton, if string return singleton object, use string as namespace, default false
	 * @return e_jshelper
	 */
	public static function getJshelper($singleton = false)
	{
		if($singleton)
		{
			return self::getSingleton('e_jshelper', true, (true === $singleton ? '' : $singleton));
		}
		return self::getObject('e_jshelper', null, true);
	}

	/**
	 * @see eResponse::addMeta()
	 * @param null $name
	 * @param null $content
	 * @param array $extended
	 * @return eResponse
	 */
	public static function meta($name = null, $content = null, $extended = array())
	{
		/** @var eResponse $response */
		$response = self::getSingleton('eResponse');

		if($name === 'description')
		{
			$response->addMetaDescription($content);	//Cam: TBD
		}

		if($name === 'keywords')
		{
			$response->addMetaKeywords($content);	//Cam: TBD
		}

		return $response->addMeta($name, $content, $extended);
	//	return self::getUrl()->response()->addMeta($name, $content, $extended);
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
	 * Retrieves class Object for specific plugin's addon such as e_url.php, e_cron.php, e_sitelink.php
	 * FIXME override from e.g. core/override/addons/
	 *
	 * @param string $pluginName e.g. faq, page
	 * @param string $addonName eg. e_cron, e_url, e_module
	 * @param mixed $className [optional] true - use default name, false - no object is returned (include only), any string will be used as class name
	 * @param mixed $param [optional] construct() param
	 * @return object
	 */
	public static function getAddon($pluginName, $addonName, $className = true)
	{
		$filename = $addonName; // e.g. 'e_cron';

		// fixme, temporary adding 's' to className, should be core fixed, better naming
		if(true === $className) $className = $pluginName.'_'.substr($addonName, 2); // remove 'e_'

		$elist = self::getPref($filename.'_list');

		if($filename === 'e_menu')
		{
			if(!in_array($pluginName, $elist)) return null;
		}
		else
		{
			if(!isset($elist[$pluginName])) return null;
		}

		// TODO override check comes here
		$path = e_PLUGIN.$pluginName.'/'.$filename.'.php';
		// e.g. include e_module, e_meta etc
		if(false === $className) return include_once($path);

		if(!class_exists($className, false))
		{
			include_once($path);
		}

		if(!class_exists($className, false))
		{
			return null;
		}
		return new $className;
	}

	/**
	 * Retrieves config() from all plugins for addons such as e_url.php, e_cron.php, e_sitelink.php
	 * @param string $addonName eg. e_cron, e_url
	 * @param string $className [optional] (if different from addonName)
	 * @param string $methodName [optional] (if different from 'config')
	 * @return array
	 */
	public static function getAddonConfig($addonName, $className = '', $methodName='config', $param=null,$param2=null )
	{
		$new_addon = array();

		$sql = self::getDb(); // Might be used by older plugins.

		$filename = $addonName; // e.g. 'e_cron';
		if(!$className)
		{
			$className = substr($filename, 2); // remove 'e_'
		}

		$elist = self::getPref($filename.'_list');

		if(!empty($elist))
		{
			foreach(array_keys($elist) as $key)
			{
				if(is_readable(e_PLUGIN.$key.'/'.$filename.'.php'))
				{
					include_once(e_PLUGIN.$key.'/'.$filename.'.php');

					$class_name = $key.'_'.$className;
					$array = self::callMethod($class_name, $methodName,$param,$param2);

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
	 * @param string|object $class_name
	 * @param string $method_name
	 * @param mixed $param -1st parameter sent to method
	 * @param mixed $param2 - 2nd parameter sent to method
	 * @return array|bool FALSE
	 */
	public static function callMethod($class_name, $method_name, $param=null, $param2=null)
	{
		$mes = self::getMessage();

		if(is_object($class_name) || class_exists($class_name))
		{

			if(is_object($class_name))
			{
				$obj = $class_name;
				$class_name = get_class($obj);
			}
			else
			{
				$obj = new $class_name;
			}

			if(method_exists($obj, $method_name))
			{
				if(E107_DBG_INCLUDES)
				{
					//$debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 6);
					$mes->addDebug('Executing <strong>'.$class_name.' :: '.$method_name.'()</strong>');
				}
				return call_user_func(array($obj, $method_name),$param, $param2);
			}
			else
			{
			//	$mes->addDebug('Function <strong>'.$class_name.' :: '.$method_name.'()</strong> NOT found.');
			}
		}
		return FALSE;
	}

	/**
	 * Retrieves the e_url config  - new v2.1.6
	 * @param string $mode config | alias | profile
	 * @return array
	 */
	public static function getUrlConfig($mode='config')
	{
		$new_addon = array();

		$filename = 'e_url';
		$elist = self::getPref($filename.'_list');
		$className = substr($filename, 2); // remove 'e_'
		$methodName = 'config';

		$url_profiles = e107::getPref('url_profiles');

		if(!empty($elist))
		{
			foreach(array_keys($elist) as $key)
			{
				if(is_readable(e_PLUGIN.$key.'/'.$filename.'.php'))
				{


					include_once(e_PLUGIN.$key.'/'.$filename.'.php');

					$class_name = $key.'_'.$className;

					if(is_object($class_name))
					{
						$obj = $class_name;
						$class_name = get_class($obj);
					}
					elseif(class_exists($class_name))
					{
						$obj = new $class_name;
					}
					else
					{
						return array();
					}

					if($mode === 'alias')
					{
						if(!empty($obj->alias))
						{
							$new_addon[$key] = $obj->alias;
						}

						continue;
					}

					if($mode === 'profiles')
					{
						if(!empty($obj->profiles))
						{
							$new_addon[$key] = $obj->profiles;
						}

						continue;
					}

					if($mode === 'generate')
					{
						if(!empty($obj->generate))
						{
							$new_addon[$key] = $obj->generate;
						}

						continue;
					}

					$profile = !empty($url_profiles[$key]) ? $url_profiles[$key] : null;

					$array = self::callMethod($obj, $methodName,$profile);

					if($array)
					{
						foreach($array as $k=>$v)
						{
							if(empty($v['alias']) && !empty($obj->alias))
							{
								$v['alias'] = $obj->alias;
							}
							$new_addon[$key][$k] = $v;

						}

					}

				}
			}
		}

		return $new_addon;
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
	//	global $user_pref; // FIXME - user model, kill user_pref global

		if(true === $for)
		{
			$for = e_ADMIN_AREA ? 'admin' : 'front';
		}
		switch($for )
		{
			case 'admin':
				$for = self::getPref('admintheme');
			break;

			case 'front':

				if(defined('USERTHEME') && USERTHEME !==false)
				{
					$for = USERTHEME;
				}
				else
				{
					$user_pref = self::getUser()->getPref();
					$for = !empty($user_pref['sitetheme']) ? $user_pref['sitetheme'] : self::getPref('sitetheme');
				}

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
		$id 						= str_replace('..', '', $id); //simple security, '/' is allowed
		$curTheme 					= self::getThemeInfo($override, 'rel');

		$override_path 				= $override ? $curTheme.'templates/'.$id.'_template.php' : null;
		$legacy_override_path 		= $override ? $curTheme.$id.'_template.php' : null;

		$legacy_core_path 			= e_THEME.'templates/'.$id.'_template.php';
		$core_path 					= e_CORE.'templates/'.$id.'_template.php';

		if($override_path && is_readable($override_path)) // v2 override template.
		{
			return $override_path;
		}
		elseif($legacy_override_path && is_readable($legacy_override_path)) //v1 override template.
		{
			return $legacy_override_path;
		}
		elseif(is_readable($legacy_core_path)) //v1 core template.
		{
		//	return $legacy_core_path; // just asking for trouble.
		}

		return $core_path;
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
	 * @param mixed $override see {@link getThemeInfo()} true/false,  front or admin.
	 * @param boolean $merge merge theme with core templates, default is false
	 * @param boolean $info retrieve template info only
	 * @return string|array
	 */
	public static function getCoreTemplate($id, $key = null, $override = true, $merge = false, $info = false)
	{
		$reg_path = 'core/e107/templates/'.$id.($override ? '/ext' : '');
		$path = self::coreTemplatePath($id, $override);
		$id = str_replace('/', '_', $id);
		$ret = self::_getTemplate($id, $key, $reg_path, $path, $info);

		### Attempt to fix merge issues; in case we override - template array not found in theme,
		### so we need to continue and merge with core templates
		if($merge && $override && empty($ret))
		{
			$ret = array();
		}

		if((!$merge && !$override) || is_string($ret))
		{
			 return $ret;
		}

		// merge
		$reg_path = 'core/e107/templates/'.$id;
		$path = self::coreTemplatePath($id, false);
		$id = str_replace('/', '_', $id);
        // Introducing noWrapper when merging
		$ret_core = self::_getTemplate($id, $key, $reg_path, $path, $info, true);

		return (is_array($ret_core) ? array_merge($ret_core, $ret) : $ret);
	}

	/**
	 * Get plugin template. Use this method for plugin templates, which are following the
	 * new template standards:
	 * - template variables naming conventions ie. ${NAME IN CAPS}_TEMPLATE['{ID}'] = "<div>...</div>";
	 * - one array variable per template only
	 * - theme override is made now by current_theme/templates/plugin_name/ folder
	 *
	 * <br><br>Results are cached (depending on $id and $override so it's safe to use
	 * this method e.g. in loop for retrieving a template string. If template (or template key) is not
	 * found, <b>NULL</b> is returned.<br><br>
	 *
	 * Example usage: <code>e107::getTemplate('user', 'short_start');</code>
	 * Will search for:
	 * - e107_themes/{current_frontend_theme}/templates/user_template.php (if $override is true) - this is the default.
	 * - e107_core/templates/user_template.php (if override not found or $override is false)
	 * - $USER_TEMPLATE array which contains all user templates
	 * - $USER_TEMPLATE['short_start'] (if key is null, $USER_TEMPLATE will be returned)
	 *
	 * @param string $plug_name if null getCoreTemplate method will be called
	 * @param string $id - file prefix, e.g. calendar for calendar_template.php
	 * @param string|null $key
	 * @param boolean $override see {@link getThemeInfo()}
	 * @param boolean $merge merge theme with plugin templates, default is false
	 * @param boolean $info retrieve template info only
	 * @return string|array
	 */
	public static function getTemplate($plug_name, $id = null, $key = null, $override = true, $merge = false, $info = false)
	{
		if(null === $plug_name)
		{
			return self::getCoreTemplate($id, $key, $override, $merge, $info);
		}
		if(null == $id || true === $id) // loads {$plug_name}/templates/{$plug_name}_template.php and an array ${PLUG_NAME}_TEMPLATE
		{
			$id = $plug_name;
		}
		$reg_path = 'plugin/'.$plug_name.'/templates/'.$id.($override ? '/ext' : '');
		$path = self::templatePath($plug_name, $id, $override);

		if(ADMIN && E107_DBG_INCLUDES)
		{
			self::getMessage()->addDebug( "Attempting to load Template File: ".$path );
		}

		$id = str_replace('/', '_', $id);
		$ret = self::_getTemplate($id, $key, $reg_path, $path, $info);

		if($merge === false || $override === false)
		{
			return ($ret === false) ? '' : $ret;
		}

		// merge
		$reg_path = 'plugin/'.$plug_name.'/templates/'.$id;
		$path = self::templatePath($plug_name, $id, false);

		$id = str_replace('/', '_', $id);
        // Introduced noWrapper when merging
		$ret_plug = self::_getTemplate($id, $key, $reg_path, $path, $info, true);

		if($merge === true && $key !== null && $ret === false) // key not set, so send 'core' version instead.
		{
			return $ret_plug;
		}

		if($ret === false)
		{
			return '';
		}

		return (is_array($ret_plug) ? array_merge($ret_plug, $ret) : $ret);
	}

	/**
	 * Register sc_style registry
	 * @param string $templateId e.g. 'contact/form' or 'contact' for all contact template wrappers
	 * @param string $scName [optional] shortcode name - if provided, wrapper (string) for the corresponding code will be returned
	 * @return array|string
	 */
	public static function templateWrapper($templateId, $scName = null)
	{
		if(!$templateId) return array();

		list($templateId, $templateKey) = explode('/', $templateId, 2);

		$wrapperRegPath = 'templates/wrapper/'.$templateId;

		$wrapper = self::getRegistry($wrapperRegPath);

		if(empty($wrapper) || !is_array($wrapper)) $wrapper = array();

		if(strpos($templateKey,'/')!==false) // quick fix support for 3 keys eg. news/view/item
		{
			list($templateKey,$templateKey2) = explode("/", $templateKey, 2);
			if($templateKey && $templateKey2)
			{
				 $wrapper = (isset($wrapper[$templateKey][$templateKey2])  ? $wrapper[$templateKey][$templateKey2] : array());
			}
		}
		else // support for 2 keys. eg. contact/form
		{
			if($templateKey) $wrapper = (isset($wrapper[$templateKey])  ? $wrapper[$templateKey] : array());
		}

		if(null !== $scName)
		{
			$scName = strtoupper($scName);
			return isset($wrapper[$scName]) ? $wrapper[$scName] : '';
		}

		return $wrapper;
	}

	/**
	 * Retrieve/set sc_style array (global shortcode wrapper)
	 * @param array $set template defined $sc_style, will be merged with current registry content
	 * @return array
	 */
	public static function scStyle($set = null)
	{
		$_sc_style = self::getRegistry('shortcodes/sc_style');
		if(!is_array($_sc_style)) $_sc_style = array();
		if(is_array($set) && !empty($set))
		{
			self::setRegistry('shortcodes/sc_style', array_merge($_sc_style, $set));
		}

		return $_sc_style;
	}

	/**
	 * Get Template Info array.
	 * Note: Available only after getTemplate()/getCoreTemplate() call
	 *
	 * @param string $plug_name if null - search for core template
	 * @param string $id
	 * @param string $key
	 * @param boolean $override
	 * @param boolean $merge
	 * @return array
	 */
	public static function getTemplateInfo($plug_name = null, $id, $key = null, $override = true, $merge = false)
	{
		if($plug_name)
		{
			$ret = self::getTemplate($plug_name, $id, null, $override, $merge, true);
		}
		else
		{
			$ret = self::getCoreTemplate($id, null, $override, $merge, true);
		}
		if($key && isset($ret[$key]) && is_array($ret[$key]))
		{
			return $ret[$key];
		}
		return $ret;
	}

	/**
	 * Return a list of available template IDs for a plugin(eg. $MYTEMPLATE['my_id'] -> array('id' => 'My Id'))
	 *
	 * FIXME - the format of $allinfo=true array is not usable at all, convert it so that it's compatible with e_form::selectbox() method
	 *
	 * @param string $plugin_name
	 * @param string $template_id [optional] if different from $plugin_name;
	 * @param mixed $where true - current theme, 'admin' - admin theme, 'front' (default)  - front theme
	 * @param string $filter_mask
	 * @param boolean $merge merge theme with core/plugin layouts, default is false
	 * @param boolean $allinfo reutrn nimerical array of templates and all available template information
	 * @return array
	 */
	public static function getLayouts($plugin_name, $template_id = '', $where = 'front', $filter_mask = '', $merge = false, $allinfo = true)
	{
		if(!$plugin_name) // Core template
		{
			$tmp = self::getCoreTemplate($template_id, null, $where, $merge);
			$tmp_info = self::getTemplateInfo(null, $template_id, null, $where, $merge);
		}
		else // Plugin template
		{
			$id = (!$template_id) ? $plugin_name : $template_id;
			$tmp = self::getTemplate($plugin_name, $id, null, $where, $merge);
			$tmp_info = self::getTemplateInfo($plugin_name, $id, null, $where, $merge);
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
			if(isset($tmp_info[$key]))
			{
				$templates[$key] = defset($tmp_info[$key]['title'], $tmp_info[$key]['title']);
				continue;
			}
			$templates[$key] = implode(' ', array_map('ucfirst', explode('_', $key))); //TODO add LANS?
		}
		return ($allinfo ? array($templates, $tmp_info) : $templates);
	}

	/**
	 * More abstsract template loader, used
	 * internal in {@link getTemplate()} and {@link getCoreTemplate()} methods
	 * If $info is set to true, only template informational array will be returned
	 *
	 * @param string $id
	 * @param string|null $key
	 * @param string $reg_path
	 * @param string $path
	 * @param boolean $info
     * @param boolean $noWrapper
	 * @return string|array
	 */
	public static function _getTemplate($id, $key, $reg_path, $path, $info = false, $noWrapper = false)
	{
		$regPath = $reg_path;
		$var = strtoupper($id).'_TEMPLATE';
		$regPathInfo = $reg_path.'/info';
		$var_info = strtoupper($id).'_INFO';

		$wrapper = strtoupper($id).'_WRAPPER'; // see contact_template.php
		$wrapperRegPath = 'templates/wrapper/'.$id;

		// Use: list($pre,$post) = explode("{---}",$text,2);

		$tp = self::getParser(); // BC FIx - avoid breaking old templates due to missing globals.

		if(null === self::getRegistry($regPath))
		{
			(deftrue('E107_DEBUG_LEVEL') ? include_once($path) : @include_once($path));
			self::setRegistry($regPath, (isset($$var) ? $$var : array()));

			// sc_style not a global anymore and uppercase

            // Fix template merge issue - no-wrapper sent to avoid sc wrappers confusions
            if(!$noWrapper)
            {
                if(isset($SC_WRAPPER))
                {
                    if(E107_DBG_BBSC)
                    {
                        self::getMessage()->addDebug("Found wrapper: ".$SC_WRAPPER);
                    }
                    self::scStyle($SC_WRAPPER);
                }

                // ID_WRAPPER support
                if(isset($$wrapper) && !empty($$wrapper) && is_array($$wrapper))
                {
                    if(E107_DBG_BBSC)
                    {
                        self::getMessage()->addDebug("Found ID wrapper: ".$wrapper);
                    }
                    self::setRegistry($wrapperRegPath, $$wrapper);
                }
            }
		}
		if(null === self::getRegistry($regPathInfo))
		{
			self::setRegistry($regPathInfo, (isset($$var_info) && is_array($$var_info) ? $$var_info : array()));
		}

		$ret = (!$info ? self::getRegistry($regPath) : self::getRegistry($regPathInfo));

		if(!$key)
		{
			return $ret;
		}

		return ($ret && is_array($ret) && isset($ret[$key]) ? $ret[$key] : false);
	}

	/**
	 * Load language file, replacement of include_lan()
	 * @outdated use e107::lan() or e107::coreLan(), e107::plugLan(), e107::themeLan()
	 * @param string $path
	 * @param boolean $force
	 * @return string
	 */
	public static function includeLan($path, $force = false)
	{
		if (!is_readable($path))
		{
			if (self::getPref('noLanguageSubs') || (e_LANGUAGE === 'English'))
			{
				return false;
			}

			self::getDebug()->log("Couldn't load language file: " . $path);

			$path = str_replace(e_LANGUAGE, 'English', $path);

			self::getDebug()->log("Attempts to load default language file: " . $path);

			if(!is_readable($path))
			{
				self::getDebug()->log("Couldn't load default language file: " . $path);
				return false;
			}
		}

		$adminLanguage = self::getPref('adminlanguage');

		if(e_ADMIN_AREA && vartrue($adminLanguage))
		{
			$path = str_replace(e_LANGUAGE, $adminLanguage, $path);
		}

		$ret = ($force) ? include($path) : include_once($path);
		return (isset($ret)) ? $ret : "";
	}

	/**
	 * Simplify importing of core Language files.
	 * All inputs are sanitized.
	 * Core Exceptions as e_LANGUAGE.'.php' and e_LANGUAGE.'_custom.php' are manually loaded. (see class2.php)
	 *
	 * Examples:
	 * <code><?php
	 * 	// import defeinitions from /e107_languages/[CurrentLanguage]/lan_comment.php</code>
	 * 	e107::coreLan('comment');
	 *
	 * 	// import defeinitions from /e107_languages/[CurrentLanguage]/admin/lan_banlist.php
	 * 	self::coreLan('banlist', true);
	 * </code>
	 *
	 * @param string $fname filename without the extension part (e.g. 'comment')
	 * @param boolean $admin true if it's an administration language file
	 * @return bool
	 */
	public static function coreLan($fname, $admin = false)
	{
		$cstring  = 'corelan/'.e_LANGUAGE.'_'.$fname.($admin ? '_admin' : '_front');
		if(self::getRegistry($cstring)) return;

		$fname = ($admin ? 'admin/' : '').'lan_'.preg_replace('/[^\w]/', '', trim($fname, '/')).'.php';
		$path = e_LANGUAGEDIR.e_LANGUAGE.'/'.$fname;

		self::setRegistry($cstring, true);

		return self::includeLan($path, false);
	}

	/**
	 * Simplify importing of plugin Language files (following e107 plugin structure standards).
	 * All inputs are sanitized.
	 *
	 * Examples:
	 * <code><?php
	 * 	// import defeinitions from /e107_plugins/forum/languages/[CurrentLanguage]/lan_forum.php
	 * 	e107::plugLan('forum', 'lan_forum');
	 *
	 * 	// import defeinitions from /e107_plugins/featurebox/languages/[CurrentLanguage]_admin_featurebox.php
	 *  // OR /e107_plugins/featurebox/languages/[CurrentLanguage]/[CurrentLanguage]_admin_featurebox.php (auto-detected)
	 * 	e107::plugLan('featurebox', 'admin_featurebox', true);
	 *
	 * 	// import defeinitions from /e107_plugins/myplug/languages/[CurrentLanguage]_front.php
	 * 	e107::plugLan('myplug');
	 *
	 * 	// import defeinitions from /e107_plugins/myplug/languages/[CurrentLanguage]_admin.php
	 * 	e107::plugLan('myplug', true);
	 *
	 * 	// import defeinitions from /e107_plugins/myplug/languages/[CurrentLanguage]/admin/common.php
	 * 	e107::plugLan('myplug', 'admin/common');
	 * </code>
	 *
	 * @param string $plugin plugin name
	 * @param string $fname filename without the extension part (e.g. 'common')
	 * @param boolean $flat false (default, preferred) Language folder structure; true - prepend Language to file name
	 * @return bool
	 */
	public static function plugLan($plugin, $fname = '', $flat = false)
	{
		$cstring  = 'pluglan/'.e_LANGUAGE.'_'.$plugin.'_'.$fname.($flat ? '_1' : '_0');
		if(self::getRegistry($cstring)) return;

		$plugin = preg_replace('/[^\w]/', '', $plugin);

		if($fname === 'global') // fix ambiguity
		{
			 $fname = e_LANGUAGE."_global";
		}
		elseif($fname && is_string($fname))
		{
			 $fname = e_LANGUAGE.($flat ? '_' : '/').preg_replace('#[^\w/]#', '', trim($fname, '/'));
		}
		elseif($fname === true) // admin file.
		{
			//$fname = "admin/".e_LANGUAGE;
			 $fname = e_LANGUAGE."_admin";
		}
		else
		{
			// $fname = e_LANGUAGE;
			$fname = e_LANGUAGE."_front";
		}

		if($flat === true) // support for alt_auth/languages/English/English_log.php etc.
		{
			$path = e_PLUGIN.$plugin.'/languages/'.e_LANGUAGE.'/'.$fname.'.php';
		}
		else
		{
			$path = e_PLUGIN.$plugin.'/languages/'.$fname.'.php';
		}

		if(deftrue('E107_DBG_INCLUDES'))
		{
			self::getMessage()->addDebug("Attempting to Load: ".$path);
		}


		self::setRegistry($cstring, true);

		return self::includeLan($path, false);
	}

	/**
	 * Simplify importing of theme Language files (following e107 plugin structure standards).
	 * All inputs are sanitized.
	 *
	 * Examples:
	 * <code><?php
	 * 	// import defeinitions from /e107_themes/[CurrentTheme]/languages/[CurrentLanguage]/lan.php
	 * 	e107::themeLan('lan');
	 *
	 * 	// import defeinitions from /e107_themes/[currentTheme]/languages/[CurrentLanguage].php
	 * 	e107::themeLan();
	 *
	 * 	// import defeinitions from /e107_themes/[currentTheme]/languages/[CurrentLanguage]_lan.php
	 * 	e107::themeLan('lan', null, true);
	 *
	 * 	// import defeinitions from /e107_themes/[currentTheme]/languages/[CurrentLanguage]/admin/lan.php
	 * 	e107::themeLan('admin/lan');
	 *
	 * 	// import defeinitions from /e107_themes/some_theme/languages/[CurrentLanguage].php
	 * 	e107::themeLan('', 'some_theme');
	 * </code>
	 *
	 * @param string $fname filename without the extension part (e.g. 'common' for common.php)
	 * @param string $theme theme name, if null current theme will be used
	 * @param boolean $flat false (default, preferred) Language folder structure; true - prepend Language to file name
	 * @return bool
	 */
	public static function themeLan($fname = '', $theme = null, $flat = false)
	{
		if(null === $theme) $theme = THEME.'languages/';
		else $theme = e_THEME.preg_replace('#[^\w/]#', '', $theme).'/languages/';

		$cstring  = 'themelan/'.$theme.$fname.($flat ? '_1' : '_0');
		if(self::getRegistry($cstring)) return;

		if($fname) $fname = e_LANGUAGE.($flat ? '_' : '/').preg_replace('#[^\w/]#', '', trim($fname, '/'));
		else $fname = e_LANGUAGE;

		$path = $theme.$fname.'.php';

		if(deftrue('E107_DBG_INCLUDES'))
		{
			self::getMessage()->addDebug("Attempting to Load: ".$path);
		}

		self::setRegistry($cstring, true);

		return self::includeLan($path, false);
	}



	/**
	 * PREFERRED Generic Language File Loading Function for use by theme and plugin developers.
	 * Language-file equivalent to e107::js, e107::meta and e107::css
	 *
	 * FIXME disallow themes and plugins named 'core' and 'theme'
	 *
	 * @param string $type
	 *   'theme' or plugin name
	 * @param string $fname
	 *   (optional): relative path to the theme or plugin language folder. (same as in the other functions)
	 *   when missing, [e_LANGUAGE]_front.php will be used, when true [e_LANGUAGE]_admin.php will be used
	 * @param $options
	 *   Set to True for admin.
	 *
	 * @example e107::lan('theme'); // Loads THEME."languages/English.php (if English is the current language)
	 * @example e107::lan('gallery'); // Loads e_PLUGIN."gallery/languages/English_front.php (if English is the current language)
	 * @example e107::lan('gallery', 'admin'); // Loads e_PLUGIN."gallery/languages/English/admin.php (if English is the current language)
	 * @example e107::lan('gallery', 'admin', true); // Loads e_PLUGIN."gallery/languages/English_admin.php (if English is the current language)
	 * @example e107::lan('gallery', 'admin/example'); // Loads e_PLUGIN."gallery/languages/English/admin/example.php (if English is the current language)
	 * @example e107::lan('gallery', true); // Loads e_PLUGIN."gallery/languages/English_admin.php (if English is the current language)
	 * @example e107::lan('gallery', "something", true); // Loads e_PLUGIN."gallery/languages/English_something.php (if English is the current language)
	 * @example e107::lan('gallery', true, true); // Loads e_PLUGIN."gallery/languages/English/English_admin.php (if English is the current language)
	 * @example e107::lan('gallery', false, true); // Loads e_PLUGIN."gallery/languages/English/English_front.php (if English is the current language)
	 */
	public static function lan($type, $fname = null, $options = null)
	{
		$options = $options ? true : false;
		switch ($type)
		{
			case 'core' :
				self::coreLan($fname, $options);
			break;

			case 'theme' :
				self::themeLan($fname, null,  $options);
				break;
			default :
				self::plugLan($type, $fname, $options);
				break;
		}

	}


	/**
	 * Generic PREF retrieval Method for use by theme and plugin developers.
	 * @param string $type : 'core', 'theme', plugin-name
	 * @param $pname : name of specific preference, or leave blank for full array.
	 * @param null $default
	 * @return mixed
	 */
	public static function pref($type = 'core', $pname = null, $default = null)
	{

		switch ($type)
		{
			case 'core' :
				return self::getPref($pname, $default);
			break;

			case 'theme' :
				return self::getThemePref($pname, $default);
			break;

			default:
				return self::getPlugPref($type, $pname, $default);
			break;
		}

	}


	/**
	 * Set or Get the current breadcrumb array.
	 * @param array $array
	 * @return array|null
	 */
	public static function breadcrumb($array = array())
	{

		if(empty($array)) // read
		{

			if(empty(self::$_breadcrumb)) //Guess what it should be..
			{
				if(defined('PAGE_NAME'))  // BC search for "PAGE_NAME"
				{
					return array(0=> array('text'=>PAGE_NAME, 'url'=>null));
				}
				elseif($caption = e107::getRender()->getMainCaption()) // BC search for primary render caption
				{
					return array(0=> array('text'=>$caption, 'url'=>null));
				}

			}

			return self::$_breadcrumb;
		}





		self::$_breadcrumb = $array; // write.

		return null;
	}

	/**
	 * Static (easy) sef-url creation method (works with e_url.php @see /index.php)
	 *
	 * @param string    $plugin - plugin folder name
	 * @param string    $key assigned in e_url.php configuration.
	 * @param array     $row Array of variables in url config.
	 * @param array     $options  (optional) An associative array of additional options, with the following elements:
	 * @param string    $options['mode']  abs | full
	 * @param array     $options['query']  An array of query key/value-pairs (without any URL-encoding) to append to the URL.
	 * @param string    $options['fragment'] A fragment identifier (named anchor) to append to the URL. Do not include the leading '#' character.
	 * @param bool      $options['legacy'] When true legacy urls will be generated regardless of mod-rewrite status.
	 * @return string
	 */
	public static function url($plugin='', $key=null, $row=array(), $options = array())
	{

		/* backward compat - core keys. ie. news/xxx/xxx user/xxx/xxx etc, */
		$legacy = array('news','page','search','user','download','gallery');

		if(strpos($plugin,'/')!==false)
		{
			$tmp = explode("/",$plugin,2);

			if(in_array($tmp[0], $legacy))
			{
				return self::getUrl()->create($plugin, $key, $row);
			}

			// shorthand - for internal use.
			$plugin = $tmp[0];
			$row = $key;
			$key = $tmp[1];
		}

		if(!$tmp = self::getRegistry('core/e107/addons/e_url'))
		{
			$tmp = self::getUrlConfig();
			self::setRegistry('core/e107/addons/e_url',$tmp);
		}

		$tp = self::getParser();

		$pref = self::getPref('e_url_alias');
		$sefActive = self::getPref('e_url_list');
		$rootNamespace = self::getPref('url_main_module');


		if(is_string($options)) // backwards compat.
		{
			$options = array(
				'mode' => $options,
			);
		}

		// Merge in defaults.
		$options += array(
			'mode'     => 'abs',
			'fragment' => '',
			'query'    => array(),
		);

		if(isset($options['fragment']) && $options['fragment'] !== '')
		{
			$options['fragment'] = '#' . $options['fragment'];
		}

		if(!empty($tmp[$plugin][$key]['sef']))
		{
			if(!empty($tmp[$plugin][$key]['alias']))
			{
				$alias = (!empty($pref[e_LAN][$plugin][$key])) ? $pref[e_LAN][$plugin][$key] : $tmp[$plugin][$key]['alias'];

				if(!empty($rootNamespace) && $rootNamespace === $plugin)
				{
					$replaceAlias = array('{alias}\/','{alias}/');
					$tmp[$plugin][$key]['sef'] = str_replace($replaceAlias, '', $tmp[$plugin][$key]['sef']);
				}
				else
				{
					$tmp[$plugin][$key]['sef'] = str_replace('{alias}', $alias, $tmp[$plugin][$key]['sef']);
				}

			}


			preg_match_all('#{([a-z_]*)}#', $tmp[$plugin][$key]['sef'],$matches);

			$active = true;

			foreach($matches[1] as $k=>$v) // check if a field value is missing, if so, revent to legacy url.
			{
				if(!isset($row[$v]))
				{
					self::getMessage()->addDebug("Missing value for ".$v." in ".$plugin."/e_url.php - '".$key."'");
					$active = false;
					break;
				}
			}

			if(empty($sefActive[$plugin])) // SEF disabled.
			{
				self::getDebug()->log('SEF URL for <b>'.$plugin.'</b> disabled.');
				$active = false;
			}



			if(deftrue('e_MOD_REWRITE') && ($active == true) && empty($options['legacy']))  // Search-Engine-Friendly URLs active.
			{
				$rawUrl = $tp->simpleParse($tmp[$plugin][$key]['sef'], $row);

				if($options['mode'] === 'full')
				{
					$sefUrl = SITEURL.$rawUrl;
				}
				elseif($options['mode'] === 'raw')
				{
					$sefUrl = $rawUrl;
				}
				else
				{
					$sefUrl = e_HTTP.$rawUrl;
				}

				// Append the query.
				if (is_array($options['query']) && !empty($options['query'])) {
					$sefUrl .= (strpos($sefUrl, '?') !== FALSE ? '&' : '?') . self::httpBuildQuery($options['query']);
				}

				return $sefUrl . $options['fragment'];
			}
			else // Legacy URL.
			{

				$srch = array();
				$repl = array();

				foreach($matches[0] as $k=>$val)
				{
					$srch[] = '$'.($k+1);
					$repl[] = $val;
				}

				$template = isset($tmp[$plugin][$key]['legacy']) ? $tmp[$plugin][$key]['legacy'] : $tmp[$plugin][$key]['redirect'];

				$urlTemplate = str_replace($srch,$repl, $template);
				$urlTemplate = $tp->replaceConstants($urlTemplate, $options['mode']);
				$legacyUrl = $tp->simpleParse($urlTemplate, $row);

				$legacyUrl = preg_replace('/&?\$[\d]/i', "", $legacyUrl); // remove any left-over $x (including prefix of '&')


				// Avoid duplicate query keys. eg. URL has ?id=x and $options['query']['id'] exists.
				// @see forum/e_url.php - topic/redirect and forum/view_shortcodes.php sc_post_url()
				list($legacyUrl,$tmp) = explode("?",$legacyUrl);

				if(!empty($tmp))
				{
					if (strpos($tmp, '=') === false)
					{
						// required for legacy urls of type "request.php?download.43"
						// @see: issue #3275
						$legacyUrl .= '?' . $tmp;
						$options['query'] = null;
					}
					else
					{

						parse_str($tmp,$qry);

						foreach($qry as $k=>$v)
						{
							if(!isset($options['query'][$k])) // $options['query'] overrides any in the original URL.
							{
								$options['query'][$k] = $v;
							}
						}

					}
				}

				// Append the query.
				if (is_array($options['query']) && !empty($options['query']))
				{

					$legacyUrl .= (strpos($legacyUrl, '?') !== FALSE ? '&' : '?') . self::httpBuildQuery($options['query']);
				}

				return $legacyUrl . $options['fragment'];
			}


		}

		if(!empty($plugin))
		{
			self::getMessage()->addDebug("e_url.php in <b>".e_PLUGIN.$plugin."</b> is missing the key: <b>".$key."</b>. Or, you may need to <a href='".e_ADMIN."db.php?mode=plugin_scan'>scan your plugin directories</a> to register e_url.php");
		}
		return false;

		/*
		elseif(varset($tmp[$plugin][$key]['redirect']))
		{
			return self::getParser()->replaceConstants($tmp[$plugin][$key]['redirect'],'full');
		}

		return;
		*/

	}


	/**
	 * Simple redirect method for developers.
	 *
	 * @param string $url
	 *  'admin' to redirect to admin entry page or leave blank to go to home page
	 *  (SITEURL).
	 * @param int $http_response_code
	 *  The HTTP status code to use for the redirection, defaults to 302.
	 *  The valid values for 3xx redirection status codes are defined in RFC 2616
	 *  and the draft for the new HTTP status codes:
	 *  - 301: Moved Permanently (the recommended value for most redirects).
	 *  - 302: Found (default in PHP, sometimes used for spamming search engines).
	 *  - 303: See Other.
	 *  - 304: Not Modified.
	 *  - 305: Use Proxy.
	 *  - 307: Temporary Redirect.
	 * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.3
	 * @see https://tools.ietf.org/html/draft-reschke-http-status-308-07
	 */
	public static function redirect($url = '', $http_response_code = 301)
	{
		self::getRedirect()->go($url, true, $http_response_code);
	}


	/**
	 * Retrieve error page handler.
	 *
	 * @return error_page
	 */
	public static function getError()
	{
		return self::getSingleton('error_page', true);
	}


	/**
	 * Parses an array into a valid, rawurlencoded query string. This differs from http_build_query() as we need to
	 * rawurlencode() (instead of urlencode()) all query parameters.
	 * @param array $query The query parameter array to be processed, e.g. $_GET.
	 * @param string $parent Internal use only. Used to build the $query array key for nested items.
	 * @return array A rawurlencoded string which can be used as or appended to the URL query string.
	 */
	public static function httpBuildQuery(array $query, $parent = '')
	{
		$params = array();

		foreach($query as $key => $value)
		{
			$key = ($parent ? $parent . '[' . rawurlencode($key) . ']' : rawurlencode($key));

			// Recurse into children.
			if(is_array($value))
			{
				$params [] = self::httpBuildQuery($value, $key);
			}
			// If a query parameter value is NULL, only append its key.
			elseif(!isset($value))
			{
				$params [] = $key;
			}
			else
			{
				// For better readability of paths in query strings, we decode slashes.
				$params [] = $key . '=' . str_replace('%2F', '/', rawurlencode($value));
			}
		}

		return implode('&', $params);
	}


	public static function minify($js,$options=array())
	{
		if(empty($js))
		{
			return null;
		}

		require_once(e_HANDLER."jsshrink/Minifier.php");
		return JShrink\Minifier::minify($js,$options);
	}



	/**
	 * Set or Retrieve WYSIWYG active status. (replaces constant  e_WYSIWYG)
	 *
	 * @param bool/string $val if null, return current value, otherwise define editor to use
	 * @param bool $returnEditor true = return name of active editor, false = return "false" for non wysiwyg editor, return "true" if wysiwyg editor should be used
	 * @return bool|mixed
	 */
	public static function wysiwyg($val=null, $returnEditor=false)
	{
		static $editor = 'bbcode';
		static $availEditors;
		$fallbackEditor = 'bbcode';

		if (self::getPref('wysiwyg',false) != true)
		{
			// wysiwyg disabled by global pref
			$editor = $fallbackEditor;
		}
		else
		{
			if(!isset($availEditors))
			{
				// init list of installed wysiwyg editors
				$availEditors = array_keys(e107::getPlug()->getInstalledWysiwygEditors());
			}

			if(!is_null($val))
			{
				// set editor if value given
				$editor = empty($val) ? $fallbackEditor : ($val === 'default' ? true : $val);
			}


			// check if choosen editor is installed,
			// if not, but a different editor is available use that one (e.g. tinymce4 choosen, but only simplemde available available, use simplemde)
			// if no wysiwyg editor available, use fallback editor (bbcode)
			if(is_bool($editor) || ($editor !== $fallbackEditor && !in_array($editor, $availEditors)))
			{
				$editor = count($availEditors) > 0 ? $availEditors[0] : $fallbackEditor;
			}
		}
		// $returnEditor => false:
		// false => fallback editor (bbcode)
		// true => default wysiwyg editor
		// $returnEditor => true:
		// return name of the editor
		//return $returnEditor ? $editor : ($editor === $fallbackEditor || $editor === false ? false : true);
		return $returnEditor ? $editor : ($editor !== $fallbackEditor);
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
		//global $pref;
		switch ($type)
		{
			case 'runtime' :
				$searchPath[1] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'_'.$unitName.'.php';
				$searchPath[2] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'/'.$unitName.'.php';
				$searchPath[3] = e_PLUGIN.$unitName.'/languages/'.e_LANGUAGE.'.php'; // menu language file.
				break;
			case 'admin' :

				$aLangPref = self::getPref('adminlanguage');
				$adminLan = vartrue($aLangPref, e_LANGUAGE);

				$searchPath[1] = e_PLUGIN.$unitName.'/languages/'.$adminLan.'_admin_'.$unitName.'.php';
				$searchPath[2] = e_PLUGIN.$unitName.'/languages/'.$adminLan.'/'.'admin_'.$unitName.'.php';
				$searchPath[3] = e_PLUGIN.$unitName.'/languages/'.$adminLan.'/admin/'.$adminLan.'.php';
				$searchPath[4] = e_PLUGIN.$unitName.'/languages/'.$adminLan.'/'.$adminLan.'_admin.php'; // Preferred.
				$searchPath[5] = e_PLUGIN.$unitName.'/languages/'.$adminLan.'_admin.php'; // consistent with English_global.php, English_log.php etc.

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
		if (self::getPref('noLanguageSubs') || (e_LANGUAGE === 'English'))
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
	 * This is done before e107_dirs initilization and config include
	 * @param bool $checkS basic security check (0.7 like), will be extended in the future
	 * @return e107
	 */
	public function prepare_request($checkS = true)
	{

		// Block common bad agents / queries / php issues.
		array_walk($_SERVER,  array('self', 'filter_request'), '_SERVER');
		if (isset($_GET)) array_walk($_GET,     array('self', 'filter_request'), '_GET');
		if (isset($_POST))
		{
			array_walk($_POST,    array('self', 'filter_request'), '_POST');
			reset($_POST);		// Change of behaviour in PHP 5.3.17?
		}
		if (isset($_COOKIE)) array_walk($_COOKIE,  array('self', 'filter_request'), '_COOKIE');
		if (isset($_REQUEST)) array_walk($_REQUEST, array('self', 'filter_request'), '_REQUEST');

		// A better way to detect an AJAX request. No need for "ajax_used=1";
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
		{
  			define('e_AJAX_REQUEST', true);
		}
		else
		{
			define('e_AJAX_REQUEST', isset($_REQUEST['ajax_used']));
		}

		unset($_REQUEST['ajax_used']); // removed because it's auto-appended from JS (AJAX), could break something...

		//$GLOBALS['_E107'] - minimal mode - here because of the e_AJAX_REQUEST
		if(isset($GLOBALS['_E107']['minimal']) || e_AJAX_REQUEST || deftrue('e_MINIMAL'))
		{
			$_e107vars = array('forceuserupdate', 'online', 'theme', 'menus', 'prunetmp');
			$GLOBALS['_E107']['minimal'] = true;
			// lame but quick - allow online when ajax request only, additonal checks are made in e_online class
			if(e_AJAX_REQUEST && !isset($GLOBALS['_E107']['online']) && !isset($GLOBALS['_E107']['minimal'])) unset($_e107vars[1]);

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

		// we can now start use $e107->_E107
		if(isset($GLOBALS['_E107']) && is_array($GLOBALS['_E107'])) $this->_E107 = & $GLOBALS['_E107'];

		// remove ajax_used=1 from query string to avoid SELF problems, ajax should always be detected via e_AJAX_REQUEST constant
		$_SERVER['QUERY_STRING'] = trim(str_replace(array('ajax_used=1', '&&'), array('', '&'), $_SERVER['QUERY_STRING']), '&');

		/* PathInfo doesn't break anything, URLs should be always absolute. Disabling the below forever.
		// e107 uses relative url's, which are broken by "pretty" URL's. So for now we don't support / after .php
		if(($pos = strpos($_SERVER['PHP_SELF'], '.php/')) !== false) // redirect bad URLs to the correct one.
		{
			$new_url = substr($_SERVER['PHP_SELF'], 0, $pos+4);
			$new_loc = ($_SERVER['QUERY_STRING']) ? $new_url.'?'.$_SERVER['QUERY_STRING'] : $new_url;
			header('Location: '.$new_loc);
			exit();
		}
		*/

		// If url contains a .php in it, PHP_SELF is set wrong (imho), affecting all paths.  We need to 'fix' it if it does.
		$_SERVER['PHP_SELF'] = (($pos = stripos($_SERVER['PHP_SELF'], '.php')) !== false ? substr($_SERVER['PHP_SELF'], 0, $pos+4) : $_SERVER['PHP_SELF']);

		// setup some php options
		self::ini_set('magic_quotes_runtime',     0);
		self::ini_set('magic_quotes_sybase',      0);
	//	self::ini_set('arg_separator.output',     '&amp;'); // non-standard and bad for third-party script compatibility. @see https://github.com/e107inc/e107/issues/3116
		self::ini_set('session.use_only_cookies', 1);
		self::ini_set('session.use_trans_sid',    0);
		self::ini_set('session.cookie_httponly',  1); // cookie won't be accessible by scripting languages, such as JavaScript. Can effectively help to reduce identity theft through XSS attacks

		//  Ensure thet '.' is the first part of the include path
		$inc_path = explode(PATH_SEPARATOR, ini_get('include_path'));
		if($inc_path[0] != '.')
		{
			array_unshift($inc_path, '.');
			$inc_path = implode(PATH_SEPARATOR, $inc_path);
			self::ini_set('include_path', $inc_path);
		}
		unset($inc_path);

		return $this;
	}

	/**
	 * Filter User Input - used by array_walk in prepare_request method above.
	 * @param string $input array value
	 * @param string $key array key
	 * @param string $type array type _SESSION, _GET etc.
	 * @param bool $base64
	 * @return bool|null
	 */
	public static function filter_request($input,$key,$type,$base64=FALSE)
	{
		if(is_string($input) && trim($input)=="")
		{
			return '';
		}

		if (is_array($input))
		{
			return array_walk($input, array('self', 'filter_request'), $type);
		}


		if($type == "_POST" || ($type == "_SERVER" && ($key == "QUERY_STRING")))
		{
			if($type == "_POST" && ($base64 === false))
			{
				$input = preg_replace("/(\[code\])(.*?)(\[\/code\])/is","",$input);
			}

			$regex = "/(base64_decode|chr|php_uname|fwrite|fopen|fputs|passthru|popen|proc_open|shell_exec|exec|proc_nice|proc_terminate|proc_get_status|proc_close|pfsockopen|apache_child_terminate|posix_kill|posix_mkfifo|posix_setpgid|posix_setsid|posix_setuid|phpinfo) *?\((.*) ?\;?/i";
			if(preg_match($regex,$input))
			{
				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}

			// Check for XSS JS
			$regex = "/(document\.location|document\.write|document\.cookie)/i";
			if(preg_match($regex,$input))
			{
				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}


			// Suspicious HTML.
			if(strpos($input, '<body/onload')!==false)
			{
				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}

			if(preg_match("/system\((.*);.*\)/i",$input))
			{
				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}

			$regex = "/(wget |curl -o |lwp-download|onmouse)/i";
			if(preg_match($regex,$input))
			{
				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}

		}

		if($type === '_GET') // Basic XSS check.
		{
			if(stripos($input, "<script")!==false || stripos($input, "%3Cscript")!==false)
			{
				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}

		}

		if($type == "_SERVER")
		{

			if(($key == "QUERY_STRING") && (
				strpos(strtolower($input),"../../")!==FALSE
				|| stripos($input,"php:")!==FALSE
				|| stripos($input,"data:")!==FALSE
				|| stripos($input,"%3cscript")!==FALSE
				))
			{

				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}

			if(($key == "QUERY_STRING") && empty($_GET['hauth_done']) && empty($_GET['hauth.done']) && ( // exception for hybridAuth.
				strpos(strtolower($input),"=http")!==FALSE
				|| strpos(strtolower($input),strtolower("http%3A%2F%2F"))!==FALSE
				))
			{

				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}

			if(($key == "HTTP_USER_AGENT") && strpos($input,"libwww-perl")!==FALSE)
			{
				header('HTTP/1.0 400 Bad Request', true, 400);
				if(deftrue('e_DEBUG'))
				{
					echo "Bad Request: ".__METHOD__." : ". __LINE__;
				}
				exit();
			}


		}

		if(strpos(str_replace('.', '', $input), '22250738585072011') !== FALSE) // php-bug 53632
		{
			header('HTTP/1.0 400 Bad Request', true, 400);
			if(deftrue('e_DEBUG'))
			{
				echo "Bad Request: ".__METHOD__." : ". __LINE__;
			}
			exit();
		}

		if($base64 != true)
		{
			self::filter_request(base64_decode($input, true),$key,$type,true);
		}



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
		if(!defined('MAGIC_QUOTES_GPC'))
		{
			define('MAGIC_QUOTES_GPC', (ini_get('magic_quotes_gpc') ? true : false));
		}

		define('MPREFIX', self::getMySQLConfig('prefix')); // mysql prefix

		define('CHARSET', 'utf-8'); // set CHARSET for backward compatibility

		if(!defined('e_MOD_REWRITE')) // Allow e107_config.php to override.
		{
			define('e_MOD_REWRITE', (getenv('HTTP_MOD_REWRITE')=='On'||  getenv('REDIRECT_HTTP_MOD_REWRITE')=='On' ? true : false));
		}

		if(!defined('e_MOD_REWRITE_MEDIA')) // Allow e107_config.php to override.
		{
			define('e_MOD_REWRITE_MEDIA', (getenv('HTTP_MOD_REWRITE_MEDIA')=='On' || getenv('REDIRECT_HTTP_MOD_REWRITE_MEDIA')=='On'  ? true : false));
		}

		if(!defined('e_MOD_REWRITE_STATIC')) // Allow e107_config.php to override.
		{
			define('e_MOD_REWRITE_STATIC', (getenv('HTTP_MOD_REWRITE_STATIC')=='On' || getenv('REDIRECT_HTTP_MOD_REWRITE_STATIC')=='On'  ? true : false));
		}

		$subdomain = false;

		// Define the domain name and subdomain name.
		if(is_numeric(str_replace(".","",$_SERVER['HTTP_HOST'])))
		{
			$domain = false;
			$subdomain = false;
		}
		else
		{
			$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			$domain = preg_replace('/^www\.|:\d*$/', '', $host); // remove www. and port numbers.

			$dtemp = explode(".", $domain);

			if(count($dtemp) > 2 && strlen($dtemp[0]) === 2) // eg. fr.mysite.com or fr.mysite.com.fr
			{
				$subdomain = $dtemp[0];
				unset($dtemp[0]);
				$domain = implode('.',$dtemp); // remove subdomain because it's a language-code.
			}

		}

		if($domain === 'localhost') // Fix for chrome.
		{
			$domain = false;
		}

		define("e_DOMAIN", $domain);
		define("e_SUBDOMAIN", ($subdomain) ? $subdomain : false);

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
	 * Relaitve server path - set_path() helper
	 * @param string $dir
	 * @return string
	 */
	public function get_override_rel($dir)
	{
		if(isset($this->e107_dirs[$dir.'_SERVER']))
		{
			return $this->e107_dirs[$dir.'_SERVER'];
		}
		$ret = e_BASE.$this->e107_dirs[$dir.'_DIRECTORY'];


		return $ret;
	}

	/**
	 * Absolute HTTP path - set_path() helper
	 * @param string $dir
	 * @return string
	 */
	public function get_override_http($dir)
	{
		if(isset($this->e107_dirs[$dir.'_HTTP']))
		{
			return $this->e107_dirs[$dir.'_HTTP'];
		}

		return e_HTTP.$this->e107_dirs[$dir.'_DIRECTORY'];
	}

	/**
	 * Set all environment vars and constants
	 * FIXME - remove globals
	 * @return e107
	 */
	public function set_paths()
	{
		// ssl_enabled pref not needed anymore, scheme is auto-detected
		$this->HTTP_SCHEME = 'http';
		if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443)
		{
			$this->HTTP_SCHEME =  'https';
		}

		$path = ""; $i = 0;

		// FIXME - Again, what if someone moves handlers under the webroot?
		if(!self::isCli())
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

//var_dump($this->server_path);
//exit;

		if ($this->server_path == "//")
		{
			$this->server_path = "/";
		}

		// Absolute file-path of directory containing class2.php
		//	define("e_ROOT", realpath(dirname(__FILE__)."/../")."/");




		$this->relative_base_path = (!self::isCli()) ? $path : e_ROOT;
		$this->http_path =  filter_var("http://{$_SERVER['HTTP_HOST']}{$this->server_path}", FILTER_SANITIZE_URL);
		$this->https_path = filter_var("https://{$_SERVER['HTTP_HOST']}{$this->server_path}", FILTER_SANITIZE_URL);

		$this->file_path = $path;

		if(defined('e_HTTP') && defined('e_ADMIN')) return $this;

		if(!defined('e_HTTP'))
		{
			define('e_HTTP', $this->server_path);			// Directory of site root relative to HTML base directory
		}

	  	define('e_BASE', $this->relative_base_path);

		// Base dir of web stuff in server terms. e_ROOT should always end with e_HTTP, even if e_HTTP = '/'
		define('SERVERBASE', substr(e_ROOT, 0, -strlen(e_HTTP) + 1));

		if(isset($_SERVER['DOCUMENT_ROOT']))
		{
		  	define('e_DOCROOT', $_SERVER['DOCUMENT_ROOT']."/");
		}
		else
		{
		  	define('e_DOCROOT', false);
		}

		//BC temporary fixes
		if (!isset($this->e107_dirs['UPLOADS_SERVER']) && $this->e107_dirs['UPLOADS_DIRECTORY']{0} == "/")
		{
			$this->e107_dirs['UPLOADS_SERVER'] = $this->e107_dirs['UPLOADS_DIRECTORY'];
		}
		if (!isset($this->e107_dirs['DOWNLOADS_SERVER']) && $this->e107_dirs['DOWNLOADS_DIRECTORY']{0} == "/")
		{
			$this->e107_dirs['DOWNLOADS_SERVER'] = $this->e107_dirs['DOWNLOADS_DIRECTORY'];
		}

		//
		// HTTP relative paths
		//
		define('e_ADMIN', $this->get_override_rel('ADMIN'));
		define('e_IMAGE', $this->get_override_rel('IMAGES'));
		define('e_THEME', $this->get_override_rel('THEMES'));
		define('e_PLUGIN', $this->get_override_rel('PLUGINS'));
		define('e_FILE', $this->get_override_rel('FILES'));
		define('e_HANDLER', $this->get_override_rel('HANDLERS'));
		define('e_LANGUAGEDIR', $this->get_override_rel('LANGUAGES'));

		define('e_DOCS', $this->get_override_rel('HELP')); // WILL CHANGE SOON - $this->_get_override_rel('DOCS')
		define('e_HELP', $this->get_override_rel('HELP'));

		define('e_MEDIA', $this->get_override_rel('MEDIA'));
		define('e_MEDIA_BASE', $this->get_override_rel('MEDIA_BASE'));
		define('e_MEDIA_FILE', $this->get_override_rel('MEDIA_FILES'));
		define('e_MEDIA_VIDEO', $this->get_override_rel('MEDIA_VIDEOS'));
		define('e_MEDIA_IMAGE', $this->get_override_rel('MEDIA_IMAGES'));
		define('e_MEDIA_ICON', $this->get_override_rel('MEDIA_ICONS'));
	//	define('e_MEDIA_AVATAR', $this->get_override_rel('MEDIA_AVATARS'));

		define('e_DOWNLOAD', $this->get_override_rel('DOWNLOADS'));
		define('e_UPLOAD', $this->get_override_rel('UPLOADS'));

		define('e_CORE', $this->get_override_rel('CORE'));
		define('e_SYSTEM', $this->get_override_rel('SYSTEM'));
		define('e_SYSTEM_BASE', $this->get_override_rel('SYSTEM_BASE'));

		define('e_WEB', $this->get_override_rel('WEB'));
		define('e_WEB_JS', $this->get_override_rel('WEB_JS'));
		define('e_WEB_CSS', $this->get_override_rel('WEB_CSS'));
		define('e_WEB_IMAGE', $this->get_override_rel('WEB_IMAGES'));
//		define('e_WEB_PACK', $this->get_override_rel('WEB_PACKS'));

		define('e_CACHE', $this->get_override_rel('CACHE'));
		define('e_CACHE_CONTENT', $this->get_override_rel('CACHE_CONTENT'));
		define('e_CACHE_IMAGE', $this->get_override_rel('CACHE_IMAGE'));
		define('e_CACHE_DB', $this->get_override_rel('CACHE_DB'));
		define('e_CACHE_URL', $this->get_override_rel('CACHE_URL'));

		define('e_LOG', $this->get_override_rel('LOGS'));
		define('e_BACKUP', $this->get_override_rel('BACKUP'));
		define('e_TEMP', $this->get_override_rel('TEMP'));
		define('e_IMPORT', $this->get_override_rel('IMPORT'));
		//
		// HTTP absolute paths
		//
		define("e_ADMIN_ABS", $this->get_override_http('ADMIN'));
		define("e_IMAGE_ABS", $this->get_override_http('IMAGES'));
		define("e_THEME_ABS", $this->get_override_http('THEMES'));
		define("e_PLUGIN_ABS", $this->get_override_http('PLUGINS'));
		define("e_FILE_ABS", $this->get_override_http('FILES')); // Deprecated!
		define("e_DOCS_ABS", $this->get_override_http('DOCS'));
		define("e_HELP_ABS", $this->get_override_http('HELP'));
		define("e_IMPORT_ABS", false);

		// DEPRECATED - not a legal http query now!
		//define("e_HANDLER_ABS", $this->get_override_http('HANDLERS'));
		//define("e_LANGUAGEDIR_ABS", $this->get_override_http('LANGUAGES'));
		//define("e_LOG_ABS", $this->get_override_http('LOGS'));

		define("e_MEDIA_ABS", $this->get_override_http('MEDIA'));
		define('e_MEDIA_FILE_ABS', $this->get_override_http('MEDIA_FILES'));
		define('e_MEDIA_VIDEO_ABS', $this->get_override_http('MEDIA_VIDEOS'));
		define('e_MEDIA_IMAGE_ABS', $this->get_override_http('MEDIA_IMAGES'));
		define('e_MEDIA_ICON_ABS', $this->get_override_http('MEDIA_ICONS'));
//		define('e_MEDIA_AVATAR_ABS', $this->get_override_http('MEDIA_AVATARS'));

		// XXX DISCUSSS - e_JS_ABS, e_CSS_ABS etc is not following the naming standards but they're more usable.
		// Example: e_JS_ABS vs e_WEB_JS_ABS

		//XXX Absolute is assumed.
		define('e_WEB_ABS', $this->get_override_http('WEB'));
		define('e_JS_ABS', $this->get_override_http('WEB_JS'));
		define('e_CSS_ABS', $this->get_override_http('WEB_CSS'));
//		define('e_PACK_ABS', $this->get_override_http('WEB_PACKS'));
		define('e_WEB_IMAGE_ABS', $this->get_override_http('WEB_IMAGES'));

		define('e_JS', $this->get_override_http('WEB_JS')); // ABS Alias
		define('e_CSS', $this->get_override_http('WEB_CSS')); // ABS Alias

		define('e_AVATAR', $this->get_override_rel('AVATARS'));
		define('e_AVATAR_UPLOAD', $this->get_override_rel('AVATARS_UPLOAD'));
		define('e_AVATAR_DEFAULT', $this->get_override_rel('AVATARS_DEFAULT'));

		define('e_AVATAR_ABS', $this->get_override_http('AVATARS'));
		define('e_AVATAR_UPLOAD_ABS', $this->get_override_http('AVATARS_UPLOAD'));
		define('e_AVATAR_DEFAULT_ABS', $this->get_override_http('AVATARS_DEFAULT'));

		if(defined('e_MEDIA_STATIC')) // experimental - subject to change.
		{
			define('e_CACHE_IMAGE_ABS', $this->get_override_http('CACHE_IMAGE'));
		}

		// Special

		define('e_BOOTSTRAP', e_WEB."bootstrap/");

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
	 *
	 * @param boolean $no_cbrace remove curly brackets from the url
	 * @return e107
	 */
	public function set_urls($no_cbrace = true)
	{
		//global $PLUGINS_DIRECTORY,$ADMIN_DIRECTORY, $eplug_admin;
		$PLUGINS_DIRECTORY = $this->getFolder('plugins');
		$ADMIN_DIRECTORY = $this->getFolder('admin');

		// Outdated
		/*$requestQry = '';
		$requestUrl = $_SERVER['REQUEST_URI'];
		if(strpos($_SERVER['REQUEST_URI'], '?') !== FALSE)
			list($requestUrl, $requestQry) = explode("?", $_SERVER['REQUEST_URI'], 2); */

		$eplug_admin = vartrue($GLOBALS['eplug_admin'], false);

		// Leave e_SELF BC, use e_REQUEST_SELF instead
		/*// moved after page check - e_PAGE is important for BC
		if($requestUrl && $requestUrl != $_SERVER['PHP_SELF'])
		{
			$_SERVER['PHP_SELF'] = $requestUrl;
		}*/

		$eSelf = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME'];
		$_self = $this->HTTP_SCHEME.'://'.$_SERVER['HTTP_HOST'].$eSelf;



		// START New - request uri/url detection, XSS protection
		// TODO - move it to a separate method
		$requestUri = $requestUrl = '';
		if (isset($_SERVER['HTTP_X_REWRITE_URL']))
		{
			// check this first so IIS will catch
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
			$requestUrl = $this->HTTP_SCHEME.'://'.$_SERVER['HTTP_HOST'].$requestUri;
			// fix request uri
			$_SERVER['REQUEST_URI'] = $requestUri;
		}
		elseif (isset($_SERVER['REQUEST_URI']))
		{
			$requestUri = $_SERVER['REQUEST_URI'];
			$requestUrl = $this->HTTP_SCHEME.'://'.$_SERVER['HTTP_HOST'].$requestUri;
		}
		else
		{
			// go back to e_SELF
			$requestUri = $eSelf;
			$requestUrl = $_self;
            if(defset('e_QUERY'))
			{
				$requestUri .= '?'.e_QUERY; // TODO e_SINGLE_ENTRY check, separate static method for cleaning QUERY_STRING
				$requestUrl .= '?'.e_QUERY;
			}
		}
		// FIXME - basic security - add url sanitize method to e_parse
		$check = rawurldecode($requestUri); // urlencoded by default

		// a bit aggressive XSS protection... convert to e.g. htmlentities if you are not a bad guy
		$checkregx = $no_cbrace ? '[<>\{\}]' : '[<>]';
		if(preg_match('/'.$checkregx.'/', $check))
		{
			// header('HTTP/1.1 403 Forbidden');
			$requestUri = filter_var($requestUri, FILTER_SANITIZE_URL);
			// exit;
		}

		// e_MENU fix
		if(e_MENU)
		{
			$requestUri = str_replace('['.e_MENU.']', '', $requestUri);
			$requestUrl = str_replace('['.e_MENU.']', '', $requestUrl);
			if(defset('e_QUERY')) parse_str(e_QUERY,$_GET);
		}

		define('e_REQUEST_URL', str_replace(array("'", '"'), array('%27', '%22'), $requestUrl)); // full request url string (including domain)

		$tmp = explode('?', e_REQUEST_URL);
		$requestSelf =  array_shift($tmp);

		if(substr($requestSelf,-4) !== '.php' && substr($requestSelf,-1) !== '/')
		{
			$requestSelf .= '/'; // Always include a trailing slash on SEF Urls so that e_REQUEST_SELF."?".e_QUERY doesn't break.
		}

		// the last anti-XSS measure, XHTML compliant URL to be used in forms instead e_SELF

		define('e_REQUEST_SELF', filter_var($requestSelf, FILTER_SANITIZE_URL)); // full URL without the QUERY string
		define('e_REQUEST_URI', str_replace(array("'", '"'), array('%27', '%22'), $requestUri)); // absolute http path + query string
		$tmp2 = explode('?', e_REQUEST_URI);
		define('e_REQUEST_HTTP', array_shift($tmp2)); // SELF URL without the QUERY string and leading domain part

		if(!deftrue('e_SINGLE_ENTRY') && !deftrue('e_SELF_OVERRIDE') )
		{
			$page = substr(strrchr($_SERVER['PHP_SELF'], '/'), 1);

			if(self::isCli() && !empty($_SERVER['_']))
			{
				$page = basename($_SERVER['_']);
			}


			define('e_PAGE', $page);
			define('e_SELF', filter_var($_self, FILTER_SANITIZE_URL));
		}
		else
		{
			define('e_SELF', e_REQUEST_SELF);

			if(deftrue('e_SELF_OVERRIDE')) // see multisite plugin.
			{
				define('e_PAGE', basename($_SERVER['SCRIPT_FILENAME']));
			}
		}



		unset($requestUrl, $requestUri);
		// END request uri/url detection, XSS protection

		// e_SELF has the full HTML path
		$inAdminDir = FALSE;
		$isPluginDir = strpos($_self,'/'.$PLUGINS_DIRECTORY) !== FALSE;		// True if we're in a plugin
		$e107Path = str_replace($this->base_path, '', $_self);				// Knock off the initial bits
		$curPage = basename($_SERVER['SCRIPT_FILENAME']);

		if	(
			 (!$isPluginDir && strpos($e107Path, $ADMIN_DIRECTORY) === 0 ) 									// Core admin directory
			  || ($isPluginDir && (strpos($curPage,'_admin.php') !== false || strpos($curPage,'admin_') === 0 || strpos($e107Path, 'admin/') !== FALSE)) // Plugin admin file or directory
			  || (vartrue($eplug_admin) || deftrue('ADMIN_AREA'))		// Admin forced
			  || (preg_match('/^\/(.*?)\/user(settings\.php|\/edit)(\?|\/)(\d+)$/i', $_SERVER['REQUEST_URI']) && ADMIN)
			  || ($isPluginDir && $curPage === 'prefs.php') //BC Fix for old plugins
			  || ($isPluginDir && $curPage === 'config.php') // BC Fix for old plugins
			  || ($isPluginDir && strpos($curPage,'_config.php')!==false) // BC Fix for old plugins eg. dtree_menu
			)
		{
			$inAdminDir = TRUE;
		}
		if ($isPluginDir)
		{
			$temp = substr($e107Path, strpos($e107Path, '/') +1);
			$plugDir = substr($temp, 0, strpos($temp, '/'));
			define('e_CURRENT_PLUGIN', rtrim($plugDir,'/'));
			define('e_PLUGIN_DIR', e_PLUGIN.e_CURRENT_PLUGIN.'/');
			define('e_PLUGIN_DIR_ABS', e_PLUGIN_ABS.e_CURRENT_PLUGIN.'/');
		}
		else
		{
		//	define('e_CURRENT_PLUGIN', ''); // leave it undefined so it can be added later during sef-url detection.
			define('e_PLUGIN_DIR', '');
			define('e_PLUGIN_DIR_ABS', '');
		}


		if(!defined('e_ADMIN_AREA'))
		{
			define('e_ADMIN_AREA', ($inAdminDir  && !deftrue('USER_AREA')));
		}

		define('ADMINDIR', $ADMIN_DIRECTORY);

		return $this;
	}

	/**
	 * The second part of e107::set_urls()
	 * Supposed to load after database has been initialized
	 *
	 * Implemented out of necessity due to
	 * https://github.com/e107inc/e107/issues/3033
	 *
	 * @return e107
	 */
	public function set_urls_deferred()
	{
		if(self::isCli())
		{
			define('SITEURL', self::getPref('siteurl'));
			define('SITEURLBASE', rtrim(SITEURL,'/'));
		}
		else
		{
			define('SITEURLBASE', $this->HTTP_SCHEME.'://'. filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL));
			define('SITEURL', SITEURLBASE.e_HTTP);
		}


		// login/signup
		define('e_SIGNUP', SITEURL.(file_exists(e_BASE.'customsignup.php') ? 'customsignup.php' : 'signup.php'));

		if(!defined('e_LOGIN'))
		{
			define('e_LOGIN', SITEURL.(file_exists(e_BASE.'customlogin.php') ? 'customlogin.php' : 'login.php'));
		}

		return $this;
	}

	/**
	 * Set request related constants
	 * @param boolean $no_cbrace remove curly brackets from the url
	 * @return e107
	 */
	public function set_request($no_cbrace = true)
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

		$eMENUQry = str_replace(array('%5B','%5D'),array('[',']'),$_SERVER['QUERY_STRING']); //FIX for urlencoded QUERY_STRING without breaking the '+' used by debug.
		if (strpos($eMENUQry, ']') && preg_match('#\[(.*?)](.*)#', $eMENUQry, $matches))
		{
			define('e_MENU', $matches[1]);
			$e_QUERY = $matches[2];
		}
		else
		{
			define('e_MENU', '');
			$e_QUERY = $_SERVER['QUERY_STRING'];
		}

		if ($no_cbrace)	$e_QUERY = str_replace(array('{', '}', '%7B', '%7b', '%7D', '%7d'), '', rawurldecode($e_QUERY));

	//	$e_QUERY = htmlentities(self::getParser()->post_toForm($e_QUERY)); //@see https://github.com/e107inc/e107/issues/719
		$e_QUERY = htmlspecialchars(self::getParser()->post_toForm($e_QUERY));

		// e_QUERY SHOULD NOT BE DEFINED IF IN SNIGLE ENTRY MODE OR ALL URLS WILL BE BROKEN - it's defined later within the the router
		if(!deftrue("e_SINGLE_ENTRY"))
		{
			define('e_QUERY', filter_var($e_QUERY, FILTER_SANITIZE_URL));
			$_SERVER['QUERY_STRING'] = e_QUERY;
		}
		else
		{
		//	 define('e_QUERY', ''); // breaks news sef-urls and possibly others. Moved to index.php.
		}


		define('e_TBQS', $_SERVER['QUERY_STRING']);
	}

	/**
	 * Basic implementation of Browser cache control per user session. Awaiting improvement in future versions
	 * If no argument is passed it returns
	 * boolean (if current page is cacheable).
	 * If string is passed, it's asumed to be aboslute request path (e_REQUEST_URI alike)
	 * If true is passed, e_REQUEST_URI is registered
	 * @param null $set
	 * @return bool|null
	 */
	public static function canCache($set = null)
	{
		$_data = self::getSession()->get('__sessionBrowserCache');
		if(!is_array($_data)) $_data = array();

		if(null === $set)
		{
			return in_array(e_REQUEST_URI, $_data);
		}

		// remove e_REQUEST_URI from the set
		if(false === $set)
		{
			$check = array_search(e_REQUEST_URI, $_data);
			if(false !== $check)
			{
				unset($_data[$check]);
				self::getSession()->set('__sessionBrowserCache', $_data);
				return;
			}
		}

		if(true === $set)
		{
			$set = e_REQUEST_URI;
		}

		if(empty($set) || !is_string($set) || in_array($set, $_data)) return;

		$_data[]  = $set;
		self::getSession()->set('__sessionBrowserCache', array_unique($_data));
	}

	/**
	 * Check if current request is secure (https)
	 * @return boolean TRUE if https, FALSE if http
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
	 * FIXME -  moved to ban helper, replace all calls
	 * @return void
	 */
	 /* No longer required - moved to eIPHelper class
	public function ban()
	{
	} */

	/**
	 * Check the banlist table. $query is used to determine the match.
	 * If $do_return, will always return with ban status - TRUE for OK, FALSE for banned.
	 * If return permitted, will never display a message for a banned user; otherwise will display any message then exit
	 * FIXME - moved to ban helper, replace all calls
	 *
	 *
	 * @param string $query
	 * @param boolean $show_error
	 * @param boolean $do_return
	 * @return boolean
	 */
	 /* No longer required - moved to eIPHelper class
	public function check_ban($query, $show_error = TRUE, $do_return = FALSE)
	{
	} */


	/**
	 * Add an entry to the banlist. $bantype = 1 for manual, 2 for flooding, 4 for multiple logins
	 * Returns TRUE if ban accepted.
	 * Returns FALSE if ban not accepted (i.e. because on whitelist, or invalid IP specified)
	 * FIXME - moved to IP handler, replace all calls
	 * @param string $bantype
	 * @param string $ban_message
	 * @param string $ban_ip
	 * @param integer $ban_user
	 * @param string $ban_notes
	 *
	 * @return boolean check result
	 */
	 /*
	public function add_ban($bantype, $ban_message = '', $ban_ip = '', $ban_user = 0, $ban_notes = '')
	{
		return e107::getIPHandler()->add_ban($bantype, $ban_message, $ban_ip, $ban_user, $ban_notes);
	} */

	/**
	 * Get the current user's IP address
	 * returns the address in internal 'normalised' IPV6 format - so most code should continue to work provided the DB Field is big enougn
	 * FIXME - call ipHandler directly (done for core - left temporarily for BC)
	 * @return string
	 */
	public function getip()
	{
		return self::getIPHandler()->getIP(FALSE);
	}

	/**
	 * Encode an IP address to internal representation. Returns string if successful; FALSE on error
	 * Default separates fields with ':'; set $div='' to produce a 32-char packed hex string
	 * FIXME - moved to ipHandler - check for calls elsewhere
	 * @param string $ip
	 * @param string $div divider
	 * @return string encoded IP
	 */

	public function ipEncode($ip, $div = ':')
	{
		return self::getIPHandler()->ipEncode($ip);
	}

	/**
	 * Takes an encoded IP address - returns a displayable one
	 * Set $IP4Legacy TRUE to display 'old' (IPv4) addresses in the familiar dotted format,
	 * FALSE to display in standard IPV6 format
	 * Should handle most things that can be thrown at it.
	 * FIXME - moved to ipHandler - check for calls elsewhere - core done; left temporarily for BC
	 * @param string $ip encoded IP
	 * @param boolean $IP4Legacy
	 * @return string decoded IP
	 */
	public function ipdecode($ip, $IP4Legacy = TRUE)
	{
		return self::getIPHandler()->ipDecode($ip, $IP4Legacy);
	}

	/**
	 * Given a string which may be IP address, email address etc, tries to work out what it is
	 * Movet to eIPHandler class
	 * FIXME - moved to ipHandler - check for calls elsewhere
	 * @param string $string
	 * @return string ip|email|url|ftp|unknown
	 */
	 /*
	public function whatIsThis($string)
	{
		//return e107::getIPHandler()->whatIsThis($string);
	} */

	/**
	 * Retrieve & cache host name
	 * @deprecated but needed by some old plugins/menus.
	 * @todo Find old calls and replace with code within.
	 * @param string $ip_address
	 * @return string host name
	 */
	public function get_host_name($ip_address)
	{
		return self::getIPHandler()->get_host_name($ip_address);
	}

	/**
	 * MOVED TO eHelper::parseMemorySize()
	 * FIXME - find all calls, replace with eHelper::parseMemorySize() (once eHelper lives in a separate file)
	 *
	 * @param integer $size
	 * @param integer $dp
	 * @return string formatted size
	 */
	public function parseMemorySize($size, $dp = 2)
	{
		return eHelper::parseMemorySize($size, $dp);
	}


	/**
	 * Removed, see eHelper::getMemoryUsage()
	 * Get the current memory usage of the code
	 * If $separator argument is null, raw data (array) will be returned
	 *
	 * @param null|string $separator
	 * @return string|array memory usage
	 */
	/*
	public function get_memory_usage($separator = '/')
	{
		return eHelper::getMemoryUsage($separator);
	}*/


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
	 * @return mixed
	 */
	public static function ini_set($var, $value)
	{
		if (function_exists('ini_set'))
		{
			return ini_set($var, $value);
		}
		return false;
	}

	/**
	 * Register autoload function (string) or static class method - array('ClassName', 'MethodName')
	 * @param string|array $function
	 * @param bool $prepend
	 * @return bool
	 */
	public static function autoload_register($function, $prepend = false)
	{
		### NEW Register Autoload - do it asap
		if(!function_exists('spl_autoload_register'))
		{
			// PHP >= 5.1.2 required
			die_fatal_error('Fatal exception - spl_autoload_* required.');
		}

		if(!$prepend || false === ($registered = spl_autoload_functions()))
		{
			return spl_autoload_register($function);
		}

		foreach ($registered as $r)
		{
			spl_autoload_unregister($r);
		}

		$result = spl_autoload_register($function);
		foreach ($registered as $r)
		{
			if(!spl_autoload_register($r)) $result = false;
		}
		return $result;
	}

	/**
	 * Former __autoload, generic core autoload logic
	 *
	 * Magic class autoload.
	 * We are raising plugin structure standard here - plugin auto-loading works ONLY if
	 * classes live inside 'includes' folder.
	 * Example: plugin_myplug_admin_ui ->
	 * <code>
	 * <?php
	 * // __autoload() will look in e_PLUGIN.'myplug/includes/admin/ui.php for this class
	 * // e_admin_ui is core handler, so it'll be autoloaded as well
	 * class plugin_myplug_admin_ui extends e_admin_ui
	 * {
	 *
	 * }
	 *
	 * // __autoload() will look in e_PLUGIN.'myplug/shortcodes/my_shortcodes.php for this class
	 * // e_admin_ui is core handler, so it'll be autoloaded as well
	 * class plugin_myplug_my_shortcodes extends e_admin_ui
	 * {
	 *
	 * }
	 * </code>
	 * We use now spl_autoload[_*] for core autoloading (PHP5 > 5.1.2)
	 * TODO - at this time we could create e107 version of spl_autoload_register - e_event->register/trigger('autoload')
	 *
	 * @todo plugname/e_shortcode.php auto-detection (hard, near impossible at this time) - we need 'plugin_' prefix to
	 * distinguish them from the core batches
	 *
	 * @param string $className
	 * @return void
	 */
	public static function autoload($className)
	{
		//Security...
	    if (strpos($className, '/') !== false)
		{
	        return;
	    }

		// Detect namespaced class
		if (strpos($className, '\\') !== false)
		{
			self::autoload_namespaced($className);
			return;
		}

		$tmp = explode('_', $className);

		//echo 'autoloding...'.$className.'<br />';
		switch($tmp[0])
		{
			case 'plugin': // plugin handlers/shortcode batches
				array_shift($tmp); // remove 'plugin'
				$end = array_pop($tmp); // check for 'shortcodes' end phrase

				if (!isset($tmp[0]) || !$tmp[0])
				{
					if($end)
					{
						// plugin root - e.g. plugin_myplug -> plugins/myplug/myplug.php, class plugin_myplug
						$filename = e_PLUGIN.$end.'/'.$end.'.php';
						break;
					}
					return; // In case we get an empty class part
				}

				// Currently only batches inside shortcodes/ folder are auto-detected,
				// read the todo for e_shortcode.php related problems
				if('shortcodes' == $end)
				{
					$filename = e_PLUGIN.$tmp[0].'/shortcodes/batch/'; // plugname/shortcodes/batch/
					unset($tmp[0]);
					$filename .= implode('_', $tmp).'_shortcodes.php'; // my_shortcodes.php
					break;
				}
				if($end)
				{
					$tmp[] = $end; // not a shortcode batch - append the end phrase again
				}

				// Handler check
				$tmp[0] .= '/includes'; //folder 'includes' is not part of the class name
				$filename = e_PLUGIN.implode('/', $tmp).'.php';
				//TODO add debug screen Auto-loaded classes - ['plugin: '.$filename.' - '.$className];
			break;

			default: //core libraries, core shortcode batches
				// core SC batch check
				$end = array_pop($tmp);
				if('shortcodes' == $end)
				{
					$filename = e_CORE.'shortcodes/batch/'.$className.'.php'; // core shortcode batch
					break;
				}

				$filename = self::getHandlerPath($className, true);
				//TODO add debug screen Auto-loaded classes - ['core: '.$filename.' - '.$className];
			break;
		}

		if(!empty($filename) && is_file($filename)) // Test with chatbox_menu
		{
			// autoload doesn't REQUIRE files, because this will break things like call_user_func()
			include($filename);
		}
	}

	/**
	 * Autoloading logic for namespaced classes
	 *
	 * @param $className
	 * @return void
	 */
	private static function autoload_namespaced($className)
	{
		$levels = explode('\\', $className);

		// Guard against classes that are not ours
		if ($levels[0] != 'e107') return;

		$levels[0] = e_HANDLER;
		$classPath = implode('/', $levels).'.php';
		if (is_file($classPath) && is_readable($classPath))
		{
			include($classPath);
		}
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'tp':
				$ret = self::getParser();
			break;

			case 'sql':
				$ret = self::getDb();
			break;

			case 'ecache':
				$ret = self::getCache();
			break;

			case 'arrayStorage':
				$ret = self::getArrayStorage();
			break;

			case 'e_event':
				$ret = self::getEvent();
			break;

			case 'ns':
				$ret = self::getRender();
			break;

			case 'url':
				$ret = self::getUrl();
			break;

			case 'admin_log':
				$ret = self::getAdminLog();
			break;

			case 'override':
				$ret = self::getSingleton('override', e_HANDLER.'override_class.php');
			break;

			case 'notify':
				$ret = self::getNotify();
			break;

			case 'e_online':
				$ret = self::getOnline();
			break;

			case 'eIPHandler':
				$ret = self::getIPHandler();
				break;
				
			case 'user_class':
				$ret = self::getUserClass();
			break;

			default:
				trigger_error('$e107->$'.$name.' not defined', E_USER_WARNING);
				return null;
			break;
		}

		$this->{$name} = $ret;
		return $ret;
	}


	/**
	 *
	 */
	public function destruct() //FIXME $path is not defined anywhere.
	{
		if(null === self::$_instance) return;
		
		$print = defined('E107_DBG_TIMEDETAILS') && E107_DBG_TIMEDETAILS;

		!$print || print('<table class="table table-striped table-condensed"><tr><td colspan="3"><b>Destructing $e107</b></td></tr>');
		$vars = get_object_vars($this);
		foreach ($vars as $name => $value) 
		{
			if(is_object($value)) 
			{
				if(method_exists($value, '__destruct'))
				{
					!$print || print('<tr><td>object [property] using __destruct()</td><td>'.$name.'</td><td>'.get_class($value).'</td></tr>');
					$value->__destruct();
				}
				else !$print || print('<tr><td>object [property]</td><td>'.$name.'</td><td>'.get_class($value).'</td></tr>');
				$this->$name = null;
			}
		}
		foreach (self::$_registry as $path => $reg) 
		{
			if(is_object($reg)) 
			{
				if(method_exists($reg, '__destruct'))
				{
					!$print || print('<tr><td>object [registry] using __destruct()</td><td>'.$path.'</td><td>'.get_class($reg).'</td></tr>');
					$reg->__destruct();
				}
				else !$print || print('<tr><td>object [registry]</td><td>'.$path.'</td><td>'.get_class($reg).'</td></tr>');
				unset(self::$_registry[$path]);
			}


		}

		if($print)
		{
			echo "</table>";
		}

		self::$_registry = null;
		self::$_instance = null;
	}


	/**
	 * Check if there's a core e107 release available
	 * @return array|bool - return array of data or false if no update available.
	 */
	public static function coreUpdateAvailable()
	{

	    // Get site version
	    $e107info= array();

	    if(is_readable(e_ADMIN."ver.php"))
	    {
			include(e_ADMIN."ver.php"); // $e107info['e107_version'];
	    }
	    else
	    {
	        return false;
	    }

        $xml  = self::getXml();
        $file = "https://e107.org/releases.php";
        if(!$xdata = $xml->loadXMLfile($file,true,false))
        {
            return false;
        }

		$curVersion = str_replace(' (git)', '', $e107info['e107_version']);

		if(empty($xdata['core'][0]['@attributes']['version']))
		{
			return false;
		}
		else
		{
			$newVersion = $xdata['core'][0]['@attributes']['version'];
		}


		self::getDebug()->log("New Version:".$newVersion);

		if(version_compare($curVersion,$newVersion) === -1)
		{
			$data = array(
				'name'          => $xdata['core'][0]['@attributes']['name'],
				'url'           => $xdata['core'][0]['@attributes']['url'],
				'date'          => $xdata['core'][0]['@attributes']['date'],
				'version'       => $xdata['core'][0]['@attributes']['version'],
				'infourl'       => $xdata['core'][0]['@attributes']['infourl'],
				'description'   => $xdata['core'][0]['description'],
			);

			return $data;
		}

		return false;

	}



}

e107::autoload_register(array(e107::class, 'autoload'));



/**
 * Interface e_admin_addon_interface @move to separate addons file?
 */
interface e_admin_addon_interface
{

	/**
	* Return a list of values for the currently viewed list page.
	* @param string $event
	* @param string $ids comma separated primary ids to return in the array.
	* @return array with primary id as keys and array of fields key/pair values.
	*/
	public function load($event, $ids);


	/**
	* Extend Admin-ui Parameters
	* @param $ui admin-ui object
	* @return array
	*/
	public function config(e_admin_ui $ui);


	/**
	* Process Posted Data.
	* @param $ui admin-ui object
	* @param int $id
	*/
	public function process(e_admin_ui $ui, $id=0);



}
