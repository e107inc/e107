<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Redirection handler
 *
 * $URL$
 * $Id$
 */

/**
 * Redirection class
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author Cameron
 * @copyright Copyright (C) 2008-2010 e107 Inc.
 */
class redirection
{
	/**
	 * List of pages to not check against e_SELF
	 *
	 * @var array
	 */
	protected $self_exceptions = array();
	
	/**
	 * List of pages to not check against defset('e_PAGE')
	 *
	 * @var array
	 */
	protected $page_exceptions = array();
	
	/**
	 * List of queries to not check against e_QUERY
	 * @var array
	 */
	protected $query_exceptions = array();


	public $staticDomains;

	public $domain;

	public $subdomain;

	public $self;

	public $siteurl;

	/**
	 * Manage Member-Only Mode.
	 *
	 * @return void
	 */
	function __construct()
	{
		$this->self_exceptions = array(e_SIGNUP, SITEURL.'fpw.php', e_LOGIN, SITEURL.'membersonly.php');
		$this->page_exceptions = array('e_jslib.php', 'sitedown.php',e_LOGIN, 'secimg.php');
		$this->query_exceptions = array('logout');
		$this->staticDomains    = defset('e_HTTP_STATIC');
		$this->domain           = defset('e_DOMAIN');
		$this->subdomain        = defset('e_SUBDOMAIN');
		$this->self             = $this->getSelf(true);

		// Remove from self_exceptions:  SITEURL, SITEURL.'index.php', // allows a custom frontpage to be viewed while logged out and membersonly active.
	}


	/**
	 * @return array
	 */
	function getSelfExceptions()
	{
		return $this->self_exceptions;
	}
	
	/**
	 * FIXME - build self_exceptions dynamically - use URL assembling to match the proper URLs later
	 * Store the current URL in a cookie for 5 minutes so we can return to it after being logged out. 
	 * @param string $url if empty self url will be used
	 * @param boolean $forceNoSef if false REQUEST_URI will be used (mod_rewrite support)
	 * @return redirection
	 */
	function setPreviousUrl($url = null, $forceNoSef = false, $forceCookie = false)
	{
		if(!$url)
		{
			// e_SELF, defset('e_PAGE') and e_QUERY not set early enough when in e_SINGLE_ENTRY mod
			if(defset('e_SELF') && in_array(e_SELF, $this->self_exceptions))
			{
				return;
			}
			elseif(in_array(e_REQUEST_URI, $this->self_exceptions))
			{
				return;
			}
			
			if(defset('e_PAGE') && in_array(e_PAGE, $this->page_exceptions))
			{
				return;
			}
			if(in_array($_SERVER['QUERY_STRING'], $this->query_exceptions))
			{
				return;
			}
			$url = $this->getSelf($forceNoSef);
		}
		
		$this->setCookie('_previousUrl', $url, 300, $forceCookie);
		//session_set(e_COOKIE.'_previousUrl',$self ,(time()+300));	
		
		return $this;
	}

	/**
	 * @param $full
	 * @return array|mixed|string|string[]
	 */
	public function getSelf($full = false)
	{
		if($full)
		{
			$url = e_REQUEST_URL;//(e_QUERY) ? e_SELF."?".e_QUERY : e_SELF;
		}
		else
		{
			// TODO - e107::requestUri() - sanitize, add support for various HTTP servers
			$url = e_REQUEST_URI;
		}
		return $url;
	}

	/**
	 * Return the URL the admin was on, prior to being logged-out. 
	 * @return string 
	 */
	public function getPreviousUrl()
	{
		return $this->getCookie('_previousUrl');
	}
		
	/**
	 * Get value stored with self::setCookie()
	 * @param string $name
	 * @return mixed
	 */
	public function getCookie($name) //TODO move to e107_class or a new user l class. 
	{	
		$cookiename = e_COOKIE."_".$name;
		$session = e107::getSession();
		
		if($session->has($name))
		{
			// expired - cookie like session implementation
			if((int) $session->get($name.'_expire') < time())
			{
				$session->clear($name.'_expire')
					->clear($name);
				return false;
			}
			return $session->get($name);
		}
		// fix - prevent null values
		elseif(isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename])
		{
			return $_COOKIE[$cookiename];	
		}

		return false;	
	}
	
	/**
	 * Register url in current session
	 * @param string $name
	 * @param string $value
	 * @param integer $expire expire after value in seconds, null (default) - ignore
	 * @return redirection
	 */
	public function setCookie($name, $value, $expire = null, $forceCookie = false)
	{
		$cookiename = e_COOKIE."_".$name;
		$session = e107::getSession();
		
		if(!$forceCookie && e107::getPref('cookie_name') != 'cookie')
		{
			// expired - cookie like session implementation
			if(null !== $expire) $session->set($name.'_expire', time() + (int) $expire); 
			$session->set($name, $value);
		}
		else
		{
			cookie($cookiename, $value, time() + (int) $expire, e_HTTP, e107::getLanguage()->getCookieDomain());
		}

		return $this;
	}
	
	/**
	 * Clear data set via self::setCookie()
	 * @param string $name
	 * @return redirection
	 */
	public function clearCookie($name)
	{
		$cookiename = e_COOKIE."_".$name;
		$session = e107::getSession();
		$session->clear($name)
			->clear($name.'_expire');
		cookie($cookiename, null, null, e_HTTP, e107::getLanguage()->getCookieDomain());
		return $this;
	}
	
	
	/**
	 * Perform re-direction when Maintenance Mode is active.
	 *
	 * @return void
	 */
	public function checkMaintenance()
	{
		// prevent looping.
		if(strpos(defset('e_SELF'), 'admin.php') !== FALSE || strpos(defset('e_SELF'), 'sitedown.php') !== FALSE)
		{
			return;
		}
		
		if(deftrue('NO_MAINTENANCE')) // per-page disable option. 
		{
			return;	
		}
		
		if(e107::getPref('maintainance_flag') && defset('e_PAGE') !== 'secure_img_render.php')
		{
			// if not admin
			
			$allowed = e107::getPref('maintainance_flag');

			if(defset('e_PAGE') === 'login.php' && empty($_POST)) // allow admins/members to login.
			{
				return null;
			}

	//		if(!ADMIN 
			// or if not mainadmin - ie e_UC_MAINADMIN
	//		|| (e_UC_MAINADMIN == e107::getPref('maintainance_flag') && !getperms('0')))
			
			if(!check_class($allowed)  && !getperms('0'))
			{
				// 307 Temporary Redirect
				$this->redirect(SITEURL.'sitedown.php', TRUE, 307);
			}
		}

	}

	
	/**
	 * Check if user is logged in.
	 *
	 * @return void
	 */
	public function checkMembersOnly()
	{
	
		if(!e107::getPref('membersonly_enabled'))
		{
			return;
		}
		
		if(USER && !e_AJAX_REQUEST)
		{
			$this->restoreMembersOnlyUrl();
			return;
		}
		if(e_AJAX_REQUEST)
		{
			return;
		}
		if(strpos(defset('e_PAGE'), 'admin') !== false)
		{
			return;
		}
		if(in_array(e_SELF, $this->self_exceptions))
		{
			return;
		}
		if(in_array(defset('e_PAGE'), $this->page_exceptions))
		{
			return;
		}
		foreach (e107::getPref('membersonly_exceptions') as $val)
		{
			$srch = trim($val);
			if(!empty($srch) && strpos(e_SELF, $srch) !== false)
			{
				return;
			}
		}
		
		/*
		echo "e_SELF=".e_SELF;
		echo "<br />defset('e_PAGE')=".defset('e_PAGE');
		print_a( $this->self_exceptions);
		print_a($this->page_exceptions);
		*/
		
		$this->saveMembersOnlyUrl();

		$redirectType = e107::getPref('membersonly_redirect');

		// #5335: with registration Disabled (user_reg=0) and no social login, the
		// stock login.php turns guests away and bounces them back here, looping until
		// the browser aborts. Fall back to the members-only splash (a dead end) to
		// break the loop; a custom login page is left to handle its own guest access.
		if($redirectType != 'splash'
			&& e_LOGIN === SITEURL.'login.php'
			&& !e107::getPref('user_reg')
			&& !e107::getUserProvider()->isSocialLoginEnabled())
		{
			$redirectType = 'splash';
		}

		$redirectURL = ($redirectType == 'splash') ? e_HTTP.'membersonly.php' : e_LOGIN;

		$this->redirect($redirectURL);
	}

	
	/**
	 * Store the current URL so that it can retrieved after login.
	 *
	 * @return void
	 */
	private function saveMembersOnlyUrl($forceNoSef = false)
	{
		// remember the url for after-login.
		//$afterlogin = e_COOKIE.'_afterlogin';
		$this->setCookie('_afterlogin', $this->getSelf($forceNoSef), 300);
		//session_set($afterlogin, $url, time() + 300);
	}

	
	/**
	 * Restore the previously saved URL, and redirect the User to it after login.
	 *
	 * @return void
	 */
	private function restoreMembersOnlyUrl()
	{
		$url = $this->getCookie('_afterlogin');
		if(USER && $url)
		{
			//session_set(e_COOKIE.'_afterlogin', FALSE, -1000);
			$this->clearCookie('_afterlogin');
			$this->redirect($url);
		}
	}

	/**
	 * @return void
	 */
	public function redirectPrevious()
	{
		if($this->getPreviousUrl())
		{
			$this->redirect($this->getPreviousUrl());
		}
	}


	/**
	 * @param $url
	 * @param $replace
	 * @param $http_response_code
	 * @param $preventCache
	 * @return void
	 * @param bool $allowOffsite see {@see go()}
	 */
	public function redirect($url, $replace = TRUE, $http_response_code = NULL, $preventCache = true, $allowOffsite = false)
	{
		$this->go($url, $replace, $http_response_code, $preventCache, $allowOffsite);
		exit;
	}

	 /**
     * Determines the correct host and generates the redirection URL if needed.
     *
     * @param array $server  The $_SERVER superglobal containing request data.
     * @param string $prefUrl The preferred site URL from preferences.
     * @return string|bool   The redirection URL if a redirection is required, or false if no redirection is needed.
     */
	public function host(array $server, $prefUrl, $adminDir='')
	{

		// Extract the current domain and port
		list($urlbase, $urlport) = explode(':', $server['HTTP_HOST'] . ':');
		$urlport = $urlport ?: (int) ($server['SERVER_PORT'] ?: 80);

		// Parse the preferred site URL
		$aPrefURL = parse_url($prefUrl);

		if(empty($aPrefURL['host']))
		{
			return false; // Invalid URL structure
		}

		$PrefRoot = $aPrefURL['host'];
		list($PrefSiteBase, $PrefSitePort) = explode(':', $PrefRoot . ':');
		$PrefSitePort = $PrefSitePort ?: (($aPrefURL['scheme'] === 'https') ? 443 : 80);

		$hostMismatch = (strcasecmp($urlbase, $PrefSiteBase) !==0); // -- base domain does not match (case-insensitive)
		$portMismatch = ($urlport !== $PrefSitePort); 	 // -- ports do not match (http <==> https)

		if(($portMismatch || $hostMismatch) && strpos($server['PHP_SELF'], $adminDir) === false)
		{
			// Reconstruct the redirect URL
			$aeSELF = explode('/', $server['PHP_SELF'], 4);
			$aeSELF[0] = $aPrefURL['scheme'] . ':'; // Correct scheme (http/https)
			$aeSELF[1] = ''; // Defensive code: ensure http:// not http:/<garbage>/
			$aeSELF[2] = $PrefRoot; // Correct domain and port if needed

			$location = implode('/', $aeSELF) . ($server['QUERY_STRING'] ? '?' . $server['QUERY_STRING'] : '');

			return filter_var($location, FILTER_SANITIZE_URL);
		}


		return false; // No redirection needed
	}


	
	/**
	 * Redirect to the given URI
	 *
	 * @param string $url or error code number. eg. 404 = Not Found. If left empty SITEURL will be used.
	 * @param boolean $replace - default TRUE
	 * @param integer|null $http_response_code - default NULL
	 * @param boolean $preventCache
	 * @return void
	 * A destination that leaves this site is refused and replaced with SITEURL
	* unless the caller asks for it. Most of what reaches here is a destination
	* the visitor named (a return address, a jump target, a query string), and
	* a redirector that follows one off site is a phishing primitive carrying
	* this site's own domain. Off-site destinations that are the site's own
	* decision rather than the visitor's, such as the marketplace, a banner
	* click-through or an external download mirror, pass $allowOffsite.
	*
	* @param boolean $allowOffsite permit a destination on another host
	 */
	public function go($url='', $replace = TRUE, $http_response_code = NULL, $preventCache = true, $allowOffsite = false)
	{
		if(e107::isCli())
		{
			return null;
		}

		$url = str_replace("&amp;", "&", $url); // cleanup when using e_QUERY in $url;

		if(empty($url))
		{
			$url = SITEURL;
		}

		if($url == 'admin')
		{
			$url = SITEURLBASE. e_ADMIN_ABS;
		}

		// A client deletes tab, LF and CR and trims leading space before it reads
		// the value, so the header has to carry the string that was tested.
		$url = ltrim(str_replace(array("\t", "\n", "\r"), '', $url), "\x00..\x20");

		if(!$allowOffsite && $this->leavesThisSite($url))
		{
			// The refusal is otherwise indistinguishable from a plugin that meant
			// to land on the home page, and go() runs too early in the boot to
			// reach the logging subsystem. Same channel as the host-check kill in
			// e107::set_urls_deferred().
			error_log('e107 redirect: refused the off-site destination '.var_export($url, true)
				.'; a caller that means to leave the site passes $allowOffsite');
			$url = SITEURL;
		}


					
		if(deftrue('e_DEBUG_REDIRECT') && strpos($url, 'sitedown.php') === false)
		{
			$error = debug_backtrace();

			$sent = headers_list();
			$message = "Headers previously sent: ".print_r($sent,true);
			e107::getLog()->addDebug($message);
			print_a($message);

			$message = "URL: ".$url."\nFile: ".$error[1]['file']."\nLine: ".$error[1]['line']."\nClass: ".$error[1]['class']."\nFunction: ".$error[1]['function']."\n\n";
			e107::getLog()->addDebug($message);
			echo "Debug active";
			print_a($message);
			echo "Go to : <a href='".$url."'>".$url."</a>";
			e107::getLog()->toFile('redirect.log',"Redirect Log", true);
			return; 
		}
		
		if(session_id())
		{
			e107::getSession()->end();
		}
		if($preventCache)
		{
			header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		}
		// issue #3179 redirect with response code >= 400 doesn't work. Only response codes below 400.
		if(null === $http_response_code || $http_response_code >= 400)
		{
			header('Location: '.$url, $replace);
		}
		else
		{
			header('Location: '.$url, $replace, $http_response_code);
		}
		
		// Safari endless loop fix.
		header('Content-Length: 0');
		
		// write session if needed
		//if(session_id()) session_write_close();
		
		exit();
	}

	/**
	 * Redirect to a destination that deliberately leaves this site.
	 *
	 * {@see go()} takes the permit as its fifth argument, and go($url, true, null,
	 * true, true) states nothing at the call site: an author who copies it and
	 * drops one argument turns off cache prevention rather than turning on the
	 * permit. Say it in words instead.
	 *
	 * @param string $url
	 * @param boolean $replace
	 * @param int|null $http_response_code
	 * @param boolean $preventCache
	 * @return void
	 */
	public function goOffsite($url = '', $replace = TRUE, $http_response_code = NULL, $preventCache = true)
	{
		$this->go($url, $replace, $http_response_code, $preventCache, true);
	}


	/**
	 * Confirm a redirect destination points somewhere on this site.
	 *
	 * The same-origin half of {@see verifyDestination()}, for the callers that hold
	 * a plain URL rather than a signed token: a visitor-supplied return address is
	 * only ever safe to redirect to once it has been through here.
	 *
	 * @param string $dest
	 * @return string|false the destination unchanged, or false if it leaves this site
	 */
	public function verifyDestinationUrl($dest)
	{
		if(!is_string($dest) || $dest === '')
		{
			return false;
		}

		$probe = $this->normaliseAsUrlParser($dest);

		// A destination the visitor named has no reason to carry a character a URL
		// parser deletes, so refuse rather than silently rewrite: "/\t/evil.example"
		// reads as a rooted path here and as an authority in a browser.
		if($probe !== str_replace('\\', '/', $dest))
		{
			return false;
		}

		// Reject protocol-relative ("//host") targets.
		if(strpos($probe, '//') === 0)
		{
			return false;
		}

		if(preg_match('#^https?://#i', $probe))
		{
			if($this->leavesThisSite($dest))
			{
				return false;
			}
		}
		elseif(strpos($probe, '/') !== 0)
		{
			// Otherwise it must be a site-rooted relative path.
			return false;
		}

		return $dest;
	}

	/**
	 * Read a URL the way a browser does before it parses one.
	 *
	 * The WHATWG basic URL parser deletes every ASCII tab, LF and CR from its
	 * input and trims leading C0 controls and space, all before it looks for a
	 * scheme or an authority. PHP's header() rejects only LF and CR, so a tab or
	 * a space survives into a Location value that the client then reads as
	 * something else: "/\t/evil.example" is a rooted path to a string predicate
	 * and an authority to a browser. Backslashes collapse for the same reason.
	 *
	 * @param string $url
	 * @return string
	 * @see https://url.spec.whatwg.org/#concept-basic-url-parser
	 */
	private function normaliseAsUrlParser($url)
	{
		$probe = str_replace(array("\t", "\n", "\r"), '', (string) $url);
		$probe = ltrim($probe, "\x00..\x20");

		return str_replace('\\', '/', $probe);
	}

	/**
	 * Whether following a redirect destination would leave this site.
	 *
	 * The weaker half of {@see verifyDestinationUrl()}, and deliberately so. That
	 * method answers "may a visitor name this destination", which additionally
	 * requires a relative target to be site-rooted. This one answers only "does
	 * this leave the site", because most of what the tree hands {@see go()} is
	 * relative to the document rather than to the root: core commonly passes a
	 * path relative to the document rather than to the root, and e107::url()
	 * returns the same shape unless asked for the full form. None of it can
	 * leave the site.
	 *
	 * A scheme other than http or https counts as leaving, so `javascript:` and
	 * friends never reach a Location header.
	 *
	 * The host is compared rather than the string prefixed. SITEURLBASE carries
	 * no trailing slash, so a prefix test would accept a third-party host that
	 * merely starts with the site's own. Alongside the served host, the
	 * `trusted_hosts` pref (e107inc/e107#5639) is honoured, so a multi-hostname
	 * install can move a visitor between its own hosts.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function leavesThisSite($url)
	{
		if(!is_string($url) || $url === '')
		{
			return false;
		}

		$probe = $this->normaliseAsUrlParser($url);

		if(preg_match('#^([a-z][a-z0-9+.\-]*):#i', $probe, $scheme))
		{
			if(strcasecmp($scheme[1], 'http') !== 0 && strcasecmp($scheme[1], 'https') !== 0)
			{
				return true;
			}
		}
		elseif(strpos($probe, '//') !== 0)
		{
			return false;
		}

		$host = parse_url($probe, PHP_URL_HOST);

		if(!is_string($host) || $host === '')
		{
			return true;
		}

		// eIPHandler is constructed before set_urls_deferred() defines the constant,
		// so a redirect from that far up the boot has only the pref to go on.
		$siteHost = defined('SITEURLBASE') ? parse_url(SITEURLBASE, PHP_URL_HOST) : '';

		if(is_string($siteHost) && $siteHost !== '' && strcasecmp($host, $siteHost) === 0)
		{
			return false;
		}

		return !e107::getInstance()->isTrustedHost($host);
	}

	/**
	 * If a static subdomain is detected, returns the equivalent non-static domain.
	 * @return string|false
	 */
	public function redirectStaticDomain()
	{
		if(empty($this->staticDomains))
		{
			return false;
		}

		$tmp = explode('.',$this->domain);

		if(!empty($tmp[0]) && strpos($tmp[0], 'static') !== false)
		{
			unset($tmp[0]);
			return str_replace($this->domain.'/', implode('.',$tmp).'/', $this->self);
		}

		return false;

	}


}
