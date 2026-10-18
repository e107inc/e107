<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * A social page address, stored as typed on the social plugin's Pages tab, as each part of the plugin that links to it prints it.
 */
class socialPageAddressTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Test\HtmlAttributes;

	const MARK = '@@socialPageAddressTest@@';

	/** @var array what each part printed, by address */
	private static $printed = array();

	/**
	 * @return void
	 */
	protected function _before()
	{
		e107::lan('social', false, true);
	}

	/**
	 * Addresses as the Pages tab stores them: exactly as typed, nothing encoded.
	 *
	 * @return array
	 */
	public function addresses()
	{
		return array(
			'a plain address'     => array('https://x.com/e107'),
			'an ampersand'        => array('https://x.com/e107?a=1&b=2'),
			'an entity as typed'  => array('https://x.com/e107?a=1&amp;b=2'),
			'a double quote'      => array('https://x.com/e107" onmouseover="alert(1)" x="'),
			'an apostrophe'       => array("https://x.com/e107' onmouseover='alert(1)' x='"),
			'an angle bracket'    => array('https://x.com/e107<img src=x onerror=alert(1)>'),
		);
	}

	/**
	 * @dataProvider addresses
	 * @param string $address
	 * @return void
	 */
	public function testTheIconsLinkToTheAddress($address)
	{
		$html = $this->printed($address, 'icons');

		self::assertSame(array(), $this->handlerAttributes($html), $html);
		self::assertSame(array($address, $address), $this->attributesOf($html, '//a/@href'), $html);
	}

	/**
	 * @dataProvider addresses
	 * @param string $address
	 * @return void
	 */
	public function testTheTwitterShareButtonNamesTheAccount($address)
	{
		$html = $this->printed($address, 'share');

		self::assertSame(array(), $this->handlerAttributes($html), $html);
		self::assertSame(basename($address), $this->creditedAccount($html), $html);
	}

	/**
	 * '#' is the placeholder e107 shipped for a network with no account, so it names no account to credit.
	 *
	 * @return void
	 */
	public function testThePlaceholderAddressNamesNoAccountInTheShareButton()
	{
		$html = $this->printed('#', 'share');

		self::assertEmpty($this->creditedAccount($html), $html);
	}

	/**
	 * @dataProvider addresses
	 * @param string $address
	 * @return void
	 */
	public function testTheTimelineShortcodeLinksToTheAddress($address)
	{
		$html = $this->printed($address, 'timeline');

		self::assertSame(array(), $this->handlerAttributes($html), $html);
		self::assertSame(array($address), $this->attributesOf($html, '//a[@class="twitter-timeline"]/@href'), $html);
		self::assertSame(array(LAN_SOCIAL_201.'@'.basename($address)), $this->attributesOf($html, '//a[@class="twitter-timeline"]'), $html);
	}

	/**
	 * @dataProvider addresses
	 * @param string $address
	 * @return void
	 */
	public function testTheTwitterMenuNamesTheAccount($address)
	{
		$html = $this->printed($address, 'twitter_menu');
		$account = basename($address);

		self::assertSame(array(), $this->handlerAttributes($html), $html);
		self::assertSame(array('https://twitter.com/'.$account.'?ref_src=twsrc%5Etfw'), $this->attributesOf($html, '//a[@class="twitter-timeline"]/@href'), $html);
		self::assertSame(array($account), $this->attributesOf($html, '//a[@class="twitter-timeline"]/@data-screen-name'), $html);
		self::assertSame(array(LAN_SOCIAL_201.'@'.$account), $this->attributesOf($html, '//a[@class="twitter-timeline"]'), $html);
	}

	/**
	 * @dataProvider addresses
	 * @param string $address
	 * @return void
	 */
	public function testTheFacebookLikeMenuLinksToTheAddress($address)
	{
		$html = $this->printed($address, 'fb_like_menu');

		self::assertSame(array(), $this->handlerAttributes($html), $html);
		self::assertSame(array($address.'/'), $this->attributesOf($html, '//div[@class="fb-like"]/@data-href'), $html);
	}

	/**
	 * Feed address aliases, as the URL configuration page stores them, and the address e107::url() builds from each.
	 *
	 * @return array
	 */
	public function feedAliases()
	{
		return array(
			'the default alias'           => array('feed', 'feed'),
			'an alias with an ampersand'  => array('R&D-feed', 'R&amp;D-feed'),
		);
	}

	/**
	 * The feed's address comes from e107::url(), which has already encoded it, so the icon prints it as built.
	 *
	 * @dataProvider feedAliases
	 * @param string $alias
	 * @param string $built
	 * @return void
	 */
	public function testTheFeedIconLinksToTheAddressTheFeedBuilt($alias, $built)
	{
		$setup = "e107::getConfig()->setPref('plug_installed/rss_menu', '1.0')->setPref('e_url_list/rss_menu', 1)"
			."->setPref('e_url_alias/'.e_LAN.'/rss_menu/index', ".var_export($alias, true)."); ";

		$printed = $this->printedInChild(array(), $setup, array(
			'url'  => "echo e107::url('rss_menu', 'index', array('rss_url' => 'news'));",
			'icon' => "echo \$sc->sc_xurl_icons(array('type' => 'rss'));",
		));

		self::assertSame(e_HTTP.$built, $printed['url']);
		self::assertSame(1, preg_match_all('/ href="([^"]*)"/', $printed['icon'], $href), $printed['icon']);
		self::assertSame(array($printed['url']), $href[1], $printed['icon']);
		self::assertSame(array(e_HTTP.$alias), $this->attributesOf($printed['icon'], '//a/@href'), $printed['icon']);
	}

	/**
	 * @param string $html what {SOCIALSHARE} printed
	 * @return string|null the via= value X receives from the one Twitter share button
	 */
	private function creditedAccount($html)
	{
		$href = $this->attributesOf($html, '//a[contains(@class, "social-share-twitter")]/@href');

		self::assertCount(1, $href, $html);

		parse_str((string) parse_url($href[0], PHP_URL_QUERY), $query);

		return isset($query['via']) ? $query['via'] : null;
	}

	/**
	 * @param string $address
	 * @param string $part a key of {@see socialPageAddressTest::parts()}
	 * @return string what the part printed in a child that booted with $address as the Facebook and Twitter page
	 */
	private function printed($address, $part)
	{
		if(!isset(self::$printed[$address]))
		{
			self::$printed[$address] = $this->printEveryPart($address);
		}

		return self::$printed[$address][$part];
	}

	/**
	 * @return array PHP printing each part, by name
	 */
	private static function parts()
	{
		return array(
			'icons'        => "echo \$sc->sc_xurl_icons(array('type' => 'facebook,twitter'));",
			'share'        => "echo \$sc->sc_socialshare(array('providers' => 'twitter'));",
			'timeline'     => "echo \$sc->sc_twitter_timeline();",
			'twitter_menu' => "require(e_PLUGIN.'social/twitter_menu.php');",
			'fb_like_menu' => "require(e_PLUGIN.'social/fb_like_menu.php');",
		);
	}

	/**
	 * The XURL_* constants are defined while class2.php boots, so the child defines them first.
	 *
	 * @param string $address
	 * @return array what each part printed, by name
	 */
	private function printEveryPart($address)
	{
		$setup = "e107::getConfig()->set('xurl', ".var_export(array('facebook' => $address, 'twitter' => $address), true)."); ";
		$setup .= "e107::getPlugConfig('social')->set('sharing_mode', 'normal'); ";

		return $this->printedInChild(array(
			'XURL_FACEBOOK'        => $address,
			'XURL_TWITTER'         => $address,
			'SOCIAL_FACEBOOK_INIT' => '/* socialPageAddressTest */',
		), $setup, self::parts());
	}

	/**
	 * @param array  $constants name => value, defined before class2.php boots
	 * @param string $setup PHP run once e107 has booted, before the social shortcodes load
	 * @param array  $parts PHP printing each part, by name, with the shortcodes in $sc
	 * @return array what each part printed, by name
	 */
	private function printedInChild(array $constants, $setup, array $parts)
	{
		$php = $setup."require_once(e_PLUGIN.'social/e_shortcode.php'); \$sc = new social_shortcodes(); ";

		foreach($parts as $code)
		{
			$php .= "echo '".self::MARK."'; ".$code." ";
		}

		$php .= "echo '".self::MARK."'; ";

		list($output) = $this->runInBootedCliDefining($constants, $php);

		$printed = explode(self::MARK, implode("\n", $output));

		self::assertCount(count($parts) + 2, $printed, "the child did not print every part:\n".implode("\n", $output));

		return array_combine(array_keys($parts), array_slice($printed, 1, count($parts)));
	}
}
