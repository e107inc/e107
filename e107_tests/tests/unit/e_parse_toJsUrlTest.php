<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {@see e_parse::toJsUrl()} turns script into a javascript: address that a single-quoted href carries unchanged.
 */
class e_parse_toJsUrlTest extends \Codeception\Test\Unit
{
	use \Test\JavascriptAddresses;

	/** @var e_parse */
	private $tp;

	protected function _before()
	{
		$this->tp = e107::getParser();
	}

	/**
	 * @return array
	 */
	public function scripts()
	{
		return array(
			'percent sign'    => array('open_window("a%27b",600,400)'),
			'apostrophe'      => array("open_window('x',600,400)"),
			'ampersand'       => array('open_window("a&amp;b",600,400)'),
			'tab'             => array("open_window(\"x\",\t600,400)"),
			'line feed'       => array("return\n1"),
			'carriage return' => array("return\r1"),
			'null byte'       => array("open_window(\"a\0b\",600,400)"),
		);
	}

	/**
	 * @dataProvider scripts
	 * @param string $script
	 */
	public function testNothingInTheScriptCanEndOrChangeTheAttribute($script)
	{
		$url = $this->tp->toJsUrl($script);

		self::assertSame(0, preg_match('/[\'&\t\n\r\x00]/', $url), 'toJsUrl() left a character the attribute or the address would change: '.$url);
	}

	/**
	 * @dataProvider scripts
	 * @param string $script
	 */
	public function testTheBrowserGetsTheScriptBack($script)
	{
		self::assertSame($script, self::scriptOf($this->tp->toJsUrl($script)));
	}
}
