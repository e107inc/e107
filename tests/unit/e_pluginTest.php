<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_pluginTest extends \Codeception\Test\Unit
	{

		/** @var e_plugin */
		private $ep;

		protected function _before()
		{
			// require_once(e_HANDLER."e_marketplace.php");

			try
			{
				$this->ep = $this->make('e_plugin');
				$this->ep->__construct();
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_plugin object");
			}
		}

/*
		public function testGetInstallRequired()
		{

		}

		public function testGetUpgradableList()
		{

		}

		public function testIsLegacy()
		{

		}
*/

		public function testSetInstalled()
		{
			$this->ep->setInstalled('some-plugin', '1.3');

			$arr = $this->ep->getInstalled();

			$this->assertArrayHasKey('some-plugin', $arr);

			// print_r($arr);

		}


		public function testIsInstalled()
		{
			$this->ep->setInstalled('some-plugin', '1.3');

			$val = $this->ep->load('some-plugin')->isInstalled();

			var_dump($val);
		}
/*
		public function testGetDetected()
		{

		}

		public function testGetCompat()
		{

		}

		public function testGetKeywords()
		{

		}

		public function testGetId()
		{

		}

		public function testGetAdminUrl()
		{

		}

		public function testGetAddons()
		{

		}

		public function testGetCategoryList()
		{

		}

		public function testGetAddonErrors()
		{

		}

		public function testGetIcon()
		{

		}

		public function testGetInstalled()
		{

		}

		public function testGetVersion()
		{

		}*/

		public function testGetFields()
		{
			$result = $this->ep->load('forum')->getFields(true);

		//	print_r($result);

			$this->assertEquals('LAN_PLUGIN_FORUM_NAME', $result['plugin_name']);
			$this->assertNotEmpty($result['plugin_id'], "plugin_id was empty" );
			$this->assertNotEmpty($result['plugin_path'], "plugin_path was empty" );
			$this->assertEmpty($result['plugin_installflag'], "plugin_installflag was true when it should be false");

		}
/*
		public function testGetAdminCaption()
		{

		}

		public function testGetDescription()
		{

		}

		public function testGetAuthor()
		{

		}

		public function testGetName()
		{

		}

		public function testBuildAddonPrefLists()
		{

		}

		public function testClearCache()
		{

		}

		public function testGetMeta()
		{

		}

		public function testLoad()
		{

		}

		public function testGetCategory()
		{

		}

		public function testGetInstalledWysiwygEditors()
		{

		}

		public function testGetDate()
		{

		}*/
	}
