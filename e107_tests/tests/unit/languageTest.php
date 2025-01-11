<?php


	class languageTest extends \Codeception\Test\Unit
	{

		/** @var language */
		protected $lan;

		protected function _before()
		{

			try
			{
				$this->lan = $this->make('language');
			}

			catch(Exception $e)
			{
				$this->fail( $e->getMessage());
			}

		}


		public function testLanguageHelpFiles()
		{
			$list = scandir(e_LANGUAGEDIR."English/admin/help");
			$ns = e107::getRender();
			$pref = e107::getPref();
			e107::getMessage()->addInfo("Dummy Info");


			foreach($list as $file)
			{
				if(strpos($file, ".php") === false)
				{
					continue;
				}


				ob_start();
				$path = e_LANGUAGEDIR.'English/admin/help/'.$file;
				require_once($path);
				$result = ob_get_clean();

				$this->assertNotEmpty($result, $path. " was empty." );
			}



		}

/*
		public function testDetect()
		{

		}

		public function testGetCookieDomain()
		{

		}

		public function testToNative()
		{

		}

		public function testSet()
		{

		}

		public function testSubdomainUrl()
		{

		}

		public function testIsLangDomain()
		{

		}

		public function testGetList()
		{

		}

		public function testTranslate()
		{

		}

		public function testBcDefs()
		{

		}

		public function testInstalled()
		{

		}

		public function testGetLanSelectArray()
		{

		}

		public function testIsValid()
		{

		}

		public function testSetDefs()
		{

		}
*/


	}
