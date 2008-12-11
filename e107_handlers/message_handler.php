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
 * $Revision: 1.4 $
 * $Date: 2008-12-11 18:13:10 $
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

//FIXME - language file! new?

class eMessage
{
	/**
	 * System Message Array
	 *
	 * @var array
	 * @access private
	 */
	var $_sysmsg = array();

	/**
	 * Constructor
	 *
	 * @param string $php4_check PHP4 singleton fix
	 * @return eMessage
	 *
	 * @access protected
	 */
	function eMessage($php4_check)
	{
		if($php4_check !== 'e107_emessage_php4_very_long_hard_to_remember_check')
		{
			exit('Fatal error! You are not allowed to direct instantinate an object for singleton class! Please use eMessage::getInstance()');
		}

		if(!session_id()) session_start();

		if(isset($_SESSION['e107_system_messages']) && is_array($_SESSION['e107_system_messages']))
		{
			$this->_sysmsg = $_SESSION['e107_system_messages'];
			return $this->resetSession();
		}

		return $this->reset();
	}

	/**
	 * Get singleton instance
	 *
	 * @return object eMessage instance
	 * @access public static
	 */
	function &getInstance()
	{
	    static $instance = array();

	    if(empty($instance))
	    {
	    	$instance[0] = new eMessage('e107_emessage_php4_very_long_hard_to_remember_check');
	    }
	    return $instance[0];
	}

	/**
	 * Add message to a type stack
	 *
	 * @param string $message
	 * @param string $type
	 * @param bool $session
	 * @return eMessage
	 */
	function add($message, $type = E_MESSAGE_INFO, $session = false)
	{
		if(!$session)
		{
			if($this->isType($type)) $this->_sysmsg[$type][] = $message;
			return $this;
		}
		return $this->addSession($message, $type);
	}

	/**
	 * Add message to a _SESSION type stack
	 *
	 * @param string $message
	 * @param string $type
	 * @return eMessage
	 */
	function addSession($message, $type = E_MESSAGE_INFO)
	{
		if($this->isType($type)) $_SESSION['e107_system_messages'][$type][] = $message;
		return $this;
	}

	/**
	 * Get type title (multi-language)
	 *
	 * @param string $type
	 * @return string title
	 *
	 * @access public static
	 */
	function getTitle($type)
	{
		return defsettrue('EMESSLAN_TITLE_'.strtoupper($type), '');
	}

	/**
	 * Message getter
	 *
	 * @param string $type valid type
	 * @param bool $raw force array return
	 * @param bool $reset reset message type stack
	 * @return string|array message
	 */
	function get($type, $raw = false, $reset = true)
	{
		$message = varsettrue($this->_sysmsg[$type], array());
		if($reset) $this->reset($type);

		return (true === $raw ? $message : eMessage::formatMessage($type, $message));
	}

	/**
	 * Session message getter
	 *
	 * @param string $type valid type
	 * @param bool $raw force array return
	 * @param bool $reset reset session message type stack
	 * @return string|array session message
	 */
	function getSession($type, $raw = false, $reset = true)
	{
		$message = varsettrue($_SESSION['e107_system_messages'][$type], array());
		if($reset) $this->resetSession($type);

		return (true === $raw ? $message : eMessage::formatMessage($type, $message));
	}

	/**
	 * Output all accumulated messages
	 *
	 * @param bool $raw force return type array
	 * @param bool $reset reset all messages
	 * @param bool $session merge with session messages
	 * @return array|string messages
	 */
	function render($raw = true, $reset = true, $session = true)
	{
		if($session)
		{
			$this->_merge();
		}
		$ret = array();

		foreach ($this->_get_types() as $type)
		{
			$message = $this->get($type, $raw);
			if(!empty($message))
			{
				$ret[$type] = (true === $raw) ? $message : eMessage::formatMessage($type, $message);
			}
		}

		if($reset) $this->reset(false);
		if(true === $raw || empty($ret)) return ($raw ? $ret : '');

		//XXX keep it id (once per page) instead class for now, ask for feedback
		return "
			<div id='s-message'>
				".implode("\n", $ret)."
			</div>
		";
	}

	/**
	 * Create message block markup based on its type.
	 *
	 * @param string $type
	 * @param array|string $message
	 * @return string formated message
	 *
	 * @access public static
	 */
	function formatMessage($type, $message)
	{
		if (empty($message)) return '';
		elseif (is_array($message))
		{
			$message = "<div class='message-item'>\n".implode("</div>\n<div class='message-item'>", $message)."</div>";
		}
		return "
			<div class='{$type}'>
				<div class='message-title'>".eMessage::getTitle($type)."</div>
				<div class='message-body'>
					{$message}
				<div>
			</div>
		";
	}

	/**
	 * Reset message array
	 *
	 * @param mixed $type false for reset all, or type constant
	 * @param bool $session reset session messages as well
	 * @return eMessage
	 */
	function reset($type, $session = false)
	{
		if(false === $type) $this->_sysmsg = $this->_type_map();
		elseif(isset($this->_sysmsg[$type])) $this->_sysmsg[$type] = array();

		if($session) $this->resetSession($type);

		return $this;
	}

	/**
	 * Reset _SESSION message array
	 *
	 * @param mixed $type false for reset all, or valid type constant
	 * @return eMessage
	 */
	function resetSession($type)
	{
		if(!$type) $_SESSION['e107_system_messages'] = $this->_type_map();
		elseif(isset($_SESSION['e107_system_messages'][$type])) $_SESSION['e107_system_messages'][$type] = array();

		return $this;
	}

	/**
	 * Merge _SESSION message array with the current messages
	 *
	 * @param bool $reset
	 * @return eMessage
	 */
	function _merge($reset = true)
	{
		foreach (array_keys($_SESSION['e107_system_messages']) as $type)
		{
			if(!$this->isType($type)) continue;
			$this->_sysmsg[$type] = array_merge($this->_sysmsg[$type], $_SESSION['e107_system_messages'][$type]);
		}

		if($reset) $this->resetSession();
		return $this;
	}

	/**
	 * Check passed type against the type map
	 *
	 * @param mixed $type
	 * @return bool
	 */
	function isType($type)
	{
		return (array_key_exists($type, $this->_type_map()));
	}

	/**
	 * Balnk type array structure
	 *
	 * @return array type map
	 */
	function _type_map()
	{
		//show them in this order!
		return array(
			E_MESSAGE_ERROR 	=> array(),
			E_MESSAGE_WARNING 	=> array(),
			E_MESSAGE_SUCCESS 	=> array(),
			E_MESSAGE_INFO 		=> array()
		);
	}

	/**
	 * Get all valid message types
	 *
	 * @return array valid message types
	 */
	function _get_types()
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
