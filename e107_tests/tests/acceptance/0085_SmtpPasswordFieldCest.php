<?php

/**
 * The mail preferences carry an SMTP password, and the form that edits it used
 * to render the stored one as the field's value.
 *
 * mailoutAdminClass::mailerPrefsTable() draws that field, e_form::password()
 * writes whatever value it is handed into the value attribute, and two pages
 * draw the table: Mailout's own preferences tab and the core Preferences page.
 * The credential was therefore in the HTML of both.
 *
 * Blanking the field would trade the leak for something worse. It sits under an
 * smtp_username text input, which is the arrangement a password manager is
 * built to recognise, and a manager that fills an empty password field there
 * offers the administrator's own site password; the next save would put that in
 * place of the mail credential. So the field renders a fixed mask when a
 * password is stored, and both save paths read the mask back as an instruction
 * to leave the stored password alone.
 *
 * That instruction is the half that has to be pinned hardest: a page that
 * understood the mask and a page that did not would differ by one silent
 * overwrite of a working credential with a row of bullets. Every case here
 * therefore reads the SitePrefs row itself rather than trusting the form.
 *
 * @see e107_handlers/mailout_admin_class.php  mailoutAdminClass::smtpPasswordFieldValue()
 * @see e107_admin/mailout.php                 mailout_main_ui::saveMailPrefs()
 * @see e107_admin/prefs.php                   the updateprefs block
 */
class SmtpPasswordFieldCest
{
	const MAILOUT = '/e107_admin/mailout.php?mode=prefs&action=prefs';

	const PREFERENCES = '/e107_admin/prefs.php';

	/** The Mailout preferences form. */
	const MAILOUT_FORM = '#mailsettingsform';

	/** The password field, wherever it is drawn. */
	const FIELD = 'input[name=smtp_password]';

	/** Submits the core Preferences form from beside the mail settings. */
	const PREFERENCES_SUBMIT = '#updateprefs-email';

	/** mailoutAdminClass::SMTP_PASSWORD_UNCHANGED, spelt out so a change to it is a failure here. */
	const MASK = '••••••••';

	/** Distinctive enough that finding it in a page or a preference row means something. */
	const SMTP_PASSWORD = 'Smtp-Field-Probe-Secret';

	const SMTP_USERNAME = 'smtp-field-probe-user';

	/** The row core preferences live in, and the column that holds them. */
	const PREFS_TABLE = 'e107_core';

	const PREFS_COLUMN = 'e107_value';

	const PREFS_ROW = 'SitePrefs';

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	/**
	 * The leak itself. The assertion is on the whole page rather than on the field,
	 * because the value attribute is only where it was found: nothing else on the
	 * preferences tab has any business repeating it either.
	 */
	public function theMailoutPreferencesFormDoesNotRenderTheStoredSmtpPassword(AcceptanceTester $I)
	{
		$was = $this->haveSmtpPassword($I, self::SMTP_PASSWORD);
		$stored = '';
		$source = '';
		$value = '';

		try
		{
			$stored = $this->grabStoredPreferences($I);

			$I->amOnPage(self::MAILOUT);

			$source = $I->grabPageSource();
			$value = $I->grabValueFrom(self::MAILOUT_FORM . ' ' . self::FIELD);
		}
		finally
		{
			$this->haveMailPreferences($I, $was);
		}

		$I->assertStringContainsString($this->storedAs(self::SMTP_PASSWORD), $stored,
			'The password has to reach the preferences, or the page has nothing to leak');
		$I->assertStringNotContainsString(self::SMTP_PASSWORD, $source,
			'The stored SMTP password was rendered in the Mailout preferences page');
		$I->assertSame(self::MASK, $value,
			'A stored password should render as the mask, so a password manager finds the field filled');
	}

	/**
	 * The other half. Submitting the form with the field as the page drew it must
	 * leave the stored password alone, or the fix above would cost the site its
	 * mail credential on the very next save.
	 */
	public function savingTheMailoutPreferencesUntouchedKeepsTheStoredSmtpPassword(AcceptanceTester $I)
	{
		$was = $this->haveSmtpPassword($I, self::SMTP_PASSWORD);
		$before = '';
		$after = '';

		try
		{
			$before = $this->grabStoredPreferences($I);

			$I->amOnPage(self::MAILOUT);
			$I->submitForm(self::MAILOUT_FORM, array(), 'updateprefs');

			$after = $this->grabStoredPreferences($I);
		}
		finally
		{
			$this->haveMailPreferences($I, $was);
		}

		$I->assertStringContainsString($this->storedAs(self::SMTP_PASSWORD), $before,
			'The password has to reach the preferences, or this proves nothing');
		$I->assertStringContainsString($this->storedAs(self::SMTP_PASSWORD), $after,
			'Saving the Mailout preferences without touching the field changed the stored SMTP password');
		$I->assertStringNotContainsString($this->storedAs(self::MASK), $after,
			'The mask was stored as the SMTP password');
	}

	/**
	 * Reading an untouched field as "keep what is stored" must not take away the
	 * only way to withdraw a credential the site should no longer hold.
	 */
	public function emptyingTheMailoutPasswordFieldStillClearsTheStoredSmtpPassword(AcceptanceTester $I)
	{
		$was = $this->haveSmtpPassword($I, self::SMTP_PASSWORD);
		$after = '';
		$value = 'not read';

		try
		{
			$this->haveMailPreferences($I, array('smtp_password' => ''));

			$after = $this->grabStoredPreferences($I);

			$I->amOnPage(self::MAILOUT);

			$value = $I->grabValueFrom(self::MAILOUT_FORM . ' ' . self::FIELD);
		}
		finally
		{
			$this->haveMailPreferences($I, $was);
		}

		$I->assertStringContainsString($this->storedAs(''), $after,
			'Emptying the field no longer clears the stored SMTP password');
		$I->assertSame('', $value,
			'With nothing stored there is nothing to mask, so the field should come back empty');
	}

	/**
	 * The core Preferences page draws the same table through the same helper but
	 * saves through a loop of its own, which writes every posted key to the
	 * preference of that name. Left to itself it would store the mask.
	 */
	public function theCorePreferencesPageMasksTheSmtpPasswordAndKeepsItOnSave(AcceptanceTester $I)
	{
		$was = $this->haveSmtpPassword($I, self::SMTP_PASSWORD);
		$source = '';
		$value = '';
		$after = '';

		try
		{
			$I->amOnPage(self::PREFERENCES);

			$source = $I->grabPageSource();
			$value = $I->grabValueFrom(self::FIELD);

			$I->click(self::PREFERENCES_SUBMIT);

			$after = $this->grabStoredPreferences($I);
		}
		finally
		{
			$this->haveMailPreferences($I, $was);
		}

		$I->assertStringNotContainsString(self::SMTP_PASSWORD, $source,
			'The stored SMTP password was rendered in the core Preferences page');
		$I->assertSame(self::MASK, $value,
			'The core Preferences page should mask a stored password the way Mailout does');
		$I->assertStringContainsString($this->storedAs(self::SMTP_PASSWORD), $after,
			'Saving the core Preferences without touching the field changed the stored SMTP password');
		$I->assertStringNotContainsString($this->storedAs(self::MASK), $after,
			'The core Preferences page stored the mask as the SMTP password');
	}

	/**
	 * Put a password in the preferences and hand back what the fields held before.
	 *
	 * @param AcceptanceTester $I
	 * @param string $password
	 * @return array ready to be handed back to haveMailPreferences()
	 */
	private function haveSmtpPassword(AcceptanceTester $I, $password)
	{
		return $this->haveMailPreferences($I, array(
			'smtp_username' => self::SMTP_USERNAME,
			'smtp_password' => $password,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $values mail preference fields to leave set, keyed by form field name
	 * @return array what those same fields held before, ready to be handed back in
	 */
	private function haveMailPreferences(AcceptanceTester $I, array $values)
	{
		$I->amOnPage(self::MAILOUT);

		$was = array();

		foreach($values as $field => $value)
		{
			$was[$field] = $I->grabValueFrom(self::MAILOUT_FORM . ' [name=' . $field . ']');
		}

		$I->submitForm(self::MAILOUT_FORM, $values, 'updateprefs');

		return $was;
	}

	/**
	 * Core preferences live var_export()ed inside one e107_core row, so there is
	 * nothing for seeInDatabase() to match on. Read the row and search it.
	 *
	 * @param AcceptanceTester $I
	 * @return string the serialised preferences
	 */
	private function grabStoredPreferences(AcceptanceTester $I)
	{
		$prefs = $I->grabFromDatabase(self::PREFS_TABLE, self::PREFS_COLUMN,
			array('e107_name' => self::PREFS_ROW));

		$I->assertNotEmpty($prefs, 'The SitePrefs row carried no preferences');

		return $prefs;
	}

	/**
	 * @param string $password
	 * @return string the smtp_password entry as the SitePrefs row spells it
	 */
	private function storedAs($password)
	{
		return "'smtp_password' => '" . $password . "'";
	}
}
