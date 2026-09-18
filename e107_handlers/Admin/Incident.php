<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

use e107\Cache\Stamp;

/**
 * A repeated event the administrator should know about, kept as one record
 * rather than one row per occurrence.
 *
 * Bots repeat, so what is stored is when the run of events began, when it was
 * last seen, how many there were, and whatever detail the newest one carried.
 * An event inside the window continues the run; one after it starts a new
 * run. The record is an observation rather than a decision, so it lives with
 * the other stamps in the cache: Empty Cache drops it and the next event
 * writes it again.
 */
class Incident
{
	const WINDOW = 86400;

	/** @var Stamp */
	private $stamps;

	/**
	 * @param Stamp $stamps
	 */
	public function __construct(Stamp $stamps)
	{
		$this->stamps = $stamps;
	}

	/**
	 * @param string $id
	 * @param array $detail
	 *   Scalars stored beside the counts; the newest event's values win.
	 * @param int $window
	 *   Seconds after a run's first event during which the next one continues it.
	 * @return array
	 *   The record as stored: 'first', 'last', 'count' and the detail.
	 */
	public function record($id, array $detail = array(), $window = self::WINDOW)
	{
		$now = time();
		$previous = $this->last($id);
		$continues = ($previous !== null && ($now - $previous['first']) < $window);

		$record = array(
			'first' => $continues ? $previous['first'] : $now,
			'last'  => $now,
			'count' => $continues ? $previous['count'] + 1 : 1,
		) + self::scalars($detail);

		$this->stamps->write($id, $record);

		return $record;
	}

	/**
	 * @param string $id
	 * @return array|null
	 *   'first', 'last' and 'count' as integers plus the stored detail, or null when nothing usable is recorded.
	 */
	public function last($id)
	{
		$data = $this->stamps->read($id);

		if($data === null || !isset($data['first'], $data['last'], $data['count']))
		{
			return null;
		}

		return array(
			'first' => (int) $data['first'],
			'last'  => (int) $data['last'],
			'count' => max(1, (int) $data['count']),
		) + self::scalars($data);
	}

	/**
	 * @param string $id
	 * @return void
	 */
	public function clear($id)
	{
		$this->stamps->clear($id);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function scalars(array $data)
	{
		$scalars = array();

		foreach($data as $key => $value)
		{
			if(is_scalar($value))
			{
				$scalars[$key] = $value;
			}
		}

		return $scalars;
	}
}
