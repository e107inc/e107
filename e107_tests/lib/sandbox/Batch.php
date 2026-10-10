<?php

namespace Sandbox;

/**
 * One Codeception process: a batch of test files run in one sandbox against one database.
 */
class Batch
{
	/** @var int */
	public $number;

	/** @var string[] */
	public $files;

	/** @var int */
	public $worker;

	/** @var string */
	public $database;

	/** @var string where the process writes its output, report and log */
	public $dir;

	/** @var float */
	public $seconds = 0.0;

	/** @var int|null */
	public $exitCode;

	/** @var resource|null */
	private $process;

	/** @var float */
	private $started;

	/**
	 * @param int $number
	 * @param string[] $files
	 * @param int $worker
	 * @param string $database
	 * @param string $dir
	 */
	public function __construct($number, array $files, $worker, $database, $dir)
	{
		$this->number = $number;
		$this->files = $files;
		$this->worker = $worker;
		$this->database = $database;
		$this->dir = $dir;
		@mkdir($dir, 0777, true);
	}

	/**
	 * @param string $command
	 * @param bool $passthrough whether the output goes to this process's own rather than to the batch log
	 * @return void
	 */
	public function start($command, $passthrough)
	{
		$log = array('file', $this->log(), 'w');
		$io = $passthrough ? array(STDIN, STDOUT, STDERR) : array(array('file', '/dev/null', 'r'), $log, $log);
		$this->started = microtime(true);
		$this->process = proc_open($command, $io, $pipes);
		if ($this->process === false)
		{
			throw new \RuntimeException("could not start: $command");
		}
	}

	/** @return bool */
	public function isRunning()
	{
		if ($this->exitCode !== null)
		{
			return false;
		}
		$status = proc_get_status($this->process);
		if ($status['running'])
		{
			return true;
		}
		$this->exitCode = $status['exitcode'];
		$this->seconds = microtime(true) - $this->started;
		proc_close($this->process);

		return false;
	}

	/** @return string */
	public function log()
	{
		return $this->dir.'/console.log';
	}

	/** @return string how the batch is named in the run's output */
	public function label()
	{
		return count($this->files) === 1 ? basename($this->files[0], '.php') : 'batch-'.$this->number;
	}
}
