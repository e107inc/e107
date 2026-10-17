<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class e_parse_isUnchangedPostTest extends \Codeception\Test\Unit
{
	/**
	 * @return array a posted value and the stored value the form showed, which a browser sends back as it was shown
	 */
	public function unchangedPosts()
	{
		return array(
			'the same text'                    => array('javascript:void(0)', 'javascript:void(0)'),
			'line breaks sent as CRLF'         => array("one\r\ntwo", "one\ntwo"),
			'a lone carriage return'           => array("one\rtwo", "one\ntwo"),
			'line breaks stored as CRLF'       => array("one\ntwo", "one\r\ntwo"),
			'nothing for nothing'              => array('', ''),
			'a number posted as text'          => array('5', 5),
			'null shown as nothing'            => array('', null),
			'the same keys in another order'   => array(array('b' => '2', 'a' => '1'), array('a' => '1', 'b' => '2')),
			'nested values'                    => array(array('url' => array("x\r\ny")), array('url' => array("x\ny"))),
			'an empty list'                    => array(array(), array()),
		);
	}

	/**
	 * @return array a posted value and a stored value it differs from
	 */
	public function changedPosts()
	{
		return array(
			'other text'                       => array('javascript:void(1)', 'javascript:void(0)'),
			'a trailing space'                 => array('news.php ', 'news.php'),
			'another case'                     => array('News.php', 'news.php'),
			'an entity for the same character' => array('&#039;', "'"),
			'a line break removed'             => array('onetwo', "one\ntwo"),
			'text for a list'                  => array('1', array('1')),
			'a list for text'                  => array(array('1'), '1'),
			'a key missing'                    => array(array('a' => '1'), array('a' => '1', 'b' => '2')),
			'a key added'                      => array(array('a' => '1', 'b' => '2'), array('a' => '1')),
			'a key renamed'                    => array(array('a' => '1', 'c' => '2'), array('a' => '1', 'b' => '2')),
			'a nested value changed'           => array(array('url' => array('x')), array('url' => array('y'))),
			'an object'                        => array(new stdClass(), ''),
		);
	}

	/**
	 * @return array a posted value, the stored value the form showed and the element that showed it, which a browser posts back altered that way when nobody touches it
	 */
	public function postsABrowserAlters()
	{
		return array(
			'a text input without its line breaks'      => array('Colour <b>y</b>', "Col\nour <b>y</b>", array('type' => 'text')),
			'a text input without a carriage return'    => array('onetwo', "one\rtwo", array('type' => 'text')),
			'a NUL read as a replacement character'     => array("a\xEF\xBF\xBDb", "a\0b", array('type' => 'text')),
			'an address input without its line breaks'  => array('javascript://void(0)', "javascript://\nvoid(0)", array('type' => 'url')),
			'a text area without its opening break'     => array("Line1\r\nLine2", "\nLine1\nLine2", array('type' => 'textarea')),
			'a text area with its opening break'        => array("\r\nLine1", "\nLine1", array('type' => 'textarea')),
			'an editor text area'                       => array('[b]x[/b]', "\r\n[b]x[/b]", array('type' => 'bbarea')),
			'an email input without its spacing'        => array('a@example.com,b@example.com', " a@example.com ,\tb@example.com ", array('type' => 'email')),
			'a tag list without its repeats'            => array('a,b', 'a,b,a', array('type' => 'tags')),
			'a tag list trimmed'                        => array('a,b', "\xC2\xA0a,b ", array('type' => 'tags')),
			'a tag list cut to its limit'               => array('1,2,3', '1,2,3,4', array('type' => 'tags', 'maxItems' => 3)),
			'a tag list cut to the widget\'s own limit' => array('1,2,3,4,5,6,7', '1,2,3,4,5,6,7,8', array('type' => 'tags')),
			'a tag list cut to a fractional limit'      => array('1,2,3', '1,2,3,4', array('type' => 'tags', 'maxItems' => 2.5)),
			'a tag list without a __proto__ tag'        => array('a,b', 'a,__proto__,b', array('type' => 'tags')),
			'a tag list posted before the widget loads' => array('a, b', "a,\n b", array('type' => 'tags')),
			'each value of a list'                      => array(array('x' => 'ab'), array('x' => "a\nb"), array('type' => 'text')),
		);
	}

	/**
	 * @return array a posted value, the stored value the form showed and the element that showed it, which no browser posts back for it untouched
	 */
	public function postsABrowserDoesNotAlter()
	{
		return array(
			'a text input with a space added'            => array('news.php ', 'news.php', array('type' => 'text')),
			'a text input with an entity for a quote'    => array('&#039;', "'", array('type' => 'text')),
			'a text area without a line break inside'    => array('ab', "a\nb", array('type' => 'textarea')),
			'a text input without its spacing'           => array('a,b', 'a, b', array('type' => 'text')),
			'an email input with another address'        => array('b@example.com', 'a@example.com', array('type' => 'email')),
			'a tag list in another order'                => array('b,a', 'a,b', array('type' => 'tags')),
			'a tag list with a tag removed'              => array('a', 'a,b', array('type' => 'tags')),
			'a tag list cut below the widget\'s limit'   => array('1,2,3', '1,2,3,4', array('type' => 'tags')),
			'a tag list emptied under a limit of nought' => array('', 'a,b', array('type' => 'tags', 'maxItems' => 0)),
			'line breaks removed with no element given'  => array('ab', "a\nb", null),
			'a NUL replaced with no element given'       => array("a\xEF\xBF\xBDb", "a\0b", null),
		);
	}

	/**
	 * @return array an element given in a shape that names no type
	 */
	public function elementsThatNameNoType()
	{
		return array(
			'a type name alone'       => array('text'),
			'no type'                 => array(array('maxItems' => 3)),
			'a type that is not text' => array(array('type' => array('text'))),
		);
	}

	/**
	 * @dataProvider unchangedPosts
	 * @param mixed $posted
	 * @param mixed $shown
	 */
	public function testAValueSentBackAsItWasShownIsUnchanged($posted, $shown)
	{
		self::assertTrue(e107::getParser()->isUnchangedPost($posted, $shown));
	}

	/**
	 * @dataProvider changedPosts
	 * @param mixed $posted
	 * @param mixed $shown
	 */
	public function testAnyOtherValueIsChanged($posted, $shown)
	{
		self::assertFalse(e107::getParser()->isUnchangedPost($posted, $shown));
	}

	/**
	 * @dataProvider postsABrowserAlters
	 * @param mixed $posted
	 * @param mixed $shown
	 * @param array $element
	 */
	public function testAValueABrowserPostsBackAlteredIsUnchangedForItsElement($posted, $shown, $element)
	{
		self::assertTrue(e107::getParser()->isUnchangedPost($posted, $shown, $element));
	}

	/**
	 * @dataProvider elementsThatNameNoType
	 * @param mixed $element
	 */
	public function testAnElementThatNamesNoTypeIsRefused($element)
	{
		try
		{
			e107::getParser()->isUnchangedPost('ab', "a\nb", $element);
		}
		catch (InvalidArgumentException $e)
		{
			return;
		}

		self::fail('the element was read as no element');
	}

	/**
	 * @dataProvider postsABrowserDoesNotAlter
	 * @param mixed $posted
	 * @param mixed $shown
	 * @param mixed $element
	 */
	public function testAnyOtherAlterationIsAChange($posted, $shown, $element)
	{
		self::assertFalse(e107::getParser()->isUnchangedPost($posted, $shown, $element));
	}
}
