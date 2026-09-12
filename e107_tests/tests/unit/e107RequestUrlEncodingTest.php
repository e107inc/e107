<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Reflection\ReflectionMethod;

/**
 * Covers e107::encodeRequestUrl(), the private helper that e107::set_urls()
 * runs over the request URI and URL before they become e_REQUEST_URL,
 * e_REQUEST_SELF, e_REQUEST_URI, e_REQUEST_HTTP and (on single-entry sites)
 * e_SELF.
 *
 * Those constants are pasted into shortcode parameter lists such as
 * {NEXTPREV=...}, so an unencoded brace or angle bracket in the request path
 * ends the tag early and the remainder lands in the page body (#6144).
 */
class e107RequestUrlEncodingTest extends \Test\Unit
{
	/** @var e107 */
	private $e107;

	/** @var ReflectionMethod */
	private $encodeRequestUrl;

	protected function _before()
	{
		try
		{
			$this->e107 = e107::getInstance();
		}
		catch(Exception $e)
		{
			self::fail("Couldn't load e107 object");
		}

		$this->encodeRequestUrl = new ReflectionMethod($this->e107, 'encodeRequestUrl');
	}

	/**
	 * @dataProvider encodeProvider
	 */
	public function testEncodeRequestUrl($url, $no_cbrace, $expected, $scenario)
	{
		$result = $this->encodeRequestUrl->invoke($this->e107, $url, $no_cbrace);
		$this->assertSame($expected, $result, "Failed scenario: $scenario");
	}

	public function encodeProvider()
	{
		return array(
			array(
				'/news.php?p=2',
				true,
				'/news.php?p=2',
				'an ordinary request is left alone',
			),
			array(
				'/news.php/}{SETIMAGE',
				true,
				'/news.php/%7D%7BSETIMAGE',
				'both braces are encoded so a shortcode tag cannot be closed early',
			),
			array(
				'/news.php/}<script>alert(1)</script>',
				true,
				'/news.php/%7D%3Cscript%3Ealert(1)%3C/script%3E',
				'angle brackets are encoded so the remainder cannot become markup',
			),
			array(
				'https://example.com/news.php/}{NEXTPREV',
				true,
				'https://example.com/news.php/%7D%7BNEXTPREV',
				'the absolute form behind e_REQUEST_URL is encoded too',
			),
			array(
				'/news.php/{a}',
				false,
				'/news.php/{a}',
				'curly brackets survive when the caller asked for them',
			),
			array(
				'/news.php/<a>',
				false,
				'/news.php/%3Ca%3E',
				'angle brackets are encoded whatever the caller asked for',
			),
			array(
				'/news.php?q=\'"',
				true,
				'/news.php?q=%27%22',
				'quotes keep the encoding the constants already carried',
			),
			array(
				'/news.php/%7B%7D',
				true,
				'/news.php/%7B%7D',
				'an already-encoded request is not encoded twice',
			),
		);
	}
}
