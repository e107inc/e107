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

use e107\Http\Request;

/**
 * The routes index.php consults before handing a request to the legacy front controller.
 */
final class RouteTable
{
	/** @var Route[] */
	private $routes = array();

	/**
	 * @param Route[] $routes in the order they are tried
	 */
	public function __construct(array $routes)
	{
		foreach($routes as $route)
		{
			if(!$route instanceof Route)
			{
				throw new \InvalidArgumentException('A route table holds only Route objects.');
			}

			if(isset($this->routes[$route->getName()]))
			{
				throw new \InvalidArgumentException('Two routes are named '.$route->getName().'.');
			}

			$this->routes[$route->getName()] = $route;
		}
	}

	/**
	 * @param Request $request
	 * @return RouteMatch|null null when no route answers, so the request belongs to the legacy path
	 */
	public function match(Request $request)
	{
		foreach($this->routes as $route)
		{
			$parameters = $route->match($request);

			if($parameters !== null)
			{
				return new RouteMatch($route, $parameters);
			}
		}

		return null;
	}
}
