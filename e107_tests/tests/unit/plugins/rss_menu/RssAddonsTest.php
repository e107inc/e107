<?php

/**
 * @see https://github.com/e107inc/e107/issues/5870
 */
class RssAddonsTest extends \Codeception\Test\Unit
{
	protected function _before()
	{
		require_once(e_PLUGIN . 'rss_menu/rss_addons.php');
	}

	public function testLegacyKeysNameTheDeclaringPlugin()
	{
		$result = rss_addons::legacyKeys();

		// news declares the pre-0.7.6 numeric key it used to answer to.
		$this::assertArrayHasKey(1, $result);
		$this::assertSame(array('plugin' => 'news', 'url' => 'news'), $result[1]);

		// Every entry names the plugin that claimed the key, so a caller can
		// refuse to canonicalise a row belonging to somebody else.
		foreach($result as $key => $entry)
		{
			$this::assertTrue(is_numeric($key), 'legacy keys are numeric');
			$this::assertArrayHasKey('plugin', $entry);
			$this::assertArrayHasKey('url', $entry);
			$this::assertNotEmpty($entry['url']);
		}
	}

	/**
	 * Only installed plugins contribute. forum declares 6/7/8/11 but is not
	 * installed in the test fixture, so none of its keys may appear.
	 */
	public function testLegacyKeysSkipsUninstalledPlugins()
	{
		$result = rss_addons::legacyKeys();

		$this::assertArrayNotHasKey(6, $result);
		$this::assertArrayNotHasKey(11, $result);
	}

	public function testFeedsStampTheDeclaringPlugin()
	{
		$result = rss_addons::feeds();

		$this::assertNotEmpty($result);

		$paths = array();

		foreach($result as $feed)
		{
			// The declaring folder is rss_menu's to set, never the plugin's.
			$this::assertArrayHasKey('path', $feed);
			$this::assertNotEmpty($feed['path']);
			$this::assertArrayHasKey('url', $feed);
			$paths[$feed['path']] = true;
		}

		$this::assertArrayHasKey('news', $paths);

		// forum is not installed in the fixture, so it must not be scanned.
		$this::assertArrayNotHasKey('forum', $paths);
	}

	/**
	 * config() methods label feeds with rss_menu's admin LAN constants, so
	 * feeds() has to load that pack itself. Without it the first undefined
	 * constant is fatal on PHP 8.
	 */
	public function testFeedsResolveTheAdminLanConstants()
	{
		$result = rss_addons::feeds();

		$descriptions = array();

		foreach($result as $feed)
		{
			$descriptions[] = (string) varset($feed['description'], varset($feed['text'], ''));
		}

		$this::assertNotEmpty(array_filter($descriptions), 'feed descriptions should not all be empty');

		foreach($descriptions as $description)
		{
			$this::assertStringNotContainsString('RSS_PLUGIN_LAN_', $description,
				'an unresolved LAN constant leaked into a feed description');
		}
	}

	/**
	 * feeds() is called once per admin import and once per unresolved feed
	 * path, so it has to survive being called more than once in a request.
	 */
	public function testFeedsIsRepeatable()
	{
		$this::assertSame(rss_addons::feeds(), rss_addons::feeds());
	}
}
