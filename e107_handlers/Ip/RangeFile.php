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
 * A {@see RangeSet} on disk, in fixed-width records searched by seeking.
 *
 * The ban check runs on every request before the database opens, so the
 * cost of reading the table is the cost that matters, and it has to stay
 * flat as the list grows. Parsing the whole table each time, whether as PHP
 * source, serialize() or JSON, is linear in the number of rows and slower
 * than the old prefix scan below a few thousand rows even with opcache. A
 * record file needs no parsing: a lookup seeks to the middle segment record,
 * compares, and halves, reading a few hundred bytes for any list size, and
 * the rows behind a hit are read only when there is one.
 *
 * Layout, every field left as text so the file can be read by eye:
 *
 * <code>
 * <?php exit; ?>
 * banranges 1 <segments> <entries> <others> <hits>       counts, 10 digits each
 * <start> <end> <hitOffset> <hitCount>                   one 80-byte record per segment
 * <entryIndex>                                           one 9-byte record per hit, in segment order
 * <id> <type> <expires> <ip padded to 100>               one 128-byte record per entry
 * </code>
 *
 * A file whose size is not what the counts add up to is refused, so a short
 * write leaves the caller with no table rather than a table missing rows.
 * The first line keeps the file inert if a web server ever serves it; the
 * file is never included.
 */
class RangeFile implements RangeLookup
{
	const GUARD = "<?php exit; ?>\n";
	const MAGIC = 'banranges';
	const VERSION = 1;
	const HEADER_WIDTH = 60;
	const SEGMENT_WIDTH = 80;
	const HIT_WIDTH = 9;
	const ENTRY_WIDTH = 128;
	const IP_WIDTH = 100;

	/** @var resource */
	private $handle;

	/** @var int */
	private $segments;

	/** @var int */
	private $entries;

	/** @var int */
	private $others;

	/** @var int byte offset of the first segment record */
	private $segmentBase;

	/** @var int byte offset of the first hit record */
	private $hitBase;

	/** @var int byte offset of the first entry record */
	private $entryBase;

	/**
	 * @param resource $handle
	 * @param int[] $counts segments, entries, others, hits
	 */
	private function __construct($handle, array $counts)
	{
		$this->handle = $handle;
		list($this->segments, $this->entries, $this->others, $hits) = $counts;
		$this->segmentBase = strlen(self::GUARD) + self::HEADER_WIDTH;
		$this->hitBase = $this->segmentBase + $this->segments * self::SEGMENT_WIDTH;
		$this->entryBase = $this->hitBase + $hits * self::HIT_WIDTH;
	}

	/**
	 * @param string $path
	 * @return RangeFile|null null when the file is missing, unreadable, or not a table this version wrote
	 */
	public static function open($path)
	{
		$handle = is_readable($path) ? fopen($path, 'rb') : false;

		if($handle === false)
		{
			return null;
		}

		$header = fread($handle, strlen(self::GUARD) + self::HEADER_WIDTH);
		$fields = explode(' ', rtrim((string) substr((string) $header, strlen(self::GUARD)), " \n"));

		if(strpos((string) $header, self::GUARD) !== 0 || count($fields) !== 6 || $fields[0] !== self::MAGIC || (int) $fields[1] !== self::VERSION)
		{
			fclose($handle);

			return null;
		}

		$counts = array_map('intval', array_slice($fields, 2));
		$expected = strlen(self::GUARD) + self::HEADER_WIDTH + $counts[0] * self::SEGMENT_WIDTH + $counts[3] * self::HIT_WIDTH + $counts[1] * self::ENTRY_WIDTH;
		$stat = fstat($handle);

		if($stat === false || $stat['size'] !== $expected)
		{
			fclose($handle);

			return null;
		}

		return new self($handle, $counts);
	}

	/**
	 * The file contents for a compiled set, for the caller to write atomically.
	 *
	 * @param RangeSet $set compiled
	 * @return string
	 */
	public static function render(RangeSet $set)
	{
		$table = $set->toArray();
		$segments = '';
		$hits = '';
		$hitCount = 0;

		foreach($table['starts'] as $i => $start)
		{
			$segments .= sprintf("%s %s %08x %04x\n", $start, $table['ends'][$i], $hitCount, count($table['hits'][$i]));

			foreach($table['hits'][$i] as $entry)
			{
				$hits .= sprintf("%08x\n", $entry);
				$hitCount++;
			}
		}

		$entries = '';

		foreach($table['entries'] as $entry)
		{
			$entries .= sprintf("%010d %4d %010d %-".self::IP_WIDTH."s\n", $entry[0], $entry[1], $entry[2], substr($entry[3], 0, self::IP_WIDTH));
		}

		$header = sprintf('%s %d %010d %010d %010d %010d', self::MAGIC, self::VERSION, count($table['starts']), count($table['entries']), $table['others'], $hitCount);

		return self::GUARD.str_pad($header, self::HEADER_WIDTH - 1)."\n".$segments.$hits.$entries;
	}

	public function find($hex)
	{
		$lo = 0;
		$hi = $this->segments;

		while($lo < $hi)
		{
			$mid = ($lo + $hi) >> 1;
			$record = $this->record($this->segmentBase, self::SEGMENT_WIDTH, $mid);

			if($record === null || strcmp(substr($record, 0, Address::LENGTH), $hex) <= 0)
			{
				$lo = $mid + 1;
			}
			else
			{
				$hi = $mid;
			}
		}

		if($lo === 0)
		{
			return -1;
		}

		$record = $this->record($this->segmentBase, self::SEGMENT_WIDTH, $lo - 1);

		if($record === null || strcmp((string) substr($record, Address::LENGTH + 1, Address::LENGTH), $hex) < 0)
		{
			return -1;
		}

		return $lo - 1;
	}

	public function hits($segment)
	{
		$record = $this->record($this->segmentBase, self::SEGMENT_WIDTH, $segment);

		if($record === null)
		{
			return array();
		}

		$offset = hexdec((string) substr($record, 2 * Address::LENGTH + 2, 8));
		$count = hexdec((string) substr($record, 2 * Address::LENGTH + 11, 4));
		$hits = array();

		for($i = 0; $i < $count; $i++)
		{
			$hit = $this->record($this->hitBase, self::HIT_WIDTH, $offset + $i);

			if($hit === null)
			{
				break;
			}

			$hits[] = hexdec(substr($hit, 0, 8));
		}

		return $hits;
	}

	public function entry($entry)
	{
		$record = $this->record($this->entryBase, self::ENTRY_WIDTH, $entry);

		if($record === null)
		{
			return null;
		}

		return array(
			'id'      => (int) substr($record, 0, 10),
			'type'    => (int) substr($record, 11, 4),
			'expires' => (int) substr($record, 16, 10),
			'ip'      => rtrim((string) substr($record, 27, self::IP_WIDTH)),
		);
	}

	public function entryCount()
	{
		return $this->entries;
	}

	public function others()
	{
		return $this->others;
	}

	/**
	 * @param int $base
	 * @param int $width
	 * @param int $index
	 * @return string|null the record, or null when the file ends before it
	 */
	private function record($base, $width, $index)
	{
		if(fseek($this->handle, $base + $index * $width) !== 0)
		{
			return null;
		}

		$record = fread($this->handle, $width);

		return (is_string($record) && strlen($record) === $width) ? $record : null;
	}
}
