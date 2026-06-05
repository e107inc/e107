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
				$this->assertTrue(false, "Couldn't e107_plugin object");
			}



		}


		public function testGetPluginRecord()
		{
			$obj = $this->ep;
			$result = $obj::getPluginRecord('banner');

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
*/
		public function testXmlExtendedFields()
		{
			// $ret = $this->ep->parse_plugin_xml('_blank');
			//	var_export($this->ep->plug_vars);

			$this->ep->plugFolder = 'test';

			$extendedVars = array (
		    'field' =>   array (
			      0 =>   array (
			        '@attributes' => array ('name' => 'custom', 'type' => 'EUF_TEXTAREA',    'default' => '0',   'active' => 'true',),
			        '@value' => '',
			      ),
			      1 =>  array (
			        '@attributes' => array ('name' => 'custom2', 'type' => 'EUF_ADDON', 'data' => 'str', 'default' => '0', 'active' => 'true', 'system' => 'false', 'text' => 'My Label' ),
			        '@value' => '',
			      ),

			      2 =>  array (
			        '@attributes' => array ('name' => 'custom3', 'type' => 'EUF_ADDON', 'data' => 'str', 'default' => 'hello', 'active' => 'true', 'system' => 'true', 'text' => 'Another Label' ),
			        '@value' => '',
			      ),
		     )
			);
			
			$expected = array ( 
				0 => array (  
					'name' => 'plugin_test_custom',  
					'attrib' =>array ( 'name' => 'custom', 'type' => 'EUF_TEXTAREA', 'default' => '0', 'active' => 'true', 'deprecate' => NULL, 'system' => true,  ),  
					'source' => 'plugin_test',
				),
				1 => array (  
					'name' => 'plugin_test_custom2',  
					'attrib' =>array ( 'name' => 'custom2', 'type' => 'EUF_ADDON', 'data' => 'str', 'default' => '0', 'active' => 'true', 'system' => false, 'text' => 'My Label', 'deprecate' => NULL,  ),  
					'source' => 'plugin_test',
				),
				2 => array (  
					'name' => 'plugin_test_custom3',  
					'attrib' =>array ( 'name' => 'custom3', 'type' => 'EUF_ADDON', 'data' => 'str', 'default' => 'hello', 'active' => 'true', 'system' => true, 'text' => 'Another Label', 'deprecate' => NULL,  ),  
					'source' => 'plugin_test',
				), 
			); 
			
			

			$result = $this->ep->XmlExtendedFields('test', $extendedVars);

			$this->assertEquals($expected, $result);

		//	var_export($result);


		}
/*
		public function testGetAddons()
		{

		}

		public function testUe_field_type()
		{

		}

		public function testManage_userclass()
		{

		}
*/
/*
		public function testXmlAdminIcons()
		{
			$plugin = e107::getPlug();
			$installed = $plugin->getInstalled();
			fwrite(STDERR, print_r($installed, true));

			if(!$data = $plugin->load('_blank')->getMeta())
			{
				$this::fail("Couldn't load plugin meta");
			}

			$this::assertNotEmpty($data['adminLinks']);

			$status = $this->ep->XmlAdminIcons('install', '_blank', $data['adminLinks']);

			fwrite(STDERR, print_r($status, true));
		}
*/

		public function testXmlSiteLinks()
		{
			$plugVars = array (
				  '@attributes' =>
				  array (
				    'name' => 'Multiple Languages',
				    'lan' => '',
				    'version' => '1.0.3',
				    'date' => '2015-06-04',
				    'compatibility' => '2.0',
				    'installRequired' => 'true',
				  ),
				  'author' =>
				  array (
				    '@attributes' =>
				    array (
				      'name' => 'cameron',
				      'url' => 'http://e107.org',
				    ),
				    '@value' => '',
				  ),
				  'summary' =>
				  array (
				    '@attributes' =>
				    array (
				      'lan' => '',
				    ),
				    '@value' => 'Multi-Language tools for e107',
				  ),
				  'description' =>
				  array (
				    '@attributes' =>
				    array (
				      'lan' => '',
				    ),
				    '@value' => 'Multi-Language tools for e107',
				  ),
				  'keywords' =>
				  array (
				    'word' =>
				    array (
				      0 => 'multilanguage',
				      1 => 'sync',
				    ),
				  ),
				  'category' => 'manage',
				  'copyright' => '',
				  'adminLinks' =>
				  array (
				    'link' =>
				    array (
				      0 =>
				      array (
				        '@attributes' =>
				        array (
				          'url' => 'admin_config.php',
				          'description' => '',
				          'icon' => 'images/multilan_32.png',
				          'iconSmall' => 'images/multilan_16.png',
				          'icon128' => 'images/multilan_128.png',
				          'primary' => 'true',
				        ),
				        '@value' => 'LAN_CONFIGURE',
				      ),
				    ),
				  ),
				  'siteLinks' =>
				  array (
				    'link' =>
				    array (
				      0 =>
				      array (
				        '@attributes' =>
				        array (
				          'url' => '#',
				          'function' => 'language',
				          'icon' => '',
				          'description' => 'Choose Language',
				          'perm' => 'admin',
				        ),
				        '@value' => 'LAN_MULTILAN_NAVICON',
				      ),
				    ),
				  ),
				  'userClasses' =>
				  array (
				    'class' =>
				    array (
				      0 =>
				      array (
				        '@attributes' =>
				        array (
				          'name' => 'TRANSLATE_ME',
				          'description' => 'Items requiring translation and the team members who do it.',
				        ),
				        '@value' => '',
				      ),
				      1 =>
				      array (
				        '@attributes' =>
				        array (
				          'name' => 'REVIEW_ME',
				          'description' => 'Items that have been auto-translated and require reivew and the team members who do it.',
				        ),
				        '@value' => '',
				      ),
				    ),
				  ),
				  'folder' => 'multilan',
				  'files' =>
				  array (
				    3 => 'admin_config.php',
				    4 => 'bing.class.php',
				    5 => 'e_admin.php',
				    6 => 'e_footer.php',
				    7 => 'e_help.php',
				    8 => 'e_meta.php',
				    9 => 'e_module.php',
				    10 => 'e_shortcode.php',
				    11 => 'e_sitelink.php',
				    12 => 'images',
				    13 => 'multilan.css',
				    14 => 'multilan.zip',
				    15 => 'plugin.xml',
				    16 => 'README.md',
				    17 => 'test.php',
				  ),
				  'administration' =>
				  array (
				    'icon' => 'images/multilan_32.png',
				    'caption' => '',
				    'iconSmall' => 'images/multilan_16.png',
				    'configFile' => 'admin_config.php',
				  ),
				);

			$status = $this->ep->XmlSiteLinks('install', $plugVars);

			$this->assertTrue($status, "Site link insertion failed");

			$actual = e107::getDb()->retrieve('links', '*', "link_owner = 'multilan' ");

			$expected = array (
			  'link_id' => '12',
			  'link_name' => 'LAN_MULTILAN_NAVICON',
			  'link_url' => '#',
			  'link_description' => '',
			  'link_button' => '',
			  'link_category' => '1',
			  'link_order' => '11',
			  'link_parent' => '0',
			  'link_open' => '0',
			  'link_class' => '254',
			  'link_function' => 'multilan::language',
			  'link_sefurl' => '',
			  'link_owner' => 'multilan',
			);

			$unimportant_keys = ['link_id', 'link_order'];
			foreach ($unimportant_keys as $unimportant_key)
			{
				unset($expected[$unimportant_key]);
				unset($actual[$unimportant_key]);
			}

			// Filter out cruft from MYSQL_BOTH database output
			foreach ($actual as $key => $value)
			{
				if (is_int($key)) unset($actual[$key]);
			}

			$this->assertEquals($expected,$actual);

			$status = $this->ep->XmlSiteLinks('uninstall',$plugVars);

			$this->assertTrue($status);

			$tmp = e107::getDb()->retrieve('links', '*', "link_owner = 'multilan' ");

			$actual = (empty($tmp)) ? true : false;

			$this->assertTrue($actual, "Link still exists after supposed removal");


		}
/*
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

		/**
		 * Test that FULLTEXT indexes are created when installing a plugin with e_search addon
		 *
		 * This test installs the forum plugin (which has e_search) and verifies
		 * that no FULLTEXT index migrations are pending after installation.
		 */
		public function testPluginInstallCreatesFulltextIndexes()
		{
			$pluginPath = 'forum';

			// Skip if forum plugin doesn't exist
			if(!is_dir(e_PLUGIN . $pluginPath))
			{
				$this->markTestSkipped('Forum plugin not available');
			}

			// Get plugin handlers
			$plug = e107::getPlug();  // e_plugin - for checking installed status
			$plugin = e107::getPlugin();  // e107plugin - for install/uninstall

			// Check if forum is already installed and uninstall it first
			$installed = $plug->load($pluginPath)->isInstalled();
			if($installed)
			{
				$plugin->install_plugin_xml($pluginPath, 'uninstall', array('delete_tables' => true));
			}

			// Install the forum plugin
			$installResult = $plugin->install_plugin_xml($pluginPath, 'install');
			$this->assertNotFalse($installResult, 'Forum plugin installation should succeed');

			// Refresh plugin cache so e_search_list is current
			$plug->clearCache()->buildAddonPrefLists();

			// Now check that no FULLTEXT indexes are pending via db_verify
			require_once(e_HANDLER . 'db_verify_class.php');

			$dbv = new db_verify();
			// Reinitialize with fresh state - clears table list cache, reloads prefs,
			// and refreshes db_verify's own cache to include this newly installed plugin
			$dbv->init(true);
			$dbv->compare($pluginPath);

			// Check for missing FULLTEXT indexes
			$missingFulltextIndexes = array();
			if($dbv->errors())
			{
				$dbv->compileResults();
				$fixList = $dbv->fixList;

				foreach($fixList as $tables)
				{
					foreach($tables as $table => $fields)
					{
						foreach($fields as $field => $modes)
						{
							// Check if this is a FULLTEXT index fix (starts with ft_)
							if(strpos($field, 'ft_') === 0 && in_array('index', $modes))
							{
								$missingFulltextIndexes[] = "{$table}.{$field}";
							}
						}
					}
				}
			}

			// Clean up: uninstall the forum plugin
			$plugin->install_plugin_xml($pluginPath, 'uninstall', array('delete_tables' => true));

			// Assert no FULLTEXT indexes are missing
			$this->assertEmpty(
				$missingFulltextIndexes,
				'FULLTEXT indexes should be created during plugin installation. Missing: ' .
				implode(', ', $missingFulltextIndexes)
			);
		}

		/**
		 * Regression for https://github.com/e107inc/e107/issues/5709
		 *
		 * A plugin folder scan runs update_plugins_table() in 'update' mode. A stale
		 * lan_global_list entry for an uninstalled plugin (here _blank, which ships
		 * languages/English/English_global.php and is installRequired, so it is never
		 * auto-installed) must be reconciled away by that scan, not survive it.
		 */
		public function testFolderScanUpdateModeRemovesStaleGlobalLanForUninstalledPlugin()
		{
			$folder = '_blank';
			$plugin = e107::getPlugin();

			// Make sure _blank is registered in the plugin table (uninstalled).
			$plugin->update_plugins_table('update');

			$plg = e107::getPlug()->clearCache();
			$plg->load($folder);
			$this->assertFalse($plg->isInstalled(), 'precondition: _blank must not be installed');

			// Baseline (after registration) to restore, so shuffled sibling tests stay clean.
			$globalBefore = e107::getConfig('core')->get('lan_global_list', array());
			$logBefore    = e107::getConfig('core')->get('lan_log_list', array());

			try
			{
				// Seed a stale entry, as if left over from a previous install or folder swap.
				e107::getConfig('core')->setPref('lan_global_list/' . $folder, $folder)->save(false, true, false);

				// The folder scan (mode 'update') must drop it.
				$plugin->update_plugins_table('update');

				$after = e107::getConfig('core', true, true)->get('lan_global_list', array());
				$this->assertArrayNotHasKey($folder, $after,
					'A folder scan (mode update) must drop the stale lan_global_list entry for uninstalled _blank');
			}
			finally
			{
				// Restore the lists we touched.
				e107::getConfig('core')->set('lan_global_list', $globalBefore)->set('lan_log_list', $logBefore)->save(false, true, false);
			}
		}

		/**
		 * Regression for https://github.com/e107inc/e107/issues/5709
		 *
		 * XmlLanguageFiles('uninstall') must persist its removePref. For a plugin that
		 * ships only English_global.php (no English_log.php) the removal was lost before
		 * the fix, because the global branch set $update instead of $updated and the
		 * trailing save() was therefore skipped.
		 */
		public function testXmlLanguageFilesUninstallPersistsGlobalOnlyRemoval()
		{
			$folder = 'temptest5709';
			$base   = e_PLUGIN . $folder;
			mkdir($base . '/languages/English', 0755, true);
			file_put_contents($base . '/languages/English/English_global.php', "<?php\nreturn array();\n");

			try
			{
				e107::getConfig('core')->setPref('lan_global_list/' . $folder, $folder)->save(false, true, false);

				$plugin = e107::getPlugin();
				$plugin->plugFolder = $folder;
				$plugin->XmlLanguageFiles('uninstall');

				$persisted = e107::getConfig('core', true, true)->get('lan_global_list', array());
				$this->assertArrayNotHasKey($folder, $persisted,
					'XmlLanguageFiles(uninstall) must save the removal of a global-only plugin from lan_global_list');
			}
			finally
			{
				e107::getConfig('core')->removePref('lan_global_list/' . $folder)->save(false, true, false);
				@unlink($base . '/languages/English/English_global.php');
				@rmdir($base . '/languages/English');
				@rmdir($base . '/languages');
				@rmdir($base);
			}
		}
	}
