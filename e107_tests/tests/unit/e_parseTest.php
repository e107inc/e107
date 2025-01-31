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
	/** @var e_parse */
	private $tp;

	protected function _before()
	{
		try
		{
			$this->tp = $this->make('e_parse');
		}
		catch (Exception $e)
		{
			self::assertTrue(false, "Couldn't load e_parser object");
		}

		$this->tp->__construct();
	}

	public function testInit()
	{
		$this->tp->init();
	}

	public function testToRoute()
	{

		$posted = array(
			'myfield'  => array(
				'computer' => array(
					'apple' => array('imac' => '1')
				),
				'os'       => array(
					'microsoft' => array('windows' => 'xp')
				)
			),
			'myfield2' => array(
				'house' => array('car' => 'red')
			),
			'myfield3' => 'string',

		);

		$expected = array(
			'myfield/computer/apple/imac'  => 'myfield',
			'myfield/os/microsoft/windows' => 'myfield',
			'myfield2/house/car'           => 'myfield2',
			'myfield3'                     => 'myfield3',
		);

		$result = $this->tp->toRoute($posted);
		self::assertSame($expected, $result);

	}

	public function testSetGetImageAltCacheFile()
    {
        $path = '{e_THEME}basic/screenshot.png';
        $value = 'Test Alt Text';

        // Call setImageAltCacheFile to generate the cache file
        $this->tp->setImageAltCacheFile($path, $value);


        $retrievedValue = $this->tp->getImageAltCacheFile($path);
        self::assertSame($value, $retrievedValue, "Retrieved value does not match the expected value");

    }



	public function testStripBlockTags()
	{
		$tests = array(
			0 => array(
				'text'     => '<p>Paragraph 1</p><p><b>Paragraph 2<br >Line 3</b></p>',
				'expected' => "Paragraph 1<b>Paragraph 2<br >Line 3</b>",
			),


		);

		foreach ($tests as $var)
		{
			$result = $this->tp->stripBlockTags($var['text']);

			if (empty($var['expected']))
			{
				echo $result . "\n\n";
				continue;
			}

			self::assertEquals($var['expected'], $result);
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

		$actual = $this->tp->toHTML($src, true);

		self::assertEquals($expected, $actual, "BBcode parsing failed");


		$src = "[center][img]{e_IMAGE}generic/blank_avatar.jpg[/img][/center]";

		$actual = $this->tp->toHTML($src, true);

		$expected = "<div class='bbcode-center' style='text-align:center'><img src='" . e_HTTP . "e107_images/generic/blank_avatar.jpg' alt='Blank Avatar' title='Blank Avatar' class='img-rounded rounded bbcode bbcode-img'  /></div>";

		self::assertEquals($expected, $actual, "BBcode parsing failed on [img]");


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

		$actual = $this->tp->toHTML($src, true);
		self::assertEquals($expected, $actual);

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

				self::assertEquals($expected, $actual, "BBcode parsing failed on <pre>");*/


	}

	/*
			public function testUstrpos()
			{

			}
	*/
	public function testThumbUrlDecode()
	{
		$tests = array(
			0 => array(
				'input'    => '/media/img/a400xa500/myimage.jpg',
				'expected' => array(
					'src' => 'e_MEDIA_IMAGE/myimage.jpg',
					'aw'  => '400',
					'ah'  => '500',
				)
			),
			1 => array(
				'input'    => '/media/img/400x500/myimage2.jpg',
				'expected' => array(
					'src' => 'e_MEDIA_IMAGE/myimage2.jpg',
					'w'   => '400',
					'h'   => '500',
				)
			),
			2 => array(
				'input'    => '/theme/img/a400xa500/mytheme/myimage.jpg',
				'expected' => array(
					'src' => 'e_THEME/mytheme/myimage.jpg',
					'aw'  => '400',
					'ah'  => '500',
				)
			),
			3 => array(
				'input'    => '/theme/img/400x500/mytheme/myimage2.jpg',
				'expected' => array(
					'src' => 'e_THEME/mytheme/myimage2.jpg',
					'w'   => '400',
					'h'   => '500',
				)
			),

		);

		foreach ($tests as $var)
		{
			$result = $this->tp->thumbUrlDecode($var['input']);
			self::assertSame($var['expected'], $result);
		}


	}


	function testToHTMLModifiers()
	{
		//	e107::getConfig()->set('make_clickable', 0)->save(false, true);

		$list = $this->tp->getModifierList();

		$tests = array(
			'emotes_off'        =>
				array(
					'input'    => ":-)",
					'expected' => ':-)',
				),
			'emotes_on'         =>
				array(
					'input'    => ":-)",
					'expected' => '<img class=\'e-emoticon\' src=\'https://localhost/e107/e107_images/emotes/default/smile.png\' alt="smile"  />',
				),
			'no_hook'           =>
				array(
					'input'    => "",
					'expected' => '',
				),
			'do_hook'           =>
				array(
					'input'    => "",
					'expected' => '',
				),
			'scripts_off'       =>
				array(
					'input'    => "",
					'expected' => '',
				),
			'scripts_on'        =>
				array(
					'input'    => "",
					'expected' => '',
				),
			'no_make_clickable' =>
				array(
					'input'    => "www.somewhere.com mailto:myemail@somewhere.com",
					'expected' => 'www.somewhere.com mailto:myemail@somewhere.com',
				),
			'make_clickable'    =>
				array(
					'input'    => "www.somewhere.com mailto:myemail@somewhere.com",
					'expected' => '', // random obfiscation
				),
			'no_replace'        =>
				array(
					'input'    => "www.somewhere.com",
					'expected' => '',
				),
			'replace'           =>
				array(
					'input'    => "www.somewhere.com",
					'expected' => '',
				),
			'consts_off'        =>
				array(
					'input'    => "{e_PLUGIN}",
					'expected' => '{e_PLUGIN}',
				),
			'consts_rel'        =>
				array(
					'input'    => "{e_PLUGIN}",
					'expected' => 'e107_plugins/',
				),
			'consts_abs'        =>
				array(
					'input'    => "{e_PLUGIN}",
					'expected' => '/e107_plugins/',
				),
			'consts_full'       =>
				array(
					'input'    => "{e_PLUGIN}",
					'expected' => 'https://localhost/e107/e107_plugins/',
				),
			'scparse_off'       =>
				array(
					'input'    => "{SITENAME}",
					'expected' => '{SITENAME}',
				),
			'scparse_on'        =>
				array(
					'input'    => "{SITENAME}",
					'expected' => 'e107',
				),
			'no_tags'           =>
				array(
					'input'    => "<b>bold</b>",
					'expected' => 'bold',
				),
			'do_tags'           =>
				array(
					'input'    => "<b>bold</b>",
					'expected' => '<b>bold</b>',
				),
			'fromadmin'         =>
				array(
					'input'    => "My Text {SITENAME} {e_PLUGIN} www.somewhere.com \nNew line :-)",
					'expected' => '',
				),
			'notadmin'          =>
				array(
					'input'    => "My Text {SITENAME} {e_PLUGIN} www.somewhere.com \nNew line :-)",
					'expected' => '',
				),
			'er_off'            =>
				array(
					'input'    => "My Text {SITENAME} {e_PLUGIN} www.somewhere.com \nNew line :-)",
					'expected' => '',
				),
			'er_on'             =>
				array(
					'input'    => "My Text {SITENAME} {e_PLUGIN} www.somewhere.com \nNew line :-)",
					'expected' => '',
				),
			'defs_off'          =>
				array(
					'input'    => "LAN_THANK_YOU",
					'expected' => 'LAN_THANK_YOU',
				),
			'defs_on'           =>
				array(
					'input'    => "LAN_THANK_YOU",
					'expected' => 'Thank you',
				),
			'dobreak'           =>
				array(
					'input'    => "Line 1\nLine 2\nLine 3",
					'expected' => 'Line 1<br />Line 2<br />Line 3',
				),
			'nobreak'           =>
				array(
					'input'    => "Line 1\nLine 2\nLine 3",
					'expected' => "Line 1\nLine 2\nLine 3",
				),
			'lb_nl'             =>
				array(
					'input'    => "Line 1\nLine 2\nLine 3",
					'expected' => "Line 1\nLine 2\nLine 3",
				),
			'lb_br'             =>
				array(
					'input'    => "Line 1\nLine 2\nLine 3",
					'expected' => 'Line 1<br />Line 2<br />Line 3',
				),
			'retain_nl'         =>
				array(
					'input'    => "Line 1\nLine 2\nLine 3",
					'expected' => "Line 1\nLine 2\nLine 3",
				),
			'defs'              =>
				array(
					'input'    => "LAN_THANK_YOU",
					'expected' => 'Thank you',
				),
			'parse_sc'          =>
				array(
					'input'    => "{SITENAME}",
					'expected' => 'e107',
				),
			'constants'         =>
				array(
					'input'    => "{e_PLUGIN}",
					'expected' => 'e107_plugins/',
				),
			'value'             =>
				array(
					'input'    => "",
					'expected' => '',
				),
			'wysiwyg'           =>
				array(
					'input'    => "",
					'expected' => '',
				),
		);


		$ret = [];
		foreach ($list as $mod => $val)
		{
			if (empty($tests[$mod]['expected']))
			{
				continue;
			}

			$result = $this->tp->toHTML($tests[$mod]['input'], false, 'defaults_off,' . $mod);
			self::assertSame($tests[$mod]['expected'], $result, $mod . " didn't match the expected result.");
			//	$ret[$mod] = $result;

		}


		//	e107::getConfig()->set('make_clickable', 0)->save(false, true);
		//	var_export($ret);

	}


	function testToHTMLWithBBcode()
	{
		$tests = array(
			0 => array(
				'text'     => '[code]$something = "something";[/code]',
				'expected' => "<pre class='prettyprint linenums code_highlight code-box bbcode-code' style='unicode-bidi: embed; direction: ltr'>\$something = &quot;something&quot;;</pre>",
			),
			1 => array(
				'text'     => '[b]Title[/b][code]$something = "something"; [b]Not parsed[/b][/code]',
				'expected' => "<strong class='bbcode bold bbcode-b'>Title</strong><pre class='prettyprint linenums code_highlight code-box bbcode-code' style='unicode-bidi: embed; direction: ltr'>\$something = &quot;something&quot;; &#091;b]Not parsed&#091;/b]</pre>",
			),
			2 => array(
				'text'     => '[php]<?php $something = "something";[/php]', // legacy usage, now deprecated.
				'expected' => "",
			),
			3 => array(
				'text'     => "[table][tr]\n[td]cell[/td]\n[/tr][/table]",
				'expected' => "<table class='table table-striped table-bordered bbcode-table'><tr>\n<td>cell</td>\n</tr></table>",
			),

			4 => array(
				'text'     => "Test\n[b]first line[/b][b]\nsecond line[/b]",
				'expected' => "Test<br /><strong class='bbcode bold bbcode-b'>first line</strong><strong class='bbcode bold bbcode-b'><br />second line</strong>",
			),

			5 => array(
				'text'     => "Test\n[code]1st [b]line[/b] of code[/code]\n[code]2nd line of code[/code]",
				'expected' => "Test<br /><pre class='prettyprint linenums code_highlight code-box bbcode-code' style='unicode-bidi: embed; direction: ltr'>1st &#091;b]line&#091;/b] of code</pre><pre class='prettyprint linenums code_highlight code-box bbcode-code' style='unicode-bidi: embed; direction: ltr'>2nd line of code</pre>",
			),


		);

		foreach ($tests as $index => $var)
		{
			$result = $this->tp->toHTML($var['text'], true);

			if (!isset($var['expected']))
			{
				echo $result . "\n\n";
				continue;
			}

			self::assertEquals($var['expected'], $result, 'Test #' . $index . ' failed.');
		}


	}

	public function testParseTemplateWithEnabledCoreShortcodes()
	{
		$needle = '<ul class="nav navbar-nav nav-main ml-auto">';
		$result = $this->tp->parseTemplate('{NAVIGATION}', true);
		self::assertStringContainsString($needle, $result);
	}

	public function testParseTemplateWithDisabledCoreShortcodes()
	{
		$result = $this->tp->parseTemplate('{NAVIGATION}', false);
		self::assertEmpty($result);
	}

	public function testParseTemplateWithCoreAddonShortcodes()
	{
		$shortcodeObject = e107::getScBatch('online', true);

		$expected = "<a href=''>lost</a>";
		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false, $shortcodeObject);
		self::assertEquals($expected, $result);

		e107::getPlugin()->uninstall('online');
		$sc = e107::getScParser();
		$sc->__construct();
		//	$sc->resetscClass('online', null);

		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false);
		self::assertEmpty($result);

		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
		self::assertEmpty($result, "{ONLINE_MEMBER_PAGE} wasn't empty: " . $result);

		$shortcodeObject = e107::getScBatch('online', true);

		$expected = "<a href=''>lost</a>";
		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false, $shortcodeObject);
		self::assertEquals($expected, $result);

		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
		self::assertEmpty($result);

		//	$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
		//	self::assertEquals($expected, $result);
	}

	public function testParseTemplateWithNonCoreShortcodes()
	{
		e107::getPlugin()->uninstall('download');
		e107::getScParser()->__construct();

		$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', false);
		self::assertEmpty($result);

		$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', true);
		self::assertEmpty($result);

		$shortcodeObject = e107::getScBatch('download', true);

		$needle = "<form class='form-search form-inline' ";
		$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', false, $shortcodeObject);
		self::assertStringContainsString($needle, $result);

		$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', false);
		self::assertEmpty($result);

		$result = $this->tp->parseTemplate('{DOWNLOAD_CAT_SEARCH}', true);
		self::assertEmpty($result);
	}

	public function testParseTemplateWithEvars()
	{
		$obj = new e_vars(array('ACTIVE' => "yes"));
		$result = $this->tp->parseTemplate('<div>something {ACTIVE}</div>', true, null, $obj);
		$expected = '<div>something yes</div>';

		self::assertEquals($expected, $result);

	}

	public function testParseSchemaTemplate()
	{
		// News Example..
		$news = [
			'news_id'   => 123,
			'news_title'   => 'Test',
			'news_text'   => 'Test',
			'news_datestamp'   => 1735732800, // January 1st, 2025, at 12:00 PM (noon)
			'news_author'   => 21,
			'news_meta_description' => "News item description",
			'news_start' => 0,
			'news_end' => 0,
			'news_modified' => 1735722000,
			'news_body' => 'Body of the news item',
			'news_extended' => '',
			'news_thumbnail'    => '{e_THEME}voux/install/gasmask.jpg,,,,',
		];

		$nsc = e107::getScBatch('news')->setScVar('news_item', $news);
		$tpl = e107::getTemplate('news', 'news_view','default');

		$result = $this->tp->parseSchemaTemplate($tpl['schema'], true, $nsc);
		$expected = '{
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://localhost/e107/news.php?extend.123"
    },
    "headline": "Test",
    "description": "News item description",
    "image": [
        "https://localhost/e107/thumb.php?src=e_THEME%2Fvoux%2Finstall%2Fgasmask.jpg&w=800&h=800"
    ],
    "author": {
        "@type": "Person",
        "name": "e107"
    },
    "publisher": {
        "@type": "Organization",
        "name": "e107",
        "logo": {
            "@type": "ImageObject",
            "url": "https://localhost/e107/e107_images/button.png"
        }
    },
    "datePublished": "2025-01-01T12:00:00+00:00",
    "dateModified": "2025-01-01T09:00:00+00:00",
    "articleBody": "Body of the news item"
}';

		$expectedDecoded = json_decode($expected, true);
		$resultDecoded = json_decode($result, true);

		self::assertSame($expectedDecoded['headline'],$resultDecoded['headline']);
		self::assertSame($expectedDecoded['description'],$resultDecoded['description']);
		self::assertSame($expectedDecoded['datePublished'],$resultDecoded['datePublished']);
		self::assertSame($expectedDecoded['dateModified'],$resultDecoded['dateModified']);
		self::assertSame($expectedDecoded['articleBody'],$resultDecoded['articleBody']);
		self::assertSame($expectedDecoded['author']['name'],$resultDecoded['author']['name']);
		self::assertSame($expectedDecoded['publisher']['name'],$resultDecoded['publisher']['name']);

		// Faqs example

		$sc = e107::getScBatch('faqs', true);
		$fullTemplate =  '
{
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
		{
		        "@type": "Question",
		        "name": "{FAQ_QUESTION}",
		        "acceptedAnswer": {
		          "@type": "Answer",
		          "text": "{FAQ_ANSWER}"
		        }
		}

	]
}
';

		$mainEntity = [
			1 => ['faq_id' => 1, 'faq_datestamp'=>1735732800, 'faq_question' => 'Question 1 &lt; 2001', 'faq_answer' => 'Answer 1', 'faq_order' => 1],
			2 => ['faq_id' => 2, 'faq_datestamp'=>1735732800, 'faq_question' => 'Question 2', 'faq_answer' => 'Answer 2', 'faq_order' => 2],

		];

		$expected = '
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "Question 1 < 2001",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Answer 1"
            }
        },
        {
            "@type": "Question",
            "name": "Question 2",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Answer 2"
            }
        }
    ]
}';

		$json = $this->tp->parseSchemaTemplate($fullTemplate, true, $sc, $mainEntity);
		$decodedExpected = json_decode($expected, true);
		$decodedJson = json_decode($json, true);

		// Assert that the decoded objects are identical
		self::assertSame($decodedExpected, $decodedJson);


	}


			public function testCreateConstants()
			{
				$tests = [
					'rel'   => '/e107_themes/agency2/install/news/bike.jpg',
					'abs'   => '{e_THEME}agency2/install/news/bike.jpg',
					'full'  => '/e107_themes/agency2/install/news/bike.jpg',
					'mix'   => '{e_THEME}agency2/install/news/bike.jpg',
					'nice'  => 'e_THEME/agency2/install/news/bike.jpg'
				];
				foreach($tests as $mode => $expected)
				{
					$result = $this->tp->createConstants("/e107_themes/agency2/install/news/bike.jpg", $mode);
					self::assertEquals($expected, $result);

				}

			}
/*
			public function testThumbEncode()
			{

			}

			public function testEmailObfuscate()
			{

			}
	*/
	public function testToFlatArray()
	{
		$input = [
			'a' => [
				'b' => [
					'c' => 'value',
				],
			],
		];
		$expected = [
			'prepend/xyza/b/c' => 'value'
		];

		$tp = $this->tp;
		$actual = $tp->toFlatArray($input, 'prepend/xyz');

		self::assertSame($expected, $actual);
	}

	public function testFromFlatArray()
	{
		$input = [
			'prepend/xyza/b/c' => 'value'
		];
		$expected = [
			'a' => [
				'b' => [
					'c' => 'value',
				],
			],
		];

		$tp = $this->tp;
		$actual = $tp->fromFlatArray($input, 'prepend/xyz');

		self::assertSame($expected, $actual);
	}

	public function testToForm()
	{

		$orig = "lr.src = window._lr.url + '/Scripts/api.js';";

		$db = $this->tp->toDB($orig);

		e107::getConfig()->updatePref('wysiwyg', true);
		e107::wysiwyg('default');
		$actual = $this->tp->toForm($db);
		$expected = 'lr.src = window._lr.url %2B &#039;/Scripts/api.js&#039;;';
		self::assertEquals($expected, $actual);

		e107::getConfig()->updatePref('wysiwyg', false);
		$actual = $this->tp->toForm($db);
		$expected = 'lr.src = window._lr.url + &#039;/Scripts/api.js&#039;;';
		self::assertEquals($expected, $actual);


		$actual = $this->tp->toForm("[html]Something &quot;hi&quot;[/html]");
		self::assertSame('[html]Something "hi"[/html]', $actual);

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

		foreach ($array as $arr)
		{
			$result = $this->tp->toASCII($arr['input']);
			self::assertEquals($arr['expected'], $result);
		}


	}

	public function testToNumber()
	{
		$result = $this->tp->toNumber('v2a');
		self::assertSame(2, $result);

		$result = $this->tp->toNumber('v1.5');
		self::assertSame(1.5, $result);


		$result = $this->tp->toNumber('v3.5');
		self::assertSame(3.5, $result);
	}
	/*
			public function testthumbUrlSEF()
			{
			//	$this->tp->thumbUrlSEF($url);




			}
	*/
	/*	public function testTextclean()
		{
			$string = "\n\n\nSomething\n\n\n";
			$result = $this->tp->textclean($string);
			var_export($result);
			//self::assertSame();
		}*/

	public function testMultibyteOn()
	{

		// enable multibyte mode.
		$this->tp->setMultibyte(true);

		$input = "русские";

		// strtoupper
		$result = $this->tp->ustrtoupper($input);
		self::assertEquals('РУССКИЕ', $result);

		// strlen
		$result = $this->tp->ustrlen($input);
		self::assertEquals(7, $result);

		// strtolower
		$result = $this->tp->ustrtolower('РУССКИЕ');
		self::assertEquals($input, $result);

		// strpos
		$result = $this->tp->ustrpos($input, 'и');
		self::assertEquals(5, $result);

		// substr
		$result = $this->tp->usubstr($input, 0, 5);
		self::assertEquals('русск', $result);

		// stristr
		$result = $this->tp->ustristr($input, 'ские', true);
		self::assertEquals('рус', $result);

		// strrpos (last occurance of a string)
		$result = $this->tp->ustrrpos($input, 'с');
		self::assertEquals(3, $result);

		$this->tp->setMultibyte(false); // disable after test.

	}

	public function testMultibyteOff()
	{

		// enable multibyte mode.
		$this->tp->setMultibyte(false);

		$input = "an example of text";

		// strtoupper
		$result = $this->tp->ustrtoupper($input);
		self::assertEquals('AN EXAMPLE OF TEXT', $result);

		// strlen
		$result = $this->tp->ustrlen($input);
		self::assertEquals(18, $result);

		// strtolower
		$result = $this->tp->ustrtolower('AN EXAMPLE OF TEXT');
		self::assertEquals($input, $result);

		// strpos
		$result = $this->tp->ustrpos($input, 't');
		self::assertEquals(14, $result);

		// substr
		$result = $this->tp->usubstr($input, 0, 5);
		self::assertEquals('an ex', $result);

		// stristr
		$result = $this->tp->ustristr($input, 'of', true);
		self::assertEquals('an example ', $result);

		// strrpos (last occurance of a string)
		$result = $this->tp->ustrrpos($input, 'e');
		self::assertEquals(15, $result);


	}

	/*
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
				'input'    => "<svg/onload=prompt(1)//",
				'expected' => '&lt;svg/onload=prompt(1)//'
			),
			1  => array(
				'input'    => "some plain text with a\nline break",
				'expected' => "some plain text with a\nline break"
			),
			2  => array(
				'input'    => "some [b]text[/b] with bbcodes",
				'expected' => "some [b]text[/b] with bbcodes"
			),
			3  => array(
				'input'    => 'some "quoted text" with a $ sign',
				'expected' => "some &quot;quoted text&quot; with a &#036; sign"
			),
			4  => array(
				'input'    => 'some <div>simple html</div><a href="http://somewhere.com">link</a>',
				'expected' => 'some <div>simple html</div><a href=&quot;http://somewhere.com&quot;>link</a>'
			),
			5  => array(
				'input'    => "[img]http://something.com[/img]",
				'expected' => "[img]http://something.com[/img]"
			),
			6  => array(
				'input'    => "<p>日本語 简体中文</p>",
				'expected' => "<p>日本語 简体中文</p>"
			),
			7  => array(
				'input'    => "<frameset onload=alert(1) data-something=where>",
				'expected' => "" // stripped xss
			),
			8  => array(
				'input'    => '<table background="javascript:alert(1)"><tr><td><a href="something.php" onclick="alert(1)">Hi there</a></td></tr></table>',
				'expected' => '<table><tr><td><a href=&quot;something.php&quot; onclick=&quot;#---sanitized---#&quot;>Hi there</a></td></tr></table>'
			),
			9  => array(
				'input'    => '<!--<img src="--><img src=x onerror=alert(1)//">',
				'expected' => "<!--<img src=&quot;--><img src=&quot;x&quot;>"
			),
			10 => array(
				'input'    => '<div style=content:url(data:image/svg+xml,%3Csvg/%3E);visibility:hidden onload=alert(1)>',
				'expected' => '<div style=&quot;#---sanitized---#&quot; onload=&quot;#---sanitized---#&quot;></div>'),
			11 => array(
				'input'    => '<a href="{e_PLUGIN}myplugin/index.php">Test</a>',
				'expected' => '<a href=&quot;{e_PLUGIN}myplugin/index.php&quot;>Test</a>'
			),
			12 => array(
				'input'    => "From here > to there",
				'expected' => "From here &gt; to there"
			),
			13 => array(
				'input'    => "[html]<div style='text-align:center'>Hello World!</div>[/html]",
				'expected' => '[html]<div style=&quot;text-align:center&quot;>Hello World!</div>[/html]'
			),
			14 => array(
				'input'    => "Something & something",
				'expected' => 'Something &amp; something'
			),
			15 => array(
				'input'    => array('news_category', '2', '0'),
				'expected' => array('news_category', '2', '0')
			),
			16 => array(
				'input'    => array('my/customer/key' => 'news_category', 3 => '2', 'bla' => 5, 'true' => true, 'false' => false, 'empty' => ''),
				'expected' => array('my/customer/key' => 'news_category', 3 => '2', 'bla' => 5, 'true' => true, 'false' => false, 'empty' => ''),
			),
			17 => array(
				'input'    => array('Some long string & stuff' => 0, 'other' => null, 'extra' => 0.3, 'null' => null),
				'expected' => array('Some long string & stuff' => 0, 'other' => null, 'extra' => 0.3, 'null' => null),
			),
			/*	18 => array(
					'input'     => '"><script>alert(123)</script>',
					'expected'  => '',
					'mode'      => 'model',
					'parm'      => array('type'=>'text', 'field'=>'news_title')
				),*/
			19 => array( // admin log simulation
				'input'    => "Array[!br!]([!br!]    [0] => zero[!br!]    [1] => one[!br!]    [2] => two[!br!])[!br!]",
				'expected' => "Array[!br!]([!br!]    [0] =&gt; zero[!br!]    [1] =&gt; one[!br!]    [2] =&gt; two[!br!])[!br!]",
				'mode'     => 'no_html',
			),
			20 => array(
				'input'    => '\\',
				'expected' => '&#092;',
				'mode'     => 'no_html',
			),
			21 => array(
				'input'    => '<a href="">Hello</a>',
				'expected' => '&lt;a href=&quot;&quot;&gt;Hello&lt;/a&gt;',
				'mode'     => 'no_html',
			),
			22 => array(
				'input'    => '< 200',
				'expected' => '&lt; 200',
			),
			23 => array(
				'input'    => '[html]<pre>echo {e_BASE}."index.php";</pre>[/html]',
				'expected' => '[html]<pre>echo &#123;e_BASE&#125;.&quot;index.php&quot;;</pre>[/html]'
			),
			24 => array(
				'input'    => '[html]<code>echo {e_BASE}."index.php";</code>[/html]',
				'expected' => '[html]<code>echo &#123;e_BASE&#125;.&quot;index.php&quot;;</code>[/html]'
			),
			25 => array(
				'input'    => '[html]<img src="{e_BASE}image.jpg" alt="">[/html]',
				'expected' => '[html]<img src=&quot;{e_BASE}image.jpg&quot; alt=&quot;&quot;>[/html]'
			),
			26 => array(
				'input'    => "[html]<code>function sc_my_shortcode(){\nreturn \"Something\";}</code>[/html]",
				'expected' => "[html]<code>function sc_my_shortcode()&#123;\nreturn &quot;Something&quot;;&#125;</code>[/html]"
			),
			27 => array(
				'input'    => "[html]<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>",
				'expected' => "[html]<pre class=&quot;whatever&quot;>require_once(&quot;class2.php&quot;);\nrequire_once(HEADERF);\necho &quot;test&quot;;&lt;br&gt;\nrequire_once(FOOTERF);</pre>",

			),
			28 => array(
				'html'     => "<pre>{THEME_PREF: code=header_width&default=container}</pre>",
				'expected' => "<pre>&#123;THEME_PREF: code=header_width&amp;default=container&#125;</pre>",
			),

			29 => array(
				'html'     => "<pre>/* {THEME_PREF: code=header_width&default=container} */</pre>",
				'expected' => "<pre>/* &#123;THEME_PREF: code=header_width&amp;default=container&#125; */</pre>",
			),

			30 => array(
				'html'     => "<hr />",
				'expected' => "<hr>",
			),

		);

		foreach ($tests as $k => $var)
		{
			if (empty($var['input']))
			{
				continue;
			}

			$mode = varset($var['mode']);
			$parm = varset($var['parm']);

			$result = $this->tp->toDB($var['input'], false, false, $mode, $parm);
			self::assertSame($var['expected'], $result, 'Test #' . $k . " failed." . print_r($this->tp->getRemoved(), true));

		}


	}

	/*

			public function testToJSONhelper()
			{

			}

			public function testToJSON()
			{

			}
	*/
	public function testPostToForm()
	{
		$text = "<div class='something'>My Test</div>";
		$expected = '&lt;div class=&#039;something&#039;&gt;My Test&lt;/div&gt;';
		$result = $this->tp->post_toForm($text);
		self::assertSame($expected, $result);

		$array = array($text);
		$arrayExp = array($expected);
		$result = $this->tp->post_toForm($array);
		self::assertSame($arrayExp, $result);


	}

	public function testHtml_truncate()
	{
		$this->tp->setMultibyte(true);

		$tests = array(
			0 => array(
				'input'    => '<p>Lorem ipsum dolor sit amet.</p>',
				'expected' => '<p>Lorem ipsum dolor...</p>',
			),
			1 => array(
				'input'    => '<p>Lorem ipsum <a href="">dolor</a> sit amet.</p>',
				'expected' => '<p>Lorem ipsum <a href="">dolor...</a></p>',
			),
			2 => array(
				'input'    => '<p>Lorem ipsum <img src="#" style="width:100px" /> dolor</img> sit amet.</p>',
				'expected' => '<p>Lorem ipsum <img src="#" style="width:100px" /> dolo...</p>',
			),
			3 => array(
				'input'    => '<p>Это <a href="#">предложение на русском</a> языке</p>',
				'expected' => '<p>Это <a href="#">предложение н...</a></p>',
			),
			4 => array(
				'input'    => '<p>Lorem ipsum &amp; dolor sit amet.</p>',
				'expected' => '<p>Lorem ipsum &amp; dol...</p>',
			),
			5 => array(
				'input'    => '<p>Это <a href="#">предложение на русском</a> языке</p>',
				'expected' => '<p>Это <a href="#">предложение...</a></p>',
				'exact'    => false,
			),
			/*	6   => array(
					'input'     => '<script>$();</script><!-- Start div --><div>Lorem</div><!-- End div --> ipsum dolor sit amet',
					'expected'  => '',
				),
				*/

		);

		foreach ($tests as $index => $var)
		{
			if (empty($var['input']))
			{
				continue;
			}

			$exact = isset($var['exact']) ? $var['exact'] : true;
			$result = $this->tp->html_truncate($var['input'], 17, '...', $exact);

			if (empty($var['expected']))
			{
				echo $result . "\n\n";
				continue;
			}

			self::assertSame($var['expected'], $result, "Failed on test #" . $index);
		}


	}

	/*
			public function testCheckHighlighting()
			{

			}

			public function testThumbWidth()
			{

			}
	*/
	public function testReplaceConstants()
	{
		$tests = array(
			0 => array(
				'path'  => '{e_BASE}news',
				'type'  => 'abs',
				'match' => e_HTTP,
			),
			1 => array(
				'path'  => '{e_BASE}news.php',
				'type'  => 'full',
				'match' => 'https://localhost/e107/news.php',
			),
			2 => array(
				'path'  => '{e_PLUGIN}news/index.php',
				'type'  => null,
				'match' => 'e107_plugins/news/index.php',
			),
		);


		foreach ($tests as $var)
		{
			$actual = $this->tp->replaceConstants($var['path'], $var['type']);
			self::assertStringContainsString($var['match'], $actual);
		}

	}

	/*
			public function testHtmlwrap()
			{
				$html = "<div><p>My paragraph <b>bold</b></p></div>";

				$result = $this->tp->htmlwrap($html, 20);
			}*/

	public function testToRss()
	{
		/*	if(PHP_VERSION_ID <  71000 )
			{
				$this->markTestSkipped("testToRSS() skipped. Requires a healthy libxml installation");
				return null;
			}*/

		$tests = array(
			'[html]<pre class=&quot;prettyprint linenums&quot; style=&quot;unicode-bidi: embed; direction: ltr;&quot;>&lt;/p&gt;&lt;p&gt;&lt;core name=&quot;e_jslib_plugin&quot;&gt;&lt;![CDATA[Array]]&gt;&lt;/core&gt;&lt;/p&gt;&lt;p&gt;&lt;core name=&quot;e_jslib_theme&quot;&gt;&lt;![CDATA[Array]]&gt;&lt;/core&gt;</pre>[/html]',
			'<div class="something">One & Two < and > " or \'</div>',
		);

		foreach ($tests as $html)
		{

			$result = $this->tp->toRss($html, true);
			$valid = $this->isValidXML($result);

			self::assertTrue($valid);
		}


		// Test with $tags = false;
		$html = '<div class="something">One & Two < and > " or \'</div>';
		$result = $this->tp->toRss($html);
		self::assertSame("One &amp; Two &lt; and &gt; \" or '", $result);
		$valid = $this->isValidXML('<tag>' . $result . '</tag>');
		self::assertTrue($valid);


	}

	private function isValidXML($xmlContent)
	{
		if (trim($xmlContent) == '')
		{
			return false;
		}

		$xmlContent = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<description>' . $xmlContent . '</description>';

		libxml_use_internal_errors(true);
		libxml_clear_errors();

		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->loadXML($xmlContent);

		$errors = libxml_get_errors();

		if (!empty($errors))
		{
			codecept_debug($errors);
		}

		libxml_clear_errors();

		return empty($errors);
	}


	/*
			public function testPreFilter()
			{

			}
	*/
	public function testThumbUrl()
	{
		$urls = array(
			0 => array(
				'path'     => '{e_PLUGIN}gallery/images/butterfly.jpg',
				'options'  => array('w' => 300, 'h' => 200),
				'expected' => '/thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&amp;w=300&amp;h=200'
			),
			1 => array(
				'path'     => '{e_PLUGIN}dummy/Freesample.svg',
				'options'  => array('w' => 300, 'h' => 200),
				'expected' => '/e107_plugins/dummy/Freesample.svg'
			),
			2 => array(
				'path'     => '{e_PLUGIN}gallery/images/butterfly.jpg',
				'options'  => array('w' => 300, 'h' => 200, 'type' => 'webp'),
				'expected' => '/thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&amp;w=300&amp;h=200&amp;type=webp'
			),
			3 => array(
				'path'     => '{e_PLUGIN}gallery/images/butterfly.jpg',
				'options'  => array('w' => 300, 'h' => 200, 'scale' => '2x'),
				'expected' => '/thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&amp;w=600&amp;h=400'
			),
			4 => array(
				'path'     => '{e_PLUGIN}gallery/images/horse.jpg',
				'options'  => array('w' => 300, 'h' => 200, 'scale' => '2x', 'type' => 'webp'),
				'expected' => '/thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fhorse.jpg&amp;w=600&amp;h=400&amp;type=webp'
			),

		);

		foreach ($urls as $val)
		{

			$actual = $this->tp->thumbUrl($val['path'], $val['options']);

			self::assertStringContainsString($val['expected'], $actual);
			//echo $$actual."\n\n";
		}


	}

	public function testThumbUrlSEF()
	{
		$urls = array(
			0 => array(
				'path'     => '{e_MEDIA_IMAGE}butterfly.jpg',
				'options'  => array('w' => 300, 'h' => 200),
				'expected' => '/media/img/300x200/butterfly.jpg'
			),
			1 => array(
				'path'     => '{e_THEME}dummy/Freesample.svg',
				'options'  => array('w' => 300, 'h' => 200),
				'expected' => '/theme/img/300x200/dummy/Freesample.svg'
			),
			2 => array(
				'path'     => '{e_AVATAR}avatar.jpg',
				'options'  => array('w' => 100, 'h' => 100),
				'expected' => '/media/avatar/100x100/avatar.jpg'
			),

			/*2 => array(
				'path'      => '{e_MEDIA_IMAGE}gallery/images/butterfly.jpg',
				'options'   =>  array('w'=>300, 'h'=>200, 'type'=>'webp'),
				'expected'  =>'/media/img/300x200/gallery/images/butterfly.webp'
				),*/
			3 => array(
				'path'     => '{e_MEDIA_IMAGE}gallery/images/butterfly.jpg',
				'options'  => array('w' => 300, 'h' => 200, 'scale' => '2x'),
				'expected' => '/media/img/600x400/gallery/images/butterfly.jpg'
			),

		);

		foreach ($urls as $val)
		{
			$actual = $this->tp->thumbUrlSEF($val['path'], $val['options']);
			self::assertStringContainsString($val['expected'], $actual);
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
	*/
	public function testToAttributeReplaceConstants()
	{
		$input = "This is e_THEME: {e_THEME}";
		$expected = "This is e_THEME: ./e107_themes/";

		$actual = $this->tp->toAttribute($input);

		self::assertEquals($expected, $actual);
	}

	public function testToAttributeDoesNotReplaceConstantsWhenStringHasSingleQuote()
	{
		$input = "This isn't e_THEME: {e_THEME}";
		$expected = "This isn&#039;t e_THEME: {e_THEME}";

		$actual = $this->tp->toAttribute($input);

		self::assertEquals($expected, $actual);
	}

	public function testToAttributeDoesReplaceConstantsWhenStringHasLeftAngleBracket()
	{
		$input = "{e_THEME} <-- e_THEME";
		$expected = "./e107_themes/ &lt;-- e_THEME";

		$actual = $this->tp->toAttribute($input);

		self::assertEquals($expected, $actual);
	}

	public function testToAttributeExplicitPure()
	{
		$input = "{e_THEME} <-- Not e_THEME";
		$expected = "{e_THEME} &lt;-- Not e_THEME";

		$actual = $this->tp->toAttribute($input, true);

		self::assertEquals($expected, $actual);
	}

	public function testToAttributeImplicitPure()
	{
		$input = "\"It's a Wonderful Life (1946)\"";
		$expected = "&quot;It&#039;s a Wonderful Life (1946)&quot;";

		$actual = $this->tp->toAttribute($input);

		self::assertEquals($expected, $actual);
	}

	public function testToAttributesEmpty()
	{
		$input = [];
		$expected = "";

		$actual = $this->tp->toAttributes($input);

		self::assertEquals($expected, $actual);
	}

	public function testToAttributesOne()
	{
		$input = ["href" => "https://e107.org"];
		$expected = " href='https://e107.org'";

		$actual = $this->tp->toAttributes($input);

		self::assertEquals($expected, $actual);
	}

	public function testToAttributesMixedPureAndReplaceConstants()
	{
		$input = [
			"href"  => "{e_THEME}",
			"title" => "I would say, \"I'm the e_THEME folder!\"",
			"alt"   => "'{e_THEME}'",
		];
		$expected = " href='./e107_themes/'" .
			" title='I would say, &quot;I&#039;m the e_THEME folder!&quot;'" .
			" alt='&#039;{e_THEME}&#039;'";

		$actual = $this->tp->toAttributes($input);

		self::assertEquals($expected, $actual);
	}

	public function testThumbCacheFile()
	{
		$tests = array(
			0 => array(
				'file'     => 'e107_plugins/gallery/images/butterfly.jpg',
				'options'  => array('w' => 222, 'h' => 272, 'aw' => 0, 'ah' => 0, 'c' => 0,),
				'expected' => array('prefix' => 'thumb_butterfly_', 'suffix' => '.jpg.cache.bin'),
			),
			1 => array(
				'file'     => 'e107_plugins/gallery/images/butterfly.jpg',
				'options'  => array('w' => 222, 'h' => 272, 'aw' => 0, 'ah' => 0, 'c' => 0, 'type' => 'webp'),
				'expected' => array('prefix' => 'thumb_butterfly_', 'suffix' => '.webp.cache.bin'),
			),
			2 => array(
				'file'     => 'e107_plugins/gallery/images/butterfly.jpg',
				'options'  => array('w' => 222, 'h' => 272,  'c' => 0, 'type' => 'webp'),
				'expected' => array('prefix' => 'thumb_butterfly_', 'suffix' => '.webp.cache.bin'),
			),

		);

		foreach ($tests as $var)
		{

			$result = $this->tp->thumbCacheFile($var['file'], $var['options']);

			self::assertStringStartsWith($var['expected']['prefix'], $result);
			self::assertStringEndsWith($var['expected']['suffix'], $result);

		}


	}

	public function testText_truncate()
	{
		$string = "This is a long string that will be truncated.";
		$result = $this->tp->text_truncate($string, 20);
		self::assertSame('This is a long  ... ', $result);

		$string = "This is has something &amp; something";
		$result = $this->tp->text_truncate($string, 29);
		self::assertSame('This is has something &  ... ', $result);

		$string = "Can't fail me now [b]Bold[/b]";
		$result = $this->tp->text_truncate($string, 25);
		self::assertSame("Can't fail me now Bold", $result);

		$string = "Can't fail me now <strong class='bbcode bold bbcode-b'>Bold</strong>";
		$result = $this->tp->text_truncate($string, 25);
		self::assertSame("Can't fail me now Bold", $result);

	}

	public function testTruncate()
	{
		// html
		$string = "Can't fail me now <strong class='bbcode bold bbcode-b'>Bold</strong>";
		$result = $this->tp->truncate($string, 25);
		self::assertSame("Can't fail me now <strong class='bbcode bold bbcode-b'>Bold</strong>", $result); // html ignored in char count.

		// bbcode - stripped.
		$string = "Can't fail me now [b]Bold[/b]";
		$result = $this->tp->truncate($string, 25);
		self::assertSame("Can't fail me now Bold", $result);

		// text
		$string = "This is a long string that will be truncated.";
		$result = $this->tp->truncate($string, 20);
		self::assertSame('This is a long st...', $result);

	}

	/*
			public function testSetThumbSize()
			{

			}

			public function testToJS()
			{

			}
	*/
	public function testSimpleParse()
	{
		$vars = array(
			'CONTACT_SUBJECT' => "My Subject",
			'CONTACT_PERSON'  => "My Name"
		);

		$template = "{CONTACT_SUBJECT} <b>{CONTACT_PERSON}</b>{MISSING_SHORTCODE}";

		$result = $this->tp->simpleParse($template, $vars);
		self::assertEquals("My Subject <b>My Name</b>", $result);

		$result = $this->tp->simpleParse($template, null);
		self::assertEquals(" <b></b>", $result);


		$vars = array(
			'aaBB_123' => "Simple Replacement"
		);

		$template = "-- {aaBB_123} --";
		$result = $this->tp->simpleParse($template, $vars);
		self::assertEquals('-- Simple Replacement --', $result);

	}

	public function testGetModifierList()
	{
		$expected = array(
			'TITLE'        =>
				array(
					'context'      => 'TITLE',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => true,
					'constants'    => false,
					'hook'         => true,
					'scripts'      => true,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => true,
					'retain_nl'    => true,
				),
			'TITLE_PLAIN'  =>
				array(
					'context'      => 'TITLE_PLAIN',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => true,
					'constants'    => false,
					'hook'         => true,
					'scripts'      => true,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => true,
					'value'        => false,
					'nobreak'      => true,
					'retain_nl'    => true,
				),
			'USER_TITLE'   =>
				array(
					'context'      => 'USER_TITLE',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => false,
					'constants'    => false,
					'hook'         => false,
					'scripts'      => false,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => false,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => true,
					'retain_nl'    => true,
				),
			'E_TITLE'      =>
				array(
					'context'      => 'E_TITLE',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => true,
					'constants'    => false,
					'hook'         => true,
					'scripts'      => false,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => true,
					'retain_nl'    => true,
				),
			'SUMMARY'      =>
				array(
					'context'      => 'SUMMARY',
					'fromadmin'    => false,
					'emotes'       => true,
					'defs'         => true,
					'constants'    => 'full',
					'hook'         => true,
					'scripts'      => true,
					'link_click'   => true,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => false,
					'retain_nl'    => false,
				),
			'DESCRIPTION'  =>
				array(
					'context'      => 'DESCRIPTION',
					'fromadmin'    => false,
					'emotes'       => true,
					'defs'         => true,
					'constants'    => 'full',
					'hook'         => true,
					'scripts'      => true,
					'link_click'   => true,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => false,
					'retain_nl'    => false,
				),
			'BODY'         =>
				array(
					'context'      => 'BODY',
					'fromadmin'    => false,
					'emotes'       => true,
					'defs'         => true,
					'constants'    => 'full',
					'hook'         => true,
					'scripts'      => true,
					'link_click'   => true,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => false,
					'retain_nl'    => false,
				),
			'WYSIWYG'      =>
				array(
					'context'      => 'WYSIWYG',
					'fromadmin'    => false,
					'emotes'       => true,
					'defs'         => false,
					'constants'    => false,
					'hook'         => false,
					'scripts'      => true,
					'link_click'   => false,
					'link_replace' => false,
					'parse_sc'     => false,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => false,
					'retain_nl'    => true,
				),
			'USER_BODY'    =>
				array(
					'context'      => 'USER_BODY',
					'fromadmin'    => false,
					'emotes'       => true,
					'defs'         => false,
					'constants'    => 'full',
					'hook'         => true,
					'scripts'      => false,
					'link_click'   => true,
					'link_replace' => true,
					'parse_sc'     => false,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => false,
					'retain_nl'    => false,
					'nostrip'      => false,
				),
			'E_BODY'       =>
				array(
					'context'      => 'E_BODY',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => true,
					'constants'    => 'full',
					'hook'         => true,
					'scripts'      => false,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => false,
					'retain_nl'    => false,
				),
			'E_BODY_PLAIN' =>
				array(
					'context'      => 'E_BODY_PLAIN',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => true,
					'constants'    => 'full',
					'hook'         => true,
					'scripts'      => false,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => true,
					'value'        => false,
					'nobreak'      => false,
					'retain_nl'    => true,
				),
			'LINKTEXT'     =>
				array(
					'context'      => 'LINKTEXT',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => true,
					'constants'    => false,
					'hook'         => false,
					'scripts'      => true,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => true,
					'no_tags'      => false,
					'value'        => false,
					'nobreak'      => true,
					'retain_nl'    => true,
				),
			'RAWTEXT'      =>
				array(
					'context'      => 'RAWTEXT',
					'fromadmin'    => false,
					'emotes'       => false,
					'defs'         => false,
					'constants'    => false,
					'hook'         => false,
					'scripts'      => true,
					'link_click'   => false,
					'link_replace' => true,
					'parse_sc'     => false,
					'no_tags'      => true,
					'value'        => false,
					'nobreak'      => true,
					'retain_nl'    => true,
				),
			'NODEFAULT'    => array(
				'context'      => 'NODEFAULT',
				'fromadmin'    => false,
				'emotes'       => false,
				'defs'         => false,
				'constants'    => false,
				'hook'         => false,
				'scripts'      => false,
				'link_click'   => false,
				'link_replace' => false,
				'parse_sc'     => false,
				'no_tags'      => false,
				'value'        => false,
				'nobreak'      => false,
				'retain_nl'    => false,
			)
		);

		$list = $this->tp->getModifierList('super');
		self::assertSame($expected, $list);


	}

	public function testToText()
	{
		$arr = array(
			// Basic Cases
			0  => array('html' => "<h1><a href='#'>My Caption</a></h1>", 'expected' => 'My Caption'),
			1  => array('html' => "<div><h1><a href='#'>My Caption</a></h1></div>", 'expected' => 'My Caption'),
			2  => array('html' => 'Line 1<br />Line 2<br />Line 3<br />', 'expected' => "Line 1\nLine 2\nLine 3\n"),
			3  => array('html' => "Line 1<br />\nLine 2<br />\nLine 3<br />", 'expected' => "Line 1\nLine 2\nLine 3\n"),

			// Special Characters
			/*
			4  => array('html' => 'Text &amp; More Text', 'expected' => 'Text & More Text'),
			5  => array('html' => 'Text &lt;b&gt;Bold&lt;/b&gt;', 'expected' => 'Text <b>Bold</b>'),
			6  => array('html' => '<b>Bold &amp; Italic</b>', 'expected' => 'Bold & Italic'),

			// HTML Entities
			7  => array('html' => '&lt;div&gt;Hello World&lt;/div&gt;', 'expected' => '<div>Hello World</div>'),
			8  => array('html' => 'Text with &copy; and &reg;', 'expected' => 'Text with © and ®'),

			// Empty and Plain Text
			9  => array('html' => '', 'expected' => ''),
			10 => array('html' => null, 'expected' => ''), // If null should be handled as empty string.
			11 => array('html' => 'Plain Text', 'expected' => 'Plain Text'),

			// Whitespace and Non-breaking Spaces
			12 => array('html' => '   Text surrounded by spaces   ', 'expected' => '   Text surrounded by spaces   '),
			13 => array('html' => 'Text&nbsp;with&nbsp;non-breaking&nbsp;spaces', 'expected' => 'Text with non-breaking spaces'),

			// Nested Tags
			14 => array('html' => '<div><span><b>Deeply Nested</b></span></div>', 'expected' => 'Deeply Nested'),
			15 => array('html' => '<ul><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>', 'expected' => "Item 1\nItem 2\nItem 3"),

			// Void Tags
			16 => array('html' => 'Text<img src="image.jpg" alt="My Image">More Text', 'expected' => 'TextMore Text'),
			17 => array('html' => '<hr />Line Break<hr />', 'expected' => "Line Break"),

			// Malformed/Invalid HTML
			18 => array('html' => '<div>Unclosed tag', 'expected' => 'Unclosed tag'), // Closes should be handled.
			19 => array('html' => 'Text<b>Bold Text<div>', 'expected' => "TextBold Text"), // Handle even broken tags.
			*/
		);


		foreach ($arr as $k=>$var)
		{
			$result = $this->tp->toText($var['html']);
			self::assertEquals($var['expected'], $result, "Test $k failed");
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
	*/
	public function testStaticUrl()
	{

		$tests = array(
			0 => array(
				'expected' => 'https://static.mydomain.com/',
				'input'    => null,
				'static'   => true,
			),
			1 => array(
				'expected' => 'https://static.mydomain.com/e107_web/lib/font-awesome/4.7.0/fonts/fontawesome-webfont.woff2?v=4.7.0',
				'input'    => e_WEB_ABS . 'lib/font-awesome/4.7.0/fonts/fontawesome-webfont.woff2?v=4.7.0',
				'static'   => true,
			),
			2 => array(
				'expected' => 'https://static.mydomain.com/e107_media/000000test/myimage.jpg',
				'input'    => e_MEDIA_ABS . 'myimage.jpg',
				'static'   => true,
			),
			3 => array(
				'expected' => 'https://static.mydomain.com/e107_themes/bootstrap3/images/myimage.jpg',
				'input'    => '{THEME}images/myimage.jpg',
				'static'   => true,
			),
			4 => array(
				'expected' => e_WEB_ABS . 'lib/font-awesome/4.7.0/fonts/fontawesome-webfont.woff2?v=4.7.0',
				'input'    => '{e_WEB}lib/font-awesome/4.7.0/fonts/fontawesome-webfont.woff2?v=4.7.0',
				'static'   => false,
			),
		);

		foreach ($tests as $val)
		{
			$static = !empty($val['static']) ? 'https://static.mydomain.com/' : null;
			$this->tp->setStaticUrl($static);
			$actual = $this->tp->staticUrl($val['input']);
			self::assertSame($val['expected'], $actual);
		}

		$this->tp->setStaticUrl(null);

		// Test with Static Array

		$static = [
			'https://static1.mydomain.com/',
			'https://static2.mydomain.com/',
			'https://static3.mydomain.com/',
		];

		$this->tp->setStaticUrl($static);
		$tests = [
			1 => array(
				'expected' => 'https://static1.mydomain.com/e107_themes/bootstrap3/images/myimage1.jpg',
				'input'    => '{THEME}images/myimage1.jpg',
				'static'   => true,
			),
			2 => array(
				'expected' => 'https://static2.mydomain.com/e107_themes/bootstrap3/images/myimage2.jpg',
				'input'    => '{THEME}images/myimage2.jpg',
				'static'   => true,
			),
			3 => array(
				'expected' => 'https://static3.mydomain.com/e107_themes/bootstrap3/images/myimage3.jpg',
				'input'    => '{THEME}images/myimage3.jpg',
				'static'   => true,
			),
			4 => array( // test that previously generated static URL retains the same static domain when called again.
				'expected' => 'https://static3.mydomain.com/e107_themes/bootstrap3/images/myimage3.jpg',
				'input'    => '{THEME}images/myimage3.jpg',
				'static'   => true,
			),
			5 => array( // test that previously generated static URL retains the same static domain when called again.
				'expected' => 'https://static2.mydomain.com/e107_themes/bootstrap3/images/myimage2.jpg',
				'input'    => '{THEME}images/myimage2.jpg',
				'static'   => true,
			),

		];

		foreach($tests as $val)
		{
			$actual = $this->tp->staticUrl($val['input']);
			self::assertSame($val['expected'], $actual);
		}

		$map = $this->tp->getStaticUrlMap();
		self::assertStringContainsString('https://static2.mydomain.com', $map['e107-themes/bootstrap3/images/myimage2.jpg'] );

		$this->tp->setStaticUrl(null);
	}

	/*
			public function testGetUrlConstants()
			{

			}

			public function testUstrrpos()
			{

			}
	*/
	public function testPost_toHTML()
	{
		$text = "<di style='width:100%'>Test</di>"; // invalid html.
		$result = $this->tp->post_toHTML($text);
		self::assertEmpty($result);

		$text = "<div style='width:100%'>Test</div>"; // valid html.
		$cleaned = '<div style="width:100%">Test</div>'; // valid and cleaned html.
		$result = $this->tp->post_toHTML($text);
		self::assertSame($cleaned, $result);

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
*/
	public function testSetScriptAccess()
	{
		$this->tp->setScriptAccess(e_UC_PUBLIC);
		$result = $this->tp->getScriptAccess();
		self::assertEquals(e_UC_PUBLIC, $result);
	}

	/*
			public function testGetAllowedTags()
			{

			}
	*/
	public function testGetScriptAccess()
	{
		$result = $this->tp->getScriptAccess();
		self::assertFalse($result);
	}

	public function testGetAllowedAttributes()
	{
		$expected = array(
			'default'  =>
				array(
					0 => 'id',
					1 => 'style',
					2 => 'class',
					3 => 'title',
					4 => 'lang',
					5 => 'accesskey',
				),
			'img'      =>
				array(
					0 => 'src',
					1 => 'alt',
					2 => 'width',
					3 => 'height',
					4 => 'id',
					5 => 'style',
					6 => 'class',
					7 => 'title',
					8 => 'lang',
					9 => 'accesskey',
				),
			'a'        =>
				array(
					0 => 'href',
					1 => 'target',
					2 => 'rel',
					3 => 'id',
					4 => 'style',
					5 => 'class',
					6 => 'title',
					7 => 'lang',
					8 => 'accesskey',
				),
			'script'   =>
				array(
					0 => 'type',
					1 => 'src',
					2 => 'language',
					3 => 'async',
					4 => 'id',
					5 => 'style',
					6 => 'class',
					7 => 'title',
					8 => 'lang',
					9 => 'accesskey',
				),
			'iframe'   =>
				array(
					'src',
					'frameborder',
					'width',
					'height',
					'allowfullscreen',
					'allow',
					'id',
					'style',
					'class',
					'title',
					'lang',
					'accesskey',
				),
			'input'    =>
				array(
					0 => 'type',
					1 => 'name',
					2 => 'value',
					3 => 'id',
					4 => 'style',
					5 => 'class',
					6 => 'title',
					7 => 'lang',
					8 => 'accesskey',
				),
			'form'     =>
				array(
					0 => 'action',
					1 => 'method',
					2 => 'target',
					3 => 'id',
					4 => 'style',
					5 => 'class',
					6 => 'title',
					7 => 'lang',
					8 => 'accesskey',
				),
			'audio'    =>
				array(
					0  => 'src',
					1  => 'controls',
					2  => 'autoplay',
					3  => 'loop',
					4  => 'muted',
					5  => 'preload',
					6  => 'id',
					7  => 'style',
					8  => 'class',
					9  => 'title',
					10 => 'lang',
					11 => 'accesskey',
				),
			'video'    =>
				array(
					0  => 'autoplay',
					1  => 'controls',
					2  => 'height',
					3  => 'loop',
					4  => 'muted',
					5  => 'poster',
					6  => 'preload',
					7  => 'src',
					8  => 'width',
					9  => 'id',
					10 => 'style',
					11 => 'class',
					12 => 'title',
					13 => 'lang',
					14 => 'accesskey',
				),
			'table'    => array(
				0 => 'border',
				1 => 'cellpadding',
				2 => 'cellspacing',
				3 => 'id',
				4 => 'style',
				5 => 'class',
				6 => 'title',
				7 => 'lang',
				8 => 'accesskey',
			),
			'td'       =>
				array(
					0 => 'colspan',
					1 => 'rowspan',
					2 => 'name',
					3 => 'bgcolor',
					4 => 'id',
					5 => 'style',
					6 => 'class',
					7 => 'title',
					8 => 'lang',
					9 => 'accesskey',
				),
			'th'       =>
				array(
					0 => 'colspan',
					1 => 'rowspan',
					2 => 'id',
					3 => 'style',
					4 => 'class',
					5 => 'title',
					6 => 'lang',
					7 => 'accesskey',
				),
			'col'      =>
				array(
					0 => 'span',
					1 => 'id',
					2 => 'style',
					3 => 'class',
					4 => 'title',
					5 => 'lang',
					6 => 'accesskey',
				),
			'embed'    =>
				array(
					0  => 'src',
					1  => 'wmode',
					2  => 'type',
					3  => 'width',
					4  => 'height',
					5  => 'id',
					6  => 'style',
					7  => 'class',
					8  => 'title',
					9  => 'lang',
					10 => 'accesskey',
				),
			'x-bbcode' =>
				array(
					0 => 'alt',
					1 => 'id',
					2 => 'style',
					3 => 'class',
					4 => 'title',
					5 => 'lang',
					6 => 'accesskey',
				),
			'label'    =>
				array(
					0 => 'for',
					1 => 'id',
					2 => 'style',
					3 => 'class',
					4 => 'title',
					5 => 'lang',
					6 => 'accesskey',
				),
			'source'   =>
				array(
					0  => 'media',
					1  => 'sizes',
					2  => 'src',
					3  => 'srcset',
					4  => 'type',
					5  => 'id',
					6  => 'style',
					7  => 'class',
					8  => 'title',
					9  => 'lang',
					10 => 'accesskey',
				),
		);


		$result = $this->tp->getAllowedAttributes();
		self::assertSame($expected, $result);


		//	var_export($result);
		//  $true = is_array($result) && in_array('style',$result['img']);

		//  self::assertTrue($true);
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
	*/
	public function testGetTags()
	{
		$html = "<div><img src='#' alt='whatever' /></div>";
		$result = $this->tp->getTags($html, 'img');
		$expected = array(
			'img' =>
				array(
					0 =>
						array(
							'src'    => '#',
							'alt'    => 'whatever',
							'@value' => '<img alt="whatever" src="#"></img>',
						),
				),
		);

		if (empty($expected['img'][0]))
		{
			self::assertTrue(false, "getTags() didn't return the correct value");
		}

		self::assertSame($expected['img'][0]['src'], $result['img'][0]['src']);
		self::assertSame($expected['img'][0]['alt'], $result['img'][0]['alt']);
	}

	public function testToGlyph()
	{
		$this->tp->setFontAwesome(4);

		$result = $this->tp->toGlyph('fa-envelope.glyph');
		$expected = "<i class='fa fa-envelope' ></i> ";
		self::assertEquals($expected, $result);

		$this->tp->setFontAwesome(5);

		$result = $this->tp->toGlyph('fa-mailchimp');
		$expected = "<i class='fab fa-mailchimp' ></i> ";
		self::assertEquals($expected, $result);

		$this->tp->setFontAwesome(6);
		$result = $this->tp->toGlyph('fa-wine-glass-empty');
		$expected = "<i class='fas fa-wine-glass-empty' ></i> ";
		self::assertSame($expected, $result);

		$result = $this->tp->toGlyph('fa-virus-covid');
		self::assertSame("<i class='fas fa-virus-covid' ></i> ", $result);

		$this->tp->setFontAwesome(4);

		$result = $this->tp->toGlyph('fab-mailchimp'); // spefific call
		$expected = "<i class='fab fa-mailchimp' ></i> ";
		self::assertEquals($expected, $result);

		$result = $this->tp->toGlyph('fas-camera'); // spefific call
		self::assertSame("<i class='fas fa-camera' ></i> ", $result);

		// test core, shims and old identifiers with FontAwesome 5 installed.
		$this->tp->setFontAwesome(5);

		$tests = array(
			'e-database-16'  => "<i class='S16 e-database-16'></i>",
			'e-database-32'  => "<i class='S32 e-database-32'></i>",
			'fa-sun-o'       => "<i class='far fa-sun' ></i> ",
			'fa-comments-o'  => "<i class='far fa-comments' ></i> ",
			'fa-file-text-o' => "<i class='far fa-file-alt' ></i> ",
			'fa-bank'        => "<i class='fa fa-university' ></i> ",
			'fa-warning'     => "<i class='fa fa-exclamation-triangle' ></i> ",
			'glyphicon-star' => "<i class='fas fa-star' ></i> ",
			'icon-star'      => "<i class='fas fa-star' ></i> ",
			'floppy-disk'    => "<i class='glyphicon glyphicon-floppy-disk' ></i> ",
			'icon-user'      => "<i class='fas fa-user' ></i> ",
			'user'           => "<i class='fas fa-user' ></i> ",
			'flag'           => "<i class='fas fa-flag' ></i> ",
			'fa-'            => null,

		);

		foreach ($tests as $icon => $expected)
		{
			$result = $this->tp->toGlyph($icon);
			self::assertSame($expected, $result);
		}


		// test core, shims and old identifiers with FontAwesome 4 installed.
		$this->tp->setFontAwesome(4);

		$tests = array(
			'e-database-16'  => "<i class='S16 e-database-16'></i>",
			'e-database-32'  => "<i class='S32 e-database-32'></i>",
			'fa-sun-o'       => "<i class='fa fa-sun-o' ></i> ",
			'fa-comments-o'  => "<i class='fa fa-comments-o' ></i> ",
			'fa-file-text-o' => "<i class='fa fa-file-text-o' ></i> ",
			'fa-bank'        => "<i class='fa fa-bank' ></i> ",
			'fa-warning'     => "<i class='fa fa-warning' ></i> ",
			'glyphicon-star' => "<i class='fa fa-star' ></i> ",
			'icon-star'      => "<i class='fa fa-star' ></i> ",
			'floppy-disk'    => "<i class='glyphicon glyphicon-floppy-disk' ></i> ",
			'icon-user'      => "<i class='fa fa-user' ></i> ",
			'user'           => "<i class='glyphicon glyphicon-user' ></i> ",
			'flag'           => "<i class='glyphicon glyphicon-flag' ></i> ",
			'fa-'            => null,

		);

		foreach ($tests as $icon => $expected)
		{
			$result = $this->tp->toGlyph($icon);
			self::assertSame($expected, $result, 'Input was: ' . $icon);
		}


		// test options.
		$this->tp->setFontAwesome(5);
		$opts = array(
			0   => ['size'=>'3x',       'expected'  => "<i class='fas fa-camera fa-3x' ></i>"],
			1   => ['spin'=>1,          'expected'  => "<i class='fas fa-camera fa-spin' ></i>"],
			2   => ['rotate'=>180,      'expected'  => "<i class='fas fa-camera fa-rotate-180' ></i>"],
			3   => ['fw'=>1,            'expected'  => "<i class='fas fa-camera fa-fw' ></i>"],
		);

		foreach($opts as $parm)
		{
			$expected = $parm['expected'];
			unset($parm['expected']);
			$result = $this->tp->toGlyph('fa-camera', $parm);
			self::assertSame($expected, $result);
		}


			// test options.
		$this->tp->setFontAwesome(4);
		$opts = array(
			0   => ['size'=>'3x',       'expected'  => "<i class='fa fa-camera fa-3x' ></i>"],
			1   => ['spin'=>1,          'expected'  => "<i class='fa fa-camera fa-spin' ></i>"],
			2   => ['rotate'=>180,      'expected'  => "<i class='fa fa-camera fa-rotate-180' ></i>"],
			3   => ['fw'=>1,            'expected'  => "<i class='fa fa-camera fa-fw' ></i>"],
		);

		foreach($opts as $parm)
		{
			$expected = $parm['expected'];
			unset($parm['expected']);
			$result = $this->tp->toGlyph('fa-camera', $parm);
			self::assertSame($expected, $result);
		}


	}

	function testToGlyphFallback()
	{
		$this->tp->setFontAwesome(5);
		$result = $this->tp->toGlyph('fa-paypal.glyph');
		self::assertSame("<i class='fab fa-paypal' ></i> ", $result);

		$this->tp->setFontAwesome(6);
		$result = $this->tp->toGlyph('fa-paypal.glyph');
		self::assertSame("<i class='fab fa-paypal' ></i> ", $result);

		$result = $this->tp->toGlyph('fa-clock.glyph');
		self::assertSame("<i class='fas fa-clock' ></i> ", $result);

		$result = $this->tp->toGlyph('clock.glyph');
		self::assertSame("<i class='fas fa-clock' ></i> ", $result);


		$this->tp->setFontAwesome(5);
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
		$icon = codecept_data_dir() . "icon_64.png";

		if (!is_dir(e_AVATAR_UPLOAD))
		{
			mkdir(e_AVATAR_UPLOAD, 0755, true);
		}

		if (!is_dir(e_AVATAR_DEFAULT))
		{
			mkdir(e_AVATAR_DEFAULT, 0755, true);
		}

		if (!copy($icon, e_AVATAR_UPLOAD . "avatartest.png"))
		{
			echo "Couldn't copy the avatar";
		}
		if (!copy($icon, e_AVATAR_DEFAULT . "avatartest.png"))
		{
			echo "Couldn't copy the avatar";
		}

		$tests = array(
			0 => array(
				'input'    => array('user_image' => '-upload-avatartest.png'),
				'parms'    => array('w' => 50, 'h' => 50, 'crop' => false),
				'expected' => array(
					"thumb.php?src=%7Be_AVATAR%7Dupload%2Favatartest.png&amp;w=50&amp;h=50",
					"class='img-rounded rounded user-avatar'"
				)
			),
			1 => array(
				'input'    => array('user_image' => 'avatartest.png'),
				'parms'    => array('w' => 50, 'h' => 50, 'crop' => false),
				'expected' => array(
					"thumb.php?src=%7Be_AVATAR%7Ddefault%2Favatartest.png&amp;w=50&amp;h=50",
					"class='img-rounded rounded user-avatar'"
				)
			),
			2 => array(
				'input'    => array('user_image' => ''),
				'parms'    => array('w' => 50, 'h' => 50, 'crop' => false),
				'expected' => array(
					"thumb.php?src=%7Be_IMAGE%7Dgeneric%2Fblank_avatar.jpg&amp;w=50&amp;h=50",
					"class='img-rounded rounded user-avatar'"
				)
			),
			3 => array(
				'input'    => array('user_image' => 'https://mydomain.com/remoteavatar.jpg'),
				'parms'    => array('w' => 50, 'h' => 50, 'crop' => false,),
				'expected' => array(
					"src='https://mydomain.com/remoteavatar.jpg'",
					"class='img-rounded rounded user-avatar'",
					"width='50' height='50'",
				)
			),
			4 => array(
				'input'    => array('user_image' => '', 'user_id' => 1),
				'parms'    => array('w' => 50, 'h' => 50, 'crop' => false, 'link' => true),
				'expected' => array(
					"thumb.php?src=%7Be_IMAGE%7Dgeneric%2Fblank_avatar.jpg&amp;w=50&amp;h=50",
					"class='img-rounded rounded user-avatar'",
					"<a class='e-tip' title=",
					e107::getUrl()->create('user/myprofile/edit')
				)
			),
			5 => array(
				'input'    => array('user_image' => 'avatartest.png'),
				'parms'    => array('w' => 30, 'h' => 20, 'crop' => true, 'shape' => 'rounded'),
				'expected' => array(
					"thumb.php?src=%7Be_AVATAR%7Ddefault%2Favatartest.png&amp;aw=30&amp;ah=20",
					"class='img-rounded user-avatar'"
				)
			),
			6 => array(
				'input'    => array('user_image' => 'avatartest.png'),
				'parms'    => array('w' => 30, 'h' => 30, 'crop' => false, 'shape' => 'circle', 'alt' => 'mytitle'),
				'expected' => array(
					"thumb.php?src=%7Be_AVATAR%7Ddefault%2Favatartest.png&amp;w=30&amp;h=30",
					"class='img-circle rounded-circle user-avatar'",
					'alt="mytitle"',
				)
			),

			7   => array(
				'input'     => array('user_image'=>'avatartest.png'),
				'parms'     => array('w'=>50, 'h'=>50, 'crop'=>true, 'base64'=>true, 'shape'=>'circle'),
				'expected'  => array(
								"src='data:image/png;base64,",
								"class='img-circle rounded-circle user-avatar'"
							)
			),

			8   => array(
				'input'     => array('user_image'=>'https://e107.org/e107_images/generic/blank_avatar.jpg'), // Test remote avatar
				'parms'     => array('w'=>50, 'h'=>50, 'crop'=>true, 'base64'=>true, 'shape'=>'circle'),
				'expected'  => array(
								"src='data:image/jpg;base64,",
								"class='img-circle rounded-circle user-avatar'"
							)
			),


		);

		foreach ($tests as $index => $var)
		{
			$result = $this->tp->toAvatar($var['input'], $var['parms']);
			foreach ($var['expected'] as $str)
			{
				self::assertStringContainsString($str, $result, "Failed on index #" . $index);
			}
		}


	}

	public function testToIcon()
	{
		$icon = codecept_data_dir() . "icon_64.png";

		if (!copy($icon, e_MEDIA_IMAGE . "icon_64.png"))
		{
			echo "Couldn't copy the icon";
		}
		if (!copy($icon, e_MEDIA_ICON . "icon_64.png"))
		{
			echo "Couldn't copy the icon";
		}

		$tests = array(
			0 => array('input' => '{e_IMAGE}e107_icon_32.png', 'parms' => null, 'expected' => '/e107_images/e107_icon_32.png'),
			1 => array('input' => '{e_MEDIA_IMAGE}icon_64.png', 'parms' => null, 'expected' => 'thumb.php?src=e_MEDIA_IMAGE'),
			2 => array('input' => '{e_MEDIA_ICON}icon_64.png', 'parms' => null, 'expected' => '/e107_media/000000test/icons/icon_64.png'),
			3 => array('input' => '{e_PLUGIN}gallery/images/gallery_32.png', 'parms' => null, 'expected' => '/e107_plugins/gallery/images/gallery_32.png'),
			4 => array('input' => 'config_16.png', 'parms' => array('legacy' => "{e_IMAGE}icons/"), 'expected' => '/e107_images/icons/config_16.png'),
		);

		foreach ($tests as $var)
		{
			$result = $this->tp->toIcon($var['input'], $var['parms']);
			self::assertStringContainsString($var['expected'], $result);
		}
	}

	public function testToImage()
	{
		$src = "{e_PLUGIN}gallery/images/butterfly.jpg";
		$this->tp->setThumbSize(80, 80); // set defaults.

		// test with defaults set above.
		$result = $this->tp->toImage($src);
		self::assertStringContainsString('butterfly.jpg&amp;w=80&amp;h=80', $result); // src
		self::assertStringContainsString('butterfly.jpg&amp;w=320&amp;h=320', $result); // srcset 4x the size on small images.

		// test overriding of defaults.
		$override = array('w' => 800, 'h' => 0);
		$result2 = $this->tp->toImage($src, $override);
		self::assertStringContainsString('butterfly.jpg&amp;w=800&amp;h=0', $result2); // src
		self::assertStringContainsString('Fbutterfly.jpg&amp;w=1600&amp;h=0', $result2); // srcset


		$override = array('w' => 0, 'h' => 0); // display image without resizing
		$result3 = $this->tp->toImage($src, $override);
		self::assertStringContainsString('Fbutterfly.jpg&amp;w=0&amp;h=0', $result3); // src

		$result4 = $this->tp->toImage($src, ['loading' => 'lazy']);
		self::assertStringContainsString('loading="lazy"', $result4); // src

		$result5 = $this->tp->toImage($src, ['type' => 'webp']);
		self::assertStringContainsString('&amp;type=webp', $result5); // src

		$result6 = $this->tp->toImage($src, ['return' => 'url']);
		self::assertStringContainsString('http', $result6); // src

		$tests = array(
			0 => array(
				'src'      => '{e_PLUGIN}gallery/images/butterfly.jpg',
				'parms'    => array('w' => 300, 'alt' => "Custom"),
				'expected' => '<img class="img-responsive img-fluid" src="thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&amp;w=300&amp;h=0" alt="Custom" srcset="thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&amp;w=600&amp;h=0 2x" width="300"  />'
			),

		);

		foreach ($tests as $index => $var)
		{
			$result = $this->tp->toImage($var['src'], $var['parms']);
			$result = preg_replace('/"([^"]*)thumb.php/', '"thumb.php', $result);
			self::assertSame($var['expected'], $result);

		}


		// news image scenario with empty value.
		$srcPath = '';
		$imgParms = array(
			'class'         => 'news-image',
			'alt'           => 'placeholder image',
			'style'         => 'display:block',
			'placeholder'   => 1,
			'legacy'        => '{e_IMAGE}newspost_images',
			'w'             => 400,
			'h'             => 325
		);

		$result = $this->tp->toImage($srcPath, $imgParms);
		$expected = '<img class="news-image" src="/thumb.php?src=&amp;w=400&amp;h=325" alt="placeholder image" width="400" height="325" style="display:block"  />';
		self::assertSame($expected, $result);
	}

	public function testThumbSrcSet()
	{
		$src = "{e_PLUGIN}gallery/images/butterfly.jpg";
		$parms = array('w' => 800, 'h' => 0, 'size' => '2x');

		$result = $this->tp->thumbSrcSet($src, $parms);
		self::assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result);

		$this->tp->setThumbSize(80, 80); // set defaults.

		$result2 = $this->tp->thumbSrcSet($src, $parms); // testing overrides
		self::assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result2);

		$result3 = $this->tp->thumbSrcSet($src, array('w' => 800, 'size' => '2x')); // testing overrides without 'h' being set.
		self::assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result3);

		$result4 = $this->tp->thumbSrcSet($src); // no overrides
		self::assertStringContainsString('butterfly.jpg&amp;w=160&amp;h=160', $result4);

	}

	public function testIsBBcode()
	{
		$tests = array(
			0 => array("My Simple Text", false), // input , expected result
			1 => array("<hr />", false),
			2 => array("[b]Bbcode[/b]", true),
			3 => array("<div class='something'>[code]something[/code]</div>", false),
			4 => array("[code]&lt;b&gt;someting&lt;/b&gt;[/code]", true),
			5 => array("[html]something[/html]", false),
			6 => array("http://something.com/index.php?what=ever", false)
		);


		foreach ($tests as $val)
		{
			list($input, $expected) = $val;
			$actual = $this->tp->isBBcode($input);

			self::assertEquals($expected, $actual, $input);
		}

	}

	public function testIsHtml()
	{
		$tests = array(
			0 => array("My Simple Text", false), // input , expected result
			1 => array("<hr />", true),
			2 => array("[b]Bbcode[/b]", false),
			3 => array("<div class='something'>[code]something[/code]</div>", true),
			4 => array("[code]&lt;b&gt;someting&lt;/b&gt;[/code]", false),
			5 => array("[html]something[/html]", true),
			6 => array("http://something.com/index.php?what=ever", false),
			7 => array("< 200", false),
			8 => array("<200>", true),
		);


		foreach ($tests as $val)
		{
			list($input, $expected) = $val;
			$actual = $this->tp->isHtml($input);

			self::assertEquals($expected, $actual, $input);
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
	*/
	public function testIsImage()
	{
		self::assertTrue($this->tp->isImage('/path-to-file/myfile.jpg'));
		self::assertFalse($this->tp->isImage('/path-to-file/myfile.mov'));

	}

	public function testtoAudio()
	{
		$expected = '<audio controls style="max-width:100%" >
<source src="/e107_media/000000test/myfile.mp3" type="audio/mpeg">
Your browser does not support the audio tag.
</audio>';

		$result = $this->tp->toAudio('{e_MEDIA}myfile.mp3');
		self::assertEquals($expected, $result);

		$expected = '<audio controls style="max-width:100%" >
<source src="/e107_media/000000test/myfile.wav" type="audio/wav">
Your browser does not support the audio tag.
</audio>';

		$result = $this->tp->toAudio('{e_MEDIA}myfile.wav');
		self::assertEquals($expected, $result);

		// Override mime.
		$expected = '<audio controls style="max-width:100%" >
<source src="/e107_media/000000test/myfile.php" type="audio/wav">
Your browser does not support the audio tag.
</audio>';

		$result = $this->tp->toAudio('{e_MEDIA}myfile.php', ['mime' => 'audio/wav']);
		self::assertEquals($expected, $result);

	}


	public function testToVideo()
	{
		$tests = [
			0 => [
				'file'      => '{e_MEDIA}myfile.mp4',
				'parms'     =>[],
				'expected'  => '<video width="320" height="240" controls>'
				],

			1 => [
				'file'      => '{e_MEDIA}myfile.mp4',
				'parms'     =>['w'=>500],
				'expected'  => '<video width="500" height="240" controls>'
				],

			2 => [
				'file'      => '{e_MEDIA}myfile.mp4',
				'parms'     =>['w'=>300, 'h'=>0],
				'expected'  => '<video width="300" height="auto" controls>'
				],

			3 => [
				'file'      => '{e_MEDIA}myfile.mp4',
				'parms'     =>['w'=>300, 'h'=>'auto'],
				'expected'  => '<video width="300" height="auto" controls>'
				],
		];

		foreach ($tests as $index => $var)
		{
			$result = $this->tp->toVideo($var['file'], $var['parms']);
			self::assertStringContainsString($var['expected'], $result, 'Failed on index #'.$index);
		}


	}

	public function testMakeClickable()
	{
		$email = 'myemail@somewhere.com.tk';

		$tp = $this->tp;

		// ----

		$result = $tp->makeClickable($email, 'email', array('sub' => '[email]'));

		self::assertStringContainsString('[email]</a>', $result);

		// -----

		$result = $tp->makeClickable($email, 'email', array('sub' => 'fa-envelope.glyph'));
		self::assertStringContainsString("fa-envelope' ></i></a>", $result);

		// links standard.
		$tests = array(
			array("before www.somewhere.com after", 'before <a class="e-url" href="http://www.somewhere.com" >www.somewhere.com</a> after'),
			array("before http://something.com after", 'before <a class="e-url" href="http://something.com" >http://something.com</a> after'),
			array("before https://someplace.com after", 'before <a class="e-url" href="https://someplace.com" >https://someplace.com</a> after'),
			array("before (www.something.com) after", 'before (<a class="e-url" href="http://www.something.com" >www.something.com</a>) after'),
			array('', ''),
		);

		foreach ($tests as $row)
		{
			list($sample, $expected) = $row;
			$result = $tp->makeClickable($sample, 'url');
			self::assertEquals($expected, $result);
		}

		// links with substituion..
		$tests = array(
			array("before www.somewhere.com after", 'before <a class="e-url" href="http://www.somewhere.com" >[link]</a> after'),
			array("before http://something.com after", 'before <a class="e-url" href="http://something.com" >[link]</a> after'),
			array("before https://someplace.com after", 'before <a class="e-url" href="https://someplace.com" >[link]</a> after'),
			array("before (www.something.com) after", 'before (<a class="e-url" href="http://www.something.com" >[link]</a>) after'),
		);

		foreach ($tests as $row)
		{
			list($sample, $expected) = $row;
			$result = $tp->makeClickable($sample, 'url', array('sub' => '[link]'));
			self::assertEquals($expected, $result);
		}

		// links with substituion and target.
		$tests = array(
			array("before www.somewhere.com after", 'before <a class="e-url" href="http://www.somewhere.com" target="_blank">[link]</a> after'),
			array("before http://something.com after", 'before <a class="e-url" href="http://something.com" target="_blank">[link]</a> after'),
			array("before https://someplace.com after", 'before <a class="e-url" href="https://someplace.com" target="_blank">[link]</a> after'),
			array("before (www.something.com) after", 'before (<a class="e-url" href="http://www.something.com" target="_blank">[link]</a>) after'),
		);

		foreach ($tests as $row)
		{
			list($sample, $expected) = $row;
			$result = $tp->makeClickable($sample, 'url', array('sub' => '[link]', 'ext' => true));
			self::assertEquals($expected, $result);
		}


	}


	public function testToDate()
	{


		$class = $this->tp;

		$time = 1519512067; //  Saturday 24 February 2018 - 22:41:07

		$long = $class->toDate($time, 'long');
		self::assertStringContainsString('Saturday 24 February 2018', $long);

		$short = $class->toDate($time, 'short');
		self::assertStringContainsString('Feb 2018', $short);

		$rel = $class->toDate($time, 'relative');
		self::assertStringContainsString('ago', $rel);
		self::assertStringContainsString('data-livestamp="1519512067"', $rel);

		$custom = $class->toDate($time, 'dd-M-yy');
		self::assertStringContainsString('<span>24-Feb-18</span>', $custom);


	}

	/*
			public function testParseBBTags()
			{

			}
	*/
	public function testFilter()
	{
		$url = 'http://www.domain.com/folder/folder2//1234_1_0.jpg';

		// Filter tests.

		$tests = array(
			0 => array('input' => 'test123 xxx', 'mode' => 'w', 'expected' => 'test123xxx'),
			1 => array('input' => 'test123 xxx', 'mode' => 'd', 'expected' => '123'),
			2 => array('input' => 'test123 xxx', 'mode' => 'wd', 'expected' => 'test123xxx'),
			3 => array('input' => 'test123 xxx', 'mode' => 'wds', 'expected' => 'test123 xxx'),
			4 => array('input' => 'test123 xxx.jpg', 'mode' => 'file', 'expected' => 'test123-xxx.jpg'),
			5 => array('input' => '2.1.4 (test)', 'mode' => 'version', 'expected' => '2.1.4'),
			6 => array('input' => $url, 'mode' => 'url', 'expected' => $url),
			7 => array('input' => array('1', 'xxx'), 'mode' => 'str', 'expected' => array('1', 'xxx')),
			8 => array('input' => 'myemail@email.com', 'mode' => 'email', 'expected' => 'myemail@email.com'),
		);

		foreach ($tests as $index => $var)
		{
			$result = $this->tp->filter($var['input'], $var['mode']);
			self::assertEquals($var['expected'], $result, "Failed on index: " . $index);
		}

		// Validate.

		$tests2 = array(
			0 => array('input' => 'http://www.domain.com/folder/file.zip', 'mode' => 'url'), // good url
			1 => array('input' => 'http:/www.domain.com/folder/file.zip', 'mode' => 'url'), // bad url
			2 => array('input' => array('1', 'xxx'), 'mode' => 'int'), // good and bad integer
			3 => array('input' => 'myemail@email.com', 'mode' => 'email'), // good email
			4 => array('input' => 'bad-email.com', 'mode' => 'email'), // bad email
			5 => array('input' => '123.23.123.125', 'mode' => 'ip'), // good ip
			6 => array('input' => 'xx.23.123.125', 'mode' => 'ip'), // bad ip
		);

		$expected2 = array(
			0 => 'http://www.domain.com/folder/file.zip',
			1 => false,
			2 => array(1, false),
			3 => 'myemail@email.com',
			4 => false,
			5 => '123.23.123.125',
			6 => false,
		);

		//	$ret = [];
		foreach ($tests2 as $index => $var)
		{
			$result = $this->tp->filter($var['input'], $var['mode'], true);
			//	$ret[$index] = $result;
			self::assertSame($expected2[$index], $result);
		}

	}

	/**
	 * e107 v0.6.0 requires strings to be passed around with quotation marks escaped for HTML as a way to prevent
	 * both SQL injection and cross-site scripting. Although {@see e_parse::toDB()} is supposed to do that, some
	 * usages, specifically {@see e_front_model::sanitizeValue()} call {@see e_parse::filter()} instead.
	 *
	 * @version 2.3.1
	 */
	public function testFilterStr()
	{
		$input = "<strong>\"e107's\"</strong>";
		$expected = "&quot;e107&#039;s&quot;";

		$actual = $this->tp->filter($input, 'str');

		self::assertEquals($expected, $actual);
	}

	public function testCleanHtml()
	{
		global $_E107;
		$_E107['phpunit'] = true; // disable CLI "all access" permissions to simulated a non-cli scenario.

		$this->tp->setScriptAccess(e_UC_NOBODY);

		$tests = array(
			0  => array(
				'html'     => "<svg/onload=prompt(1)//",
				'expected' => '&lt;svg/onload=prompt(1)//'
			),
			//	1   => array('html' => '<script>alert(123)</script>', 'expected'=>''),
			//	2   => array('html' => '"><script>alert(123)</script>', 'expected'=>'"&gt;'),
			3  => array(
				'html'     => '< 200',
				'expected' => '&lt; 200'
			),
			4  => array(
				'html'     => "<code>function sc_my_shortcode(){\nreturn \"Something\";}</code>",
				'expected' => "<code>function sc_my_shortcode()&#123;\nreturn \"Something\";&#125;</code>"
			),
			5  => array(
				'html'     => "<pre class=\"prettyprint linenums\">function sc_my_shortcode(){\nreturn \"Something\";}</pre>",
				'expected' => "<pre class=\"prettyprint linenums\">function sc_my_shortcode()&#123;\nreturn \"Something\";&#125;</pre>"
			),
			6  => array(
				'html'     => '<img src="{e_BASE}image.jpg" alt="">',
				'expected' => '<img src="{e_BASE}image.jpg" alt="">'
			),
			7  => array( // with <br> inside <pre> ie. TinyMce
				'html'     => '<pre class="whatever">require_once("class2.php");<br>require_once(HEADERF);<br>echo "test";&lt;br&gt;<br>require_once(FOOTERF);</pre>',
				'expected' => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>"
			),
			8  => array( // with \n
				'html'     => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>",
				'expected' => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>"
			),
			9  => array( // with \r\n (windows) line-breaks.
				'html'     => "<pre class=\"whatever\">require_once(\"class2.php\");\r\nrequire_once(HEADERF);\r\necho \"test\";&lt;br&gt;\r\nrequire_once(FOOTERF);</pre>",
				'expected' => "<pre class=\"whatever\">require_once(\"class2.php\");\nrequire_once(HEADERF);\necho \"test\";&lt;br&gt;\nrequire_once(FOOTERF);</pre>"
			),
			10 => array(
				'html'     => '<a href="#" onchange="whatever">Test</a>',
				'expected' => '<a href="#">Test</a>'
			),

			11 => array(
				'html'     => "<pre>{THEME_PREF: code=header_width&default=container}</pre>",
				'expected' => "<pre>&#123;THEME_PREF: code=header_width&amp;default=container&#125;</pre>",
			),

			12 => array(
				'html'     => "<pre>/* {THEME_PREF: code=header_width&default=container} */</pre>",
				'expected' => "<pre>/* &#123;THEME_PREF: code=header_width&amp;default=container&#125; */</pre>",
			),

			13 => array(
				'html'     => '<div class="video-responsive"><div class="video-responsive"><video width="320" height="240" controls="controls"><source src="e107_media/xxxxx5/videos/2018-07/SampleVideo.mp4" type="video/mp4">Your browser does not support the video tag.</video></div></div>',
				'expected' => '<div class="video-responsive"><div class="video-responsive"><video width="320" height="240" controls="controls"><source src="e107_media/xxxxx5/videos/2018-07/SampleVideo.mp4" type="video/mp4">Your browser does not support the video tag.</source></video></div></div>'
			),
			14 => array(
				'html'     => '<script>alert(1)</script>', // test removal of 'script' tags
				'expected' => ''
			),

			15 => array(
				'html'     => '<iframe width="640" height="360" frameborder="0" allowfullscreen src="http://nowhere.com" this-attribute-should-be-removed="value1" this-attribute-should-also-be-removed="value2"></iframe>',
				'expected' => '<iframe width="640" height="360" frameborder="0" allowfullscreen="" src="http://nowhere.com"></iframe>'

			),
			// BC Compat.
			16 => array(
				'html'     => '<table border="1" cellpadding="5" cellspacing="7"><tr><td></td></tr></table>',
				'expected' => '<table border="1" cellpadding="5" cellspacing="7"><tr><td></td></tr></table>',
			),
			// BC Compat.
			17 => array(
				'html'     => '<td name="G" bgcolor="#660000">colored</td>',
				'expected' => '<td name="G" bgcolor="#660000">colored</td>',
			),

			18 => array(
				'html'     => 'Τη γλώσσα μου έδωσαν ελληνική 您好，世界 こんにちは、世界',
				'expected' => 'Τη γλώσσα μου έδωσαν ελληνική 您好，世界 こんにちは、世界',
			),

		);


		foreach ($tests as $var)
		{
			$result = $this->tp->cleanHtml($var['html']);
			self::assertEquals($var['expected'], $result);
		}

		// ----------- Test with Script access enabled --------------


		$this->tp->setScriptAccess(e_UC_PUBLIC);

		$scriptAccess = array(
			0 => array(
				'html'     => '<a href="#" onchange="whatever">Test</a>',
				'expected' => '<a href="#" onchange="whatever">Test</a>'
			),
			1 => array(
				'html'     => '<script>alert(1)</script>', // test support for 'script' tags
				'expected' => '<script>alert(1)</script>'
			)
		);

		foreach ($scriptAccess as $var)
		{
			$result = $this->tp->cleanHtml($var['html']);
			self::assertEquals($var['expected'], $result);
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
