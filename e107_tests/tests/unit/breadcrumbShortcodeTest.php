<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {BREADCRUMB=name} prints the crumbs of the global array it names.
 */
class breadcrumbShortcodeTest extends \Codeception\Test\Unit
{
	public function testTheRequestCannotSupplyTheCrumbs()
	{
		$request = $_GET;
		$_GET = array('fieldlist' => 'a', 'a' => array('value' => '<b>from the request</b>'));

		try
		{
			$html = e107::getParser()->parseTemplate('{BREADCRUMB=_GET}', true);
		}
		finally
		{
			$_GET = $request;
		}

		$this->assertStringNotContainsString('<b>from the request</b>', $html);
	}

	public function testAGlobalThatIsNotACrumbArrayPrintsNothing()
	{
		$GLOBALS['breadcrumbShortcodeTestText'] = 'text';

		try
		{
			$html = e107::getParser()->parseTemplate('{BREADCRUMB=breadcrumbShortcodeTestText}', true);
		}
		finally
		{
			unset($GLOBALS['breadcrumbShortcodeTestText']);
		}

		$this->assertSame('', $html);
	}

	public function testANamedCrumbArrayStillPrints()
	{
		$GLOBALS['breadcrumbShortcodeTestCrumb'] = array('fieldlist' => 'a', 'a' => array('value' => "<a href='/'>Home</a>"));

		try
		{
			$html = e107::getParser()->parseTemplate('{BREADCRUMB=breadcrumbShortcodeTestCrumb}', true);
		}
		finally
		{
			unset($GLOBALS['breadcrumbShortcodeTestCrumb']);
		}

		$this->assertStringContainsString("<a href='/'>Home</a>", $html);
	}
}
