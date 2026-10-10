<?php

namespace Sandbox;

/**
 * The suite's test files, heaviest first, handed out one sandbox's worth at a time to whichever worker is free.
 */
class Queue
{
	/** @var array<string,float> file => estimated seconds, heaviest first */
	private $weights;

	/** @var int */
	private $workers;

	/** @var float|null what a batch is worth at least, or null for a file per batch */
	private $floor;

	/**
	 * @param array<string,float> $weights
	 * @param int $workers
	 * @param bool $batched whether to hand out batches of files rather than one at a time
	 */
	public function __construct(array $weights, $workers, $batched)
	{
		arsort($weights);
		$this->weights = $weights;
		$this->workers = $workers;
		$this->floor = $batched ? array_sum($weights) / (4 * $workers) : null;
	}

	/** @return bool */
	public function isEmpty()
	{
		return empty($this->weights);
	}

	/** @return int */
	public function count()
	{
		return count($this->weights);
	}

	/**
	 * The next batch: one file, or a share of what is left that shrinks towards a quarter of one worker's part as the run nears its end, so the workers finish together and few processes are started.
	 *
	 * @return string[]
	 */
	public function next()
	{
		if ($this->floor === null || $this->workers === 1)
		{
			return $this->floor === null ? array($this->take()) : $this->takeAll();
		}

		$target = max(array_sum($this->weights) / (2 * $this->workers), $this->floor);
		$batch = array();
		$sum = 0.0;
		while (!$this->isEmpty() && ($sum < $target || empty($batch)))
		{
			$sum += reset($this->weights);
			$batch[] = $this->take();
		}

		return $batch;
	}

	private function take()
	{
		reset($this->weights);
		$file = key($this->weights);
		unset($this->weights[$file]);

		return $file;
	}

	private function takeAll()
	{
		$files = array_keys($this->weights);
		$this->weights = array();

		return $files;
	}
}
