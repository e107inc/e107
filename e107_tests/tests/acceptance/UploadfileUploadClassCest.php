<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A custom page that names {UPLOADFILE} shows a visitor outside "who may upload" its refusal, not the form.
 */
class UploadfileUploadClassCest
{
	public function aVisitorWhoMayNotUploadSeesTheRestOfThePage(AcceptanceTester $I)
	{
		$id = $I->haveInDatabase('e107_page', array(
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

		$I->amOnPage('/page.php?id='.$id);

		$I->see('Disabled after');
		$I->dontSeeElement('input[type=file]');
	}
}
