<?php

/** Which template shape each reworked page reads, on every theme state that can present one (#6017). */
class TemplateShapeCest
{
	/** The theme the suite runs on, restored after each case. */
	const SHIPPED_THEME = 'bootstrap5';

	/** An ordinary member, because the settings form is a member's page and an admin sees fields a member does not. */
	const MEMBER = 'tpshapemember';

	/** A user-extended category and one field under it, asserted against the page source because the form leaves a tag unterminated and see() reads strip_tags(). */
	const EXTENDED_CATEGORY = 'TP shape category';
	const EXTENDED_FIELD = 'TP shape field';
	const EXTENDED_FIELD_ID = 'ue-user-tpshapefield';

	/** LAN_MEMBERS_1, the one string membersonly.php prints whoever is asking. */
	const RESTRICTED_AREA = 'This is a restricted area.';

	/** LAN_PLUGIN_FORUM_POSTS, which the forum plugin's e_user addon puts on every profile. */
	const ADDON_LABEL = 'Forum posts';

	/** Text that only a PHP diagnostic puts on a rendered page. */
	private static $diagnostics = array('Fatal error', 'Parse error', 'Warning:', 'Undefined ');

	public function _after(AcceptanceTester $I)
	{
		$I->logoutFromForum();
	}

	public function fpwOffersAFormOnAV1Theme(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, 'tpstate1_legacy');
	}

	public function fpwOffersAFormOnAThemeDeclaringNoFramework(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, 'tpstate3_plain');
	}

	public function fpwOffersAFormOnAV1ThemeThatDefinesBootstrap(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, 'tpstate4_legacybs');
	}

	public function fpwOffersAFormOnTheShippedTheme(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, self::SHIPPED_THEME);
	}

	public function aTemplateAtTheThemeRootWins(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, 'tpstate3_rootfpw');
		$I->see('TPSTATE3_ROOTFPW_MARKER');
	}

	public function aThemePhpGlobalWins(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, 'tpstate3_globalfpw');
		$I->see('TPSTATE3_GLOBALFPW_MARKER');
	}

	public function userSettingsOffersItsFormOnAV1Theme(AcceptanceTester $I)
	{
		$this->seeASettingsForm($I, 'tpstate1_legacy');
		$I->seeElement('table.adminform');
	}

	public function userSettingsOffersItsFormOnAThemeDeclaringNoFramework(AcceptanceTester $I)
	{
		$this->seeASettingsForm($I, 'tpstate3_plain');
	}

	public function userSettingsOffersItsFormOnAV1ThemeThatDefinesBootstrap(AcceptanceTester $I)
	{
		$this->seeASettingsForm($I, 'tpstate4_legacybs');
		$I->seeElement('table.adminform');
	}

	public function userSettingsOffersItsFormOnTheShippedTheme(AcceptanceTester $I)
	{
		$this->seeASettingsForm($I, self::SHIPPED_THEME);
	}

	/** The copy of the legacy template discussions #6008 and #6111 tell a theme author to make; its tables are what say the theme's file won. */
	public function aUserSettingsTemplateInTheThemeWins(AcceptanceTester $I)
	{
		$this->seeASettingsForm($I, 'tpstate3_tplusersettings');
		$I->seeElement('table.adminform');
	}

	public function searchOffersItsFormOnAV1Theme(AcceptanceTester $I)
	{
		$this->seeASearchForm($I, 'tpstate1_legacy');
		$I->seeElement('#tp-search input[name=q]');
	}

	public function searchOffersItsFormOnAThemeDeclaringNoFramework(AcceptanceTester $I)
	{
		$this->seeASearchForm($I, 'tpstate3_plain');
		$I->seeElement('#tp-search input[name=q]');
	}

	public function searchOffersItsFormOnAV1ThemeThatDefinesBootstrap(AcceptanceTester $I)
	{
		$this->seeASearchForm($I, 'tpstate4_legacybs');
		$I->seeElement('#tp-search input[name=q]');
	}

	public function searchOffersItsFormOnTheShippedTheme(AcceptanceTester $I)
	{
		$this->seeASearchForm($I, self::SHIPPED_THEME);
	}

	public function signupOffersItsFormOnAV1Theme(AcceptanceTester $I)
	{
		$this->seeARegistrationForm($I, 'tpstate1_legacy');
	}

	public function signupOffersItsFormOnAThemeDeclaringNoFramework(AcceptanceTester $I)
	{
		$this->seeARegistrationForm($I, 'tpstate3_plain');
	}

	public function signupOffersItsFormOnAV1ThemeThatDefinesBootstrap(AcceptanceTester $I)
	{
		$this->seeARegistrationForm($I, 'tpstate4_legacybs');
	}

	public function signupOffersItsFormOnTheShippedTheme(AcceptanceTester $I)
	{
		$this->seeARegistrationForm($I, self::SHIPPED_THEME);
	}

	public function userListAndProfileRenderOnAV1Theme(AcceptanceTester $I)
	{
		$this->seeTheMemberListAndAProfile($I, 'tpstate1_legacy');
	}

	public function userListAndProfileRenderOnAThemeDeclaringNoFramework(AcceptanceTester $I)
	{
		$this->seeTheMemberListAndAProfile($I, 'tpstate3_plain');
	}

	public function userListAndProfileRenderOnAV1ThemeThatDefinesBootstrap(AcceptanceTester $I)
	{
		$this->seeTheMemberListAndAProfile($I, 'tpstate4_legacybs');
	}

	public function userListAndProfileRenderOnTheShippedTheme(AcceptanceTester $I)
	{
		$this->seeTheMemberListAndAProfile($I, self::SHIPPED_THEME);
	}

	public function membersOnlyRendersOnAV1Theme(AcceptanceTester $I)
	{
		$this->seeTheRestrictedAreaPage($I, 'tpstate1_legacy');
	}

	public function membersOnlyRendersOnAThemeDeclaringNoFramework(AcceptanceTester $I)
	{
		$this->seeTheRestrictedAreaPage($I, 'tpstate3_plain');
	}

	public function membersOnlyRendersOnAV1ThemeThatDefinesBootstrap(AcceptanceTester $I)
	{
		$this->seeTheRestrictedAreaPage($I, 'tpstate4_legacybs');
	}

	public function membersOnlyRendersOnTheShippedTheme(AcceptanceTester $I)
	{
		$this->seeTheRestrictedAreaPage($I, self::SHIPPED_THEME);
	}

	/** Both copies at once, as discussions #6008 and #6111 describe; the fborder table is the one only the theme's own file puts on this page. */
	public function bothTemplatesInTheThemeWin(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, 'tpstate3_tplusersettings');
		$I->seeElement('table.fborder');
	}

	/** The theme.php global on a theme that declares a framework, which is a state the page used to overwrite it in. */
	public function aThemePhpGlobalWinsOnAThemeDefiningBootstrap(AcceptanceTester $I)
	{
		$this->seeAResetForm($I, 'tpstate4_globalfpw');
		$I->see('TPSTATE4_GLOBALFPW_MARKER');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $theme theme directory name; a fixture when tests/_data holds one, else a shipped theme
	 */
	private function seeAResetForm(AcceptanceTester $I, $theme)
	{
		$I->haveThemeFixture($theme);
		$I->haveSiteTheme($theme);

		$I->amOnPage('/fpw.php');

		$I->seeElement('input#email');
		$I->seeElement('button#pwsubmit');

		$this->seeNoDiagnostics($I);
	}

	/**
	 * The settings form, as the member whose settings they are, with the theme switched after the sign-in so the case measures usersettings.php alone.
	 *
	 * @param AcceptanceTester $I
	 * @param string $theme theme directory name; a fixture when tests/_data holds one, else a shipped theme
	 */
	private function seeASettingsForm(AcceptanceTester $I, $theme)
	{
		$this->haveUserExtendedCategory($I);

		$I->haveForumMember(self::MEMBER);
		$I->loginToForum(self::MEMBER);

		$I->haveThemeFixture($theme);
		$I->haveSiteTheme($theme);

		$I->amOnPage('/usersettings.php');

		$I->seeElement('input#loginname');
		$I->seeElement('input#email');
		$I->seeElement('input#password1');
		$I->seeElement('#signature');

		$I->seeElement('#'.self::EXTENDED_FIELD_ID);
		$I->seeInSource(self::EXTENDED_CATEGORY);

		$this->seeNoDiagnostics($I);
	}

	/**
	 * The search form, and with it whatever the theme's own {SEARCH} produced.
	 *
	 * @param AcceptanceTester $I
	 * @param string $theme theme directory name; a fixture when tests/_data holds one, else a shipped theme
	 */
	private function seeASearchForm(AcceptanceTester $I, $theme)
	{
		$I->haveThemeFixture($theme);
		$I->haveSiteTheme($theme);

		$I->amOnPage('/search.php');

		$I->seeElement('#searchform');
		$I->seeElement('#searchform input[name=q]');

		$this->seeNoDiagnostics($I);
	}

	/**
	 * The registration form, as a visitor, past the age question the site's use_coppa preference stands in front of it.
	 *
	 * @param AcceptanceTester $I
	 * @param string $theme theme directory name; a fixture when tests/_data holds one, else a shipped theme
	 */
	private function seeARegistrationForm(AcceptanceTester $I, $theme)
	{
		$I->haveThemeFixture($theme);
		$I->haveSiteTheme($theme);

		$I->amOnPage('/signup.php');

		$I->selectOption('coppa', '1');
		$I->click('newver');

		$I->seeElement('input#email');
		$I->seeElement('input[name=register]');

		$this->seeNoDiagnostics($I);
	}

	/**
	 * The member list and the first member's profile, as a signed-in member, because memberlist_access keeps a visitor off both.
	 *
	 * @param AcceptanceTester $I
	 * @param string $theme theme directory name; a fixture when tests/_data holds one, else a shipped theme
	 */
	private function seeTheMemberListAndAProfile(AcceptanceTester $I, $theme)
	{
		$I->haveForumPluginInstalled();
		$I->haveForumMember(self::MEMBER);
		$I->loginToForum(self::MEMBER);

		$I->haveThemeFixture($theme);
		$I->haveSiteTheme($theme);

		$I->amOnPage('/user.php');
		$I->seeElement('.user-list');
		$this->seeNoDiagnostics($I);

		$I->amOnPage('/user.php?id.1');
		$I->seeInSource(self::ADDON_LABEL);
		$this->seeNoDiagnostics($I);
	}

	/**
	 * The members-only notice, as a visitor who is not signed in, which is the only way to arrive at that page for real.
	 *
	 * @param AcceptanceTester $I
	 * @param string $theme theme directory name; a fixture when tests/_data holds one, else a shipped theme
	 */
	private function seeTheRestrictedAreaPage(AcceptanceTester $I, $theme)
	{
		$I->haveThemeFixture($theme);
		$I->haveSiteTheme($theme);

		$I->amOnPage('/membersonly.php');

		$I->seeInSource(self::RESTRICTED_AREA);

		$this->seeNoDiagnostics($I);
	}

	/**
	 * One category and one text field under it, readable and writable by anybody, seeded straight into the structure table the settings form reads.
	 *
	 * @param AcceptanceTester $I
	 */
	private function haveUserExtendedCategory(AcceptanceTester $I)
	{
		$category = $I->haveInDatabase('e107_user_extended_struct',
			$this->extendedStructRow('tpshapecat', self::EXTENDED_CATEGORY, 0, 0));

		$I->haveInDatabase('e107_user_extended_struct',
			$this->extendedStructRow('tpshapefield', self::EXTENDED_FIELD, 1, (int) $category));
	}

	/**
	 * A structure row whose three userclasses are e_UC_PUBLIC, which the test process does not define.
	 *
	 * @param string $name
	 * @param string $text
	 * @param int $type 0 is a category; 1 is EUF_TEXT
	 * @param int $parent the category the field belongs to
	 * @return array
	 */
	private function extendedStructRow($name, $text, $type, $parent)
	{
		return array(
			'user_extended_struct_name'       => $name,
			'user_extended_struct_text'       => $text,
			'user_extended_struct_type'       => $type,
			'user_extended_struct_parms'      => '',
			'user_extended_struct_values'     => '',
			'user_extended_struct_default'    => '',
			'user_extended_struct_read'       => 0,
			'user_extended_struct_write'      => 0,
			'user_extended_struct_required'   => 0,
			'user_extended_struct_signup'     => 0,
			'user_extended_struct_applicable' => 0,
			'user_extended_struct_order'      => 1,
			'user_extended_struct_parent'     => $parent,
		);
	}

	/**
	 * @param AcceptanceTester $I
	 */
	private function seeNoDiagnostics(AcceptanceTester $I)
	{
		foreach (self::$diagnostics as $text)
		{
			$I->dontSeeInSource($text);
		}
	}
}
