<?php

/** The markup the search page prints from PHP, which no template pack can reach (#6301, #6312). */
class SearchFormMarkupCest
{
	/** The shipped theme the suite runs on, and the one that declares Bootstrap 5. */
	const SHIPPED_THEME = 'bootstrap5';

	/** The shipped theme that declares Bootstrap 3, whose dropdown arrow is drawn by a span. */
	const BOOTSTRAP3_THEME = 'bootstrap3';

	/** A fixture theme declaring Bootstrap 4, the version the caret branch used to exclude. */
	const BOOTSTRAP4_THEME = 'tpstate3_bs4';

	/** A fixture theme whose theme.php defines BOOTSTRAP as a boolean rather than a number. */
	const BOOTSTRAP_BOOLEAN_THEME = 'tpstate3_bstrue';

	/** A fixture theme that names no framework in theme.xml or theme.php. */
	const PLAIN_THEME = 'tpstate3_plain';

	public function theAdvancedMenuIsNotInsideTheButtonOnAThemeNamingNoFramework(AcceptanceTester $I)
	{
		$this->seeTheSearchForm($I, self::PLAIN_THEME);

		$I->dontSeeElement('#searchform button ul');
		$I->seeElement('#searchform .btn-group > ul.dropdown-menu');
	}

	public function theAdvancedMenuIsNotInsideTheButtonOnABootstrap4Theme(AcceptanceTester $I)
	{
		$this->seeTheSearchForm($I, self::BOOTSTRAP4_THEME);

		$I->dontSeeElement('#searchform button ul');
		$I->seeElement('#searchform .btn-group > ul.dropdown-menu');
	}

	/** Every framework's spelling of "align this menu to the right", on one element, as nextprev_template.php writes its own. */
	public function theAdvancedMenuNamesEveryFrameworksAlignmentClass(AcceptanceTester $I)
	{
		$this->seeTheSearchForm($I, self::SHIPPED_THEME);

		$I->seeElement('#searchform ul.dropdown-menu.pull-right.dropdown-menu-right.dropdown-menu-end');
	}

	/** Bootstrap 5 draws the arrow from .dropdown-toggle::after, so a span of our own is a second one. */
	public function theShippedThemeGetsNoCaretSpan(AcceptanceTester $I)
	{
		$this->seeTheSearchForm($I, self::SHIPPED_THEME);

		$I->dontSeeElement('#searchform span.caret');
	}

	public function aBootstrap3ThemeKeepsItsCaretSpan(AcceptanceTester $I)
	{
		$this->seeTheSearchForm($I, self::BOOTSTRAP3_THEME);

		$I->seeElement('#searchform span.caret');
	}

	/** The boolean is the Bootstrap 2 and 3 era's spelling, and comparing it as a number is what keeps it there. */
	public function aThemeDefiningBootstrapAsABooleanKeepsItsCaretSpan(AcceptanceTester $I)
	{
		$this->seeTheSearchForm($I, self::BOOTSTRAP_BOOLEAN_THEME);

		$I->seeElement('#searchform span.caret');
	}

	/** Bootstrap 5 styles a select from .form-select, and 3 and 4 from .form-control, so the element carries both. */
	public function theCategorySelectorCarriesBothSelectSpellings(AcceptanceTester $I)
	{
		$this->seeTheSearchForm($I, self::SHIPPED_THEME);

		$I->seeElement('#searchform select#t.form-control.form-select');
	}

	/**
	 * The search page on a given theme, with the form it printed.
	 *
	 * @param AcceptanceTester $I
	 * @param string $theme theme directory name; a fixture when tests/_data holds one, else a shipped theme
	 */
	private function seeTheSearchForm(AcceptanceTester $I, $theme)
	{
		$I->haveThemeFixture($theme);
		$I->haveSiteTheme($theme);

		$I->amOnPage('/search.php');

		$I->seeElement('#searchform');
		$I->seeElement('#searchform input[name=q]');
	}
}
