<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Message Handler
 *
 * $URL$
 * $Id$
 *
*/

if (!defined('e107_INIT')) { exit; }

/*
 * Type defines
 */
define('E_MESSAGE_INFO',      'info');
define('E_MESSAGE_SUCCESS',   'success');
define('E_MESSAGE_WARNING',   'warning');
define('E_MESSAGE_ERROR',     'error');
define('E_MESSAGE_DEBUG',     'debug');
define('E_MESSAGE_NODISPLAY', 'nodisplay'); // Appears to be needed by update_routine

//FIXME - language file! new?

/**
 * Handle system messages
 * 
 * @package e107
 * @subpackage	e107_handlers
 * @version $Id$
 * @author SecretR
 * @copyright Copyright (C) 2008-2010 e107 Inc (e107.org)
 */
class eMessage
{
	/**
	 * Type defines
	 */
	 const E_INFO       = 'info';
	 const E_SUCCESS    = 'success';
	 const E_WARNING    = 'warning';
	 const E_ERROR      = 'error';
	 const E_DEBUG      = 'debug';
	 const E_NODISPLAY  = 'nodisplay';
	
	
	/**
	 * System Message Array
	 * in format [type][message_stack] = array(message[, ...])
	 *
	 * @var array
	 */
	protected $_sysmsg = array();
	
	/**
	 * Session key for storing session messages
	 *
	 * @var string
	 */
	protected $_session_id;
	
	/**
	 * @var e_core_session
	 */
	protected $_session_handler = null;
	
	/**
	 * @var array
	 */
	protected $_unique = array();

	/**
	 * @var array
	 */
	static $_customTitle = array();

	/**
	 * Custom font-awesome icon
	 * @var array
	 */
	static $_customIcon = array();


	static $_close = array('info'=>true,'success'=>true,'warning'=>true,'error'=>true,'debug'=>true);
	/**
	 * Singleton instance
	 * 
	 * @var eMessage
	 */
	//protected static $_instance = null;
	
	/**
	 * Constructor
	 * 
	 * Use {@link getInstance()}, direct instantiating 
	 * is not possible for signleton objects
	 *
	 * @return void
	 */
	public function __construct()
	{
		//if(!session_id()) session_start();
		
		// require_once(e_HANDLER.'e107_class.php');
		$this->_session_id = '_system_messages';
		
		$this->reset()->mergeWithSession();
	}

	/**
	 * Cloning is not allowed
	 *
	 */
	// private function __clone()
	// {
	// }
	
	/**
	 * Singleton is not required, we go for factory instead
	 * @return eMessage
	 */
	public static function getInstance()
	{
		// if(null == self::$_instance)
		// {
		    // self::$_instance = new self();
		// }
	  	return e107::getMessage();
	}
	
	/**
	 * Set message session id
	 * @param string $name 
	 * @return eMessage
	 */
	public function setSessionId($name = '')
	{
		$sid = $name.'_system_messages';
		if($this->_session_id != $sid)
		{
			if(session_id())
			{
				$session = $this->getSessionHandler();
				$session->set($sid, $session->get($this->_session_id, true)); // move
				if(!$session->has($sid)) $session->set($sid, array()); // be sure it's array
			}
			$this->_session_id = $sid;
		}
		return $this;
	}
	
	/**
	 * Get session handler
	 * @return e_core_session
	 */
	public function getSessionHandler()
	{
		if(null === $this->_session_handler)
		{
			$session = e107::getSession();
			if(!$session->has($this->_session_id)) $session->set($this->_session_id, array());
			$this->_session_handler = $session;
		}
		return $this->_session_handler;
	}
	
	
	/**
	 * Set unique message stacks
	 * @param string $mstack message stack which should have only unique message values
	 * @return eMessage
	 */
	public function setUnique($mstack='default')
	{
		if(!in_array($mstack, $this->_unique))
		{
			$this->_unique[] = $mstack;
		}
		return $this;
	}


	/**
	 * Add message to a type stack and default message stack
	 * If $message is array, $message[0] will be the message stack and
	 * $message[1] the message itself
	 *
	 * @param string|array $message
	 * @param string $type
	 * @param boolean $session
	 * @return eMessage
	 */
	public function add($message, $type = eMessage::E_INFO, $session = false)
	{
		if(empty($message)) return $this;
		
		$mstack = 'default';
		$msg = $message;
		if(is_array($message))
		{
			$mstack = $message[1];
			$msg = $message[0];
		}
		if(empty($msg)) return $this;
		
		if(!$session)
		{
			// unique messages only
			if(in_array($mstack, $this->_unique) && isset($this->_sysmsg[$type][$mstack]) && in_array($msg, $this->_sysmsg[$type][$mstack])) return $this;
			if($this->isType($type)) $this->_sysmsg[$type][$mstack][] = $msg;
			return $this;
		}
		
		$this->addSession($message, $type);
		return $this;
	}
	
	/**
	 * Alias of {@link add()}.
	 * Should be used for dealing with messages with custom message stacks.
	 * Supports message arrays.
	 * 
	 * @param string|array $message message(s)
	 * @param string $mstack defaults to 'default' 
	 * @param string $type [optional]
	 * @param boolean $session [optional]
	 * @return eMessage
	 */
	public function addStack($message, $mstack = 'default', $type = E_MESSAGE_INFO, $session = false)
	{
		if(!is_array($message))
		{
			$message = array($message);
		}
		
		foreach ($message as $m)
		{
			$this->add(array($m, $mstack), $type, $session);
		}
		return $this;
	}
	
	/**
	 * Add success message
	 * 
	 * @param string $message
	 * @param string $mstack message stack, default value is 'default'
	 * @param boolean $session
	 * @return eMessage
	 */
	public function addSuccess($message, $mstack = 'default', $session = false)
	{
		return $this->addStack($message, $mstack, E_MESSAGE_SUCCESS, $session);
	}
	
	/**
	 * Add error message
	 * 
	 * @param string $message
	 * @param string $mstack message stack, default value is 'default'
	 * @param boolean $session
	 * @return eMessage
	 */
	public function addError($message, $mstack = 'default', $session = false)
	{
		return $this->addStack($message, $mstack, E_MESSAGE_ERROR, $session);
	}
	
	/**
	 * Add warning message
	 * 
	 * @param string $message
	 * @param string $mstack message stack, default value is 'default'
	 * @param boolean $session
	 * @return eMessage
	 */
	public function addWarning($message, $mstack = 'default', $session = false)
	{
		return $this->addStack($message, $mstack, E_MESSAGE_WARNING, $session);
	}
	
	/**
	 * Add info message
	 * 
	 * @param string $message
	 * @param string $mstack message stack, default value is 'default'
	 * @param boolean $session
	 * @return eMessage
	 */
	public function addInfo($message, $mstack = 'default', $session = false)
	{
		return $this->addStack($message, $mstack, E_MESSAGE_INFO, $session);
	}
	
	/**
	 * Add debug message
	 * 
	 * @param string $message
	 * @param string $mstack message stack, default value is 'default'
	 * @param boolean $session
	 * @return eMessage
	 */
	public function addDebug($message, $mstack = 'default', $session = false) //TODO Add different types of DEBUG depending on the debug mode. 
	{
		return $this->addStack($message, $mstack, E_MESSAGE_DEBUG, $session);
	}

	/**
	 * Add message to a _SESSION type stack
	 * If $message is array, $message[0] will be the message stack and
	 * $message[1] the message itself
	 * 
	 * @param string|array $message
	 * @param string $type
	 * @return eMessage
	 */
	public function addSession($message, $type = E_MESSAGE_INFO)
	{
		if(empty($message) || !session_id()) return $this;
		
		$mstack = 'default';
		if(is_array($message))
		{
			$mstack = $message[1];
			$message = $message[0];
		}
		$SESSION = $this->getSessionHandler()->get($this->_session_id);

		if($this->isType($type)) 
		{
			// unique messages only
			if(in_array($mstack, $this->_unique) && in_array($message, $SESSION[$type][$mstack])) return $this;
			
			$SESSION[$type][$mstack][] = $message;
			$this->getSessionHandler()->set($this->_session_id, $SESSION);
		}
		return $this;
	}
	
	/**
	 * Alias of {@link addSession()}.
	 * Should be used for dealing with messages with custom message stacks.
	 * Supports message arrays.
	 * 
	 * @param string|array $message message(s)
	 * @param string $mstack defaults to 'default' 
	 * @param string $type [optional]
	 * @return eMessage
	 */
	public function addSessionStack($message, $mstack = 'default', $type = E_MESSAGE_INFO)
	{
		if(!is_array($message))
		{
			$message = array($message);
		}
		foreach ($message as $m)
		{
			$this->addSession(array($m, $mstack), $type);
		}
		return $this;
	}

	/**
	 * Get type title (multi-language)
	 *
	 * @param string $type
	 * @param string $message_stack
	 * @return string title
	 */
	public static function getTitle($type, $message_stack = 'default')
	{
		if(!empty(self::$_customTitle[$type]))
		{
			return self::$_customTitle[$type];		
		}
		
		if($message_stack && $message_stack != 'default' && defined('EMESSLAN_TITLE_'.strtoupper($type.'_'.$message_stack)))
		{
			return constant('EMESSLAN_TITLE_'.strtoupper($type.'_'.$message_stack));
		}
		return deftrue('EMESSLAN_TITLE_'.strtoupper($type), '');
	}


	/**
	 * Set a custom title/caption (useful for front-end)
	 *
	 * @param string $title
	 * @param string $type E_MESSAGE_SUCCESS,E_MESSAGE_ERROR, E_MESSAGE_WARNING, E_MESSAGE_INFO
	 * @return $this
	 * @example e107::getMessage()->setTitle('Custom Title', E_MESSAGE_INFO);
	 */
	public function setTitle($title, $type)
	{
		$tp = e107::getParser();
		self::$_customTitle[$type] = $tp->toText($title);
		
		return $this;
	}

	/**
	 * Set a custom icon (useful for front-end)
	 *
	 * @param string $fa FontAwesome reference. eg. fa-cog
	 * @param string $type E_MESSAGE_SUCCESS,E_MESSAGE_ERROR, E_MESSAGE_WARNING, E_MESSAGE_INFO
	 * @return $this
	 * @example e107::getMessage()->setIcon('fa-cog', E_MESSAGE_INFO);
	 */
	public function setIcon($fa, $type)
	{
		$tp = e107::getParser();
		self::$_customIcon[$type] = $tp->toText($fa);

		return $this;
	}


	/**
	 * Enable the 'x' close functionality of an alert.
	 *
	 * @param boolean $toggle
	 * @param string $type E_MESSAGE_SUCCESS,E_MESSAGE_ERROR, E_MESSAGE_WARNING, E_MESSAGE_INFO
	 * @return $this
	 * @example e107::getMessage()->setClose(false, E_MESSAGE_INFO);
	 */
	public function setClose($toggle, $type)
	{
		self::$_close[$type] = $toggle;
		return $this;
	}



	/**
	 * Message getter
	 *
	 * @param string $type valid type
	 * @param string $mstack message stack name
	 * @param bool $raw force array return
	 * @param bool $reset reset message type stack
	 * @return string|array message
	 */
	public function get($type, $mstack = 'default', $raw = false, $reset = true)
	{	
		$message = isset($this->_sysmsg[$type][$mstack]) ? $this->_sysmsg[$type][$mstack] : '';
		if($reset) $this->reset($type, $mstack, false);

		return (true === $raw ? $message : self::formatMessage($mstack, $type, $message));
	}
	
	/**
	 * Get all messages for a stack
	 *
	 * @param string $mstack message stack name
	 * @param bool $raw force array return
	 * @param bool $reset reset message type stack
	 * @return array messages
	 */
	public function getAll($mstack = 'default', $raw = false, $reset = true)
	{	
		$ret = array();
		foreach ($this->_get_types() as $type)
		{
			$message = $this->get($type, $mstack, $raw, $reset);
			if(!empty($message))
			{
				$ret[$type] = $message;
			}
		}
		
		return $ret;
	}

	/**
	 * Session message getter
	 *
	 * @param string $type valid type
	 * @param string $mstack message stack
	 * @param bool $raw force array return
	 * @param bool $reset reset session message type stack
	 * @return string|array session message
	 */
	public function getSession($type, $mstack = 'default', $raw = false, $reset = true)
	{
		if(!session_id()) return null;
		$SESSION = $this->getSessionHandler()->get($this->_session_id);
		$message = isset($SESSION[$type][$mstack]) ? $SESSION[$type][$mstack] : '';
		if($reset) $this->resetSession($type, $mstack);

		return (true === $raw ? $message : self::formatMessage($mstack, $type, $message));
	}
	
	/**
	 * Get all session messages for a stack
	 *
	 * @param string $mstack message stack name
	 * @param bool $raw force array return
	 * @param bool $reset reset message type stack
	 * @return array session messages
	 */
	public function getAllSession($mstack = 'default', $raw = false, $reset = true)
	{	
		if(!session_id()) return array();
		$ret = array();
		foreach ($this->_get_types() as $type)
		{
			$message = $this->getSession($type, $mstack, $raw, $reset);
			if(!empty($message))
			{
				$ret[$type] = $message;
			}
		}
		
		return $ret;
	}

	/**
	 * Output all accumulated messages OR a specific type of messages. eg. 'info', 'warning', 'error', 'success'
	 *
	 * @param string $mstack message stack name
	 * @param bool|string $options  - true : merge with session messages or enter a type 'info', 'warning', 'error', 'success'
	 * @param bool $reset reset all messages
	 * @param bool $raw force return type array
	 * @return array|string messages
	 */
	public function render($mstack = 'default', $options = false, $reset = true, $raw = false)
	{
		if($options === true )
		{
			$this->mergeWithSession(true, $mstack);
		}
		$ret = array();

		$typesArray = (is_string($options) && in_array($options, $this->_get_types()))  ? array($options) : $this->_get_types();		
		
		foreach ($typesArray as $type)
		{
			if(E_MESSAGE_DEBUG === $type && !deftrue('E107_DEBUG_LEVEL'))
			{
				continue;
			}
			$message = $this->get($type, $mstack, $raw);

			if(!empty($message))
			{
				$ret[$type] = $message;
			}
		}

		if($reset) $this->reset(false, $mstack);
		if(true === $raw || empty($ret)) return ($raw ? $ret : '');


		return "
			<div class='s-message'>
				".implode("\n", $ret)."
			</div>
		";
	}

	/**
	 * Create message block markup based on its type.
	 *
	 * @param string $mstack
	 * @param string $type
	 * @param array|string $message
	 * @return string
	 */
	public static function formatMessage($mstack, $type, $message)
	{
		$bstrap = array('info'=>'alert-info','error'=>'alert-error alert-danger','warning'=>'alert-warning','success'=>'alert-success','debug'=>'alert-warning');
		$bclass = vartrue($bstrap[$type]) ? " ".$bstrap[$type] : "";
		
		if (empty($message))
		{
			 return '';
		}
		elseif (is_array($message))
		{
			// XXX quick fix disabled because of various troubles - fix attempt made inside pref handler (the source of the problem)
			// New feature added - setUnique($mstack) -> array_unique only for given message stacks
			//$message = array_unique($message); // quick fix for duplicates. 
			$message = "<div class='s-message-item'>".implode("</div>\n<div class='s-message-item'>", $message)."</div>";
		}

		$icon = !empty(self::$_customIcon[$type]) ? "s-message-empty fa fa-2x ".self::$_customIcon[$type] : "s-message-".$type;

		
		$text = "<div class='s-message alert alert-block fade in {$type} {$bclass}'>";
		$text .= (self::$_close[$type] === true) ? "<a class='close' data-dismiss='alert'>×</a>" : "";
		$text .= "<i class='s-message-icon ".$icon."'></i>
				<h4 class='s-message-title'>".self::getTitle($type, $mstack)."</h4>
				<div class='s-message-body'>
					{$message}
				</div>
			</div>
		";


		return $text;
	}

	/**
	 * Reset message array
	 *
	 * @param mixed $type false for reset all or type string
	 * @param mixed $mstack false for reset all or stack name string 
	 * @param boolean $session reset session messages as well
	 * @return eMessage
	 */
	public function reset($type = false, $mstack = false, $session = false)
	{
		if(false === $type) 
		{
			if(false === $mstack)
			{
				$this->_sysmsg = $this->_type_map();
			}
			elseif(is_array($this->_sysmsg))
			{
				foreach ($this->_sysmsg as $t => $_mstack) 
				{
					if(is_array($_mstack))
					{
						unset($this->_sysmsg[$t][$mstack]);
					}
				}
			}
		}
		elseif(isset($this->_sysmsg[$type])) 
		{
			if(false === $mstack)
			{
				$this->_sysmsg[$type] = array();
			}
			elseif(is_array($this->_sysmsg[$type])) 
			{
				unset($this->_sysmsg[$type][$mstack]);
			}
		}

		if($session) $this->resetSession($type, $mstack);

		return $this;
	}

	/**
	 * Reset _SESSION message array
	 *
	 * @param mixed $type false for reset all, or valid type constant
	 * @param mixed $mstack false for reset all or stack name string 
	 * @return eMessage
	 */
	public function resetSession($type = false, $mstack = false)
	{
		if(!session_id()) return $this;
		$SESSION = $this->getSessionHandler()->get($this->_session_id);
		if(false === $type) 
		{
			if(false === $mstack)
			{
				$SESSION = $this->_type_map();
			}
			elseif($SESSION)
			{
				foreach ($SESSION as $t => $_mstack) 
				{
					if(is_array($_mstack))
					{
						unset($SESSION[$t][$mstack]);
					}
				}
			}
		}
		elseif(isset($SESSION[$type])) 
		{
			if(false === $mstack)
			{
				$SESSION[$type] = array();
			}
			elseif(is_array($SESSION[$type])) 
			{
				unset($SESSION[$type][$mstack]);
			}
		}
		$this->getSessionHandler()->set($this->_session_id, $SESSION);
		return $this;
	}

	/**
	 * Merge _SESSION message array with the current messages
	 * 
	 * @param boolean $reset
	 * @param boolean $mstack
	 * @return eMessage
	 */
	public function mergeWithSession($reset = true, $mstack = false)
	{
		// do nothing if there is still no session
		if(!session_id()) return $this;
		$SESSION = $this->getSessionHandler()->get($this->_session_id);
		
		if(!empty($SESSION))
		{
			foreach (array_keys($SESSION) as $type)
			{
				if(!$this->isType($type)) 
				{ 
					unset($SESSION[$type]);
					continue;
				}
				if(false === $mstack)
				{
					$this->_sysmsg[$type] = array_merge_recursive($this->_sysmsg[$type], $SESSION[$type]);
					continue;
				}
				
				if(isset($SESSION[$type][$mstack]))
				{
					$this->_sysmsg[$type][$mstack] = $SESSION[$type][$mstack];
				}
			}
			$this->getSessionHandler()->set($this->_session_id, $SESSION);
		}
		if($reset) $this->resetSession(false, $mstack);
		return $this;
	}
	
	/**
	 * Convert current messages to Session messages 
	 *
	 * @param bool $mstack false - move all message stacks
	 * @param bool $message_type false - move all types
	 * @return eMessage
	 */
	public function moveToSession($mstack = false, $message_type = false)
	{
		// do nothing if there is still no session
		if(!session_id()) return $this;
		$SESSION = $this->getSessionHandler()->get($this->_session_id);
		
		foreach (array_keys($this->_sysmsg) as $type)
		{
			if(!$this->isType($type) || ($message_type && $message_type !== $type)) 
			{ 
				unset($this->_sysmsg[$type]);
				continue;
			}
			if(false === $mstack)
			{
				$SESSION[$type] = array_merge_recursive($SESSION[$type], $this->_sysmsg[$type]);
				continue;
			}
			
			if(isset($this->_sysmsg[$type][$mstack]))
			{
				$SESSION[$type][$mstack] = $this->_sysmsg[$type][$mstack];
			}
		}
		$this->getSessionHandler()->set($this->_session_id, $SESSION);
		$this->reset($message_type, $mstack, false);
		return $this;
	}
	
	/**
	 * Merge messages from source stack with destination stack
	 * and reset source stack
	 * 
	 * @param string $from_stack source stack
	 * @param string $to_stack [optional] destination stack
	 * @param bool $type [optional] merge for a given type only
	 * @param bool $session [optional] merge session as well
	 * @return eMessage
	 */
	public function moveStack($from_stack, $to_stack = 'default', $type = false, $session = true)
	{
		if($from_stack == $to_stack) return $this;
		foreach ($this->_sysmsg as $_type => $stacks)
		{
			if($type && $type !== $_type)
			{
				continue;
			}
			
			if(isset($stacks[$from_stack]))
			{
				if(!isset($this->_sysmsg[$_type][$to_stack]))
				{
					$this->_sysmsg[$_type][$to_stack] = array();
				}
				if(in_array($from_stack, $this->_unique))
				{
					// check the destination stack messages, remove duplicates
					foreach ($this->_sysmsg[$_type][$from_stack] as $i => $_m) 
					{
						if(in_array($_m, $this->_sysmsg[$_type][$to_stack])) unset($this->_sysmsg[$_type][$from_stack][$i]);
					}
				}
				$this->_sysmsg[$_type][$to_stack] = array_merge($this->_sysmsg[$_type][$to_stack], $this->_sysmsg[$_type][$from_stack]);
				unset($this->_sysmsg[$_type][$from_stack]);
			}
		}
		
		if($session) $this->moveSessionStack($from_stack, $to_stack, $type);
		
		return $this;
	}
	
	/**
	 * Merge session messages from source stack with destination stack
	 * and reset source stack
	 * 
	 * @param string $from_stack source stack
	 * @param string $to_stack [optional] destination stack
	 * @param string|bool $type [optional] merge for a given type only
	 * @return eMessage
	 */
	public function moveSessionStack($from_stack, $to_stack = 'default', $type = false)
	{
		// do nothing if there is still no session
		if(!session_id() || $from_stack == $to_stack) return $this;
		$SESSION = $this->getSessionHandler()->get($this->_session_id);
		
		foreach ($SESSION as $_type => $stacks)
		{
			if($type && $type !== $_type)
			{
				continue;
			}
			if(isset($stacks[$from_stack]))
			{
				if(!isset($SESSION[$_type][$to_stack]))
				{
					$SESSION[$_type][$to_stack] = array();
				}
				$SESSION[$_type][$to_stack] = array_merge($SESSION[$_type][$to_stack], $this->_sysmsg[$_type][$from_stack]);
				unset($SESSION[$_type][$from_stack]);
			}
		}
		$this->getSessionHandler()->set($this->_session_id, $SESSION);
		
		return $this;
	}
	
	/**
	 * Check passed type against the type map
	 *
	 * @param mixed $type
	 * @return boolean
	 */
	public function isType($type)
	{
		return (array_key_exists($type, $this->_type_map()));
	}

	/**
	 * Check for messages
	 *
	 * @param mixed $type
	 * @param string|bool $mstack
	 * @param boolean $session
	 * @return boolean
	 */
	public function hasMessage($type = false, $mstack = false, $session = true)
	{
		if(!$mstack) $mstack = 'default';
		
		if(false === $type)
		{
			foreach ($this->_get_types() as $_type)
			{
				if($this->get($_type, $mstack, true, false) || ($session && $this->getSession($_type, $mstack, true, false)))
				{
					return true;
				}
			}
		}
		return ($this->get($type, $mstack, true, false) || ($session && $this->getSession($type, $mstack, true, false)));
	}

	/**
	 * Balnk type array structure
	 *
	 * @return array type map
	 */
	protected function _type_map()
	{
		//show them in this order!
		return array(
			E_MESSAGE_ERROR 	=> array(),
			E_MESSAGE_WARNING 	=> array(),
			E_MESSAGE_SUCCESS 	=> array(),
			E_MESSAGE_INFO 		=> array(),
			E_MESSAGE_DEBUG		=> array()
		);
	}

	/**
	 * Get all valid message types
	 *
	 * @return array valid message types
	 */
	protected function _get_types()
	{
		return array_keys($this->_type_map());
	}
	
	/**
	 * Proxy for undefined methods. It allows quick (less arguments)
	 * call to {@link addStack()}. 
	 * Name of the method should equal to valid eMessage type - {@link _type_map()}
	 * 
	 * Example:
	 * <code>
	 * e107::getMessage()->success('Success', false);
	 * //calls internal $this->addStack('Success', E_MESSAGE_SUCCESS, false);
	 * </code>
	 * @param string $method valid message type
	 * @param array $arguments array(0 => (string) message, [optional] 1 =>(boolean) session, [optional] 2=> message stack )
	 * @return eMessage
	 * @throws Exception
	 */
	function __call($method, $arguments) {
		if($this->isType($method))
		{
			$this->addStack($arguments[0], vartrue($arguments[2], 'default'), $method, (isset($arguments[1]) && !empty($arguments[1])));
			return $this;
		}
		throw new Exception('Method eMessage::'.$method.' does not exist!');//FIXME - e107Exception handler
	}
	
	
	
	/**
	 * Automate DB system messages
	 * NOTE: default value of $output parameter will be changed to false (no output by default) in the future
	 *
	 * @param integer|bool $update return result of db::db_Query
	 * @param string $type update|insert|update
	 * @param string|bool $success forced success message
	 * @param string|bool $failed forced error message
	 * @param bool $output false suppress any function output
	 * @return integer|bool db::db_Query result
	 */
	 // TODO - This function often needs to be available BEFORE header.php is loaded. 
	 // It has been copied from admin_update() in e107_admin/header.php
	 
	public function addAuto($update, $type = 'update', $success = false, $failed = false, $output = false)
	{

		$sql = e107::getDb();

		if (($type == 'update' && $update) || ($type == 'insert' && $update !== false))
		{
			$this->add(($success ? $success : ($type == 'update' ? LAN_UPDATED : LAN_CREATED)), E_MESSAGE_SUCCESS);
		}
		elseif ($type == 'delete' && $update)
		{
			$this->add(($success ? $success : LAN_DELETED), E_MESSAGE_SUCCESS);
		}
		elseif (!$sql->getLastErrorNumber())
		{
			if ($type == 'update')
			{
				$this->add(LAN_NO_CHANGE.' '.LAN_TRY_AGAIN, E_MESSAGE_INFO);
			}
			elseif ($type == 'delete')
			{
				$this->add(LAN_DELETED_FAILED.' '.LAN_TRY_AGAIN, E_MESSAGE_INFO);
			}
		}
		else
		{
			switch ($type)
			{
				case 'insert':
					$msg = LAN_CREATED_FAILED;
				break;
				case 'delete':
					$msg = LAN_DELETED_FAILED;
				break;
				default:
					$msg = LAN_UPDATED_FAILED;
				break;
			}

			$text = ($failed ? $failed : $msg." - ".LAN_TRY_AGAIN)."<br />".LAN_ERROR." ".$sql->getLastErrorNumber().": ".$sql->getLastErrorText();
			$this->add($text, E_MESSAGE_ERROR);
		}

		if ($output) echo $this->render();
		return $update;
	}
	
	

}

function show_emessage($mode, $message, $line = 0, $file = "") {
	global $tp;

	// For critical errors where no theme is available.
	$errorHead = '
			<!doctype html>
		<html lang="en">
		<head>
		<meta charset="utf-8" />
		<title>Error</title>
		<link rel="stylesheet" media="all" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
		<link rel="stylesheet" media="all" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />
		<link rel="stylesheet" media="all" type="text/css" href="/e107_web/css/e107.css" />
		</head>
		<body >
		<div class="container" style="margin-top:100px">';


	$errorFoot = "</div></body></html>";



	if(is_numeric($message))
	{
		if(!defined('e_LANGUAGE'))
		{
			define('e_LANGUAGE', 'English');
		}

		if(!defined('e_LANGUAGEDIR'))
		{
			define('e_LANGUAGEDIR','e107_languages/');
		}

		$path = e_LANGUAGEDIR.e_LANGUAGE."/lan_error.php";

		if(is_readable($path))
		{
			include($path);
		}

    //	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_error.php");
		global $mySQLdefaultdb;

		$emessage[1] = "<b>".LAN_ERROR_25."</b>";
		$emessage[2] = "<b>".LAN_ERROR_26."</b>";
		$emessage[3] = "<b>".LAN_ERROR_27."</b>";
		$emessage[4] = "<b>".LAN_ERROR_28."</b>";
		$emessage[5] = LAN_ERROR_29;
		$emessage[6] = "<b>".LAN_ERROR_30."</b>";
		$emessage[7] = "<b>".$tp->lanVars(LAN_ERROR_31, $mySQLdefaultdb)."</b>";
		/*$emessage[8] = "
			<div style='text-align:center; font: 12px Verdana, Tahoma'><b>".LAN_ERROR_32." </b><br /><br />
			".chr(36)."ADMIN_DIRECTORY = \"e107_admin/\";<br />
			".chr(36)."FILES_DIRECTORY = \"e107_files/\";<br />
			".chr(36)."IMAGES_DIRECTORY = \"e107_images/\"; <br />
			".chr(36)."THEMES_DIRECTORY = \"e107_themes/\"; <br />
			".chr(36)."PLUGINS_DIRECTORY = \"e107_plugins/\"; <br />
			".chr(36)."HANDLERS_DIRECTORY = \"e107_handlers/\"; <br />
			".chr(36)."LANGUAGES_DIRECTORY = \"e107_languages/\"; <br />
			".chr(36)."HELP_DIRECTORY = \"e107_docs/help/\";  <br />
			".chr(36)."DOWNLOADS_DIRECTORY =  \"e107_files/downloads/\";\n
			</div>";*/
			//v2.x
		$emessage[8] = '<b>'.LAN_ERROR_32.' </b><br /><br /><pre>
$ADMIN_DIRECTORY     = "e107_admin/";
$IMAGES_DIRECTORY    = "e107_images/";
$THEMES_DIRECTORY    = "e107_themes/";
$PLUGINS_DIRECTORY   = "e107_plugins/";
$HANDLERS_DIRECTORY  = "e107_handlers/";
$LANGUAGES_DIRECTORY = "e107_languages/";
$HELP_DIRECTORY	     = "e107_docs/help/";
$MEDIA_DIRECTORY     = "e107_media/";
$SYSTEM_DIRECTORY    = "e107_system/";</pre>

		';

	}


	if (class_exists('e107table'))
	{
	  $ns = new e107table;
	}

	switch($mode)
	{
		case "CRITICAL_ERROR" :

			$message = !empty($emessage[$message]) ? $emessage[$message] : $message;

			//FIXME - this breaks class2 pref check!!! ?

		    if (is_readable(e_THEME.'error.html'))
			{
				require_once(e_THEME.'error.html');
				exit;
			}


			if(defined('e_LOG_CRITICAL'))
			{
				$date = date('r');
				@file_put_contents(e_LOG.'criticalError.log',$date."\t\t". strip_tags($message)."\n", FILE_APPEND);
				$message = LAN_ERROR_46; // "Check log for details";
				$line = null;
				$file = null;
			}


			if(!defined('HEADERF'))
			{
				echo $errorHead;
			}

			echo "<div class='alert alert-block alert-error alert-danger' style='font: 11px verdana, tahoma, arial, helvetica, sans-serif;'><h4>CRITICAL ERROR: </h4>";
			echo (!empty($line)) ? "Line $line " : "";
			echo (!empty($file)) ? $file : "";
			echo "<div>".$message."</div>";
			echo "</div>";

			if(!defined('FOOTERF'))
			{
				echo $errorFoot;
			}

			break;

		case "MESSAGE":
			if(strstr(e_SELF, "forum_post.php")) //FIXME Shouldn't be here.
			{
				return;
			}
			$ns->tablerender("", "<div class='alert alert-block' style='text-align:center'><b>{$message}</b></div>");
			break;

		case "ADMIN_MESSAGE":
			$ns->tablerender("Admin Message", "<div class='alert'><b>{$message}</b></div>");
			break;

		case "ALERT":
			$message = isset($emessage[$message]) ? $emessage[$message] : $message;
			echo "<noscript>$message</noscript><script type='text/javascript'>alert(\"".$tp->toJS($message)."\"); window.history.go(-1); </script>\n"; exit;
			break;

		case "P_ALERT":
			echo "<script type='text/javascript'>alert(\"".$tp->toJS($message)."\"); </script>\n";
			break;

		case 'POPUP':

			$mtext = "<html><head><title>Message</title><link rel=stylesheet href=" . THEME . "style.css></head><body style=padding-left:2px;padding-right:2px;padding:2px;padding-bottom:2px;margin:0px;align;center marginheight=0 marginleft=0 topmargin=0 leftmargin=0><table width=100% align=center style=width:100%;height:99%padding-bottom:2px class=bodytable height=99% ><tr><td width=100% style='text-align:center'><b>--- Message ---</b><br /><br />".$message."<br /><br /><form><input class=button type=submit onclick=self.close() value = ok /></form></td></tr></table></body></html> ";

			echo "
			<script type='text/javascript'>
			winl=(screen.width-200)/2;
			wint = (screen.height-100)/2;
			winProp = 'width=200,height=100,left='+winl+',top='+wint+',scrollbars=no';
			window.open('javascript:document.write(\"".$mtext."\");', \"message\", winProp);
			</script >";

			break;

	}
}


