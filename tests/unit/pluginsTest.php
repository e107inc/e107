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

		private function dumpInfo($plugin)
		{

			$tp = e107::getParser();

			echo "Log ------------\n";
			$log = e107::getPlugin()->getLog();

			foreach($log as $line)
			{
				echo " - ".$line."\n";
			}
			echo "-------------------\n\n";

			echo "-- Pref: plug_installed (version)\n\n";
			$pref = e107::getConfig('core',true,true)->get('plug_installed');

			print_r($pref[$plugin]);

			echo "\n-- Plugin Prefs: \n\n";
			$table = e107::pref($plugin);
			print_r($table);


			echo "\n-- Plugin Table: ".$plugin."\n\n";
			$table = e107::getDb()->retrieve('plugin','*', "plugin_path='".$plugin."' LIMIT 1", true);
			print_r($table);

			echo "\n-- Menu Table: ".$plugin."\n\n";
			$table = e107::getDb()->retrieve('menus','*', "menu_location = 0 AND menu_path='".$plugin."/' LIMIT 10", true);
			print_r($table);

			echo "\n-- Site Links Table: ".$plugin."\n\n";
			$table = e107::getDb()->retrieve('links','*', "link_owner='".$plugin."' ", true);
			print_r($table);

			$dir = scandir(e_PLUGIN.$plugin);
			$corePref = e107::getConfig('core',true,true)->getPref();


			echo "\n-- Addons\n\n";
			echo "----------------------------------------\n";
			echo "Addon file\t\tIn Core pref e_xxxx_list\n\n";
			foreach($dir as $file)
			{
				$name = basename($file,".php");

				if(substr($file,0,2) === 'e_')
				{
					$key = $name."_list";
					$status = !empty($corePref[$key][$plugin]) ? 'YES' : 'NO';

					echo $file."\t\t".$status."\n";

				}
			}

			echo "----------------------------------------\n\n\n";





		}

		function testBanner()
		{

			e107::getPlugin()->install('banner');

			$tp = e107::getParser();

			$result = $tp->parseTemplate("{BANNER=e107promo}",true);

			$this->assertContains("<img class='e-banner img-responsive img-fluid'",$result);
			
			$opts = array(
				'delete_tables' => 1,
				'delete_files'   => 0
			);

			e107::getPlugin()->uninstall('banner', $opts);

			$result = $tp->parseTemplate("{BANNER=e107promo}",true); // should return null since plugin is uninstalled.

			// $this->dumpInfo('banner'); // see more values to test

		}








	}
