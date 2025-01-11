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

}
