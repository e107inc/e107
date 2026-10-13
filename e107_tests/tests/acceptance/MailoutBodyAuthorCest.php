<?php

/**
 * A mailout's body expands shortcodes only when an administrator wrote it, so saving one through the Mail screen
 * must record who wrote the body.
 */
class MailoutBodyAuthorCest
{
	const TITLE = 'MailoutBodyAuthorCest probe';

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	public function aMailoutSavedThroughTheMailScreenRecordsWhoWroteItsBody(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/mailout.php?mode=main&action=create');
		$I->fillField('mail_title', self::TITLE);
		$I->fillField('mail_subject', self::TITLE);
		$I->fillField('mail_body', 'Dear {USERNAME}');
		$I->click('button[name=etrigger_submit]');

		$other = $I->grabFromDatabase('e107_mail_content', 'mail_other', array('mail_title' => self::TITLE));
		$admin = $I->grabFromDatabase('e107_user', 'user_id', array('user_loginname' => 'admin'));

		$I->assertMatchesRegularExpression('/mail_body_author\W+(?:i:)?'.(int) $admin.'\b/', (string) $other);
	}
}
