<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2019 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


class e_fileTest extends \Codeception\Test\Unit
{

	/** @var e_file  */
	protected $fl;
	protected $exploitFile = '';
	protected $filetypesFile = '';

	protected function _before()
	{
		try
		{
			$this->fl = $this->make('e_file');
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't load e_file object");
		}

		$this->exploitFile = e_TEMP."test_exploit_file.jpg";

		$content = "<?php system(\$_GET['q']) ?>";

		file_put_contents($this->exploitFile,$content);

		$this->filetypesFile = e_SYSTEM."filetypes.xml";

		$content = '<?xml version="1.0" encoding="utf-8"?>
						<e107Filetypes>
							<class name="253" type="zip,gz,jpg,jpeg,png,gif,xml,pdf" maxupload="2M" />
						</e107Filetypes>';

		file_put_contents($this->filetypesFile, $content);

	}

	protected function _after()
	{
		unlink($this->exploitFile);
		unlink($this->filetypesFile);
	}


	public function testIsClean()
	{

		$isCleanTest = array(
			array('path'=>$this->exploitFile,                       'expected' => false), // suspicious
			array('path'=>e_SYSTEM."filetypes.xml",                 'expected' => true), // okay
			array('path'=>e_PLUGIN."gallery/images/butterfly.jpg",  'expected' => true), // okay
		);

		foreach($isCleanTest as $file)
		{
			$actual = $this->fl->isClean($file['path'], $file['path']);
			$this->assertEquals($file['expected'],$actual, "isClean() failed on {$file['path']} with error code: ".$this->fl->getErrorCode());
		}

	}

	public function testGetAllowedFileTypes()
	{
		$actual = $this->fl->getAllowedFileTypes();

		$expected = array (
			'zip' => 2097152, // 2M in bytes
			'gz' => 2097152,
			'jpg' => 2097152,
			'jpeg' => 2097152,
			'png' => 2097152,
			'gif' => 2097152,
			'xml' => 2097152,
			'pdf' => 2097152,
		);

		$this->assertEquals($expected,$actual);

	}

	public function testIsAllowedType()
	{

		$isAllowedTest = array(
			array('path'=> 'somefile.bla',                          'expected' => false), // suspicious
			array('path'=> e_SYSTEM."filetypes.xml",                 'expected' => true), // okay
			array('path'=> e_PLUGIN."gallery/images/butterfly.jpg",  'expected' => true), // okay
		);

		foreach($isAllowedTest as $file)
		{
			$actual = $this->fl->isAllowedType($file['path']);
			$this->assertEquals($file['expected'],$actual, "isAllowedType() failed on: ".$file['path']);
		}

	}
	/*
			public function testSend()
			{

			}

			public function testFile_size_encode()
			{

			}

			public function testMkDir()
			{

			}

			public function testGetRemoteContent()
			{

			}

			public function testDelete()
			{

			}

			public function testGetRemoteFile()
			{

			}

			public function test_chMod()
			{

			}

			public function testIsValidURL()
			{

			}

			public function testGet_dirs()
			{

			}

			public function testGetErrorMessage()
			{

			}

			public function testCopy()
			{

			}

			public function testInitCurl()
			{

			}

			public function testScandir()
			{

			}

			public function testGetFiletypeLimits()
			{

			}
	*/
	public function testFile_size_decode()
	{
		$arr = array(
			'1024'  => 1024,
			'2kb'   => 2048,
			'1KB'   => 1024,
			'1M'    => 1048576,
			'1G'    => 1073741824,
			'1Gb'   => 1073741824,
			'1TB'   => 1099511627776,
		);

		foreach($arr as $key => $expected)
		{
			$actual = $this->fl->file_size_decode($key);
			$this->assertEquals($expected,$actual, $key." does not equal ".$expected." bytes");
		}

	}
	/*
			public function testZip()
			{

			}

			public function testSetDefaults()
			{

			}

			public function testSetMode()
			{

			}

			public function testUnzipArchive()
			{

			}

			public function testSetFileFilter()
			{

			}

			public function testGetErrorCode()
			{

			}

			public function testChmod()
			{

			}

			public function testSetFileInfo()
			{

			}*/

	public function testGet_file_info()
	{
		$path = APP_PATH."/e107_web/lib/font-awesome/4.7.0/fonts/fontawesome-webfont.svg";

		$ret = $this->fl->get_file_info($path);

		$this->assertEquals('image/svg+xml',$ret['mime']);


	}
	/*
			public function testPrepareDirectory()
			{

			}

			public function testGetFileExtension()
			{

			}

			public function testRmtree()
			{

			}

			public function testGet_files()
			{

			}

			public function testGetUserDir()
			{

			}

			public function testRemoveDir()
			{

			}
			*/

	public function testUnzipGithubArchive()
	{
		$prefix = 'e107-master';
		$fake_e107_files = [
			'desired' => [
				'/index.php',
				'/e107_admin/index.html',
				'/e107_core/index.html',
				'/e107_docs/index.html',
				'/e107_handlers/index.html',
				'/e107_images/index.html',
				'/e107_languages/index.html',
				'/e107_media/index.html',
				'/e107_plugins/index.html',
				'/e107_system/index.html',
				'/e107_themes/index.html',
				'/e107_web/index.html',
			],
			'undesired' => [
				'/.github/codecov.yml',
				'/e107_tests/index.php',
				'/.codeclimate.yml',
				'/.editorconfig',
				'/.gitignore',
				'/.gitmodules',
				'/CONTRIBUTING.md',
				'/LICENSE',
				'/README.md',
				'/composer.json',
				'/composer.lock',
				'/install.php',
				'/favicon.ico',
			]
		];

		$src_dest_map = array(
			'/e107_admin/'       => '/'.e107::getFolder('ADMIN'),
			'/e107_core/'        => '/'.e107::getFolder('CORE'),
			'/e107_docs/'        => '/'.e107::getFolder('DOCS'),
			'/e107_handlers/'    => '/'.e107::getFolder('HANDLERS'),
			'/e107_images/'      => '/'.e107::getFolder('IMAGES'),
			'/e107_languages/'   => '/'.e107::getFolder('LANGUAGES'),
			'/e107_media/'       => '/'.e107::getFolder('MEDIA'),
			'/e107_plugins/'     => '/'.e107::getFolder('PLUGINS'),
			'/e107_system/'      => '/'.e107::getFolder('SYSTEM'),
			'/e107_themes/'      => '/'.e107::getFolder('THEMES'),
			'/e107_web/'         => '/'.e107::getFolder('WEB'),
		);

		/**
		 * @var e_file
		 */
		$e_file = $this->make('e_file', [
			'getRemoteFile' => function($remote_url, $local_file, $type='temp') use ($fake_e107_files, $prefix)
			{
				touch(e_TEMP.$local_file);
				$archive = new ZipArchive();
				$archive->open(e_TEMP.$local_file, ZipArchive::OVERWRITE);
				array_walk_recursive($fake_e107_files, function($fake_filename) use ($archive, $prefix)
				{
					$archive->addFromString($prefix.$fake_filename, $fake_filename);
				});
				$archive->close();
			}
		]);
		$destination = e_TEMP."fake-git-remote-destination/";
		$e_file->removeDir($destination);
		$e_file->mkDir($destination);
		$results = $e_file->unzipGithubArchive('core', $destination);

		$this->assertEmpty($results['error'], "Errors not expected from Git remote update");
		$results['success'] = array_map(function($path)
		{
			$realpath = realpath($path);
			$this->assertNotFalse($realpath,
				"File {$path} reported as successfully extracted but does not exist");
			return $realpath;
		}, $results['success']);
		foreach($fake_e107_files['desired'] as $desired_filename)
		{
			foreach ($src_dest_map as $src => $dest)
			{
				$desired_filename = preg_replace("/^".preg_quote($src, '/')."/", $dest, $desired_filename);
			}
			$this->assertContains(realpath($destination.$desired_filename), $results['success'],
				"Desired file {$desired_filename} did not appear in file system");
		}
		foreach($fake_e107_files['undesired'] as $undesired_filename)
		{
			$this->assertContains($prefix.$undesired_filename, $results['skipped'],
				"{$undesired_filename} was not skipped but should have been");
		}
	}

	/*
	public function testGetRootFolder()
	{

	}

	public function testGetUploaded()
	{

	}

	public function testGitPull()
	{

	}

	public function testCleanFileName()
	{

	}*/
}
