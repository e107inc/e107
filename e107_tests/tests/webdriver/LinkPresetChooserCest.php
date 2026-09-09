<?php

/**
 * The browser half of the "Predefined link" chooser in Admin Area » Settings »
 * Navigation. The server applies the preset on save whatever the browser did, which
 * is what 0095_LinkPresetCest measures; what only a browser can say is that the
 * administrator sees the URL, the name and the user class arrive in the fields before
 * they save, and that the answer to the overwrite question is honoured.
 *
 * The chooser resets itself to none afterwards, either way. That is not cosmetic: the
 * server applies whatever key the form posts, so a chooser left on Logout after a
 * refused overwrite would put the preset back on the next save.
 *
 * Master only: release/v2.3.x has no webdriver suite.
 */
class LinkPresetChooserCest
{
	const CREATE_PATH = '/e107_admin/links.php?mode=main&action=create';

	/** The dump's News entry, whose link_owner and link_sefurl make e107::url() own its address. */
	const GENERATED_LINK_PATH = '/e107_admin/links.php?mode=main&action=edit&id=6';

	/** The key and the three columns of the one preset core ships. */
	const PRESET = 'logout';
	const PRESET_NAME = 'Logout';
	const PRESET_URL = 'index.php?logout&e-token={E_TOKEN}';
	const PRESET_CLASS = '253';

	/** LAN_LINKS_PRESET_OVERWRITE followed by LAN_JSCONFIRM. */
	const OVERWRITE_CONFIRM = 'This will replace the URL and name already entered. Are you sure?';

	/** LAN_AUTO_GENERATED, the label standing where a generated link's URL field would be. */
	const AUTO_GENERATED = 'Auto-generated';

	/** A URL the administrator typed before reaching for the chooser. */
	const TYPED_URL = 'contact.php?linkpreset';

	/** Long enough for a slow container, short enough to fail a test rather than a suite. */
	const TIMEOUT = 10;

	public function _before(\WebDriverTester $I)
	{
		$I->loginAsAdmin();
	}

	public function pickingAPresetFillsTheFieldsInFrontOfTheAdministrator(\WebDriverTester $I)
	{
		$I->wantTo('pick Logout and watch the URL, the name and the user class arrive');
		$I->amOnPage(self::CREATE_PATH);

		$I->selectOption('#link-preset', self::PRESET);

		$this->seeThePresetInTheFields($I);
	}

	public function decliningTheOverwriteLeavesTheTypedUrlAlone(\WebDriverTester $I)
	{
		$I->wantTo('refuse to have a URL I typed replaced');
		$I->amOnPage(self::CREATE_PATH);
		$I->fillField('#link-url', self::TYPED_URL);

		$I->selectOption('#link-preset', self::PRESET);

		$I->seeInPopup(self::OVERWRITE_CONFIRM);
		$I->cancelPopup();

		$I->assertSame(self::TYPED_URL, $I->grabValueFrom('#link-url'), 'a refused preset wrote anyway');
		$I->assertSame('', $I->grabValueFrom('#link-preset'),
			'a refused preset left in the chooser is one the server applies on the next save');
	}

	public function acceptingTheOverwriteFillsOverTheTypedUrl(\WebDriverTester $I)
	{
		$I->wantTo('agree to have a URL I typed replaced');
		$I->amOnPage(self::CREATE_PATH);
		$I->fillField('#link-url', self::TYPED_URL);

		$I->selectOption('#link-preset', self::PRESET);

		$I->seeInPopup(self::OVERWRITE_CONFIRM);
		$I->acceptPopup();

		$this->seeThePresetInTheFields($I);
	}

	/**
	 * A link whose address e107::url() generates has no URL field to fill, so it is
	 * offered no chooser either.
	 */
	public function aLinkWithAGeneratedAddressIsOfferedNoChooser(\WebDriverTester $I)
	{
		$I->wantTo('open a link whose URL is generated and find no chooser above it');
		$I->amOnPage(self::GENERATED_LINK_PATH);

		$I->see(self::AUTO_GENERATED);
		$I->dontSeeElement('#link-preset');
	}

	/**
	 * @param \WebDriverTester $I
	 * @return void
	 */
	private function seeThePresetInTheFields(\WebDriverTester $I)
	{
		$filled = \Test\Poll::until(function () use ($I)
		{
			return $I->grabValueFrom('#link-url') !== '';
		}, self::TIMEOUT);

		$I->assertTrue($filled, 'the chooser put nothing in the URL field');
		$I->assertSame(self::PRESET_URL, $I->grabValueFrom('#link-url'));
		$I->assertSame(self::PRESET_NAME, $I->grabValueFrom('#link-name'));
		$I->assertSame(self::PRESET_CLASS, $I->grabValueFrom('#link-class'));
		$I->assertSame('', $I->grabValueFrom('#link-preset'),
			'a chooser left on its preset would have the server apply it again on save');
	}
}
