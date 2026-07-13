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
	 * Assemble a system URL, ready to embed directly in an HTML/XHTML attribute.
	 *
	 * With the default options the result is attribute-safe by construction:
	 * every dynamic segment (SEF path parts and GET values) is rawurlencoded, and
	 * the default parameter separator ({@see eRouter::$_defaultAssembleOptions}
	 * `amp` => `&amp;`) is already HTML-escaped. Do NOT pass the result through
	 * {@see e_parse::toAttribute()} or htmlspecialchars() - that double-encodes the
	 * separator to `&amp;amp;` and corrupts any multi-parameter URL.
	 *
	 * For a non-HTML context (Location header, JSON, plain-text email) pass
	 * `array('amp' => '&')` in $options to get a raw `&` separator instead.
	 *
	 * @param string       $route   e.g. `news/view/item`; {@see eRouter::assemble()} for the route syntax
	 * @param array|string $params  route/GET parameters, or a full DB row (unused keys are dropped)
	 * @param array|string $options {@see eRouter::$_defaultAssembleOptions}
	 * @return string attribute-ready URL
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

