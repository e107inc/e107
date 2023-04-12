<?php
	/**
	 * Created by PhpStorm.
	 * Date: 2/7/2019
	 * Time: 5:03 PM
	 */


	class themeHandlerTest extends \Codeception\Test\Unit
	{

		/** @var themeHandler */
		protected $th;

		protected function _before()
		{

			try
			{
				$this->th = $this->make('themeHandler');
			}
			catch(Exception $e)
			{
				$this->fail("Couldn't load themeHandler object");
			}

		}



		public function testSetThemeConfig()
		{

		}
/*
		public function testTheme_adminlog()
		{

		}

		public function testPostObserver()
		{

		}

		public function testInstallContent()
		{

		}

		public function testRenderTheme()
		{

		}

		public function testSetAdminStyle()
		{

		}

		public function testRenderThemeInfo()
		{

		}

		public function testRenderUploadForm()
		{

		}
*/
		public function testFindDefault()
		{
			$result = $this->th->findDefault('bootstrap3');
			$this->assertSame('jumbotron_sidebar_right', $result);

		}

		public function testFindDefaultCSS()
		{
			$result = $this->th->findDefaultCSS('voux');
			$this->assertSame('style.css', $result);

			$result = $this->th->findDefaultCSS('bootstrap5');
			$this->assertSame('https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.2.3/quartz/bootstrap.min.css', $result);

		}
/*
		public function testGetThemes()
		{

		}

		public function testRenderOnline()
		{

		}

		public function testShowThemes()
		{

		}

		public function testSetLayouts()
		{

		}

		public function testRenderThemeConfig()
		{

		}

		public function testGetThemeCategory()
		{

		}

		public function testShowPreview()
		{

		}

		public function testLoadThemeConfig()
		{

		}

		public function testParse_theme_php()
		{

		}

		public function testRenderThemeHelp()
		{

		}

		public function testSetAdminTheme()
		{

		}

		public function testRefreshPage()
		{

		}
*/

/*
		public function testThemeUpload()
		{

		}

		public function testInstallContentCheck()
		{

		}

		public function testSetStyle()
		{

		}

		public function testGetMarketplace()
		{

		}

		public function testRenderPresets()
		{

		}

		public function testRenderPlugins()
		{

		}

		public function testThemePreview()
		{

		}

		public function testSetCustomPages()
		{

		}

		public function testGetThemeInfo()
		{

		}

		public function testSetTheme()
		{

		}

	*/


	}
