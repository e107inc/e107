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
 * e_help.php reads e_PAGE, which a process defines once, so each page renders in a child process of its own.
 */
class alt_auth_e_helpTest extends \Test\Unit
{
	/**
	 * @return array<string, array{string}>
	 */
	public function fieldMappingPages()
	{
		return array(
			'LDAP'           => array('ldap_conf.php'),
			'other database' => array('otherdb_conf.php'),
		);
	}

	/**
	 * @dataProvider fieldMappingPages
	 * @param string $page
	 */
	public function testHelpExplainsCopyingAndConvertingFields($page)
	{
		$lan = include(e_PLUGIN.'alt_auth/languages/English/admin_alt_auth.php');

		list($output) = $this->runInBootedCliDefining(
			array('e_SINGLE_ENTRY' => true, 'e_PAGE' => $page),
			"\$ns = e107::getRender(); include(e_PLUGIN.'alt_auth/e_help.php');"
		);
		$help = implode("\n", $output);

		$this->assertStringContainsString(strtok($lan['LAN_ALT_VALIDATE_HELP'], "\n"), $help, "The help panel did not render at all:\n".$help);
		$this->assertStringContainsString(strtok($lan['LAN_ALT_COPY_HELP'], "\n"), $help);
		$this->assertStringContainsString(strtok($lan['LAN_ALT_CONVERSION_HELP'], "\n"), $help);
	}
}
