<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/radius_auth.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+

RFC2865 is the main RADIUS standard - http://www.faqs.org/rfcs/rfc2865

Potential enhancements:
	- Multiple servers (done, but not tested)
	- Configurable port (probably not necessary)
	- Configurable timeout
	- Configurable retries

Error recfrom: 10054 - winsock error for 'connection reset'
*/

define('RADIUS_DEBUG',FALSE);

class auth_login extends alt_auth_base
{
	private $server;
	private	$secret;
	private	$port;
	private	$usr;
	private	$pwd;
	private	$connection;			// Handle to use on successful creation
	public	$Available = FALSE;		// Flag indicates whether DB connection available
	public	$ErrorText;				// e107 error string on exit


	/**
	 *	Read configuration, initialise connection to LDAP database
	 *
	 *	@return AUTH_xxxx result code
	 */
	function __construct()
	{
		$this->copyAttribs = array();
		$radius = $this->altAuthGetParams('radius');

		$this->server = explode(',',$radius['radius_server']);
		$this->port = 1812;								// Assume fixed port number for now - 1812 (UDP) is listed for servers, 1645 for authentification. (1646, 1813 for accounting)
														// (A Microsoft app note says 1812 is the RFC2026-compliant port number. (http://support.microsoft.com/kb/230786)
//		$this->port = 1645;
		$this->secret = explode(',',$radius['radius_secret']);
		if ((count($this->server) > 1)  && (count($this->secret) == 1))
		{
			$this->secret = array();
			foreach ($this->server as $k => $v)
			{
				$this->secret[$k] = $radius['radius_secret'];		// Same secret for all servers, if only one entered
			}
		}
		$this->ErrorText = '';
		if(!function_exists('radius_auth_open'))
		{
			return AUTH_NORESOURCE;
		}

		if(!$this -> connect())
		{
			return AUTH_NOCONNECT;
		}
		$this->Available = TRUE;
		return AUTH_SUCCESS;
	}



	/**
	 *	Retrieve and construct error strings
	 */
	function makeErrorText($extra = '')
	{
		$this->ErrorText = $extra.radius_strerror($this->connection) ;
		if (!RADIUS_DEBUG) return;
		$text = "<br />Server: {$this->server}  Stored secret: ".radius_server_secret($this->connection)."  Port: {$this->port}";
		$this->ErrorText .= $text;
	}



	/**
	 *	Try to connect to a radius server
	 *
	 *	@return boolean TRUE for success, FALSE for failure
	 */
	function connect()
	{
		if (!($this->connection = radius_auth_open()))
		{
			$this->makeErrorText('RADIUS open failed: ') ;
			return FALSE;
		}
		foreach ($this->server as $k => $s)
		{
			if (!radius_add_server($this->connection, $s, $this->port, $this->secret[$k], 15, 1))	// fixed 15 second timeout, one try ATM
			{
				$this->makeErrorText('RADIUS add server failed: ') ;
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 *	Close the connection to the Radius server
	 */
	function close()
	{
		if ( !radius_close( $this->connection))		// (Not strictly necessary, but tidy)
		{
			$this->makeErrorText('RADIUS close error: ') ;
			return false;
		}
		else
		{
			return true;
		}
	}



	/**
	 *	Validate login credentials
	 *
	 *	@param string $uname - The user name requesting access
	 *	@param string $pass - Password to use (usually plain text)
	 *	@param pointer &$newvals - pointer to array to accept other data read from database
	 *	@param boolean $connect_only - TRUE to simply connect to the server
	 *
	 *	@return integer result (AUTH_xxxx)
	 *
	 *	On a successful login, &$newvals array is filled with the requested data from the server
	 */
	function login($uname, $pass, &$newvals, $connect_only = FALSE)
	{
		// Create authentification request
		if (!radius_create_request($this->connection,RADIUS_ACCESS_REQUEST))
		{
			$this->makeErrorText('RADIUS failed authentification request: ') ;
			return AUTH_NOCONNECT;
		}

		if (trim($pass) == '') return AUTH_BADPASSWORD;				// Pick up a blank password - always expect one

		// Attach username and password
		if (!radius_put_attr($this->connection,RADIUS_USER_NAME,$uname)
		|| !radius_put_attr($this->connection,RADIUS_USER_PASSWORD,$pass))
		{
			$this->makeErrorText('RADIUS could not attach username/password: ') ;
			return AUTH_NOCONNECT;
		}

		// Finally, send request to server
		switch (radius_send_request($this->connection))
		{
			case RADIUS_ACCESS_ACCEPT :		// Valid username/password
				break;
			case RADIUS_ACCESS_CHALLENGE :	// CHAP response required - not currently implemented
				$this->makeErrorText('CHAP not supported');
				return AUTH_NOUSER;
			case RADIUS_ACCESS_REJECT :		// Specifically rejected
			default:						// Catch-all
				$this->makeErrorText('RADIUS validation error: ') ;
				return AUTH_NOUSER;
		}			

// User accepted here.

		if ($connect_only) return AUTH_SUCCESS;
		return AUTH_SUCCESS;					// Not interested in any attributes returned ATM, so done.



		// See if we get any attributes - not really any use to us unless we implement CHAP, so disabled ATM
		$attribs = array();
		while ($resa = radius_get_attr($this->connection)) 
		{
			if (!is_array($resa)) 
			{
				$this->makeErrorText("Error getting attribute: ");
				exit;
			}
//			Decode attribute according to type (this isn't an exhaustive list)
//		Codes: 2, 3, 4, 5, 30, 31, 32, 60, 61 should never be received by us
//		Codes 17, 21 not assigned
			switch ($resa['attr'])
			{
				case 8 :		// IP address to be set (255.255.255.254 indicates 'allocate your own address')
				case 9 :		// Subnet mask
				case 14 :		// Login-IP host
					$attribs[$resa['attr']] = radius_cvt_addr($resa['data']);
					break;
				case 6 :		// Service type  (integer bitmap)
				case 7 :		// Protocol (integer bitmap)
				case 10 :		// Routing method (integer)
				case 12 :		// Framed MTU
				case 13 :		// Compression method
				case 15 :		// Login service (bitmap)
				case 16 :		// Login TCP port
				case 23 :		// Framed IPX network (0xFFFFFFFE indicates 'allocate your own')
				case 27 :		// Session timeout - maximum connection/login time in seconds
				case 28 :		// Idle timeout in seconds
				case 29 :		// Termination action
				case 37 :		// AppleTalk link number
				case 38 :		// AppleTalk network
				case 62 :		// Max ports
				case 63 :		// Login LAT port
					$attribs[$resa['attr']] = radius_cvt_int($resa['data']);
					break;
				case 1 :		// User name
				case 11 :		// Filter ID - could get several of these
				case 18 :		// Reply message (text, various purposes)
				case 19 :		// Callback number
				case 20 :		// Callback ID
				case 22 :		// Framed route - could get several of these
				case 24 :		// State - used in CHAP
				case 25 :		// Class
				case 26 :		// Vendor-specific
				case 33 :		// Proxy State
				case 34 :		// Login LAT service
				case 35 :		// Login LAT node
				case 36 :		// Login LAT group
				case 39 :		// AppleTalk zone
				default :
					$attribs[$resa['attr']] = radius_cvt_string($resa['data']);		// Default to string type
			}
			printf("Got Attr: %d => %d Bytes %s\n", $resa['attr'], strlen($attribs[$resa['attr']]), $attribs[$resa['attr']]);
		}

		return AUTH_SUCCESS;
	}
}
?>
