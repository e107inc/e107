<?php

/**
 * A hero slide's bullets and buttons, from whoever stored them, print as the values they are.
 */
class hero_shortcodesTest extends \Test\Unit
{
	const MARKUP = '"><svg onload=alert(1)>';

	/** @var plugin_hero_hero_shortcodes */
	private $sc;

	protected function _before()
	{
		require_once(e_PLUGIN.'hero/hero_shortcodes.php');
		$this->sc = new plugin_hero_hero_shortcodes();
	}

	public function testStoredMarkupInBulletsAndButtonsNeverRuns()
	{
		$button = array('icon' => '<i class="fa"></i>'.self::MARKUP, 'label' => 'Go'.self::MARKUP, 'url' => 'https://example.com/'.self::MARKUP, 'class' => "primary' autofocus onfocus='alert(1)");
		$this->sc->setVars(array(
			'hero_bullets' => array(array(
				'icon'            => '<i class="fa"></i>'.self::MARKUP,
				'icon_style'      => 'warning'.self::MARKUP,
				'text'            => 'Bullet'.self::MARKUP,
				'animation'       => 'fadeIn'.self::MARKUP,
				'animation_delay' => '15'.self::MARKUP,
			)),
			'hero_button1' => $button,
			'hero_button2' => $button,
		));
		$this->sc->count = 0;

		$this->assertNoMarkupIn($this->sc->sc_hero_icon(), 'icon');
		$this->assertNoMarkupIn($this->sc->sc_hero_icon(array('raw' => 1)), 'raw icon');
		$this->assertNoMarkupIn($this->sc->sc_hero_text(), 'text');
		$this->assertNoMarkupIn('<span class="label-'.$this->sc->sc_hero_icon_style().'"></span>', 'icon style');
		$this->assertNoMarkupIn('<div class="'.$this->sc->sc_hero_animation().' '.$this->sc->sc_hero_animation_delay().'"></div>', 'animation');

		foreach(array('1', '2') as $n)
		{
			$this->assertNoMarkupIn($this->sc->{'sc_hero_button'.$n.'_icon'}(), 'button '.$n.' icon');
			$this->assertNoMarkupIn($this->sc->{'sc_hero_button'.$n.'_label'}(), 'button '.$n.' label');
			$this->assertNoMarkupIn('<a href="'.$this->sc->{'sc_hero_button'.$n.'_url'}().'" class="btn btn-'.$this->sc->{'sc_hero_button'.$n.'_class'}().'">x</a>', 'button '.$n.' link');
			$this->assertNoMarkupIn("<a href='".$this->sc->{'sc_hero_button'.$n.'_url'}()."' class='btn btn-".$this->sc->{'sc_hero_button'.$n.'_class'}()."'>x</a>", 'button '.$n.' link');
		}

		$nextprev = '{NEXTPREV: total=2&amount=1&current=0&tmpl_prefix=default&url=%22%20autofocus%20onfocus%3Dalert(1)%20x%3D%22}';
		$printItem = "{PRINT_ITEM=x^' autofocus onfocus=alert(1) x='}";
		$this->sc->setVars(array(
			'hero_bullets' => array(
				array('text' => $nextprev, '__trust__' => array('text' => 0)),
				array('text' => $printItem, '__trust__' => array('text' => 0)),
			),
			'hero_button1' => array('label' => $nextprev),
			'hero_button2' => array('label' => $printItem),
		));

		foreach(array(0, 1) as $count)
		{
			$this->sc->count = $count;
			$this->assertNoMarkupIn($this->sc->sc_hero_text(), 'shortcode in text '.$count);
		}

		$this->assertNoMarkupIn($this->sc->sc_hero_button1_label(), 'shortcode in label 1');
		$this->assertNoMarkupIn($this->sc->sc_hero_button2_label(), 'shortcode in label 2');

		$this->sc->setVars(array('hero_button1' => array('url' => 'javascript:alert(1)', 'label' => 'Go', 'class' => 'primary', 'icon' => '')));
		$this->assertSame('', $this->sc->sc_hero_button1_url());

		$this->sc->setVars(array('hero_button1' => array('url' => 'https://example.com/?a=1&b=2')));
		$this->assertSame('https://example.com/?a=1&amp;b=2', $this->sc->sc_hero_button1_url());
	}

	/**
	 * A title or description stored as toDB() stores an HTML author's markup prints with its attribute values quoted.
	 */
	public function testATitleAndDescriptionPrintAsHtml()
	{
		$stored = 'Hero <b title=&quot;x autofocus onfocus=alert(1) y&quot;>here</b>';
		$this->sc->setVars(array('hero_title' => $stored, 'hero_description' => $stored));

		$this->assertNoMarkupIn($this->sc->sc_hero_title(), 'title');
		$this->assertNoMarkupIn($this->sc->sc_hero_description(), 'description');

		$this->sc->setVars(array('hero_title' => 'A [powerful] &amp; <b>free</b> &quot;hero&quot; area'));
		$this->assertSame('A <strong>powerful</strong> &amp; <b>free</b> "hero" area', $this->sc->sc_hero_title(array('enwrap' => 'strong')));

		$this->sc->setVars(array('hero_title' => 'e_ROOT', 'hero_description' => 'e_SYSTEM', 'hero_button1' => array('label' => 'e_ROOT', '__trust__' => array('label' => 1))));
		$this->assertSame('e_ROOT', $this->sc->sc_hero_title(), 'a constant name in a title stays a name');
		$this->assertSame('e_SYSTEM', $this->sc->sc_hero_description(), 'a constant name in a description stays a name');
		$this->assertSame('e_ROOT', $this->sc->sc_hero_button1_label(), 'a constant name in a label stays a name');
	}

	public function testPlainBulletsAndButtonsPrintAsBefore()
	{
		$this->sc->setVars(array(
			'hero_bullets' => array(array('icon' => 'fa-sun-o.glyph', 'icon_style' => 'warning', 'text' => 'Add some [b]bullet[/b] text', 'animation' => 'fadeInRight', 'animation_delay' => '15')),
			'hero_button1' => array('icon' => 'fa-arrow-circle-o-down', 'label' => 'Button', 'url' => 'https://example.com/q7c', 'class' => 'primary'),
		));
		$this->sc->count = 0;

		$this->assertStringStartsWith("<i class='", $this->sc->sc_hero_icon());
		$this->assertSame('warning', $this->sc->sc_hero_icon_style());
		$this->assertStringContainsString('<strong', $this->sc->sc_hero_text());
		$this->assertSame('fadeInRight', $this->sc->sc_hero_animation());
		$this->assertSame('animation-delay-15', $this->sc->sc_hero_animation_delay());
		$this->assertStringStartsWith("<i class='", $this->sc->sc_hero_button1_icon());
		$this->assertSame('Button', $this->sc->sc_hero_button1_label());
		$this->assertSame('https://example.com/q7c', $this->sc->sc_hero_button1_url());
		$this->assertSame('primary', $this->sc->sc_hero_button1_class());

		$this->sc->setVars(array('hero_button1' => array('label' => '{LAN=LAN_READ_MORE}')));
		$this->assertSame(e107::getParser()->attributeValue(LAN_READ_MORE), $this->sc->sc_hero_button1_label());
	}

	public function testPartsPrintAsTheirAuthorMayPostThem()
	{
		$button = array('label' => 'Go <b>now</b>', 'url' => 'tel:+15555550100', 'class' => 'primary', 'icon' => '<i class=&quot;fa fa-phone&quot;></i>');
		$bullet = array('text' => 'Some <b>bold</b> text', 'icon' => '<i class=&quot;fa fa-sun&quot;></i>');

		$this->sc->setVars(array('hero_bullets' => array($bullet + $this->recorded($bullet, 1)), 'hero_button1' => $button + $this->recorded($button, 1)));
		$this->sc->count = 0;
		$this->assertStringContainsString('<b>bold</b>', $this->sc->sc_hero_text());
		$this->assertStringContainsString('<b>now</b>', $this->sc->sc_hero_button1_label());
		$this->assertSame('tel:+15555550100', $this->sc->sc_hero_button1_url());
		$this->assertNoMarkupIn($this->sc->sc_hero_icon(), 'icon by an HTML author');

		foreach(array('javascript:alert(1)', ' JavaScript:alert(1)', 'data:text/html,x', 'vbscript:x', 'javascript&#58;alert(1)', 'javascript&colon;alert(1)') as $url)
		{
			$this->sc->setVars(array('hero_button1' => array('url' => $url, '__trust__' => array('url' => 1))));
			$this->assertSame('', $this->sc->sc_hero_button1_url(), $url);
		}

		$this->sc->setVars(array('hero_bullets' => array($bullet + $this->recorded($bullet, 2)), 'hero_button1' => $button + $this->recorded($button, 2)));
		$this->assertSame('<i class="fa fa-sun"></i>', $this->sc->sc_hero_icon());
		$this->assertStringStartsWith('<i class="fa fa-phone"></i>', $this->sc->sc_hero_button1_icon());

		$this->sc->setVars(array('hero_bullets' => array($bullet + $this->recorded($bullet, 0))));
		$this->assertStringNotContainsString('<b>', $this->sc->sc_hero_text());

		foreach(array(array(), $this->recorded($button + $bullet, 0)) as $record)
		{
			$this->sc->setVars(array('hero_bullets' => array($bullet + $record), 'hero_button1' => $button + $record));
			$this->assertStringNotContainsString('<b>', $this->sc->sc_hero_button1_label());
			$this->assertSame('', $this->sc->sc_hero_button1_url());
			$this->assertNoMarkupIn($this->sc->sc_hero_icon(), 'icon by an author who may not post HTML');
		}
	}

	/**
	 * Each value of a part prints as its own author may post it, whoever wrote the part's other values.
	 */
	public function testEachValueOfAPartPrintsAsItsOwnAuthorMayPostIt()
	{
		$this->sc->setVars(array('hero_button1' => array('label' => 'Go <b>now</b>', 'url' => 'tel:+15555550100', '__trust__' => array('label' => 0, 'url' => 1))));

		$this->assertStringNotContainsString('<b>', $this->sc->sc_hero_button1_label());
		$this->assertSame('tel:+15555550100', $this->sc->sc_hero_button1_url());
	}

	/**
	 * A bullet's text with no record of who saved it, stored as it was posted, prints as the same text prints once
	 * {@see e_parse::toDB()} has stored it for an author who may post HTML but not script: its markup and shortcodes
	 * kept, its scripts and event handlers gone.
	 */
	public function testABulletWithNoLevelPrintsAsAnHtmlAuthorWithoutScriptSavedIt()
	{
		$texts = array(
			'<strong id="q418s">Sale</strong> at {SITENAME}',
			'<img id="q418i" src="x.png" onerror="q418()">Bullet <b id="q418b" onclick="q418()">kept</b>'
				.'<script>var q418 = 1;</script>',
		);
		$printed = array();
		$expected = array();

		$this->withPostClasses(e_UC_PUBLIC, e_UC_NOBODY, function () use ($texts, &$printed, &$expected)
		{
			foreach($texts as $i => $text)
			{
				$saved = e107::getParser()->toDB($text, false, false, false, 1);
				$this->sc->setVars(array('hero_bullets' => array(
					array('text' => $saved, '__trust__' => array('text' => 1)),
					array('text' => $text),
				)));
				$this->sc->count = 0;
				$expected[$i] = $this->sc->sc_hero_text();
				$this->sc->count = 1;
				$printed[$i] = $this->sc->sc_hero_text();
			}
		});

		$this->assertSame($expected, $printed);

		$this->assertStringContainsString('<strong id="q418s">Sale</strong> at '.SITENAME, $printed[0]);
		$this->assertStringContainsString('src="x.png"', $printed[1]);
		$this->assertStringContainsString('<b id="q418b">kept</b>', $printed[1]);
		$this->assertNoMarkupIn($printed[1], 'scripted bullet');
		$this->assertStringNotContainsString('q418()', $printed[1]);
		$this->assertStringNotContainsString('var q418', $printed[1]);
	}

	/**
	 * @param array $part a bullet or a button
	 * @param int $level
	 * @return array the record of $level for each of its values
	 */
	private function recorded(array $part, $level)
	{
		return array('__trust__' => array_fill_keys(array_keys($part), $level));
	}

	/**
	 * @param string $html
	 * @param string $label
	 */
	private function assertNoMarkupIn($html, $label)
	{
		$doc = new DOMDocument();
		$errors = libxml_use_internal_errors(true);
		$doc->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');
		libxml_clear_errors();
		libxml_use_internal_errors($errors);

		foreach($doc->getElementsByTagName('*') as $element)
		{
			$this->assertNotSame('svg', $element->nodeName, $label.': '.$html);

			foreach($element->attributes as $attribute)
			{
				$this->assertStringStartsNotWith('on', $attribute->name, $label.': '.$html);
				$this->assertNotSame('autofocus', $attribute->name, $label.': '.$html);
				$this->assertStringStartsNotWith('javascript:', strtolower(trim($attribute->value)), $label.': '.$html);
			}
		}
	}
}
