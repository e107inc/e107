<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e107pluginTest extends \Codeception\Test\Unit
	{

		/** @var e107plugin */
		protected $ep;

		protected function _before()
		{
			try
			{
				$this->ep = $this->make('e107plugin');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't e107_plugi object");
			}

		}


		public function testGetPluginRecord()
		{
			$result = $this->ep::getPluginRecord('banner');

		//	print_r($result);

			$this->assertEquals("LAN_PLUGIN_BANNER_NAME", $result['plugin_name']);

		}


/*

		public function testDisplayArray()
		{

		}

		public function testExecute_function()
		{

		}

		public function testManage_plugin_prefs()
		{

		}

		public function testInstall()
		{

		}

		public function testGetall()
		{

		}

		public function testXmlPrefs()
		{

		}

		public function testParse_plugin_php()
		{

		}

		public function testRefresh()
		{

		}

		public function testUninstall()
		{

		}

		public function testRebuildUrlConfig()
		{

		}

		public function testManage_icons()
		{

		}

		public function testParse_plugin()
		{

		}

		public function testManage_comments()
		{

		}

		public function testManage_search()
		{

		}

		public function testInstall_plugin_xml()
		{

		}

		public function testGetAddonsDiz()
		{

		}

		public function testUpdate_plugins_table()
		{

		}

		public function testXmlBBcodes()
		{

		}

		public function testInstall_plugin()
		{

		}

		public function testManage_extended_field_sql()
		{

		}

		public function testUpdateRequired()
		{

		}

		public function testGetCorePlugins()
		{

		}

		public function testManage_prefs()
		{

		}

		public function testGetPerm()
		{

		}

		public function testGetAddonsList()
		{

		}

		public function testXmlExtendedFields()
		{

		}

		public function testGetAddons()
		{

		}

		public function testUe_field_type()
		{

		}

		public function testManage_userclass()
		{

		}

		public function testXmlSiteLinks()
		{

		}

		public function testGetIcon()
		{

		}

		public function testGetId()
		{

		}

		public function testXmlLanguageFileCheck()
		{

		}

		public function testSetUe()
		{

		}

		public function testManage_tables()
		{

		}

		public function testInstall_plugin_php()
		{

		}

		public function testXmlMediaCategories()
		{

		}

		public function testXmlDependencies()
		{

		}

		public function testXmlTables()
		{

		}

		public function testXmlUserClasses()
		{

		}

		public function testCheckAddon()
		{

		}

		public function testManage_notify()
		{

		}

		public function testUe_field_type_name()
		{

		}

		public function testManage_link()
		{

		}

		public function testUe_field_name()
		{

		}

		public function testIsUsedByAnotherPlugin()
		{

		}

		public function testGetOtherPlugins()
		{

		}

		public function testGetLog()
		{

		}

		public function testManage_category()
		{

		}

		public function testGetinfo()
		{

		}

		public function testXmlAdminLinks()
		{

		}

		public function testXmlLanguageFiles()
		{

		}

		public function testManage_extended_field()
		{

		}

		public function testParse_plugin_xml()
		{

		}*/
	}
