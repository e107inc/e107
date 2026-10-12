<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A list rendered for a user the dispatcher refuses the edit route to must carry no
 * inline-edit widget. e_admin_form_ui::getList() clears the inline attribute of every
 * field it is about to hand the list renderer, alongside hiding the edit button, and
 * leaves the rest of each declaration where it found it.
 *
 * Clearing the attribute decides more than the editor. On a tree list the child icon is
 * emitted by the inline pass when a field is editable and by treePrefix() itself when it
 * is not, so treePrefix() has to read the flag the renderer resolved rather than the one
 * the controller still declares.
 */
class adminUiInlineEditDenialTest extends \Test\Unit
{
	/**
	 * @param string $deniedRoute the one route the dispatcher refuses, '' to refuse none
	 * @param string $class       the controller fixture to build the form on
	 * @return AdminUiInlineEditFormProbe
	 */
	private function formFor($deniedRoute, $class = 'AdminUiInlineEditProbeFixture')
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiInlineEditProbeFixture.php');

		return new AdminUiInlineEditFormProbe(new $class($deniedRoute));
	}

	/** @return array the field declarations the list renderer was given */
	private function renderWith($deniedRoute)
	{
		$form = $this->formFor($deniedRoute);
		$form->getList(true);

		return $form->capturedFields();
	}

	public function testDeniedEditRouteDisablesInlineEditing()
	{
		$fields = $this->renderWith('main/edit');

		$this->assertFalse($fields['probe_title']['inline'],
			'A list must not offer inline editing on the route the dispatcher just refused.');
		$this->assertFalse($fields['probe_open']['inline'],
			'Every inline field loses its widget, not only the first.');
		$this->assertSame(e_UC_NOBODY, $fields['options']['readParms']['editClass'],
			'The edit button is hidden on the same denial.');
	}

	public function testAllowedEditRouteKeepsInlineEditing()
	{
		$fields = $this->renderWith('');

		$this->assertTrue($fields['probe_title']['inline'],
			'A permitted edit route keeps inline editing.');
		$this->assertFalse(isset($fields['options']['readParms']['editClass']),
			'Nor is the edit button hidden.');
	}

	public function testDisablingInlineEditingChangesNothingElse()
	{
		$allowed = $this->renderWith('');
		$denied = $this->renderWith('main/edit');

		$this->assertSame(array_keys($allowed), array_keys($denied),
			'Denying the edit route must not add or drop a column.');

		foreach($allowed as $name => $expected)
		{
			$expected['inline'] = false;

			if($name === 'options')
			{
				$expected['readParms']['editClass'] = e_UC_NOBODY;
			}

			$actual = $denied[$name];
			ksort($expected);
			ksort($actual);

			$this->assertSame($expected, $actual,
				$name . ' answers the denial with the inline flag and the edit button class, nothing else.');
		}
	}

	/**
	 * @return string the tree column as the list renderer draws it, declarations and all
	 */
	private function renderTreeColumn($deniedRoute, $class = 'AdminUiInlineEditTreeProbeFixture')
	{
		$form = $this->formFor($deniedRoute, $class);
		$form->getList(true);
		$fields = $form->capturedFields();

		return $form->renderValue('probe_title', 'Child', $fields['probe_title'], 1);
	}

	public function testADeniedTreeListKeepsItsChildIconAndLosesItsEditor()
	{
		$cell = $this->renderTreeColumn('main/edit');

		$this->assertStringNotContainsString('e-editable', $cell,
			'The denied route is the one the inline widget would have saved through.');
		$this->assertStringStartsWith('<img', $cell,
			'Losing the editor must not cost the tree its indentation icon.');
		$this->assertStringContainsString('treeprefix level-2', $cell);
		$this->assertStringEndsWith('Child', $cell);
	}

	public function testAPermittedTreeListKeepsBothItsChildIconAndItsEditor()
	{
		$cell = $this->renderTreeColumn('');

		$this->assertStringContainsString('e-editable', $cell,
			'A permitted edit route keeps inline editing.');
		$this->assertStringStartsWith('<img', $cell,
			'The inline pass draws the icon whenever it is the pass that runs.');
		$this->assertStringContainsString('treeprefix level-2', $cell);
	}

	public function testAColumnTheFormRefusesToEditKeepsItsChildIcon()
	{
		$cell = $this->renderTreeColumn('', 'AdminUiInlineEditNoeditTreeProbeFixture');

		$this->assertStringNotContainsString('e-editable', $cell,
			'noedit stops the inline pass however editable the column declares itself.');
		$this->assertStringStartsWith('<img', $cell,
			'So read mode is the only pass left that can draw the icon, however the column is declared.');
		$this->assertStringEndsWith('Child', $cell);
	}
}
