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
			self::fail($e->getMessage());
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
			self::assertEquals($file['expected'],$actual, "isClean() failed on {$file['path']} with error code: ".$this->fl->getErrorCode());
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

			self::assertSame($expected,$actual);
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

			self::assertSame($var['expected'], $actual);
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
			self::assertEquals($file['expected'],$actual, "isAllowedType() failed on: ".$file['path']);
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
					self::assertSame($expected, $result);

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
				self::assertSame($expected, $actual);
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
			self::assertEquals($expected,$actual, $key." does not equal ".$expected." bytes");
		}

	}

	public function testZip()
	{
	    // Arrange
	    $sourcePath = [
	        e_IMAGE.'logo.png',
	         e_IMAGE.'logoHD.png',
	    ];

	    $destinationPath = e_TEMP."testZip.zip";

	    $result = $this->fl->zip($sourcePath, $destinationPath, ['remove_path'=>e_IMAGE]);

	    self::assertNotEmpty($result);
	    self::assertFileExists($destinationPath);

		$expected = [
		  0 => 'logo.png',
		  1 => 'logoHD.png',
		];

	    $contents = self::readZipFile($destinationPath);
		self::assertSame($expected, $contents);


		// Test directory path.
		 $destinationPath = e_TEMP."testZip2.zip";

		 $data = array();
		 $data[] = e_TEMP;
		 $result = $this->fl->zip($data , $destinationPath, ['remove_path'=>e_IMAGE]);
		 $contents = self::readZipFile($destinationPath);
		 self::assertNotEmpty($contents);

	}

	/*

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


			self::assertEquals($item['expected']['mime'], $ret['mime']);

			if($item['imgchk'])
			{
				self::assertEquals($item['expected']['img-width'], $ret['img-width']);
				self::assertEquals($item['expected']['img-height'], $ret['img-height']);
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
		
					self::assertSame($ext, $actual);
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

	     		self::assertContains('install.xml', $files); // 1 level deep.
	     		self::assertContains('theme.php', $files);
	     		self::assertContains('theme.xml', $files);
				self::assertNotContains('style.css', $files);


				// test folder with ony a folder inside. (no files)
				$publicFilter = array('_FT', '^thumbs\.db$','^Thumbs\.db$','.*\._$','^\.htaccess$','^\.cvsignore$','^\.ftpquota$','^index\.html$','^null\.txt$','\.bak$','^.tmp'); // Default file filter (regex format)
				$result = $this->fl->get_files(e_DOCS,'',$publicFilter);
				$expected = array();

				self::assertSame($expected, $result);

			}

			private static function readZipFile($filePath)
			{
			    $zip = new ZipArchive;
				$ret = [];

			    if ($zip->open($filePath) === true)
			    {
			        for($i = 0; $i < $zip->numFiles; $i++)
			        {
			            $ret[] = $zip->getNameIndex($i);

			        }

			        $zip->close();
			    }
			    else
			    {
			        return false;
			    }

			    return $ret;
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

		self::assertEmpty($results['error'], "Errors not expected from Git remote update");
		$results['success'] = array_map(function($path)
		{
			$realpath = realpath($path);
			self::assertNotFalse($realpath,
				"File $path reported as successfully extracted but does not exist");
			return $realpath;
		}, $results['success']);
		foreach($fake_e107_files['desired'] as $desired_filename)
		{
			foreach ($src_dest_map as $src => $dest)
			{
				$desired_filename = preg_replace("/^".preg_quote($src, '/')."/", $dest, $desired_filename);
			}
			self::assertContains(realpath($destination.$desired_filename), $results['success'],
				"Desired file $desired_filename did not appear in file system");
		}
		foreach($fake_e107_files['undesired'] as $undesired_filename)
		{
			self::assertContains($prefix.$undesired_filename, $results['skipped'],
				"$undesired_filename was not skipped but should have been");
		}
	}

	/**
	 * Public addresses (real-world resolvable IPs and unbracketed/bracketed
	 * IPv6) should be allowed through `e_file::isUrlSafe()`.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeAcceptsPublicAddresses()
	{
		self::assertTrue($this->fl->isUrlSafe('http://1.1.1.1/'));
		self::assertTrue($this->fl->isUrlSafe('https://8.8.8.8/'));
		self::assertTrue($this->fl->isUrlSafe('http://93.184.216.34/'));
		self::assertTrue($this->fl->isUrlSafe('http://[2606:4700:4700::1111]/'));
	}

	/**
	 * RFC 1918 ranges (10/8, 172.16/12, 192.168/16) are the canonical
	 * SSRF-pivot targets and must be rejected.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsPrivateIPv4()
	{
		self::assertFalse($this->fl->isUrlSafe('http://10.0.0.1/'));
		self::assertFalse($this->fl->isUrlSafe('http://10.255.255.255/'));
		self::assertFalse($this->fl->isUrlSafe('http://172.16.0.1/'));
		self::assertFalse($this->fl->isUrlSafe('http://172.31.255.255/'));
		self::assertFalse($this->fl->isUrlSafe('http://192.168.0.1/'));
		self::assertFalse($this->fl->isUrlSafe('http://192.168.255.255/'));
	}

	/**
	 * Reserved/loopback/link-local/broadcast IPv4 ranges, including the
	 * AWS-style metadata endpoint at 169.254.169.254.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsReservedIPv4()
	{
		self::assertFalse($this->fl->isUrlSafe('http://0.0.0.0/'));
		self::assertFalse($this->fl->isUrlSafe('http://127.0.0.1/'));
		self::assertFalse($this->fl->isUrlSafe('http://169.254.169.254/'));
		self::assertFalse($this->fl->isUrlSafe('http://255.255.255.255/'));
	}

	/**
	 * IPv6 loopback, ULA, link-local, and IPv4-mapped IPv6 forms must be
	 * rejected. The IPv4-mapped case (`::ffff:127.0.0.1`) is a common
	 * filter-evasion vector that PHP's FILTER_FLAG_NO_RES_RANGE catches.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsReservedIPv6()
	{
		self::assertFalse($this->fl->isUrlSafe('http://[::1]/'));
		self::assertFalse($this->fl->isUrlSafe('http://[fc00::1]/'));
		self::assertFalse($this->fl->isUrlSafe('http://[fd00::dead:beef]/'));
		self::assertFalse($this->fl->isUrlSafe('http://[fe80::1]/'));
		self::assertFalse($this->fl->isUrlSafe('http://[::ffff:127.0.0.1]/'));
	}

	/**
	 * Anything other than http(s) must be refused, even if the host would
	 * otherwise pass the IP check. This stops file://, gopher://, dict://,
	 * etc. before they ever reach cURL.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsNonHttpSchemes()
	{
		self::assertFalse($this->fl->isUrlSafe('file:///etc/passwd'));
		self::assertFalse($this->fl->isUrlSafe('gopher://1.1.1.1/'));
		self::assertFalse($this->fl->isUrlSafe('ftp://1.1.1.1/'));
		self::assertFalse($this->fl->isUrlSafe('dict://1.1.1.1/'));
		self::assertFalse($this->fl->isUrlSafe('javascript:alert(1)'));
	}

	/**
	 * Empty, schemeless, hostless, or otherwise unparseable URLs should be
	 * rejected without making any network call.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsMalformedUrls()
	{
		self::assertFalse($this->fl->isUrlSafe(''));
		self::assertFalse($this->fl->isUrlSafe('not-a-url'));
		self::assertFalse($this->fl->isUrlSafe('http:///path-only'));
		self::assertFalse($this->fl->isUrlSafe('//1.1.1.1/'));
	}

	/**
	 * Decimal/hex/octal-encoded IPv4 forms (e.g. `2130706433` for
	 * 127.0.0.1) bypass FILTER_VALIDATE_IP because they aren't dotted
	 * quads. They then fail dns_get_record (no valid A/AAAA record),
	 * so the no-IPs-found branch rejects them.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsObfuscatedAddresses()
	{
		self::assertFalse($this->fl->isUrlSafe('http://2130706433/'));
		self::assertFalse($this->fl->isUrlSafe('http://0x7f000001/'));
	}

	/**
	 * `getRemoteFile()` should refuse SSRF candidates without making any
	 * cURL call and surface a meaningful error message.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testGetRemoteFileRejectsUnsafeUrl()
	{
		$result = $this->fl->getRemoteFile('http://127.0.0.1/foo.jpg', 'foo.jpg');
		self::assertFalse($result);
		self::assertStringContainsString('private/reserved IP', $this->fl->getErrorMessage());
	}

	/**
	 * `getRemoteContent()` should refuse SSRF candidates without making
	 * any cURL call and surface a meaningful error message.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testGetRemoteContentRejectsUnsafeUrl()
	{
		$result = $this->fl->getRemoteContent('http://10.0.0.1/');
		self::assertFalse($result);
		self::assertStringContainsString('private/reserved IP', $this->fl->getErrorMessage());
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
