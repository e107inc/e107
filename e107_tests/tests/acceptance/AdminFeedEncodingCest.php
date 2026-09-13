<?php

/**
 * e107_admin/boot.php:124-131 renders the admin dashboard's news feed with no
 * encoding at all:
 *
 *   <a target="_blank" href="'.$row['link'].'">'.$row['title'].'</a>
 *   <small>&mdash; '.$row['pubDate'].'</small>
 *
 * Every one of those three values is whatever the remote feed said. The
 * shipped feed is e107.org's, so the trust boundary is "whoever can answer for
 * that host, or sit between it and the site": a hijacked feed, a DNS answer, or
 * a plain-HTTP intermediary all end up executing script in the control panel of
 * every e107 administrator who opens the dashboard.
 *
 * The feed is pointed at a local fixture through the seam boot.php:25 already
 * offers (its define is guarded), so nothing here reaches the network.
 *
 * The link lands in a DOUBLE-quoted attribute, so the payload for it carries a
 * double quote; the other two land in element text. Different contexts, so
 * different expected bytes, which is the whole point of asserting them.
 *
 * Fifty lines below that block, boot.php:150-205 does the same thing again for
 * ?mode=addons, which infopanel.php:101-102 loads on the same dashboard render
 * from the same remote host, and then caches the composed HTML for three hours
 * (:202) and echoes it back unencoded on every later request (:159-160). So the
 * addons cases here ask twice: the second request is served from that cache.
 */
class AdminFeedEncodingCest
{
	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\P8Fixture::PROBE_FILE, \Helper\P8Fixture::probeSource());
		$I->writeAppFile(\Helper\P8Fixture::ADMIN_FEED_FILE, \Helper\P8Fixture::adminFeedXml());
		$I->writeAppFile(\Helper\P8Fixture::ADDON_FEED_FILE, \Helper\P8Fixture::addonFeedXml());
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(\Helper\P8Fixture::PROBE_FILE);
		$I->deleteAppFile(\Helper\P8Fixture::ADMIN_FEED_FILE);
		$I->deleteAppFile(\Helper\P8Fixture::ADDON_FEED_FILE);
	}

	public function theFeedLinkIsEncodedForItsAttribute(AcceptanceTester $I)
	{
		$I->wantTo('Encode a feed item link before it lands in an href attribute');

		$this->renderFeed($I);

		$I->dontSeeInSource('P8XSSD" onmouseover=');
		$I->seeInSource(\Helper\P8Fixture::DQ_ATTR_PAYLOAD_ENCODED);
	}

	public function theFeedTitleIsEncodedForElementText(AcceptanceTester $I)
	{
		$I->wantTo('Encode a feed item title before it lands in element text');

		$this->renderFeed($I);

		$I->dontSeeInSource('P8XSSC<img');
		$I->seeInSource(\Helper\P8Fixture::TEXT_PAYLOAD_ENCODED);
	}

	public function theFeedPubDateIsEncodedForElementText(AcceptanceTester $I)
	{
		$I->wantTo('Encode a feed item date before it lands in element text');

		$this->renderFeed($I);

		$I->dontSeeInSource('P8XSSE<b onmouseover=');
		$I->seeInSource(\Helper\P8Fixture::TEXT_PAYLOAD_2_ENCODED);
	}

	/**
	 * Positive control. The panel still has to show the feed: a benign item's
	 * title, link and date all survive, so a fix that refused to render the
	 * feed, or strip_tags'd it into nothing, cannot pass.
	 */
	public function aBenignFeedItemStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('Still render a benign feed item in full');

		$this->renderFeed($I);

		$I->seeInSource('P8 benign item');
		$I->seeInSource('href="https://example.com/benign"');
		$I->seeInSource('Sun, 02 Aug 2026 00:00:00 +0000');
	}

	/**
	 * The addons panel: the same remote host, the same admin victim, the same
	 * file, fifty lines away, and none of its five interpolations encoded.
	 */
	public function theAddonsPanelEncodesEveryRemoteValue(AcceptanceTester $I)
	{
		$I->wantTo('Encode every remote value the addons panel renders');

		$this->renderAddons($I);

		$I->dontSeeInSource('P8XSSD" onmouseover=');
		$I->seeInSource(\Helper\P8Fixture::DQ_ATTR_PAYLOAD_ENCODED);

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8ADDONNAME'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8ADDONNAME'));

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8ADDONVER'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8ADDONVER'));

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8ADDONAUTH'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8ADDONAUTH'));

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8ADDONDESC'));
	}

	/**
	 * The cache is the reason encoding at composition time is not enough on its
	 * own: :202 stores the composed string and :159-160 echoes it back for three
	 * hours. A second request is served from that store.
	 */
	public function theCachedAddonsPanelIsAlsoClean(AcceptanceTester $I)
	{
		$I->wantTo('Serve the cached addons panel without the payload too');

		$this->renderAddons($I);
		$this->renderAddons($I);

		$I->dontSeeInSource('P8XSSD" onmouseover=');
		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8ADDONNAME'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8ADDONNAME'));
	}

	/**
	 * Positive control for the addons panel.
	 */
	public function aBenignAddonStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('Still render a benign addon in full');

		$this->renderAddons($I);

		$I->seeInSource('P8 benign addon');
		$I->seeInSource('src="https://example.com/benign.png"');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function renderFeed(AcceptanceTester $I)
	{
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=adminfeed');
		$I->dontSeeInSource('P8_FAIL');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function renderAddons(AcceptanceTester $I)
	{
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=addonsfeed');
		$I->dontSeeInSource('P8_FAIL');
	}
}
