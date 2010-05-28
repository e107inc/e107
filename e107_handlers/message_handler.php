<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
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
define('E_MESSAGE_INFO', 		'info');
define('E_MESSAGE_SUCCESS', 	'success');
define('E_MESSAGE_WARNING', 	'warning');
define('E_MESSAGE_ERROR', 		'error');
define('E_MESSAGE_DEBUG', 		'debug');

//FIXME - language file! new?

/**
 * Handle system messages
 * 
 * @package e107
 *	@subpackage	e107_handlers
 * @version $Id$
 * @author SecretR
 * @copyright Copyright (C) 2008-2010 e107 Inc (e107.org)
 */
class eMessage
{
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
	 * Singleton instance
	 * 
	 * @var eMessage
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
		if(!session_id()) session_start();
		
		require_once(e_HANDLER.'e107_class.php');
		$this->_session_id = e107::getPref('cookie_name', 'e107').'_system_messages';
		
		//clean up old not used sessions
		$tmp = array_keys($_SESSION);
		foreach ($tmp as $key)
		{
			if($key != $this->_session_id && strpos($key, '_system_messages'))
			{
				unset($_SESSION[$key]);
			}
		}
		unset($tmp);
		
		if(!isset($_SESSION[$this->_session_id]))
		{
			$_SESSION[$this->_session_id] = array();
		}
		
		$this->reset()->mergeWithSession();
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
	 * @return eMessage
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
	 * Set message session id
	 * @param string $name 
	 * @return object $this
	 */
	public function setSessionId($name)
	{
		$this->_session_id = $name.'_system_messages';
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
	public function add($message, $type = E_MESSAGE_INFO, $session = false)
	{
		if(empty($message)) return $this;
		
		$mstack = 'default';
		$msg = $message;
		if(is_array($message))
		{
			$mstack = $message[1];
			$msg = $message[0];
		}

		if(!$session)
		{
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
	 * @param boolean $sesion [optional]
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
	public function addDebug($message, $mstack = 'default', $session = false)
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
		if(empty($message)) return $this;
		
		$mstack = 'default';
		if(is_array($message))
		{
			$mstack = $message[1];
			$message = $message[0];
		}

		if($this->isType($type)) $_SESSION[$this->_session_id][$type][$mstack][] = $message;
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
	 * @param boolean $sesion [optional]
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
		if($message_stack && $message_stack != 'default' && defined('EMESSLAN_TITLE_'.strtoupper($type.'_'.$message_stack)))
		{
			return constant('EMESSLAN_TITLE_'.strtoupper($type.'_'.$message_stack));
		}
		return defsettrue('EMESSLAN_TITLE_'.strtoupper($type), '');
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
		$message = isset($_SESSION[$this->_session_id][$type][$mstack]) ? $_SESSION[$this->_session_id][$type][$mstack] : '';
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
	 * Output all accumulated messages
	 *
	 * @param string $mstack message stack name
	 * @param bool $session merge with session messages
	 * @param bool $reset reset all messages
	 * @param bool $raw force return type array
	 * @return array|string messages
	 */
	public function render($mstack = 'default', $session = false, $reset = true, $raw = false)
	{
		if($session)
		{
			$this->mergeWithSession(true, $mstack);
		}
		$ret = array();

		foreach ($this->_get_types() as $type)
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

		//changed to class
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
		if (empty($message)) return '';
		elseif (is_array($message))
		{
			$message = "<div class='s-message-item'>".implode("</div>\n<div class='s-message-item'>", $message)."</div>";
		}
		return "
			<div class='{$type}'>
				<div class='s-message-title'>".self::getTitle($type, $mstack)."</div>
				<div class='s-message-body'>
					{$message}
				</div>
			</div>
		";
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
		if(false === $type) 
		{
			if(false === $mstack)
			{
				$_SESSION[$this->_session_id] = $this->_type_map();
			}
			elseif($_SESSION[$this->_session_id])
			{
				foreach ($_SESSION[$this->_session_id] as $t => $_mstack) 
				{
					if(is_array($_mstack))
					{
						unset($_SESSION[$this->_session_id][$t][$mstack]);
					}
				}
			}
		}
		elseif(isset($_SESSION[$this->_session_id][$type])) 
		{
			if(false === $mstack)
			{
				$_SESSION[$this->_session_id][$type] = array();
			}
			elseif(is_array($_SESSION[$this->_session_id][$type])) 
			{
				unset($_SESSION[$this->_session_id][$type][$mstack]);
			}
		}

		return $this;
	}

	/**
	 * Merge _SESSION message array with the current messages
	 * 
	 * @param boolean $reset
	 * @return eMessage
	 */
	public function mergeWithSession($reset = true, $mstack = false)
	{
		if(is_array($_SESSION[$this->_session_id]))
		{
			foreach (array_keys($_SESSION[$this->_session_id]) as $type)
			{
				if(!$this->isType($type)) 
				{ 
					unset($_SESSION[$this->_session_id][$type]);
					continue;
				}
				if(false === $mstack)
				{
					$this->_sysmsg[$type] = array_merge_recursive($this->_sysmsg[$type], $_SESSION[$this->_session_id][$type]);
					continue;
				}
				
				if(isset($_SESSION[$this->_session_id][$type][$mstack]))
				{
					$this->_sysmsg[$type][$mstack] = $_SESSION[$this->_session_id][$type][$mstack];
				}
				
			}
		}
		if($reset) $this->resetSession(false, $mstack);
		return $this;
	}
	
	/**
	 * Convert current messages to Session messages 
	 *
	 * @param string $mstack false - move all message stacks
	 * @param string $message_type false - move all types
	 * @return unknown
	 */
	public function moveToSession($mstack = false, $message_type = false)
	{
		foreach (array_keys($this->_sysmsg) as $type)
		{
			if(!$this->isType($type) || ($message_type && $message_type !== $type)) 
			{ 
				unset($this->_sysmsg[$type]);
				continue;
			}
			if(false === $mstack)
			{
				$_SESSION[$this->_session_id][$type] = array_merge_recursive( $_SESSION[$this->_session_id][$type], $this->_sysmsg[$type]);
				continue;
			}
			
			if(isset($this->_sysmsg[$type][$mstack]))
			{
				$_SESSION[$this->_session_id][$type][$mstack] = $this->_sysmsg[$type][$mstack];
			}
		}

		$this->reset($message_type, $mstack, false);
		return $this;
	}
	
	/**
	 * Merge messages from source stack with destination stack
	 * and reset source stack
	 * 
	 * @param string $from_stack source stack
	 * @param string $to_stack [optional] destination stack
	 * @param string $type [optional] merge for a given type only
	 * @param string $session [optional] merge session as well
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
	 * @param string $type [optional] merge for a given type only
	 * @return eMessage
	 */
	public function moveSessionStack($from_stack, $to_stack = 'default', $type = false)
	{
		if($from_stack == $to_stack) return $this;
		foreach ($_SESSION[$this->_session_id] as $_type => $stacks)
		{
			if($type && $type !== $_type)
			{
				continue;
			}
			if(isset($stacks[$from_stack]))
			{
				if(!isset($_SESSION[$this->_session_id][$_type][$to_stack]))
				{
					$_SESSION[$this->_session_id][$_type][$to_stack] = array();
				}
				$_SESSION[$this->_session_id][$_type][$to_stack] = array_merge($_SESSION[$this->_session_id][$_type][$to_stack], $this->_sysmsg[$_type][$from_stack]);
				unset($_SESSION[$this->_session_id][$_type][$from_stack]);
			}
		}
		
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
	 * @param string $mstack
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

}

function show_emessage($mode, $message, $line = 0, $file = "") {
	global $tp;
	if(is_numeric($message))
	{
    	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_error.php");
	$emessage[1] = "<b>".LAN_ERROR_25."</b>";
	$emessage[2] = "<b>".LAN_ERROR_26."</b>";
	$emessage[3] = "<b>".LAN_ERROR_27."</b>";
	$emessage[4] = "<b>".LAN_ERROR_28."</b>";
	$emessage[5] = LAN_ERROR_29;
	$emessage[6] = "<b>".LAN_ERROR_30."</b>";
	$emessage[7] = "<b>".LAN_ERROR_31."</b>";
	$emessage[8] = "
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
		</div>";
	}

	if (class_exists('e107table'))
	{
	  $ns = new e107table;
	}
	switch($mode)
	{
	  case "CRITICAL_ERROR" :
		$message = $emessage[$message] ? $emessage[$message] : $message;
		//FIXME - this breaks class2 pref check!!!
	    if (is_readable(e_THEME.'index.html'))
		{
		  require_once(e_THEME.'index.html');
		  exit;
		}
		echo "<div style='text-align:center; font: 11px verdana, tahoma, arial, helvetica, sans-serif;'><b>CRITICAL_ERROR: </b><br />Line $line $file<br /><br />Error reported as: ".$message."</div>";
		break;

	  case "MESSAGE":
		if(strstr(e_SELF, "forum_post.php"))
		{
			return;
		}
		$ns->tablerender("", "<div style='text-align:center'><b>{$message}</b></div>");
		break;

		case "ADMIN_MESSAGE":
		$ns->tablerender("Admin Message", "<div style='text-align:center'><b>{$message}</b></div>");
		break;

		case "ALERT":
		$message = $emessage[$message] ? $emessage[$message] : $message;
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

?>