<?php

/**
 * What core's .e-modal links, and the Menu Manager's own, load into the modal, in a browser that runs the handlers.
 *
 * Master only: release/v2.3.x has no webdriver suite.
 */
class EModalTargetCest
{
	/** A link target that is a script address once its entity is decoded a second time, as a page's HTML holds it. */
	const ENTITY_TARGET_IN_HTML = 'javascript&amp;colon;window.top.__emodal=1';

	/** The same target as the browser reads it from the link, which is what the frame has to be given. */
	const ENTITY_TARGET = 'javascript&colon;window.top.__emodal=1';

	public function aTargetSpelledWithAnEntityIsLoadedAsWritten(\WebDriverTester $I)
	{
		$I->amOnPage('/page.php?id='.$this->havePageWithModalLink($I, self::ENTITY_TARGET_IN_HTML));
		$I->click('#emodal');

		$this->assertFrameHoldsTheTargetAsWritten($I);
	}

	public function aScriptTargetIsNotOpened(\WebDriverTester $I)
	{
		$I->amOnPage('/news.php');
		$this->addModalLink($I, 'javascript:window.top.__emodal=1');
		$I->click('#emodal');

		$this->assertNothingRan($I);
		$I->dontSeeElement('#e-modal-iframe');
	}

	public function aPageOnTheSiteStillOpensInTheModal(\WebDriverTester $I)
	{
		$I->amOnPage('/page.php?id='.$this->havePageWithModalLink($I, '/news.php'));
		$I->click('#emodal');

		$I->waitForElementVisible('#uiModal #e-modal-iframe[src="/news.php"]', 10);
	}

	public function aTargetSpelledWithAnEntityIsLoadedAsWrittenInTheAdminArea(\WebDriverTester $I)
	{
		$I->amSignedInAs();
		$I->amOnPage('/e107_admin/admin.php');
		$this->addModalLink($I, self::ENTITY_TARGET_IN_HTML);
		$I->click('#emodal');

		$this->assertFrameHoldsTheTargetAsWritten($I);
	}

	public function aMenuManagerTargetSpelledWithAnEntityIsLoadedAsWritten(\WebDriverTester $I)
	{
		$this->openMenuManagerOnMenuLink($I, self::ENTITY_TARGET_IN_HTML, '');
		$I->click('#emodal');
		$I->switchToIFrame();

		$this->assertFrameHoldsTheTargetAsWritten($I);
	}

	/**
	 * A caption as a page can hold it: toHTML() leaves ontoggle alone, where it would break up onerror.
	 */
	public function aMenuManagerCaptionIsShownAsText(\WebDriverTester $I)
	{
		$this->openMenuManagerOnMenuLink($I, '/news.php', '&lt;details open ontoggle=window.top.__emodal=1&gt;&lt;/details&gt;');
		$I->click('#emodal');
		$I->switchToIFrame();

		$I->waitForElement('#uiModal #e-modal-iframe[src="/news.php"]', 10);
		$I->assertStringStartsWith('<details open ontoggle=window.top.__emodal=1></details>',
			$I->executeJS('return document.querySelector("#uiModal .modal-caption").textContent;'));
		$this->assertNothingRan($I);
	}

	/**
	 * Opens the Menu Manager on a layout that renders a page-menu itself, and moves into its frame.
	 *
	 * bootstrap3's modern_business_home layout holds {CMENU=jumbotron-menu-1}, so the test site's page-menu of that name
	 * is shown in the frame, here with an .e-modal-menumanager link to $href in it.
	 */
	private function openMenuManagerOnMenuLink(\WebDriverTester $I, $href, $caption)
	{
		$I->haveSiteTheme('bootstrap3');
		$I->updateInDatabase('e107_page',
			array('menu_text' => '<a id="emodal" class="e-modal-menumanager" href="'.$href.'" data-modal-caption="'.$caption.'">open</a>'),
			array('menu_name' => 'jumbotron-menu-1'));

		$I->amSignedInAs();
		$I->amOnPage('/e107_admin/menus.php?lay=modern_business_home');
		$I->switchToIFrame('#menu_iframe');
		$I->waitForElementVisible('#emodal', 20);
	}

	/**
	 * @return int the id of a public page whose text holds an .e-modal link to $href, written as the page stores it
	 */
	private function havePageWithModalLink(\WebDriverTester $I, $href)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Modal link page',
			'page_text'      => '<p>A page.</p><a id="emodal" class="e-modal" href="'.$href.'">open</a>',
			'page_class'     => '0',
			'page_author'    => 1,
			'page_datestamp' => time(),
		));
	}

	/**
	 * Adds an .e-modal link to the open page and attaches core's behaviours to it, for a target no page would keep.
	 */
	private function addModalLink(\WebDriverTester $I, $href)
	{
		$link = '<a id="emodal" class="e-modal" href="'.$href.'" style="position:fixed;top:0;left:0;z-index:99999">open</a>';
		$I->executeJS('document.body.insertAdjacentHTML("afterbegin", '.json_encode($link).'); e107.attachBehaviors(document.body);');
	}

	/**
	 * Waits for the modal's frame, and checks it was given the link's target as the browser read it and that nothing ran.
	 */
	private function assertFrameHoldsTheTargetAsWritten(\WebDriverTester $I)
	{
		$I->waitForElement('#uiModal #e-modal-iframe', 10);
		$I->assertSame(self::ENTITY_TARGET, $I->executeJS('return document.getElementById("e-modal-iframe").getAttribute("src");'));
		$this->assertNothingRan($I);
	}

	/**
	 * Waits for a javascript: frame inserted after the click to run, so a script target the click loaded has had its chance.
	 */
	private function assertNothingRan(\WebDriverTester $I)
	{
		$I->executeJS('document.body.insertAdjacentHTML("beforeend",'
			.' \'<iframe id="emodal-sentinel" src="javascript:window.top.__emodalSentinel=1"></iframe>\');');
		$I->waitForJS('return window.__emodalSentinel === 1;', 10);

		$I->assertSame('SAFE', $I->executeJS('return window.__emodal === 1 ? "RAN" : "SAFE";'),
			'The link\'s target ran as script in the site\'s origin.');
	}
}
