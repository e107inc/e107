<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * What {@see e_parse::bbcodeTextAsTyped()} takes back from a value a post stores, and what it leaves written.
 */
class e_parse_bbcodeTextAsTypedTest extends \Codeception\Test\Unit
{
	/** @var e_parse */
	private $tp;

	protected function _before()
	{
		$this->tp = e107::getParser();
	}

	/**
	 * @return array the value stored, and what the author typed
	 */
	public function storedTexts()
	{
		return array(
			'ampersand'                   => array('a=1&amp;b=2', 'a=1&b=2'),
			'ampersand stored twice'      => array('a=1&amp;amp;b=2', 'a=1&b=2'),
			'less and greater than'       => array('&lt;b&gt;', '<b>'),
			'less and greater than twice' => array('&amp;lt;b&amp;gt;', '<b>'),
			'double quote'                => array('say &quot;hi&quot;', 'say "hi"'),
			'double quote twice'          => array('say &amp;quot;hi&amp;quot;', 'say "hi"'),
			'apostrophe'                  => array('Tom&#039;s cat', "Tom's cat"),
			'apostrophe twice'            => array('Tom&amp;#039;s cat', "Tom's cat"),
			'all five'                    => array('&amp; &lt; &gt; &quot; &#039;', '& < > " \''),
			'nothing written'             => array('https://example.com/a.php?x=1', 'https://example.com/a.php?x=1'),
			'percent escape'              => array('a=1%26b=2', 'a=1%26b=2'),
			'empty'                       => array('', ''),
		);
	}

	/**
	 * @dataProvider storedTexts
	 * @param string $stored
	 * @param string $typed
	 */
	public function testTwoLevelsOfTheFiveCharactersAreTakenBack($stored, $typed)
	{
		self::assertSame($typed, $this->tp->bbcodeTextAsTyped($stored));
	}

	public function testAThirdLevelStaysWritten()
	{
		self::assertSame('a=1&amp;b=2', $this->tp->bbcodeTextAsTyped('a=1&amp;amp;amp;b=2'));
	}

	/**
	 * @return array a numeric reference to one of the five characters, and the character
	 */
	public function numericReferences()
	{
		return array(
			'ampersand'                 => array('&#38;', '&'),
			'ampersand in hex'          => array('&#x26;', '&'),
			'less than'                 => array('&#60;', '<'),
			'greater than'              => array('&#62;', '>'),
			'double quote'              => array('&#34;', '"'),
			'apostrophe'                => array('&#39;', "'"),
			'apostrophe in hex'         => array('&#x27;', "'"),
			'ampersand reference twice' => array('&amp;#38;', '&'),
		);
	}

	/**
	 * @dataProvider numericReferences
	 * @param string $stored
	 * @param string $typed
	 */
	public function testANumericReferenceToOneOfTheFiveIsTakenBack($stored, $typed)
	{
		self::assertSame($typed, $this->tp->bbcodeTextAsTyped($stored));
	}

	/**
	 * @return array a reference that stays written
	 */
	public function otherReferences()
	{
		return array(
			'braces'            => array('&#123;e_BASE&#125;'),
			'braces in hex'     => array('&#x7B;SITENAME&#x7D;'),
			'square brackets'   => array('&#91;b&#93;'),
			'named copyright'   => array('&copy; 2026'),
			'numeric copyright' => array('&#169; 2026'),
			'no-break space'    => array('a&nbsp;b'),
			'named apostrophe'  => array('Tom&apos;s cat'),
		);
	}

	/**
	 * @dataProvider otherReferences
	 * @param string $stored
	 */
	public function testEveryOtherReferenceStaysWritten($stored)
	{
		self::assertSame($stored, $this->tp->bbcodeTextAsTyped($stored));
	}

	public function testABraceReferenceStoredTwiceStaysWritten()
	{
		self::assertSame('&#123;e_BASE&#125;', $this->tp->bbcodeTextAsTyped('&amp;#123;e_BASE&amp;#125;'));
	}
}
