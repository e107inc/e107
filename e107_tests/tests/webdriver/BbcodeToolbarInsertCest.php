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
 *
 * A second probe carries the shape the FAQ submission form writes by hand
 * (#6367): a textarea no bbarea wraps, and the toolbar underneath it from the
 * deprecated ren_help(), which the caret listeners have to recognise on the
 * wrapper the bbcode handler puts round every toolbar it renders. Recognising
 * the wrapper is necessary and not sufficient, so that probe carries a second
 * form holding two boxes as well: with no bbarea to narrow it to one, a single
 * textarea in the form is the whole of the answer, and two of them is no answer.
 */
class BbcodeToolbarInsertCest
{
	const BBAREA_PROBE_FILE = 'e107_tests_bbcode_toolbar_probe.php';

	const TOOLBAR_PROBE_FILE = 'e107_tests_bbcode_toolbar_bare_probe.php';

	/** Long enough for a slow container, short enough to fail a test rather than a suite. */
	const TIMEOUT = 10;

	const SEED = 'first message';

	const OTHER_SEED = 'second message';

	const AMBIGUOUS_SEED = 'either message';

	/** The bold button, wherever a toolbar renders it: the anchor is an inline call carrying the tag. */
	const BOLD_BUTTON = 'a[onclick*="[b]"]';

	const ARMED_FIELD = 'return window.e107_selectedInputArea ? window.e107_selectedInputArea.name : null;';

	public function aBoldTagGoesInWithoutClickingTheFieldFirst(\WebDriverTester $I)
	{
		$I->wantTo('write bold text in a message box I have not clicked into yet');

		$this->amOnBbareaProbe($I);

		$I->assertSame(self::SEED, $I->grabValueFrom('[name=probetext]'),
			'precondition: the box holds its server-rendered text, with no caret stored anywhere');
		$I->assertNull($I->executeJS(self::ARMED_FIELD),
			'precondition: nothing has been touched, so nothing is stored');

		$I->click('#firstarea a');
		$I->waitForJS($this->inserted('probetext'), self::TIMEOUT);

		$I->assertStringContainsString('[b][/b]', $I->grabValueFrom('[name=probetext]'));
		$I->assertSame(self::OTHER_SEED, $I->grabValueFrom('[name=othertext]'),
			'the other box on the page is no business of this toolbar');
	}

	public function theToolbarWritesIntoTheBoxItSitsWith(\WebDriverTester $I)
	{
		$I->wantTo('bold the message I am writing, not the one I typed in first');

		$this->amOnBbareaProbe($I);

		$I->click('[name=probetext]');
		$I->assertSame('probetext', $I->executeJS(self::ARMED_FIELD),
			'precondition: touching a box is what stores a caret, and the rest of this test is about ignoring it');

		$I->click('#secondarea a');
		$I->waitForJS($this->inserted('othertext'), self::TIMEOUT);

		$I->assertSame(self::SEED, $I->grabValueFrom('[name=probetext]'),
			'both boxes share a form, so the form cannot say which one the toolbar belongs to; its own bbarea can');
	}

	public function aBoldTagGoesInFromAToolbarNoBbareaWraps(\WebDriverTester $I)
	{
		$I->wantTo('write bold text on a form that lays its own toolbar out');

		$this->amOnToolbarProbe($I);

		$I->assertSame(self::SEED, $I->grabValueFrom('[name=data]'),
			'precondition: the box holds its server-rendered text, with no caret stored anywhere');
		$I->assertNull($I->executeJS(self::ARMED_FIELD),
			'precondition: nothing has been touched, so nothing is stored');

		$I->click('#dataform '.self::BOLD_BUTTON);
		$I->waitForJS($this->inserted('data'), self::TIMEOUT);

		$I->assertStringContainsString('[b][/b]', $I->grabValueFrom('[name=data]'));
	}

	public function aBareToolbarOverTwoBoxesPicksNeither(\WebDriverTester $I)
	{
		$I->wantTo('be left alone rather than guessed at when nothing says which box the toolbar is for');

		$this->amOnToolbarProbe($I);

		$I->click('#ambiguousform '.self::BOLD_BUTTON);

		$I->assertNull($I->executeJS(self::ARMED_FIELD),
			'no bbarea to narrow it and two textareas in the form is no answer, so the fallback stores nothing');
		$I->assertSame(self::AMBIGUOUS_SEED, $I->grabValueFrom('[name=ambiguousone]'));
		$I->assertSame(self::AMBIGUOUS_SEED, $I->grabValueFrom('[name=ambiguoustwo]'));
	}

	private function amOnBbareaProbe(\WebDriverTester $I)
	{
		$I->haveProbe(self::BBAREA_PROBE_FILE, $this->bbareaProbeSource());
		$this->amOnProbe($I, '#firstarea a');
	}

	private function amOnToolbarProbe(\WebDriverTester $I)
	{
		$I->haveProbe(self::TOOLBAR_PROBE_FILE, $this->toolbarProbeSource());
		$this->amOnProbe($I, '#ambiguousform '.self::BOLD_BUTTON);
	}

	private function amOnProbe(\WebDriverTester $I, $ready)
	{
		$I->amOnProbe();
		$I->dontSee(\Helper\ProbeGuard::REFUSAL);
		$I->waitForElement($ready, self::TIMEOUT);
	}

	/** True once the named box holds something other than what the server rendered into it. */
	private function inserted($name)
	{
		return 'var f = document.getElementsByName("'.$name.'")[0]; return f.value !== f.defaultValue;';
	}

	private function toolbarProbeSource()
	{
		$seed = self::SEED;
		$ambiguous = self::AMBIGUOUS_SEED;

		return <<<PHP
<?php
// Fixture for BbcodeToolbarInsertCest, written as e107_plugins/faqs/faqs.php writes its form.
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
require_once(HEADERF);
require_once(e_HANDLER.'ren_help.php');

\$handlers = "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";

echo "<form id='dataform'>"
	."<textarea id='data' class='tbox' name='data' \$handlers>$seed</textarea>"
	.ren_help('addtext')
	."</form>";
echo "<form id='ambiguousform'>"
	."<textarea id='ambiguousone' class='tbox' name='ambiguousone' \$handlers>$ambiguous</textarea>"
	."<textarea id='ambiguoustwo' class='tbox' name='ambiguoustwo' \$handlers>$ambiguous</textarea>"
	.ren_help('addtext')
	."</form>";

require_once(FOOTERF);
PHP;
	}

	private function bbareaProbeSource()
	{
		$seed = self::SEED;
		$other = self::OTHER_SEED;

		return <<<PHP
<?php
// Fixture for BbcodeToolbarInsertCest.
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
