<?php
if (!defined('e107_INIT')) { exit; }

// Multi indice array sort by sweetland@whoadammit.com
if (!function_exists('asortbyindex')) {
	function asortbyindex($sortarray, $index) {
		$lastindex = count ($sortarray) - 1;
		for ($subindex = 0; $subindex < $lastindex; $subindex++) {
			$lastiteration = $lastindex - $subindex;
			for ($iteration = 0; $iteration < $lastiteration; $iteration++) {
				$nextchar = 0;
				if (comesafter ($sortarray[$iteration][$index], $sortarray[$iteration + 1][$index])) {
					$temp = $sortarray[$iteration];
					$sortarray[$iteration] = $sortarray[$iteration + 1];
					$sortarray[$iteration + 1] = $temp;
				}
			}
		}
		return ($sortarray);
	}
}

if (!function_exists('comesafter')) {
	function comesafter($s1, $s2) {
		$order = 1;
		if (strlen ($s1) > strlen ($s2)) {
			$temp = $s1;
			$s1 = $s2;
			$s2 = $temp;
			$order = 0;
		}
		for ($index = 0; $index < strlen ($s1); $index++) {
			if ($s1[$index] > $s2[$index]) return ($order);
				if ($s1[$index] < $s2[$index]) return (1 - $order);
			}
		return ($order);
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

        if(!$natsort) ($order=='asc')? asort($sort_values) : arsort($sort_values);
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
	protected $_action = 'default';
	
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
	 * @param string|array $merge_with [optional] override request values
	 * @return string url encoded query string
	 */
	public function buildQueryString($merge_with = array())
	{
		$ret = $this->getQuery();
		if(is_string($merge_with))
		{
			parse_str($merge_with, $merge_with);
		}
		return http_build_query(array_merge($ret, (array) $merge_with));
	}
	
	/**
	 * Convert string to camelCase
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
 * TODO - request related code should be moved to core
 * request handler
 */
class e_admin_dispatcher
{
	/**
	 * @var e_admin_request
	 */
	protected $_request;
	
	/** 
	 * @var e_admin_controller
	 */
	protected $_current_controller;
	
	/**
	 * Required (set by child class).
	 * Controller map array in format 
	 * 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'path' => 'CONTROLLER SCRIPT PATH']);
	 * 
	 * @var array
	 */
	protected $controllerList;
	
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
	 */
	public function __construct($request = null)
	{
		if(null === $request || !is_object($request))
		{
			$request = new e_admin_request($request);
		}
		
		$this->setRequest($request)->init();
		$this->_initController();
		
	}
	
	/**
	 * User defined constructor - called before _initController() method
	 * @return e_admin_dispatcher
	 */
	public function init()
	{
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
	 * Get request object
	 * @return e_admin_request
	 */
	public function getRequest()
	{
		return $this->_request;
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
		//call_user_func(array($this->_current_controller, 'dispatchObserver'), $this->getRequest()->getActionName());
		$this->_current_controller->dispatchObserver();
		
		//search for $actionName.'Header' method, js manager should be used inside
		if($run_header && !deftrue('e_AJAX_REQUEST'))
		{
			//call_user_func(array($this->_current_controller, 'dispatchHeader'), $this->getRequest()->getActionName());
			$this->_current_controller->dispatchHeader();
			
		}
		return $this;
	}
	
	/**
	 * Render page body
	 * TODO - set/getParams(), convert $return to parameter, pass parameters to the requet object
	 * 
	 * @param boolean $return if true, array(title, body, render_mod) will be returned 
	 * @return string|array
	 */
	public function renderPage($return = false)
	{
		//search for $actionName.'Page' method, js manager should be used inside
		//return call_user_func_array(array($this->_current_controller, 'dispatchPage'), array($this->getRequest()->getActionName(), $return));
		$this->_current_controller->setParam('return_type', $return);
		return $this->_current_controller->dispatchHeader();
	}
	
	/**
	 * Get current controller object
	 * @return e_admin_controller
	 */
	public function getController()
	{
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
		if(isset($this->controllerList[$request->getModeName()]) && isset($this->controllerList[$request->getModeName()]['controller']))
		{
			$class_name = $this->controllerList[$request->getModeName()]['controller'];
			$class_path = vartrue($this->controllerList[$request->getModeName()]['path']);
			
			if($class_path)
			{
				require_once(e107::getParser()->replaceConstants($class_path));
			}
			if($class_name && class_exists($class_name))//NOTE: autoload in the play
			{
				$this->_current_controller = new  $class_name();
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
		return new $class_name();
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
						$v = $tp->replaceConstants($v, 'abs').'?mode='.$tmp[0].'&action='.$tmp[1];
					break;
				
					default:
						$k2 = $k;
					break;
				}
				$var[$key][$k2] = $v;
			}
			if(vartrue($var[$key]['link']))
			{
				$var[$key]['link'] = e_SELF.'?mode='.$tmp[0].'&action='.$tmp[1];
			}
			
			/*$var[$key]['text'] = $val['caption'];
			$var[$key]['link'] = (vartrue($val['url']) ? $tp->replaceConstants($val['url'], 'abs') : e_SELF).'?mode='.$tmp[0].'&action='.$tmp[1];
			$var[$key]['perm'] = $val['perm'];	*/
		}

		e_admin_menu($this->menuTitle, $this->getMode().'/'.$this->getAction(), $var);
	}
}

class e_admin_controller
{
	/**
	 * @var e_admin_request
	 */
	protected $_request;
	
	/**
	 * @var array User defined parameters
	 */
	protected $_params = array();
	
	/**
	 * Constructor 
	 * @param e_admin_request $request [optional]
	 */
	public function __construct($request = null)
	{
		$this->_request = $request;
	}
	
	/**
	 * User defined init
	 * Called before dispatch routine
	 */
	public function init()
	{
	}
	
	/**
	 * Get parameter
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
	 * Reset parameter array
	 * @param array $params
	 * @return e_admin_controller
	 */
	public function setParams($params)
	{
		$this->_params = (array) $params;
		return $this;
	}
	
	/**
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
		$this->_dispatcher = $request;
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
		
		if(null === $action)
		{
			$action = $request->getActionName();
		}
		
		// check for observer
		$actionObserverName = $action.'Observer';
		if(method_exists($this, $actionObserverName))
		{
			$this->$actionObserverName();
		}
		
		// check for triggers
		if($this->triggersEnabled())
		{
			$posted = $request->getPosted();
			foreach ($posted as $key => $value)
			{
				if(strpos($key, 'etrigger_') === 0)
				{
					$actionTriggerName = $action.$request->camelize(substr($key, 9)).'Trigger';
					if(method_exists($this, $actionTriggerName))
					{
						$this->$actionTriggerName();
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
	 * Dispatch header
	 * @param string $action [optional]
	 * @return e_admin_controller
	 */
	public function dispatchHeader($action = null)
	{
		$request = $this->getRequest();
		if(null === $request)
		{
			$request = new e_admin_request();
			$this->setRequest($request);
		}
		
		if(null === $action)
		{
			$action = $request->getActionName();
		}
		
		// check for observer
		$actionHeaderName = $action.'Header';
		if(method_exists($this, $actionHeaderName))
		{
			$this->$actionHeaderName();
		}
		return $this;
	}
	
	public function dispatchPage($action = null)
	{
		$request = $this->getRequest();
		if(null === $request)
		{
			$request = new e_admin_request();
			$this->setRequest($request);
		}
		
		if(null === $action)
		{
			$action = $request->getActionName();
		}
		
		// check for observer
		$actionName = $action.'Page';
		$ret = '';
		if(!method_exists($this, $actionName))
		{
			e107::getMessage()->add('Action '.$actionName.' no found!', E_MESSAGE_ERROR);
		}
		
		// get output TODO - response handler
		ob_start(); //catch any output
		$ret = $this->$actionName();
		$ret .= ob_get_clean();
		
		// show messages if any
		$ret = e107::getMessage()->render().$ret;
		
		return $ret;
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

