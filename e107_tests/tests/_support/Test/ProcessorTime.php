<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * Timings of a test's own work, for tests that hold a run to about the speed of another.
 */
trait ProcessorTime
{
	/**
	 * The least processor time $run takes on each of $inputs, over three rounds that visit every input in turn.
	 *
	 * @param callable $run called with one input
	 * @param array    $inputs keyed by name
	 * @return float[] seconds, keyed as $inputs
	 */
	protected static function leastProcessorTimes($run, array $inputs)
	{
		return self::leastProcessorTimesOver($run, $inputs, 3);
	}

	/**
	 * The least processor time $run takes on each of $inputs, over $rounds rounds that visit every input in turn.
	 *
	 * @param callable $run called with one input
	 * @param array    $inputs keyed by name
	 * @param int      $rounds how many rounds visit every input in turn, at least one
	 * @return float[] seconds, keyed as $inputs
	 */
	protected static function leastProcessorTimesOver($run, array $inputs, $rounds)
	{
		return array_map('min', self::processorTimesByRound($run, $inputs, $rounds));
	}

	/**
	 * The processor time $run takes on each of $inputs in each of $rounds rounds that visit every input in turn.
	 *
	 * @param callable $run called with one input
	 * @param array    $inputs keyed by name
	 * @param int      $rounds how many rounds visit every input in turn, at least one
	 * @return float[][] seconds by round, keyed as $inputs
	 */
	protected static function processorTimesByRound($run, array $inputs, $rounds)
	{
		$took = array_fill_keys(array_keys($inputs), array());

		for($round = 0; $round < $rounds; $round++)
		{
			foreach($inputs as $name => $input)
			{
				$start = self::processorTime();
				$run($input);
				$took[$name][] = self::processorTime() - $start;
			}
		}

		return $took;
	}

	/**
	 * @return float seconds of processor time this process has used, in user and in kernel mode
	 */
	private static function processorTime()
	{
		$usage = getrusage();

		return $usage['ru_utime.tv_sec'] + $usage['ru_stime.tv_sec'] + ($usage['ru_utime.tv_usec'] + $usage['ru_stime.tv_usec']) / 1000000;
	}
}
