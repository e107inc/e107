<?php


	class e_prefTest extends \Codeception\Test\Unit
	{
		use \Test\BootedCli;

		/** @var e_pref */
		protected $pref;

		protected function _before()
		{

			try
			{
				$this->pref = $this->make('e_pref');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

			$this->pref->__construct('core');
			$this->pref->load();

		}

		/**
		 * Rows a test registered through {@see e_prefTest::expectRow()}.
		 *
		 * @var string[]
		 */
		protected $rows = array();

		protected function _after()
		{
			foreach($this->rows as $prefid)
			{
				e107::getDb()->delete('core', "e107_name = '".$prefid."'");
				e107::getCache()->clear_sys('Config_'.$prefid);
			}

			$this->rows = array();
		}

		/**
		 * Register a row, and the cache file that stands for it, for removal in {@see e_prefTest::_after()}.
		 *
		 * @param string $prefid
		 * @return string the row name, for the assertions that read it back
		 */
		private function expectRow($prefid)
		{
			$this->rows[$prefid] = $prefid;

			return $prefid;
		}

		/**
		 * Read a row back from the database rather than from the cache file.
		 *
		 * @param string $prefid
		 * @return array
		 */
		private function readStored($prefid)
		{
			$sql = e107::getDb();

			if(!$sql->select('core', 'e107_value', 'e107_name = :name', array('name' => $prefid)))
			{
				return array();
			}

			$row = $sql->fetch();

			return e107::unserialize($row['e107_value']);
		}

		/**
		 * A plugin or theme preference object, built the way a plugin builds one.
		 *
		 * @param string $class e_plugin_pref or e_theme_pref
		 * @param string $id folder or theme name
		 * @param string $multi_row
		 * @return e_pref
		 */
		private function openOwnedPref($class, $id, $multi_row = '')
		{
			$pref = $this->make($class);
			$pref->__construct($id, $multi_row, false);
			$pref->load();

			return $pref;
		}

		public function ownedPrefProvider()
		{
			return array(
				'plugin' => array('e_plugin_pref', 'plugin_'),
				'theme'  => array('e_theme_pref', 'theme_'),
			);
		}

		/**
		 * @dataProvider ownedPrefProvider
		 * @param string $class
		 * @param string $prefix
		 */
		public function testMultiRowPrefReadsItsOwnRow($class, $prefix)
		{
			$id = 'e107help_pref_multi_row';
			$this->expectRow($prefix.$id);
			$this->expectRow($prefix.$id.'_second');

			$this->openOwnedPref($class, $id)->set('kept', 'base')->save(false, true, false);

			$this->assertSame(array(), $this->openOwnedPref($class, $id, 'second')->getPref(), 'a row nobody has saved holds nothing, whatever the base row\'s cache file says');
		}

		/**
		 * @dataProvider ownedPrefProvider
		 * @param string $class
		 * @param string $prefix
		 */
		public function testMultiRowPrefSaveLeavesTheBaseRowAlone($class, $prefix)
		{
			$id = 'e107help_pref_multi_row';
			$row = $this->expectRow($prefix.$id.'_second');
			$this->expectRow($prefix.$id);

			$this->openOwnedPref($class, $id)->set('kept', 'base')->save(false, true, false);
			$this->openOwnedPref($class, $id, 'second')->set('other', 'value')->save(false, true, false);

			$this->assertSame(array('other' => 'value'), $this->readStored($row), 'a save stores what was set on the row and nothing of the base row');
			$this->assertSame(array('kept' => 'base'), $this->openOwnedPref($class, $id)->getPref(), 'the base row reads its own preferences back after a save to another row');
		}


		public function testGetPref()
		{
			$result = $this->pref->getPref();

			$this->assertIsArray($result);
			$this->assertArrayHasKey('maintainance_flag', $result);

		}
		public function testAddPref()
		{
			$this->pref->addPref('test_preference', "my custom preference");

			$result = $this->pref->get('test_preference');
			$expected = "my custom preference";
			$this->assertSame($expected, $result);

			// test multidimentional
			$this->pref->addPref('test_list/key1', "value1");
			$this->pref->addPref('test_list/key2', "value2");
			$result = $this->pref->get('test_list');
			$expected = array (
			  'key1' => 'value1',
			  'key2' => 'value2',
			);

			$this->assertSame($expected, $result);

		}

		/**
		 * The branch ends in trigger_error(E_USER_NOTICE), so that one notice is passed over and every other diagnostic is handed back to the runner's own handler.
		 *
		 * @return void
		 */
		public function testSilentSaveKeepsAFailureOffTheScreen()
		{
			require_once(__DIR__ . '/fixtures/PrefSqlErrorProbeFixture.php');

			$prefid = 'test_pref_silent_failure';

			$pref = $this->make('PrefSqlErrorProbeFixture');
			$pref->__construct($prefid);
			$pref->armSqlError();

			$mes = e107::getMessage();
			$mes->reset(false, false, true);

			$previous = set_error_handler(function($no, $str, $file = '', $line = 0) use (&$previous) {
				if($no === E_USER_NOTICE && $str === 'Settings not saved')
				{
					return true;
				}

				return $previous ? call_user_func($previous, $no, $str, $file, $line) : false;
			});

			try
			{
				$saved = $pref->save(false, true, false);
			}
			finally
			{
				restore_error_handler();
			}

			$displayed = $mes->hasMessage(E_MESSAGE_ERROR, 'default', true) || $mes->hasMessage(E_MESSAGE_ERROR, $prefid, true);
			$mes->reset(false, false, true);

			$this->assertFalse($saved, 'a save that could not write should report failure');
			$this->assertFalse($displayed, 'a caller asking for no messages should not get a red block');
		}

		/**
		 * A constant cannot be undefined once the process has defined it, so the save runs in a booted child.
		 * That child skips online tracking, because the boot's own preference save loads the admin phrases and would answer the question before this save does.
		 */
		public function testASaveOutsideTheAdminAreaDefinesNoAdminPhrases()
		{
			$row = $this->expectRow('test_pref_admin_lan');
			$begin = '@@e107help-adlan8-begin@@';
			$end = '@@e107help-adlan8-end@@';

			$php = "\$before = defined('ADLAN_8'); "
				."\$pref = new e_pref('".$row."'); "
				."\$pref->set('probe', uniqid('', true)); "
				."\$saved = \$pref->save(false, true, false); "
				."echo '".$begin."', var_export(\$saved, true), '|', "
				."\$before ? 'defined before the save' : (defined('ADLAN_8') ? 'defined by the save' : 'undefined'), '".$end."';";

			list($output, $status) = $this->runInBootedCli($php, '', array('cli' => true, 'no_online' => true));

			$printed = implode("\n", $output);
			$matches = array();

			$this->assertSame(0, $status, "the save never returned:\n".$printed);
			$this->assertSame(1, preg_match('/'.preg_quote($begin, '/').'(.*)'.preg_quote($end, '/').'/s', $printed, $matches),
				"the child printed nothing:\n".$printed);

			$measured = explode('|', $matches[1]);

			$this->assertSame('true', $measured[0],
				"the child's save wrote nothing, so the rest of this measures nothing:\n".$printed);
			$this->assertSame('undefined', $measured[1],
				'a preference saved outside the admin area must not pull the admin language pack in');
		}

	}
