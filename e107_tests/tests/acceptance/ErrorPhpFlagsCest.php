<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * error.php hands class2.php the flags it boots with: an error page sends nobody off to complete their profile, and it still counts towards Who's Online and the flood check.
 *
 * @see https://github.com/e107inc/e107/issues/6900
 */
class ErrorPhpFlagsCest
{
	const ERROR_PAGE = '/error.php?404';

	const ONLINE_TABLE = 'e107_online';

	public function _after(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->restoreSitePrefs();
		$I->dropForumProbe();
	}

	public function aMemberWithAnIncompleteProfileIsShownTheErrorPage(AcceptanceTester $I)
	{
		$I->amSignedInWithAnIncompleteProfile();

		$I->amOnPage(self::ERROR_PAGE);

		$I->dontSeeTheDemandToCompleteTheProfile();
		$I->seeResponseCodeIs(404);
	}

	public function aGuestsErrorPageStillCountsTowardsTheFloodCheck(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->amOnPage(self::ERROR_PAGE);
		$counted = $this->guestPagesCounted($I);

		$I->amOnPage(self::ERROR_PAGE);

		$I->seeResponseCodeIs(404);
		$I->assertSame($counted + 1, $this->guestPagesCounted($I));
	}

	/**
	 * @param AcceptanceTester $I
	 * @return int the page count on the guest row that Who's Online shows on the error page
	 */
	private function guestPagesCounted(AcceptanceTester $I)
	{
		return (int) $I->grabFromDatabase(self::ONLINE_TABLE, 'online_pagecount', array('online_user_id' => '0', 'online_location like' => '%error.php%'));
	}
}
