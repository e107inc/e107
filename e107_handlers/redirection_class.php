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
	 * List of pages to not check against e_PAGE
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
	 * Manage Member-Only Mode.
	 *
	 * @return void
	 */
	function __construct()
	{
		$this->self_exceptions = array(SITEURL.e_SIGNUP, SITEURL.'index.php', SITEURL.'fpw.php', SITEURL.e_LOGIN, SITEURL.'membersonly.php');
		$this->page_exceptions = array('e_ajax.php', 'e_js.php', 'e_jslib.php', 'sitedown.php');
		$this->query_exceptions = array('logout');
	}
	
	/**
	 * Store the current URL in a cookie for 5 minutes so we can return to it after being logged out. 
	 * @param string $url if empty self url will be used
	 * @param boolean $forceNoSef if false REQUEST_URI will be used (mod_rewrite support)
	 * @return redirection
	 */
	function setPreviousUrl($url = null, $forceNoSef = false, $forceCookie = false)
	{
		if(!$url)
		{
			if(in_array(e_SELF, $this->self_exceptions))
			{
				return;
			}
			if(in_array(e_PAGE, $this->page_exceptions))
			{
				return;
			}
			if(in_array(e_QUERY, $this->query_exceptions))
			{
				return;
			}
			$url = $this->getSelf($forceNoSef);
		}
		
		$this->setCookie('_previousUrl', $url, 300, $forceCookie);
		//session_set(e_COOKIE.'_previousUrl',$self ,(time()+300));	
		
		return $this;
	}
	
	public function getSelf($forceNoSef = false)
	{
		if($forceNoSef)
		{
			$url = (e_QUERY) ? e_SELF."?".e_QUERY : e_SELF;
		}
		else
		{
			// TODO - e107::requestUri() - sanitize, add support for various HTTP servers
			$url = SITEURLBASE.strip_tags($_SERVER['REQUEST_URI']);
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
			if((integer) $session->get($name.'_expire') < time())
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
			if(null !== $expire) $session->set($name.'_expire', time() + (integer) $expire); 
			$session->set($name, $value);
		}
		else
		{
			cookie($cookiename, $value, time() + (integer) $expire, e_HTTP, e107::getLanguage()->getCookieDomain());
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
		if(strpos(e_SELF, 'admin.php') !== FALSE || strpos(e_SELF, 'sitedown.php') !== FALSE)
		{
			return;
		}
		
		if(e107::getPref('maintainance_flag'))
		{
			// if not admin
			if(!ADMIN 
			// or if not mainadmin - ie e_UC_MAINADMIN
			|| (e_UC_MAINADMIN == e107::getPref('maintainance_flag') && !getperms('0')))
			{
				// 307 Temporary Redirect
				$this->redirect(SITEURL.'sitedown.php', TRUE, 307);
			}
		}
		else
		{
			return;
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
		if(strpos(e_PAGE, 'admin') !== FALSE)
		{
			return;
		}
		if(in_array(e_SELF, $this->self_exceptions))
		{
			return;
		}
		if(in_array(e_PAGE, $this->page_exceptions))
		{
			return;
		}
		foreach (e107::getPref('membersonly_exceptions') as $val)
		{
			$srch = trim($val);
			if(strpos(e_SELF, $srch) !== FALSE)
			{
				return;
			}
		}
		
		$this->saveMembersOnlyUrl();
		$this->redirect(e_HTTP.'membersonly.php');
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
	
	public function redirectPrevious()
	{
		if($this->getPreviousUrl())
		{
			$this->redirect($this->getPreviousUrl());
		}
	}

	
	/**
	 * Redirect to the given URI
	 *
	 * @param string $url
	 * @param boolean $replace - default TRUE
	 * @param integer|null $http_response_code - default NULL
	 * @return void
	 */
	public function redirect($url, $replace = TRUE, $http_response_code = NULL)
	{
		if(NULL == $http_response_code)
		{
			header('Location: '.$url, $replace);
		}
		else
		{
			header('Location: '.$url, $replace, $http_response_code);
		}
		
		// Safari endless loop fix.
		header('Content-Length: 0');
		exit();
	}
}
