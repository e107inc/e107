<?php
if (!defined('e107_INIT')) { exit; }

// Better Array-sort by key function by acecream (22-Apr-2003 11:02) http://php.net/manual/en/function.asort.php
if (!function_exists('asortbyindex')) 
{
	function asortbyindex($array, $key)
	{
	   foreach ($array as $i => $k)
	   {
	        $sort_values[$i] = $array[$i][$key];
	   }
	   asort ($sort_values);
	   reset ($sort_values);
	   while (list ($arr_key, $arr_val) = each ($sort_values))
	   {
	          $sorted_arr[] = $array[$arr_key];
	   }
	   return $sorted_arr;
	}
}




if (!function_exists('multiarray_sort')) {
    function multiarray_sort(&$array, $key, $order = 'asc', $natsort = true, $case = true)
    {
        if(!is_array($array)) return $array;

        $order = strtolower($order);
        foreach ($array as $i => $arr)
        {
           $sort_values[$i] = $arr[$key];
        }

        if(!$natsort) 
        {
        	($order=='asc')? asort($sort_values) : arsort($sort_values);
        }
        else
        {
             $case ? natsort($sort_values) : natcasesort($sort_values);
             if($order != 'asc') $sort_values = array_reverse($sort_values, true);
        }
        reset ($sort_values);

        while (list ($arr_key, $arr_val) = each ($sort_values))
        {
             $sorted_arr[] = $array[$arr_key];
        }
        return $sorted_arr;
    }
}



/**
 * TODO - core request handler (non-admin), core response
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
	protected $_mode = 'main';
	
	/**
	 * Key name for mode search
	 * @var string
	 */
	protected $_mode_key = 'mode';
	
	/**
	 * Current action
	 * @var string
	 */
	protected $_action = 'index';
	
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
	 * @return 
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
		return $this->_mode;
	}
	
	/**
	 * Get current mode name
	 * 
	 * @return string
	 */
	public function getModeName()
	{
		return strtolower(str_replace('-', '_', $this->_mode));
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
	 * @return 
	 */
	public function getAction()
	{
		return $this->_action;
	}
	
	/**
	 * Get current action name
	 * @return string camelized action
	 */
	public function getActionName()
	{
		return $this->camelize($this->_action);
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
			$exclude_from_query = $ret;
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
			$this->{$meta}[] = $tp->toAttribute(strip_tags($content));
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
	 * 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'path' => 'CONTROLLER SCRIPT PATH']);
	 * 
	 * @var array
	 */
	protected $modes;
	
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
	 * @return 
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
		
		foreach($this->adminMenu as $key => $val)
		{
			$tmp = explode('/', trim($key, '/'), 2);
			
			foreach ($val as $k=>$v)
			{
				switch($k)
				{
					case 'caption':
						$k2 = 'text';
					break;
					
					case 'url':
						$k2 = 'link';
						$v = $tp->replaceConstants($v, 'abs').'?mode='.$tmp[0].'&amp;action='.$tmp[1];
					break;
				
					default:
						$k2 = $k;
					break;
				}
				$var[$key][$k2] = $v;
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
		$selected = $request->getMode().'/'.$request->getAction();
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
			e107::getMessage()->add('Action <strong>'.$method.'</strong> not found!', E_MESSAGE_ERROR);
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
		//var_dump(call_user_func(array($this, $actionName)), $this->{$actionName}());
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
			var_dump($response_type, $response->getJsHelper());
		}
		else
		{
			$response->appendBody($ret);
		}
		
		return $response;
	}
	
	public function E404Observer()
	{
		$this->getResponse()->setTitle('Page Not Found');
	}
	
	public function E404Page()
	{
		return '<div class="center">Requested page was not found!</div>'; // TODO - lan
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
	 * @return 
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
	 * @return 
	 */
	public function toMethodName($action_name, $type= 'page', $ajax = null)
	{
		if(null === $ajax) $ajax = e_AJAX_REQUEST; //auto-resolving
		return $action_name.($ajax ? 'Ajax' : '').ucfirst(strtolower($type));
	}
	
	/**
	 * Check if there is a trigger available in the posted data
	 * @return boolean
	 */
	public function hasTrigger()
	{
		$posted = array_keys($this->getPosted()); 
		foreach ($posted as $key)
		{
			if(strpos($key, 'etrigger_') === 0)
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
	
}

class e_admin_ui extends e_admin_controller_ui
{
	protected $fields = array();
	protected $fieldpref = array();
	protected $fieldTypes = array();
	protected $dataFields = array();
	protected $validationRules = array();
	protected $prefs = array();
	protected $pluginName;
	
	protected $listQry;
	protected $tableJoin;
	protected $editQry;
	protected $table;
	protected $tableAlias;
	protected $pid;
	
	protected $pluginTitle;
	protected $perPage = 20;
	protected $batchDelete = true;

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
	 * Constructor 
	 * @param e_admin_request $request
	 * @param e_admin_response $response
	 * @param array $params [optional]
	 */
	public function __construct($request, $response, $params = array())
	{
		$this->setDefaultAction('list');
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
	 * Catch batch submit
	 * @param string $batch_trigger
	 * @return 
	 */
	public function ListBatchTrigger($batch_trigger)
	{
		$this->setPosted('etrigger_batch', null); 
		// proceed ONLY if there is no other trigger
		if($batch_trigger && !$this->hasTrigger()) $this->_handleListBatch($batch_trigger);
	}
	
	/**
	 * Catch fieldpref submit
	 * @param string $batch_trigger
	 * @return 
	 */
	public function ListEcolumnsTrigger()
	{
		$this->triggersEnabled(false); //disable further triggering
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
	 * Catch delete submit
	 * @param string $batch_trigger
	 * @return 
	 */
	public function ListDeleteTrigger($posted)
	{
		$this->triggersEnabled(false);
		$id = intval(array_shift($posted)); 
		$this->getTreeModel()->delete($id);
		$this->getTreeModel()->setMessages();
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
		$this->getTreeModel()->setParam('db_query', $this->_modifyListQry())->load();
		$this->addTitle('List'); // FIXME - get captions from dispatch list
	}
	
	/**
	 * Generic List action page
	 * @return string
	 */
	public function ListPage()
	{
		return $this->getUI()->getList();
	}
	
	public function FilterAjaxPage()
	{
		$ret = '<ul>';
		$ret .= "<li><span class='informal warning'> clear filter </span></li>";
		
		$srch = $this->getPosted('searchquery');
		$this->getRequest()->setQuery('searchquery', $srch); //_modifyListQry() is requiring GET String
		$reswords = array();
		if(trim($srch) !== '')
		{
			// Build query
			$qry = $this->_modifyListQry(true, 0, 20);

			// Make query
			$sql = e107::getDb();
			if($qry && $sql->db_Select_gen($qry))
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
		
		$ret .= '</ul>';
		return $ret;
	}
	
	/**
	 * List action observer
	 * @return void
	 */
	public function ListAjaxObserver()
	{
		$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, 0))->load();
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
		$this->CreateSubmitTrigger();
	}
	
	/**
	 * Edit - send JS to page Header
	 * @return 
	 */
	function EditHeader()
	{
		// TODO - make it part of e_from::textarea/bbarea(), invoke it on className (not all textarea elements)
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
		$this->triggersEnabled(true);
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
		// Scenario I - use request owned POST data - toForm already exeuted
		$posted = $this->getPosted();
		$this->convertToData($posted);
		$this->getModel()->setPostedData($posted, null, false, false)
			->save(true);
		// Scenario II - inner model sanitize
		//$this->getModel()->setPosted($this->convertToData($_POST(, null, false, true);
		
		// Copy model messages to the default message stack
		$this->getModel()->setMessages();
		
		// Take action based on use choice after success
		if(!$this->getModel()->hasError())
		{
			$this->doAfterSubmit($this->getModel()->getId(), 'edit');
		}
		
	}
	
	/**
	 * Create - send JS to page Header
	 * @return 
	 */
	function CreateHeader()
	{
		// TODO - make it part of e_from::textarea/bbarea(), invoke it on className (not all textarea elements)
		e107::getJs()->requireCoreLib('core/admin.js');
	}
	
	/**
	 * 
	 * @return 
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
	 * Handle posted batch options
	 * @param string $batch_trigger
	 * @return void
	 */
	protected function _handleListBatch($batch_trigger)
	{
		$tp = e107::getParser();
		$multi_name = vartrue($this->fields['checkboxes']['toggle'], 'multiselect');
		$selected = array_values($this->getPosted($multi_name, array()));
		
		if(empty($selected)) return;
		
		$selected = array_map('intval', $selected);
		$trigger = $tp->toDB(explode('__', $batch_trigger));
		
		$this->triggersEnabled(false); //disable further triggering

		switch($trigger[0])
		{
			case 'delete': //FIXME - confirmation screen
				if(!$this->getBatchDelete())
				{
					e107::getMessage()->add('Batch delete not allowed!', E_MESSAGE_WARNING);
					return;
				} 
				$this->getTreeModel()->delete($selected);
				$this->getTreeModel()->setMessages();
			break;
			
			case 'bool': 
				// direct query
				$field = $trigger[1];
				$value = $trigger[2] ? 1 : 0;
				$cnt = $this->getTreeModel()->update($field, $value, $selected, $value, false);
				if($cnt)
				{
					$this->getTreeModel()->addMessageSuccess($cnt.' records successfully updated.');
				}
				$this->getTreeModel()->setMessages();
			break;
			
			case 'boolreverse':
				// direct query
				$field = $trigger[1]; //TODO - errors
				$tree = $this->getTreeModel();
				$cnt = $tree->update($field, "1-{$field}", $selected, null, false);
				if($cnt)
				{
					$tree->addMessageSuccess($cnt.' records successfully reversed.');
					//sync models
					$tree->load(true);
				}
				$this->getTreeModel()->setMessages();
			break;
		
			default:
				//something like handleListUrlTypeBatch(); for custom handling of 'url_type' field name
				$method = 'handle'.$this->getRequest()->getActionName().$this->getRequest()->camelize($trigger[0]).'Batch';
				if(method_exists($this, $method)) // callback handling
				{
					$this->$method($trigger[1], $selected);
				}
				else // default handling
				{
					$field = $trigger[0];
					$value = $trigger[1]; //TODO - errors
					
					$cnt = $this->getTreeModel()->update($field, "'".$value."'", $selected, $value, false);
					if($cnt)
					{ 
						$vttl = $this->getUI()->renderValue($field, $value, $this->getFieldAttr($field));
						$this->getTreeModel()->addMessageSuccess('<strong>'.$vttl.'</strong> set for <strong>'.$cnt.'</strong> records.');
					}
					$this->getTreeModel()->setMessages();
					//$this->redirectAction();
				}
			break;
		}
	}
	
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
		
		if($this->tableJoin)
		{
			foreach ($this->tableJoin as $table => $att)
			{
				if(strpos($table, '.') !== false)
				{
					$tmp = explode('.', $table, 2);
					unset($this->tableJoin[$table]);
					$att['alias'] = $tmp[0];
					$att['table'] = $tmp[1];
					$att['__tablePath'] = $att['alias'].'.';
					$att['__tableFrom'] = '`#'.$att['table'].'` AS '.$att['alias'];
					$this->tableJoin[$att['alias']] = $att;
					unset($tmp);
					continue;
				}
				$this->tableJoin[$table]['table'] = $table;
				$this->tableJoin[$table]['alias'] = '';
				$this->tableJoin[$table]['__tablePath'] = '`#'.$this->tableJoin[$table]['table'].'`.';
				$this->tableJoin[$table]['__tableFrom'] = '`#'.$this->tableJoin[$table]['table'].'`';
			}
		}
		
		// check for table aliases
		$fields = array(); // preserve order
		foreach ($this->fields as $field => $att)
		{
			if(strpos($field, '.') !== false)
			{
				$tmp = explode('.', $field, 2);
				$att['alias'] = $tmp[0];
				$fields[$tmp[1]] = $att;
				$field = $tmp[1];
				unset($tmp);
			}
			else
			{
				$att['alias'] = $this->tableAlias;
				$fields[$field] = $att;
			}
			if($fields[$field]['alias'])
			{
				
				if($fields[$field]['alias'] == $this->tableAlias)
				{
					$fields[$field]['__tableField'] = $this->tableAlias.'.'.$field;
				}
				else
				{
					$fields[$field]['__tableField'] = $this->tableJoin[$fields[$field]['alias']]['__tablePath'].$field;
				}
			}
			else
			{
				$fields[$field]['__tableField'] = '`#'.$this->table.'`.'.$field;
			}
		}
		$this->fields = $fields;
		
		return $this;
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
	 * Convert posted values when needed (based on field type)
	 * @param array $data
	 * @return void
	 */
	protected function convertToData(&$data)
	{
		foreach ($this->getFields() as $key => $attributes)
		{
			if(!isset($data[$key]))
			{
				continue;
			}
			switch($attributes['type'])
			{
				case 'datestamp':
					if(!is_numeric($data[$key]))
					{
						$data[$key] = e107::getDateConvert()->toTime($data[$key], 'input'); 
					}
				break;
				
				case 'ip': // TODO - ask Steve if this check is required
					if(strpos($data[$key], '.') !== FALSE)
					{
						$data[$key] = e107::getInstance()->ipEncode($data[$key]);
					}
				break;
				//more to come
			}
		}
	}
	
	protected function _modifyListQry($isfilter = false, $forceFrom = false, $forceTo = false)
	{
		$searchQry = array();
		$filterFrom = array();
		$request  = $this->getRequest();
		$tp = e107::getParser();
		$tablePath = '`#'.$this->table.'`.';
		$tableFrom = '`#'.$this->table.'`';
		$tableSFields = '`#'.$this->table.'`.*';
		if($this->tableAlias)
		{
			$tablePath = $this->tableAlias.'.';
			$tableFrom = '`#'.$this->table.'` AS '.$this->tableAlias;
			$tableSFields = ''.$this->tableAlias.'.*';
		}
		
		$searchQuery = $tp->toDB($request->getQuery('searchquery', ''));
		list($filterField, $filterValue) = $tp->toDB(explode('__', $request->getQuery('filter_options', '')));
		
		// FIXME - currently broken
		if($filterField && $filterValue !== '' && isset($this->fields[$filterField]))
		{
			$searchQry[] = $this->fields[$filterField]['__tableField']." = '".$filterValue."'";
		}
		
		
		$filter = array();
		
		// Commented for now - we should search in ALL searchable fields, not only currently active. Discuss.
		//foreach($this->fieldpref as $key)
		foreach($this->fields as $key => $var)
		{
			//if(!vartrue($this->fields[$key])) continue;
			//$var = $this->fields[$key];
			$searchable_types = array('text', 'textearea', 'bbarea', 'user'); //method?
			
			if(trim($searchQuery) !== '' && !vartrue($var['nolist']) && in_array($var['type'], $searchable_types))
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
		
		$jwhere = array();
		$joins = array();
		if($this->tableJoin) 
		{
			$qry = "SELECT SQL_CALC_FOUND_ROWS ".$tableSFields;
			foreach ($this->tableJoin as $jtable => $tparams)
			{
				// Select fields
				if(!$isfilter)
				{
					$fields = vartrue($tparams['fields']);
					if('*' === $fields)
					{
						$qry .= ", {$tparams['__tablePath']}*";
					}
					else
					{
						$fields = explode(',', $fields);
						foreach ($fields as $field)
						{
							$qry .= ", {$tparams['__tablePath']}`".trim($field).'`';
						}
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
			$qry .= " FROM ".$tableFrom;
			
			// Joins
			if(count($joins) > 0)
			{
				$qry .=  "\n".implode("\n", $joins);
			}
		}
		else
		{
			$qry = $this->listQry ? $this->listQry : "SELECT SQL_CALC_FOUND_ROWS ".$tableSFields." FROM ".$tableFrom;
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
		
		// where query
		if(count($searchQry) > 0)
		{
			$qry .= " WHERE ".implode(" AND ", $searchQry);
		}
		
		$orderField = $request->getQuery('field', $this->getPrimaryName());
		if(isset($this->fields[$orderField]))
		{
			// no need of sanitize - it's found in field array
			$qry .= ' ORDER BY '.$this->fields[$orderField]['__tableField'].' '.($request->getQuery('asc') == 'desc' ? 'DESC' : 'ASC');
		}
		
		if($this->getPerPage() || false !== $forceTo)
		{
			$from = false === $forceFrom ? intval($request->getQuery('from', 0)) : intval($forceFrom);
			if(false === $forceTo) $forceTo = $this->getPerPage();
			$qry .= ' LIMIT '.$from.', '.intval($forceTo);
		}

		return $qry;
	}
	
	public function getPerPage()
	{
		return $this->perPage;
	}
	
	public function getPrimaryName()
	{
		if(!varset($this->pid) && vartrue($this->fields))
		{
			$mes = e107::getMessage();
			$mes->add("There is no <b>pid</b> set.", E_MESSAGE_WARNING);			
		}
		
		return $this->pid;
	}
	
	public function getPluginName()
	{
		return $this->pluginName;
	}
	
	public function getPluginTitle()
	{
		return $this->pluginTitle;
	}
	
	public function getTableName($alias = false, $prefix = false)
	{
		if($alias && $this->tableAlias) return $this->tableAlias;
		return ($prefix ? '#.' : '').$this->table;
	}
	
	public function getJoinTable($alias = false, $prefix = false)
	{
		if($alias && $this->tableAlias) return $this->tableAlias;
		return ($prefix ? '#.' : '').$this->table;
	}
	
	public function getBatchDelete()
	{
		return $this->batchDelete;
	}
	
	public function getFields()
	{
		return $this->fields;
	}
	
	public function getFieldAttr($key)
	{
		if(isset($this->fields[$key]))
		{
			return $this->fields[$key];
		}
		return array();
	}
	
	public function getFieldPref()
	{
		return $this->fieldpref;
	}
	
	public function getFieldPrefAttr($key)
	{
		if(isset($this->fieldpref[$key]))
		{
			return $this->fieldpref[$key];
		}
		return array();
	}
	
	/**
	 * Get Config object 
	 * @return e_plugin_pref|e_core_pref
	 */
	public function getConfig()
	{ 
		if(null === $this->_pref)
		{
			$this->_pref = $this->pluginName == 'core' ? e107::getConfig() : e107::getPlugConfig($this->pluginName);
			
			$dataFields = $validateRules = array();
			foreach ($this->prefs as $key => $att)
			{
				// create dataFields array
				if(vartrue($att['data']))
				{
					$dataFields[$key] = $att['data'];
				}
				
				// create validation array
				if(vartrue($att['validate']))
				{
					$validateRules[$key] = array((true === $att['validate'] ? 'required' : $att['validate']), varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
				}
				
				
				$this->_pref->setDataFields($dataFields)->setValidationRules($validateRules);
				/* Not implemented in e_model yet 
				elseif(vartrue($att['check']))
				{
					$validateRules[$key] = array($att['check'], varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
				}*/
			}
			
		}
		return $this->_pref;
	}
	
	/**
	 * Get Config object 
	 * @return e_plugin_pref|e_core_pref
	 */
	public function getPrefs()
	{
		return $this->prefs;
	}
	
	/**
	 * Get column preference array
	 * @return array
	 */
	public function getUserPref()
	{
		global $user_pref;
		return vartrue($user_pref['admin_cols_'.$this->getTableName()], array());
	}
	
	/**
	 * Get column preference array
	 * @return array
	 */
	public function setUserPref($new)
	{
		global $user_pref;
		$user_pref['admin_cols_'.$this->getTableName()] = $new;
		$this->fieldpref = $new;
		save_prefs('user');
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
			// try to create dataFields array if missing
			if(!$this->dataFields)
			{
				$this->dataFields = array();
				foreach ($this->fields as $key => $att)
				{
					if((null !== $att['type'] && !vartrue($att['noedit'])) || vartrue($att['forceSave']))
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
			
			// default model
			$this->_model = new e_admin_model();
			$this->_model->setModelTable($this->table)
				->setFieldIdName($this->pid)
				->setValidationRules($this->validationRules)
				->setFieldTypes($this->fieldTypes)
				->setDataFields($this->dataFields)
				->setParam('db_query', $this->editQry);
		}
		
		return $this->_model;
	}
	
	public function setModel($model)
	{
		$this->_model = $model;
	}
	
	public function getTreeModel()
	{
		if(null === $this->_tree_model)
		{
			// default tree model
			$this->_tree_model = new e_admin_tree_model();
			$this->_tree_model->setModelTable($this->table)
				->setFieldIdName($this->pid)
				->setParams(array('model_class' => 'e_admin_model', 'db_query' => $this->listQry));
		}
		
		return $this->_tree_model;
	}
	
	public function setTreeModel($tree_model)
	{
		$this->_tree_model = $tree_model;
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
			if($this->getParam('ui'))
			{
				$this->_ui = $this->getParam('ui');
				$this->setParam('ui', null);
			}
			else// default ui
			{
				$this->_ui = new e_admin_form_ui($this);
			}
		}
		return $this->_ui;
	}
	
	public function setUI($ui)
	{
		$this->_ui = $ui;
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
		$err = "";
		$fields = array_keys($this->getController()->getFields());

		foreach($fields as $val)
		{
			if(method_exists('e_form',$val)) // check even if type is not method. - just in case of an upgrade later by 3rd-party. 
			{
				$err .= "<h2>ERROR: The field name (".$val.") is not allowed.</h2>";
				$err .= "Please rename the key (".$val.") to something else in your fields array and database table.<br /><br />";
			}	
		}
		
		if($err)
		{
			echo $err;
			exit;
		}		
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
			$legend = LAN_UPDATE.' record #'.$controller->getId();
		}
		else
		{
			$legend = 'New record';
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
						'after_submit_options' => true, // or true for default redirect options
						'after_submit_default' => $request->getPosted('__after_submit_action', $controller->getDefaultAction()), // or true for default redirect options
						'triggers' => 'auto', // standard create/update-cancel triggers 
					)
				) 
		);
		$models[] = $controller->getModel();
		
		return $this->createForm($forms, $models, e_AJAX_REQUEST);
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
		$legend = 'Settings';
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
		
		return $this->createForm($forms, $models, e_AJAX_REQUEST);
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
		$tree = $controller->getTreeModel(); 
		$options = array(
			'id' => $this->getElementId(), // unique string used for building element ids, REQUIRED
			'pid' => $controller->getPrimaryName(), // primary field name, REQUIRED
			//'url' => e_SELF, default
			//'query' => e_QUERY, default 
			'head_query' => $request->buildQueryString('field=[FIELD]&asc=[ASC]&from=[FROM]', false), // without field, asc and from vars, REQUIRED
			'np_query' => $request->buildQueryString(array(), false, 'from'), // without from var, REQUIRED for next/prev functionality
			'legend' => $controller->getPluginTitle(), // hidden by default
			'form_pre' => !$ajax ? $this->renderFilter($tp->post_toForm(array($controller->getQuery('searchquery'), $controller->getQuery('filter_options'))), $controller->getMode().'/'.$controller->getAction()) : '', // needs to be visible when a search returns nothing
			'form_post' => '', // markup to be added after closing form element
			'fields' => $controller->getFields(), // see e_admin_ui::$fields
			'fieldpref' => $controller->getFieldPref(), // see e_admin_ui::$fieldpref
			'table_pre' => '', // markup to be added before opening table element 
			'table_post' => !$tree->isEmpty() ? $this->renderBatch($controller->getBatchDelete()) : '',
			'fieldset_pre' => '', // markup to be added before opening fieldset element 
			'fieldset_post' => '', // markup to be added after closing fieldset element
			'perPage' => $controller->getPerPage(), // if 0 - no next/prev navigation
			'from' => $controller->getQuery('from', 0), // current page, default 0
			'field' => $controller->getQuery('field'), //current order field name, default - primary field
			'asc' => $controller->getQuery('asc', 'desc'), //current 'order by' rule, default 'asc'
		);
		return $this->listForm($options, $tree, $ajax);
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
		$text = "
			<form method='get' action='".e_SELF."?".e_QUERY."'>
				<fieldset class='e-filter'>
					<legend class='e-hideme'>Filter</legend>
					<div class='left'>
						".$this->text('searchquery', $current_query[0], 50, $input_options)."
						".$this->select_open('filter_options', array('class' => 'tbox select filter', 'id' => false))."
							".$this->option('Display All', '')."
							".$this->option('Clear Filter', '___reset___')."
							".$this->renderBatchFilter('filter', $current_query[1])."
						".$this->select_close()."
						<div class='e-autocomplete'></div>	
						".$this->hidden('mode', $l[0])."
						".$this->hidden('action', $l[1])."
						".$this->admin_button('etrigger_filter', 'etrigger_filter', 'filter e-hide-if-js', LAN_FILTER, array('id' => false))."
						<span class='indicator' style='display: none;'>
							<img src='".e_IMAGE_ABS."generic/loading_16.gif' class='icon action S16' alt='Loding...' />
						</span>
					</div>
				</fieldset>
			</form>
		"; 

		e107::getJs()->requireCoreLib('scriptaculous/controls.js', 2);
		//TODO - external JS
		e107::getJs()->footerInline("
	            //autocomplete fields
	             \$\$('input[name=searchquery]').each(function(el, cnt) { 
				 	if(!cnt) el.focus();
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
		
		$text = "
			<div class='buttons-bar left'>
         		<img src='".e_IMAGE_ABS."generic/branchbottom.gif' alt='' class='icon action' />
				".$this->select_open('etrigger_batch', array('class' => 'tbox select batch e-autosubmit', 'id' => false))."
					".$this->option('With selected...', '')."
					".($allow_delete ? $this->option('&nbsp;&nbsp;&nbsp;&nbsp;'.LAN_DELETE, 'delete') : '')."
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
		$optdiz = array('batch' => 'Modify ', 'filter'=> 'Filter by ');
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
					case 'boolean': //TODO modify description based on $val['parm]
						$option['bool__'.$key.'__1'] = LAN_YES;
						$option['bool__'.$key.'__0'] = LAN_NO;
						if($type == 'batch')
						{
							$option['boolreverse__'.$key] = LAN_BOOL_REVERSE;
						}
					break;
					
					case 'dropdown': // use the array $parm; 
						unset($parms['__options']); //remove element options if any
						foreach($parms as $k => $name)
						{
							$option[$key.'__'.$k] = $name;
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

// FIXME - here because needed on AJAX calls (header.php not loaded), should be moved to separate file!
if (!defined('ADMIN_TRUE_ICON'))
{
	define("ADMIN_TRUE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/true_16.png' alt='' />");
	define("ADMIN_TRUE_ICON_PATH", e_IMAGE."admin_images/true_16.png");
}

if (!defined('ADMIN_FALSE_ICON'))
{
	define("ADMIN_FALSE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/false_16.png' alt='' />");
	define("ADMIN_FALSE_ICON_PATH", e_IMAGE."admin_images/false_16.png");
}

if (!defined('ADMIN_EDIT_ICON'))
{
	define("ADMIN_EDIT_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/edit_16.png' alt='' title='".LAN_EDIT."' />");
	define("ADMIN_EDIT_ICON_PATH", e_IMAGE."admin_images/edit_16.png");
}

if (!defined('ADMIN_DELETE_ICON'))
{
	define("ADMIN_DELETE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/delete_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DELETE_ICON_PATH", e_IMAGE."admin_images/delete_16.png");
}

if (!defined('ADMIN_UP_ICON'))
{
	define("ADMIN_UP_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/up_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_UP_ICON_PATH", e_IMAGE."admin_images/up_16.png");
}

if (!defined('ADMIN_DOWN_ICON'))
{
	define("ADMIN_DOWN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/down_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DOWN_ICON_PATH", e_IMAGE."admin_images/down_16.png");
}

if (!defined('ADMIN_WARNING_ICON'))
{
	define("ADMIN_WARNING_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/warning_16.png' alt='' />");
	define("ADMIN_WARNING_ICON_PATH", e_IMAGE."admin_images/warning_16.png");
}

if (!defined('ADMIN_INFO_ICON'))
{
	define("ADMIN_INFO_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' />");
	define("ADMIN_INFO_ICON_PATH", e_IMAGE."admin_images/info_16.png");
}

if (!defined('ADMIN_CONFIGURE_ICON'))
{
	define("ADMIN_CONFIGURE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/configure_16.png' alt='' />");
	define("ADMIN_CONFIGURE_ICON_PATH", e_IMAGE."admin_images/configure_16.png");
}

if (!defined('ADMIN_ADD_ICON'))
{
	define("ADMIN_ADD_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/add_16.png' alt='' />");
	define("ADMIN_ADD_ICON_PATH", e_IMAGE."admin_images/add_16.png");
}

if (!defined('ADMIN_VIEW_ICON'))
{
	define("ADMIN_VIEW_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/search_16.png' alt='' />");
	define("ADMIN_VIEW_ICON_PATH", e_IMAGE."admin_images/admin_images/search_16.png");
}

if (!defined('ADMIN_URL_ICON'))
{
	define("ADMIN_URL_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/forums_16.png' alt='' />");
	define("ADMIN_URL_ICON_PATH", e_IMAGE."admin_images/forums_16.png");
}

if (!defined('ADMIN_INSTALLPLUGIN_ICON'))
{
	define("ADMIN_INSTALLPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/plugin_install_16.png' alt='' />");
	define("ADMIN_INSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_install_16.png");
}

if (!defined('ADMIN_UNINSTALLPLUGIN_ICON'))
{
	define("ADMIN_UNINSTALLPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/plugin_uninstall_16.png' alt='' />");
	define("ADMIN_UNINSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_unstall_16.png");
}

if (!defined('ADMIN_UPGRADEPLUGIN_ICON'))
{
	define("ADMIN_UPGRADEPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/up_16.png' alt='' />");
	define("ADMIN_UPGRADEPLUGIN_ICON_PATH", e_IMAGE."admin_images/up_16.png");
}

/**
 * TODO:
 * 1. move abstract peaces of code to the proper classes
 * 2. remove duplicated code (e_form & e_admin_form_ui), refactoring
 * 3. make JS Manager handle Styles (.css files and inline CSS)
 * 4. [DONE] e_form is missing some methods used in e_admin_form_ui
 * 5. [DONE] date convert needs string-to-datestamp auto parsing, strptime() is the solution but needs support for 
 * 		Windows and PHP < 5.1.0 - build custom strptime() function (php_compatibility_handler.php) on this - 
 * 		http://sauron.lionel.free.fr/?page=php_lib_strptime (bad license so no copy/paste is allowed!)
 * 6. [DONE - read/writeParms introduced ] $fields[parms] mess - fix it, separate list/edit mode parms somehow
 * 7. clean up/document all object vars (e_admin_ui, e_admin_dispatcher)
 * 8. [DONE hopefully] clean up/document all parameters (get/setParm()) in controller and model classes
 * 9. [DONE] 'ip' field type - convert to human readable format while showing/editing record
 * 10. draggable ordering (list view)
 * 11. realtime search filter (typing text) - like downloads currently
 * 12. autosubmit when 'filter' dropdown is changed (quick fix?)
 * 13. tablerender captions 
 * 14. [DONE] textareas auto-height
 * 15. [DONE] multi JOIN table support (optional), aliases
 */