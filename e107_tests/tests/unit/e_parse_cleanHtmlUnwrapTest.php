<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class e_parse_cleanHtmlUnwrapTest extends \Test\Unit
{
	/**
	 * @return e_parse a parser cleaning as it does for an author without script access
	 */
	private function parserWithoutScriptAccess()
	{
		$tp = new e_parse();
		$tp->setScriptAccess(e_UC_NOBODY);

		return $tp;
	}

	public static function inlineTagsOffTheAllowList()
	{
		$tags = array('font', 'strike', 's', 'del', 'kbd', 'q', 'tt', 'big', 'var', 'dfn', 'samp', 'acronym', 'mark', 'time', 'bdi', 'center');
		$cases = array();

		foreach($tags as $tag)
		{
			$cases[$tag] = array('<div>Hello <' . $tag . '>kind</' . $tag . '> world</div>', '<div>Hello kind world</div>');
		}

		$cases['nested'] = array('<div><font><font face="Arial">deep <b>bold</b></font> text</font></div>', '<div>deep <b>bold</b> text</div>');
		$cases['word smart tag'] = array('<p>In <st1:place>Bratislava</st1:place> today</p>', '<p>In Bratislava today</p>');
		$cases['around paragraphs'] = array('<font face="Arial"><p>Whole paragraph inside a font tag.</p><p>Second.</p></font>', '<p>Whole paragraph inside a font tag.</p><p>Second.</p>');

		return $cases;
	}

	/**
	 * @dataProvider inlineTagsOffTheAllowList
	 */
	public function testATagOffTheAllowListIsDroppedAndItsContentKept($posted, $stored)
	{
		$this->assertSame($stored, $this->parserWithoutScriptAccess()->cleanHtml($posted));
	}

	public static function structuralTagsOffTheAllowList()
	{
		$cases = array();

		foreach(array('address', 'nav', 'header', 'footer', 'main', 'details', 'summary') as $tag)
		{
			$cases[$tag] = array($tag);
		}

		return $cases;
	}

	/**
	 * @dataProvider structuralTagsOffTheAllowList
	 */
	public function testAStructuralTagOffTheAllowListKeepsItsParagraphs($tag)
	{
		$this->assertSame('<p>One</p><p>Two</p>', $this->parserWithoutScriptAccess()->cleanHtml('<' . $tag . '><p>One</p><p>Two</p></' . $tag . '>'));
	}

	public function testATableFooterKeepsItsRow()
	{
		$stored = $this->parserWithoutScriptAccess()->cleanHtml('<table><tbody><tr><td>Item</td></tr></tbody><tfoot><tr><td>TOTAL</td></tr></tfoot></table>');

		$this->assertStringContainsString('<td>TOTAL</td>', $stored);
		$this->assertStringNotContainsString('tfoot', $stored);
	}

	public function testABlockTagInsideAParagraphKeepsItsText()
	{
		$stored = $this->parserWithoutScriptAccess()->cleanHtml('<p>Hello <font color="red">world</font>, this is <strike>old</strike> text in <center>the middle</center>.</p>');

		$this->assertStringContainsString('Hello world, this is old text in ', $stored);
		$this->assertStringContainsString('the middle', $stored);
		$this->assertStringNotContainsString('<font', $stored);
		$this->assertStringNotContainsString('<center', $stored);
	}

	public function testTheKeptContentIsCleanedAsAnyOther()
	{
		$tp = $this->parserWithoutScriptAccess();
		$stored = $tp->cleanHtml('<font color="red"><a href="javascript:alert(1)" onclick="alert(2)">link</a><blink>more</blink></font>');

		$this->assertSame('<a href="#---sanitized---#">link</a>more', $stored);

		$removed = $tp->getRemoved();
		$this->assertSame(array('font', 'blink'), $removed['tags']);
	}

	public function testAPreInsideATagOffTheAllowListIsStillTreatedAsPreformatted()
	{
		$this->assertSame('<pre>&#123;SITENAME&#125;</pre>', $this->parserWithoutScriptAccess()->cleanHtml('<center><pre>{SITENAME}</pre></center>'));
	}

	public static function elementsRemovedWhole()
	{
		$tags = array('script', 'style', 'svg', 'math', 'xmp', 'noembed', 'noframes', 'plaintext', 'iframe', 'noscript', 'title', 'textarea',
			'applet', 'form', 'button', 'object', 'ins', 'select');
		$cases = array();

		foreach($tags as $tag)
		{
			$cases[$tag] = array($tag);
		}

		return $cases;
	}

	/**
	 * @dataProvider elementsRemovedWhole
	 */
	public function testAScriptTagOrAnElementABrowserDoesNotReadAsHtmlIsRemovedWithItsContent($tag)
	{
		$tp = $this->parserWithoutScriptAccess();
		$tp->setAllowedTags(array('html', 'body', 'p', 'b'));

		$this->assertSame('<p>kept</p>', $tp->cleanHtml('<p>kept</p><' . $tag . '><b>gone</b> gone</' . $tag . '>'));
	}

	public function testAVoidScriptTagGoesAndTheTextAfterItStaysOnEveryLibxml()
	{
		$this->assertSame('<p>Watch  then read this.</p><p>Next</p>', $this->parserWithoutScriptAccess()->cleanHtml('<p>Watch <embed src="v.swf"> then read this.</p><p>Next</p>'));
	}

	public function testAnInsertionIsLostAndADeletionKeptForAnAuthorWithoutScriptAccess()
	{
		$this->assertSame('<p>old</p>', $this->parserWithoutScriptAccess()->cleanHtml('<p><del>old</del><ins>new</ins></p>'));
	}

	public function testContentStoredWithoutScriptKeepsTheTextOfATagOffTheAllowList()
	{
		$tp = new e_parse();
		$stored = $this->withPostClasses(e_UC_PUBLIC, e_UC_PUBLIC, function () use ($tp)
		{
			return $tp->toDBWithoutScript('<p>Hello <font color="red">world</font></p><form><b>gone</b></form><script>alert(1)</script>');
		}, e_UC_NOBODY);

		$this->assertSame('<p>Hello world</p>', $stored);
	}

	public function testToDbKeepsTheTextOfATagOffTheAllowListForAnAuthorWithoutScriptAccess()
	{
		$tp = new e_parse();
		$stored = $this->withPostClasses(e_UC_PUBLIC, e_UC_NOBODY, function () use ($tp)
		{
			return $tp->toDB('[html]<p>Hello <font color="red">world</font></p><script>alert(1)</script>[/html]');
		}, e_UC_NOBODY);

		$this->assertStringContainsString('<p>Hello world</p>', $stored);
		$this->assertStringNotContainsString('alert', $stored);
	}

	public function testHtmlToBbcodeKeepsTheTextOfATagOffItsOwnList()
	{
		$this->assertStringContainsString('Hello world', e107::getBB()->htmltoBBcode('<p>Hello <span style="color:red">world</span></p>'));
	}
}
