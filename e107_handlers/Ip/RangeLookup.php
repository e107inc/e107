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
 * A compiled range table that answers which rows cover an address.
 *
 * {@see RangeSet} is the table in memory, where it is built; {@see RangeFile}
 * is the same table read from disk one record at a time.
 */
interface RangeLookup
{
	/**
	 * The segment holding an address.
	 *
	 * @param string $hex address as {@see Address::toHex()} gives it
	 * @return int segment index for {@see RangeLookup::hits()}, or -1 when no range covers the address
	 */
	public function find($hex);

	/**
	 * @param int $segment
	 * @return int[] entry indexes, whitelist rows first, then narrowest range, then oldest row
	 */
	public function hits($segment);

	/**
	 * @param int $entry
	 * @return array|null id, type, expires, ip; null when the table holds no such row
	 */
	public function entry($entry);

	/**
	 * @return int rows compiled in as ranges
	 */
	public function entryCount();

	/**
	 * @return int rows that are not address ranges, such as email and host patterns
	 */
	public function others();
}
