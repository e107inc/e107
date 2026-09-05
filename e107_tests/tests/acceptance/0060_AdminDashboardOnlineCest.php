<?php

/**
 * e107_plugins/user/e_dashboard.php:456-457 interpolates two visitor-controlled
 * strings straight into single-quoted HTML attributes on the admin dashboard:
 *
 *   <a class='e-tip' href='$val[user_location]' title='$val[user_location]'>
 *   <a class='e-tip' href='#' title='$val[user_agent]'>
 *
 * user_agent is the User-Agent header of any visitor at all, stored by
 * e107_handlers/online_class.php:123 on the insert that records them as online.
 * Online tracking is on by default (track_online), the user plugin is installed
 * by default, and its dashboard panel is rendered inline on every admin
 * dashboard. So the attacker needs no account and the victim is an
 * administrator.
 *
 * ASSERTING ENCODED BYTES, NOT ABSENT TAGS. A payload that never contained a
 * tag would satisfy dontSeeInSource('<script>') while still closing the
 * attribute it lands in. These tests assert the exact bytes the sink has to
 * emit, and for the attribute sinks that means asserting the quote character
 * itself is encoded.
 *
 * @see WebDriver AdminDashboardOnlineXssCest for the same payload in a browser
 */
class AdminDashboardOnlineCest
{
	const DASHBOARD = '/e107_admin/admin.php';
	const BENIGN_AGENT = 'Mozilla/5.0 (P8Benign; R&D) Gecko/20100101';
	const BENIGN_LOCATION = '/index.php?p8=benign&x=1';

	/** Exactly what the two sinks have to emit for {@see BENIGN_LOCATION}. */
	const BENIGN_LOCATION_ENCODED = '/index.php?p8=benign&amp;x=1';

	/** And for {@see BENIGN_AGENT}. */
	const BENIGN_AGENT_ENCODED = 'Mozilla/5.0 (P8Benign; R&amp;D) Gecko/20100101';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\P8Fixture::PROBE_FILE, \Helper\P8Fixture::probeSource());
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');

		$I->resetFloodProtection();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=cleanup');
		$I->deleteAppFile(\Helper\P8Fixture::PROBE_FILE);
	}

	/**
	 * Reachability, not the defect. Proves the attacker needs nothing at all:
	 * a single cookieless GET puts an arbitrary string in the row an
	 * administrator is later shown.
	 *
	 * Expected to pass before and after the fix. Encoding belongs at the sink,
	 * so the stored bytes stay the bytes the visitor sent.
	 */
	public function anUnauthenticatedVisitorControlsTheStoredUserAgent(AcceptanceTester $I)
	{
		$I->wantTo('Store an unauthenticated visitor User-Agent verbatim in the online table');

		$I->resetAllCookies();
		$I->haveHttpHeader('User-Agent', \Helper\P8Fixture::ATTR_PAYLOAD);
		$I->amOnPage('/index.php');
		$I->deleteHeader('User-Agent');

		$I->seeInDatabase('e107_online', array(
			'online_user_id' => '0',
			'online_agent'   => \Helper\P8Fixture::ATTR_PAYLOAD,
		));
	}

	/**
	 * The defect. The stored User-Agent reaches a title attribute unencoded.
	 */
	public function theOnlinePanelEncodesTheUserAgentAttribute(AcceptanceTester $I)
	{
		$I->wantTo('Encode a visitor User-Agent before it lands in a title attribute');

		$this->seedOnlineRow($I, \Helper\P8Fixture::ATTR_PAYLOAD, self::BENIGN_LOCATION);
		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);

		// The quote has to be gone as a quote, not merely followed by no tag.
		$I->dontSeeInSource("P8XSSA' onmouseover=");
		$I->seeInSource(\Helper\P8Fixture::ATTR_PAYLOAD_ENCODED);
	}

	/**
	 * The same row's location reaches both an href and a title on :456.
	 *
	 * HARDENING, NOT A LIVE VECTOR, and the difference matters for the advisory.
	 * online_class.php:115 stores e_REQUEST_URI, and e107_class.php:5896 defines
	 * that constant as str_replace(array("'", '"'), array('%27', '%22'), ...), so
	 * the two quote characters are already gone before FILTER_SANITIZE_URL sees
	 * the value. The angle brackets do survive, but neither of them can terminate
	 * a quoted attribute on its own. So the row is seeded through the probe here
	 * rather than by a real request: no real request can produce it. Only the
	 * User-Agent is attacker-controlled end to end, and
	 * {@see anUnauthenticatedVisitorControlsTheStoredUserAgent} proves that one
	 * with a cookieless GET.
	 */
	public function theOnlinePanelEncodesTheVisitorLocationAttributes(AcceptanceTester $I)
	{
		$I->wantTo('Encode a visitor location before it lands in href and title attributes');

		$this->seedOnlineRow($I, self::BENIGN_AGENT, '/index.php?a='.\Helper\P8Fixture::BREAKOUT_PAYLOAD);
		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);

		$I->dontSeeInSource("P8XSSB'><img");
		$I->seeInSource(\Helper\P8Fixture::BREAKOUT_PAYLOAD_ENCODED);
	}

	/**
	 * Positive control. A refusal to render the panel at all, or a blanket
	 * strip_tags of everything in it, would pass both tests above and destroy
	 * the feature. The panel still has to show who is online, where they are
	 * and what they are using.
	 *
	 * Both benign values carry an ampersand on purpose. An encoder has to emit
	 * &amp; for it exactly once: html_truncate() is entity-aware
	 * (e_parse_class.php:1304-1320), so its output is not raw text, and encoding
	 * that again would produce &amp;amp; in the anchor text.
	 */
	public function theOnlinePanelStillShowsBenignVisitors(AcceptanceTester $I)
	{
		$I->wantTo('Still render the online panel for a visitor with nothing to encode');

		$this->seedOnlineRow($I, self::BENIGN_AGENT, self::BENIGN_LOCATION);
		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);

		$I->seeInSource(self::BENIGN_AGENT_ENCODED);
		$I->seeInSource(self::BENIGN_LOCATION_ENCODED);
		// The anchor text, encoded once and not twice.
		$I->seeInSource('>index.php?p8=benign&amp;x=1</a>');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $agent
	 * @param string $location
	 * @return void
	 */
	private function seedOnlineRow(AcceptanceTester $I, $agent, $location)
	{
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=online'
			.'&agent='.urlencode(base64_encode($agent))
			.'&loc='.urlencode(base64_encode($location)));
		$I->see('P8_OK online');
	}
}
