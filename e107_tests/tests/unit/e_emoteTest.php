<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Emoticons turned into images and back into codes.
 */
class e_emoteTest extends \Codeception\Test\Unit
{
	/**
	 * Texts holding the default pack's smile, and what the reverse gives back: the first of the smile's codes.
	 *
	 * @return array
	 */
	public function textsWithAnEmoticon()
	{
		return array(
			'one emoticon and nothing else' => array(':)', ':)'),
			'an emoticon between words' => array('Thanks :) see you', 'Thanks :) see you'),
			'another code for the same emoticon' => array(':-)', ':)'),
		);
	}

	/**
	 * @dataProvider textsWithAnEmoticon
	 * @param string $text
	 * @param string $code
	 */
	public function testFilterEmotesRevGivesBackACodeForEachEmoticon($text, $code)
	{
		$emote = new e_emote();
		$images = $emote->filterEmotes($text);

		self::assertNotSame($text, $images);
		self::assertSame($code, $emote->filterEmotesRev($images));
	}
}
