<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * writeBanListFiles() turns the banlist table into the files eIPHandler
 * prefix-matches every visitor against.
 */
class banlistManagerTest extends \Codeception\Test\Unit
{
	/** @var banlistManager */
	private $mgr;

	/** @var array banlist_id values written by this test */
	private $insertedIds = array();

	const BAN_TYPE = -2;
	const IP_WILDCARD = '10.77.66.*';
	const IP_IN_WILDCARD = '10.77.66.65';
	const IP_WILDCARD_TOKEN = '0000:0000:0000:0000:0000:ffff:0a4d:42';
	const IP_WILDCARD_WHITELISTED = '10.77.99.*';
	const IP_WILDCARD_WHITELISTED_TOKEN = '0000:0000:0000:0000:0000:ffff:0a4d:63';
	const IP_WILDCARD_WHITELISTED_STORED = '10.77.99.';
	const IP_IN_WHITELISTED_WILDCARD = '10.77.99.65';
	const IP_WILDCARD_LEGACY = '10.77.55.*';
	const IP_WILDCARD_LEGACY_TOKEN = '0000:0000:0000:0000:0000:ffff:0a4d:37';
	const WHITELIST_TYPE = 100;
	const LEGACY_TYPE = 0;
	const IP_NOBODY_BANNED = '203.0.113.9';

	protected function _before()
	{
		require_once(e_HANDLER.'iphandler_class.php');

		$this->mgr = new banlistManager();
	}

	protected function _after()
	{
		if(!empty($this->insertedIds))
		{
			e107::getDb()->delete('banlist', '`banlist_id` IN ('.implode(',', $this->insertedIds).')');
			$this->insertedIds = array();

			// The ban files are generated from the table, and the run under test
			// regenerated them with these rows in. Put them back as they were.
			$this->mgr->writeBanListFiles('ip');
		}
	}

	/**
	 * @param string $ip
	 * @param int $type
	 * @return int banlist_id
	 */
	private function haveBan($ip, $type = self::BAN_TYPE)
	{
		$id = e107::getDb()->insert('banlist', array(
			'banlist_id'         => 0,
			'banlist_ip'         => $ip,
			'banlist_bantype'    => $type,
			'banlist_datestamp'  => time() - 3600,
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'e107help banlist file probe',
			'banlist_notes'      => '',
		));

		self::assertNotEmpty($id, 'could not write the banlist row this test needs');
		$this->insertedIds[] = (int) $id;

		return (int) $id;
	}

	/**
	 * The match tokens of the generated IP ban file, in the order written.
	 *
	 * @return array
	 */
	private function banFileTokens()
	{
		$file = e107::getIPHandler()->getConfigDir().eIPHandler::BAN_FILE_IP_NAME.eIPHandler::BAN_FILE_EXTENSION;
		self::assertFileExists($file, 'writeBanListFiles() wrote no IP ban file');

		$tokens = array();
		foreach(file($file) as $line)
		{
			$parts = explode(' ', trim($line));
			if(count($parts) === 3) $tokens[] = $parts[0];
		}

		return $tokens;
	}

	/**
	 * eIPHandler compares the visitor's encoded address against each entry of
	 * this file, so a wildcard row has to reach it encoded. Written in the
	 * dotted form it was typed in, it prefixes nothing and bans nobody, while
	 * the admin screen goes on showing it as a live ban.
	 *
	 * A legacy row is a ban too. Its stored type is 0 rather than negative, and
	 * it is normalised to BAN_TYPE_UNKNOWN before anything is written, so it
	 * reaches the file on the same terms as every other ban.
	 */
	public function testWildcardBanIsWrittenAsAnEncodedPrefix()
	{
		$this->haveBan(self::IP_WILDCARD);
		$this->haveBan(self::IP_WILDCARD_LEGACY, self::LEGACY_TYPE);

		$this->mgr->writeBanListFiles('ip');

		$tokens = $this->banFileTokens();
		self::assertContains(self::IP_WILDCARD_TOKEN, $tokens,
			'a wildcard ban has to reach the file as the encoded prefix of its range');
		self::assertContains(self::IP_WILDCARD_LEGACY_TOKEN, $tokens,
			'and a legacy wildcard ban with it, since the type it is normalised to is a ban');
		self::assertSame(0, strpos(e107::getIPHandler()->ipEncode(self::IP_IN_WILDCARD), self::IP_WILDCARD_TOKEN),
			'and that prefix has to be the start of every encoded address in the range');
	}

	/**
	 * A whitelist entry is not a ban, and the ban type is the whole of what
	 * tells them apart. Whitelist rows are written into this file first and end
	 * the scan on a match, so a wildcard whitelist row that reaches it encoded
	 * stops every ban inside its range firing, with nothing on the banlist
	 * screen saying so. It keeps the stored form it has always had, which
	 * prefixes no encoded address and exempts nobody.
	 */
	public function testWildcardWhitelistRowIsWrittenAsStored()
	{
		$this->haveBan(self::IP_WILDCARD_WHITELISTED, self::WHITELIST_TYPE);

		$this->mgr->writeBanListFiles('ip');

		$tokens = $this->banFileTokens();
		self::assertNotContains(self::IP_WILDCARD_WHITELISTED_TOKEN, $tokens,
			'a wildcard whitelist row must not reach the file in the form the ban check matches');
		self::assertContains(self::IP_WILDCARD_WHITELISTED_STORED, $tokens,
			'it has to reach the file in the form it was stored in');
		self::assertNotSame(0, strpos(e107::getIPHandler()->ipEncode(self::IP_IN_WHITELISTED_WILDCARD), self::IP_WILDCARD_WHITELISTED_STORED),
			'and that form has to stay inert against the addresses in its range');
	}

	/**
	 * The other direction, and the reason the encoder is choosy. whatIsThis()
	 * calls anything built from hex digits, dots and wildcards an address, so
	 * all of these rows reach the file, and each has to arrive exactly as it
	 * does today: a host name and a plain stored address name no range to
	 * encode, an embedded wildcard would widen the ban to a /8, a wildcard
	 * inside an octet would shrink it to the single address 10.77.66.5, and an
	 * octet of two wildcards is not an octet ipEncode() can read.
	 */
	public function testAnAddressPatternThatCannotBeEncodedIsLeftAsStored()
	{
		$expected = array(
			'bad.cc'      => 'bad.cc',
			'10.66.66.33' => '10.66.66.33',
			'10.*.66.5'   => '10.',
			'10.77.66.5*' => '10.77.66.5',
			'10.77.66.**' => '10.77.66.',
		);
		foreach(array_keys($expected) as $stored)
		{
			$this->haveBan($stored);
		}

		$this->mgr->writeBanListFiles('ip');

		$tokens = $this->banFileTokens();
		foreach($expected as $stored => $token)
		{
			self::assertContains($token, $tokens, $stored.' has to reach the ban file exactly as it did before');
		}

		$visitor = e107::getIPHandler()->ipEncode(self::IP_NOBODY_BANNED);
		foreach($tokens as $token)
		{
			self::assertNotSame(0, strpos($visitor, $token),
				'no entry may match an address nobody banned, and this one does: '.$token);
		}
	}
}
