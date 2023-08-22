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
			$this->fail($e->getMessage());
		}

		$this->exploitFile = e_TEMP."test_exploit_file.jpg";

		$content = "<?php echo 'This file could be dangerous.'; ?>";

		file_put_contents($this->exploitFile,$content);

		$this->filetypesFile = e_SYSTEM."filetypes.xml";

		$content = '<?xml version="1.0" encoding="utf-8"?>
						<e107Filetypes>
							<class name="253" type="zip,gz,jpg,jpeg,png,gif,xml,pdf" maxupload="2M" />
							<class name="admin" type="zip,gz,jpg,jpeg,png,gif,xml,pdf" maxupload="4M" />
							<class name="main" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf,mov" maxupload="5M" />
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


		$tests = array(
			e_UC_MEMBER => array (
				'zip' => 2097152, // 2M in bytes
				'gz' => 2097152,
				'jpg' => 2097152,
				'jpeg' => 2097152,
				'png' => 2097152,
				'gif' => 2097152,
				'xml' => 2097152,
				'pdf' => 2097152,
			),
			e_UC_ADMIN => array (
				  'zip' => 4194304,
				  'gz' => 4194304,
				  'jpg' => 4194304,
				  'jpeg' => 4194304,
				  'png' => 4194304,
				  'gif' => 4194304,
				  'xml' => 4194304,
				  'pdf' => 4194304,
				),
			e_UC_MAINADMIN => array (
				  'zip' => 5242880,
				  'gz' => 5242880,
				  'jpg' => 5242880,
				  'jpeg' => 5242880,
				  'png' => 5242880,
				  'gif' => 5242880,
				  'webp' => 5242880,
				  'xml' => 5242880,
				  'pdf' => 5242880,
				  'mov' => 5242880,
				),
		);

		foreach($tests as $class => $expected)
		{
			$actual = $this->fl->getAllowedFileTypes($class);

			if(empty($expected))
			{
				var_export($actual);
				continue;
			}


			$this->assertSame($expected,$actual);
		}



	}


	public function testGetMime()
	{
		$test = array(
			array('path'=> 'somefile',                              'expected' => false), // no extension
			array('path'=> 'somefile.bla',                          'expected' => 'application/octet-stream'), // unknown
			array('path'=> "{e_PLUGIN}filetypes.xml",               'expected' => 'application/xml'),
			array('path'=> "gallery/images/butterfly.jpg",          'expected' => 'image/jpeg'),
			array('path'=> "image.webp",                            'expected' => 'image/webp'),
		);

		foreach($test as $var)
		{
			$actual = $this->fl->getMime($var['path']);

			$this->assertSame($var['expected'], $actual);
		}
	}

	public function testIsAllowedType()
	{

		$isAllowedTest = array(
			array('path'=> 'somefile.bla',                          'expected' => false), // suspicious
			array('path'=> 'somefile.php',                          'expected' => false), // suspicious
			array('path'=> 'somefile.exe',                          'expected' => false), // suspicious
			array('path'=> e_SYSTEM."filetypes.xml",                 'expected' => true), // permitted
			array('path'=> e_PLUGIN."gallery/images/butterfly.jpg",  'expected' => true), // permitted
			array('path'=> 'http://127.0.0.1:8070/file.svg',        'expected'=>false), // not permitted
			array('path'=> 'http://127.0.0.1:8070/butterfly.jpg',   'expected'=>false), // not permitted
			array('path'=> 'http://localhost:8070/file.svg',        'expected'=>false), // not permitted
			array('path'=> 'http://localhost:8070/butterfly.jpg',   'expected'=>false), // not permitted
			array('path'=> 'http://domain.com:8070/file.svg',        'expected'=>false), // suspicious
			array('path'=> 'http://domain.com:8070/butterfly.jpg',   'expected'=>true), // permitted
			array('path'=> 'http://127.0.0.1/bla.php',              'expected'=>false), // suspicious
			array('path'=> 'http://127.0.0.1/bla.php?butterfly.jpg',   'expected'=>false), // suspicious

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
*/
			public function testFile_size_encode()
			{
				$arr = array(
					'1&nbsp;kB'   => 1024,
					'2&nbsp;kB'   => 2048,
					'1&nbsp;MB'   => 1048576,
					'1&nbsp;GB'   => 1073741824,
					'1&nbsp;TB'   => 1099511627776,
				);

				foreach($arr as $expected => $bytes)
				{
					$result = $this->fl->file_size_encode($bytes);
					$this->assertSame($expected, $result);

				}

			}
/*
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
*/
			public function testGet_dirs()
			{
				$actual = $this->fl->get_dirs(e_LANGUAGEDIR);
				$expected = array (  0 => 'English' );
				$this->assertSame($expected, $actual);
			}
/*
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

	public function testGetFileInfo()
	{
		$tests = array(
			0   => array(
				'input'     => "e107_web/lib/font-awesome/4.7.0/fonts/fontawesome-webfont.svg",
				'imgchk'    => false,
				'expected'  => ['mime'=>'image/svg+xml']
			),
			1   => array(
				'input'     => "e107_plugins/gallery/images/horse.jpg",
				'imgchk'    => true,
				'expected'  => ['mime'=>'image/jpeg', 'img-width'=>1500, 'img-height'=>1000]
				),
			2   => array(
				'input'     => "e107_tests/tests/_data/fileTest/corrupted_image.webp",
				'imgchk'    => false,
				'expected'  => ['mime' => false]
				),
			3   => array(
				'input'     => "none-existent-file.png",
				'imgchk'    => false,
				'expected'  => ['mime' => false]
				),
		);

		foreach($tests as $item)
		{
			$path = APP_PATH.'/'.$item['input'];
			$ret = $this->fl->getFileInfo($path);

			if($ret === false)
			{
				$ret = array('mime'=>false);
			}


			$this->assertEquals($item['expected']['mime'], $ret['mime']);

			if($item['imgchk'])
			{
				$this->assertEquals($item['expected']['img-width'], $ret['img-width']);
				$this->assertEquals($item['expected']['img-height'], $ret['img-height']);
			}
		}

	}
	/*
			public function testPrepareDirectory()
			{

			}
*/
			public function testGetFileExtension()
			{
				$test = array(
				'application/ecmascript'                                                    => '.es',
				'application/epub+zip'                                                      => '.epub',
				'application/java-archive'                                                  => '.jar',
				'application/javascript'                                                    => '.js',
				'application/json'                                                          => '.json',
				'application/msword'                                                        => '.doc',
				'application/octet-stream'                                                  => '.bin',
				'application/ogg'                                                           => '.ogx',
				'application/pdf'                                                           => '.pdf',
				'application/rtf'                                                           => '.rtf',
				'application/typescript'                                                    => '.ts',
				'application/vnd.amazon.ebook'                                              => '.azw',
				'application/vnd.apple.installer+xml'                                       => '.mpkg',
				'application/vnd.mozilla.xul+xml'                                           => '.xul',
				'application/vnd.ms-excel'                                                  => '.xls',
				'application/vnd.ms-fontobject'                                             => '.eot',
				'application/vnd.ms-powerpoint'                                             => '.ppt',
				'application/vnd.oasis.opendocument.presentation'                           => '.odp',
				'application/vnd.oasis.opendocument.spreadsheet'                            => '.ods',
				'application/vnd.oasis.opendocument.text'                                   => '.odt',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => '.xlsx',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => '.docx',
				'application/vnd.visio'                                                     => '.vsd',
				'application/x-7z-compressed'                                               => '.7z',
				'application/x-abiword'                                                     => '.abw',
				'application/x-bzip'                                                        => '.bz',
				'application/x-bzip2'                                                       => '.bz2',
				'application/x-csh'                                                         => '.csh',
				'application/x-rar-compressed'                                              => '.rar',
				'application/x-sh'                                                          => '.sh',
				'application/x-shockwave-flash'                                             => '.swf',
				'application/x-tar'                                                         => '.tar',
				'application/xhtml+xml'                                                     => '.xhtml',
				'application/xml'                                                           => '.xml',
				'application/zip'                                                           => '.zip',
				'audio/aac'                                                                 => '.aac',
				'audio/midi'                                                                => '.midi',
				'audio/mpeg'                                                                => '.mp3',
				'audio/ogg'                                                                 => '.oga',
				'audio/wav'                                                                 => '.wav',
				'audio/webm'                                                                => '.weba',
				'font/otf'                                                                  => '.otf',
				'font/ttf'                                                                  => '.ttf',
				'font/woff'                                                                 => '.woff',
				'font/woff2'                                                                => '.woff2',
				'image/bmp'                                                                 => '.bmp',
				'image/gif'                                                                 => '.gif',
				'image/jpeg'                                                                => '.jpg',
				'image/png'                                                                 => '.png',
				'image/svg+xml'                                                             => '.svg',
				'image/tiff'                                                                => '.tiff',
				'image/webp'                                                                => '.webp',
				'image/x-icon'                                                              => '.ico',
				'text/calendar'                                                             => '.ics',
				'text/css'                                                                  => '.css',
				'text/csv'                                                                  => '.csv',
				'text/html'                                                                 => '.html',
				'text/plain'                                                                => '.txt',
				'video/mp4'                                                                 => '.mp4',
				'video/mpeg'                                                                => '.mpeg',
				'video/ogg'                                                                 => '.ogv',
				'video/webm'                                                                => '.webm',
				'video/x-msvideo'                                                           => '.avi',
				);

				foreach($test as $mime=>$ext)
				{
					$actual = $this->fl->getFileExtension($mime);
		
					$this->assertSame($ext, $actual);
				}	
			}
/*
			public function testRmtree()
			{

			}
*/
			public function testGet_files()
			{
				$reject = array('style.*');
				$result = $this->fl->get_files(e_THEME."voux/", "\.php|\.css|\.xml|preview\.jpg|preview\.png", $reject, 1);

				$files = array();
				foreach($result as $f)
				{
					$files[] = $f['fname'];
				}

	     		$this->assertContains('install.xml', $files); // 1 level deep.
	     		$this->assertContains('theme.php', $files);
	     		$this->assertContains('theme.xml', $files);
				$this->assertNotContains('style.css', $files);


				// test folder with ony a folder inside. (no files)
				$publicFilter = array('_FT', '^thumbs\.db$','^Thumbs\.db$','.*\._$','^\.htaccess$','^\.cvsignore$','^\.ftpquota$','^index\.html$','^null\.txt$','\.bak$','^.tmp'); // Default file filter (regex format)
				$result = $this->fl->get_files(e_DOCS,'',$publicFilter);
				$expected = array();

				$this->assertSame($expected, $result);

			}
/*
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
