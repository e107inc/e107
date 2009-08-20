<?php
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     �Steve Dunstan 2001-2002
 |     http://e107.org
 |     jalist@e107.org
 |
 |     Released under the terms and conditions of the
 |     GNU General Public License (http://gnu.org).
 |
 |     $Source: /cvs_backup/e107_0.8/e107_handlers/redirection_class.php,v $
 |     $Revision: 1.1 $
 |     $Date: 2009-08-20 13:54:42 $
 |     $Author: e107coders $
 +----------------------------------------------------------------------------+
 */

class redirection
{
		
		var $self_exceptions = array();
		
		var $page_exceptions = array();
		/**
		 *	Manage Member-Only Mode.
		 */
		
		function __construct()
		{
			$this->self_exceptions = array(SITEURL.e_SIGNUP, SITEURL.'index.php', SITEURL.'fpw.php', SITEURL.e_LOGIN, SITEURL.'membersonly.php');
			$this->page_exceptions = array('e_ajax.php', 'e_js.php', 'e_jslib.php', 'sitedown.php');
		} 
		
		public function checkMaintenance()
		{
			if(ADMIN == TRUE)
			{
				return;
			}
			
			if (!e107::getPref('maintainance_flag') || (strpos(e_SELF, 'admin.php') !== FALSE) || (strpos(e_SELF, 'sitedown.php') !== FALSE))
			{
					return;
			} 
			$this->redirect(SITEURL.'sitedown.php');
		} 
		/** check if user is logged in.
		 *
		 */
		
		public function checkMembersOnly()
		{
			
			if(!e107::getPref('membersonly_enabled'))
			{
				return;
			}
			
			if (USER && !e_AJAX_REQUEST)
			{
					$this->restoreMembersOnlyUrl();
					return;
			} 
			if (e_AJAX_REQUEST)
			{
					return;
			} 
			if (strpos(e_PAGE, 'admin') !== FALSE)
			{
					return;
			} 
			if (in_array(e_SELF, $this->self_exceptions))
			{
					return;
			} 
			if (in_array(e_PAGE, $this->page_exceptions))
			{
					return;
			} 
			foreach (e107::getPref('membersonly_exceptions') as $val)
			{
					$srch = trim($val);
					if (strpos(e_SELF, $srch) !== FALSE)
					{
							return;
					} 
			} 
			
			$this->saveMembersOnlyUrl();
			$this->redirect(e_HTTP.'membersonly.php');
		} 
		
		/** Store the current URL so that it can retrieved after login. 
		 * @param
		 * @return
		 */
		
		private function saveMembersOnlyUrl()
		{
				// remember the url for after-login. 
				$afterlogin = e_COOKIE.'_afterlogin';
				$url = (e_QUERY ? e_SELF.'?'.e_QUERY : e_SELF);
				session_set($afterlogin, $url, time() + 300);
		} 
		
		
		/** Restore the previously saved URL, and redirect the User to it after login. 
		 * @param
		 * @return
		 */
		
		private function restoreMembersOnlyUrl()
		{
				if (USER && ($_SESSION[e_COOKIE.'_afterlogin'] || $_COOKIE[e_COOKIE.'_afterlogin']))
				{
						$url = ($_SESSION[e_COOKIE.'_afterlogin']) ? $_SESSION[e_COOKIE.'_afterlogin'] : $_COOKIE[e_COOKIE.'_afterlogin'];
						session_set(e_COOKIE.'_afterlogin', FALSE, -1000);
						$this->redirect($url);
				} 
		} 
		
		function redirect($url)
		{
				header('Location: '.$url);
				exit();
		} 
}
?>
