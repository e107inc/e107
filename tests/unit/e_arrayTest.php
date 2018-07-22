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
			$actual = $this->arrObj->unserialize(stripslashes($string_4));
			$this->assertArrayHasKey('json', $actual);


		}
/*

		public function testStore()
		{

		}

		public function testSerialize()
		{

		}*/
	}
