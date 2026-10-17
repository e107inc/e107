<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A field declared a link address is judged on every save and by the list's default batch, and a declaration the check cannot honour is refused.
 */
class adminUiLinkAddressFieldTest extends \Codeception\Test\Unit
{
	use \Test\ScriptAccess;

	protected function _before()
	{
		require_once(e_HANDLER.'admin_ui.php');
		require_once(__DIR__.'/fixtures/AdminUiLinkAddressProbeFixture.php');
		require_once(__DIR__.'/fixtures/AdminUiBatchProbeFixture.php');

		$this->holdScriptAccess();
	}

	protected function _after()
	{
		$this->restoreScriptAccess();
	}

	/**
	 * @return array a declaration and the model's data types under which the model would store the address in a form the check does not judge
	 */
	public function waysToStoreUnjudged()
	{
		return array(
			'safestr data' => array(array(), array('probe_url' => 'safestr')),
			'json data'    => array(array(), array('probe_url' => 'json')),
			'a data path'  => array(array('dataPath' => 'probe/url'), array('probe_url' => 'str')),
		);
	}

	/**
	 * @dataProvider waysToStoreUnjudged
	 * @param array $declared
	 * @param array $types
	 */
	public function testALinkAddressFieldTheModelWouldStoreUnjudgedIsRefused($declared, $types)
	{
		$probe = new AdminUiLinkAddressProbeFixture(array_merge($this->linkAddressField(), $declared), $types);

		$this->assertTheDeclarationIsRefused(array($probe, 'probeSubmit'), $probe->model);
	}

	/**
	 * @return array the probe pages, one declaring the preference in $prefs and one through getPrefs()
	 */
	public function preferencePages()
	{
		return array(
			'in $prefs'          => array('AdminUiLinkAddressPrefsProbeFixture'),
			'through getPrefs()' => array('AdminUiLinkAddressPrefsGetterProbeFixture'),
		);
	}

	/**
	 * @dataProvider preferencePages
	 * @param string $page
	 */
	public function testAPreferenceDeclaredALinkAddressIsRefused($page)
	{
		$probe = new $page($this->linkAddressField());

		$this->assertTheDeclarationIsRefused(array($probe, 'PrefsSaveTrigger'), $probe->config);
	}

	public function testALinkAddressFieldTheCheckJudgesIsSaved()
	{
		$probe = new AdminUiLinkAddressProbeFixture($this->linkAddressField(), array('probe_url' => 'str'));

		$probe->probeSubmit();

		self::assertTrue($probe->model->saved, 'a plain address in a field the check judges has to be saved');
	}

	public function testABatchCannotSetALinkAddressThatRunsScript()
	{
		self::assertSame(array(), $this->batch('probe_url__javascript:alert(1)'), 'the batch wrote the address unjudged');
	}

	public function testABatchStillSetsALinkAddressThatRunsNoScript()
	{
		self::assertCount(1, $this->batch('probe_url__news.php'), 'a plain address has to be set by the batch');
	}

	/**
	 * @param callable $save the save the declaration governs
	 * @param AdminUiLinkAddressModelStub $store what that save writes to
	 */
	private function assertTheDeclarationIsRefused($save, $store)
	{
		try
		{
			call_user_func($save);
		}
		catch (LogicException $e)
		{
			self::assertFalse($store->saved, 'nothing may be saved under a declaration that is refused');

			return;
		}

		self::fail('the declaration was taken, so the address could be stored without being judged');
	}

	/**
	 * @return array
	 */
	private function linkAddressField()
	{
		return array('title' => 'Probe', 'type' => 'url', 'data' => 'str', 'linkAddress' => true);
	}

	/**
	 * @param string $trigger
	 * @return array the writes the batch asked the list for
	 */
	private function batch($trigger)
	{
		$request = new e_admin_request('mode=main&action=list');
		$request->setPosted(array('e-multiselect' => array(1, 2)));

		$probe = new AdminUiBatchProbeFixture();
		$probe->setRequest($request);
		$probe->setProbeFields(array(
			'checkboxes' => array('title' => '', 'type' => null, 'forced' => '1', 'toggle' => 'e-multiselect'),
			'probe_url'  => array_merge($this->linkAddressField(), array('batch' => true)),
		));

		return $probe->probe($trigger);
	}
}
