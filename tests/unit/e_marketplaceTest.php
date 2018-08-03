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
				$this->mp = $this->make('e_marketplace');
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
			$path = e_PLUGIN."nofollow";
			$tempPath = e_TEMP."nofollow";
			$id = 912; // No-follow plugin on e107.org

			if(is_dir($path))
			{
				rename($path, $path."_old_".time());
			}

			if(is_dir($tempPath))
			{
				rename($tempPath, $tempPath."_old_".time());
			}

		//	e107::getMessage()->reset();

			$status = $this->mp->download($id,'','plugin' );

		//	$messages = e107::getMessage()->render( 'default',false,  true, true);
		//	print_r($messages);

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
