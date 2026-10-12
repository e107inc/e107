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
class nextprev_shortcodeTest extends \Test\Unit
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
}
