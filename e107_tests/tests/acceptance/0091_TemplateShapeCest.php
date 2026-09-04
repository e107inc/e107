<?php

/** Which template shape each reworked page reads, on every theme state that can present one (#6017). */
class TemplateShapeCest
{
	/** The theme the suite runs on, restored after each case. */
	const SHIPPED_THEME = 'bootstrap5';

	/** Text that only a PHP diagnostic puts on a rendered page. */
	private static $diagnostics = array('Fatal error', 'Parse error', 'Warning:', 'Undefined ');

	public function _after(AcceptanceTester $I)
	{
		$I->dropThemeFixtures();
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

		foreach (self::$diagnostics as $text)
		{
			$I->dontSeeInSource($text);
		}
	}
}
