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




	}
