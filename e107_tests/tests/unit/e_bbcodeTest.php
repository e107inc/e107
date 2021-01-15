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

			$this->bb->__construct();

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
*/
		public function testParseBBCodes()
		{
			$codes = array (
					  '_br' =>
					  array (

					  ),
					  'b' =>
					  array (
					  ),
					  'alert' =>  array (
					    'warning'  => array('input'=>'Warning Message', 'expected'=>"<div class='alert alert-warning'>Warning Message</div>"),
					  ),
					  'block' =>
					  array (
					  ),
					  'code' => array (

					  ),
					  'glyph' =>
					  array (
					  ),
					  'h' =>
					  array (
					  ),
					  'img' =>
					  array (
					  ),
					  'nobr' =>
					  array (
					  ),
					  'p' =>
					  array (
					  ),
					  'video' =>
					  array (
					  ),
					  'youtube' =>
					  array (
					  ),
					  'blockquote' =>
					  array (
					  ),
					  'br' =>
					  array (
					  ),
					  'center' =>
					  array (
					  ),
					  'color' =>
					  array (
					  ),
					  'email' =>
					  array (
					  ),
					  'file' =>
					  array (
					  ),
					  'flash' =>
					  array (
					  ),
					  'hide' =>
					  array (
					  ),
					  'html' =>
					  array (
					  ),
					  'i' =>
					  array (
					  ),
					  'index.html' =>
					  array (
					  ),
					  'justify' =>
					  array (
					  ),
					  'left' =>
					  array (
					  ),
					  'link' =>  array (
					    // [bbcode=xxxxxx] param                      [bbcode]xxxxxx[/bbode]        expected output
					    'http://mysite.com external'        => array('input'=>'http://mysite.com', 'expected'=>"<a class='bbcode bbcode-link' href='http://mysite.com' rel='external' >http://mysite.com</a>"),
						'http://mysite.com rel=external'    => array('input'=>'http://mysite.com', 'expected'=>"<a class='bbcode bbcode-link' href='http://mysite.com' rel='external' >http://mysite.com</a>"),
						'external'                          => array('input'=>'http://mysite.com', 'expected'=>"<a class='bbcode bbcode-link' href='http://mysite.com' rel='external' >http://mysite.com</a>"),
						'mailto:myemail@email.com'          => array('input'=>'My Name', 'expected'=>"<a class='bbcode' rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"myemail\"+\"@\"+\"email.com\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"myemail\"+\"@\"+\"email.com\"; return true;' onmouseout='window.status=\"\";return true;'>My Name</a>"),
						'external=http://mysite.com'        => array('input'=>'http://mysite.com', 'expected'=>"<a class='bbcode bbcode-link' href='http://mysite.com' rel='external' >http://mysite.com</a>"),
					  ),
					  'list' =>
					  array (
					  ),
					  'quote' =>
					  array (
					  ),
					  'right' =>
					  array (
					  ),
					  'sanitised' =>
					  array (
					  ),
					  'size' =>
					  array (
					  ),
					  'spoiler' =>
					  array (
					  ),
					  'stream' =>
					  array (
					  ),
					  'table' =>
					  array (
					  ),
					  'tbody' =>
					  array (
					  ),
					  'td' =>
					  array (
					  ),
					  'textarea' =>
					  array (
					  ),
					  'th' =>
					  array (
					  ),
					  'time' =>
					  array (
					  ),
					  'tr' =>
					  array (
					  ),
					  'u' =>
					  array (
					  ),
					  'url' =>
					  array (
					  ),
					);

			$ret = [];
			foreach($codes as $bbcode=>$var)
			{
				if(empty($var))
				{
					$input = '['.$bbcode.']http://mysite.com[/'.$bbcode.']';
					$result = $this->bb->parseBBCodes($input, true); // parsing to check for PHP errors.
				//	$this->assertNotEmpty($result, $input." was empty.");
					continue;
				}

				foreach($var as $parms=>$p)
				{
					$input2 = '['.$bbcode.'='.$parms.']'.$p['input'].'[/'.$bbcode.']';
					$result2 = $this->bb->parseBBCodes($input2);
					$this->assertEquals($p['expected'], $result2);
				}
			}


		}
/*
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
