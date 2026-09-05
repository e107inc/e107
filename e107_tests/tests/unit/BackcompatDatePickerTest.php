<?php

/** The date picker's calendar is folded by backcompat.css on a theme without Bootstrap, since the widget's own stylesheet hides nothing. */
class BackcompatDatePickerTest extends \Codeception\Test\Unit
{
	public function testDatePickerDropdownIsFoldedAndPositioned()
	{
		$css = file_get_contents(e_WEB.'css/backcompat.css');

		$this->assertSame(1, preg_match('/\.datetimepicker\.dropdown-menu\s*\{([^}]*)\}/', $css, $match), 'backcompat.css carries no rule for .datetimepicker.dropdown-menu');

		$declarations = preg_replace('/\s+/', '', $match[1]);

		$this->assertNotFalse(strpos($declarations, 'display:none'), 'The picker is not hidden until opened');
		$this->assertNotFalse(strpos($declarations, 'position:absolute'), 'The picker is not taken out of the page flow');
	}
}
