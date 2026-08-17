<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_parse::toDB() encodes a visitor's quotes, and e_parse::toHTML() gives them
 * back: $search/$replace turns &quot; into " immediately before bbcodes are
 * parsed. Every .bb file therefore receives raw quotes in $parm and has to
 * encode them itself before putting them in markup, and five of them did not,
 * which let any member store JavaScript that ran for whoever read the page.
 *
 * These tests drive the whole path a stored payload really takes, toDB() then
 * toHTML() with bbcode parsing on, because the defect lives in the seam between
 * the two rather than in either half.
 *
 * Two shapes of assertion are used, and neither pins the exact markup, so the
 * templates stay free to change:
 *
 *   - where a bbcode has no business emitting an event handler at all, none may
 *     appear;
 *   - where one legitimately does ([link] and [email] build a mailto out of
 *     inline script), the handler may not gain a quote that a benign address
 *     would not have produced, since a quote is what ends the string literal.
 *
 * Attribute values are read back through DOMDocument, which decodes them the
 * way a browser does before compiling the handler.
 */
class bbcodeAttributeInjectionTest extends \Codeception\Test\Unit
{
	/** @var e_parse */
	private $tp;

	/** @var mixed */
	private $originalMakeClickable;

	protected function _before()
	{
		$this->tp = e107::getParser();
		$this->originalMakeClickable = e107::getConfig()->get('make_clickable');
	}

	protected function _after()
	{
		e107::getConfig()->set('make_clickable', $this->originalMakeClickable);
	}

	/**
	 * The round trip a stored signature, forum post or comment actually makes.
	 *
	 * @param string $bbcode
	 * @param e_parse $parser
	 * @return string
	 */
	private function renderStored($bbcode, $parser = null)
	{
		$parser = $parser ? $parser : $this->tp;

		return $parser->toHTML($parser->toDB($bbcode), true);
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
	 * The handler these two bbcodes build is a chain of JavaScript string
	 * literals joined by +, and the whole defect is that a parameter could end
	 * one of them early and have the rest of itself run as code. So the property
	 * is structural: read back as the browser decodes it, the handler must still
	 * be nothing but well-formed literals and the + between them.
	 *
	 * @param string $html
	 * @param string $message
	 */
	private function assertHandlerIsOnlyStringLiterals($html, $message)
	{
		foreach($this->elementsOf($html) as $element)
		{
			if($element->tagName === 'a' && $element->hasAttribute('onmouseover'))
			{
				self::assertSame(1, preg_match(
					'#^window\.status=(?:"(?:[^"\\\\]|\\\\.)*"|\+)+; return true;$#',
					$element->getAttribute('onmouseover')), $message.' Rendered: '.$html);

				return;
			}
		}

		self::fail('No anchor carrying an onmouseover was rendered from: '.$html);
	}

	public function testTheMailtoLinkBbcodeCannotEndAStringLiteral()
	{
		$this->assertHandlerIsOnlyStringLiterals(
			$this->renderStored('[link=mailto:"+(alert(1))+"@example.com]contact[/link]'),
			'A [link=mailto:] parameter escaped the JavaScript string literal it was placed in.');
	}

	/**
	 * A valid address never reaches this bbcode while make_clickable is on: the
	 * clickable-link pass rewrites it first. A payload does reach it, because it
	 * does not look like an address, which is exactly why the sink matters.
	 */
	public function testTheEmailBbcodeCannotEndAStringLiteral()
	{
		e107::getConfig()->set('make_clickable', 1);
		$parser = new e_parse();

		$this->assertHandlerIsOnlyStringLiterals(
			$this->renderStored('[email="+(alert(1))+"@example.com]contact[/email]', $parser),
			'An [email] parameter escaped the JavaScript string literal it was placed in.');
	}

	/**
	 * The same payload, written in the text rather than the parameter.
	 */
	public function testTheEmailBbcodeCannotEndAStringLiteralFromItsText()
	{
		e107::getConfig()->set('make_clickable', 1);
		$parser = new e_parse();

		$this->assertHandlerIsOnlyStringLiterals(
			$this->renderStored('[email]"+(alert(1))+"@example.com[/email]', $parser),
			'An [email] body escaped the JavaScript string literal it was placed in.');
	}

	/**
	 * The address still has to survive the escaping, or the fix would have
	 * quietly broken every mailto link on every site.
	 */
	public function testAnOrdinaryMailtoLinkStillCarriesItsAddress()
	{
		$html = $this->renderStored('[link=mailto:john.doe@example.co.uk]contact[/link]');

		self::assertNotSame(false, strpos($html, '"john.doe"'), $html);
		self::assertNotSame(false, strpos($html, '"example.co.uk"'), $html);
	}

	public function testTheQuoteBbcodeCannotAddAnAttributeToItsCitation()
	{
		$html = $this->renderStored('[quote=" onmouseover="alert(1)]hello[/quote]');

		$this->assertNoEventHandler($html, 'A [quote] author name escaped the title attribute.');
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
	 * [table] emits its parameter as raw attributes by design, so the guard is
	 * an allow list of names rather than an encoder, and a rejected parameter is
	 * dropped whole. Asserting the payload is simply absent is what makes this
	 * faithful: DOMDocument does not reproduce every transition a browser makes
	 * back into attribute-name state, and treats style="x"/onmouseover="..." as
	 * one attribute where Chrome reads two.
	 *
	 * @dataProvider tableInjections
	 * @param string $bbcode
	 */
	public function testTheTableBbcodeDropsAnAttributeItDoesNotAllow($bbcode)
	{
		$html = $this->renderStored($bbcode);

		self::assertSame(false, strpos($html, 'alert(1)'),
			'A [table] parameter naming a disallowed attribute was emitted: '.$bbcode);
		$this->assertNoEventHandler($html, 'A [table] parameter emitted an event handler.');
	}

	/**
	 * A parameter carrying a > would close the tag early, whether or not it is
	 * still spelled as an entity when this bbcode is parsed.
	 */
	public function testTheTableBbcodeDropsAParameterThatClosesTheTag()
	{
		foreach(array('[table style=x>foo]y[/table]', '[table style=x&gt;foo]y[/table]') as $bbcode)
		{
			self::assertSame(false, strpos($this->renderStored($bbcode), 'foo'),
				'A [table] parameter closed its own tag: '.$bbcode);
		}
	}

	/**
	 * @return array
	 */
	public function tablePassthroughs()
	{
		return array(
			'class'          => array('[table class=table]x[/table]', "class=table"),
			'generated css'  => array('[table style=border-collapse: collapse; width: 100%]x[/table]',
				'style=border-collapse: collapse; width: 100%'),
			'several'        => array('[table border=1 cellpadding=2 cellspacing=0]x[/table]',
				'border=1 cellpadding=2 cellspacing=0'),
			'quoted'         => array('[table style="width: 50%" class="foo"]x[/table]',
				'style="width: 50%" class="foo"'),
		);
	}

	/**
	 * The WYSIWYG converter writes [table style=<raw css>], unquoted and full of
	 * spaces, so the allow list has to leave that spelling alone.
	 *
	 * @dataProvider tablePassthroughs
	 * @param string $bbcode
	 * @param string $expected
	 */
	public function testTheTableBbcodeKeepsTheAttributesItAllows($bbcode, $expected)
	{
		self::assertNotSame(false, strpos($this->renderStored($bbcode), $expected),
			'A [table] parameter that only names allowed attributes was dropped: '.$bbcode);
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

		self::assertSame(false, strpos($html, '<svg'),
			'A [flash] parameter injected an element: '.$html);
		$this->assertNoEventHandler($html, 'A [flash] parameter emitted an event handler.');
	}
}
