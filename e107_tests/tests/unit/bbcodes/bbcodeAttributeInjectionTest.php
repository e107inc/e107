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
 * The .bb bbcodes are driven along the whole path a stored payload really
 * takes, toDB() then toHTML() with bbcode parsing on, because they get no
 * pre-save pass and the defect lives in the seam between the two. [img] is a
 * bb_*.php class whose toDB() rewrites the parameter string, so its cases are
 * driven at the render, which is the boundary a row stored before that class
 * existed has to meet.
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

		self::assertSame(false, strpos($html, 'alert(1)'),
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
		self::assertSame(false, strpos($this->renderStored('[textarea autofocus]x[/textarea]'), 'autofocus'),
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
			'style'     => array('[textarea style=width:100%]x[/textarea]', "style = 'width:100%'"),
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
		self::assertNotSame(false, strpos($this->renderStored($bbcode), $expected),
			'A [textarea] parameter naming an allowed attribute was dropped: '.$bbcode);
	}

	public function testTheTextareaBbcodeKeepsEveryAllowedAttributeOfAMultipartParameter()
	{
		self::assertNotSame(false, strpos($this->renderUnencoded('[textarea name=comment&rows=5&cols=40]x[/textarea]'), "name = 'comment' rows = '5' cols = '40' "),
			'A [textarea] with several allowed parameters lost some of them.');
	}

	public function testTheStreamBbcodeKeepsTheParametersItAllows()
	{
		$html = $this->renderUnencoded(
			'[stream autostart=false&showcontrols=true&wmode=transparent&width=640&height=480]http://e.com/a.wmv[/stream]');

		self::assertNotSame(false, strpos($html, "wmode='transparent'"),
			'A [stream] lost the one attribute e_parse itself allows on an embed.');
		self::assertNotSame(false, strpos($html, "autostart='false' showcontrols='true' "),
			'A [stream] parameter naming an allowed player setting was dropped.');
		self::assertNotSame(false, strpos($html, "<param name='autostart' value='false'>"),
			'A [stream] parameter naming an allowed player setting was dropped from the object.');
		self::assertNotSame(false, strpos($html, "width='640' height='480'"),
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

		self::assertSame(false, strpos($this->tp->toDB($bbcode), 'onmouseover'),
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
		self::assertNotSame(false, strpos($this->renderUnencoded($bbcode), $expected),
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

		self::assertSame(false, strpos($html, 'alert(1)'),
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
		self::assertNotSame(false, strpos($this->renderStored($bbcode), $expected),
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

		self::assertSame(false, strpos($html, 'position-fixed'),
			'An [alert] variant smuggled a second class in. Rendered: '.$html);
	}

	/**
	 * bb_block guards id and style with eHelper two lines below where it built
	 * its class, and did not guard the class. bb_p and bb_h guard all three.
	 */
	public function testTheBlockBbcodeCannotEscapeItsClassAttribute()
	{
		$html = $this->renderStored('<b>hi</b>[block=class=q" onmouseover="alert(1)]x[/block]');

		self::assertSame(false, strpos($html, 'alert(1)'),
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

		self::assertSame(false, strpos($html, 'url('),
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

		self::assertSame(false, strpos($html, 'Array'),
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

		self::assertSame(false, strpos($html, '" onload="'),
			'An [img] alt escaped the title attribute. Rendered: '.$html);
		self::assertNotSame(false, strpos($html, 'title="q&quot;'),
			'The title attribute lost the encoding that keeps it inside its quotes. Rendered: '.$html);
	}

	/**
	 * figcaption is not an attribute; mediaImage() reads it and unsets it. It
	 * still has to survive the render's allow list to get there.
	 */
	public function testTheImageBbcodeStillBuildsAFigureCaption()
	{
		$html = $this->renderUnencoded('[img figcaption=Hello]{e_MEDIA_IMAGE}b.gif[/img]');

		self::assertNotSame(false, strpos($html, '<figcaption>Hello</figcaption>'),
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

			self::assertSame(false, strpos($html, '="0"'),
				'An [img] dimension was cast to zero: '.$bbcode.' Rendered: '.$html);
		}
	}
}
