<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_bbcodeTest extends \Test\Unit
	{
		use \Test\HideGlobals;
		use \Test\HtmlAttributes;

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


		public function testIsRegistered()
		{
			$tests = array(
				array('b', true),
				array('youtube', true),
				array('QUOTE', true),
				array('size2', true),
				array('_br', true),
				array('youtube=600', true),
				array('h=2|class=lead', true),
				array('br', false), // the register holds it as _br
				array('html', false),
				array('php', false),
				array('notabbcode', false),
				array('/b', false),
				array(' b', false),
				array('', false),
				array(null, false),
				array(array('b'), false),
			);

			foreach($tests as $test)
			{
				list($code, $expected) = $test;
				self::assertSame($expected, $this->bb->isRegistered($code), var_export($code, true));
			}
		}

		public function testIsRegisteredAcceptsAPluginBbcode()
		{
			$config = e107::getConfig();
			$saved = $config->get('bbcode_list');

			try
			{
				$config->set('bbcode_list', array('myplugin' => array('mycode' => 0)));
				$this->bb->__construct();

				self::assertTrue($this->bb->isRegistered('mycode'));
			}
			finally
			{
				$config->set('bbcode_list', $saved);
				$this->bb->__construct();
			}
		}


		public function testHtmltoBBcode()
		{
			$text = '<h1 style="text-align: center;">Heading 1</h1>
<h2 style="text-align: right;">Heading 2</h2>
<h3 style="text-align: left;">Heading 3</h3>
<h4>Heading 4</h4>
<h5>Heading 5</h5>
<h6>Heading 6</h6>
<div style="background-color: #333; color: white; padding: 10px;">
<p>Paragraph.</p>
<table onclick="alert(1)">
<colgroup>
<col style="width:30%" />
<col style="width:70%" />
</colgroup>
<thead>
<tr><th>Column 1</th><th>Column 2</th></tr>
</thead>
<tbody>
	<tr><td><a href="#">link</a></td><td></td></tr>
</tbody>
</table>
</div>';

			$result = $this->bb->htmltoBbcode($text);

			$expected = strip_tags($result);

			$this->assertSame($expected, $result);

		}

		public function testImgToBBcode()
		{

			$tests = [
				0 => [
					'html'      => '<p><img class="img-rounded rounded bbcode bbcode-img" src="/e107v2/thumb.php?src=e_MEDIA_IMAGE%2F2021-10%2Ftest.jpg&amp;w=0&amp;h=0" alt="test" title="test"></p>',
					'expected'  =>'<p>[img title=test]{e_MEDIA_IMAGE}2021-10/test.jpg[/img]</p>'
				],

			/*	1 => [
					'html'      => '<p><img class="img-rounded rounded bbcode bbcode-img" src="/e107v2/thumb.php?src=e_MEDIA_IMAGE%2F2021-10%2Ftest.jpg&w=0&h=0" alt="test" title="test"></p>',
					'expected'  =>'<p>[img title=test]{e_MEDIA_IMAGE}2021-10/test.jpg[/img]</p>'
				],*/

			];


			foreach($tests as $count => $t)
			{
				$actual = $this->bb->imgToBBcode($t['html']);
				$this->assertSame($t['expected'], $actual, 'Test '.$count.' failed');

			}

		}
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
						'Ted'  => array('input'=>'Quoted Message', 'expected'=>'<blockquote><p>Quoted Message</p><small><cite title="Ted">Ted</cite></small></blockquote>'),

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


		public function testParseBBCodesKeepsAnUnmatchedOpeningCodeVerbatim()
		{
			$inputs = array(
				'[b]bold[/b] and [color=#ff0000]unclosed',
				'[b]bold[/b] and [size2]unclosed',
				'[b]bold[/b] and [URL=https://example.com]a link',
				'Our logo lives at [/img/logo.png] and here is [url=https://example.com/a?b=c]a link',
				'[size2]hello[b]world[/size]',
				'[h1]Title [b]bold[/h]',
				'[size2=20]a [b]b[/size2]',
				'[hide]a [b]b[/hide]',
			);

			$stored = array();

			foreach($inputs as $input)
			{
				$stored[] = $this->bb->parseBBCodes($input, '', 'default', 'PRE');
			}

			$this->assertSame($inputs, $stored);
		}


		/**
		 * Saving keeps a post as typed, for posts put together at random from codes left open and closed out of order.
		 */
		public function testSavingKeepsAPostAsTyped()
		{
			$tags = array('[b]', '[/b]', '[i]', '[/i]', '[size2=3]', '[/size2]', '[size=3]', '[/size]', '[hide]', '[/hide]', '[hide2]', '[/hide2]', '[color=red]', '[/color]', '[x', '[/b2]');

			mt_srand(6573);

			try
			{
				for($i = 0; $i < 500; $i++)
				{
					$post = '';

					for($j = mt_rand(2, 12); $j > 0; $j--)
					{
						$post .= mt_rand(0, 2) ? $tags[mt_rand(0, count($tags) - 1)] : 'w' . $j . ' ';
					}

					self::assertSame($post, $this->bb->parseBBCodes($post, '', 'default', 'PRE'));
				}
			}
			finally
			{
				mt_srand();
			}
		}


		public function testParseBBCodesDisplaysAnUnmatchedOpeningCodeVerbatim()
		{
			$display = $this->bb->parseBBCodes('[b]bold[/b] and [color=#ff0000]unclosed');

			$this->assertStringContainsString('[color=#ff0000]unclosed', $display);
			$this->assertStringNotContainsString('[color]', $display);
		}


		public function testParseBBCodesPairsAClosingCodeOnlyWithAnOpeningCodeOfTheSameDigits()
		{
			$tests = array(
				'[h1]Title [b]bold[/h][/b][/h1]'           => "<h2 class='bbcode-h2'>Title <strong class='bbcode bold bbcode-b'>bold[/h]</strong></h2>",
				'[size2=20]Big [b]bold[/size][/b][/size2]' => "<span class='bbcode-size' style='font-size:20px'>Big <strong class='bbcode bold bbcode-b'>bold[/size]</strong></span>",
			);

			foreach($tests as $text => $expected)
			{
				self::assertSame($expected, $this->bb->parseBBCodes($text), $text);
			}
		}


		public function testAnOpenNumberedCodeInsideATableStillRendersTheTable()
		{
			$html = e107::getParser()->toHTML('[table][tr][td][h2]Title[/td][/tr][/table]', true);

			self::assertStringContainsString('<td>Title</td>', $html, $html);
		}


		/**
		 * @dataProvider hideBlocks
		 * @param string $text
		 */
		public function testAHideBlockHidesItsContent($text)
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				foreach(array($text, $tp->toDB($text)) as $row)
				{
					$this->allowHiddenContent(false);
					$hidden = $tp->toHTML($row, true);
					$this->allowHiddenContent(true);
					$shown = $tp->toHTML($row, true);

					self::assertStringNotContainsString('answer', $hidden, $row);
					self::assertStringNotContainsString('[/hide', $hidden, $row);
					self::assertStringContainsString('The answer is 42', $shown, $row);
					self::assertStringNotContainsString('[/hide', $shown, $row);
				}
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function hideBlocks()
		{
			return array(
				'a plain block'                                        => array('[hide]The answer is 42[/hide]'),
				'a numbered code left open straight after the opening' => array('[hide][h2]The answer is 42[/hide]'),
				'a parameter'                                          => array('[hide=x]The answer is 42[/hide]'),
				'digits'                                               => array('[hide2]The answer is 42[/hide]'),
				'digits with a leading zero'                           => array('[hide02]The answer is 42[/hide]'),
				'digits and a parameter'                               => array('[hide2=x]The answer is 42[/hide]'),
				'a longer word'                                        => array('[hideme]The answer is 42[/hide]'),
				'digits closed by their own tag, then a plain block'   => array('[hide2]The answer is 42[/hide2] and [hide]more[/hide]'),
				'digits closed by their own tag with a leading zero'   => array('[hide02]The answer is 42[/hide2] and [hide]more[/hide]'),
				'digits holding a block of the same digits'            => array('[hide2]The answer is 42 [hide2]again[/hide2][/hide]'),
				'upper case'                                           => array('[HIDE]The answer is 42[/HIDE]'),
				'mixed case and a parameter'                           => array('[Hide=x]The answer is 42[/hIDE]'),
				'inside a table'                                       => array('[table][tr][td][hide]The answer is 42[/hide][/td][/tr][/table]'),
				'text typed before a code left open'                   => array('[hide]The answer is 42 [b]x[/hide]'),
				'a code block never closed'                            => array('[hide]The answer is 42 [code]x[/hide]'),
				'a code block closed only after the block'             => array('[hide]The answer is 42 [code]x[/hide] [code]y[/code]'),
			);
		}


		public function testAPairLeftToTheHideBbcodeWritesItsParameterBackAsText()
		{
			global $e_hide_block;

			$was = $e_hide_block;
			$e_hide_block = null;

			try
			{
				self::assertSame('[hide=&lt;i title=&quot;x&quot;&gt;]y[/hide]', $this->bb->parseBBCodes('[hide=<i title="x">]y[/hide]', ''));
				self::assertSame('[hide=a&amp;b]y[/hide]', $this->bb->parseBBCodes('[hide=a&amp;b]y[/hide]', ''));
			}
			finally
			{
				$e_hide_block = $was;
			}
		}


		/**
		 * @dataProvider hideBlockSpellings
		 * @param string $text
		 * @param array  $expected each block as array(open, content, close)
		 */
		public function testHideBlocksReportsEachBlockAsTheParserPairsIt($text, $expected)
		{
			$blocks = array();

			foreach($this->bb->hideBlocks($text) as $block)
			{
				self::assertSame($block['open'] . $block['content'] . $block['close'], (string) substr($text, $block['start'], $block['length']), $text);
				$blocks[] = array($block['open'], $block['content'], $block['close']);
			}

			self::assertSame($expected, $blocks, $text);
		}


		public function hideBlockSpellings()
		{
			return array(
				'none'                                => array('No [b]hidden[/b] text', array()),
				'plain'                               => array('a [hide]b[/hide] c', array(array('[hide]', 'b', '[/hide]'))),
				'upper and mixed case'                => array('[HIDE]b[/HIDE] [Hide]c[/hIDE]', array(array('[HIDE]', 'b', '[/HIDE]'), array('[Hide]', 'c', '[/hIDE]'))),
				'parameters'                          => array('[hide=x]b[/hide] [hide:y]c[/hide] [hide z]d[/hide]', array(array('[hide=x]', 'b', '[/hide]'), array('[hide:y]', 'c', '[/hide]'), array('[hide z]', 'd', '[/hide]'))),
				'digits and longer words'             => array('[hide2]b[/hide] [hideme]c[/hide2]', array(array('[hide2]', 'b[/hide] [hideme]c', '[/hide2]'))),
				'a longer word inside'                => array('[hide]a [hideout] b[/hide] c', array(array('[hide]', 'a [hideout] b', '[/hide]'))),
				'a numbered closer inside'            => array('[hide]a[/hide2]b[/hide] c', array(array('[hide]', 'a[/hide2]b', '[/hide]'))),
				'a longer closer inside'              => array('[hide]a[/hideme]b[/hide] c', array(array('[hide]', 'a[/hideme]b', '[/hide]'))),
				'digits around a longer word'         => array('[hide2]a [hideout] b[/hide] c', array(array('[hide2]', 'a [hideout] b', '[/hide]'))),
				'nested, outermost only'              => array('[hide]a [hide]b[/hide] c[/hide]', array(array('[hide]', 'a [hide]b[/hide] c', '[/hide]'))),
				'a code left open inside'             => array('[hide]a [b]b[/hide] c', array(array('[hide]', 'a [b]b', '[/hide]'))),
				'never closed'                        => array('a [hide]b', array()),
				'only a closing tag'                  => array('a b[/hide]', array()),
				'inside a code block'                 => array('[code][hide]b[/hide][/code]', array()),
				'inside a table'                      => array('[table][tr][td][hide]b[/hide][/td][/tr][/table]', array(array('[hide]', 'b', '[/hide]'))),
				'stored as HTML'                      => array('[html]<p>[hide]b[/hide]</p>[/html]', array(array('[hide]', 'b', '[/hide]'))),
				'across lines'                        => array("a\n[hide]\nb\n[/hide]\nc", array(array('[hide]', "\nb\n", '[/hide]'))),
				'a stray bracket in front'            => array('a [x[hide]b[/hide]', array(array('[hide]', 'b', '[/hide]'))),
				'a marker inside the word'            => array('[html]<p>[hi[html]de]b[/hide]</p>[/html]', array()),
				'a code block never closed inside'    => array('[hide]a [code]b[/hide] c', array(array('[hide]', 'a [code]b', '[/hide]'))),
				'a code block never closed in front'  => array('[code]a [hide]b[/hide]', array(array('[hide]', 'b', '[/hide]'))),
				'a code block closed after the block' => array('[hide]a [code]b[/hide] c [code]d[/code]', array(array('[hide]', 'a [code]b', '[/hide]'))),
				'a closer inside a closed code block' => array('[hide]a [code][/hide][/code] b[/hide]', array(array('[hide]', 'a [code][/hide][/code] b', '[/hide]'))),
			);
		}


		/**
		 * The topic page renders a block as hidden exactly where {@see e_bbcode::hideBlocks()} reports one, for posts put together at random from hide tags spelled every way the parser reads them, interrupted by other bbcode.
		 */
		public function testToHtmlHidesExactlyTheBlocksHideBlocksReports()
		{
			$pieces = array('[html]', '[/html]', '[b][/b]', '[i]', '[/i]', '[code]', '[/code]');
			$containers = array(array('', ''), array('[html]<p>', '</p>[/html]'), array('', ' [html]<p>c</p>[/html]'), array('[table]', '[/table]'), array('[hidex]', '[/hide]'), array('[hide]o ', ' [/hide]'));
			$spell = function ($close) use ($pieces)
			{
				$tag = '[' . ($close ? '/' : '');

				foreach(array('h', 'i', 'd', 'e') as $letter)
				{
					$tag .= (mt_rand(0, 4) === 0 ? $pieces[mt_rand(0, count($pieces) - 1)] : '') . (mt_rand(0, 3) === 0 ? strtoupper($letter) : $letter);
				}

				$suffixes = array('', '', '2', '=a', ' x', 'me', '02');

				return $tag . $suffixes[mt_rand(0, count($suffixes) - 1)] . ']';
			};

			mt_srand(6573);
			$this->holdHideGlobals();

			try
			{
				for($i = 0; $i < 240; $i++)
				{
					$container = $containers[$i % count($containers)];

					$this->assertThePageHidesSecretExactlyWhereABlockHoldsIt($container[0] . 'Before ' . $spell(false) . ' SECRET ' . $spell(true) . ' after' . $container[1]);
				}
			}
			finally
			{
				mt_srand();
				$this->restoreHideGlobals();
			}
		}


		/**
		 * A [hide] block the parser pairs inside what toHTML() takes for a [code] block renders as its box, never as its marker.
		 */
		public function testAHideBlockAfterACodeTagClosedWithOtherDigitsRendersItsBox()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				foreach(array(true, false) as $allowed)
				{
					$this->allowHiddenContent($allowed);
					$html = $tp->toHTML('[code][/code2][hide]The answer is 42[/hide][HIDE][/code]', true);

					self::assertStringNotContainsString('e_hide_', $html);
					self::assertSame($allowed, strpos($html, 'The answer is 42') !== false, $html);
				}
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		/**
		 * The topic page decides blocks on the text as stored, before any of its own rewriting.
		 *
		 * @dataProvider postsRewrittenBeforeRendering
		 * @param string $post
		 */
		public function testBlocksAreDecidedOnTheTextAsStored($post)
		{
			$this->holdHideGlobals();

			try
			{
				$this->assertThePageHidesSecretExactlyWhereABlockHoldsIt($post);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function postsRewrittenBeforeRendering()
		{
			return array(
				'a closer after a centred opening' => array('[center]<p>p</p>[hide]A[b=[/center][/hide]SECRET[/hide] tail'),
				'a block after a centred opening'  => array('[center]<p>a</p>[b=[/center][code][hide]SECRET[/hide] tail'),
			);
		}


		/**
		 * @param string $post holding the word SECRET once
		 * @return void
		 */
		private function assertThePageHidesSecretExactlyWhereABlockHoldsIt($post)
		{
			$tp = e107::getParser();
			$secret = strpos($post, 'SECRET');
			$inBlock = false;

			foreach($this->bb->hideBlocks($post) as $block)
			{
				$inBlock = $inBlock || ($secret >= $block['start'] && $secret < $block['start'] + $block['length']);
			}

			$this->allowHiddenContent(false);
			$hidden = $tp->toHTML($post, true);
			$this->allowHiddenContent(true);
			$shown = $tp->toHTML($post, true);

			self::assertSame($inBlock, (strpos($hidden, 'SECRET') === false) && (strpos($shown, 'SECRET') !== false), $post);
		}


		/**
		 * Text the parser does not pair as a [hide] block is shown: a tag that only reads as [hide] once something inside it is taken out, one never closed, one inside [code].
		 *
		 * @dataProvider tagsThatAreNotBlocks
		 * @param string $text
		 */
		public function testATagThatIsNotABlockHidesNothing($text)
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				$this->allowHiddenContent(false);

				self::assertStringContainsString('The answer is 42', $tp->toHTML($text, true), $text);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function tagsThatAreNotBlocks()
		{
			return array(
				'a marker inside the word'  => array('[html]<p>[hi[html]de]The answer is 42[/hide]</p>[/html]'),
				'never closed'              => array('[hide]The answer is 42'),
				'inside a code block'       => array('[code][hide]The answer is 42[/hide][/code]'),
			);
		}


		/**
		 * A block a bbcode takes into an attribute is left out there, so what a reader is shown can never add an attribute to the tag around it.
		 *
		 * @dataProvider blocksInsideAnAttribute
		 * @param string $text
		 * @param array  $prefs core preferences to render with
		 */
		public function testABlockNeverRendersInsideAnAttribute($text, $prefs)
		{
			$restore = $this->withCorePrefs($prefs);
			$this->holdHideGlobals();

			try
			{
				$tp = $this->make('e_parse');
				$tp->__construct();
				$html = $tp->toHTML($text, true);
				$document = new DOMDocument();
				@$document->loadHTML('<html><body>' . $html . '</body></html>');

				foreach((new DOMXPath($document))->query('//@*') as $attribute)
				{
					self::assertStringStartsNotWith('on', strtolower($attribute->nodeName), $html);
					self::assertStringNotContainsString('<div', $attribute->nodeValue, $html);
				}
			}
			finally
			{
				$this->restoreHideGlobals();
				$restore();
			}
		}


		public function blocksInsideAnAttribute()
		{
			return array(
				'a url'                  => array('[url]http://example.com/[hide][url=http://a.example/x/onmouseover=alert(document.domain)//]a[/url][/hide][/url]', array()),
				'a link'                 => array('[link]http://example.com/[hide][url=http://a.example/x/onmouseover=alert(1)//]a[/url][/hide][/link]', array()),
				'an address made a link' => array('see http://example.com/[hide][url=http://a.example/x/onmouseover=alert(1)//]a[/url][/hide] now', array('make_clickable' => 1)),
			);
		}


		/**
		 * A tag that only begins with the word, inside a block, never ends the block before its own [/hide].
		 *
		 * @dataProvider tagsThatOnlyBeginWithTheWord
		 * @param string $text
		 */
		public function testATagThatOnlyBeginsWithTheWordKeepsTheBlockClosed($text)
		{
			$this->holdHideGlobals();

			try
			{
				$this->allowHiddenContent(false);

				self::assertStringNotContainsString('SECRET', e107::getParser()->toHTML($text, true), $text);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function tagsThatOnlyBeginWithTheWord()
		{
			return array(
				'a longer word'                                   => array('[hide]SECRET [hideout] map[/hide] after'),
				'a longer word, glued'                            => array('[hide]SECRET[hideme]B[/hide] tail'),
				'a numbered closer'                               => array('[hide]A[/hide2]SECRET[/hide] tail'),
				'a longer closer'                                 => array('[hide]A[/hideme]SECRET[/hide] tail'),
				'around a longer word, opened with digits'        => array('[hide2]SECRET [hideout] map[/hide] after'),
				'around a longer word, opened with a longer word' => array('[hideme]SECRET [hideout] map[/hide] after'),
			);
		}


		/**
		 * A block whose marker a bbcode encodes is left out, and nothing of the marker reaches the page.
		 *
		 * @dataProvider blocksInsideEncodedContent
		 * @param string $text
		 */
		public function testNoMarkerIsLeftInThePage($text)
		{
			$this->holdHideGlobals();

			try
			{
				self::assertStringNotContainsString('e_hide', e107::getParser()->toHTML($text, true));
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function blocksInsideEncodedContent()
		{
			return array(
				'an image' => array('[img]http://x.example/a[hide]The answer is 42[/hide].png[/img]'),
				'a url'    => array('[url]http://x.example/[hide]The answer is 42[/hide][/url]'),
			);
		}


		/**
		 * The tag mentioned in prose, or typed and never closed, leaves the text after it in view.
		 *
		 * @dataProvider mentionsOfTheTag
		 * @param string $text
		 * @param string $visible
		 */
		public function testAMentionOfTheTagHidesNothingAfterIt($text, $visible)
		{
			$this->holdHideGlobals();

			try
			{
				$this->allowHiddenContent(false);

				self::assertStringContainsString($visible, strip_tags(e107::getParser()->toHTML($text, true)), $text);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function mentionsOfTheTag()
		{
			return array(
				'before a block'    => array('To hide a link, wrap it in [hide] like this: [hide]http://example.com/file[/hide] Thanks.', 'like this:'),
				'before two blocks' => array('Note [hide] ignores case. First: [HIDE]a[/HIDE] and second: [hide]b[/hide]. End.', 'ignores case. First:'),
			);
		}


		/**
		 * Taking a block out of an address never joins the text either side of it.
		 *
		 * @dataProvider blocksInsideAnAddress
		 * @param string $text
		 */
		public function testABlockTakenOutOfAnAddressNeverJoinsTheTextEitherSide($text)
		{
			$this->holdHideGlobals();

			try
			{
				foreach(array(false, true) as $allowed)
				{
					$this->allowHiddenContent($allowed);
					$html = e107::getParser()->toHTML($text, true);
					$document = new DOMDocument();
					@$document->loadHTML('<html><body>' . $html . '</body></html>');

					foreach((new DOMXPath($document))->query('//@href|//@src') as $attribute)
					{
						self::assertSame(0, preg_match('#^[\s\x00-\x1f]*(?:javascript|vbscript|data):#i', str_replace(array("\t", "\n", "\r"), '', $attribute->nodeValue)), $html);
					}
				}
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function blocksInsideAnAddress()
		{
			return array(
				'a url'          => array('[url]java[hide]x[/hide]script:alert(1)[/url]'),
				'a link'         => array('[link]java[hide]x[/hide]script:alert(1)[/link]'),
				'another scheme' => array('[url]vb[hide]x[/hide]script:msgbox(1)[/url]'),
			);
		}


		public function testALongTagDoesNotStopABlockFromRendering()
		{
			$this->holdHideGlobals();

			try
			{
				$html = e107::getParser()->toHTML('[html]<p title=' . str_repeat('a', 20000) . '>x</p>[/html] [hide]The answer is 42[/hide]', true);

				self::assertStringContainsString('The answer is 42', $html);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function testWordWrapLeavesAHideBlockWhole()
		{
			$restore = $this->withCorePrefs(array('main_wordwrap' => 20));
			$this->holdHideGlobals();

			try
			{
				$tp = $this->make('e_parse');
				$tp->__construct();

				for($i = 0; $i < 20; $i++)
				{
					self::assertStringContainsString('The answer is 42', $tp->toHTML('Download:[hide]The answer is 42[/hide]', true));
				}
			}
			finally
			{
				$this->restoreHideGlobals();
				$restore();
			}
		}

		public function testParseBBCodesStripsAListedCodeOnlyWhereItIsClosed()
		{
			$tests = array(
				'Problem with [img] tags'           => 'Problem with [img] tags',
				'Where did [/quote] come from?'     => 'Where did [/quote] come from?',
				'See [IMG=left] here'               => 'See [IMG=left] here',
				'[img]a.png[/img] and [img] alone'  => 'a.png and [img] alone',
				'[quote=x]a [img]b[/img][/quote]'   => 'a b',
				'line [br] break'                   => 'line  break',
			);

			foreach($tests as $text => $expected)
			{
				self::assertSame($expected, $this->bb->parseBBCodes($text, '', 'default', 'img,quote,br'), $text);
			}

			self::assertSame('bold, [img] alone', $this->bb->parseBBCodes('[b]bold[/b], [img] alone', '', 'default', true));
		}

		public function testGetRegisteredMapsEachCodeToWhereItsHandlerLives()
		{
			$config = e107::getConfig();
			$saved = $config->get('bbcode_list');

			try
			{
				$config->set('bbcode_list', array('myplugin' => array('mycode' => 0, '_mysingle' => 0, 'b' => 0)));
				$this->bb->__construct();

				$registered = $this->bb->getRegistered();
			}
			finally
			{
				$config->set('bbcode_list', $saved);
				$this->bb->__construct();
			}

			self::assertSame('myplugin', $registered['mycode']);
			self::assertSame('myplugin', $registered['_mysingle']);
			self::assertSame('myplugin', $registered['b']);
			self::assertSame('core', $registered['i']);
			self::assertSame('core', $registered['_br']);
			self::assertArrayNotHasKey('br', $registered);
			self::assertArrayNotHasKey('html', $registered);
		}

		/**
		 * Each region of a nest goes through the parser once, so a nest twice as deep hands it about twice the text.
		 */
		public function testANestTwiceAsDeepHandsTheParserAboutTwiceTheText()
		{
			require_once(__DIR__ . '/fixtures/BBCodeParserByteCounter.php');

			$tp = e107::getParser();
			$parser = e107::getRegistry('core/e107/singleton/e_bbcode');
			$counter = new BBCodeParserByteCounter();
			$bytes = array();
			e107::setRegistry('core/e107/singleton/e_bbcode', $counter);
			$this->holdHideGlobals();

			try
			{
				foreach(array(40, 80) as $depth)
				{
					$counter->bytes = 0;
					$tp->toHTML(str_repeat('[hide]a ', $depth) . 'core' . str_repeat(' c[/hide]', $depth), true);
					$bytes[$depth] = $counter->bytes;
				}
			}
			finally
			{
				$this->restoreHideGlobals();
				e107::setRegistry('core/e107/singleton/e_bbcode', $parser);
			}

			self::assertLessThan(2.25 * $bytes[40], $bytes[80], json_encode($bytes));
		}


		/**
		 * @dataProvider nestsAndTheirTagsSideBySide
		 * @param string $nest
		 * @param string $sideBySide the same tags, each block closed before the next opens
		 * @param bool   $allowed whether the reader may see what a block holds
		 * @param float  $limit
		 */
		public function testANestTakesAboutAsLongAsItsTagsSideBySide($nest, $sideBySide, $allowed, $limit)
		{
			$tp = e107::getParser();
			$took = array('nest' => INF, 'side by side' => INF);
			$this->holdHideGlobals();
			$this->allowHiddenContent($allowed);

			try
			{
				for($i = 0; $i < 3; $i++)
				{
					foreach(array('nest' => $nest, 'side by side' => $sideBySide) as $shape => $text)
					{
						$start = microtime(true);
						$tp->toHTML($text, true);
						$took[$shape] = min($took[$shape], microtime(true) - $start);
					}
				}
			}
			finally
			{
				$this->restoreHideGlobals();
			}

			self::assertLessThan($limit, $took['nest'] / $took['side by side'], json_encode($took));
		}


		public function nestsAndTheirTagsSideBySide()
		{
			return array(
				'bold tags left open in nested blocks, hidden' => array(str_repeat('[hide]a ', 300) . str_repeat('[b]', 300) . 'core' . str_repeat(' c[/hide]', 300), str_repeat('[hide]a [b]core c[/hide] ', 300), false, 4),
				'nested blocks, shown'                         => array(str_repeat('[hide]a ', 120) . 'core' . str_repeat(' c[/hide]', 120), str_repeat('[hide]a c[/hide] ', 120), true, 8),
				'a deep nest, shown'                           => array(str_repeat('[hide]a ', 4000) . 'core' . str_repeat(' c[/hide]', 4000), str_repeat('[hide]a c[/hide] ', 4000), true, 2.5),
			);
		}


		public function testEveryBlockOfADeepNestIsBoxedForAReaderWhoMaySeeIt()
		{
			$tp = e107::getParser();
			$nest = str_repeat('[hide]a ', 40) . 'core' . str_repeat(' c[/hide]', 40);
			$this->holdHideGlobals();

			try
			{
				list($open, $close) = $this->shownBox();
				self::assertSame(str_repeat($open . 'a ', 40) . 'core' . str_repeat(' c' . $close, 40), $tp->toHTML($nest, true));

				$this->allowHiddenContent(false);
				self::assertSame($tp->toHTML('[hide]X[/hide]', true), $tp->toHTML($nest, true));
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function testBlankLinesAfterNestedBlocksLoseTheirBreaksAsBefore()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				list($open, $close) = $this->shownBox();
				self::assertSame($open . 'a<br />' . $open . 'b' . $close . 'c' . $close, $tp->toHTML("[hide]a\n[hide]b[/hide]\n\nc[/hide]", true));
				self::assertSame($open . 'a ' . $open . 'b ' . $open . 'c' . $close . 'd' . $close . 'e' . $close, $tp->toHTML("[hide]a [hide]b [hide]c[/hide]\n\nd[/hide]\n\ne[/hide]", true));
				self::assertSame($open . 'a ' . $open . 'b ' . $open . 'c' . $close . '<br /><br /><br />d' . $close . $close, $tp->toHTML("[hide]a [hide]b [hide]c[/hide]\n\n\n\n\n\nd[/hide][/hide]", true));
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		/**
		 * A [code] left holding the closer of the first block makes the whole post read past [code] blocks, but the second block's content is read on its own, where its [code] still holds the [hide] as text.
		 */
		public function testAHideBlockInsideACodeBlockOfAnotherBlockStaysCode()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				list($open) = $this->shownBox();
				$html = $tp->toHTML('[hide][code][/hide][/code] [hide][code][hide]B[/hide][/code][/hide]', true);
				self::assertStringContainsString('&#091;hide]B&#091;/hide]', $html);
				self::assertStringNotContainsString($open . 'B', $html);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function testALongRunOfLineBreaksAfterABlockElementIsKept()
		{
			$tp = e107::getParser();
			$html = $tp->toHTML($tp->toDB('[center]x[/center]' . str_repeat("\r\n", 25000) . 'TAIL'), true);

			self::assertStringEndsWith('TAIL', $html);
			self::assertSame(24999, substr_count($html, '<br />'));
		}


		/**
		 * A block whose content starts inside a [code] block of the text around it reads its own [code] blocks, so a tag it holds outside them is written as text.
		 */
		public function testANestedBlockReadsCodeBlocksAsItsOwnContentDoes()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				$html = $tp->toHTML('[hide][hide][code]a[/code1] [hide][code2]x[/code] [code4]w [code3]y[/code2] <b z autofocus tabindex=0 onfocus=print() [/code3][/hide][/hide][/hide]', true);
				self::assertStringContainsString('&lt;b z autofocus', $html);
				self::assertStringNotContainsString('<b z', $html);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		/**
		 * A block whose closing tag ends a tag begun in its content reads that tag as text, as its content does alone.
		 */
		public function testANestedBlockReadsATagItsCloserEndsAsText()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				$html = $tp->toHTML('[hide][hide][hide]<img src=x onerror=print() [/hide=>][/hide][/hide]', true);
				self::assertStringContainsString('&lt;img src=x', $html);
				self::assertStringNotContainsString('<img', $html);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		/**
		 * A block whose opening tag begins a tag its content goes on with reads that tag as text, as its content does alone.
		 */
		public function testANestedBlockReadsATagItsOpenerBeginsAsText()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				$html = $tp->toHTML('[hide][hide][hide <b title="]X <i onmouseover=print() ">Y[/hide][/hide][/hide]', true);
				self::assertSame(array(), $this->handlerAttributes($html), $html);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		/**
		 * The tags of a block refused for straddling a tag are not paired again by the renderer, which would write the opener's parameter back as markup.
		 */
		public function testARefusedBlockInsideANestIsNotPairedByTheRenderer()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				$html = $tp->toHTML('<p>[hide]a [hide]b [hide <i title=&quot;][/hide][hide]&quot;>][quote][/hide x][/hide] c[/hide] x&quot; autofocus tabindex=0 onfocus=&quot;print()&quot; y=</i></p>', true);
				$document = new DOMDocument();
				@$document->loadHTML('<div>' . $html . '</div>');
				self::assertSame(0, (new DOMXPath($document))->query('//@onfocus')->length, $html);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		/**
		 * A [hide] that the opening tag of a block left in a nested region holds is written as text with it, so the renderer cannot pair it either.
		 *
		 * @dataProvider openersHoldingAnother
		 * @param string $text as stored
		 */
		public function testAnOpenerInsideTheOpenerOfABlockLeftInANestIsNotPairedByTheRenderer($text)
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				$html = $tp->toHTML($text, true);
				$document = new DOMDocument();
				@$document->loadHTML('<div>' . $html . '</div>');
				self::assertSame(0, (new DOMXPath($document))->query('//@onfocus')->length, $html);
			}
			finally
			{
				$this->restoreHideGlobals();
			}
		}


		public function openersHoldingAnother()
		{
			return array(
				'freed by a refused block'    => array('<p>[hide]b [hide]c [hide[hide <b title=&quot;][/hide][hide &quot;>][quote]<u title=&quot;[b  autofocus tabindex=0 onfocus=print() &quot;>u</u>[/hide][/hide] y[/hide] z[/hide]</b></p>'),
				'closed by a later closer'    => array('<p>[hide]a [hide]b [hide [hide <i title=&quot;]&quot;>[quote]<u title=&quot;[hide][hide] autofocus tabindex=0 onfocus=print() &quot;></u>[/hide][/hide]<u title=&quot;[/hide]&quot;>u</u> c[/hide] d[/hide] z</i></p>'),
				'inside another left as text' => array('<p>[hide]a [hide]b [hide]c [hide]d [quote][]<u title=&quot;[hide]&quot;></u><i title=&quot;][/hide][hide]&quot;>]</i>&quot;&gt;<b><u title=&quot;[hide]&quot;></u><u title=&quot;[b  autofocus tabindex=0 onfocus=print() &quot;>u</u>[hide <b title=&quot;]&lt;u title=&quot;>[b][hidex][hidex][/hide x] w[/hide] x[/hide] y[/hide] z[/hide] x&quot; autofocus tabindex=0 onfocus=&quot;print()&quot; y=</b></b></p>'),
			);
		}


		/**
		 * A nested region is read for [hide] blocks once, however many tags of an opener each block it writes as text would free, so twice the tags hand the parser about twice the text.
		 */
		public function testANestedRegionWithTwiceTheTagsHandsTheParserAboutTwiceTheText()
		{
			require_once(__DIR__ . '/fixtures/BBCodeParserByteCounter.php');

			$tp = e107::getParser();
			$parser = e107::getRegistry('core/e107/singleton/e_bbcode');
			$counter = new BBCodeParserByteCounter();
			$bytes = array();
			e107::setRegistry('core/e107/singleton/e_bbcode', $counter);
			$this->holdHideGlobals();

			try
			{
				foreach(array(100, 200) as $tags)
				{
					$counter->bytes = 0;
					$tp->toHTML('<p>[hide]a [hide]b ' . str_repeat('[hide ', $tags) . 'X] ' . str_repeat('<i title=&quot;[hide]&quot;></i>[/hide] ', $tags) . '<u title=&quot;[/hide]&quot;>u</u> c[/hide] d[/hide]</p>', true);
					$bytes[$tags] = $counter->bytes;
				}
			}
			finally
			{
				$this->restoreHideGlobals();
				e107::setRegistry('core/e107/singleton/e_bbcode', $parser);
			}

			self::assertLessThan(2.25 * $bytes[100], $bytes[200], json_encode($bytes));
		}


		/**
		 * A nest of blocks each read in a view of its own, each followed by another block, renders in little memory.
		 */
		public function testANestOfBlocksReadInViewsOfTheirOwnRendersInLittleMemory()
		{
			$tp = e107::getParser();
			$this->holdHideGlobals();

			try
			{
				$bytes = $this->peakBytesOf(function () use ($tp)
				{
					$tp->toHTML('[hide][hide]' . str_repeat('[code]a[/code1][hide][/code]', 399) . '[code]a[/code1][hide]<b [/code][/hide][hide]s[/hide]' . str_repeat('[/hide][hide]s[/hide]', 399) . '[/hide][/hide]', true);
				});
			}
			finally
			{
				$this->restoreHideGlobals();
			}

			self::assertLessThan(32 * 1048576, $bytes);
		}


		/**
		 * The readings of a nest take about as long as those of the same number of blocks side by side, however the closers pair.
		 */
		public function testHideBlocksOfANestTakesAboutAsLongAsOfBlocksSideBySide()
		{
			$took = array();

			foreach(array('nest' => str_repeat('[hide2]a ', 2000) . 'core' . str_repeat(' c[/hide]', 2000), 'side by side' => str_repeat('[hide]a c[/hide] ', 2000)) as $shape => $text)
			{
				$took[$shape] = INF;

				for($i = 0; $i < 3; $i++)
				{
					$start = microtime(true);
					$this->bb->hideBlocks($text);
					$took[$shape] = min($took[$shape], microtime(true) - $start);
				}
			}

			self::assertLessThan(4, $took['nest'] / $took['side by side'], json_encode($took));
		}


		public function testManyTagsInsideOneOpenTagParseInLittleMemory()
		{
			$bytes = $this->peakBytesOf(function ()
			{
				$this->bb->parseBBCodes('[quote]' . str_repeat('[b]x[/b] ', 4000) . '[/quote]', '');
			});

			self::assertLessThan(16 * 1048576, $bytes);
		}


		public function testHideBlockTreeNamesTheBlockEachLiesIn()
		{
			self::assertSame(array(
				array('start' => 2, 'length' => 31, 'open' => '[hide]', 'close' => '[/hide]', 'parent' => -1),
				array('start' => 10, 'length' => 14, 'open' => '[hide]', 'close' => '[/hide]', 'parent' => 0),
				array('start' => 34, 'length' => 14, 'open' => '[hide]', 'close' => '[/hide]', 'parent' => -1),
			), $this->bb->hideBlockTree('a [hide]b [hide]c[/hide] d[/hide] [hide]e[/hide]'));
		}


		public function testAClosingTagThatPairsBelowTheTopLeavesTheTagsAboveItOpen()
		{
			self::assertSame("x<em class='bbcode italic bbcode-i'><strong class='bbcode bold bbcode-b'>y</strong>z</em>", $this->bb->parseBBCodes('[b]x[i]y[/b]z[/i]', ''));
			self::assertSame("x[i]<strong class='bbcode bold bbcode-b'>y</strong> z", $this->bb->parseBBCodes('[b]x[i]y[/b] z', ''));
		}


		public function testThePairAfterACodeWithNoFileIsHandedWhatThatCodeGave()
		{
			$config = e107::getConfig();
			$saved = $config->get('bbcode_list');

			try
			{
				$config->set('bbcode_list', array('removedplugin' => array('gone' => 0)));
				$this->bb->__construct();

				self::assertSame("<strong class='bbcode bold bbcode-b'></strong>", $this->bb->parseBBCodes('[b][gone]a[/gone][url][/url][/b]', ''));
			}
			finally
			{
				$config->set('bbcode_list', $saved);
				$this->bb->__construct();
			}
		}


		public function testASealedBlockNestedThreeDeepIsOpenedInPlace()
		{
			global $e_hide_unseal;

			$tp = e107::getParser();
			$was = $e_hide_unseal;
			$e_hide_unseal = function ($sealed)
			{
				return ($sealed === 'x..y.z.w') ? 'opened [b]b[/b] [hide]in[/hide]' : null;
			};
			$this->holdHideGlobals();

			try
			{
				list($open, $close) = $this->shownBox();
				self::assertSame($open . 'a ' . $open . 'b ' . $open . "opened <strong class='bbcode bold bbcode-b'>b</strong> " . $open . 'in' . $close . $close . $close . $close, $tp->toHTML('[hide]a [hide]b [hide]x..y.z.w[/hide][/hide][/hide]', true));
				$this->allowHiddenContent(false);
				$hidden = $tp->toHTML('[hide]X[/hide]', true);
				$this->allowHiddenContent(true);
				self::assertSame($open . 'a ' . $open . 'b ' . $hidden . $close . $close, $tp->toHTML('[hide]a [hide]b [hide]x..y.z.q[/hide][/hide][/hide]', true));
			}
			finally
			{
				$this->restoreHideGlobals();
				$e_hide_unseal = $was;
			}
		}


		/**
		 * The bytes $run takes at its peak over those in use before it.
		 *
		 * @param callable $run
		 * @return int
		 */
		private function peakBytesOf($run)
		{
			if (!function_exists('memory_reset_peak_usage'))
			{
				self::markTestSkipped('A peak is measured from a reset, which needs PHP 8.2.');
			}

			$before = memory_get_usage();
			memory_reset_peak_usage();
			$run();

			return memory_get_peak_usage() - $before;
		}


		/**
		 * The box of a [hide] block as rendered for a reader who may see what it holds, split where its content goes.
		 *
		 * @return string[] the opening and the closing of the box
		 */
		private function shownBox()
		{
			$this->allowHiddenContent(true);

			return explode('X', e107::getParser()->toHTML('[hide]X[/hide]', true), 2);
		}

	}
