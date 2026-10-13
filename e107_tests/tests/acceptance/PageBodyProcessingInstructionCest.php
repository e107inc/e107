<?php

/**
 * Markup a page's author writes inside a processing instruction meets the cleaner like any other markup.
 */
class PageBodyProcessingInstructionCest
{
	/** An administrator who holds the Pages permission and nothing else. */
	const PAGES_ADMIN = 'pi262pages';

	const PAGES_ADMIN_PASS = 'Pi262PagesPass';

	public function _before(AcceptanceTester $I)
	{
		$I->haveMember(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS, array('user_admin' => 1, 'user_perms' => '5.'));
	}

	public function aProcessingInstructionCarriesNoHandler(AcceptanceTester $I)
	{
		$page = $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Instruction pi262',
			'page_sef'       => 'instruction-pi262',
			'page_chapter'   => 0,
			'page_text'      => 'Body',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
			'page_fields'    => '',
		));

		$I->amSignedInAs(self::PAGES_ADMIN);
		foreach(array('<?x', "<\r?x") as $start)
		{
			$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
			$I->submitForm('#core-page-form', array(
				'page_text' => '<p>Before'.$start.' <b id="pi262" onclick="pi262()">Hidden</b>?> after</p>',
			), 'etrigger_submit');

			$I->amOnPage('/page.php?id='.$page);
			$I->dontSeeElement('[onclick="pi262()"]');
			$I->see('after');
		}
	}
}
