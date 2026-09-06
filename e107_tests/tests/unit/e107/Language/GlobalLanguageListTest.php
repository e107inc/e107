<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

namespace e107\Language;

	class GlobalLanguageListTest extends \Test\Unit
	{
		const SENTINEL = 'e107help_not_a_plugin';

		protected function _before()
		{
			GlobalLanguageList::invalidate();
		}

		protected function _after()
		{
			GlobalLanguageList::invalidate();
		}

		public function testTheColdBuildIsInstalledPluginsThatShipAGlobalLan()
		{
			$list = GlobalLanguageList::plugins();
			$installed = \e107::getPlug()->getInstalled();

			$this->assertNotEmpty($list, 'a stock install has plugins that ship a global language file');

			foreach($list as $folder)
			{
				$this->assertArrayHasKey($folder, $installed, $folder.' is listed but not installed');

				$nested = e_PLUGIN.$folder.'/languages/English/English_global.php';
				$flat   = e_PLUGIN.$folder.'/languages/English_global.php';

				$this->assertTrue(file_exists($nested) || file_exists($flat),
					$folder.' is listed but ships no global language file');
			}

			$this->assertContains('news', $list, 'news is installed and ships a global language file');
			$this->assertNotContains('_blank', $list, '_blank ships a global language file but is never installed');
		}

		public function testHasAnswersForAnInstalledAndAnUninstalledPlugin()
		{
			$this->assertTrue(GlobalLanguageList::has('news'));
			$this->assertFalse(GlobalLanguageList::has('_blank'));
		}

		public function testTheWarmPathReadsTheCacheRatherThanRebuilding()
		{
			$this->writeCache(json_encode(array(self::SENTINEL)));

			$this->assertSame(array(self::SENTINEL), GlobalLanguageList::plugins(),
				'a cached list has to be believed, or the entry is not a cache');
		}

		public function testAnEmptyListRoundTripsWithoutARebuild()
		{
			$this->writeCache(json_encode(array()));

			$this->assertSame(array(), GlobalLanguageList::plugins(),
				'legitimately empty and cache miss have to stay distinguishable');
		}

		public function testUnreadableCacheContentRebuilds()
		{
			$this->writeCache('{ this is not the list ');

			$this->assertContains('news', GlobalLanguageList::plugins(),
				'a corrupt entry is a miss, and a miss is answered by rebuilding');
		}

		public function testClearingThePluginCacheDropsTheList()
		{
			$this->writeCache(json_encode(array(self::SENTINEL)));
			$this->assertTrue(GlobalLanguageList::has(self::SENTINEL), 'precondition: the poisoned entry is live');

			\e107::getPlug()->clearCache();

			$this->assertFalse(GlobalLanguageList::has(self::SENTINEL),
				'every install, uninstall, refresh and upgrade goes through clearCache(), so it has to drop this list too');
		}

		/**
		 * Regression for https://github.com/e107inc/e107/issues/5709. A folder scan
		 * changes installed-ness without going through clearCache(), so it has to
		 * drop the list itself or the list stops following the plugin table.
		 */
		public function testAFolderScanDropsTheList()
		{
			$this->writeCache(json_encode(array(self::SENTINEL)));
			$this->assertTrue(GlobalLanguageList::has(self::SENTINEL), 'precondition: the poisoned entry is live');

			\e107::getPlugin()->update_plugins_table('update');

			$this->assertFalse(GlobalLanguageList::has(self::SENTINEL),
				'a folder scan changes what is installed, so it has to drop this list');
			$this->assertNotContains('_blank', GlobalLanguageList::plugins(),
				'a scan must not put an uninstalled plugin back in the list');
		}

		public function testABootedRequestLoadsPluginGlobalLansWithNoCacheToStartFrom()
		{
			GlobalLanguageList::invalidate();

			list($output,) = $this->runInBootedCli("echo defined('LAN_PLUGIN_NEWS_NAME') ? 'DEFINED' : 'MISSING';");

			$this->assertNotFalse(strpos(implode("\n", $output), 'DEFINED'),
				'a request that starts with an empty cache has to rebuild the list and load the language files');
		}

		/**
		 * @param string $payload
		 */
		private function writeCache($payload)
		{
			GlobalLanguageList::invalidate();
			\e107::getCache()->set(GlobalLanguageList::CACHE_TAG, $payload, true, true, true);
		}
	}
