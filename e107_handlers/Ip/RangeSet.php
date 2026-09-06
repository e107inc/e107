<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Ip;

/**
 * Tagged address ranges compiled into disjoint segments for binary search.
 *
 * Each range carries the ban-list row it came from: its id, ban type, expiry
 * and stored text. Ranges may overlap, so compiling cuts the address space at
 * every boundary and gives each resulting segment the rows that cover it, in
 * the order a lookup should consider them: whitelist rows first, then the
 * narrowest range, then the oldest row. Adjacent segments with the same rows
 * are joined, so the table never holds more than twice the number of ranges.
 *
 * The compiled table is plain arrays of hex strings; {@see RangeFile} writes
 * it to disk in the form a request reads. {@see RangeSet::find()} allocates
 * nothing.
 *
 * <code>
 * $set = new RangeSet();
 * $set->add(Range::fromString('10.77.66.0/24'), 17, -1, 0, '10.77.66.0/24');
 * $set->compile();
 * $i = $set->find(Address::toHex('10.77.66.65'));   // segment index, or -1
 * foreach($set->hits($i) as $entry) { $row = $set->entry($entry); }
 * </code>
 */
class RangeSet implements RangeLookup
{
	const VERSION = 1;
	const WHITELIST_TYPE = 100;

	/** @var array[] rows added before compiling: start, end, id, type, expires, stored */
	private $pending = array();

	/** @var int rows that are not address ranges, counted so the caller knows the list is not empty */
	private $others = 0;

	/** @var string[] */
	private $starts = array();

	/** @var string[] */
	private $ends = array();

	/** @var int[][] entry indexes per segment, in lookup order */
	private $hits = array();

	/** @var array[] id, type, expires, stored */
	private $entries = array();

	/**
	 * @param Range $range
	 * @param int $id ban-list row id
	 * @param int $type ban type; {@see RangeSet::WHITELIST_TYPE} and above is a whitelist row
	 * @param int $expires unix time, or 0 for never
	 * @param string $stored the row's address text as stored
	 * @return void
	 */
	public function add(Range $range, $id, $type, $expires, $stored)
	{
		$this->pending[] = array($range->start(), $range->end(), (int) $id, (int) $type, (int) $expires, (string) $stored);
	}

	/**
	 * Count a row that is not an address range, such as an email or host pattern.
	 *
	 * @return void
	 */
	public function addOther()
	{
		$this->others++;
	}

	/**
	 * Turn the rows added so far into the segment table. Output is the same
	 * whatever order the rows were added in.
	 *
	 * @return void
	 */
	public function compile()
	{
		usort($this->pending, array($this, 'compareRows'));

		$this->entries = array();
		$ranges = array();
		$boundaries = array();

		foreach($this->pending as $i => $row)
		{
			$this->entries[$i] = array($row[2], $row[3], $row[4], $row[5]);
			$ranges[] = array(
				'start'     => $row[0],
				'end'       => $row[1],
				'entry'     => $i,
				'whitelist' => $row[3] >= self::WHITELIST_TYPE ? 0 : 1,
				'width'     => Address::sub($row[1], $row[0]),
			);
			$boundaries[$row[0]] = true;
			$after = Address::succ($row[1]);

			if($after !== null)
			{
				$boundaries[$after] = true;
			}
		}

		usort($ranges, array($this, 'compareStarts'));
		$boundaries = array_keys($boundaries);
		sort($boundaries, SORT_STRING);

		$this->starts = array();
		$this->ends = array();
		$this->hits = array();
		$active = array();
		$next = 0;
		$total = count($ranges);
		$last = count($boundaries) - 1;

		foreach($boundaries as $k => $start)
		{
			$end = $k === $last ? Address::MAX : Address::pred($boundaries[$k + 1]);

			foreach($active as $j => $range)
			{
				if(Address::compare($range['end'], $start) < 0)
				{
					unset($active[$j]);
				}
			}

			while($next < $total && $ranges[$next]['start'] === $start)
			{
				$active[] = $ranges[$next];
				$next++;
			}

			if(empty($active))
			{
				continue;
			}

			usort($active, array($this, 'comparePriority'));
			$hits = array();

			foreach($active as $range)
			{
				$hits[] = $range['entry'];
			}

			$segment = count($this->starts) - 1;

			if($segment >= 0 && $this->hits[$segment] === $hits && Address::succ($this->ends[$segment]) === $start)
			{
				$this->ends[$segment] = $end;
				continue;
			}

			$this->starts[] = $start;
			$this->ends[] = $end;
			$this->hits[] = $hits;
		}

		$this->pending = array();
	}

	/**
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private function compareRows($a, $b)
	{
		if($a[2] !== $b[2])
		{
			return $a[2] < $b[2] ? -1 : 1;
		}

		return strcmp($a[5], $b[5]);
	}

	/**
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private function compareStarts($a, $b)
	{
		$order = strcmp($a['start'], $b['start']);

		return $order !== 0 ? $order : $a['entry'] - $b['entry'];
	}

	/**
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private function comparePriority($a, $b)
	{
		if($a['whitelist'] !== $b['whitelist'])
		{
			return $a['whitelist'] - $b['whitelist'];
		}

		$order = strcmp($a['width'], $b['width']);

		return $order !== 0 ? $order : $a['entry'] - $b['entry'];
	}

	/**
	 * The segment holding an address.
	 *
	 * @param string $hex
	 * @return int segment index for {@see RangeSet::hits()}, or -1 when no range covers the address
	 */
	public function find($hex)
	{
		$lo = 0;
		$hi = count($this->starts);

		while($lo < $hi)
		{
			$mid = ($lo + $hi) >> 1;

			if(strcmp($this->starts[$mid], $hex) <= 0)
			{
				$lo = $mid + 1;
			}
			else
			{
				$hi = $mid;
			}
		}

		if($lo === 0 || strcmp($this->ends[$lo - 1], $hex) < 0)
		{
			return -1;
		}

		return $lo - 1;
	}

	/**
	 * @param int $segment
	 * @return int[] entry indexes, whitelist rows first, then narrowest range, then oldest row
	 */
	public function hits($segment)
	{
		return $this->hits[$segment];
	}

	/**
	 * @param int $entry
	 * @return array|null id, type, expires, ip; null when the table holds no such row
	 */
	public function entry($entry)
	{
		if(!isset($this->entries[$entry]))
		{
			return null;
		}

		$row = $this->entries[$entry];

		return array('id' => $row[0], 'type' => $row[1], 'expires' => $row[2], 'ip' => $row[3]);
	}

	/**
	 * @return int rows compiled in as ranges
	 */
	public function entryCount()
	{
		return count($this->entries);
	}

	/**
	 * @return int rows counted with {@see RangeSet::addOther()}
	 */
	public function others()
	{
		return $this->others;
	}

	/**
	 * @return int
	 */
	public function segmentCount()
	{
		return count($this->starts);
	}

	/**
	 * @return array the compiled table, as {@see RangeFile::render()} writes it
	 */
	public function toArray()
	{
		return array(
			'version' => self::VERSION,
			'others'  => $this->others,
			'starts'  => $this->starts,
			'ends'    => $this->ends,
			'hits'    => $this->hits,
			'entries' => $this->entries,
		);
	}
}
