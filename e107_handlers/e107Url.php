<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL Handler
 *
 * $URL$
 * $Id$
*/


if (!defined('e107_INIT')) { exit; }


/**
 *
 */
class eUrl
{
	protected $_front;
	
	public function __construct()
	{
		$front = eFront::instance();
		if(null === $front->getRequest())
		{
			// init
			$request = new eRequest();
			$front->setRequest($request);
			
			$dispatcher = new eDispatcher();
			$front->setDispatcher($dispatcher);
			
			$router = new eRouter();
			$front->setRouter($router);
			
			$response = new eResponse();
			$front->setResponse($response);
			
		}
		$this->_front = $front;
	}

	/**
	 * @param $route
	 * @param $params
	 * @param $options
	 * @return string
	 */
	public function create($route, $params = array(), $options = array())
	{
		return $this->router()->assemble($route, $params, $options);
	}

	/**
	 * @param $route
	 * @param $params
	 * @param $options
	 * @return string
	 */
	public function sc($route, $params = array(), $options = array())
	{
		return $this->router()->assembleSc($route, $params, $options);
	}
	
	/**
	 * @return eRouter
	 */
	public function router()
	{
		return $this->_front->getRouter();
	}
	
	/**
	 * @return eDispatcher
	 */
	public function dispatcher()
	{
		return $this->_front->getDispatcher();
	}
	
	/**
	 * @return eFront
	 */
	public function front()
	{
		return $this->_front;
	}
	
	/**
	 * @return eResponse
	 */
	public function response()
	{
		return $this->_front->getResponse();
	}
	
	/**
	 * @return eRequest
	 */
	public function request()
	{
		return $this->_front->getRequest();
	}
}

