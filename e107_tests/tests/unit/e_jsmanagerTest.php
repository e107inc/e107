<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_jsmanagerTest extends \Codeception\Test\Unit
	{

		/** @var e_jsmanager */
		protected $js;

		protected function _before()
		{

			try
			{
				$this->js = $this->make('e_jsmanager');
			} catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_jsmanager object");
			}

		}

/*
		public function testHeaderPlugin()
		{

		}

		public function testTryHeaderInline()
		{

		}
*/
		public function testIsInAdmin()
		{
			$result = $this->js->isInAdmin();
			$this->assertFalse($result);

		}

		public function testRequireCoreLib()
		{

		}

		public function testSetInAdmin()
		{

		}

		public function testCoreCSS()
		{

		}

		public function testResetDependency()
		{

		}

		public function testJsSettings()
		{

		}

		public function testGetInstance()
		{

		}

		public function testFooterFile()
		{

		}

		public function testSetData()
		{

		}

		public function testLibraryCSS()
		{

		}

		public function testTryHeaderFile()
		{

		}

		public function testThemeCSS()
		{

		}

		public function testOtherCSS()
		{

		}

		public function testSetLastModfied()
		{

		}

		public function testRenderLinks()
		{

		}

		public function testThemeLib()
		{

		}

		public function testRenderFile()
		{

		}

		public function testHeaderCore()
		{

		}

		public function testRenderInline()
		{

		}

		public function testFooterTheme()
		{

		}

		public function testGetData()
		{

		}

		public function testRequirePluginLib()
		{

		}

		public function testGetCacheId()
		{

		}

		public function testHeaderTheme()
		{

		}

		public function testInlineCSS()
		{

		}

		public function testHeaderFile()
		{

		}

		public function testSetDependency()
		{

		}

		public function testHeaderInline()
		{

		}

		public function testGetLastModfied()
		{

		}

		public function testSetCacheId()
		{

		}

		public function testGetCurrentTheme()
		{

		}

		public function testPluginCSS()
		{

		}

		public function testCheckLibDependence()
		{

		}

		public function testRenderCached()
		{

		}

		public function testGetCurrentLocation()
		{

		}

		public function testFooterInline()
		{

		}

		public function testAddLibPref()
		{

		}

		public function testAddLink()
		{

		}

		public function testLibDisabled()
		{

		}

		public function testArrayMergeDeepArray()
		{

		}

		public function testRenderJs()
		{

		}

		public function testRemoveLibPref()
		{

		}



	}
