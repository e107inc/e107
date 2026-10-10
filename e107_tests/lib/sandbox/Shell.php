<?php

namespace Sandbox;

/**
 * Runs the mount and file commands the sandboxes are made of.
 */
class Shell
{
	/**
	 * @param string $script run by sh -c
	 * @return string[] its output lines
	 */
	public function run($script)
	{
		exec('sh -c '.escapeshellarg($script).' 2>&1', $output, $status);
		if ($status !== 0)
		{
			throw new \RuntimeException("sandbox command failed ($status): $script\n".implode("\n", $output));
		}

		return $output;
	}
}
