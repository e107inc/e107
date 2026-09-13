<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Core ships English only and every other language is a separate download, so
 * upgrading core never updates a pack: a site upgraded in place runs a pack
 * that predates the phrases core has added since, and nothing fills the gap,
 * because e107::includeLan() substitutes English for a whole file it cannot
 * read rather than for a phrase that is missing from one it can.
 *
 * e107_admin/boot.php builds the admin navigation before any authentication,
 * so from PHP 8 the first phrase the pack does not carry is a fatal that takes
 * the whole admin area down at the login page, for everybody.
 *
 * A constant cannot be undefined once the process has defined it, so this runs
 * in a booted CLI subprocess, where admin/lan_admin.php is not loaded:
 * class2.php includes it only inside the admin area.
 */
class sitelinksAdminLinksGuardedTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	const BEGIN = '@@e107help-titles-begin@@';
	const END   = '@@e107help-titles-end@@';

	/**
	 * @param string $out everything the subprocess wrote
	 * @return string the lines that killed it, or the head of the output when nothing did
	 */
	private static function diagnosis($out)
	{
		$lines = explode("\n", $out);
		$fatal = array();

		foreach ($lines as $line)
		{
			if (preg_match('/^(PHP )?(Fatal error|Warning|Parse error):/', $line))
			{
				$fatal[] = trim($line);
			}
		}

		return implode("\n", $fatal ? $fatal : array_slice($lines, 0, 20));
	}

	public function testTheAdminNavigationRendersWithoutTheAdminLanguageFile()
	{
		$php = "error_reporting(E_ALL); \$rows = e107::getNav()->adminLinks('legacy'); "
			."echo '".self::BEGIN."', implode('|', array(\$rows[0][1], \$rows[0][2], \$rows[12][1])), '".self::END."';";

		list($output, $status) = $this->runInBootedCli($php);

		$printed = implode("\n", $output);

		self::assertSame(0, $status, "the admin navigation never returned:\n".self::diagnosis($printed));
		self::assertSame(0, preg_match('/undefined constant/i', $printed),
			"a phrase the language pack does not carry must not be read bare:\n".self::diagnosis($printed));

		$matches = array();

		self::assertSame(1, preg_match('/'.self::BEGIN.'(.*)'.self::END.'/s', $printed, $matches),
			"the navigation printed no titles:\n".self::diagnosis($printed));

		$titles = explode('|', $matches[1]);

		self::assertSame('Untitled', $titles[0],
			'ADLAN_8 heads the table, so an unguarded read dies on it first');
		self::assertSame('No description', $titles[1],
			'a missing description falls back too, rather than to the constant name');
		self::assertSame('Untitled', $titles[2],
			'LAN_NAVIGATION arrived with v2.3 and is the phrase the field report died on');
	}
}
