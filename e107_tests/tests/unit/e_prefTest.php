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

/*		public function testRemoveData()
		{

		}

		public function testClearPrefCache()
		{

		}

		public function testValidate()
		{

		}

		public function testReset()
		{

		}

		public function test__construct()
		{

		}

		public function testSetPref()
		{

		}

		public function testLoadData()
		{

		}

		public function testSave()
		{

		}

		public function testGet()
		{

		}

		public function testRemovePref()
		{

		}

		public function testLoad()
		{

		}

		public function testSetOptionSerialize()
		{

		}

		public function testRemove()
		{

		}

		public function testSetData()
		{

		}

		public function testAddData()
		{

		}

		public function testDelete()
		{

		}

		public function testUpdatePref()
		{

		}*/

		public function testGetPref()
		{
			$result = $this->pref->getPref();

			$this->assertIsArray($result);
			$this->assertArrayHasKey('maintainance_flag', $result);

		}
/*
		public function testSetOptionBackup()
		{

		}

		public function testSet()
		{

		}

		public function testUpdate()
		{

		}

		public function testAdd()
		{

		}
*/
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
			$this->rows[$prefid] = $prefid;

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

	}
