<?php

/**
 * What the Menu Manager's .e-menumanager-option links load into the admin page around its frame, in a browser that runs the handler.
 */
class MenuManagerOptionSameOriginCest
{
	/** @var string the page-menu's text as the test found it */
	private $menuText;

	public function _before(\WebDriverTester $I)
	{
		$I->haveProbe('mmo-probe.html', '<b>elsewhere</b><script>window.top.__mmo = 1;</script>');
		$I->haveSiteTheme('bootstrap3');
		$this->menuText = $I->grabFromDatabase('e107_page', 'menu_text', array('menu_name' => 'jumbotron-menu-1'));
		$visibility = '/e107_admin/menus.php?lay=modern_business_home&amp;vis='
			.$I->grabFromDatabase('e107_menus', 'menu_id', array('menu_name' => 'login_menu')).'&amp;iframe=1';
		$I->updateInDatabase('e107_page', array('menu_text' =>
			'<a id="mmo-away" class="e-menumanager-option" href="http://attacker.example/x">away</a> '
			.'<a id="mmo-elsewhere" class="e-menumanager-option" href="/mmo-probe.html">elsewhere</a> '
			.'<a id="mmo-forward" class="e-menumanager-option" href="/e107_admin/menus.php?mode=conf&amp;id=1&amp;path=..%2Fmmo-probe.html%3Fx%3D">forward</a> '
			.'<a id="mmo-core" class="e-menumanager-option" href="'.$visibility.'">visibility</a>'),
			array('menu_name' => 'jumbotron-menu-1'));

		$I->amSignedInAs();
		$I->amOnPage('/e107_admin/menus.php?lay=modern_business_home');
		$I->startRecordingRequests();
		$I->switchToIFrame('#menu_iframe');
		$I->waitForElementVisible('#mmo-core', 20);
	}

	public function _after(\WebDriverTester $I)
	{
		$I->updateInDatabase('e107_page', array('menu_text' => $this->menuText), array('menu_name' => 'jumbotron-menu-1'));
	}

	public function aLinkToAnotherSiteRequestsNothing(\WebDriverTester $I)
	{
		$I->click('#mmo-away');
		$this->openCoreOption($I);

		$opened = $I->grabRequestsOpened();
		$I->assertStringContainsString('/e107_admin/menus.php', $opened);
		$I->assertStringNotContainsString('attacker.example', $opened, 'the link to another site was requested');
	}

	public function aPageElsewhereOnThisSiteIsNotLoaded(\WebDriverTester $I)
	{
		$I->click('#mmo-elsewhere');
		$this->openCoreOption($I);

		$I->assertStringNotContainsString('/mmo-probe.html', $I->grabRequestsOpened(), 'a page other than the Menu Manager was requested');
		$this->assertProbeDidNotRun($I);
	}

	public function aForwardFromTheMenuManagerIsNotLoaded(\WebDriverTester $I)
	{
		$I->click('#mmo-forward');
		$I->switchToIFrame();
		$I->waitForRequestsEnded(1);

		$I->assertStringContainsString('mode=conf', $I->grabRequestsOpened());
		$this->assertProbeDidNotRun($I);
	}

	/**
	 * Checks that the probe page's script, wherever it was fetched from, never ran in the admin page.
	 */
	private function assertProbeDidNotRun(\WebDriverTester $I)
	{
		$I->assertNull($I->executeJS('return window.__mmo === undefined ? null : window.__mmo;'), 'the probe page ran its script');
	}

	/**
	 * Clicks core's visibility link for the login menu, and waits in the admin page for its form to load.
	 */
	private function openCoreOption(\WebDriverTester $I)
	{
		$I->executeJS('document.getElementById("mmo-core").click();');
		$I->switchToIFrame();
		$I->waitForElement('#uiModal .modal-body #e-save-form', 10);
	}
}
