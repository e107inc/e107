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
	 * @param string $queryWord
	 * @param callable $factory
	 */
	private function __construct($name, $queryWord, callable $factory)
	{
		$this->name = (string) $name;
		$this->queryWord = (string) $queryWord;
		$this->factory = $factory;

		if($this->name === '' || $this->queryWord === '')
		{
			throw new \InvalidArgumentException('A route needs a name and something to match.');
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
		return new self($name, $word, $factory);
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
		return $request->hasLeadingQueryWord($this->queryWord) ? new RouteMatch($this->name, array(), $this->factory) : null;
	}
}
