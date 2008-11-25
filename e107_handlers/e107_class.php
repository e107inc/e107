<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/e107_class.php,v $
|     $Revision: 1.25 $
|     $Date: 2008-11-25 16:26:02 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

/**
 * Core e107 class
 *
 */
class e107
{
	var $server_path;
	var $e107_dirs;
	var $http_path;
	var $https_path;
	var $base_path;
	var $file_path;
	var $relative_base_path;
	var $_ip_cache;
	var $_host_name_cache;

	/**
	 * e107 class constructor
	 *
	 * @param array $e107_paths
	 * @param string $e107_root_path
	 * @return e107
	 */
	function e107()
	{
		if(defined('e107_php4_check') && constant('e107_php4_check'))
		{
			echo ('Fatal error! You are not allowed to direct instantinate an object for singleton class! Please use e107::getInstance()');
			exit();
		}
	}

	function _init($e107_paths, $e107_root_path)
	{
		$this->e107_dirs = $e107_paths;
		$this->set_paths();
		$this->file_path = $this->fix_windows_paths($e107_root_path)."/";
	}

  /**
   * Get instance - php4 singleton implementation
   *
   * @return singleton object
   */
  function &getInstance()
  {
    static $instance = array();//it's array because of an odd PHP 4 bug

    if(!$instance)
    {
        $instance[0] = new e107();
        define('e107_php4_check', true);
    }
    return $instance[0];
  }

	function set_base_path()
	{
		global $pref;
		$this->base_path = ($pref['ssl_enabled']==1 ?  $this->https_path : $this->http_path);
	}


	function set_paths()
	{
		global $DOWNLOADS_DIRECTORY, $ADMIN_DIRECTORY, $IMAGES_DIRECTORY, $THEMES_DIRECTORY, $PLUGINS_DIRECTORY,
		$FILES_DIRECTORY, $HANDLERS_DIRECTORY, $LANGUAGES_DIRECTORY, $HELP_DIRECTORY, $CACHE_DIRECTORY,
		$NEWSIMAGES_DIRECTORY, $CUSTIMAGES_DIRECTORY, $UPLOADS_DIRECTORY,$_E107;

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
		define("e_ROOT", realpath(dirname(__FILE__)."/../")."/");

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
			define("e_ADMIN", e_BASE.$ADMIN_DIRECTORY);
			define("e_IMAGE", e_BASE.$IMAGES_DIRECTORY);
			define("e_THEME", e_BASE.$THEMES_DIRECTORY);
			define("e_PLUGIN", e_BASE.$PLUGINS_DIRECTORY);
			define("e_FILE", e_BASE.$FILES_DIRECTORY);
			define("e_HANDLER", e_BASE.$HANDLERS_DIRECTORY);
			define("e_LANGUAGEDIR", e_BASE.$LANGUAGES_DIRECTORY);
			define("e_DOCS", e_BASE.$HELP_DIRECTORY);
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
            	define("e_CACHE", e_BASE.$FILES_DIRECTORY."cache/");
			}

			if($NEWSIMAGES_DIRECTORY)
			{
            	define("e_NEWSIMAGE", e_BASE.$NEWSIMAGES_DIRECTORY);
			}
			else
			{
            	define("e_NEWSIMAGE", e_IMAGE."newspost_images/");
			}

			if($CUSTIMAGES_DIRECTORY)
			{
            	define("e_CUSTIMAGE", e_BASE.$CUSTIMAGES_DIRECTORY);
			}
			else
			{
            	define("e_CUSTIMAGE", e_IMAGE."custom/");
			}

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
			}
		}
	}

	function fix_windows_paths($path)
	{
		$fixed_path = str_replace(array('\\\\', '\\'), array('/', '/'), $path);
		$fixed_path = (substr($fixed_path, 1, 2) == ":/" ? substr($fixed_path, 2) : $fixed_path);
		return $fixed_path;
	}

	/**
	 * Check if current user is banned
	 *
	 */
	function ban()
	{
		global $sql, $e107, $tp, $pref;
		$ban_count = $sql->db_Count("banlist");
		if($ban_count)
		{
			$vals = array();
			$ip = $this->getip();			// This will be in normalised IPV6 form
			if ($ip != 'x.x.x.x')
			{
				$vals[] = $ip;				// Always look for exact match
				if (strpos($ip,'0000:0000:0000:0000:0000:ffff:') === 0)
				{	// It's an IPV4 address
					$vals[] = substr($ip,0,-2).'*';
					$vals[] = substr($ip,0,-4).'*';
					$vals[] = substr($ip,0,-7).'*';		// Knock off colon as well here
				}
				else
				{	// Its an IPV6 address - ban in blocks of 16 bits
					$vals[] = substr($ip,0,-4).'*';
					$vals[] = substr($ip,0,-9).'*';
					$vals[] = substr($ip,0,-14).'*';
				}
			}

		if(varsettrue($pref['enable_rdns']))
		{
		  $tmp = array_reverse(explode('.',$addr = $e107->get_host_name(getenv('REMOTE_ADDR'))));
		  $line = '';
//		  $vals[] = $addr;
		  foreach ($tmp as $e)
		  {
			$line = '.'.$e.$line;
			$vals[] = '*'.$line;
		  }
		}

		if ((defined('USEREMAIL') && USEREMAIL))
		{
		  $vals[] = USEREMAIL;
		}

		if (($ip != '127.0.0.1') && count($vals))
		{
		  $match = "`banlist_ip`='".implode("' OR `banlist_ip`='",$vals)."'";
		  $this->check_ban($match);
		}
	  }
	}


	// Check the banlist table. $query is used to determine the match.
	// If $show_error, displays "HTTP/1.1 403 Forbidden"
	// If $do_return, will always return with ban status - TRUE for OK, FALSE for banned.
	// If return permitted, will never display a message for a banned user; otherwise will display any message then exit
	function check_ban($query,$show_error=TRUE, $do_return = FALSE)
	{
	  global $sql, $tp, $pref, $admin_log, $e107;
//	  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Check for Ban",$query,FALSE,LOG_TO_ROLLING);
	  if ($sql->db_Select('banlist','*',$query.' ORDER BY `banlist_bantype` DESC'))
	  {
		// Any whitelist entries will be first - so we can answer based on the first DB record read
		define('BAN_TYPE_WHITELIST',100);			// Entry for whitelist
		$row = $sql->db_Fetch();
		if ($row['banlist_bantype'] >= BAN_TYPE_WHITELIST)
		{
//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Whitelist hit",$query,FALSE,LOG_TO_ROLLING);
		  return TRUE;
		}

		// Found banlist entry in table here
		if (($row['banlist_banexpires'] > 0) && ($row['banlist_banexpires'] < time()))
		{	// Ban has expired - delete from DB
		  $sql->db_Delete('banlist', $query);
		  return TRUE;
		}

		if (varsettrue($pref['ban_retrigger']) && varsettrue($pref['ban_durations'][$row['banlist_bantype']]))
		{	// May need to retrigger ban period
		  $sql->db_UpdateArray('banlist',
			"`banlist_banexpires`=".intval(time() + ($pref['ban_durations'][$row['banlist_bantype']]*60*60)),
			"WHERE `banlist_ip`='{$row['banlist_ip']}'");
//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Retrigger Ban",$row['banlist_ip'],FALSE,LOG_TO_ROLLING);
		}
//	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Active Ban",$query,FALSE,LOG_TO_ROLLING);
		if ($show_error) header("HTTP/1.1 403 Forbidden", true);
		if (isset($pref['ban_messages']))
		{  // May want to display a message
		  // Ban still current here
		  if ($do_return) return FALSE;
		  echo $tp->toHTML(varsettrue($pref['ban_messages'][$row['banlist_bantype']]));		// Show message if one set
		}
		$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,'BAN_03','LAN_AUDIT_LOG_003',$query,FALSE,LOG_TO_ROLLING);
		exit();
	  }
//	  $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","No ban found",$query,FALSE,LOG_TO_ROLLING);
	  return TRUE;			// Email address OK
	}


	// Add an entry to the banlist. $bantype = 1 for manual, 2 for flooding, 4 for multiple logins
	// Returns TRUE if ban accepted.
	// Returns FALSE if ban not accepted (i.e. because on whitelist, or invalid IP specified)
	function add_ban($bantype,$ban_message='',$ban_ip='',$ban_user = 0,$ban_notes='')
	{
	  global $sql, $pref, $e107;
	  if (!$ban_message) $ban_message = 'No explanation given';
	  if (!$ban_ip) $ban_ip = $this->getip();
	  $ban_ip = preg_replace("/[^\w@\.]*/",'',urldecode($ban_ip));		// Make sure no special characters
	  if (!$ban_ip) return FALSE;
	  // See if the address is in the whitelist
	  if ($sql->db_Select('banlist','*','`banlist_bantype` >= '.BAN_TYPE_WHITELIST))
	  { // Got a whitelist entry for this
	    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"BANLIST_11",'LAN_AL_BANLIST_11',$ban_ip,FALSE,LOG_TO_ROLLING);
		return FALSE;
	  }
	  if (varsettrue($pref['enable_rdns_on_ban']))
	  {
		$ban_message .= 'Host: '.$e107->get_host_name($ban_ip);
	  }
	  // Add using an array - handles DB changes better
	  $sql->db_Insert('banlist',array('banlist_ip' => $ban_ip, 'banlist_bantype' => $bantype, 'banlist_datestamp' => time(),
		'banlist_banexpires' => (varsettrue($pref['ban_durations'][$bantype]) ? time() + ($pref['ban_durations'][$bantype]*60*60) : 0),
		'banlist_admin' => $ban_user, 'banlist_reason' => $ban_message, 'banlist_notes' => $ban_notes));
	  return TRUE;
	}


	/**
	 * Get the current user's IP address
	 *
	 * @return string
	 * returns the address in internal 'normalised' IPV6 format - so most code should continue to work provided the DB Field is big enougn
	 */
	function getip()
	{
		if(!$this->_ip_cache)
		{
			if (getenv('HTTP_X_FORWARDED_FOR'))
			{
				$ip=$_SERVER['REMOTE_ADDR'];
				if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip3))
				{
					$ip2 = array('#^0\..*#',
						'#^127\..*#', 							// Local loopbacks
						'#^192\.168\..*#', 						// RFC1918 - Private Network
						'#^172\.(?:1[6789]|2\d|3[01])\..*#', 	// RFC1918 - Private network
						'#^10\..*#', 							// RFC1918 - Private Network
						'#^169\.254\..*#', 						// RFC3330 - Link-local, auto-DHCP
						'#^2(?:2[456789]|[345][0-9])\..*#'		// Single check for Class D and Class E
						);
					$ip = preg_replace($ip2, $ip, $ip3[1]);
				}
			}
			else
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			if ($ip == "")
			{
				$ip = "x.x.x.x";
			}
			$this->_ip_cache = $this->ipEncode($ip);			// Normalise for storage
		}
		return $this->_ip_cache;
	}


	// Encode an IP address to internal representation. Returns string if successful; FALSE on error
	// Default separates fields with ':'; set $div='' to produce a 32-char packed hex string
	function ipEncode($ip, $div=':')
	{
		$ret = '';
		$divider = '';
		if (strstr($ip,':'))
		{   // Its IPV6 (could have an IP4 'tail')
			if (strstr($ip,'.'))
			{  // IPV4 'tail' to deal with
				$temp = strrpos($ip,':') +1;
				$ip4 = substr($ip,$temp);
				$ip = substr($ip,0, $temp).$this->ip4_encode($ip4);
			}
			// Now 'normalise' the address
			$temp = explode(':',$ip);
			$s = 8 - count($temp);		// One element will of course be the blank
			foreach ($temp as $f)
			{
				if ($f == '')
				{
					$ret .= $divider.'0000';		// Always put in one set of zeros for the blank
					$divider = $div;
					if ($s > 0)
					{
						$ret .= str_repeat($div.'0000',$s);
						$s = 0;
					}
				}
				else
				{
					$ret .= $divider.sprintf('%04x',hexdec($f));
					$divider = $div;
				}
			}
			return $ret;
		}
		if (strstr($ip,'.'))
		{  // Its IPV4
			$ipa = explode('.', $ip);
			$temp = sprintf('%02x%02x%s%02x%02x', $ipa[0], $ipa[1], $div, $ipa[2], $ipa[3]);
			return str_repeat('0000'.$div,5).'ffff'.$div.$temp;
		}
		return FALSE;		// Unknown
	}


	// Takes an encoded IP address - returns a displayable one
	// Set $IP4Legacy TRUE to display 'old' (IPv4) addresses in the familiar dotted format, FALSE to display in standard IPV6 format
	// Should handle most things that can be thrown at it.
	function ipDecode($ip, $IP4Legacy = TRUE)
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


	// Given a string which may be IP address, email address etc, tries to work out what it is
	function whatIsThis($string)
	{
		if (strstr($string,'@')) return 'email';		// Email address
		if (strstr($string,'http://')) return 'url';
		if (strstr($string,'ftp://')) return 'ftp';
		$string = strtolower($string);
		if (str_replace(' ','',strtr($string,'0123456789abcdef.:*','                   ')) == '')	// Delete all characters found in ipv4 or ipv6 addresses, plus wildcards
		{
			return 'ip';
		}
		return 'unknown';
	}

	function get_host_name($ip_address)
	{
		if(!$this->_host_name_cache[$ip_address])
		{
			$this->_host_name_cache[$ip_address] = gethostbyaddr($ip_address);
		}
		return $this->_host_name_cache[$ip_address];
	}


	// Return a memory value formatted helpfully
	// $dp overrides the number of decimal places displayed - realistically, only 0..3 are sensible
	function parseMemorySize($size, $dp = 2)
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
	 *
	 * @return string memory usage
	 */
	function get_memory_usage()
	{
		if(function_exists("memory_get_usage"))
		{
	      $ret = $this->parseMemorySize(memory_get_usage());
		  // With PHP>=5.2.0, can show peak usage as well
	      if (function_exists("memory_get_peak_usage")) $ret .= '/'.$this->parseMemorySize(memory_get_peak_usage(TRUE));
		  return $ret;
		}
		else
		{
		  return ('Unknown');
		}
	}

}
?>
