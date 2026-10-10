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
	/** @var string */
	private $name;

	/** @var array */
	private $parameters;

	/** @var callable */
	private $factory;

	/**
	 * @param string $name
	 * @param array $parameters name => value
	 * @param callable $factory returns the RequestHandlerInterface
	 */
	public function __construct($name, array $parameters, callable $factory)
	{
		$this->name = (string) $name;
		$this->parameters = $parameters;
		$this->factory = $factory;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return array name => value
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @return RequestHandlerInterface a new one on every call
	 */
	public function createHandler()
	{
		$handler = call_user_func($this->factory);

		if(!$handler instanceof RequestHandlerInterface)
		{
			throw new \UnexpectedValueException('The factory for route '.$this->name.' did not return a RequestHandlerInterface.');
		}

		return $handler;
	}
}
