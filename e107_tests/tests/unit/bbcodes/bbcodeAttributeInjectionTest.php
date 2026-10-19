<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Stored payloads driven through toDB() and then toHTML(), the seam where five
 * bbcodes let a member's own quotes reach the page as markup.
 *
 * [img] is a bb_*.php class whose toDB() rewrites the parameter string, so its
 * cases are driven at the render, which is the boundary a row stored before
 * that class existed has to meet.
 */
class bbcodeAttributeInjectionTest extends \Test\Unit
{
	/** A JavaScript string literal in double quotes, as toJsString() writes one. */
	const STRING_LITERAL = '"(?:[^"\\\\]|\\\\.)*"';

	/** @var e_parse */
	private $tp;

	/** @var mixed */
	private $originalMakeClickable;

	/** @var array */
	private $originalHideGlobals;

	protected function _before()
	{
		global $e_hide_allowed, $e_hide_query, $sql;

		$this->tp = e107::getParser();
		e107::getScParser()->registerShortcode(new bbcodeAttributeInjectionShortcodes(), true, '', true);
		$this->originalMakeClickable = e107::getConfig()->get('make_clickable');

		$this->originalHideGlobals = array($e_hide_allowed, $e_hide_query, $sql);
		$e_hide_allowed = true;
		$e_hide_query = 'SELECT 1';
		$sql = e107::getDb();
	}

	protected function _after()
	{
		global $e_hide_allowed, $e_hide_query, $sql;

		e107::getConfig()->set('make_clickable', $this->originalMakeClickable);
		list($e_hide_allowed, $e_hide_query, $sql) = $this->originalHideGlobals;
	}

	/**
	 * The round trip a stored signature, forum post or comment actually makes.
	 *
	 * @param string $bbcode
	 * @param e_parse $parser
	 * @param string $modifiers
	 * @return string
	 */
	private function renderStored($bbcode, $parser = null, $modifiers = '')
	{
		$parser = $parser ? $parser : $this->tp;

		return $parser->toHTML($parser->toDB($bbcode), true, $modifiers);
	}

	/**
	 * @param string $html
	 * @return DOMElement[]
	 */
	private function elementsOf($html)
	{
		$document = new DOMDocument();

		$previous = libxml_use_internal_errors(true);
		$document->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>');
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		$elements = array();
		foreach($document->getElementsByTagName('*') as $element)
		{
			$elements[] = $element;
		}

		return $elements;
	}

	/**
	 * @param string $html
	 * @return string
	 */
	private function hrefOfFirstAnchor($html)
	{
		return $this->elementIn($html, 'a')->getAttribute('href');
	}

	/**
	 * @param string $html
	 * @return string the address the first anchor's javascript: href sends the window to
	 */
	private function addressTheScriptOpens($html)
	{
		preg_match_all('/'.self::STRING_LITERAL.'/', rawurldecode($this->hrefOfFirstAnchor($html)), $literals);

		return implode('', array_map('json_decode', $literals[0]));
	}

	/**
	 * @param string $html
	 * @param string $message
	 */
	private function assertNoEventHandler($html, $message)
	{
		$handlers = array();

		foreach($this->elementsOf($html) as $element)
		{
			foreach($element->attributes as $attribute)
			{
				if(strpos(strtolower($attribute->nodeName), 'on') === 0)
				{
					$handlers[] = $attribute->nodeName.'="'.$attribute->nodeValue.'"';
				}
			}
		}

		self::assertSame(array(), $handlers, $message.' Rendered: '.$html);
	}

	/**
	 * @param string $html
	 * @return DOMElement
	 */
	private function tableIn($html)
	{
		return $this->elementIn($html, 'table');
	}

	/**
	 * @param string $html
	 * @param string $tagName
	 * @return DOMElement
	 */
	private function elementIn($html, $tagName)
	{
		foreach($this->elementsOf($html) as $element)
		{
			if($element->tagName === $tagName) return $element;
		}

		self::fail('No '.$tagName.' was rendered from: '.$html);
	}

	/**
	 * @param string $url
	 * @param string $message
	 */
	private function assertUrlCannotExecute($url, $message)
	{
		self::assertSame(0, preg_match('#^javascript:#i', preg_replace('/[\x00-\x20\x7F]/', '', $url)), $message);
	}

	/**
	 * Both scripts must read back as nothing but well-formed literals and the + between
	 * them, the href after the extra percent-decode a javascript: URL gets.
	 *
	 * @param string $html
	 * @param string $message
	 */
	private function assertHandlerIsOnlyStringLiterals($html, $message)
	{
		$literals = '(?:'.self::STRING_LITERAL.'|\+)+';

		foreach($this->elementsOf($html) as $element)
		{
			if($element->tagName === 'a' && $element->hasAttribute('onmouseover'))
			{
				self::assertMatchesRegularExpression('#^window\.status='.$literals.'; return true;$#',
					$element->getAttribute('onmouseover'), $message.' Rendered: '.$html);

				self::assertMatchesRegularExpression('#^javascript:window\.location='.$literals.';self\.close\(\);$#',
					rawurldecode($element->getAttribute('href')), $message.' Rendered: '.$html);

				return;
			}
		}

		self::fail('No anchor carrying an onmouseover was rendered from: '.$html);
	}

	/**
	 * @return array
	 */
	public function mailtoPayloads()
	{
		return array(
			'link parameter'     => array('[link=mailto:"+(alert(1))+"@example.com]contact[/link]'),
			'link percent'       => array('[link=mailto:%22+alert(1)+%22@example.com]contact[/link]'),
			'email parameter'    => array('[email="+(alert(1))+"@example.com]contact[/email]'),
			'email percent'      => array('[email=%22+alert(1)+%22@example.com]contact[/email]'),
			'email body'         => array('[email]"+(alert(1))+"@example.com[/email]'),
			'email body percent' => array('[email]%22+alert(1)+%22@example.com[/email]'),
		);
	}

	/**
	 * @dataProvider mailtoPayloads
	 * @param string $bbcode
	 */
	public function testAMailtoBbcodeCannotEndAStringLiteral($bbcode)
	{
		e107::getConfig()->set('make_clickable', 1);

		$this->assertHandlerIsOnlyStringLiterals($this->renderStored($bbcode, new e_parse()),
			'A mailto bbcode escaped the JavaScript string literal it was placed in: '.$bbcode);
	}

	/**
	 * The address still has to survive the escaping, or the fix would have
	 * quietly broken every mailto link on every site.
	 */
	public function testAnOrdinaryMailtoLinkStillCarriesItsAddress()
	{
		$html = $this->renderStored('[link=mailto:john.doe@example.co.uk]contact[/link]');

		self::assertStringContainsString('"john.doe"', $html);
		self::assertStringContainsString('"example.co.uk"', $html);
	}

	/** The script a mailto link runs goes to the address typed, an ampersand in it included. */
	public function testAMailtoLinkGoesToTheAddressTyped()
	{
		self::assertSame('mailto:tom&jerry@example.com', $this->addressTheScriptOpens($this->renderStored('[link=mailto:tom&jerry@example.com]Mail[/link]')));
	}

	/**
	 * A member's & was stored as &amp;amp; from 2.2.0 to 2.3.12, and the post is shown as it was saved.
	 *
	 * @return array
	 */
	public function linksAMemberSavedBefore2313()
	{
		return array(
			'link'      => array('[link=https://e107.org/news.php?a=1&amp;amp;b=2]x[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'link body' => array('[link]https://e107.org/news.php?a=1&amp;amp;b=2[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'url'       => array('[url=https://e107.org/news.php?a=1&amp;amp;b=2]x[/url]', 'https://e107.org/news.php?a=1&b=2'),
			'url body'  => array('[url]https://e107.org/news.php?a=1&amp;amp;b=2[/url]', 'https://e107.org/news.php?a=1&b=2'),
			'file'      => array('[file=https://e107.org/request.php?a=1&amp;amp;b=2]x[/file]', 'https://e107.org/request.php?a=1&b=2'),
			'img thumbnail' => array('[img]https://example.com/pics/th_cat.png?a=1&amp;amp;b=2[/img]', 'https://example.com/pics/cat.png?a=1&b=2'),
		);
	}

	/**
	 * @dataProvider linksAMemberSavedBefore2313
	 * @param string $stored
	 * @param string $href
	 */
	public function testALinkAMemberSavedBefore2313GoesToTheAddressTyped($stored, $href)
	{
		self::assertSame($href, $this->hrefOfFirstAnchor($this->tp->toHTML($stored, true)));
	}

	public function testAMailtoLinkAMemberSavedBefore2313GoesToTheAddressTyped()
	{
		self::assertSame('mailto:tom&jerry@example.com', $this->addressTheScriptOpens($this->tp->toHTML('[link=mailto:tom&amp;amp;jerry@example.com]Mail[/link]', true)));
	}

	/**
	 * @param string $address
	 * @param string $stored
	 */
	private function assertEmailLinkGoesTo($address, $stored)
	{
		e107::getConfig()->set('make_clickable', 0);
		self::assertSame($address, $this->hrefOfFirstAnchor($this->tp->toHTML($stored, true)), 'The mailto: link went elsewhere.');

		e107::getConfig()->set('make_clickable', 1);
		self::assertSame($address, $this->addressTheScriptOpens($this->tp->toHTML($stored, true)), 'The script went elsewhere.');
	}

	/**
	 * @return array
	 */
	public function emailLinks()
	{
		return array(
			'body'             => array('[email]tom&jerry@example.com[/email]', 'mailto:tom&jerry@example.com'),
			'parameter'        => array('[email=tom&jerry@example.com]Mail[/email]', 'mailto:tom&jerry@example.com'),
			'body typed &amp;' => array('[email]tom&amp;jerry@example.com[/email]', 'mailto:tom&jerry@example.com'),
			'body %26'         => array('[email]tom%26jerry@example.com[/email]', 'mailto:tom%26jerry@example.com'),
			'body apostrophe'  => array("[email]o'brien@example.com[/email]", "mailto:o'brien@example.com"),
		);
	}

	/**
	 * An [email] link goes to the address typed, written as a mailto: link or, with make_clickable on, as a script.
	 *
	 * @dataProvider emailLinks
	 * @param string $bbcode
	 * @param string $address
	 */
	public function testAnEmailLinkGoesToTheAddressTyped($bbcode, $address)
	{
		$this->assertEmailLinkGoesTo($address, $this->tp->toDB($bbcode));
	}

	/**
	 * @return array
	 */
	public function emailLinksAMemberSavedBefore2313()
	{
		return array(
			'body'      => array('[email]tom&amp;amp;jerry@example.com[/email]'),
			'parameter' => array('[email=tom&amp;amp;jerry@example.com]Mail[/email]'),
		);
	}

	/**
	 * @dataProvider emailLinksAMemberSavedBefore2313
	 * @param string $stored
	 */
	public function testAnEmailLinkAMemberSavedBefore2313GoesToTheAddressTyped($stored)
	{
		$this->assertEmailLinkGoesTo('mailto:tom&jerry@example.com', $stored);
	}

	/**
	 * @return array
	 */
	public function linkAddressBreakouts()
	{
		return array(
			'apostrophe'              => array("[link=//x/'/autofocus/onfocus='alert(1)'/]y[/link]"),
			'body apostrophe'         => array("[link]//x/'/autofocus/onfocus='alert(1)'/[/link]"),
			'apostrophe entity'       => array('[link=//x/&#039;/autofocus/onfocus=&#039;alert(1)&#039;/]y[/link]'),
			'apostrophe stored twice' => array('[link=//x/&amp;#039;/autofocus/onfocus=&amp;#039;alert(1)&amp;#039;/]y[/link]'),
		);
	}

	/**
	 * An apostrophe in a [link] address stays inside its href.
	 *
	 * @dataProvider linkAddressBreakouts
	 * @param string $bbcode
	 */
	public function testALinkAddressCannotAddAnAttribute($bbcode)
	{
		foreach(array($this->renderStored($bbcode), $this->renderUnencoded($bbcode)) as $html)
		{
			self::assertSame("//x/'/autofocus/onfocus='alert(1)'/", $this->hrefOfFirstAnchor($html), 'A [link] address escaped its href: '.$html);
		}
	}

	/** A brace an upload or contact form wrote as a numeric reference stays written in a [link] address, so no shortcode runs from it. */
	public function testABraceWrittenAsAReferenceStaysWrittenInALinkAddress()
	{
		self::assertSame('&#123;SITEURL&#125;page.php', $this->hrefOfFirstAnchor($this->tp->toHTML('[link=&#123;SITEURL&#125;page.php]x[/link]', true, 'BODY')));
	}

	/**
	 * @return array
	 */
	public function scriptUrls()
	{
		return array(
			'url in a table' => array('[table][url=[nobr]]javascript:alert(1)//[/nobr]">x[/url][/table]'),
			'url in a hide'  => array('[hide][url=[nobr]]javascript:alert(1)//[/nobr]">x[/url][/hide]'),
			'url body'       => array("[url]java\tscript:alert(1)[/url]"),
			'url parameter'  => array("[url=java\tscript:alert(1)]x[/url]"),
			'link parameter' => array("[link=java\tscript:alert(1)]x[/link]"),
			'link entity'    => array('[link=java&#115;cript:alert(1)]x[/link]'),
			'link entity first' => array('[link=&#106;avascript:alert(1)]x[/link]'),
			'link entity stored twice' => array('[link=&amp;#106;avascript:alert(1)]x[/link]'),
			'file parameter' => array('[file=javascript:alert(1)]x[/file]'),
			'file tab'       => array("[file=java\tscript:alert(1)]x[/file]"),
			'file constant'  => array('[file={e_MENU}javascript:alert(1)]x[/file]'),
			'file nested'    => array('[file={e_{e_MENU}CORE}javascript:alert(1)]x[/file]'),
			'file shortcode' => array('[file={SETSTYLE}javascript:alert(1)]x[/file]'),
			'file printed by a shortcode' => array('[file={BBCODE_ATTRIBUTE_ECHO=javascript:alert(1)}]x[/file]'),
			'file completed by a shortcode' => array('[file=java{BBCODE_ATTRIBUTE_ECHO=script:alert(1)}]x[/file]'),
			'url constant'   => array('[url={e_MENU}javascript:alert(1)]x[/url]'),
			'url body constant' => array('[url]{e_MENU}javascript:alert(1)[/url]'),
			'link constant'  => array('[link={e_MENU}javascript:alert(1)]x[/link]'),
			'link body constant' => array('[link]{e_MENU}javascript:alert(1)[/link]'),
			'url nested'     => array('[url={e_{e_MENU}CORE}javascript:alert(1)]x[/url]'),
			'link nested'    => array('[link={e_{e_MENU}CORE}javascript:alert(1)]x[/link]'),
			'link shortcode' => array('[link={SETSTYLE}javascript:alert(1)]x[/link]'),
			'url printed by a shortcode' => array('[url={BBCODE_ATTRIBUTE_ECHO=javascript:alert(1)}]x[/url]'),
			'url body printed by a shortcode' => array('[url]{BBCODE_ATTRIBUTE_ECHO=javascript:alert(1)}[/url]'),
			'link printed by a shortcode' => array('[link={BBCODE_ATTRIBUTE_ECHO=javascript:alert(1)}]x[/link]'),
			'link completed by a shortcode' => array('[link=java{BBCODE_ATTRIBUTE_ECHO=script:alert(1)}]x[/link]'),
			'url shortcode'  => array('[url={SETSTYLE}javascript:alert(1)]x[/url]'),
			'url completed by a shortcode' => array('[url=java{BBCODE_ATTRIBUTE_ECHO=script:alert(1)}]x[/url]'),
			'img thumbnail'  => array('[img]javascript:alert(1)//th_x.png[/img]'),
			'img thumb'      => array('[img]javascript:alert(1)//thumb_x.png[/img]'),
			'img thumbnail constant' => array('[img]{e_MENU}javascript:alert(1)//th_x.png[/img]'),
		);
	}

	/**
	 * A browser skips control characters while it reads a scheme, so a guard that
	 * compares the first eleven characters never sees the scheme it is looking for.
	 *
	 * @dataProvider scriptUrls
	 * @param string $bbcode
	 */
	public function testAnAnchorNeverCarriesAUrlThatCanExecute($bbcode)
	{
		foreach(array('', 'BODY', 'USER_BODY') as $modifiers)
		{
			foreach($this->elementsOf($this->renderStored($bbcode, null, $modifiers)) as $element)
			{
				if($element->tagName !== 'a') continue;

				$this->assertUrlCannotExecute($element->getAttribute('href'), 'An anchor carried a URL that can execute: '.$bbcode.' '.$modifiers);
			}
		}
	}

	/**
	 * @return array
	 */
	public function refusedSchemes()
	{
		return array(
			'url tel'         => array('[url=tel:+15551234567]Call us[/url]', 'Call us'),
			'file script'     => array('[file=javascript:alert(1)]notes[/file]', 'notes'),
			'img thumbnail'   => array('[img]javascript:alert(1)//th_x.png[/img]', 'th_x.png'),
		);
	}

	/**
	 * A scheme a bbcode will not link costs the link, never the words the member wrote.
	 *
	 * @dataProvider refusedSchemes
	 * @param string $bbcode
	 * @param string $text
	 */
	public function testARefusedSchemeStillRendersItsText($bbcode, $text)
	{
		$html = $this->renderStored($bbcode);

		self::assertStringContainsString($text, $html, 'A refused scheme took the text with it.');
		self::assertStringNotContainsString('<a', $html, 'A refused scheme was linked anyway: '.$html);
	}

	/**
	 * A third value names the constant the expected href starts with.
	 *
	 * @return array
	 */
	public function ordinaryLinks()
	{
		return array(
			'url'                 => array('[url=http://example.com/a?b=1]x[/url]', 'http://example.com/a?b=1'),
			'file'                => array('[file=https://example.com/report.zip]x[/file]', 'https://example.com/report.zip'),
			'file under e_BASE'   => array('[file={e_BASE}report.zip]x[/file]', 'report.zip', 'SITEURL'),
			'file path constant'  => array('[file={e_MEDIA_FILE}report.zip]x[/file]', 'report.zip', 'e_MEDIA_FILE'),
			'url path constant'   => array('[url={e_PLUGIN}forum/forum.php]x[/url]', 'forum/forum.php', 'e_PLUGIN'),
			'link path constant'  => array('[link={e_PLUGIN}forum/forum.php]x[/link]', 'forum/forum.php', 'e_PLUGIN'),
			'link ampersand'      => array('[link=https://e107.org/news.php?a=1&b=2]x[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'link body ampersand' => array('[link]https://e107.org/news.php?a=1&b=2[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'link typed &amp;'    => array('[link=https://e107.org/news.php?a=1&amp;b=2]x[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'link body typed &amp;' => array('[link]https://e107.org/news.php?a=1&amp;b=2[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'link %26'            => array('[link=https://e107.org/search.php?q=Tom%26Jerry]x[/link]', 'https://e107.org/search.php?q=Tom%26Jerry'),
			'link body %26'       => array('[link]https://e107.org/search.php?q=Tom%26Jerry[/link]', 'https://e107.org/search.php?q=Tom%26Jerry'),
			'link marked external' => array('[link=https://e107.org/news.php?a=1&b=2 external]x[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'link external body'  => array('[link=external]https://e107.org/news.php?a=1&b=2[/link]', 'https://e107.org/news.php?a=1&b=2'),
			'link path query'     => array('[link={e_PLUGIN}forum/forum.php?f=1&id=2]x[/link]', 'forum/forum.php?f=1&id=2', 'e_PLUGIN'),
			'link apostrophe'     => array("[link=https://en.wikipedia.org/wiki/Ender's_Game]x[/link]", "https://en.wikipedia.org/wiki/Ender's_Game"),
			'link in html'        => array('[html]<p>[link=https://e107.org/news.php?a=1&amp;b=2]x[/link]</p>[/html]', 'https://e107.org/news.php?a=1&b=2'),
			'url ampersand'       => array('[url=https://e107.org/news.php?a=1&b=2]x[/url]', 'https://e107.org/news.php?a=1&b=2'),
			'url body ampersand'  => array('[url]https://e107.org/news.php?a=1&b=2[/url]', 'https://e107.org/news.php?a=1&b=2'),
			'url typed &amp;'     => array('[url=https://e107.org/news.php?a=1&amp;b=2]x[/url]', 'https://e107.org/news.php?a=1&b=2'),
			'url %26'             => array('[url=https://e107.org/search.php?q=Tom%26Jerry]x[/url]', 'https://e107.org/search.php?q=Tom%26Jerry'),
			'url apostrophe'      => array("[url=https://en.wikipedia.org/wiki/Ender's_Game]x[/url]", "https://en.wikipedia.org/wiki/Ender's_Game"),
			'url in a table'      => array('[table][tr][td][url=https://e107.org/news.php?a=1&b=2]x[/url][/td][/tr][/table]', 'https://e107.org/news.php?a=1&b=2'),
			'url in html'         => array('[html]<p>[url=https://e107.org/news.php?a=1&amp;b=2]x[/url]</p>[/html]', 'https://e107.org/news.php?a=1&b=2'),
			'file ampersand'      => array('[file=https://e107.org/request.php?a=1&b=2]x[/file]', 'https://e107.org/request.php?a=1&b=2'),
			'img thumbnail'       => array('[img]https://example.com/pics/th_cat.png[/img]', 'https://example.com/pics/cat.png'),
			'img thumbnail query' => array('[img]https://example.com/pics/th_cat.png?a=1&b=2[/img]', 'https://example.com/pics/cat.png?a=1&b=2'),
			'img thumbnail path'  => array('[img]{e_IMAGE}custom/th_cat.png[/img]', 'custom/cat.png', 'e_IMAGE_ABS'),
		);
	}

	/**
	 * Without this the payload rows above would pass on a bbcode that rendered nothing at all.
	 *
	 * @dataProvider ordinaryLinks
	 * @param string $bbcode
	 * @param string $href
	 * @param string $prefix
	 */
	public function testAnOrdinaryUrlIsStillLinked($bbcode, $href, $prefix = null)
	{
		self::assertSame(($prefix === null ? '' : constant($prefix)).$href, $this->hrefOfFirstAnchor($this->renderStored($bbcode)));
	}

	/**
	 * @return array
	 */
	public function thumbnailLinks()
	{
		return array(
			'query'      => array('[img]https://example.com/pics/th_cat.png?a=1&b=2[/img]', 'th_'),
			'apostrophe' => array("[img]https://example.com/it's/th_cat.png[/img]", 'th_'),
			'thumb'      => array('[img]https://example.com/pics/thumb_cat.png?a=1&b=2[/img]', 'thumb_'),
		);
	}

	/**
	 * A thumbnail links to the address its own image was given, less the prefix.
	 *
	 * @dataProvider thumbnailLinks
	 * @param string $bbcode
	 * @param string $prefix
	 */
	public function testAThumbnailLinksToItsOwnImageWithoutThePrefix($bbcode, $prefix)
	{
		$html = $this->renderStored($bbcode);

		self::assertSame(str_replace('/'.$prefix, '/', $this->elementIn($html, 'img')->getAttribute('src')), $this->hrefOfFirstAnchor($html));
	}

	/**
	 * Rows shaped as in {@see bbcodeAttributeInjectionTest::ordinaryLinks()}.
	 *
	 * @return array
	 */
	public function shortcodeLinks()
	{
		return array(
			'file site url'        => array('[file={SITEURL}report.zip]x[/file]', 'report.zip', 'SITEURL'),
			'file printed address' => array('[file={BBCODE_ATTRIBUTE_ECHO=https://example.com/}report.zip]x[/file]', 'https://example.com/report.zip'),
			'file in an html body' => array('[html]<p>[file={SITEURL}report.zip]x[/file]</p>[/html]', 'report.zip', 'SITEURL'),
			'file in a table'      => array('[table][tr][td][file={SITEURL}report.zip]x[/file][/td][/tr][/table]', 'report.zip', 'SITEURL'),
			'link site url'        => array('[link={SITEURL}page.php]x[/link]', 'page.php', 'SITEURL'),
			'url site url'         => array('[url={SITEURL}page.php]x[/url]', 'page.php', 'SITEURL'),
			'url body site url'    => array('[url]{SITEURL}page.php[/url]', 'page.php', 'SITEURL'),
			'link printed address' => array('[link={BBCODE_ATTRIBUTE_ECHO=https://example.com/}page.php]x[/link]', 'https://example.com/page.php'),
			'url printed address'  => array('[url={BBCODE_ATTRIBUTE_ECHO=https://example.com/}page.php]x[/url]', 'https://example.com/page.php'),
			'link in an html body' => array('[html]<p>[link={SITEURL}page.php]x[/link]</p>[/html]', 'page.php', 'SITEURL'),
			'link in a table'      => array('[table][tr][td][link={SITEURL}page.php]x[/link][/td][/tr][/table]', 'page.php', 'SITEURL'),
			'img thumbnail site url' => array('[img]{SITEURL}pics/th_cat.png[/img]', 'pics/cat.png', 'SITEURL'),
		);
	}

	/**
	 * @dataProvider shortcodeLinks
	 * @param string $bbcode
	 * @param string $href
	 * @param string $prefix
	 */
	public function testAShortcodeInABodyUrlStillRenders($bbcode, $href, $prefix = null)
	{
		self::assertSame(($prefix === null ? '' : constant($prefix)).$href, $this->hrefOfFirstAnchor($this->renderStored($bbcode, null, 'BODY')));
	}

	/**
	 * A member's post parses no shortcodes, so one in a bbcode's address prints as typed.
	 *
	 * @dataProvider shortcodeLinks
	 * @param string $bbcode
	 */
	public function testAShortcodeInAMemberUrlPrintsAsTyped($bbcode)
	{
		self::assertSame('{', substr($this->hrefOfFirstAnchor($this->renderStored($bbcode, null, 'USER_BODY')), 0, 1));
	}

	/**
	 * @return array
	 */
	public function shortcodeParameterQuotes()
	{
		return array(
			'file' => array('[file={BBCODE_ATTRIBUTE_ECHO=x"y}]x[/file]'),
			'url' => array('[url={BBCODE_ATTRIBUTE_ECHO=x"y}]x[/url]'),
			'url body' => array('[url]{BBCODE_ATTRIBUTE_ECHO=x"y}[/url]'),
			'link' => array('[link={BBCODE_ATTRIBUTE_ECHO=x"y}]x[/link]'),
			'link body' => array('[link]{BBCODE_ATTRIBUTE_ECHO=x"y}[/link]'),
		);
	}

	/**
	 * The address renders before the body's own shortcodes, so its shortcode is the first one handed a parameter.
	 *
	 * @dataProvider shortcodeParameterQuotes
	 * @param string $bbcode
	 */
	public function testAShortcodeInABodyUrlIsHandedNoQuote($bbcode)
	{
		bbcodeAttributeInjectionShortcodes::$handed = array();
		$this->renderStored($bbcode, null, 'BODY');

		self::assertNotEmpty(bbcodeAttributeInjectionShortcodes::$handed, 'No shortcode in the address rendered.');
		self::assertStringNotContainsString('"', bbcodeAttributeInjectionShortcodes::$handed[0]);
	}

	/**
	 * @return array
	 */
	public function bbcodeRenderingShortcodeLinks()
	{
		return array(
			'file' => array('[file={BBCODE_ATTRIBUTE_RENDERED=https://example.com/}]Visit[/file]'),
			'url'  => array('[url={BBCODE_ATTRIBUTE_RENDERED=https://example.com/}]Visit[/url]'),
			'link' => array('[link={BBCODE_ATTRIBUTE_RENDERED=https://example.com/}]Visit[/link]'),
		);
	}

	/**
	 * A shortcode that renders bbcode in a link's address leaves the link its own text.
	 *
	 * @dataProvider bbcodeRenderingShortcodeLinks
	 * @param string $bbcode
	 */
	public function testAShortcodeThatRendersBbcodeKeepsTheLinkText($bbcode)
	{
		$html = $this->renderStored($bbcode, null, 'BODY');

		self::assertSame('https://example.com/', $this->hrefOfFirstAnchor($html));
		self::assertSame('Visit', trim(strip_tags($html)), 'The link took the text of the bbcode its shortcode rendered: '.$html);
	}

	/**
	 * @dataProvider bbcodeRenderingShortcodeLinks
	 * @param string $bbcode
	 */
	public function testAShortcodeThatRendersBbcodeLeavesTheOuterGlobals($bbcode)
	{
		global $postID, $fromadmin;

		$this->tp->toHTML($this->tp->toDB($bbcode), true, 'BODY, fromadmin', 'outer post');

		self::assertSame('outer post', $postID);
		self::assertTrue($fromadmin);
	}

	/**
	 * A shortcode in a bbcode's address that stores text with a bbcode in it leaves the bbcodes after it rendering.
	 */
	public function testAShortcodeThatStoresBbcodeLeavesTheLaterBbcodesRendering()
	{
		$html = $this->renderStored('[url]{BBCODE_ATTRIBUTE_STORED=x[/b]1234}[/url] [b]after[/b]', null, 'BODY');

		self::assertStringNotContainsString('[b]after[/b]', $html, 'A bbcode after the address was printed as stored: '.$html);
	}

	public function testTheQuoteBbcodeCannotAddAnAttributeToItsCitation()
	{
		$html = $this->renderStored('[quote=" onmouseover="alert(1)]hello[/quote]');

		$this->assertNoEventHandler($html, 'A [quote] author name escaped the title attribute.');
	}

	/**
	 * @return array
	 */
	public function quotedNames()
	{
		return array(
			'ampersand'   => array('[quote=Tom & Jerry]hello[/quote]', 'Tom & Jerry'),
			'typed &amp;' => array('[quote=Tom &amp; Jerry]hello[/quote]', 'Tom & Jerry'),
			'apostrophe'  => array("[quote=Tom's cat]hello[/quote]", "Tom's cat"),
		);
	}

	/**
	 * @dataProvider quotedNames
	 * @param string $bbcode
	 * @param string $name
	 */
	public function testAQuoteCitesTheNameTyped($bbcode, $name)
	{
		self::assertSame($name, $this->elementIn($this->renderStored($bbcode), 'cite')->getAttribute('title'));
	}

	public function testAQuoteAMemberSavedBefore2313CitesTheNameTyped()
	{
		self::assertSame('Tom & Jerry', $this->elementIn($this->tp->toHTML('[quote=Tom &amp;amp; Jerry]hello[/quote]', true), 'cite')->getAttribute('title'));
	}

	/** A quote a bbcode handler encoded inside a [hide] block stays encoded for a reader the block is shown to. */
	public function testAHandlerQuoteInsideAHiddenBlockStaysEncoded()
	{
		$html = $this->renderStored("[hide][img]x.png' tabindex='0' autofocus onfocus='window.probe=1[/img][/hide]", null, 'USER_BODY');

		$this->elementIn($html, 'img');
		$this->assertNoEventHandler($html, 'A quote a handler encoded inside a [hide] block was decoded.');
	}

	/** The markup a link bbcode builds inside a [table] or [hide] block is never read as bbcode a second time. */
	public function testALinkInsideABlockGainsNoEventHandler()
	{
		foreach(array('[table]%s[/table]', '[hide]%s[/hide]') as $block)
		{
			$bbcode = sprintf($block, '[url=[nobr]]http://example.com/[/nobr]" autofocus onfocus="alert(1)" x="[/url]');

			foreach(array('', 'BODY', 'USER_BODY') as $modifiers)
			{
				$html = $this->renderStored($bbcode, null, $modifiers);

				$this->elementIn($html, 'a');
				$this->assertNoEventHandler($html, 'A link inside a block gained an event handler: '.$bbcode.' '.$modifiers);
			}
		}
	}

	/**
	 * @return array
	 */
	public function constantParameters()
	{
		return array(
			'quote' => array('[quote={e_THEME}]x[/quote]'),
			'flash' => array('[flash=1,1,a={e_THEME}]http://example.com/a.swf[/flash]'),
		);
	}

	/**
	 * e_parse::toAttribute() expands an e107 path constant unless it is told the
	 * value is a visitor's, which would hand every reader the server's paths.
	 *
	 * @dataProvider constantParameters
	 * @param string $bbcode
	 */
	public function testAParameterDoesNotExpandAnE107PathConstant($bbcode)
	{
		self::assertStringNotContainsString('e107_themes', $this->renderStored($bbcode),
			'A bbcode parameter expanded an e107 path constant: '.$bbcode);
	}

	/**
	 * @return array
	 */
	public function tableInjections()
	{
		return array(
			'plain'            => array('[table onmouseover=alert(1)]x[/table]'),
			'after an allowed' => array('[table style=x onmouseover=alert(1)]x[/table]'),
			'after a quote'    => array('[table class="a" onmouseover=alert(1)]x[/table]'),
			'no separator'     => array('[table class="a"onmouseover=alert(1)]x[/table]'),
			'numeric entity'   => array('[table class=&#34;a&#34;onmouseover=alert(1)]x[/table]'),
			'slash separated'  => array('[table style="x"/onmouseover="alert(1)"]x[/table]'),
			'tab separated'    => array("[table class=a\tonmouseover=alert(1)]x[/table]"),
			'spaced equals'    => array('[table onmouseover = alert(1)]x[/table]'),
			'uppercase'        => array('[table ONMOUSEOVER=alert(1)]x[/table]'),
		);
	}

	/**
	 * The assertion is the payload's plain absence, because DOMDocument does not
	 * reproduce every transition a browser makes back into attribute-name state.
	 *
	 * @dataProvider tableInjections
	 * @param string $bbcode
	 */
	public function testTheTableBbcodeDropsAnAttributeItDoesNotAllow($bbcode)
	{
		$html = $this->renderStored($bbcode);

		self::assertStringNotContainsString('alert(1)', $html,
			'A [table] parameter naming a disallowed attribute was emitted: '.$bbcode);
		$this->assertNoEventHandler($html, 'A [table] parameter emitted an event handler.');
	}

	/** An unquoted value ends at a >, however that > was spelled when it was stored. */
	public function testTheTableBbcodeDropsAParameterThatClosesTheTag()
	{
		foreach(array('[table style=x>foo]y[/table]', '[table style=x&gt;foo]y[/table]') as $bbcode)
		{
			self::assertStringNotContainsString('foo', $this->renderStored($bbcode),
				'A [table] parameter closed its own tag: '.$bbcode);
		}
	}

	/**
	 * A browser ends an unquoted value at whitespace or >, and keeps a / inside it, so
	 * the pair that follows one is part of the value rather than an attribute of its own.
	 */
	public function testAnUnquotedTableValueKeepsASlashRatherThanStartingAnAttribute()
	{
		$table = $this->tableIn($this->renderStored('[table style=x/onmouseover=alert(1)]y[/table]'));

		self::assertSame('x/onmouseover=alert(1)', $table->getAttribute('style'));
		self::assertFalse($table->hasAttribute('onmouseover'));
	}

	/**
	 * @return array
	 */
	public function tablePassthroughs()
	{
		return array(
			'generated css'  => array('[table style=border-collapse: collapse; width: 100%]x[/table]',
				array('style' => 'border-collapse: collapse; width: 100%')),
			'several'        => array('[table border=1 cellpadding=2 cellspacing=0]x[/table]',
				array('border' => '1', 'cellpadding' => '2', 'cellspacing' => '0')),
			'quoted'         => array('[table style="width: 50%" class="foo"]x[/table]',
				array('style' => 'width: 50%')),
			'entity encoded' => array('[table summary="a &gt; b" border=1]x[/table]',
				array('summary' => 'a > b', 'border' => '1')),
		);
	}

	/**
	 * The WYSIWYG converter writes [table style=<raw css>], unquoted and full of spaces.
	 *
	 * @dataProvider tablePassthroughs
	 * @param string $bbcode
	 * @param array $expected
	 */
	public function testTheTableBbcodeKeepsTheAttributesItAllows($bbcode, $expected)
	{
		$table = $this->tableIn($this->renderStored($bbcode));

		foreach($expected as $name => $value)
		{
			self::assertSame($value, $table->getAttribute($name),
				'A [table] parameter that only names allowed attributes was dropped: '.$bbcode);
		}
	}

	/** A parameter of its own is not a reason to drop the class this bbcode always emits. */
	public function testTheTableBbcodeAddsAParameterClassToItsOwn()
	{
		self::assertSame(e107::getBB()->getClass('table').' table',
			$this->tableIn($this->renderStored('[table class=table]x[/table]'))->getAttribute('class'),
			'A [table] class parameter did not join the class this bbcode emits.');
	}

	/**
	 * @return array
	 */
	public function tableQuoteEscapes()
	{
		return array(
			'double quoted' => array('[table title="a]" onmouseover=alert(1)>[/table]'),
			'single quoted' => array("[table title='a]' onmouseover=alert(1)>[/table]"),
		);
	}

	/**
	 * The bbcode ends at the first ], so the closing quote and everything after it
	 * arrive as the body, which this bbcode must not be able to end its tag with.
	 *
	 * @dataProvider tableQuoteEscapes
	 * @param string $bbcode
	 */
	public function testTheTableBbcodeCannotBeClosedByItsOwnBody($bbcode)
	{
		$this->assertNoEventHandler($this->renderStored($bbcode),
			'A [table] parameter with an unbalanced quote took an event handler from its body: '.$bbcode);
	}

	/** The entity pass rewrites onerror inside the rendered tag, which must stay cosmetic. */
	public function testTheTableBbcodeKeepsItsBodyWhenAValueIsRewritten()
	{
		self::assertSame('z', trim($this->tableIn($this->renderStored('[table title=xonerrory]z[/table]'))->textContent),
			'A [table] value containing onerror ended the tag early.');
	}

	/** An <object> takes its data from a member, so the scheme has to be one that cannot run. */
	public function testTheFlashBbcodeRefusesAUrlThatCanExecute()
	{
		$html = $this->renderStored('[flash=50,50]javascript:alert(1)[/flash]');

		self::assertStringNotContainsString('javascript:', $html,
			'A [flash] URL kept a scheme that can execute: '.$html);
	}

	/**
	 * The addresses a [flash] or [stream] player loads: the object's data, its movie or filename parameter, the embed's src.
	 *
	 * @param string $html
	 * @return string[]
	 */
	private function playerUrlsIn($html)
	{
		$urls = array();

		foreach($this->elementsOf($html) as $element)
		{
			if($element->tagName === 'object' && $element->hasAttribute('data'))
			{
				$urls[] = $element->getAttribute('data');
			}
			elseif($element->tagName === 'param' && in_array($element->getAttribute('name'), array('movie', 'filename'), true))
			{
				$urls[] = $element->getAttribute('value');
			}
			elseif($element->tagName === 'embed')
			{
				$urls[] = $element->getAttribute('src');
			}
		}

		return $urls;
	}

	/**
	 * @return array
	 */
	public function playerScriptUrls()
	{
		return array(
			'flash constant'                => array('[flash=50,50]{e_CORE}javascript:alert(1)[/flash]'),
			'flash shortcode'               => array('[flash=50,50]{SETSTYLE}javascript:alert(1)[/flash]'),
			'flash printed by a shortcode'  => array('[flash=50,50]{BBCODE_ATTRIBUTE_ECHO=javascript:alert(1)}[/flash]'),
			'stream'                        => array('[stream]javascript:alert(1)[/stream]'),
			'stream constant'               => array("[stream]{e_CORE}javascript:alert('1')[/stream]"),
			'stream printed by a shortcode' => array('[stream]{BBCODE_ATTRIBUTE_ECHO=javascript:alert(1)}[/stream]'),
		);
	}

	/**
	 * @dataProvider playerScriptUrls
	 * @param string $bbcode
	 */
	public function testAPlayerBbcodeChecksTheUrlItPrints($bbcode)
	{
		foreach(array('', 'BODY', 'USER_BODY') as $modifiers)
		{
			$urls = $this->playerUrlsIn($this->renderStored($bbcode, null, $modifiers));

			self::assertCount(2, $urls, 'The player did not render both of its addresses: '.$bbcode.' '.$modifiers);

			foreach($urls as $url)
			{
				$this->assertUrlCannotExecute($url, 'A player carried a URL that can execute: '.$bbcode.' '.$modifiers);
			}
		}
	}

	/**
	 * Rows shaped as in {@see bbcodeAttributeInjectionTest::ordinaryLinks()}.
	 *
	 * @return array
	 */
	public function playerUrls()
	{
		return array(
			'flash'                => array('[flash=60,40]http://example.com/a.swf[/flash]', 'http://example.com/a.swf'),
			'flash path constant'  => array('[flash=60,40]{e_MEDIA_FILE}a.swf[/flash]', 'a.swf', 'e_MEDIA_FILE'),
			'flash site url'       => array('[flash=60,40]{SITEURL}a.swf[/flash]', 'a.swf', 'SITEURL'),
			'stream'               => array('[stream width=60]http://example.com/a.wmv[/stream]', 'http://example.com/a.wmv'),
			'stream path constant' => array('[stream width=60]{e_MEDIA_FILE}a.wmv[/stream]', 'a.wmv', 'e_MEDIA_FILE'),
			'stream site url'      => array('[stream width=60]{SITEURL}a.wmv[/stream]', 'a.wmv', 'SITEURL'),
			'flash rendered by a shortcode'  => array('[flash=60,40]{BBCODE_ATTRIBUTE_RENDERED=https://example.com/}a.swf[/flash]', 'https://example.com/a.swf'),
			'stream rendered by a shortcode' => array('[stream width=60]{BBCODE_ATTRIBUTE_RENDERED=https://example.com/}a.wmv[/stream]', 'https://example.com/a.wmv'),
			'flash ampersand'      => array('[flash=60,40]http://example.com/a.swf?a=1&b=2[/flash]', 'http://example.com/a.swf?a=1&b=2'),
			'stream ampersand'     => array('[stream width=60]http://example.com/a.wmv?a=1&b=2[/stream]', 'http://example.com/a.wmv?a=1&b=2'),
		);
	}

	/**
	 * @dataProvider playerUrls
	 * @param string $bbcode
	 * @param string $url
	 * @param string $prefix
	 */
	public function testAPlayerBbcodeStillPlaysAnOrdinaryUrl($bbcode, $url, $prefix = null)
	{
		$html = $this->renderStored($bbcode, null, 'BODY');
		$expected = ($prefix === null ? '' : constant($prefix)).$url;

		self::assertSame(array($expected, $expected), $this->playerUrlsIn($html));
		self::assertSame('60', $this->elementIn($html, 'object')->getAttribute('width'));
	}

	/**
	 * @return array
	 */
	public function playersAMemberSavedBefore2313()
	{
		return array(
			'flash'  => array('[flash=60,40]http://example.com/a.swf?a=1&amp;amp;b=2[/flash]', 'http://example.com/a.swf?a=1&b=2'),
			'stream' => array('[stream width=60]http://example.com/a.wmv?a=1&amp;amp;b=2[/stream]', 'http://example.com/a.wmv?a=1&b=2'),
		);
	}

	/**
	 * @dataProvider playersAMemberSavedBefore2313
	 * @param string $stored
	 * @param string $url
	 */
	public function testAPlayerAMemberSavedBefore2313PlaysTheAddressTyped($stored, $url)
	{
		self::assertSame(array($url, $url), $this->playerUrlsIn($this->tp->toHTML($stored, true, 'BODY')));
	}

	/**
	 * parse_str() URL-decodes this bbcode's third parameter, which is how a <
	 * reaches the output when every other bbcode still has it encoded. That
	 * makes a whole element injectable, and an injected element needs no
	 * pointer over it to run.
	 */
	public function testTheFlashBbcodeCannotInjectAnElement()
	{
		$html = $this->renderStored(
			'[flash=1,1,a=%27%3E%3Csvg+onload%3Dalert(1)%3E]http://example.com/a.swf[/flash]');

		self::assertStringNotContainsString('<svg', $html,
			'A [flash] parameter injected an element: '.$html);
		$this->assertNoEventHandler($html, 'A [flash] parameter emitted an event handler.');
	}

	/**
	 * toDB() encodes an ampersand, and toHTML() never gives it back, so a
	 * stored multi-parameter bbcode only ever delivers its first parameter.
	 * Content written by a user who may post HTML, and a bbcode written into a
	 * template, keep their ampersands and are the paths on which the rest of
	 * the parameters arrive.
	 *
	 * @param string $bbcode
	 * @return string
	 */
	private function renderUnencoded($bbcode)
	{
		return $this->tp->toHTML($bbcode, true);
	}

	/**
	 * @return array
	 */
	public function attributeNameInjections()
	{
		return array(
			'textarea slash separated' => array('[textarea autofocus/onfocus=alert(1)]x[/textarea]'),
			'textarea handler alone'   => array('[textarea onclick=alert(1)]x[/textarea]'),
			'textarea uppercase'       => array('[textarea ONCLICK=alert(1)]x[/textarea]'),
			'textarea after an allowed' => array('[textarea rows/onfocus=alert(1)]x[/textarea]'),
			'stream slash separated'   => array('[stream autostart/onmouseover=alert(1)]http://e.com/a.wmv[/stream]'),
			'stream handler alone'     => array('[stream onclick=alert(1)]http://e.com/a.wmv[/stream]'),
		);
	}

	/**
	 * [textarea] and [stream] turn their parameter into an array with
	 * parse_str() and emit each key as an attribute NAME. toAttribute() makes a
	 * string safe between quotes and there are no quotes around a name, so it
	 * changed nothing here: a key of autofocus/onfocus reached the tag intact
	 * and HTML5 reads the slash as the end of one attribute and the start of
	 * the next, giving an auto-focused textarea with a live handler on it.
	 *
	 * The assertion is that the payload is simply absent, because the guard is
	 * an allow list of names and a rejected key is dropped whole. DOMDocument
	 * cannot be the oracle: it reads autofocus/onfocus as the single attribute
	 * autofocus and drops the handler, so reading attributes back would pass on
	 * the unfixed code that Chrome fires.
	 *
	 * @dataProvider attributeNameInjections
	 * @param string $bbcode
	 */
	public function testABbcodeParameterCannotNameAnAttribute($bbcode)
	{
		$html = $this->renderStored($bbcode);

		self::assertStringNotContainsString('alert(1)', $html,
			'A bbcode parameter named an attribute that is not allowed: '.$bbcode);
		self::assertSame(0, preg_match('#[\s/]on[a-z]+\s*=#i', $html),
			'A bbcode parameter emitted an event handler: '.$bbcode.' Rendered: '.$html);
	}

	/**
	 * @return e_parse storing as an author who may post HTML but not script
	 */
	private function htmlAuthor()
	{
		$parser = new e_parse();
		$parser->setScriptAccess(e_UC_NOBODY);

		return $parser;
	}

	/**
	 * A post as the editor's Insert e107 BBcode item writes it: each bbcode as typed, base64-encoded in an x-bbcode alt.
	 *
	 * @param string[] $bbcodes inserted in one paragraph, a word apart
	 * @return string
	 */
	private function insertedBbcode(array $bbcodes)
	{
		$elements = array();

		foreach ($bbcodes as $bbcode)
		{
			$elements[] = '<x-bbcode alt="'.base64_encode($bbcode).'">x</x-bbcode>';
		}

		return '[html]<p>Before '.implode(' middle ', $elements).'   after</p>[/html]';
	}

	/**
	 * @return string an x-bbcode element whose alt decodes to markup, as an author who may post HTML but not script stores it
	 */
	private function storedMarkupAlt()
	{
		return $this->htmlAuthor()->toDB('[html]<p><x-bbcode alt="PHUgdGFiaW5kZXg9MCBhdXRvZm9jdXMgb25mb2N1cz1wcmludCgpIGlkPT54PC91Pg==">x</x-bbcode></p>[/html]');
	}

	public function testAnXBbcodeAltCannotAddAHandler()
	{
		$html = $this->htmlAuthor()->toHTML($this->storedMarkupAlt(), true);

		$this->assertNoEventHandler($html, 'An x-bbcode alt added a handler to the page.');
		self::assertNotSame(false, strpos($html, '&lt;u tabindex=0'), 'An x-bbcode alt was not shown as text: '.$html);
	}

	/**
	 * @return array the bbcodes typed in one paragraph, and as the page shows them
	 */
	public function insertedBbcodes()
	{
		return array(
			'plain'                => array(array('[b]bold[/b]'), '[b]bold[/b]'),
			'a dollar sign'        => array(array('[b]costs $10[/b]'), '[b]costs $10[/b]'),
			'a plus in the alt'    => array(array('[b]a ~[/b]'), '[b]a ~[/b]'),
			'a slash in the alt'   => array(array('[link=https://e107.org/?a=1&b=2]e107[/link]'), '[link=https://e107.org/?a=1&amp;b=2]e107[/link]'),
			'two in one paragraph' => array(array('[b]one[/b]', '[i]two[/i]'), '[b]one[/b] middle [i]two[/i]'),
		);
	}

	/**
	 * @dataProvider insertedBbcodes
	 * @param string[] $bbcodes
	 * @param string $shown
	 */
	public function testAnInsertedBbcodeReadsAsTyped(array $bbcodes, $shown)
	{
		$author = $this->htmlAuthor();

		self::assertSame('<!-- bbcode-html-start --><p>Before '.$shown.'   after</p><!-- bbcode-html-end -->',
			$author->toHTML($author->toDB($this->insertedBbcode($bbcodes)), true));
	}

	/**
	 * autofocus is what makes the handler fire without the reader touching
	 * anything, and a post has no business taking the caret off the page.
	 */
	public function testTheTextareaBbcodeCannotStealTheCaret()
	{
		self::assertStringNotContainsString('autofocus', $this->renderStored('[textarea autofocus]x[/textarea]'),
			'A [textarea] parameter focused itself on load.');
	}

	/**
	 * The payload reaches the database byte for byte: it carries no angle
	 * bracket for toDB() to strip, and a legacy .bb bbcode gets no pre-save
	 * pass at all, unlike a bb_*.php class which whitelists its parameters
	 * there. Everything already stored therefore still holds its payload, and
	 * only the render can be made to neutralise it.
	 */
	public function testTheTextareaPayloadReachesTheDatabaseIntact()
	{
		$bbcode = '[textarea autofocus/onfocus=alert(1)]x[/textarea]';

		self::assertSame($bbcode, $this->tp->toDB($bbcode),
			'toDB() altered the payload, which would make this a save-time defect rather than a render-time one.');
	}

	/**
	 * @return array
	 */
	public function attributeNamePassthroughs()
	{
		return array(
			'name'      => array('[textarea name=comment]x[/textarea]', "name = 'comment'"),
			'uppercase' => array('[textarea NAME=comment]x[/textarea]', "NAME = 'comment'"),
			'style'     => array('[textarea style=width:100%]x[/textarea]', "style = 'width:100'"),
			'style colour' => array('[textarea style=color:#3366ff]x[/textarea]', "style = 'color:3366ff'"),
			'rows'      => array('[textarea rows=5]x[/textarea]', "rows = '5'"),
			'autocomplete' => array('[textarea autocomplete=off]x[/textarea]', "autocomplete = 'off'"),
		);
	}

	/**
	 * @dataProvider attributeNamePassthroughs
	 * @param string $bbcode
	 * @param string $expected
	 */
	public function testTheTextareaBbcodeKeepsTheAttributesItAllows($bbcode, $expected)
	{
		self::assertStringContainsString($expected, $this->renderStored($bbcode),
			'A [textarea] parameter naming an allowed attribute was dropped: '.$bbcode);
	}

	public function testTheTextareaBbcodeKeepsEveryAllowedAttributeOfAMultipartParameter()
	{
		self::assertStringContainsString("name = 'comment' rows = '5' cols = '40' ",
			$this->renderUnencoded('[textarea name=comment&rows=5&cols=40]x[/textarea]'),
			'A [textarea] with several allowed parameters lost some of them.');
	}

	/**
	 * [textarea] allows a style and wrote the value between the quotes as it
	 * was given, so a member's declaration list rendered whole. It goes through
	 * the guard [img] uses, which leaves no bracket to open a URL with. The
	 * guard is a character class, so it takes the hash off a colour and the per
	 * cent off a width as well, and it keeps a positioning declaration: whether
	 * a member may position an element at all is open for [img] and [block] on
	 * the same terms.
	 */
	public function testTheTextareaBbcodeGuardsItsStyleAttribute()
	{
		$html = $this->renderStored('[textarea style=background:url(//evil.tld/x);position:fixed]x[/textarea]');

		self::assertStringNotContainsString('url(', $html,
			'A [textarea] style reached out for a remote URL. Rendered: '.$html);
	}

	/**
	 * bb_img takes the whitespace and punctuation out of an id and [textarea]
	 * wrote the member's value as given, which is invalid markup and a name a
	 * script can be made to collide with.
	 */
	public function testTheTextareaBbcodeGuardsItsIdAttribute()
	{
		self::assertStringContainsString("id = 'qonmouseoveralert1'",
			$this->renderStored('[textarea id=q onmouseover=alert(1)]x[/textarea]'),
			'A [textarea] id kept the characters the [img] guard removes.');
	}

	public function testTheStreamBbcodeKeepsTheParametersItAllows()
	{
		$html = $this->renderUnencoded(
			'[stream autostart=false&showcontrols=true&wmode=transparent&width=640&height=480]http://e.com/a.wmv[/stream]');

		self::assertStringContainsString("wmode='transparent'", $html,
			'A [stream] lost the one attribute e_parse itself allows on an embed.');
		self::assertStringContainsString("autostart='false' showcontrols='true' ", $html,
			'A [stream] parameter naming an allowed player setting was dropped.');
		self::assertStringContainsString("<param name='autostart' value='false'>", $html,
			'A [stream] parameter naming an allowed player setting was dropped from the object.');
		self::assertStringContainsString("width='640' height='480'", $html,
			'A [stream] lost its size.');
	}

	/**
	 * @return array
	 */
	public function imageInjections()
	{
		return array(
			'attribute name'            => array('[img onmouseover=alert(1)]{e_IMAGE}generic/blank.gif[/img]'),
			'attribute name slash'      => array('[img autofocus/onfocus=alert(1)]{e_IMAGE}generic/blank.gif[/img]'),
			'style out of its quotes'   => array('[img style=q" onmouseover="alert(1)]{e_MEDIA_IMAGE}b.gif[/img]'),
			'id out of its quotes'      => array('[img id=q" onmouseover="alert(1)]{e_MEDIA_IMAGE}b.gif[/img]'),
			'class out of its quotes'   => array('[img class=q" onmouseover="alert(1)]{e_MEDIA_IMAGE}b.gif[/img]'),
			'width out of its quotes'   => array('[img width=q" onmouseover="alert(1)]{e_MEDIA_IMAGE}b.gif[/img]'),
			'height out of its quotes'  => array('[img height=q" onload="alert(1)]{e_MEDIA_IMAGE}b.gif[/img]'),
			'loading out of its quotes' => array('[img loading=q" onmouseover="alert(1)]{e_MEDIA_IMAGE}b.gif[/img]'),
		);
	}

	/**
	 * bb_img::toDB() whitelists these parameters on the way in and the render
	 * trusted that. It should not have: a row written before that whitelist
	 * existed still holds whatever was stored, and toHTML() is handed it either
	 * way, so the render is the boundary and the render is what is measured
	 * here.
	 *
	 * @dataProvider imageInjections
	 * @param string $bbcode
	 */
	public function testTheImageBbcodeGuardsItsParametersWhereItRendersThem($bbcode)
	{
		$html = $this->renderUnencoded($bbcode);

		self::assertSame(0, preg_match('#[\s/]on[a-z]+\s*=#i', $html),
			'An [img] parameter emitted an event handler: '.$bbcode.' Rendered: '.$html);
		$this->assertNoEventHandler($html, 'An [img] parameter emitted an event handler.');
	}

	/**
	 * The save-time pass is the second boundary and keeps a proof of its own:
	 * bb_img::toDB() rebuilds the parameter string from the keys it allows, so
	 * a handler name never reaches the row.
	 */
	public function testTheImageBbcodeDropsAParameterItDoesNotAllowAtSave()
	{
		$bbcode = '[img onmouseover=alert(1)]{e_MEDIA_IMAGE}b.gif[/img]';

		self::assertStringNotContainsString('onmouseover', $this->tp->toDB($bbcode),
			'bb_img::toDB() stored a parameter its allow list does not hold.');
		self::assertSame(0, preg_match('#[\s/]on[a-z]+\s*=#i', $this->renderStored($bbcode)),
			'A stored [img] emitted an event handler.');
	}

	/**
	 * @return array
	 */
	public function imagePassthroughs()
	{
		return array(
			'plain'  => array('[img class=floatleft&id=pic1&style=border:1px&alt=A+cat&width=100]{e_IMAGE}generic/blank.gif[/img]',
				"class='img-rounded rounded bbcode bbcode-img floatleft' id='pic1' style='border:1px' alt='A cat' width='100'"),
			'sized'  => array('[img width=100]{e_IMAGE}generic/blank.gif[/img]', "width='100'"),
			'bare'   => array('[img]{e_IMAGE}generic/blank.gif[/img]', "src='/e107_images/generic/blank.gif'"),
			'loading' => array('[img loading=eager]{e_MEDIA_IMAGE}b.gif[/img]', 'loading="eager"'),
		);
	}

	/**
	 * @dataProvider imagePassthroughs
	 * @param string $bbcode
	 * @param string $expected
	 */
	public function testTheImageBbcodeKeepsTheAttributesItAllows($bbcode, $expected)
	{
		self::assertStringContainsString($expected, $this->renderUnencoded($bbcode),
			'An [img] parameter naming an allowed attribute was dropped: '.$bbcode);
	}

	/**
	 * [alert] concatenates its parameter into a class attribute and neither its
	 * toDB() nor its toHTML() touched it, so an apostrophe closed the attribute.
	 * toHTML() hands one back: $search/$replace restores &#039; before bbcodes
	 * are parsed. bb_p, bb_block and bb_h already guard the same position with
	 * eHelper.
	 */
	public function testTheAlertBbcodeCannotEscapeItsClassAttribute()
	{
		$html = $this->renderStored("[alert=info' onmouseover='alert(1)]hello[/alert]");

		self::assertStringNotContainsString('alert(1)', $html,
			'An [alert] parameter escaped the class attribute. Rendered: '.$html);
		self::assertSame(0, preg_match('#[\s/]on[a-z]+\s*=#i', $html),
			'An [alert] parameter emitted an event handler. Rendered: '.$html);
	}

	/**
	 * @return array
	 */
	public function alertPassthroughs()
	{
		return array(
			'named'   => array('[alert=warning]hello[/alert]', "class='alert alert-warning'"),
			'default' => array('[alert]hello[/alert]', "class='alert alert-info'"),
		);
	}

	/**
	 * @dataProvider alertPassthroughs
	 * @param string $bbcode
	 * @param string $expected
	 */
	public function testTheAlertBbcodeKeepsAnOrdinaryVariant($bbcode, $expected)
	{
		self::assertStringContainsString($expected, $this->renderStored($bbcode),
			'An ordinary [alert] lost its variant class: '.$bbcode);
	}

	/**
	 * An allow list of names keeps a member out of the handler attributes, but
	 * eHelper::secureClassAttr() keeps whitespace, so a class VALUE is still a
	 * class list. e107 bundles Bootstrap, whose positioning utilities then turn
	 * one bbcode into a transparent overlay across the whole viewport that eats
	 * every click on the page. No script needed, so an allow list of names is
	 * not on its own enough wherever the value lands in a class.
	 */
	public function testTheAlertBbcodeCannotCoverThePageWithUtilityClasses()
	{
		$html = $this->renderStored('[alert=info position-fixed top-0 start-0 w-100 vh-100 opacity-0]x[/alert]');

		self::assertStringNotContainsString('position-fixed', $html,
			'An [alert] variant smuggled a second class in. Rendered: '.$html);
	}

	/**
	 * bb_block guards id and style with eHelper two lines below where it built
	 * its class, and did not guard the class. bb_p and bb_h guard all three.
	 */
	public function testTheBlockBbcodeCannotEscapeItsClassAttribute()
	{
		$html = $this->renderStored('[block=class=q" onmouseover="alert(1)]x[/block]');

		self::assertStringNotContainsString('alert(1)', $html,
			'A [block] class escaped its attribute. Rendered: '.$html);
		self::assertSame(0, preg_match('#[\s/]on[a-z]+\s*=#i', $html),
			'A [block] class emitted an event handler. Rendered: '.$html);
	}

	/**
	 * The allow list compares lowercased but the guards below it address the
	 * lowercase key, so an uppercase name used to pass the list and then skip
	 * its guard. Names are case-insensitive to a browser, so STYLE is style.
	 */
	public function testTheImageBbcodeGuardsAnUppercaseAttributeName()
	{
		$html = $this->renderUnencoded('[img STYLE=background:url(//evil.tld/x)]{e_IMAGE}generic/blank.gif[/img]');

		self::assertStringNotContainsString('url(', $html,
			'An uppercase [img] parameter skipped the guard its lowercase spelling gets. Rendered: '.$html);
	}

	/**
	 * parse_str() returns an array for a bracketed key, and the brackets reach
	 * it URL-encoded so the bbcode splitter never sees the closing one. Every
	 * guard downstream expects a string: strtolower() throws outright on PHP 8,
	 * and the eHelper guards hand an array to preg_replace() and get one back.
	 * A bbcode parameter has no legitimate array value, so it is refused where
	 * the parameter is taken in rather than at each guard.
	 *
	 * @dataProvider arrayValuedParameters
	 * @param string $bbcode
	 */
	public function testABbcodeParameterCannotCarryAnArrayValue($bbcode)
	{
		$html = $this->renderUnencoded($bbcode);

		self::assertStringNotContainsString('Array', $html,
			'A bracketed parameter reached the markup: '.$bbcode.' Rendered: '.$html);
	}

	/**
	 * @return array
	 */
	public function arrayValuedParameters()
	{
		return array(
			'img loading' => array('[img loading%5B%5D=lazy]{e_MEDIA_IMAGE}b.gif[/img]'),
			'img class'   => array('[img class%5B%5D=x]{e_MEDIA_IMAGE}b.gif[/img]'),
			'img style'   => array('[img style%5B%5D=x]{e_IMAGE}generic/blank.gif[/img]'),
			'img alt'     => array('[img alt%5B%5D=x]{e_MEDIA_IMAGE}b.gif[/img]'),
			'textarea'    => array('[textarea style%5B%5D=x]y[/textarea]'),
			'stream'      => array('[stream autostart%5B%5D=false]http://e.com/a.wmv[/stream]'),
		);
	}

	/**
	 * bb_img fills title from alt, and toImage() encodes alt but prints title
	 * as it is given. alt is the user's own text, so the payload legitimately
	 * survives inside it; what may not happen is title gaining a real quote,
	 * because that is what ends the attribute.
	 */
	public function testTheImageBbcodeCannotEscapeItsTitleByWayOfAlt()
	{
		$html = $this->renderUnencoded('[img alt=q" onload="alert(1)]{e_MEDIA_IMAGE}b.gif[/img]');

		self::assertStringNotContainsString('" onload="', $html,
			'An [img] alt escaped the title attribute. Rendered: '.$html);
		self::assertStringContainsString('title="q&quot;', $html,
			'The title attribute lost the encoding that keeps it inside its quotes. Rendered: '.$html);
	}

	/**
	 * bb_img cut an onerror= substring out of the parameter before parsing it,
	 * above the allow list that is the boundary, and took the words of ordinary
	 * text with it. The lower-case spelling never arrives here to be cut,
	 * because e_parse's own $search has rewritten it before a bbcode is read,
	 * so the one capitalisation that reaches bb_img whole is what is measured.
	 */
	public function testTheImageBbcodeKeepsAnUppercaseOnerrorInItsAlt()
	{
		self::assertStringContainsString('alt="Onerror = a handler"',
			$this->renderUnencoded('[img alt=Onerror = a handler]{e_MEDIA_IMAGE}b.gif[/img]'),
			'An [img] alt lost the words a blocklist matched.');
	}

	/**
	 * figcaption is not an attribute; mediaImage() reads it and unsets it. It
	 * still has to survive the render's allow list to get there.
	 */
	public function testTheImageBbcodeStillBuildsAFigureCaption()
	{
		$html = $this->renderUnencoded('[img figcaption=Hello]{e_MEDIA_IMAGE}b.gif[/img]');

		self::assertStringContainsString('<figcaption>Hello</figcaption>', $html,
			'An [img] lost its explicit caption. Rendered: '.$html);
	}

	/**
	 * @return array
	 */
	public function imageSources()
	{
		return array(
			'ampersand'   => array('[img]https://example.com/pics/cat.png?a=1&b=2[/img]', 'https://example.com/pics/cat.png?a=1&b=2'),
			'typed &amp;' => array('[img]https://example.com/pics/cat.png?a=1&amp;b=2[/img]', 'https://example.com/pics/cat.png?a=1&b=2'),
			'%26'         => array('[img]https://example.com/pics/cat.png?q=Tom%26Jerry[/img]', 'https://example.com/pics/cat.png?q=Tom%26Jerry'),
			'apostrophe'  => array("[img]https://example.com/pics/Tom's_cat.png[/img]", "https://example.com/pics/Tom's_cat.png"),
		);
	}

	/**
	 * @dataProvider imageSources
	 * @param string $bbcode
	 * @param string $src
	 */
	public function testAnImageLoadsTheAddressTyped($bbcode, $src)
	{
		self::assertSame($src, $this->elementIn($this->renderStored($bbcode), 'img')->getAttribute('src'));
	}

	public function testAnImageAMemberSavedBefore2313LoadsTheAddressTyped()
	{
		self::assertSame('https://example.com/pics/cat.png?a=1&b=2',
			$this->elementIn($this->tp->toHTML('[img]https://example.com/pics/cat.png?a=1&amp;amp;b=2[/img]', true), 'img')->getAttribute('src'));
	}

	/** A local [img] in a news item, whose images a new install resizes, asks for its thumbnail at that size. */
	public function testAResizedImageAsksForItsSize()
	{
		$bbcodes = e107::getBB();
		$mode = $bbcodes->getMode();
		$bbcodes->setClass('news');

		try
		{
			$src = $this->elementIn($this->renderStored('[img]{e_IMAGE}generic/blank.gif[/img]'), 'img')->getAttribute('src');
		}
		finally
		{
			$bbcodes->setClass($mode);
		}

		parse_str((string) parse_url($src, PHP_URL_QUERY), $query);
		self::assertSame(array('src', 'w', 'h'), array_keys($query), $src);
	}

	/**
	 * An [img] parameter is a query string, so an & in its text is typed %26. A third value is the caption a media image shows.
	 *
	 * @return array
	 */
	public function imageTexts()
	{
		return array(
			'alt %26'                  => array('[img alt=Tom %26 Jerry]https://example.com/pics/cat.png[/img]', 'Tom & Jerry'),
			'alt apostrophe'           => array("[img alt=Tom's cat]https://example.com/pics/cat.png[/img]", "Tom's cat"),
			'alt typed &lt;'           => array('[img alt=x %26lt;y%26gt;]https://example.com/pics/cat.png[/img]', 'x &lt;y&gt;'),
			'media alt %26'            => array('[img alt=Tom %26 Jerry]{e_MEDIA_IMAGE}b.gif[/img]', 'Tom & Jerry', 'Tom & Jerry'),
			'media alt apostrophe'     => array("[img alt=Tom's cat]{e_MEDIA_IMAGE}b.gif[/img]", "Tom's cat", "Tom's cat"),
			'media caption %26'        => array('[img alt=Cat&figcaption=Tom %26 Jerry]{e_MEDIA_IMAGE}b.gif[/img]', 'Cat', 'Tom & Jerry'),
			'media caption apostrophe' => array("[img alt=Cat&figcaption=Tom's cat]{e_MEDIA_IMAGE}b.gif[/img]", 'Cat', "Tom's cat"),
			'media caption typed &lt;' => array('[img alt=Cat&figcaption=Write %26lt;b%26gt; for bold]{e_MEDIA_IMAGE}b.gif[/img]', 'Cat', 'Write &lt;b&gt; for bold'),
		);
	}

	/**
	 * An [img] alt reads as typed, and so do the title it fills and the caption.
	 *
	 * @dataProvider imageTexts
	 * @param string $bbcode
	 * @param string $alt
	 * @param string $caption
	 */
	public function testAnImageTextReadsAsTyped($bbcode, $alt, $caption = null)
	{
		$html = $this->renderStored($bbcode);
		$image = $this->elementIn($html, 'img');

		self::assertSame(array($alt, $alt), array($image->getAttribute('alt'), $image->getAttribute('title')), 'Rendered: '.$html);

		if($caption !== null)
		{
			self::assertSame($caption, $this->elementIn($html, 'figcaption')->textContent, 'Rendered: '.$html);
		}
	}

	/**
	 * A dimension that is not a number is dropped rather than cast, because
	 * casting turns it into 0 and an image sized 0 is an image nobody can see.
	 */
	public function testTheImageBbcodeDoesNotCollapseANonNumericDimension()
	{
		foreach(array('[img width=auto]{e_MEDIA_IMAGE}b.gif[/img]',
			'[img height=auto]{e_MEDIA_IMAGE}b.gif[/img]') as $bbcode)
		{
			$html = $this->renderUnencoded($bbcode);

			self::assertStringNotContainsString('="0"', $html,
				'An [img] dimension was cast to zero: '.$bbcode.' Rendered: '.$html);
		}
	}

	/** No CLI request mints an e-token, so the token rows render in a booted subprocess that defines this stand-in. */
	const SESSION_TOKEN = 'e107t0ken0123456789abcdef0123456';

	/**
	 * @return array
	 */
	public function sessionTokenBbcodes()
	{
		return array(
			'img src'   => array('[img]http://evil.example/p.png?t={e_TOKEN}[/img]'),
			'url href'  => array('[url=http://evil.example/?u={e_TOKEN}]read[/url]'),
			'link href' => array('[link={e_TOKEN}http://evil.example/]read[/link]'),
		);
	}

	/**
	 * @dataProvider sessionTokenBbcodes
	 * @param string $bbcode
	 */
	public function testABbcodeDoesNotExpandTheSessionToken($bbcode)
	{
		list($output, $status) = $this->runInBootedCli("define('e_TOKEN', '".self::SESSION_TOKEN."'); \$tp = e107::getParser(); "
			."echo '@@'.\$tp->toHTML(\$tp->toDB(".var_export($bbcode, true)."), true).'@@';");

		$printed = implode("\n", $output);
		$matches = array();

		self::assertSame(0, $status, 'The render exited '.$status.":\n".$printed);
		self::assertSame(1, preg_match('/@@(.*)@@/s', $printed, $matches), "The render printed no answer:\n".$printed);
		self::assertStringNotContainsString(self::SESSION_TOKEN, $matches[1], 'A bbcode expanded {e_TOKEN} into its output: '.$matches[1]);
		self::assertStringContainsString('e_TOKEN}', $matches[1], 'A bbcode dropped {e_TOKEN} instead of leaving it as text: '.$matches[1]);
	}

	public function testALinkStillExpandsAPathConstantInItsAddress()
	{
		$html = $this->renderStored('[link={e_PLUGIN}forum/forum.php]Forum[/link]');

		self::assertStringContainsString("href='".e_PLUGIN."forum/forum.php'", $html, 'A path constant in a link stopped resolving: '.$html);
	}

	/**
	 * @return array
	 */
	public function serverPathBbcodes()
	{
		return array(
			'link href to downloads' => array('[link={e_DOWNLOAD}]files[/link]', 'e_DOWNLOAD'),
			'url href to uploads'    => array('[url]{e_UPLOAD}[/url]', 'e_UPLOAD'),
			'link href to system'    => array('[link={e_SYSTEM}logs/]logs[/link]', 'e_SYSTEM'),
			'img src in downloads'   => array('[img]{e_DOWNLOAD}x.png[/img]', 'e_DOWNLOAD'),
			'img thumbnail link to system' => array('[img]{e_SYSTEM}logs/th_x.png[/img]', 'e_SYSTEM'),
		);
	}

	/**
	 * @dataProvider serverPathBbcodes
	 * @param string $bbcode
	 * @param string $constant
	 */
	public function testABbcodeDoesNotExpandAServerPathConstant($bbcode, $constant)
	{
		$html = $this->renderStored($bbcode);

		self::assertStringNotContainsString(constant($constant), $html, 'A bbcode printed the server path {'.$constant.'}: '.$html);
		self::assertStringContainsString($constant.'}', $html, 'A bbcode dropped {'.$constant.'} instead of leaving it as text: '.$html);
	}

	/**
	 * @return array
	 */
	public function adminFolderBbcodes()
	{
		return array(
			'img src, forum post'    => array('[img]http://evil.example/p.png?a={e_ADMIN}[/img]', 'USER_BODY'),
			'img src, news body'     => array('[img]http://evil.example/p.png?a={e_ADMIN}[/img]', 'BODY'),
			'img src after a quote'  => array("[img]http://evil.example/p.png?q='&a={e_ADMIN}[/img]", 'USER_BODY'),
			'url href'               => array('[url=http://evil.example/?a={e_ADMIN}]read[/url]', 'USER_BODY'),
			'link href'              => array('[link=http://evil.example/?a={e_ADMIN}]read[/link]', 'USER_BODY'),
			'flash data'             => array('[flash=50,50]http://evil.example/f.swf?a={e_ADMIN}[/flash]', 'DESCRIPTION'),
		);
	}

	/**
	 * @dataProvider adminFolderBbcodes
	 * @param string $bbcode
	 * @param string $context
	 */
	public function testABbcodeDoesNotCarryTheAdminFolderOfAnAdministratorReader($bbcode, $context)
	{
		list($output, $status) = $this->runInBootedCli("\$tp = e107::getParser(); "
			."echo '@@'.(ADMIN ? 'admin' : 'guest').'|'.e107::getFolder('admin').'|'.\$tp->toHTML(\$tp->toDB(".var_export($bbcode, true)."), true, '".$context."').'@@';");

		$printed = implode("\n", $output);
		$matches = array();

		self::assertSame(0, $status, 'The render exited '.$status.":\n".$printed);
		self::assertSame(1, preg_match('/@@admin\|([^|]+)\|(.*)@@/s', $printed, $matches), "The render did not run as an administrator:\n".$printed);
		self::assertStringNotContainsString($matches[1], $matches[2], 'A bbcode printed the admin folder: '.$matches[2]);
	}

	public function testAWebPathConstantAfterAQuoteStillResolvesInAPost()
	{
		$html = $this->tp->toHTML($this->tp->toDB("[img]{e_PLUGIN}forum/images/x.png?q='[/img]"), true, 'USER_BODY');

		self::assertStringContainsString('e107_plugins/forum/images/x.png', $html, 'A web path constant after a quote stopped resolving: '.$html);
		self::assertStringNotContainsString('&#123;e_PLUGIN', $html);
	}

	/**
	 * @return array
	 */
	public function glyphBreakouts()
	{
		return array(
			'glyph name'       => array("[glyph]fa-x' autofocus tabindex=1 onfocus='alert(1)[/glyph]"),
			'glyph image path' => array("[glyph]x' autofocus tabindex=1 onfocus='alert(1).png[/glyph]"),
		);
	}

	/**
	 * A quote in [glyph] text stays inside the attribute {@see e_parse::toIcon()} prints it into.
	 *
	 * @dataProvider glyphBreakouts
	 * @param string $bbcode
	 */
	public function testAGlyphCannotEscapeItsAttribute($bbcode)
	{
		$this->assertNoEventHandler($this->renderStored($bbcode), 'A [glyph] escaped its attribute: '.$bbcode);
	}

	/** A glyph is one class name, and a class list would let it lay Bootstrap utilities over the page. */
	public function testAGlyphCannotCoverThePageWithUtilityClasses()
	{
		$html = $this->renderStored('[glyph]fa-x position-fixed top-0 start-0 w-100 vh-100[/glyph]');

		foreach($this->elementsOf($html) as $element)
		{
			self::assertNotContains('position-fixed', preg_split('/\s+/', $element->getAttribute('class')), 'A [glyph] smuggled a second class in. Rendered: '.$html);
		}
	}

	/** The media manager inserted some Bootstrap 3 names with a line break and tabs inside them. */
	public function testAGlyphNameWithWhitespaceInsideStillRenders()
	{
		$expected = $this->renderStored('[glyph]glyphicon-book.glyph[/glyph]');
		$html = $this->renderStored("[glyph]glyphicon-book\n\t\t\t.glyph[/glyph]");

		self::assertStringContainsString('book', $expected);
		self::assertSame($expected, $html, 'A [glyph] name with whitespace inside it stopped rendering.');
	}

	/**
	 * @return array
	 */
	public function glyphPassthroughs()
	{
		return array(
			'glyph from the media manager' => array('[glyph]fas-camera.glyph[/glyph]', 'fa-camera'),
			'core glyph'                   => array('[glyph]e-database-32[/glyph]', "class='S32 e-database-32'"),
			'glyph image path'             => array('[glyph]{e_IMAGE}generic/blank.gif[/glyph]', 'generic/blank.gif'),
		);
	}

	/**
	 * @dataProvider glyphPassthroughs
	 * @param string $bbcode
	 * @param string $expected
	 */
	public function testAnOrdinaryGlyphStillRenders($bbcode, $expected)
	{
		$html = $this->renderStored($bbcode);

		self::assertStringContainsString($expected, $html, 'An ordinary [glyph] stopped rendering: '.$bbcode.' Rendered: '.$html);
	}

	/**
	 * @return array
	 */
	public function videoBreakouts()
	{
		return array(
			'video youtube'  => array('[video]x" onload="alert(1);//.youtube[/video]'),
			'video playlist' => array('[video]x" onload="alert(1).youtubepl[/video]'),
			'video file'     => array('[video]x" autofocus tabindex=1 onfocus="alert(1).mp4[/video]'),
		);
	}

	/**
	 * A quote in [video] text stays inside the attribute {@see e_parse::toVideo()} prints it into.
	 *
	 * @dataProvider videoBreakouts
	 * @param string $bbcode
	 */
	public function testAVideoCannotEscapeItsAttribute($bbcode)
	{
		$this->assertNoEventHandler($this->renderStored($bbcode), 'A [video] escaped its attribute: '.$bbcode);
	}

	/** A YouTube id reaches a style attribute as well as a src, so CSS has to be refused there too. */
	public function testAVideoIdCannotAddCssToItsFrame()
	{
		$html = $this->renderStored('[video]x);position:fixed;top:0.youtube[/video]');

		self::assertStringNotContainsString('position:fixed', $html, 'A [video] id carried CSS into the frame. Rendered: '.$html);
	}

	/**
	 * @return array
	 */
	public function videoPassthroughs()
	{
		return array(
			'youtube video'                    => array('[video]dQw4w9WgXcQ.youtube[/video]', 'youtube.com/embed/dQw4w9WgXcQ?'),
			'youtube playlist'                 => array('[video]PLx0sYbCqOb8TBPRdmBHs5Iftvv9TPboYG.youtubepl[/video]', 'list=PLx0sYbCqOb8TBPRdmBHs5Iftvv9TPboYG"'),
			'video file'                       => array('[video]{e_MEDIA_FILE}2026-10/clip.mp4[/video]', 'files/2026-10/clip.mp4" type="video/mp4"'),
			'remote video file'                => array('[video]https://example.com/media/clip.mp4[/video]', 'src="https://example.com/media/clip.mp4"'),
			'remote video file with ampersand' => array('[video]https://example.com/tom&jerry/clip.mp4[/video]', 'src="https://example.com/tom&amp;jerry/clip.mp4"'),
		);
	}

	/**
	 * @dataProvider videoPassthroughs
	 * @param string $bbcode
	 * @param string $expected
	 */
	public function testAnOrdinaryVideoStillRenders($bbcode, $expected)
	{
		$html = $this->renderStored($bbcode);

		self::assertStringContainsString($expected, $html, 'An ordinary [video] stopped rendering: '.$bbcode.' Rendered: '.$html);
	}
}

/**
 * Print their parameter, as shortcodes that print stored or remote data would.
 */
class bbcodeAttributeInjectionShortcodes
{
	/** @var string[] */
	public static $handed = array();

	/**
	 * @param string $parm
	 * @return string
	 */
	public function sc_bbcode_attribute_echo($parm = '')
	{
		self::$handed[] = (string) $parm;

		return (string) $parm;
	}

	/**
	 * Renders its parameter as bbcode and prints the text, as a shortcode printing a member's field does.
	 *
	 * @param string $parm
	 * @return string
	 */
	public function sc_bbcode_attribute_rendered($parm = '')
	{
		return strip_tags(e107::getParser()->toHTML('[b]'.$parm.'[/b]', true));
	}

	/**
	 * Stores its parameter as a form would, as a shortcode saving a field does.
	 *
	 * @param string $parm
	 * @return string
	 */
	public function sc_bbcode_attribute_stored($parm = '')
	{
		e107::getParser()->toDB((string) $parm);

		return 'https://example.com/';
	}
}
