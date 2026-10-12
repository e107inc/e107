<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The options save of an admin screen writes the options that screen declares and no other posted key.
 */
class adminUiPrefsSaveScopeTest extends \Test\Unit
{
	/**
	 * @param array      $prefs
	 * @param array      $posted
	 * @param array|null $before
	 * @return AdminUiPrefsSaveConfigRecorder what the save handed over
	 */
	private function save(array $prefs, array $posted, $before = null)
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiPrefsSaveProbeFixture.php');

		$screen = new AdminUiPrefsSaveProbeFixture($prefs, $posted, $before);
		$screen->PrefsSaveTrigger();

		return $screen->getConfig();
	}

	public function testAScreenWithNoOptionsWritesNoPostedKey()
	{
		$config = $this->save(array(), array(
			'sitename'      => 'p7rp',
			'p7rp_new_pref' => '1',
			'e-token'       => 'token',
			'etrigger_save' => 'save',
		));

		$this->assertSame(array(), $config->written,
			'A screen that declares no options must hand no posted key to the preferences.');
	}

	public function testAScreenWritesItsOwnOptionsAndDropsTheRest()
	{
		$config = $this->save(
			array('admin_page_perpage' => array('title' => 'Per page', 'type' => 'number', 'data' => 'int')),
			array('admin_page_perpage' => '13', 'sitename' => 'p7rp', 'e-token' => 'token', 'etrigger_save' => 'save')
		);

		$this->assertSame(array('admin_page_perpage' => '13'), $config->written,
			'Only the declared option may reach the preferences.');
		$this->assertTrue($config->saved);
	}

	public function testAnUndeclaredKeyFromBeforePrefsSaveIsNotWritten()
	{
		$config = $this->save(
			array('admin_page_perpage' => array('title' => 'Per page', 'type' => 'number', 'data' => 'int')),
			array('admin_page_perpage' => '13'),
			array('admin_page_perpage' => '13', 'sitename' => 'p7rp')
		);

		$this->assertSame(array('admin_page_perpage' => '13'), $config->written,
			'A key the screen does not declare is not saved because a hook returned it.');
	}

	public function testAnUnpostedCheckboxOptionIsStillWrittenEmpty()
	{
		$config = $this->save(
			array('listPages' => array('title' => 'List pages', 'type' => 'checkbox', 'data' => 'int')),
			array('etrigger_save' => 'save')
		);

		$this->assertSame(array('listPages' => null), $config->written,
			'An unticked checkbox posts nothing and must still be saved as empty.');
	}
}
