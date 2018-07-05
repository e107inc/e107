<?php
/*
* e107 website system
*
* Copyright 2008-2013 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* IP Address related routines, including banning-related code
*
* $URL$
* $Revision$
* $Id$
*
*/


/**
* @package e107
* @subpackage e107_handlers
* @version $Id$;
*
* Routines to manage IP addresses and banning.
*/



/**
 *	Class to handle ban-related checks, and provide some utility functions related to IP addresses
 *	There are two parts to the class:
 *	
 *	Part 1
 *	------
 *	This part intentionally does NO database access, and requires an absolute minimum of file paths to be set up
 *	(this is to minimise processing load in the event of an access from a banned IP address)
 *	It works only with the user's IP address, and potentially browser 'signature'
 *	The objective of this part is to do only those things which can be done without the database open, and without complicating things later on
 *	(If DB access is required to handle a ban, it should only need to be done occasionally)
 *
 *	Part 2
 *	------
 *	This part handles those functions which require DB access.
 *	The intention is that Part 1 will catch most existing bans, to reduce the incidence of abortive DB opens
 *	If part 1 signals that a ban has expired, part 2 removes it from the database
 *
 *	Elsewhere
 *	---------
 *	if ban retriggering is enabled, cron task needs to scan the ban log periodically to update the expiry times. (Can't do on every access, since it would
 *		eliminate the benefits of this handler - a DB access would be needed on every access from a banned IP address).
 *	@todo	Implement the ban retriggering cron job (elsewhere)
 *				- do we have a separate text file for the accesses in need of retriggering? Could then delete it once actioned; keeps it small
 *	@todo	Implement flood bans - needs db access - maybe leave to the second part of this file or the online handler
 *
 *	All IP addresses are stored in 'normal' form - a fixed length IPV6 format with separator colons.
 *
 *	To use:
 *		include this file, early on (before DB accesses started), and instantiate class ipHandler.
 *
 */


class eIPHandler
{
	/**
	 * IPV6 string for localhost - as stored in DB
	 */
//	const LOCALHOST_IP = '0000:0000:0000:0000:0000:ffff:7f00:0001';


	const BAN_REASON_COUNT =	7;				// Update as more ban reasons added (max 10 supported)

	const BAN_TYPE_LEGACY = 	0;				// Shouldn't get these unless update process not run
	const BAN_TYPE_MANUAL = 	-1;				/// Manually entered bans
	const BAN_TYPE_FLOOD  = 	-2;				/// Flood ban
	const BAN_TYPE_HITS = 		-3;
	const BAN_TYPE_LOGINS = 	-4;
	const BAN_TYPE_IMPORTED = 	-5;				/// Imported bans
	const BAN_TYPE_USER = 		-6;				/// User is banned
												// Spare value
	const BAN_TYPE_UNKNOWN = 	-8;
	const BAN_TYPE_TEMPORARY =	-9;				/// Used during CSV import - giving it this value highlights problems

	const BAN_TYPE_WHITELIST = 	100;			/// Entry for whitelist - actually not a ban at all! Keep at this value for BC


	const BAN_FILE_DIRECTORY 	= 'cache/';				/// Directory containing the text files (within e_SYSTEM)
	const BAN_LOG_DIRECTORY 	= 'logs/';				/// Directory containing the log file (within e_SYSTEM)

	const BAN_FILE_LOG_NAME 	= 'banlog.log';			/// Logs bans etc
	// Note for the following file names - the code appends the extension
	const BAN_FILE_IP_NAME 		= 'banlist';			/// Saves list of banned and whitelisted IP addresses
	const BAN_FILE_ACTION_NAME	= 'banactions';			/// Details of actions for different ban types
	const BAN_FILE_HTACCESS 	= 'banhtaccess';		/// File in format for direct paste into .htaccess
	const BAN_FILE_CSV_NAME 	= 'banlistcsv';			/// Output file in CSV format
	const BAN_FILE_RETRIGGER_NAME = 'banretrigger';		/// Any bans needing retriggering
	const BAN_FILE_EXTENSION 	= '.php';				/// File extension to use

	/**
	 *	IP address of current user, in 'normal' form
	 */
	private $ourIP = '';

	private $serverIP = '';

	private $debug = false;
	/**
	 *	Host name of current user
	 *	Initialised when requested
	 */
	private $_host_name_cache = '';


	/**
	 *	Token for current user, calculated from browser settings.
	 *	Supplements IP address (Can be spoofed, but helps differentiate among honest users at the same IP address)
	 */
	private $accessID = '';

	/**
	 *	Path to directory containing current config file(s)
	 */
	private	$ourConfigDir = '';

	/**
	 *	Current user's IP address status. Usually zero (neutral); may be one of the BAN_TYPE_xxx constants
	 */
	private $ipAddressStatus = 0;


	/**
	 *	Flag set to the IP address that triggered the match, if current IP has an expired ban to clear
	 */
	private $clearBan = FALSE;


	/**
	 *	IP Address from ban list file which matched (may have wildcards)
	 */
	private $matchAddress = '';

	/**
	 *	Number of entries read from banlist/whitelist
	 */
	private $actionCount = 0;

	/**
	 *	Constructor
	 *
	 *	Only one instance of this class is ever loaded, very early on in the initialisation sequence
	 *
	 *	@param	string	$configDir	Path to the directory containing the files used by this class
	 *								If not set, defaults to BAN_FILE_DIRECTORY constant
	 *
	 *	On load it gets the user's IP address, and checks it against whitelist and blacklist files
	 *	If the address is blacklisted, displays an appropriate message (as configured) and aborts
	 *	Otherwise sets up 
	 */
	public function __construct($configDir = '')
	{
		$configDir = trim($configDir);

		if ($configDir)
		{
			$this->ourConfigDir = realpath($configDir);
		}
		else
		{
			$this->ourConfigDir = e_SYSTEM.eIPHandler::BAN_FILE_DIRECTORY;
		}


		$this->ourIP = $this->ipEncode($this->getCurrentIP());

		$this->serverIP = $this->ipEncode($_SERVER['SERVER_ADDR']);

		$this->makeUserToken();
		$ipStatus = $this->checkIP($this->ourIP);
		if ($ipStatus != 0)
		{
			if ($ipStatus < 0)
			{	// Blacklisted
				$this->logBanItem($ipStatus, 'result --> '.$ipStatus); // only log blacklist
				$this->banAction($ipStatus);		// This will abort if appropriate
			}
			elseif ($ipStatus > 0)
			{	// Whitelisted - we may want to set a specific indicator
			}
		}
		// Continue here - user not banned (so far)
	}

	public function setIP($ip)
	{
		$this->ourIP = $this->ipEncode($ip);

	}


	public function debug($value)
	{
		$this->debug = ($value === true) ? true: false;
	}




	/**
	 *	Add an entry to the banlist log file (which is a simple text file)
	 *	A date/time string is prepended to the line
	 *
	 *	@param int $reason - numeric reason code, usually in range -10..+10
	 *	@param string $message - additional text as required (length not checked, but should be less than 100 characters or so
	 *
	 *	@return void
	 */
	private function logBanItem($reason, $message)
	{
		if ($tmp = fopen(e_SYSTEM.eIPHandler::BAN_LOG_DIRECTORY.eIPHandler::BAN_FILE_LOG_NAME, 'a'))
		{
			$logLine = time().' '.$this->ourIP.' '.$reason.' '.$message."\n";
			fwrite($tmp,$logLine);
			fclose($tmp);
		}
	}

	

	/**
	 *	Generate relatively unique user token from browser info
	 *		(but don't believe that the browser info is accurate - can readily be spoofed)
	 *
	 *	This supplements use of the IP address in some places; both to improve user identification, and to help deal with dynamic IP allocations
	 *
	 *	May be replaced by a 'global' e107 token at some point
	 */
	private function makeUserToken()
	{
		$tmpStr = '';
		foreach (array('HTTP_USER_AGENT', 'HTTP_ACCEPT', 'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_ACCEPT_ENCODING') as $v)
		{
			if (isset($_SERVER[$v]))
			{
				$tmpStr .= $_SERVER[$v];
			}
			else
			{
				$tmpStr .= 'dummy'.$v;
			}
		}
		$this->accessID = md5($tmpStr);
	}



	/**
	 *	Return browser-characteristics token
	 */
	public function getUserToken()
	{
		return $this->accessID;				// Should always be defined at this point
	}



	/**
	 *	Check whether an IP address is routable
	 *
	 *	@param string $ip - IPV4 or IPV6 numeric address.
	 *
	 *	@return boolean TRUE if routable, FALSE if not
	 
	 @todo handle IPV6 fully
	 */
	public function isAddressRoutable($ip)
	{
		$ignore = array(
						'0\..*' , '^127\..*' , 			// Local loopbacks
						'192\.168\..*' , 					// RFC1918 - Private Network
						'172\.(?:1[6789]|2\d|3[01])\..*' ,	// RFC1918 - Private network
						'10\..*' , 							// RFC1918 - Private Network
						'169\.254\..*' , 					// RFC3330 - Link-local, auto-DHCP
						'2(?:2[456789]|[345][0-9])\..*'		// Single check for Class D and Class E
					);
	
		
		
		$pattern = '#^('.implode('|',$ignore).')#';
				
		if(preg_match($pattern,$ip))
		{
			return false;	
		}
		
		
		/* XXX preg_match doesn't accept arrays. 
		if (preg_match(array(
						'#^0\..*#' , '#^127\..*#' , 			// Local loopbacks
						'#^192\.168\..*#' , 					// RFC1918 - Private Network
						'#^172\.(?:1[6789]|2\d|3[01])\..*#' ,	// RFC1918 - Private network
						'#^10\..*#' , 							// RFC1918 - Private Network
						'#^169\.254\..*#' , 					// RFC3330 - Link-local, auto-DHCP
						'#^2(?:2[456789]|[345][0-9])\..*#'		// Single check for Class D and Class E
					), $ip))
		{
			return FALSE;
		} 
		*/
		
		if (strpos(':', $ip) === FALSE) return TRUE;
		// Must be an IPV6 address here
		// @todo need to handle IPV4 addresses in IPV6 format
		$ip = strtolower($ip);
		if ($ip == 'ff02::1') return FALSE; 			// link-local all nodes multicast group
		if ($ip == 'ff02:0000:0000:0000:0000:0000:0000:0001') return FALSE;
		if ($ip == '::1') return FALSE;											// localhost
		if ($ip == '0000:0000:0000:0000:0000:0000:0000:0001') return FALSE;
		if (substr($ip, 0, 5) == 'fc00:') return FALSE;							// local addresses
		// @todo add:
		// ::0 (all zero) - invalid
		// ff02::1:ff00:0/104 - Solicited-Node multicast addresses - add?
		// 2001:0000::/29 through 2001:01f8::/29 - special purpose addresses
		// 2001:db8::/32 - used in documentation
		return TRUE;
	}



	/**
	 *	Get current user's IP address in 'normal' form.
	 *	Likely to be very similar to existing e107::getIP() function
	 *	May log X-FORWARDED-FOR cases - or could generate a special IPV6 address, maybe?
	 */
	private function getCurrentIP()
	{
		if(!$this->ourIP)
		{
			$ip = $_SERVER['REMOTE_ADDR'];
			if ($ip4 = getenv('HTTP_X_FORWARDED_FOR'))
			{
				if (!$this->isAddressRoutable($ip))
				{
					$ip3 = explode(',', $ip4);				// May only be one address; could be several, comma separated, if multiple proxies used
					$ip = trim($ip3[sizeof($ip3) - 1]);						// If IP address is unroutable, replace with any forwarded_for address
					$this->logBanItem(0, 'X_Forward  '.$ip4.' --> '.$ip);		// Just log for interest ATM
				}
			}
			if($ip == '')
			{
				$ip = 'x.x.x.x';
			}
			$this->ourIP = $this->ipEncode($ip); 				// Normalise for storage
		}
		return $this->ourIP;
	}



	/**
	 *	Return the user's IP address, in normal or display-friendly form as requested
	 *
	 *	@param boolean $forDisplay - TRUE for minimum-length display-friendly format. FALSE for 'normal' form (to be used when storing into DB etc)
	 *
	 *	@return string IP address
	 *
	 *	Note: if we define USER_IP (and maybe USER_DISPLAY_IP) constant, this function is strictly unnecessary. But we still need a format conversion routine
	 */
	public function getIP($forDisplay = FALSE)
	{
		if ($forDisplay == FALSE) return $this->ourIP;
		return $this->ipDecode($this->ourIP);
	}



	/**
	 *	Takes appropriate action for a blacklisted IP address
	 *
	 *	@param int $code - integer value < 0 specifying the ban reason.
	 *
	 *	@return void (may not even return)
	 *
	 *	Looks up the reason code, and extracts the corresponding text. 
	 *	If this text begins with 'http://' or 'https://', assumed to be a link to a web page, and redirects.
	 *	Otherwise displays an error message to the user (if configured) then aborts.
	 */
	private function banAction($code)
	{
		$search = '['.$code.']';
		$fileName = $this->ourConfigDir.eIPHandler::BAN_FILE_ACTION_NAME.eIPHandler::BAN_FILE_EXTENSION;

		if(!is_readable($fileName)) // Note readable, but the IP is still banned, so half further script execution.
		{
			if($this->debug === true || e_DEBUG === true)
			{
				echo "Your IP is banned!";
			}

			die();
		    // return;		//
		}

		$vals  = file($fileName);
		if ($vals === FALSE || count($vals) == 0) return;
		if (substr($vals[0], 0, 5) != '<?php')
		{
			echo 'Invalid message file';
			die();
		}
		unset($vals[0]);
		foreach ($vals as $line)
		{
			if (substr($line, 0, 1) == ';') continue;
			if (strpos($line, $search) === 0)
			{	// Found the action line
				if (e107::getPref('ban_retrigger'))
				{
					if ($tmp = fopen($this->ourConfigDir.eIPHandler::BAN_FILE_RETRIGGER_NAME.eIPHandler::BAN_FILE_EXTENSION, 'a'))
					{
						$logLine = time().' '.$this->matchAddress.' '.$code.' Retrigger: '.$this->ourIP."\n";	// Same format as log entries - can share routines
						fwrite($tmp,$logLine);
						fclose($tmp);
					}
				}
				$line = trim(substr($line, strlen($search)));
				if ((strpos($line, 'http://') === 0) || (strpos($line, 'https://') === 0))
				{	// Display a specific web page
					if (strpos($line, '?') === FALSE)
					{
						$line .= '?'.$search;			// Add on the ban reason - may be useful in the page
					}
					e107::redirect($line);
					exit();
				}
				// Otherwise just display any message and die
				if($this->debug)
				{
					print_a("User Banned");
				}

				echo $line;

				die();
			}
		}
		$this->logBanItem($code, 'Unmatched action: '.$search.' - no block implemented');
	}



	/**
	 *	Get whitelist and blacklist
	 *
	 *	@return array  - each element is an array with elements 'ip', 'action, and 'time_limit'
	 *
	 *	Note: Intentionally a single call, so the two lists can be split across files as convenient
	 *
	 *	At present the list is a single file, one entry per line, whitelist entries first. Most precisely defined addresses before larger subnets
	 *
	 *	Format of each line is:
	 *		IP_address	action	expiry_time additional_parameters
	 *
	 *	where action is: >0 = whitelisted, <0 blacklisted, value is 'reason code'
	 *		expiry_time is zero for an indefinite ban, time stamp for a limited ban
	 *		additional_parameters may be required for certain actions in the future
	 */
	private function getWhiteBlackList()
	{
		$ret = array();
		$fileName = $this->ourConfigDir.eIPHandler::BAN_FILE_IP_NAME.eIPHandler::BAN_FILE_EXTENSION;
		if (!is_readable($fileName)) return $ret;

		$vals  = file($fileName);
		if ($vals === FALSE || count($vals) == 0) return $ret;
		if (substr($vals[0], 0, 5) != '<?php')
		{
			echo 'Invalid list file';
			die();			// Debatable, because admins can't get in if this fails. But can manually delete the file.
		}
		unset($vals[0]);
		foreach ($vals as $line)
		{
			if (substr($line, 0, 1) == ';') continue;
			if (trim($line))
			{
				$tmp = explode(' ',$line);
				if (count($tmp) >= 2)
				{
					$ret[] = array('ip' => $tmp[0], 'action' => $tmp[1], 'time_limit' => intval(varset($tmp[2], 0)));
				}
			}
		}
		$this->actionCount = count($ret);		// Note how many entries in list
		return $ret;
	}



	/**
	 *	Checks whether IP address is in the whitelist or blacklist.
	 *
	 *	@param string $addr - IP address in 'normal' form
	 *
	 *	@return int - >0 = whitelisted, 0 = not listed (= 'OK'), <0 is 'reason code' for ban
	 *
	 *	note: Could maybe combine this with getWhiteBlackList() for efficiency, but makes it less general
	 */
	private function checkIP($addr)
	{
		$now = time();
		$checkLists = $this->getWhiteBlackList();

		if($this->debug)
		{
			echo "<h4>Banlist.php</h4>";
			print_a($checkLists);
			print_a("Now: ".$now. "   ".date('r',$now));
		}


		foreach ($checkLists as $val)
		{
			if (strpos($addr, $val['ip']) === 0)	// See if our address begins with an entry - handles wildcards
			{	// Match found

				if($this->debug)
				{
					print_a("Found ".$addr." in file.  TimeLimit: ".date('r',$val['time_limit']));
				}

				if (($val['time_limit'] == 0) || ($val['time_limit'] > $now))
				{	// Indefinite ban, or timed ban (not expired) or whitelist entry
					if ($val['action']== eIPHandler::BAN_TYPE_LEGACY) return eIPHandler::BAN_TYPE_MANUAL;		// Precautionary
					$this->matchAddress = $val['ip'];
					return $val['action'];			// OK to just return - PHP should release the memory used by $checkLists
				}
				// Time limit expired
				$this->clearBan = $val['ip'];	// Note what triggered the match - it could be a wildcard (although timed ban unlikely!)
				return 0;						// Can just return - shouldn't be another entry
			}

		}
		return 0;
	}


	/**
	 *    Encode an IPv4 address into IPv6
	 *    Similar functionality to ipEncode
	 *
	 * @param $ip
	 * @param bool $wildCards
	 * @param string $div
	 * @return string - the 'ip4' bit of an IPv6 address (i.e. last 32 bits)
	 */
	private function ip4Encode($ip, $wildCards = FALSE, $div = ':')
	{
		$ipa = explode('.', $ip);
		$temp = '';
		for ($s = 0; $s < 4; $s++)
		{
			if (!isset($ipa[$s])) $ipa[$s] = '*';
			if ((($ipa[$s] == '*') || (strpos($ipa[$s], 'x') !== FALSE)) && $wildCards)
			{
				$temp .= 'xx';
			}
			else
			{	// Put a zero in if wildcards not allowed
				$temp .= sprintf('%02x', $ipa[$s]);
			}
			if ($s == 1) $temp .= $div;
		}
		return $temp;
	}


	/**
	 * Encode an IP address to internal representation. Returns string if successful; FALSE on error
	 * Default separates fields with ':'; set $div='' to produce a 32-char packed hex string
	 *
	 *	@param string $ip - 'raw' IP address. May be IPv4, IPv6
	 *	@param boolean $wildCards - if TRUE, wildcard characters allowed at the end of an address:
	 *				'*' replaces 2 hex characters (primarily for 8-bit subnets of IPv4 addresses)
	 *				'x' replaces a single hex character
	 *	@param string $div separator between 4-character blocks of the IPv6 address
	 *
	 * @return bool|string encoded IP. Always exactly 32 characters plus separators if conversion successful
	 *				FALSE if conversion unsuccessful
	 */
	public function ipEncode($ip, $wildCards = FALSE, $div = ':')
	{
		$ret = '';
		$divider = '';
		if(strpos($ip, ':')!==FALSE)
		{ // Its IPV6 (could have an IP4 'tail')
			if(strpos($ip, '.')!==FALSE)
			{ // IPV4 'tail' to deal with
				$temp = strrpos($ip, ':')+1;
				$ip = substr($ip, 0, $temp).$this->ip4Encode(substr($ip, $temp), $wildCards, $div);
			}
			// Now 'normalise' the address
			$temp = explode(':', $ip);
			$s = 8-count($temp); // One element will of course be the blank
			foreach($temp as $f)
			{
				if($f=='')
				{
					$ret .= $divider.'0000'; // Always put in one set of zeros for the blank
					$divider = $div;
					if($s>0)
					{
						$ret .= str_repeat($div.'0000', $s);
						$s = 0;
					}
				}
				else
				{
					$ret .= $divider.sprintf('%04x', hexdec($f));
					$divider = $div;
				}
			}
			return $ret;
		}
		if(strpos($ip, '.')!==FALSE)
		{ // Its IPV4
			return str_repeat('0000'.$div, 5).'ffff'.$div.$this->ip4Encode($ip, $wildCards, $div);
		}
		return FALSE; // Unknown
	}


	/**
	 *    Given a potentially truncated IPV6 address as used in the ban list files, adds 'x' characters etc to create
	 *    a normalised IPV6 address as stored in the DB. Returned length is exactly 39 characters
	 * @param $address
	 * @return string
	 */
	public function ip6AddWildcards($address)
	{
		while (($togo = (39 - strlen($address))) > 0)
		{
			if (($togo % 5) == 0)
			{
				$address .= ':';
			}
			else
			{
				$address .= 'x';
			}
		}
		return $address;
	}


	/**
	 * Takes an encoded IP address - returns a displayable one
	 * Set $IP4Legacy TRUE to display 'old' (IPv4) addresses in the familiar dotted format,
	 * FALSE to display in standard IPV6 format
	 * Should handle most things that can be thrown at it.
	 *	If wildcard characters ('x' found, incorporated 'as is'
	 *
	 * @param string $ip encoded IP
	 * @param boolean $IP4Legacy
	 * @return string decoded IP
	 */
	public function ipDecode($ip, $IP4Legacy = TRUE)
	{
		if (strstr($ip,'.'))
		{
			if ($IP4Legacy) return $ip;			// Assume its unencoded IPV4
			$ipa = explode('.', $ip);
			$ip = '0:0:0:0:0:ffff:'.sprintf('%02x%02x:%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
			$ip = str_repeat('0000'.':', 5).'ffff:'.$this->ip4Encode($ip, TRUE, ':');
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
			if (($v != 0) || ($z == 2) || (strpos($t, 'x') !== FALSE))
			{
				if ($z == 1)
				{ // Just finished a run of zeros
					$z++;
					$ret .= ':';
				}
				if ($ret) $ret .= ':';
				if (strpos($t, 'x') !== FALSE)
				{
					$ret .= $t;
				}
				else
				{
					$ret .= sprintf('%x',$v);				// Drop leading zeros
				}
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
			$temp = str_replace(':', '', substr($ip,-9, 9));
			$tmp = str_split($temp, 2);			// Four 2-character hex values
			$z = array();
			foreach ($tmp as $t)
			{
				if ($t == 'xx')
				{
					$z[] = '*';
				}
				else
				{
					$z[] = hexdec($t);
				}
			}
			$ret = implode('.',$z);
		}
		return $ret;
	}



	/**
	 * Given a string which may be IP address, email address etc, tries to work out what it is
	 * Uses a fairly simplistic (but quick) approach - does NOT check formatting etc
	 *
	 * @param string $string
	 * @return string ip|email|url|ftp|unknown
	 */
	public function whatIsThis($string)
	{
		$string = trim($string);
		if (strpos($string, '@') !== FALSE) return 'email';		// Email address
		if (strpos($string, 'http://') === 0) return 'url';
		if (strpos($string, 'https://') === 0) return 'url';
		if (strpos($string, 'ftp://') === 0) return 'ftp';
		if (strpos($string, ':') !== FALSE) return 'ip';	// Identify ipv6
		$string = strtolower($string);
		if (str_replace(' ', '', strtr($string,'0123456789abcdef.*', '                   ')) == '')	// Delete all characters found in ipv4 addresses, plus wildcards
		{
			return 'ip';
		}
		return 'unknown';
	}


	/**
	 * Retrieve & cache host name
	 *
	 * @param string $ip_address
	 * @return string host name
	 */
	public function get_host_name($ip_address)
	{
		if(!isset($this->_host_name_cache[$ip_address]))
		{
			$this->_host_name_cache[$ip_address] = gethostbyaddr($ip_address);
		}
		return $this->_host_name_cache[$ip_address];
	}


	/**
	 *    Generate DB query for domain name-related checks
	 *
	 *    If an email address is passed, discards the individual's name
	 *
	 * @param string $email - an email address or domain name string
	 * @param string $fieldName
	 * @return array|bool false if invalid domain name format
	 * false if invalid domain name format
	 * array of values to compare
	 * @internal param string $fieldname - if non-empty, each array entry is a comparison with this field
	 *
	 */
	function makeDomainQuery($email, $fieldName = 'banlist_ip')
	{
		$tp = e107::getParser();
		if (($tv = strrpos('@', $email)) !== FALSE)
		{
			$email = substr($email, $tv+1);
		}
		$tmp = strtolower($tp -> toDB(trim($email)));
		if ($tmp == '') return FALSE;
		if (strpos($tmp,'.') === FALSE) return FALSE;
		$em = array_reverse(explode('.',$tmp));
		$line = '';
		$out = array('*@'.$tmp);		// First element looks for domain as email address
		foreach ($em as $e)
		{
			$line = '.'.$e.$line;
			$out[] = '*'.$line;
		}
		if ($fieldName)
		{
			foreach ($out as $k => $v)
			{
				$out[$k] = '(`'.$fieldName."`='".$v."')";
			}
		}
		return $out;
	}



	/**
	 *	Split up an email address to check for banned domains.
	 *	@param string $email - email address to process
	 *	@param string $fieldname - name of field being searched in DB
	 *
	 *	@return bool|string false if invalid address. Otherwise returns a set of values to check
	 *	(Moved in from user_handler.php)
	 */
	public function makeEmailQuery($email, $fieldname = 'banlist_ip')
	{
		$tp = e107::getParser();
		$tmp = strtolower($tp -> toDB(trim(substr($email, strrpos($email, "@")+1))));	// Pull out the domain name
		if ($tmp == '') return FALSE;
		if (strpos($tmp,'.') === FALSE) return FALSE;
		$em = array_reverse(explode('.',$tmp));
		$line = '';
		$out = array($fieldname."='*@{$tmp}'");		// First element looks for domain as email address
		foreach ($em as $e)
		{
			$line = '.'.$e.$line;
			$out[] = '`'.$fieldname."`='*{$line}'";
		}
		return implode(' OR ',$out);
	}



/**
 *	Routines beyond here are to handle banlist-related tasks which involve the DB
 *	note: Most of these routines already existed; moved in from e107_class.php
 */


	/**
	 * Check if current user is banned
	 *
	 *	This is called soon after the DB is opened, to do checks which require it.
	 *	Previous checks have already done IP-based bans.
	 *
	 *	Starts by removing expired bans if $this->clearBan is set
	 *
	 * 	Generates the queries to interrogate the ban list, then calls $this->check_ban().
	 *	If the user is banned, $check_ban() never returns - so a return from this routine indicates a non-banned user.
	 *
	 *	@return void
	 *
	 *	@todo should be possible to simplify, since IP addresses already checked earlier
	 */
	public function ban()
	{
		$sql = e107::getDb();

		if ($this->clearBan !== FALSE)
		{	// Expired ban to clear - match exactly the address which triggered this action - could be a wildcard
			$clearAddress = $this->ip6AddWildcards($this->clearBan);
			if ($sql->delete('banlist',"`banlist_ip`='{$clearAddress}'"))
			{
				$this->actionCount--;		// One less item on list
				$this->logBanItem(0,'Ban cleared: '.$clearAddress);
				// Now regenerate the text files - so no further triggers from this entry
				$this->regenerateFiles();
			}
		}


		// do other checks - main IP check is in _construct()
		if($this->actionCount)
		{
			$ip = $this->getip(); // This will be in normalised IPV6 form

			if ($ip !== e107::LOCALHOST_IP && ($ip !== e107::LOCALHOST_IP2) && ($ip !== $this->serverIP)) // Check host name, user email to see if banned
			{
				$vals = array();
				if (e107::getPref('enable_rdns'))
				{
					$vals = array_merge($vals, $this->makeDomainQuery($this->get_host_name($ip), ''));
				}
				if ((defined('USEREMAIL') && USEREMAIL))
				{
						// @todo is there point to this? Usually avoid a complete query if we skip it
					$vals = array_merge($vals, $this->makeDomainQuery(USEREMAIL, ''));
				}
				if (count($vals))
				{
					$vals = array_unique($vals);			// Could get identical values from domain name check and email check

					if($this->debug)
					{
						print_a($vals);
					}


					$match = "`banlist_ip`='".implode("' OR `banlist_ip`='", $vals)."'";
					$this->checkBan($match);
				}
			}
			elseif($this->debug)
			{
				print_a("IP is LocalHost -  skipping ban-check");
			}
		}
	}



	/**
	 * Check the banlist table. $query is used to determine the match.
	 * If $do_return, will always return with ban status - TRUE for OK, FALSE for banned.
	 * If return permitted, will never display a message for a banned user; otherwise will display any message then exit
	 * @todo consider whether can be simplified
	 *
	 * @param string $query - the 'WHERE' part of the DB query to be executed
	 * @param boolean $show_error - if true, adds a '403 Forbidden' header for a banned user
	 * @param boolean $do_return - if TRUE, returns regardless without displaying anything. if FALSE, for a banned user displays any message and exits
	 * @return boolean TRUE for OK, FALSE for banned.
	 */
	public function checkBan($query, $show_error = TRUE, $do_return = FALSE)
	{
		$sql = e107::getDb();
		$pref = e107::getPref();
		$tp = e107::getParser();
		$admin_log = e107::getAdminLog();

		//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Check for Ban",$query,FALSE,LOG_TO_ROLLING);
		if ($sql->select('banlist', '*', $query.' ORDER BY `banlist_bantype` DESC'))
		{
			// Any whitelist entries will be first, because they are positive numbers - so we can answer based on the first DB record read
			$row = $sql->fetch();
			if ($row['banlist_bantype'] >= eIPHandler::BAN_TYPE_WHITELIST)
			{
				//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Whitelist hit",$query,FALSE,LOG_TO_ROLLING);
				return TRUE;		// Whitelisted entry
			}
			// Found banlist entry in table here
			if (($row['banlist_banexpires']>0) && ($row['banlist_banexpires']<time()))
			{ // Ban has expired - delete from DB
				$sql->delete('banlist', $query);
				$this->regenerateFiles();
				return TRUE;
			}
			
			// User is banned hereafter - just need to sort out the details.
			if (vartrue($pref['ban_retrigger']) && vartrue($pref['ban_durations'][$row['banlist_bantype']]))
			{ // May need to retrigger ban period
				$sql->update('banlist', "`banlist_banexpires`=".intval(time()+($pref['ban_durations'][$row['banlist_bantype']]*60*60)), "WHERE `banlist_ip`='{$row['banlist_ip']}'");
				$this->regenerateFiles();
				//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Retrigger Ban",$row['banlist_ip'],FALSE,LOG_TO_ROLLING);
			}
			//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","Active Ban",$query,FALSE,LOG_TO_ROLLING);
			if ($show_error)
			{
				header('HTTP/1.1 403 Forbidden', true);
			}
			if (isset($pref['ban_messages']))
			{ // May want to display a message
				// Ban still current here
				if($do_return)
				{
					return FALSE;
				}
				echo $tp->toHTML(varset($pref['ban_messages'][$row['banlist_bantype']])); 	// Show message if one set
			}
			//$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, 'BAN_03', 'LAN_AUDIT_LOG_003', $query, FALSE, LOG_TO_ROLLING);

			if($this->debug)
			{
				echo "<pre>query: ".$query;
				echo "\nBanned</pre>";
			}

			// added missing if clause
			if ($do_return)
			{
				return false;
			}
			exit();
		}

		if($this->debug)
		{
			echo "query: ".$query;
			echo "<br />Not Banned ";
		}


		//$admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","No ban found",$query,FALSE,LOG_TO_ROLLING);
		return TRUE; 		// Email address OK
	}



	/**
	 * Add an entry to the banlist. $bantype = 1 for manual, 2 for flooding, 4 for multiple logins
	 * Returns TRUE if ban accepted.
	 * Returns FALSE if ban not accepted (e.g. because on whitelist, or invalid IP specified)
	 *
	 * @param integer $bantype - either one of the BAN_TYPE_xxx constants, or a legacy value as above
	 * @param string $ban_message
	 * @param string $ban_ip
	 * @param integer $ban_user
	 * @param string $ban_notes
	 *
	 * @return boolean|integer check result - FALSE if ban rejected. TRUE if ban added. 1 if IP address already banned
	 */
	public function add_ban($bantype, $ban_message = '', $ban_ip = '', $ban_user = 0, $ban_notes = '')
	{

		if ($ban_ip == e107::LOCALHOST_IP || $ban_ip == e107::LOCALHOST_IP2)
		{
			return false;
		}


		$sql = e107::getDb();
		$pref = e107::getPref();

		switch ($bantype)		// Convert from 'internal' ban types to those used in the DB
		{
			case 1 : $bantype = eIPHandler::BAN_TYPE_MANUAL; break;
			case 2 : $bantype = eIPHandler::BAN_TYPE_FLOOD; break;
			case 4 : $bantype = eIPHandler::BAN_TYPE_LOGINS; break;
		}
		if (!$ban_message)
		{
			$ban_message = 'No explanation given';
		}
		if (!$ban_ip)
		{
			$ban_ip = $this->getip();
		}
		$ban_ip = preg_replace('/[^\w@\.:]*/', '', urldecode($ban_ip)); // Make sure no special characters
		if (!$ban_ip)
		{
			return FALSE;
		}
		// See if address already in the banlist
		if ($sql->select('banlist', '`banlist_bantype`', "`banlist_ip`='{$ban_ip}'"))
		{
			list($banType) = $sql->fetch();
			
			if ($banType >= eIPHandler::BAN_TYPE_WHITELIST)
			{ // Got a whitelist entry for this
				//$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "BANLIST_11", 'LAN_AL_BANLIST_11', $ban_ip, FALSE, LOG_TO_ROLLING);
				return FALSE;
			}
			return 1;		// Already in ban list
		}
		/*
		// See if the address is in the whitelist
		if ($sql->db_Select('banlist', '*', "`banlist_ip`='{$ban_ip}' AND `banlist_bantype` >= ".eIPHandler::BAN_TYPE_WHITELIST))
		{ // Got a whitelist entry for this
			//$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "BANLIST_11", 'LAN_AL_BANLIST_11', $ban_ip, FALSE, LOG_TO_ROLLING);
			return FALSE;
		} */
		if(vartrue($pref['enable_rdns_on_ban']))
		{
			$ban_message .= 'Host: '.$this->get_host_name($ban_ip);
		}
		// Add using an array - handles DB changes better
		$sql->insert('banlist', 
			array(
				'banlist_id'			=> 0,
				'banlist_ip' 			=> $ban_ip , 
				'banlist_bantype' 		=> $bantype , 
				'banlist_datestamp' 	=> time() , 
				'banlist_banexpires' 	=> (vartrue($pref['ban_durations'][$bantype]) ? time()+($pref['ban_durations'][$bantype]*60*60) : 0) ,
				'banlist_admin' 		=> $ban_user , 
				'banlist_reason' 		=> $ban_message , 
				'banlist_notes' 		=> $ban_notes
			));

		$this->regenerateFiles();
		return TRUE;
	}


	/**
	 *	Regenerate the text-based banlist files (called after a banlist table mod)
	 */
	public function regenerateFiles()
	{
		// Now regenerate the text files - so accesses of this IP address don't use the DB
		$ipAdministrator = new banlistManager;
		$ipAdministrator->writeBanListFiles('ip,htaccess');
	}



	public function getConfigDir()
	{
		return $this->ourConfigDir;
	}



	/**
	 *	Routine checks whether a file or directory has sufficient permissions
	 *
	 *	********** @todo this is in the wrong place! Move it to a more appropriate class! *************
	 *
	 *	@param string $name - file with path (if ends in anything other than '/' or '\') or directory (if ends in '/' or '\')
	 *	@param string(?) $perms - required permissions as standard *nix 3-digit string
	 *	@param boolean $message - if TRUE, and insufficient rights, a message is output (in 0.8, to the message handler)
	 *
	 *	@return boolean TRUE if sufficient permissions, FALSE if not (or error)
	 *
	 *	For each mode character:
	 *		1 - execute
	 *		2 - writable
	 *		4 - readable
	 */
	public function checkFilePerms($name, $perms, $message = TRUE)
	{
		$isDir = ((substr($name, -1,1) == '\\') || (substr($name, -1,1) == '/'));
		$result = FALSE;
		$msg = '';
		$dest = $isDir ? 'Directory' : 'File';
		$reqPerms = intval('0'.$perms) & 511;				// We want an integer value to match the return from fileperms()
		if (!file_exists($name))
		{
			$msg = $dest.': '.$name.' does not exist';
		}
		if ($msg == '')
		{
			$realPerms = fileperms($name);
			$mgs = $name.' is not a '.$dest;		// Assume an error to start; clear messsage if all OK
			switch ($realPerms & 0xf000)
			{
				case 0x8000 :
					if (!$isDir)
					{
						$msg = '';
					}
					break;
				case 0x4000 :
					if ($isDir)
					{
						$msg = '';
					}
					break;
			}
		}
		if ($msg == '')
		{
			if (($reqPerms & $realPerms) == $reqPerms)
			{
				$result = TRUE;
			}
			else
			{
				$msg = $name.': Insufficient permissions. Required: '.$this->permsToString($reqPerms).'  Actual: '.$this->permsToString($realPerms);
			}
		}
		if ($message && $msg)
		{	// Do something with the error message
		}
		return $result;
	}


	/**
	 *	Decode file/directory permissions into human-readable characters
	 *
	 *	@param int $val representing permissions (LS 9 bits used)
	 *
	 *	@return string exactly 9 characters, with blocks of 3 representing user, group and world permissions
	 */
	public function permsToString($val)
	{
		$perms = 'rwxrwxrwx';
		$mask = 0x100;

		for ($i = 0; $i < 9; $i++)
		{
			if (($mask & $val) == 0) $perms[$i] = '-';
			$mask = $mask >> 1;
		}
		return $perms;
	}


	/**
	 *	Function to see whether a user is already logged as being online
	 *
	 *	@todo - this is possibly in the wrong place!
	 *
	 *	@param string $ip - in 'normalised' IPV6 form
	 *	@param string $browser - browser token as logged
	 *
	 *	@return boolean|array  FALSE if DB error or not found. Best match table row if found
	 */
	public function isUserLogged($ip, $browser)
	{
		$ourDB = e107::getDb('olcheckDB');			// @todo is this OK, or should an existing one be used?

		$result = $ourDB->select('online', '*', "`user_ip` = '{$ip}' OR `user_token` = '{$browser}'");
		if ($result === FALSE) return FALSE;
		$gotIP = FALSE;
		$gotBrowser = FALSE;
		$bestRow = FALSE;
		while (FALSE !== ($row = $ourDB->fetch()))
		{
			if ($row['user_token'] == $browser)
			{
				if ($row['user_ip'] == $ip)
				{	// Perfect match
					return $row;
				}
				// Just browser token match here
				if ($bestRow === FALSE)
				{
					$bestRow = $row;
					$gotBrowser = TRUE;
				}
				else
				{	// Problem - two or more rows with same browser token. What to do?
				}
			}
			elseif ($row['user_ip'] == $ip)
				{	// Just IP match here
					if ($bestRow === FALSE)
					{
						$bestRow = $row;
						$gotIP = TRUE;
					}
					else
					{	// Problem - two or more rows with same IP address. Hopefully better offer later!
					}
				}
		}
		return $bestRow;
	}
}






/**
 *	Routines involved with the management of the ban list and associated files
 */
class banlistManager
{
	private $ourConfigDir = '';
	public $banTypes = array();

	public function __construct()
	{
		$this->ourConfigDir = e107::getIPHandler()->getConfigDir();
		$this->banTypes = array( // Used in Admin-ui. 
			'-1' 				=> BANLAN_101, // manual
			'-2'				=> BANLAN_102, // Flood
			'-3'				=> BANLAN_103, // Hits
			'-4'				=> BANLAN_104, // Logins
			'-5'				=> BANLAN_105, // Imported
			'-6'				=> BANLAN_106, // Users
			'-8'				=> BANLAN_107, // Imported
			'100'				=> BANLAN_120 // Whitelist
		);
		
		
	}

	/**
	 *	Return an array of valid ban types (for use as indices into array, generally)
	 */
	public function getValidReasonList()
	{
		return array(
			eIPHandler::BAN_TYPE_LEGACY,
			eIPHandler::BAN_TYPE_MANUAL, 
			eIPHandler::BAN_TYPE_FLOOD,
			eIPHandler::BAN_TYPE_HITS,
			eIPHandler::BAN_TYPE_LOGINS,
			eIPHandler::BAN_TYPE_IMPORTED,
			eIPHandler::BAN_TYPE_USER,
														// Spare value
			eIPHandler::BAN_TYPE_UNKNOWN
			);
	} 


	/**
	 *	Create banlist-related text files as requested:
	 *		List of whitelisted and blacklisted IP addresses
	 *		file for easy import into .htaccess file  (allow from...., deny from....)
	 *		Generic CSV-format export file
	 *
	 *	@param string $options {ip|htaccess|csv} - comma separated list (no spaces) to select which files to write
	 *	@param string $typeList - optional comma-separated list of ban types required (default is all)
	 *	Uses constants:
	 *		BAN_FILE_IP_NAME		Saves list of banned and whitelisted IP addresses
	 *		BAN_FILE_ACTION_NAME	Details of actions for different ban types
	 *		BAN_FILE_HTACCESS		File in format for direct paste into .htaccess
	 *		BAN_FILE_CSV_NAME
	 *		BAN_FILE_EXTENSION		File extension to append
	 *
	 */ 
	public function writeBanListFiles($options = 'ip', $typeList = '')
	{
		e107::getMessage()->addDebug("Writing new Banlist files.");
		$sql = e107::getDb();
		$ipManager = e107::getIPHandler();

		$optList = explode(',',$options);
		$fileList = array();				// Array of file handles once we start

		$fileNameList = array('ip' => eIPHandler::BAN_FILE_IP_NAME, 'htaccess' => eIPHandler::BAN_FILE_HTACCESS, 'csv' => eIPHandler::BAN_FILE_CSV_NAME);

		$qry = 'SELECT * FROM `#banlist` ';
		if ($typeList != '') $qry .= " WHERE`banlist_bantype` IN ({$typeList})";
		$qry .= ' ORDER BY `banlist_bantype` DESC';			// Order ensures whitelisted addresses appear first

		// Create a temporary file for each type as demanded. Vet the options array on this pass, as well
		foreach($optList as $k => $opt)
		{
			if (isset($fileNameList[$opt]))
			{
				if ($tmp = fopen($this->ourConfigDir.$fileNameList[$opt].'_tmp'.eIPHandler::BAN_FILE_EXTENSION, 'w'))
				{
					$fileList[$opt] = $tmp;			// Save file handle
					fwrite($fileList[$opt], "<?php\n; die();\n");
					//echo "Open File for write: ".$this->ourConfigDir.$fileNameList[$opt].'_tmp'.eIPHandler::BAN_FILE_EXTENSION.'<br />';
				}
				else
				{
					unset($optList[$k]);
					/// @todo - flag error?
				}
			}
			else
			{
				unset($optList[$k]);
			}
		}

		if ($sql->gen($qry))
		{
			while ($row = $sql->db_Fetch())
			{
				$row['banlist_ip'] = $this->trimWildcard($row['banlist_ip']);
				if ($row['banlist_ip'] == '') continue;								// Ignore empty IP addresses
				if ($ipManager->whatIsThis($row['banlist_ip']) != 'ip') continue;		// Ignore non-numeric IP Addresses
				if ($row['banlist_bantype'] == eIPHandler::BAN_TYPE_LEGACY) $row['banlist_bantype'] = eIPHandler::BAN_TYPE_UNKNOWN;		// Handle legacy bans
				foreach ($optList as $opt)
				{
					$line = '';
					switch ($opt)
					{
						case 'ip' :
							// IP_address	action	expiry_time additional_parameters
							$line = $row['banlist_ip'].' '.$row['banlist_bantype'].' '.$row['banlist_banexpires']."\n";
							break;
						case 'htaccess' :
							$line = (($row['banlist_bantype'] > 0) ? 'allow from ' : 'deny from ').$row['banlist_ip']."\n";
							break;
						case 'csv' :		/// @todo - when PHP5.1 is minimum, can use fputcsv() function
							$line = $row['banlist_ip'].','.$this->dateFormat($row['banlist_datestamp']).','.$this->dateFormat($row['banlist_expires']).',';
							$line .= $row['banlist_bantype'].',"'.$row['banlist_reason'].'","'.$row['banlist_notes'].'"'."\n";
							break;
					}
					fwrite($fileList[$opt], $line);
				}
			}
		}
		
		// Now close each file
		foreach ($optList as $opt)
		{
			fclose($fileList[$opt]);
		}
		
		// Finally, delete the working file, rename the temporary one
		// Docs suggest that 'newname' is auto-deleted if it exists (as it usually should) 
		//		- but didn't appear to work, hence copy then delete
		foreach ($optList as $opt)
		{
			$oldName = $this->ourConfigDir.$fileNameList[$opt].'_tmp'.eIPHandler::BAN_FILE_EXTENSION;
			$newName = $this->ourConfigDir.$fileNameList[$opt].eIPHandler::BAN_FILE_EXTENSION;
			copy($oldName, $newName);
			unlink($oldName);
		}
	}


	/**
	 *    Trim wildcards from IP addresses
	 *
	 * @param string $ip - IP address in any normal form
	 *
	 *    Note - this removes all characters after (and including) the first '*' or 'x' found. So an '*' or 'x' in the middle of a string may
	 *            cause unexpected results.
	 * @return string
	 */
	private function trimWildcard($ip)
	{
		$ip = trim($ip);
		$temp = strpos($ip, 'x');
		if ($temp !== FALSE) 
		{
			return substr($ip, 0, $temp);
		}
		$temp = strpos($ip, '*');
		if ($temp !== FALSE) 
		{
			return substr($ip, 0, $temp);
		}
		return $ip;
	}


	/**
	 *	Format date and time for export into a text file.
	 *
	 *	@param int $date - standard Unix time stamp
	 *
	 *	@return string. '0' if date is zero, else formatted in consistent way.
	 */
	private function dateFormat($date)
	{
		if ($date == 0) return '0';
		return strftime('%Y%m%d_%H%M%S',$date);
	}



	/**
	 *	Return string corresponding to a ban type
	 *	@param int $banType - constant representing the ban type
	 *	@param bool $forMouseover - if true, its the (usually longer) explanatory string for a mouseover
	 *
	 *	@return string
	 */
	public function getBanTypeString($banType, $forMouseover = FALSE)
	{
		switch ($banType)
		{
			case eIPHandler::BAN_TYPE_LEGACY :	$listOffset = 0; break;
			case eIPHandler::BAN_TYPE_MANUAL :	$listOffset = 1; break;
			case eIPHandler::BAN_TYPE_FLOOD :	$listOffset = 2; break;
			case eIPHandler::BAN_TYPE_HITS :	$listOffset = 3; break;
			case eIPHandler::BAN_TYPE_LOGINS :	$listOffset = 4; break;
			case eIPHandler::BAN_TYPE_IMPORTED :	$listOffset = 5; break;
			case eIPHandler::BAN_TYPE_USER :	$listOffset = 6; break;
			case eIPHandler::BAN_TYPE_TEMPORARY :	$listOffset = 9; break;

			case eIPHandler::BAN_TYPE_WHITELIST :
				return BANLAN_120;		// Special case - may never occur
			case eIPHandler::BAN_TYPE_UNKNOWN :	
			default :
				if (($banType > 0) && ($banType < 9))
				{
					$listOffset = $banType;			// BC conversions
				}
				else
				{
					$listOffset = 8;
				}
		}
		if ($forMouseover) return constant('BANLAN_11'.$listOffset);
		return constant('BANLAN_10'.$listOffset);
	}



	/**
	 *	Write a text file containing the ban messages related to each ban reason
	 */
	public function writeBanMessageFile()
	{
		$pref['ban_messages'] = e107::getPref('ban_messages');
		
		$oldName = $this->ourConfigDir.eIPHandler::BAN_FILE_ACTION_NAME.'_tmp'.eIPHandler::BAN_FILE_EXTENSION;
		if ($tmp = fopen($oldName, 'w'))
		{
			fwrite($tmp, "<?php\n; die();\n");
			foreach ($this->getValidReasonList() as $type)
			{
				fwrite($tmp,'['.$type.']'.$pref['ban_messages'][$type]."\n");
			}
			fclose($tmp);
			$newName = $this->ourConfigDir.eIPHandler::BAN_FILE_ACTION_NAME.eIPHandler::BAN_FILE_EXTENSION;
			copy($oldName, $newName);
			unlink($oldName);
		}
	}



	/**
	 *	Check whether the message file (containing responses to ban types) exists
	 *
	 *	@return boolean TRUE if exists, FALSE if doesn't exist
	 */
	public function doesMessageFileExist()
	{
		return is_readable($this->ourConfigDir.eIPHandler::BAN_FILE_ACTION_NAME.eIPHandler::BAN_FILE_EXTENSION);
	}



	/**
	 *	Get entries from the ban action log
	 *
	 *	@param int $start - offset into list (zero is first entry)
	 *	@param int $count - number of entries to return - zero is a special case
	 *	@param int $numEntry - filled in on return with the total number of entries in the log file
	 *
	 *	@return array of strings; each string is a single log entry, newest first.
	 *
	 *	Returns an empty array if an error occurs (or if no entries)
	 *	If $count is zero, all entries are returned, in ascending order.
	 */
	public function getLogEntries($start, $count, &$numEntry)
	{
		$ret = array();
		$numEntry = 0;
		$fileName = e_SYSTEM.eIPHandler::BAN_LOG_DIRECTORY.eIPHandler::BAN_FILE_LOG_NAME;
		if (!is_readable($fileName)) return $ret;

		$vals  = file($fileName);
		if ($vals === FALSE) return $ret;
		if (substr($vals[0], 0, 5) == '<?php')
		{
			unset($vals[0]);
		}
		if (substr($vals[0], 0, 1) == ';') unset($vals[0]);
		$numEntry = count($vals);
		if ($start > $numEntry) return $ret;		// Empty return if beyond the end
		if ($count == 0) return $vals;				// Special case - return the lot in ascending date order
		// Array is built up with newest last - but we want newest first. And we don't want to duplicate the array!
		if (($start + $count) > $numEntry) $count = $numEntry - $start;		// Last segment might not have enough entries
		$ret = array_slice($vals, -$start - $count, $count);
		return array_reverse($ret);
	}
	
	
	/**
	 *	Converts one of the strings returned in a getLogEntries string into an array of values
	 *
	 *	@param string $string - a text line, possibly including a 'newline' at the end
	 *
	 *	@return array of up to $count entries
	 *		['banDate'] - time/date stamp
	 *		['banIP'] - IP address involved
	 *		['banReason'] - Numeric reason code for entry
	 *		['banNotes'] = any text appended
	 */
	public function splitLogEntry($string)
	{
		$temp = explode(' ',$string, 4);
		while (count($temp) < 4) $temp[] = '';
		$ret['banDate'] = $temp[0];
		$ret['banIP'] = $temp[1];
		$ret['banReason'] = $temp[2];
		$ret['banNotes'] = str_replace("\n", '', $temp[3]);
		return $ret;
	}
	

	/**
	 *	Delete ban Log file
	 *
	 *	@return boolean TRUE on success, FALSE on failure
	 */
	public function deleteLogFile()
	{
		$fileName = e_SYSTEM.eIPHandler::BAN_LOG_DIRECTORY.eIPHandler::BAN_FILE_LOG_NAME;
		return unlink($fileName);
	}


	/**
	 *	Update expiry time for IP addresses that have accessed the site while banned.
	 *	Processes the entries in the 'ban retrigger' action file, and deletes the file
	 *
	 *	Needs to be called from a cron job, at least once per hour, and ideally every few minutes. Otherwise banned users who access
	 *	the site in the period since the last call to this routine may be able to get in because their ban has expired. (Unlikely to be
	 *	an issue in practice)
	 *
	 *	@return int number of IP addresses updated
	 *
	 *	@todo - implement cron job and test
	 */
	public function banRetriggerAction()
	{
		//if (!e107::getPref('ban_retrigger')) return 0;		// Should be checked earlier

		$numEntry = 0;			// Make sure this variable declared before passing it - total number of log entries.
		$ipAction = array();	// Array of IP addresses to action
		$fileName = $this->ourConfigDir.eIPHandler::BAN_FILE_RETRIGGER_NAME.eIPHandler::BAN_FILE_EXTENSION;
		$entries = file($fileName);
		if (!is_array($entries))
		{
			return 0;			// Probably no retrigger actions
		}
		@unlink($fileName);				// Delete the action file now we've read it in.
		
		// Scan the list completely before doing any processing - this will ensure we only process the most recent entry for each IP address
		while (count($entries) > 0)
		{
			$line = array_shift($entries);
			$info = $this->splitLogEntry($line);
			if ($info['banReason'] < 0)
			{
				$ipAction[$info['banIP']] = array('date' => $info['banDate'], 'reason' => $info['banReason']);			// This will result in us gathering the most recent access from each IP address
			}
		}

		if (count($ipAction) == 0) return 0;				// Nothing more to do

		// Now run through the database updating times
		$numRet = 0;
		$pref['ban_durations'] = e107::getPref('ban_durations');
		$ourDb = e107::getDB();		// Should be able to use $sql, $sql2 at this point
		$writeDb = e107::getDB('sql2');

		foreach ($ipAction as $ipKey => $ipInfo)
		{
			if ($ourDb->select('banlist', '*', "`banlist_ip`='".$ipKey."'") === 1)
			{
				if ($row = $ourDb->fetch())
				{
					// @todo check next line
					$writeDb->db_Update('banlist', 
					'`banlist_banexpires` = '.intval($row['banlist_banexpires'] + $pref['ban_durations'][$row['banlist_banreason']]));
					$numRet++;
				}
			}
		}
		if ($numRet)
		{
			$this->writeBanListFiles('ip');		// Just rewrite the ban list - the actual IP addresses won't have changed
		}
		return $numRet;
	}
}


?>