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
 */
class bbcodeAttributeInjectionTest extends \Test\Unit
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
	 * @param string $html
	 * @return DOMElement
	 */
	private function tableIn($html)
	{
		foreach($this->elementsOf($html) as $element)
		{
			if($element->tagName === 'table') return $element;
		}

		self::fail('No table was rendered from: '.$html);
	}

	/**
	 * The script these two bbcodes build is a chain of JavaScript string literals
	 * joined by +, and the whole defect is that a parameter could end one of them
	 * early and have the rest of itself run as code. So the property is structural:
	 * read back as the browser decodes it, each of the two must still be nothing
	 * but well-formed literals and the + between them. The href is read through
	 * rawurldecode() because a javascript: URL is percent-decoded before it is
	 * compiled, and the handler is not.
	 *
	 * @param string $html
	 * @param string $message
	 */
	private function assertHandlerIsOnlyStringLiterals($html, $message)
	{
		$literals = '(?:"(?:[^"\\\\]|\\\\.)*"|\+)+';

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

	/**
	 * @return array
	 */
	public function scriptUrls()
	{
		return array(
			'url body'       => array("[url]java\tscript:alert(1)[/url]"),
			'url parameter'  => array("[url=java\tscript:alert(1)]x[/url]"),
			'link parameter' => array("[link=java\tscript:alert(1)]x[/link]"),
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
		foreach($this->elementsOf($this->renderStored($bbcode)) as $element)
		{
			if($element->tagName !== 'a') continue;

			self::assertSame(0, preg_match('#^javascript:#i',
				preg_replace('/[\x00-\x20\x7F]/', '', $element->getAttribute('href'))),
				'An anchor carried a URL that can execute: '.$bbcode);
		}
	}

	public function testTheQuoteBbcodeCannotAddAnAttributeToItsCitation()
	{
		$html = $this->renderStored('[quote=" onmouseover="alert(1)]hello[/quote]');

		$this->assertNoEventHandler($html, 'A [quote] author name escaped the title attribute.');
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
}
