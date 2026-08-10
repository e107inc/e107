<?php

/**
 * The same sink as acceptance's AdminDashboardOnlineCest, asked of a real
 * browser: e107_plugins/user/e_dashboard.php:457 puts a visitor's User-Agent
 * into a single-quoted title attribute on the admin dashboard.
 *
 * A source assertion can only say the encoded bytes are there. This says the
 * payload does not run, which is the property that actually matters and the
 * one no amount of dontSeeInSource('<script>') establishes.
 *
 * Master only: release/v2.3.x has no webdriver suite.
 *
 * @see \Helper\OutputEncodingFixture
 */
class AdminDashboardOnlineXssCest
{
	const DASHBOARD = '/e107_admin/admin.php';
	const BENIGN_AGENT = 'Mozilla/5.0 (EncBenign) Gecko/20100101';
	const BENIGN_LOCATION = '/index.php?p8=benign';

	public function _before(\WebDriverTester $I)
	{
		$I->writeAppFile(\Helper\OutputEncodingFixture::PROBE_FILE, \Helper\OutputEncodingFixture::probeSource());
	}

	public function _after(\WebDriverTester $I)
	{
		$I->deleteAppFile(\Helper\OutputEncodingFixture::PROBE_FILE);
	}

	/**
	 * The payload closes the title attribute and the anchor, then opens an
	 * <img> whose onerror is guaranteed to fire. If the dashboard does not
	 * encode, the browser runs it.
	 */
	public function aVisitorUserAgentDoesNotExecuteOnTheDashboard(\WebDriverTester $I)
	{
		$I->wantTo('Load the admin dashboard with a hostile visitor User-Agent and see nothing run');

		$this->seedOnlineRow($I, \Helper\OutputEncodingFixture::BREAKOUT_PAYLOAD, self::BENIGN_LOCATION);

		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);
		$I->waitForElement('body', 10);

		$this->assertPayloadDidNotRun($I);
	}

	/**
	 * The location column of the same row feeds an href and a title on :456.
	 */
	public function aVisitorLocationDoesNotExecuteOnTheDashboard(\WebDriverTester $I)
	{
		$I->wantTo('Load the admin dashboard with a hostile visitor location and see nothing run');

		$this->seedOnlineRow($I, self::BENIGN_AGENT,
			'/index.php?a='.\Helper\OutputEncodingFixture::BREAKOUT_PAYLOAD);

		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);
		$I->waitForElement('body', 10);

		$this->assertPayloadDidNotRun($I);
	}

	/**
	 * Two controls in one, because "nothing ran" is worthless on its own.
	 *
	 * First: an image with the same onerror, inserted into the page after it
	 * loaded, does set the flag. That proves the browser runs these handlers
	 * and that reading the flag back works, so a green result above means the
	 * payload was encoded rather than that the detector is blind.
	 *
	 * Second: the panel is still there and still shows a benign visitor, so a
	 * fix that simply stopped rendering the column cannot pass.
	 */
	public function theDetectorFiresAndThePanelStillRenders(\WebDriverTester $I)
	{
		$I->wantTo('Prove the detector fires and the online panel still shows a benign visitor');

		$this->seedOnlineRow($I, self::BENIGN_AGENT, self::BENIGN_LOCATION);

		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);
		$I->waitForElement('body', 10);

		$I->seeInSource(self::BENIGN_AGENT);
		$I->seeInSource(self::BENIGN_LOCATION);

		$I->executeJS('document.body.insertAdjacentHTML("beforeend",'
			.' \'<img src="/e107_tests_encoding_no_such_image.png" onerror="window.__p8xss=1">\');');
		$I->waitForJS('return window.__p8xss === 1;', 10);
	}

	/**
	 * @param \WebDriverTester $I
	 * @return void
	 */
	private function assertPayloadDidNotRun(\WebDriverTester $I)
	{
		// A fixed sleep fails open: an error event that has not fired within a
		// second reports SAFE. Insert a sentinel image with the same URL shape as
		// the payload's and wait for ITS error event instead, so the payload's own
		// has demonstrably had the same chance.
		$I->executeJS('window.__p8sentinel = false;'
			.' document.body.insertAdjacentHTML("beforeend",'
			.' \'<img id="p8sentinel" src="/e107_tests_encoding_no_such_image.png"'
			.' onerror="window.__p8sentinel=true">\');');
		$I->waitForJS('return window.__p8sentinel === true;', 10);

		$ran = $I->executeJS('return window.__p8xss === 1 ? "RAN" : "SAFE";');
		$I->assertSame('SAFE', $ran, 'The injected payload executed in the browser.');

		// And a DOM assertion that can actually fail. dontSeeInSource() cannot:
		// WebDriver reads the serialised live DOM, so the raw bytes are absent
		// whether the browser parsed the payload into real nodes or the page
		// never emitted it at all. Counting the nodes distinguishes the two, and
		// the sentinel above is excluded by id so it cannot mask the count.
		$handlers = $I->executeJS(
			'return document.querySelectorAll("img[onerror]:not(#p8sentinel)").length;');
		$I->assertSame(0, (int) $handlers,
			'The payload was parsed into a real element carrying an event handler.');
	}

	/**
	 * @param \WebDriverTester $I
	 * @param string $agent
	 * @param string $location
	 * @return void
	 */
	private function seedOnlineRow(\WebDriverTester $I, $agent, $location)
	{
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=online'
			.'&agent='.urlencode(base64_encode($agent))
			.'&loc='.urlencode(base64_encode($location)));
		$I->see('P8_OK online');
	}
}
