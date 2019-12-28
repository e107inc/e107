<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_marketplaceTest extends \Codeception\Test\Unit
	{
		/** @var e_marketplace */
		private $mp;

		protected function _before()
		{
			require_once(e_HANDLER."e_marketplace.php");

			try
			{
				$mock_adapter = $this->make('e_marketplace_adapter_wsdl',
					[
						'getRemoteFile' => function($remote_url, $local_file, $type='temp')
						{
							file_put_contents(e_TEMP.$local_file,
								/**
								 * Zip file containing:
								 *   thing/
								 *   thing/plugin.php
								 *   thing/theme.php
								 *   thing/index.php
								 *   thing/README.md
								 */
								base64_decode(
									<<<DATA
UEsDBAoAAAAAAHaVYU0AAAAAAAAAAAAAAAAGABwAdGhpbmcvVVQJAAOvj9tbuI/bW3V4CwABBOgD
AAAE6AMAAFBLAwQKAAAAAABxlWFNAAAAAAAAAAAAAAAAEAAcAHRoaW5nL3BsdWdpbi5waHBVVAkA
A6aP21umj9tbdXgLAAEE6AMAAAToAwAAUEsDBAoAAAAAAHOVYU0AAAAAAAAAAAAAAAAPABwAdGhp
bmcvdGhlbWUucGhwVVQJAAOpj9tbqY/bW3V4CwABBOgDAAAE6AMAAFBLAwQKAAAAAAB0lWFNAAAA
AAAAAAAAAAAADwAcAHRoaW5nL2luZGV4LnBocFVUCQADrI/bW6yP21t1eAsAAQToAwAABOgDAABQ
SwMECgAAAAAAdpVhTQAAAAAAAAAAAAAAAA8AHAB0aGluZy9SRUFETUUubWRVVAkAA6+P21uvj9tb
dXgLAAEE6AMAAAToAwAAUEsBAh4DCgAAAAAAdpVhTQAAAAAAAAAAAAAAAAYAGAAAAAAAAAAQAP1B
AAAAAHRoaW5nL1VUBQADr4/bW3V4CwABBOgDAAAE6AMAAFBLAQIeAwoAAAAAAHGVYU0AAAAAAAAA
AAAAAAAQABgAAAAAAAAAAAC0gUAAAAB0aGluZy9wbHVnaW4ucGhwVVQFAAOmj9tbdXgLAAEE6AMA
AAToAwAAUEsBAh4DCgAAAAAAc5VhTQAAAAAAAAAAAAAAAA8AGAAAAAAAAAAAALSBigAAAHRoaW5n
L3RoZW1lLnBocFVUBQADqY/bW3V4CwABBOgDAAAE6AMAAFBLAQIeAwoAAAAAAHSVYU0AAAAAAAAA
AAAAAAAPABgAAAAAAAAAAAC0gdMAAAB0aGluZy9pbmRleC5waHBVVAUAA6yP21t1eAsAAQToAwAA
BOgDAABQSwECHgMKAAAAAAB2lWFNAAAAAAAAAAAAAAAADwAYAAAAAAAAAAAAtIEcAQAAdGhpbmcv
UkVBRE1FLm1kVVQFAAOvj9tbdXgLAAEE6AMAAAToAwAAUEsFBgAAAAAFAAUAoQEAAGUBAAAAAA==
DATA
								));
							return true;
						}
					]);
				$this->mp = $this->make('e_marketplace',
					[
						'adapter' => $mock_adapter
					]);
				$this->mp->__construct();
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_marketplace object");
			}
		}

/*
		public function testRenderLoginForm()
		{

		}
*/
		public function testDownload()
		{
			$path = e_PLUGIN."thing";
			$tempPath = e_TEMP."thing";
			$id = 912;

			if(is_dir($path))
			{
				e107::getFile()->removeDir($path);
			//	rename($path, $path."_old_".time());
			}

			if(is_dir($tempPath))
			{
				e107::getFile()->removeDir($tempPath);
				// rename($tempPath, $tempPath."_old_".time());
			}

			$status = $this->mp->download($id,'','plugin' );

			$this->assertTrue($status,"Couldn't download plugin or move to plugin folder.");

			$exists = (is_dir($path) && count(scandir($path)) > 4);

			$this->assertTrue($exists,"plugin folder is missing files.");

		}
/*
		public function testGenerateAuthKey()
		{

		}

		public function testCall()
		{

		}

		public function testGetVersionList()
		{

		}

		public function testHasAuthKey()
		{

		}

		public function testAdapter()
		{

		}

		public function testMakeAuthKey()
		{

		}

		public function testSetAuthKey()
		{

		}

		public function testGetDownloadModal()
		{

		}*/
	}
