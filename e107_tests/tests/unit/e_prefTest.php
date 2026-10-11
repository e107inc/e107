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

		/**
		 * Rows of every kind of value a preference can hold, each with whether JSON carries it exactly.
		 *
		 * @return array
		 */
		public function cachedRowProvider()
		{
			return array(
				'strings' => array(array('plain' => 'text', 'empty' => '', 'quoted' => "it's \"x\" \\ /", 'unicode' => "\xc3\x9cn\xc3\xafc\xc3\xb8d\xc3\xa9 \xe2\x9c\x93", 'markup' => '<a href="/x">x</a>', 'nul' => "a\0b"), true),
				'integers and numeric strings' => array(array('int' => 7, 'negative' => -7, 'zero' => 0, 'numeric' => '7', 'padded' => '007', 'largest' => PHP_INT_MAX, 'decimal' => '1.50'), true),
				'fractions' => array(array('half' => 1.5, 'negative' => -2.25, 'tiny' => 1.0E-300), true),
				'a fraction JSON rounds below PHP 7.1' => array(array('inexact' => 0.1 + 0.2), PHP_VERSION_ID >= 70100),
				'a whole float, an integer in var_export() before PHP 7' => array(array('whole' => 2.0), PHP_MAJOR_VERSION < 7),
				'negative zero, an integer in var_export() before PHP 7' => array(array('negative_zero' => -0.0), PHP_MAJOR_VERSION < 7),
				'an infinite float' => array(array('limit' => INF), false),
				'booleans and null' => array(array('on' => true, 'off' => false, 'none' => null), true),
				'nested arrays' => array(array('list' => array('a', 'b'), 'sparse' => array(3 => 'c', 1 => 'd'), 'map' => array('k' => array('deep' => array(1, '1', 1.5, true, null))), 'none' => array()), true),
				'a whole float nested' => array(array('map' => array('k' => array('deep' => array(1, 1.0)))), PHP_MAJOR_VERSION < 7),
				'keys' => array(array(0 => 'zero', -1 => 'negative', '' => 'empty', '01' => 'padded'), true),
				'bytes that are not UTF-8' => array(array('plain' => 'text', 'latin1' => "caf\xe9"), false),
				'no preferences' => array(array(), false),
			);
		}

		/**
		 * @dataProvider cachedRowProvider
		 * @param array $row
		 * @param bool $json
		 */
		public function testARowCachedByASaveReadsBackAsItsExportedStringDoes($row, $json)
		{
			$id = $this->expectRow('test_pref_cache_saved');
			$exported = e107::serialize($row, false);

			$this->defineProbe('e_pref_cache_probe', '
					public function writeCache($cache_string)
					{
						return $this->setPrefCache($cache_string, true);
					}
			');

			$writer = new e_pref_cache_probe($id);
			$writer->writeCache($exported);

			$this->assertCachedAs($id, $exported, $json);
		}

		/**
		 * Rows as the database holds them, each with whether it is stored by serialize() and whether JSON carries it exactly.
		 *
		 * @return array
		 */
		public function storedRowProvider()
		{
			return array(
				'a row JSON carries' => array(array('name' => 'value', 'count' => 3, 'ratio' => 1.5, 'list' => array('a', 'b'), 'off' => false, 'none' => null), false, true),
				'a row JSON cannot carry' => array(array('name' => 'value', 'limit' => INF), false, false),
				'a serialize() row holding text its exported form reads differently' => array(array('name' => 'a =&gt; b', 'count' => 3), true, true),
			);
		}

		/**
		 * @dataProvider storedRowProvider
		 * @param array $row
		 * @param bool $serialized
		 * @param bool $json
		 */
		public function testARowCachedByALoadReadsBackAsItsExportedStringDoes($row, $serialized, $json)
		{
			$id = $this->expectRow('test_pref_cache_loaded');
			e107::getDb()->insert('core', array('e107_name' => $id, 'e107_value' => $serialized ? serialize($row) : e107::serialize($row, false)));

			$loader = $this->make('e_pref');
			$loader->__construct($id);
			$loader->setOptionSerialize($serialized);
			$loader->load();

			$this->assertCachedAs($id, e107::serialize($row, false), $json);
		}

		public function testARowJsonCarriesExactlyIsCachedAsJson()
		{
			$id = 'test_pref_cache_json';
			$row = array('name' => 'value', 'count' => 3, 'list' => array('a', 'b'), 'off' => false);
			$this->openPref($id)->loadData($row, false)->save(false, true, false);

			$cached = e107::getCache()->retrieve_sys('Config_'.$id, false, true);

			$this->assertSame($row, json_decode($cached, true), "the cache file should hold the row as JSON:\n".$cached);
		}

		public function testACacheFileInTheExportedFormIsStillRead()
		{
			$id = $this->expectRow('test_pref_cache_exported');
			$row = array('name' => 'value', 'count' => 3, 'list' => array('a', 'b'), 'off' => false);
			e107::getCache()->set_sys('Config_'.$id, e107::serialize($row, false), true);

			$reader = $this->make('e_pref');
			$reader->__construct($id);
			$reader->load();

			$this->assertSame($row, $reader->getPref());
		}

		/**
		 * Asserts a row's cache file is JSON or the exported form, and that reading it loads what the exported string does, compared serialised because assertSame() takes -0.0 for 0.0.
		 *
		 * @param string $id
		 * @param string $exported
		 * @param bool $json
		 * @return void
		 */
		private function assertCachedAs($id, $exported, $json)
		{
			$cached = e107::getCache()->retrieve_sys('Config_'.$id, false, true);

			$this->assertIsString($cached, 'the row should have a cache file');
			$this->assertSame($json ? 'JSON' : 'exported', in_array(substr($cached, 0, 1), array('{', '['), true) ? 'JSON' : 'exported', "the cache file:\n".$cached);
			$this->assertSame(serialize(e107::unserialize($exported)), serialize($this->openPref($id)->getPref()));
		}

		/**
		 * A preference object standing alone, on its own row, the way a second
		 * request would hold one.
		 *
		 * @param string $prefid
		 * @param string $class
		 * @return e_pref
		 */
		private function openPref($prefid, $class = 'e_pref')
		{
			$this->expectRow($prefid);

			$pref = $this->make($class);
			$pref->__construct($prefid);
			$pref->load();

			return $pref;
		}

		/**
		 * Declares an e_pref subclass, in a string because Codeception parses test files before e107 has defined e_pref.
		 *
		 * @param string $class
		 * @param string $body
		 * @return void
		 */
		private function defineProbe($class, $body)
		{
			if(class_exists($class, false))
			{
				return;
			}

			eval('class ' . $class . ' extends e_pref {' . $body . '}');
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
