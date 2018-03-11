<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_parserTest extends \Codeception\Test\Unit
	{
		protected $tp;
		protected $parser;

		protected function _before()
		{
			try
			{
				$this->tp = $this->make('e_parse');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_parser object");
			}

			try
			{
				$this->parser = $this->make('e_parser');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e_parser object");
			}

		}

/*
		public function testAddAllowedTag()
		{

		}

		public function testAddAllowedAttribute()
		{

		}

		public function testSetAllowedTags()
		{

		}

		public function testSetScriptAccess()
		{

		}

		public function testGetAllowedTags()
		{

		}

		public function testGetScriptAccess()
		{

		}

		public function testSetAllowedAttributes()
		{

		}

		public function testSetScriptTags()
		{

		}

		public function testLeadingZeros()
		{

		}

		public function testLanVars()
		{

		}

		public function testGetTags()
		{

		}
*/
		public function testToGlyph()
		{
			$tp = $this->parser;

			$result = $tp->toGlyph('fa-envelope.glyph');

			$expected = "<i class='fa fa-envelope' ><!-- --></i> ";

			$this->assertEquals($expected,$result);

		}
/*
		public function testToBadge()
		{

		}

		public function testToLabel()
		{

		}

		public function testToFile()
		{

		}

		public function testToAvatar()
		{

		}

		public function testToIcon()
		{

		}

		public function testToImage()
		{

		}

		public function testIsBBcode()
		{

		}

		public function testIsHtml()
		{

		}

		public function testIsJSON()
		{

		}

		public function testIsUTF8()
		{

		}

		public function testIsVideo()
		{

		}

		public function testIsImage()
		{

		}

		public function testToVideo()
		{

		}*/

		public function testMakeClickable()
		{
			$email = 'myemail@somewhere.com.tk';

			$tp = $this->tp;

			// ----

			$result = $tp->makeClickable($email, 'email', array('sub' => '[email]'));

			$this->assertContains('[email]</a>', $result);

			// -----

			$result = $tp->makeClickable($email, 'email', array('sub' => 'fa-envelope.glyph'));

			$this->assertContains("<i class='fa fa-envelope' ><!-- --></i></a>", $result);

			// -----
		}

		public function testToDate()
		{


			$class = $this->parser;

			$time = 1519512067; //  Saturday 24 February 2018 - 22:41:07

			$long = $class->toDate($time, 'long');
			$this->assertContains('Saturday 24 February 2018',$long);

			$short = $class->toDate($time, 'short');
			$this->assertContains('Feb 2018', $short);

			$rel = $class->toDate($time, 'relative');
			$this->assertContains('ago', $rel);
			$this->assertContains('data-livestamp="1519512067"', $rel);

			$custom = $class->toDate($time, 'dd-M-yy');
			$this->assertContains('<span>24-Feb-18</span>', $custom);



		}
/*
		public function testParseBBTags()
		{

		}

		public function testFilter()
		{

		}

		public function testCleanHtml()
		{

		}

		public function testSecureAttributeValue()
		{

		}

		public function testInvalidAttributeValue()
		{

		}
*/
	}
