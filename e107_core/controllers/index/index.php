<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Front page controller
 *
 * $URL$
 * $Id$
*/
class core_index_index_controller extends eController
{
	/**
	 * Do frontpage checks
	 * Valid formats for frontpage preference value:
	 * - url:Blog/My Blog Title.html (no redirect)
	 * - url:news.php?extend.2 (no redirect)
	 * - route:news/view/item?id=2 (no redirect)
	 * - news.php?extend.2 (no redirect)
	 * - http://mysite.com/news.php?extend.2 (redirect)
	 * - http://mysite.com/Blog/My Blog Title.html (redirect)
	 * - http://NotMysite.com/someurl/ (redirect) - really not sure who'd need that...
	 * @throws eException
	 */
	public function actionIndex()
	{	
		$pref = eFront::app()->getPref();
		$tp = e107::getParser();
		$indexRoute = 'index/index/index';
		
		if (file_exists(e_BASE.'index_include.php'))
		{
			include (e_BASE.'index_include.php');
		}

		$location = '';
		$class_list = explode(',', USERCLASS_LIST);
		
		if (isset($pref['frontpage']['all']) && $pref['frontpage']['all'])
		{ // 0.7 method
			$location = $pref['frontpage']['all'];
		}
		else
		{ // This is the 'new' method - assumes $pref['frontpage'] is an ordered list of rules
			if(vartrue($pref['frontpage']))
			{
				foreach ($pref['frontpage'] as $fk=>$fp)
				{
					if (in_array($fk, $class_list))
					{
						$location = $fp;
					break;
					}
				}
			}
		}
		
		if (!$location)
		{ // Try and use the 'old' method (this bit can go later)
			if (ADMIN)
			{
				$location = $pref['frontpage'][e_UC_ADMIN];
			}
			elseif (USER)
			{ // This is the key bit - what to do for a 'normal' logged in user
				// We have USERCLASS_LIST - comma separated. Also e_CLASS_REGEXP
				$inclass = false;
				foreach ($class_list as $fp_class)
				{
					if (!$inclass && check_class($fp_class['userclass_id']))
					{
						$location = $pref['frontpage'][$fp_class['userclass_id']];
						$inclass = true;
					}
				}
				$location = $location ? $location : $pref['frontpage'][e_UC_MEMBER];
			}
			else
			{
				$location = $pref['frontpage'][e_UC_GUEST];
			}
		}
		
		$location = trim($location);
		$request = $this->getRequest();

		// Defaults to news
		if(!$location) $location = 'url:/news';
		// Former Welcome Message front-page. Should be handled by current theme layout
		elseif($location == 'index.php' || $location == 'url:/' || $location == 'route:/' || $location == '/') 
		{
			define('e_FRONTPAGE', true);
			$this->_forward('front'); 
			return;
		}
		elseif($location[0] === '{')
		{
			$location = $tp->replaceConstants($location, true);
		}
		
		// new url format; if set to 'url:' only it'll resolve current main module (if any)
		if(strpos($location, 'url:') === 0)
		{
			$url = substr($location, 4);
			$request->setPathInfo($url)->setRequestParams(array());
			$router = eFront::instance()->getRouter();
			
			if($router->route($request, true))
			{
				if($request->getRoute() == $indexRoute) 
				{
					throw new eException('Infinite loop detected while dispatching front page.', 2);
				}
				define('e_FRONTPAGE', true);
				$this->_forward($request->getRoute());
				return;
			}
			$this->_forward('system/error/notfound', array('frontPageErorr' => null));
		}
		// route is provided
		elseif(strpos($location, 'route:') === 0)
		{
			list($route, $qstr) = explode('?', substr($location, 6).'?');
			
			if(!$qstr) $qstr = array();
			else parse_str($qstr, $qstr);
			
			$request->setRoute($route);
			$request->setRequestParams($qstr);
			
			if($request->getRoute() == $indexRoute) 
			{
				throw new eException('Infinite loop detected while dispatching front page.', 2);
			}
			define('e_FRONTPAGE', true);
			$this->_forward($request->getRoute(), $qstr);
			
			return;
		}
		// redirect to this address
		elseif(strpos($location, 'http://') === 0 || strpos($location, 'https://') === 0)
		{
			if(e_REQUEST_URL != $location)
			{
				header("Location: {$location}");
				exit;
			}
		}
		// Enter in legacy mod, include the front page
		elseif(strpos($location, '.php') !== false)
		{
			list($page, $qstr) = explode("?", $location."?");
			
			$request->setLegacyPage($page)
				->setLegacyQstring($qstr);
				
			$request->routed = true;
			define('e_FRONTPAGE', true);
			eFront::isLegacy('{e_BASE}'.$page);
			return;
		}
		// Redirect
		else
		{
			$location = SITEURL.$location;
			if(e_REQUEST_URL != $location)
			{
				header("Location: {$location}");
				exit;
			}
		}
		
		// we can't do much
		$this->_forward('system/error/notfound', array('frontPageErorr' => null));
	}

	public function actionFront()
	{
		// we could notify current theme we are in front page controlled by the theme layout only...
		// switch off tablerender
		$this->getResponse()->setParam('render', false);
	}
}
