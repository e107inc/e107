<?php

use Helper\ProbeGuard;

/** The date picker's calendar stays folded on a theme that loads no Bootstrap, because backcompat.css carries the dropdown rule the widget leaves to Bootstrap. */
class DatePickerWithoutBootstrapCest
{
	const PROBE_FILE = 'e107_tests_datepicker_probe.php';

	public function _before(WebDriverTester $I)
	{
		$I->haveThemeFixture('tpstate3_plain');
		$I->haveSiteTheme('tpstate3_plain');
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(WebDriverTester $I)
	{
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function calendarStaysFoldedUntilTheFieldIsClicked(WebDriverTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?'.ProbeGuard::query());
		$I->seeElement('input.e-date');
		$I->dontSeeElement('.datetimepicker.dropdown-menu');

		$I->click('input.e-date');
		$I->waitForElementVisible('.datetimepicker.dropdown-menu', 5);

		$drop = $I->executeJS('var calendar = document.querySelector(".datetimepicker.dropdown-menu").getBoundingClientRect(), field = document.querySelector("input.e-date").getBoundingClientRect(); return Math.round(calendar.top - field.top);');
		$I->assertGreaterThan(0, $drop, 'The calendar did not hang from its field');
		$I->assertLessThan(40, $drop, 'The calendar opened somewhere other than under its field');
	}

	/** @return string */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for DatePickerWithoutBootstrapCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
$e107_tests_date_field = e107::getForm()->datepicker('e107_tests_date', '', array('format' => 'yyyy-mm-dd', 'return' => 'string'));
require_once(HEADERF);
echo $e107_tests_date_field;
require_once(FOOTERF);
PHP;
	}
}
