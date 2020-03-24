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


		private function getSitePrefExample()
		{
			$data = '
$data = array (
  \'email_password\' => \'$2y$10$IpizFx.gp5USl98SLXwwbeod3SYF3M3raAQX0y01ETexzoutvdyWW\',
);
';


			return (string) $data;
		}




/*
		public function testLoad()
		{

		}
*/
		public function testUnserialize()
		{

			$src = codecept_data_dir()."unserializeTest.log";
			$stringFile_0 = file_get_contents($src);
			$actual = $this->arrObj->unserialize($stringFile_0);
			$this->assertArrayHasKey('email_password', $actual);


			// Check for legacy (corrupted) link-words preferences.
			$src = codecept_data_dir()."unserializeTest2.log";
			$stringFile_1 = file_get_contents($src);
			$actual = $this->arrObj->unserialize($stringFile_1);
			$this->assertArrayHasKey('lw_context_visibility', $actual);


			// Buggy value test -------.
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


			$tests = array(
				0   => array(
						'string' => $this->getSitePrefExample(),
						'expected' => array('email_password' => '$2y$10$IpizFx.gp5USl98SLXwwbeod3SYF3M3raAQX0y01ETexzoutvdyWW' )
						),
				1   => array(
						'string' => "{\n    \"hello\": \"h\u00e9ll\u00f2 w\u00f2rld\"\n}",
						'expected' => array('hello'=>'héllò wòrld')
						),


			);

			foreach($tests as $var)
			{
				$result = $this->arrObj->unserialize($var['string']);
				$this->assertEquals($var['expected'], $result);
			}
		//	var_dump($actual);

		}
/*

		public function testStore()
		{

		}
*/
		public function testSerialize()
		{

			$pref1      = array('hello'=>'world');
			$result1    = $this->arrObj->serialize($pref1);
			$expected1  = "array (\n  'hello' => 'world',\n)";
			$this->assertEquals($expected1,$result1);

			$pref2      = array();
			$result2    = $this->arrObj->serialize($pref2);
			$expected2  = null;
			$this->assertEquals($expected2,$result2);


			$pref3      = array();
			$result3    = $this->arrObj->serialize($pref3,true);
			$expected3  = null;
			$this->assertEquals($expected3,$result3);


			$pref4      = array();
			$result4    = $this->arrObj->serialize($pref4,'json');
			$expected4  = null;
			$this->assertEquals($expected4,$result4);

			$pref5      = array('hello'=>'world');
			$result5    = $this->arrObj->serialize($pref5,'json');
			$expected5  = "{\n    \"hello\": \"world\"\n}";
			$this->assertEquals($expected5,$result5);

			$pref6      = array('hello'=> mb_convert_encoding('héllò wòrld', 'ISO-8859-1'));
			$result6    = $this->arrObj->serialize($pref6,'json');
			$expected6  = "{\n    \"hello\": \"h\u00e9ll\u00f2 w\u00f2rld\"\n}";

			$this->assertEquals($expected6,$result6);



		}
	}
