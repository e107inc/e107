<?php

namespace Sandbox;

/**
 * A process a run starts beside its workers, in a session of its own so the hangup that ends an interactive run does not end it too; it watches for its runner instead.
 *
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class Loop
{
	/**
	 * @param string $command
	 * @param string $log where its output goes
	 * @return resource
	 */
	public static function start($command, $log)
	{
		return proc_open('exec setsid '.$command, array(array('file', '/dev/null', 'r'), array('file', $log, 'a'), array('file', $log, 'a')), $pipes);
	}

	/**
	 * Whether the run has stopped the loop, or the runner is gone without stopping it.
	 *
	 * @param string $dir where the runner leaves a file named stop
	 * @param int $runner the process that started this loop
	 * @return bool
	 */
	public static function ended($dir, $runner)
	{
		return file_exists("$dir/stop") || posix_getppid() !== $runner;
	}
}
