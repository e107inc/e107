<?php

/**
 * What the Mailout edit form shows of an email's sender and copies, which are kept together in one stored array.
 */
class MailoutSavedEmailCest
{
	const EMAIL_ID = 99011;

	/** MAIL_STATUS_SAVED */
	const STATUS_SAVED = 20;

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	public function theFormShowsASenderNameWithAnApostrophe(AcceptanceTester $I)
	{
		$this->haveSavedEmail($I, array(
			'mail_sender_name'  => "O'Brien Ltd",
			'mail_sender_email' => 'obrien@example.invalid',
			'mail_bcopy_to'     => 'archive@example.invalid',
		));

		$I->amOnPage('/e107_admin/mailout.php?mode=main&action=edit&id=' . self::EMAIL_ID);

		$I->seeInField('mail_sender_name', "O'Brien Ltd");
		$I->seeInField('mail_bcopy_to', 'archive@example.invalid');
	}

	/**
	 * @param array $other the sender, copies and other details, stored as {@see e107MailManager::mailToDb()} writes them
	 */
	private function haveSavedEmail(AcceptanceTester $I, array $other)
	{
		$I->haveInDatabase('e107_mail_content', array(
			'mail_source_id'      => self::EMAIL_ID,
			'mail_content_status' => self::STATUS_SAVED,
			'mail_create_date'    => 1,
			'mail_creator'        => 1,
			'mail_create_app'     => 'core',
			'mail_title'          => 'Saved email probe',
			'mail_subject'        => 'Saved email probe',
			'mail_body'           => 'Saved email probe body',
			'mail_body_templated' => '',
			'mail_other'          => var_export($other, true),
		));
	}
}
