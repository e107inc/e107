<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Banlist;

use e107\Ip\Range;

/**
 * The admin form, the importer and the file writer all ask this class what
 * a typed or stored entry is. A kind decided wrongly is a row that the
 * screen lists as live and nothing enforces, which is issue #6245.
 */
class EntryTest extends \Test\Unit
{
	protected function _before()
	{
		require_once(e_HANDLER.'Ip/Address.php');
		require_once(e_HANDLER.'Ip/Range.php');
		require_once(e_HANDLER.'Banlist/Entry.php');
	}

	/**
	 * A single address is stored encoded, whatever spelling it arrived in,
	 * because login.php, users.php and add_ban() look rows up by that exact
	 * string.
	 */
	public function testSingleAddressesAreStoredEncoded()
	{
		$encoded = '0000:0000:0000:0000:0000:ffff:0a4d:4241';

		foreach(array('10.77.66.65', ' 10.77.66.65 ', $encoded, '::ffff:10.77.66.65', '0a4d4241', '10.77.66.65/32') as $text)
		{
			$entry = Entry::fromText($text);
			self::assertSame(Entry::ADDRESS, $entry->kind(), $text.' is one address');
			self::assertSame($encoded, $entry->stored(), $text.' is stored in the encoded form');
			self::assertInstanceOf(Range::class, $entry->range());
			self::assertTrue($entry->range()->isSingle());
		}

		self::assertSame('2001:0db8:0000:0000:0000:0000:0000:0001', Entry::fromText('2001:DB8::1')->stored());
	}

	/**
	 * A range is stored as written, lowercased and without whitespace, so
	 * the retrigger log's space-separated line can carry it and the list
	 * shows the admin what they typed.
	 */
	public function testRangesAreStoredAsWrittenWithoutWhitespace()
	{
		$cases = array(
			'10.77.66.*'                             => '10.77.66.*',
			'10.77.66.0/24'                          => '10.77.66.0/24',
			'2001:DB8::/32'                          => '2001:db8::/32',
			' 10.77.66.1 - 10.77.66.9 '              => '10.77.66.1-10.77.66.9',
			'0000:0000:0000:0000:0000:ffff:0a4d:42xx' => '0000:0000:0000:0000:0000:ffff:0a4d:42xx',
		);

		foreach($cases as $text => $stored)
		{
			$entry = Entry::fromText($text);
			self::assertSame(Entry::RANGE, $entry->kind(), $text.' is a range');
			self::assertSame($stored, $entry->stored());
			self::assertNotNull($entry->range());
			self::assertFalse($entry->range()->isSingle());
			self::assertSame($entry->range()->start(), Range::fromString($stored)->start(), 'the stored form parses back to the same range');
		}
	}

	/**
	 * makeEmailQuery() looks for exactly two shapes, the full address and
	 * *@domain, and makeDomainQuery() only ever builds *.suffix patterns, so
	 * those are the pattern shapes accepted. The address part of an email
	 * keeps its case; a domain does not have one.
	 */
	public function testEmailAndHostPatternsAreStoredRaw()
	{
		self::assertSame(Entry::EMAIL, Entry::fromText('user@example.com')->kind());
		self::assertSame('User@Example.com', Entry::fromText(' User@Example.com ')->stored());
		self::assertSame(Entry::EMAIL, Entry::fromText('*@example.com')->kind());
		self::assertSame('*@example.com', Entry::fromText('*@Example.COM')->stored());
		self::assertSame(Entry::HOST, Entry::fromText('*.example.com')->kind());
		self::assertSame('*.my-domain.co.uk', Entry::fromText('*.My-Domain.co.uk')->stored());
		self::assertSame(Entry::HOST, Entry::fromText('*.de')->kind());
		self::assertNull(Entry::fromText('*.de')->range());
	}

	/**
	 * Everything else is refused, and the reasons are the rows #6245 found
	 * listed as live: a bare host name matches no reverse-DNS pattern, a
	 * wildcard in the middle of an address is no range, and junk is junk.
	 */
	public function testEverythingElseIsInvalid()
	{
		$invalid = array(
			'', ' ', null, 'bad.cc', 'example.com', '10.*.66.5', '10.77.66.5*', '10.77.66.', '256.300.1.1',
			'*.-bad.com', '*.bad-.com', '*@', 'foo@', '@bar.com', 'user @example.com', 'http://example.com/',
			'*.example.com/', '*.*', '*', '1.2.3.5/30', '9.9.9.9-1.1.1.1', 'not an entry',
		);

		foreach($invalid as $text)
		{
			$entry = Entry::fromText($text);
			self::assertSame(Entry::INVALID, $entry->kind(), var_export($text, true).' is invalid');
			self::assertNull($entry->stored());
			self::assertNull($entry->range());
		}
	}
}
