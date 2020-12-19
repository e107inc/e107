<?php


	class e_library_managerTest extends \Codeception\Test\Unit
	{

		/** @var e_library_manager */
		protected $lib;

		/** @var e_library_manager */
		protected $lib2;

		/** @var core_library */
		protected $corelib;

		protected $libraries = array();

		protected function _before()
		{

			try
			{
				$this->lib = $this->make('e_library_manager');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}


			try
			{
				$this->corelib = $this->make('core_library');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

			$coreLibraries = $this->corelib->config();

			$this->assertNotEmpty($coreLibraries);

			$this->libraries = array_keys($coreLibraries);

		}

		/**
		 * Make sure the default lookup contains no callbacks.
		 */
		public function testDetectionCallbacks()
		{

			foreach($this->libraries as $name)
			{
				$this->lib->detect($name);
			}

			$lookups = $this->lib->getCallbackLog();

			foreach($lookups as $name)
			{
				$this->assertFalse(true, "'version' key is missing in core_library:config() -- ".$name);
			}


		}

		function testDetectionVersionConsistency()
		{

			foreach($this->libraries as $name)
			{
				$coded = $this->lib->detect($name);
				$detected = $this->lib->detect($name, true);

				if(empty($coded['version']))
				{
					$this->assertTrue(false, "No coded version returned in core_library:config() -- ".$name);
				}

				if(empty($detected['version']))
				{
					$this->assertTrue(false, "No looked-up version in core_library:config() -- ".$name);
				}

				$this->assertSame($coded['version'],$detected['version'], 'Version mismatch in core_library:config() -- '.$name);

			}


		}
/*
		public function testInfo()
		{

		}

		public function testGetProperty()
		{

		}

		public function testLoad()
		{

		}

		public function testGetExcludedLibraries()
		{

		}

		public function testGetPath()
		{

		}
*/



	}
