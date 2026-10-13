<?php

/**
 * A request past the flood threshold whose address another request has already banned.
 */
class FloodBanCest
{
	const ADDRESS = '203.0.113.83';

	/** {@see ADDRESS} as eIPHandler stores it in both tables. */
	const STORED_ADDRESS = '0000:0000:0000:0000:0000:ffff:cb00:7153';

	public function aRequestOverTheThresholdStopsWhenItsAddressIsAlreadyBanned(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->haveInDatabase('e107_online', array(
			'online_timestamp' => time(),
			'online_flag'      => 0,
			'online_user_id'   => '0',
			'online_ip'        => self::STORED_ADDRESS,
			'online_location'  => '/index.php',
			'online_pagecount' => 150,
			'online_active'    => 0,
			'online_agent'     => '',
			'online_language'  => 'en',
		));
		$I->haveInDatabase('e107_banlist', array(
			'banlist_ip'         => self::STORED_ADDRESS,
			'banlist_bantype'    => -2,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'FloodBanCest',
			'banlist_notes'      => '',
		));

		$I->haveHttpHeader('X-Forwarded-For', self::ADDRESS);
		$I->amOnPage('/index.php');
		$I->deleteHeader('X-Forwarded-For');

		$I->assertSame('', trim($I->grabPageSource()), 'the request stops as the one that wrote the ban did, rather than being served the page');
		$I->seeNumRecords(1, 'e107_banlist', array('banlist_ip' => self::STORED_ADDRESS));
	}
}
