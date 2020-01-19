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

		protected $_debugPlugin = ''; // 'linkwords'; // add plugin-dir for full report.

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

		private function makePluginReport($pluginDir)
		{
			$debug = $this->_debugPlugin;
			$debug_text = "\n\n---- Log \n\n";
			$log = e107::getPlugin()->getLog();

			foreach($log as $line)
			{
				$debug_text .= " - ".$line."\n";
			}

			$debug_text .= "----------------------------------------\n\n";

			$debug_text .= "---- Pref: plug_installed (version)\n\n";
			$pref = e107::getConfig('core',true,true)->get('plug_installed');

			$installedPref = isset($pref[$pluginDir]) ? $pref[$pluginDir] : false;

			$debug_text .= print_r($installedPref,true);

			$debug_text .= "\n\n---- Plugin Prefs: \n\n";
			$pluginPref = e107::pref($pluginDir);
			$debug_text .= print_r($pluginPref,true);


			$debug_text .= "\n---- Plugin Table: ".$pluginDir."\n\n";
			$pluginTable = e107::getDb()->retrieve('plugin','*', "plugin_path='".$pluginDir."' LIMIT 1", true);
			$debug_text .= print_r($pluginTable,true);

			$debug_text .= "\n---- Menu Table: ".$pluginDir."\n\n";
			$menuTable = e107::getDb()->retrieve('menus','*', "menu_location = 0 AND menu_path='".$pluginDir."/' LIMIT 10", true);
			$debug_text .= print_r($menuTable, true);

			$debug_text .= "\n---- Site Links Table: ".$pluginDir."\n\n";
			$linksTable = e107::getDb()->retrieve('links','*', "link_owner='".$pluginDir."' ", true);
			$debug_text .= print_r($linksTable, true);

			$files_in_plugin_directory = @scandir(e_PLUGIN.$pluginDir) ?: [];
			$corePref = e107::getConfig('core',true,true)->getPref();


			$debug_text .= "\n---- Addons\n\n";
			$debug_text .= "-------------------------------------------------------------------\n";
			$debug_text .= "Addon file                  In Core pref e_xxxx_list  \n";
			$debug_text .= "-------------------------------------------------------------------\n";

			$addonPref = array();

			$plugin_addon_names = $this->pluginFileListToPluginAddonNames($files_in_plugin_directory);

			foreach($plugin_addon_names as $plugin_addon_name)
			{
				$key = $plugin_addon_name."_list";
				$addon_pref_is_present = !empty($corePref[$key][$pluginDir]);
				$debug_addon_pref_is_present = ($addon_pref_is_present) ? 'YES' : 'NO';

				if($key === 'e_admin_events_list')
				{
					$debug_addon_pref_is_present = "DEPRECATED by Admin-UI events";
				}
				if($key === 'e_help_list')
				{
					$debug_addon_pref_is_present = "DEPRECATED by Admin-UI renderHelp()";
				}
				else
				{
					$addonPref[$plugin_addon_name] = $addon_pref_is_present;
				}

				$debug_text .= str_pad("$plugin_addon_name.php",20)
					."\t\t$debug_addon_pref_is_present\n";
			}

			$debug_text .= "-------------------------------------------------------------------\n";

			if(!empty($debug) &&  $pluginDir === $debug)
			{
				codecept_debug($debug_text);
				echo $debug_text;
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

		/**
		 * @see https://github.com/e107inc/e107/issues/3547
		 */
		public function testBanner()
		{

			$this->pluginInstall('banner');

			// App needs e_parse_shortcode to be reloaded because another test
			// could have initialized e_parse_shortcode already before the
			// "banner" plugin was installed.
			e107::getScParser()->__construct();

			$tp = e107::getParser();

			$result = $tp->parseTemplate("{BANNER=e107promo}",true);
			$this->assertStringContainsString("<img class='e-banner img-responsive img-fluid'", $result);

			$result = $tp->parseTemplate("{BANNER=e107promo}",false,
				e107::getScBatch('banner', true));
			$this->assertStringContainsString("<img class='e-banner img-responsive img-fluid'", $result);

			$result = $tp->parseTemplate("{BANNER=e107promo}",false);
			$this->assertEquals("", $result);

			$this->pluginUninstall('banner');

			$result = $tp->parseTemplate("{BANNER=e107promo}",true);
			// The expected value below was the actual observed output when the assertion was written:
			$this->assertEquals('&nbsp;', $result,
				"Banner shortcode is not returning an empty value, despite banner being uninstalled");
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

			$pref1 = e107::pref('core', 'lw_custom_class');
			$this->assertNotEmpty($pref1);

			$pref2 = e107::pref('core', 'lw_context_visibility');
			$this->assertNotEmpty($pref2['SUMMARY']);

			$this->pluginUninstall('linkwords');

			$pref2 = e107::pref('core', 'lw_context_visibility');
			$this->assertEmpty($pref2);
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

		public function testplugInstalledStatus()
		{
			$sql = e107::getDb();

			$plg = e107::getPlug()->clearCache();
			$plg->load('tagcloud');

			// check it's NOT installed.
			$status = $plg->isInstalled();
			$dbStatus = (bool) $sql->retrieve('plugin', "plugin_installflag", "plugin_path='tagcloud'");
			$this->assertEquals($status,$dbStatus,"e_plugin:isInstalled() doesn't match plugin_installflag in db table.");
			$this->assertFalse($status, "Status for tagcloud being installed should be false");


			e107::getPlugin()->install('tagcloud');

			// check it's installed.
			$status = (int) $plg->isInstalled();
			$actual = (bool) $status;
			$dbStatus = (int) $sql->retrieve('plugin', "plugin_installflag", "plugin_path='tagcloud'");
			$this->assertEquals($status,$dbStatus,"e_plugin:isInstalled() = ".$status." but plugin_installflag = ".$dbStatus." after install.");
			$this->assertTrue($actual, "Status for tagcloud being installed should be true after being installed.");


			e107::getPlugin()->uninstall('tagcloud');


			// check it's NOT installed.
			$status = (int) $plg->isInstalled();
			$actual = (bool) $status;
			$dbStatus = (int) $sql->retrieve('plugin', "plugin_installflag", "plugin_path='tagcloud'");
			$this->assertEquals($status,$dbStatus,"e_plugin:isInstalled() = ".$status." but plugin_installflag = ".$dbStatus." after uninstall.");
			$this->assertFalse($actual, "Status for tagcloud being installed should be false after being uninstalled.");

		}


		public function testPluginAddons()
		{
			$plg = e107::getPlug()->clearCache();

			$plg->buildAddonPrefLists();

			$errors = array(
				1   => 'PHP tag Syntax issue',
				2   => "File Missing",
			);

			foreach($plg->getCorePluginList() as $folder)
			{
				$plg->load($folder);

				$errMsg = '';

				$addons = $plg->getAddons();

					foreach(explode(',', $addons) as $this_addon)
					{
						if(empty($this_addon))
						{
							continue;
						}

						$result = $plg->getAddonErrors($this_addon);

						if(is_numeric($result) && $result != 0)
						{
							$errMsg = " (".$errors[$result].")";
						}
						elseif(isset($result['msg']))
						{
							$errMsg = " (".$result['msg'].")";
						}

						$this->assertEmpty($result, $folder." > ".$this_addon." returned error #".$result.$errMsg);
					//	echo $folder;
					//	var_dump($result);
					}





			}


		}



		public function testRemotePlugin()
		{
			require_once(e_HANDLER."e_marketplace.php");
			try
			{
				$mock_adapter = $this->make('e_marketplace_adapter_wsdl',
					[
						'getRemoteFile' => function($remote_url, $local_file, $type='temp')
						{
							file_put_contents(e_TEMP.$local_file, self::samplePluginContents());
							return true;
						}
					]);
				$mp = $this->make('e_marketplace',
					[
						'adapter' => $mock_adapter
					]);
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e_marketplace object");
			}
			$mp->__construct();

			$id = 912; // No-follow plugin on e107.org

			$this->assertFalse(is_dir(e_PLUGIN."nofollow"), "Plugin nofollow exists before download");
			$mp->download($id, '', 'plugin');
			$this->assertTrue(is_dir(e_PLUGIN."nofollow"), "Plugin nofollow is missing after download");

			$this->pluginInstall('nofollow');

			$opts = array(
					'delete_tables' => 1,
					'delete_files'   => 1
			);

			$this->pluginUninstall('nofollow',$opts);

			$status = is_dir(e_PLUGIN."nofollow");

			$this->assertFalse($status,"nofollow plugin still exists, despite opt to have it removed during uninstall.");



		}




		private function pluginInstall($pluginDir)
		{
			e107::getPlugin()->uninstall($pluginDir);

			$return_text = e107::getPlugin()->install($pluginDir);
			$this->assertNotEquals("Plugin is already installed.", $return_text);

			$install = $this->makePluginReport($pluginDir);

			//todo additional checks

			foreach($install['addonPref'] as $key=>$val)
			{
				$this->assertTrue($val, $key." list pref is missing for ".$pluginDir);
			}
		}

		private function pluginUninstall($pluginDir, $opts=array())
		{
			if(empty($opts))
			{
				$opts = array(
					'delete_tables' => 1,
					'delete_files'   => 0
				);
			}



			e107::getPlugin()->uninstall($pluginDir, $opts);

			$uninstall = $this->makePluginReport($pluginDir);

			//todo additional checks

			$this->assertEmpty($uninstall['linksTable'], $pluginDir." link still exists in the links table");

			foreach($uninstall['addonPref'] as $key=>$val)
			{
				$message = $key." list pref still contains '".$pluginDir."' after uninstall of ".$pluginDir.". ";
				$message .= print_r($uninstall,true);

				$this->assertEmpty($val, $message);
			}

			return $uninstall;
		}

		/**
		 * @param $plugin_file_list
		 * @return array
		 */
		private function pluginFileListToPluginAddonNames($plugin_file_list)
		{

			$plugin_addon_names = array_map(function ($addon_path)
			{

				return basename($addon_path, '.php');
			}, $plugin_file_list);

			$class_name_that_has_plugin_addons_array = 'e107plugin';

			try
			{
				$reflectionClass = new ReflectionClass($class_name_that_has_plugin_addons_array);
			}
			catch(ReflectionException $e)
			{
				$this->fail("Could not instantiate $class_name_that_has_plugin_addons_array to get \$plugin_addons");
			}

			$reflectionProperty = $reflectionClass->getProperty('plugin_addons');
			$reflectionProperty->setAccessible(true);
			$valid_plugin_addon_names = $reflectionProperty->getValue(new $class_name_that_has_plugin_addons_array());

			$plugin_addon_names = array_filter($plugin_addon_names, function ($plugin_addon_name) use ($valid_plugin_addon_names)
			{

				return in_array($plugin_addon_name, $valid_plugin_addon_names);
			});

			return $plugin_addon_names;
		}

		private static function samplePluginContents()
		{
			return base64_decode(<<<DATA
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
oMEVl9jk49hgvpg/josCXkiGO24vyCshGv84rhGq6mr+UFVQwpgu1E8lhNqGtuMLQpiojQGpVhGt
+lr7FeDOomwM7MXkhRCS0cmEZPuz3Tze3UJUa9DKWW8FWC0EYtBE3XdP12swVnPZwsj6qhAmpjGy
CpYIvVYMjT9d+b8Uf7qoZU5rlD4nPJLFgfpoptE6LYfUBNUs1IY+3jdAlGTpASsnWfgmvI0bbymP
7o4+On6B8bggmd9mFtbt/7KR7bg5/jrzeRuEw8OAHcCAcjNr1lzONNZ58S/5Y8eEa/C+bvGdfifn
8jJ1zfftfecsnDLK1uhv33hHQ0Kc/y6CF1ERDCZR5U9vrMmj0k+T0GJfKhsNsw2VBj4NX6Ri2bDH
iCXolYQf+ZsKGAmf2RtQSwMECgAAAAAAb2aNTAAAAAAAAAAAAAAAABMAHABub2ZvbGxvdy9sYW5n
dWFnZXMvVVQJAAMi7tBanpPbW3V4CwABBOgDAAAE6AMAAFBLAwQKAAAAAABvZo1MAAAAAAAAAAAA
AAAAGwAcAG5vZm9sbG93L2xhbmd1YWdlcy9FbmdsaXNoL1VUCQADIu7QWp6T21t1eAsAAQToAwAA
BOgDAABQSwMECgACAAAAb2aNTK6ya6MFAAAABQAAACwAHABub2ZvbGxvdy9sYW5ndWFnZXMvRW5n
bGlzaC9FbmdsaXNoX2Zyb250LnBocFVUCQADIu7QWvfw0Fp1eAsAAQToAwAABOgDAAA8P3BocFBL
AwQUAAIACABvZo1MFsAQtAgEAAC6CgAALAAcAG5vZm9sbG93L2xhbmd1YWdlcy9FbmdsaXNoL0Vu
Z2xpc2hfYWRtaW4ucGhwVVQJAAMi7tBa9/DQWnV4CwABBOgDAAAE6AMAALVWYW+jRhD93l8xvUrn
WHdCrfrh1KTVCcfERsKADE5zykVobRazF9hFLMSxmvvvnV1wgp0Q5yr1UwLszJs38+at//xcpMVP
MU0YpycDx3Qj17vwHMf7Owrsme9Y0TScOdHYm0W+OQ+s+eAjDAKWFxkF9QXwC/iklBRmtEpFPBie
vZxubk2sq06SOV1b928L9efWRRSao2hm2q6KnRHGj5+2rs6dRWB7btDEcLKmYN2vsloywaVK8EqG
S9OJzj03tK7CaNHWvJC0BF/Iisbg8Wz7eg2HGSJzPGvq13k+gBnnjLf53p7KurTmX8Kp7U5UKuuO
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
AG9mjUwSY0gZuAEAALgBAAAfABwAbm9mb2xsb3cvaW1hZ2VzL25vZm9sbG93XzE2LnBuZ1VUCQAD
Iu7QWvfw0Fp1eAsAAQToAwAABOgDAACJUE5HDQoaCgAAAA1JSERSAAAAEAAAABAIBgAAAB/z/2EA
AAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAABWklEQVR42mL0+3LFgYGBYT0Q
CzCQBj4AcSALMZr/vXrH8G3FdjDNJCbEwOHrwMCiKA3Ss56FkOY/958yfKqZxPD/63e42K+Tlxj4
WvLAhjARq5knL5pBeMMkMA3ifwe6CARY8Bpw5TYDV4Qn2Nls5npgMXYnc7B3YC7CasDvK3cYfmw+
AFYE0swF1QxzFSgsWBRlsBvwc99Jhi+TlqKIgfwstLQTbPDn9tlgMVBAYhgAsvXr3HUMjNycsEAC
G8gMtA3ZYFA4sOqooBoAkgQpgmkG+f830GZOYBhgMxgGWGA2gzSDJEAKQOATNKC+QUMbm2a4Ad+B
BsAUgGgQEJzVADYU5G+QJpCfYXIYBkBCVRpFAcxmdiczeBRiA+CEBFIAih6QV0CGgcIDxAaFA6uO
Kt4MwQjMTP+xJVdYeGBzNroB70H5AWQzyM9gZwETDyjFEZMjGSnNzgABBgBLWbuDDI4oYwAAAABJ
RU5ErkJgglBLAwQUAAIACABvZo1M0t2jjectAADYhgAAEAAcAG5vZm9sbG93L0xJQ0VOU0VVVAkA
AyLu0Fpyk9tbdXgLAAEE6AMAAAToAwAAxT3bcttGlu/9FV18sVRFK3Eyk8wkU6miZTpmjSxpJDke
P4JEU8QaBLhoQDL36/dc+wKSdnZeNjVTiSSg+/Tpc7/B2sN/fr/+YGdv387vbuzv8+v53ezK3n54
fbW4tPD/+fX93Njj//zhOl+1jf1xal/93V63T267dJ394fvvfzbGXra7fVc9bnp7dnlOv7RvO+fs
fbvun4vO2bft0JRFDytM7aJZXdh/bPp+98t33639+qLtHr/7zdj5k+v2beNs5e3Odduq711p+9au
YHVbNKUtK9931XLonYVnl7DeFv9YOW9su7b9Bt6sq5VrvLNluxq2rumnFp63q03RPFbNo616XL5p
e1vUdfvsygtz6sj0z23niu2ydvjUw8YxAtdr17X2d9e4rqjt7bCETe2VbAzLF3YNp58S4LVb9wGo
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
TNbRIN3AWDURLrnvU4rOAH178aPz8adP2a3ZMi8GF9/bqNHZD/USsqo3OrvCAP//UqpK8aqQ3ec9
h30NHnj8lSLfAhmXWo2J57FooUket1sf4nUHVQQxG15PvlZCJpuKNF9e3NDE+2rp6qmKKxtIxCZy
s68udZCKGAqORHPLCuM7S0ULuFP7C1BLAQIeAwoAAAAAAG9mjUwAAAAAAAAAAAAAAAAJABgAAAAA
AAAAEADAQQAAAABub2ZvbGxvdy9VVAUAAyLu0Fp1eAsAAQToAwAABOgDAABQSwECHgMUAAIACABv
Zo1Mt17g42EMAABWKQAAFQAYAAAAAAABAAAA7YFDAAAAbm9mb2xsb3cvTm9Gb2xsb3cucGhwVVQF
AAMi7tBadXgLAAEE6AMAAAToAwAAUEsBAh4DFAACAAgAb2aNTD0Onq8qAgAA0wMAABQAGAAAAAAA
AQAAAO2B8wwAAG5vZm9sbG93L2VfcGFyc2UucGhwVVQFAAMi7tBadXgLAAEE6AMAAAToAwAAUEsB
Ah4DCgAAAAAAb2aNTAAAAAAAAAAAAAAAABMAGAAAAAAAAAAQAMBBaw8AAG5vZm9sbG93L2xhbmd1
YWdlcy9VVAUAAyLu0Fp1eAsAAQToAwAABOgDAABQSwECHgMKAAAAAABvZo1MAAAAAAAAAAAAAAAA
GwAYAAAAAAAAABAAwEG4DwAAbm9mb2xsb3cvbGFuZ3VhZ2VzL0VuZ2xpc2gvVVQFAAMi7tBadXgL
AAEE6AMAAAToAwAAUEsBAh4DCgACAAAAb2aNTK6ya6MFAAAABQAAACwAGAAAAAAAAQAAAO2BDRAA
AG5vZm9sbG93L2xhbmd1YWdlcy9FbmdsaXNoL0VuZ2xpc2hfZnJvbnQucGhwVVQFAAMi7tBadXgL
AAEE6AMAAAToAwAAUEsBAh4DFAACAAgAb2aNTBbAELQIBAAAugoAACwAGAAAAAAAAQAAAO2BeBAA
AG5vZm9sbG93L2xhbmd1YWdlcy9FbmdsaXNoL0VuZ2xpc2hfYWRtaW4ucGhwVVQFAAMi7tBadXgL
AAEE6AMAAAToAwAAUEsBAh4DCgACAAAAb2aNTP1V0Rs2AAAANgAAAC0AGAAAAAAAAQAAAO2B5hQA
AG5vZm9sbG93L2xhbmd1YWdlcy9FbmdsaXNoL0VuZ2xpc2hfZ2xvYmFsLnBocFVUBQADIu7QWnV4
CwABBOgDAAAE6AMAAFBLAQIeAxQAAgAIAG9mjUzyU8QxoAMAAEYHAAASABgAAAAAAAEAAADtgYMV
AABub2ZvbGxvdy9SRUFETUUubWRVVAUAAyLu0Fp1eAsAAQToAwAABOgDAABQSwECHgMUAAIACABv
Zo1MxwlDFlELAAC3JQAAFgAYAAAAAAABAAAA7YFvGQAAbm9mb2xsb3cvZV9saWJyYXJ5LnBocFVU
BQADIu7QWnV4CwABBOgDAAAE6AMAAFBLAQIeAxQAAgAIAG9mjUx4cXJwmgUAAC4RAAAZABgAAAAA
AAEAAADtgRAlAABub2ZvbGxvdy9hZG1pbl9jb25maWcucGhwVVQFAAMi7tBadXgLAAEE6AMAAATo
AwAAUEsBAh4DFAACAAgAb2aNTP5BJCtnAgAAvwQAABMAGAAAAAAAAQAAAO2B/SoAAG5vZm9sbG93
L3BsdWdpbi54bWxVVAUAAyLu0Fp1eAsAAQToAwAABOgDAABQSwECHgMKAAAAAABvZo1MAAAAAAAA
AAAAAAAAEAAYAAAAAAAAABAAwEGxLQAAbm9mb2xsb3cvaW1hZ2VzL1VUBQADIu7QWnV4CwABBOgD
AAAE6AMAAFBLAQIeAwoAAgAAAG9mjUxp2KX1tgoAALYKAAAgABgAAAAAAAAAAADtgfstAABub2Zv
bGxvdy9pbWFnZXMvbm9mb2xsb3dfMTI4LnBuZ1VUBQADIu7QWnV4CwABBOgDAAAE6AMAAFBLAQIe
AwoAAgAAAG9mjUxqUP8fIwMAACMDAAAfABgAAAAAAAAAAADtgQs5AABub2ZvbGxvdy9pbWFnZXMv
bm9mb2xsb3dfMzIucG5nVVQFAAMi7tBadXgLAAEE6AMAAAToAwAAUEsBAh4DCgACAAAAb2aNTBJj
SBm4AQAAuAEAAB8AGAAAAAAAAAAAAO2BhzwAAG5vZm9sbG93L2ltYWdlcy9ub2ZvbGxvd18xNi5w
bmdVVAUAAyLu0Fp1eAsAAQToAwAABOgDAABQSwECHgMUAAIACABvZo1M0t2jjectAADYhgAAEAAY
AAAAAAABAAAA7YGYPgAAbm9mb2xsb3cvTElDRU5TRVVUBQADIu7QWnV4CwABBOgDAAAE6AMAAFBL
AQIeAwoAAAAAAG9mjUwAAAAAAAAAAAAAAAANABgAAAAAAAAAEADAQclsAABub2ZvbGxvdy9saWIv
VVQFAAMi7tBadXgLAAEE6AMAAAToAwAAUEsBAh4DFAACAAgAb2aNTKtfRMezOQAADf4AACAAGAAA
AAAAAQAAAO2BEG0AAG5vZm9sbG93L2xpYi9zaW1wbGVfaHRtbF9kb20ucGhwVVQFAAMi7tBadXgL
AAEE6AMAAAToAwAAUEsBAh4DCgAAAAAAb2aNTAAAAAAAAAAAAAAAABMAGAAAAAAAAAAQAMBBHacA
AG5vZm9sbG93L3RlbXBsYXRlcy9VVAUAAyLu0Fp1eAsAAQToAwAABOgDAABQSwECHgMUAAIACABv
Zo1MEfqi3LMDAAD7CQAAMQAYAAAAAAABAAAA7YFqpwAAbm9mb2xsb3cvdGVtcGxhdGVzL3Byb2pl
Y3RfaW5mb19tZW51X3RlbXBsYXRlLnBocFVUBQADIu7QWnV4CwABBOgDAAAE6AMAAFBLBQYAAAAA
FQAVAOYHAACIqwAAAAA=
DATA
			);
		}
	}
