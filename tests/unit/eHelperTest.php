<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class eHelperTest extends \Codeception\Test\Unit
	{
		/** @var eHelper */
		protected $hp;

		protected function _before()
		{
			try
			{
				$this->hp = $this->make('eHelper');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load eHelper object");
			}

		}

/*

		public function testFormatMetaTitle()
		{

		}

		public function testFormatMetaKeys()
		{

		}

		public function testGetMemoryUsage()
		{

		}

		public function testUnderscore()
		{

		}

		public function testFormatMetaDescription()
		{

		}

		public function testSecureIdAttr()
		{

		}

		public function testTitle2sef()
		{

		}

		public function testCamelize()
		{

		}

		public function testScParams()
		{

		}

		public function testLabelize()
		{

		}

		public function testSecureClassAttr()
		{

		}

		public function testSecureStyleAttr()
		{

		}

		public function testScDualParams()
		{

		}

		public function testDasherize()
		{

		}

		public function testParseMemorySize()
		{

		}

		public function testBuildAttr()
		{

		}

		public function testSecureSef()
		{

		}*/


		
	}
