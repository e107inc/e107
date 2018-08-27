<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_parseTest extends \Codeception\Test\Unit
	{
		/** @var e_parse  */
		private $tp;

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

		}
/*
		public function testHtmlAbuseFilter()
		{

		}

		public function testE_highlight()
		{

		}*/

		public function testToHTML()
		{
			$src = <<<TMP
[center]centered text[/center]

[color=#00ff00][size=22]Colored text[/size][/color]

[link=http://e107.org]Linked Text[/link]

[size=22]Sized Text[/size]

TMP;

		$expected = "<div class='bbcode-center' style='text-align:center'>centered text</div><br /><span class='bbcode-color' style='color:#00ff00;'><span class='bbcode-size' style='font-size:22px'>Colored text</span></span><br /><br /><a class='bbcode bbcode-link' href='http://e107.org' rel='external' >Linked Text</a><br /><br /><span class='bbcode-size' style='font-size:22px'>Sized Text</span><br />";

		$actual = $this->tp->toHTML($src,true);

		$this->assertEquals($expected,$actual, "BBcode parsing failed");



		}
/*
		public function testUstrpos()
		{

		}

		public function testThumbUrlDecode()
		{

		}

		public function testParseTemplate()
		{

		}

		public function testCreateConstants()
		{

		}

		public function testThumbEncode()
		{

		}

		public function testEmailObfuscate()
		{

		}
*/
		public function testToForm()
		{

			$orig = "lr.src = window._lr.url + '/Scripts/api.js';";

			$db = $this->tp->toDB($orig);

			$actual = $this->tp->toForm($db);

			$this->assertEquals($orig, $actual);
			
		}
/*
		public function testUstristr()
		{

		}

		public function testThumbDimensions()
		{

		}

		public function testToASCII()
		{

		}

		public function testToNumber()
		{

		}

		public function testTextclean()
		{

		}

		public function testUstrtoupper()
		{

		}

		public function testUstrlen()
		{

		}

		public function testAmpEncode()
		{

		}

		public function testThumbUrlScale()
		{

		}

		public function testToEmail()
		{

		}

		public function testUsubstr()
		{

		}

		public function testThumbCrop()
		{

		}

		public function testThumbSrcSet()
		{

		}

		public function testToDB()
		{

		}

		public function testHtml_truncate_old()
		{

		}

		public function testToJSONhelper()
		{

		}

		public function testToJSON()
		{

		}

		public function testPost_toForm()
		{

		}

		public function testHtml_truncate()
		{

		}

		public function testCheckHighlighting()
		{

		}

		public function testThumbWidth()
		{

		}

		public function testReplaceConstants()
		{

		}

		public function testHtmlwrap()
		{

		}

		public function testToRss()
		{

		}

		public function testPreFilter()
		{

		}
*/
		public function testThumbUrl()
		{
			$urls = array(
				array('path'    => '{e_PLUGIN}gallery/images/butterfly.jpg', 'expected'=>'/thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&amp;w=300&amp;h=200'),
				array('path'    => '{e_PLUGIN}dummy/Freesample.svg', 'expected'=>'/e107_plugins/dummy/Freesample.svg'),
			);

			foreach($urls as $val)
			{

				$actual = $this->tp->thumbUrl($val['path'], array('w'=>300, 'h'=>200));

				$this->assertContains($val['expected'], $actual);
				//echo $$actual."\n\n";
			}


		}
/*
		public function testParseBBCodes()
		{

		}

		public function testGetEmotes()
		{

		}

		public function testThumbHeight()
		{

		}

		public function testDataFilter()
		{

		}

		public function testToAttribute()
		{

		}

		public function testThumbCacheFile()
		{

		}

		public function testText_truncate()
		{

		}

		public function testMakeClickable()
		{

		}

		public function testSetThumbSize()
		{

		}

		public function testToJS()
		{

		}

		public function testSimpleParse()
		{

		}

		public function testToText()
		{

		}

		public function testUstrtolower()
		{

		}

		public function testObfuscate()
		{

		}

		public function testDoReplace()
		{

		}

		public function testStaticUrl()
		{

		}

		public function testGetUrlConstants()
		{

		}

		public function testUstrrpos()
		{

		}

		public function testPost_toHTML()
		{

		}*/

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
			$result = $this->tp->toGlyph('fa-envelope.glyph');

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


			$class = $this->tp;

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
