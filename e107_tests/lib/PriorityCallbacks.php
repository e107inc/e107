<?php
/**
 * Execute callbacks before Codeception does
 */

class PriorityCallbacks
{
	/** @var array */
	private $shutdown_functions = [];

	private function __construct()
	{
		register_shutdown_function([$this, 'call_shutdown_functions']);
	}

	public static function instance()
	{
		static $instance = null;
		if (!$instance instanceof self)
		{
			$instance = new static();
		}
		return $instance;
	}

	public function call_shutdown_functions()
	{
		foreach ($this->shutdown_functions as $shutdown_function)
		{
			call_user_func($shutdown_function);
		}
	}

	public function register_shutdown_function($callable)
	{
		$this->shutdown_functions[] = $callable;
	}

	private function __clone() {}

	private function __sleep() {}

	private function __wakeup() {}
}

PriorityCallbacks::instance();