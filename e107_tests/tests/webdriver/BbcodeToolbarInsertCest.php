<?php

/**
 * The BBCode toolbar, clicked in a browser that actually runs the handler (#6135).
 *
 * addtext() refuses to insert until storeCaret() has recorded a target, and the
 * toolbar's buttons are inline addtext() calls, so the first click on one did
 * nothing at all unless the visitor had already clicked into the message box.
 * None of that is visible to PhpBrowser, which runs no JavaScript: the PHP side
 * emits the same buttons either way, and a unit test over the emitted markup can
 * only say the button has the shape it was written with. It cannot say the
 * browser ends up with the tag in the field, which is the whole of the report.
 *
 * The probe is one form holding two message boxes, because the second question
 * the fix has to answer is which box a toolbar belongs to. A form is no answer
 * where there are two of them, so the toolbar resolves the box inside its own
 * bbarea, and the box the visitor happened to touch before is left alone.
 */
class BbcodeToolbarInsertCest
{
	const PROBE_FILE = 'e107_tests_bbcode_toolbar_probe.php';

	/** Long enough for a slow container, short enough to fail a test rather than a suite. */
	const TIMEOUT = 10;

	const SEED = 'first message';

	const OTHER_SEED = 'second message';

	const INSERTED_FIRST = 'var f = document.getElementsByName("probetext")[0]; return f.value !== f.defaultValue;';

	const INSERTED_SECOND = 'var f = document.getElementsByName("othertext")[0]; return f.value !== f.defaultValue;';

	const ARMED_FIELD = 'return window.e107_selectedInputArea ? window.e107_selectedInputArea.name : null;';

	public function _before(\WebDriverTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(\WebDriverTester $I)
	{
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function aBoldTagGoesInWithoutClickingTheFieldFirst(\WebDriverTester $I)
	{
		$I->wantTo('write bold text in a message box I have not clicked into yet');

		$this->amOnProbe($I);

		$I->assertSame(self::SEED, $I->grabValueFrom('[name=probetext]'),
			'precondition: the box holds its server-rendered text, with no caret stored anywhere');
		$I->assertNull($I->executeJS(self::ARMED_FIELD),
			'precondition: nothing has been touched, so nothing is stored');

		$I->click('#firstarea a');
		$I->waitForJS(self::INSERTED_FIRST, self::TIMEOUT);

		$I->assertStringContainsString('[b][/b]', $I->grabValueFrom('[name=probetext]'));
		$I->assertSame(self::OTHER_SEED, $I->grabValueFrom('[name=othertext]'),
			'the other box on the page is no business of this toolbar');
	}

	public function theToolbarWritesIntoTheBoxItSitsWith(\WebDriverTester $I)
	{
		$I->wantTo('bold the message I am writing, not the one I typed in first');

		$this->amOnProbe($I);

		$I->click('[name=probetext]');
		$I->assertSame('probetext', $I->executeJS(self::ARMED_FIELD),
			'precondition: touching a box is what stores a caret, and the rest of this test is about ignoring it');

		$I->click('#secondarea a');
		$I->waitForJS(self::INSERTED_SECOND, self::TIMEOUT);

		$I->assertSame(self::SEED, $I->grabValueFrom('[name=probetext]'),
			'both boxes share a form, so the form cannot say which one the toolbar belongs to; its own bbarea can');
	}

	private function amOnProbe(\WebDriverTester $I)
	{
		$I->amOnPage('/' . self::PROBE_FILE . '?' . \Helper\ProbeGuard::query());
		$I->dontSee(\Helper\ProbeGuard::REFUSAL);
		$I->waitForElement('#firstarea a', self::TIMEOUT);
	}

	private function probeSource()
	{
		$seed = self::SEED;
		$other = self::OTHER_SEED;

		return <<<PHP
<?php
// Fixture for BbcodeToolbarInsertCest. Removed again in the Cest's _after().
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
require_once(HEADERF);

\$frm = e107::getForm();
\$toolbar = "<div class='btn-group'>{BB=b}</div>";

echo "<form id='probeform'>"
	."<div id='firstarea'>".\$frm->bbarea('probetext', '$seed', \$toolbar)."</div>"
	."<div id='secondarea'>".\$frm->bbarea('othertext', '$other', \$toolbar)."</div>"
	."</form>";

require_once(FOOTERF);
PHP;
	}
}
