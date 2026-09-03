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
 * fromString() decides which stored ban-list rows are enforced by address and
 * what they cover. A form it accepts too widely bans strangers; one it
 * accepts too narrowly lets the banned back in; one it rejects is listed
 * as not enforced. Every accepted spelling is pinned here with its bounds.
 */
class RangeTest extends \Test\Unit
{
	const V4_PREFIX = '00000000000000000000ffff';

	protected function _before()
	{
		require_once(e_HANDLER.'Ip/Address.php');
		require_once(e_HANDLER.'Ip/Range.php');
	}

	/**
	 * The help text has promised dotted wildcards since 2007, the encoded
	 * form with x nibbles is what older rows hold, and CIDR and hyphenated
	 * ranges are the two notations everything else on the internet uses.
	 */
	public function testEveryAcceptedSyntaxYieldsItsBounds()
	{
		$cases = array(
			'10.77.66.65'                                  => array(self::V4_PREFIX.'0a4d4241', self::V4_PREFIX.'0a4d4241'),
			'0000:0000:0000:0000:0000:ffff:0a4d:4241'      => array(self::V4_PREFIX.'0a4d4241', self::V4_PREFIX.'0a4d4241'),
			'10.77.66.*'                                   => array(self::V4_PREFIX.'0a4d4200', self::V4_PREFIX.'0a4d42ff'),
			'10.77.66.x'                                   => array(self::V4_PREFIX.'0a4d4200', self::V4_PREFIX.'0a4d42ff'),
			'10.77.*.*'                                    => array(self::V4_PREFIX.'0a4d0000', self::V4_PREFIX.'0a4dffff'),
			'214.098.*.*'                                  => array(self::V4_PREFIX.'d6620000', self::V4_PREFIX.'d662ffff'),
			'*.*.*.*'                                      => array(self::V4_PREFIX.'00000000', self::V4_PREFIX.'ffffffff'),
			'0000:0000:0000:0000:0000:ffff:0a4d:42xx'      => array(self::V4_PREFIX.'0a4d4200', self::V4_PREFIX.'0a4d42ff'),
			'0000:0000:0000:0000:0000:ffff:0a4d:42Xx'      => array(self::V4_PREFIX.'0a4d4200', self::V4_PREFIX.'0a4d42ff'),
			'0000:0000:0000:0000:0000:ffff:xxxx:xxxx'      => array(self::V4_PREFIX.'00000000', self::V4_PREFIX.'ffffffff'),
			'00000000000000000000ffff0a4d42xx'             => array(self::V4_PREFIX.'0a4d4200', self::V4_PREFIX.'0a4d42ff'),
			'2001:0db8:00xx:xxxx:xxxx:xxxx:xxxx:xxxx'      => array('20010db8000000000000000000000000', '20010db800ffffffffffffffffffffff'),
			'10.77.66.0/24'                                => array(self::V4_PREFIX.'0a4d4200', self::V4_PREFIX.'0a4d42ff'),
			'10.77.66.128/25'                              => array(self::V4_PREFIX.'0a4d4280', self::V4_PREFIX.'0a4d42ff'),
			'10.77.66.65/32'                               => array(self::V4_PREFIX.'0a4d4241', self::V4_PREFIX.'0a4d4241'),
			'0.0.0.0/0'                                    => array(self::V4_PREFIX.'00000000', self::V4_PREFIX.'ffffffff'),
			'::ffff:0:0/96'                                => array(self::V4_PREFIX.'00000000', self::V4_PREFIX.'ffffffff'),
			'2001:db8::/32'                                => array('20010db8000000000000000000000000', '20010db8ffffffffffffffffffffffff'),
			'2001:DB8::/33'                                => array('20010db8000000000000000000000000', '20010db87fffffffffffffffffffffff'),
			'::/0'                                         => array(Address::MIN, Address::MAX),
			'ffff::/16'                                    => array('ffff0000000000000000000000000000', Address::MAX),
			'10.77.66.1-10.77.66.100'                      => array(self::V4_PREFIX.'0a4d4201', self::V4_PREFIX.'0a4d4264'),
			' 10.77.66.1 - 10.77.66.9 '                    => array(self::V4_PREFIX.'0a4d4201', self::V4_PREFIX.'0a4d4209'),
			'10.77.66.9-10.77.66.9'                        => array(self::V4_PREFIX.'0a4d4209', self::V4_PREFIX.'0a4d4209'),
			'2001:db8::1-2001:db8::ff'                     => array('20010db8000000000000000000000001', '20010db80000000000000000000000ff'),
			'8000::-ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff' => array('80000000000000000000000000000000', Address::MAX),
		);

		foreach($cases as $text => $bounds)
		{
			$range = Range::fromString($text);
			self::assertNotNull($range, $text.' has to parse');
			self::assertSame($bounds[0], $range->start(), $text.' starts elsewhere');
			self::assertSame($bounds[1], $range->end(), $text.' ends elsewhere');
		}
	}

	/**
	 * The list mod_antiloris rejects, plus the forms e107 used to widen or
	 * shrink silently: a wildcard inside an address widened it to the prefix
	 * before the wildcard, and a stray character after an octet shrank it to
	 * a single address. Host names and emails are for the database checks.
	 */
	public function testRejectsMalformedRanges()
	{
		$rejected = array(
			'', ' ', null, 42,
			'1.2.3.4/33', '::/129', '::/384', '::/24junk', '2001:db8::/24junk', '1.2.3.0/', '1.2.3.0/+24', '1.2.3.0/-24', '/24',
			'1.2.3.5/30', '2001:db8::1/32', '1.2.3.0/24/24',
			'9.9.9.9-1.1.1.1', '1.1.1.1-::1', '::1-1.1.1.1', '1.1.1.1-', '-1.1.1.1', '1.1.1.1-2.2.2.2-3.3.3.3',
			'10.*.66.5', '10.77.66.5*', '10.77.66.**', '*.77.66.5', '10.77.66.', '10.77.66', '10.77.66.256.*', 'x.x.x',
			'0000:0000:0000:0000:0000:ffff:0a4d:x241', '0000:0000:0000:0000:0000:ffff:0a4dxx:42', 'xxxx',
			'256.300.1.1', 'not.an.ip', 'notanip', '10.77. 66.0', '10.77.66.0 /24', '10.77.66. *', '10.77.66.1 -10.77.66.9 x', '10 .77.66.1-10.77.66.9',
			'*.de', 'bad.cc', '*@example.com', 'user@example.com', 'http://example.com/',
		);

		foreach($rejected as $text)
		{
			self::assertNull(Range::fromString($text), var_export($text, true).' is not an address range');
		}
	}

	/**
	 * Containment is inclusive at both ends, and an IPv4 range never covers a
	 * native IPv6 visitor or the other way round, because the two live in
	 * different parts of the same 128-bit space.
	 */
	public function testContainsIsInclusiveAndFamilyBound()
	{
		$range = Range::fromString('10.77.66.0/24');

		self::assertTrue($range->contains(Address::toHex('10.77.66.0')));
		self::assertTrue($range->contains(Address::toHex('10.77.66.255')));
		self::assertFalse($range->contains(Address::toHex('10.77.65.255')));
		self::assertFalse($range->contains(Address::toHex('10.77.67.0')));
		self::assertFalse($range->contains(Address::toHex('2001:db8::a4d:4241')));
		self::assertFalse(Range::fromString('::/96')->contains(Address::toHex('10.77.66.65')), 'an IPv6 prefix below the mapped block leaves IPv4 alone');
		self::assertTrue(Range::fromString('::/8')->contains(Address::toHex('10.77.66.65')), 'a prefix wide enough to reach the mapped block covers IPv4 too');
		self::assertTrue(Range::fromString('::/0')->contains(Address::toHex('10.77.66.65')), 'the whole space includes the mapped block');
		self::assertTrue(Range::fromString('10.77.66.65')->isSingle());
		self::assertFalse($range->isSingle());
	}

	/**
	 * Width orders overlapping bans, narrowest first. A single address has
	 * width zero, a /24 has 255, and the whole IPv4 space is wider than any
	 * IPv4 range but narrower than ::/0.
	 */
	public function testWidthOrdersRangesByTheirSize()
	{
		$single = Range::fromString('10.77.66.65')->width();
		$slash24 = Range::fromString('10.77.66.0/24')->width();
		$allV4 = Range::fromString('0.0.0.0/0')->width();
		$all = Range::fromString('::/0')->width();

		self::assertSame(Address::MIN, $single);
		self::assertSame('000000000000000000000000000000ff', $slash24);
		self::assertLessThan(0, Address::compare($single, $slash24));
		self::assertLessThan(0, Address::compare($slash24, $allV4));
		self::assertLessThan(0, Address::compare($allV4, $all));
	}

	/**
	 * The admin list shows what a row covers, in text that pastes back in as
	 * the same range.
	 */
	public function testDisplayRoundTrips()
	{
		$cases = array('10.77.66.65', '10.77.66.0 - 10.77.66.255', '10.77.66.1 - 10.77.66.100', '2001:db8::1', '2001:db8:: - 2001:db8:ffff:ffff:ffff:ffff:ffff:ffff', ':: - ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');

		foreach($cases as $text)
		{
			self::assertSame($text, Range::fromString($text)->toDisplay());
		}

		self::assertSame('10.77.66.0 - 10.77.66.255', Range::fromString('10.77.66.*')->toDisplay());
		self::assertSame('10.77.66.65', Range::fromString('0000:0000:0000:0000:0000:ffff:0a4d:4241')->toDisplay());
	}

	/**
	 * Apache reads CIDR blocks and nothing else, so a hyphenated range has
	 * to become the fewest blocks that cover exactly it, and each block has
	 * to be written in the family's own notation.
	 */
	public function testCidrCoverIsMinimalAndExact()
	{
		self::assertSame(array('10.77.66.0/24'), Range::fromString('10.77.66.*')->toCidr());
		self::assertSame(array('10.77.66.65/32'), Range::fromString('10.77.66.65')->toCidr());
		self::assertSame(array('0.0.0.0/0'), Range::fromString('*.*.*.*')->toCidr());
		self::assertSame(array('::/0'), Range::fromString('::/0')->toCidr());
		self::assertSame(array('2001:db8::/32'), Range::fromString('2001:db8::/32')->toCidr());
		self::assertSame(
			array('10.77.66.1/32', '10.77.66.2/31', '10.77.66.4/30', '10.77.66.8/31'),
			Range::fromString('10.77.66.1-10.77.66.9')->toCidr()
		);
		self::assertSame(array('10.77.66.0/25', '10.77.66.128/26', '10.77.66.192/27', '10.77.66.224/28', '10.77.66.240/29', '10.77.66.248/30', '10.77.66.252/31', '10.77.66.254/32'),
			Range::fromString('10.77.66.0-10.77.66.254')->toCidr());
		self::assertSame(array('8000::/1'), Range::fromString('8000::-ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->toCidr(),
			'a range that ends at the top of the space has to close without a successor');

		$range = Range::fromString('10.77.66.3-10.77.67.250');
		$covered = 0;
		foreach($range->toCidr() as $block)
		{
			$piece = Range::fromString($block);
			self::assertTrue($range->contains($piece->start()) && $range->contains($piece->end()), $block.' spills outside the range');
			$covered += hexdec($piece->width()) + 1;
		}
		self::assertSame(hexdec($range->width()) + 1, $covered, 'the blocks have to add up to the range exactly');
	}
}
