<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Session handler
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT'))
{
	exit;
}

/**
 * @package e107
 * @subpackage	e107_handlers
 * @version $Id$
 * @author SecretR
 * 
 * Dependencies:
 * - direct: language handler
 * - indirect: system preferences (required by language handler)
 * 
 * What could break it?
 * If session is started before the first system session call (see class2.php
 * 'Start: Set User Language' phase), session config will not be applied!
 * This could happen if included $CLASS2_INCLUDE script (see class2.php)
 * calls session_start(). However, sessions will not be broken, just not secured
 * as per e_SECURITY_LEVEL setting.
 * 
 * Security levels:
 * - SECURITY_LEVEL_NONE [0]: security disabled - no token checks, all session validation settings dsiabled 
 * - SECURITY_LEVEL_BALANCED [5]: ValidateRemoteAddr, ValidateHttpXForwardedFor are on,
 * session token is created/checked, but not regenerated on every page load
 * - SECURITY_LEVEL_HIGH [7]: Same as above but ValidateHttpVia, ValidateHttpUserAgent are on. 
 * - SECURITY_LEVEL_PARANOID [9]: Same as SECURITY_LEVEL_HIGH except session token is regenerated on
 * every page load. 'httponly' is on, which means JS is unable to retrieve session cookie, this may cause
 * troubles with some browsers.
 * - SECURITY_LEVEL_INSANE [10]: Same as SECURITY_LEVEL_HIGH plus session id is regenerated at the end
 * of every page request. 
 * 
 * Session objects are created by namespace:
 * $_SESSION['e107'] is default namesapce auto created with
 * <code><?php e107::getSession();</code>
 * Session handler is validating corresponding session COOKIE 
 * (named as current session name, keeping the session id)
 * on regular basis (session lifetime/4). If validation
 * fails, corresponding cookie is destroyed (not the session itself).  
 * 
 * Initial system Session is started after language detection (see class2.php) to 
 * ensure proper session handling for sites using language sub-domains (e.g. fr.site.com)
 * 
 * Some important system session data will be kept outside of the object for now (e.g. user validation data) 
 * 
 */


class e_session
{
	/**
	 * No protection, label 'Looking for trouble'
	 * @var integer
	 */
	const SECURITY_LEVEL_NONE = 0;


	const SECURITY_LEVEL_LOW = 3;
	/**
	 * Default system protection, balanced for best user experience, 
	 * label 'Safe mode - Balanced'
	 * @var integer
	 */
	const SECURITY_LEVEL_BALANCED = 5;
	
	/**
	 * Adds more system security, but there is a chance (minimal) to break stuff,
	 * label 'High Security'
	 * @var integer
	 */
	const SECURITY_LEVEL_HIGH = 7;
	
	/**
	 * High system protection, session id is regenerated on every page request,
	 * label 'Paranoid'
	 * @var integer
	 */
	const SECURITY_LEVEL_PARANOID = 9;
	
	/**
	 * Highest system protection, session id and token values are regenerated on every page request,
	 * label 'Insane'
	 * @var int unknown_type
	 */
	const SECURITY_LEVEL_INSANE = 10;

	/**
	 * A POST carrying no token is allowed through, the behaviour before
	 * GHSA-72q5-94gw-prww
	 * @var int
	 */
	const TOKEN_CHECK_OFF = 0;

	/**
	 * A POST carrying no token is allowed through but recorded in the admin log,
	 * so an operator can measure what would break before switching enforcement on
	 * @var int
	 */
	const TOKEN_CHECK_LOG = 1;

	/**
	 * A POST carrying no token is refused
	 * @var int
	 */
	const TOKEN_CHECK_ENFORCE = 2;

	/**
	 * Session save path
	 * @var string
	 */
	protected $_sessionSavePath = false;

	/**
	 * Session save method
	 * @var string files|db
	 */
	protected $_sessionSaveMethod = 'files';//'files';

	/**
	 * Session cache limiter, ignored if empty
	 * php.net/manual/en/function.session-cache-limiter.php
	 * @var string public|private_no_expire|private|nocache
	 */
	protected $_sessionCacheLimiter = '';
	
	protected $_namespace;
	protected $_name;
	protected static $_sessionStarted = false; // Fixes lost $_SESSION value problem.

	/**
	 * Validation options
	 * @var boolean
	 */
	protected $_sessionValidateRemoteAddr = true;
	protected $_sessionValidateHttpVia = true;
	protected $_sessionValidateHttpXForwardedFor = true;
	protected $_sessionValidateHttpUserAgent = true;

	/**
	 * Skip validation
	 * @var array
	 */
	protected $_sessionValidateRemoteAddrSkip = array();
	protected $_sessionValidateHttpViaSkip = array();
	protected $_sessionValidateHttpXForwardedForSkip = array();
	protected $_sessionValidateHttpUserAgentSkip = array();

	/**
	 * Default session options
	 * @var array
	 */
	protected $_options = array(
		'lifetime'	 => 3600 , // 1 hour
		'path'		 => '',
		'domain'	 => '',
		'secure'	 => false,
		'httponly'	 => true,
	);

	/**
	 * Session data
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * Set session options
	 * @param string $key
	 * @param mixed $value
	 * @return e_session
	 */
	public function setOption($key, $value)
	{
		$this->setOptions(array($key => $value));
		return $this;
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}



	/**
	 * Get session option
	 * @param string $key
	 * @param mixed $default
	 * @return mixed value
	 */
	public function getOption($key, $default = null)
	{
		return (isset($this->_options[$key]) ? $this->_options[$key] : $default);
	}
	
	/**
	 * Set default settings/options based on the current security level
	 * NOTE: new prefs 'session_save_path', 'session_save_method', 'session_lifetime' introduced, 
	 * still not added to preference administration
	 * @return e_session
	 */
	public function setDefaultSystemConfig()
	{
        if ($this->getSessionId()) return $this;

        $config = array(
            'ValidateRemoteAddr' => (e_SECURITY_LEVEL >= self::SECURITY_LEVEL_BALANCED),
            'ValidateHttpVia' => (e_SECURITY_LEVEL >= self::SECURITY_LEVEL_HIGH),
            'ValidateHttpXForwardedFor' => (e_SECURITY_LEVEL >= self::SECURITY_LEVEL_LOW),
            'ValidateHttpUserAgent' => (e_SECURITY_LEVEL >= self::SECURITY_LEVEL_HIGH),
        );

        $options = array(
            //		'httponly' => (e_SECURITY_LEVEL >= self::SECURITY_LEVEL_PARANOID),
            'httponly' => true,
        );

        if (!defined('E107_INSTALL'))
        {
            $systemSaveMethod = ini_get('session.save_handler');

            $saveMethod = (!empty($systemSaveMethod)) ? $systemSaveMethod : 'files';

            $config['SavePath']     = e107::getPref('session_save_path', false); // FIXME - new pref
            $config['SaveMethod']   = e107::getPref('session_save_method', $saveMethod);
            $options['lifetime']    = (int) e107::getPref('session_lifetime', 86400);
            $options['path']        = e107::getPref('session_cookie_path', ''); // FIXME - new pref
            $options['secure']      = e107::getPref('ssl_enabled', false); //

            e107::getDebug()->log("Session Save Method: ".$config['SaveMethod']);

            if (!empty($options['secure']))
            {
                ini_set('session.cookie_secure', 1);
            }

            ini_set('session.gc_maxlifetime', $options['lifetime']);
        }

        if (defined('SESSION_SAVE_PATH')) // safer than a pref.
        {
            $config['SavePath'] = e_BASE . SESSION_SAVE_PATH;
        }

        $hashes = hash_algos();

    //    if ((e_SECURITY_LEVEL >= self::SECURITY_LEVEL_BALANCED) && in_array('sha512', $hashes))
        {

          //  ini_set('session.hash_function', 'sha512'); Removed in PHP 7.1
          //  ini_set('session.hash_bits_per_character', 5); Removed in PHP 7.1
        }

        $this->fixSessionFileGarbageCollection();

        $this->setConfig($config)
            ->setOptions($options);

        return $this;
	}

    /**
     * Modify PHP ini at runtime to enable session file garbage collection
     *
     * Takes no action if the garbage collector is already enabled.
     *
     * @see https://github.com/e107inc/e107/issues/4113
     * @return void
     */
	private function fixSessionFileGarbageCollection()
    {
        $gc_probability = ini_get('session.gc_probability');
        if ($gc_probability > 0) return;

        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
    }
	
	/**
	 * Retrieve value from current session namespace
	 * Equals to $_SESSION[NAMESPACE][$key]
	 * @param string $key
	 * @param boolean $clear unset key
	 * @return mixed
	 */
	public function get($key, $clear = false)
	{
		$ret = isset($this->_data[$key]) ? $this->_data[$key] : null;
		if($clear) $this->clear($key);
		return $ret;
	}
	
	/**
	 * Retrieve value from current session namespace
	 * If key is null, returns all current session namespace data
	 * 
	 * @param string|null $key
	 * @param boolean $clear
	 * @return mixed
	 */
	public function getData($key = null, $clear = false)
	{
		if(null === $key)
		{
			$ret = $this->_data;
			if($clear) $this->clearData();
			return $ret;
		}
		return $this->get($key, $clear);
	}
	
	/**
	 * Set value in current session namespace
	 * Equals to $_SESSION[NAMESPACE][$key] = $value
	 * @param string $key Also accepts multi-dimensinal format. key1/key2
	 * @param mixed $value
	 * @return e_session
	 */
	public function set($key, $value)
	{
		if(strpos($key,'/') !== false) // multi-dimensional
		{
			$keyArr = explode('/',$key);
			$count = count($keyArr);

		    if($count === 2)
		    {
		        list($k1, $k2) = $keyArr;
		        $this->_data[$k1][$k2] = $value;
		    }
		    elseif($count === 3)
		    {
		        list($k1, $k2, $k3) = $keyArr;
		        $this->_data[$k1][$k2][$k3] = $value;
		    }

		}
		else
		{
			$this->_data[$key] = $value;
		}

		return $this;
	}
	
	/**
	 * Set value in current session namespace
	 * If $key is array, the whole namespace array will be replaced with it,
	 * $value will be ignored
	 * @param string|null $key
	 * @param mixed $value
	 * @return e_session
	 */
	public function setData($key, $value = null)
	{
		if(is_array($key))
		{
			$this->_data = $key;
			return $this;
		}
		return $this->set($key, $value);
	}
	
	/**
	 * Check if given key is set in current session namespace
	 * Equals to isset($_SESSION[NAMESPACE][$key])
	 * @param string $key
	 * @return boolean
	 */
	public function is($key)
	{
		return isset($this->_data[$key]);
	}
	
	/**
	 * Check if given key is set and not empty in current session namespace
	 * Equals to !empty($_SESSION[NAMESPACE][$key]) check
	 * @param string $key
	 * @return boolean
	 */
	public function has($key)
	{
		return (isset($this->_data[$key]) && $this->_data[$key]);
	}
	
	/**
	 * Checks if current session namespace contains any data
	 * Equals to !empty($_SESSION[NAMESPACE]) check
	 * @return boolean
	 */
	public function hasData()
	{
		return !empty($this->_data);
	}
	
	/**
	 * Unset member of current session namespace array
	 * Equals to unset($_SESSION[NAMESPACE][$key])
	 * @param string $key
	 * @return e_session
	 */
	public function clear($key=null)
	{
		if($key == null) // clear all under this namespace.
		{
			$this->_data = array(); // must be set to array() not unset.
			return $this;
		}

		if(strpos($key,'/') !== false) // multi-dimensional
		{
			$keyArr = explode('/',$key);
			$count = count($keyArr);

		    if($count === 2)
		    {
		        list($k1, $k2) = $keyArr;
		        unset($this->_data[$k1][$k2]);
		    }
		    elseif($count === 3)
		    {
		        list($k1, $k2, $k3) = $keyArr;
		        unset($this->_data[$k1][$k2][$k3]);
		    }

		}
		else
		{
			unset($this->_data[$key]);
		}


		return $this;
	}
	
	/**
	 * Reset current session namespace to empty array 
	 * @return e_session
	 */
	public function clearData()
	{
		$this->_data = array();
		return $this;
	}

	/**
	 * Set protected class vars, prefixed with _session
	 * @param array $config
	 * @return e_session
	 */
	public function setConfig($config)
	{
		foreach ($config as $k => $v)
		{
			$key = '_session'.$k;
			if (isset($this->$key)) $this->$key = $v;
		}
		return $this;
	}
	
	/**
	 * Get registered namespace key
	 * @return string
	 */
	public function getNamespaceKey()
	{
		return $this->_namespace;
	}

	/**
	 * Runtime override of the token check mode, or null to follow the preference.
	 *
	 * @var int|null
	 */
	private static $_tokenCheckMode = null;

	/**
	 * How hard {@see e_core_session::check()} is on a request that submits no token.
	 *
	 * This is the designated API for the question. Read it here rather than
	 * reaching for the preference, so the runtime override below is honoured
	 * everywhere.
	 *
	 * It governs the tokenless case only. A request that presents a token which
	 * does not validate is always refused, whatever this returns. It is also the
	 * only lever: {@see e_token_injector} always injects, so a site whose forms
	 * are being handed a token they cannot honour relaxes this rather than
	 * switching injection off.
	 *
	 * @return int one of the TOKEN_CHECK_* constants
	 */
	public static function tokenCheckMode()
	{
		if(self::$_tokenCheckMode !== null)
		{
			return self::$_tokenCheckMode;
		}

		return (int) e107::getPref('csrf_enforce', self::TOKEN_CHECK_ENFORCE);
	}

	/**
	 * Override the token check mode for the rest of this request.
	 *
	 * Exists so a test, or a bootstrap that knows better than the stored
	 * preference, can set the mode without a define. Pass null to hand control
	 * back to the preference.
	 *
	 * @param int|null $mode one of the TOKEN_CHECK_* constants, or null
	 * @return int|null the override that was in force, for restoring afterwards
	 */
	public static function setTokenCheckMode($mode)
	{
		$previous = self::$_tokenCheckMode;

		self::$_tokenCheckMode = ($mode === null) ? null : (int) $mode;

		return $previous;
	}

	/**
	 * Reset session options
	 * @param array $options
	 * @return e_session
	 */
	public function setOptions($options)
	{
		if (empty($options) || !is_array($options)) return $this;
		foreach ($options as $k => $v)
		{
			switch ($k)
			{
				case 'lifetime':
					$v = intval($v);
				break;

				case 'path':
				case 'domain':
					$v = (string) $v;
				break;

				case 'secure':
				case 'httponly':
					$v = $v ? true : false;
				break;

				default:
					$v = null;
				break;
			}

			if($v !== null)
			{
				$this->_options[$k] = $v;
			}
		}
		return $this;
	}

	/**
	 * @param $namespace
	 * @param $sessionName
	 * @return void
	 */
	public function init($namespace, $sessionName = null)
	{
		$this->start($sessionName);

		if (!isset($_SESSION[$namespace]))
		{
			$_SESSION[$namespace] = array();
		}
		$this->_data =& $_SESSION[$namespace];
		$this->_namespace = $namespace;

		$this->validate();
		$this->validateSessionCookie();
	}

	/**
	 * Conigure and start session
	 *
	 * @param string $sessionName optional session name
	 * @return e_session
	 */
	public function start($sessionName = null)
	{
	
		if (isset($_SESSION) && (self::$_sessionStarted === true))
		{
			return $this;
		}

		if (false !== $this->_sessionSavePath && is_writable($this->_sessionSavePath))
		{
			session_save_path($this->_sessionSavePath);
		}

		switch ($this->_sessionSaveMethod)
		{
			case 'db':
			//	ini_set('session.save_handler', 'user');

				$session = new e_session_db;
				session_set_save_handler(
					[$session, 'open'],
					[$session, 'close'],
					[$session, 'read'],
					[$session, 'write'],
					[$session, 'destroy'],
					[$session, 'gc']
				);
				$session->setSaveHandler();
			break;

			default:
				if(!isset($_SESSION))
				{
					session_module_name($this->_sessionSaveMethod);
				}
			break;
		}

		if (empty($this->_options['domain']))
		{
			// MULTILANG_SUBDOMAIN set during initial language detection in language handler
			$doma = ((deftrue('e_SUBDOMAIN') || deftrue('MULTILANG_SUBDOMAIN')) && e_DOMAIN != FALSE) ? ".".e_DOMAIN : FALSE; // from v1.x
			$this->_options['domain'] = $doma;
		}

		if (empty($this->_options['path']))
		{
			if(defined('e_MULTISITE_MATCH')) // multisite support.
			{
				$this->_options['path'] = '/';
			}
			else
			{
				$this->_options['path'] = defined('e_HTTP') ? e_HTTP : '/';
			}
		}

		// session name before options - problems reported on php.net
		if (!empty($sessionName))
		{
			$this->setSessionName($sessionName);
		}
		
		// set session cookie params
		session_set_cookie_params($this->_options['lifetime'],
			$this->_options['path'],
			$this->_options['domain'],
			$this->_options['secure'],
			$this->_options['httponly']);

		if ($this->_sessionCacheLimiter)
		{
			session_cache_limiter((string) $this->_sessionCacheLimiter); //XXX Remove and have e_headers class handle it?
		}
		
	
		session_start();
		self::$_sessionStarted = true;
		return $this;
	}

	/**
	 * Set session ID
	 * @param string $sid
	 * @return e_session
	 */
	public function setSessionId($sid = null)
	{
		// comma and minus allowed since 5.0
		if (!empty($sid) && preg_match('#^[0-9a-zA-Z,-]+$#', $sid))
		{
			session_id($sid);
		}
		return $this;
	}

	/**
	 * Retrieve current session id
	 * @return string
	 */
	public function getSessionId()
	{
		return session_id();
	}
	
	/**
	 * Retrieve current session save method. 
	 * @return string
	 */
	public function getSaveMethod()
	{
		return $this->_sessionSaveMethod;	
	}

	/**
	 * Set new session name
	 * @param string $name alphanumeric characters only
	 * @return false old session name or false on error
	 */
	public function setSessionName($name)
	{
		if (!empty($name) && preg_match('#^[0-9a-z_]+$#i', $name))
		{
			$this->_name = $name;
		//	return session_name($name);
		}
		return false;
	}

	/**
	 * Retrieve current session name
	 * @return string
	 */
	public function getSessionName()
	{
		return session_name();
	}

	/**
	 * Reset session cookie lifetime
	 * We reset session cookie on every (session_lifetime / 4) seconds 
	 * It's done by all session handler instances, they all share
	 * one and the same '_cookie_session_validate' variable (global session namespace)
	 * @return e_session
	 */
	public function validateSessionCookie()
	{
		if (!$this->_options['lifetime'])
		{
			return $this;
		}

		if (empty($_SESSION['_cookie_session_validate']))
		{
			$time = time() + round($this->_options['lifetime'] / 4);
			$_SESSION['_cookie_session_validate'] = $time;
		}
		elseif ($_SESSION['_cookie_session_validate'] < time())
		{ 
			if (!headers_sent())
			{
				cookie(session_name(), session_id(), time() + $this->_options['lifetime'], $this->_options['path'], $this->_options['domain'], $this->_options['secure']);
				$time = time() + round($this->_options['lifetime'] / 4);
				$_SESSION['_cookie_session_validate'] = $time;
			}
		}

		return $this;
	}
	
	/**
	 * Delete session cookie
	 * @return e_session
	 */
	public function cookieDelete()
	{
		cookie(session_name(), null, null, $this->_options['path'], $this->_options['domain'], $this->_options['secure']);
		return $this;
	}

	/**
	 * Validate current session
	 * @return e_session
	 */
	public function validate()
	{
		if (!isset($this->_data['_session_validate_data']))
		{
			$this->_data['_session_validate_data'] = $this->getValidateData();
		}
		elseif (!$this->_validate())
		{ 
			$sessionData = $this->_data['_session_validate_data'];
			$validateData = $this->getValidateData();
			
			$details = 'USER INFORMATION: '.(isset($_COOKIE[e_COOKIE]) ? $_COOKIE[e_COOKIE] : (isset($_SESSION[e_COOKIE]) ? $_SESSION[e_COOKIE] : 'n/a'))."\n";
			$details .= "HOST: ".$_SERVER['HTTP_HOST']."\n";
			$details .= "REQUEST_URI: ".$_SERVER['REQUEST_URI']."\n";	
			$details .= "SESSION OPTIONS: ".print_r($this->_options, true)."\n";	
			$details .= "SESSION NAMESPACE: ".$this->_namespace."\n";	
			$details .= "SESSION VALIDATION DATA SAVED: ".print_r($sessionData, true)."\n";
			$details .= "SESSION VALIDATION DATA CURRENT: ".print_r($validateData, true)."\n";
			$details .= "CURRENT NAMESPACE SESSION DATA:\n";
			$this->clear('_session_validate_data'); // already logged
			$details .= print_r($this->_data, true);
			$this->close(false);
			$details .= "SESSION GLOBAL DATA:\n";
			$details .= print_r($_SESSION, true);
			
			// delete cookie, destroy session
			$this->cookieDelete()->destroy();
			
			// TODO event trigger
			
			// e107::getAdminLog()->add('Session validation failed!', $details, E_LOG_FATAL);
			// TODO session exception, handle it proper on live site
			// throw new Exception('');
			
			// just for now
			$msg = 'Session validation failed! <a href="'.strip_tags($_SERVER['REQUEST_URI']).'">Go Back</a>';
		//	die($msg); //FIXME not functioning as intended. 
		}

		return $this;
	}

	/**
	 * Validate current session based on config options
	 *
	 * @return bool
	 */
	protected function _validate()
	{
		$sessionData = $this->_data['_session_validate_data'];
		$validateData = $this->getValidateData();
		$keyvar = '_sessionValidate';
		
		foreach ($validateData as $vkey => $value) 
		{
			$var = $keyvar.$vkey;
			$varskip = $var.'Skip';
			if ($this->$var && $sessionData[$vkey] != $value && !in_array($value, $this->$varskip))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve data for validator
	 * @return array
	 */
	public function getValidateData()
	{
		$data = array(
			'RemoteAddr' => '',
			'HttpVia' => '',
			'HttpXForwardedFor' => '',
			'HttpUserAgent' => ''
		);

		// collect ip data
		if (isset($_SERVER['REMOTE_ADDR']))
		{
			$data['RemoteAddr'] = (string) $_SERVER['REMOTE_ADDR'];
		}
		if (isset($_ENV['HTTP_VIA']))
		{
			$data['HttpVia'] = (string) $_ENV['HTTP_VIA'];
		}
		if (isset($_ENV['HTTP_X_FORWARDED_FOR']))
		{
			$data['HttpXForwardedFor'] = (string) $_ENV['HTTP_X_FORWARDED_FOR'];
		}

		// collect user agent data
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$data['HttpUserAgent'] = (string) $_SERVER['HTTP_USER_AGENT'];
		}

		return $data;
	}

	/**
	 * Retrieve (create if doesn't exist) XSF protection token
	 * @param boolean $in_form if true (default) - value for forms, else raw session value
	 * @return string
	 */
	public function getFormToken($in_form = true)
	{
		// e_TOKEN_DISABLE used to suppress this too, to stop the error page
		// "refreshing" the token. It could never do that: the has() test already
		// means an existing token is left alone. All it did was skip minting the
		// FIRST one, so a session that met error.php before anything else got
		// md5(null) stamped into every form on the page and was then refused for
		// presenting a token that cannot validate.
		if(!$this->has('__form_token'))
		{
			$this->set('__form_token', $this->_generateFormToken());
			if(deftrue('e_DEBUG_SESSION')) // XXX enable to troubleshoot "Unauthorized Access!" issues.
			{
				$message = date('r')."\t\t".e_REQUEST_URI."\n";
				file_put_contents(__DIR__.'/session.log', $message, FILE_APPEND);
			}
		}
		return ($in_form ? md5($this->get('__form_token')) : $this->get('__form_token'));
	}
	
	/**
	 * Regenerate form token value
	 * TODO - save old token
	 * @return e_session
	 */
	protected function _regenerateFormToken()
	{
		$this->set('__form_token', $this->_generateFormToken());
		return $this;
	}

	/**
	 * Mint a raw XSF protection token.
	 *
	 * Only md5() of this value ever reaches the client, so the raw format is
	 * unconstrained; 256 bits of CSPRNG output as hex. Aborts the request when
	 * the platform has no CSPRNG rather than minting a guessable token.
	 *
	 * @see e_random::hex()
	 * @return string 64 hex characters
	 */
	protected function _generateFormToken()
	{
		return e_random::hex(64);
	}

	/**
	 * Do a check against passed token
	 * @param string $token
	 * @return boolean
	 */
	public function checkFormToken($token)
	{
		$utoken = $this->getFormToken(false);
		return ($token === md5($utoken));
	}
	
	/**
	 * Clear and Unset current namespace, unregister session singleton
	 * e107::getSession('namespace') if needed.
	 * @param boolean $unregister if true (default) - unregister Singleton, destroy namespace, 
	 * 								else alias of self::clearData()
	 * @return void
	 */
	public function close($unregister = true)
	{
		$this->clearData();
		if($unregister) 
		{
			unset($_SESSION[$this->_namespace]);
			e107::setRegistry('core/e107/session/'.$this->_namespace, null);
		}
	}
	
	/**
	 * Save session data to disk, end session.
	 * Sessions can't be used after this point.
	 * Method should be called before every header redirect.
	 * @return void
	 */
	public function end()
	{
		session_write_close();
	}
	
	/**
	 * Destroy all session data
	 * @return e_session
	 */
	public function destroy()
	{
		$this->cookieDelete()->close();
		//unset($_SESSION);
		
		// cleanup
		cookie(e_COOKIE, null, null); // remove user auth cookie
		// unset($_SESSION['_cookie_session_validate']);
		
		session_destroy();
		return $this;
	}

	/**
	 * @return void
	 */
	public function replaceRegistry()
	{
		e107::setRegistry('core/e107/session/'.$this->_namespace, $this, true);
	}
}


/**
 *
 */
class e_core_session extends e_session
{
	/**
	 * Constructor
	 * 3rd party code and/or other system areas are 
	 * able to extend the base e_session class and 
	 * add more or override the implemented functionality, has their own
	 * namespace, add more session security etc.
	 * @param array $data session config data
	 */
	public function __construct($data = array())
	{	
		// default system configuration
		$this->setDefaultSystemConfig();

		$namespace = 'e107sess'; // Quick Fix for Fatal Error "Cannot use object of type e107 as array" on line 550
		$name = (isset($data['name']) && !empty($data['name']) ? $data['name'] : deftrue('e_COOKIE', 'e107')).'SID';
		if(isset($data['namespace']) && !empty($data['namespace'])) $namespace = $data['namespace'];

		// create $_SESSION['e107'] namespace by default
		$this->init($namespace, $name);
	}
	
	/**
	 * Session shutdown - called at the top of footer_default.php by default
	 * @return void
	 */
	public function shutdown()
	{
		if(!session_id()) // someone closed the session?
		{
			$this->init($this->_namespace, $this->_name); // restart
		}
		
		// give 3rd party code a way to prevent token re-generation
		if(e_SECURITY_LEVEL >= e_session::SECURITY_LEVEL_PARANOID && !deftrue('e_TOKEN_FREEZE'))
		{	
			if(e_SECURITY_LEVEL == e_session::SECURITY_LEVEL_INSANE)
			{
				// regenerate SID
				$oldSID = session_id(); // old SID
				$oldSData = $_SESSION; // old session data
				session_regenerate_id(false); // true don't work on php4 - so time to move on people!	
				$newSID = session_id(); // new SID
				
				// Clean
				session_id($oldSID); // switch to the old session
				session_destroy(); // destroy it
				
				// set new ID, reopen the session, set saved data
				session_id($newSID);
				session_start();
				$_SESSION = $oldSData;
			}
			$this->set('__form_token_regenerate', time()); // check() needs it to re-create token on the next request
		}
		// write session data
		$this->end();
	}

	/**
	 * @param $status
	 * @param $type
	 * @return void|null
	 */
	private function log($status, $type=E_LOG_FATAL)
	{

		if(!deftrue('e_DEBUG_SESSION'))
		{
			return null;
		}


	//	$details = "USER: ".USERNAME."\n";
		$details = "HOST: ".$_SERVER['HTTP_HOST']."\n";
		$details .= "REQUEST_URI: ".$_SERVER['REQUEST_URI']."\n";

		$details .= ($_POST['e-token']) ? "e-token (POST): ".$_POST['e-token']."\n" : "";
		$details .= ($_GET['e-token']) ? "e-token (GET): ".$_GET['e-token']."\n" : "";
		$details .= ($_POST['e_token']) ? "AJAX e_token (POST): ".$_POST['e_token']."\n" : "";
/*
		$utoken = $this->getFormToken(false);
		$details .= "raw token: ".$utoken."\n";
		$details .= "checkFormToken (e-token should match this): ".md5($utoken)."\n";
		$details .= "md5(e-token): ".md5($_POST['e-token'])."\n";*/
/*
		$regenerate = $this->get('__form_token_regenerate');
		$details .= "Regenerate after: ".date('r', $regenerate)." (".$regenerate.")\n";
*/

		$details .= "has __form_token: ";
		$hasToken = $this->has('__form_token');
		$details .= empty($hasToken) ? 'false' : 'true';
		$details .= "\n";

		$details .= "_SESSION:\n";
		$details .= print_r($_SESSION,true);

		/*	if($pref['plug_installed'])
			{
				$details .= "\nPlugins:\n";
				$details .= print_r($pref['plug_installed'],true);
			}*/

		$details .= $status."\n\n---------------------------------\n\n";

		$log = e107::getLog();
		$log->addDebug($details);

		if(deftrue('e_DEBUG_SESSION'))
		{
			$log->toFile('Unauthorized_access','Unauthorized access Log', true);
		}

		$log->add($status, $details, $type);


	}
	/**
	 * Core CSF protection, see class2.php
	 * Could be adopted by plugins for their own (different) protection logic
	 *
	 * A POST is rejected both when it carries a token that does not validate and
	 * when it carries no token at all. The second half is governed by
	 * {@see e_session::tokenCheckMode()} and applies only to a request that
	 * presented a cookie, see {@see e_core_session::hasAmbientAuthority()}.
	 *
	 * @param boolean $die
	 * @return boolean
	 */
	public function check($die = true)
	{
		// define('e_TOKEN_NAME', 'e107_token_'.md5($_SERVER['HTTP_HOST'].e_HTTP));
		// TODO e-token required for all system forms?

		// only if not disabled and not in 'cli' mod
		if(e_SECURITY_LEVEL < e_session::SECURITY_LEVEL_LOW || e107::getE107('cli')) return true;

		if($this->getSessionId())
		{

			if((isset($_POST['e-token']) && !$this->checkFormToken($_POST['e-token']))
			|| (isset($_GET['e-token']) && !$this->checkFormToken($_GET['e-token']))
			|| (isset($_SERVER['HTTP_X_E_TOKEN']) && !$this->checkFormToken($_SERVER['HTTP_X_E_TOKEN']))
			|| (isset($_POST['e_token']) && !$this->checkFormToken($_POST['e_token']))) // '-' is not allowed in jquery. b
			{
				$this->log('Unauthorized access!');
				// do not redirect, prevent dead loop, save server resources
				if($die == true)
				{
					 die('Unauthorized access!');
				}

				return false;
			}

			if(self::isStateChangingRequest() && self::hasAmbientAuthority() && !self::hasSubmittedToken())
			{
				$mode = self::tokenCheckMode();

				if($mode === self::TOKEN_CHECK_LOG)
				{
					$this->logMissingToken($mode);
				}

				if($mode >= self::TOKEN_CHECK_ENFORCE)
				{
					$this->log('Unauthorized access!');

					if($die == true)
					{
						die('Unauthorized access!');
					}

					return false;
				}
			}

			$this->log('Session Token Okay!', defset('E_LOG_NOTICE', 1));

		}

		if(!defined('e_TOKEN'))
		{
			// FREEZE token regeneration if minimal, ajax or iframe (ajax and iframe not implemented yet) request
			$_toFreeze = (e107::getE107('minimal') || e107::getE107('ajax') || e107::getE107('iframe'));
			if(!defined('e_TOKEN_FREEZE') && $_toFreeze)
			{
				define('e_TOKEN_FREEZE', true);
			}
			// __form_token_regenerate set in footer, so if footer is not called, token will be never regenerated!
			if(e_SECURITY_LEVEL == e_session::SECURITY_LEVEL_INSANE && !deftrue('e_TOKEN_FREEZE') && $this->has('__form_token_regenerate')) 
			{
				$this->_regenerateFormToken()
					->clear('__form_token_regenerate');
			}
			define('e_TOKEN', $this->getFormToken());
		}
		
		return true;
	}


	
	/**
	 * Only POST is treated as state-changing. It is the sole method a browser will
	 * send cross-site without a CORS preflight, so it is the whole CSRF surface,
	 * and confining the check to it keeps every GET working exactly as before.
	 *
	 * @return bool
	 */
	private static function isStateChangingRequest()
	{
		return (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST');
	}

	/**
	 * @return bool whether the request presented a token at all, valid or not
	 */
	private static function hasSubmittedToken()
	{
		return (isset($_POST['e-token']) || isset($_GET['e-token'])
			|| isset($_SERVER['HTTP_X_E_TOKEN']) || isset($_POST['e_token']));
	}

	/**
	 * Does this request carry anything the browser would attach on its own?
	 *
	 * Cross-site request forgery works by borrowing ambient authority: the
	 * attacker cannot read the response, so the only thing the forged request is
	 * worth is whatever the browser attaches without being asked. A request that
	 * presents no cookie at all has nothing to borrow, so refusing it buys no
	 * security and breaks every machine-to-machine caller, a payment gateway's
	 * IPN callback among them.
	 *
	 * @return bool
	 */
	private static function hasAmbientAuthority()
	{
		return !empty($_COOKIE);
	}

	/**
	 * Record a tokenless POST in the admin log.
	 *
	 * Only ever called in log-only mode, which an operator turns on deliberately
	 * and briefly. Writing a row for a refused request instead hands anyone who
	 * can reach the site an unthrottled insert into an indexed table.
	 *
	 * Field names are recorded, values are not, because a login POST would
	 * otherwise put a password in the log.
	 *
	 * @param int $mode the mode this was decided under
	 * @return void
	 */
	private function logMissingToken($mode)
	{
		$details  = "METHOD: POST\n";
		$details .= "URI: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . "\n";
		$details .= "REFERER: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') . "\n";
		$details .= "FIELDS: " . implode(', ', array_keys($_POST)) . "\n";
		$details .= "SESSION: " . substr(sha1((string) $this->getSessionId()), 0, 12) . "\n";
		$details .= "ACTION: " . (($mode >= self::TOKEN_CHECK_ENFORCE) ? 'refused' : 'allowed, log-only mode') . "\n";

		e107::getLog()->add('CSRF_01', $details, defset('E_LOG_WARNING', 2));
	}

	/**
	 * Manually Reset the Token.
	 * @see e107forum::ajaxQuickReply();
	 */
	public function reset()
	{
		$this->_regenerateFormToken()->clear('__form_token_regenerate');
	}
	
	
	/**
	 * Make sure there is unique challenge string for CHAP login
	 * @see class2.php
	 * @return e_core_session
	 
	 @TODO: Remove debug code
	 */
	public function challenge()
	{
		// could go, see _validate()
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		if (!$this->is('ubrowser'))
		{
			$this->set('ubrowser', md5('E107'.$user_agent));
		}

		// Only generate CHAP challenges if CHAP authentication is enabled
		if (!e107::pref('core', 'password_CHAP', 0))
		{
			return $this;
		}

		if (!$this->is('challenge'))		// TODO: Eliminate need for this
		{
			$this->set('challenge', e_random::hex(40)); // New challenge for next time, same 40 hex characters sha1() gave
		}
		if ($this->is('challenge'))
		{	
			$this->set('prevprevchallenge', $this->get('prevchallenge'));		// Purely for debug
			$this->set('prevchallenge', $this->get('challenge'));				// Need to check user login against this
		}
		else
		{
			$this->set('prevchallenge', '');									// Dummy value
			$this->set('prevprevchallenge', '');								// Dummy value
		}
		//$this->set('challenge', sha1(time().rand().$this->getSessionId()));		// Temporarily disabled
		// FIXME - session id will be regenerated if e_SECURITY_LEVEL is 'paranoid|insane' - generate  (might be OK as long as values retained)
		
		//$extra_text = 'C: '.$this->get('challenge').' PC: '.$this->get('prevchallenge').' PPC: '.$this->get('prevprevchallenge');
		//$logfp = fopen(e_LOG.'authlog.txt', 'a+'); fwrite($logfp, strftime('%H:%M:%S').' CHAP start: '.$extra_text."\n"); fclose($logfp);

		return $this;
	}
}


/**
 * Database session handler
 *
 * @todo PHP 8.1 support with {@see SessionHandlerInterface}
 */
class e_session_db #implements SessionHandlerInterface
{
	/**
	 * @var e_db
	 */
	protected $_db = null;

	/**
	 * Table name
	 * @var string
	 */
	protected $_table = 'session';
	
	/**
	 * @var integer
	 */
	protected $_lifetime = null;
	
	public function __construct()
	{
		$this->_db = e107::getDb('session');
	}

	/**
	 *
	 */
	public function __destruct()
	{
		session_write_close();
	}
	
	/**
	 * @return string
	 */
	public function getTable()
	{
		return $this->_table;
	}
	
	/**
	 * @param string $table
	 * @return e_session_db
	 */
	public function setTable($table)
	{
		$this->_table = $table;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	public function getLifetime()
	{
		if(null === $this->_lifetime)
		{
			$this->_lifetime = ini_get('session.gc_maxlifetime');
			if(!$this->_lifetime)
			{
				$this->_lifetime = 3600;
			}
		}
		return (integer) $this->_lifetime;
	}
	
	/**
	 * @param integer $seconds
	 * @return e_session_db
	 */
	public function setLifetime($seconds = null)
	{
		$this->_lifetime = $seconds;
		return $this;
	}
	
	/**
	 * Set session save handler
	 * @return e_session_db
	 */
	public function setSaveHandler()
	{
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
		return $this;
	}
	
	/**
	 * Open session, parameters are ignored (see e_session handler)
	 * @param string $save_path
	 * @param string $sess_name
	 * @return boolean
	 */
    public function open($save_path, $sess_name)
    {
        return true;
    }
    
	/**
	 * Close session
	 * @return boolean
	 */
    public function close()
    {
    	$this->gc($this->getLifetime());
        return true;
    }
    
    /**
     * Get session data
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {
    	$data = false;
    	$check = $this->_db->select($this->getTable(), 'session_data', "session_id='".$this->_sanitize($session_id)."' AND session_expires>".time());
    	if($check)
    	{
    		$tmp = $this->_db->fetch();
    		$data = base64_decode($tmp['session_data']);
    	}
    	elseif(false !== $check)
    	{
    		$data = '';
    	}
    	return $data;
    }
    
    /**
     * Write session data
     * @param string $session_id
     * @param string $session_data
     * @return boolean
     */
    public function write($session_id, $session_data)
    {
    	$data = array(
    		'data' => array(
	    		'session_expires' => time() + $this->getLifetime(),
	    		'session_data' 	  => base64_encode($session_data),
	    		'session_user'    => defset('USERID'),
    		),
    		'_FIELD_TYPES' => array(
    			'session_id'		=> 'str',
    			'session_expires'	=> 'int',
    			'session_user'      => 'int',
    			'session_data'		=> 'str'
    		),
    		'_DEFAULT' => 'str'
    	);
    	if(!($session_id = $this->_sanitize($session_id)))
    	{
    		return false;
    	}

    	$check = $this->_db->select($this->getTable(), 'session_id', "`session_id`='{$session_id}'");
    	
    	if($check)
    	{
    		$data['WHERE'] = "`session_id`='{$session_id}'";
    		if(false !== $this->_db->update($this->getTable(), $data))
    		{
    			return true;
    		}
    	}
    	else
    	{
    		$data['data']['session_id'] = $session_id;
    		if($this->_db->insert($this->getTable(), $data))
    		{
    			return true;
    		}	
    	}
    	return false;
    }
    
    /**
     * Destroy session
     * @param string $session_id
     * @return boolean
     */
    public function destroy($session_id)
    {
    	$session_id = $this->_sanitize($session_id);
    	$this->_db->delete($this->getTable(), "`session_id`='{$session_id}'");
    	return true;
    }
    
    /**
     * Garbage collection
     * @param integer $session_maxlf ignored - see write()
     * @return boolean
     */
    public function gc($session_maxlf)
    {
    	$this->_db->delete($this->getTable(), '`session_expires`<'.time());
    	return true;
    }
    
    /**
     * Allow only well formed session id string 
     * @param string $session_id
     * @return string
     */
    protected function _sanitize($session_id)
    {
    	return preg_replace('#[^0-9a-zA-Z,-]#', '', $session_id);
    }
}
