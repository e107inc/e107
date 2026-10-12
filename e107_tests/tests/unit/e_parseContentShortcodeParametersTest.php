<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Shortcodes named in stored text, which the BODY, DESCRIPTION and TITLE contexts parse, as toDB() leaves that text.
 */
class e_parseContentShortcodeParametersTest extends \Codeception\Test\Unit
{
	/**
	 * @return array
	 */
	public function hostileParameters()
	{
		return array(
			'glyph class, percent-encoded'    => array('{GLYPH: type=fa-star&amp;class=x%27%3E%3Csvg%20onload%3Dalert(1)%3E}', '<svg'),
			'glyph space, percent-encoded'    => array('{GLYPH: type=fa-star&amp;space=%3Csvg%20onload%3Dalert(1)%3E}', '<svg'),
			'glyph class, typed quote'        => array('{GLYPH: type=fa-star&amp;class=x&#039; autofocus onfocus=&#039;alert(1)}', "' autofocus"),
			'url anchor, percent-encoded'     => array('{URL: 1=%3Csvg%20onload%3Dalert(1)%3E}', '<svg'),
			'avatar address, percent-encoded' => array('{USER_AVATAR: user_image=x%3A%2F%2F%3Csvg%20onload%3Dalert(1)%3E&amp;type=url}', '<svg'),
			'news image, equals form'         => array('{NEWSIMAGE=x&#039; autofocus tabindex=1 onfocus=&#039;alert(1)}', "' autofocus"),
			'profile, equals form'            => array('{PROFILE=x&#039; autofocus onfocus=&#039;alert(1)}', "' autofocus"),
			'print item, equals form'         => array('{PRINT_ITEM=x&quot; autofocus onfocus=&quot;alert(1)^y}', '" autofocus'),
		);
	}

	/**
	 * @dataProvider hostileParameters
	 * @param string $stored
	 * @param string $markup
	 */
	public function testAShortcodeInTextCannotPrintItsParametersAsMarkup($stored, $markup)
	{
		$html = e107::getParser()->toHTML($stored, true, 'BODY');

		self::assertNotSame('', trim($html), 'The shortcode printed nothing, so the row proves nothing');
		self::assertStringNotContainsString($markup, $html, 'A shortcode parameter became markup: '.$html);
	}

	/**
	 * @return array
	 */
	public function hostileDisclaimerClasses()
	{
		return array(
			'quote, percent-encoded'            => array('x%27%20autofocus%20onfocus%3D%27alert(1)', "' autofocus"),
			'quote entity, percent-encoded'     => array('x%26%23039%3B%20autofocus%20onfocus%3D%26%23039%3Balert(1)', "' autofocus"),
			'shortcode braces, percent-encoded' => array('x%7BGLYPH:%20type=fa-star%26class=a%20autofocus%20onfocus=alert(1)%20b%7D', '<i class='),
		);
	}

	/**
	 * @dataProvider hostileDisclaimerClasses
	 * @param string $class
	 * @param string $markup
	 */
	public function testTheSiteDisclaimerKeepsItsClassInsideTheAttribute($class, $markup)
	{
		$html = e107::getParser()->toHTML('{SITEDISCLAIMER: class='.$class.'}', true, 'BODY');

		self::assertStringContainsString("<a class='x", $html);
		self::assertStringNotContainsString($markup, $html);
	}

	/**
	 * @return array
	 */
	public function nestedShortcodes()
	{
		return array(
			'braces, percent-encoded' => array('{GLYPH: type=fa-star&amp;class=x%7BGLYPH:%20type=fa-star%26class=a%20autofocus%20onfocus=alert(1)%20b%7D}'),
			'braces, typed'           => array('{GLYPH: type=fa-star&amp;class=x{GLYPH: type=fa-star%26class=a autofocus onfocus=alert(1) b}}'),
		);
	}

	/**
	 * @dataProvider nestedShortcodes
	 * @param string $stored
	 */
	public function testAParameterCannotPrintAShortcodeForALaterParse($stored)
	{
		$tp = e107::getParser();
		$html = $tp->parseTemplate($tp->toHTML($stored, true, 'BODY'), true);

		self::assertStringContainsString("<i class='x", $html);
		self::assertStringNotContainsString("<i class='a", $html);
	}

	public function testAWrappedWordKeepsAHexadecimalEntityWhole()
	{
		self::assertSame('Jonathan&#x27;s', e107::getParser()->htmlwrap('Jonathan&#x27;s', 10));
	}

	public function testAShortcodeInTextKeepsAnOrdinaryParameter()
	{
		$html = e107::getParser()->toHTML('{GLYPH: type=fa-star&amp;class=text-danger}', true, 'BODY');

		self::assertStringContainsString('text-danger', $html);
		self::assertStringContainsString('fa-star', $html);
	}
}
