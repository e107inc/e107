<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class pluginsTest extends \Codeception\Test\Unit
	{

	//	protected $_plg;

		protected function _before()
		{
			/*try
			{
				$this->_plg = $this->make('e107plugin');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107plugin object");
			}*/
		}

		private function checkPlugin($plugin, $debug=false)
		{

			

			$text = "\n\n---- Log \n\n";
			$log = e107::getPlugin()->getLog();

			foreach($log as $line)
			{
				$text .= " - ".$line."\n";
			}

			$text .= "----------------------------------------\n\n";

			$text .= "---- Pref: plug_installed (version)\n\n";
			$pref = e107::getConfig('core',true,true)->get('plug_installed');

			$text .= print_r($pref[$plugin],true);

			$installedPref = isset($pref[$plugin]) ? $pref[$plugin] : false;

			$text .= "\n\n---- Plugin Prefs: \n\n";
			$pluginPref = e107::pref($plugin);
			$text .= print_r($pluginPref,true);


			$text .= "\n---- Plugin Table: ".$plugin."\n\n";
			$pluginTable = e107::getDb()->retrieve('plugin','*', "plugin_path='".$plugin."' LIMIT 1", true);
			$text .= print_r($pluginTable,true);

			$text .= "\n---- Menu Table: ".$plugin."\n\n";
			$menuTable = e107::getDb()->retrieve('menus','*', "menu_location = 0 AND menu_path='".$plugin."/' LIMIT 10", true);
			$text .= print_r($menuTable, true);

			$text .= "\n---- Site Links Table: ".$plugin."\n\n";
			$linksTable = e107::getDb()->retrieve('links','*', "link_owner='".$plugin."' ", true);
			$text .= print_r($linksTable, true);

			$dir = scandir(e_PLUGIN.$plugin);
			$corePref = e107::getConfig('core',true,true)->getPref();


			$text .= "\n---- Addons\n\n";
			$text .= "-------------------------------------------------------------------\n";
			$text .= "Addon file                  In Core pref e_xxxx_list  \n";
			$text .= "-------------------------------------------------------------------\n";

			$addonPref = array();

			foreach($dir as $file)
			{
				$name = basename($file,".php");

				if(substr($file,0,2) === 'e_')
				{


					$key = $name."_list";
					$status = !empty($corePref[$key][$plugin]) ? 'YES' : 'NO';

					if($key === 'e_help_list')
					{
						$status = "DEPRECATED by Admin-UI renderHelp()";
					}
					else
					{
						$addonPref[$name] = ($status === 'YES') ? true : false;
					}

					$text .= str_pad($file,20)."\t\t".$status."\n";

				}
			}

			$text .= "-------------------------------------------------------------------\n";

			if($debug === true)
			{
				echo $text;
			}

			return array(
				'log'           => $log,
				'installedPref' => $installedPref,
				'pluginPref'    => $pluginPref,
				'pluginTable'   => $pluginTable,
				'menuTable'     => $menuTable,
				'linksTable'    => $linksTable,
				'addonPref'     => $addonPref
			);



		}

		public function testBanner()
		{
			$this->pluginInstall('banner');

			$tp = e107::getParser();

			$result = $tp->parseTemplate("{BANNER=e107promo}",true);
			$this->assertContains("<img class='e-banner img-responsive img-fluid'",$result);

			$this->pluginUninstall('banner');
			$result2 = $tp->parseTemplate("{BANNER=e107promo}",true); // should return null since plugin is uninstalled.


		}

		public function testChatbox_Menu()
		{
			$this->pluginInstall('chatbox_menu');

			$this->pluginUninstall('chatbox_menu');
		}

		public function testDownload()
		{
			$this->pluginInstall('download');

			$this->pluginUninstall('download');
		}

		public function testFaqs()
		{
			$this->pluginInstall('faqs');
			$this->pluginUninstall('faqs');
		}

		public function testFeaturebox()
		{
			$this->pluginInstall('featurebox');
			$this->pluginUninstall('featurebox');
		}

		public function testForum()
		{
			$this->pluginInstall('forum');
			$this->pluginUninstall('forum');
		}

		public function testGallery()
		{
			$this->pluginInstall('gallery');
			$this->pluginUninstall('gallery');
		}

		public function testGsitemap()
		{
			$this->pluginInstall('gsitemap');
			$this->pluginUninstall('gsitemap');
		}

		public function testImport()
		{
			$this->pluginInstall('import');
			$this->pluginUninstall('import');
		}

		public function testLinkwords()
		{
			$this->pluginInstall('linkwords');
			$this->pluginUninstall('linkwords');
		}

		public function testPm()
		{
			$this->pluginInstall('pm');
			$this->pluginUninstall('pm');
		}

		public function testPoll()
		{
			$this->pluginInstall('poll');
			$this->pluginUninstall('poll');
		}

		public function testRss_menu()
		{
			$this->pluginInstall('rss_menu');
			$this->pluginUninstall('rss_menu');
		}

		public function testSocial()
		{
			$this->pluginUninstall('social');
			$this->pluginInstall('social');
		}

		public function testTagcloud()
		{
			$this->pluginInstall('tagcloud');
			$this->pluginUninstall('tagcloud');
		}

		private function pluginInstall($pluginDir)
		{

			e107::getPlugin()->install($pluginDir);

			$install = $this->checkPlugin($pluginDir, false); // set to true to see more info

		//	print_r($install);

			//todo additional checks

			foreach($install['addonPref'] as $key=>$val)
			{
				$this->assertTrue($val, $key." list pref is missing for ".$pluginDir);
			}



		}

		private function pluginUninstall($pluginDir)
		{
			$opts = array(
				'delete_tables' => 1,
				'delete_files'   => 0
			);

			e107::getPlugin()->uninstall($pluginDir, $opts);

			$uninstall = $this->checkPlugin($pluginDir, false); // set to true to see more info

		//	print_r($uninstall);

			//todo additional checks

			$this->assertEmpty($uninstall['linksTable'], $pluginDir." link still exists in the links table");

			foreach($uninstall['addonPref'] as $key=>$val)
			{
				$message = $key." list pref still contains '".$pluginDir."' after uninstall of ".$pluginDir.". ";
				$this->assertEmpty($val, $message);
			}




		}






	}
