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
		$this->self_exceptions = array(e_SIGNUP, SITEURL.'fpw.php', e_LOGIN, SITEURL.'membersonly.php');
		$this->page_exceptions = array('e_ajax.php', 'e_js.php', 'e_jslib.php', 'sitedown.php',e_LOGIN, 'secimg.php');
		$this->query_exceptions = array('logout');

		// Remove from self_exceptions:  SITEURL, SITEURL.'index.php', // allows a custom frontpage to be viewed while logged out and membersonly active.
	}



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
			// e_SELF, e_PAGE and e_QUERY not set early enough when in e_SINGLE_ENTRY mod
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
		if(strpos(defset('e_SELF'), 'admin.php') !== FALSE || strpos(defset('e_SELF'), 'sitedown.php') !== FALSE)
		{
			return;
		}
		
		if(deftrue('NO_MAINTENANCE')) // per-page disable option. 
		{
			return;	
		}
		
		if(e107::getPref('maintainance_flag') && defset('e_PAGE') != 'secure_img_render.php')
		{
			// if not admin
			
			$allowed = e107::getPref('maintainance_flag'); 
			
	//		if(!ADMIN 
			// or if not mainadmin - ie e_UC_MAINADMIN
	//		|| (e_UC_MAINADMIN == e107::getPref('maintainance_flag') && !getperms('0')))
			
			if(!check_class($allowed)  && !getperms('0'))
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
		
		/*
		echo "e_SELF=".e_SELF;
		echo "<br />e_PAGE=".e_PAGE;
		print_a( $this->self_exceptions);
		print_a($this->page_exceptions);
		*/
		
		$this->saveMembersOnlyUrl();

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


	public function redirect($url, $replace = TRUE, $http_response_code = NULL, $preventCache = true)
	{
		$this->go($url, $replace, $http_response_code, $preventCache);
		exit; 	
	}

	
	/**
	 * Redirect to the given URI
	 *
	 * @param string $url or error code number. eg. 404 = Not Found. If left empty SITEURL will be used.
	 * @param boolean $replace - default TRUE
	 * @param integer|null $http_response_code - default NULL
	 * @param boolean $preventCache
	 * @return void
	 */
	public function go($url='', $replace = TRUE, $http_response_code = NULL, $preventCache = true)
	{
		$url = str_replace("&amp;", "&", $url); // cleanup when using e_QUERY in $url;

		if(empty($url))
		{
			$url = SITEURL;
		}

		if($url == 'admin')
		{
			$url = SITEURLBASE. e_ADMIN_ABS;
		}


					
		if(defset('e_DEBUG') === 'redirect')
		{
			$error = debug_backtrace();

			$message = "URL: ".$url."\nFile: ".$error[1]['file']."\nLine: ".$error[1]['line']."\nClass: ".$error[1]['class']."\nFunction: ".$error[1]['function']."\n\n";
			e107::getLog()->addDebug($message, true);
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
			header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true);
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT', true); 
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
}
