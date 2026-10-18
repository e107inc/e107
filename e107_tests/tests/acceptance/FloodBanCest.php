<?php

/**
 * The flood check adds a ban only from the request that flags the address, once per counting window.
 */
class FloodBanCest
{
	public function aRequestOverTheThresholdAddsNoBanWhenItsAddressIsAlreadyFlagged(AcceptanceTester $I)
	{
		$stored = '0000:0000:0000:0000:0000:ffff:cb00:7153';
		$this->haveOnlineRow($I, '0', $stored, 150, 1, time());

		$this->requestAsGuestFrom($I, '203.0.113.83');

		$I->seeInDatabase('e107_online', array('online_ip' => $stored, 'online_pagecount' => 151));
		$I->seeNumRecords(0, 'e107_banlist', array('banlist_ip' => $stored));
	}

	public function theRequestThatFlagsTheAddressAddsTheBanAndStops(AcceptanceTester $I)
	{
		$stored = '0000:0000:0000:0000:0000:ffff:cb00:7154';
		$this->haveOnlineRow($I, '0', $stored, 150, 0, time());

		$this->requestAsGuestFrom($I, '203.0.113.84');

		$I->assertSame('', trim($I->grabPageSource()));
		$I->seeNumRecords(1, 'e107_banlist', array('banlist_ip' => $stored));
		$I->seeInDatabase('e107_online', array('online_ip' => $stored, 'online_flag' => 1));
	}

	public function aNewCountingWindowClearsTheFlag(AcceptanceTester $I)
	{
		$stored = '0000:0000:0000:0000:0000:ffff:cb00:7155';
		$this->haveOnlineRow($I, '0', $stored, 10, 1, time() - 600);

		$this->requestAsGuestFrom($I, '203.0.113.85');

		$I->seeInDatabase('e107_online', array('online_ip' => $stored, 'online_pagecount' => 1, 'online_flag' => 0));
	}

	public function aMembersNewCountingWindowClearsTheFlag(AcceptanceTester $I)
	{
		$member = $this->haveLoggedInMember($I, 'floodwindow');
		$this->haveOnlineRow($I, $member, '0000:0000:0000:0000:0000:ffff:cb00:7156', 10, 1, time() - 600);

		$this->requestFrom($I, '203.0.113.86');

		$I->seeInDatabase('e107_online', array('online_user_id' => $member, 'online_pagecount' => 1, 'online_flag' => 0));
	}

	public function aMemberAlreadyFlaggedAtTheSameAddressAddsNoBan(AcceptanceTester $I)
	{
		$member = $this->haveLoggedInMember($I, 'floodstayer');
		$stored = '0000:0000:0000:0000:0000:ffff:cb00:7159';
		$this->haveOnlineRow($I, $member, $stored, 250, 1, time());

		$this->requestFrom($I, '203.0.113.89');

		$I->seeInDatabase('e107_online', array('online_user_id' => $member, 'online_pagecount' => 251));
		$I->seeNumRecords(0, 'e107_banlist', array('banlist_ip' => $stored));
	}

	public function aMemberFlaggedAtOneAddressIsBannedAtTheNextOne(AcceptanceTester $I)
	{
		$member = $this->haveLoggedInMember($I, 'floodmover');
		$this->haveOnlineRow($I, $member, '0000:0000:0000:0000:0000:ffff:cb00:7157', 250, 1, time());

		$this->requestFrom($I, '203.0.113.88');

		$I->assertSame('', trim($I->grabPageSource()));
		$I->seeNumRecords(1, 'e107_banlist', array('banlist_ip' => '0000:0000:0000:0000:0000:ffff:cb00:7158'));
	}

	/**
	 * An online row for the visitor, as eIPHandler stores the address.
	 *
	 * @param AcceptanceTester $I
	 * @param string $userId    '0' for a guest, or the member's "id.name"
	 * @param string $stored    the address in its stored form
	 * @param int    $pagecount hits counted in the window
	 * @param int    $flag      online_flag
	 * @param int    $since     when the window started
	 * @return void
	 */
	private function haveOnlineRow(AcceptanceTester $I, $userId, $stored, $pagecount, $flag, $since)
	{
		$I->haveInDatabase('e107_online', array(
			'online_timestamp' => $since,
			'online_flag'      => $flag,
			'online_user_id'   => $userId,
			'online_ip'        => $stored,
			'online_location'  => '/index.php',
			'online_pagecount' => $pagecount,
			'online_active'    => 0,
			'online_agent'     => '',
			'online_language'  => 'en',
		));
	}

	/**
	 * A member logged in through the form, with the online row the login wrote taken away.
	 *
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @return string the member's online_user_id
	 */
	private function haveLoggedInMember(AcceptanceTester $I, $name)
	{
		$id = $I->haveMember($name, $name.'-password');
		$I->loginAsMember($name, $name.'-password');

		$member = $id.'.'.$name;
		$I->getDbModule()->_getDbh()
			->prepare('DELETE FROM e107_online WHERE online_user_id = ?')
			->execute(array($member));

		return $member;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $address the address the harness forwards the request for
	 * @return void
	 */
	private function requestAsGuestFrom(AcceptanceTester $I, $address)
	{
		$I->resetAllCookies();
		$this->requestFrom($I, $address);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $address the address the harness forwards the request for
	 * @return void
	 */
	private function requestFrom(AcceptanceTester $I, $address)
	{
		$I->haveHttpHeader('X-Forwarded-For', $address);
		$I->amOnPage('/index.php');
		$I->deleteHeader('X-Forwarded-For');
	}
}
