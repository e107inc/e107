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
	public function actionIndex($fpref =null) // used for testing.
	{
		$tp = e107::getParser();
		$indexRoute = 'index/index/index';
		
		if (file_exists(e_BASE.'index_include.php'))
		{
			include (e_BASE.'index_include.php');
		}

		$location = e107::getFrontpage();
		
		if($location === false)
		{
			define('e_FRONTPAGE', true);
			$this->_forward('front'); 
			return;
		}
		
		
		$request = $this->getRequest();
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
			define('e_URL_LEGACY', $location);

			eFront::isLegacy('{e_BASE}'.$page);
			e107::canonical('_SITEURL_');

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
