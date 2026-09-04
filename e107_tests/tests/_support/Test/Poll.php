<?php
namespace Test;

/**
 * Waits on a condition rather than on a duration; the one sleep the test tree may contain.
 */
final class Poll
{
	const INTERVAL_MICROSECONDS = 50000;

	/**
	 * Calls $condition until it returns something truthy or $timeout seconds have passed.
	 *
	 * @param callable  $condition returns a truthy value once whatever is awaited has happened
	 * @param int|float $timeout   seconds
	 * @return mixed the first truthy value $condition returned, or false once the deadline passed
	 */
	public static function until(callable $condition, $timeout)
	{
		$deadline = microtime(true) + $timeout;

		while (true)
		{
			$result = call_user_func($condition);

			if ($result)
			{
				return $result;
			}

			if (microtime(true) >= $deadline)
			{
				return false;
			}

			usleep(self::INTERVAL_MICROSECONDS);
		}
	}
}
