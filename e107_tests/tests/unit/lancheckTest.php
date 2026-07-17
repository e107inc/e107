<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 1/30/2019
	 * Time: 12:17 PM
	 */


	class lancheckTest extends \Codeception\Test\Unit
	{

		/** @var lancheck */
		protected $lan;

		protected function _before()
		{
			require_once(e_ADMIN."lancheck.php");

			try
			{
				$this->lan = $this->make('lancheck');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load lancheck object");
			}
		}


/*
		public function testCheck_lan_errors()
		{

		}

		public function testCheckLog()
		{

		}
*/
		public function testFill_phrases_array()
		{

			$strings =
				'define("LAN1", "Főadminisztrátor");'."\n".
				'define("LAN2", "Hői");'."\n".
				'define("LAN3", "Rendszerinformáció");'."\n".
				'define("LAN4", "Felhasználó");'."\n".
				'define("LAN5", "Regisztrált felhasználó");';

			$expected = array (
				'orig' =>
					array (
						'LAN1' => 'Főadminisztrátor',
						'LAN2' => 'Hői',
						'LAN3' => 'Rendszerinformáció',
						'LAN4' => 'Felhasználó',
						'LAN5' => 'Regisztrált felhasználó',
					),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed.');

		}

		public function testFill_phrases_array_const()
		{
			$strings =
				'const LAN1 = "Főadminisztrátor";'."\n".
				'const CORE_LC = "es";'."\n".
				"const LAN2 = 'Hői';";

			$expected = array(
				'orig' => array(
					'LAN1'    => 'Főadminisztrátor',
					'CORE_LC' => 'es',
					'LAN2'    => 'Hői',
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed on const syntax.');
		}

		public function testFill_phrases_array_returnArray()
		{
			$strings =
				'return array('."\n".
				"\t'LAN1' => 'Főadminisztrátor',"."\n".
				"\t'LAN2' => 'Hői',"."\n".
				');';

			$expected = array(
				'orig' => array(
					'LAN1' => 'Főadminisztrátor',
					'LAN2' => 'Hői',
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed on return-array syntax.');
		}

		public function testFill_phrases_array_shortArray()
		{
			$strings =
				'return ['."\n".
				"\t'LAN1' => \"It's escaped\","."\n".
				"\t'LAN2' => 'A \\'quoted\\' value',"."\n".
				'];';

			$expected = array(
				'orig' => array(
					'LAN1' => "It's escaped",
					'LAN2' => "A 'quoted' value",
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed on short-array syntax with escapes.');
		}

		public function testFill_phrases_array_ignoresComments()
		{
			// define()s inside comments must NOT be picked up (the old regex needed a
			// pre-pass to strip /* */ blocks; the tokenizer ignores them natively).
			$strings =
				'/* define("LAN_COMMENTED", "ignore me"); */'."\n".
				'// define("LAN_LINE", "ignore me too");'."\n".
				'define("LAN1", "kept");';

			$expected = array(
				'orig' => array(
					'LAN1' => 'kept',
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() must ignore commented-out statements.');
		}

		public function testFill_phrases_array_registersEmptyType()
		{
			// A modern file with no recognised phrases must still register the type
			// key (as an empty array) so it is not misreported as "File missing!".
			$strings = '$foo = 1; // nothing to harvest here';

			$expected = array(
				'orig' => array(),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() must register the type for phrase-less files.');
		}

/*
		public function testThirdPartyPlugins()
		{

		}

		public function testInit()
		{

		}

		public function testCheck_lanfiles()
		{

		}

		public function testGetFilePaths()
		{

		}

		public function testGetOnlineLanguagePacks()
		{

		}

		public function testGet_comp_lan_phrases()
		{

		}
*/
		public function testIs_utf8()
		{
			$strings = array(
				"Főadminisztrátor",
				"Hői",
				"Rendszerinformáció",
				"Felhasználó",
				"Regisztrált felhasználó");

			foreach($strings as $expected)
			{
				$actual = $this->lan->is_utf8($expected);
				$this->assertEquals(true, $actual, 'is_utf8() failed on '.$expected.'.');
			}


		}

/*
		public function testWrite_lanfile()
		{

		}

		public function testCountFiles()
		{

		}

		public function test__construct()
		{

		}

		public function testCleanFile()
		{

		}

		public function testGetLocalLanguagePacks()
		{

		}

		public function testCheck_core_lanfiles()
		{

		}

		public function testRemoveLanguagePack()
		{

		}

		public function testErrorsOnly()
		{

		}

		public function testCheck_all()
		{

		}

		public function testZipLang()
		{

		}

		public function testGet_lan_file_phrases()
		{

		}

		public function testNewFile()
		{

		}

		public function testEdit_lanfiles()
		{

		}

*/


	}
