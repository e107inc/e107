<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A link set to open in a 600x400 or 800x600 window is rendered as an open_window() call, by {NAV_LINK_OPEN} as an
 * onclick handler and by {@see sitelinks::makeLink()} and {SITELINKS_ALT} as a javascript: address.
 */
class sitelinksPopupTest extends \Test\Unit
{
	use \Test\HtmlAttributes;
	use \Test\JavascriptAddresses;
	use \Test\SitelinkRows;

	/** One open_window() call, given one JavaScript string literal in either quote and a size, and nothing else. */
	const CALL = '/^open_window\((?|"((?:[^"\\\\]|\\\\.)*)"|\'((?:[^\'\\\\]|\\\\.)*)\'),(600,400|800,600)\)$/s';

	/**
	 * @return void
	 */
	protected function _before()
	{
		require_once(e_CORE.'shortcodes/batch/navigation_shortcodes.php');
		require_once(e_CORE.'shortcodes/single/sitelinks_alt.php');
	}

	/**
	 * Addresses as the links table stores them, toDB() having written quotes as entities, and as a browser reads them.
	 *
	 * @return array
	 */
	public function addresses()
	{
		return array(
			'a path'                      => array('news.php', 'news.php'),
			'an apostrophe'               => array('x&#039;+(top.__probe=4)+&#039;', "x'+(top.__probe=4)+'"),
			'a double quote'              => array('x&quot;+(top.__probe=4)+&quot;', 'x"+(top.__probe=4)+"'),
			'percent-encoded quotes'      => array('x%22+(top.__probe=4)+%22%27', 'x%22+(top.__probe=4)+%22%27'),
			'an encoded ampersand'        => array('page.php?a=1&amp;b=2', 'page.php?a=1&b=2'),
			'a percent-encoded ampersand' => array('search.php?q=a%26b', 'search.php?q=a%26b'),
			'a closing tag'               => array('x&lt;/a&gt;&lt;b&gt;', 'x</a><b>'),
		);
	}

	/**
	 * @dataProvider addresses
	 * @param string $stored
	 * @return void
	 */
	public function testNavLinkOpenOpensTheAddressNavLinkUrlPrints($stored)
	{
		foreach(array(4 => '600,400', 5 => '800,600') as $mode => $size)
		{
			$sc = new navigation_shortcodes();
			$sc->setVars(self::linkRow(array('link_url' => $stored, 'link_open' => $mode)));

			$markup = '<a href="'.$sc->sc_nav_link_url().'"'.$sc->sc_nav_link_open().'>x</a>';
			$href = $this->attributesOf($markup, '//a/@href');
			$handler = $this->attributesOf($markup, '//a/@onclick');
			$suffix = '; return false;';

			self::assertCount(1, $handler, 'a popup link has an onclick handler: '.$markup);
			self::assertSame($suffix, substr($handler[0], -strlen($suffix)), 'the handler must end by cancelling the click: '.$markup);
			$this->assertOpens((string) substr($handler[0], 0, -strlen($suffix)), $href[0], $size);
		}
	}

	/**
	 * @dataProvider addresses
	 * @param string $stored
	 * @param string $address
	 * @return void
	 */
	public function testMakeLinkPassesTheAddressAsOneString($stored, $address)
	{
		$markup = self::makeLink(self::linkRow(array('link_name' => 'Popup', 'link_url' => $stored, 'link_open' => 4)));

		$this->assertOpens($this->scriptRunFor($markup), e_HTTP.$address, '600,400');
	}

	/**
	 * @dataProvider addresses
	 * @param string $stored
	 * @param string $address
	 * @return void
	 */
	public function testSitelinksAltPassesTheAddressAsOneString($stored, $address)
	{
		$this->assertOpens($this->scriptRunFor(sitelinks_alt::adnav_cat('Popup', $stored, 'no_icons', false, 5)), $address, '800,600');
		$this->assertOpens($this->scriptRunFor(sitelinks_alt::adnav_main('Popup', $stored, 'no_icons', false, array(), 4)), $address, '600,400');
	}

	/**
	 * @param string $script what the browser runs
	 * @param string $address the address the window must open
	 * @param string $size
	 * @return void
	 */
	private function assertOpens($script, $address, $size)
	{
		self::assertSame(1, preg_match(self::CALL, $script, $match), 'the address must reach open_window() as one string: '.$script);
		self::assertSame($address, self::stringValue($match[1]), 'open_window() must be given the address: '.$script);
		self::assertSame($size, $match[2]);
	}

	/**
	 * @param string $literal the inside of a JavaScript string literal, without its quotes
	 * @return string|null the string it stands for, or null for an escape this test does not read
	 */
	private static function stringValue($literal)
	{
		return json_decode('"'.preg_replace(array("/\\\\'/", '/(?<!\\\\)"/'), array("'", '\\"'), $literal).'"');
	}

	/**
	 * @param string $markup
	 * @return string the script a browser runs for the first link's javascript: address, which it percent-decodes first
	 */
	private function scriptRunFor($markup)
	{
		$href = $this->attributesOf($markup, '(//a)[1]/@href');

		self::assertCount(1, $href, "no link was rendered:\n".$markup);

		return self::scriptOf($href[0]);
	}
}
