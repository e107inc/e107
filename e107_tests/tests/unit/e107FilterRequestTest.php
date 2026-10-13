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
 * Covers e107::filter_request(), which prepare_request() runs over every
 * $_POST value and which answers a suspicious one with a bare HTTP 400.
 *
 * A refusal calls exit(), so only values that must be let through can be
 * asserted here: reaching the assertion at all is the proof that none was refused.
 */
class e107FilterRequestTest extends \Test\Unit
{
	/**
	 * The language manager posts translations of strings that name the tools
	 * the Cron setup page tells the admin to use, such as
	 * LAN_CRON_SETUP_WGET_LABEL ("With wget instead of curl").
	 *
	 * @dataProvider proseProvider
	 */
	public function testPostedProseMentioningDownloadToolsIsNotRefused($text)
	{
		$this->assertNull(e107::filter_request($text, 'newlang', '_POST'));
	}

	public function proseProvider()
	{
		return array(
			array('With wget instead of curl'),
			array('Com wget em vez de curl'),
			array('Con wget en lugar de curl'),
			array('Use curl or wget, whichever your host provides.'),
		);
	}
}
