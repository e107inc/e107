<?php

namespace Test;

/**
 * A subprocess with class2.php booted in CLI mode, for tests that need a process of their own.
 *
 * Several tests in this suite build this by hand. They can drop their copy and
 * name this trait instead; the signature carries what each of them needs.
 *
 * Keep this in PHP 5.6 syntax: it is part of the shipping-adjacent test tree
 * that the downgrade pipeline walks.
 */
trait BootedCli
{
	/**
	 * Runs $php in a subprocess that has booted class2.php in CLI mode.
	 *
	 * @param string $php
	 * @param string $ini extra php command-line arguments, e.g. '-d memory_limit=64M'
	 * @param array $e107 what $_E107 holds when class2.php boots
	 * @param int $timeout seconds
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function runInBootedCli($php, $ini = '', $e107 = array('cli' => true), $timeout = 60)
	{
		$boot = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$boot .= "\$_E107 = ".var_export($e107, true)."; ";
		$boot .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";

		$output = array();
		$status = 0;
		exec(sprintf('timeout %d php %s -r %s 2>&1', $timeout, $ini, escapeshellarg($boot.$php)), $output, $status);

		if($status === 124)
		{
			$head = array_slice($output, 0, 20);

			self::fail(sprintf("the subprocess wedged, so nothing was measured. The first %d of its %d line(s):\n%s",
				count($head), count($output), implode("\n", $head)));
		}

		return array($output, $status);
	}
}
