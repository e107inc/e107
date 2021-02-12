<?php


	class ArrayDataTest extends \Codeception\Test\Unit
	{

		/** @var ArrayData */
		protected $ad;

		protected function _before()
		{

			try
			{
				$this->ad = $this->make('ArrayData');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}

		public function testReadArray()
		{
			// e107 var_export test.
			$string = "array (
			  'most_members_online' => 10,
			  'most_guests_online' => 20,
			  'most_online_datestamp' => 1534279911,
			  'most_enabled'    => true
			)";

			$expected = array (
			  'most_members_online' => 10,
			  'most_guests_online' => 20,
			  'most_online_datestamp' => 1534279911,
			  'most_enabled' => true
			);

			$result = $this->ad->ReadArray($string);
			$this->assertSame($expected, $result);

			// legacy Prefs test.
			$string = 'a:4:{s:19:"most_members_online";i:10;s:18:"most_guests_online";i:20;s:21:"most_online_datestamp";i:1534279911;s:12:"most_enabled";b:1;}';
			$actual = $this->ad->ReadArray($string);
			$this->assertSame($expected, $actual);


		}

		public function testWriteArray()
		{
			// Test with addslashes enabled.
			$input = array('one'=>'two', 'three'=>true);
			$result = $this->ad->WriteArray($input);
			$expected = 'array (
  \\\'one\\\' => \\\'two\\\',
  \\\'three\\\' => true,
)';
			$this->assertSame($expected, $result);


			// Test with addslashes disabled.
			$result = $this->ad->WriteArray($input, false);
			$expected = "array (
  'one' => 'two',
  'three' => true,
)";

			$this->assertSame($expected, $result);

		}




	}
