<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The page a front-page rule sends someone to after signing in keeps the slashes in its path when e_HTTP is '/' (#6894).
 */
class userloginFrontpageForceTest extends \Test\Unit
{
	public function testThePostLoginPageOfAFrontPageRuleKeepsTheSlashesInItsPath()
	{
		$php = "require_once(".var_export(__DIR__.'/fixtures/FrontControllerFixture.php', true)."); ";
		$php .= "e107::setRegistry('core/e107/singleton/redirection', new FrontRedirectionFixture()); ";
		$php .= "e107::getConfig()->set('frontpage_force', array(e_UC_PUBLIC => '{e_PLUGIN}_blank/_blank.php')); ";
		$php .= 'echo "\nSITEURL=", json_encode(SITEURL), "\n"; ';
		$php .= '(new userlogin())->login('.var_export(\Helper\AdminLogin::ADMIN_USER, true).', '.var_export(\Helper\AdminLogin::ADMIN_PASS, true).', 0); ';

		list($output) = $this->runInBootedCli($php, self::BUFFERED, array());

		$siteUrl = self::reportedAfter($output, 'SITEURL=', 'the child signing in');
		self::assertSame($siteUrl.'e107_plugins/_blank/_blank.php', self::reportedAfter($output, 'REDIRECT=', 'the child signing in'), 'the address a sign-in is sent to');
	}
}
