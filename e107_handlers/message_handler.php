<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Message Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/message_handler.php,v $
 * $Revision: 1.13 $
 * $Date: 2009-07-31 16:11:35 $
 * $Author: secretr $
 *
*/

if (!defined('e107_INIT')) { exit; }

/*
 * Type defines
 */
define('E_MESSAGE_INFO', 	'info');
define('E_MESSAGE_SUCCESS', 'success');
define('E_MESSAGE_WARNING', 'warning');
define('E_MESSAGE_ERROR', 	'error');
define('E_MESSAGE_DEBUG', 	'debug');

//FIXME - language file! new?

/**
 * Handle system messages
 * 
 * @package e107
 * @category e107_handlers
 * @version 1.1
 * @author SecretR
 * @copyright Copyright (c) 2009, e107 Inc.
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
		
		$this->reset()->_mergeSession();
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
			$mstack = $message[0];
			$msg = $message[1];
		}

		if(!$session)
		{
			if($this->isType($type)) $this->_sysmsg[$type][$mstack][] = $msg;
			return $this;
		}
		return $this->addSession($message, $type);
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
			$mstack = $message[0];
			$message = $message[1];
		}

		if($this->isType($type)) $_SESSION[$this->_session_id][$type][$mstack][] = $message;
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
	 * Output all accumulated messages
	 *
	 * @param string $mstack message stack name
	 * @param bool $raw force return type array
	 * @param bool $reset reset all messages
	 * @param bool $session merge with session messages
	 * @return array|string messages
	 */
	public function render($mstack = 'default', $raw = false, $reset = true, $session = false)
	{
		if($session)
		{
			$this->_mergeSession(true, $mstack);
		}
		$ret = array();

		foreach ($this->_get_types() as $type)
		{
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
	protected function _mergeSession($reset = true, $mstack = false)
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

		case "POPUP":

		$mtext = "<html><head><title>Message</title><link rel=stylesheet href=" . THEME . "style.css></head><body style=padding-left:2px;padding-right:2px;padding:2px;padding-bottom:2px;margin:0px;align;center marginheight=0 marginleft=0 topmargin=0 leftmargin=0><table width=100% align=center style=width:100%;height:99%padding-bottom:2px class=bodytable height=99% ><tr><td width=100% ><center><b>--- Message ---</b><br /><br />".$message."<br /><br /><form><input class=button type=submit onclick=self.close() value = ok /></form></center></td></tr></table></body></html> ";

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