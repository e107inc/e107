<?php

abstract class Deployer
{
	abstract public function start();
	abstract public function stop();

	protected $params;

	public function __construct($params = [])
	{
		$this->params = $params;
	}

	protected static function println($text = '')
	{
		codecept_debug($text);

		//echo("${text}\n");

		//$prefix = debug_backtrace()[1]['function'];
		//echo("[\033[1m${prefix}\033[0m] ${text}\n");
	}

	protected $components = array();

	/**
	 * @param array $components
	 */
	public function setComponents($components)
	{
		$this->components = $components;
	}

	public function unlinkAppFile($relative_path)
	{
		throw new \PHPUnit\Framework\SkippedTestError("Test wants \"$relative_path\" to be deleted from the app, ".
		"but the configured deployer ".get_class($this)." is not capable of doing that.");
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
		throw new BadMethodCallException(get_class($this)."::$method_name is not implemented");
	}
}