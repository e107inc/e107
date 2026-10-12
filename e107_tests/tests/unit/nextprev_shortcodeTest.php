<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {NEXTPREV} parameters written into text a member wrote, which the BODY and DESCRIPTION contexts parse for shortcodes.
 */
class nextprev_shortcodeTest extends \Codeception\Test\Unit
{
	public function testAPageTitleDoesNotPrintAConstantThatIsNotALanguageTerm()
	{
		self::assertNotSame('', e_ROOT);

		$html = e107::getParser()->parseTemplate('{NEXTPREV:total=2&amount=1&current=0&type=page&tmpl=admin&url=x&pagetitle=e_ROOT|e_SYSTEM}', true);

		self::assertStringNotContainsString(e_ROOT, $html, 'A page title printed the server path: '.$html);
		self::assertStringNotContainsString(e_SYSTEM, $html, 'A page title printed the system folder: '.$html);
		self::assertStringContainsString('e_SYSTEM', $html, 'A page title that is not a language term should print as written: '.$html);
	}

	public function testAPageTitleStillPrintsALanguageTerm()
	{
		$html = e107::getParser()->parseTemplate('{NEXTPREV:total=2&amount=1&current=0&type=page&tmpl=admin&url=x&pagetitle=LAN_EDIT|Plain}', true);

		self::assertStringContainsString(LAN_EDIT, $html);
		self::assertStringContainsString('Plain', $html);
	}

	/**
	 * @return array
	 */
	public function countsWithoutAnAmount()
	{
		return array(
			'keyed'  => array('{NEXTPREV=total=2&current=0}'),
			'legacy' => array('{NEXTPREV=2,0,0}'),
		);
	}

	/**
	 * @dataProvider countsWithoutAnAmount
	 * @param string $shortcode
	 */
	public function testARecordCountWithoutAnAmountPrintsNothing($shortcode)
	{
		self::assertSame('', e107::getParser()->parseTemplate($shortcode, true));
	}

	/**
	 * @return array
	 */
	public function hostileUrls()
	{
		return array(
			'quote breaking out of href' => array('x%22%20autofocus%20onfocus%3Dalert(1)%20x%3D%22', '" autofocus'),
			'script scheme'              => array('javascript%3Aalert(1)', 'href="javascript:'),
			'assembler, unknown module'  => array('url::x%22%20autofocus%20onfocus%3Dalert(1)%20x%3D%22/y/z', '" autofocus'),
			'route, unknown module'      => array('route::x%22%20autofocus%20onfocus%3Dalert(1)%20x%3D%22/y/z::', '" autofocus'),
		);
	}

	/**
	 * @dataProvider hostileUrls
	 * @param string $url
	 * @param string $printed
	 */
	public function testALinkAddressCannotAddMarkupOrRunScript($url, $printed)
	{
		$html = e107::getParser()->parseTemplate('{NEXTPREV:total=2&amount=1&current=1&type=page&tmpl=admin&url='.$url.'}', true);

		self::assertStringContainsString('nextprev-item', $html, 'The pagination did not render: '.$html);
		self::assertStringNotContainsString($printed, $html, 'A link address escaped its href or kept a script scheme: '.$html);
	}

	public function testALinkAddressKeepsItsQueryString()
	{
		$html = e107::getParser()->parseTemplate('{NEXTPREV:total=2&amount=1&current=1&type=page&tmpl=admin&url=page.php%3Fa%3D1--AMP--b%3D%5BFROM%5D}', true);

		self::assertStringContainsString('href="page.php?a=1&amp;b=2"', $html);
	}

	/**
	 * @return array
	 */
	public function hostileLegacyUrls()
	{
		return array(
			'quote breaking out of href' => array("x' autofocus onfocus=alert(1) x='", "' autofocus"),
			'script scheme'              => array('javascript:alert(1)', 'javascript:'),
			'assembler, unknown module'  => array("url::x' autofocus onfocus=alert(1) x='/y/z::", "' autofocus"),
		);
	}

	/**
	 * @dataProvider hostileLegacyUrls
	 * @param string $url
	 * @param string $printed
	 */
	public function testALegacyLinkAddressCannotAddMarkupOrRunScript($url, $printed)
	{
		$html = e107::getParser()->parseTemplate('{NEXTPREV=2,1,0,'.$url.'}', true);

		self::assertNotSame('', $html, 'The pagination did not render');
		self::assertStringNotContainsString($printed, $html, 'A legacy link address escaped its attribute or kept a script scheme: '.$html);
	}

	public function testALinkAddressWithAByteThatIsNotUtf8StillLinks()
	{
		$html = e107::getParser()->parseTemplate('{NEXTPREV:total=2&amount=1&current=1&type=page&tmpl=admin&url=search.php%3Fq%3Dcaf%E9--AMP--r%3D%5BFROM%5D}', true);

		self::assertStringContainsString('href="search.php?q=caf%E9&amp;r=2"', $html);
	}

	public function testALegacyAssemblerAddressStillLinks()
	{
		$html = e107::getParser()->parseTemplate('{NEXTPREV=30,10,0,url::news/list/items::page=[FROM]}', true);

		self::assertSame(0, preg_match("/(href|value)=''/", $html), 'A legacy url:: address printed an empty link: '.$html);
		self::assertStringContainsString('news.php', $html);
	}

	public function testAnOldStyleLegacyLinkAddressCannotAddMarkup()
	{
		$config = e107::getConfig();
		$before = $config->get('old_np');
		$config->set('old_np', 1);

		try
		{
			$html = e107::getParser()->parseTemplate("{NEXTPREV=2,1,0,url::news/list/items::page=[FROM]' autofocus onfocus=alert(1) x='}", true);
		}
		finally
		{
			$config->set('old_np', $before);
		}

		self::assertStringNotContainsString("' autofocus", $html, 'An old-style link address escaped its attribute: '.$html);
	}
}
