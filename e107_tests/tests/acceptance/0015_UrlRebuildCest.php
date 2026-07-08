<?php

/**
 * Regression test for discussion #5793: rebuilding SEF URLs for a core module
 * (Custom Pages) from Admin > Site URLs must not fail with "Invalid Generator
 * identifier" / "Missing Generator data".
 *
 * The identifier allowlist added by the SQL-injection hardening was built only
 * from e107::getUrlConfig('generate'), which reads plugin e_url.php $generate
 * properties. Only the download plugin defines one, so every core module's
 * generator identifiers (page, news, ...) were rejected and the Rebuild button
 * errored out.
 */
class UrlRebuildCest
{
	const SENTINEL = 'sentinel-unchanged-5793';

	public function _before(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('authsubmit');
	}

	public function rebuildRegeneratesCustomPageSef(AcceptanceTester $I)
	{
		$I->wantTo('Rebuild SEF URLs for Custom Pages without a generator-config error');

		$pageId = $I->haveInDatabase('e107_page', array(
			'page_title' => 'My Test Custom Page',
			'page_sef'   => self::SENTINEL,
			'page_text'  => 'regression seed for #5793',
		));

		$I->amOnPage('/e107_admin/eurl.php?mode=main&action=config');
		$I->seeElement("button[name='rebuild[page]']");
		$I->click("button[name='rebuild[page]']");

		$I->dontSee('Invalid Generator identifier');
		$I->dontSee('Missing Generator data');

		// The Rebuild must actually run: page_sef is regenerated from page_title,
		// so the seeded sentinel is gone.
		$I->dontSeeInDatabase('e107_page', array(
			'page_id'  => $pageId,
			'page_sef' => self::SENTINEL,
		));
	}
}
