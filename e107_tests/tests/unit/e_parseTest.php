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
	use \Test\CorePrefs;
	use \Test\BootedCli;

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
			$this->assertTrue(false, "Couldn't load e_parser object");
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
		$this->assertSame($expected, $result);

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

			$this->assertEquals($var['expected'], $result);
		}


	}


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

		$this->assertEquals($expected, $actual, "BBcode parsing failed");


		$src = "[center][img]{e_IMAGE}generic/blank_avatar.jpg[/img][/center]";

		$actual = $this->tp->toHTML($src, true);

		$expected = "<div class='bbcode-center' style='text-align:center'><img src='" . e_HTTP . "e107_images/generic/blank_avatar.jpg' alt='Blank Avatar' title='Blank Avatar' class='img-rounded rounded bbcode bbcode-img'  /></div>";

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

		$actual = $this->tp->toHTML($src, true);
		$this->assertEquals($expected, $actual);

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

	/**
	 * The text inside an iframe, which a browser never shows, cannot become markup, a script block or a bbcode.
	 */
	public function testIframeTextStaysText()
	{
		$storedAsWritten = array(
			'<iframe><script ></iframe><p>&lt;b id=&quot;inside&quot; onclick=&quot;x()&quot;&gt; after</p>',
			'<iframe><script ></iframe><p>&lt;b id=&quot;inside&quot; onclick=&quot;x()&quot;&gt; after</p><iframe></script></iframe>',
			'<iframe></iframex><script ></iframe><p>&lt;b id=&quot;inside&quot;&gt; after</p>',
			'<iframe>[html]<b id="inside">x</b>[/html]</iframe><p>after</p>',
		);

		foreach($storedAsWritten as $stored)
		{
			$html = $this->tp->toHTML($stored, true, 'BODY');

			$this->assertStringNotContainsString('<b id=', $html, $stored);
			$this->assertStringContainsString('after', $html, $stored);
		}

		$embed = '<iframe src="https://www.example.com/" width="560" height="315"></iframe>';
		$this->assertSame($embed, $this->tp->toHTML($embed, true, 'BODY'));
	}

	/**
	 * Markup written inside a processing instruction meets the cleaner like any other markup.
	 */
	public function testAProcessingInstructionCarriesNothingPastTheCleaner()
	{
		foreach(array('<?x', "<\r?x") as $start)
		{
			$html = $this->tp->toHTML($this->tp->toDB('<p>Before'.$start.' <a id="inside" href="javascript:x()">L</a>?> after</p>'), true, 'BODY');

			$this->assertStringNotContainsString('javascript:', $html, $start);
			$this->assertStringContainsString('after', $html, $start);
		}
	}

	/**
	 * An HTML comment in stored text stays one comment, whatever its text holds.
	 */
	public function testAnHtmlCommentStaysOneComment()
	{
		$bodies = array(
			'<p>Before<!-- --&gt;<b id="inside">x</b> --> after</p>',
			'<p>Before<!-- --!&gt;<b id="inside">x</b> --> after</p>',
			'<p>Before<!-- [html]<b id="inside">x</b>[/html] --> after</p>',
			'<p>Before<!-- [block][/block]<b id="inside">x</b> --> after</p>',
			'<noscript><!-- </noscript><b id="inside">x</b> --></noscript><p>after</p>',
			'<iframe><!-- </iframe><b id="inside">x</b> --></iframe><p>after</p>',
			'<p>Before<!--><b id="inside">x</b>--> after</p>',
			'<p>Before<!---><b id="inside">x</b>--> after</p>',
		);

		foreach($bodies as $body)
		{
			foreach(array($this->tp->toDB($body), $body) as $stored)
			{
				$html = $this->tp->toHTML($stored, true, 'BODY');

				$this->assertStringNotContainsString('<b id=', $html, $stored);
				$this->assertStringContainsString('after', $html, $stored);
			}
		}

		$storedAsWritten = array(
			'<p>Before<!-- --!><b id="inside">x</b> --> after</p>',
			'<iframe><style ></iframe><p>Before<!-- [html]<b id="inside">x</b>[/html] --> after</p>',
			'<iframe></iframex><style ></iframe><p>Before<!-- [html]<b id="inside">x</b>[/html] --> after</p>',
			"<iframe></iframe\x0b><style ></iframe><p>Before<!-- [html]<b id=\"inside\">x</b>[/html] --> after</p>",
		);

		foreach($storedAsWritten as $stored)
		{
			$html = $this->tp->toHTML($stored, true, 'BODY');

			$this->assertStringNotContainsString('<b id=', $html, $stored);
			$this->assertStringNotContainsString('--!>', $html, $stored);
			$this->assertStringContainsString('after', $html, $stored);
		}

		$long = $this->tp->toHTML('<p>Before</p><!-- '.str_repeat('a', 1100000).' --><p>After</p>', true, 'BODY');
		$this->assertStringContainsString('<p>After</p>', $long);
	}

	public function testToHTMLStripsTheBlockBbcodesAStripListNames()
	{
		foreach (array('table', 'html', 'php', 'code', 'scode', 'hide') as $code)
		{
			self::assertSame('kept text', $this->tp->toHTML('['.$code.']kept text[/'.$code.']', $code), $code);
		}

		self::assertSame(1, preg_match('#^<pre [^>]*>kept text</pre>$#', $this->tp->toHTML('[code]kept text[/code]', 'table')));
		self::assertSame('after', $this->tp->toHTML('[php]echo 1;[/php] after', 'TITLE'));
	}

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
			$this->assertSame($var['expected'], $result);
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
			$this->assertSame($tests[$mod]['expected'], $result, $mod . " didn't match the expected result.");
			//	$ret[$mod] = $result;

		}


		//	e107::getConfig()->set('make_clickable', 0)->save(false, true);
		//	var_export($ret);

	}


	public function testAWholeFieldPrintsOnlyALanguageTermItNames()
	{
		self::assertSame('e_ROOT', $this->tp->toHTML('e_ROOT', false, 'TITLE'), 'A title that names a server path constant printed its value');
		self::assertSame('e_SYSTEM', $this->tp->toHTML('e_SYSTEM', false, 'DESCRIPTION'));
		self::assertSame(LAN_EDIT, $this->tp->toHTML('LAN_EDIT', false, 'TITLE'));
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

			$this->assertEquals($var['expected'], $result, 'Test #' . $index . ' failed.');
		}


	}

	/**
	 * A quote a bbcode handler encodes inside a [table] block must not be decoded back into the markup the handler built.
	 */
	public function testToHTMLDoesNotDecodeHandlerQuotesInsideTable()
	{
		$stored = $this->tp->toDB("[table][img]x.png' tabindex='0' autofocus onfocus='window.vanny=1[/img][/table]");

		foreach (array('', 'USER_BODY', 'USER_TITLE', 'BODY', 'TITLE', 'WYSIWYG') as $modifiers)
		{
			$result = $this->tp->toHTML($stored, true, $modifiers);
			$context = "modifiers '$modifiers'";

			$this->assertStringNotContainsString("onfocus='window.vanny=1", $result, "the handler quote must stay encoded with $context");
			$this->assertStringContainsString("x.png&#039;", $result, "the encoded quote must survive rendering with $context");
		}
	}

	/**
	 * An [html] block whose tags differ from the exact lowercase pair toDB() strips must keep a handler's quote encoded.
	 */
	public function testToHTMLDoesNotDecodeHandlerQuotesInsideHtmlBlock()
	{
		$stored = '[html ][img]x.png&#039; tabindex=&#039;0&#039; autofocus onfocus=&#039;window.vanny=1[/img][/HTML]';

		foreach (array('', 'USER_BODY') as $modifiers)
		{
			$result = $this->tp->toHTML($stored, true, $modifiers);
			$context = "modifiers '$modifiers'";

			$this->assertStringNotContainsString("onfocus='window.vanny=1", $result, "the handler quote must stay encoded with $context");
			$this->assertStringContainsString("x.png&#039;", $result, "the encoded quote must survive rendering with $context");
		}
	}

	/**
	 * Bbcode inside a [table] still renders, a link and a [nobr] among it.
	 */
	public function testToHTMLStillRendersBbcodeInsideTable()
	{
		$result = $this->tp->toHTML($this->tp->toDB('[table][tr][td][b]bold[/b] [url=https://example.com/a?b=1]Read the notes[/url] [nobr]keep together[/nobr][/td][/tr][/table]'), true, 'USER_BODY');

		self::assertStringContainsString('<table', $result);
		self::assertStringContainsString('>bold</strong>', $result);
		self::assertStringContainsString('href="https://example.com/a?b=1"', $result);
		self::assertStringContainsString('keep together', $result);
		self::assertStringNotContainsString('[nobr]', $result);
	}

	/**
	 * A [code] block shows its source as typed, alone or inside a [table], so the entity table must not run over it.
	 */
	public function testToHTMLLeavesCodeBlockSourceUnrestored()
	{
		$standalone = $this->tp->toHTML($this->tp->toDB('[code]img.onerror = handler;[/code]'), true, 'USER_BODY');
		$nested = $this->tp->toHTML($this->tp->toDB('[table][tr][td][code]img.onerror = handler;[/code][/td][/tr][/table]'), true, 'USER_BODY');
		$mixed = $this->tp->toHTML($this->tp->toDB('[table][tr][td][code]write [scode]x[/scode] for img.onerror[/code][/td][/tr][/table]'), true, 'USER_BODY');

		$this->assertStringContainsString('img.onerror = handler;', $standalone);
		$this->assertStringNotContainsString('one&lt;i&gt;', $nested);
		$this->assertStringNotContainsString('one&lt;i&gt;', $mixed);
	}

	/**
	 * An author's <script> inside an [html] or [table] block keeps its source, as it does outside one.
	 */
	public function testToHTMLLeavesScriptBlocksInsideBlocksUnrestored()
	{
		$script = '<script type=&quot;text/javascript&quot;>i.onerror = f;</script>';

		foreach (array('[html]<p>s</p>' . $script . '[/html]', '[table][tr][td]x[/td][/tr]' . $script . '[/table]') as $stored)
		{
			$this->assertStringContainsString('i.onerror = f;', $this->tp->toHTML($stored, true, 'BODY'), $stored);
		}

		$bare = '[html]<script>var x = 1;</script><p><a href=&quot;/page.php&quot;>link</a></p>[/html]';

		$this->assertStringContainsString('<a href="/page.php">', $this->tp->toHTML($bare, true, 'BODY'));

		$adjacent = '[html]<script type=&quot;a&quot;>var a = 1;</script><script>i.onerror = f;</script><p>t</p>[/html]';
		$upper = '[html]<SCRIPT LANGUAGE=&quot;JavaScript&quot;>var s = &quot;x&quot;;</SCRIPT>[/html]';

		$this->assertStringContainsString('i.onerror = f;', $this->tp->toHTML($adjacent, true, 'BODY'));
		$this->assertStringContainsString('var s = "x";', $this->tp->toHTML($upper, true, 'BODY'));
	}

	/**
	 * The same reorder must leave legible quotes in table cell text decoded for display.
	 */
	public function testToHTMLKeepsDecodedQuotesInTableCellText()
	{
		$stored = $this->tp->toDB('[table][tr][td]Tom\'s "weekly" notes[/td][/tr][/table]');

		$result = $this->tp->toHTML($stored, true);

		$this->assertStringContainsString('<td>Tom\'s "weekly" notes</td>', $result);
	}

	/**
	 * A tag inside an [scode] block keeps the quotes around its attribute values, alone or inside a block.
	 *
	 * @dataProvider scodeWrapProvider
	 */
	public function testToHTMLRestoresQuotesInsideScodeBlock($wrap)
	{
		$tag = '<b title="x onfocus=print()//">B</b>';
		$stored = $this->tp->toDB(sprintf($wrap, '[scode]' . $tag . '[/scode]'));

		foreach (array('', 'USER_BODY', 'BODY', 'WYSIWYG') as $modifiers)
		{
			self::assertStringContainsString($tag, $this->tp->toHTML($stored, true, $modifiers), "$stored with modifiers '$modifiers'");
		}
	}

	public function scodeWrapProvider()
	{
		return array(
			'alone'        => array('%s'),
			'inside html'  => array('[html]<p>%s</p>[/html]'),
			'inside table' => array('[table][tr][td]%s[/td][/tr][/table]'),
		);
	}

	/**
	 * A [code] block inside an [scode] block still shows its source as typed.
	 */
	public function testToHTMLLeavesCodeInsideScodeUnrestored()
	{
		$result = $this->tp->toHTML($this->tp->toDB('[scode]see [code]img.onerror = handler;[/code][/scode]'), true, 'USER_BODY');

		self::assertStringContainsString('img.onerror = handler;', $result);
		self::assertStringContainsString('&lt;b title=&quot;x&quot;&gt;B&lt;/b&gt;', $this->tp->toHTML($this->tp->toDB('[scode]see [code]<b title="x">B</b>[/code][/scode]'), true, 'USER_BODY'));
	}

	/**
	 * No tag leaves toHTML() with an attribute value quoted by an entity, which a browser reads as unquoted.
	 *
	 * @dataProvider entityQuotedTagProvider
	 */
	public function testToHTMLLeavesNoAttributeQuotedByAnEntity($typed)
	{
		$stored = $this->tp->toDB($typed);

		foreach (array('', 'USER_BODY', 'BODY', 'WYSIWYG') as $modifiers)
		{
			$result = $this->tp->toHTML($stored, true, $modifiers);

			self::assertSame(0, preg_match('#<[a-z][^<>]*=\s*&quot;#i', $result), "$stored with modifiers '$modifiers' gave $result");
		}
	}

	public function entityQuotedTagProvider()
	{
		$tag = '<b title="x onfocus=print()//">B</b>';

		return array(
			'scode'                                        => array("[scode]{$tag}[/scode]"),
			'scode inside html'                            => array("[html]<p>[scode]{$tag}[/scode]</p>[/html]"),
			'scode inside table'                           => array("[table][tr][td][scode]{$tag}[/scode][/td][/tr][/table]"),
			'code opener broken by a line'                 => array("[code\n]{$tag}[/code]"),
			'code opener broken by a line, in scode'       => array("[scode][code\n]{$tag}[/code][/scode]"),
			'code opener broken by a line, in table'       => array("[table][tr][td][code\n]{$tag}[/code][/td][/tr][/table]"),
			'code opener reaching its closer, in scode'    => array("[scode][code {$tag} [/code][/scode]"),
			'code opener reaching its closer, in html'     => array("[html]<p>[code {$tag} [/code]</p>[/html]"),
			'longer word than code'                        => array("[codex]{$tag}[/code]"),
			'numbered code closed without its number'      => array("[code2]{$tag}[/code]"),
			'code taken into an earlier bracket, in scode' => array("[scode][b [code]{$tag}[/code][/scode]"),
			'code taken into an earlier bracket, in html'  => array("[html]<p>[b [code]{$tag}[/code]</p>[/html]"),
		);
	}

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
		$shortcodeObject = e107::getScBatch('online', true);

		$expected = "<a href=''>lost</a>";
		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false, $shortcodeObject);
		$this->assertEquals($expected, $result);

		e107::getPlugin()->uninstall('online');
		$sc = e107::getScParser();
		$sc->__construct();
		//	$sc->resetscClass('online', null);

		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false);
		$this->assertEmpty($result);

		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
		$this->assertEmpty($result, "{ONLINE_MEMBER_PAGE} wasn't empty: " . $result);

		$shortcodeObject = e107::getScBatch('online', true);

		$expected = "<a href=''>lost</a>";
		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', false, $shortcodeObject);
		$this->assertEquals($expected, $result);

		$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
		$this->assertEmpty($result);

		//	$result = $this->tp->parseTemplate('{ONLINE_MEMBER_PAGE}', true);
		//	$this->assertEquals($expected, $result);
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

		$this->assertSame($expected, $actual);
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

		$this->assertSame($expected, $actual);
	}

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


		$actual = $this->tp->toForm("[html]Something &quot;hi&quot;[/html]");
		$this->assertSame('[html]Something "hi"[/html]', $actual);

	}

	public function testToASCII()
	{

		$array = array(
			array('input' => 'ľ, ú, ŕ, ô, ť', 'expected' => 'l, u, r, o, t'),
		);

		foreach ($array as $arr)
		{
			$result = $this->tp->toASCII($arr['input']);
			$this->assertEquals($arr['expected'], $result);
		}


	}

	public function testToNumber()
	{
		$result = $this->tp->toNumber('v2a');
		$this->assertSame(2, $result);

		$result = $this->tp->toNumber('v1.5');
		$this->assertSame(1.5, $result);


		$result = $this->tp->toNumber('v3.5');
		$this->assertSame(3.5, $result);
	}
	
	public function testMultibyteOn()
	{

		// enable multibyte mode.
		$this->tp->setMultibyte(true);

		$input = "русские";

		// strtoupper
		$result = $this->tp->ustrtoupper($input);
		$this->assertEquals('РУССКИЕ', $result);

		// strlen
		$result = $this->tp->ustrlen($input);
		$this->assertEquals(7, $result);

		// strtolower
		$result = $this->tp->ustrtolower('РУССКИЕ');
		$this->assertEquals($input, $result);

		// strpos
		$result = $this->tp->ustrpos($input, 'и');
		$this->assertEquals(5, $result);

		// substr
		$result = $this->tp->usubstr($input, 0, 5);
		$this->assertEquals('русск', $result);

		// stristr
		$result = $this->tp->ustristr($input, 'ские', true);
		$this->assertEquals('рус', $result);

		// strrpos (last occurance of a string)
		$result = $this->tp->ustrrpos($input, 'с');
		$this->assertEquals(3, $result);

		$this->tp->setMultibyte(false); // disable after test.

	}

	public function testMultibyteOff()
	{

		// enable multibyte mode.
		$this->tp->setMultibyte(false);

		$input = "an example of text";

		// strtoupper
		$result = $this->tp->ustrtoupper($input);
		$this->assertEquals('AN EXAMPLE OF TEXT', $result);

		// strlen
		$result = $this->tp->ustrlen($input);
		$this->assertEquals(18, $result);

		// strtolower
		$result = $this->tp->ustrtolower('AN EXAMPLE OF TEXT');
		$this->assertEquals($input, $result);

		// strpos
		$result = $this->tp->ustrpos($input, 't');
		$this->assertEquals(14, $result);

		// substr
		$result = $this->tp->usubstr($input, 0, 5);
		$this->assertEquals('an ex', $result);

		// stristr
		$result = $this->tp->ustristr($input, 'of', true);
		$this->assertEquals('an example ', $result);

		// strrpos (last occurance of a string)
		$result = $this->tp->ustrrpos($input, 'e');
		$this->assertEquals(15, $result);


	}

	public function testUsubstrWithoutLengthReturnsRestOfString()
	{

		foreach (array(false, true) as $multibyte)
		{
			$this->tp->setMultibyte($multibyte);

			self::assertSame('world', $this->tp->usubstr('hello world', 6));
			self::assertSame('rld', $this->tp->usubstr('hello world', -3));
			self::assertSame('hello world', $this->tp->usubstr('hello world', 0));
		}

	}

	public function testUsubstrPastTheEndReturnsEmptyString()
	{

		foreach (array(false, true) as $multibyte)
		{
			$this->tp->setMultibyte($multibyte);

			self::assertSame('', $this->tp->usubstr('hello', 10));
			self::assertSame('', $this->tp->usubstr('hello', 10, 2));
		}

	}

	/**
	 * A quote inside an attribute value stays inside it on its way through toDB() and toHTML().
	 */
	public function testAQuoteInsideAnAttributeValueStaysInIt()
	{
		$typed = array(
			'<b title="q&quot; x=&#039;y">T</b>'        => 'q" x=\'y',
			'<b title="&amp;quot; x=&amp;quot;y">T</b>' => '" x="y',
			'<b title=\'q" x\'>T</b>'                     => 'q" x',
			'<b title="it\'s">T</b>'                      => "it's",
			'[html]<p><b title="q&quot; x=&#039;y">T</b></p>[/html]'                  => 'q" x=\'y',
			'[table][tr][td]<b title="q&quot; x=&#039;y">T</b>[/td][/tr][/table]' => 'q" x=\'y',
		);

		foreach($typed as $html => $title)
		{
			$doc = new DOMDocument();
			$errors = libxml_use_internal_errors(true);
			$doc->loadHTML('<!DOCTYPE html><html><body>'.$this->tp->toHTML($this->tp->toDB($html), true, 'BODY').'</body></html>');
			libxml_clear_errors();
			libxml_use_internal_errors($errors);

			$b = $doc->getElementsByTagName('b')->item(0);
			$this->assertSame(1, $b->attributes->length, $html);
			$this->assertSame($title, $b->getAttribute('title'), $html);
		}
	}

	/**
	 * Given an author, toDB() keeps that author's HTML but no script they may not post, whatever the current user may (the phpunit flag turns off check_class()'s command-line grant).
	 */
	public function testToDBKeepsNoScriptAnAuthorMayNotPost()
	{
		$config = e107::getConfig();
		$prefs = array('post_html' => $config->get('post_html'), 'post_script' => $config->get('post_script'));
		$phpunit = isset($GLOBALS['_E107']['phpunit']) ? $GLOBALS['_E107']['phpunit'] : null;
		$config->set('post_html', e_UC_ADMIN)->set('post_script', e_UC_MAINADMIN);
		$GLOBALS['_E107']['phpunit'] = true;
		$sql = e107::getDb();
		$author = (int) $sql->insert('user', array(
			'user_name' => 'todb_author', 'user_loginname' => 'todb_author', 'user_password' => '', 'user_email' => 'todb_author@example.com',
			'user_join' => time(), 'user_class' => '', 'user_admin' => 1, 'user_perms' => '',
			'user_prefs' => '', 'user_signature' => '', 'user_realm' => '', 'user_xup' => '',
		));
		$this->tp->setScriptAccess(e_UC_PUBLIC);

		try
		{
			$stored = $this->tp->toDB('<b onclick="x()">y</b>', false, false, false, $author);
		}
		finally
		{
			$sql->delete('user', 'user_id = '.$author);
			$config->set('post_html', $prefs['post_html'])->set('post_script', $prefs['post_script']);
			$GLOBALS['_E107']['phpunit'] = $phpunit;
		}

		self::assertStringContainsString('<b>y</b>', $stored);
		self::assertStringNotContainsString('onclick', $stored);
	}

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
			$this->assertSame($var['expected'], $result, 'Test #' . $k . " failed." . print_r($this->tp->getRemoved(), true));

		}


	}

	public function testToDBEncodesTheAmpersandOfAnAuthorWithoutHtmlOnce()
	{
		global $_E107;
		$_E107['phpunit'] = true;
		$restore = $this->withCorePrefs(array('post_html' => e_UC_NOBODY));
		$inputs = array(
			'plain'             => 'Tom & Jerry',
			'typed entity'      => 'Tom &amp; Jerry',
			'code'              => '[code]if (a && b) {}[/code]',
			'code reference'    => '[code]it&#x27;s[/code]',
			'numeric reference' => 'x&amp;#38;y',
		);
		$stored = array();

		try
		{
			foreach ($inputs as $name => $input)
			{
				$saved = $this->tp->toDB($input);
				$stored[$name] = array($saved, $this->tp->toDB(html_entity_decode($this->tp->toForm($saved), ENT_QUOTES, 'UTF-8')));
			}
		}
		finally
		{
			$restore();
			unset($_E107['phpunit']);
		}

		self::assertSame(array(
			'plain'             => array('Tom &amp; Jerry', 'Tom &amp; Jerry'),
			'typed entity'      => array('Tom &amp; Jerry', 'Tom &amp; Jerry'),
			'code'              => array('[code]if (a &amp;&amp; b) {}[/code]', '[code]if (a &amp;&amp; b) {}[/code]'),
			'code reference'    => array('[code]it&amp;#x27;s[/code]', '[code]it&amp;#x27;s[/code]'),
			'numeric reference' => array('x&amp;amp;#38;y', 'x&amp;amp;#38;y'),
		), $stored);
	}

	public function testPostToForm()
	{
		$text = "<div class='something'>My Test</div>";
		$expected = '&lt;div class=&#039;something&#039;&gt;My Test&lt;/div&gt;';
		$result = $this->tp->post_toForm($text);
		$this->assertSame($expected, $result);

		$array = array($text);
		$arrayExp = array($expected);
		$result = $this->tp->post_toForm($array);
		$this->assertSame($arrayExp, $result);


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

			$this->assertSame($var['expected'], $result, "Failed on test #" . $index);
		}


	}

	/**
	 * An inexact cut walks back to the last space, which some strings have none of and others have only at the front ({@see e_parse::html_truncate()}).
	 */
	public function testHtmlTruncateKeepsAStringWithNoSpaceToWalkBackTo()
	{
		self::assertSame(
			str_repeat('a', 20) . '...',
			$this->tp->html_truncate(str_repeat('a', 50), 20, '...', false)
		);

		self::assertSame(
			'<b>' . str_repeat('a', 20) . '...</b>',
			$this->tp->html_truncate('<b>' . str_repeat('a', 50) . '</b>', 20, '...', false)
		);

		self::assertSame(
			' ' . str_repeat('a', 19) . '...',
			$this->tp->html_truncate(' ' . str_repeat('a', 50), 20, '...', false)
		);

		self::assertSame(
			'a...',
			$this->tp->html_truncate('a ' . str_repeat('b', 50), 20, '...', false)
		);
	}

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
			3 => array(
				'path'  => '{e_DOWNLOAD}x.zip',
				'type'  => null,
				'match' => e_DOWNLOAD.'x.zip',
			),
			4 => array(
				'path'  => '{e_HANDLER}e_parse_class.php',
				'type'  => null,
				'match' => e_HANDLER.'e_parse_class.php',
			),
		);


		foreach ($tests as $var)
		{
			$actual = $this->tp->replaceConstants($var['path'], $var['type']);
			$this->assertStringContainsString($var['match'], $actual);
		}

	}

	public function testHtmlwrapKeepsATagWhoseAttributeQuoteIsNeverClosed()
	{
		$tag = '<a href="' . str_repeat('a', 40) . '>';

		self::assertStringContainsString($tag, $this->tp->htmlwrap($tag . ' tail', 20),
			'htmlwrap() ends a tag at the first > and leaves it alone, where makeClickable() reads the '
			. 'quotes and keeps scanning. The two splitters cannot be shared: the stricter one makes this '
			. 'a run of text and writes a break character into the attribute.');
	}

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

			$this->assertTrue($valid);
		}


		// Test with $tags = false;
		$html = '<div class="something">One & Two < and > " or \'</div>';
		$result = $this->tp->toRss($html);
		$this->assertSame("One &amp; Two &lt; and &gt; \" or '", $result);
		$valid = $this->isValidXML('<tag>' . $result . '</tag>');
		$this->assertTrue($valid);


	}

	/**
	 * With no WYSIWYG editor on the page, toForm() decodes a stored %3C, %3E or %27 to its entity, so it comes out as text, and leaves a stored ' as it is.
	 */
	public function testToFormKeepsPercentEscapedMarkupAsText()
	{
		$editor = e107::wysiwyg(null, true);
		e107::wysiwyg(false);

		try
		{
			$result = $this->tp->toForm('Look %3C/textarea%3E%3cimg src=x%3E %27q%27 it\'s <b> a+b');
		}
		finally
		{
			e107::wysiwyg($editor);
		}

		self::assertSame('Look &lt;/textarea&gt;&lt;img src=x&gt; &#039;q&#039; it\'s &lt;b&gt; a+b', $result);
	}

	/**
	 * toDB() leaves no [html] marker in a member's text, however deeply nested (the phpunit flag turns off check_class()'s command-line grant).
	 */
	public function testToDBLeavesNoHtmlMarkerInAMembersText()
	{
		$config = e107::getConfig();
		$postHtml = $config->get('post_html');
		$phpunit = isset($GLOBALS['_E107']['phpunit']) ? $GLOBALS['_E107']['phpunit'] : null;
		$config->set('post_html', e_UC_NOBODY);
		$GLOBALS['_E107']['phpunit'] = true;

		try
		{
			$stored = $this->tp->toDB('[[html]html]&lt;b&gt;bold&lt;/b&gt;[[/html]/html] and '.str_repeat('[', 300).str_repeat('html]', 300));
		}
		finally
		{
			$config->set('post_html', $postHtml);
			$GLOBALS['_E107']['phpunit'] = $phpunit;
		}

		self::assertStringContainsString('&lt;b&gt;bold&lt;/b&gt;', $stored);
		self::assertStringNotContainsString('[html]', $stored);
		self::assertStringNotContainsString('[/html]', $stored);
	}

	/**
	 * toEmail() restores an administrator's stored attribute quotes but adds no element and no attribute to a member's text.
	 */
	public function testToEmailAddsNoElementOrAttribute()
	{
		$result = $this->tp->toEmail('[html]<!-- it\'s --><!--&#45;><b>x</b>--><p class=&quot;x&quot;>&lt;img src=x onerror=alert(1)&gt; &#60;b&#62; &#x3C;i&#x3E; &amp; &copy;</p><a href="http://x&quot; onmouseover=&quot;alert(1)">y</a>[/html]');

		self::assertSame('<!-- it\'s --><!--&#45;><b>x</b>--><p class="x">&lt;img src=x onerror=alert(1)&gt; &#60;b&#62; &#x3C;i&#x3E; & ©</p><a href="http://x&quot; onmouseover=&quot;alert(1)">y</a>', $result);
	}

	/**
	 * toRss() prints no tag from a member's encoded text, whether its [html] marker was stored or rebuilt by rendering.
	 *
	 * @dataProvider membersEncodedTags
	 * @param string $stored a post as toDB() stores it for a member
	 * @param bool $tags true for RSS 2.0, whose description is HTML in CDATA
	 */
	public function testToRssKeepsAMembersEncodedTagAsText($stored, $tags)
	{
		$result = $this->tp->toRss($stored, $tags);
		$html = $tags ? $result : html_entity_decode($result, ENT_QUOTES | ENT_XML1, 'UTF-8');

		self::assertStringContainsString('src=x', $html);
		self::assertStringNotContainsString('<img', $html);
	}

	/**
	 * @return array
	 */
	public function membersEncodedTags()
	{
		$tag = '&lt;img src=x onerror=alert(1)&gt;';

		return array(
			'a stored marker, RSS 2.0'                => array('[html]'.$tag.'[/html]', true),
			'an unpaired stored marker, RSS 2.0'      => array('lead [html] then '.$tag.' tail', true),
			'an unpaired stored marker, plain feeds'  => array('lead [html] then '.$tag.' tail', false),
			'a marker rendering rebuilds, plain feeds' => array('[ht[b][/b]ml] '.$tag, false),
		);
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

			$this->assertStringContainsString($val['expected'], $actual);
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
			$this->assertStringContainsString($val['expected'], $actual);
			//echo $$actual."\n\n";
		}


	}

	public function testToAttributeReplaceConstants()
	{
		$input = "This is e_THEME: {e_THEME}";
		$expected = "This is e_THEME: ./e107_themes/";

		$actual = $this->tp->toAttribute($input);

		$this->assertEquals($expected, $actual);
	}

	public function testToAttributeDoesNotReplaceConstantsWhenStringHasSingleQuote()
	{
		$input = "This isn't e_THEME: {e_THEME}";
		$expected = "This isn&#039;t e_THEME: {e_THEME}";

		$actual = $this->tp->toAttribute($input);

		$this->assertEquals($expected, $actual);
	}

	public function testToAttributeDoesReplaceConstantsWhenStringHasLeftAngleBracket()
	{
		$input = "{e_THEME} <-- e_THEME";
		$expected = "./e107_themes/ &lt;-- e_THEME";

		$actual = $this->tp->toAttribute($input);

		$this->assertEquals($expected, $actual);
	}

	public function testToAttributeExplicitPure()
	{
		$input = "{e_THEME} <-- Not e_THEME";
		$expected = "{e_THEME} &lt;-- Not e_THEME";

		$actual = $this->tp->toAttribute($input, true);

		$this->assertEquals($expected, $actual);
	}

	public function testToAttributeImplicitPure()
	{
		$input = "\"It's a Wonderful Life (1946)\"";
		$expected = "&quot;It&#039;s a Wonderful Life (1946)&quot;";

		$actual = $this->tp->toAttribute($input);

		$this->assertEquals($expected, $actual);
	}

	public function testAttributeValueEncodesMarkupAndLeavesAnEntityAlreadyThereAlone()
	{
		$this->assertSame('&lt;b&gt; &amp; &quot;q&quot; &#039;s&#039;', $this->tp->attributeValue('<b> & "q" \'s\''));
		$this->assertSame('O&#039;Brien &amp; Co', $this->tp->attributeValue('O&#039;Brien &amp; Co'));
		$this->assertSame("\xEF\xBF\xBD", $this->tp->attributeValue("\xC3"));
		$this->assertSame('12', $this->tp->attributeValue(12));
	}

	public function testToAttributesEmpty()
	{
		$input = [];
		$expected = "";

		$actual = $this->tp->toAttributes($input);

		$this->assertEquals($expected, $actual);
	}

	public function testToAttributesOne()
	{
		$input = ["href" => "https://e107.org"];
		$expected = " href='https://e107.org'";

		$actual = $this->tp->toAttributes($input);

		$this->assertEquals($expected, $actual);
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

		$this->assertEquals($expected, $actual);
	}

	public function testBootstrapData()
	{
		$expected = [
			'data-toggle'    => 'collapse',
			'data-bs-toggle' => 'collapse',
			'data-target'    => '#sub-main-custom',
			'data-bs-target' => '#sub-main-custom',
		];

		$input = ['toggle' => 'collapse', 'target' => '#sub-main-custom'];

		foreach ([3, 4, 5] as $version)
		{
			$this->tp->setBootstrap($version);

			self::assertSame($expected, $this->tp->bootstrapData($input), 'Bootstrap ' . $version);
		}

		self::assertSame([], $this->tp->bootstrapData([]));
	}

	public function testBootstrapShowClass()
	{
		$expected = [3 => 'in', 4 => 'show', 5 => 'show'];

		foreach ($expected as $version => $class)
		{
			$this->tp->setBootstrap($version);

			self::assertSame($class, $this->tp->bootstrapShowClass(), 'Bootstrap ' . $version);
		}

		$undeclared = $this->make('e_parse');

		self::assertNull($undeclared->getBootstrap());
		self::assertSame('in', $undeclared->bootstrapShowClass());
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

			$this->assertStringStartsWith($var['expected']['prefix'], $result);
			$this->assertStringEndsWith($var['expected']['suffix'], $result);

		}


	}

	public function testText_truncate()
	{
		$string = "This is a long string that will be truncated.";
		$result = $this->tp->text_truncate($string, 20);
		$this->assertSame('This is a long  ... ', $result);

		$string = "This is has something &amp; something";
		$result = $this->tp->text_truncate($string, 29);
		$this->assertSame('This is has something &amp;  ... ', $result);

		$string = "Can't fail me now [b]Bold[/b]";
		$result = $this->tp->text_truncate($string, 25);
		$this->assertSame("Can't fail me now Bold", $result);

		$string = "Can't fail me now <strong class='bbcode bold bbcode-b'>Bold</strong>";
		$result = $this->tp->text_truncate($string, 25);
		$this->assertSame("Can't fail me now Bold", $result);

	}

	public function testText_truncateKeepsHtmlEntitiesEncoded()
	{
		$string = '&lt;img src=x onerror=alert(1)&gt;' . str_repeat(' pad', 20);

		foreach (array(10, 25, 33, 34, 50, 200, 500) as $len)
		{
			$result = $this->tp->text_truncate($string, $len, '');
			$this->assertStringNotContainsString('<img', $result, 'len=' . $len);
			$this->assertStringNotContainsString('>', $result, 'len=' . $len);
		}

		$result = $this->tp->text_truncate('&amp;&amp;&amp;&amp;&amp;&amp;', 3, '');
		$this->assertSame('&amp;&amp;&amp;', $result);
	}

	public function testTruncateKeepsHtmlEntitiesEncoded()
	{
		$string = '&lt;img src=x onerror=alert(1)&gt;' . str_repeat(' pad', 20);

		foreach (array(10, 25, 33, 34, 50, 200, 500) as $len)
		{
			$result = $this->tp->truncate($string, $len, '');
			$this->assertStringNotContainsString('<img', $result, 'len=' . $len);
			$this->assertStringNotContainsString('>', $result, 'len=' . $len);
		}
	}

	public function testText_truncateCutsCharactersNotBytes()
	{
		$this->assertSame(str_repeat('é', 10), $this->tp->text_truncate(str_repeat('é', 40), 10, ''));
		$this->assertSame('Café cr...', $this->tp->text_truncate('Café crème and more text here', 10, '...'));
		$this->assertSame(str_repeat('😀', 5), $this->tp->text_truncate(str_repeat('😀', 20), 5, ''));
		$this->assertSame('日本語のテキス ... ', $this->tp->text_truncate(str_repeat('日本語のテキスト', 5), 12, ' ... '));

		$result = $this->tp->html_truncate('<p>' . str_repeat('é', 40) . '</p>', 10, '');
		$this->assertSame('<p>' . str_repeat('é', 10) . '</p>', $result);

		$fits = '<p>' . str_repeat('é', 8) . '</p>';
		$this->assertSame($fits, $this->tp->html_truncate($fits, 10, '...'));
	}

	public function testText_truncateKeepsValidCharactersWholeBesideAnInvalidByte()
	{
		$result = $this->tp->text_truncate(str_repeat('é', 20) . "\xFF", 5, '');
		$this->assertSame(str_repeat('é', 5), $result);

		$result = $this->tp->text_truncate(str_repeat('日', 20) . "\xFF", 5, '');
		$this->assertSame(str_repeat('日', 5), $result);

		$result = $this->tp->html_truncate('<p>' . str_repeat('é', 20) . "\xFF" . '</p>', 5, '');
		$this->assertSame('<p>' . str_repeat('é', 5) . '</p>', $result);

		$result = $this->tp->html_truncate('<p>' . str_repeat('日', 20) . "\xFF" . '</p>', 5, '');
		$this->assertSame('<p>' . str_repeat('日', 5) . '</p>', $result);
	}

	/**
	 * @dataProvider multiLineTexts
	 */
	public function testText_truncateKeepsTheNewlinesOfAMultiLineText($text, $len, $expected)
	{
		$this->assertSame($expected, $this->tp->text_truncate($text, $len, '...'));
		$this->assertSame($expected, $this->tp->text_truncate($text . "\xFF", $len, '...'), 'the same text ending in an invalid byte');
	}

	public function multiLineTexts()
	{
		return array(
			'an entity in a multi-line text stays whole'      => array("line one\nline two &amp;copy and more", 22, "line one\nline two &amp;..."),
			'an entity directly behind a newline stays whole' => array("line one\n&amp;copy and more words", 13, "line one\n&amp;..."),
			'a newline kept last stays ahead of the ending'   => array("AT&T\nmore words here", 8, "AT&T\n..."),
		);
	}

	public function testTruncatorsCountAWholeEntityAsOneCharacterAndNothingMore()
	{
		$entities = str_repeat('&#1234567;', 20);

		$this->assertSame(str_repeat('&#1234567;', 10), $this->tp->text_truncate($entities, 10, ''));
		$this->assertSame('<p>' . str_repeat('&#1234567;', 10) . '</p>', $this->tp->html_truncate('<p>' . $entities . '</p>', 10, ''));
		$this->assertSame("&\xC5\xBF", $this->tp->text_truncate(str_repeat("&\xC5\xBF\xC5\xBF;", 4) . 'tail', 2, ''));
	}

	public function testTruncatingAVeryLongTextStaysWithinASmallMemoryLimit()
	{
		$probe = 'define("e107_INIT", true);'
			. ' require ' . var_export(e_HANDLER . 'core_functions.php', true) . ';'
			. ' require ' . var_export(e_HANDLER . 'e_parse_class.php', true) . ';'
			. ' $tp = new e_parse();'
			. ' $text = str_repeat("a", 4 * 1024 * 1024);'
			. ' echo $tp->text_truncate($text, 200, "..."), "\n", $tp->html_truncate("<p>" . $text . "</p>", 200, "...");';

		list($output, $status) = $this->runInCli($probe, '-d memory_limit=48M');

		$this->assertSame(0, $status, implode("\n", $output));
		$this->assertSame(array(str_repeat('a', 197) . '...', '<p>' . str_repeat('a', 200) . '...</p>'), $output);
	}

	/**
	 * search_highlight is created by e107_admin/search.php and by nothing else, so
	 * a site whose administrator has never saved the search settings page reaches
	 * the highlighting test without it. Absent has to mean off, not a warning on
	 * every parsed string.
	 */
	public function testCheckHighlightingTreatsAMissingSearchHighlightPreferenceAsOff()
	{
		$restore = $this->overrideSitePrefs(array('sitename' => 'e107'), 'https://example.com/news.php?q=needle');

		try
		{
			self::assertFalse($this->tp->checkHighlighting());
		}
		finally
		{
			$restore();
		}
	}

	public function testCheckHighlightingIsOnWhenTheSearchHighlightPreferenceIsSet()
	{
		$restore = $this->overrideSitePrefs(array('search_highlight' => 1), 'https://example.com/news.php?q=needle');

		try
		{
			self::assertTrue($this->tp->checkHighlighting());
		}
		finally
		{
			$restore();
		}
	}

	/**
	 * Swap in the legacy $pref global and the referrer that checkHighlighting()
	 * reads, and hand back the callable that puts both back as they were found.
	 *
	 * @param array  $prefs
	 * @param string $referer
	 * @return callable
	 */
	private function overrideSitePrefs(array $prefs, $referer)
	{
		$hadPref = array_key_exists('pref', $GLOBALS);
		$savedPref = $hadPref ? $GLOBALS['pref'] : null;
		$hadReferer = array_key_exists('HTTP_REFERER', $_SERVER);
		$savedReferer = $hadReferer ? $_SERVER['HTTP_REFERER'] : null;

		$GLOBALS['pref'] = $prefs;
		$_SERVER['HTTP_REFERER'] = $referer;

		return static function () use ($hadPref, $savedPref, $hadReferer, $savedReferer)
		{
			if($hadPref)
			{
				$GLOBALS['pref'] = $savedPref;
			}
			else
			{
				unset($GLOBALS['pref']);
			}

			if($hadReferer)
			{
				$_SERVER['HTTP_REFERER'] = $savedReferer;
			}
			else
			{
				unset($_SERVER['HTTP_REFERER']);
			}
		};
	}

	public function testTruncate()
	{
		// html
		$string = "Can't fail me now <strong class='bbcode bold bbcode-b'>Bold</strong>";
		$result = $this->tp->truncate($string, 25);
		$this->assertSame("Can't fail me now <strong class='bbcode bold bbcode-b'>Bold</strong>", $result); // html ignored in char count.

		// bbcode - stripped.
		$string = "Can't fail me now [b]Bold[/b]";
		$result = $this->tp->truncate($string, 25);
		$this->assertSame("Can't fail me now Bold", $result);

		// text
		$string = "This is a long string that will be truncated.";
		$result = $this->tp->truncate($string, 20);
		$this->assertSame('This is a long st...', $result);

	}

	public function testSimpleParse()
	{
		$vars = array(
			'CONTACT_SUBJECT' => "My Subject",
			'CONTACT_PERSON'  => "My Name"
		);

		$template = "{CONTACT_SUBJECT} <b>{CONTACT_PERSON}</b>{MISSING_SHORTCODE}";

		$result = $this->tp->simpleParse($template, $vars);
		$this->assertEquals("My Subject <b>My Name</b>", $result);

		$result = $this->tp->simpleParse($template, null);
		$this->assertEquals(" <b></b>", $result);


		$vars = array(
			'aaBB_123' => "Simple Replacement"
		);

		$template = "-- {aaBB_123} --";
		$result = $this->tp->simpleParse($template, $vars);
		$this->assertEquals('-- Simple Replacement --', $result);

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
		$this->assertSame($expected, $list);


	}

	public function testToText()
	{
		$arr = array(
			0 => array('html' => "<h1><a href='#'>My Caption</a></h1>", 'expected' => 'My Caption'),
			1 => array('html' => "<div><h1><a href='#'>My Caption</a></h1></div>", 'expected' => 'My Caption'),
			2 => array('html' => 'Line 1<br />Line 2<br />Line 3<br />', 'expected' => "Line 1\nLine 2\nLine 3\n"),
			3 => array('html' => "Line 1<br />\nLine 2<br />\nLine 3<br />", 'expected' => "Line 1\nLine 2\nLine 3\n"),
		);


		foreach ($arr as $var)
		{
			$result = $this->tp->toText($var['html']);
			$this->assertEquals($var['expected'], $result);
		}

	}

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
			$this->assertSame($val['expected'], $actual);
		}

		$this->tp->setStaticUrl(null);


	}

	public function testPost_toHTML()
	{
		$text = "<di style='width:100%'>Test</di>"; // invalid html.
		$result = $this->tp->post_toHTML($text);
		$this->assertEmpty($result);

		$text = "<div style='width:100%'>Test</div>"; // valid html.
		$cleaned = '<div style="width:100%">Test</div>'; // valid and cleaned html.
		$result = $this->tp->post_toHTML($text);
		$this->assertSame($cleaned, $result);

	}

	public function testSetScriptAccess()
	{
		$this->tp->setScriptAccess(e_UC_PUBLIC);
		$result = $this->tp->getScriptAccess();
		$this->assertEquals(e_UC_PUBLIC, $result);
	}

	public function testGetScriptAccess()
	{
		$result = $this->tp->getScriptAccess();
		$this->assertFalse($result);
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
		$this->assertSame($expected, $result);


		//	var_export($result);
		//  $true = is_array($result) && in_array('style',$result['img']);

		//  $this->assertTrue($true);
	}

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
			$this->assertTrue(false, "getTags() didn't return the correct value");
		}

		$this->assertSame($expected['img'][0]['src'], $result['img'][0]['src']);
		$this->assertSame($expected['img'][0]['alt'], $result['img'][0]['alt']);
	}

	public function testToGlyph()
	{
		$this->tp->setFontAwesome(4);

		$result = $this->tp->toGlyph('fa-envelope.glyph');
		$expected = "<i class='fa fa-envelope' ></i> ";
		$this->assertEquals($expected, $result);

		$this->tp->setFontAwesome(5);

		$result = $this->tp->toGlyph('fa-mailchimp');
		$expected = "<i class='fab fa-mailchimp' ></i> ";
		$this->assertEquals($expected, $result);

		$this->tp->setFontAwesome(6);
		$result = $this->tp->toGlyph('fa-wine-glass-empty');
		$expected = "<i class='fas fa-wine-glass-empty' ></i> ";
		$this->assertSame($expected, $result);

		$result = $this->tp->toGlyph('fa-virus-covid');
		$this->assertSame("<i class='fas fa-virus-covid' ></i> ", $result);

		$this->tp->setFontAwesome(4);

		$result = $this->tp->toGlyph('fab-mailchimp'); // spefific call
		$expected = "<i class='fab fa-mailchimp' ></i> ";
		$this->assertEquals($expected, $result);

		$result = $this->tp->toGlyph('fas-camera'); // spefific call
		$this->assertSame("<i class='fas fa-camera' ></i> ", $result);

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
			$this->assertSame($expected, $result);
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
			$this->assertSame($expected, $result, 'Input was: ' . $icon);
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
			$this->assertSame($expected, $result);
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
			$this->assertSame($expected, $result);
		}


	}

	function testToGlyphFallback()
	{
		$this->tp->setFontAwesome(5);
		$result = $this->tp->toGlyph('fa-paypal.glyph');
		$this->assertSame("<i class='fab fa-paypal' ></i> ", $result);

		$this->tp->setFontAwesome(6);
		$result = $this->tp->toGlyph('fa-paypal.glyph');
		$this->assertSame("<i class='fab fa-paypal' ></i> ", $result);

		$result = $this->tp->toGlyph('fa-clock.glyph');
		$this->assertSame("<i class='fas fa-clock' ></i> ", $result);

		$result = $this->tp->toGlyph('clock.glyph');
		$this->assertSame("<i class='fas fa-clock' ></i> ", $result);


		$this->tp->setFontAwesome(5);
	}

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
					"thumb.php?src=e_AVATAR%2Fupload%2Favatartest.png&amp;w=50&amp;h=50",
					"class='img-rounded rounded user-avatar'"
				)
			),
			1 => array(
				'input'    => array('user_image' => 'avatartest.png'),
				'parms'    => array('w' => 50, 'h' => 50, 'crop' => false),
				'expected' => array(
					"thumb.php?src=e_AVATAR%2Fdefault%2Favatartest.png&amp;w=50&amp;h=50",
					"class='img-rounded rounded user-avatar'"
				)
			),
			2 => array(
				'input'    => array('user_image' => ''),
				'parms'    => array('w' => 50, 'h' => 50, 'crop' => false),
				'expected' => array(
					"thumb.php?src=e_IMAGE%2Fgeneric%2Fblank_avatar.jpg&amp;w=50&amp;h=50",
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
					"thumb.php?src=e_IMAGE%2Fgeneric%2Fblank_avatar.jpg&amp;w=50&amp;h=50",
					"class='img-rounded rounded user-avatar'",
					"<a class='e-tip' title=",
					e107::getUrl()->create('user/myprofile/edit')
				)
			),
			5 => array(
				'input'    => array('user_image' => 'avatartest.png'),
				'parms'    => array('w' => 30, 'h' => 20, 'crop' => true, 'shape' => 'rounded'),
				'expected' => array(
					"thumb.php?src=e_AVATAR%2Fdefault%2Favatartest.png&amp;aw=30&amp;ah=20",
					"class='img-rounded user-avatar'"
				)
			),
			6 => array(
				'input'    => array('user_image' => 'avatartest.png'),
				'parms'    => array('w' => 30, 'h' => 30, 'crop' => false, 'shape' => 'circle', 'alt' => 'mytitle'),
				'expected' => array(
					"thumb.php?src=e_AVATAR%2Fdefault%2Favatartest.png&amp;w=30&amp;h=30",
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

		// Stub the remote GET so the remote-avatar + base64 case (index #8) is
		// deterministic and offline. toAvatar() fetches remote images through
		// the e_file singleton; inject a double that returns fixed bytes.
		$registryId = 'core/e107/singleton/e_file';
		$originalFile = e107::getRegistry($registryId);
		$remoteBytes = file_get_contents($icon);
		$stubFile = $this->make('e_file', array(
			'getRemoteContent' => function () use ($remoteBytes) { return $remoteBytes; },
		));
		e107::setRegistry($registryId, $stubFile);

		try
		{
			foreach ($tests as $index => $var)
			{
				$result = $this->tp->toAvatar($var['input'], $var['parms']);
				foreach ($var['expected'] as $str)
				{
					self::assertStringContainsString($str, $result, "Failed on index #" . $index);
				}
			}
		}
		finally
		{
			// Restore the registry (null => removes our stub) so the shuffled
			// suite isn't polluted for later tests.
			e107::setRegistry($registryId, $originalFile);
		}


	}

	/**
	 * The hd option doubles an avatar's dimensions, a default height included.
	 * @see https://github.com/e107inc/e107/issues/6060
	 */
	public function testToAvatarHdWithoutAnExplicitHeight()
	{
		$parser = e107::getParser();
		$originalHeight = $parser->thumbHeight();
		$parser->thumbHeight(0);

		try
		{
			$img = $this->tp->toAvatar(array('user_image' => ''), array('w' => 50, 'crop' => false, 'hd' => true));

			self::assertStringContainsString("width='50' ", $img);
			self::assertStringNotContainsString("height='", $img);
			self::assertStringContainsString('thumb.php?src=e_IMAGE%2Fgeneric%2Fblank_avatar.jpg&amp;w=100&amp;h=0', $img);

			$sized = $this->tp->toAvatar(array('user_image' => ''), array('w' => 50, 'h' => 50, 'crop' => false, 'hd' => true));

			self::assertStringContainsString("width='50' height='50'", $sized);
			self::assertStringContainsString('thumb.php?src=e_IMAGE%2Fgeneric%2Fblank_avatar.jpg&amp;w=100&amp;h=100', $sized);
		}
		finally
		{
			$parser->thumbHeight($originalHeight);
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
			$this->assertStringContainsString($var['expected'], $result);
		}
	}

	public function testToImage()
	{
		$src = "{e_PLUGIN}gallery/images/butterfly.jpg";
		$this->tp->setThumbSize(80, 80); // set defaults.

		// test with defaults set above.
		$result = $this->tp->toImage($src);
		$this->assertStringContainsString('butterfly.jpg&amp;w=80&amp;h=80', $result); // src
		$this->assertStringContainsString('butterfly.jpg&amp;w=320&amp;h=320', $result); // srcset 4x the size on small images.

		// test overriding of defaults.
		$override = array('w' => 800, 'h' => 0);
		$result2 = $this->tp->toImage($src, $override);
		$this->assertStringContainsString('butterfly.jpg&amp;w=800&amp;h=0', $result2); // src
		$this->assertStringContainsString('Fbutterfly.jpg&amp;w=1600&amp;h=0', $result2); // srcset


		$override = array('w' => 0, 'h' => 0); // display image without resizing
		$result3 = $this->tp->toImage($src, $override);
		$this->assertStringContainsString('Fbutterfly.jpg&amp;w=0&amp;h=0', $result3); // src

		$result4 = $this->tp->toImage($src, ['loading' => 'lazy']);
		$this->assertStringContainsString('loading="lazy"', $result4); // src

		$result5 = $this->tp->toImage($src, ['type' => 'webp']);
		$this->assertStringContainsString('&amp;type=webp', $result5); // src


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
			$this->assertSame($var['expected'], $result);

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
		$this->assertSame($expected, $result);
	}

	/**
	 * Every operand of the <img> statement is encoded where it is written, the src path and srcset included.
	 */
	public function testToImageAttributeInjection()
	{
		$src = '{e_PLUGIN}gallery/images/butterfly.jpg';
		$breakout = '" onload="alert(1)';

		$parm = array(
			'w'       => 0,
			'h'       => 0,
			'id'      => 'x'.$breakout,
			'class'   => 'x'.$breakout,
			'alt'     => 'x'.$breakout,
			'style'   => 'width:1px;'.$breakout,
			'title'   => 'x'.$breakout,
			'loading' => 'lazy'.$breakout,
			'width'   => '1'.$breakout,
			'height'  => '1'.$breakout,
		);

		$result = $this->tp->toImage($src, $parm);
		$result = preg_replace('/"([^"]*)thumb.php/', '"thumb.php', $result);

		$expected = '<img id="x&quot; onload=&quot;alert(1)" class="x&quot; onload=&quot;alert(1)"'
			.' src="thumb.php?src=e_PLUGIN%2Fgallery%2Fimages%2Fbutterfly.jpg&amp;w=0&amp;h=0"'
			.' alt="x&quot; onload=&quot;alert(1)"'
			.' width="1&quot; onload=&quot;alert(1)" height="1&quot; onload=&quot;alert(1)"'
			.' style="width:1px;&quot; onload=&quot;alert(1)"'
			.' loading="lazy&quot; onload=&quot;alert(1)"'
			.' title="x&quot; onload=&quot;alert(1)"  />';

		self::assertSame($expected, $result);
		self::assertStringNotContainsString($breakout, $result);
	}

	/**
	 * The sink encodes what it is given; it does not validate it, and it does not encode it twice.
	 */
	public function testToImageKeepsLegacyAttributeValues()
	{
		$src = '{e_PLUGIN}gallery/images/butterfly.jpg';

		$parm = array(
			'w'      => 0,
			'h'      => 0,
			'class'  => 'salt &amp; pepper',
			'title'  => "Ben&#039;s",
			'width'  => '50%',
			'height' => 'auto',
		);

		$result = $this->tp->toImage($src, $parm);

		self::assertStringContainsString('class="salt &amp; pepper"', $result);
		self::assertStringContainsString('title="Ben&#039;s"', $result);
		self::assertStringContainsString('width="50%"', $result);
		self::assertStringContainsString('height="auto"', $result);
	}

	/**
	 * The src path and the srcset are operands like the rest, and a caller may hand either of them a quote.
	 */
	public function testToImageEncodesThePathAndSrcset()
	{
		$breakout = '" onwheel="alert(1)';

		$parm = array('srcset' => 'b' . $breakout . '.gif 2x');

		$result = $this->tp->toImage('http://example.com/a' . $breakout . '.gif', $parm);

		$expected = '<img class="img-responsive img-fluid"'
			. ' src="http://example.com/a&quot; onwheel=&quot;alert(1).gif"'
			. ' alt="a&quot; onwheel=&quot;alert(1).gif"'
			. ' srcset="b&quot; onwheel=&quot;alert(1).gif 2x"  />';

		self::assertSame($expected, $result);
		self::assertStringNotContainsString($breakout, $result);
	}

	/**
	 * thumbUrl() and thumbSrcSet() mint an &amp; of their own, and the encoder has to leave it as one.
	 */
	public function testToImageLeavesItsOwnThumbnailUrlsAlone()
	{
		$result = $this->tp->toImage('{e_MEDIA_IMAGE}2020-12/b.gif', array('w' => 100, 'h' => 100));
		$result = preg_replace('/"([^"]*)thumb.php/', '"thumb.php', $result);

		$expected = '<img class="img-responsive img-fluid"'
			. ' src="thumb.php?src=e_MEDIA_IMAGE%2F2020-12%2Fb.gif&amp;w=100&amp;h=100"'
			. ' alt="b.gif"'
			. ' srcset="thumb.php?src=e_MEDIA_IMAGE%2F2020-12%2Fb.gif&amp;w=200&amp;h=200 2x"'
			. ' width="100" height="100"  />';

		self::assertSame($expected, $result);
	}

	public function testThumbSrcSet()
	{
		$src = "{e_PLUGIN}gallery/images/butterfly.jpg";
		$parms = array('w' => 800, 'h' => 0, 'size' => '2x');

		$result = $this->tp->thumbSrcSet($src, $parms);
		$this->assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result);

		$this->tp->setThumbSize(80, 80); // set defaults.

		$result2 = $this->tp->thumbSrcSet($src, $parms); // testing overrides
		$this->assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result2);

		$result3 = $this->tp->thumbSrcSet($src, array('w' => 800, 'size' => '2x')); // testing overrides without 'h' being set.
		$this->assertStringContainsString('butterfly.jpg&amp;w=1600&amp;h=0', $result3);

		$result4 = $this->tp->thumbSrcSet($src); // no overrides
		$this->assertStringContainsString('butterfly.jpg&amp;w=160&amp;h=160', $result4);

	}

	public function testPreFilterRunsBbcodeSaveHandlersOnTextThatAlsoContainsHtml()
	{
		$payload = '[img height=one]{e_THEME}bootstrap3/images/logo.png[/img]';

		$filtered = $this->tp->preFilter($payload);
		self::assertSame('[img]{e_THEME}bootstrap3/images/logo.png[/img]', $filtered);

		self::assertSame('<b>hi</b>' . $filtered, $this->tp->preFilter('<b>hi</b>' . $payload));
	}

	public function testPreFilterRunsBbcodeSaveHandlersOnTextWhoseOnlyBbcodeIsNotOneOfTheNine()
	{
		$payload = '[youtube]https://www.youtube.com/watch?v=dQw4w9WgXcQ[/youtube]';

		self::assertSame('[youtube]dQw4w9WgXcQ[/youtube]', $this->tp->preFilter($payload));
	}

	public function testToTextConvertsBbcodeThatIsNotOneOfTheNine()
	{
		self::assertSame('See click here please', $this->tp->toText('See [url=https://example.com/p]click here[/url] please'));
	}

	public function testIsBBcode()
	{
		$tests = array(
			0 => array("My Simple Text", false), // input , expected result
			1 => array("<hr />", false),
			2 => array("[b]Bbcode[/b]", true),
			3 => array("<div class='something'>[code]something[/code]</div>", true),
			4 => array("[code]&lt;b&gt;someting&lt;/b&gt;[/code]", true),
			5 => array("[html]something[/html]", false),
			6 => array("http://something.com/index.php?what=ever", false),
			7 => array("<b>hi</b>[img height=1]{e_THEME}logo.png[/img]", true),
			8 => array("1<2>3 [b]Bbcode[/b]", true),
			9 => array("See [url=https://example.com/p]click here[/url] please", true),
			10 => array("Watch this [youtube]dQw4w9WgXcQ[/youtube] now", true),
			11 => array("He said [quote]something quoted[/quote] earlier", true),
			12 => array("[list][*]one[*]two[/list]", true),
			13 => array("[size2]bigger[/size2]", true),
			14 => array("[QUOTE]Shouting[/QUOTE]", true),
			15 => array("[notabbcode]something[/notabbcode]", false),
			16 => array("[php]echo 1;[/php]", false),
			17 => array("[b]never closed", false),
			18 => array("[youtube=600]dQw4w9WgXcQ[/youtube=600]", true), // the parser matches a parameter on a closing tag too
			19 => array("[//b]", false),
			20 => array("[/ b]", false),
			21 => array("[b]the log lives at [/var/log/syslog[/b] on that box", true),
			22 => array("[code]run cd [/home/user[/code] to get there", true),
			23 => array("[b]hello there[/zzz[/b]", true)
		);


		foreach ($tests as $val)
		{
			list($input, $expected) = $val;
			$actual = $this->tp->isBBcode($input);

			$this->assertEquals($expected, $actual, $input);
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

			$this->assertEquals($expected, $actual, $input);
		}


	}

	public function testIsImage()
	{
		$this->assertTrue($this->tp->isImage('/path-to-file/myfile.jpg'));
		$this->assertFalse($this->tp->isImage('/path-to-file/myfile.mov'));

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

	/**
	 * The file path is printed into a src, and a caller may hand it a quote or an entity it already encoded.
	 */
	public function testToVideoEncodesTheFilePath()
	{
		$breakout = '" onwheel="alert(1)';

		$result = $this->tp->toVideo('http://example.com/a' . $breakout . '.mp4');

		self::assertStringContainsString('src="http://example.com/a&quot; onwheel=&quot;alert(1).mp4"', $result);
		self::assertStringNotContainsString($breakout, $result);

		$result = $this->tp->toVideo('http://example.com/tom&amp;jerry.mp4');

		self::assertStringContainsString('src="http://example.com/tom&amp;jerry.mp4"', $result);
	}

	public function testMakeClickable()
	{
		$email = 'myemail@somewhere.com.tk';

		$tp = $this->tp;

		// ----

		$result = $tp->makeClickable($email, 'email', array('sub' => '[email]'));

		$this->assertStringContainsString('[email]</a>', $result);

		// -----

		$result = $tp->makeClickable($email, 'email', array('sub' => 'fa-envelope.glyph'));
		$this->assertStringContainsString("fa-envelope' ></i></a>", $result);

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
			$this->assertEquals($expected, $result);
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
			$this->assertEquals($expected, $result);
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
			$this->assertEquals($expected, $result);
		}


	}

	public function testMakeClickableLeavesAnAddressThatCanExecuteAsText()
	{
		foreach (array('javascript://x%0Aalert(1)', 'JavaScript://x%0Aalert(1)', 'vbscript://x%0Amsgbox(1)') as $address)
		{
			$sample = 'before ' . $address . ' after';

			self::assertEquals($sample, $this->tp->makeClickable($sample, 'url'), 'Linked ' . $address);
			self::assertEquals($sample, $this->tp->makeClickable($sample, 'url', array('sub' => '[link]', 'ext' => true)), 'Linked ' . $address . ' with a substitute');
		}

		$expected = 'before <a class="e-url" href="ftps://example.com/x" >ftps://example.com/x</a> after';
		self::assertEquals($expected, $this->tp->makeClickable('before ftps://example.com/x after', 'url'), 'An ftps url stopped being linked');
	}

	public function testMakeClickablePrintsTheSubstituteLinkTextAsWritten()
	{
		$html = $this->tp->makeClickable('see http://x.example/p and www.y.example/q and ftp.z.example/r here', 'url', array('sub' => 'Price $5 \\1'));

		$expected = 'see <a class="e-url" href="http://x.example/p" >Price $5 \\1</a>'
			. ' and <a class="e-url" href="http://www.y.example/q" >Price $5 \\1</a>'
			. ' and <a class="e-url" href="ftp://ftp.z.example/r" >Price $5 \\1</a> here';

		self::assertSame($expected, $html);
	}

	public function testClickableLinksInPlaceNeverCarryAnAddressThatCanExecute()
	{
		$restore = $this->withCorePrefs(array('make_clickable' => 1, 'link_replace' => 1, 'links_new_window' => 0, 'link_text' => ''));

		try
		{
			$tp = $this->make('e_parse');
			$tp->__construct();

			$html = $tp->toHTML($tp->toDB('Notes at javascript://x%0Aalert(1) and at https://example.com/notes'), true, 'USER_BODY');

			self::assertStringNotContainsString('href="javascript', $html, 'A member\'s javascript: address was linked: ' . $html);
			self::assertStringContainsString('<a class="e-url" href="https://example.com/notes" >', $html, 'An ordinary address stopped being linked: ' . $html);
		}
		finally
		{
			$restore();
		}
	}

	public function testMakeClickableSkipsMarkup()
	{
		$tp = $this->tp;

		$untouched = array(
			'email' => array(
				"<a class='e-email' href='mailto:john@example.com'>john@example.com</a>" => 'an address between an <a> and its </a>',
				'<a href="#">john@example.com'                                          => 'an address after an <a> that is never closed',
				"<img src='x.png' alt='a > john@example.com' />"                         => 'an address inside a quoted attribute holding a >',
				'<img src="x.png" alt="john@example.com" />'                             => 'an address inside an attribute',
			),
			'url'   => array(
				'<a href="#">visit www.example.com</a>' => 'a url between an <a> and its </a>',
				'<b>bold</b>www.example.com'            => 'a url running straight on from a tag',
			),
		);

		foreach ($untouched as $type => $samples)
		{
			foreach ($samples as $sample => $why)
			{
				self::assertEquals($sample, $tp->makeClickable($sample, $type), 'Rewrote ' . $why);
			}
		}

		$result = $tp->makeClickable('<a href="#">click</a> john@example.com', 'email');
		self::assertStringContainsString("<a class='e-email' ", $result, 'An address after a closed link was left alone');

		$expected = 'first line' . E_NL . '<a class="e-url" href="http://www.example.com" >www.example.com</a>';
		self::assertEquals($expected, $tp->makeClickable('first line' . E_NL . 'www.example.com', 'url'), 'A url after a line break was left alone');

		$expected = '<a class="e-url" href="http://example.com" >http://example.com</a>' . E_NL . 'next';
		self::assertEquals($expected, $tp->makeClickable('http://example.com' . E_NL . 'next', 'url'), 'A line break was swallowed into the url');

		$expected = 'first line' . E_NL . '<a class="e-url" href="http://WWW.EXAMPLE.COM" >WWW.EXAMPLE.COM</a>';
		self::assertEquals($expected, $tp->makeClickable('first line' . E_NL . 'WWW.EXAMPLE.COM', 'url'), 'An upper case www. url stopped being linked');

		$expected = 'first line' . E_NL . '<a class="e-url" href="FTP://FTP.EXAMPLE.COM" >FTP.EXAMPLE.COM</a>';
		self::assertEquals($expected, $tp->makeClickable('first line' . E_NL . 'FTP.EXAMPLE.COM', 'url'), 'An upper case ftp. url stopped being linked');
	}

	public function testEmailBbcodesWithMakeClickableOn()
	{
		$restore = $this->withCorePrefs(array('make_clickable' => 1));

		try
		{
			$samples = array(
				'[email]john@example.com[/email]',
				'[email=john@example.com]Mail me[/email]',
				'[link=mailto:john@example.com]Mail me[/link]',
			);

			foreach ($samples as $sample)
			{
				$tp = $this->make('e_parse');
				$tp->__construct();

				$result = $tp->toHTML($sample, true);

				self::assertStringContainsString('"john"+"@"+"example.com"', $result, 'Address lost from ' . $sample);
				self::assertStringNotContainsString("class='e-email'", $result, 'Nested link produced by ' . $sample);
			}
		}
		finally
		{
			$restore();
		}
	}


	public function testToDate()
	{


		$class = $this->tp;

		$time = 1519512067; //  Saturday 24 February 2018 - 22:41:07

		$long = $class->toDate($time, 'long');
		$this->assertStringContainsString('Saturday 24 February 2018', $long);

		$short = $class->toDate($time, 'short');
		$this->assertStringContainsString('Feb 2018', $short);

		$rel = $class->toDate($time, 'relative');
		$this->assertStringContainsString('ago', $rel);
		$this->assertStringContainsString('data-livestamp="1519512067"', $rel);

		$custom = $class->toDate($time, 'dd-M-yy');
		$this->assertStringContainsString('<span>24-Feb-18</span>', $custom);


	}

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
			$this->assertEquals($var['expected'], $result, "Failed on index: " . $index);
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
			$this->assertSame($expected2[$index], $result);
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

		$this->assertEquals($expected, $actual);
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
				// <source> is an HTML5 void element — no closing tag, and the
				// "Your browser does not support..." text is a sibling of
				// <source>, not its content.
				'html'     => '<div class="video-responsive"><div class="video-responsive"><video width="320" height="240" controls="controls"><source src="e107_media/xxxxx5/videos/2018-07/SampleVideo.mp4" type="video/mp4">Your browser does not support the video tag.</video></div></div>',
				'expected' => '<div class="video-responsive"><div class="video-responsive"><video width="320" height="240" controls="controls"><source src="e107_media/xxxxx5/videos/2018-07/SampleVideo.mp4" type="video/mp4">Your browser does not support the video tag.</video></div></div>'
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
			$this->assertEquals($var['expected'], $result);
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
			$this->assertEquals($var['expected'], $result);
		}

		$this->tp->setScriptAccess(false);
		unset($_E107['phpunit']);

	}

	public function testCleanHtmlRefusesAScriptSchemeWhateverTheLocale()
	{
		$previous = setlocale(LC_CTYPE, 0);

		if (setlocale(LC_CTYPE, 'tr_TR.UTF-8', 'tr_TR.utf8') === false)
		{
			self::markTestSkipped('No Turkish locale is installed.');
		}

		try
		{
			$html = $this->tp->cleanHtml('<a href="JAVASCRIPT:document.title=1">notes</a>');
		}
		finally
		{
			setlocale(LC_CTYPE, $previous);
		}

		self::assertSame('<a href="#---sanitized---#">notes</a>', $html);
	}

	/**
	 * The parser is a singleton and cleanHtml() reuses one DOMDocument, so the
	 * node lists it fills while walking a document have to be emptied before it
	 * walks the next one. A <pre> or <code> inside a tag that gets stripped is
	 * collected but never replaced, so it is still holding a node of the old
	 * document when the next call reparses over the top of it.
	 */
	public function testCleanHtmlDoesNotReuseNodesOfThePreviousDocument()
	{
		$first = $this->tp->cleanHtml('<center><pre>kept</pre></center>');
		self::assertSame('', $first, 'the stripped tag takes its <pre> child with it');

		self::assertSame('<p>plain</p>', $this->tp->cleanHtml('<p>plain</p>'), 'second call on a reused parser');
		self::assertSame('<pre>later</pre>', $this->tp->cleanHtml('<pre>later</pre>'), 'third call on a reused parser');
	}

	/**
	 * libxml < 2.13 mis-parsed <source> and other HTML5 void elements as
	 * non-void, making following text become the element's child content.
	 * cleanHtml() runs a normalization pass that promotes those children
	 * to siblings so saveHTML() output is spec-compliant regardless of the
	 * installed libxml. This test builds a DOM that looks like the broken
	 * older-libxml parse and asserts the normalizer fixes it.
	 */
	public function testCleanHtmlNormalizesVoidElementsOnOldLibxml()
	{
		$normalize = new \e107\Reflection\ReflectionMethod('e_parse', 'normalizeVoidElements');
		$strip = new \e107\Reflection\ReflectionMethod('e_parse', 'stripVoidClosingTags');

		$doc = new DOMDocument();
		$doc->loadHTML('<body><video><source src="x.mp4" type="video/mp4"></source></video></body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		$source = $doc->getElementsByTagName('source')->item(0);
		$source->appendChild($doc->createTextNode('Your browser does not support the video tag.'));
		self::assertTrue($source->hasChildNodes(), 'precondition: simulated old-libxml placed content inside <source>');

		$normalize->invoke($this->tp, $doc);

		$source = $doc->getElementsByTagName('source')->item(0);
		self::assertFalse($source->hasChildNodes(), '<source> must be childless after normalization');

		$video = $doc->getElementsByTagName('video')->item(0);
		self::assertStringContainsString('Your browser does not support the video tag.', $video->textContent, 'promoted text must remain inside <video>');

		$serialized = $strip->invoke($this->tp, $doc->saveHTML($doc->documentElement));
		self::assertStringNotContainsString('</source>', $serialized, 'serialized output must not include a </source> closing tag');
		self::assertStringContainsString('Your browser does not support the video tag.', $serialized, 'promoted text must survive serialization');
	}

	public function testCleanHtmlKeepsABareLessThanBesideMarkup()
	{
		$this->tp->setScriptAccess(e_UC_NOBODY);

		self::assertSame('a &lt; b <b>c</b> d', $this->tp->cleanHtml('a < b <b>c</b> d'));
		self::assertSame('<p>1 &lt;2 and 3 &lt;= 4</p>', $this->tp->cleanHtml('<p>1 <2 and 3 <= 4</p>'));
		self::assertSame('<i>x</i> &lt;', $this->tp->cleanHtml('<i>x</i> <'));
		self::assertSame('a &lt; b <img src="x">', $this->tp->cleanHtml('a < b <img src="x" onerror="y">'));
		self::assertSame('<b>a</b><p title="1 &lt; 2">x &lt; y</p>', $this->tp->cleanHtml('<b>a</b><p title="1 < 2">x < y</p>'));
		self::assertSame('<p>x &lt; y &amp;#0000060; z</p>', $this->tp->cleanHtml('<p>x < y &#38;#0000060; z</p>'));
		self::assertSame('<p>x</p><!-- a < b -->', $this->tp->cleanHtml('<p>x</p><!-- a < b -->'));
	}

	public function testCleanHtmlLeavesALessThanReferenceTheAuthorTypedAlone()
	{
		self::assertStringContainsString('<!-- &#0000060; -->', $this->tp->cleanHtml('<p>x < y</p><!-- &#0000060; -->'));
		self::assertStringNotContainsString('a < b', $this->tp->cleanHtml("<p>x < y</p><!-- a &#000\x010060; b -->"));

		$this->tp->setScriptAccess(e_UC_PUBLIC);
		self::assertStringNotContainsString('a < b', $this->tp->cleanHtml("<p>x < y</p><script>a &#000\x010060; b</script>"));
	}


		private function avatarTag($html)
	{
		$doc = new DOMDocument();
		$doc->loadHTML('<body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		return $doc->getElementsByTagName('img')->item(0);
	}

	/**
	 * The avatar manager needs the file a user_image names rather than the url that
	 * renders it, and it must never be handed a path outside the avatar folders.
	 * @see https://github.com/e107inc/e107/issues/6023
	 */
	public function testToAvatarPath()
	{
		self::assertSame(e_AVATAR_DEFAULT . 'avatartest.png', $this->tp->toAvatarPath('avatartest.png'));
		self::assertSame(e_AVATAR_UPLOAD . 'avatartest.png', $this->tp->toAvatarPath('-upload-avatartest.png'));

		$rejected = array(
			'',
			'-upload-',
			'.',
			'..',
			'-upload-.',
			'-upload-..',
			'http://mydomain.com/remoteavatar.jpg',
			'https://mydomain.com/remoteavatar.jpg',
			'../../../class2.php',
			'-upload-../../../class2.php',
			'sub/avatartest.png',
			'sub\\avatartest.png',
			"avatartest.png\0.php",
			null,
			array('avatartest.png'),
		);

		foreach ($rejected as $image)
		{
			self::assertSame('', $this->tp->toAvatarPath($image), 'Resolved a path for ' . var_export($image, true));
		}
	}

	/**
	 * An administrator's alt text carries the stored name without its -upload-
	 * prefix, as it did when toAvatar() stripped the prefix itself.
	 */
	public function testToAvatarKeepsTheUploadedNameInTheAdminAltText()
	{
		if (!deftrue('ADMIN'))
		{
			self::markTestSkipped('toAvatar() only puts the raw user_image in alt for an administrator.');
		}

		if (!is_dir(e_AVATAR_UPLOAD))
		{
			mkdir(e_AVATAR_UPLOAD, 0755, true);
		}

		copy(codecept_data_dir() . 'icon_64.png', e_AVATAR_UPLOAD . 'avatartest.png');

		$parms = array('w' => 50, 'h' => 50, 'crop' => false);
		$img = $this->avatarTag($this->tp->toAvatar(array('user_image' => '-upload-avatartest.png'), $parms));

		self::assertEquals('avatartest.png', $img->getAttribute('alt'));
	}

}
