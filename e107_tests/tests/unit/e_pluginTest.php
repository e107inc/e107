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
				$this->assertTrue(false, "Couldn't load e_plugin object: $e");
			}
		}


		/**
		 * Creates a dummy plugin entry to make sure such plugins are ignored
		 */
		public function testIgnoringOfInvalidPlugin()
		{

			$dir = e_PLUGIN."temptest";
			$file = e_PLUGIN."temptest/plugin.php";

			mkdir($dir,0755);
			file_put_contents($file, "\n");

			$detected = $this->ep->clearCache()->getDetected();

			foreach($detected as $path)
			{
				if($path == 'temptest')
				{
					$this->assertFalse(true);
				}
			}

			unlink($file);
			rmdir($dir);

			$this->assertFalse(false);


		}

		public function testClearCache()
		{

			$detected = $this->ep->clearCache()->getDetected();
			$num = e107::getDb()->count('plugin','(*)');
			$det = count($detected);
			$this->assertEquals($num,$det);

			// Simulate an orphaned plugin entry.
			$insert = array(
			 'plugin_name'          => "testClearCache",
			 'plugin_version'       => 1,
			 'plugin_path'          => 'missing_path',
			 'plugin_installflag'   => 1,
			 'plugin_addons'        => '',
			 'plugin_category'      => 'tools'
			);

			e107::getDb()->insert('plugin', $insert);


			$detected = $this->ep->clearCache()->getDetected();
			$num = e107::getDb()->count('plugin','(*)');
			$det = count($detected);
			$this->assertEquals($num,$det);

		}




		public function testBuildAddonPrefList()
		{
			e107::getPlugin()->install('gallery');

            $newUrls = array('gallery'=>0, 'news'=>'news', 'rss_menu'=>0);

            e107::getConfig()->setData('e_url_list', $newUrls)->save(false,false,false);

			$urlsBefore = e107::pref('core', 'e_url_list');
			$userBefore = e107::pref('core', 'e_user_list');

		//	print_r($userBefore);

			$this->ep->clearCache()->buildAddonPrefLists();

			$urlsAfter = e107::pref('core', 'e_url_list');
			$userAfter = e107::pref('core', 'e_user_list');

		//	print_r($userAfter);

			$this->assertEquals($urlsBefore['gallery'],$urlsAfter['gallery']);
			$this->assertEquals($userBefore['user'],$userAfter['user']);

		}


		public function testGetInstallRequired()
		{
			$this->ep->load('user');

			$result = $this->ep->clearCache()->getInstallRequired();

			$this->assertFalse($result);

		}
/*
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
			$result = $this->ep->clearCache()->load('user')->isInstalled();

			$this->assertTrue($result);
		}

		public function testGetDetected()
		{
			$result = $this->ep->clearCache()->getDetected();

			$hasBanner = in_array("banner", $result);

			$this->assertTrue($hasBanner);

			$hasUser = in_array("user", $result);

			$this->assertTrue($hasUser);
		}
/*
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
*/
		public function testGetInstalled()
		{
			$result = $this->ep->clearCache()->getInstalled();

			$this->assertNotEmpty($result['user']);

		}
/*
		public function testGetVersion()
		{

		}*/

		public function testGetFields()
		{
			e107::getPlugin()->uninstall('forum');
			$result = $this->ep->clearCache()->load('forum')->getFields(true);

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




*/

		/**
		 * Test check for global lan file.
		 */
		public function testHasLanGlobal()
		{
			$result = $this->ep->clearCache()->load('chatbox_menu')->hasLanGlobal();

			$this->assertEquals('chatbox_menu', $result);

			$result = $this->ep->clearCache()->load('alt_auth')->hasLanGlobal();

			$this->assertFalse($result);
		}

		public function testGetMeta()
		{
			$result = $this->ep->clearCache()->load('news')->getMeta();

			$this->assertEquals('news', $result['folder']);
			$this->assertEquals('menu', $result['category']);
		}

		public function testIsValidAddonMarkup()
        {
            $content = '<?php    
            
            ';
            $result = $this->ep->isValidAddonMarkup($content);
            $this->assertTrue($result);


            $content = ' <?php    ';
            $result = $this->ep->isValidAddonMarkup($content);
            $this->assertFalse($result);

            $content = ' ?>
            ';
            $result = $this->ep->isValidAddonMarkup($content);
            $this->assertFalse($result);

            $content = '<?php
            ?>';
            $result = $this->ep->isValidAddonMarkup($content);
            $this->assertTrue($result);

        }
/*
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
