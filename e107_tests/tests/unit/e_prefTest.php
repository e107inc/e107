<?php


	class e_prefTest extends \Test\Unit
	{

		/** @var e_pref */
		protected $pref;

		/** @var bool whether the global $pref existed on entry */
		protected $hadGlobalPref = false;

		/** @var mixed the global $pref as found, restored in _after() */
		protected $savedGlobalPref = null;

		/**
		 * The object under test is aliased 'core', and every mutator on a
		 * core-aliased e_pref mirrors its own data into the global $pref for
		 * backward compatibility. So reset(), loadData() and set() here do not
		 * just change this object, they replace the array the rest of the
		 * process reads, and the journal tests deliberately reduce it to a
		 * single key. Note the constructor mirrors too, by way of loadData(),
		 * so the value has to be taken before that runs.
		 */
		protected function _before()
		{
			$this->hadGlobalPref = array_key_exists('pref', $GLOBALS);
			$this->savedGlobalPref = $this->hadGlobalPref ? $GLOBALS['pref'] : null;

			try
			{
				$this->pref = $this->make('e_pref');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

			// 'core' is the alias; the row it stands for is SitePrefs, per
			// e_core_pref::$aliases. Constructed with 'core' as the prefid the
			// object looked for a row of that name, which no e107 database has,
			// and so loaded nothing whenever the alias-keyed cache happened to
			// be cold. It read real preferences only by borrowing that cache.
			$this->pref->__construct('SitePrefs', 'core');
			$this->pref->load();

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
		 * A preference row holds one serialized array shared by every writer, so
		 * save() has to know which preferences this object actually changed rather
		 * than writing back the whole array it happens to be holding. These pin the
		 * record it keeps to answer that.
		 */

		public function testJournalIsEmptyAfterLoad()
		{
			$this->assertSame(array(), $this->pref->getJournal());
			$this->assertFalse($this->pref->isJournalReplaced());
		}

		public function testJournalRecordsSimpleSetters()
		{
			$this->pref->set('journal_a', 'one');
			$this->pref->setPref('journal_b', 'two');

			$this->assertSame(array(
				array('set', array('journal_a', 'one', false)),
				array('setPref', array('journal_b', 'two')),
			), $this->pref->getJournal());
		}

		public function testJournalRecordsRemovals()
		{
			$this->pref->set('journal_gone', 'here');
			$this->pref->remove('journal_gone');
			$this->pref->removePref('journal_path/leaf');

			$journal = $this->pref->getJournal();

			$this->assertSame(array('remove', array('journal_gone')), $journal[1]);
			$this->assertSame(array('removeData', array('journal_path/leaf')), $journal[2]);
		}

		public function testJournalRecordsArraySetterPerKey()
		{
			$this->pref->setPref(array('journal_x' => 1, 'journal_y' => 2));

			// Recorded per key, not as one array write, so a replay merges rather
			// than replacing preferences the caller never mentioned.
			$this->assertSame(array(
				array('setData', array('journal_x', 1, false)),
				array('setData', array('journal_y', 2, false)),
			), $this->pref->getJournal());
		}

		public function testJournalSkipsAddOfAnExistingPreference()
		{
			$this->pref->set('journal_taken', 'original');
			$this->pref->add('journal_taken', 'ignored');

			// add() did nothing, so replaying it must not revive the preference if
			// another writer removed it in the meantime.
			$this->assertSame(array(
				array('set', array('journal_taken', 'original', false)),
			), $this->pref->getJournal());
			$this->assertSame('original', $this->pref->get('journal_taken'));
		}

		public function testJournalSkipsUpdateOfAMissingPreference()
		{
			$this->pref->update('journal_absent', 'nope');

			// update() did nothing, so replaying it must not write a value this
			// object never held.
			$this->assertSame(array(), $this->pref->getJournal());
		}

		public function testLoadDataMarksTheObjectAsAWholeRow()
		{
			$this->pref->set('journal_before', 'recorded');
			$this->pref->loadData(array('journal_whole' => 'row'), false);

			// The caller handed over a complete array, so it is the row to write and
			// the individual changes recorded before it no longer describe anything.
			$this->assertTrue($this->pref->isJournalReplaced());
			$this->assertSame(array(), $this->pref->getJournal());
		}

		public function testResetMarksTheObjectAsAWholeRow()
		{
			$this->pref->reset();

			$this->assertTrue($this->pref->isJournalReplaced());
			$this->assertSame(array(), $this->pref->getJournal());
		}

		public function testWholeRowObjectStopsRecording()
		{
			$this->pref->reset();
			$this->pref->set('journal_after_reset', 'value');

			$this->assertTrue($this->pref->isJournalReplaced());
			$this->assertSame(array(), $this->pref->getJournal());
		}

		/**
		 * Rows written by these live on in the shared fixture database, so each one
		 * is removed again along with the cache file save() writes for it.
		 *
		 * @var string[]
		 */
		protected $rows = array();

		protected function _after()
		{
			foreach($this->rows as $prefid)
			{
				e107::getDb()->createQueryBuilder()->delete('core')->where('e107_name', $prefid)->execute();
				e107::getCache()->clear_sys('Config_'.$prefid);
			}

			$this->rows = array();

			if($this->hadGlobalPref)
			{
				$GLOBALS['pref'] = $this->savedGlobalPref;
			}
			else
			{
				unset($GLOBALS['pref']);
			}
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
		 * Read a row back from the database rather than from the cache file, which
		 * is where a lost write would still be hiding.
		 *
		 * @param string $prefid
		 * @return array
		 */
		private function readStored($prefid)
		{
			$row = e107::getDb()->createQueryBuilder()
				->select('e107_value')->from('core')
				->where('e107_name', $prefid)
				->fetchRow();

			return empty($row) ? array() : e107::unserialize($row['e107_value']);
		}

		public function testConcurrentWritersBothSurvive()
		{
			$id = 'test_pref_concurrent';
			$this->openPref($id)->set('shared', 'seed')->save(false, true, false);

			// Two requests holding the row as it stood before either of them wrote.
			$first = $this->openPref($id);
			$second = $this->openPref($id);

			$first->set('by_first', 'one')->save(false, true, false);
			$second->set('by_second', 'two')->save(false, true, false);

			$stored = $this->readStored($id);

			// The second writer must not take the first one's preference with it.
			$this->assertSame('seed', $stored['shared']);
			$this->assertSame('one', $stored['by_first']);
			$this->assertSame('two', $stored['by_second']);
		}

		public function testConcurrentWritersOnNestedPaths()
		{
			$id = 'test_pref_nested';
			$this->openPref($id)->set('branch', array('keep' => 'kept'))->save(false, true, false);

			$first = $this->openPref($id);
			$second = $this->openPref($id);

			$first->setPref('branch/from_first', 'one')->save(false, true, false);
			$second->setPref('branch/from_second', 'two')->save(false, true, false);

			$stored = $this->readStored($id);

			$this->assertSame('kept', $stored['branch']['keep']);
			$this->assertSame('one', $stored['branch']['from_first']);
			$this->assertSame('two', $stored['branch']['from_second']);
		}

		public function testRemovalIsNotUndoneByAConcurrentWriter()
		{
			$id = 'test_pref_removal';
			$this->openPref($id)->setPref(array('doomed' => 'here', 'other' => 'stays'))->save(false, true, false);

			$remover = $this->openPref($id);
			$writer = $this->openPref($id);

			$remover->remove('doomed')->save(false, true, false);
			$writer->set('added', 'yes')->save(false, true, false);

			$stored = $this->readStored($id);

			// The writer still held 'doomed' in memory. Replaying only what it
			// changed must not put it back.
			$this->assertArrayNotHasKey('doomed', $stored);
			$this->assertSame('stays', $stored['other']);
			$this->assertSame('yes', $stored['added']);
		}

		public function testSavingAnUnchangedObjectWritesNothing()
		{
			$id = 'test_pref_unchanged';
			$this->openPref($id)->set('value', 'same')->save(false, true, false);

			$pref = $this->openPref($id);
			$pref->set('value', 'same');

			// Forced, but there is nothing to write, so it reports no change rather
			// than rewriting every preference in the row.
			$this->assertSame(0, $pref->save(false, true, false));
			$this->assertSame('same', $this->readStored($id)['value']);
		}

		public function testWholeRowCallerStillReplacesEverything()
		{
			$id = 'test_pref_whole';
			$this->openPref($id)->setPref(array('old' => 'gone', 'other' => 'also gone'))->save(false, true, false);

			// loadData() states the object IS the row, so it is written entire and
			// preferences absent from it are meant to go.
			$replacer = $this->openPref($id);
			$replacer->loadData(array('only' => 'this'), false);
			$replacer->save(false, true, false);

			$this->assertSame(array('only' => 'this'), $this->readStored($id));
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
			e107::getDb()->createQueryBuilder()->insert('core')
				->values(array('e107_name' => $id, 'e107_value' => $serialized ? serialize($row) : e107::serialize($row, false)))
				->execute();

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

		private function defineRaceProbe()
		{
			// Lands another writer's row in between this object's read and its
			// write, which is the window the compare-and-swap exists to close.
			$this->defineProbe('e_pref_race_probe', '
					public $raceArmed = true;

					protected function replayJournal(array $base)
					{
						if($this->raceArmed)
						{
							$this->raceArmed = false;

							$rival = $base;
							$rival["rival"] = "won";

							e107::getDb()->createQueryBuilder()->update("core")
								->set("e107_value", e107::serialize($rival, false))
								->where("e107_name", $this->prefid)
								->execute();
						}

						return parent::replayJournal($base);
					}
			');
		}

		/**
		 * Declares a probe that lands a different row before every attempt, so no write can name a value storage still holds and the retries run out.
		 *
		 * @return void
		 */
		private function defineConflictProbe()
		{
			$this->defineProbe('e_pref_conflict_probe', '
					public $rivals = 0;

					protected function replayJournal(array $base)
					{
						$this->rivals++;

						$rival = $base;
						$rival["rival"] = $this->rivals;

						e107::getDb()->createQueryBuilder()->update("core")
							->set("e107_value", e107::serialize($rival, false))
							->where("e107_name", $this->prefid)
							->execute();

						return parent::replayJournal($base);
					}
			');
		}

		public function testWriteIsRetriedWhenTheRowChangesUnderneathIt()
		{
			$this->defineRaceProbe();

			$id = 'test_pref_race';
			$this->openPref($id)->set('shared', 'seed')->save(false, true, false);

			$pref = $this->openPref($id, 'e_pref_race_probe');

			$this->assertTrue($pref->set('ours', 'kept')->save(false, true, false));

			$stored = $this->readStored($id);

			// The rival landed between this save's read and its write, so the first
			// attempt wrote nothing and the retry merged over the rival's result.
			$this->assertSame('won', $stored['rival']);
			$this->assertSame('kept', $stored['ours']);
			$this->assertSame('seed', $stored['shared']);
			$this->assertFalse($pref->raceArmed, 'the race should have been run exactly once');
		}

		public function testSilentSaveKeepsAFailureOffTheScreen()
		{
			$this->defineConflictProbe();

			$id = 'test_pref_conflict';
			$this->openPref($id)->set('shared', 'seed')->save(false, true, false);

			$pref = $this->openPref($id, 'e_pref_conflict_probe');
			$pref->set('ours', 'lost');

			$mes = e107::getMessage();
			$mes->reset(false, false, true);

			$saved = $pref->save(false, true, false);

			$displayed = $mes->hasMessage(E_MESSAGE_ERROR, 'default', true) || $mes->hasMessage(E_MESSAGE_ERROR, $id, true);
			$mes->reset(false, false, true);

			$this->assertFalse($saved);
			$this->assertSame(3, $pref->rivals, 'every attempt should have lost its race');
			$this->assertFalse($displayed, 'a caller asking for no messages should not get a red block');
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

		public function testPluginPrefDeleteRemovesTheRow()
		{
			$folder = 'e107help_pref_delete';
			$row = $this->expectRow('plugin_'.$folder);

			$this->openOwnedPref('e_plugin_pref', $folder)->set('kept', 'value')->save(false, true, false);
			$this->assertSame(array('kept' => 'value'), $this->readStored($row));
			$this->assertSame('value', $this->openOwnedPref('e_plugin_pref', $folder)->get('kept'));

			$this->openOwnedPref('e_plugin_pref', $folder)->delete(null);

			$this->assertSame(array(), $this->readStored($row));
			$this->assertSame(array(), $this->openOwnedPref('e_plugin_pref', $folder)->getPref(), 'the cache file is where a deleted row lives on');
		}

		public function testThemePrefDeleteRemovesTheRow()
		{
			$theme = 'e107help_pref_delete';
			$row = $this->expectRow('theme_'.$theme);

			$this->openOwnedPref('e_theme_pref', $theme)->set('kept', 'value')->save(false, true, false);
			$this->assertSame(array('kept' => 'value'), $this->readStored($row));

			$this->openOwnedPref('e_theme_pref', $theme)->delete(null);

			$this->assertSame(array(), $this->readStored($row));
			$this->assertSame(array(), $this->openOwnedPref('e_theme_pref', $theme)->getPref());
		}

		public function testPluginPrefDeleteRemovesAMultiRowRow()
		{
			$folder = 'e107help_pref_delete';
			$row = $this->expectRow('plugin_'.$folder.'_second');
			$neighbour = $this->expectRow('plugin_'.$folder);

			$this->openOwnedPref('e_plugin_pref', $folder)->set('kept', 'base')->save(false, true, false);
			$this->openOwnedPref('e_plugin_pref', $folder, 'second')->set('kept', 'value')->save(false, true, false);
			$this->assertSame(array('kept' => 'value'), $this->readStored($row));

			$this->openOwnedPref('e_plugin_pref', $folder, 'second')->delete(null);

			$this->assertSame(array(), $this->readStored($row));
			$this->assertSame(array('kept' => 'base'), $this->readStored($neighbour), 'a row the object does not own must survive');
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

			$this->assertSame(array('other' => 'value'), $this->readStored($row), 'the first save of a row stores what was set on it and nothing of the base row');
			$this->assertSame(array('kept' => 'base'), $this->openOwnedPref($class, $id)->getPref(), 'the base row reads its own preferences back after a save to another row');
		}

		/**
		 * A constant cannot be undefined once the process has defined it, so the save runs in a booted child.
		 * That child skips online tracking and finds the plugin language list already built, because either one loads the admin phrases itself and would answer the question before the save does.
		 */
		public function testASaveOutsideTheAdminAreaDefinesNoAdminPhrases()
		{
			\e107\Language\GlobalLanguageList::invalidate();
			\e107\Language\GlobalLanguageList::plugins();

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
