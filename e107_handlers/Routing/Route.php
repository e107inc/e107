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
 * One named route: what it answers to, and the factory that builds the handler answering it.
 */
final class Route
{
	/** @var string */
	private $name;

	/** @var string */
	private $queryWord;

	/** @var callable */
	private $factory;

	/**
	 * @param string $name
	 * @param string $queryWord empty for a route no request reaches
	 * @param callable $factory
	 */
	private function __construct($name, $queryWord, callable $factory)
	{
		$this->name = (string) $name;
		$this->queryWord = (string) $queryWord;
		$this->factory = $factory;

		if($this->name === '')
		{
			throw new \InvalidArgumentException('A route needs a name.');
		}
	}

	/**
	 * A route that answers on any path index.php serves when the query string leads with $word, as in index.php?logout.
	 *
	 * @param string $name area/action, as {@see e107::route()} names pages
	 * @param string $word
	 * @param callable $factory returns the RequestHandlerInterface, called only when the route is taken
	 * @return Route
	 */
	public static function queryWord($name, $word, callable $factory)
	{
		$route = new self($name, $word, $factory);

		if($route->queryWord === '')
		{
			throw new \InvalidArgumentException('A query word route needs its word.');
		}

		return $route;
	}

	/**
	 * A route no request reaches by its address: it answers only where code takes it by name, through {@see RouteTable::named()}.
	 *
	 * @param string $name as {@see e107::route()} names pages
	 * @param callable $factory returns the RequestHandlerInterface, called only when the route is taken
	 * @return Route
	 */
	public static function internal($name, callable $factory)
	{
		return new self($name, '', $factory);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param Request $request
	 * @return RouteMatch|null null when the route does not answer
	 */
	public function match(Request $request)
	{
		return $request->hasLeadingQueryWord($this->queryWord) ? $this->toMatch() : null;
	}

	/**
	 * @internal Only {@see RouteTable::named()} calls it; it changes as the table needs.
	 * @return RouteMatch the route taken whatever the request, with no captured parameters
	 */
	public function toMatch()
	{
		return new RouteMatch($this->name, array(), $this->factory);
	}
}
