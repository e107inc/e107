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

	public function writeAppFile($relative_path, $contents)
	{
		throw new \PHPUnit\Framework\SkippedTestError("Test wants to write \"$relative_path\" into the app, ".
		"but the configured deployer ".get_class($this)." is not capable of doing that.");
	}

	/**
	 * Recursively remove paths (files or whole directories) from the app.
	 * Absent paths are not an error.
	 *
	 * Takes a list instead of one path per call because a deploying deployer
	 * pays a network round trip per invocation.
	 *
	 * A deployer without an app of its own has nothing to sweep, so unlike
	 * unlinkAppFile() this is not an error: housekeeping must never be the
	 * reason a suite stops.
	 *
	 * @param string[] $relative_paths paths relative to the app root
	 * @return void
	 */
	public function removeAppPaths(array $relative_paths)
	{
		self::println(get_class($this)." cannot remove app paths; nothing swept");
	}

	/**
	 * These paths reach an `rm -rf`, so containment is not optional even
	 * though every caller so far passes a hard-coded name.
	 *
	 * @param string $relative_path
	 * @return void
	 */
	protected static function assertPathInsideApp($relative_path)
	{
		if ($relative_path === '' || $relative_path[0] === '/'
			|| preg_match('#(^|/)\.\.(/|$)#', $relative_path))
		{
			throw new RuntimeException("Refusing to remove \"$relative_path\": not a path inside the app root");
		}
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