<?php

abstract class Deployer
{
	abstract public function start();
	abstract public function stop();

	protected $components = array();

	/**
	 * @param array $components
	 */
	public function setComponents($components)
	{
		$this->components = $components;
	}

	/**
	 * Methods not implemented
	 *
	 * @param $method_name
	 * @param $arguments
	 * @return null
	 */
	public function __call($method_name, $arguments)
	{
		return null;
	}
}