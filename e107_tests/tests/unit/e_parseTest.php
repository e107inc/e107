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

            $this->tp->__construct();
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


		$src = <<<SRC
[center]<script type=&quot;text/javascript&quot;><!--
google_ad_client = &quot;12345678&quot;;
/* vertical */
google_ad_slot = &quot;12345&quot;;
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type=&quot;text/javascript&quot;
src=&quot;http://pagead2.googlesyndication.com/pagead/show_ads.js&quot;>
</script>[/center]
SRC;

        $expected = <<<EXPECTED
<div style='text-align:center'><script type="text/javascript"><!--
google_ad_client = "12345678";
/* vertical */
google_ad_slot = "12345";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script></div>
EXPECTED;

                $actual = $this->tp->toHTML($src,true);
                $this->assertEquals($expected,$actual);

/*
$src = "[html]
<pre>&#036;sql = e107::getDb();
&#036;sql-&gt;select(&#039;tablename&#039;, &#039;field1, field2&#039;, &#039;field_id = 1&#039;);
while(&#036;row = &#036;sql-&gt;fetch())
&#123;
    echo &#036;row[&#039;field1&#039;];
&#125;</pre>
[/html]";

    $actual = $this->tp->toHTML($src,true);
    $expected = '';

        $this->assertEquals($expected, $actual, "BBcode parsing failed on <pre>");*/


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
			$needle = '<ul class="nav navbar-nav nav-main ml-auto">';
			$result = $this->tp->parseTemplate('{NAVIGATION}', true);
			$this->assertStringContainsString($needle, $result);
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
			$this->assertStringContainsString($needle, $result);

			$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', false);
			$this->assertEmpty($result);

			$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', true);
			$this->assertEmpty($result);
		}

		public function testParseTemplateWithEvars()
		{
			$obj = new e_vars(array('ACTIVE' => "yes"));
			$result = $this->tp->parseTemplate('<div>something {ACTIVE}</div>', true, null, $obj);
			$expected = '<div>something yes</div>';

			$this->assertEquals($expected, $result);

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

			e107::getConfig()->updatePref('wysiwyg', true);
			e107::wysiwyg('default');
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
*/
		public function testToASCII()
		{

			$array = array(
				array('input' => 'ľ, ú, ŕ, ô, ť', 'expected' => 'l, u, r, o, t'),
			);

			foreach($array as $arr)
			{
				$result = $this->tp->toASCII($arr['input']);
				$this->assertEquals($arr['expected'], $result);
			}



		}
/*
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
*/


		public function testToDB()
		{

			$tests = array(
				0  => array(
					'input'     => "<svg/onload=prompt(1)//",
					'expected'  => '&lt;svg/onload=prompt(1)//'
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
					'expected'  => '<table><tr><td><a href=&quot;something.php&quot; onclick=&quot;#---sanitized---#&quot;>Hi there</a></td></tr></table>'
				),
				9  => array(
					'input'     => '<!--<img src="--><img src=x onerror=alert(1)//">',
					'expected'  => "<!--<img src=&quot;--><img src=&quot;x&quot;>"
				),
				10 => array(
					'input'     => '<div style=content:url(data:image/svg+xml,%3Csvg/%3E);visibility:hidden onload=alert(1)>',
					'expected'  => '<div style=&quot;#---sanitized---#&quot; onload=&quot;#---sanitized---#&quot;></div>'),
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
					'input'     => array('news_category', '2', '0'),
					'expected'  => array('news_category', '2', '0')
				),
				16 => array(
					'input'     => array('my/customer/key'=>'news_category', 3=>'2', 'bla'=>5, 'true'=>true, 'false'=>false, 'empty'=>''),
					'expected'  => array('my/customer/key'=>'news_category', 3=>'2', 'bla'=>5, 'true'=>true, 'false'=>false, 'empty'=>''),
				),
				17 => array(
					'input'     => array('Some long string & stuff'=> 0, 'other'=>null, 'extra'=>0.3, 'null'=>null),
					'expected'  => array('Some long string & stuff'=> 0, 'other'=>null, 'extra'=>0.3, 'null'=>null),
				),
			/*	18 => array(
					'input'     => '"><script>alert(123)</script>',
					'expected'  => '',
					'mode'      => 'model',
					'parm'      => array('type'=>'text', 'field'=>'news_title')
				),*/
				19  => array( // admin log simulation
					'input'     => "Array[!br!]([!br!]    [0] => zero[!br!]    [1] => one[!br!]    [2] => two[!br!])[!br!]",
					'expected'  => "Array[!br!]([!br!]    [0] =&gt; zero[!br!]    [1] =&gt; one[!br!]    [2] =&gt; two[!br!])[!br!]",
					'mode'      => 'no_html',
				),
				20  => array(
					'input'     => '\\',
					'expected'  => '&#092;',
					'mode'      => 'no_html',
				),
				21 => array(
					'input'     => '<a href="">Hello</a>',
					'expected'  => '&lt;a href=&quot;&quot;&gt;Hello&lt;/a&gt;',
					'mode'      => 'no_html',
				),
				22 => array(
					'input'     => '< 200',
					'expected'  => '&lt; 200',
				),
				23 => array(
					'input'     => '[html]<pre>echo {e_BASE}."index.php";</pre>[/html]',
					'expected'  => '[html]<pre>echo &#123;e_BASE&#125;.&quot;index.php&quot;;</pre>[/html]'
				),
				24 => array(
					'input'     => '[html]<code>echo {e_BASE}."index.php";</code>[/html]',
					'expected'  => '[html]<code>echo &#123;e_BASE&#125;.&quot;index.php&quot;;</code>[/html]'
				),
				25 => array(
					'input'     => '[html]<img src="{e_BASE}image.jpg" alt="">[/html]',
					'expected'  => '[html]<img src=&quot;{e_BASE}image.jpg&quot; alt=&quot;&quot;>[/html]'
				),
				26 => array(
				    'input'     => "[html]<code>function sc_my_shortcode(){\nreturn \"Something\";}</code>[/html]",
				    'expected'  => "[html]<code>function sc_my_shortcode()&#123;\nreturn &quot;Something&quot;;&#125;</code>[/html]"
                ),
                27 => array(
                    'input'     =>"[html]<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>",
                    'expected'  =>"[html]<pre class=&quot;whatever&quot;>require_once(&quot;class2.php&quot;);\nrequire_once(HEADERF);\necho &quot;test&quot;;&lt;br&gt;\nrequire_once(FOOTERF);</pre>",

                ),
                28 => array(
                    'html'      => "<pre>{THEME_PREF: code=header_width&default=container}</pre>",
                    'expected'  => "<pre>&#123;THEME_PREF: code=header_width&amp;default=container&#125;</pre>",
                ),

                29 => array(
                    'html'      => "<pre>/* {THEME_PREF: code=header_width&default=container} */</pre>",
                    'expected'  => "<pre>/* &#123;THEME_PREF: code=header_width&amp;default=container&#125; */</pre>",
                ),

			);

			foreach($tests as $k=>$var)
			{
				if(empty($var['input']))
				{
					continue;
				}

				$mode = varset($var['mode']);
				$parm = varset($var['parm']);

				$result = $this->tp->toDB($var['input'], false, false, $mode, $parm);
				$this->assertEquals($var['expected'], $result, 'Test #'.$k." failed.". print_r($this->tp->getRemoved(),true));

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

			$this->assertStringContainsString(e_HTTP,$actual);

			
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

				$this->assertStringContainsString($val['expected'], $actual);
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
*/
		public function testToText()
		{
			$arr = array(
				0   => array('html'=>"<h1><a href='#'>My Caption</a></h1>", 'expected' => 'My Caption'),
				1   => array('html'=>"<div><h1><a href='#'>My Caption</a></h1></div>", 'expected' => 'My Caption'),
				2   => array('html'=>'Line 1<br />Line 2<br />Line 3<br />', 'expected'=> "Line 1\nLine 2\nLine 3\n"),
				3   => array('html'=>"Line 1<br />\nLine 2<br />\nLine 3<br />", 'expected'=> "Line 1\nLine 2\nLine 3\n"),
			);


			foreach($arr as $var)
			{
				$result = $this->tp->toText($var['html']);
				$this->assertEquals($var['expected'],$result);
			}

		}
/*
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
*/
		public function testSetScriptAccess()
		{
            $this->tp->setScriptAccess(e_UC_PUBLIC);
            $result = $this->tp->getScriptAccess();
            $this->assertEquals(e_UC_PUBLIC, $result);
		}
/*
		public function testGetAllowedTags()
		{

		}
*/
		public function testGetScriptAccess()
		{
            $result = $this->tp->getScriptAccess();
            $this->assertFalse($result);
		}

		public function testGetAllowedAttributes()
		{
            $result = $this->tp->getAllowedAttributes();

            $true = is_array($result) && in_array('style',$result['img']);

            $this->assertTrue($true);
		}
/*
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
*/
		public function testToAvatar()
		{
			$icon = codecept_data_dir()."icon_64.png";

			if(!is_dir(e_AVATAR_UPLOAD))
			{
				mkdir(e_AVATAR_UPLOAD,0755, true);
			}

			if(!is_dir(e_AVATAR_DEFAULT))
			{
				mkdir(e_AVATAR_DEFAULT,0755, true);
			}

			if(!copy($icon, e_AVATAR_UPLOAD."avatartest.png"))
			{
				echo "Couldn't copy the avatar";
			}
			if(!copy($icon, e_AVATAR_DEFAULT."avatartest.png"))
			{
				echo "Couldn't copy the avatar";
			}

			$tests = array(
				0   => array(
					'input'     => array('user_image'=>'-upload-avatartest.png'),
					'parms'     => array('w'=>50, 'h'=>50),
					'expected'  => array(
									"thumb.php?src=%7Be_AVATAR%7Dupload%2Favatartest.png&amp;w=50&amp;h=50",
									"class='img-rounded rounded user-avatar'"
								)
				),
				1   => array(
					'input'     => array('user_image'=>'avatartest.png'),
					'parms'     => array('w'=>50, 'h'=>50),
					'expected'  => array(
									"thumb.php?src=%7Be_AVATAR%7Ddefault%2Favatartest.png&amp;w=50&amp;h=50",
									"class='img-rounded rounded user-avatar'"
								)
				),
				2   => array(
					'input'     => array('user_image'=>''),
					'parms'     => array('w'=>50, 'h'=>50),
					'expected'  => array(
									"thumb.php?src=%7Be_IMAGE%7Dgeneric%2Fblank_avatar.jpg&amp;w=50&amp;h=50",
									"class='img-rounded rounded user-avatar'"
								)
				),
				3   => array(
					'input'     => array('user_image'=>'https://mydomain.com/remoteavatar.jpg'),
					'parms'     => array('w'=>50, 'h'=>50),
					'expected'  => array(
									"src='https://mydomain.com/remoteavatar.jpg'",
									"class='img-rounded rounded user-avatar'",
									"width='50' height='50'",
								)
				),
				4   => array(
					'input'     => array('user_image'=>'', 'user_id'=>1),
					'parms'     => array('w'=>50, 'h'=>50, 'link'=>true),
					'expected'  => array(
									"thumb.php?src=%7Be_IMAGE%7Dgeneric%2Fblank_avatar.jpg&amp;w=50&amp;h=50",
									"class='img-rounded rounded user-avatar'",
									"<a class='e-tip' title=",
									"usersettings.php"
								)
				),
				5   => array(
					'input'     => array('user_image'=>'avatartest.png'),
					'parms'     => array('w'=>30, 'h'=>20, 'crop'=>true, 'shape'=>'rounded'),
					'expected'  => array(
									"thumb.php?src=%7Be_AVATAR%7Ddefault%2Favatartest.png&amp;aw=30&amp;ah=20",
									"class='img-rounded user-avatar'"
								)
				),
				6   => array(
					'input'     => array('user_image'=>'avatartest.png'),
					'parms'     => array('w'=>30, 'h'=>30, 'shape'=>'circle', 'alt'=>'mytitle'),
					'expected'  => array(
									"thumb.php?src=%7Be_AVATAR%7Ddefault%2Favatartest.png&amp;w=30&amp;h=30",
									"class='img-circle user-avatar'",
									'alt="mytitle"',
								)
				),
				/** @fixme - doesn't pass under CLI  */
			/*	6   => array(
					'input'     => array('user_image'=>'avatartest.png'),
					'parms'     => array('w'=>50, 'h'=>50, 'crop'=>true, 'base64'=>true, 'shape'=>'circle'),
					'expected'  => array(
									"src='data:image/png;base64,",
									"class='img-circle user-avatar'"
								)
				),*/


			);

			foreach($tests as $var)
			{
				$result = $this->tp->toAvatar($var['input'], $var['parms']);
				foreach($var['expected'] as $str)
				{
					$this->assertStringContainsString($str, $result);
				}
				//var_dump($result);

			}


		}

		public function testToIcon()
		{
			$icon = codecept_data_dir()."icon_64.png";

			if(!copy($icon,e_MEDIA_IMAGE."icon_64.png"))
			{
				echo "Couldn't copy the icon";
			}
			if(!copy($icon,e_MEDIA_ICON."icon_64.png"))
			{
				echo "Couldn't copy the icon";
			}

			$tests = array(
				0   => array('input'=> '{e_IMAGE}e107_icon_32.png',    'parms'=>null,             'expected'  => '/e107_images/e107_icon_32.png'),
				1   => array('input'=> '{e_MEDIA_IMAGE}icon_64.png',   'parms'=>null,             'expected'  => 'thumb.php?src=e_MEDIA_IMAGE'),
				2   => array('input'=> '{e_MEDIA_ICON}icon_64.png',     'parms'=>null,            'expected'  => '/e107_media/000000test/icons/icon_64.png'),
				3   => array('input'=> '{e_PLUGIN}gallery/images/gallery_32.png',  'parms'=>null, 'expected'  => '/e107_plugins/gallery/images/gallery_32.png'),
				4   => array('input'=> 'config_16.png', 'parms'=>array('legacy'=> "{e_IMAGE}icons/"), 'expected' => '/e107_images/icons/config_16.png'),
			);

			foreach($tests as $var)
			{
				$result = $this->tp->toIcon($var['input'],$var['parms']);
				$this->assertStringContainsString($var['expected'],$result);
				//var_dump($result);
			}
		}

		public function testToImage()
		{
			$src = "{e_PLUGIN}gallery/images/butterfly.jpg";
			$this->tp->setThumbSize(80,80); // set defaults.

			// test with defaults set above.
			$result = $this->tp->toImage($src);
			$this->assertStringContainsString('butterfly.jpg&amp;w=80&amp;h=80', $result); // src
			$this->assertStringContainsString('butterfly.jpg&amp;w=320&amp;h=320', $result); // srcset 4x the size on small images.

			// test overriding of defaults.
			$override = array('w'=>800, 'h'=>0);
			$result2 = $this->tp->toImage($src, $override);
			$this->assertStringContainsString('butterfly.jpg&amp;w=800&amp;h=0', $result2); // src
			$this->assertStringContainsString('Fbutterfly.jpg&amp;w=1600&amp;h=0', $result2); // srcset


			$override = array('w'=>0, 'h'=>0); // display image without resizing
			$result3 = $this->tp->toImage($src, $override);
			$this->assertStringContainsString('Fbutterfly.jpg&amp;w=0&amp;h=0', $result3); // src

			$result4 = $this->tp->toImage($src, ['loading'=>'lazy']);
			$this->assertStringContainsString('loading="lazy"', $result4); // src

		}

		public function testThumbSrcSet()
		{
			$src = "{e_PLUGIN}gallery/images/butterfly.jpg";
			$parms = array('w'=>800, 'h'=>0, 'size'=>'2x');

			$result = $this->tp->thumbSrcSet($src, $parms);
			$this->assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result);

			$this->tp->setThumbSize(80,80); // set defaults.

			$result2 = $this->tp->thumbSrcSet($src, $parms); // testing overrides
			$this->assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result2);

			$result3 = $this->tp->thumbSrcSet($src, array('w'=>800, 'size'=>'2x')); // testing overrides without 'h' being set.
			$this->assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result3);

			$result4 = $this->tp->thumbSrcSet($src); // no overrides
			$this->assertStringContainsString('butterfly.jpg&amp;w=160&amp;h=160', $result4);

		}

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
				6   => array("http://something.com/index.php?what=ever", false),
				7   => array("< 200", false),
				8   => array("<200>", true),
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

			$this->assertStringContainsString('[email]</a>', $result);

			// -----

			$result = $tp->makeClickable($email, 'email', array('sub' => 'fa-envelope.glyph'));
			$this->assertStringContainsString("<i class='fa fa-envelope' ><!-- --></i></a>", $result);

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
			$this->assertStringContainsString('Saturday 24 February 2018',$long);

			$short = $class->toDate($time, 'short');
			$this->assertStringContainsString('Feb 2018', $short);

			$rel = $class->toDate($time, 'relative');
			$this->assertStringContainsString('ago', $rel);
			$this->assertStringContainsString('data-livestamp="1519512067"', $rel);

			$custom = $class->toDate($time, 'dd-M-yy');
			$this->assertStringContainsString('<span>24-Feb-18</span>', $custom);



		}
/*
		public function testParseBBTags()
		{

		}
*/
		public function testFilter()
		{

			$tests = array(
				0   => array('input' => 'test123 xxx',      'mode' => 'w',        'expected' => 'test123xxx'),
				1   => array('input' => 'test123 xxx',      'mode' => 'd',        'expected' => '123'),
				2   => array('input' => 'test123 xxx',      'mode' => 'wd',       'expected' => 'test123xxx'),
				3   => array('input' => 'test123 xxx',      'mode' => 'wds',      'expected' => 'test123 xxx'),
				4   => array('input' => 'test123 xxx.jpg',  'mode' => 'file',     'expected' => 'test123-xxx.jpg'),
				5   => array('input' => '2.1.4 (test)',     'mode' => 'version',  'expected' => '2.1.4'),
			);

			foreach($tests as $var)
			{
				$result = $this->tp->filter($var['input'],$var['mode']);
				$this->assertEquals($var['expected'],$result);
			}


		}

		public function testCleanHtml()
		{
		    global $_E107;
			$_E107['phpunit'] = true; // disable CLI "all access" permissions to simulated a non-cli scenario.

		    $this->tp->setScriptAccess(e_UC_NOBODY);

			$tests = array(
				0   => array(
				    'html' => "<svg/onload=prompt(1)//",
				    'expected' => '&lt;svg/onload=prompt(1)//'
                ),
			//	1   => array('html' => '<script>alert(123)</script>', 'expected'=>''),
			//	2   => array('html' => '"><script>alert(123)</script>', 'expected'=>'"&gt;'),
				3   => array(
				    'html' => '< 200',
				    'expected'=>'&lt; 200'
                ),
				4   => array(
				    'html' => "<code>function sc_my_shortcode(){\nreturn \"Something\";}</code>",
				    'expected' =>  "<code>function sc_my_shortcode()&#123;\nreturn \"Something\";&#125;</code>"
                ),
               	5   => array(
               	    'html' => "<pre class=\"prettyprint linenums\">function sc_my_shortcode(){\nreturn \"Something\";}</pre>",
               	    'expected' => "<pre class=\"prettyprint linenums\">function sc_my_shortcode()&#123;\nreturn \"Something\";&#125;</pre>"
                ),
                6   => array(
                    'html'      => '<img src="{e_BASE}image.jpg" alt="">',
                    'expected'  =>'<img src="{e_BASE}image.jpg" alt="">'
                ),
                7 => array( // with <br> inside <pre> ie. TinyMce
                    'html'      => '<pre class="whatever">require_once("class2.php");<br>require_once(HEADERF);<br>echo "test";&lt;br&gt;<br>require_once(FOOTERF);</pre>',
                    'expected'  => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>"
                ),
                8 => array( // with \n
                    'html'      => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>",
                    'expected'  => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>"
                ),
                9 => array( // with \r\n (windows) line-breaks.
                    'html'      => "<pre class=\"whatever\">require_once(\"class2.php\");\r\nrequire_once(HEADERF);\r\necho \"test\";&lt;br&gt;\r\nrequire_once(FOOTERF);</pre>",
                    'expected'  => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>"
                ),
                10   => array(
			        'html'=>    '<a href="#" onchange="whatever">Test</a>',
			        'expected'=>'<a href="#">Test</a>'
                ),

                11 => array(
                    'html'      => "<pre>{THEME_PREF: code=header_width&default=container}</pre>",
                    'expected'  => "<pre>&#123;THEME_PREF: code=header_width&amp;default=container&#125;</pre>",
                ),

                12 => array(
                    'html'      => "<pre>/* {THEME_PREF: code=header_width&default=container} */</pre>",
                    'expected'  => "<pre>/* &#123;THEME_PREF: code=header_width&amp;default=container&#125; */</pre>",
                ),

                13 => array(
                    'html'      => '<div class="video-responsive"><div class="video-responsive"><video width="320" height="240" controls="controls"><source src="e107_media/xxxxx5/videos/2018-07/SampleVideo.mp4" type="video/mp4">Your browser does not support the video tag.</video></div></div>',
                    'expected'  => '<div class="video-responsive"><div class="video-responsive"><video width="320" height="240" controls="controls"><source src="e107_media/xxxxx5/videos/2018-07/SampleVideo.mp4" type="video/mp4">Your browser does not support the video tag.</source></video></div></div>'
                ),
				14 => array(
                    'html'      => '<script>alert(1)</script>', // test removal of 'script' tags
                    'expected'  => ''
                )


			);


			foreach($tests as $var)
			{
				$result = $this->tp->cleanHtml($var['html']);
				$this->assertEquals($var['expected'], $result);
			}

            // ----------- Test with Script access enabled --------------


			$this->tp->setScriptAccess(e_UC_PUBLIC);

			$scriptAccess = array(
			    0   => array(
			        'html'      => '<a href="#" onchange="whatever">Test</a>',
			        'expected'  => '<a href="#" onchange="whatever">Test</a>'
                ),
                1 => array(
                    'html'      => '<script>alert(1)</script>', // test support for 'script' tags
                    'expected'  => '<script>alert(1)</script>'
                )
            );

			foreach($scriptAccess as $var)
			{
				$result = $this->tp->cleanHtml($var['html']);
				$this->assertEquals($var['expected'], $result);
			}

            $this->tp->setScriptAccess(false);
            unset($_E107['phpunit']);

		}


/*
		public function testSecureAttributeValue()
		{

		}

		public function testInvalidAttributeValue()
		{

		}
*/
/*
        public function testGrantScriptAccess()
        {
            $before = $this->tp->getAllowedAttributes();

            $this->tp->grantScriptAccess();

            $after = $this->tp->getAllowedAttributes();


        }*/
	}
