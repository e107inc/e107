<?php


class ecacheTest extends \Codeception\Test\Unit
{

	/** @var ecache */
	protected $cache;

	protected function _before()
	{

		try
		{
			$this->cache = $this->make('ecache');
		}
		catch(Exception $e)
		{
			$this->assertTrue(false, $e->getMessage());
		}

		$file = codecept_data_dir('ecache/content/S_Config_test.cache.php_');
		$dest = e_CACHE_CONTENT."S_Config_test.cache.php";

		if(!file_exists($dest) && !copy($file,$dest))
		{
			$this->assertTrue(false, "Couldn't copy cache file from ".$file);
		}

		$file = codecept_data_dir('ecache/content/S_Update_core.cache.php_');
		$dest = e_CACHE_CONTENT."S_Update_core.cache.php";

		if(!file_exists($dest) && !copy($file, $dest))
		{
			$this->assertTrue(false, "Couldn't copy cache file from ".$file);
		}

		$file = codecept_data_dir('ecache/db/online.php_');
		$dest = e_CACHE_DB."online.php";

		if(!file_exists($dest) && !copy($file, $dest))
		{
			$this->assertTrue(false, "Couldn't copy cache file from ".$file);
		}

		$file = codecept_data_dir('ecache/content/C_wmessage_0800fc577294c34e0b28ad2839435945.cache.php_');
		$dest = e_CACHE_CONTENT."C_wmessage_0800fc577294c34e0b28ad2839435945.cache.php";

		if(!file_exists($dest) && !copy($file, $dest))
		{
			$this->assertTrue(false, "Couldn't copy cache file from ".$file);
		}


	}

	public function testRetrieve()
	{
		$tests = array(
			0   => array(
				'name'      => 'Config_test',
				'system'    => true,
				'expected'  => "array (
					'most_members_online' => 4,
					'most_guests_online' => 4,
					'most_online_datestamp' => 1534279911,
				)",
			),
			1   => array(
				'name'      => 'Update_core',
				'system'    => true,
				'expected'  => '{
						"status": "not needed"
				}',
			),
			2   => array(
				'name'      => 'wmessage',
				'system'    => false,
				'expected'  => '<!--tablestyle:style=defaultid=wm--><div>GetStarted</div>',
			),
		);

		$this->cache->setMD5('hash'); // set a consistent hash value: ie. 0800fc577294c34e0b28ad2839435945

		$clean = ["\t", "\n", "\r", " "];

		foreach($tests as $var)
		{

			$result = $this->cache->retrieve($var['name'], false, true, $var['system']);
			$result = str_replace($clean, '', $result);
			$expected = str_replace($clean, '', $var['expected']);
			$this->assertSame($expected, $result);
		}

		$errorStatus = $this->cache->getLastError();
		$this->assertEmpty($errorStatus, "An error occurred during cache: ".$errorStatus);

	}
/*
	public function testClear_sys()
	{

	}

	public function testSet_sys()
	{

	}
*/
	public function testClear()
	{
		$cacheName = 'testClearCache';
		$this->cache->set($cacheName, "something", true);
		$file = $this->cache->getLastFile();

		// check it has been created.
		$exists = file_exists($file);
		$this->assertTrue($exists);

		// check it has been deleted.
		$this->cache->clear($cacheName);
		$exists = file_exists($file);
		$this->assertFalse($exists);


	}
/*
	public function testDelete()
	{

	}

	public function test__construct()
	{

	}

	public function testClearAll()
	{

	}

	public function testGetMD5()
	{

	}

	public function testSetMD5()
	{

	}

	public function testRetrieve_sys()
	{

	}*/

	public function testSetAndRetrieve()
	{
		$tests = array(
			0   => array(
				'data'      => 'This is my cached data',
				'braw'      => false,
				'system'    => true
			),
			1   => array(
				'data'      => 'This is my cached data 1',
				'braw'      => false,
				'system'    => false
			),
			2   => array(
				'data'      => 'This is my cached data 2',
				'braw'      => true,
				'system'    => false
			),
			3   => array(
				'data'      => 'This is my cached data 3',
				'braw'      => true,
				'system'    => true
			),

		);

		foreach($tests as $index => $var)
		{
			$tag = "custom_".$index;
			$this->cache->set($tag, $var['data'], true, $var['braw'], $var['system']);
			$result = $this->cache->retrieve($tag, false, true, $var['system']);
			$this->assertSame($var['data'],$result);
		}


	}
/*
	public function testCache_fname()
	{

	}
*/

	public function testSetLeavesOnlyTheEntryBehind()
	{
		$this->cache->set('atomic_leftovers', str_repeat('x', 4096), true);
		$file = $this->cache->getLastFile();

		$this->assertFileExists($file);
		$this->assertSame(array(), glob(e_CACHE_CONTENT . 'e107*'), 'the temporary file the write went through was renamed away, not left beside the entry');
		$this->assertSame('0755', substr(sprintf('%o', fileperms($file)), -4), 'cache files keep the mode they have always had');

		@unlink($file);
	}

	public function testAnEntryClearedUnderneathTheReaderIsASilentMiss()
	{
		$this->cache->set('atomic_vanish', 'gone soon', true);
		$file = $this->cache->getLastFile();
		unlink($file);

		$this->assertFalse($this->cache->retrieve('atomic_vanish', false, true));
		$this->assertStringContainsString('not found', $this->cache->getLastError());
	}

	public function testAnExpiredEntryIsRemovedQuietly()
	{
		$this->cache->set('atomic_expired', 'stale', true);
		$file = $this->cache->getLastFile();
		touch($file, time() - 3600);
		clearstatcache();

		$this->assertFalse($this->cache->retrieve('atomic_expired', 1, true));
		$this->assertFileNotExists($file);
	}

	/**
	 * Four copies of _data/ecache/hammer.php race {@see ecache::set()},
	 * {@see ecache::retrieve()} and {@see ecache::delete()} on one entry for
	 * a second each. Runs for a fixed time; asserts nothing about speed.
	 */
	public function testConcurrentSetRetrieveAndClearNeverTearOrWarn()
	{
		$dir = sys_get_temp_dir() . '/e107_ecache_hammer_' . uniqid('', true) . '/';
		mkdir($dir, 0777, true);

		$hammer   = codecept_data_dir('ecache/hammer.php');
		$children = array();

		for($seed = 1; $seed <= 4; $seed++)
		{
			$command = implode(' ', array_map('escapeshellarg', array(
				PHP_BINARY,
				'-d', 'display_errors=stderr',
				'-d', 'error_reporting=' . (E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED),
				'-d', 'log_errors=0',
				'-d', 'html_errors=0',
				'-d', 'xdebug.mode=off',
				$hammer,
				e_HANDLER,
				$dir,
				'1.0',
				'hammer_tag',
				'98304',
				(string) $seed,
			)));

			$descriptors = array(
				0 => array('pipe', 'r'),
				1 => array('file', $dir . 'out.' . $seed, 'w'),
				2 => array('file', $dir . 'err.' . $seed, 'w'),
			);
			$pipes = array();
			$proc  = proc_open($command, $descriptors, $pipes);
			$this->assertIsResource($proc, 'could not start hammer child ' . $seed);
			fclose($pipes[0]);
			$children[$seed] = $proc;
		}

		$reports = array();
		foreach($children as $seed => $proc)
		{
			$exit   = proc_close($proc);
			$stdout = trim((string) file_get_contents($dir . 'out.' . $seed));
			$stderr = trim((string) file_get_contents($dir . 'err.' . $seed));
			$reports[] = "child $seed: exit=$exit $stdout" . ($stderr !== '' ? "\n  stderr: $stderr" : '');

			$this->assertSame(0, $exit, "hammer child $seed exited $exit\n$stderr");
			$this->assertSame('', $stderr, "hammer child $seed wrote a diagnostic:\n$stderr");
			$this->assertRegExp('/^iterations=(\d+) torn=(\d+) misses=\d+$/', $stdout, "hammer child $seed reported nothing usable: $stdout");

			preg_match('/^iterations=(\d+) torn=(\d+)/', $stdout, $m);
			$this->assertGreaterThan(0, (int) $m[1], "hammer child $seed did no work");
			$this->assertSame(0, (int) $m[2], "hammer child $seed read a torn cache file {$m[2]} times\n" . implode("\n", $reports));
		}

		$leftovers = array_diff(scandir($dir), array('.', '..', 'C_hammer_tag.cache.php'));
		$leftovers = preg_grep('/^(out|err)\.\d+$/', $leftovers, PREG_GREP_INVERT);
		$this->assertSame(array(), array_values($leftovers), 'a temporary file survived the run');

		foreach(scandir($dir) as $entry)
		{
			if($entry !== '.' && $entry !== '..')
			{
				@unlink($dir . $entry);
			}
		}
		@rmdir($dir);
	}

}
