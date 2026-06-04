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

	/**
	 * Default lifetime (in seconds) for a captured post-login destination.
	 */
	const LOGIN_DEST_TTL = 1800;

	/**
	 * Name of the cookie carrying the signed post-login destination token.
	 */
	const LOGIN_DEST_COOKIE = 'e107_logindest';

	/**
	 * Name of the form field carrying the signed post-login destination token.
	 */
	const LOGIN_DEST_FIELD = '__logindest';

	/**
	 * Static-asset extensions that must never be captured as a return destination.
	 * A missing thumbnail or source-map routes through index.php, but bouncing a
	 * user to one of those after login/logout is the bug behind issue #5218.
	 *
	 * @var array
	 */
	protected $asset_extensions = array(
		'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'svgz', 'ico', 'bmp', 'avif',
		'css', 'js', 'mjs', 'map',
		'woff', 'woff2', 'ttf', 'eot', 'otf',
		'mp4', 'webm', 'ogg', 'ogv', 'mp3', 'wav', 'm4a',
		'pdf', 'zip', 'gz', 'tar',
	);

	/**
	 * Per-request cache of signed destination tokens, so repeated renders (e.g. two
	 * login forms on one page) emit the same value instead of minting a fresh JWT
	 * on every call.
	 *
	 * @var array
	 */
	protected $destination_token_cache = array();


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
		$this->page_exceptions = array('e_ajax.php', 'e_js.php', 'e_jslib.php', 'sitedown.php',e_LOGIN, 'secimg.php');
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
			// Only remember a real page navigation, never an asset / AJAX / login-family
			// request. This is also the issue #5218 guard for the auto-capture path:
			// a missing asset routing through index.php must not become the return URL.
			if(!$this->isCapturable())
			{
				return $this;
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
	 * @param int $expire expire after value in seconds, null (default) - ignore
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
		// Also capture through the unified signed-destination path, so a members-only
		// site returns the visitor via the same mechanism as every other login seam.
		$this->setLoginDestination();

		$redirectType = e107::getPref('membersonly_redirect');

		$redirectURL = ($redirectType == 'splash') ? 'membersonly.php' : 'login.php';

		$this->redirect(e_HTTP.$redirectURL);
	}

	
	/**
	 * Store the current URL so that it can retrieved after login.
	 *
	 * @return void
	 */
	private function saveMembersOnlyUrl($forceNoSef = false)
	{
		// Never remember an asset / AJAX request as the after-login target (issue #5218).
		if(!$this->isCapturable())
		{
			return;
		}

		// remember the url for after-login.
		$this->setCookie('_afterlogin', $this->getSelf($forceNoSef), 300);
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
	 * Decide whether the current (or a given) request is a sensible page to send a
	 * user back to after they log in.
	 *
	 * Only a normal GET page view qualifies: not a POST/HEAD target, not an AJAX
	 * call, not a static asset (by extension), and not one of the login / signup /
	 * fpw / membersonly / logout URLs. The asset check is what stops a missing
	 * thumbnail or source-map (which still routes through index.php) from being
	 * remembered as a destination - the root cause of issue #5218.
	 *
	 * @param string|null $url defaults to the current request URI
	 * @return bool
	 */
	public function isCapturable($url = null)
	{
		// Only ever remember a normal GET page view.
		if(isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')
		{
			return false;
		}

		// Never remember a background / AJAX request.
		if(deftrue('e_AJAX_REQUEST'))
		{
			return false;
		}

		// Only ever remember a top-level document navigation. Browsers tag every
		// request with its destination via the Fetch Metadata header
		// Sec-Fetch-Dest: a top-level navigation is 'document', whereas an <iframe>
		// sub-request is 'iframe', a fetch()/XHR is 'empty', an image is 'image',
		// and so on. Anything that is not a top-level document - most importantly
		// the menu manager's iframe body, whose src is an admin-perms-gated URL -
		// must never become the post-login return destination, or the user is
		// dumped into the bare embedded view with no way to navigate. The header is
		// set by the browser and cannot be spoofed by page script; clients that
		// omit it (older browsers) fall through to the marker check below.
		if(isset($_SERVER['HTTP_SEC_FETCH_DEST'])
			&& strtolower($_SERVER['HTTP_SEC_FETCH_DEST']) !== 'document')
		{
			return false;
		}

		if(null === $url)
		{
			// e_REQUEST_URI is reliable even in e_SINGLE_ENTRY mode, where e_SELF /
			// e_PAGE / e_QUERY are not set early enough (see setPreviousUrl() note).
			$url = $this->getSelf(false);
		}

		if(!is_string($url) || $url === '')
		{
			return false;
		}

		// Login / signup / fpw / membersonly / logout are not landing pages.
		if(in_array($url, $this->self_exceptions))
		{
			return false;
		}
		if(defset('e_SELF') && in_array(e_SELF, $this->self_exceptions))
		{
			return false;
		}
		if(defset('e_PAGE') && in_array(e_PAGE, $this->page_exceptions))
		{
			return false;
		}
		if(isset($_SERVER['QUERY_STRING']) && in_array($_SERVER['QUERY_STRING'], $this->query_exceptions))
		{
			return false;
		}

		// Reject static assets by extension (issue #5218).
		$path = parse_url($url, PHP_URL_PATH);
		if(is_string($path) && $path !== '')
		{
			$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			if($ext !== '' && in_array($ext, $this->asset_extensions))
			{
				return false;
			}
		}

		// Embedded / dialog views are not navigable landing pages. e107 switches a
		// page into iframe or modal rendering via these request markers (the menu
		// manager's ?configure=, the shared ?iframe=1, and cpage / image dialogs'
		// ?mode=dialog / ?action=dialog - see e107_admin/boot.php, menus.php,
		// cpage.php), but each page only recognises the marker AFTER its getperms()
		// gate has already funnelled the unauthenticated sub-request through
		// redirection::go('admin'). Recognise the markers here - off the URL itself,
		// so it also covers clients that send no Fetch Metadata - so an iframe or
		// modal sub-request can never overwrite the real page the user was viewing.
		$query = parse_url($url, PHP_URL_QUERY);
		if(is_string($query) && $query !== '')
		{
			parse_str($query, $params);
			if(!empty($params['iframe'])
				|| isset($params['configure'])
				|| (isset($params['mode']) && $params['mode'] === 'dialog')
				|| (isset($params['action']) && $params['action'] === 'dialog'))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Sign the current (or a given) destination URL into a stateless token.
	 *
	 * The token is a JWT signed with the site secret (see {@see e_jwt}), so the
	 * destination is server-certified: a visitor cannot forge or alter it. That is
	 * what makes it safe to carry in a hidden form field or a cookie without
	 * opening a redirect-injection hole. Returns '' when the request is not a
	 * capturable target (see self::isCapturable()).
	 *
	 * @param string|null $url defaults to the current request URI (relative, query preserved)
	 * @param int $ttl token lifetime in seconds
	 * @return string signed token, or '' when nothing should be captured
	 */
	public function getLoginDestinationToken($url = null, $ttl = self::LOGIN_DEST_TTL)
	{
		if(null === $url)
		{
			$url = $this->getSelf(false); // e_REQUEST_URI - relative, SEF / single-entry safe
		}

		$cacheKey = $url . '|' . (int) $ttl;
		if(isset($this->destination_token_cache[$cacheKey]))
		{
			return $this->destination_token_cache[$cacheKey];
		}

		if(!$this->isCapturable($url))
		{
			$this->destination_token_cache[$cacheKey] = '';
			return '';
		}

		$token = e107::getJWT()->encode(array('dest' => $url), (int) $ttl);
		$this->destination_token_cache[$cacheKey] = $token;

		return $token;
	}

	/**
	 * Decode a destination token and confirm it points somewhere on this site.
	 *
	 * Signature, issuer and expiry are verified by {@see e_jwt}. On top of that we
	 * enforce a same-origin / site-rooted target as defence-in-depth, so the
	 * redirect can never be turned into an off-site (open-redirect) jump even if a
	 * signed token somehow carried one.
	 *
	 * @param string $token
	 * @return string|false the verified destination URL, or false
	 */
	public function verifyDestination($token)
	{
		if(!is_string($token) || $token === '')
		{
			return false;
		}

		$payload = e107::getJWT()->decode($token);

		if(empty($payload['dest']) || !is_string($payload['dest']))
		{
			return false;
		}

		$dest = $payload['dest'];

		// Collapse backslashes so "/\evil" or "\\evil" cannot smuggle an off-site host.
		$probe = str_replace('\\', '/', $dest);

		// Reject protocol-relative ("//host") targets.
		if(strpos($probe, '//') === 0)
		{
			return false;
		}

		if(preg_match('#^https?://#i', $probe))
		{
			// An absolute URL must point at this site or one of its trusted hosts.
			// The literal SITEURL match covers the common case; the host check
			// additionally honours the `trusted_hosts` pref (e107inc/e107#5639),
			// so a multi-hostname install can return a visitor to whichever of
			// its own hosts they came in on, but never to a third-party host.
			$host = parse_url($probe, PHP_URL_HOST);
			$onSite = (strpos($probe, SITEURLBASE) === 0 || strpos($probe, SITEURL) === 0);
			if(!$onSite && (!is_string($host) || $host === '' || !e107::getInstance()->isTrustedHost($host)))
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
	 * Capture the current (or a given) URL as the page to return the user to after
	 * they log in. Stateless: stored as a signed token in a cookie, so guests never
	 * create a server-side session row. No-op when the request is not capturable.
	 *
	 * @param string|null $url defaults to the current request URI
	 * @param int $ttl cookie / token lifetime in seconds
	 * @return redirection
	 */
	public function setLoginDestination($url = null, $ttl = self::LOGIN_DEST_TTL)
	{
		$token = $this->getLoginDestinationToken($url, $ttl);

		if($token !== '')
		{
			$this->writeDestinationCookie($token, time() + (int) $ttl);
			$_COOKIE[self::LOGIN_DEST_COOKIE] = $token;
		}

		return $this;
	}

	/**
	 * Return the verified post-login destination, or false.
	 *
	 * Reads the signed token from the submitted form first (so it still works with
	 * cookies disabled), then the cookie. The result is always same-origin (see
	 * self::verifyDestination()).
	 *
	 * @return string|false
	 */
	public function getLoginDestination()
	{
		$token = '';

		if(isset($_POST[self::LOGIN_DEST_FIELD]) && is_string($_POST[self::LOGIN_DEST_FIELD]))
		{
			$token = $_POST[self::LOGIN_DEST_FIELD];
		}
		elseif(isset($_COOKIE[self::LOGIN_DEST_COOKIE]) && is_string($_COOKIE[self::LOGIN_DEST_COOKIE]))
		{
			$token = $_COOKIE[self::LOGIN_DEST_COOKIE];
		}

		if($token === '')
		{
			return false;
		}

		return $this->verifyDestination($token);
	}

	/**
	 * Raw signed destination token currently stored in the cookie, or '' if there
	 * is none or it no longer verifies. Used to re-emit the destination as a hidden
	 * form field so it survives the login POST even if the cookie later expires.
	 *
	 * @return string
	 */
	public function getStoredDestinationToken()
	{
		if(isset($_COOKIE[self::LOGIN_DEST_COOKIE])
			&& is_string($_COOKIE[self::LOGIN_DEST_COOKIE])
			&& $this->verifyDestination($_COOKIE[self::LOGIN_DEST_COOKIE]) !== false)
		{
			return $_COOKIE[self::LOGIN_DEST_COOKIE];
		}

		return '';
	}

	/**
	 * Forget any stored post-login destination (called once it has been consumed).
	 *
	 * @return redirection
	 */
	public function clearLoginDestination()
	{
		$this->writeDestinationCookie('', time() - 3600);
		unset($_COOKIE[self::LOGIN_DEST_COOKIE]);

		return $this;
	}

	/**
	 * Write the destination cookie, aligned with the session cookie (path / domain /
	 * secure) and hardened with HttpOnly + SameSite=Lax, mirroring
	 * {@see CSRFCookieHandler::setCookieToken()}. The PHP-version handling for the
	 * SameSite attribute lives in {@see eShims::setcookie()}.
	 *
	 * @param string $value token value ('' to delete)
	 * @param int $expires absolute expiry timestamp
	 * @return void
	 */
	private function writeDestinationCookie($value, $expires)
	{
		$opt = e107::getSession()->getOptions();
		$path = !empty($opt['path']) ? $opt['path'] : '/';
		$domain = !empty($opt['domain']) ? $opt['domain'] : '';
		$secure = !empty($opt['secure']);

		eShims::setcookie(self::LOGIN_DEST_COOKIE, $value, array(
			'expires'  => $expires,
			'path'     => $path,
			'domain'   => $domain,
			'secure'   => $secure,
			'httponly' => true,
			'samesite' => 'Lax',
		));
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
	 */
	public function redirect($url, $replace = TRUE, $http_response_code = NULL, $preventCache = true)
	{
		$this->go($url, $replace, $http_response_code, $preventCache);
		exit; 	
	}

	 /**
     * Determines the correct host and generates the redirection URL if needed.
     *
     * @param array $server  The $_SERVER superglobal containing request data.
     * @param string $prefUrl The preferred site URL from preferences.
     * @return string|bool   The redirection URL if a redirection is required, or false if no redirection is needed.
     */
	public function host(array $server, string $prefUrl, string $adminDir='')
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
	 * @param int|null $http_response_code - default NULL
	 * @param boolean $preventCache
	 * @return void
	 */
	public function go($url='', $replace = TRUE, $http_response_code = NULL, $preventCache = true)
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
			// Every admin page bounces an unauthorised visitor to the admin login
			// through this branch (e107::redirect('admin') in the page's getperms()
			// gate). Capture the page they were on first, so a successful admin login
			// can return them to it instead of always landing on the dashboard
			// (consumed in e107_admin/auth.php). setLoginDestination() self-guards via
			// isCapturable(); skip it for users who are already admins, e.g. the
			// post-login go('admin') to the dashboard.
			if(!e107::getUser()->isAdmin())
			{
				$this->setLoginDestination();
			}
			$url = SITEURLBASE. e_ADMIN_ABS;
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
