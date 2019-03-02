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


		$src = "[center][img]{e_IMAGE}generic/blank_avatar.jpg[/img][/center]";
		
		$actual = $this->tp->toHTML($src,true);

		$expected = "<div class='bbcode-center' style='text-align:center'><img src='".e_HTTP."e107_images/generic/blank_avatar.jpg' width='' alt='Blank Avatar' title='Blank Avatar' class='img-rounded rounded bbcode bbcode-img'  /></div>";

		$this->assertEquals($expected, $actual, "BBcode parsing failed on [img]");


		}
/*
		public function testUstrpos()
		{

		}

		public function testThumbUrlDecode()
		{

		}
*/

		public function testParseTemplateWithEnabledCoreShortcodes()
		{
			$needle = '<ul class="nav navbar-nav nav-main">';
			$result = $this->tp->parseTemplate('{NAVIGATION}', true);
			$this->assertContains($needle, $result);
		}

		public function testParseTemplateWithDisabledCoreShortcodes()
		{
			$result = $this->tp->parseTemplate('{NAVIGATION}', false);
			$this->assertEmpty($result);
		}

		public function testParseTemplateWithCoreAddonShortcodes()
		{
			e107::getPlugin()->uninstall('online');
			e107::getScParser()->__construct();

			$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false);
			$this->assertEmpty($result);

			$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
			$this->assertEmpty($result);

			$shortcodeObject = e107::getScBatch('online', true);

			$expected = "<a href=''>lost</a>";
			$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false, $shortcodeObject);
			$this->assertEquals($expected, $result);

			$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false);
			$this->assertEmpty($result);

			$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
			$this->assertEquals($expected, $result);
		}

		public function testParseTemplateWithNonCoreShortcodes()
		{
			e107::getPlugin()->uninstall('download');
			e107::getScParser()->__construct();

			$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', false);
			$this->assertEmpty($result);

			$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', true);
			$this->assertEmpty($result);

			$shortcodeObject = e107::getScBatch('download', true);

			$needle = "<form class='form-search form-inline' ";
			$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', false, $shortcodeObject);
			$this->assertContains($needle, $result);

			$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', false);
			$this->assertEmpty($result);

			$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', true);
			$this->assertEmpty($result);
		}

/*
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

			e107::wysiwyg('default');
			e107::getConfig()->updatePref('wysiwyg', true);
			$actual = $this->tp->toForm($db);
			$expected = 'lr.src = window._lr.url %2B &#039;/Scripts/api.js&#039;;';
			$this->assertEquals($expected, $actual);

			e107::getConfig()->updatePref('wysiwyg', false);
			$actual = $this->tp->toForm($db);
			$expected = 'lr.src = window._lr.url + &#039;/Scripts/api.js&#039;;';
			$this->assertEquals($expected, $actual);
			
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
*/
		public function testToDB()
		{

			$tests = array(
				0  => array(
					'input'     => "<svg/onload=prompt(1)//",
					'expected'  => ''
				),
				1  => array(
					'input'     => "some plain text with a\nline break",
					'expected'  => "some plain text with a\nline break"
				),
				2  => array(
					'input'     => "some [b]text[/b] with bbcodes",
					'expected'  => "some [b]text[/b] with bbcodes"
				),
				3  => array(
					'input'     => 'some "quoted text" with a $ sign',
					'expected'  => "some &quot;quoted text&quot; with a &#036; sign"
				),
				4  => array(
					'input'     => 'some <div>simple html</div><a href="http://somewhere.com">link</a>',
					'expected'  => 'some <div>simple html</div><a href=&quot;http://somewhere.com&quot;>link</a>'
				),
				5  => array(
					'input'     => "[img]http://something.com[/img]",
					'expected'  => "[img]http://something.com[/img]"
				),
				6  => array(
					'input'     => "<p>日本語 简体中文</p>",
					'expected'  => "<p>日本語 简体中文</p>"
				),
				7  => array(
					'input'     => "<frameset onload=alert(1) data-something=where>",
					'expected'  => "" // stripped xss
				),
				8  => array(
					'input'     => '<table background="javascript:alert(1)"><tr><td><a href="something.php" onclick="alert(1)">Hi there</a></td></tr></table>',
					'expected'  => "<table><tr><td><a href=&quot;something.php&quot;>Hi there</a></td></tr></table>"
				),
				9  => array(
					'input'     => '<!--<img src="--><img src=x onerror=alert(1)//">',
					'expected'  => "<!--<img src=&quot;--><img src=&quot;x&quot;>"
				),
				10 => array(
					'input'     => '<div style=content:url(data:image/svg+xml,%3Csvg/%3E);visibility:hidden onload=alert(1)>',
					'expected'  => '<div style=&quot;#---sanitized---#&quot;></div>'),
				11 => array(
					'input'     => '<a href="{e_PLUGIN}myplugin/index.php">Test</a>',
					'expected'  => '<a href=&quot;{e_PLUGIN}myplugin/index.php&quot;>Test</a>'
				),
				12 => array(
					'input'     => "From here > to there",
					'expected'  => "From here &gt; to there"
				),
				13 => array(
					'input'     => "[html]<div style='text-align:center'>Hello World!</div>[/html]",
					'expected'  => '[html]<div style=&quot;text-align:center&quot;>Hello World!</div>[/html]'
				),
				14 => array(
					'input'     => "Something & something",
					'expected'  => 'Something &amp; something'
				),
				15 => array(
					'input'     => array('news_category', '2'),
					'expected'  => array('news_category', '2')
				),
				16 => array(
					'input'     => array('my/customer/key'=>'news_category', 3=>'2', 'bla'=>5),
					'expected'  => array('my/customer/key'=>'news_category', 3=>'2', 'bla'=>5),
				),
				17 => array(
					'input'     => array('Some long string & stuff'=> 0, 'other'=>null, 'extra'=>0.3, 'null'=>null),
					'expected'  => array('Some long string & stuff'=> 0, 'other'=>null, 'extra'=>0.3, 'null'=>null),
				),

			);

			foreach($tests as $var)
			{
				if(empty($var['input']))
				{
					continue;
				}

				$result = $this->tp->toDB($var['input']);
				$this->assertEquals($var['expected'], $result);
			//	var_dump($result);
			}




		}
/*
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
*/
		public function testReplaceConstants()
		{
			$actual = $this->tp->replaceConstants('{e_BASE}news','abs');

			$this->assertContains(e_HTTP,$actual);

			
		}
/*
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

			$this->tp->setFontAwesome(5);

			$result = $this->tp->toGlyph('fa-mailchimp');
			$expected = "<i class='fab fa-mailchimp' ><!-- --></i> ";
			$this->assertEquals($expected, $result);


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
*/


		public function testIsBBcode()
		{
			$tests = array(
				0   => array("My Simple Text", false), // input , expected result
				1   => array("<hr />", false),
				2   => array("[b]Bbcode[/b]", true),
				3   => array("<div class='something'>[code]something[/code]</div>", false),
				4   => array("[code]&lt;b&gt;someting&lt;/b&gt;[/code]", true),
				5   => array("[html]something[/html]", false),
				6   => array("http://something.com/index.php?what=ever", false)
			);


			foreach($tests as $val)
			{
				list($input, $expected) = $val;
				$actual = $this->tp->isBBcode($input);

				$this->assertEquals($expected, $actual, $input);
			}

		}

		public function testIsHtml()
		{
			$tests = array(
				0   => array("My Simple Text", false), // input , expected result
				1   => array("<hr />", true),
				2   => array("[b]Bbcode[/b]", false),
				3   => array("<div class='something'>[code]something[/code]</div>", true),
				4   => array("[code]&lt;b&gt;someting&lt;/b&gt;[/code]", false),
				5   => array("[html]something[/html]", true),
				6   => array("http://something.com/index.php?what=ever", false)
			);


			foreach($tests as $val)
			{
				list($input, $expected) = $val;
				$actual = $this->tp->isHtml($input);

				$this->assertEquals($expected, $actual, $input);
			}


		}
/*
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

			// links standard.
			$tests = array(
				array("before www.somewhere.com after",     'before <a class="e-url" href="http://www.somewhere.com" >www.somewhere.com</a> after'),
				array("before http://something.com after",  'before <a class="e-url" href="http://something.com" >http://something.com</a> after'),
				array("before https://someplace.com after", 'before <a class="e-url" href="https://someplace.com" >https://someplace.com</a> after'),
				array("before (www.something.com) after",   'before (<a class="e-url" href="http://www.something.com" >www.something.com</a>) after'),
			);

			foreach($tests as $row)
			{
				list($sample,$expected) = $row;
				$result = $tp->makeClickable($sample, 'url');
				$this->assertEquals($expected, $result);
			}

			// links with substituion..
			$tests = array(
				array("before www.somewhere.com after",     'before <a class="e-url" href="http://www.somewhere.com" >[link]</a> after'),
				array("before http://something.com after",  'before <a class="e-url" href="http://something.com" >[link]</a> after'),
				array("before https://someplace.com after", 'before <a class="e-url" href="https://someplace.com" >[link]</a> after'),
				array("before (www.something.com) after",   'before (<a class="e-url" href="http://www.something.com" >[link]</a>) after'),
			);

			foreach($tests as $row)
			{
				list($sample,$expected) = $row;
				$result = $tp->makeClickable($sample, 'url',array('sub' => '[link]'));
				$this->assertEquals($expected, $result);
			}

			// links with substituion and target.
			$tests = array(
				array("before www.somewhere.com after",     'before <a class="e-url" href="http://www.somewhere.com" target="_blank">[link]</a> after'),
				array("before http://something.com after",  'before <a class="e-url" href="http://something.com" target="_blank">[link]</a> after'),
				array("before https://someplace.com after", 'before <a class="e-url" href="https://someplace.com" target="_blank">[link]</a> after'),
				array("before (www.something.com) after",   'before (<a class="e-url" href="http://www.something.com" target="_blank">[link]</a>) after'),
			);

			foreach($tests as $row)
			{
				list($sample,$expected) = $row;
				$result = $tp->makeClickable($sample, 'url',array('sub' => '[link]', 'ext'=>true));
				$this->assertEquals($expected, $result);
			}


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
*/
		public function testCleanHtml()
		{
			$tests = array(
				0   => array('html' => "<svg/onload=prompt(1)//", 'expected' => ''),


			);

			foreach($tests as $var)
			{
				$result = $this->tp->cleanHtml($var['html']);
				var_dump($result);
			}


		}
/*
		public function testSecureAttributeValue()
		{

		}

		public function testInvalidAttributeValue()
		{

		}
*/
	}
