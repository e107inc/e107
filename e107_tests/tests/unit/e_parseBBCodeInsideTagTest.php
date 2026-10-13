<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Bbcodes written inside a tag's attribute value in text toHTML() parses.
 */
class e_parseBBCodeInsideTagTest extends \Test\Unit
{
	use \Test\HtmlAttributes;
	use \Test\HideGlobals;

	/**
	 * @return array
	 */
	public function bbcodesInsideAttributes()
	{
		return array(
			'link in a double-quoted title'  => array('<p><b title="[url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]">B</b></p>'),
			'image in a single-quoted title' => array("<p><b title='[img]http://e107.org/x.png tabindex=0 autofocus onfocus=alert(1) x[/img]'>B</b></p>"),
			'html block, as stored'          => array('[html]<p><b title=&quot;[url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>[/html]'),
			'html block, quotes typed'       => array('[html]<p><b title="[url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]">B</b></p>[/html]'),
			'table block'                    => array('[table][tr][td]<b title=&quot;[url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/td][/tr][/table]'),
			'hide block across lines'        => array("[hide=a\nb]<b title=\"[url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]\">B</b>[/hide]"),
			'html block, beside a hidden block'                 => array('[html]<p><b title=&quot;x [hide]s[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>[/html]'),
			'table block, beside a hidden block'                => array('[table][tr][td]<b title=&quot;x [hide]s[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/td][/tr][/table]'),
			'hide block across lines, beside a hidden block'    => array("[hide=a\nb]<b title=&quot;x [hide]s[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/hide]"),
			'html block, single-quoted, beside a hidden block'  => array('[html]<p><b title=&#039;x [hide]s[/hide] [img]http://e107.org/x.png tabindex=0 autofocus onfocus=alert(1) x[/img]&#039;>B</b></p>[/html]'),
			'table block, single-quoted, beside a hidden block' => array('[table][tr][td]<b title=&#39;x [hide]s[/hide] [img]http://e107.org/x.png tabindex=0 autofocus onfocus=alert(1) x[/img]&#39;>B</b>[/td][/tr][/table]'),
			'code block in a title'                             => array('<p><b title=&quot;x [code]a[/code] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>'),
			'code block in a title, quotes typed'               => array('<p><b title="x [code]a[/code] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]">B</b></p>'),
			'scode block in a title'                            => array('<p><b title=&quot;x [scode]a[/scode] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>'),
			'scode opened in a title, closed after the tag'     => array('<p><b title=&quot;x [scode]a [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/scode]</p>'),
			'table block in a title in a table block'           => array('[table][tr][td]<b title=&quot;x [table]a[/table] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/td][/tr][/table]'),
			'html block, scode opened in a title beside a hidden block'  => array('[html]<p><b title=&quot;x [scode][hide]s[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/scode]</p>[/html]'),
			'table block, scode opened in a title beside a hidden block' => array('[table][tr][td]<b title=&quot;x [scode][hide]s[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/scode][/td][/tr][/table]'),
			'hide block across lines, scode opened in a title beside a hidden block' => array("[hide=a\nb]<b title=&quot;x [scode][hide]s[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b>[/scode][/hide]"),
			'hide opener left unpaired, closed in a title'      => array("<p>[hide=a\nb]<b title=&quot;x [/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>"),
			'a hide word, closed in a title'                    => array('<p>see [hide later <b title=&quot;x [/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>'),
			'code opener broken by a line, around an open quote' => array("<p>[code=\n]<b x=\" [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]>B[/code]</p>"),
		);
	}

	/**
	 * @return array
	 */
	public function hiddenBlocksAcrossATag()
	{
		return array(
			'opened in a title, a bbcode after the tag' => array(false, '<p><b title=&quot;x [hide]y&quot;>B</b> [/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]</p>'),
			'opened in a title, a quote after the tag'  => array(false, '<p><b title=&quot;x [hide]y&quot;>B</b> [/hide] &quot; tabindex=0 autofocus onfocus=alert(1) x </p><p class=&quot;z&quot;>after</p>'),
			'opened before a tag, closed in its title'  => array(true, '<p>[hide]x <b title=&quot;y[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>'),
			'holding a tag left open, shown'            => array(true, '<p>[hide]<p>x <b title=&quot;y</p>[/hide] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]</p>'),
		);
	}

	/**
	 * @dataProvider hiddenBlocksAcrossATag
	 * @param bool   $allowed
	 * @param string $stored
	 */
	public function testAHiddenBlockAcrossATagCannotAddAttributes($allowed, $stored)
	{
		$this->holdHideGlobals();

		try
		{
			$this->allowHiddenContent($allowed);
			$html = e107::getParser()->toHTML($stored, true, 'BODY');
		}
		finally
		{
			$this->restoreHideGlobals();
		}

		self::assertSame(array(), $this->handlerAttributes($html), 'A hidden block across a tag added a handler: '.$html);
	}

	/**
	 * @return array
	 */
	public function hiddenBlocksBesideTextTheParserCannotRead()
	{
		return array(
			'inside a block refused for opening in a title' => array('<p><b title=&quot;x [hide]&quot;>B</b> [hide]secret[/hide] [/hide]</p>'),
			'holding a comment with a stray bracket'        => array('<p>[hide]<!-- a<b <i --> secret[/hide]</p>'),
			'holding a less than sign, as stored long ago'  => array('[hide]if (a<b) then: http://example.com/secret[/hide]'),
		);
	}

	/**
	 * @dataProvider hiddenBlocksBesideTextTheParserCannotRead
	 * @param string $stored
	 */
	public function testAHiddenBlockStaysHiddenBesideTextTheParserCannotRead($stored)
	{
		$this->holdHideGlobals();

		try
		{
			$this->allowHiddenContent(false);
			$html = e107::getParser()->toHTML($stored, true, 'USER_BODY');
		}
		finally
		{
			$this->restoreHideGlobals();
		}

		self::assertStringNotContainsString('secret', $html);
	}

	/**
	 * @return array
	 */
	public function shownHiddenBlocksHoldingALessThanSign()
	{
		return array(
			'written with bbcodes' => array("[hide]if a<b then\n[url=http://e107.org/]read this[/url][/hide]"),
			'written in HTML'      => array('<p>Download:</p>[hide]<p>if a<b use<br /><a href="http://e107.org/">this</a></p>[/hide]'),
		);
	}

	/**
	 * @dataProvider shownHiddenBlocksHoldingALessThanSign
	 * @param string $stored
	 */
	public function testAShownHiddenBlockHoldingALessThanSignKeepsItsLinkAndLineBreak($stored)
	{
		$this->holdHideGlobals();

		try
		{
			$html = e107::getParser()->toHTML($stored, true, 'BODY');
		}
		finally
		{
			$this->restoreHideGlobals();
		}

		self::assertSame(array('http://e107.org/'), $this->attributesOf($html, '//a/@href'), $html);
		self::assertStringContainsString('<br />', $html);
		self::assertStringContainsString('if a&lt;b', $html);
	}

	public function testACodeBlockHoldingALessThanSignStaysACodeBlock()
	{
		$html = e107::getParser()->toHTML('[code]if (a<b) return;[/code]', true, 'BODY');

		self::assertStringContainsString('a&lt;b', $html);
		self::assertStringNotContainsString('code]', $html);
	}

	/**
	 * @return array
	 */
	public function bbcodeLists()
	{
		return array(
			'b'     => array('b'),
			'b,img' => array('b,img'),
			'hide'  => array('hide'),
			'table' => array('table'),
		);
	}

	/**
	 * @dataProvider bbcodeLists
	 * @param string $list
	 */
	public function testABlockInsideAnAttributeCannotAddAttributesWhicheverBbcodesAreStripped($list)
	{
		$html = e107::getParser()->toHTML('<p><b title=&quot;x [code]a[/code] [url]http://e107.org/ tabindex=0 autofocus onfocus=alert(1) x[/url]&quot;>B</b></p>', $list, 'BODY');

		self::assertSame(array(), $this->handlerAttributes($html), 'A block inside an attribute let a bbcode add a handler: '.$html);
	}

	public function testABlockInsideAnAttributeIsKeptAsTypedText()
	{
		$html = e107::getParser()->toHTML('<p><b title=&quot;x [code]a[/code] [scode]s[/scode] [table]t[/table]&quot;>B</b></p>', true, 'BODY');

		self::assertSame(array('x [code]a[/code] [scode]s[/scode] [table]t[/table]'), $this->attributesOf($html, '//b/@title'));
	}

	/**
	 * @dataProvider bbcodesInsideAttributes
	 * @param string $stored
	 */
	public function testABbcodeInsideAnAttributeCannotAddAttributes($stored)
	{
		$html = e107::getParser()->toHTML($stored, true, 'BODY');

		self::assertNotSame('', trim($html));
		self::assertSame(array(), $this->handlerAttributes($html), 'A bbcode inside an attribute added a handler: '.$html);
	}

	public function testABbcodeInsideAnAttributeIsKeptThereAsText()
	{
		$html = e107::getParser()->toHTML('<p><b title="[url]http://e107.org/[/url]">t</b></p>', true, 'BODY');
		$title = $this->attributesOf($html, '//b/@title');

		self::assertCount(1, $title);
		self::assertStringContainsString('http://e107.org/</a>', $title[0]);
	}

	public function testABbcodeAfterALongCommentStillExpands()
	{
		$html = e107::getParser()->toHTML('<!-- '.str_repeat('x', 20000).' --><p>[b]bold[/b]</p>', true, 'BODY');

		self::assertStringContainsString('<strong', $html);
	}

	public function testAHiddenBlockBesideABbcodeLeavesNoMarker()
	{
		$html = e107::getParser()->toHTML('<p><b title="x [hide]secret[/hide] [b]y[/b]">B</b></p>', true, 'BODY');

		self::assertStringNotContainsString('e_hide_', $html);
	}

	public function testAShortcodeInABbcodeInsideAnAttributeRunsOnce()
	{
		$shortcodes = e107::getScParser();
		$list = new ReflectionProperty($shortcodes, 'scList');
		$list->setAccessible(true);
		$registered = $list->getValue($shortcodes);
		$list->setValue($shortcodes, array('BBCODE_INSIDE_TAG_FIRST' => 'return "{BBCODE_INSIDE_TAG_SECOND}";', 'BBCODE_INSIDE_TAG_SECOND' => 'return "printed by the second";') + $registered);

		try
		{
			$html = e107::getParser()->toHTML('<b title="[link=http://e107.org/{BBCODE_INSIDE_TAG_FIRST}]y[/link]">B</b>', true, 'BODY');
		}
		finally
		{
			$list->setValue($shortcodes, $registered);
		}

		self::assertStringContainsString('{BBCODE_INSIDE_TAG_SECOND}', implode('', $this->attributesOf($html, '//b/@title')), $html);
		self::assertStringNotContainsString('printed by the second', $html);
	}

	public function testAShortcodeInABbcodeInsideAnAttributeOfABlockRunsOnceInsideAShortcode()
	{
		$shortcodes = e107::getScParser();
		$list = new ReflectionProperty($shortcodes, 'scList');
		$list->setAccessible(true);
		$registered = $list->getValue($shortcodes);
		$list->setValue($shortcodes, array(
			'BBCODE_INSIDE_TAG_FIRST'  => 'return "{BBCODE_INSIDE_TAG_SECOND}";',
			'BBCODE_INSIDE_TAG_SECOND' => 'return "printed by the second";',
			'BBCODE_INSIDE_TAG_THIRD'  => 'return strpos(e107::getParser()->toHTML("[html]<b title=&quot;[link=http://e107.org/{BBCODE_INSIDE_TAG_FIRST}]y[/link]&quot;>B</b>[/html]", true, "BODY"), "printed by the second") === false ? "rendered-once" : "rendered-twice";',
		) + $registered);

		try
		{
			$html = e107::getParser()->toHTML('[link=http://e107.org/{BBCODE_INSIDE_TAG_THIRD}]z[/link]', true, 'BODY');
		}
		finally
		{
			$list->setValue($shortcodes, $registered);
		}

		self::assertStringContainsString('rendered-once', $html);
	}

	public function testABbcodeOutsideTagsStillExpands()
	{
		self::assertStringContainsString('<strong', e107::getParser()->toHTML('[b]bold[/b]', true, 'BODY'));
	}
}
