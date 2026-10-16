<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * upload.php sends a visitor away while public uploads are switched off.
 */
class UploadPageSwitchCest
{
	public function _before(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('upload_enabled', 0);
		$I->haveSitePrefUntilAfter('upload_class', 0);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
		$I->dropForumProbe();
	}

	public function thePageOpensWhileUploadsAreOn(AcceptanceTester $I)
	{
		$I->haveSitePref('upload_enabled', 1);

		$I->amOnPage('/upload.php');

		$I->seeInCurrentUrl('/upload.php');
	}

	public function aVisitorIsSentAwayWhileUploadsAreOff(AcceptanceTester $I)
	{
		$I->amOnPage('/upload.php');

		$I->dontSeeInCurrentUrl('/upload.php');
	}
}
