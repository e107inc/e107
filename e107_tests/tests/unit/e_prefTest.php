<?php


	class e_prefTest extends \Codeception\Test\Unit
	{

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

	}
