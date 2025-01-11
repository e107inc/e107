<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e107ConstantsTest extends \Codeception\Test\Unit
	{
		public function testVerifyE_BASE()
		{

			$res = defined('e_BASE');
			$this->assertTrue($res);
		}

		public function testVerifyE_SYSTEM_BASE()
		{
		    $res = true;

		    if(!defined('e_SYSTEM'))
		    {
		        $res = false;
		    }
		    elseif(!defined('e_SYSTEM_BASE'))
		    {
		        $res = false;
		    }
		    elseif(e_SYSTEM_BASE === e_SYSTEM)
		    {
		        $res = false;
		    }

			$this->assertTrue($res);
		}

		public function testVerifyE_MEDIA_BASE()
		{
		    $res = true;

		    if(!defined('e_MEDIA'))
		    {
		        $res = false;
		    }
		    elseif(!defined('e_MEDIA_BASE'))
		    {
		        $res = false;
		    }
		    elseif(e_MEDIA_BASE === e_MEDIA)
		    {
		        $res = false;
		    }

			$this->assertTrue($res);
		}

		public function testVerifyE107_INIT()
		{
		    $res = true;

		    if(!defined('e107_INIT'))
		    {
		        $res = false;
		    }

			$this->assertTrue($res);
		}


		public function testVerifyUSERCLASS_LIST()
		{
			$res = true;

			if(!defined('USERCLASS_LIST'))
			{
				 $res = false;
			}

			$this->assertTrue($res);
		}


		public function testVerifye_ROOT()
		{
			$res = true;

			if(!defined('e_ROOT'))
			{
				 $res = false;
			}

			$this->assertTrue($res);


		}


		public function testThemeConstants()
		{
			$this->assertStringEndsWith('e107_themes/bootstrap3/', THEME);
			$this->assertStringEndsWith('/e107_themes/bootstrap3/', THEME_ABS);

			$this->assertNotNull(THEME_LEGACY);
			$this->assertFalse(THEME_LEGACY);

			$this->assertSame('style.css', THEME_STYLE);
		//	$this->assertSame('jumbotron_sidebar_right', THEME_LAYOUT); // loaded later in header.

			$e107 = e107::getInstance();
			$this->assertSame('bootstrap3', $e107->site_theme);
		//	$this->assertStringEndsWith('/e107_themes/bootstrap3/', $e107->http_theme_dir);



		}
	}
