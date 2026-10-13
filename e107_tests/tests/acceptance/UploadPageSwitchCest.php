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
	/** @var string */
	private $enabledWas = '';

	/** @var string */
	private $classWas = '';

	public function _before(AcceptanceTester $I)
	{
		$this->enabledWas = $I->haveSitePref('upload_enabled', 0);
		$this->classWas = $I->haveSitePref('upload_class', 0);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('upload_enabled', $this->enabledWas === '' ? null : $this->enabledWas);
		$I->haveSitePref('upload_class', $this->classWas === '' ? null : $this->classWas);
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
