<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_parse::toAttribute() makes a value safe to sit between quotes, which is the
 * whole job for a title and half of it for an inline event handler: the browser
 * HTML-decodes the attribute before the handler is compiled as JavaScript, so a
 * &quot; that toAttribute() produced turns back into a real quote and closes
 * whatever string literal the value was sitting in.
 *
 * toJsString() is the other half. It emits the value as a complete JavaScript
 * string literal whose own delimiters are the only bare quotes in it, with every
 * character that could end a literal or start a tag written as \uXXXX, so one
 * HTML-decode pass leaves it intact.
 *
 * The property each test here defends is that no input can add a quote to the
 * script context. Assertions are written against a decoded copy, because the
 * decoded copy is what a browser compiles.
 */
class e_parse_toJsStringTest extends \Test\Unit
{
	/** @var e_parse */
	private $tp;

	protected function _before()
	{
		$this->tp = e107::getParser();
	}

	/**
	 * @return array
	 */
	public function ordinaryValues()
	{
		return array(
			'local part'   => array('john.doe', '"john.doe"'),
			'plus address' => array('john+tag', '"john+tag"'),
			'domain'       => array('mail.example.co.uk', '"mail.example.co.uk"'),
			'dashed'       => array('first-last_1', '"first-last_1"'),
			'empty'        => array('', '""'),
			'digits'       => array('12345', '"12345"'),
		);
	}

	/**
	 * An address made of ordinary characters has to come back as itself between
	 * quotes, or the fix would change the markup e107 has always emitted.
	 *
	 * @dataProvider ordinaryValues
	 * @param string $value
	 * @param string $expected
	 */
	public function testAnOrdinaryValueIsUnchangedApartFromItsDelimiters($value, $expected)
	{
		self::assertSame($expected, $this->tp->toJsString($value));
	}

	/**
	 * @return array
	 */
	public function dangerousValues()
	{
		return array(
			'double quote'   => array('"+(alert(1))+"'),
			'single quote'   => array("'+alert(1)+'"),
			'tag'            => array('</script><img src=x>'),
			'ampersand'      => array('&quot;+alert(1)+&quot;'),
			'numeric entity' => array('&#34;+alert(1)+&#34;'),
			'backslash'      => array('\\"+alert(1)+\\"'),
			'newline'        => array("a\nalert(1)//"),
			'line separator' => array("a\xE2\x80\xA8alert(1)"),
		);
	}

	/**
	 * The delimiting pair is the only bare quote allowed, however the value is
	 * spelled. Anything else means the value can close its own literal once the
	 * browser has decoded the attribute around it.
	 *
	 * @dataProvider dangerousValues
	 * @param string $value
	 */
	public function testNoValueCanAddAQuoteToTheScriptContext($value)
	{
		$literal = $this->tp->toJsString($value);
		$decoded = html_entity_decode($literal, ENT_QUOTES, 'UTF-8');

		self::assertSame(2, substr_count($decoded, '"'),
			'toJsString() let a value introduce a quote into the script context: '.$literal);
		self::assertSame('"', substr($decoded, 0, 1));
		self::assertSame('"', substr($decoded, -1));
	}

	/**
	 * @dataProvider dangerousValues
	 * @param string $value
	 */
	public function testNoValueCanStartATagOrAnEntity($value)
	{
		$literal = $this->tp->toJsString($value);

		self::assertSame(0, preg_match('/[<>&\']/', $literal),
			'toJsString() emitted a character that can start a tag or an entity: '.$literal);
	}

	/**
	 * Escaping is only correct if it is reversible: whatever the visitor typed
	 * has to be what the browser ends up with inside the string.
	 *
	 * @dataProvider dangerousValues
	 * @param string $value
	 */
	public function testTheEscapedValueStillDecodesToTheOriginal($value)
	{
		self::assertSame($value, json_decode($this->tp->toJsString($value)));
	}

	/**
	 * json_encode() returns false on malformed UTF-8. Returning that verbatim
	 * would interpolate as an empty string and leave the surrounding literal
	 * unterminated, so the failure has to produce an empty literal instead.
	 */
	public function testMalformedUtf8FailsClosedToAnEmptyLiteral()
	{
		self::assertSame('""', $this->tp->toJsString("\xB1\x31"));
	}

	public function testNonStringsAreCoerced()
	{
		self::assertSame('"1"', $this->tp->toJsString(1));
		self::assertSame('""', $this->tp->toJsString(null));
	}
}
