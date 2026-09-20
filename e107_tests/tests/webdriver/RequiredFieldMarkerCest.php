<?php

/** The marker core emits for a required field is drawn, and one that already carries content is left alone (#6124). */
class RequiredFieldMarkerCest
{
	/** The marker the signup template puts after the username label. */
	const MARKER = 'label[for="loginname"] span.required';

	public function aRequiredFieldCarriesAMarker(WebDriverTester $I)
	{
		$I->wantTo('see which fields on the signup form are compulsory');

		$this->amOnTheRegistrationForm($I);
		$I->seeElementInDOM(self::MARKER);

		$drawn = $I->executeJS('return getComputedStyle(document.querySelector('
			."'".self::MARKER."'".'), "::after").content;');

		$I->assertSame('" *"', $drawn, 'A required field has to say so before the form is submitted.');
	}

	/** The rule ignores a marker that carries its own content, so nothing that already draws one gains a second. */
	public function aMarkerThatAlreadyHasContentIsLeftAlone(WebDriverTester $I)
	{
		$I->wantTo('leave a marker alone when something already drew it');

		$this->amOnTheRegistrationForm($I);

		$drawn = $I->executeJS('document.body.insertAdjacentHTML("beforeend",'
			.' \'<span class="required" id="e107-test-filled">&nbsp;*</span>\');'
			.' return getComputedStyle(document.getElementById("e107-test-filled"), "::after").content;');

		$I->assertSame('none', $drawn, 'A marker that carries its own content must not gain a second one.');
	}

	/** The COPPA age gate stands in front of the fields, and its radio defaults to the answer that refuses. */
	private function amOnTheRegistrationForm(WebDriverTester $I)
	{
		$I->amOnPage('/signup.php');

		if(strpos($I->grabPageSource(), 'name="newver"') !== false)
		{
			$I->selectOption('input[name="coppa"]', '1');
			$I->click('input[name="newver"]');
		}
	}
}
