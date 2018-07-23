<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_arrayTest extends \Codeception\Test\Unit
	{
		/** @var e_array */
		private $arrObj;

		protected function _before()
		{
			try
			{
				$this->arrObj = $this->make('e_array');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_array object");
			}
		}

/*
		public function testLoad()
		{

		}
*/
		public function testUnserialize()
		{
			// Buggy value test.

			$string_1 = "\$data = array(
			\'buggy_array\' => \'some value\',
			);
			";

			$actual = $this->arrObj->unserialize($string_1);
			$this->assertArrayHasKey('buggy_array', $actual);


			// var_export format test with slashes ----

			$string_2 = "array(\'var_export\' => \'some value\',)";
			$actual = $this->arrObj->unserialize($string_2);
			$this->assertArrayHasKey('var_export', $actual);


			// var_export format test without slashes ----

			$string_3 = "array('var_export' => 'some value',)";
			$actual = $this->arrObj->unserialize($string_3);
			$this->assertArrayHasKey('var_export', $actual);


			// json value test.

			$string_4 = '{ "json": "some value" }';
			$actual = $this->arrObj->unserialize($string_4);
			$this->assertArrayHasKey('json', $actual);

			// case linkwords prefs.
			$string_5 = "array (
				'OLDDEFAULT' => '',
				'TITLE' => '',
				'SUMMARY' => 1,
				'BODY' => 1,
				'DESCRIPTION'=> 1,
				'USER_TITLE' => '',
				'USER_BODY' => 1,
				'LINKTEXT' => '',
				'RAWTEXT' => ''
			)";

			$actual = $this->arrObj->unserialize($string_5);
			$this->assertArrayHasKey('TITLE', $actual);


		}
/*

		public function testStore()
		{

		}

		public function testSerialize()
		{

		}*/
	}
