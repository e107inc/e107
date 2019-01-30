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

		public function testFill_phrases_array()
		{

		}

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
			// @todo please use multiple assertions in here rather than multiple methods.
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
