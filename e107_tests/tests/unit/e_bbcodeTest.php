<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_bbcodeTest extends \Codeception\Test\Unit
	{

		/** @var e_bbcode */
		protected $bb;

		protected function _before()
		{

			try
			{
				$this->bb = $this->make('e_bbcode');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}
/*
		public function testSetClass()
		{

		}

		public function testResizeWidth()
		{

		}

		public function testGetContent()
		{

		}

		public function testHtmltoBBcode()
		{

		}

		public function testImgToBBcode()
		{

		}

		public function testResizeHeight()
		{

		}

		public function testRenderButtons()
		{

		}

		public function testProcessTag()
		{

		}

		public function testParseBBCodes()
		{

		}

		public function testClearClass()
		{

		}

		public function testGetClass()
		{

		}

		public function testGetMode()
		{

		}
*/



	}
