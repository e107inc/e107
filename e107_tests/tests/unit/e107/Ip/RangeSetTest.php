<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Ip;

/**
 * The compiled table is what every request searches, so a segment boundary
 * one address off, or a candidate in the wrong order, is a visitor banned or
 * let through by mistake. These pin the boundaries, the order and the shape
 * of the file the table travels in.
 */
class RangeSetTest extends \Test\Unit
{
	const WHITELIST = 100;
	const MANUAL = -1;
	const FLOOD = -2;

	protected function _before()
	{
		require_once(e_HANDLER.'Ip/Address.php');
		require_once(e_HANDLER.'Ip/Range.php');
		require_once(e_HANDLER.'Ip/RangeLookup.php');
		require_once(e_HANDLER.'Ip/RangeSet.php');
	}

	/**
	 * @param array[] $rows each: text, id, type, expires
	 * @return RangeSet compiled
	 */
	private function compiled(array $rows)
	{
		$set = new RangeSet();

		foreach($rows as $row)
		{
			$range = Range::fromString($row[0]);
			self::assertNotNull($range, $row[0].' has to parse for this test to mean anything');
			$set->add($range, $row[1], $row[2], isset($row[3]) ? $row[3] : 0, $row[0]);
		}

		$set->compile();

		return $set;
	}

	/**
	 * @param RangeSet $set
	 * @param string $address
	 * @return int[] ids of the rows covering the address, in lookup order
	 */
	private function idsAt(RangeSet $set, $address)
	{
		$segment = $set->find(Address::toHex($address));

		if($segment < 0)
		{
			return array();
		}

		$ids = array();

		foreach($set->hits($segment) as $entry)
		{
			$row = $set->entry($entry);
			$ids[] = $row['id'];
		}

		return $ids;
	}

	/**
	 * Both ends of every range are in, and the address on either side is out.
	 * ::/0 reaches the mapped IPv4 block and the all-ones address alike.
	 */
	public function testLookupHitsEveryBoundaryAndMissesJustOutside()
	{
		$set = $this->compiled(array(
			array('10.77.66.0/24', 1, self::MANUAL),
			array('10.77.70.10-10.77.70.20', 2, self::MANUAL),
			array('2001:db8::/32', 3, self::MANUAL),
		));

		foreach(array('10.77.66.0', '10.77.66.255', '10.77.70.10', '10.77.70.20', '2001:db8::', '2001:db8:ffff:ffff:ffff:ffff:ffff:ffff') as $inside)
		{
			self::assertNotSame(array(), $this->idsAt($set, $inside), $inside.' is inside a range');
		}

		foreach(array('10.77.65.255', '10.77.67.0', '10.77.70.9', '10.77.70.21', '2001:db7:ffff:ffff:ffff:ffff:ffff:ffff', '2001:db9::', '0.0.0.0', '255.255.255.255', '::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff') as $outside)
		{
			self::assertSame(array(), $this->idsAt($set, $outside), $outside.' is outside every range');
		}

		$everything = $this->compiled(array(array('::/0', 9, self::MANUAL)));
		self::assertSame(array(9), $this->idsAt($everything, '192.0.2.1'));
		self::assertSame(array(9), $this->idsAt($everything, '::'));
		self::assertSame(array(9), $this->idsAt($everything, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'));
		self::assertSame(1, $everything->segmentCount());

		$empty = new RangeSet();
		$empty->compile();
		self::assertSame(-1, $empty->find(Address::toHex('10.77.66.65')), 'an empty table covers nothing');
	}

	/**
	 * The help text has promised since 2007 that it should not be possible
	 * for a whitelisted address to be banned, so a whitelist row comes first
	 * however narrow the ban inside it, and however many bans there are.
	 */
	public function testWhitelistComesFirstAtEveryDepth()
	{
		$set = $this->compiled(array(
			array('10.1.1.5', 3, self::FLOOD),
			array('10.1.1.0/24', 2, self::MANUAL),
			array('10.1.0.0/16', 1, self::WHITELIST),
		));

		self::assertSame(array(1), $this->idsAt($set, '10.1.200.1'));
		self::assertSame(array(1, 2), $this->idsAt($set, '10.1.1.6'));
		self::assertSame(array(1, 3, 2), $this->idsAt($set, '10.1.1.5'));

		$single = $this->compiled(array(
			array('10.1.1.0/24', 2, self::MANUAL),
			array('10.1.1.5', 1, self::WHITELIST),
		));

		self::assertSame(array(2), $this->idsAt($single, '10.1.1.4'));
		self::assertSame(array(1, 2), $this->idsAt($single, '10.1.1.5'), 'a single whitelisted address inside a banned /24 comes first');
		self::assertSame(array(2), $this->idsAt($single, '10.1.1.6'));
		self::assertSame(3, $single->segmentCount());
	}

	/**
	 * Among bans the narrowest range comes first, so a ban on one address
	 * inside a banned block reports its own reason. Equal widths fall back
	 * to the oldest row, which is the one an admin will find first.
	 */
	public function testNarrowerBanComesBeforeWiderAndOlderBeforeNewer()
	{
		$set = $this->compiled(array(
			array('10.0.0.0/8', 30, self::MANUAL),
			array('10.1.0.0/16', 20, self::FLOOD),
			array('10.1.1.0/24', 10, self::MANUAL),
			array('10.1.1.0/24', 11, self::FLOOD),
		));

		self::assertSame(array(10, 11, 20, 30), $this->idsAt($set, '10.1.1.9'));
		self::assertSame(array(20, 30), $this->idsAt($set, '10.1.2.9'));
		self::assertSame(array(30), $this->idsAt($set, '10.2.0.1'));
	}

	/**
	 * Overlapping and touching ranges with different rows stay apart, since
	 * each segment has to say which rows cover it, while touching segments
	 * that agree on their rows are joined so the table stays small.
	 */
	public function testAdjacentAndOverlappingRangesCompileToDisjointSegments()
	{
		$set = $this->compiled(array(
			array('10.0.0.10-10.0.0.19', 1, self::MANUAL),
			array('10.0.0.20-10.0.0.29', 2, self::MANUAL),
			array('10.0.0.5-10.0.0.14', 3, self::MANUAL),
			array('10.0.0.0-10.0.0.35', 4, self::MANUAL),
		));

		for($i = 0; $i <= 35; $i++)
		{
			self::assertContains(4, $this->idsAt($set, '10.0.0.'.$i), '10.0.0.'.$i.' is covered');
		}

		self::assertSame(array(), $this->idsAt($set, '10.0.0.36'));
		self::assertSame(array(), $this->idsAt($set, '9.255.255.255'));
		self::assertSame(array(1, 3, 4), $this->idsAt($set, '10.0.0.12'), 'equal widths order by row id');
		self::assertSame(6, $set->segmentCount());

		$table = $set->toArray();
		foreach($table['starts'] as $k => $start)
		{
			self::assertLessThanOrEqual(0, Address::compare($start, $table['ends'][$k]), 'a segment never ends before it starts');
			if($k > 0)
			{
				self::assertLessThan(0, Address::compare($table['ends'][$k - 1], $start), 'segments never overlap');
			}
		}

		$joined = $this->compiled(array(
			array('10.0.0.0/25', 1, self::MANUAL),
			array('10.0.0.128/25', 1, self::MANUAL),
		));
		self::assertSame(2, $joined->segmentCount(), 'two rows are two segments even when they touch');

		$same = $this->compiled(array(
			array('10.0.0.0/24', 1, self::MANUAL),
			array('10.0.1.0/24', 2, self::MANUAL),
		));
		self::assertSame(2, $same->segmentCount());
	}

	/**
	 * A range that reaches the top of the address space has no address after
	 * it, and the compiler has to close that segment without asking for one.
	 */
	public function testRangeReachingTheMaximumAddressCloses()
	{
		$set = $this->compiled(array(
			array('ffff::/16', 1, self::MANUAL),
			array('8000::-ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 2, self::MANUAL),
		));

		self::assertSame(array(1, 2), $this->idsAt($set, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'));
		self::assertSame(array(2), $this->idsAt($set, '9000::'));
		self::assertSame(array(), $this->idsAt($set, '7fff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'));
		self::assertSame(Address::MAX, $set->toArray()['ends'][1]);
	}

	/**
	 * The file is regenerated after every change and read by every request,
	 * so the same rows have to give the same bytes whatever order the
	 * database returned them in.
	 */
	public function testCompileIsDeterministicAcrossInputOrder()
	{
		$rows = array(
			array('10.1.1.5', 3, self::FLOOD, 1234),
			array('10.1.1.0/24', 2, self::MANUAL),
			array('10.1.0.0/16', 1, self::WHITELIST),
			array('2001:db8::/32', 4, self::MANUAL),
			array('10.0.0.0-10.0.0.35', 5, self::MANUAL),
		);

		$first = $this->compiled($rows)->toArray();
		$second = $this->compiled(array_reverse($rows))->toArray();
		shuffle($rows);
		$third = $this->compiled($rows)->toArray();

		self::assertSame($first, $second);
		self::assertSame($first, $third);
	}

	/**
	 * A host-name or email row is no range, but its presence has to be
	 * visible to the caller, because a list of only such rows still needs
	 * the database checks to run.
	 */
	public function testCountsRowsItCouldNotParse()
	{
		$set = new RangeSet();
		$set->addOther();
		$set->addOther();
		$set->compile();

		self::assertSame(2, $set->others());
		self::assertSame(0, $set->entryCount());
		self::assertSame(0, $set->segmentCount());
		self::assertSame(2, $set->toArray()['others']);
	}
}
