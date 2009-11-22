<?php 
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     Copyright (C) 2008-2009 e107 Inc
 |     http://e107.org
 |
 |
 |     Released under the terms and conditions of the
 |     GNU General Public License (http://gnu.org).
 |
 |     $Source: /cvs_backup/e107_0.8/e107_handlers/redirection_class.php,v $
 |     $Revision: 1.8 $
 |     $Date: 2009-11-22 14:10:07 $
 |     $Author: e107coders $
 +----------------------------------------------------------------------------+
 */

/**
 * Redirection class
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author Cameron
 * @copyright Copyright (C) 2009, e107 Inc.
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
	 * @return 
	 */
	function setPreviousUrl()
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
		
		$self = (e_QUERY) ? e_SELF."?".e_QUERY : e_SELF;
		
		session_set(e_COOKIE.'_previousUrl',$self ,(time()+300));	
	}

	
	/**
	 * Return the URL the admin was on, prior to being logged-out. 
	 * @return 
	 */
	public function getPreviousUrl()
	{		
		return $this->getCookie('previousUrl');
	}
		
	
	private function getCookie($name) //TODO move to e107_class or a new user l class. 
	{	
		$cookiename = e_COOKIE."_".$name;
		
		if(vartrue($_SESSION[$cookiename]))
		{
			return $_SESSION[$cookiename];
		}
		elseif(vartrue($_COOKIE[$cookiename]))
		{
			return $_COOKIE[$cookiename];	
		}

		return FALSE;	
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
	private function saveMembersOnlyUrl()
	{
		// remember the url for after-login.
		$afterlogin = e_COOKIE.'_afterlogin';
		$url = (e_QUERY ? e_SELF.'?'.e_QUERY : e_SELF);
		session_set($afterlogin, $url, time() + 300);
	}

	
	/**
	 * Restore the previously saved URL, and redirect the User to it after login.
	 *
	 * @return void
	 */
	private function restoreMembersOnlyUrl()
	{
		if(USER && ($_SESSION[e_COOKIE.'_afterlogin'] || $_COOKIE[e_COOKIE.'_afterlogin']))
		{
			$url = ($_SESSION[e_COOKIE.'_afterlogin']) ? $_SESSION[e_COOKIE.'_afterlogin'] : $_COOKIE[e_COOKIE.'_afterlogin'];
			session_set(e_COOKIE.'_afterlogin', FALSE, -1000);
			$this->redirect($url);
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
