<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
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
if (!defined('e107_INIT')){ exit; }  
 
 
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
			$this->_id = preg_replace('/[^\w\-:\.]/', '', $this->_request_qry[$this->_id_key]);
		}

		$this->_posted_qry =& $_POST; //raw?

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
			unset($_GET[$key]);
			return $this;
		}

		$this->_request_qry[$key] = $value;
		$_GET[$key] = $value;
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
	 * @return string
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
	 * @param boolean $keepSpecial don't exclude special vars as 'mode' and 'action'
	 * @return string url encoded query string
	 */
	public function buildQueryString($merge_with = array(), $encode = true, $exclude_from_query = '', $keepSpecial = true)
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
				if($keepSpecial && $var != $this->_action_key && $var != $this->_mode_key) unset($ret[$var]);
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
	function getTitle($namespace = 'default', $reset = false, $glue = '  ')
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

		$glue = deftrue('SEP',' - '); // Defined by admin theme. // admin-ui used only by bootstrap. 

		return implode($glue, $content);
		// return $head. implode($glue, $content).$foot;
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
		//TODO generic $_GET to activate for any page of admin. 
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
	protected $modes = array();
	
	/**
	 * Optional - access restrictions per action 
	 * Access array in format (similar to adminMenu)
	 * 'MODE/ACTION' => e_UC_* (userclass constant, or custom userclass ID if dynamically set) 
	 *
	 * @var array
	 */
	protected $access = array();
	
	/**
	 * Optional - generic entry point access restriction (via getperms()) 
	 * Value of this for plugins would be always 'P'.
	 * When an array is detected, route mode/action = admin perms is used. (similar to $access)
	 * More detailed access control is granted with $access and $modes[MODE]['perm'] or  $modes[MODE]['userclass'] settings
	 *
	 * @var string|array
	 */
	protected $perm;

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
	 * Note that 'perm' and 'userclass' restrictions are inherited from the $modes, $access and $perm, so you don't have to set that vars if 
	 * you don't need any additional 'visual' control.
	 * All valid key-value pair (see e107::getNav()->admin function) are accepted.
	 * @var array
	 */
	protected $adminMenu = array();
	

	protected $adminMenuIcon = null;
	/**
	 * Optional (set by child class).
	 * Page titles for pages not in adminMenu (e.g. main/edit)
	 * Format array(mod/action => Page Title)
	 * @var string
	 */
	protected $pageTitles = array(
		'main/edit' => LAN_MANAGE,
	);

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
	public function __construct($auto_observe = true, $request = null, $response = null)
	{
		// we let know some admin routines we are in UI mod - related with some legacy checks and fixes
		if(!defined('e_ADMIN_UI'))
		{
			define('e_ADMIN_UI', true);
		}

		if(!empty($_GET['iframe']))
		{
			define('e_IFRAME', true);
		}

		require_once(e_ADMIN.'boot.php');
		
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



		// current user does not have access to default route, so find a new one.
		if(!$hasAccess = $this->hasRouteAccess($this->defaultMode.'/'.$this->defaultAction))
		{
			if($newRoute = $this->getApprovedAccessRoute())
			{
				list($this->defaultMode,$this->defaultAction) = explode('/',$newRoute);
			}
		}


		$request->setDefaultMode($this->defaultMode)->setDefaultAction($this->defaultAction);

		// register itself
		e107::setRegistry('admin/ui/dispatcher', $this);
		
		// permissions and restrictions
		$this->checkAccess();

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
	
	public function checkAccess()
	{
		$request = $this->getRequest();
		$currentMode = $request->getMode();

		// access based on mode setting - general controller access
		if(!$this->hasModeAccess($currentMode))
		{
			$request->setAction('e403');
			e107::getMessage()->addError(LAN_NO_PERMISSIONS)
				->addDebug('Mode access restriction triggered.');
			return false;
		}
		
		// access based on $access settings - access per action
		$currentAction = $request->getAction();
		$route = $currentMode.'/'.$currentAction;



		if(!$this->hasRouteAccess($route))
		{
			$request->setAction('e403');
			e107::getMessage()->addError(LAN_NO_PERMISSIONS)
				->addDebug('Route access restriction triggered:'.$route);
			return false;
		}
		
		return true;
	}
	
	public function hasModeAccess($mode)
	{
		// mode userclass (former check_class())
		if(isset($this->modes[$mode]['userclass']) && !e107::getUser()->checkClass($this->modes[$mode]['userclass'], false))
		{
			return false;
		}
		// mode admin permission (former getperms())
		if(isset($this->modes[$mode]['perm']) && !e107::getUser()->checkAdminPerms($this->modes[$mode]['perm']))
		{
			return false;
		}
		
		// generic dispatcher admin permission  (former getperms())
		if(null !== $this->perm && is_string($this->perm) && !e107::getUser()->checkAdminPerms($this->perm))
		{
			return false;
		}

		return true;
	}
	
	public function hasRouteAccess($route)
	{
		if(isset($this->access[$route]) && !e107::getUser()->checkClass($this->access[$route], false))
		{
			return false;
		}

		if(is_array($this->perm) && isset($this->perm[$route]) && !e107::getUser()->checkAdminPerms($this->perm[$route]))
		{
			return false;
		}


		return true;
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
	 * Search through access for an approved route.
	 * Returns false if no approved route found.
	 *
	 * @return string|bool
	 */
	private function getApprovedAccessRoute()
	{
		if(empty($this->access))
		{
			return false;
		}

		foreach($this->access as $route=>$uclass)
		{
			if(check_class($uclass))
			{
				return $route;
			}
		}

		return false;
	}

	/**
	 * Get admin menu array
	 * @return array
	 */
	public function getMenuData()
	{
		return $this->adminMenu;
	}
	
	/**
	 * Get admin menu array
	 * @return array
	 */
	public function getPageTitles()
	{
		return $this->pageTitles;
	}

	/**
	 * Get admin menu array
	 * @return array
	 */
	public function getMenuAliases()
	{
		return $this->adminMenuAliases;
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
	 * @param boolean $run_header see runObservers()
	 * @param boolean $return see runPage()
	 * @return string|array current admin page body
	 */
	public function run($run_header = true, $return = 'render')
	{
		return $this->runObservers()->runPage($return);
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
	 * Get perms
	 * @return array|string
	 */
	public function getPerm()
	{
		return $this->perm;
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
			// Known controller (found in e_admin_dispatcher::$modes), class not found exception
			else
			{
				// TODO - admin log
				// get default controller
				$this->_current_controller = $this->getDefaultController();
				// add messages
				e107::getMessage()->add('Can\'t find class <strong>&quot;'.($class_name ? $class_name : 'n/a').'&quot;</strong> for controller <strong>&quot;'.ucfirst($request->getModeName()).'&quot;</strong>', E_MESSAGE_ERROR)
					->add('Requested: '.e_REQUEST_SELF.'?'.$request->buildQueryString(), E_MESSAGE_DEBUG);
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
		// Not known controller (not found in e_admin_dispatcher::$modes) exception
		else
		{
			// TODO - admin log
			$this->_current_controller = $this->getDefaultController();
			// add messages
			e107::getMessage()->add('Can\'t find class for controller <strong>&quot;'.ucfirst($request->getModeName()).'&quot;</strong>', E_MESSAGE_ERROR)
				->add('Requested: '.e_REQUEST_SELF.'?'.$request->buildQueryString(), E_MESSAGE_DEBUG);
			// go to not found page
			$request->setMode($this->getDefaultControllerName())->setAction('e404');
			$this->_current_controller->setRequest($request)->init();
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

			if(isset($val['perm']) && $val['perm']!=='' && !getperms($val['perm']))
			{
				continue;
			}

			$tmp = explode('/', trim($key, '/'), 3);

			// sync with mode/route access
			if(!$this->hasModeAccess($tmp[0]) || !$this->hasRouteAccess($tmp[0].'/'.varset($tmp[1])))
			{
				continue;
			}

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
							$qry = (isset($val['query'])) ? $val['query'] : '?mode='.$tmp[0].'&amp;action='.$tmp[1];
						$v = $tp->replaceConstants($v, 'abs').$qry;
					break;

					case 'uri':
						$k2 = 'link';
						$v = $tp->replaceConstants($v, 'abs');

						if(!empty($v) && (e_REQUEST_URI === $v))
						{
							$selected = $key;
						}

					break;


					case 'badge': // array('value'=> int, 'type'=>'warning');
						$k2 = 'badge';
						$v = (array) $v;
					break;

					default:
						$k2 = $k;
						
					break;
				}

				

				// Access check done above
				// if($val['perm']!= null) // check perms
				// {
					// if(getperms($val['perm']))
					// {
						// $var[$key][$k2] = $v;
					// }
				// }
				// else
				{
					$var[$key][$k2] = $v;
				
				}

			}
		
			
			
			// TODO slide down menu options?
			if(!vartrue($var[$key]['link']))
			{
				$var[$key]['link'] = e_REQUEST_SELF.'?mode='.$tmp[0].'&amp;action='.$tmp[1]; // FIXME - URL based on $modes, remove url key
			}

				
			if(varset($val['tab']))
			{
				$var[$key]['link'] .= "&amp;tab=".$val['tab'];	
			}

			/*$var[$key]['text'] = $val['caption'];
			$var[$key]['link'] = (vartrue($val['url']) ? $tp->replaceConstants($val['url'], 'abs') : e_SELF).'?mode='.$tmp[0].'&action='.$tmp[1];
			$var[$key]['perm'] = $val['perm'];	*/
			if(!empty($val['modal']))
			{
				$var[$key]['link_class'] = ' e-modal';
				if(!empty($val['modal-caption']))
				{
					$var[$key]['link_data'] = array('data-modal-caption' => $val['modal-caption']);
				}

			}

		}


		if(empty($var)) return '';

		$request = $this->getRequest();
		if(!$selected) $selected = $request->getMode().'/'.$request->getAction();
		$selected = vartrue($this->adminMenuAliases[$selected], $selected);

		$icon = '';

		if(!empty($this->adminMenuIcon))
		{
			$icon = e107::getParser()->toIcon($this->adminMenuIcon);
		}
		elseif(deftrue('e_CURRENT_PLUGIN'))
		{
			$icon = e107::getPlug()->load(e_CURRENT_PLUGIN)->getIcon(24);
		}

		return e107::getNav()->admin($icon."<span>".$this->menuTitle."</span>", $selected, $var);
	}


	/**
	 * Render Help Text in <ul> format. XXX TODO
	 */
	function renderHelp()
	{


		
	}

	
	/** 
	 * Check for table issues and warn the user. XXX TODO 
	 * ie. user is using French interface but no french tables found for the current DB tables. 
	 */
	function renderWarnings()
	{
		
		
		
		
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
	 * @var string default trigger action.
	 */
	protected $_default_trigger = 'auto';
	
	/**
	 * List (numerical array) of only allowed for this controller actions
	 * Useful to grant access for certain pre-defined actions only
	 * XXX - we may move this in dispatcher (or even having it also there), still searching the most 'friendly' way
	 * @var array
	 */
	protected $allow = array();
	
	/**
	 * List (numerical array) of only disallowed for this controller actions
	 * Useful to restrict access for certain pre-defined actions only
	 * XXX - we may move this in dispatcher (or even having it also there), still searching the most 'friendly' way
	 * @var array
	 */
	protected $disallow = array();

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
			
		$this->checkAccess();

		$this->_log(); // clear the log (when debug is enabled)

	}

	/**
	 * Check against allowed/disallowed actions
	 * FIXME check plugin admin access (check_class(P)), confirm e-token is verified
	 */
	public function checkAccess()
	{
		$request = $this->getRequest();
		$currentAction = $request->getAction();

		// access based on mode setting - general controller access
		if(!empty($this->disallow) && in_array($currentAction, $this->disallow))
		{
			$request->setAction('e403');
			e107::getMessage()->addError(LAN_NO_PERMISSIONS)
				->addDebug('Controller action disallowed restriction triggered.');
			return false;
		}
		
		// access based on $access settings - access per action
		if(!empty($this->allow) && !in_array($currentAction, $this->allow))
		{
			$request->setAction('e403');
			e107::getMessage()->addError(LAN_NO_PERMISSIONS)
				->addDebug('Controller action not in allowed list restriction triggered.');
			return false;
		}
		return true;
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
	 * Get current dispatcher object
	 * @return e_admin_dispatcher
	 */
	public function getDispatcher()
	{
		return e107::getRegistry('admin/ui/dispatcher');
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
	 * @param string $title if boolean true - current menu caption will be used
	 * @param boolean $meta add to meta as well
	 * @return object e_admin_controller
	 */
	public function addTitle($title = true, $meta = true)
	{
		
		
		if(true === $title)
		{
			$_dispatcher = $this->getDispatcher();
			$data = $_dispatcher->getPageTitles();
			$search = $this->getMode().'/'.$this->getAction();



			if(isset($data[$search]))
			{
				 $res['caption'] = $data[$search];
			}
			else 
			{


				$data = $_dispatcher->getMenuData();

				if(isset($data[$search]))
				{
					 $res = $data[$search];
				}
				else
				{
					// check for an alias match.
					$d = $_dispatcher->getMenuAliases();
					if(isset($d[$search]))
					{
						$search = $d[$search];
						$res = $data[$search];

					}
					else
					{
						 return $this;
					}
				//	var_dump($d);
				//	var_dump("Couldnt find: ".$search);

				}
			}
			$title = $res['caption'];


		}
		
		//	echo "<h3>".__METHOD__." - ".$title."</h3>";
	
	//	print_a($title);
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
	public function addMetaTitle($title=null)
	{
		if($title === null)
		{
			return $this;
		}

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
	public function addHeader($content=null)
	{
		if($content === null)
		{
			return $this;
		}

		$this->getResponse()->addHeaderContent(vartrue($content));
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
			$this->_log("Skipping ".$method."() (not found)");
			$this->getRequest()->setAction($this->getDefaultAction());
		}

		// switch to 404 if needed
		$method = $this->toMethodName($this->getRequest()->getActionName(), 'page');
		if(!method_exists($this, $method))
		{
			$this->_log("Skipping ".$method."() (not found)");
			$this->getRequest()->setAction('e404');
			$message = e107::getParser()->lanVars(LAN_UI_404_METHOD_ERROR, $method, true);
			e107::getMessage()->add($message, E_MESSAGE_ERROR);
		}
	}

	/**
	 * Log Controller when e_DEBUG is active.
	 * @param string|null $message
	 * @return null
	 */
	protected function _log($message=null)
	{
		if(!deftrue('e_DEBUG'))
		{
			return null;
		}

		if($message === null) // clear the log.
		{
			file_put_contents(e_LOG."adminUI.log", '');
			return null;
		}

		$date = (!empty($message)) ? date('c') : '';

		file_put_contents(e_LOG."adminUI.log",$date."\t".$message."\n",FILE_APPEND);

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
			$this->_log("Executing ".$actionObserverName."()");
			$this->$actionObserverName();
		}
		else
		{
			$this->_log("Skipping ".$actionObserverName."() (not found)");
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
	//	print_a($response);
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
			$this->_log("Skipping ".$actionName."() (not found)");
			e107::getMessage()->add('Action '.$actionName.' no found!', E_MESSAGE_ERROR);
			return $response;
		}
		else
		{
			$this->_log("Executing ".$actionName."()");
		}
		
		if($action != 'Prefs' && $action != 'Create' && $action !='Edit' && $action != 'List') // Custom Page method in use, so add the title.
		{
			$this->addTitle();
		}


	//	e107::getDebug()->log("Admin-ui Action: <b>".$action."</b>");




		
		
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
		return '<div class="center">'.LAN_UI_404_BODY_ERROR.'</div>';
	}


	public function E404AjaxPage()
	{
		exit;
	}
	

	public function E403Observer()
	{
		$this->getResponse()->setTitle(LAN_UI_403_TITLE_ERROR);
	}

	public function E403Page()
	{
		return '<div class="center">'.LAN_UI_403_BODY_ERROR.'</div>';
	}


	public function E403AjaxPage()
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
		if(!$path) $path = e_REQUEST_SELF;
		
		//prevent cache
		header('Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	//	header('Pragma: no-cache');

		$url = $path.'?'.$request->buildQueryString($merge_query, false, $exclude_query);
		// Transfer all messages to session
		e107::getMessage()->moveToSession();
		// write session data
		session_write_close();

		// do redirect
		e107::redirect($url);
	//	header('Location: '.$url);
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
			if(vartrue($modes[$mode]) && vartrue($modes[$mode]['url']))
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



	public function getDefaultTrigger()
	{
		return $this->_default_trigger;

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
	 * Set default trigger
	 * @param string|array $triggers 'auto' or array of triggers
	 * @example $triggers['submit'] = array(LAN_UPDATE, 'update', $model->getId());
				$triggers['submit'] = array(LAN_CREATE, 'create', 0);
				$triggers['cancel'] = array(LAN_CANCEL, 'cancel');
	 * @return e_admin_controller
	 */
	public function setDefaultTrigger($triggers)
	{
		$this->_default_trigger = $triggers;
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

	protected $table;
	/**
	 * @var array UI field data
	 */

	protected $listQry;

	protected $pid;

	protected $fields = array();

	/**
	 * @var array default fields activated on List view
	 */
	protected $fieldpref = array();

	/**
	 * Custom Field (User) Preferences Name. (for viewable columns)
	 * @var string
	 */
	protected $fieldPrefName = '';

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
	 * Array of fields detected from listQry which are JOINs
	 * @example returns array('user_name'=>'u.user_name'); from $listQry = "SELECT n.*,u.user_name FROM #news...."etc.
	 */
	protected $joinField = array();


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
	 * @var string event name
	 * base event trigger name to be used. Leave blank for no trigger. 
	 */
	protected $eventName = null;

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
	 * @var string SQL group-by field name (optional)
	 */
	protected $listGroup = null;
	
	/**
	 * @var string field containing the order number
	 */
	protected $sortField = null;

	/**
	 * @var string field containing the order number
	 */
	protected $treePrefix = null;

	/**
	 * @var string field containing the parent field
	 */
	protected $sortParent = null;
	
	/**
	 * @var int reorder step
	 */
	protected $orderStep = 1;
	
	/**
	 * Example: array('0' => 'Tab label', '1' => 'Another label');
	 * Referenced from $field property per field - 'tab => xxx' where xxx is the tab key (identifier)
	 * @var array edit/create form tabs
	 */
	protected $tabs = array();
	
	/**
	 * Example: array('0' => 'Tab label', '1' => 'Another label');
	 * Referenced from $prefs property per field - 'tab => xxx' where xxx is the tab key (identifier)
	 * @var array edit/create form tabs
	 */
	protected $preftabs = array();
	
	/**
	 * TODO Example: 
	 * Contains required data for auto-assembling URL from every record
	 * For greater control - override url() method
	 * @var array
	 */
	protected $url = array();
	
	/**
	 * TODO Example: 
	 * Contains required data for mapping featurebox fields
	 * @var array
	 */
	protected $featurebox = array();

	/**
	 * Structure same as TreeModel parameters used for building the load() SQL
	 * @var additional SQL to be applied when auto-building the list query
	 */
	protected $listQrySql = array();
	
	/**
	 * @var Custom Filter SQL Query override.
	 */
	protected $filterQry = null;

	/**
	 * @var boolean
	 */
	protected $batchDelete = true;
	
	/**
	 * @var boolean
	 */
	protected $batchCopy = false;
	
    /**
     * @var boolean
     */
    protected $batchLink = false;
	
    /**
     * @var boolean
     */
    protected $batchFeaturebox = false;

	 /**
     * @var boolean
     */
    protected $batchExport = false;

	/**
	 * @var array
	 */
	protected $batchOptions = array();
	
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
	 * Data for grid layout.
	 * @var array
	 */
	protected $grid = array();
	
		/**
	 * @var e_admin_model
	 */
	protected $formQuery = false; // custom form post query

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
	
	/**
	 * Prevent parsing table aliases more than once
	 * @var boolean
	 */
	protected $_alias_parsed = false;

	/**
	 * @var bool
	 */
	protected $afterSubmitOptions = true;

	public function getAfterSubmitOptions()
	{
		return $this->afterSubmitOptions;
	}

	public function getBatchDelete()
	{
		return $this->batchDelete;
	}
	
	public function getBatchCopy()
	{
		return $this->batchCopy;
	}
    
    
    public function getBatchLink()
    {
        return $this->batchLink;
    }


    public function getBatchFeaturebox()
    {
        return $this->batchFeaturebox;
    }

	public function getBatchExport()
    {
        return $this->batchExport;
    }

	public function getBatchOptions()
	{
		return $this->batchOptions;
	}


	/**
	 * @return string
	 */
	public function getEventName()
	{
		return  $this->eventName;
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
	 * Get Sort Field data
	 * @return string
	 */
	public function getSortField()
	{
		return $this->sortField;
	}

	/**
	 * Get Sort Field data
	 * @return string
	 */
	public function getSortParent()
	{
		return $this->sortParent;
	}



		/**
	 * Get Sort Field data
	 * @return string
	 */
	public function getTreePrefix()
	{
		return $this->treePrefix;
	}
	
	/**
	 * Get Tab data
	 * @return array
	 */
	public function getTabs()
	{
		return $this->tabs;
	}

	public function addTab($key,$val)
	{
		$this->tabs[$key] = (string) $val;
	}

	/**
	 * Get Tab data
	 * @return array
	 */
	public function getPrefTabs()
	{
		return $this->preftabs;
	}

    /**
     * Get URL profile
     * @return array
     */
    public function getUrl()
    {
        return $this->url;
    }
	

      /**
     * Get Featurebox Copy 
     * @return array
     */
    public function getFeaturebox()
    {
        return $this->featurebox;
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
	 * @param string|array $field
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

		if($this->getAction() === 'grid')
		{
			if($this->getGrid('carousel') === true)
			{
				return 0;
			}

			return $this->getGrid('perPage');
		}


		return $this->perPage;
	}

	public function getGrid($key=null)
	{
		if($key !== null)
		{
			return $this->grid[$key];
		}

		return $this->grid;
	}


	public function getFormQuery()
	{
		return $this->formQuery;
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

		$name = (!empty($this->fieldPrefName)) ? strtolower($this->pluginName."_".$this->fieldPrefName) : $this->getTableName();

		e107::getMessage()->addDebug("Loading Field Preferences using name: ".$name);
		$this->_log("Loading Field Preferences using name: ".$name);
		return e107::getUser()->getPref('admin_cols_'.$name, array());
	}

	/**
	 * Set column preference array
	 * @return boolean success
	 */
	public function setUserPref($new, $name='')
	{
		//global $user_pref;
		//e107::getUser()->getConfig()->setData($new);
		//$user_pref['admin_cols_'.$this->getTableName()] = $new;
		//$this->fieldpref = $new;
		//return save_prefs('user');
		if(!empty($new))
        {
		    $this->fieldpref = $new;
        }

		if(empty($name))
		{
			$name = $this->getTableName();
		}
		else
		{
			$name = strtolower($this->pluginName."_".$name);
		}

        $msg = "Saving User Field preferences using name: ".$name;
		e107::getMessage()->addDebug($msg);
		$this->_log($msg);

		return e107::getUser()->getConfig()
			->set('admin_cols_'.$name, $new)
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
	 * Alias for getModel()->get and getListModel()->get().
	 * May be used inside field-method in read/write mode.
	 *
	 * @param string $key
	 * @return mixed|null - current value of the chosen db field.
	 */
	public function getFieldVar($key = null)
	{
		if(empty($key))
		{
			return null;
		}

		if($this->getAction() === 'list' || $this->getAction() === 'grid')
		{
			$obj = $this->getListModel();
			if(is_object($obj))
			{
				return $obj->get($key);
			}

			return null;
		}

		return $this->getModel()->get($key);

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

	public function getIfTableAlias($prefix = false, $quote = false) //XXX May no longer by useful. see joinAlias()
	{
		$alias = $this->getTableName(true);
		if($alias)
		{
			return $alias;
		}
		return ( !$quote ? $this->getTableName(false, $prefix) : '`'.$this->getTableName(false, $prefix).'`' );
	}

	/**
	 * Get join table data - XXX DEPRECATE?
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

	public function setJoinData($table, $data) //XXX - DEPRECATE?
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
	 * Get ordered models by their parents
	 * add extra
	 * @lonalore
	 * @return e_admin_tree_model
	 */
	public function getTreeModelSorted()
	{
		$tree = $this->getTreeModel();

		$parentField = $this->getSortParent();
		$orderField = $this->getSortField();

		$arr = array();
		/**
		 * @var  $id
		 * @var e_tree_model $model
		 */
		foreach ($tree->getTree() as $id => $model)
		{
			$parent = $model->get($parentField);
			$order = $model->get($orderField);

			$model->set('_depth', '9999'); // include extra field in output, just as the MySQL function did.


			$arr[$id] = $model;
		}


	//	usort($arr); array_multisort() ?

		$tree->setTree($arr,true); // set the newly ordered tree.

	//	var_dump($arr);

		return $this->_tree_model;
	}


	/**
	 * @lonalore - found online.
	 * @param string $idField       The item's ID identifier (required)
	 * @param string $parentField   The item's parent identifier (required)
	 * @param array $els            The array (required)
	 * @param int   $parentID       The parent ID for which to sort (internal)
	 * @param array $result         The result set (internal)
	 * @param int   $depth          The depth (internal)
	 * @return array
	 */
	function parentChildSort_r($idField, $parentField, $els=array(), $parentID = 0, &$result = array(), &$depth = 0)
	{
	    foreach ($els as $key => $value)
	    {
	        if ($value[$parentField] == $parentID)
	        {
	            $value['depth'] = $depth;
	            array_push($result, $value);
	            unset($els[$key]);
	            $oldParent = $parentID;
	            $parentID = $value[$idField];
	            $depth++;
	            $this->parentChildSort_r($idField,$parentField, $els, $parentID, $result, $depth);
	            $parentID = $oldParent;
	            $depth--;
	        }
	    }

	    return $result;
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
	 * @param $val
	 */
	public function setBatchDelete($val)
	{
		$this->batchDelete = $val;
		return $this;
	}



	/**
	 * @param $val
	 */
	public function setBatchCopy($val)
	{
		$this->batchCopy = $val;
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
	 * @return null
	 */
	public function manageColumns()
	{
		$cols = array();
		$posted = $this->getPosted('e-columns', array());
		foreach ($this->getFields() as $field => $attr)
		{
			if((/*vartrue($attr['forced']) || */ in_array($field, $posted)) && !vartrue($attr['nolist']))
			{
				$cols[] = $field;
				continue;
			}
		}

		// Alow for an empty array to be saved also, to reset to default.
	    if($this->getPosted('etrigger_ecolumns', false)) // Column Save Button
		{
			$this->setUserPref($cols, $this->fieldPrefName);
			e107::getMessage()->addDebug("User Field Preferences Saved: ".print_a($cols,true));
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
		$trigger = $tp->toDB(explode('__', $batch_trigger));

		if(!empty($selected))
		{
			foreach ($selected as $i => $_sel) 
			{
				$selected[$i] = preg_replace('/[^\w\-:.]/', '', $_sel);
			}
		}

		// XXX An empty selection should always be permitted for custom batch methods which may apply changes to all records, not only selected ones.


		if(substr($batch_trigger, 0, 6) === 'batch_')
		{
			list($tmp,$plugin,$command) = explode("_",$batch_trigger,3);
			$this->setPosted(array());
			$this->getRequest()->setAction('batch');
			$cls = e107::getAddon($plugin,'e_admin',true);
			e107::callMethod($cls,'process',$this,array('cmd'=>$command,'ids'=>$selected));
			return $this;
		}


		$this->setTriggersEnabled(false); //disable further triggering

		$actionName = $this->getRequest()->getActionName();

		if($actionName === 'Grid')
		{
			$actionName = 'List';
		}


		switch($trigger[0])
		{

			case 'sefgen':
				$field = $trigger[1];
				$value = $trigger[2];

				//handleListBatch(); for custom handling of all field names
				if(empty($selected)) return $this;
				$method = 'handle'.$actionName.'SefgenBatch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected, $field, $value);
				}
			break;


			case 'export':
				if(empty($selected)) return $this;
				$method = 'handle'.$actionName.'ExportBatch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected);
				}

			break;

			case 'delete':
				//method handleListDeleteBatch(); for custom handling of 'delete' batch
				// if(empty($selected)) return $this;
				// don't check selected data - subclass need to check additional post variables(confirm screen)

				if(empty($selected) && !$this->getPosted('etrigger_delete_confirm')) // it's a delete batch, confirm screen
				{
					$params = $this->getFieldAttr($trigger[1], 'writeParms', array());
					if(!is_array($params)) parse_str($params, $params);
					if(!vartrue($params['batchNoCheck']))
					{
						return $this;
					}
				}

				$method = 'handle'.$actionName.'DeleteBatch';
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
				$method = 'handle'.$actionName.'BoolBatch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected, $field, $value);
				}
			break;

			case 'boolreverse':
				if(empty($selected)) return $this;
				$field = $trigger[1];
				//something like handleListBoolreverseBatch(); for custom handling of 'boolreverse' batch
				$method = 'handle'.$actionName.'BoolreverseBatch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected, $field);
				}
			break;
			
			// see commma, userclasses batch options
			case 'attach':
			case 'deattach':
			case 'addAll':
			case 'clearAll':
				if(empty($selected)) return $this;
				$field = $trigger[1];
				$value = $trigger[2];
				
				if($trigger[0] === 'addAll')
				{
					$parms = $this->getFieldAttr($field, 'writeParms', array());
					if(!is_array($parms)) parse_str($parms, $parms);
					unset($parms['__options']);
					$value = $parms;
					if(empty($value)) return $this;
					if(!is_array($value)) $value = array_map('trim', explode(',', $value));
				}
				
				if(method_exists($this, 'handleCommaBatch')) 
				{
					$this->handleCommaBatch($selected, $field, $value, $trigger[0]);
				}
			break;
			
			// append to userclass list
			case 'ucadd':
			case 'ucremove':
				if(empty($selected)) return $this;
				$field = $trigger[1];
				$class = $trigger[2];
				$user = e107::getUser();
				$e_userclass = e107::getUserClass(); 
				
				// check userclass manager class
				if (!isset($e_userclass->class_tree[$class]) || !$user->checkClass($e_userclass->class_tree[$class]))
				{
					return $this;
				}

				if(method_exists($this, 'handleCommaBatch')) 
				{
					$trigger[0] = $trigger[0] === 'ucadd' ? 'attach' : 'deattach';
					$this->handleCommaBatch($selected, $field, $class, $trigger[0]);
				}
			break;
			
			// add all to userclass list
			// clear userclass list
			case 'ucaddall':
			case 'ucdelall':
				if(empty($selected)) return $this;
				$field = $trigger[1];
				$user = e107::getUser();
				$e_userclass = e107::getUserClass(); 
				$parms = $this->getFieldAttr($field, 'writeParms', array());
				if(!is_array($parms)) parse_str($parms, $parms);
				if(!vartrue($parms['classlist'])) return $this;
				
				$classes = $e_userclass->uc_required_class_list($parms['classlist']);
				foreach ($classes as $id => $label) 
				{
					// check userclass manager class
					if (!isset($e_userclass->class_tree[$id]) || !$user->checkClass($e_userclass->class_tree[$id]))
					{
						$msg = $tp->lanVars(LAN_NO_ADMIN_PERMISSION,$label);
						$this->getTreeModel()->addMessageWarning($msg);
						unset($classes[$id],$msg);
					}
				}
				if(method_exists($this, 'handleCommaBatch'))
				{
					$this->handleCommaBatch($selected, $field, array_keys($classes), $trigger[0] === 'ucdelall' ? 'clearAll' : 'addAll');
				}
			break;

			// handleListCopyBatch etc.
			default:
				$field = $trigger[0];
				$value = $trigger[1];

				//something like handleListUrlTypeBatch(); for custom handling of 'url_type' field name
				$method = 'handle'.$actionName.$this->getRequest()->camelize($field).'Batch';

				e107::getMessage()->addDebug("Searching for custom batch method: ".$method."(".$selected.",".$value.")");

				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($selected, $value);
					break;
				}

				//handleListBatch(); for custom handling of all field names
				//if(empty($selected)) return $this;
				$method = 'handle'.$actionName.'Batch';
				e107::getDebug()->log("Checking for batch method: ".$method);
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
	 * @param string $filter_value
	 * @return array field -> value
	 */
	protected function _parseFilterRequest($filter_value)
	{
		$tp = e107::getParser();
		if(!$filter_value || $filter_value === '___reset___')
		{
			return array();
		}
		$filter = (array) $tp->toDB(explode('__', $filter_value));
		$res = array();
		switch($filter[0])
		{
			case 'bool':
				// direct query
				$res = array($filter[1], $filter[2]);
				$this->_log("listQry Filtered by ".$filter[1]." (".($filter[2] ? 'true': 'false').")");
			break;
			
			case 'datestamp':
							
				//XXX DO NOT TRANSLATE THESE VALUES!
				$dateConvert = array(
					"hour"	=> "1 hour ago",
					"day"	=> "24 hours ago",
					"week"	=> "1 week ago",
					"month"	=> "1 month ago",
					"month3"	=> "3 months ago",
					"month6"	=> "6 months ago",
					"month9"	=> "9 months ago",
					"year"	=> "1 year ago",
					"nhour"	=> "now + 1 hour",
					"nday"	=> "now + 24 hours",
					"nweek"	=> "now + 1 week",
					"nmonth"	=> "now + 1 month",
					"nmonth3"	=> "now + 3 months",
					"nmonth6"	=> "now + 6 months",
					"nmonth9"	=> "now + 9 months",
					"nyear"	=> "now + 1 year",
				);
				
				$ky = $filter[2];
				$time = vartrue($dateConvert[$ky]);
				$timeStamp = strtotime($time);
				
				$res = array($filter[1], $timeStamp);

				$this->_log("listQry Filtered by ".$filter[1]." (".$time.")");
				
			break;

			default:
				//something like handleListUrlTypeFilter(); for custom handling of 'url_type' field name filters
				$method = 'handle'.$this->getRequest()->getActionName().$this->getRequest()->camelize($filter[0]).'Filter';
				$args = array_slice($filter, 1);

				e107::getMessage()->addDebug("Searching for custom filter method: ".$method."(".implode(', ', $args).")");


				if(method_exists($this, $method)) // callback handling
				{
					//return $this->$method($filter[1], $selected); selected?
					// better approach - pass all values as method arguments
					// NOTE - callbacks are allowed to return QUERY as a string, it'll be added in the WHERE clause

					e107::getMessage()->addDebug('Executing filter callback <strong>'.get_class($this).'::'.$method.'('.implode(', ', $args).')</strong>');

					return call_user_func_array(array($this, $method), $args);
				}
				else // default handling
				{
					$res = array($filter[0], $filter[1]);
					$this->_log("listQry Filtered by ".$filter[0]." (".$filter[1].")");
				}
			break;
		}

		//print_a($res);
		//exit;

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
			
				case 'password': //TODO more encryption options. 
					if(strlen($value) < 30) // expect a non-md5 value if less than 32 chars. 
					{
						$value = md5($value);
					}
					
				break;	
			
			
				case 'datestamp':
					if(!is_numeric($value))
					{
						if(!empty($attributes['writeParms']))
						{
							if(is_string($attributes['writeParms']))
							{
								parse_str($attributes['writeParms'],$opt);
							}
							elseif(is_array($attributes['writeParms']))
							{
								$opt = $attributes['writeParms'];
							}
						}

						
						$format = $opt['type'] ? ('input'.$opt['type']) : 'inputdate';
						$value = trim($value) ? e107::getDate()->toTime($value, $format) : 0;
					}
				break;

				case 'ip': // TODO - ask Steve if this check is required
					//if(strpos($value, '.') !== FALSE)
					{
						$value = trim($value) ? e107::getIPHandler()->ipEncode($value) : '';
					}
				break;

				case 'dropdown': // TODO - ask Steve if this check is required
				case 'lanlist':
				case 'userclasses':
				case 'comma':
				case 'checkboxes':
					if(is_array($value))
					{
						// no sanitize here - data is added to model posted stack
						// and validated & sanitized before sent to db
						//$value = array_map(array(e107::getParser(), 'toDB'), $value);
						$value = implode(',', $value);
					}
				break;
				
				case 'images':
				case 'files':
		
				//	XXX Cam @ SecretR: didn't work here. See model_class.php line 2046. 
				// if(!is_array($value))
			//		{
				//		$value = e107::unserialize($value);	
				//	}
				break;
				
	
			}
/*
			if($attributes['serialize'] == true)
			{
				$attributes['data'] = 'array';		
			}

			if($attributes['data'] != 'array')
			{
				$value = e107::unserialize($value);	
			}
*/
	
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
		if(e_AJAX_REQUEST) return;
		
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
				$this->redirectAction(preg_replace('/[^\w\-:.]/', '', $choice[0]), vartrue($choice[1]), vartrue($choice[2]));
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
			$this->_log("Filter ListQry: ".$qry);
			//file_put_contents(e_LOG.'uiAjaxResponseSQL.log', $qry."\n\n", FILE_APPEND);

			// Make query
			$sql = e107::getDb();
			if($qry && $sql->gen($qry, $debug))
			{
				while ($res = $sql->fetch())
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
	 * Given an alias such as 'u' or 'n.news_datestamp' -  will return the associated table such as 'user' or 'news'
	 */
	function getTableFromAlias($alias)
	{
		if(strpos($alias,".")!==false)
		{
			list($alias,$tmp) = explode(".",$alias,2);	
		}
				
		$tmp = array_flip($this->joinAlias);
		return vartrue($tmp[$alias]);			
	}

	function getJoinField($field)
	{
		return isset($this->joinField[$field]) ? $this->joinField[$field] : false; // vartrue($this->joinField[$field],false);
	}

	/**
	 * Parses all available field data, adds internal attributes for handling join requests
	 * @return e_admin_controller_ui
	 */
	protected function parseAliases()
	{
		if($this->_alias_parsed) return $this; // already parsed!!!

		$this->joinAlias(); // generate Table Aliases from listQry
		
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
		

		if(empty($this->fields))
		{
			$this->_alias_parsed = true;
			return $this;
		}


		// check for table & field aliases
		$fields = array(); // preserve order
		foreach ($this->fields as $field => $att)
		{
			// fieldAlias.fieldName // table name no longer required as it's included in listQry. (see joinAlias() )
			if(strpos($field, '.') !== false) // manually entered alias.
			{
				$tmp = explode('.', $field, 2);
				$table = $this->getTableFromAlias($tmp[0]);
				$att['table'] = $table;
				$att['alias'] = $tmp[0];
				$att['field'] = $tmp[1];
				$att['__tableField'] = $field;
				$att['__tablePath'] = $att['alias'].'.';
				$att['__tableFrom'] = "`#".$table."`.".$tmp[1];//." AS ".$att['alias'];
				$field = $att['alias'] ? $tmp[1] : $tmp[0];

				$fields[$field] = $att;
				unset($tmp);
			}
			else
			{

				$att['table'] = $this->getIfTableAlias(false);
				
				if($newField = $this->getJoinField($field)) // Auto-Detect. 
				{
					$table = $this->getTableFromAlias($newField); // Auto-Detect. 
					$att['table'] = $table;
					$att['alias'] = $newField;
					$att['__tableField'] = $newField;	
					// $att['__tablePath'] = $newField; ????!!!!!
					$att['__tableFrom'] = "`#".$table."`.".$field;//." AS ".$newField;
				}			
				elseif(isset($this->joinAlias[$this->table]) && $field !='checkboxes' && $field !='options')
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
		//		$fields[$field]['__tableField'] = $this->getJoinData($fields[$field]['table'], '__tablePath').$field;
			}
			/*
			if($fields[$field]['table'])
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
			}
			*/
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
		if($this->listQry)
		{
			preg_match_all("/`?#([\w-]+)`?\s*(as|AS)\s*([\w-])/im",$this->listQry,$matches);
			$keys = array();
			foreach($matches[1] AS $k=>$v)
			{
				if(varset($matches[3][$k]) && !array_key_exists($v, $this->joinAlias))
				{
					$this->joinAlias[$v] = $matches[3][$k]; // array. eg $this->joinAlias['core_media'] = 'm';
				}
				
				$keys[] = $matches[3][$k];
			}
			
			foreach($keys as $alias)
			{
				preg_match_all("/".$alias."\.([\w]*)/i",$this->listQry,$match);
				foreach($match[1] as $k=>$m)
				{
					$this->joinField[$m] = $match[0][$k];		
				}					
			}
			
		}
		elseif($this->tableJoin)
		{
			foreach ($this->tableJoin as $tbl => $data) 
			{
				$matches = explode('.', $tbl, 2);
				$this->joinAlias[$matches[1]] = $matches[0]; // array. eg $this->joinAlias['core_media'] = 'm';
				//'user_name'=>'u.user_name'
				if(isset($data['fields']) && $data['fields'] !== '*')
				{
					$tmp = explode(',', $data['fields']);
					foreach ($tmp as $field) 
					{
						$this->joinField[$field] = $matches[0].'.'.$field;
					}
				}
			}
		}
		
	}

	/**
	 * Quick fix for bad custom $listQry; 
	 */
	protected function parseCustomListQry($qry)
	{
		if(E107_DEBUG_LEVEL == E107_DBG_SQLQUERIES)
		{
			e107::getMessage()->addDebug('Using Custom listQry ');	
		}
			
		if(strpos($qry,'`')===false && strpos($qry, 'JOIN')===false) 
		{
			$ret = preg_replace("/FROM\s*(#[\w]*)/","FROM `$1`", $qry);  // backticks missing, so add them. 
						
			if($ret)
			{
				e107::getMessage()->addDebug('Your $listQry is missing `backticks` around the table name! It should look like this'. print_a($ret,true)); 
				return $ret; 	
			}
		}
		
		return $qry; 
	}

	/**
	 * Fix search string by replacing the commonly used '*' wildcard
	 * with the mysql represenation of it '%' and '?' with '_' (single character)
	 *
	 * @param string $search
	 * @return string
	 */
	protected function fixSearchWildcards($search)
	{
		$search = trim($search);
		if (empty($search))
		{
			return '';
		}

		// strip wildcard on the beginning and the end
		while (substr($search, 0, 1) == '*') $search = substr($search, 1);
		while (substr($search, -1) == '*') $search = substr($search, 0, -1);

		// replace "*" wildcard with mysql wildcard "%"
		return str_replace(array('*', '?'), array('%', '_'), $search);
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

		$this->listQry = $listQry;

		$filterOptions = $request->getQuery('filter_options', '');

		$searchQuery = $this->fixSearchWildcards($tp->toDB($request->getQuery('searchquery', '')));
		$searchFilter = $this->_parseFilterRequest($filterOptions);

		$listQry = $this->listQry; // check for modification during parseFilterRequest();

		if(E107_DEBUG_LEVEL == E107_DBG_SQLQUERIES)
		{
			e107::getMessage()->addDebug('searchQuery: <b>'.$searchQuery.'</b>'); 
		}

		if($searchFilter && is_array($searchFilter))
		{

			list($filterField, $filterValue) = $searchFilter;
			
			if($filterField && $filterValue !== '' && isset($this->fields[$filterField]))
			{
				$_dataType = $this->fields[$filterField]['data'];
				$_fieldType = $this->fields[$filterField]['type'];

				if($_fieldType === 'comma' || $_fieldType === 'checkboxes' || $_fieldType === 'userclasses' || ($_fieldType === 'dropdown' && !empty($this->fields[$filterField]['writeParms']['multiple'])))
				{
					 $_dataType = 'set';
				}

				switch ($_dataType)
				{
					case 'set':
						$searchQry[] = "FIND_IN_SET('".$tp->toDB($filterValue)."', ".$this->fields[$filterField]['__tableField'].")";
					break;
					
					case 'int':
					case 'integer':
						if($_fieldType === 'datestamp') // Past Month, Past Year etc.
						{
							if($filterValue > time())
							{
								$searchQry[] = $this->fields[$filterField]['__tableField']." > ".time();
								$searchQry[] = $this->fields[$filterField]['__tableField']." < ".intval($filterValue);
							}
							else
							{
								$searchQry[] = $this->fields[$filterField]['__tableField']." > ".intval($filterValue);
								$searchQry[] = $this->fields[$filterField]['__tableField']." < ".time();
							}

						}
						else 
						{
							$searchQry[] = $this->fields[$filterField]['__tableField']." = ".intval($filterValue);	
						}		
					break;
					
					
					
					default: // string usually. 

						if($filterValue === '_ISEMPTY_')
						{
							$searchQry[] = $this->fields[$filterField]['__tableField']." = '' ";
						}

						else
						{

							if($_fieldType === 'method') // More flexible filtering.
							{

								$searchQry[] = $this->fields[$filterField]['__tableField']." LIKE \"%".$tp->toDB($filterValue)."%\"";		
							}
							else
							{

								$searchQry[] = $this->fields[$filterField]['__tableField']." = '".$tp->toDB($filterValue)."'";	
							}
						}
						
						//exit;
					break;
				}
				
			}
				//echo 'type= '. $this->fields[$filterField]['data'];
					//	print_a($this->fields[$filterField]);
		}
		elseif($searchFilter && is_string($searchFilter))
		{
			
			// filter callbacks could add to WHERE clause
			$searchQry[] = $searchFilter;
		}

		if(E107_DEBUG_LEVEL == E107_DBG_SQLQUERIES)
		{
			e107::getMessage()->addDebug(print_a($searchQry,true));
		}

		$className = get_class($this);

		// main table should select everything
		$tableSFieldsArr[] = $tablePath.'*';
		foreach($this->getFields() as $key => $var)
		{
			// disabled or system
			if((!empty($var['nolist']) && empty($var['filter'])) || empty($var['type']) || empty($var['data']))
			{
				continue;
			}

			// select FROM... for main table
			if(!empty($var['alias']) && !empty($var['__tableField']))
			{
				$tableSFieldsArr[] = $var['__tableField'];
			}

			// filter for WHERE and FROM clauses
			$searchable_types = array('text', 'textarea', 'bbarea', 'url', 'ip', 'tags', 'email', 'int', 'integer', 'str', 'string', 'number'); //method? 'user',
			
			if($var['type'] === 'method' && !empty($var['data']) && ($var['data'] === 'string' || $var['data'] === 'str' || $var['data'] === 'int'))
			{
				$searchable_types[] = 'method';
			}
			
			if(trim($searchQuery) !== '' && in_array($var['type'], $searchable_types) && $var['__tableField'])
			{
				// Search for customer filter handler.
				$cutomerSearchMethod = 'handle'.$this->getRequest()->getActionName().$this->getRequest()->camelize($key).'Search';
				$args = array($tp->toDB($request->getQuery('searchquery', '')));

				e107::getMessage()->addDebug("Searching for custom search method: ".$className.'::'.$cutomerSearchMethod."(".implode(', ', $args).")");

				if(method_exists($this, $cutomerSearchMethod)) // callback handling
				{
					e107::getMessage()->addDebug('Executing custom search callback <strong>'.$className.'::'.$cutomerSearchMethod.'('.implode(', ', $args).')</strong>');

					$filter[] = call_user_func_array(array($this, $cutomerSearchMethod), $args);
					continue;
				}


				if($var['data'] === 'int' || $var['data'] === 'integer' ||  $var['type'] === 'int' || $var['type'] === 'integer')
				{
					if(is_numeric($searchQuery))
					{
						$filter[] = $var['__tableField']." = ".$searchQuery;
					}
					continue;
				}

				if($var['type'] === 'ip')
				{
					$ipSearch = e107::getIPHandler()->ipEncode($searchQuery);
					if(!empty($ipSearch))
					{
						$filter[] = $var['__tableField']." LIKE '%".$ipSearch."%'";
					}
					// Continue below for BC check also.
				}


				if(strpos($searchQuery, " ") !==false) // search multiple words across fields.
				{
					$tmp = explode(" ", $searchQuery);

					if(count($tmp) < 4) // avoid excessively long query.
					{
						foreach($tmp as $splitSearchQuery)
						{
							if(!empty($splitSearchQuery))
							{
								$filter[] = $var['__tableField']." LIKE '%".$splitSearchQuery."%'";
							}
						}
					}
					else
					{
						$filter[] = $var['__tableField']." LIKE '%".$searchQuery."%'";
					}

				}
				else
				{
					$filter[] = $var['__tableField']." LIKE '%".$searchQuery."%'";
				}


				if($isfilter)
				{
					$filterFrom[] = $var['__tableField'];

				}
			}
		}


		if(strpos($filterOptions,'searchfield__') === 0) // search in specific field, so remove the above filters.
		{
			$filter = array(); // reset filter.
		}


		if(E107_DEBUG_LEVEL == E107_DBG_SQLQUERIES)
		{
		//	e107::getDebug()->log(print_a($filter,true));
			// e107::getMessage()->addInfo(print_a($filter,true));
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
					elseif($fields)
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
		else    // default listQry
		{
			if(!empty($listQry))
			{
				$qry = $this->parseCustomListQry($listQry);
			}
			elseif($this->sortField && $this->sortParent && !deftrue('e_DEBUG_TREESORT')) // automated 'tree' sorting.
			{
			//	$qry = "SELECT SQL_CALC_FOUND_ROWS a. *, CASE WHEN a.".$this->sortParent." = 0 THEN a.".$this->sortField." ELSE b.".$this->sortField." + (( a.".$this->sortField.")/1000) END AS treesort FROM `#".$this->table."` AS a LEFT JOIN `#".$this->table."` AS b ON a.".$this->sortParent." = b.".$this->pid;
				$qry                = $this->getParentChildQry(true);
				//$this->listOrder	= '_treesort '; // .$this->sortField;
			//	$this->orderStep    = ($this->orderStep === 1) ? 100 : $this->orderStep;
			}
			else
			{
				$qry = "SELECT SQL_CALC_FOUND_ROWS ".$tableSFields." FROM ".$tableFrom;
			}

		}

		// group field - currently auto-added only if there are joins
		$groupField = '';
		if($joins && $this->getPrimaryName())
		{
			$groupField = $tablePath.$this->getPrimaryName();
		}

		// appended to GROUP BY when true.
		if(!empty($this->listGroup))
		{
			$groupField = $this->listGroup;
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

			$orderField = $request->getQuery('field', $this->getDefaultOrderField());

			$rawData['tableFrom'] = $tableSFieldsArr;
			$rawData['joinsFrom'] = $tableSJoinArr;
			$rawData['joins'] = $joins;
			$rawData['groupField'] = $groupField;
			$rawData['orderField'] = isset($this->fields[$orderField]) ? $this->fields[$orderField]['__tableField'] : '';
			$rawData['orderType'] = $request->getQuery('asc') === 'desc' ? 'DESC' : 'ASC';
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
			// add more where details on the fly via $this->listQrySql['db_where'];
			$qry .= (strripos($qry, 'where')==FALSE) ? " WHERE " : " AND "; // Allow 'where' in custom listqry
			$qry .= implode(" AND ", $searchQry);

			// Disable tree (use flat list instead) when filters are applied
			// Implemented out of necessity under https://github.com/e107inc/e107/issues/3204
			// Horrible hack, but only needs this one line of additional code
			$this->getTreeModel()->setParam('sort_parent', null);
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
				$qry .= ' ORDER BY '.$this->fields[$orderField]['__tableField'].' '.(strtolower($orderDef) === 'desc' ? 'DESC' : 'ASC');
			}
		}
		
		if(isset($this->filterQry)) // custom query on filter. (see downloads plugin)
		{
			$qry = $this->filterQry;
		}
		
		if($this->getPerPage() || false !== $forceTo)
		{
			$from = false === $forceFrom ? intval($request->getQuery('from', 0)) : intval($forceFrom);
			if(false === $forceTo) $forceTo = $this->getPerPage();
			$qry .= ' LIMIT '.$from.', '.intval($forceTo);
		}

		// Debug Filter Query.
		if(E107_DEBUG_LEVEL == E107_DBG_SQLQUERIES)
		{
			e107::getMessage()->addDebug('QRY='.str_replace('#', MPREFIX, $qry));
		}
	//	 echo $qry.'<br />';	
	// print_a($this->fields);	
	
		$this->_log('listQry: '.str_replace('#', MPREFIX, $qry));

		return $qry;
	}


	/**
	 * Return a Parent/Child SQL Query based on sortParent and sortField variables
	 *
	 * Note: Since 2018-01-28, the queries were replaced with pure PHP sorting. See:
	 *       https://github.com/e107inc/e107/issues/3015
	 *
	 * @param bool|false $orderby - include 'ORDER BY' in the qry.
	 * @return string
	 */
	public function getParentChildQry($orderby=false)
	{
		return "SELECT SQL_CALC_FOUND_ROWS * FROM `#".$this->getTableName()."` ";
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
	protected function _manageSubmit($callbackBefore = '', $callbackAfter = '', $callbackError = '', $noredirectAction = '', $forceSave=false)
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
				$model->setPostedData($_posted, null, false);
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

	//	$model->addMessageDebug(print_a($_posted,true));
	//	$model->addMessageDebug(print_a($this,true));

		// - Autoincrement sortField on 'Create'.


		// Prevent parent being assigned as self.
		if(!empty($this->sortParent) && $this->getAction() === 'edit' && ($model->getId() == $_posted[$this->sortParent] ) )
		{
			$vars = array(
				'x'=> $this->getFieldAttr($this->sortParent,'title'),
				'y'=> $this->getFieldAttr($this->pid,'title'),
			);

			$message = e107::getParser()->lanVars(LAN_UI_X_CANT_EQUAL_Y, $vars);
			$model->addMessageWarning($message);
			$model->setMessages();
			$this->getUI()->addWarning($this->sortParent);
			return false;
		}




		if(($this->getAction() === 'create') && !empty($this->sortField) && empty($this->sortParent) && empty($_posted[$this->sortField])  )
		{

			$incVal = e107::getDb()->max($this->table, $this->sortField) + 1;
			$_posted[$this->sortField] = $incVal;
		//	$model->addMessageInfo(print_a($_posted,true));
		}

		// Trigger Admin-ui event.  'pre'
		if($triggerName = $this->getEventTriggerName($_posted['etrigger_submit'])) // 'create' or 'update'; 
		{
			$id = $model->getId();
			$eventData = array('newData'=>$_posted,'oldData'=>$old_data,'id'=> $id);
			$model->addMessageDebug('Admin-ui Trigger fired: <b>'.$triggerName.'</b>');
			$this->_log('Triggering Event: '.$triggerName. " (before)");
			if(E107_DBG_ALLERRORS >0 )
			{
				$model->addMessageDebug($triggerName.' data: '.print_a($eventData,true));
			}

			if($halt = e107::getEvent()->trigger($triggerName, $eventData))
			{
				$model->setMessages();
				return false; 
			}	
		}


		// Scenario I - use request owned POST data - toForm already executed
		$model->setPostedData($_posted, null, false) // insert() or update() dbInsert();
			->save(true, $forceSave);



	//	if(!empty($_POST))
		{

		}
			
		// Scenario II - inner model sanitize
		//$this->getModel()->setPosted($this->convertToData($_POST, null, false, true);

		// Take action based on use choice after success
		if(!$this->getModel()->hasError())
		{
			// callback (if any)
			$new_data 		= $model->getData();
			$id 			= $model->getId();

			e107::getAddonConfig('e_admin',null,'process', $this, $id);

			// Trigger Admin-ui event. 'post' 
			if($triggerName = $this->getEventTriggerName($_posted['etrigger_submit'],'after')) // 'created' or 'updated';
			{
				unset($_posted['etrigger_submit'], $_posted['__after_submit_action'], $_posted['submit_value'], $_posted['e-token']);

				$pid = $this->getPrimaryName();
				$_posted[$pid] = $id; 	// add in the primary ID field.
				$eventData = array('newData'=>$_posted,'oldData'=>$old_data,'id'=> $id); // use $_posted as it may include unsaved data.
				$model->addMessageDebug('Admin-ui Trigger fired: <b>'.$triggerName.'</b>');
				$this->_log('Triggering Event: '.$triggerName." (after)");
				if(E107_DBG_ALLERRORS >0 )
				{
					$model->addMessageDebug($triggerName.' data: '.print_a($eventData,true));
				}
				e107::getEvent()->trigger($triggerName, $eventData);	
			}
			
			if($callbackAfter && method_exists($this, $callbackAfter))
			{
				$this->$callbackAfter($new_data, $old_data, $id);
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


	/**
	 *  Return a custom event trigger name
	 * @param null $type  Usually 'Create' or 'Update'
	 * @param string $when ' before or after
	 * @return bool|string
	 */
	public function getEventTriggerName($type=null, $when='before')
	{
		$plug = $this->getEventName();
		
		if(empty($plug) || empty($type))
		{
			return false; 
		}

		if($when === 'after')
		{
			$type .= 'd'; // ie. 'created' or 'updated'.
		}
		
		return 'admin_'.strtolower($plug).'_'.strtolower($type); 

	}
}

class e_admin_ui extends e_admin_controller_ui
{

	protected $fieldTypes = array();
	protected $dataFields = array();
	protected $fieldInputTypes = array();
	protected $validationRules = array();

	protected $table;
	protected $pid;
	protected $listQry;
	protected $editQry;
	protected $sortField;
	protected $sortParent;
	protected $orderStep;
	protected $treePrefix;


	/**
	 * Markup to be auto-inserted before List filter
	 * @var string
	 */
	public $preFilterMarkup = '';

	/**
	 * Markup to be auto-inserted after List filter
	 * @var string
	 */
	public $postFilterMarkup = '';

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
	 * Confirm screen custom message
	 * @var string
	 */
	public $deleteConfirmMessage = null;



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

	/*	$ufieldpref = $this->getUserPref();
		if($ufieldpref)
		{
			$this->fieldpref = $ufieldpref;
		}*/

		$this->addTitle($this->pluginTitle, true)->parseAliases();

		$this->initAdminAddons();


		if($help = $this->renderHelp())
		{
			if(!empty($help))
			{
				e107::setRegistry('core/e107/adminui/help',$help);
			}
		}


	}


	private function initAdminAddons()
	{
		$tmp = e107::getAddonConfig('e_admin', null, 'config', $this);

		if(empty($tmp))
		{
			return;
		}

		$opts = null;

		foreach($tmp as $plug=>$config)
		{

			$form = e107::getAddon($plug, 'e_admin', $plug."_admin_form"); // class | false.

			if(!empty($config['fields']))
			{
				if(!empty($this->fields['options']))
				{
					$opts = $this->fields['options'];
					unset($this->fields['options']);
				}

				foreach($config['fields'] as $k=>$v)
				{
					$v['data'] = false; // disable data-saving to db table. .

					$fieldName = 'x_'.$plug.'_'.$k;
					e107::getDebug()->log($fieldName." initiated by ".$plug);

					if($v['type'] === 'method' && method_exists($form,$fieldName))
					{
						$v['method'] = $plug."_admin_form::".$fieldName;
						//echo "Found method ".$fieldName." in ".$plug."_menu_form";
						//echo $form->$fieldName();
					}


					$this->fields[$fieldName] = $v; // ie. x_plugin_key

				}

				if(!empty($opts)) // move options field to the end.
				{
					$this->fields['options'] = $opts;
				}
			}

			if(!empty($config['batchOptions']))
			{
				$opts = array();
				foreach($config['batchOptions'] as $k=>$v)
				{
					$fieldName = 'batch_'.$plug.'_'.$k;

					$opts[$fieldName] = $v; // ie. x_plugin_key

				}

				$batchCat = deftrue('LAN_PLUGIN_'.strtoupper($plug).'_NAME', $plug);
				$this->batchOptions[$batchCat] = $opts;

			}

			if(!empty($config['tabs']))
			{
				foreach($config['tabs'] as $t=>$tb)
				{
					$this->tabs[$t] = $tb;
				}
			}


		}




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
	 * Detect if a batch function has been fired.
	 * @param $batchKey
	 * @return bool
	 */
	public function batchTriggered($batchKey)
	{
		return (!empty($_POST['e__execute_batch']) && (varset($_POST['etrigger_batch']) == $batchKey));
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
	 * Catch batch submit
	 * @param string $batch_trigger
	 * @return none
	 */
	public function GridBatchTrigger($batch_trigger)
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
		
		$tp = e107::getParser();
		
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
				$selected = explode(',', $this->getPosted('delete_confirm_value'));
				foreach ($selected as $i => $_sel) 
				{
					$selected[$i] = preg_replace('/[^\w\-:.]/', '', $_sel);
				}
			}
		}

		// delete one by one - more control, less performance
		// pass  afterDelete() callback to tree delete method
		$set_messages = true;
		$delcount = 0;
		$nfcount = 0;
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
					if($check) $delcount++;
					if(!$this->afterDelete($data, $id, $check))
					{
						$set_messages = false;
					}
				}
			}
			else
			{
				$set_messages = true;	
				$nfcount++; 
			}
		}

		//$this->getTreeModel()->delete($selected);
		if($set_messages) 
		{
			$this->getTreeModel()->setMessages();
			// FIXME lan
			if($delcount) e107::getMessage()->addSuccess($tp->lanVars(LAN_UI_DELETED, $delcount, true));
			if($nfcount) e107::getMessage()->addError($tp->lanVars(LAN_UI_DELETED_FAILED, $nfcount,true));
		}

		//$this->redirect();
	}

	/**
	 * Batch copy trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListCopyBatch($selected)
	{
		// Batch Copy

		$res = $this->getTreeModel()->copy($selected);
		// callback
		$this->afterCopy($res, $selected);
		// move messages to default stack 
		$this->getTreeModel()->setMessages();
		// send messages to session
		e107::getMessage()->moveToSession();
		// redirect
		$this->redirect();
	}


	/**
	 * Batch Export trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListExportBatch($selected)
	{
		// Batch Copy
		$res = $this->getTreeModel()->export($selected);
		// callback
	//	$this->afterCopy($res, $selected);
		// move messages to default stack
		$this->getTreeModel()->setMessages();
		// send messages to session
		e107::getMessage()->moveToSession();
		// redirect
		$this->redirect();
	}


		/**
	 * Batch Export trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListSefgenBatch($selected, $field, $value)
	{

		$tree = $this->getTreeModel();
		$c= 0;
		foreach($selected as $id)
        {
        	if(!$tree->hasNode($id))
        	{
        		e107::getMessage()->addError('Item #ID '.htmlspecialchars($id).' not found.');
        		continue;
        	}

        	$model = $tree->getNode($id);

        	$name = $model->get($value);

        	$sef = eHelper::title2sef($name,'dashl');





        	$model->set($field, $sef);


        	$model->save();

        	$data = $model->getData();

        	if($model->isModified())
	        {
	           	$this->getModel()->setData($data)->save(false,true);
		        $c++;
	        }
        }



		$caption = e107::getParser()->lanVars(LAN_UI_BATCH_BOOL_SUCCESS, $c, true);
		e107::getMessage()->addSuccess($caption);

	//	e107::getMessage()->moveToSession();
		// redirect
	//	$this->redirect();
	}



    /** 
     * Batch URL trigger
     * @param array $selected
     * @return void
     */
    protected function handleListUrlBatch($selected)
    {
        if($this->_add2nav($selected))
		{
			e107::getMessage()->moveToSession();
			$this->redirect();
		}
    }


	/** TODO
	 * Batch Featurebox Transfer
	 * @param array $selected
	 * @return void
	 */
	protected function handleListFeatureboxBatch($selected)
	{
		 if($this->_add2featurebox($selected))
		{
			e107::getMessage()->moveToSession();
			$this->redirect();
		}
	}
	
	protected function _add2nav($selected)
	{
		if(empty($selected)) return false;// TODO warning message
		
		if(!is_array($selected)) $selected  = array($selected);

        $sql        = e107::getDb();
		$urlData	= $this->getUrl();
		$allData 	= $this->getTreeModel()->url($selected, array('sc' => true), true);

        e107::getMessage()->addDebug('Using Url Route:'.$urlData['route']);   
        
		$scount = 0;
        foreach($allData as $id => $data)
        {
            $name = $data['name'];
            $desc = $data['description'];
            
            $link = $data['url'];
            
            $link = str_replace('{e_BASE}', "", $link); // TODO temporary here, discuss
            
            // _FIELD_TYPES auto created inside mysql handler now
            $linkArray = array(
                'link_name'         => $name, 
                'link_url'          => $link,
                'link_description'  => e107::getParser()->toDB($desc), // retrieved field type is string, we might need todb here
                'link_button'       => '',
                'link_category'     => 255, // Using an unassigned template rather than inactive link-class, since other inactive links may already exist. 
                'link_order'        => 0,
                'link_parent'       => 0,
                'link_open'         => '',
                'link_class'        => 0,
                'link_sefurl'		=> e107::getParser()->toDB($urlData['route'].'?'.$id),
            );
            
            $res = $sql->insert('links', $linkArray);
            
            if($res !== FALSE)
            {
				e107::getMessage()->addSuccess(LAN_CREATED.": ".LAN_SITELINK.": ".($name ? $name : 'n/a'));   
				$scount++; 
            }
            else 
            {
                if($sql->getLastErrorNumber())
                {
					e107::getMessage()->addError(LAN_CREATED_FAILED.": ".LAN_SITELINK.": ".$name.": ".LAN_SQL_ERROR);
                    e107::getMessage()->addDebug('SQL Link Creation Error #'.$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
                }
				else
				{
					e107::getMessage()->addError(LAN_CREATED_FAILED.": ".LAN_SITELINK.": ".$name.": ".LAN_UNKNOWN_ERROR);//Unknown Error  
				}
            }

        }
        
		if($scount > 0)
		{
			e107::getMessage()->addSuccess(LAN_CREATED." (".$scount.") ".ADLAN_138);
			e107::getMessage()->addSuccess("<a class='btn btn-small btn-primary' href='".e_ADMIN_ABS."links.php?searchquery=&filter_options=link_category__255'>".LAN_CONFIGURE." ".ADLAN_138."</a>");
			return $scount;        
		}
        
        return false; 
 
	}

	protected function _add2featurebox($selected)
	{
		// FIX - don't allow if plugin not installed
		if(!e107::isInstalled('featurebox'))
		{
			return false;
		}
		
		if(empty($selected)) return false;// TODO warning message
		
		if(!is_array($selected)) $selected = array($selected);

        $sql        = e107::getDb();
		$tree = $this->getTreeModel();
		$urlData = $this->getTreeModel()->url($selected, array('sc' => true), false);
		$data = $this->featurebox;
		
		$scount = 0;
        foreach($selected as $id)
        {
        	if(!$tree->hasNode($id)) 
        	{
        		e107::getMessage()->addError('Item #ID '.htmlspecialchars($id).' not found.');
        		continue; // TODO message
        	} 
        	
        	$model = $tree->getNode($id);
            if($data['url'] === true)
			{
				$url = $urlData[$id];
			}
			else $url = $model->get($data['url']);
			$name = $model->get($data['name']);
			
			$category = e107::getDb()->retrieve('featurebox_category', 'fb_category_id', "fb_category_template='unassigned'");
			
            $fbArray = array (
                	'fb_title' 		=> $name, 
     				'fb_text' 		=> $model->get($data['description']), 
					'fb_image' 		=> vartrue($data['image']) ? $model->get($data['image']) : '',
					'fb_imageurl'	=> $url, 
					'fb_class' 		=> isset($data['visibility']) && $data['visibility'] !== false ? $model->get($data['visibility']) : e_UC_ADMIN,
					'fb_template' 	=> 'default',
					'fb_category' 	=> $category, // TODO popup - choose category
					'fb_order' 		=> $scount, 
            );

            $res = $sql->insert('featurebox', $fbArray);

            if($res !== FALSE)
            {
				e107::getMessage()->addSuccess(LAN_CREATED.": ".LAN_PLUGIN_FEATUREBOX_NAME.": ".($name ? $name : 'n/a'));
				$scount++; 
            }
            else
            {
                if($sql->getLastErrorNumber())
                {
					e107::getMessage()->addError(LAN_CREATED_FAILED.": ".LAN_PLUGIN_FEATUREBOX_NAME.": ".$name.": ".LAN_SQL_ERROR);
					e107::getMessage()->addDebug('SQL Featurebox Creation Error #'.$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
                }  
				else
				{
					e107::getMessage()->addError(LAN_CREATED_FAILED.": ".$name.": ".LAN_UNKNOWN_ERROR);  
				}
            }
        }
        
        if($scount > 0)
        {
			e107::getMessage()->addSuccess(LAN_CREATED." (".$scount.") ".LAN_PLUGIN_FEATUREBOX_NAME);  
			e107::getMessage()->addSuccess("<a class='btn btn-small btn-primary' href='".e_PLUGIN_ABS."featurebox/admin_config.php?searchquery=&filter_options=fb_category__{$category}'".LAN_CONFIGURE." ".LAN_PLUGIN_FEATUREBOX_NAME."</a>");
			return $scount;        
        }
        
        return false; 
 
	}













	
	/**
	 * Batch boolean trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListBoolBatch($selected, $field, $value)
	{
		$cnt = $this->getTreeModel()->batchUpdate($field, $value, $selected, $value, false);
		if($cnt)
		{
			$caption = e107::getParser()->lanVars(LAN_UI_BATCH_BOOL_SUCCESS, $cnt, true);
			$this->getTreeModel()->addMessageSuccess($caption);
		}
		$this->getTreeModel()->setMessages();
	}

	/**
	 * Batch boolean reverse trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListBoolreverseBatch($selected, $field)
	{
		$tree = $this->getTreeModel();
		$cnt = $tree->batchUpdate($field, "1-{$field}", $selected, null, false);
		if($cnt)
		{
			$caption = e107::getParser()->lanVars(LAN_UI_BATCH_REVERSED_SUCCESS, $cnt, true);
			$tree->addMessageSuccess($caption);
			//sync models
			$tree->loadBatch(true);
		}
		$this->getTreeModel()->setMessages();
	}

	public function handleCommaBatch($selected, $field, $value, $type)
	{
		$tree = $this->getTreeModel();
		$cnt = $rcnt = 0;
		$value = e107::getParser()->toDB($value);
		
		switch ($type) 
		{
			case 'attach':
			case 'deattach':
				$this->_setModel();
				foreach ($selected as $key => $id) 
				{
					$node = $tree->getNode($id);
					if(!$node) continue;
					$val = $node->get($field);
					
					if(empty($val)) $val = array();
					elseif(!is_array($val)) $val = explode(',', $val);
					
					if($type === 'deattach')
					{
						$search = array_search($value, $val);
						if(false === $search) continue;
						unset($val[$search]);
						sort($val);
						$val = implode(',', $val);
						$node->set($field, $val);
						$check = $this->getModel()->setData($node->getData())->save(false, true);
						
						if(false === $check) $this->getModel()->setMessages();
						else $rcnt++;
						continue;
					}
					
					// attach it
					if(false === in_array($value, $val))
					{
						$val[] = $value; 
						sort($val);
						$val = implode(',', array_unique($val));
						$node->set($field, $val);
						$check = $this->getModel()->setData($node->getData())->save(false, true);
						if(false === $check) $this->getModel()->setMessages();
						else $cnt++;
					}
				}
				$this->_model = null;
			break;
				
			case 'addAll':
				if(!empty($value))
				{
					if(is_array($value))
					{ 
						sort($value);	
						$value = implode(',', array_map('trim', $value));
					}
					
					$cnt = $this->getTreeModel()->batchUpdate($field, $value, $selected, true, true);
				}
				else
				{
					$this->getTreeModel()->addMessageWarning(LAN_UPDATED_FAILED)->setMessages();//"Comma list is empty, aborting."
					$this->getTreeModel()->addMessageDebug(LAN_UPDATED_FAILED.": Comma list is empty, aborting.")->setMessages();
				}
			break;
				
			case 'clearAll':
				$allowed = !is_array($value) ? explode(',', $value) : $value;
				if(!$allowed)
				{
					$rcnt = $this->getTreeModel()->batchUpdate($field, '', $selected, '', true);
				}
				else
				{
					$this->_setModel();
					foreach ($selected as $key => $id) 
					{
						$node = $tree->getNode($id);
						if(!$node) continue;
						
						$val = $node->get($field);
						
						// nothing to do
						if(empty($val)) break;
						elseif(!is_array($val)) $val = explode(',', $val);
						
						// remove only allowed, see userclass
						foreach ($val as $_k => $_v) 
						{
							if(in_array($_v, $allowed))
							{
								unset($val[$_k]);
							}
						}
						
						sort($val);
						$val = !empty($val) ? implode(',', $val) : '';
						$node->set($field, $val);
						$check = $this->getModel()->setData($node->getData())->save(false, true);
						
						if(false === $check) $this->getModel()->setMessages();
						else $rcnt++;
					}
					$this->_model = null;
				}

				// format for proper message
				$value = implode(',', $allowed);
			break;
		}

		if($cnt)
		{
			$vttl = $this->getUI()->renderValue($field, $value, $this->getFieldAttr($field));
			$caption = e107::getParser()->lanVars(LAN_UI_BATCH_UPDATE_SUCCESS, array('x'=>$vttl, 'y'=>$cnt), true);
			$this->getTreeModel()->addMessageSuccess($caption);
		}
		elseif($rcnt)
		{
			$vttl = $this->getUI()->renderValue($field, $value, $this->getFieldAttr($field));
			$caption = e107::getParser()->lanVars(LAN_UI_BATCH_DEATTACH_SUCCESS, array('x'=>$vttl, 'y'=>$cnt), true);
			$this->getTreeModel()->addMessageSuccess($caption);
		}
		$this->getTreeModel()->setMessages();
	}


	/**
	 * Method to generate "Search in Field" query.
	 * @param $selected
	 * @return string
	 */
	protected function handleListSearchfieldFilter($selected)
	{
		$string = $this->getQuery('searchquery');



		if(empty($string))
		{
			return null;
		}

		return $selected. " LIKE '%".e107::getParser()->toDB($string)."%' "; // array($selected, $this->getQuery('searchquery'));
	}

	/**
	 * Batch default (field) trigger
	 * @param array $selected
	 * @return void
	 */
	protected function handleListBatch($selected, $field, $value)
	{
		// special exceptions
		
		if($value === '#delete') // see admin->users
		{
			$val = "''";
			$value = "(empty)";	
		}	
		elseif($value === "#null")
		{
			$val = null;
			$value = "(empty)";
		}
		else
		{
			$val = "'".$value."'";	
		}
		
		if($field === 'options') // reserved field type. see: admin -> media-manager - batch rotate image.
		{
			return; 
		}




		$cnt = $this->getTreeModel()->batchUpdate($field, $val, $selected, true, false);
		if($cnt)
		{
			$vttl = $this->getUI()->renderValue($field, $value, $this->getFieldAttr($field));
			$msg = e107::getParser()->lanVars(LAN_UI_BATCH_UPDATE_SUCCESS, array('x' => $vttl, 'y' => $cnt), true);
			$this->getTreeModel()->addMessageSuccess($msg);
			// force reload the collection from DB, fix some issues as 'observer' is executed before the batch handler
			$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, false, false, $this->listQry))->loadBatch(true);
		}
		$this->getTreeModel()->setMessages();
		return $cnt;
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

		$id = intval(key($posted));
		if($this->deleteConfirmScreen && !$this->getPosted('etrigger_delete_confirm'))
		{
			// forward data to delete confirm screen
			$this->setPosted('delete_confirm_value', $id);
			return; // User confirmation expected
		}

		$this->setTriggersEnabled(false);
		$data = array();
		$model = $this->getTreeModel()->getNode($id); //FIXME - this has issues with being on a page other than the 1st. 
		if($model)
		{
			$data = $model->getData();
			if($this->beforeDelete($data, $id))
			{
				
				$eventData = array('oldData'=>$data,'id'=> $id);
				
				if($triggerName = $this->getEventTriggerName('delete')) // trigger for before. 
				{

					if(E107_DBG_ALLERRORS >0 )
					{
						$this->getTreeModel()->addMessageDebug('Admin-ui Trigger fired: <b>'.$triggerName.'</b> with data '.print_a($eventData,true));
					}

					if($halt = e107::getEvent()->trigger($triggerName, $eventData))
					{
						$this->getTreeModel()->setMessages();
						return; 
					} 	
				}
				
				$check = $this->getTreeModel()->delete($id);
				 		 
				if($this->afterDelete($data, $id, $check))
				{
					if($triggerName = $this->getEventTriggerName('deleted')) // trigger for after. 
					{
						if(E107_DBG_ALLERRORS > 0)
						{
							$this->getTreeModel()->addMessageDebug('Admin-ui Trigger fired: <b>'.$triggerName.'</b>'); //FIXME - Why doesn't this display?
						}
						e107::getEvent()->trigger($triggerName, $eventData);
					}
					
					$this->getTreeModel()->setMessages();
				}
			}
			else
			{
				$this->getTreeModel()->setMessages();// errors
			}
		}
		else  //FIXME - this is a fall-back for the BUG which causes model to fail on all list pages other than the 1st
		{ 
			//echo "Couldn't get Node for ID: ".$id;
			// exit; 
			e107::getMessage()->addDebug('Model Failure Fallback in use!! ID: '.$id.' file: '.__FILE__. " line: ".__LINE__ ,'default',true); 
			$check = $this->getTreeModel()->delete($id);
			return;			
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
		//e107::js('core','core/tabs.js','prototype');
		//e107::js('core','core/admin.js','prototype');
	}

	/**
	 * List action observer
	 * @return void
	 */
	public function ListObserver()
	{
		if($ufieldpref = $this->getUserPref())
		{
			$this->fieldpref = $ufieldpref;
		}

		$table = $this->getTableName();
		if(empty($table))
		{
			return;
		}

		$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, false, false, $this->listQry))->loadBatch();

		$this->addTitle();

		if($this->getQuery('filter_options'))
		{
		//	var_dump($this);
			// $this->addTitle("to-do"); // display filter option when active.
		}
		
	}

	/**
	 * Grid action observer
	 */
	public function GridObserver()
	{

		$table = $this->getTableName();
		if(empty($table))
		{
			return;
		}
		$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, false, false, $this->listQry))->loadBatch();
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
	 * Inline edit action
	 * @return void
	 */
	public function InlineAjaxPage()
	{
		$this->logajax("Inline Ajax Triggered");

		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		if(!vartrue($_POST['name']) || !vartrue($this->fields[$_POST['name']]))
		{
			header($protocol.': 404 Not Found', true, 404);
			header("Status: 404 Not Found", true, 404);
			echo LAN_FIELD.": ".$this->fields[$_POST['name']].": ".LAN_NOT_FOUND; // Field: x: not found!
			$this->logajax('Field not found');
			return;
		}
		
		$_name = $_POST['name'];
		$_value = $_POST['value'];
		$_token = $_POST['token'];

		$parms = $this->fields[$_name]['readParms'] ? $this->fields[$_name]['readParms'] : '';
		if(!is_array($parms)) parse_str($parms, $parms);
		if(!empty($parms['editable'])) $this->fields[$_name]['inline'] = true;
		
		if(!empty($this->fields[$_name]['noedit']) || !empty($this->fields[$_name]['nolist']) || empty($this->fields[$_name]['inline']) || empty($_token) || !password_verify(session_id(),$_token))
		{
			header($protocol.': 403 Forbidden', true, 403);
			header("Status: 403 Forbidden", true, 403);
			echo ADLAN_86; //Forbidden

			$result = var_export($this->fields[$_name], true);

			$problem = array();
			$problem['noedit'] = !empty($this->fields[$_name]['noedit']) ? 'yes' : 'no';
			$problem['nolist'] = !empty($this->fields[$_name]['nolist']) ? 'yes' : 'no';
			$problem['inline'] = empty($this->fields[$_name]['inline']) ? 'yes' : 'no';
			$problem['token'] = empty($_token) ? 'yes' : 'no';
			$problem['password'] = !password_verify(session_id(),$_token) ? 'yes' : 'no';

			$result .= "\nForbidden Caused by: ".print_r($problem,true);
			$this->logajax("Forbidden\nAction:".$this->getAction()."\nField (".$_name."):\n".$result);
			return;
		}




		
		$model = $this->getModel()->load($this->getId());
		$_POST = array(); //reset post
		$_POST[$_name] = $_value; // set current field only
		$_POST['etrigger_submit'] = 'update'; // needed for event trigger

	//	print_r($_POST);
		
		// generic handler - same as regular edit form submit

		$this->convertToData($_POST);

		$model->setPostedData($_POST, null, false);
		$model->setParam('validateAvailable', true); // new param to control validate of available data only, reset on validate event
		// Do not update here! Because $old_data and $new_data will be the same in afterUpdate() methods.
		// Data will be saved in _manageSubmit() method.
		// $model->update(true);

		if($model->hasError())
		{
			// using 400
			header($protocol.': 400 Bad Request', true, 400);
			header("Status: 400 Bad Request", true, 400);
			$this->logajax("Bad Request");
			// DEBUG e107::getMessage()->addError('Error test.', $model->getMessageStackName())->addError('Another error test.', $model->getMessageStackName());


			if(E107_DEBUG_LEVEL) $message = e107::getMessage()->get('debug', $model->getMessageStackName(), true);
			else $message = e107::getMessage()->get('error', $model->getMessageStackName(), true);
			
			if(!empty($message)) echo implode(' ', $message);
			$this->logajax(implode(' ', $message));
			return;
		}

		//TODO ? afterInline trigger?
		$res = $this->_manageSubmit('beforeUpdate', 'afterUpdate', 'onUpdateError', 'edit');
	}

	// Temporary - but useful. :-)
	public function logajax($message)
	{
		if(e_DEBUG !== true)
		{
			return;
		}

		$message = date('r')."\n".$message."\n";
		$message .= "\n_POST\n";
		$message .= print_r($_POST,true);
		$message .= "\n_GET\n";
		$message .= print_r($_GET,true);

		$message .= "---------------";
		
		file_put_contents(e_LOG.'uiAjaxResponseInline.log', $message."\n\n", FILE_APPEND);
	}
	
	
	/**
	 * Drag-n-Drop sort action
	 * @return void
	 */
	public function SortAjaxPage()
	{
		if(!isset($_POST['all']) || empty($_POST['all']))
		{
			return;
		}
		if(!$this->sortField)
		{
			echo 'Missing sort field value';
			return;
		}

		if(!empty($this->sortParent)) // Force 100 positions for child when sorting with parent/child.
		{
			$this->orderStep = 100;
		}

		
		$sql    = e107::getDb();
		$step   = $this->orderStep ? intval($this->orderStep) : 1;
		$from   = !empty($_GET['from']) ? (int) $_GET['from'] * $step : $step;

		$c = $from;
		$updated = array();

		foreach($_POST['all'] as $row)
		{

			list($tmp,$id) = explode("-", $row, 2);
			$id = preg_replace('/[^\w\-:.]/', '', $id);
			if(!is_numeric($id)) $id = "'{$id}'";
			if($sql->update($this->table, $this->sortField." = {$c} WHERE ".$this->pid." = ".$id)!==false)
			{
				$updated[] = "#".$id."  --  ".$this->sortField." = ".$c;
			}

			// echo($sql->getLastQuery()."\n");
			$c += $step;

		}


		if(!empty($this->sortParent) && !empty($this->sortField) )
		{
			return null;
		}

//	file_put_contents(e_LOG."sortAjax.log", print_r($updated,true));

		// Increment every other record after the current page of records.
	//	$changed = (intval($_POST['neworder']) * $step) + $from ;
		$changed = $c - $step;
		$qry = "UPDATE `#".$this->table."` e, (SELECT @n := ".($changed).") m  SET e.".$this->sortField." = @n := @n + ".$step." WHERE ".$this->sortField." > ".($changed);

		$result = $sql->gen($qry);


		// ------------ Fix Child Order when parent is used. ----------------
/*
		if(!empty($this->sortParent) && !empty($this->sortField) ) // Make sure there is space for at least 99
		{
			$parent = array();

			$data2 = $sql->retrieve($this->table,$this->pid.','.$this->sortField,$this->sortParent .' = 0',true);
			foreach($data2 as $val)
			{
				$id = $val[$this->pid];
				$parent[$id] = $val[$this->sortField];

			}

			$previous = 0;

			$data = $sql->retrieve($this->table,'*',$this->sortParent.' != 0 ORDER BY '.$this->sortField,true);

			foreach($data as $row)
			{
				$p = $row[$this->sortParent];

				if($p != $previous)
				{
					$c = $parent[$p];
				}

				$c++;
				$previous = $p;

				//	echo "<br />".$row['forum_name']." with parent: ".$p." old: ".$row['forum_order']."  new: ".$c;
				$sql->update($this->table, $this->sortField . ' = '.$c.' WHERE '.$this->pid.' = '.intval($row[$this->pid]).' LIMIT 1');

			}





		}
*/
		$this->afterSort($result, $_POST);

	//	e107::getLog()->addDebug(print_r($_POST,true))->toFile('SortAjax','Admin-UI Ajax Sort Log', true);
	//	 e107::getLog()->addDebug(print_r($updated,true))->toFile('SortAjax','Admin-UI Ajax Sort Log', true);
	//	e107::getLog()->addDebug($qry)->toFile('SortAjax','Admin-UI Ajax Sort Log', true);

	// eg. 	$qry = "UPDATE e107_faqs e, (SELECT @n := 249) m  SET e.faq_order = @n := @n + 1 WHERE 1";

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
	 * Generic List action page
	 * @return string
	 */
	public function GridPage()
	{
		if($this->deleteConfirmScreen && !$this->getPosted('etrigger_delete_confirm') && $this->getPosted('delete_confirm_value'))
		{
			// 'edelete_confirm_data' set by single/batch delete trigger
			return $this->getUI()->getConfirmDelete($this->getPosted('delete_confirm_value')); // User confirmation expected
		}

		return $this->getUI()->getList(null,'grid');
	}

	/**
	 * List action observer
	 * @return void
	 */
	public function ListAjaxObserver()
	{
	    if($ufieldpref = $this->getUserPref())
		{
			$this->fieldpref = $ufieldpref;
		}

		$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, 0, false, $this->listQry))->loadBatch();
	}


	/**
	 * List action observer
	 * @return void
	 */
	public function GridAjaxObserver()
	{
		$this->ListAjaxObserver();
	}

	/**
	 * Generic List action page (Ajax)
	 * @return string
	 */
	public function ListAjaxPage()
	{
		return $this->getUI()->getList(true);
	}


	public function GridAjaxPage()
	{
		return $this->getUI()->getList(true,'grid');
	}

	/**
	 * Generic Edit observer
	 */
	public function EditObserver()
	{
		$this->getModel()->load($this->getId());
		$this->addTitle();
		$this->addTitle('#'.$this->getId()); // Inform user of which record is being edited.
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
		// e107::getJs()->requireCoreLib('core/admin.js');
		e107::js('core','core/admin.js','prototype');
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
		$this->addTitle();
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
	 * @param $new_data
	 * @param $old_data
	 */
	public function beforeCreate($new_data, $old_data)
	{
	}

	/**
	 * User defined after-create logic
	 * @param $new_data
	 * @param $old_data
	 * @param $id
	 */
	public function afterCreate($new_data, $old_data, $id)
	{
	}

	/**
	 * User defined error handling, return true to suppress model messages
	 * @param $new_data
	 * @param $old_data
	 */
	public function onCreateError($new_data, $old_data)
	{
	}

	/**
	 * User defined pre-update logic, return false to prevent DB query execution
	 * @param $new_data
	 * @param $old_data
	 */
	public function beforeUpdate($new_data, $old_data, $id)
	{
	}



	/**
	 * User defined after-update logic
	 * @param $new_data 
	 * @param $old_data
	 */
	public function afterUpdate($new_data, $old_data, $id)
	{
	}

	/**
	 * User defined before pref saving logic
	 * @param $new_data
	 * @param $old_data
	 */
	public function beforePrefsSave($new_data, $old_data)
	{
	}

	/**
	 * User defined before pref saving logic
	 */
	public function afterPrefsSave()
	{

	}

	/**
    * User defined error handling, return true to suppress model messages
    */
	public function onUpdateError($new_data, $old_data, $id)
	{
	}

	/**
	 * User defined after-update logic
	 * @param mixed $result
	 * @param array $selected
	 * @return void
	 */
	public function afterCopy($result, $selected)
	{
	}


	/**
	 * User defined after-sort logic
	 * @param mixed $result
	 * @param array $selected
	 * @return void
	 */
	public function afterSort($result, $selected)
	{
	}


	/**
	 * @return string
	 */
	public function renderHelp()
	{

	}

	/**
	 * Create - send JS to page Header
	 * @return none
	 */
	function CreateHeader()
	{
		// TODO - invoke it on className (not all textarea elements)
		//e107::getJs()->requireCoreLib('core/admin.js');
		e107::js('core','core/admin.js','prototype');
	}

	/**
	 *
	 * @return string
	 */
	public function CreatePage()
	{
		return $this->getUI()->getCreate();
	}

	public function PrefsSaveTrigger()
	{
		$data = $this->getPosted();

		$beforePref = $data;
		unset($beforePref['e-token'],$beforePref['etrigger_save']);

		$tmp = $this->beforePrefsSave($beforePref, $this->getConfig()->getPref());

		if(!empty($tmp))
		{
			$data = $tmp;
		}

		foreach($this->prefs as $k=>$v) // fix for empty checkboxes - need to save a value.
		{
			if(!isset($data[$k]) && $v['data'] !== false && ($v['type'] === 'checkboxes' || $v['type'] === 'checkbox'))
			{
				$data[$k] = null;
			}
		}

		foreach($data as $key=>$val)
		{

			if(!empty($this->prefs[$key]['multilan']))
			{

				if(is_string($this->getConfig()->get($key))) // most likely upgraded to multilan=>true, so reset to an array structure.
				{
					$this->getConfig()->setPostedData($key, array(e_LANGUAGE => $val), false);
				}
				else
				{
					$lang = key($val);
					$value = $val[$lang];
					$this->getConfig()->setData($key.'/'.$lang, str_replace("'", '&#39;', $value));
				}

			}
			else
			{
				$this->getConfig()->setPostedData($key, $val, false);
			}

		}

		$this->getConfig()->save(true);

		$this->afterPrefsSave();

/*
		$this->getConfig()
			->setPostedData($this->getPosted(), null, false)
			//->setPosted('not_existing_pref_test', 1)
			->save(true);
*/

		$this->getConfig()->setMessages();

	}
	
	public function PrefsObserver()
	{
		$this->addTitle();
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
			$message = e107::getParser()->toHTML(LAN_UI_NOPID_ERROR,true);
			e107::getMessage()->add($message, E_MESSAGE_WARNING);
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
		$this->_pref = $this->pluginName === 'core' ? e107::getConfig() : e107::getPlugConfig($this->pluginName);

		if($this->pluginName !== 'core' && !e107::isInstalled($this->pluginName))
		{
			$obj = get_class($this);
			e107::getMessage()->addWarning($obj."The plugin is not installed or \$pluginName: is not valid. (".$this->pluginName. ")"); // debug only.
			return $this;
		}

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
				if($key == $this->pid && empty($att['data'])) // Set integer as default for primary ID when not specified. MySQL Strict Fix.
				{
					$this->dataFields[$key] = 'int';
					continue;
				}

				if(varset($att['type']) === 'comma' && (empty($att['data']) || empty($att['rule'])))
				{
					$att['data'] = 'set';
					$att['validate'] = 'set';
					$_parms = vartrue($att['writeParms'], array());
					if(is_string($_parms)) parse_str($_parms, $_parms);
					unset($_parms['__options']);
					$att['rule'] = $_parms;
					unset($_parms);
				}

				if(!empty($att['data']) && $att['data'] === 'array' && ($this->getAction() === 'inline')) // FIX for arrays being saved incorrectly with inline editing.
				{
					$att['data'] = 'set';
				}

				if(($key !== 'options' && false !== varset($att['data']) && null !== varset($att['type'],null) && !vartrue($att['noedit'])) || vartrue($att['forceSave']))
				{
					$this->dataFields[$key] = vartrue($att['data'], 'str');
					if(!empty($att['type']))
					{
						$this->fieldInputTypes[$key] = $att['type'];
					}
				}



			}
		}

		// TODO - do it in one loop, or better - separate method(s) -> convertFields(validate), convertFields(data),...
		if(!$this->validationRules)
		{
			$this->validationRules = array();
			foreach ($this->fields as $key => $att)
			{
				if(null === varset($att['type'], null) || vartrue($att['noedit']))
				{
					continue;
				}
				if(vartrue($att['validate']))
				{
					$this->validationRules[$key] = array((true === $att['validate'] ? 'required' : $att['validate']), varset($att['rule']), $att['title'], varset($att['error'], vartrue($att['help'])));
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
			->setUrl($this->url)
			->setValidationRules($this->validationRules)
			->setDbTypes($this->fieldTypes)
			->setFieldInputTypes($this->fieldInputTypes)
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
			->setUrl($this->url)
			->setMessageStackName('admin_ui_tree_'.$this->table)
			->setParams(array('model_class' => 'e_admin_model',
			                  'model_message_stack' => 'admin_ui_model_'.$this->table,
			                  'db_query' => $this->listQry,
			                  // Information necessary for PHP-based tree sort
			                  'sort_parent' => $this->getSortParent(),
			                  'sort_field' => $this->getSortField(),
			                  'primary_field' => $this->getPrimaryName(),
			                  ));

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
	protected $_list_view  = null;



	/**
	 * Constructor
	 * @param e_admin_controller_ui $controller
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

		if(empty($fields))
		{
			return null;
		}

		foreach($fields as $field => $foptions)
		{
			// check form custom methods
			if(vartrue($foptions['type']) === 'method' && method_exists('e_form', $field)) // check even if type is not method. - just in case of an upgrade later by 3rd-party.
			{
				$message = e107::getParser()->lanVars(LAN_UI_FORM_METHOD_ERROR, array('x'=>$field), true);
				e107::getMessage()->addError($message);
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
	 * @todo Get a 'depth/level' field working with mysql and change the 'level' accordingly
	 * @param mixed $curVal
	 * @param string $mode read|write|inline
	 * @param array $parm
	 * @return array|string
	 */
	public function treePrefix($curVal, $mode, $parm)
	{
		$controller         = $this->getController();
		$parentField        = $controller->getSortParent();
		$treePrefixField    = $controller->getTreePrefix();
		$parent 	        = $controller->getListModel()->get($parentField);
		$level              = $controller->getListModel()->get("_depth");


		if($mode === 'read')
		{

			$inline = $this->getController()->getFieldAttr($treePrefixField,'inline');

			if($inline === true)
			{
				return $curVal;
			}

			$level_image = $parent ? str_replace('level-x','level-'.$level, ADMIN_CHILD_ICON) : '';

			return ($parent) ?  $level_image.$curVal : $curVal;

		}


		if($mode === 'inline')
		{
			$ret = array('inlineType'=>'text');

			if(!empty($parent))
			{
				$ret['inlineParms'] = array('pre'=> str_replace('level-x','level-'.$level, ADMIN_CHILD_ICON));
			}


			return $ret;
		}


/*
			if($mode == 'write') //  not used.
			{
			//	return $frm->text('forum_name',$curVal,255,'size=xxlarge');
			}

			if($mode == 'filter')
			{
				return;
			}
			if($mode == 'batch')
			{
				return;
			}
*/




	}


	/**
	 * Generic DB Record Creation Form.
	 * @return string
	 */
	function getCreate()
	{
		$controller = $this->getController();
		$request = $controller->getRequest();
		if($controller->getId())
		{
			$legend = e107::getParser()->lanVars(LAN_UI_EDIT_LABEL, $controller->getId()); // sprintXXX(LAN_UI_EDIT_LABEL, $controller->getId());
			$form_start = vartrue($controller->headerUpdateMarkup);
			$form_end = vartrue($controller->footerUpdateMarkup);
		}
		else
		{
			$legend = LAN_UI_CREATE_LABEL;
			$form_start = vartrue($controller->headerCreateMarkup);
			$form_end = vartrue($controller->footerCreateMarkup);
		}

		$tabs = $controller->getTabs();

		if($multiLangInfo = $this->renderLanguageTableInfo())
		{
			if(empty($tabs))
			{
				$head = "<div id='admin-ui-edit-db-language' class='text-right'>".$multiLangInfo."</div>";
			}
			else
			{
				$head = "<div id='admin-ui-edit-db-language' class='text-right tabs'>".$multiLangInfo."</div>";
			}
		}
		else
		{
			$head = '';
		}

		$forms = $models = array();
		$forms[] = array(
				'id'  => $this->getElementId(),
				'header' => $head,
				'footer' => '',
				//'url' => e_SELF,
				//'query' => 'self', or custom GET query, self is default
				'fieldsets' => array(
					'create' => array(
						'tabs'	=>  $tabs, //used within a single form.
						'legend' => $legend,
						'fields' => $controller->getFields(), //see e_admin_ui::$fields
						'header' => $form_start, //XXX Unused?
						'footer' => $form_end,  //XXX Unused?
						'after_submit_options' => $controller->getAfterSubmitOptions(), // or true for default redirect options
						'after_submit_default' => $request->getPosted('__after_submit_action', $controller->getDefaultAction()), // or true for default redirect options
						'triggers' => $controller->getDefaultTrigger(), // standard create/update-cancel triggers
					)
				)
		);

		$models[] = $controller->getModel();

		return $this->renderCreateForm($forms, $models, e_AJAX_REQUEST);
	}

	/**
	 * Generic Settings Form.
	 * @return string
	 */
	function getSettings()
	{
		$controller = $this->getController();
	//	$request = $controller->getRequest();
		$legend = LAN_UI_PREF_LABEL;
		$forms = $models = array();
		$forms[] = array(
				'id'  => $this->getElementId(),
				//'url' => e_SELF,
				//'query' => 'self', or custom GET query, self is default
				'tabs' => false, // TODO - NOT IMPLEMENTED YET - enable tabs (only if fieldset count is > 1)
				'fieldsets' => array(
					'settings' => array(
						'tabs'	=> $controller->getPrefTabs(), //used within a single form. 
						'legend' => $legend,
						'fields' => $controller->getPrefs(), //see e_admin_ui::$prefs
						'after_submit_options' => false,
						'after_submit_default' => false, // or true for default redirect options
						'triggers' => array('save' => array(LAN_SAVE, 'update')), // standard create/update-cancel triggers
					)
				)
		);
		$models[] = $controller->getConfig();

	//	print_a($forms);

		return $this->renderCreateForm($forms, $models, e_AJAX_REQUEST);
	}


	/**
	 * Integrate e_addon data into the list model.
	 * @param e_tree_model $tree
	 * @param array $fields
	 * @param string $pid
	 * @return null
	 */
	private function setAdminAddonModel(e_tree_model $tree, $fields, $pid)
	{

		$event= $this->getController()->getEventName();

		$arr = array();

		/** @var e_tree_model $model */
		foreach($tree->getTree() as $model)
		{
			foreach($fields as $fld)
			{

				if(strpos($fld,'x_') !== 0)
				{
					continue;
				}

				list($prefix,$plug,$field) = explode("_",$fld,3);

				if($prefix !== 'x' || empty($field) || empty($plug))
				{
					continue;
				}

				$id = $model->get($pid);

				if(!empty($id))
				{
					$arr[$plug][$field][$id] = $model;
				}
			}


		}


		foreach($arr as $plug=>$field)
		{

			if($obj = e107::getAddon($plug, 'e_admin'))
			{
				foreach($field as $fld=>$var)
				{
					$ids = implode(",", array_keys($var));

					$value = (array) e107::callMethod($obj,'load', $event,$ids);

				//	$value = (array) $obj->load($event, $ids);

					foreach($var as $id=>$model)
					{
						$model->set("x_".$plug."_".$fld, varset($value[$id][$fld],null));
					}
				}
			}

		}

	}


	/**
	 * Create list view
	 * Search for the following GET variables:
	 * - from: integer, current page
	 *
	 * @return string
	 */
	public function getList($ajax = false, $view='default')
	{
		$tp = e107::getParser();
		$this->_list_view = $view;
		$controller = $this->getController();

		$request = $controller->getRequest();
		$id = $this->getElementId();
		$pid = $controller->getPrimaryName();
		$tree = $options = array();
		$tree[$id] = $controller->getTreeModel();




		if(deftrue('e_DEBUG_TREESORT') && $view === 'default')
		{
			$controller->getTreeModelSorted();
		}

		// if going through confirm screen - no JS confirm
		$controller->setFieldAttr('options', 'noConfirm', $controller->deleteConfirmScreen);

		$fields = $controller->getFields();

		$this->setAdminAddonModel($tree[$id], array_keys($fields), $pid);

		// checks dispatcher acess/perms for create/edit/delete access in list mode.
		$mode           = $controller->getMode();
		$deleteRoute    = $mode."/delete";
		$editRoute      = $mode."/edit";
		$createRoute    = $mode."/create";

		if(!$controller->getDispatcher()->hasRouteAccess($createRoute)) // disable the batchCopy option.
		{
			$controller->setBatchCopy(false);
		}

		if(!$controller->getDispatcher()->hasRouteAccess($deleteRoute)) // disable the delete button and batch delete.
		{
			$fields['options']['readParms']['deleteClass'] = e_UC_NOBODY;
			$controller->setBatchDelete(false);
		}

		if(!$controller->getDispatcher()->hasRouteAccess($editRoute))
		{
			$fields['options']['readParms']['editClass'] = e_UC_NOBODY; // display the edit button.
			foreach($options[$id]['fields'] as $k=>$v) // disable inline editing.
			{
				$fields[$k]['inline'] = false;
			}
		}

		if(!$controller->getSortField())
		{
			$fields['options']['sort'] = false;
		}

		if($treefld = $controller->getTreePrefix())
		{
			$fields[$treefld]['type'] = 'method';
			$fields[$treefld]['method'] = 'treePrefix'; /* @see e_admin_form_ui::treePrefix(); */

			$tr = $controller->getTreeModel()->toArray();

			foreach($tr as $row)
			{
				e107::getDebug()->log($row[$treefld].' >  '.$row['_treesort']);
			}

		}



		// ------------------------------------------

		$coreBatchOptions = array(
			'delete'        => $controller->getBatchDelete(),
			'copy'          => $controller->getBatchCopy(),
			'url'           => $controller->getBatchLink(),
			'featurebox'    => $controller->getBatchFeaturebox(),
			'export'        => $controller->getBatchExport(),

		);


		$options[$id] = array(
			'id'            => $this->getElementId(), // unique string used for building element ids, REQUIRED
			'pid'           => $pid, // primary field name, REQUIRED
			'query'	        => $controller->getFormQuery(), // work around - see form in newspost.php (submitted news)
			'head_query'    => $request->buildQueryString('field=[FIELD]&asc=[ASC]&from=[FROM]', false), // without field, asc and from vars, REQUIRED
			'np_query'      => $request->buildQueryString(array(), false, 'from'), // without from var, REQUIRED for next/prev functionality
			'legend'        => $controller->getPluginTitle(), // hidden by default
			'form_pre'      => !$ajax ? $this->renderFilter($tp->post_toForm(array($controller->getQuery('searchquery'), $controller->getQuery('filter_options'))), $controller->getMode().'/'.$controller->getAction()) : '', // needs to be visible when a search returns nothing
			'form_post'     => '', // markup to be added after closing form element
			'fields'        => $fields, // see e_admin_ui::$fields
			'fieldpref'     => $controller->getFieldPref(), // see e_admin_ui::$fieldpref
			'table_pre'     => '', // markup to be added before opening table element
		//	'table_post' => !$tree[$id]->isEmpty() ? $this->renderBatch($controller->getBatchDelete(),$controller->getBatchCopy(),$controller->getBatchLink(),$controller->getBatchFeaturebox()) : '',

			'table_post'    => $this->renderBatch($coreBatchOptions, $controller->getBatchOptions()),
			'fieldset_pre'  => '', // markup to be added before opening fieldset element
			'fieldset_post' => '', // markup to be added after closing fieldset element
			'grid'          =>  $controller->getGrid(),
			'perPage'       => $controller->getPerPage(), // if 0 - no next/prev navigation
			'from'          => $controller->getQuery('from', 0), // current page, default 0
			'field'         => $controller->getQuery('field'), //current order field name, default - primary field
			'asc'           => $controller->getQuery('asc', 'desc'), //current 'order by' rule, default 'asc'
		);



		if($view === 'grid')
		{
			return $this->renderGridForm($options, $tree, $ajax);
		}

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
		
		if(!empty($controller->deleteConfirmMessage))
        { 
			e107::getMessage()->addWarning(str_replace("[x]","<b>".$delcount."</b>", $controller->deleteConfirmMessage));
        }
		else 
        {
			e107::getMessage()->addWarning(str_replace("[x]","<b>".$delcount."</b>",LAN_UI_DELETE_WARNING));
        }
    
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
			'triggers' => array('hidden' => $this->hidden('etrigger_delete['.$ids.']', $ids) . $this->token(), 'delete_confirm' => array(LAN_CONFDELETE, 'confirm', $ids), 'cancel' => array(LAN_CANCEL, 'cancel')),
		);
		if($delcount > 1)
		{
			$fieldsets['confirm']['triggers']['hidden'] = $this->hidden('etrigger_batch', 'delete');
		}

		$id = null;
		$forms[$id] = array(
			'id' => $this->getElementId(), // unique string used for building element ids, REQUIRED
			'url' => e_REQUEST_SELF, // default
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


	/**
	 * Render pagination
	 * @return string
	 */
	public function renderPagination()
	{
		if($this->_list_view === 'grid' && $this->getController()->getGrid('carousel') === true)
		{
			return '<div class="btn-group" >
			<a id="admin-ui-carousel-prev" class="btn btn-default btn-secondary" href="#admin-ui-carousel" data-slide="prev"><i class="fa fa-backward"></i></a>
			<a id="admin-ui-carousel-index" class="btn btn-default btn-secondary">1</a>
			<a id="admin-ui-carousel-next" class="btn btn-default btn-secondary" href="#admin-ui-carousel" data-slide="next"><i class="fa fa-forward"></i></a>
			</div>';
		}

		$tree           = $this->getController()->getTreeModel();
		$totalRecords   = $tree->getTotal();
		$perPage        = $this->getController()->getPerPage();
		$fromPage       = $this->getController()->getQuery('from', 0);

		$vars           = $this->getController()->getQuery();
		$vars['from']   = '[FROM]';

		$paginate       = http_build_query($vars, null, '&amp;');

		return $this->pagination(e_REQUEST_SELF.'?'.$paginate,$totalRecords,$fromPage,$perPage,array('template'=>'basic'));

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
		$input_options['class'] = 'tbox input-text filter input-xlarge ';
		$controller = $this->getController();
		$filter_pre = vartrue($controller->preFilterMarkup);
		$filter_post = vartrue($controller->postFilterMarkup);
		$filter_preserve_var = array();
		// method requires controller - stanalone advanced usage not possible 
		if($this->getController())
		{
			$get = $this->getController()->getQuery();
			foreach ($get as $key => $value) 
			{
				if($key === 'searchquery' || $key === 'filter_options' || $key === 'etrigger_filter')
				{
					continue;
				}

				// Reset pager after filtering.
				if ($key === 'from')
				{
					continue;
				}
				
				$key = preg_replace('/[^\w]/', '', $key);
				$filter_preserve_var[] = $this->hidden($key, rawurlencode($value));
			}
		}
		else
		{
			$filter_preserve_var[] = $this->hidden('mode', $l[0]);
			$filter_preserve_var[] = $this->hidden('action', $l[1]);
		}


		//	$tree = $this->getTree();
		//	$total = $this->getTotal();
		$grid = $this->getController()->getGrid();


		$gridToggle = '';

		if(!empty($grid) && varset($grid['toggleButton']) !==false)
		{
			$gridAction = $this->getController()->getAction() === 'grid' ? 'list' : 'grid';
			$gridQuery = (array) $_GET;
			$gridQuery['action'] = $gridAction;
			$toggleUrl = e_REQUEST_SELF."?".http_build_query($gridQuery, null, '&amp;');
			$gridIcon = ($gridAction === 'grid') ? ADMIN_GRID_ICON : ADMIN_LIST_ICON;
			$gridTitle = ($gridAction === 'grid') ? LAN_UI_VIEW_GRID_LABEL : LAN_UI_VIEW_LIST_LABEL;
			$gridToggle = "<a class='btn btn-default' href='".$toggleUrl."' title=\"".$gridTitle."\">".$gridIcon."</a>";
		}

	// <!--<i class='fa fa-search searchquery form-control-feedback form-control-feedback-left'></i>-->

		$text = "
			<form method='get' action='".e_REQUEST_SELF."'>
				<fieldset id='admin-ui-list-filter' class='e-filter'>
					<legend class='e-hideme'>".LAN_LABEL_LABEL_SELECTED."</legend>
					".$filter_pre."
					<div class='row-fluid'>
						<div  class='left form-inline span8 col-md-8' >
							<span id='admin-ui-list-search' class='form-group has-feedback has-feedback-left'>
								".$this->text('searchquery', $current_query[0], 50, $input_options)."					
							</span>
							".$this->select_open('filter_options', array('class' => 'form-control e-tip tbox select filter', 'id' => false, 'title'=>LAN_FILTER))."
								".$this->option(LAN_FILTER_LABEL_DISPLAYALL, '')."
								".$this->option(LAN_FILTER_LABEL_CLEAR, '___reset___')."
								".$this->renderBatchFilter('filter', $current_query[1])."
							".$this->select_close()."
							<div class='e-autocomplete'></div>
							".implode("\n", $filter_preserve_var)."
							".$this->admin_button('etrigger_filter', 'etrigger_filter', 'filter e-hide-if-js', ADMIN_FILTER_ICON, array('id' => false,'title'=>LAN_FILTER))."
							
							".$this->renderPagination()."
							<span class='indicator' style='display: none;'>
								<img src='".e_IMAGE_ABS."generic/loading_16.gif' class='icon action S16' alt='".LAN_LOADING."' />
							</span>
							".$gridToggle."
						</div>
						<div id='admin-ui-list-db-language' class='span4 col-md-4 text-right' >";


						// Let Admin know which language table is being saved to. (avoid default table overwrites) 
						$text .= $this->renderLanguageTableInfo();
						
						$text .= "
						</div>
					</div>
					".$filter_post."
				</fieldset>
			</form>
		";

	
		e107::js('core','scriptaculous/controls.js','prototype', 2);
		//TODO - external JS
		e107::js('footer-inline',"
	
	            //autocomplete fields
	             \$\$('input[name=searchquery]').each(function(el, cnt) {
				 	if(!cnt) el.activate();
					else return;
					new Ajax.Autocompleter(el, el.next('div.e-autocomplete'), '".e_REQUEST_SELF."?mode=".$l[0]."&action=filter', {
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
		",'prototype');
		
		// TODO implement ajax queue
		// FIXME
		// dirty way to register events after ajax update - DO IT RIGHT - see all.jquery, create object and use handler,
		// re-register them global after ajax update (context)... use behaviors and call e107.attachBehaviors();
		e107::js('footer-inline',"
			var filterRunning = false, request;
			var applyAfterAjax = function(context) {
		      	\$('.e-expandit', context).click(function () {
		       		var href = (\$(this).is('a')) ? \$(this).attr('href') : '';
		       		if(href == '' && \$(this).attr('data-target'))
		       		{
		       			href = '#' + \$(this).attr('data-target');	
		       		}
					if(href === '#' || href == '') 
					{
						idt = \$(this).nextAll('div');	
						\$(idt).toggle('slow');
						 return true;			
					}
		       		//var id = $(this).attr('href');   		
					\$(href).toggle('slow');
					return false;
				}); 
				\$('input.toggle-all', context).click(function(evt) {
					var selector = 'input[type=\"checkbox\"].checkbox';
					if(\$(this).val().startsWith('jstarget:')) {
						selector = 'input[type=\"checkbox\"][name^=\"' + \$(this).val().split(/jstarget\:/)[1] + '\"]';
					}
					
					if(\$(this).is(':checked')){
						\$(selector).attr('checked', 'checked');
					}
					else{
						\$(selector).removeAttr('checked');
					}
				});
			};
			var searchQueryHandler = function (e) {
				var el = \$(this), frm = el.parents('form'), cont = frm.nextAll('.e-container');
				if(cont.length < 1 || frm.length < 1 || (el.val().length > 0 && el.val().length < 3)) return;
				e.preventDefault();
				
				if(filterRunning && request) request.abort();
				filterRunning = true;
				
				cont.css({ opacity: 0.5 });
				
				request = \$.get(frm.attr('action'), frm.serialize(), function(data){
					filterRunning = false;
					setTimeout(function() {
						if(filterRunning) {
							//cont.css({ opacity: 1 });
							return;
						}
						cont.html(data).css({ opacity: 1 });
						// TODO remove applyAfterAjax() and use behaviors!
						applyAfterAjax(cont);
						// Attach behaviors to the newly loaded contents.
						e107.attachBehaviors();
					}, 700);
				}, 'html')
				.error(function() {
					filterRunning = false;
					cont.css({ opacity: 1 });
				});
			};
			\$('#searchquery').on('keyup', searchQueryHandler);
		", 'jquery');

		return $text;
	}


	private function renderLanguageTableInfo()
	{

		if(!e107::getConfig()->get('multilanguage'))
		{
			return null;
		}

		$curTable = $this->getController()->getTableName();
		$sitelanguage = e107::getConfig()->get('sitelanguage');

		$val = e107::getDb()->hasLanguage($curTable, true);

		if($val === false)
		{
			return null;
		}

		if($curTable != e107::getDb()->hasLanguage($curTable))
		{
			$lang = e107::getDb()->mySQLlanguage;
		}
		else
		{
			$lang = $sitelanguage;
		}

		$def = deftrue('LAN_UI_USING_DATABASE_TABLE','Using [x] database table');
		$diz  = e107::getParser()->lanVars($def, $lang); // "Using ".$lang." database table";
		$class = ($sitelanguage == $lang) ? "default" : "";

		$text = "<span class='adminui-language-table-info ".$class." e-tip' title=\"".$diz."\">";
		$text .= e107::getParser()->toGlyph('fa-hdd-o'); // '<i class="icon-hdd"></i> ';
		$text .= e107::getLanguage()->toNative($lang)."</span>";
		return $text;


	}



	// FIXME - use e_form::batchoptions(), nice way of building batch dropdown - news administration show_batch_options()

	/**
	 * @param array $options array of flags for copy, delete, url, featurebox, batch
	 * @param array $customBatchOptions
	 * @return string
	 */
	function renderBatch($options, $customBatchOptions=array())
	{

		$fields = $this->getController()->getFields();

		if(!varset($fields['checkboxes']))
		{
			$mes = e107::getMessage();
			$mes->add("Cannot display Batch drop-down as 'checkboxes' was not found in \$fields array.", E_MESSAGE_DEBUG);
			return '';
		}
		
		// FIX - don't show FB option if plugin not installed
		if(!e107::isInstalled('featurebox'))
		{
			$options['featurebox'] = false;
		}
		
		// TODO - core ui-batch-option class!!! REMOVE INLINE STYLE!
		// XXX Quick Fix for styling - correct. 
		$text = "
			<div id='admin-ui-list-batch' class='navbar navbar-inner left' >
				<div class='span6 col-md-6'>";

		$selectStart = "<div class='form-inline input-inline'>
					".ADMIN_CHILD_ICON."
	         		 		<div class='input-group input-append'>
						".$this->select_open('etrigger_batch', array('class' => 'tbox form-control input-large select batch e-autosubmit reset', 'id' => false))."
						".$this->option(LAN_BATCH_LABEL_SELECTED, '', false);

		$selectOpt = '';
				
		if(!$this->getController()->getTreeModel()->isEmpty())
		{		
			$selectOpt .= !empty($options['copy']) ? $this->option(LAN_COPY, 'copy', false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"')) : '';
			$selectOpt .= !empty($options['delete']) ? $this->option(LAN_DELETE, 'delete', false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"')) : '';
			$selectOpt .= !empty($options['export']) ? $this->option(LAN_UI_BATCH_EXPORT, 'export', false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"')) : '';
			$selectOpt .= !empty($options['url']) ? $this->option(LAN_UI_BATCH_CREATELINK, 'url', false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"')) : '';
			$selectOpt .= !empty($options['featurebox']) ? $this->option(LAN_PLUGIN_FEATUREBOX_BATCH, 'featurebox', false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"')) : '';

		//	if(!empty($parms['sef'])



			if(!empty($customBatchOptions))
			{
				foreach($customBatchOptions as $key=>$val)
				{

					if(is_array($val))
					{
						$selectOpt .= $this->optgroup_open($key);

						foreach($val as $k=>$v)
						{
							$selectOpt .= $this->option($v, $k, false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"'));
						}

						$selectOpt .= $this->optgroup_close();
					}
					else
					{
						$selectOpt .= $this->option($val, $key, false, array('class' => 'ui-batch-option class', 'other' => 'style="padding-left: 15px"'));
					}


				}

			}


			$selectOpt .= $this->renderBatchFilter('batch');

			if(!empty($selectOpt))
			{
				$text .= $selectStart;

				$text .= $selectOpt;

				$text .= $this->select_close();

				$text .= "<div class='input-group-btn input-append'>
				".$this->admin_button('e__execute_batch', 'e__execute_batch', 'batch e-hide-if-js', LAN_GO, array('id' => false))."
				</div>";
				$text .= "</div></div>";
			}

			$text .= "</div>";

		}

		
		$text .= "

				<div id='admin-ui-list-total-records' class='span6 col-md-6 right'><span>".e107::getParser()->lanVars(LAN_UI_TOTAL_RECORDS,number_format($this->getController()->getTreeModel()->getTotal()))."</span></div>
			</div>
		";


		return $text;
	}


	/**
	 * Render Batch and Filter Dropdown options.
	 * @param string $type
	 * @param string $selected
	 * @return string
	 */
	function renderBatchFilter($type='batch', $selected = '') // Common function used for both batches and filters.
	{
		$optdiz = array('batch' => LAN_BATCH_LABEL_PREFIX.'&nbsp;', 'filter'=> LAN_FILTER_LABEL_PREFIX.'&nbsp;');
		$table = $this->getController()->getTableName();
		$text = '';
		$textsingle = '';
				

		$searchFieldOpts = array();

		$fieldList = $this->getController()->getFields();



		foreach($fieldList as $key=>$val)
		{
			if(!empty($val['search']))
			{
				$searchFieldOpts["searchfield__".$key] = $val['title'];
			}

			if(empty($val[$type])) // ie. filter = false or batch = false.
			{
				continue;
			}

			$option = array();
			$parms = vartrue($val['writeParms'], array());
			if(is_string($parms)) parse_str($parms, $parms);

			//Basic batch support for dropdown with multiple values. (comma separated)
			if(!empty($val['writeParms']['multiple']) && $val['type'] === 'dropdown' && !empty($val['writeParms']['optArray']))
			{
				$val['type'] = 'comma';
				$parms = $val['writeParms']['optArray'];
			}



			switch($val['type'])
			{

					case 'text';

						if(!empty($parms['sef']))
						{
							$option['sefgen__'.$key.'__'.$parms['sef']] = LAN_GENERATE;
						}

						$searchFieldOpts["searchfield__".$key] = $val['title'];

					break;


					case 'number';
						if($type === 'filter')
						{
							$option[$key.'___ISEMPTY_'] = LAN_UI_FILTER_IS_EMPTY;
						}

						$searchFieldOpts["searchfield__".$key] = $val['title'];

					break;

					case 'textarea':
					case 'tags':
						$searchFieldOpts["searchfield__".$key] = $val['title'];
					break;

					case 'bool':
					case 'boolean': //TODO modify description based on $val['parm]

						// defaults
						$LAN_TRUE = LAN_ON;
						$LAN_FALSE = LAN_OFF;

						if(varset($parms['label']) === 'yesno')
						{
							$LAN_TRUE = LAN_YES;
							$LAN_FALSE = LAN_NO;
						}
						
						if(!empty($parms['enabled']))
						{
							$LAN_TRUE = $parms['enabled'];
						}
						elseif(!empty($parms['true']))
						{
							$LAN_TRUE = $parms['true'];
						}

						if(!empty($parms['disabled']))
						{
							$LAN_FALSE = $parms['disabled'];
						}
						elseif(!empty($parms['false']))
						{
							$LAN_FALSE = $parms['false'];
						}

						if(!empty($parms['reverse'])) // reverse true/false values;
						{
							$option['bool__'.$key.'__0'] = $LAN_TRUE;	// see newspost.php : news_allow_comments for an example.
							$option['bool__'.$key.'__1'] = $LAN_FALSE;
						}
						else 
						{
							$option['bool__'.$key.'__1'] = $LAN_TRUE;
							$option['bool__'.$key.'__0'] = $LAN_FALSE;
						}
							
						if($type === 'batch')
						{
							$option['boolreverse__'.$key] = LAN_BOOL_REVERSE;
						}
					break;
					
					case 'checkboxes':
					case 'comma':
						// TODO lan
						if(!isset($parms['__options'])) $parms['__options'] = array();
						if(!is_array($parms['__options'])) parse_str($parms['__options'], $parms['__options']);
						$opts = $parms['__options'];
						unset($parms['__options']); //remove element options if any
						
						$options = $parms ? $parms : array();
						if(empty($options)) continue 2;
						
						
						if($type === 'batch')
						{
							$_option = array(); 

							if(isset($options['addAll']))
							{
								$option['attach_all__'.$key] = vartrue($options['addAll'], "(".LAN_ADD_ALL.")");
								unset($options['addAll']);
							}
							if(isset($options['clearAll']))
							{
								$_option['deattach_all__'.$key] = vartrue($options['clearAll'], "(".LAN_CLEAR_ALL.")");
								unset($options['clearAll']);
							}
							
							if(vartrue($opts['simple']))
							{
								foreach ($options as $value) 
								{
									$option['attach__'.$key.'__'.$value] = LAN_ADD." ".$value;
									$_option['deattach__'.$key.'__'.$value] = LAN_REMOVE." ".$value;
								}
							}
							else 
							{
								foreach ($options as $value => $label) 
								{
									$option['attach__'.$key.'__'.$value] = LAN_ADD." ".$label;	
									$_option['deattach__'.$key.'__'.$value] = LAN_REMOVE." ".$label;	
								}
							}
							$option = array_merge($option, $_option);
							unset($_option);
						}
						else
						{
							unset($options['addAll'], $options['clearAll']);
							if(vartrue($opts['simple']))
							{
								foreach($options as $k)
								{
									$option[$key.'__'.$k] = $k;
								}
							}
							else
							{
								foreach($options as $k => $name)
								{
									$option[$key.'__'.$k] = $name;
								}
							}

						}						
					break;
						
					case 'templates':
					case 'layouts':
						$parms['raw'] = true;
						$val['writeParms'] = $parms;
						$tmp = $this->renderElement($key, '', $val);
						if(is_array($tmp))
						{	
							foreach ($tmp as $k => $name)
							{
								$option[$key.'__'.$k] = $name;
							}
						}
					break;

					case 'dropdown': // use the array $parm;




						if(!empty($parms['optArray']))
						{
							$fopts = $parms;
							$parms = $fopts['optArray'];
							unset($fopts['optArray']);
							$parms['__options'] = $fopts;
						}


						if(!is_array(varset($parms['__options']))) parse_str($parms['__options'], $parms['__options']);
						$opts = $parms['__options'];
						if(vartrue($opts['multiple']) && $type === 'batch')
						{
							// no batch support for multiple, should have some for filters soon
							continue 2;
						}

						unset($parms['__options']); //remove element options if any



						foreach($parms as $k => $name)
						{
							$option[$key.'__'.$k] = $name;
						}
					break;

					case 'language': // full list of 
					case 'lanlist': // use the array $parm;
						if(!is_array(varset($parms['__options']))) parse_str($parms['__options'], $parms['__options']);
						$opts = $parms['__options'];
						if(vartrue($opts['multiple']))
						{
							// no batch support for multiple, should have some for filters soon
							continue 2;
						}
						$options = ($val['type'] === 'language') ? e107::getLanguage()->getList() : e107::getLanguage()->getLanSelectArray();
						foreach($options as $code => $name)
						{
							$option[$key.'__'.$code] = $name;
						}
					break;

					case 'datestamp':
						$tp = e107::getParser();
						$dateFilters = array (
							'hour'		=> LAN_UI_FILTER_PAST_HOUR,
							"day"		=> LAN_UI_FILTER_PAST_24_HOURS,
							"week"		=> LAN_UI_FILTER_PAST_WEEK,
							"month"		=> LAN_UI_FILTER_PAST_MONTH,
							"month3"	=> $tp->lanVars(LAN_UI_FILTER_PAST_XMONTHS,3),
							"month6"	=> $tp->lanVars(LAN_UI_FILTER_PAST_XMONTHS,6),
							"month9"	=> $tp->lanVars(LAN_UI_FILTER_PAST_XMONTHS,9),
							"year"		=> LAN_UI_FILTER_PAST_YEAR
						);

						$dateFiltersFuture = array (
								'nhour'		=> LAN_UI_FILTER_NEXT_HOUR,
								"nday"		=> LAN_UI_FILTER_NEXT_24_HOURS,
								"nweek"		=> LAN_UI_FILTER_NEXT_WEEK,
								"nmonth"	=> LAN_UI_FILTER_NEXT_MONTH,
								"nmonth3"	=> $tp->lanVars(LAN_UI_FILTER_NEXT_XMONTHS,3),
								"nmonth6"	=> $tp->lanVars(LAN_UI_FILTER_NEXT_XMONTHS,6),
								"nmonth9"	=> $tp->lanVars(LAN_UI_FILTER_NEXT_XMONTHS,9),
								"nyear"		=> LAN_UI_FILTER_NEXT_YEAR
						);

						if($val['filter'] === 'future' )
						{
							$dateFilters = $dateFiltersFuture;
						}

						if($val['filter'] === 'both')
						{
							$dateFilters += $dateFiltersFuture;
						}
					    
						foreach($dateFilters as $k => $name)
						{
							$option['datestamp__'.$key.'__'.$k] = $name;
						//	$option['bool__'.$key.'__0'] = LAN_NO;	
						//	$option[$key.'__'.$k] = $name;
						}
					break;

					case 'userclass':
						$classes = e107::getUserClass()->uc_required_class_list(vartrue($parms['classlist'], 'public,nobody,guest,member,admin,main,classes'));
						foreach($classes as $k => $name)
						{
							$option[$key.'__'.$k] = $name;
						}
					break;
					case 'userclasses':
						$classes = e107::getUserClass()->uc_required_class_list(vartrue($parms['classlist'], 'public,nobody,guest,member,admin,main,classes'));
						$_option = array();
						
						if($type === 'batch')
						{
							foreach ($classes as $k => $v) 
							{
								$option['ucadd__'.$key.'__'.$k] = LAN_ADD.' '.$v;
								$_option['ucremove__'.$key.'__'.$k] = LAN_REMOVE." ".$v;
							}
							$option['ucaddall__'.$key] = "(".LAN_ADD_ALL.")";
							$_option['ucdelall__'.$key] = "(".LAN_CLEAR_ALL.")";
							$option = array_merge($option, $_option);
						}
						else
						{
							foreach ($classes as $k => $v) 
							{
								$option[$key.'__'.$k] = $v;	
							}
						}
						
						unset($_option);
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
								continue 2;
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
							continue 2;
						}
					break;

					case 'user': // TODO - User Filter
					
						$sql = e107::getDb();
						$field = $val['field'];
						
						$query = "SELECT d.".$field.", u.user_name FROM #".$val['table']." AS d LEFT JOIN #user AS u ON d.".$field." = u.user_id  GROUP BY d.".$field." ORDER BY u.user_name";
						$row = $sql->retrieve($query,true);
						foreach($row as $data)
						{
							$k = $data[$field];
							if($k == 0)
							{
								$option[$key.'__'.$k] = "(".LAN_ANONYMOUS.")";	
							}
							else 
							{
								$option[$key.'__'.$k] = vartrue($data['user_name'],LAN_UNKNOWN);
							}
							
							
						}
					break;
			}



			if(!empty($option))
			{
				$text .= "\t".$this->optgroup_open($optdiz[$type].defset($val['title'], $val['title']), varset($disabled))."\n";
				foreach($option as $okey=>$oval)
				{
					$text .= $this->option($oval, $okey, $selected == $okey)."\n";
				}
				$text .= "\t".$this->optgroup_close()."\n";
			}
		}


		if(!empty($searchFieldOpts))
		{
			$text .= "\t".$this->optgroup_open(defset("LAN_UI_FILTER_SEARCH_IN_FIELD", "Search in Field"))."\n";

			foreach($searchFieldOpts as $key=>$val)
			{
				$text .= $this->option($val, $key, $selected == $key)."\n";
			}

			$text .= "\t".$this->optgroup_close()."\n";
		}



		return $textsingle.$text;

	}

	public function getElementId()
	{
		$controller = $this->getController();
		$name = str_replace('_', '-', ($controller->getPluginName() === 'core' ? 'core-'.$controller->getTableName() : 'plugin-'.$controller->getPluginName()));
		return e107::getForm()->name2id($name); // prevent invalid ids.
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
 

