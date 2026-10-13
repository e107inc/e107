<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A custom page that names {UPLOADFILE} shows its refusal to a visitor who may not upload, and its form to one who may.
 */
class UploadfileUploadClassCest
{
	/** @var string */
	private $enabledWas = '';

	/** @var string */
	private $classWas = '';

	public function _before(AcceptanceTester $I)
	{
		$this->enabledWas = $I->haveSitePref('upload_enabled', 0);
		$this->classWas = $I->haveSitePref('upload_class', 255);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('upload_enabled', $this->enabledWas === '' ? null : $this->enabledWas);
		$I->haveSitePref('upload_class', $this->classWas === '' ? null : $this->classWas);
		$I->dropForumProbe();
	}

	public function aVisitorWhoMayNotUploadSeesTheRestOfThePage(AcceptanceTester $I)
	{
		$I->amOnPage('/page.php?id='.$this->haveUploadPage($I));

		$I->see('Disabled after');
		$I->dontSeeElement('input[type=file]');
	}

	public function aGuestWhoMayUploadSeesTheForm(AcceptanceTester $I)
	{
		$I->haveSitePref('upload_enabled', 1);
		$I->haveSitePref('upload_class', 0);

		$I->amOnPage('/page.php?id='.$this->haveUploadPage($I));

		$I->see('Add Another File');
		$I->seeElement('input[type=file]');
	}

	/**
	 * @return int the page id
	 */
	private function haveUploadPage(AcceptanceTester $I)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Upload form',
			'page_sef'       => '',
			'page_chapter'   => 0,
			'page_text'      => 'Before {UPLOADFILE=|nowarn} after',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
		));
	}
}
