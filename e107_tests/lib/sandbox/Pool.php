<?php

namespace Sandbox;

/**
 * Databases cloned ahead of need and dropped after use by child processes, so neither a clone nor MariaDB's occasional multi-second DROP sits on a worker's path.
 *
 * The children and the runner talk through a directory: a file in ready/ is a database a worker may take, a file in used/ one to drop, and stop ends the cloners.
 */
class Pool
{
	/** @var string */
	private $dir;

	/** @var resource[] */
	private $cloners = array();

	/** @param string $dir */
	public function __construct($dir)
	{
		$this->dir = $dir;
		foreach (array('ready', 'used') as $sub)
		{
			@mkdir("$dir/$sub", 0777, true);
		}
	}

	/** @param string $command how to start this script's pool loops */
	public function startDropper($command)
	{
		$this->spawn("$command --drop ".escapeshellarg($this->dir));
	}

	/**
	 * @param string $command how to start this script's pool loops
	 * @param string $template
	 * @param string $prefix every pool database's name starts with it
	 * @param int $ready how many to keep cloned ahead
	 * @param int $cloners
	 * @return void
	 */
	public function startCloners($command, $template, $prefix, $ready, $cloners)
	{
		for ($i = 1; $i <= $cloners; $i++)
		{
			$this->cloners[] = $this->spawn("$command --clone ".implode(' ', array_map('escapeshellarg', array($this->dir, $template, "$prefix{$i}_", $ready))));
		}
	}

	/** @return bool whether any cloner is still at work */
	public function isCloning()
	{
		foreach ($this->cloners as $cloner)
		{
			$status = proc_get_status($cloner);
			if ($status['running'])
			{
				return true;
			}
		}

		return !empty(glob($this->dir.'/ready/*'));
	}

	/** @return string|null a ready database, now the caller's */
	public function claim()
	{
		foreach (glob($this->dir.'/ready/*') ?: array() as $file)
		{
			if (@unlink($file))
			{
				return basename($file);
			}
		}

		return null;
	}

	/** @param string $name a database to drop */
	public function release($name)
	{
		touch($this->dir.'/used/'.$name);
	}

	/** Stop cloning and hand everything still ready to the dropper, which outlives the run if it has to. */
	public function stop()
	{
		if (file_exists($this->dir.'/stop'))
		{
			return;
		}
		touch($this->dir.'/stop');
		foreach ($this->cloners as $cloner)
		{
			proc_close($cloner);
		}
		while (($name = $this->claim()) !== null)
		{
			$this->release($name);
		}
	}

	/**
	 * @param Databases $databases
	 * @param string $dir
	 * @param string $template
	 * @param string $prefix
	 * @param int $ready
	 * @return void
	 */
	public static function cloneLoop(Databases $databases, $dir, $template, $prefix, $ready)
	{
		$alive = fopen("$dir/cloner-$prefix.lock", 'c');
		flock($alive, LOCK_EX);
		$runner = posix_getppid();
		for ($k = 1; is_dir($dir) && !self::ended($dir, $runner); )
		{
			if (count(glob("$dir/ready/*") ?: array()) >= $ready)
			{
				usleep(10000);
				continue;
			}
			$databases->copy($template, $prefix.$k);
			if (self::ended($dir, $runner))
			{
				touch("$dir/used/$prefix$k");
				break;
			}
			touch("$dir/ready/$prefix".$k++);
		}
	}

	/**
	 * @param Databases $databases
	 * @param string $dir
	 * @return void
	 */
	public static function dropLoop(Databases $databases, $dir)
	{
		$runner = posix_getppid();
		while (is_dir($dir))
		{
			$ended = self::ended($dir, $runner);
			$used = array_merge(glob("$dir/used/*") ?: array(), $ended ? (glob("$dir/ready/*") ?: array()) : array());
			if (empty($used) && $ended && !self::cloning($dir))
			{
				return;
			}
			foreach ($used as $file)
			{
				$databases->drop(basename($file));
				@unlink($file);
			}
			usleep(20000);
		}
	}

	/**
	 * Whether the run has stopped the pool, or the runner is gone without stopping it.
	 *
	 * @param string $dir
	 * @param int $runner the process that started this loop
	 * @return bool
	 */
	private static function ended($dir, $runner)
	{
		return file_exists("$dir/stop") || posix_getppid() !== $runner;
	}

	/** Whether a cloner is still alive, and may yet hand over the copy it is making: each holds a lock on its own file until it exits. */
	private static function cloning($dir)
	{
		foreach (glob("$dir/cloner-*.lock") ?: array() as $file)
		{
			$lock = fopen($file, 'c');
			$free = flock($lock, LOCK_EX | LOCK_NB);
			fclose($lock);
			if (!$free)
			{
				return true;
			}
		}

		return false;
	}

	/** In a session of its own, so the hangup that ends an interactive run does not end the dropper with it; the loops watch for the runner instead. */
	private function spawn($command)
	{
		$log = $this->dir.'/pool.log';

		return proc_open('exec setsid '.$command, array(array('file', '/dev/null', 'r'), array('file', $log, 'a'), array('file', $log, 'a')), $pipes);
	}
}
