<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Bbcode tags whose ] lies inside an HTML tag or comment that began after their [, or past a tag of an element whose content a browser does not read as HTML, in text as toDB() stores it for an author who may post HTML.
 */
class e_parseBBCodeTagInsideHtmlTagTest extends \Codeception\Test\Unit
{
	use \Test\HtmlAttributes;
	use \Test\HideGlobals;

	/**
	 * @return array
	 */
	public function bbcodeTagsEndingInsideATag()
	{
		$tail = ' w x&quot; autofocus tabindex=0 onfocus=&quot;alert(1)&quot; y=';
		$link = ' <u title=&quot;x] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>u</u>';

		return array(
			'quote, parameter after a space'   => array('<p>[quote <u title=&quot;x]&quot;>u</u>[/quote]'.$tail.'</p>', '//u[@title="x]"]'),
			'quote, parameter after ='         => array('<p>[quote=<u title=&quot;x]&quot;>u</u>[/quote]'.$tail.'</p>', '//u[@title="x]"]'),
			'quote, in an html block'          => array('[html]<p>[quote <u title=&quot;x]&quot;>u</u>[/quote]'.$tail.'</p>[/html]', '//u[@title="x]"]'),
			'quote, in a table block'          => array('[table][tr][td][quote <u title=&quot;x]&quot;>u</u>[/quote]'.$tail.'[/td][/tr][/table]', '//u[@title="x]"]'),
			'quote, in a shown hide block'     => array('<p>[hide][quote <u title=&quot;x]&quot;>u</u>[/quote]'.$tail.'[/hide]</p>', '//u[@title="x]"]'),
			'closing bold, inside a comment'   => array('<p>[b]a[/b <!-- ]<u tabindex=0 autofocus onfocus=alert(1)>x</u> --></p>', '//comment()[contains(., "onfocus")]'),
			'table block, link in the title'   => array('<p>[table'.$link.'[/table]</p>', '//u[starts-with(@title, "x] ")]'),
			'table block alone, link in the title' => array('[table'.$link.'[/table]', '//u[starts-with(@title, "x] ")]'),
			'hide block alone, link in the title'  => array('[hide'.$link.'[/hide]', '//u[starts-with(@title, "x] ")]'),
			'code block alone, link in the title'  => array('[code'.$link.'[/code]', '//u[starts-with(@title, "x] ")]'),
		);
	}

	/**
	 * @dataProvider bbcodeTagsEndingInsideATag
	 * @param string $stored
	 * @param string $kept an XPath query for the tag or comment the bbcode tag would have cut, whole
	 */
	public function testABbcodeTagEndingInsideATagLeavesTheTagWhole($stored, $kept)
	{
		$html = $this->rendered($stored);

		self::assertSame(array(), $this->handlerAttributes($html), 'A bbcode tag ending inside a tag added a handler: '.$html);
		self::assertNotEmpty($this->attributesOf($html, $kept), 'The tag the bbcode tag ended inside was cut: '.$html);
	}

	/**
	 * @return array
	 */
	public function blockTagsEndingInsideATag()
	{
		return array(
			'code'               => array('[code <u title=&quot;x]&quot;>u</u>[/code]', '[code u[/code]'),
			'table'              => array('[table <u title=&quot;x]&quot;>u</u>[/table]', '[table u[/table]'),
			'html'               => array('[html <u title=&quot;x]&quot;>u</u>[/html]', '[html u[/html]'),
			'code, closing tag'  => array('[code]a[/code <u title=&quot;x]&quot;>u</u>', '[code]a[/code u'),
			'code, in an html block' => array('[html][code <u title=&quot;x]&quot;>u</u>[/code][/html]', '[code u[/code]'),
			'code, closing tag, in an html block' => array('[html][code]a[/code <u title=&quot;x]&quot;>u</u>[/html]', '[code]a[/code u'),
		);
	}

	/**
	 * @dataProvider blockTagsEndingInsideATag
	 * @param string $stored
	 * @param string $text the text a reader sees
	 */
	public function testABlockTagEndingInsideATagIsReadAsText($stored, $text)
	{
		$html = $this->rendered($stored);

		self::assertSame(array($text), $this->attributesOf($html, '/html/body/div'), $html);
		self::assertSame(array('x]'), $this->attributesOf($html, '//u/@title'), $html);
	}

	/**
	 * An iframe in the bbcode tag, whose text a browser reads as text, is not left to carry the tag past a ] inside a tag.
	 */
	public function testABbcodeTagHoldingAnIframeIsReadAsText()
	{
		$html = $this->rendered('<p>[quote <iframe><!--</iframe><u title=&quot;--&gt;]&quot;>u</u>[/quote] x&quot; autofocus tabindex=0 onfocus=&quot;alert(1)&quot; y=</p>');

		self::assertEmpty($this->attributesOf($html, '//blockquote'), $html);
	}

	/**
	 * @return array
	 */
	public function walkerReadings()
	{
		return array(
			'render'                    => array('render', '[quote <u title="x]">u</u>[/quote]', '[quote <u title="x]">u</u>[/quote]'),
			'render, closer'            => array('render', '[b]x[/b <u title="x]">u</u>', '[b]x[/b <u title="x]">u</u>'),
			'render, comment'           => array('render', '[b <!-- ] -->x[/b]', '[b <!-- ] -->x[/b]'),
			'render, quote in a comment' => array('render', '[quote <!-- ]<u>x</u>[/quote] -->', '[quote <!-- ]<u>x</u>[/quote] -->'),
			'render, comment ended by >' => array('render', '[b <!--> <u title="-->]">x</u>[/b]', '[b <!--> <u title="-->]">x</u>[/b]'),
			'render, comment ended by ->' => array('render', '[b <!---> <u title="-->]">x</u>[/b]', '[b <!---> <u title="-->]">x</u>[/b]'),
			'render, comment ended by --!>' => array('render', '[b <!-- a --!> <u title="-->]">x</u>[/b]', '[b <!-- a --!> <u title="-->]">x</u>[/b]'),
			'render, iframe text'       => array('render', '[quote <iframe><!--</iframe><u title="-->]">u</u>[/quote]', '[quote <iframe><!--</iframe><u title="-->]">u</u>[/quote]'),
			'render, iframe start tag'  => array('render', '[b <iframe>]x</iframe>[/b]', '[b <iframe>]x</iframe>[/b]'),
			'render, noscript end tag'  => array('render', '[b </noscript>]x[/b]', '[b </noscript>]x[/b]'),
			'render, unclosed tag'      => array('render', '[quote <u title="x]q[/quote]', '[quote <u title="x]q[/quote]'),
			'before saving'             => array('save', '[code <u title="x]">u</u>[/code]', '[code <u title="x]">u</u>[/code]'),
			'stripping all'             => array('strip', '[quote <u title="x]">u</u>', '[quote <u title="x]">u</u>'),
			'hide blocks'               => array('hide', '[hide <u title="x]">s</u>[/hide]', '[]'),
			'image contents'            => array('img', '[img <u title="x]">a.png[/img]', '[]'),
		);
	}

	/**
	 * @dataProvider walkerReadings
	 * @param string $reading
	 * @param string $text
	 * @param string $expected
	 */
	public function testTheBbcodeParserReadsABbcodeTagEndingInsideATagAsText($reading, $text, $expected)
	{
		$bb = e107::getBB();

		switch ($reading)
		{
			case 'render':
				$result = $bb->parseBBCodes($text, '');
				break;
			case 'save':
				$result = $bb->parseBBCodes($text, '', 'default', 'PRE');
				break;
			case 'strip':
				$result = $bb->parseBBCodes($text, '', 'default', true);
				break;
			case 'hide':
				$result = json_encode($bb->hideBlocks($text));
				break;
			default:
				$result = json_encode($bb->getContent('img', $text));
		}

		self::assertSame($expected, $result);
	}

	/**
	 * @return array
	 */
	public function bbcodeTagsHoldingNoOpenTag()
	{
		return array(
			'a < stored as an entity' => array('[quote=a &lt;u title=&quot;x]q[/quote]', '//blockquote'),
			'a whole tag in the parameter' => array('[url=<b>x</b>]y[/url]', '//a[@href]'),
			'a whole comment in the parameter' => array('[quote=a <!-- c --> b]q[/quote]', '//blockquote'),
			'a ] in a tag after the bbcode tag' => array('[b]x[/b] <u title="x]">u</u>', '//strong'),
			'a ] in a tag inside a code block' => array('[code]<u title="x]">[/code]', '//pre'),
			'a tag inside an html block' => array('[html]<p>[b]x[/b] <u title=&quot;x]&quot;>u</u></p>[/html]', '//strong'),
		);
	}

	/**
	 * @dataProvider bbcodeTagsHoldingNoOpenTag
	 * @param string $stored
	 * @param string $rendered an XPath query for the element the bbcode renders
	 */
	public function testABbcodeTagHoldingNoOpenTagStillRenders($stored, $rendered)
	{
		$html = e107::getParser()->toHTML($stored, true, 'BODY');

		self::assertNotEmpty($this->attributesOf($html, $rendered), $html);
	}

	/**
	 * @return array
	 */
	public function openersBeforeOneTag()
	{
		return array(
			'code openers, a ] in a value'  => array(str_repeat('[code', 8000) . '<b title="]">[/code]', str_repeat('[code', 8000) . '<b title="x">[/code]'),
			'hide openers, a ] in a value'  => array(str_repeat('[hide', 8000) . '<b title="]">[/hide]', str_repeat('[hide', 8000) . '<b title="x">[/hide]'),
			'code openers, each a comment'  => array(str_repeat('[code<!--', 6000) . '][/code]', str_repeat('[code<!---->', 6000) . '][/code]'),
		);
	}

	/**
	 * Many bbcode openers before one ] that leaves a tag or comment open render about as fast as before one that does not: no [ is read up to that ] again.
	 *
	 * @dataProvider openersBeforeOneTag
	 * @param string $open the openers before a ] that leaves a tag or comment open
	 * @param string $whole the same openers before a ] that leaves none open
	 */
	public function testOpenersBeforeAnOpenTagRenderAboutAsFastAsBeforeAWholeOne($open, $whole)
	{
		$took = array('open' => $this->fastestRender($open), 'whole' => $this->fastestRender($whole));

		self::assertLessThan(4, $took['open'] / $took['whole'], json_encode($took));
	}

	/**
	 * @return array
	 */
	public function openersBeforeAnOpenTagWithNoCloser()
	{
		return array(
			'code openers'                  => array(str_repeat('[code', 8000) . '<b title="]">', str_repeat('[code', 8000) . '<b title="]">[/code]'),
			'hide openers'                  => array(str_repeat('[hide', 8000) . '<b title="]">', str_repeat('[hide', 8000) . '<b title="]">[/hide]'),
			'code openers in an html block' => array('[html]' . str_repeat('[code', 8000) . '<b title="]">[/html]', '[html]' . str_repeat('[code', 8000) . '<b title="]">[/code][/html]'),
		);
	}

	/**
	 * Many bbcode openers before a ] that leaves a tag open render about as fast with no closing tag after them as with one: the text is not searched from each of them to its end.
	 *
	 * @dataProvider openersBeforeAnOpenTagWithNoCloser
	 * @param string $unclosed the openers and the tag, with no closing tag
	 * @param string $closed the same, followed by a closing tag
	 */
	public function testOpenersWithNoCloserRenderAboutAsFastAsWithOne($unclosed, $closed)
	{
		$took = array('unclosed' => $this->fastestRender($unclosed), 'closed' => $this->fastestRender($closed));

		self::assertLessThan(4, $took['unclosed'] / $took['closed'], json_encode($took));
	}

	/**
	 * @param string $text
	 * @return float the fastest of three toHTML() renders of $text, in seconds
	 */
	private function fastestRender($text)
	{
		$took = INF;

		for ($i = 0; $i < 3; $i++)
		{
			$start = microtime(true);
			e107::getParser()->toHTML($text, true, 'BODY');
			$took = min($took, microtime(true) - $start);
		}

		return $took;
	}

	/**
	 * @param string $stored
	 * @return string $stored as toHTML() renders it, hidden content shown
	 */
	private function rendered($stored)
	{
		$this->holdHideGlobals();

		try
		{
			$this->allowHiddenContent(true);

			return e107::getParser()->toHTML($stored, true, 'BODY');
		}
		finally
		{
			$this->restoreHideGlobals();
		}
	}
}
