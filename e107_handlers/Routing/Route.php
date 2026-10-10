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
use e107\Http\RequestHandlerInterface;

/**
 * One named route: what it answers to, and how to build the handler that answers it.
 */
final class Route
{
	/** @var string */
	private $name;

	/** @var string */
	private $flag;

	/** @var callable */
	private $factory;

	/**
	 * @param string $name
	 * @param string $flag
	 * @param callable $factory
	 */
	private function __construct($name, $flag, callable $factory)
	{
		$this->name = (string) $name;
		$this->flag = (string) $flag;
		$this->factory = $factory;

		if($this->name === '' || $this->flag === '')
		{
			throw new \InvalidArgumentException('A route needs a name and something to match.');
		}
	}

	/**
	 * A route that answers when the query string is the bare word $flag, as in index.php?logout.
	 *
	 * @param string $name
	 * @param string $flag
	 * @param callable $factory returns the RequestHandlerInterface, called only when the route is taken
	 * @return Route
	 */
	public static function flag($name, $flag, callable $factory)
	{
		return new self($name, $flag, $factory);
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
	 * @return array|null the parameters the route captured, or null when it does not answer
	 */
	public function match(Request $request)
	{
		return $request->hasFlag($this->flag) ? array() : null;
	}

	/**
	 * @return RequestHandlerInterface
	 */
	public function handler()
	{
		$handler = call_user_func($this->factory);

		if(!$handler instanceof RequestHandlerInterface)
		{
			throw new \UnexpectedValueException('The factory for route '.$this->name.' did not return a RequestHandlerInterface.');
		}

		return $handler;
	}
}
