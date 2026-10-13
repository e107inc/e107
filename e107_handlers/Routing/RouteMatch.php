<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Routing;

use e107\Http\RequestHandlerInterface;

/**
 * The route a request was matched to, with the parameters the match captured.
 */
final class RouteMatch
{
	/** @var Route */
	private $route;

	/** @var array */
	private $parameters;

	/**
	 * @param Route $route
	 * @param array $parameters
	 */
	public function __construct(Route $route, array $parameters)
	{
		$this->route = $route;
		$this->parameters = $parameters;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->route->getName();
	}

	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @return RequestHandlerInterface
	 */
	public function handler()
	{
		return $this->route->handler();
	}
}
