<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A posted etrigger_batch must not be dispatched as a column assignment unless
 * its field segment is one the batch dropdown itself offers, which is what
 * e_admin_form_ui::renderBatchFilter() means by a field carrying 'batch'.
 * e_admin_controller_ui::_handleListBatch() stops otherwise, on every trigger that names a
 * field, unless the screen declares a handler for that field itself.
 * e_front_tree_model::batchUpdate() refuses a name outside the identifier grammar rather than
 * letting the query builder throw.
 *
 * The two sides of that dropdown must also agree on the batch options a field's optArray asks
 * for: the menu spells them attach_all__<field> and deattach_all__<field>, and what it offers
 * one at a time is what those two entries write.
 */
class adminUiBatchTriggerTest extends \Test\Unit
{
	/** @var string prefixed scratch table */
	private $table;

	/** @var array|null registry entries replaced by stubUserRegistry() */
	private $savedUserRegistry;

	protected function _before()
	{
		$this->table = MPREFIX . 'admin_batch_guard';
	}

	protected function _after()
	{
		e107::getDb()->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
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

		$request = new e_admin_request('mode=failed&action=list');
		$request->setPosted($posted);

		$probe = new $class();
		$probe->setRequest($request);
		$probe->setFields(array(
			'checkboxes'    => array('title' => '', 'type' => null, 'forced' => '1', 'toggle' => 'e-multiselect'),
			'gen_ip'        => array('title' => 'IP', 'type' => 'ip', 'data' => 'str', 'batch' => true),
			'gen_chardata'  => array('title' => 'Description', 'type' => 'method', 'data' => 'str',
				'writeParms' => array('classlist' => 'member', 'addAll' => 1, 'clearAll' => 1)),
			'gen_options'   => array('title' => 'Options', 'type' => 'comma', 'data' => 'str', 'batch' => true,
				'writeParms' => array('optArray' => array(1 => 'Red', 2 => 'Blue', 'addAll' => 1, 'clearAll' => 1))),
			'gen_simple'    => array('title' => 'Tags', 'type' => 'comma', 'data' => 'str', 'batch' => true,
				'writeParms' => array('optArray' => array('Red', 'Blue', 'addAll' => 1), 'simple' => 1)),
			'gen_multiple'  => array('title' => 'Pick', 'type' => 'dropdown', 'data' => 'str', 'batch' => true,
				'writeParms' => array('multiple' => 1, 'optArray' => array(3 => 'Green', 4 => 'Grey',
					'addAll' => 1, 'clearAll' => 1))),
			'gen_checks'    => array('title' => 'Boxes', 'type' => 'checkboxes', 'data' => 'str', 'batch' => true,
				'writeParms' => array('optArray' => array(7 => 'Seven', 8 => 'Eight',
					'addAll' => 1, 'clearAll' => 1))),
			'gen_empty'     => array('title' => 'Nothing', 'type' => 'comma', 'data' => 'str', 'batch' => true,
				'writeParms' => array('optArray' => array('addAll' => 1, 'clearAll' => 1))),
			'gen_method'    => array('title' => 'Custom', 'type' => 'method', 'data' => 'str', 'batch' => true,
				'writeParms' => array('classlist' => 'member', 'addAll' => 1, 'clearAll' => 1)),
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

	public function testUndeclaredBatchTriggerNeverReachesBatchUpdate()
	{
		// banlist.php's failed-login list declares a 'delete-all' batch option
		// that names no column; it must not arrive as one.
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('delete-all');

		$this->assertSame(array(), $calls,
			'A batch trigger that is not a declared field must not reach batchUpdate().');
	}

	public function testDeclaredButUnbatchedFieldNeverReachesBatchUpdate()
	{
		// A column the dropdown never offers, because it carries no 'batch'.
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('gen_chardata__x');

		$this->assertSame(array(), $calls,
			'Only a field the batch dropdown offers may be assigned through a batch trigger.');
	}

	public function testDeclaredBatchTriggerStillReachesBatchUpdate()
	{
		$calls = $this->makeProbe(array('e-multiselect' => array(1, 2)))->probe('gen_ip__127.0.0.1');

		$this->assertCount(1, $calls, 'A declared field must still be batched.');
		$this->assertSame('gen_ip', $calls[0]['field']);
		$this->assertSame('127.0.0.1', $calls[0]['value']);
		$this->assertSame(array('1', '2'), $calls[0]['ids']);
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
			'sefgen'       => array('sefgen__gen_chardata__gen_ip'),
			'bool'         => array('bool__gen_chardata__1'),
			'boolreverse'  => array('boolreverse__gen_chardata'),
			'attach'       => array('attach__gen_chardata__253'),
			'deattach'     => array('deattach__gen_chardata__253'),
			'attach_all'   => array('attach_all__gen_chardata'),
			'deattach_all' => array('deattach_all__gen_chardata'),
			'ucaddall'     => array('ucaddall__gen_chardata'),
			'ucdelall'     => array('ucdelall__gen_chardata'),
		);
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

	public function testAddAllBatchOptionReachesTheCommaHandler()
	{
		$dispatched = $this->dispatchOf('attach_all__gen_options');

		$this->assertCount(1, $dispatched, 'The "(Add all)" entry the batch dropdown renders must be dispatched.');
		$this->assertSame('addAll', $dispatched[0]['handler']);
		$this->assertSame('gen_options', $dispatched[0]['field']);
		$this->assertSame(array(1, 2), $dispatched[0]['value'],
			'The field is written its own option list, keyed as the record stores it and without the two menu keys.');
	}

	public function testClearAllBatchOptionReachesTheCommaHandler()
	{
		$dispatched = $this->dispatchOf('deattach_all__gen_options');

		$this->assertCount(1, $dispatched, 'The "(Clear all)" entry the batch dropdown renders must be dispatched.');
		$this->assertSame('clearAll', $dispatched[0]['handler']);
		$this->assertSame('gen_options', $dispatched[0]['field']);
		$this->assertSame(array(1, 2), $dispatched[0]['value'],
			'Only the options the field declares may be withdrawn, which is what the userclass spelling hands over.');
	}

	public function testBatchOptionListFollowsTheSimpleOptionShape()
	{
		$dispatched = $this->dispatchOf('attach_all__gen_simple');

		$this->assertCount(1, $dispatched);
		$this->assertSame(array('Red', 'Blue'), $dispatched[0]['value'],
			'A simple optArray stores its labels, so the menu offers the values and so must the batch.');
	}

	public function testBatchOptionListFollowsTheDropdownThatTakesMultipleValues()
	{
		$dispatched = $this->dispatchOf('attach_all__gen_multiple');

		$this->assertCount(1, $dispatched);
		$this->assertSame(array(3, 4), $dispatched[0]['value'],
			'The menu reads a multiple dropdown as a comma field, and the batch must read it the same way.');
	}

	public function testBatchOptionTriggersStopOnAFieldThatOffersNoSuchEntry()
	{
		$this->assertSame(array(), $this->dispatchOf('attach_all__gen_type'),
			'A field that declares no "(Add all)" entry must not have one dispatched, whatever its writeParms hold.');
		$this->assertSame(array(), $this->dispatchOf('deattach_all__gen_simple'),
			'Nor "(Clear all)" on a field that declares only "(Add all)".');
		$this->assertSame(array(), $this->dispatchOf('attach_all__gen_method'),
			'Nor on a field whose type puts no such entry on the menu, whose writeParms would otherwise '
			.'be written into the record as if they were its options.');
	}

	public function testTheBatchMenuAndTheDispatcherAgreeOnEveryOptionListEntry()
	{
		$menu = $this->renderedBatchMenu();

		$this->assertCount(9, $menu['entries'],
			'The declaration set offers (Add all) on five fields and (Clear all) on four.');

		foreach($menu['entries'] as $trigger => $field)
		{
			$dispatched = $this->dispatchOf($trigger);
			$offered = isset($menu['options'][$field]) ? $menu['options'][$field] : array();

			if(empty($offered))
			{
				$this->assertSame(array(), $dispatched, $trigger . ' is offered for a field the same '
					.'dropdown lists no options for, so there is nothing to write.');
				continue;
			}

			$this->assertCount(1, $dispatched,
				$trigger . ' is on the batch dropdown, so the dispatcher must accept that spelling.');
			$this->assertSame($offered, array_map('strval', $dispatched[0]['value']),
				$trigger . ' must write the options the same dropdown offers one at a time.');
		}
	}

	/**
	 * The batch dropdown makeProbe()'s fields render: its (Add all) and (Clear all) triggers by
	 * field, and the option values it offers one at a time for each of them.
	 *
	 * @return array
	 */
	private function renderedBatchMenu()
	{
		$form = new e_admin_form_ui($this->makeProbe(array()));
		preg_match_all('/value=([\'"])((?:attach|deattach)[^\'"]*)\1/',
			$form->renderBatchFilter('batch'), $found);

		$menu = array('entries' => array(), 'options' => array());

		foreach($found[2] as $trigger)
		{
			$segment = explode('__', $trigger);

			if($segment[0] === 'attach_all' || $segment[0] === 'deattach_all')
			{
				$menu['entries'][$trigger] = $segment[1];
			}
			elseif($segment[0] === 'attach' && isset($segment[2]))
			{
				$menu['options'][$segment[1]][] = $segment[2];
			}
		}

		return $menu;
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

	public function testScreenDeclaringItsOwnFieldHandlerKeepsIt()
	{
		$dispatched = $this->dispatchOf('gen_chardata__x', 'AdminUiBatchCustomFieldProbeFixture');

		$this->assertCount(1, $dispatched,
			'handle<Action><Field>Batch() is looked up before the check, and stays the way a screen opts a field in.');
		$this->assertSame('genChardata', $dispatched[0]['handler']);
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

	private function makeTree()
	{
		$sql = e107::getDb();
		$sql->gen('DROP TEMPORARY TABLE IF EXISTS `' . $this->table . '`');
		$sql->gen('CREATE TEMPORARY TABLE `' . $this->table
			. '` (id INT NOT NULL, a VARCHAR(255) NULL)');
		$sql->gen("INSERT INTO `" . $this->table . "` (id, a) VALUES (1, 'orig_a')");

		$tree = new e_front_tree_model();
		$tree->setModelTable('admin_batch_guard');
		$tree->setFieldIdName('id');

		return $tree;
	}

	private function readColumnA()
	{
		$sql = e107::getDb();
		$sql->gen('SELECT a FROM `' . $this->table . '` WHERE id = 1');
		$row = $sql->fetch();

		return $row['a'];
	}

	public function testBatchUpdateRefusesAFieldNameThatIsNotAnIdentifier()
	{
		$tree = $this->makeTree();

		$this->assertFalse($tree->batchUpdate('delete-all', 'x', array(1), null, false),
			'batchUpdate() documents "false on error"; an unquotable field name must not throw.');
		$this->assertSame('orig_a', $this->readColumnA(), 'No query may run for a refused field name.');
	}

	public function testBatchUpdateStillUpdatesAValidField()
	{
		$tree = $this->makeTree();

		$this->assertNotFalse($tree->batchUpdate('a', 'new_a', array(1), null, false));
		$this->assertSame('new_a', $this->readColumnA());
	}
}
