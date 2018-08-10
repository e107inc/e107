<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_fileTest extends \Codeception\Test\Unit
	{

		/** @var e_file  */
		protected $fl;
		protected $exploitFile = '';

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

		}

		protected function _after()
		{
			unlink($this->exploitFile);
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
				$this->assertEquals($file['expected'],$actual, "isClean() failed with error code: ".$this->fl->getErrorCode());
			}

		}

		public function testGetAllowedFileTypes()
		{
			$actual = $this->fl->getAllowedFileTypes();

			$expected = array (
			  'zip' => 2048,
			  'gz' => 2048,
			  'jpg' => 2048,
			  'jpeg' => 2048,
			  'png' => 2048,
			  'gif' => 2048,
			  'xml' => 2048,
			  'pdf' => 2048,
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
			//	$this->assertEquals($file['expected'],$actual, "isAllowedType() failed on: ".$file['path']);
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

		public function testFile_size_decode()
		{

		}

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

		public function testUnzipGithubArchive()
		{

		}

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
