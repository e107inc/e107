<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Covers {@see e107::filter_request()}, which runs over every POSTed value before any page code does.
 */
class e107FilterRequestTest extends \Test\Unit
{
	const LET_THROUGH = '@@LET_THROUGH@@';

	/**
	 * @dataProvider proseProvider
	 */
	public function testPostedProseNamingADownloadToolOrHandlerIsNotRefused($text)
	{
		list($output) = $this->runInBootedCli("e107::filter_request(".var_export($text, true).", 'body', '_POST'); echo '".self::LET_THROUGH."';");
		$printed = implode("\n", $output);

		self::assertStringContainsString(self::LET_THROUGH, $printed,
			"the child ended before the text was let through, so the filter refused it or the boot failed:\n".$printed);
	}

	public function proseProvider()
	{
		return array(
			'wget in a translation'                => array('Com wget em vez de curl'),
			'curl -o in download instructions'     => array('Download it with curl -o e107.zip followed by the address.'),
			'lwp-download in an install note'      => array('Install lwp-download from libwww-perl.'),
			'onmouseover in a JavaScript tutorial' => array('Set onmouseover on the link to show the tooltip.'),
		);
	}
}
