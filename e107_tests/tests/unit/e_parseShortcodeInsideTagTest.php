<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Shortcodes written inside a tag's attribute value, by an author or by a bbcode, in text toHTML() parses.
 */
class e_parseShortcodeInsideTagTest extends \Test\Unit
{
	use \Test\HtmlAttributes;

	protected function _before()
	{
		e107::getScParser()->registerShortcode(new e_parseShortcodeInsideTagShortcodes(), true, '', true);
		e_parseShortcodeInsideTagShortcodes::$handed = array();
	}

	/**
	 * @return array
	 */
	public function shortcodesInsideAttributes()
	{
		return array(
			'author title, equals form'   => array('<p><b title="{PRINT_ITEM=a tabindex=0 autofocus onfocus=alert(1) b^news.1}">P</b></p>'),
			'author title, colon form'    => array('<p><b title="{SOCIALSHARE: class=a tabindex=0 autofocus onfocus=alert(1) b}">S</b></p>'),
			'image bbcode, colon form'    => array('[img]http://x.example/a.png?{GLYPH: type=fa-star&class=a tabindex=1 autofocus onfocus=alert(1) b}[/img]'),
			'image bbcode, equals form'   => array('[img]http://x.example/a.png?{PROFILE=x tabindex=1 autofocus onfocus=alert(1) y}[/img]'),
			'closed after the tag'        => array('<p><b title="{SOCIALSHARE: class=a tabindex=0 autofocus onfocus=alert(1) b&x=">S</b>}</p>'),
			'closed after the bbcode'     => array('[img]http://x.example/a.png?{PROFILE=x tabindex=1 autofocus onfocus=alert(1) y[/img]}'),
			'beside a lone quote'         => array('<p><b title="x &quot; y=&quot;{PRINT_ITEM=a tabindex=0 autofocus onfocus=alert(1) b^news.1}&quot; z \'">P</b></p>'),
		);
	}

	/**
	 * @dataProvider shortcodesInsideAttributes
	 * @param string $stored
	 */
	public function testAShortcodeInsideAnAttributeCannotAddAttributes($stored)
	{
		$html = e107::getParser()->toHTML($stored, true, 'BODY');

		self::assertNotSame('', trim($html));
		self::assertSame(array(), $this->handlerAttributes($html), 'A shortcode inside an attribute added a handler: '.$html);
	}

	public function testAShortcodeAfterALongCommentStillExpands()
	{
		$html = e107::getParser()->toHTML('<!-- '.str_repeat('x', 20000).' --><p>Welcome to {SITENAME}.</p>', true, 'BODY');

		self::assertStringContainsString('Welcome to '.SITENAME.'.', $html);
	}

	public function testAShortcodeBesideACodeBlockNoBbcodePassRendersCannotAddAttributes()
	{
		$html = e107::getParser()->toHTML("<p>[code]<b title='x[/code] {GLYPH: type=fa-star&class=a tabindex=1 autofocus onfocus=alert(1) b} y</p>", false, 'BODY');

		self::assertSame(array(), $this->handlerAttributes($html), 'A shortcode beside a [code] block left unrendered added a handler: '.$html);
	}

	public function testAHiddenBlockBesideAShortcodeLeavesNoMarker()
	{
		$html = e107::getParser()->toHTML('<p><b title="{SITENAME} [hide]secret[/hide]">B</b></p>', true, 'BODY');

		self::assertStringNotContainsString('e_hide_', $html);
	}

	public function testAShortcodeStillFillsALinkAddress()
	{
		self::assertSame(array(SITEURL.'page.php'), $this->renderedAttributes('<a href="{SITEURL}page.php">Pages</a>', 'BODY', '//a/@href'));
	}

	public function testAShortcodeStillFillsAnImageBbcodeAddress()
	{
		self::assertSame(array(SITEURL.'e107_images/button.png'), $this->renderedAttributes('[img]{SITEURL}e107_images/button.png[/img]', 'BODY', '//img/@src'));
	}

	public function testAConstantStillFillsAnImageAddress()
	{
		self::assertSame(array(SITEURLBASE.e_IMAGE_ABS.'button.png'), $this->renderedAttributes('<img src="{e_IMAGE}button.png" alt="B" />', 'BODY', '//img/@src'));
	}

	public function testAConstantInATitleImageAddressIsLeftAsWritten()
	{
		self::assertSame(array('{e_IMAGE}button.png'), $this->renderedAttributes('<img src="{e_IMAGE}button.png" alt="B" />', 'TITLE', '//img/@src'));
	}

	/**
	 * @return array
	 */
	public function shortcodeParameterQuotes()
	{
		return array(
			'apostrophe in a double-quoted value' => array('<p><b title="{SHORTCODE_INSIDE_TAG_ECHO=x\'y}">P</b></p>', "'", "x'y"),
			'quote in a single-quoted value'      => array('<p><b title=\'{SHORTCODE_INSIDE_TAG_ECHO=x"y}\'>P</b></p>', '"', 'x"y'),
		);
	}

	/**
	 * @dataProvider shortcodeParameterQuotes
	 * @param string $stored
	 * @param string $quote
	 * @param string $title
	 */
	public function testAShortcodeInsideAnAttributeIsHandedNoQuote($stored, $quote, $title)
	{
		$titles = $this->renderedAttributes($stored, 'BODY', '//b/@title');

		self::assertNotEmpty(e_parseShortcodeInsideTagShortcodes::$handed, 'The shortcode inside the attribute did not render.');
		self::assertStringNotContainsString($quote, implode(' ', e_parseShortcodeInsideTagShortcodes::$handed));
		self::assertSame(array($title), $titles);
	}

	public function testAShortcodeInsideAnAttributeIsHandedAPlainParameterAsWritten()
	{
		$titles = $this->renderedAttributes('<p><b title="{SHORTCODE_INSIDE_TAG_ECHO=plain words}">P</b></p>', 'BODY', '//b/@title');

		self::assertSame(array('plain words'), e_parseShortcodeInsideTagShortcodes::$handed);
		self::assertSame(array('plain words'), $titles);
	}

	/**
	 * @param string $stored
	 * @param string $context
	 * @param string $query
	 * @return string[]
	 */
	private function renderedAttributes($stored, $context, $query)
	{
		return $this->attributesOf(e107::getParser()->toHTML($stored, true, $context), $query);
	}
}

/**
 * Prints its parameter, as shortcodes that print stored or remote data would, and keeps what it was handed.
 */
class e_parseShortcodeInsideTagShortcodes
{
	/** @var string[] */
	public static $handed = array();

	/**
	 * @param string $parm
	 * @return string
	 */
	public function sc_shortcode_inside_tag_echo($parm = '')
	{
		self::$handed[] = (string) $parm;

		return (string) $parm;
	}
}
