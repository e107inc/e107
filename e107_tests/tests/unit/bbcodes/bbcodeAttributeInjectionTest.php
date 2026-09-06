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
	 * Both scripts must read back as nothing but well-formed literals and the + between
	 * them, the href after the extra percent-decode a javascript: URL gets.
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

	/** A scheme this bbcode will not link costs the link, never the words the member wrote. */
	public function testARefusedSchemeStillRendersItsText()
	{
		$html = $this->renderStored('[url=tel:+15551234567]Call us[/url]');

		self::assertStringContainsString('Call us', $html, 'A refused scheme took the text with it.');
		self::assertStringNotContainsString('<a', $html, 'A refused scheme was linked anyway: '.$html);
	}

	/** Without this the payload rows above would pass on a bbcode that rendered nothing at all. */
	public function testAnOrdinaryUrlIsStillLinked()
	{
		$html = $this->renderStored('[url=http://example.com/a?b=1]x[/url]');

		foreach($this->elementsOf($html) as $element)
		{
			if($element->tagName === 'a')
			{
				self::assertSame('http://example.com/a?b=1', $element->getAttribute('href'), $html);

				return;
			}
		}

		self::fail('An ordinary URL rendered no anchor at all: '.$html);
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
}
