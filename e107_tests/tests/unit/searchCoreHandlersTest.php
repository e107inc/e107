<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers the comments search handler, the one search handler core registers itself.
 *
 * @see https://github.com/e107inc/e107/issues/6422
 */
class searchCoreHandlersTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;
	use \Helper\SearchPage;

	public function testCommentsSearchIsOfferedWithNoPluginSearchHandler()
	{
		$html = $this->renderSearchPage(array(),
			"e107::getConfig()->setPref('comments_disabled', 0); " .
			"e107::getConfig('search')->removePref('plug_handlers'); " .
			"e107::getConfig('search')->setPref('core_handlers/comments/class', e_UC_PUBLIC); "
		);

		$this->assertSame(1, $this->searchPageXPath($html)->query("//input[@name='t[comments]']")->length,
			"A site with comments enabled and no plugin search handler must still offer the comments search.\n" . $html);
	}

	public function testSearchPageRendersWhenNoSearchHandlerIsUsable()
	{
		$html = $this->renderSearchPage(array('missing' => 'on'),
			"e107::getConfig()->setPref('comments_disabled', 1); " .
			"e107::getConfig('search')->removePref('plug_handlers'); "
		);

		$this->assertStringNotContainsString('Fatal error', $html,
			"The search page must render when its only search handler's plugin is gone and comments are disabled.\n" . $html);
		$this->assertSame(1, $this->searchPageXPath($html)->query("//input[@name='q']")->length,
			"The search form must still be rendered.\n" . $html);
	}

	public function testTheUpgradeRemovesTheUsersCoreHandler()
	{
		require_once(e_ADMIN . 'update_routines.php');

		update_20x_to_latest('do');
		$this->assertTrue(update_20x_to_latest('check'), 'The fixture must have nothing else pending, or the check below proves nothing.');

		e107::getConfig('search')->setPref('core_handlers/users', array('class' => '0', 'order' => '3'))->save(false, true, false);

		$this->assertFalse(update_20x_to_latest('check'), 'update_needed() reports by returning false');

		update_20x_to_latest('do');

		$handlers = e107::getConfig('search')->getPref('core_handlers');
		$this->assertArrayNotHasKey('users', $handlers);
		$this->assertArrayHasKey('comments', $handlers);
	}

	public function testFreshInstallRegistersTheCommentsCoreHandlerAlone()
	{
		$xml = e107::getXml();
		$search = $xml->e107ImportPrefs($xml->loadXMLfile(e_CORE . 'xml/default_install.xml', 'advanced'), 'search');

		$this->assertSame(array('comments'), array_keys($search['core_handlers']));
	}
}
