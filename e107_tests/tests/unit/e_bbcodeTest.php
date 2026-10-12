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
			);

			$stored = array();

			foreach($inputs as $input)
			{
				$stored[] = $this->bb->parseBBCodes($input, '', 'default', 'PRE');
			}

			$this->assertSame($inputs, $stored);
		}


		public function testParseBBCodesDisplaysAnUnmatchedOpeningCodeVerbatim()
		{
			$display = $this->bb->parseBBCodes('[b]bold[/b] and [color=#ff0000]unclosed');

			$this->assertStringContainsString('[color=#ff0000]unclosed', $display);
			$this->assertStringNotContainsString('[color]', $display);
		}

	}
