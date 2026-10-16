<?php

/**
 * What core's .e-expandit links reveal, in a browser that runs the handlers.
 *
 * Master only: release/v2.3.x has no webdriver suite.
 */
class EExpandItTargetCest
{
	public function anExpandItLinkWhoseTargetIsMarkupBuildsNothing(\WebDriverTester $I)
	{
		$this->clickLinkTo($I, 'e-expandit');
		$this->assertNothingRan($I);
	}

	public function anExpandItOnLinkWhoseTargetIsMarkupBuildsNothing(\WebDriverTester $I)
	{
		$this->clickLinkTo($I, 'e-expandit-on');
		$this->assertNothingRan($I);
	}

	public function anExpandItOffLinkWhoseTargetIsMarkupBuildsNothing(\WebDriverTester $I)
	{
		$this->clickLinkTo($I, 'e-expandit-off');
		$this->assertNothingRan($I);
	}

	public function anExpandItLinkStillRevealsTheElementItNames(\WebDriverTester $I)
	{
		$I->amOnPage('/page.php?id='.$this->havePage($I,
			'<a id="eexp" class="e-expandit" href="#eexpbox">more</a><div id="eexpbox" style="display:none">More text.</div>'));
		$I->click('#eexp');

		$I->waitForElementVisible('#eexpbox', 10);
	}

	/**
	 * Opens a page holding a link of $class whose href is markup the cleaner keeps, and clicks it where it stays.
	 */
	private function clickLinkTo(\WebDriverTester $I, $class)
	{
		$I->amOnPage('/page.php?id='.$this->havePage($I,
			'<a id="eexp" class="'.$class.'" href="&lt;img/src=\'x\'/ONERROR=window.top.__eexp=1&gt;">more</a>'));
		$I->assertSame("<img/src='x'/ONERROR=window.top.__eexp=1>",
			$I->executeJS('return document.getElementById("eexp").getAttribute("href");'), 'the page renders the href as it was written');
		$I->executeJS('document.addEventListener("click", function (event) { event.preventDefault(); });');
		$I->click('#eexp');
	}

	/**
	 * @return int the id of a public page with $text
	 */
	private function havePage(\WebDriverTester $I, $text)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title'     => 'Expand link page',
			'page_text'      => '<p>A page.</p>'.$text,
			'page_class'     => '0',
			'page_author'    => 1,
			'page_datestamp' => time(),
		));
	}

	/**
	 * Waits for an image with the payload's address to fail after the click, so the payload's own image has had its chance.
	 */
	private function assertNothingRan(\WebDriverTester $I)
	{
		$I->executeJS('window.__eexpSentinel = false; var img = new Image();'
			.' img.onerror = function () { window.__eexpSentinel = true; }; img.src = "x";');
		$I->waitForJS('return window.__eexpSentinel === true;', 10);

		$I->assertSame('SAFE', $I->executeJS('return window.__eexp === 1 ? "RAN" : "SAFE";'),
			'The link\'s target was built into an element and ran its handler.');
	}
}
