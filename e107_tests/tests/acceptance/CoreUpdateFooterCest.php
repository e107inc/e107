<?php

/**
 * What the update page's footer holds once an administrator has run a core update from it.
 */
class CoreUpdateFooterCest
{
	/** LAN_UPDATE_7 around the start of LAN_UPDATE_4, which a core update that ran answers with. */
	const EXECUTED = 'Executed Update from 2.x to';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	public function theFooterAfterACoreUpdateNamesNoStatisticsAddress(AcceptanceTester $I)
	{
		$I->wantTo('run a pending core update and see its page ask the browser for nothing at e107.org');
		$I->expectOutboundRequest('e107.org');

		$this->leaveTheCoreUpdatePending($I);

		$I->amOnCoreUpdatePage();
		$I->click(array('name' => 'update_core[20x_to_latest]'));

		$I->see(self::EXECUTED);
		$I->seeInSource('</html>');
		$I->dontSeeInSource(\Helper\Acceptance::STATISTICS_ADDRESS);
	}

	private function leaveTheCoreUpdatePending(AcceptanceTester $I)
	{
		$I->haveSitePref('flood_protect', 1);
	}
}
