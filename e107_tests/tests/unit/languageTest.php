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

		/**
		 * The guard reads $_select_array before anything writes it, so the class must declare it.
		 */
		public function testGetLanSelectArrayReadsADeclaredProperty()
		{
			$this->assertTrue(defined('e_LANLIST'), 'e_LANLIST never reached the constant table.');

			$errors = array();
			set_error_handler(function ($no, $str) use (&$errors) {
				$errors[] = $str;

				return true;
			});

			$language = new language();
			$select = $language->getLanSelectArray();

			restore_error_handler();

			$this->assertSame(array(), $errors, 'getLanSelectArray() complained on its first call.');
			$this->assertNotEmpty($select, 'getLanSelectArray() returned no languages.');
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
