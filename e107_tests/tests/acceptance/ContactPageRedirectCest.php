<?php

/**
 * contact.php sends a visitor home when the page has nothing for them: no form, no contact details, no prompt to sign in
 * and no info template of the theme's own.
 */
class ContactPageRedirectCest
{
	const VISIBILITY_EVERYONE = 0;

	const VISIBILITY_MEMBERS = 253;

	const NO_ONE = 255;

	const MAIN_ADMIN = 250;

	const SITE_THEME = 'bootstrap5';

	/** @var string|null the theme template this test wrote */
	private $themeTemplate;

	public function _before(AcceptanceTester $I)
	{
		$this->themeTemplate = null;
	}

	public function _after(AcceptanceTester $I)
	{
		if($this->themeTemplate !== null)
		{
			$I->deleteAppFile($this->themeTemplate);
		}

		$I->dropThemeFixtures();
		$I->restoreSitePrefs();
		$I->startFollowingRedirects();
	}

	public function aPageNoOneMayUseWithBlankFieldsSendsEveryoneHome(AcceptanceTester $I)
	{
		$this->haveContactPrefs($I, self::NO_ONE, self::MAIN_ADMIN, $this->blankFields());

		$this->seeContactPageRedirectsHome($I);
		$I->loginAsAdmin();
		$this->seeContactPageRedirectsHome($I);
	}

	public function aFormWithNobodyToWriteToAndBlankFieldsSendsEveryoneHome(AcceptanceTester $I)
	{
		$this->haveContactPrefs($I, self::VISIBILITY_EVERYONE, self::NO_ONE, $this->blankFields());

		$this->seeContactPageRedirectsHome($I);
		$I->loginAsAdmin();
		$this->seeContactPageRedirectsHome($I);
	}

	public function aMembersOnlyFormAsksAGuestToSignInAndShowsNoEmptyDetails(AcceptanceTester $I)
	{
		$this->haveContactPrefs($I, self::VISIBILITY_MEMBERS, self::MAIN_ADMIN, $this->blankFields());

		$this->seeSignInPromptAndNoDetails($I);
	}

	public function aMembersOnlyFormAsksAGuestToSignInOnTheInstallDefaults(AcceptanceTester $I)
	{
		$this->haveContactPrefs($I, self::VISIBILITY_MEMBERS, self::MAIN_ADMIN, array());

		$this->seeSignInPromptAndNoDetails($I);
	}

	public function oneFilledFieldIsShownToAVisitorNoOneMayWriteFrom(AcceptanceTester $I)
	{
		$fields = $this->blankFields();
		$fields['organization'] = 'Example Test Organisation';
		$this->haveContactPrefs($I, self::NO_ONE, self::MAIN_ADMIN, $fields);

		$I->amOnPage('/contact.php');
		$I->seeResponseCodeIs(200);
		$I->see('Example Test Organisation', '#contactInfo');
	}

	public function anInfoTemplateOfTheThemesOwnIsShownWithBlankFields(AcceptanceTester $I)
	{
		$this->haveContactPrefs($I, self::NO_ONE, self::MAIN_ADMIN, $this->blankFields());
		$this->themeTemplate = 'e107_themes/'.self::SITE_THEME.'/templates/contact_template.php';
		$I->writeAppFile($this->themeTemplate, "<?php\n\$CONTACT_TEMPLATE['info'] = '<p id=\"theme-contact-info\">Visit us at the harbour.</p>';\n");
		$I->haveSiteTheme(self::SITE_THEME);

		$I->amOnPage('/contact.php');
		$I->seeResponseCodeIs(200);
		$I->see('Visit us at the harbour.', '#theme-contact-info');
	}

	private function seeContactPageRedirectsHome(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();
		$I->amOnPage('/contact.php');
		$I->seeResponseCodeIs(301);
		$I->seeRedirectToTheHomePage();
		$I->startFollowingRedirects();
	}

	private function seeSignInPromptAndNoDetails(AcceptanceTester $I)
	{
		$I->amOnPage('/contact.php');
		$I->seeResponseCodeIs(200);
		$I->see('and signed-in to use this form');
		$I->dontSeeElement('#contactInfo');
	}

	/**
	 * @return array<string, string> every Site Contact Information field, blank, as a Preferences save stores them
	 */
	private function blankFields()
	{
		return array_fill_keys(array('organization', 'address', 'phone1', 'phone2', 'phone3', 'fax', 'email1', 'email2', 'coordinates', 'hours', 'message'), '');
	}

	/**
	 * @param int $visibility contact_visibility
	 * @param int $contacts sitecontacts
	 * @param array<string, string> $fields contact_info
	 */
	private function haveContactPrefs(AcceptanceTester $I, $visibility, $contacts, array $fields)
	{
		$I->haveSitePrefUntilAfter('contact_visibility', $visibility);
		$I->haveSitePrefUntilAfter('sitecontacts', $contacts);
		$I->haveSitePrefUntilAfter('contact_info', $fields);
		$I->haveSitePrefUntilAfter('sitecontactinfo', null);
	}
}
