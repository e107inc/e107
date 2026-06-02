<?php
namespace Helper;

use Codeception\Module;
use Codeception\TestInterface;

/**
 * Delete e107's on-disk SitePrefs cache before each test.
 *
 * A raw dump load (DelayedDb) bypasses e107, so its cached SitePrefs
 * (e107_system/<hash>/cache/content/S_Config_*.cache.php) keep the install-time
 * values and mask the dump's prefs, notably the `trusted_hosts` the host-header
 * check needs. Deleting the cache forces e107 to reload prefs from the
 * freshly-populated database. Enable after DelayedDb.
 */
class PrefCacheReset extends Module
{
	public function _before(TestInterface $test)
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
