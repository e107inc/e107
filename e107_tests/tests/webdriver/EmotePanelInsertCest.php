<?php

/**
 * The emote panel, clicked in a browser that actually runs the handler (#5613).
 *
 * addtext() refuses to insert until storeCaret() has recorded a target, and
 * nothing records one at page load, so the first click on a smiley did nothing
 * at all unless the visitor had already clicked into the message box. None of
 * that is visible to PhpBrowser, which runs no JavaScript: the PHP side emits
 * the same anchors either way, and a unit test over the emitted script can only
 * say the script has the shape it was written with. It cannot say the browser
 * ends up with the smiley in the field, which is the whole of the report.
 *
 * The panel is rendered through a probe rather than through the chatbox or PM,
 * because both of those surfaces put it behind a collapse and a login. The
 * probe is three forms: the one holding the panel, a second field to touch
 * first, and a third form holding a panel over two fields, which is the case
 * the handler declines to answer.
 *
 * Every field is seeded and carries the inline handlers of the surfaces this is
 * about, and neither is decoration. A PM quote reply is pre-filled and never
 * focused, and on a field in that state the caret assignment inside addtext()
 * queues a select event which runs the inline onselect after the handler has
 * returned. That is why the emote handler resolves its own form's field on
 * every click instead of storing one and putting the global back: a restore
 * cannot survive that event, and theSmileyGoesIntoTheFieldTheEmotePanelBelongsTo
 * is what reds if one is reinstated.
 */
class EmotePanelInsertCest
{
	const PROBE_FILE = 'e107_tests_emote_panel_probe.php';

	/** Long enough for a slow container, short enough to fail a test rather than a suite. */
	const TIMEOUT = 10;

	const SEED = 'quoted text';

	const OTHER_SEED = 'somewhere else entirely';

	const AMBIGUOUS_SEED = 'one of two fields';

	const INSERTED = 'var f = document.getElementById("probetext"); return f.value !== f.defaultValue;';

	const ARMED_FIELD = 'return window.e107_selectedInputArea ? window.e107_selectedInputArea.id : null;';

	public function _before(\WebDriverTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(\WebDriverTester $I)
	{
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function aSmileyGoesInWithoutClickingTheFieldFirst(\WebDriverTester $I)
	{
		$I->wantTo('add a smiley to a message box I have not clicked into yet');

		$this->amOnProbe($I);

		$I->assertSame(self::SEED, $I->grabValueFrom('#probetext'),
			'precondition: the field holds its server-rendered text, with no caret stored anywhere');
		$I->assertNull($I->executeJS(self::ARMED_FIELD),
			'precondition: nothing has been touched, so nothing is stored');

		$I->click('#probeform .addEmote');
		$I->waitForJS(self::INSERTED, self::TIMEOUT);
	}

	public function theSmileyGoesIntoTheFieldTheEmotePanelBelongsTo(\WebDriverTester $I)
	{
		$I->wantTo('pick a smiley for the message box under it, having typed in another field first');

		$this->amOnProbe($I);

		$I->click('#othertext');
		$I->assertSame('othertext', $I->executeJS(self::ARMED_FIELD),
			'precondition: touching a field is what stores a caret, and the rest of this test is about ignoring it');

		$I->click('#probeform .addEmote');
		$I->waitForJS(self::INSERTED, self::TIMEOUT);

		$I->assertSame(self::OTHER_SEED, $I->grabValueFrom('#othertext'),
			'the panel belongs to its own form, so the field the visitor last touched elsewhere is left alone');
		$I->assertSame('probetext', $I->executeJS(self::ARMED_FIELD),
			'the click ends with the field it wrote into stored, which is what putting the global back would undo');
	}

	public function aPanelOverTwoFieldsPicksNeither(\WebDriverTester $I)
	{
		$I->wantTo('be left alone rather than guessed at when the panel cannot tell which field is mine');

		$this->amOnProbe($I);

		$I->click('#ambiguousform .addEmote');

		$I->assertNull($I->executeJS(self::ARMED_FIELD),
			'two textareas in the form is no answer, and a guess is worse than nothing');
		$I->assertSame(self::AMBIGUOUS_SEED, $I->grabValueFrom('#ambiguousone'));
		$I->assertSame(self::AMBIGUOUS_SEED, $I->grabValueFrom('#ambiguoustwo'));
	}

	private function amOnProbe(\WebDriverTester $I)
	{
		$I->amOnPage('/' . self::PROBE_FILE . '?' . \Helper\ProbeGuard::query());
		$I->dontSee(\Helper\ProbeGuard::REFUSAL);
		$I->waitForElement('.addEmote', self::TIMEOUT);
	}

	private function probeSource()
	{
		$seed = self::SEED;
		$other = self::OTHER_SEED;
		$ambiguous = self::AMBIGUOUS_SEED;

		return <<<PHP
<?php
// Fixture for EmotePanelInsertCest. Removed again in the Cest's _after().
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
require_once(HEADERF);

\$handlers = "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";

echo "<form id='otherform'><textarea id='othertext' name='othertext' rows='4' \$handlers>$other</textarea></form>";
echo "<form id='probeform'><textarea id='probetext' name='probetext' rows='4' \$handlers>$seed</textarea>".r_emote()."</form>";
echo "<form id='ambiguousform'><textarea id='ambiguousone' name='ambiguousone' rows='4' \$handlers>$ambiguous</textarea>"
	."<textarea id='ambiguoustwo' name='ambiguoustwo' rows='4' \$handlers>$ambiguous</textarea>".r_emote()."</form>";

require_once(FOOTERF);
PHP;
	}
}
