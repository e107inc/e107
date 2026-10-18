<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * What a cache entry is filed under, read for two web paths of one site.
 */
class e107WebPathCacheKeyTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/**
	 * @dataProvider cachedEntries
	 * @param string $entry a key of what {@see e107WebPathCacheKeyTest::keysFor()} returns
	 */
	public function testTwoWebPathsGetTwoEntries($entry)
	{
		$root = $this->keysFor('/');
		$again = $this->keysFor('/');
		$other = $this->keysFor('/zq9mk/');

		self::assertSame($root[$entry], $again[$entry], "$entry is filed under another name for the same web path");
		self::assertNotSame($root[$entry], $other[$entry], "$entry is filed under one name for the web paths / and /zq9mk/");
	}

	/**
	 * @return array[] what {@see e107WebPathCacheKeyTest::testTwoWebPathsGetTwoEntries()} takes
	 */
	public function cachedEntries()
	{
		return array(
			'a page entry after setMD5(null)'       => array('setMD5'),
			'a page entry'                          => array('page'),
			'a page entry after setMD5() with text' => array('text'),
			'an nq_ entry'                          => array('nq'),
			'the combined CSS and JS file'          => array('combined'),
		);
	}

	/**
	 * @param string $webPath what e_HTTP holds
	 * @return string[] the name each kind of entry is filed under, with everything but the web path the same
	 */
	private function keysFor($webPath)
	{
		$root = rtrim(APP_PATH, '/\\');

		$php = "define('e107_INIT', true); "
			."define('e_HTTP', ".var_export($webPath, true)."); "
			."define('e_BASE', './'); "
			."define('e_LANGUAGE', 'English'); "
			."define('THEME', ".var_export($root.'/e107_themes/bootstrap5/', true)."); "
			."define('USERCLASS_LIST', '253,251,0'); "
			."define('e_QUERY', 'item.1'); "
			."define('e_CACHE_CONTENT', ''); "
			."require ".var_export($root.'/e107_handlers/core_functions.php', true)."; "
			."require ".var_export($root.'/e107_handlers/cache_handler.php', true)."; "
			."require ".var_export($root.'/e107_handlers/js_manager.php', true)."; "
			."\$set = new ecache(); "
			."\$page = new ecache(); "
			."\$text = new ecache(); "
			."\$nq = new ecache(); "
			."\$fileId = new ReflectionMethod('e_jsmanager', 'getCacheFileId'); "
			."\$fileId->setAccessible(true); "
			."\$js = new ReflectionClass('e_jsmanager'); "
			."echo json_encode(array("
			."'setMD5' => \$set->setMD5(null)->getMD5(), "
			."'page' => \$page->cache_fname('news.php'), "
			."'text' => \$text->setMD5('English253,251,0')->cache_fname('nfpCache_default'), "
			."'nq' => \$nq->cache_fname('nq_news'), "
			."'combined' => \$fileId->invoke(\$js->newInstanceWithoutConstructor(), array('{e_WEB}css/e107.css'))"
			.")), \"\\n\";";

		list($lines, $status) = $this->runInCli($php, '-d display_errors=1 -d log_errors=0 -d error_reporting='.(E_ALL & ~E_DEPRECATED));

		self::assertSame(0, $status, "the probe exited with status $status:\n".implode("\n", $lines));
		self::assertCount(1, $lines, "the probe raised a diagnostic:\n".implode("\n", $lines));

		return json_decode($lines[0], true);
	}
}
