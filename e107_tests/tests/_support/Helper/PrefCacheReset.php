<?php
namespace Helper;

use Codeception\Module;

/**
 * Utility for clearing e107's on-disk SitePrefs cache.
 *
 * A raw dump load (DelayedDb) bypasses e107, so its cached SitePrefs
 * (e107_system/<hash>/cache/content/S_Config_*.cache.php) keep the install-time
 * values and mask the dump's prefs, notably the `trusted_hosts` the host-header
 * check needs. Purging the cache forces e107 to reload prefs from the
 * freshly-populated database.
 *
 * Enabled after DelayedDb, this purges once when the suite starts (after the
 * dump is loaded, before the first request). It deliberately does NOT run per
 * test, so a future test can exercise e107's pref-caching behaviour; call
 * purge() directly when a test needs a clean cache mid-suite.
 */
class PrefCacheReset extends Module
{
	public function _beforeSuite($settings = array())
	{
		$this->purge();
	}

	public function purge()
	{
		// Helper/ -> _support/ -> tests/ -> e107_tests/ -> app docroot.
		$docroot = dirname(__DIR__, 4);
		$caches = glob($docroot . '/e107_system/*/cache/content/S_Config_*.cache.php');
		foreach ($caches ?: array() as $cacheFile)
		{
			@unlink($cacheFile);
		}
	}
}
