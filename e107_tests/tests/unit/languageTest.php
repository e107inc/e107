<?php


	class languageTest extends \Test\Unit
	{

		/** @var language */
		protected $lan;

		/** @var array Scratch language files and directories to remove after the test. */
		protected $scratch = array('files' => array(), 'dirs' => array());

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


		protected function _after()
		{
			foreach($this->scratch['files'] as $file)
			{
				if(is_file($file))
				{
					unlink($file);
				}
			}

			foreach($this->scratch['dirs'] as $dir)
			{
				if(is_dir($dir))
				{
					rmdir($dir);
				}
			}

			$this->scratch = array('files' => array(), 'dirs' => array());
		}

		/**
		 * includeLanArray()'s English fallback re-includes this file on every
		 * non-English request, so nothing in it may declare anything twice.
		 */
		public function testEnglishCorePackCanBeIncludedTwice()
		{
			$path = e_LANGUAGEDIR.'English/English.php';

			$errors = array();
			set_error_handler(function ($no, $str) use (&$errors) {
				$errors[] = $str;

				return true;
			});

			$first = include($path);
			$second = include($path);

			restore_error_handler();

			$this->assertSame(array(), $errors, 'English.php complained when it was included again.');

			$this->assertIsArray($first, 'English.php returned no terms.');
			$this->assertArrayHasKey('CORE_LC', $first, 'English.php no longer carries CORE_LC.');
			$this->assertArrayHasKey('CORE_LC2', $first, 'English.php no longer carries CORE_LC2.');
			$this->assertSame('en', $first['CORE_LC']);
			$this->assertSame('gb', $first['CORE_LC2']);
			$this->assertSame($first, $second, 'The second include returned different terms.');
		}

		public function testIncludeLanDefinesTheEnglishLocaleCodes()
		{
			e107::includeLan(e_LANGUAGEDIR.'English/English.php', true);

			$this->assertTrue(defined('CORE_LC'), 'CORE_LC never reached the constant table.');
			$this->assertTrue(defined('CORE_LC2'), 'CORE_LC2 never reached the constant table.');
			$this->assertSame('en', CORE_LC);
			$this->assertSame('gb', CORE_LC2);
		}

		/** The ordering that lets a translated pack keep its own CORE_LC. */
		public function testOwnPackTermsWinOverTheEnglishFallback()
		{
			$language = uniqid('Testlang');
			$dir = e_LANGUAGEDIR.$language.'/';

			$this->assertTrue(mkdir($dir), 'Could not create the scratch language directory.');
			$this->scratch['dirs'][] = $dir;

			$own = $dir.'lan_test5979.php';
			$english = e_LANGUAGEDIR.'English/lan_test5979.php';
			$this->scratch['files'][] = $own;
			$this->scratch['files'][] = $english;

			file_put_contents($own, "<?php\nreturn array('LAN_TEST5979' => 'own');");
			file_put_contents($english, "<?php\nreturn array('LAN_TEST5979' => 'english');");

			e107::includeLan($own, true, $language);

			$this->assertSame('own', constant('LAN_TEST5979'));
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
