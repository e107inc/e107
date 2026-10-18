<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_parse::splitAtBBCodeTags(), which reads a whole text once, and e_parse::BBCODE_TAG_LEAVES_NO_HTML_TAG_OPEN, which reads one bbcode tag, give every [ the same answer.
 */
class e_parseSplitAtBBCodeTagsTest extends \Codeception\Test\Unit
{
	/** a [ with a ] somewhere after it, the shape every pattern handed to e_parse::splitAtBBCodeTags() reads */
	const BRACKET_BEFORE_A_CLOSER = '#(\[)(?=[^\]]*+\])#';

	public function testABracketBeginsAMatchExactlyWhereTheLookaheadHoldsAfterIt()
	{
		$pieces = array('[', ']', '>', '!', '"', "'", '<', '<>', '<a', '</', '</a', '<!x', '<!--', '-->', '--!>', '<!-->', '<!--->', '-->]', '>]', '<svg>');
		$texts = array('');
		$differing = array();

		for ($length = 1; $length <= 4; $length++)
		{
			$longer = array();

			foreach ($texts as $text)
			{
				foreach ($pieces as $piece)
				{
					$longer[] = $text . $piece;
				}
			}

			$texts = $longer;

			foreach ($texts as $text)
			{
				if ($this->bracketsTheSplitLetBegin($text) !== $this->bracketsTheLookaheadLetBegin($text))
				{
					$differing[] = $text;
				}
			}
		}

		self::assertSame(array(), $differing);
	}

	public function testASplitHandsBackTheTextAsWritten()
	{
		$text = '[code <u title="x]">u</u>[/code] [code]a[b]c[/code] <i title="[code]">i</i>';

		self::assertSame(
			array('[code <u title="x]">u</u>[/code] ', '[code]a[b]c[/code]', 'code', ' <i title="[code]">i</i>'),
			e_parse::splitAtBBCodeTags('#(\[(code).*?\[/\2\])#is', $text, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE)
		);
		self::assertSame(
			array('a ', '[b]x[/b]', ' c'),
			e_parse::splitAtBBCodeTags('#(\[b\].*?\[/b\])#', 'a [b]x[/b] c', PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE)
		);
	}

	public function testABracketBeforeATagThePatternEngineGivesUpOnBeginsNoMatch()
	{
		$text = '[<a' . str_repeat(' ""', 20000) . '>]';
		$limit = ini_get('pcre.backtrack_limit');
		$jit = ini_get('pcre.jit');
		ini_set('pcre.backtrack_limit', '20000');
		ini_set('pcre.jit', '0');

		try
		{
			preg_match('#\G\[' . e_parse::BBCODE_TAG_LEAVES_NO_HTML_TAG_OPEN . '#', $text);
			$error = preg_last_error();
			$pieces = e_parse::splitAtBBCodeTags(self::BRACKET_BEFORE_A_CLOSER, $text, PREG_SPLIT_DELIM_CAPTURE);
		}
		finally
		{
			ini_set('pcre.backtrack_limit', $limit);

			if ($jit !== false)
			{
				ini_set('pcre.jit', $jit);
			}
		}

		if ($error === PREG_NO_ERROR)
		{
			self::markTestSkipped('The pattern engine read the tag within the lowered limit.');
		}

		self::assertSame(array($text), $pieces);
	}

	/**
	 * @param string $text
	 * @return int[] the offset of every [ that begins a match of the split
	 */
	private function bracketsTheSplitLetBegin($text)
	{
		$begins = array();
		$offset = 0;

		foreach (e_parse::splitAtBBCodeTags(self::BRACKET_BEFORE_A_CLOSER, $text, PREG_SPLIT_DELIM_CAPTURE) as $key => $piece)
		{
			if ($key % 2)
			{
				$begins[] = $offset;
			}

			$offset += strlen($piece);
		}

		return $begins;
	}

	/**
	 * @param string $text
	 * @return int[] the offset of every [ after which the lookahead holds
	 */
	private function bracketsTheLookaheadLetBegin($text)
	{
		$begins = array();

		for ($offset = strpos($text, '['); $offset !== false; $offset = strpos($text, '[', $offset + 1))
		{
			if (preg_match('#\G\[' . e_parse::BBCODE_TAG_LEAVES_NO_HTML_TAG_OPEN . '#', $text, $match, 0, $offset))
			{
				$begins[] = $offset;
			}
		}

		return $begins;
	}
}
