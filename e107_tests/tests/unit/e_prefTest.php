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

	}
