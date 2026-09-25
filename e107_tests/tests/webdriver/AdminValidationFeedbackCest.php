<?php

/**
 * A Save that jquery.h5validate cancels never reaches PHP, so the admin gets no message from the server and the only
 * sign left is whatever the page itself shows. This asks a real browser what it shows.
 *
 * Regression cover for #6523, reported on discussion #6101 and issue #6103. Corporate is the skin that draws nothing:
 * the only rule reaching the class is Bootstrap's `.has-error .form-control`, which a control carrying the class
 * itself does not match. It is therefore the one skin here whose colour assertion needs the core stylesheet; on the
 * other two the same red comes from their own `input:invalid` rule, and the assertion is a pin that the core rule has
 * not taken their colour away.
 *
 * The field being on screen afterwards is asserted but not owned by e107: `focus()` on the control the library
 * rejected brings it in by itself, which was measured against this suite rather than assumed.
 *
 * K-Admin is left out. Its own `input[type="text"]:focus` ties with the core rule on specificity and wins on document
 * order, so a K-Admin input keeps its grey focus border for as long as it holds focus.
 *
 * Master only: release/v2.3.x has no webdriver suite.
 */
class AdminValidationFeedbackCest
{
	const PREFS = '/e107_admin/prefs.php';

	const FIELD = "input[name='siteurl']";
	const SAVE = '#updateprefs-main';

	const ERROR_BORDER = 'rgb(255, 0, 0)';

	const SETTLE = 5;

	/**
	 * The admin skin to put back after a test that swapped it, or null when none did.
	 *
	 * Never empty: the preference is what {@see e_pref::update()} is asked to repair in e107_admin/auth.php, and that
	 * method assigns only to a key it already holds, so removing it redirects the admin area to itself for ever.
	 *
	 * @var string|null
	 */
	private $adminCss = null;

	public function _before(\WebDriverTester $I)
	{
		$I->loginAsAdmin();
	}

	public function _after(\WebDriverTester $I)
	{
		if($this->adminCss !== null)
		{
			$I->haveSitePref('admincss', $this->adminCss);
			$this->adminCss = null;
		}
	}

	/**
	 * Every skin is named, because the suite's dump seeds Legacy Dark Admin rather than the default.
	 *
	 * @example ["css/modern-light.css"]
	 * @example ["css/corporate.css"]
	 * @example ["css/modern-dark.css"]
	 */
	public function aCancelledSaveDrawsAndShowsTheBlockingField(\WebDriverTester $I, \Codeception\Example $example)
	{
		$I->wantTo('cancel a Save on '.$example[0].' and see what the page does');
		$this->swapSkin($I, $example[0]);

		$I->amOnPage(self::PREFS);
		$I->waitForElementVisible(self::FIELD, 10);

		$I->fillField(self::FIELD, 'foo');
		$I->scrollTo(self::SAVE, 0, -150);
		$I->assertFalse($this->isInViewport($I), 'the Site URL box is off screen with the Save button in reach');

		$I->click(self::SAVE);
		$I->waitForElement("input[name='siteurl'].has-error", 10);

		$I->waitForJS($this->borderColourIs(self::ERROR_BORDER), self::SETTLE);
		$I->assertTrue($this->isInViewport($I), 'the blocked field is brought into view');

		$this->makeTheMarkedFieldValid($I);
		$I->waitForJS($this->borderColourIs(self::ERROR_BORDER), self::SETTLE);
	}

	/**
	 * Give the marked field a value that passes its pattern, so it keeps the class without being natively `:invalid`.
	 *
	 * That is the one state in which the core rule outranks a skin's own rule for the class, so it is the state that
	 * holds the four skins carrying their own copy of these declarations to the copy in the core stylesheet. Writing
	 * the value from script fires no event, so the library neither revalidates nor takes the class away.
	 */
	private function makeTheMarkedFieldValid(\WebDriverTester $I)
	{
		$I->executeJS('document.querySelector("'.self::FIELD.'").value = "http://example.com/";');
		$I->assertTrue((bool) $I->executeJS('return document.querySelector("'.self::FIELD.'").matches(".has-error:valid");'),
			'the field is still marked and no longer natively invalid');
	}

	private function swapSkin(\WebDriverTester $I, $skin)
	{
		$this->adminCss = $I->haveSitePref('admincss', $skin) ?: 'css/modern-light.css';
	}

	/**
	 * @return string JS answering whether the Site URL box is drawn in $colour, for a wait that outlasts the
	 *                150ms border-colour transition every Bootstrap 3 skin puts on .form-control
	 */
	private function borderColourIs($colour)
	{
		return 'return window.getComputedStyle(document.querySelector("'.self::FIELD.'")).borderTopColor'
			." === '".$colour."';";
	}

	/**
	 * Whether the Site URL box is somewhere the admin can actually see it.
	 *
	 * Being inside the viewport is not enough on its own: the admin navbar is `navbar-fixed-top`, so a field the
	 * browser has merely brought to the top edge on focus sits underneath it. The centre of the box has to belong to
	 * the box.
	 *
	 * @return bool
	 */
	private function isInViewport(\WebDriverTester $I)
	{
		return (bool) $I->executeJS("
			var field = document.querySelector(\"".self::FIELD."\");
			var rect = field.getBoundingClientRect();
			var height = window.innerHeight || document.documentElement.clientHeight;

			if(rect.top < 0 || rect.bottom > height) { return false; }

			return document.elementFromPoint(rect.left + rect.width / 2, rect.top + rect.height / 2) === field;
		");
	}
}
