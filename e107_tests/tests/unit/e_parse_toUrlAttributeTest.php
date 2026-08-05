<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_parse::toAttribute() is htmlspecialchars() plus an optional constant
 * substitution (e_parse_class.php:2069-2083). That makes a value safe to sit
 * between quotes, which is the right answer for a title or an alt and only half
 * the answer for an href: "javascript:alert(1)" contains no quote, no angle
 * bracket and no ampersand, so it survives an encoder intact and fires on click.
 *
 * Six sinks in this package build an href or a src out of a URL chosen by a
 * remote feed or by a visitor, which is why the predicate is a function rather
 * than six inline checks.
 *
 * The deny list is written as an allow list on purpose: e107 already owns a deny
 * list at e_parse_class.php:3896 ($badAttrValues), and it is reachable only
 * through cleanHtml() on a whole HTML string, not on a bare URL, and a deny list
 * is the wrong shape for a scheme test in any case.
 */
class e_parse_toUrlAttributeTest extends \Codeception\Test\Unit
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
	public function executableSchemes()
	{
		return array(
			'javascript'            => array('javascript:alert(1)'),
			'javascript uppercase'  => array('JaVaScRiPt:alert(1)'),
			'javascript with tab'   => array("java\tscript:alert(1)"),
			'javascript with space' => array('  javascript:alert(1)'),
			'javascript newline'    => array("java\nscript:alert(1)"),
			'vbscript'              => array('vbscript:msgbox(1)'),
			'data html'             => array('data:text/html;base64,PHNjcmlwdD4='),
			'unknown scheme'        => array('chrome://settings'),
		);
	}

	/**
	 * @dataProvider executableSchemes
	 * @param string $url
	 */
	public function testAnExecutableSchemeIsReplaced($url)
	{
		$this->assertSame('', $this->tp->toUrlAttribute($url),
			'A URL whose scheme can execute was still emitted as an href: '.$url);
	}

	public function testTheFallbackIsUsedWhenGiven()
	{
		$this->assertSame('#', $this->tp->toUrlAttribute('javascript:alert(1)', '#'));
	}

	/**
	 * @return array
	 */
	public function ordinaryUrls()
	{
		return array(
			'absolute http'       => array('http://example.com/a', 'http://example.com/a'),
			'absolute https'      => array('https://example.com/a', 'https://example.com/a'),
			'mailto'              => array('mailto:someone@example.com', 'mailto:someone@example.com'),
			'ftp'                 => array('ftp://example.com/a', 'ftp://example.com/a'),
			'site rooted'         => array('/news.php?extend.7', '/news.php?extend.7'),
			'relative'            => array('news.php', 'news.php'),
			'protocol relative'   => array('//example.com/a', '//example.com/a'),
			'fragment'            => array('#top', '#top'),
			'colon in the query'  => array('/a.php?t=12:30', '/a.php?t=12:30'),
			'ampersand encoded'   => array('/a.php?x=1&y=2', '/a.php?x=1&amp;y=2'),
			'quote encoded'       => array("/a.php?x=1' onmouseover='alert(1)",
				'/a.php?x=1&#039; onmouseover=&#039;alert(1)'),
		);
	}

	/**
	 * @dataProvider ordinaryUrls
	 * @param string $url
	 * @param string $expected
	 */
	public function testAnOrdinaryUrlSurvivesEncodedForItsAttribute($url, $expected)
	{
		$this->assertSame($expected, $this->tp->toUrlAttribute($url));
	}

	/**
	 * The encoder half is still the encoder half: nothing that reaches an
	 * attribute may carry a character that can close it.
	 */
	public function testNothingItReturnsCanCloseAnAttribute()
	{
		$payloads = array(
			"https://example.com/?a=1' onmouseover='alert(1)",
			'https://example.com/?a=1" onmouseover="alert(1)',
			'https://example.com/?a=1><script>alert(1)</script>',
		);

		foreach($payloads as $payload)
		{
			$actual = $this->tp->toUrlAttribute($payload);

			$this->assertSame(0, preg_match('/[\'"<>]/', $actual),
				'toUrlAttribute() emitted a character that closes an attribute: '.$actual);
		}
	}
}
