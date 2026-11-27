<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A field whose optArray is not an array, on the edit form and in the list.
 *
 * @see https://github.com/e107inc/e107/issues/6531
 */
class e_formOptArrayTest extends \Test\Unit
{
	const STORED = 'e107help-stored-7';

	/** @var e_form */
	private $form;

	protected function _before()
	{
		$this->form = new e_form();
		$this->resetDebugMessages();
	}

	public function optionTypes()
	{
		return array(
			'dropdown'   => array('dropdown'),
			'radio'      => array('radio'),
			'checkboxes' => array('checkboxes'),
			'comma'      => array('comma'),
		);
	}

	/**
	 * @dataProvider optionTypes
	 * @param string $type
	 */
	public function testTheEditFormShowsTheStoredValueReadOnlyWhereOptArrayIsNotAnArray($type)
	{
		$markup = $this->form->renderElement('gen_strparm', self::STORED, $this->declaredAs($type, 'a=A&b=B'));

		$this->assertStringContainsString(self::STORED, strip_tags($markup), 'The stored value is shown as text.');
		$this->assertSame(1, $this->hiddenInputs('gen_strparm', self::STORED, $markup),
			'A hidden input posts the stored value back, so a save keeps it: ' . $markup);
		$this->assertStringNotContainsString('<select', $markup);
		$this->assertSame(1, substr_count($this->debugMessages(), 'gen_strparm'), 'The field is named in debug, once.');
	}

	public function testAnImagePickerWithAnOptArrayThatIsNotAnArrayIsShownReadOnly()
	{
		$markup = $this->form->renderElement('gen_strparm', self::STORED, $this->declaredAs('image', 'a=A&b=B'));

		$this->assertSame(1, $this->hiddenInputs('gen_strparm', self::STORED, $markup), $markup);
		$this->assertStringContainsString('gen_strparm', $this->debugMessages());
	}

	public function testAnArrayValueIsShownAndPostedBackElementByElement()
	{
		$markup = $this->form->renderElement('gen_strparm', array('e107help-a', 'e107help-b'), $this->declaredAs('checkboxes', 'a=A&b=B'));

		$this->assertStringContainsString('e107help-a, e107help-b', strip_tags($markup));
		$this->assertSame(1, $this->hiddenInputs('gen_strparm[0]', 'e107help-a', $markup), $markup);
		$this->assertSame(1, $this->hiddenInputs('gen_strparm[1]', 'e107help-b', $markup), $markup);
		$this->assertStringNotContainsString('Array', $markup);
	}

	public function testAReadonlyFieldPostsAnArrayValueBackElementByElement()
	{
		$markup = $this->form->renderElement('gen_ro', array('x' => 'e107help-a', 'y' => 'e107help-b'), array('title' => 'Probe', 'type' => 'checkboxes', 'data' => 'str', 'readonly' => 2));

		$this->assertSame(1, $this->hiddenInputs('gen_ro[x]', 'e107help-a', $markup), $markup);
		$this->assertSame(1, $this->hiddenInputs('gen_ro[y]', 'e107help-b', $markup), $markup);
		$this->assertStringNotContainsString("name='gen_ro'", $markup);
	}

	/**
	 * @dataProvider optionTypes
	 * @param string $type
	 */
	public function testTheListShowsTheStoredValueWhereOptArrayIsNotAnArray($type)
	{
		$markup = $this->form->renderValue('gen_strparm', self::STORED, $this->declaredAs($type, 'a=A&b=B'));

		$this->assertStringContainsString(self::STORED, $markup);
		$this->assertStringContainsString('gen_strparm', $this->debugMessages());
	}

	public function testAnOptArrayThatIsAnArrayStillRendersItsOptions()
	{
		$markup = $this->form->renderElement('gen_list', 'b', $this->declaredAs('dropdown', array('a' => 'Alpha', 'b' => 'Beta')));

		$this->assertStringContainsString('<select', $markup);
		$this->assertStringContainsString('Alpha', $markup);
		$this->assertStringContainsString('Beta', $markup);
		$this->assertSame('Beta', $this->form->renderValue('gen_list', 'b', $this->declaredAs('dropdown', array('a' => 'Alpha', 'b' => 'Beta'))));
		$this->assertSame('', $this->debugMessages());
	}

	public function testAnEmptyOptArrayIsStillAnEmptyControlOnTheEditForm()
	{
		$markup = $this->form->renderElement('gen_none', 'b', $this->declaredAs('dropdown', array()));

		$this->assertStringContainsString('<select', $markup);
		$this->assertSame(0, $this->hiddenInputs('gen_none', 'b', $markup), 'An empty control, not the read-only shape: ' . $markup);
		$this->assertSame('', $this->debugMessages());
	}

	/**
	 * @param string $type
	 * @param mixed $optArray
	 * @return array an admin UI field declaration
	 */
	private function declaredAs($type, $optArray)
	{
		return array('title' => 'Probe', 'type' => $type, 'data' => 'str', 'writeParms' => array('optArray' => $optArray));
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $markup
	 * @return int how many hidden inputs post $value under $name
	 */
	private function hiddenInputs($name, $value, $markup)
	{
		return preg_match_all("/<input[^>]*type=['\"]hidden['\"][^>]*name=['\"]" . preg_quote($name, '/') . "['\"][^>]*value=['\"]" . preg_quote($value, '/') . "['\"]/", $markup);
	}
}
