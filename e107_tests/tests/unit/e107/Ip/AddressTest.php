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
 * The 32-character hex form is what the ban engine sorts, searches and
 * subtracts, so every way an address has ever been written has to land on
 * the same string, and the string arithmetic has to agree with the numbers.
 */
class AddressTest extends \Test\Unit
{
	const LOCALHOST = '00000000000000000000ffff7f000001';
	const V4 = '00000000000000000000ffff0a4d4241';

	protected function _before()
	{
		require_once(e_HANDLER.'Ip/Address.php');
	}

	/**
	 * Dotted IPv4 with and without leading zeros, IPv6 in every spelling,
	 * the encoded form eIPHandler stores, the packed hex ipEncode() gives
	 * with an empty divider, and the 8-character hex of 0.7-era rows are all
	 * the same address.
	 */
	public function testEveryTextualFormParsesToTheSameHex()
	{
		$cases = array(
			'10.77.66.65'                             => self::V4,
			' 10.77.66.65 '                           => self::V4,
			'0000:0000:0000:0000:0000:ffff:0a4d:4241' => self::V4,
			'::ffff:10.77.66.65'                      => self::V4,
			'::ffff:a4d:4241'                         => self::V4,
			'00000000000000000000ffff0a4d4241'        => self::V4,
			'0a4d4241'                                => self::V4,
			'127.0.0.1'                               => self::LOCALHOST,
			'7f000001'                                => self::LOCALHOST,
			'214.098.001.1'                           => '00000000000000000000ffffd6620101',
			'2001:db8::1'                             => '20010db8000000000000000000000001',
			'2001:DB8:0:0:0:0:0:1'                    => '20010db8000000000000000000000001',
			'2001:0DB8:0000:0000:0000:0000:0000:0001' => '20010db8000000000000000000000001',
			'::1'                                     => '00000000000000000000000000000001',
			'::'                                      => Address::MIN,
			'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff' => Address::MAX,
		);

		foreach($cases as $text => $hex)
		{
			self::assertSame($hex, Address::toHex($text), $text.' has to canonicalise to '.$hex);
		}

		self::assertSame(bin2hex(inet_pton('::ffff:10.77.66.65')), self::V4,
			'an IPv4 address has to sit where inet_pton() puts it, so the two never disagree');
	}

	/**
	 * Wildcards, ranges, host names, emails and out-of-range octets are the
	 * business of other classes or of nobody. ipEncode() used to turn
	 * 256.300.1.1 into a string of the right shape; the engine must not.
	 */
	public function testRejectsWhatIsNotAnAddress()
	{
		$rejected = array(
			'', ' ', null, 123, 'not.an.ip', '256.300.1.1', '10.77.66', '10.77.66.65.1', '10.77.66.',
			'10.77.66.*', '10.77.66.x', '*.*.*.*', '*.de', 'bad.cc', '*@example.com', 'user@example.com',
			'10.77.66.0/24', '10.77.66.1-10.77.66.9', '10.77. 66.0', '2001:db8::/32',
			'0000:0000:0000:0000:0000:ffff:0a4d:42xx', '00000000000000000000ffff0a4d42x', 'g0000000',
		);

		foreach($rejected as $text)
		{
			self::assertNull(Address::toHex($text), var_export($text, true).' is not an address');
		}
	}

	/**
	 * A visitor is shown a dotted address when the stored form is IPv4-mapped
	 * and the shortest IPv6 spelling otherwise; the admin list and the
	 * .htaccess export both read this.
	 */
	public function testDisplayIsDottedForIpv4AndCompressedForIpv6()
	{
		self::assertSame('10.77.66.65', Address::toDisplay(self::V4));
		self::assertSame('127.0.0.1', Address::toDisplay(self::LOCALHOST));
		self::assertSame('2001:db8::1', Address::toDisplay('20010db8000000000000000000000001'));
		self::assertSame('::', Address::toDisplay(Address::MIN));
		self::assertSame('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', Address::toDisplay(Address::MAX));
	}

	/**
	 * The stored form is what login.php, users.php and add_ban() compare
	 * against with plain equality, so it has to be byte-identical to what
	 * eIPHandler::ipEncode() has always written.
	 */
	public function testEncodedFormMatchesIpEncode()
	{
		self::assertSame('0000:0000:0000:0000:0000:ffff:0a4d:4241', Address::toEncoded(self::V4));
		self::assertSame('2001:0db8:0000:0000:0000:0000:0000:0001', Address::toEncoded('20010db8000000000000000000000001'));
		self::assertSame(\e107::getIPHandler()->ipEncode('10.77.66.65'), Address::toEncoded(self::V4));
		self::assertSame(\e107::getIPHandler()->ipEncode('2001:db8::1'), Address::toEncoded('20010db8000000000000000000000001'));
		self::assertSame(self::V4, Address::toHex(Address::toEncoded(self::V4)));
	}

	/**
	 * Both strings are numeric to PHP (the first is 1e0 with padding), so
	 * the comparison operators would call the larger one smaller. Everything
	 * in the engine orders with compare() for that reason, and this pins it.
	 */
	public function testHexOrderIsStringOrderNotNumericOrder()
	{
		$high = '00000000000000001e00000000000000';
		$low  = '00000000000000000000000000000002';

		self::assertTrue($high < $low, 'PHP itself reads these as floats and gets the order backwards');
		self::assertGreaterThan(0, Address::compare($high, $low), 'compare() has to give the numeric order of the addresses');
		self::assertLessThan(0, Address::compare($low, $high));
		self::assertSame(0, Address::compare(self::V4, self::V4));
	}

	/**
	 * The set compiler walks range boundaries with succ() and pred(). A carry
	 * has to ripple through every nibble, and the two ends of the address
	 * space have no neighbour, which is the case that hung mod_antiloris.
	 */
	public function testIncrementAndDecrementCarryAndStopAtTheEdges()
	{
		self::assertSame('00000000000000000000ffff0a4d4242', Address::succ(self::V4));
		self::assertSame('00000000000000000000ffff0a4d4240', Address::pred(self::V4));
		self::assertSame('00000000000000000000ffff0a4d4300', Address::succ('00000000000000000000ffff0a4d42ff'));
		self::assertSame('00000000000000000000ffff0a4d42ff', Address::pred('00000000000000000000ffff0a4d4300'));
		self::assertSame('00000000000000000000000000000000', Address::pred('00000000000000000000000000000001'));
		self::assertNull(Address::succ(Address::MAX), 'nothing follows the all-ones address');
		self::assertNull(Address::pred(Address::MIN), 'nothing precedes the all-zeros address');
	}

	/**
	 * Candidate order inside a segment is by width, and width is end minus
	 * start, so the subtraction has to borrow correctly across nibbles.
	 */
	public function testSubtractionBorrowsAcrossNibbles()
	{
		self::assertSame('000000000000000000000000000000ff', Address::sub('00000000000000000000ffff0a4d42ff', '00000000000000000000ffff0a4d4200'));
		self::assertSame('00000000000000000000000000000001', Address::sub('00000000000000000000ffff0a4d4300', '00000000000000000000ffff0a4d42ff'));
		self::assertSame(Address::MIN, Address::sub(self::V4, self::V4));
		self::assertSame(Address::MAX, Address::sub(Address::MAX, Address::MIN));
		self::assertSame('000000000000000000000000ffffffff', Address::sub('00000000000000000000ffff00000000', '00000000000000000000fffe00000001'));
	}
}
