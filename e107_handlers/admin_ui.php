<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration User Interface logic
 *
 * $URL$
 * $Id$
 */

 /**
 * @package e107
 * @subpackage e107_handlers
 * @version $Id$
 *
 * Administration User Interface logic
 */


/**
 * @todo core request handler (non-admin), core response
 */
class e_admin_request
{
	/**
	 * Current GET request array
	 * @var array
	 */
	protected $_request_qry;

	/**
	 * Current POST array
	 * @var array
	 */
	protected $_posted_qry;

	/**
	 * Current Mode
	 * @var string
	 */
	protected $_mode = '';

	/**
	 * Default Mode
	 * @var string
	 */
	protected $_default_mode = 'main';

	/**
	 * Key name for mode search
	 * @var string
	 */
	protected $_mode_key = 'mode';

	/**
	 * Current action
	 * @var string
	 */
	protected $_action = '';

	/**
	 * Default Action
	 * @var string
	 */
	protected $_default_action = 'index';

	/**
	 * Key name for action search
	 * @var string
	 */
	protected $_action_key = 'action';

	/**
	 * Current ID
	 * @var integer
	 */
	protected $_id = 0;

	/**
	 * Key name for ID search
	 * @var string
	 */
	protected $_id_key = 'id';

	/**
	 * Constructor
	 *
	 * @param string|array $qry [optional]
	 * @return none
	 */
	public function __construct($request_string = null, $parse = true)
	{
		if(null === $request_string)
		{
			$request_string = str_replace('&amp;', '&', e_QUERY);
		}
		if($parse)
		{
			$this->parseRequest($request_string);
		}
	}

	/**
	 * Parse request data
	 * @param string|array $request_data
	 * @return e_admin_request
	 */
	protected function parseRequest($request_data)
	{
		if(is_string($request_data))
		{
			parse_str($request_data, $request_data);
		}
		$this->_request_qry = (array) $request_data;

		// Set current mode
		if(isset($this->_request_qry[$this->_mode_key]))
		{
			$this->_mode = preg_replace('/[^\w]/', '', $this->_request_qry[$this->_mode_key]);
		}

		// Set current action
		if(isset($this->_request_qry[$this->_action_key]))
		{
			$this->_action = preg_replace('/[^\w]/', '', $this->_request_qry[$this->_action_key]);
		}

		// Set current id
		if(isset($this->_request_qry[$this->_id_key]))
		{
			$this->_id = intval($this->_request_qry[$this->_id_key]);
		}

		$this->_posted_qry = $_POST; //raw?

		return $this;
	}

	/**
	 * Retrieve variable from GET scope
	 * If $key is null, all GET data will be returned
	 *
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getQuery($key = null, $default = null)
	{
		if(null === $key)
		{
			return $this->_request_qry;
		}
		return (isset($this->_request_qry[$key]) ? $this->_request_qry[$key] : $default);
	}

	/**
	 * Set/Unset GET variable
	 * If $key is array, $value is not used.
	 * If $value is null, (string) $key is unset
	 *
	 * @param string|array $key
	 * @param mixed $value [optional]
	 * @return e_admin_request
	 */
	public function setQuery($key, $value = null)
	{
		if(is_array($key))
		{
			foreach ($key as $k=>$v)
			{
				$this->setQuery($k, $v);
			}
			return $this;
		}

		if(null === $value)
		{
			unset($this->_request_qry[$key]);
			return $this;
		}

		$this->_request_qry[$key] = $value;
		return $this;
	}

	/**
	 * Retrieve variable from POST scope
	 * If $key is null, all POST data will be returned
	 *
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getPosted($key = null, $default = null)
	{
		if(null === $key)
		{
			return $this->_posted_qry;
		}
		return (isset($this->_posted_qry[$key]) ? $this->_posted_qry[$key] : $default);
	}

	/**
	 * Set/Unset POST variable
	 * If $key is array, $value is not used.
	 * If $value is null, (string) $key is unset
	 *
	 * @param object $key
	 * @param object $value [optional]
	 * @return e_admin_request
	 */
	public function setPosted($key, $value = null)
	{
		if(is_array($key))
		{
			if(empty($key))
			{
				$this->_posted_qry = array(); //POST reset
				return $this;
			}
			foreach ($key as $k=>$v)
			{
				$this->setPosted($k, $v);
			}
			return $this;
		}

		if(null === $value)
		{
			unset($this->_posted_qry[$key]);
			return $this;
		}

		$tp = e107::getParser();
		$this->_posted_qry[$tp->post_toForm($key)] = $tp->post_toForm($value);
		return $this;
	}

	/**
	 * Get current mode
	 * @return string
	 */
	public function getMode()
	{
		if(!$this->_mode) return $this->getDefaultMode();
		return $this->_mode;
	}

	/**
	 * Get default mode
	 * @return string
	 */
	public function getDefaultMode()
	{
		return $this->_default_mode;
	}

	/**
	 * Get current mode name
	 *
	 * @return string
	 */
	public function getModeName()
	{
		return strtolower(str_replace('-', '_', $this->getMode()));
	}

	/**
	 * Reset current mode
	 * @param string $mode
	 * @return e_admin_request
	 */
	public function setMode($mode)
	{
		$this->_mode = preg_replace('/[^\w]/', '', $mode);
		$this->setQuery($this->_mode_key, $this->_mode);
		return $this;
	}

	/**
	 * Set default mode
	 * @param string $mode
	 * @return e_admin_request
	 */
	public function setDefaultMode($mode)
	{
		if($mode) $this->_default_mode = $mode;
		return $this;
	}

	/**
	 * Set mode key name
	 * @param string $key
	 * @return e_admin_request
	 */
	public function setModeKey($key)
	{
		$this->_mode_key = $key;
		return $this;
	}

	/**
	 * Get current action
	 * @return TBD
	 */
	public function getAction()
	{
		if(!$this->_action) return $this->getDefaultAction();
		return $this->_action;
	}

	/**
	 * Get default action
	 * @return string
	 */
	public function getDefaultAction()
	{
		return $this->_default_action;
	}

	/**
	 * Get current action name
	 * @return string camelized action
	 */
	public function getActionName()
	{
		return $this->camelize($this->getAction());
	}

	/**
	 * Reset current action
	 *
	 * @param string $action
	 * @return e_admin_request
	 */
	public function setAction($action)
	{
		$this->_action = preg_replace('/[^\w]/', '', $action);
		$this->setQuery($this->_action_key, $this->_action);
		return $this;
	}

	/**
	 * Set default action
	 *
	 * @param string $action
	 * @return e_admin_request
	 */
	public function setDefaultAction($action)
	{
		if($action) $this->_default_action = $action;
		return $this;
	}

	/**
	 * Set action key name
	 * @param string $key
	 * @return e_admin_request
	 */
	public function setActionKey($key)
	{
		$this->_action_key = $key;
		return $this;
	}

	/**
	 * Get current ID
	 * @return integer
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Reset current ID
	 * @param string $id
	 * @return e_admin_request
	 */
	public function setId($id)
	{
		$id = intval($id);
		$this->_id = $id;
		$this->setQuery($this->_id_key, $id);
		return $this;
	}

	/**
	 * Set id key name
	 * @param string $key
	 * @return e_admin_request
	 */
	public function setIdKey($key)
	{
		$this->_id_key = $key;
		return $this;
	}

	/**
	 * Build query string from current request array
	 * NOTE: changing url separator to &amp; ($encode==true) (thus URL XHTML compliance) works in PHP 5.1.2+ environment
	 *
	 * @param string|array $merge_with [optional] override request values
	 * @param boolean $encode if true &amp; separator will be used, all values will be http encoded, default true
	 * @param string|array $exclude_from_query numeric array/comma separated list of vars to be excluded from current query, true - don't use current query at all
	 * @return string url encoded query string
	 */
	public function buildQueryString($merge_with = array(), $encode = true, $exclude_from_query = '')
	{
		$ret = $this->getQuery();

		//special case - exclude all current
		if(true === $exclude_from_query)
		{
			$exclude_from_query = array_keys($ret);
		}
		// to array
		if(is_string($exclude_from_query))
		{
			$exclude_from_query = array_map('trim', explode(',', $exclude_from_query));
		}
		if($exclude_from_query)
		{
			foreach ($exclude_from_query as $var)
			{
				unset($ret[$var]);
			}
		}

		if(is_string($merge_with))
		{
			parse_str($merge_with, $merge_with);
		}
		$ret = array_merge($ret, (array) $merge_with);
		$separator = '&';
		if($encode)
		{
			$separator = '&amp;';
			//$ret = array_map('rawurlencode', $ret);
		}

		$ret = http_build_query($ret, 'numeric_', $separator);
		if(!$encode)
		{
			return rawurldecode($ret);
		}
		return $ret;
	}

	/**
	 * Convert string to CamelCase
	 *
	 * @param string $str
	 * @return string
	 */
	public function camelize($str)
	{
		return implode('', array_map('ucfirst', explode('-', str_replace('_', '-', $str))));
	}
}

/**
 * TODO - front response parent, should do all the header.php work
 */
class e_admin_response
{
	/**
	 * Body segments
	 *
	 * @var array
	 */
	protected $_body = array();

	/**
	 * Title segments
	 *
	 * @var unknown_type
	 */
	protected $_title = array();

	/**
	 * e107 meta title
	 *
	 * @var array
	 */
	protected $_e_PAGETITLE = array();

	/**
	 * e107 meta description
	 *
	 * @var array
	 */
	protected $_META_DESCRIPTION = array();

	/**
	 * e107 meta keywords
	 *
	 * @var array
	 */
	protected $_META_KEYWORDS = array();

	/**
	 * Render mods
	 *
	 * @var array
	 */
	protected $_render_mod = array();

	/**
	 * Meta title segment description
	 *
	 * @var string
	 */
	protected $_meta_title_separator = ' - ';

	/**
	 * Title segment separator
	 *
	 * @var string
	 */
	protected $_title_separator = ' &raquo; ';

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		$this->_render_mod['default'] = 'admin_page';
	}

	/**
	 * Set body segments for a namespace
	 *
	 * @param string $content
	 * @param string $namespace segment namesapce
	 * @return e_admin_response
	 */
	function setBody($content, $namespace = 'default')
	{
		$this->_body[$namespace] = $content;
		return $this;
	}

	/**
	 * Append body segment to a namespace
	 *
	 * @param string $content
	 * @param string $namespace segment namesapce
	 * @return e_admin_response
	 */
	function appendBody($content, $namespace = 'default')
	{
		if(!isset($this->_body[$namespace]))
		{
			$this->_body[$namespace] = array();
		}
		$this->_body[$namespace][] = $content;
		return $this;
	}

	/**
	 * Prepend body segment to a namespace
	 *
	 * @param string $content
	 * @param string $namespace segment namespace
	 * @return e_admin_response
	 */
	function prependBody($content, $namespace = 'default')
	{
		if(!isset($this->_body[$namespace]))
		{
			$this->_body[$namespace] = array();
		}
		$this->_body[$namespace] = array_merge(array($content), $this->_body[$namespace]);
		return $this;
	}

	/**
	 * Get body segments from a namespace
	 *
	 * @param string $namespace segment namesapce
	 * @param boolean $reset reset segment namespace
	 * @param string|boolean $glue if false return array, else return string
	 * @return string|array
	 */
	function getBody($namespace = 'default', $reset = false, $glue = '')
	{
		$content = vartrue($this->_body[$namespace], array());
		if($reset)
		{
			$this->_body[$namespace] = array();
		}
		if(is_bool($glue))
		{
			return ($glue ? $content : implode('', $content));
		}
		return implode($glue, $content);
	}

	/**
	 * Set title segments for a namespace
	 *
	 * @param string $title
	 * @param string $namespace
	 * @return e_admin_response
	 */
	function setTitle($title, $namespace = 'default')
	{
		$this->_title[$namespace] = array($title);
		return $this;
	}

	/**
	 * Append title segment to a namespace
	 *
	 * @param string $title
	 * @param string $namespace segment namesapce
	 * @return e_admin_response
	 */
	function appendTitle($title, $namespace = 'default')
	{
		if(empty($title))
		{
			return $this;
		}
		if(!isset($this->_title[$namespace]))
		{
			$this->_title[$namespace] = array();
		}
		$this->_title[$namespace][] = $title;
		return $this;
	}

	/**
	 * Prepend title segment to a namespace
	 *
	 * @param string $title
	 * @param string $namespace segment namespace
	 * @return e_admin_response
	 */
	function prependTitle($title, $namespace = 'default')
	{
		if(empty($title))
		{
			return $this;
		}
		if(!isset($this->_title[$namespace]))
		{
			$this->_title[$namespace] = array();
		}
		$this->_title[$namespace] = array_merge(array($title), $this->_title[$namespace]);
		return $this;
	}

	/**
	 * Get title segments from namespace
	 *
	 * @param string $namespace
	 * @param boolean $reset
	 * @param boolean|string $glue
	 * @return unknown
	 */
	function getTitle($namespace = 'default', $reset = false, $glue = ' - ')
	{
		$content = array();
		if(isset($this->_title[$namespace]) && is_array($this->_title[$namespace]))
		{
			$content = $this->_title[$namespace];
		}
		if($reset)
		{
			unset($this->_title[$namespace]);
		}
		if(is_bool($glue) || empty($glue))
		{
			return ($glue ? $content : implode($this->_title_separator, $content));
		}

		return implode($glue, $content);
	}

	/**
	 * Set render mode for a namespace
	 *
	 * @param string $render_mod
	 * @param string $namespace
	 * @return e_admin_response
	 */
	function setRenderMod($render_mod, $namespace = 'default')
	{
		$this->_render_mod[$namespace] = $render_mod;
		return $this;
	}

	/**
	 * Set render mode for namespace
	 *
	 * @param string $namespace
	 * @return string
	 */
	function getRenderMod($namespace = 'default')
	{
		return varset($this->_render_mod[$namespace], null);
	}

	/**
	 * Add meta title, description and keywords segments
	 *
	 * @param string $meta property name
	 * @param string $content meta content
	 * @return e_admin_response
	 */
	function addMetaData($meta, $content)
	{
		$tp = e107::getParser();
		$meta = '_' . $meta;
		if(isset($this->{$meta}) && !empty($content))
		{
			$this->{$meta}[] = strip_tags($content);
		}
		return $this;
	}

	/**
	 * Add meta title segment
	 *
	 * @param string $title
	 * @return e_admin_response
	 */
	function addMetaTitle($title)
	{
		$this->addMetaData('e_PAGETITLE', $title);
		return $this;
	}

	/**
	 * Add meta description segment
	 *
	 * @param string $description
	 * @return e_admin_response
	 */
	function addMetaDescription($description)
	{
		$this->addMetaData('META_DESCRIPTION', $description);
		return $this;
	}

	/**
	 * Add meta keywords segment
	 *
	 * @param string $keyword
	 * @return e_admin_response
	 */
	function addMetaKeywords($keyword)
	{
		$this->addMetaData('META_KEYWORDS', $keyword);
		return $this;
	}

	/**
	 * Send e107 meta-data
	 *
	 * @return e_admin_response
	 */
	function sendMeta()
	{
		//HEADERF already included or meta content already sent
		if(e_AJAX_REQUEST || defined('HEADER_INIT') || defined('e_PAGETITLE'))
			return $this;

		if(!defined('e_PAGETITLE') && !empty($this->_e_PAGETITLE))
		{
			define('e_PAGETITLE', implode($this->_meta_title_separator, $this->_e_PAGETITLE));
		}

		if(!defined('META_DESCRIPTION') && !empty($this->_META_DESCRIPTION))
		{
			define('META_DESCRIPTION', implode(' ', $this->_META_DESCRIPTION));
		}
		if(!defined('META_KEYWORDS') && !empty($this->_META_KEYWORDS))
		{
			define('META_KEYWORDS', implode(', ', $this->_META_KEYWORDS));
		}
		return $this;
	}

	/**
	 * Add content segment to the header namespace
	 *
	 * @param string $content
	 * @return e_admin_response
	 */
	function addHeaderContent($content)
	{
		$this->appendBody($content, 'header_content');
		return $this;
	}

	/**
	 * Get page header namespace content segments
	 *
	 * @param boolean $reset
	 * @param boolean $glue
	 * @return string
	 */
	function getHeaderContent($reset = true, $glue = "\n\n")
	{
		return $this->getBody('header_content', $reset, $glue);
	}

	/**
	 * Switch to iframe mod
	 * FIXME - implement e_IFRAME to frontend - header_default.php
	 *
	 * @return e_admin_response
	 */
	function setIframeMod()
	{
		global $HEADER, $FOOTER, $CUSTOMHEADER, $CUSTOMFOOTER;
		$HEADER = $FOOTER = '';
		$CUSTOMHEADER = $CUSTOMFOOTER = array();

		// New
		if(!defined('e_IFRAME'))
		{
			define('e_IFRAME', true);
		}
		return $this;
	}

	/**
	 * Send Response Output
	 *
	 * @param string $name segment
	 * @param array $options valid keys are: messages|render|meta|return|raw|ajax
	 * @return mixed
	 */
	function send($name = 'default', $options = array())
	{
		if(is_string($options))
		{
			parse_str($options, $options);
		}

		// Merge with all available default options
		$options = array_merge(array(
			'messages' => true,
			'render' => true,
			'meta' => false,
			'return' => false,
			'raw' => false,
			'ajax' => false
		), $options);

		$content = $this->getBody($name, true);
		$title = $this->getTitle($name, true);
		$return = $options['return'];

		if($options['ajax'] || e_AJAX_REQUEST)
		{
			$type = $options['ajax'] && is_string($options['ajax']) ? $options['ajax'] : '';
			$this->getJsHelper()->sendResponse($type);
		}

		if($options['messages'])
		{
			$content = e107::getMessage()->render().$content;
		}

		if($options['meta'])
		{
			$this->sendMeta();
		}

		// raw output expected - force return array
		if($options['raw'])
		{
			return array($title, $content, $this->getRenderMod($name));
		}

		//render disabled by the controller
		if(!$this->getRenderMod($name))
		{
			$options['render'] = false;
		}

		if($options['render'])
		{
			return e107::getRender()->tablerender($title, $content, $this->getRenderMod($name), $return);
		}

		if($return)
		{
			return $content;
		}

		print($content);
		return '';
	}

	/**
	 * Get JS Helper instance
	 *
	 * @return e_jshelper
	 */
	public function getJsHelper()
	{
		return e107::getSingleton('e_jshelper', true, 'admin_response');
	}
}

/**
 * TODO - request related code should be moved to core
 * request handler
 */
class e_admin_dispatcher
{
	/**
	 * @var e_admin_request
	 */
	protected $_request = null;

	/**
	 * @var e_admin_response
	 */
	protected $_response = null;

	/**
	 * @var e_admin_controller
	 */
	protected $_current_controller = null;

	/**
	 * Required (set by child class).
	 * Controller map array in format
	 * 'MODE' => array('controller' =>'CONTROLLER_CLASS_NAME'[, 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => extend of 'comments_admin_form_ui', 'uipath' => 'path/to/ui/']);
	 *
	 * @var array
	 */
	protected $modes;

	/**
	 * @var string
	 */
	protected $defaultMode = '';

	/**
	 * @var string
	 */
	protected $defaultAction = '';

	/**
	 * Optional - map 'mode/action' pair to 'modeAlias/actionAlias'
	 * @var string
	 */
	protected $adminMenuAliases = array();

	/**
	 * Optional (set by child class).
	 * Required for admin menu render
	 * Format: 'mode/action' => array('caption' => 'Link title'[, 'perm' => '0', 'url' => '{e_PLUGIN}plugname/admin_config.php'], ...);
	 * All valid key-value pair (see e_admin_menu function) are accepted.
	 * @var array
	 */
	protected $adminMenu = array();

	/**
	 * Optional (set by child class).
	 * @var string
	 */
	protected $menuTitle = 'Menu';

	/**
	 * @var string
	 */
	protected $pluginTitle = '';

	/**
	 * Constructor
	 *
	 * @param string|array|e_admin_request $request [optional]
	 * @param e_admin_response $response
	 */
	public function __construct($request = null, $response = null, $auto_observe = true)
	{
		if(null === $request || !is_object($request))
		{
			$request = new e_admin_request($request);
		}

		if(null === $response)
		{
			$response = new e_admin_response();
		}

		$this->setRequest($request)->setResponse($response)->init();

		if(!$this->defaultMode || !$this->defaultAction)
		{
			$this->setDefaults();
		}

		$request->setDefaultMode($this->defaultMode)->setDefaultAction($this->defaultAction);

		// register itself
		e107::setRegistry('admin/ui/dispatcher', $this);

		if($auto_observe)
		{
			$this->runObservers(true);
		}

	}

	/**
	 * User defined constructor - called before _initController() method
	 * @return e_admin_dispatcher
	 */
	public function init()
	{
	}

	/**
	 * Retrieve missing default action/mode
	 * @return e_admin_dispatcher
	 */
	public function setDefaults()
	{
		// try Admin menu first
		if($this->adminMenu)
		{
			reset($this->adminMenu);
			list($mode, $action) = explode('/', key($this->adminMenu), 3);
		}
		else
		{
			reset($this->modes);
			$mode = key($this->modes);
			$action = $this->modes[$mode]['index'];
		}

		if(!$this->defaultMode) $this->defaultMode = $mode;
		if(!$this->defaultAction) $this->defaultAction = $action;

		return $this;
	}

	/**
	 * Get request object
	 * @return e_admin_request
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * Set request object
	 * @param e_admin_request $request
	 * @return e_admin_dispatcher
	 */
	public function setRequest($request)
	{
		$this->_request = $request;
		return $this;
	}

	/**
	 * Get response object
	 * @return e_admin_response
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Set response object
	 * @param e_admin_response $response
	 * @return e_admin_dispatcher
	 */
	public function setResponse($response)
	{
		$this->_response = $response;
		return $this;
	}

	/**
	 * Dispatch & render all
	 *
	 * @param boolean $return if true, array(title, body, render_mod) will be returned
	 * @return string|array current admin page body
	 */
	public function run($return = false)
	{
		return $this->runObserver()->renderPage($return);
	}

	/**
	 * Run observers/headers only, should be called before header.php call
	 *
	 * @return e_admin_dispatcher
	 */
	public function runObservers($run_header = true)
	{
		//search for $actionName.'Observer' method. Additional $actionName.$triggerName.'Trigger' methods will be called as well
		$this->getController()->dispatchObserver();

		//search for $actionName.'Header' method, js manager should be used inside for sending JS to the page,
		// meta information should be created there as well
		if($run_header)
		{
			$this->getController()->dispatchHeader();

		}
		return $this;
	}

	/**
	 * Run page action.
	 * If return type is array, it should contain allowed response options (see e_admin_response::send())
	 * Available return type string values:
	 * - render_return: return rendered content ( see e107::getRender()->tablerender()), add system messages, send meta information
	 * - render: outputs rendered content ( see e107::getRender()->tablerender()), add system messages
	 * - response: return response object
	 * - raw: return array(title, content, render mode)
	 * - ajax: force ajax output (and exit)
	 *
	 * @param string|array $return_type expected string values: render|render_out|response|raw|ajax[_text|_json|_xml]
	 * @return mixed
	 */
	public function runPage($return_type = 'render')
	{
		$response = $this->getController()->dispatchPage();
		if(is_array($return_type))
		{
			return $response->send('default', $return_type);
		}
		switch($return_type)
		{
			case 'render_return':
				$options = array(
					'messages' => true,
					'render' => true,
					'meta' => true,
					'return' => true,
					'raw' => false
				);
			break;

			case 'raw':
				$options = array(
					'messages' => false,
					'render' => false,
					'meta' => false,
					'return' => true,
					'raw' => true
				);
			break;

			case 'ajax':
			case 'ajax_text':
			case 'ajax_xml';
			case 'ajax_json';
				$options = array(
					'messages' => false,
					'render' => false,
					'meta' => false,
					'return' => false,
					'raw' => false,
					'ajax' => str_replace(array('ajax_', 'ajax'), array('', 'text'), $return_type)
				);
			break;

			case 'response':
				return $response;
			break;

			case 'render':
			default:
				$options = array(
					'messages' => true,
					'render' => true,
					'meta' => false,
					'return' => false,
					'raw' => false
				);
			break;
		}
		return $response->send('default', $options);
	}

	/**
	 * Proxy method
	 *
	 * @return string
	 */
	public function getHeader()
	{
		return $this->getController()->getHeader();
	}

	/**
	 * Get current controller object
	 * @return e_admin_controller
	 */
	public function getController()
	{
		if(null === $this->_current_controller)
		{
			$this->_initController();
		}
		return $this->_current_controller;
	}

	/**
	 * Try to init Controller from request using current controller map
	 *
	 * @return e_admin_dispatcher
	 */
	protected function _initController()
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		if(isset($this->modes[$request->getModeName()]) && isset($this->modes[$request->getModeName()]['controller']))
		{
			$class_name = $this->modes[$request->getModeName()]['controller'];
			$class_path = vartrue($this->modes[$request->getModeName()]['path']);

			if($class_path)
			{
				require_once(e107::getParser()->replaceConstants($class_path));
			}
			if($class_name && class_exists($class_name))//NOTE: autoload in the play
			{
				$this->_current_controller = new  $class_name($request, $response);
				//give access to current request object, user defined init
				$this->_current_controller->setRequest($this->getRequest())->init();
			}
			else
			{
				//TODO - get default controller (core or user defined), set Action for
				//'Controller not found' page, add message(?), break
				// get default controller
				$this->_current_controller = $this->getDefaultController();
				// add messages
				e107::getMessage()->add('Can\'t find class '.($class_name ? $class_name : 'n/a'), E_MESSAGE_ERROR)
					->add('Requested: '.e_SELF.'?'.$request->buildQueryString(), E_MESSAGE_DEBUG);
				//
				$request->setMode($this->getDefaultControllerName())->setAction('e404');
				$this->_current_controller->setRequest($request)->init();
			}

			if(vartrue($this->modes[$request->getModeName()]['ui']))
			{
				$class_name = $this->modes[$request->getModeName()]['ui'];
				$class_path = vartrue($this->modes[$request->getModeName()]['uipath']);
				if($class_path)
				{
					require_once(e107::getParser()->replaceConstants($class_path));
				}
				if(class_exists($class_name))//NOTE: autoload in the play
				{
					$this->_current_controller->setParam('ui', new $class_name($this->_current_controller));
				}
			}
			$this->_current_controller->setParam('modes', $this->modes);

		}

		return $this;
	}

	/**
	 * Default controller object - needed if controller not found
	 * @return e_admin_controller
	 */
	public function getDefaultController()
	{
		$class_name = $this->getDefaultControllerName();
		return new $class_name($this->getRequest(), $this->getResponse());
	}

	/**
	 *  Default controller name - needed if controller not found
	 * @return string name of controller
	 */
	public function getDefaultControllerName()
	{
		return 'e_admin_controller';
	}

	/**
	 * Generic Admin Menu Generator
	 * @return string
	 */
	function renderMenu()
	{
		$tp = e107::getParser();
		$var = array();
		$selected = false;
		foreach($this->adminMenu as $key => $val)
		{
			$tmp = explode('/', trim($key, '/'), 3);
			
			// custom 'selected' check
			if(isset($val['selected']) && $val['selected']) $selected = $val['selected'] === true ? $key : $val['selected'];
			
			foreach ($val as $k=>$v)
			{
				switch($k)
				{
					case 'caption':
						$k2 = 'text';
						$v = defset($v, $v);
					break;

					case 'url':
						$k2 = 'link';
						$v = $tp->replaceConstants($v, 'abs').'?mode='.$tmp[0].'&amp;action='.$tmp[1];
					break;
					
					case 'uri':
						$k2 = 'link';
						$v = $tp->replaceConstants($v, 'abs');
					break;

					default:
						$k2 = $k;
					break;
				}

				if($val['perm']!= null) // check perms
				{
					if(getperms($val['perm']))
					{
						$var[$key][$k2] = $v;
					}
				}
				else
				{
					$var[$key][$k2] = $v;
				}

			}
			// TODO slide down menu options?
			if(!vartrue($var[$key]['link']))
			{
				$var[$key]['link'] = e_SELF.'?mode='.$tmp[0].'&amp;action='.$tmp[1]; // FIXME - URL based on $modes, remove url key
			}

			/*$var[$key]['text'] = $val['caption'];
			$var[$key]['link'] = (vartrue($val['url']) ? $tp->replaceConstants($val['url'], 'abs') : e_SELF).'?mode='.$tmp[0].'&action='.$tmp[1];
			$var[$key]['perm'] = $val['perm'];	*/
		}

		$request = $this->getRequest();
		if(!$selected) $selected = $request->getMode().'/'.$request->getAction();
		$selected = vartrue($this->adminMenuAliases[$selected], $selected);
		return e_admin_menu($this->menuTitle, $selected, $var);
	}
}

class e_admin_controller
{
	/**
	 * @var e_admin_request
	 */
	protected $_request;

	/**
	 * @var e_admin_response
	 */
	protected $_response;

	/**
	 * @var array User defined parameters
	 */
	protected $_params = array();

	/**
	 * @var string default action name
	 */
	protected $_default_action = 'index';

	/**
	 * Constructor
	 * @param e_admin_request $request [optional]
	 */
	public function __construct($request, $response, $params = array())
	{
		$this->_params = array_merge(array('enable_triggers' => false), $params);
		$this->setRequest($request)
			->setResponse($response)
			->setParams($params);
	}

	/**
	 * User defined init
	 * Called before dispatch routine
	 */
	public function init()
	{
	}

	/**
	 * Get controller parameter
	 * Currently used core parameters:
	 * - enable_triggers: don't use it direct, see {@link setTriggersEnabled()}
	 * - modes - see dispatcher::$modes
	 * - ajax_response - text|xml|json - default is 'text'; this should be set by the action method
	 * - TODO - more parameters/add missing to this list
	 *
	 * @param string $key [optional] if null - get whole array
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getParam($key = null, $default = null)
	{
		if(null === $key)
		{
			return $this->_params;
		}
		return (isset($this->_params[$key]) ? $this->_params[$key] : $default);
	}

	/**
	 * Set parameter
	 * @param string $key
	 * @param mixed $value
	 * @return e_admin_controller
	 */
	public function setParam($key, $value)
	{
		if(null === $value)
		{
			unset($this->_params[$key]);
			return $this;
		}
		$this->_params[$key] = $value;
		return $this;
	}

	/**
	 * Merge passed parameter array with current parameters
	 * @param array $params
	 * @return e_admin_controller
	 */
	public function setParams($params)
	{
		$this->_params = array_merge($this->_params, $params);
		return $this;
	}

	/**
	 * Reset parameter array
	 * @param array $params
	 * @return e_admin_controller
	 */
	public function resetParams($params)
	{
		$this->_params = $params;
		return $this;
	}

	/**
	 * Get current request object
	 * @return e_admin_request
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * Set current request object
	 * @param e_admin_request $request
	 * @return e_admin_controller
	 */
	public function setRequest($request)
	{
		$this->_request = $request;
		return $this;
	}

	/**
	 * Get current response object
	 * @return e_admin_response
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Set current response object
	 * @param e_admin_response $response
	 * @return e_admin_controller
	 */
	public function setResponse($response)
	{
		$this->_response = $response;
		return $this;
	}

	/**
	 * Request proxy method
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getQuery($key = null, $default = null)
	{
		return $this->getRequest()->getQuery($key, $default);
	}

	/**
	 * Request proxy method
	 * @param string|array $key
	 * @param mixed $value [optional]
	 * @return e_admin_controller
	 */
	public function setQuery($key, $value = null)
	{
		$this->getRequest()->setQuery($key, $value);
		return $this;
	}

	/**
	 * Request proxy method
	 * @param string $key [optional]
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function getPosted($key = null, $default = null)
	{
		return $this->getRequest()->getPosted($key, $default);
	}

	/**
	 * Request proxy method
	 * @param string $key
	 * @param mixed $value [optional]
	 * @return e_admin_controller
	 */
	public function setPosted($key, $value = null)
	{
		$this->getRequest()->setPosted($key, $value);
		return $this;
	}

	/**
	 * Add page title, response proxy method
	 *
	 * @param string $title
	 * @param boolean $meta add to meta as well
	 * @return e_admin_controller
	 */
	public function addTitle($title, $meta = true)
	{
		$this->getResponse()->appendTitle($title);
		if($meta) $this->addMetaTitle($title);
		return $this;
	}

	/**
	 * Add page meta title, response proxy method.
	 * Should be called before header.php
	 *
	 * @param string $title
	 * @return e_admin_controller
	 */
	public function addMetaTitle($title)
	{
		$this->getResponse()->addMetaTitle($title);
		return $this;
	}

	/**
	 * Add header content, response proxy method
	 * Should be called before header.php
	 *
	 * @param string $content
	 * @return e_admin_controller
	 */
	public function addHeader($content)
	{
		$this->getResponse()->addHeaderContent($content);
		return $this;
	}

	/**
	 * Get header content, response proxy method
	 *
	 * @return string
	 */
	public function getHeader()
	{
		return $this->getResponse()->getHeaderContent();
	}

	/**
	 * Get current mode, response proxy method
	 * @return string
	 */
	public function getMode()
	{
		return $this->getRequest()->getMode();
	}

	/**
	 * Get current actin, response proxy method
	 * @return string
	 */
	public function getAction()
	{
		return $this->getRequest()->getAction();
	}

	/**
	 * Get current ID, response proxy method
	 * @return string
	 */
	public function getId()
	{
		return $this->getRequest()->getId();
	}

	/**
	 * Get response owned JS Helper instance, response proxy method
	 *
	 * @return e_jshelper
	 */
	public function getJsHelper()
	{
		return $this->getResponse()->getJsHelper();
	}

	protected function _preDispatch($action = '')
	{
		if(!$action) $action = $this->getRequest()->getActionName();
		$method = $this->toMethodName($action, 'page');
		if(!method_exists($this, $method))
		{
			$this->getRequest()->setAction($this->getDefaultAction());
		}

		// switch to 404 if needed
		$method = $this->toMethodName($this->getRequest()->getActionName(), 'page');
		if(!method_exists($this, $method))
		{
			$this->getRequest()->setAction('e404');
			e107::getMessage()->add(sprintf(LAN_UI_404_METHOD_ERROR, $method), E_MESSAGE_ERROR);
		}
	}

	/**
	 * Dispatch observer, check for triggers
	 *
	 * @param string $action [optional]
	 * @return e_admin_controller
	 */
	public function dispatchObserver($action = null)
	{
		$request = $this->getRequest();
		if(null === $request)
		{
			$request = new e_admin_request();
			$this->setRequest($request);
		}

		$this->_preDispatch($action);
		if(null === $action)
		{
			$action = $request->getActionName();
		}

		// check for observer
		$actionObserverName = $this->toMethodName($action, 'observer', e_AJAX_REQUEST);
		if(method_exists($this, $actionObserverName))
		{
			$this->$actionObserverName();
		}

		// check for triggers, not available in Ajax mode
		if(!e_AJAX_REQUEST && $this->triggersEnabled())
		{
			$posted = $request->getPosted();
			foreach ($posted as $key => $value)
			{
				if(strpos($key, 'etrigger_') === 0)
				{
					$actionTriggerName = $this->toMethodName($action.$request->camelize(substr($key, 9)), 'trigger', false);
					if(method_exists($this, $actionTriggerName))
					{
						$this->$actionTriggerName($value);
					}
					//Check if triggers are still enabled
					if(!$this->triggersEnabled())
					{
						break;
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Dispatch header, not allowed in Ajax mode
	 * @param string $action [optional]
	 * @return e_admin_controller
	 */
	public function dispatchHeader($action = null)
	{
		// not available in Ajax mode
		if(e_AJAX_REQUEST)
		{
			return $this;
		}

		$request = $this->getRequest();
		if(null === $request)
		{
			$request = new e_admin_request();
			$this->setRequest($request);
		}

		$this->_preDispatch($action);
		if(null === $action)
		{
			$action = $request->getActionName();
		}

		// check for observer
		$actionHeaderName = $this->toMethodName($action, 'header', false);
		if(method_exists($this, $actionHeaderName))
		{
			$this->$actionHeaderName();
		}

		//send meta data
		$this->getResponse()->sendMeta();
		return $this;
	}

	/**
	 * Dispatch controller action
	 *
	 * @param string $action [optional]
	 * @return e_admin_response
	 */
	public function dispatchPage($action = null)
	{
		$request = $this->getRequest();
		if(null === $request)
		{
			$request = new e_admin_request();
			$this->setRequest($request);
		}
		$response = $this->getResponse();
		$this->_preDispatch($action);

		if(null === $action)
		{
			$action = $request->getActionName();
		}
		
		// check for observer
		$actionName = $this->toMethodName($action, 'page');
		$ret = '';
		if(!method_exists($this, $actionName)) // pre dispatch already switched to default action/not found page if needed
		{
			e107::getMessage()->add('Action '.$actionName.' no found!', E_MESSAGE_ERROR);
			return $response;
		}
		ob_start(); //catch any output
		$ret = $this->{$actionName}();


		//Ajax XML/JSON communication
		if(e_AJAX_REQUEST && is_array($ret))
		{
			$response_type = $this->getParam('ajax_response', 'xml');
			ob_clean();
			$js_helper = $response->getJsHelper();
			foreach ($ret as $act => $data)
			{
				$js_helper->addResponse($data, $act);
			}
			$js_helper->sendResponse($response_type);
		}

		$ret .= ob_get_clean();

		// Ajax text response
		if(e_AJAX_REQUEST)
		{
			$response_type = 'text';
			$response->getJsHelper()->addResponse($ret)->sendResponse($response_type);
		}
		else
		{
			$response->appendBody($ret);
		}

		return $response;
	}

	public function E404Observer()
	{
		$this->getResponse()->setTitle(LAN_UI_404_TITLE_ERROR);
	}

	public function E404Page()
	{
		return '<div class="center">'.LAN_UI_404_BODY_ERROR.'</div>'; // TODO - lan
	}


	public function E404AjaxPage()
	{
		exit;
	}

	/**
	 * Generic redirect handler, it handles almost everything we would need.
	 * Additionally, it moves currently registered system messages to SESSION message stack
	 * In almost every case {@link redirectAction()} and {@link redirectMode()} are better solution
	 *
	 * @param string $action defaults to current action
	 * @param string $mode defaults to current mode
	 * @param string|array $exclude_query comma delimited variable names to be excluded from current query OR TRUE to exclude everything
	 * @param string|array $merge_query query string (&amp; delimiter) or associative array to be merged with current query
	 * @param string $path default to e_SELF
	 * @return void
	 */
	public function redirect($action = null, $mode = null, $exclude_query = '', $merge_query = array(), $path = null)
	{
		$request = $this->getRequest();

		if($mode) $request->setMode($mode);
		if($action) $request->setAction($action);
		if(!$path) $path = e_SELF;
		
		$url = $path.'?'.$request->buildQueryString($merge_query, false, $exclude_query);
		// Transfer all messages to session
		e107::getMessage()->moveToSession();
		// write session data
		session_write_close();
		// do redirect
		header('Location: '.$url);
		exit;
	}

	/**
	 * Convenient redirect() proxy method, make life easier when redirecting between actions
	 * in same mode.
	 *
	 * @param string $action [optional]
	 * @param string|array $exclude_query [optional]
	 * @param string|array $merge_query [optional]
	 * @return none
	 */
	public function redirectAction($action = null, $exclude_query = '', $merge_query = array())
	{
		$this->redirect($action, null, $exclude_query, $merge_query);
	}

	/**
	 * Convenient redirect to another mode (doesn't use current Query state)
	 * If path is empty, it'll be auto-detected from modes (dispatcher) array
	 *
	 * @param string $mode
	 * @param string $action
	 * @param string|array $query [optional]
	 * @param string $path
	 * @return void
	 */
	public function redirectMode($mode, $action, $query = array(), $path = null)
	{
		if(!$path && $this->getParam('modes'))
		{
			$modes = $this->getParam('modes');
			if(vartue($modes[$mode]) && vartrue($modes[$mode]['url']))
			{
				$path = e107::getParser()->replaceConstants($modes[$mode]['url'], 'abs');
			}
		}
		$this->redirect($action, $mode, true, $query, $path);
	}

	/**
	 * Convert action name to method name
	 *
	 * @param string $action_name formatted (e.g. request method getActionName()) action name
	 * @param string $type page|observer|header|trigger
	 * @param boolean $ajax force with true/false, if null will be auto-resolved
	 * @return string
	 */
	public function toMethodName($action_name, $type= 'page', $ajax = null)
	{
		if(null === $ajax) $ajax = e_AJAX_REQUEST; //auto-resolving
		return $action_name.($ajax ? 'Ajax' : '').ucfirst(strtolower($type));
	}

	/**
	 * Check if there is a trigger available in the posted data
	 * @param array $exclude
	 * @return boolean
	 */
	public function hasTrigger($exclude = array())
	{
		$posted = array_keys($this->getPosted());
		foreach ($posted as $key)
		{
			if(!in_array($key, $exclude) && strpos($key, 'etrigger_') === 0)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Get default action
	 * @return string action
	 */
	public function getDefaultAction()
	{
		return $this->_default_action;
	}

	/**
	 * Set default action
	 * @param string $action_name
	 * @return e_admin_controller
	 */
	public function setDefaultAction($action_name)
	{
		$this->_default_action = $action_name;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function triggersEnabled()
	{
		return $this->getParam('enable_triggers');
	}

	/**
	 * @param boolean $flag
	 * @return e_admin_controller
	 */
	public function setTriggersEnabled($flag)
	{
		$this->setParam('enable_triggers', $flag);
		return $this;
	}
}

//FIXME - move everything from e_admin_ui except model auto-create related code
class e_admin_controller_ui extends e_admin_controller
{
	/**
	 * @var array UI field data
	 */
	protected $fields = array();

	/**
	 * @var array default fields activated on List view
	 */
	protected $fieldpref = array();

	/**
	 * @var array Plugin Preference description array
	 */
	protected $prefs = array();

	/**
	 * Data required for _modifyListQry() to automate
	 * db query building
	 * @var array
	 */
	protected $tableJoin = array();
	
	/**
	 * Array of table names and their aliases. (detected from listQry)
	 * db query building
	 * @var array
	 */
	protected $joinAlias = array();

	/**
	 * Main model table alias
	 * @var string
	 */
	protected $tableAlias;

	/**
	 * @var string plugin name
	 */
	protected $pluginName;

	/**
	 * @var string
	 */
	protected $defaultOrderField = null;

	/**
	 * @var string
	 */
	protected $defaultOrder = 'asc';

	/**
	 * @var string SQL order, false to disable order, null is default order
	 */
	protected $listOrder = null;
	
	/**
	 * Structure same as TreeModel parameters used for building the load() SQL
	 * @var additional SQL to be applied when auto-building the list query
	 */
	protected $listQrySql = array();

	/**
	 * @var boolean
	 */
	protected $batchDelete = true;

	/**
	 * Could be LAN constant (mulit-language support)
	 *
	 * @var string plugin name
	 */
	protected $pluginTitle;

	/**
	 * Default (db) limit value
	 * @var integer
	 */
	protected $perPage = 20;

	/**
	 * @var e_admin_model
	 */
	protected $_model = null;

	/**
	 * @var e_admin_tree_model
	 */
	protected $_tree_model = null;

	/**
	 * @var e_admin_tree_model
	 */
	protected $_ui = null;

	/**
	 * @var e_plugin_pref|e_core_pref
	 */
	protected $_pref = null;

	public function getBatchDelete()
	{
		return $this->batchDelete;
	}

	/**
	 * @return string
	 */
	public function getPluginName()
	{
		return $this->pluginName;
	}

	/**
	 * @return string
	 */
	public function getPluginTitle()
	{
		return deftrue($this->pluginTitle, $this->pluginTitle);
	}

	/**
	 * Get all field data
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 *
	 * @param string $field
	 * @param string $key attribute name
	 * @param mixed $default default value if not set, default is null
	 * @return mixed
	 */
	public function getFieldAttr($field, $key = null, $default = null)
	{
		if(isset($this->fields[$field]))
		{
			if(null !== $key)
			{
				return isset($this->fields[$field][$key]) ? $this->fields[$field][$key] : $default;
			}
			return $this->fields[$field];
		}
		return $default;
	}

	/**
	 *
	 * @param string $field
	 * @param string $key attribute name
	 * @param mixed $value default value if not set, default is null
	 * @return e_admin_controller_ui
	 */
	public function setFieldAttr($field, $key = null, $value = null)
	{
		// add field array
		if(is_array($field))
		{
			foreach ($field as $f => $atts)
			{
				$this->setFieldAttr($f, $atts);
			}
			return $this;
		}
		// remove a field
		if(null === $key)
		{
			unset($this->fields[$field]);
			return $this;
		}
		// add to attribute array of a field
		if(is_array($key))
		{
			foreach ($key as $k => $att)
			{
				$this->setFieldAttr($field, $k, $att);
			}
			return $this;
		}
		// remove attribute from field attribute set
		if(null === $value && $key != 'type')
		{
			unset($this->fields[$field][$key]);
			return $this;
		}
		// set attribute value
		$this->fields[$field][$key] = $value;
		return $this;
	}

	/**
	 * Get fields stored as user preferences
	 * @return array
	 */
	public function getFieldPref()
	{
		return $this->fieldpref;
	}

	/**
	 * Get Config data array
	 * @return array
	 */
	public function getPrefs()
	{
		return $this->prefs;
	}

	public function getPerPage()
	{
		return $this->perPage;
	}

	public function getPrimaryName()
	{
		return $this->getModel()->getFieldIdName();
	}


	public function getDefaultOrderField()
	{
		return ($this->defaultOrder ? $this->defaultOrderField : $this->getPrimaryName());
	}

	public function getDefaultOrder()
	{
		return ($this->defaultOrder ? $this->defaultOrder : 'asc');
	}

	/**
	 * Get column preference array
	 * @return array
	 */
	public function getUserPref()
	{
		//global $user_pref;
		// return vartrue($user_pref['admin_cols_'.$this->getTableName()], array());
		return e107::getUser()->getPref('admin_cols_'.$this->getTableName(), array());
	}

	/**
	 * Set column preference array
	 * @return boolean success
	 */
	public function setUserPref($new)
	{
		//global $user_pref;
		//e107::getUser()->getConfig()->setData($new);
		//$user_pref['admin_cols_'.$this->getTableName()] = $new;
		//$this->fieldpref = $new;
		//return save_prefs('user');
		$this->fieldpref = $new;
		return e107::getUser()->getConfig()
			->set('admin_cols_'.$this->getTableName(), $new)
			->save();
	}

	/**
	 * Get current model
	 *
	 * @return e_admin_model
	 */
	public function getModel()
	{
		if(null === $this->_model)
		{
			$this->_setModel();
		}

		return $this->_model;
	}

	/**
	 * Set controller model
	 * @param e_admin_model $model
	 * @return e_admin_controller_ui
	 */
	public function setModel($model)
	{
		$this->_model = $model;
		return $this;
	}

	/**
	 * Get model validation array
	 * @return array
	 */
	public function getValidationRules()
	{
		return $this->getModel()->getValidationRules();
	}

	/**
	 * Get model data field array
	 * @return array
	 */
	public function getDataFields()
	{
		return $this->getModel()->getDataFields();
	}

	/**
	 * Get model table or alias
	 * @param boolean $alias get table alias on true, default false
	 * @param object $prefix add e107 special '#' prefix, default false
	 * @return string
	 */
	public function getTableName($alias = false, $prefix = false)
	{
		if($alias) return ($this->tableAlias ? $this->tableAlias : '');
		return ($prefix ? '#' : '').$this->getModel()->getModelTable();
	}

	public function getIfTableAlias($prefix = false, $quote = false)
	{
		$alias = $this->getTableName(true);
		if($alias)
		{
			return $alias;
		}
		return ( !$quote ? $this->getTableName(false, $prefix) : '`'.$this->getTableName(false, $prefix).'`' );
	}

	/**
	 * Get join table data
	 * @param string $table if null all data will be returned
	 * @param string $att_name search for specific attribute, default null (no search)
	 * @return mixed
	 */
	public function getJoinData($table = null, $att_name = null, $default_att = null)
	{
		if(null === $table)
		{
			return $this->tableJoin;
		}
		if(null === $att_name)
		{
			return (isset($this->tableJoin[$table]) ? $this->tableJoin[$table] : array());
		}
		return (isset($this->tableJoin[$table][$att_name]) ? $this->tableJoin[$table][$att_name] : $default_att);
	}

	public function setJoinData($table, $data)
	{
		if(null === $data)
		{
			unset($this->tableJoin[$table]);
			return $this;
		}
		$this->tableJoin[$table] = (array) $data;
		return $this;
	}

	/**
	 * User defined model setter
	 * @return e_admin_controller_ui
	 */
	protected function _setModel()
	{
		return $this;
	}

	/**
	 * Get current tree model
	 * @return e_admin_tree_model
	 */
	public function getTreeModel()
	{
		if(null === $this->_tree_model)
		{
			$this->_setTreeModel();
		}

		return $this->_tree_model;
	}

	/**
	 * Set controller tree model
	 * @param e_admin_tree_model $tree_model
	 * @return e_admin_controller_ui
	 */
	public function setTreeModel($tree_model)
	{
		$this->_tree_model = $tree_model;
		return $this;
	}
	
	/**
	 * Get currently parsed model while in list mode
	 * Model instance is registered by e_form::renderListForm()
	 *
	 * @return e_admin_model
	 */
	public function getListModel()
	{
		return e107::getRegistry('core/adminUI/currentListModel');
	}
	
	public function setListModel($model)
	{
		e107::setRegistry('core/adminUI/currentListModel', $model);
		return $this;
	}

	/**
	 * User defined tree model setter
	 * @return e_admin_controller_ui
	 */
	protected function _setTreeModel()
	{
		return $this;
	}

	/**
	 * Get extended (UI) Form instance
	 *
	 * @return e_admin_form_ui
	 */
	public function getUI()
	{
		if(null === $this->_ui)
		{
			$this->_setUI();
		}
		return $this->_ui;
	}

	/**
	 * Set controller UI form
	 * @param e_admin_form_ui $ui
	 * @return e_admin_controller_ui
	 */
	public function setUI($ui)
	{
		$this->_ui = $ui;
		return $this;
	}

	/**
	 * User defined UI form setter
	 * @return e_admin_controller_ui
	 */
	protected function _setUI()
	{
		return $this;
	}

	/**
	 * Get Config object
	 * @return e_plugin_pref or e_core_pref when used in core areas
	 */
	public function getConfig()
	{
		if(null === $this->_pref)
		{
			$this->_setConfig();
		}
		return $this->_pref;
	}

	/**
	 * Set Config object
	 * @return e_admin_controller_ui
	 */
	public function setConfig($config)
	{
		$this->_prefs = $config;
		return $this;
	}

	/**
	 * User defined config setter
	 * @return e_admin_controller_ui
	 */
	protected function _setConfig()
	{
		return $this;
	}

	/**
	 * Manage column visibility
	 * @param string $batch_trigger
	 * @return none
	 */
	public function manageColumns()
	{
		$cols = array();
		$posted = $this->getPosted('e-columns', array());
		foreach ($this->getFields() as $field => $attr)
		{
			if((vartrue($attr['forced']) || in_array($field, $posted)) && !vartrue($attr['nolist']))
			{
				$cols[] = $field;
				continue;
			}
		}

		if($cols)
		{
			$this->setUserPref($cols);
		}
	}

	/**
	 * Handle posted batch options routine
	 * @param string $batch_trigger
	 * @return e_admin_controller_ui
	 */
	protected function _handleListBatch($batch_trigger)
	{
		$tp = e107::getParser();
		//$multi_name = vartrue($this->fields['checkboxes']['toggle'], 'multiselect');
		$multi_name = $this->getFieldAttr('checkboxes', 'toggle', 'multiselect');
		$selected = array_values($this->getPosted($multi_name, array()));

		//if(empty($selected)) return $this; - allow empty (no selected) submit for custom batch handlers - e.g. Export CSV
		// requires writeParams['batchNoCheck'] == true!!!
		
		$selected = array_map('intval', $selected);
		$trigger = $tp->toDB(explode('__', $batch_trigger));

		$this->setTriggersEnabled(false); //disable further triggering

		switch($trigger[0])
		{
			case 'delete': //FIXME - confirmation screen
				//method handleListDeleteBatch(); for custom handling of 'delete' batch
				// if(empty($selected)) return $this;
				// don't check selected data - subclass need to check additional post variables(confirm screen)
				$method = 'handle'.$this->getRequest()->getActionName().'DeleteBatch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected);
				}
			break;

			case 'bool':
				if(empty($selected)) return $this;
				$field = $trigger[1];
				$value = $trigger[2] ? 1 : 0;
				//something like handleListBoolBatch(); for custom handling of 'bool' batch
				$method = 'handle'.$this->getRequest()->getActionName().'BoolBatch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected, $field, $value);
					break;
				}
			break;

			case 'boolreverse':
				if(empty($selected)) return $this;
				$field = $trigger[1];
				//something like handleListBoolreverseBatch(); for custom handling of 'boolreverse' batch
				$method = 'handle'.$this->getRequest()->getActionName().'BoolreverseBatch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected, $field);
					break;
				}
			break;

			default:
				$field = $trigger[0];
				$value = $trigger[1];
				$params = $this->getFieldAttr($field, 'writeParms', array());
				if(!is_array($params)) parse_str($params, $params);

				if(!vartrue($params['batchNoCheck']) && empty($selected))
				{
					return $this;
				}
				
				//something like handleListUrlTypeBatch(); for custom handling of 'url_type' field name
				$method = 'handle'.$this->getRequest()->getActionName().$this->getRequest()->camelize($field).'Batch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected, $value);
					break;
				}
				
				//handleListBatch(); for custom handling of all field names
				if(empty($selected)) return $this;
				$method = 'handle'.$this->getRequest()->getActionName().'Batch';
				if(method_exists($this, $method))
				{
					$this->$method($selected, $field, $value);
				}
			break;
		}
		return $this;
	}

	/**
	 * Handle requested filter dropdown value
	 * @param string $value
	 * @return array field -> value
	 */
	protected function _parseFilterRequest($filter_value)
	{
		$tp = e107::getParser();
		if(!$filter_value || $filter_value === '___reset___')
		{
			return array();
		}
		$filter = $tp->toDB(explode('__', $filter_value));
		$res = array();
		switch($filter[0])
		{
			case 'bool':
				// direct query
				$res = array($filter[1], $filter[2]);
			break;

			default:
				//something like handleListUrlTypeFilter(); for custom handling of 'url_type' field name filters
				$method = 'handle'.$this->getRequest()->getActionName().$this->getRequest()->camelize($filter[0]).'Filter';
				if(method_exists($this, $method)) // callback handling
				{
					return $this->$method($filter[1], $selected);
				}
				else // default handling
				{
					$res = array($filter[0], $filter[1]);
				}
			break;
		}
		return $res;
	}


	/**
	 * Convert posted to model values after submit (based on field type)
	 * @param array $data
	 * @return void
	 */
	protected function convertToData(&$data)
	{
		$model = new e_model($data);
		foreach ($this->getFields() as $key => $attributes)
		{
			$value = vartrue($attributes['dataPath']) ? $model->getData($attributes['dataPath'])  : $model->get($key);

			if(null === $value)
			{
				continue;
			}
			switch($attributes['type'])
			{
				case 'datestamp':
					if(!is_numeric($value))
					{
						$value = trim($value) ? e107::getDateConvert()->toTime($value, 'input') : 0;
					}
				break;

				case 'ip': // TODO - ask Steve if this check is required
					//if(strpos($value, '.') !== FALSE)
					{
						$value = trim($value) ? e107::getInstance()->ipEncode($value) : '';
					}
				break;
				
				case 'dropdown': // TODO - ask Steve if this check is required
				case 'lanlist':
					if(is_array($value))
					{
						// no sanitize here - data is added to model posted stack 
						// and validated & sanitized before sent to db
						//$value = array_map(array(e107::getParser(), 'toDB'), $value); 
						$value = implode(',', $value); 
					}
				break;
			}
			if(vartrue($attributes['dataPath']))
			{
				$model->setData($attributes['dataPath'], $value);
			}
			else
			{
				$model->set($key, $value);
			}

		}

		$data = $model->getData();
		unset($model);
		$this->toData($data);
	}

	/**
	 * User defined method for converting POSTED to MODEL data
	 * @param array $data posted data
	 * @param string $type current action type - edit, create, list or user defined
	 * @return void
	 */
	protected function toData(&$data, $type = '')
	{
	}

	/**
	 * Take approproate action after successfull submit
	 *
	 * @param integer $id optional, needed only if redirect action is 'edit'
	 * @param string $noredirect_for don't redirect if action equals to its value
	 */
	protected function doAfterSubmit($id = 0, $noredirect_for = '')
	{
		if($noredirect_for && $noredirect_for == $this->getPosted('__after_submit_action') && $noredirect_for == $this->getAction())
		{
			return;
		}

		$choice = $this->getPosted('__after_submit_action', 0);
		switch ($choice) {
			case 'create': // create
				$this->redirectAction('create', 'id');
			break;

			case 'edit': // edit
				$this->redirectAction('edit', '', 'id='.$id);
			break;

			case 'list': // list
				$this->redirectAction('list', 'id');
			break;

			default:
				$choice = explode('|', str_replace('{ID}', $id, $choice), 3);
				$this->redirectAction(preg_replace('/[^\w\-]/', '', $choice[0]), vartrue($choice[1]), vartrue($choice[2]));
			break;
		}
		return;
	}

	/**
	 * Build ajax auto-complete filter response
	 * @return string response markup
	 */
	protected function renderAjaxFilterResponse($listQry = '')
	{
		$debug = false;
		$srch = $this->getPosted('searchquery');
		$this->getRequest()->setQuery('searchquery', $srch); //_modifyListQry() is requiring GET String

		$ret = '<ul>';
		$ret .= '<li>'.$srch.'<span class="informal warning"> '.LAN_FILTER_LABEL_TYPED.'</span></li>'; // fix Enter - search for typed word only

		$reswords = array();
		if(trim($srch) !== '')
		{
			// Build query
			$qry = $this->_modifyListQry(false, true, 0, 20, $listQry);
			//file_put_contents(e_LOG.'uiAjaxResponseSQL.log', $qry."\n\n", FILE_APPEND);
			
			// Make query
			$sql = e107::getDb();
			if($qry && $sql->db_Select_gen($qry, $debug))
			{
				while ($res = $sql->db_Fetch())
				{
					$tmp1 = array();
					$tmp = array_values(preg_grep('#'.$srch.'#i', $res));
					foreach ($tmp as $w)
					{
						if($w == $srch)
						{
							array_unshift($reswords, $w); //exact match
							continue;
						}
						preg_match('#[\S]*('.$srch.')[\S]*#i', $w, $tmp1);
						if($tmp1[0]) $reswords[] = $tmp1[0];
					}
				}
			}

			// Build response
			$reswords = array_unique($reswords);
			if($reswords)
			{
				$ret .= '<li>'.implode("</li>\n\t<li>", $reswords).'</li>';
			}
		}

		$ret .= '<li><span class="informal warning"> '.LAN_FILTER_LABEL_CLEAR.' </span></li>'; // clear filter option
		$ret .= '</ul>';
		return $ret;
	}

	/**
	 * Parses all available field data, adds internal attributes for handling join requests
	 * @return e_admin_controller_ui
	 */
	protected function parseAliases()
	{
		if($this->_alias_parsed) return $this; // already parsed!!!
		
		
		
		if($this->getJoinData())
		{
			foreach ($this->getJoinData() as $table => $att)
			{
				if(strpos($table, '.') !== false)
				{
					$tmp = explode('.', $table, 2);
					$this->setJoinData($table, null);
					$att['alias'] = $tmp[0];
					$att['table'] = $tmp[1];
					$att['__tablePath'] = $att['alias'].'.';
					$att['__tableFrom'] = '`#'.$att['table'].'` AS '.$att['alias'];
					$this->setJoinData($att['alias'], $att); 
					unset($tmp);
					continue;
				}
				$att['table'] = $table;
				$att['alias'] = '';
				$att['__tablePath'] = '`#'.$att['table'].'`.';
				$att['__tableFrom'] = '`#'.$att['table'].'`';
				$this->setJoinData($table, $att);
			}
		}
		
		
		
		$this->joinAlias(); // generate Table Aliases from listQry
		
		// check for table & field aliases
		$fields = array(); // preserve order
		foreach ($this->fields as $field => $att)
		{
			// tableAlias.fieldName.fieldAlias
			if(strpos($field, '.') !== false) // manually entered alias. 
			{
				$tmp = explode('.', $field, 3);
				$att['table'] = $tmp[0] ? $tmp[0] : $this->getIfTableAlias(false);
				$att['alias'] = vartrue($tmp[2]);
				$att['field'] = $tmp[1];
				$field = $att['alias'] ? $att['alias'] : $tmp[1];
				$fields[$field] = $att;
				unset($tmp);
			}
			else
			{
				
				$att['table'] = $this->getIfTableAlias(false);
				if(isset($this->joinAlias[$this->table]) && $field !='checkboxes' && $field !='options')
				{
					$att['alias'] = $this->joinAlias[$this->table].".".$field; 	
				}
				else
				{
					$att['alias'] = "";	
				}
				$att['field'] = $field;
				$fields[$field] = $att;
			}

			if($fields[$field]['table'] == $this->getIfTableAlias(false))
			{
				$fields[$field]['__tableField'] = $att['alias'] ? $att['alias'] : $this->getIfTableAlias(true, true).'.'.$att['field'];
				$fields[$field]['__tableFrom'] = $this->getIfTableAlias(true, true).'.'.$att['field'].($att['alias'] ? ' AS '.$att['alias'] : '');
			}
			else
			{
				$fields[$field]['__tableField'] = $this->getJoinData($fields[$field]['table'], '__tablePath').$field;
			}
			/*if($fields[$field]['table'])
			{
				if($fields[$field]['table'] == $this->getIfTableAlias(false))
				{
					$fields[$field]['__tableField'] = $att['alias'] ? $att['alias'] : $this->getIfTableAlias(true, true).'.'.$att['field'];
					$fields[$field]['__tableFrom'] = $this->getIfTableAlias(true, true).'.'.$att['field'].($att['alias'] ? ' AS '.$att['alias'] : '');
				}
				else
				{
					$fields[$field]['__tableField'] = $this->getJoinData($fields[$field]['table'], '__tablePath').$field;
				}
			}
			else
			{
				$fields[$field]['__tableField'] = '`'.$this->getTableName(false, true).'`.'.$field;
			}*/
		}

		$this->fields = $fields;
		$this->_alias_parsed = true;
		return $this;
	}

	/**
	 *  Intuitive LEFT JOIN Qry support. (preferred)
	 *  Generate array of table names and their alias - auto-detected from listQry;
	 *  eg. $listQry = "SELECT m.*, u.user_id,u.user_name FROM #core_media AS m LEFT JOIN #user AS u ON m.media_author = u.user_id"; 
	 */
	protected function joinAlias()
	{
		//TODO - editQry
		// TODO - auto-detect fields that belong to other tables. eg. u.user_id,u.user_name and adjust query to suit. 
		if($this->listQry) 
		{
			preg_match_all("/`?#([\w-]+)`?\s*(as|AS)\s*([\w-])/im",$this->listQry,$matches);
			
			foreach($matches[1] AS $k=>$v)
			{
				if(varset($matches[3][$k]))
				{
					$this->joinAlias[$v] = $matches[3][$k]; // array. eg $this->joinAlias['core_media'] = 'm';
				}			
			}
		}
		
	}




	// TODO - abstract, array return type, move to parent?
	protected function _modifyListQry($raw = false, $isfilter = false, $forceFrom = false, $forceTo = false, $listQry = '')
	{
		$searchQry = array();
		$filterFrom = array();
		$request  = $this->getRequest();
		$tp = e107::getParser();
		$tablePath = $this->getIfTableAlias(true, true).'.';
		$tableFrom = '`'.$this->getTableName(false, true).'`'.($this->getTableName(true) ? ' AS '.$this->getTableName(true) : '');
		$tableSFieldsArr = array(); // FROM for main table
		$tableSJoinArr = array(); // FROM for join tables
		$filter = array();
		
		$searchQuery = $tp->toDB($request->getQuery('searchquery', ''));
		$searchFilter = $this->_parseFilterRequest($request->getQuery('filter_options', ''));
		list($filterField, $filterValue) = $searchFilter;

		if($filterField && $filterValue !== '' && isset($this->fields[$filterField]))
		{
			$searchQry[] = $this->fields[$filterField]['__tableField']." = '".$filterValue."'";
		}


		// main table should select everything
		$tableSFieldsArr[] = $tablePath.'*';
		foreach($this->getFields() as $key => $var)
		{
			// disabled or system
			if((vartrue($var['nolist']) && !vartrue($var['filter'])) || null === $var['type'])
			{
				continue;
			}

			// select FROM... for main table
			if($var['alias'] && vartrue($var['__tableFrom']))
			{
				$tableSFieldsArr[] = $var['__tableFrom'];
			}

			// filter for WHERE and FROM clauses
			$searchable_types = array('text', 'textarea', 'bbarea', 'user', 'email'); //method?
			if(trim($searchQuery) !== '' && in_array($var['type'], $searchable_types))
			{
				$filter[] = $var['__tableField']." REGEXP ('".$searchQuery."')";
				if($isfilter)
				{
					$filterFrom[] = $var['__tableField'];
				
				}
			}
		}


		
		if($isfilter)
		{
			if(!$filterFrom) return false;
			$tableSFields = implode(', ', $filterFrom);
		}
		else
		{
			$tableSFields = $tableSFieldsArr ? implode(', ', $tableSFieldsArr) : $tablePath.'*';
		}


		$jwhere = array();
		$joins = array();
		//file_put_contents(e_LOG.'uiAjaxResponseSFields.log', $tableSFields."\n\n", FILE_APPEND);
		//file_put_contents(e_LOG.'uiAjaxResponseFields.log', print_r($this->getFields(), true)."\n\n", FILE_APPEND);
		if($this->getJoinData())
		{
			$qry = "SELECT SQL_CALC_FOUND_ROWS ".$tableSFields;
			foreach ($this->getJoinData() as $jtable => $tparams)
			{
				// Select fields
				if(!$isfilter)
				{
					$fields = vartrue($tparams['fields']);
					if('*' === $fields)
					{
						$tableSJoinArr[] = "{$tparams['__tablePath']}*";
					}
					else
					{
						$tableSJoinArr[] = $fields;
						/*$fields = explode(',', $fields);
						foreach ($fields as $field)
						{
							$qry .= ", {$tparams['__tablePath']}`".trim($field).'`';
						}*/
					}
				}

				// Prepare Joins
				$joins[] = "
					".vartrue($tparams['joinType'], 'LEFT JOIN')." {$tparams['__tableFrom']} ON ".(vartrue($tparams['leftTable']) ? $tparams['leftTable'].'.' : $tablePath)."`".vartrue($tparams['leftField'])."` = {$tparams['__tablePath']}`".vartrue($tparams['rightField'])."`".(vartrue($tparams['whereJoin']) ? ' '.$tparams['whereJoin'] : '');

				// Prepare Where
				if(vartrue($tparams['where']))
				{
					$jwhere[] = $tparams['where'];
				}
			}

			//From
			$qry .= $tableSJoinArr ? ', '.implode(', ', $tableSJoinArr)." FROM ".$tableFrom : " FROM ".$tableFrom;

			// Joins
			if(count($joins) > 0)
			{
				$qry .=  "\n".implode("\n", $joins);
			}
		}
		else
		{
			$qry = $listQry ? $listQry : "SELECT SQL_CALC_FOUND_ROWS ".$tableSFields." FROM ".$tableFrom;
		}
		
		// group field - currently auto-added only if there are joins
		// TODO - groupField property  
		$groupField = '';
		if($joins && $this->getPrimaryName())
		{
			$groupField = $tablePath.$this->getPrimaryName();
		}

		if($raw)
		{
			$rawData = array(
				'joinWhere' => $jwhere, 
				'filter' => $filter, 
				'listQrySql' => $this->listQrySql,
				'filterFrom' => $filterFrom, 
				'search' => $searchQry, 
				'tableFromName' => $tableFrom,
			);
			$rawData['tableFrom'] = $tableSFieldsArr;
			$rawData['joinsFrom'] = $tableSJoinArr;
			$rawData['joins'] = $joins;
			$rawData['groupField'] = $groupField;
			$rawData['orderField'] = isset($this->fields[$orderField]) ? $this->fields[$orderField]['__tableField'] : '';
			$rawData['orderType'] = $request->getQuery('asc') == 'desc' ? 'DESC' : 'ASC';
			$rawData['limitFrom'] = false === $forceFrom ? intval($request->getQuery('from', 0)) : intval($forceFrom);
			$rawData['limitTo'] = false === $forceTo ? intval($this->getPerPage()) : intval($forceTo);
			return $rawData;
		}

		// join where
		if(count($jwhere) > 0)
		{
			$searchQry[] = " (".implode(" AND ",$jwhere)." )";
		}
		// filter where
		if(count($filter) > 0)
		{
			$searchQry[] = " ( ".implode(" OR ",$filter)." ) ";
		}
		
		// more user added sql
		if(isset($this->listQrySql['db_where']) && $this->listQrySql['db_where'])
		{
			if(is_array($this->listQrySql['db_where']))
			{
				$searchQry[] = implode(" AND ", $this->listQrySql['db_where']);
			}
			else
			{
				$searchQry[] = $this->listQrySql['db_where'];
			}
		}

		// where query
		if(count($searchQry) > 0)
		{
			$qry .= " WHERE ".implode(" AND ", $searchQry);
		}
		
		// GROUP BY if needed
		if($groupField)
		{
			$qry .= ' GROUP BY '.$groupField;
		}

		// only when no custom order is required
		if($this->listOrder && !$request->getQuery('field') && !$request->getQuery('asc'))
		{
			$qry .= ' ORDER BY '.$this->listOrder;
		}
		elseif(false !== $this->listOrder)
		{
			$orderField = $request->getQuery('field', $this->getDefaultOrderField());
			$orderDef = (null === $request->getQuery('asc', null) ? $this->getDefaultOrder() : $request->getQuery('asc'));
			if(isset($this->fields[$orderField]) && strpos($this->listQry,'ORDER BY')==FALSE) //override ORDER using listQry (admin->sitelinks)
			{
				// no need of sanitize - it's found in field array
				$qry .= ' ORDER BY '.$this->fields[$orderField]['__tableField'].' '.(strtolower($orderDef) == 'desc' ? 'DESC' : 'ASC');
			}
		}
		if($this->getPerPage() || false !== $forceTo)
		{
			$from = false === $forceFrom ? intval($request->getQuery('from', 0)) : intval($forceFrom);
			if(false === $forceTo) $forceTo = $this->getPerPage();
			$qry .= ' LIMIT '.$from.', '.intval($forceTo);
		}
		
		// Debug Filter Query. 	
		// echo $qry;
	
		return $qry;
	}

	/**
	 * Manage submit item
	 * Note: $callbackBefore will break submission if returns false
	 *
	 * @param string $callbackBefore existing method from $this scope to be called before submit
	 * @param string $callbackAfter existing method from $this scope to be called after successfull submit
	 * @param string $noredirectAction passed to doAfterSubmit()
	 * @return boolean
	 */
	protected function _manageSubmit($callbackBefore = '', $callbackAfter = '', $callbackError = '', $noredirectAction = '')
	{
		$model = $this->getModel();
		$old_data = $model->getData();

		$_posted = $this->getPosted();
		$this->convertToData($_posted);

		if($callbackBefore && method_exists($this, $callbackBefore))
		{
			$data = $this->$callbackBefore($_posted, $old_data, $model->getId());
			if(false === $data)
			{
				// we don't wanna loose posted data
				$model->setPostedData($_posted, null, false, false);
				return false;
			}
			if($data && is_array($data))
			{
				// add to model data fields array if required
				foreach ($data as $f => $val)
				{
					if($this->getFieldAttr($f, 'data'))
					{
						$model->setDataField($f, $this->getFieldAttr($f, 'data'));
					}
				}
				$_posted = array_merge($_posted, $data);
			}
		}


		// Scenario I - use request owned POST data - toForm already executed
		$model->setPostedData($_posted, null, false, false)
			->save(true);
		// Scenario II - inner model sanitize
		//$this->getModel()->setPosted($this->convertToData($_POST, null, false, true);

		// Take action based on use choice after success
		if(!$this->getModel()->hasError())
		{
			// callback (if any)
			if($callbackAfter && method_exists($this, $callbackAfter))
			{
				$this->$callbackAfter($model->getData(), $old_data, $model->getId());
			}
			$model->setMessages(true); //FIX - move messages (and session messages) to the default stack
			$this->doAfterSubmit($model->getId(), $noredirectAction);
			return true;
		}
		elseif($callbackError && method_exists($this, $callbackError))
		{
			// suppress messages if callback returns TRUE
			if(true !== $this->$callbackError($_posted, $old_data, $model->getId()))
			{
				// Copy model messages to the default message stack
				$model->setMessages();
			}
			return false;
		}

		// Copy model messages to the default message stack
		$model->setMessages();
		return false;
	}
}

class e_admin_ui extends e_admin_controller_ui
{

	protected $fieldTypes = array();
	protected $dataFields = array();
	protected $validationRules = array();

	protected $table;
	protected $pid;
	protected $listQry;
	protected $editQry;
	
	
	/**
	 * Markup to be auto-inserted before List filter
	 * @var string
	 */
	public $preFiliterMarkup = '';
	
	/**
	 * Markup to be auto-inserted after List filter
	 * @var string
	 */
	public $postFiliterMarkup = '';
	
	/**
	 * Markup to be auto-inserted at the top of Create form
	 * @var string
	 */
	public $headerCreateMarkup = '';
	
	/**
	 * Markup to be auto-inserted at the bottom of Create form
	 * @var string
	 */
	public $footerCreateMarkup = '';
	
	/**
	 * Markup to be auto-inserted at the top of Update form
	 * @var string
	 */
	public $headerUpdateMarkup = '';
	
	/**
	 * Markup to be auto-inserted at the bottom of Update form
	 * @var string
	 */
	public $footerUpdateMarkup = '';
	
	/**
	 * Show confirm screen before (batch/single) delete
	 * @var boolean
	 */
	public $deleteConfirmScreen = false;

	/**
	 * Constructor
	 * @param e_admin_request $request
	 * @param e_admin_response $response
	 * @param array $params [optional]
	 */
	public function __construct($request, $response, $params = array())
	{
		$this->setDefaultAction($request->getDefaultAction());
		$params['enable_triggers'] = true; // override

		parent::__construct($request, $response, $params);

		if(!$this->pluginName)
		{
			$this->pluginName = 'core';
		}

		$ufieldpref = $this->getUserPref();
		if($ufieldpref)
		{
			$this->fieldpref = $ufieldpref;
		}

		$this->addTitle($this->pluginTitle, true)->parseAliases();
	}

	/**
	 * Catch fieldpref submit
	 * @return none
	 */
	public function ListEcolumnsTrigger()
	{
		$this->setTriggersEnabled(false); //disable further triggering
		parent::manageColumns();
	}

	/**
	 * Catch batch submit
	 * @param string $batch_trigger
	 * @return none
	 */
	public function ListBatchTrigger($batch_trigger)
	{
		$this->setPosted('etrigger_batch', null);
 
		if($this->getPosted('etrigger_cancel')) 
		{ 
			$this->setPosted(array());
			return; // always break on cancel!
		}
		$this->deleteConfirmScreen = true; // Confirm screen ALWAYS enabled when multi-deleting!
		
		// proceed ONLY if there is no other trigger, except delete confirmation
		if($batch_trigger && !$this->hasTrigger(array('etrigger_delete_confirm'))) $this->_handleListBatch($batch_trigger);
	}

	/**
	 * Batch delete trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListDeleteBatch($selected)
	{
		if(!$this->getBatchDelete())
		{
			e107::getMessage()->add(LAN_UI_BATCHDEL_ERROR, E_MESSAGE_WARNING);
			return;
		}
		if($this->deleteConfirmScreen)
		{
			if(!$this->getPosted('etrigger_delete_confirm'))
			{
				// ListPage will show up confirmation screen
				$this->setPosted('delete_confirm_value', implode(',', $selected));
				return;
			}
			else
			{
				// already confirmed, resurrect selected values
				$selected = array_map('intval', explode(',', $this->getPosted('delete_confirm_value')));
			}
		}
		
		// delete one by one - more control, less performance
		// TODO - pass  afterDelete() callback to tree delete method?
		$set_messages = true;
		foreach ($selected as $id)
		{
			$data = array();
			$model = $this->getTreeModel()->getNode($id);
			if($model)
			{
				$data = $model->getData();
				if($this->beforeDelete($data, $id))
				{
					$check = $this->getTreeModel()->delete($id);
					if(!$this->afterDelete($data, $id, $check))
					{
						$set_messages = false;
					}
				}
			}
		}

		//$this->getTreeModel()->delete($selected);
		if($set_messages) $this->getTreeModel()->setMessages();
		$this->redirect();
	}

	/**
	 * Batch boolean trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListBoolBatch($selected, $field, $value)
	{
		$cnt = $this->getTreeModel()->update($field, $value, $selected, $value, false);
		if($cnt)
		{
			$this->getTreeModel()->addMessageSuccess(sprintf(LAN_UI_BATCH_BOOL_SUCCESS, $cnt));
		}
		$this->getTreeModel()->setMessages();
	}

	/**
	 * Batch boolean reverse trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListBoolreverseBatch($selected, $field, $value)
	{
		$tree = $this->getTreeModel();
		$cnt = $tree->update($field, "1-{$field}", $selected, null, false);
		if($cnt)
		{
			$tree->addMessageSuccess(sprintf(LAN_UI_BATCH_REVERSED_SUCCESS, $cnt));
			//sync models
			$tree->load(true);
		}
		$this->getTreeModel()->setMessages();
	}

	/**
	 * Batch default (field) trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListBatch($selected, $field, $value)
	{
		$cnt = $this->getTreeModel()->update($field, "'".$value."'", $selected, $value, false);
		if($cnt)
		{
			$vttl = $this->getUI()->renderValue($field, $value, $this->getFieldAttr($field));
			$this->getTreeModel()->addMessageSuccess(sprintf(LAN_UI_BATCH_UPDATE_SUCCESS, $vttl, $cnt));
		}
		$this->getTreeModel()->setMessages();
	}

	/**
	 * Catch delete submit
	 * @param string $batch_trigger
	 * @return none
	 */
	public function ListDeleteTrigger($posted)
	{
		if($this->getPosted('etrigger_cancel')) 
		{ 
			$this->setPosted(array());
			return; // always break on cancel!
		}
		$id = intval(array_shift($posted));
		if($this->deleteConfirmScreen && !$this->getPosted('etrigger_delete_confirm'))
		{
			// forward data to delete confirm screen
			$this->setPosted('delete_confirm_value', $id);
			return; // User confirmation expected
		}
		
		$this->setTriggersEnabled(false);
		$data = array();
		$model = $this->getTreeModel()->getNode($id);
		if($model)
		{
			$data = $model->getData();
			if($this->beforeDelete($data, $id))
			{
				$check = $this->getTreeModel()->delete($id);
				if($this->afterDelete($data, $id, $check))
				{
					$this->getTreeModel()->setMessages();
				}
			}
			else
			{
				$this->getTreeModel()->setMessages();// errors
			}
		}
	}

	/**
	 * User defined pre-delete logic
	 */
	public function beforeDelete($data, $id)
	{
		return true;
	}

	/**
	 * User defined after-delete logic
	 */
	public function afterDelete($deleted_data, $id, $deleted_check)
	{
		return true;
	}

	/**
	 * List action header
	 * @return void
	 */
	public function ListHeader()
	{
		e107::getJs()->headerCore('core/tabs.js')
			->headerCore('core/admin.js');
	}

	/**
	 * List action observer
	 * @return void
	 */
	public function ListObserver()
	{
		$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, false, false, $this->listQry))->load();
		$this->addTitle('List'); // FIXME - get captions from dispatch list
	}

	/**
	 * Filter response ajax page
	 * @return string
	 */
	public function FilterAjaxPage()
	{
		return $this->renderAjaxFilterResponse($this->listQry); //listQry will be used only if available
	}

	/**
	 * Generic List action page
	 * @return string
	 */
	public function ListPage()
	{
		if($this->deleteConfirmScreen && !$this->getPosted('etrigger_delete_confirm') && $this->getPosted('delete_confirm_value'))
		{
			// 'edelete_confirm_data' set by single/batch delete trigger
			return $this->getUI()->getConfirmDelete($this->getPosted('delete_confirm_value')); // User confirmation expected
		}
		return $this->getUI()->getList();
	}

	/**
	 * List action observer
	 * @return void
	 */
	public function ListAjaxObserver()
	{
		$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, 0, false, $this->listQry))->load();
	}

	/**
	 * Generic List action page (Ajax)
	 * @return string
	 */
	public function ListAjaxPage()
	{
		return $this->getUI()->getList(true);
	}

	/**
	 * Generic Edit observer
	 */
	public function EditObserver()
	{
		$this->getModel()->load($this->getId());
		$this->addTitle(LAN_UPDATE, true);
	}

	/**
	 * Generic Create submit trigger
	 */
	public function EditCancelTrigger()
	{
		$this->redirectAction('list', 'id');
	}

	/**
	 * Generic Edit submit trigger
	 */
	public function EditSubmitTrigger()
	{
		$this->_manageSubmit('beforeUpdate', 'afterUpdate', 'onUpdateError', 'edit');
	}

	/**
	 * Edit - send JS to page Header
	 * @return none
	 */
	function EditHeader()
	{
		e107::getJs()->requireCoreLib('core/admin.js');
	}

	/**
	 * Generic Edit page
	 * @return string
	 */
	public function EditPage()
	{
		return $this->CreatePage();
	}

	/**
	 * Generic Create observer
	 * @return string
	 */
	public function CreateObserver()
	{
		$this->setTriggersEnabled(true);
		$this->addTitle(LAN_CREATE, true);
	}

	/**
	 * Generic Create submit trigger
	 */
	public function CreateCancelTrigger()
	{
		$this->redirectAction('list', 'id');
	}

	/**
	 * Generic Create submit trigger
	 */
	public function CreateSubmitTrigger()
	{
		$this->_manageSubmit('beforeCreate', 'afterCreate', 'onCreateError');
	}

	/**
	 * User defined pre-create logic, return false to prevent DB query execution
	 */
	public function beforeCreate($new_data, $old_data)
	{
	}

	/**
	 * User defined after-create logic
	 */
	public function afterCreate($new_data, $old_data, $id)
	{
	}

	/**
	 * User defined error handling, return true to suppress model messages
	 */
	public function onCreateError($new_data, $old_data)
	{
	}

	/**
	 * User defined pre-update logic, return false to prevent DB query execution
	 */
	public function beforeUpdate($new_data, $old_data, $id)
	{
	}

	/**
	 * User defined after-update logic
	 */
	public function afterUpdate($new_data, $old_data, $id)
	{
	}

	/**
	 * User defined error handling, return true to suppress model messages
	 */
	public function onUpdateError($new_data, $old_data, $id)
	{
	}

	/**
	 * Create - send JS to page Header
	 * @return none
	 */
	function CreateHeader()
	{
		// TODO - invoke it on className (not all textarea elements)
		e107::getJs()->requireCoreLib('core/admin.js');
	}

	/**
	 *
	 * @return TBD
	 */
	public function CreatePage()
	{
		return $this->getUI()->getCreate();
	}

	public function PrefsSaveTrigger()
	{
		$this->getConfig()
			->setPostedData($this->getPosted(), null, false, false)
			//->setPosted('not_existing_pref_test', 1)
			->save(true);

		$this->getConfig()->setMessages();

	}

	public function PrefsPage()
	{
		return $this->getUI()->getSettings();
	}

	/**
	 * Parent overload
	 * @return e_admin_ui
	 */
	protected function parseAliases()
	{
		// parse table
		if(strpos($this->table, '.') !== false)
		{
			$tmp = explode('.', $this->table, 2);
			$this->table = $tmp[1];
			$this->tableAlias = $tmp[0];
			unset($tmp);
		}

		parent::parseAliases();

		return $this;
	}

	public function getPrimaryName()
	{
		// Option for working with tables having no PID
		if(!varset($this->pid) && vartrue($this->fields) && false !== $this->pid)
		{
			e107::getMessage()->add(LAN_UI_NOPID_ERROR, E_MESSAGE_WARNING);
		}

		return $this->pid;
	}

	public function getTableName($alias = false, $prefix = false)
	{
		if($alias) return ($this->tableAlias ? $this->tableAlias : '');
		return ($prefix ? '#' : '').$this->table;
	}

	/**
	 * Validation rules retrieved from controller object
	 * @return array
	 */
	public function getValidationRules()
	{
		return $this->validationRules;
	}

	/**
	 * Data Field array retrieved from controller object
	 * @return array
	 */
	public function getDataFields()
	{
		return $this->dataFields;
	}


	/**
	 * Set read and write parms with drop-down-list array data (ie. type='dropdown')
	 * @param str $field
	 * @param array $array [optional]
	 * @return none
	 */
	public function setDropDown($field,$array) //TODO Have Miro check this.
	{
		$this->fields[$field]['readParms'] = $array;
		$this->fields[$field]['writeParms'] = $array;
	}


	/**
	 * Set Config object
	 * @return e_admin_ui
	 */
	protected function _setConfig()
	{
		$this->_pref = $this->pluginName == 'core' ? e107::getConfig() : e107::getPlugConfig($this->pluginName);

		$dataFields = $validateRules = array();
		foreach ($this->prefs as $key => $att)
		{
			// create dataFields array
			$dataFields[$key] = vartrue($att['data'], 'string');

			// create validation array
			if(vartrue($att['validate']))
			{
				$validateRules[$key] = array((true === $att['validate'] ? 'required' : $att['validate']), varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
			}
			/* Not implemented in e_model yet
			elseif(vartrue($att['check']))
			{
				$validateRules[$key] = array($att['check'], varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
			}*/
		}
		$this->_pref->setDataFields($dataFields)->setValidationRules($validateRules);

		return $this;
	}

	/**
	 * Set current model
	 *
	 * @return e_admin_ui
	 */
	public function _setModel()
	{
		// try to create dataFields array if missing
		if(!$this->dataFields)
		{
			$this->dataFields = array();
			foreach ($this->fields as $key => $att)
			{
				if((false !== varset($att['data']) && null !== $att['type'] && !vartrue($att['noedit'])) || vartrue($att['forceSave']))
				{
					$this->dataFields[$key] = vartrue($att['data'], 'str');
				}
			}
		}
		// TODO - do it in one loop, or better - separate method(s) -> convertFields(validate), convertFields(data),...
		if(!$this->validationRules)
		{
			$this->validationRules = array();
			foreach ($this->fields as $key => $att)
			{
				if(null === $att['type'] || vartrue($att['noedit']))
				{
					continue;
				}
				if(vartrue($att['validate']))
				{
					$this->validationRules[$key] = array((true === $att['validate'] ? 'required' : $att['validate']), varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
				}
				/*elseif(vartrue($att['check'])) could go?
				{
					$this->checkRules[$key] = array($att['check'], varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
				}*/
			}
		}
		
		// don't touch it if already exists
		if($this->_model) return $this;
		
		// default model
		$this->_model = new e_admin_model();
		$this->_model->setModelTable($this->table)
			->setFieldIdName($this->pid)
			->setValidationRules($this->validationRules)
			->setDbTypes($this->fieldTypes)
			->setDataFields($this->dataFields)
			->setMessageStackName('admin_ui_model_'.$this->table)
			->setParam('db_query', $this->editQry);

		return $this;
	}

	/**
	 * Set current tree
	 * @return e_admin_ui
	 */
	public function _setTreeModel()
	{
		// default tree model
		$this->_tree_model = new e_admin_tree_model();
		$this->_tree_model->setModelTable($this->table)
			->setFieldIdName($this->pid)
			->setMessageStackName('admin_ui_tree_'.$this->table)
			->setParams(array('model_class' => 'e_admin_model', 'model_message_stack' => 'admin_ui_model_'.$this->table ,'db_query' => $this->listQry));

		return $this;
	}

	/**
	 * Get extended (UI) Form instance
	 *
	 * @return e_admin_ui
	 */
	public function _setUI()
	{
		if($this->getParam('ui'))
		{
			$this->_ui = $this->getParam('ui');
			$this->setParam('ui', null);
		}
		else// default ui
		{
			$this->_ui = new e_admin_form_ui($this);
		}
		return $this;
	}

}

class e_admin_form_ui extends e_form
{
	/**
	 * @var e_admin_ui
	 */
	protected $_controller = null;


	/**
	 * Constructor
	 * @param e_admin_ui $controller
	 * @param boolean $tabindex [optional] enable form element auto tab-indexing
	 */
	function __construct($controller, $tabindex = false)
	{
		$this->_controller = $controller;
		parent::__construct($tabindex);

		// protect current methods from conflict.
		$this->preventConflict();
		// user constructor
		$this->init();
	}

	protected function preventConflict()
	{
		$err = false;
		$fields = $this->getController()->getFields();
		foreach($fields as $field => $foptions)
		{
			// check form custom methods
			if($foptions['type'] === 'method' && method_exists('e_form', $field)) // check even if type is not method. - just in case of an upgrade later by 3rd-party.
			{
				e107::getMessage()->addError(sprintf(LAN_UI_FORM_METHOD_ERROR, $field));
				$err = true;
			}
		}
	
		/*if($err)
		{
			//echo $err;
			//exit;
		}*/
	}



	/**
	 * User defined init
	 */
	public function init()
	{
	}

	/**
	 * TODO - lans
	 * Generic DB Record Creation Form.
	 * @return string
	 */
	function getCreate()
	{
		$controller = $this->getController();
		$request = $controller->getRequest();
		if($controller->getId())
		{
			$legend = sprintf(LAN_UI_EDIT_LABEL, $controller->getId());
			$form_start = vartrue($controller->headerUpdateMarkup);
			$form_end = vartrue($controller->footerUpdateMarkup);
		}
		else
		{
			$legend = LAN_UI_CREATE_LABEL;
			$form_start = vartrue($controller->headerCreateMarkup);
			$form_end = vartrue($controller->footerCreateMarkup);
		}

		
		$forms = $models = array();
		$forms[] = array(
				'id'  => $this->getElementId(),
				//'url' => e_SELF,
				//'query' => 'self', or custom GET query, self is default
				'tabs' => true, // TODO - NOT IMPLEMENTED YET - enable tabs (only if fieldset count is > 1)
				'fieldsets' => array(
					'create' => array(
						'legend' => $legend,
						'fields' => $controller->getFields(), //see e_admin_ui::$fields
						'header' => $form_start,
						'footer' => $form_end,
						'after_submit_options' => true, // or true for default redirect options
						'after_submit_default' => $request->getPosted('__after_submit_action', $controller->getDefaultAction()), // or true for default redirect options
						'triggers' => 'auto', // standard create/update-cancel triggers
					)
				)
		);
		$models[] = $controller->getModel();

		return $this->renderCreateForm($forms, $models, e_AJAX_REQUEST);
	}

	/**
	 * TODO - lans
	 * Generic Settings Form.
	 * @return string
	 */
	function getSettings()
	{
		$controller = $this->getController();
		$request = $controller->getRequest();
		$legend = LAN_UI_PREF_LABEL;
		$forms = $models = array();
		$forms[] = array(
				'id'  => $this->getElementId(),
				//'url' => e_SELF,
				//'query' => 'self', or custom GET query, self is default
				'tabs' => false, // TODO - NOT IMPLEMENTED YET - enable tabs (only if fieldset count is > 1)
				'fieldsets' => array(
					'settings' => array(
						'legend' => $legend,
						'fields' => $controller->getPrefs(), //see e_admin_ui::$prefs
						'after_submit_options' => false,
						'after_submit_default' => false, // or true for default redirect options
						'triggers' => array('save' => array(LAN_SAVE, 'update')), // standard create/update-cancel triggers
					)
				)
		);
		$models[] = $controller->getConfig();

		return $this->renderCreateForm($forms, $models, e_AJAX_REQUEST);
	}

	/**
	 * Create list view
	 * Search for the following GET variables:
	 * - from: integer, current page
	 *
	 * @return string
	 */
	public function getList($ajax = false)
	{
		$tp = e107::getParser();
		$controller = $this->getController();

		$request = $controller->getRequest();
		$id = $this->getElementId();
		$tree = $options = array();
		$tree[$id] = $controller->getTreeModel();
		
		// if going through confirm screen - no JS confirm
		$controller->setFieldAttr('options', 'noConfirm', $controller->deleteConfirmScreen);

		$options[$id] = array(
			'id' => $this->getElementId(), // unique string used for building element ids, REQUIRED
			'pid' => $controller->getPrimaryName(), // primary field name, REQUIRED
			//'url' => e_SELF, default
			//'query' => $request->buildQueryString(array(), true, 'ajax_used'), - ajax_used is now removed from QUERY_STRING - class2
			'head_query' => $request->buildQueryString('field=[FIELD]&asc=[ASC]&from=[FROM]', false), // without field, asc and from vars, REQUIRED
			'np_query' => $request->buildQueryString(array(), false, 'from'), // without from var, REQUIRED for next/prev functionality
			'legend' => $controller->getPluginTitle(), // hidden by default
			'form_pre' => !$ajax ? $this->renderFilter($tp->post_toForm(array($controller->getQuery('searchquery'), $controller->getQuery('filter_options'))), $controller->getMode().'/'.$controller->getAction()) : '', // needs to be visible when a search returns nothing
			'form_post' => '', // markup to be added after closing form element
			'fields' => $controller->getFields(), // see e_admin_ui::$fields
			'fieldpref' => $controller->getFieldPref(), // see e_admin_ui::$fieldpref
			'table_pre' => '', // markup to be added before opening table element
			'table_post' => !$tree[$id]->isEmpty() ? $this->renderBatch($controller->getBatchDelete()) : '',
			'fieldset_pre' => '', // markup to be added before opening fieldset element
			'fieldset_post' => '', // markup to be added after closing fieldset element
			'perPage' => $controller->getPerPage(), // if 0 - no next/prev navigation
			'from' => $controller->getQuery('from', 0), // current page, default 0
			'field' => $controller->getQuery('field'), //current order field name, default - primary field
			'asc' => $controller->getQuery('asc', 'desc'), //current 'order by' rule, default 'asc'
		);
		return $this->renderListForm($options, $tree, $ajax);
	}
	
	public function getConfirmDelete($ids, $ajax = false)
	{
		$controller = $this->getController();
		$request = $controller->getRequest();
		$fieldsets = array();
		$forms = array();
		$id_array = explode(',', $ids);
		$delcount = count($id_array);
		
		e107::getMessage()->addWarning(sprintf(LAN_UI_DELETE_WARNING, $delcount));
		
		$fieldsets['confirm'] = array(
			'fieldset_pre' => '', // markup to be added before opening fieldset element
			'fieldset_post' => '', // markup to be added after closing fieldset element
			'table_head' => '', // markup between <thead> tag
			// Colgroup Example: array(0 => array('class' => 'label', 'style' => 'text-align: left'), 1 => array('class' => 'control', 'style' => 'text-align: left'));
			'table_colgroup' => '', // array to be used for creating markup between  <colgroup> tag (<col> list)
			'table_pre' => '', // markup to be added before opening table element
			'table_post' => '', // markup to be added after closing table element
			'table_rows' => '', // rows array (<td> tags)
			'table_body' => '', // string body - used only if rows empty 
			'pre_triggers' => '',
			'triggers' => array('hidden' => $this->hidden('etrigger_delete['.$ids.']', $ids), 'delete_confirm' => array(LAN_CONFDELETE, 'submit', $ids), 'cancel' => array(LAN_CANCEL, 'cancel')),
		);
		if($delcount > 1)
		{
			$fieldsets['confirm']['triggers']['hidden'] = $this->hidden('etrigger_batch', 'delete');
		}
		
		$forms[$id] = array(
			'id' => $this->getElementId(), // unique string used for building element ids, REQUIRED
			'url' => e_SELF, // default
			'query' => $request->buildQueryString(array(), true, 'ajax_used'), // - ajax_used is now removed from QUERY_STRING - class2
			'legend' => $controller->addTitle(LAN_UI_DELETE_LABEL), // hidden by default
			'form_pre' => '',  // markup to be added before opening form element
			'form_post' => '', // markup to be added after closing form element
			'header' => '', // markup to be added after opening form element
			'footer' => '', // markup to be added before closing form element
			'fieldsets' => $fieldsets,
		);
		return $this->renderForm($forms, $ajax);
	}

	function renderFilter($current_query = array(), $location = '', $input_options = array())
	{
		if(!$input_options) $input_options = array('size' => 20);
		if(!$location)
		{
			$location = 'main/list'; //default location
		}
		$l = e107::getParser()->post_toForm(explode('/', $location));
		if(!is_array($input_options))
		{
			parse_str($input_options, $input_options);
		}
		$input_options['id'] = false;
		$input_options['class'] = 'tbox input-text filter';
		$controller = $this->getController();
		$filter_pre = vartrue($controller->preFiliterMarkup);
		$filter_post = vartrue($controller->postFiliterMarkup);
		$text = "
			<form method='get' action='".e_SELF."'>
				<fieldset class='e-filter'>
					<legend class='e-hideme'>".LAN_LABEL_LABEL_SELECTED."</legend>
					".$filter_pre."
					<div class='left'>
						".$this->text('searchquery', $current_query[0], 50, $input_options)."
						".$this->select_open('filter_options', array('class' => 'tbox select filter', 'id' => false))."
							".$this->option(LAN_FILTER_LABEL_DISPLAYALL, '')."
							".$this->option(LAN_FILTER_LABEL_CLEAR, '___reset___')."
							".$this->renderBatchFilter('filter', $current_query[1])."
						".$this->select_close()."
						<div class='e-autocomplete'></div>
						".$this->hidden('mode', $l[0])."
						".$this->hidden('action', $l[1])."
						".$this->admin_button('etrigger_filter', 'etrigger_filter', 'filter e-hide-if-js', LAN_FILTER, array('id' => false))."
						<span class='indicator' style='display: none;'>
							<img src='".e_IMAGE_ABS."generic/loading_16.gif' class='icon action S16' alt='".LAN_LOADING."' />
						</span>
					</div>
					".$filter_post."
				</fieldset>
			</form>
		";

		e107::getJs()->requireCoreLib('scriptaculous/controls.js', 2);
		//TODO - external JS
		e107::getJs()->footerInline("
	            //autocomplete fields
	             \$\$('input[name=searchquery]').each(function(el, cnt) {
				 	if(!cnt) el.activate();
					else return;
					new Ajax.Autocompleter(el, el.next('div.e-autocomplete'), '".e_SELF."?mode=".$l[0]."&action=filter', {
					  paramName: 'searchquery',
					  minChars: 2,
					  frequency: 0.5,
					  afterUpdateElement: function(txt, li) {
					  	var cfrm = el.up('form'), cont = cfrm.next('.e-container');
						if(!cont) {
							return;
						}
					  	cfrm.submitForm(cont);
					  },
					  indicator:  el.next('span.indicator'),
					  parameters: 'ajax_used=1'
					});
					var sel = el.next('select.filter');
					if(sel) {
						sel.observe('change', function (e) {
							var cfrm = e.element().up('form'), cont = cfrm.next('.e-container');
							if(cfrm && cont && e.element().value != '___reset___') {
								e.stop();
								cfrm.submitForm(cont);
								return;
							}
							e107Helper.selectAutoSubmit(e.element());
						});
					}
				});
		");

		return $text;
	}

	// FIXME - use e_form::batchoptions(), nice way of buildig batch dropdown - news administration show_batch_options()
	function renderBatch($allow_delete = false)
	{
		$fields = $this->getController()->getFields();
		if(!varset($fields['checkboxes']))
		{
			return '';
		}
		// TODO - core ui-batch-option class!!! REMOVE INLINE STYLE!
		$text = "
			<div class='buttons-bar left'>
         		<img src='".e_IMAGE_ABS."generic/branchbottom.gif' alt='' class='icon action' />
				".$this->select_open('etrigger_batch', array('class' => 'tbox select batch e-autosubmit reset', 'id' => false))."
					".$this->option(LAN_BATCH_LABEL_SELECTED, '')."
					".($allow_delete ? $this->option(LAN_DELETE, 'delete', false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"')) : '')."
					".$this->renderBatchFilter('batch')."
				".$this->select_close()."
				".$this->admin_button('e__execute_batch', 'e__execute_batch', 'batch e-hide-if-js', 'Execute', array('id' => false))."
			</div>
		";
		
		return $text;
	}

	// TODO - do more
	function renderBatchFilter($type='batch', $selected = '') // Common function used for both batches and filters.
	{
		$optdiz = array('batch' => LAN_BATCH_LABEL_PREFIX.'&nbsp;', 'filter'=> LAN_FILTER_LABEL_PREFIX.'&nbsp;');
		$table = $this->getController()->getTableName();
		$text = '';
		$textsingle = '';

		foreach($this->getController()->getFields() as $key=>$val)
		{
			if(!varset($val[$type]))
			{
				continue;
			}

			$option = array();
			$parms = vartrue($val['writeParms'], array());
			if(is_string($parms)) parse_str($parms, $parms);
			
			switch($val['type'])
			{
					case 'bool':
					case 'boolean': //TODO modify description based on $val['parm]
						$option['bool__'.$key.'__1'] = LAN_YES;
						$option['bool__'.$key.'__0'] = LAN_NO;
						if($type == 'batch')
						{
							$option['boolreverse__'.$key] = LAN_BOOL_REVERSE;
						}
					break;

					case 'templates':
					case 'layouts':
						$parms['raw'] = true;
						$val['writeParms'] = $parms;
						$tmp = $this->renderElement($key, '', $val);
						foreach ($tmp as $k => $name)
						{
							$option[$key.'__'.$k] = $name;
						}
					break;

					case 'dropdown': // use the array $parm;
						if(!is_array(varset($parms['__options']))) parse_str($parms['__options'], $parms['__options']);
						$opts = $parms['__options'];
						if(vartrue($opts['multiple'])) 
						{
							// no batch support for multiple, should have some for filters soon
							continue; 
						}
						unset($parms['__options']); //remove element options if any
						foreach($parms as $k => $name)
						{
							$option[$key.'__'.$k] = $name;
						}
					break;
					
					case 'lanlist': // use the array $parm;
						if(!is_array(varset($parms['__options']))) parse_str($parms['__options'], $parms['__options']);
						$opts = $parms['__options'];
						if(vartrue($opts['multiple'])) 
						{
							// no batch support for multiple, should have some for filters soon
							continue; 
						}
						$options = e107::getLanguage()->getLanSelectArray();
						foreach($options as $code => $name)
						{
							$option[$key.'__'.$code] = $name;
						}
					break;

					case 'datestamp': // use $parm to determine unix-style or YYYY-MM-DD
					    //TODO last hour, today, yesterday, this-month, last-month etc.
					/*	foreach($val['parm'] as $k=>$name)
						{
							$text .= $frm->option($name, $type.'__'.$key."__".$k);
						}*/
					break;

					case 'userclass':
					//case 'userclasses':
						$classes = e107::getUserClass()->uc_required_class_list(vartrue($parms['classlist'], ''));
						foreach($classes as $k => $name)
						{
							$option[$key.'__'.$k] = $name;
						}
					break;

					case 'method':
						$method = $key; 
						$list = call_user_func_array(array($this, $method), array('', $type, $parms));
						
						if(is_array($list))
						{
							//check for single option
							if(isset($list['singleOption']))
							{
								$textsingle .= $list['singleOption'];
								continue;
							}
							// non rendered options array
							foreach($list as $k => $name)
							{
								$option[$key.'__'.$k] = $name;
							}
						}
						elseif(!empty($list)) //optgroup, continue
						{
							$text .= $list;
							continue;
						}
					break;
					
					case 'user': // TODO - User Filter				
						//$option[$key.'__'.$k] = $name;	
					break;
			}

			if(count($option) > 0)
			{
				$text .= "\t".$this->optgroup_open($optdiz[$type].defset($val['title'], $val['title']), varset($disabled))."\n";
				foreach($option as $okey=>$oval)
				{
					$text .= $this->option($oval, $okey, $selected == $okey)."\n";
				}
				$text .= "\t".$this->optgroup_close()."\n";
			}
		}

		return $textsingle.$text;

	}

	public function getElementId()
	{
		$controller = $this->getController();
		return str_replace('_', '-', ($controller->getPluginName() == 'core' ? 'core-'.$controller->getTableName() : 'plugin-'.$controller->getPluginName()));
	}

	/**
	 * @return e_admin_ui
	 */
	public function getController()
	{
		return $this->_controller;
	}
}


include_once(e107::coreTemplatePath('admin_icons'));

/**
 * TODO:
 * 1. [DONE - a good start] move abstract peaces of code to the proper classes
 * 2. [DONE - at least for alpha release] remove duplicated code (e_form & e_admin_form_ui), refactoring
 * 3. make JS Manager handle Styles (.css files and inline CSS)
 * 4. [DONE] e_form is missing some methods used in e_admin_form_ui
 * 5. [DONE] date convert needs string-to-datestamp auto parsing, strptime() is the solution but needs support for
 * 		Windows and PHP < 5.1.0 - build custom strptime() function (php_compatibility_handler.php) on this -
 * 		http://sauron.lionel.free.fr/?page=php_lib_strptime (bad license so no copy/paste is allowed!)
 * 6. [DONE - read/writeParms introduced ] $fields[parms] mess - fix it, separate list/edit mode parms somehow
 * 7. clean up/document all object vars (e_admin_ui, e_admin_dispatcher)
 * 8. [DONE hopefully] clean up/document all parameters (get/setParm()) in controller and model classes
 * 9. [DONE] 'ip' field type - convert to human readable format while showing/editing record
 * 10. draggable (or not?) ordering (list view)
 * 11. [DONE] realtime search filter (typing text) - like downloads currently
 * 12. [DONE] autosubmit when 'filter' dropdown is changed (quick fix?)
 * 13. tablerender captions
 * 14. [DONE] textareas auto-height
 * 15. [DONE] multi JOIN table support (optional), aliases
 * 16. tabs support (create/edit view)
 * 17. tree list view (should handle cases like Site Links admin page)
 */