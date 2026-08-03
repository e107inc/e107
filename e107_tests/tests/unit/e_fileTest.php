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
	/**
	 * Traversal out of the downloads directory must not resolve, however the
	 * separators are spelled. The dot-padded form is the one that never
	 * contained the literal `../../` the QUERY_STRING filter looks for, which
	 * is why containment, not the filter, is the boundary.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathRejectsTraversal()
	{
		$payloads = array(
			'../../../e107_config.php',
			'.././.././../e107_config.php',
			'../../../class2.php',
			'..\\..\\..\\e107_config.php',
			'sub/../../../../e107_config.php',
		);

		foreach($payloads as $payload)
		{
			self::assertFalse($this->fl->resolveSendPath(e_DOWNLOAD . $payload),
				"resolveSendPath() must reject traversal payload: " . $payload);
		}
	}

	/**
	 * Files that exist but sit outside every permitted root.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathRejectsPathsOutsideRoots()
	{
		self::assertFalse($this->fl->resolveSendPath('/etc/passwd'));
		self::assertFalse($this->fl->resolveSendPath(e_ROOT . 'class2.php'));
		self::assertFalse($this->fl->resolveSendPath(e_PLUGIN . 'download/request.php'));
	}

	/**
	 * A NUL byte must be rejected before realpath() sees it: PHP 8 raises a
	 * ValueError there, which would turn an unauthenticated request into a
	 * fatal. PHP 5.6/7 return NULL rather than false, so the guard cannot be a
	 * `=== false` test either.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathRejectsNullByteWithoutFatal()
	{
		self::assertFalse($this->fl->resolveSendPath(e_SYSTEM . "filetypes.xml\0.jpg"));
		self::assertFalse($this->fl->resolveSendPath("\0"));
	}

	/**
	 * Stream wrappers never survive realpath(), and must not be served.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathRejectsStreamWrappers()
	{
		self::assertFalse($this->fl->resolveSendPath('php://filter/read=convert.base64-encode/resource=' . e_ROOT . 'class2.php'));
		self::assertFalse($this->fl->resolveSendPath('phar://' . e_SYSTEM . 'nope.phar/x'));
		self::assertFalse($this->fl->resolveSendPath('data://text/plain;base64,SGVsbG8='));
	}

	/**
	 * Empty and non-string input must return false rather than raising.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathRejectsEmptyInput()
	{
		self::assertFalse($this->fl->resolveSendPath(''));
		self::assertFalse($this->fl->resolveSendPath(null));
		self::assertFalse($this->fl->resolveSendPath(array()));
		self::assertFalse($this->fl->resolveSendPath(e_MEDIA), 'a directory is not a file to send');
	}

	/**
	 * A sibling directory sharing a root's name prefix must not pass. This is
	 * what the old strpos()-anywhere test got wrong.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathRejectsSiblingPrefix()
	{
		$sibling = rtrim(e_SYSTEM, '/\\') . '_sibling';
		@mkdir($sibling, 0755, true);
		$file = $sibling . '/secret.txt';
		file_put_contents($file, 'nope');

		try
		{
			self::assertFalse($this->fl->resolveSendPath($file),
				'a directory that merely shares a prefix with a root must not pass');
		}
		catch (Exception $e)
		{
			@unlink($file);
			@rmdir($sibling);
			throw $e;
		}

		@unlink($file);
		@rmdir($sibling);
	}

	/**
	 * Legitimate files inside the permitted roots must still be served: this is
	 * the backwards-compatibility guard, since the old check never actually
	 * enforced anything on a default install.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathAcceptsPermittedRoots()
	{
		$expected = realpath($this->filetypesFile);
		self::assertSame($expected, $this->fl->resolveSendPath($this->filetypesFile),
			'a file under e_SYSTEM must still be sendable');

		$mediaFile = e_MEDIA . 'ghsa87hm_probe.txt';
		file_put_contents($mediaFile, 'ok');
		$actual = $this->fl->resolveSendPath($mediaFile);
		@unlink($mediaFile);

		self::assertSame(realpath(e_MEDIA) . DIRECTORY_SEPARATOR . 'ghsa87hm_probe.txt', $actual,
			'a file under e_MEDIA must still be sendable');
	}

	/**
	 * An explicit roots list replaces the defaults, so a caller handling
	 * untrusted input can pin itself to one directory.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathNarrowsToSuppliedRoots()
	{
		self::assertFalse($this->fl->resolveSendPath($this->filetypesFile, array(e_DOWNLOAD)),
			'an e_SYSTEM file must not pass when the caller pinned itself to e_DOWNLOAD');

		self::assertSame(realpath($this->filetypesFile),
			$this->fl->resolveSendPath($this->filetypesFile, array(e_SYSTEM)));
	}

	/**
	 * Widening to e_ROOT is what keeps {e_PLUGIN} and {e_THEME} media rows
	 * downloadable through request.php, and must still refuse to leave the
	 * installation.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathHonoursRootWidening()
	{
		$pluginFile = e_PLUGIN . 'download/request.php';
		self::assertSame(realpath($pluginFile), $this->fl->resolveSendPath($pluginFile, array(e_ROOT)));
		self::assertFalse($this->fl->resolveSendPath('/etc/passwd', array(e_ROOT)));
	}

	/**
	 * Root lists that cannot be resolved must fail closed rather than letting
	 * everything through, which is exactly how the original check failed.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathFailsClosedOnUnusableRoots()
	{
		self::assertFalse($this->fl->resolveSendPath($this->filetypesFile, array()));
		self::assertFalse($this->fl->resolveSendPath($this->filetypesFile, array('/definitely/not/here/')));
		self::assertFalse($this->fl->resolveSendPath($this->filetypesFile, array(false, null, '')));
	}

	/**
	 * A trailing separator on a supplied root must not change the outcome.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testResolveSendPathIgnoresTrailingSeparatorOnRoots()
	{
		$with = $this->fl->resolveSendPath($this->filetypesFile, array(rtrim(e_SYSTEM, '/\\') . '/'));
		$without = $this->fl->resolveSendPath($this->filetypesFile, array(rtrim(e_SYSTEM, '/\\')));

		self::assertSame($with, $without);
		self::assertSame(realpath($this->filetypesFile), $with);
	}

	/**
	 * Names the download plugin may look up. Traversal, absolute paths, NUL
	 * bytes and {e_XXX} constants are refused; ordinary file names, including
	 * ones with dots inside a component, are not.
	 *
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testIsSafeRelativePath()
	{
		$reject = array(
			'../e107_config.php',
			'.././.././../e107_config.php',
			'..%2f..%2fe107_config.php',
			'%2e%2e%2fe107_config.php',
			'a/../../b',
			'..\\..\\e107_config.php',
			'/etc/passwd',
			'\\\\server\\share\\x',
			'C:\\windows\\win.ini',
			"x\0.zip",
			'{e_BASE}e107_config.php',
			'{e_MEDIA_FILE}x.zip',
			'',
			null,
		);

		foreach($reject as $path)
		{
			self::assertFalse(e_file::isSafeRelativePath($path),
				"isSafeRelativePath() must reject: " . var_export($path, true));
		}

		$accept = array(
			'myfile.zip',
			'sub/dir/myfile.zip',
			'2026-07/foo.tar.gz',
			'pub_file.zip',
			'my..file.zip',
			'.hidden.zip',
			'..file.zip',
		);

		foreach($accept as $path)
		{
			self::assertTrue(e_file::isSafeRelativePath($path),
				"isSafeRelativePath() must accept: " . $path);
		}
	}

	/**
	 * Ref: GHSA-87hm-vh32-7c3r.
	 */
	public function testIsAbsolutePath()
	{
		self::assertTrue(e_file::isAbsolutePath('/home/account/files'));
		self::assertTrue(e_file::isAbsolutePath('\\\\server\\share\\x'));
		self::assertTrue(e_file::isAbsolutePath('C:\\xampp\\htdocs'));
		self::assertTrue(e_file::isAbsolutePath('c:/xampp/htdocs'));

		self::assertFalse(e_file::isAbsolutePath('e107_media/files/'));
		self::assertFalse(e_file::isAbsolutePath('../e107_media/'));
		self::assertFalse(e_file::isAbsolutePath(''));
		self::assertFalse(e_file::isAbsolutePath(null));
	}

	/**
	 * The guard files land, and only once.
	 *
	 * Called ahead of every attachment the PM plugin stores, so rewriting a
	 * file that is already there would be an upload's worth of pointless
	 * writes. The rule that has to survive is that anything already in place
	 * is left exactly as it was found: an administrator who has widened or
	 * narrowed the rule by hand keeps their version.
	 */
	public function testProtectDirectory()
	{
		$dir = e_TEMP . 'test_protect_directory_' . uniqid() . '/';

		self::assertFalse($this->fl->protectDirectory($dir),
			'A directory that is not there must not be created');
		self::assertDirectoryDoesNotExist($dir);

		self::assertFalse($this->fl->protectDirectory(''));

		mkdir($dir);

		try
		{
			self::assertTrue($this->fl->protectDirectory(rtrim($dir, '/')),
				'A path without a trailing separator is the same directory');

			self::assertFileExists($dir . '.htaccess');
			self::assertFileExists($dir . 'index.html');
			self::assertSame('', file_get_contents($dir . 'index.html'));

			$rule = file_get_contents($dir . '.htaccess');
			self::assertStringContainsString('RedirectMatch 403', $rule);
			self::assertStringContainsString('Deny from all', $rule);
			self::assertStringNotContainsString('Require ', $rule,
				'A guard file may ask for no AllowOverride class beyond the ones e107.htaccess already needs');

			file_put_contents($dir . '.htaccess', 'an administrator wrote this');

			self::assertTrue($this->fl->protectDirectory($dir));
			self::assertSame('an administrator wrote this', file_get_contents($dir . '.htaccess'),
				'An existing guard file must be left alone');
		}
		finally
		{
			@unlink($dir . '.htaccess');
			@unlink($dir . 'index.html');
			@rmdir($dir);
		}
	}

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

	/**
	 * Public addresses (real-world resolvable IPs and unbracketed/bracketed
	 * IPv6) should be allowed through `e_file::isUrlSafe()`.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeAcceptsPublicAddresses()
	{
		$this->assertTrue($this->fl->isUrlSafe('http://1.1.1.1/'));
		$this->assertTrue($this->fl->isUrlSafe('https://8.8.8.8/'));
		$this->assertTrue($this->fl->isUrlSafe('http://93.184.216.34/'));
		$this->assertTrue($this->fl->isUrlSafe('http://[2606:4700:4700::1111]/'));
	}

	/**
	 * RFC 1918 ranges (10/8, 172.16/12, 192.168/16) are the canonical
	 * SSRF-pivot targets and must be rejected.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsPrivateIPv4()
	{
		$this->assertFalse($this->fl->isUrlSafe('http://10.0.0.1/'));
		$this->assertFalse($this->fl->isUrlSafe('http://10.255.255.255/'));
		$this->assertFalse($this->fl->isUrlSafe('http://172.16.0.1/'));
		$this->assertFalse($this->fl->isUrlSafe('http://172.31.255.255/'));
		$this->assertFalse($this->fl->isUrlSafe('http://192.168.0.1/'));
		$this->assertFalse($this->fl->isUrlSafe('http://192.168.255.255/'));
	}

	/**
	 * Reserved/loopback/link-local/broadcast IPv4 ranges, including the
	 * AWS-style metadata endpoint at 169.254.169.254.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsReservedIPv4()
	{
		$this->assertFalse($this->fl->isUrlSafe('http://0.0.0.0/'));
		$this->assertFalse($this->fl->isUrlSafe('http://127.0.0.1/'));
		$this->assertFalse($this->fl->isUrlSafe('http://169.254.169.254/'));
		$this->assertFalse($this->fl->isUrlSafe('http://255.255.255.255/'));
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
		$this->assertFalse($this->fl->isUrlSafe('http://[::1]/'));
		$this->assertFalse($this->fl->isUrlSafe('http://[fc00::1]/'));
		$this->assertFalse($this->fl->isUrlSafe('http://[fd00::dead:beef]/'));
		$this->assertFalse($this->fl->isUrlSafe('http://[fe80::1]/'));
		$this->assertFalse($this->fl->isUrlSafe('http://[::ffff:127.0.0.1]/'));
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
		$this->assertFalse($this->fl->isUrlSafe('file:///etc/passwd'));
		$this->assertFalse($this->fl->isUrlSafe('gopher://1.1.1.1/'));
		$this->assertFalse($this->fl->isUrlSafe('ftp://1.1.1.1/'));
		$this->assertFalse($this->fl->isUrlSafe('dict://1.1.1.1/'));
		$this->assertFalse($this->fl->isUrlSafe('javascript:alert(1)'));
	}

	/**
	 * Empty, schemeless, hostless, or otherwise unparseable URLs should be
	 * rejected without making any network call.
	 *
	 * Ref: GHSA-92fr-7h4f-22pp.
	 */
	public function testIsUrlSafeRejectsMalformedUrls()
	{
		$this->assertFalse($this->fl->isUrlSafe(''));
		$this->assertFalse($this->fl->isUrlSafe('not-a-url'));
		$this->assertFalse($this->fl->isUrlSafe('http:///path-only'));
		$this->assertFalse($this->fl->isUrlSafe('//1.1.1.1/'));
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
		$this->assertFalse($this->fl->isUrlSafe('http://2130706433/'));
		$this->assertFalse($this->fl->isUrlSafe('http://0x7f000001/'));
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
		$this->assertFalse($result);
		$this->assertStringContainsString('private/reserved IP', $this->fl->getErrorMessage());
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
		$this->assertFalse($result);
		$this->assertStringContainsString('private/reserved IP', $this->fl->getErrorMessage());
	}

	/**
	 * A directory holding uploads that are not for direct download gets a deny
	 * rule and a blank index.html.
	 */
	public function testProtectDirectoryWritesBothGuards()
	{
		$dir = e_TEMP.'e107_tests_protect_'.uniqid().'/';
		mkdir($dir);

		self::assertTrue($this->fl->protectDirectory($dir));
		self::assertFileExists($dir.'index.html');
		$rule = file_get_contents($dir.'.htaccess');
		self::assertStringContainsString('RedirectMatch 403', $rule);
		self::assertStringNotContainsString('Require ', $rule);

		unlink($dir.'.htaccess');
		unlink($dir.'index.html');
		rmdir($dir);
	}

	/**
	 * A rule an administrator has edited is never rewritten, which is what
	 * makes the call cheap enough to make ahead of every write.
	 */
	public function testProtectDirectoryKeepsWhatIsAlreadyThere()
	{
		$dir = e_TEMP.'e107_tests_protect_'.uniqid().'/';
		mkdir($dir);
		file_put_contents($dir.'.htaccess', 'Require ip 10.0.0.0/8');

		self::assertTrue($this->fl->protectDirectory($dir));
		self::assertSame('Require ip 10.0.0.0/8', file_get_contents($dir.'.htaccess'));

		unlink($dir.'.htaccess');
		unlink($dir.'index.html');
		rmdir($dir);
	}

	/**
	 * The helper never creates the directory: a caller that thought it had
	 * protected somewhere has to be told it has not.
	 */
	public function testProtectDirectoryRefusesAMissingDirectory()
	{
		self::assertFalse($this->fl->protectDirectory(e_TEMP.'e107_tests_absent_'.uniqid().'/'));
		self::assertFalse($this->fl->protectDirectory(''));
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
