<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A posted etrigger_batch must not be dispatched as a column assignment unless its field
 * segment is one the batch dropdown itself offers, which is what
 * e_admin_form_ui::renderBatchFilter() means by a field carrying 'batch'.
 * e_admin_controller_ui::_handleListBatch() stops otherwise, on every trigger that names a
 * field and not only on the default one.
 */
class adminUiBatchTriggerTest extends \Codeception\Test\Unit
{
	/** @var array|null registry entries replaced by stubUserRegistry() */
	private $savedUserRegistry;

	protected function _after()
	{
		$this->restoreUserRegistry();
	}

	private function stubUserRegistry()
	{
		if($this->savedUserRegistry !== null)
		{
			return;
		}

		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiBatchProbeFixture.php');

		$this->savedUserRegistry = array(
			'core/e107/singleton/user_class' => e107::getRegistry('core/e107/singleton/user_class'),
			'core/e107/current_user'         => e107::getRegistry('core/e107/current_user'),
		);

		e107::setRegistry('core/e107/singleton/user_class', new AdminUiBatchUserClassStub());
		e107::setRegistry('core/e107/current_user', new AdminUiBatchUserStub());
	}

	private function restoreUserRegistry()
	{
		if($this->savedUserRegistry === null)
		{
			return;
		}

		foreach($this->savedUserRegistry as $key => $value)
		{
			e107::setRegistry($key, $value);
		}

		$this->savedUserRegistry = null;
	}

	private function makeProbe($posted, $class = 'AdminUiBatchProbeFixture')
	{
		require_once(e_HANDLER . 'admin_ui.php');
		require_once(__DIR__ . '/fixtures/AdminUiBatchProbeFixture.php');

		$request = new e_admin_request('mode=main&action=list');
		$request->setPosted($posted);

		$probe = new $class();
		$probe->setRequest($request);
		$probe->setProbeFields(array(
			'checkboxes'    => array('title' => '', 'type' => null, 'forced' => '1', 'toggle' => 'e-multiselect'),
			'gen_ip'        => array('title' => 'IP', 'type' => 'ip', 'data' => 'str', 'batch' => true),
			'gen_chardata'  => array('title' => 'Description', 'type' => 'method', 'data' => 'str',
				'writeParms' => array('classlist' => 'member')),
			'gen_user_id'   => array('title' => 'Flag', 'type' => 'boolean', 'data' => 'int', 'batch' => true),
			'gen_type'      => array('title' => 'Classes', 'type' => 'userclasses', 'data' => 'str', 'batch' => true),
			'gen_sef'       => array('title' => 'SEF', 'type' => 'text', 'data' => 'str', 'batch' => true,
				'writeParms' => 'sef=gen_ip'),
			'x_probe_thing' => array('title' => 'Addon', 'type' => 'boolean', 'data' => false, 'batch' => true),
			'options'       => array('title' => 'Options', 'type' => null, 'forced' => '1'),
		));

		return $probe;
	}

	private function dispatchOf($trigger, $class = 'AdminUiBatchProbeFixture')
	{
		return $this->makeProbe(array('e-multiselect' => array(1, 2)), $class)->probeDispatch($trigger);
	}

	/**
	 * @dataProvider unbatchedTypedTriggers
	 */
	public function testTypedTriggerRefusesAFieldThatOffersNoBatch($trigger)
	{
		$this->assertSame(array(), $this->dispatchOf($trigger),
			$trigger . ' names a column the batch dropdown never offers and must not be dispatched.');
	}

	public function unbatchedTypedTriggers()
	{
		return array(
			'sefgen'      => array('sefgen__gen_chardata__gen_ip'),
			'bool'        => array('bool__gen_chardata__1'),
			'boolreverse' => array('boolreverse__gen_chardata'),
			'attach'      => array('attach__gen_chardata__253'),
			'deattach'    => array('deattach__gen_chardata__253'),
			'addAll'      => array('addAll__gen_chardata__253'),
			'clearAll'    => array('clearAll__gen_chardata__253'),
			'ucaddall'    => array('ucaddall__gen_chardata'),
			'ucdelall'    => array('ucdelall__gen_chardata'),
		);
	}

	public function testDeclaredButUnbatchedFieldNeverReachesBatchUpdate()
	{
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('gen_chardata__x');

		$this->assertSame(array(), $calls,
			'Only a field the batch dropdown offers may be assigned through a batch trigger.');
	}

	public function testDeclaredBatchTriggerStillReachesBatchUpdate()
	{
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('gen_ip__127.0.0.1');

		$this->assertCount(1, $calls, 'A declared field must still be batched.');
		$this->assertSame('gen_ip', $calls[0]['field']);
		$this->assertSame("'127.0.0.1'", $calls[0]['value'],
			'handleListBatch() quotes the literal itself on this branch.');
	}

	public function testUserclassTriggersRefuseAFieldThatOffersNoBatch()
	{
		$this->stubUserRegistry();

		$this->assertSame(array(), $this->dispatchOf('ucadd__gen_chardata__253'),
			'ucadd names a column the batch dropdown never offers and must not be dispatched.');
		$this->assertSame(array(), $this->dispatchOf('ucremove__gen_chardata__253'),
			'ucremove names a column the batch dropdown never offers and must not be dispatched.');
	}

	public function testUserclassTriggerStillReachesItsHandlerForABatchField()
	{
		$this->stubUserRegistry();

		$dispatched = $this->dispatchOf('ucadd__gen_type__253');

		$this->assertCount(1, $dispatched, 'A userclasses field the dropdown offers must still be batched.');
		$this->assertSame('attach', $dispatched[0]['handler']);
		$this->assertSame('gen_type', $dispatched[0]['field']);
	}

	public function testSefgenTriggerRefusesASourceTheFieldDoesNotDeclare()
	{
		$this->assertSame(array(), $this->dispatchOf('sefgen__gen_sef__gen_chardata'),
			'The sef source is read off the record, so it must be the one the menu built the option from.');
	}

	public function testSefgenTriggerStillReachesItsHandlerForTheDeclaredSource()
	{
		$dispatched = $this->dispatchOf('sefgen__gen_sef__gen_ip');

		$this->assertCount(1, $dispatched, 'The declared sef source must still be batched.');
		$this->assertSame('sefgen', $dispatched[0]['handler']);
		$this->assertSame('gen_sef', $dispatched[0]['field']);
		$this->assertSame('gen_ip', $dispatched[0]['value']);
	}

	public function testTypedTriggerStillReachesItsHandlerForABatchField()
	{
		$dispatched = $this->dispatchOf('bool__gen_user_id__1');

		$this->assertCount(1, $dispatched, 'A field the dropdown offers must still be batched.');
		$this->assertSame('bool', $dispatched[0]['handler']);
		$this->assertSame('gen_user_id', $dispatched[0]['field']);
		$this->assertSame(1, $dispatched[0]['value']);
	}

	public function testCommaTriggerStillReachesItsHandlerForABatchField()
	{
		$dispatched = $this->dispatchOf('attach__gen_type__253');

		$this->assertCount(1, $dispatched, 'A comma field the dropdown offers must still be batched.');
		$this->assertSame('attach', $dispatched[0]['handler']);
		$this->assertSame('gen_type', $dispatched[0]['field']);
	}

	public function testAddonFieldOutsideTheTableNeverReachesBatchUpdate()
	{
		// initAdminAddons() declares every x_<plugin>_<key> field with 'data' => false,
		// so no such name is a column of the table the batch writes to.
		$probe = $this->makeProbe(array('e-multiselect' => array(1, 2)));

		$this->assertSame(array(), $probe->probe('x_probe_thing__1'),
			'A field declared outside the table must not be assigned as a column.');
		$this->assertSame(array(), $this->dispatchOf('bool__x_probe_thing__1'),
			'Nor through a typed trigger.');
	}

	public function testOverriddenHandleListBatchStillCrossesTheGuard()
	{
		$refused = $this->makeProbe(array('e-multiselect' => array(1, 2)), 'AdminUiBatchOverrideProbeFixture');

		$this->assertSame(array(), $refused->probe('gen_chardata__x'),
			'A screen that overrides handleListBatch() must not be able to skip the guard.');
		$this->assertSame(array(), $refused->dispatched,
			'The override must not be reached at all for a field the dropdown never offers.');
		$this->assertSame(array(), $refused->probeDispatch('bool__gen_chardata__1'),
			'Nor may a typed trigger reach a handler such a screen overrides.');

		$allowed = $this->makeProbe(array('e-multiselect' => array(1, 2)), 'AdminUiBatchOverrideProbeFixture');
		$calls = $allowed->probe('gen_ip__127.0.0.1');

		$this->assertCount(1, $calls, 'The override still runs for a field the dropdown offers.');
		$this->assertCount(1, $allowed->dispatched);
		$this->assertSame('listBatch', $allowed->dispatched[0]['handler']);
	}
}
