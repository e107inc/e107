<?php

/**
 * e107_plugins/newsfeed/newsfeed_functions.php renders a third-party feed on a
 * public page, and every value it renders is a value the feed operator chose.
 * The href sinks were the obvious ones; the text sinks beside them went through
 * $tp->toHTML($x, FALSE), whose default modifier set has 'scripts' => true
 * (e_parse_class.php:96), so toHTML re-emits a <script> block verbatim
 * (e_parse_class.php:1794) and passes any other tag through untouched.
 *
 * So this Cest names one marker per sink:
 *
 *   channel image  <a href>, <img src>, <img alt>
 *   {FEEDTITLE}    <a href> and its anchor text
 *   {FEEDLASTBUILDDATE}, {FEEDCOPYRIGHT}, {FEEDLANGUAGE}
 *   {FEEDITEMLINK} <a href> and its anchor text
 *   {FEEDITEMCREATOR}, {FEEDITEMTEXT}
 *
 * plus two properties an encoder alone does not give:
 *
 *   a javascript: link is not an href, because htmlspecialchars() does not touch
 *   one character of one;
 *
 *   a newsfeed_data row written before the patch does not render, because the
 *   channel image used to be composed into finished HTML and stored, and a feed
 *   inside its update interval is served from storage without re-encoding.
 *
 * The feed is served from this container, so nothing here reaches the network.
 * The probe stands in for newsfeed.php:51-62 and :82 (pick up the shipped
 * template, then call newsfeedInfo()) for two reasons: the fetch has to be
 * allowed to reach a private address, and the plugin's cache filename is hashed
 * over e_QUERY (cache_handler.php:100-105), so a feed primed from one URL is
 * invisible from any other. newsfeedInfo() is the production entry point that
 * both newsfeed.php:82 and newsfeed_menu.php:34 call, and every sink under test
 * is inside it.
 *
 * {FEEDDESCRIPTION} IS DELIBERATELY NOT ASSERTED ON. Unlike every value above it
 * does not arrive raw from MagpieRSS but through $tp->toDB()
 * (newsfeed_functions.php:196-205), which runs cleanHtml() and, for the ordinary
 * case of a guest page view triggering the refetch, htmlspecialchars() as well.
 * It is encoded at render with double_encode off, which covers the case where an
 * administrator's page view triggered the refetch without double-encoding the
 * ordinary one.
 */
class NewsfeedEncodingCest
{
	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\P8Fixture::PROBE_FILE, \Helper\P8Fixture::probeSource());
		$I->writeAppFile(\Helper\P8Fixture::NEWSFEED_FILE, \Helper\P8Fixture::newsfeedXml());

		// A request to a plugin nobody installed is turned away because the
		// plugin is absent, which looks exactly like a correct refusal.
		$I->havePluginInstalled('newsfeed');

		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');

	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=cleanup');
		$I->dropPluginInstall('newsfeed');
		$I->dropPluginProbe();
		$I->deleteAppFile(\Helper\P8Fixture::PROBE_FILE);
		$I->deleteAppFile(\Helper\P8Fixture::NEWSFEED_FILE);
	}

	public function theChannelLinkIsEncodedForItsAttribute(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed channel link before it lands in an href attribute');

		$this->showFeed($I);

		$I->dontSeeInSource(\Helper\P8Fixture::attrPayloadRaw('P8NFCHANLINK'));
		$I->seeInSource(\Helper\P8Fixture::attrPayloadEncoded('P8NFCHANLINK'));
	}

	public function theChannelTitleIsEncodedForElementText(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed channel title before it lands in element text');

		$this->showFeed($I);

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8NFCHANTITLE'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8NFCHANTITLE'));
	}

	public function theChannelImageAttributesAreEncoded(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed channel image href, src and alt');

		$this->showFeed($I);

		$I->dontSeeInSource(\Helper\P8Fixture::attrPayloadRaw('P8NFIMGLINK'));
		$I->seeInSource(\Helper\P8Fixture::attrPayloadEncoded('P8NFIMGLINK'));

		$I->dontSeeInSource(\Helper\P8Fixture::attrPayloadRaw('P8NFIMGSRC'));
		$I->seeInSource(\Helper\P8Fixture::attrPayloadEncoded('P8NFIMGSRC'));

		$I->dontSeeInSource(\Helper\P8Fixture::attrPayloadRaw('P8NFIMGALT'));
		$I->seeInSource(\Helper\P8Fixture::attrPayloadEncoded('P8NFIMGALT'));
	}

	/**
	 * {FEEDLASTBUILDDATE} is in both shipped templates
	 * (newsfeed_template.php:53 and newsfeed_menu_template.php:36) and
	 * simpleParse() escapes nothing (e_parse_class.php:977-983), so this element
	 * was the shortest path from a hostile feed to script on the public page.
	 */
	public function theChannelBuildDateIsEncodedForElementText(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed build date before it lands in element text');

		$this->showFeed($I);

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8NFDATE'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8NFDATE'));
	}

	public function theChannelCopyrightIsEncodedForElementText(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed copyright before it lands in element text');

		$this->showFeed($I);

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8NFCOPY'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8NFCOPY'));
	}

	/**
	 * {FEEDLANGUAGE} and {FEEDLINK} are documented template variables that no
	 * SHIPPED template places, which is why the probe places them itself the way
	 * a theme override would. Unrendered is not the same as safe: the next theme
	 * to use either of them would have inherited the hole.
	 */
	public function theChannelLanguageAndLinkAreEncodedForATemplateThatPlacesThem(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed language and link for a theme that places them');

		$this->showFeed($I);

		// The template addition rendered, so what follows is not vacuous.
		$I->seeInSource("id='p8-extra'");

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8NFLANG'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8NFLANG'));

		$I->seeInSource("<a href='".\Helper\P8Fixture::attrPayloadEncoded('P8NFCHANLINK')."'>P8 feed link</a>");
	}

	public function theItemLinkIsEncodedForItsAttribute(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed item link before it lands in an href attribute');

		$this->showFeed($I);

		$I->dontSeeInSource(\Helper\P8Fixture::attrPayloadRaw('P8NFITEMLINK'));
		$I->seeInSource(\Helper\P8Fixture::attrPayloadEncoded('P8NFITEMLINK'));
	}

	/**
	 * The anchor text beside that href, and the author beside it. Both went
	 * through toHTML(), which passes a script block through verbatim.
	 */
	public function theItemTitleAndAuthorAreEncodedForElementText(AcceptanceTester $I)
	{
		$I->wantTo('Encode a remote feed item title and author before they land in element text');

		$this->showFeed($I);

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8NFITEMTITLE'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8NFITEMTITLE'));

		$I->dontSeeInSource(\Helper\P8Fixture::textPayloadRaw('P8NFAUTHOR'));
		$I->seeInSource(\Helper\P8Fixture::textPayloadEncoded('P8NFAUTHOR'));
	}

	/**
	 * The item body. The menu layout already stripped tags out of it; the main
	 * layout did not, and handed the raw remote HTML to toHTML() with scripts
	 * enabled. Both layouts are rendered by the probe in one response.
	 */
	public function theItemDescriptionCarriesNoMarkup(AcceptanceTester $I)
	{
		$I->wantTo('Strip markup out of a remote feed item body in both layouts');

		$this->showFeed($I);

		$I->dontSeeInSource('<script>window.__p8xss');
		$I->dontSeeInSource('<img src=x onerror');

		// The body still has to be shown, or the assertions above prove nothing.
		$I->seeInSource('P8NFDESC');
	}

	/**
	 * The half an encoder does not answer. htmlspecialchars() touches no
	 * character of "javascript:alert(...)", so a fix built only out of encoders
	 * turns a zero-click XSS into a one-click one and calls it done.
	 */
	public function aJavascriptSchemeNeverReachesAnHref(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a javascript: URL from a remote feed as an href');

		$this->showFeed($I);

		$I->dontSeeInSource('javascript:alert');
		// The item itself still renders, so this is not passing on an empty page.
		$I->seeInSource('P8 newsfeed scheme item');
	}

	/**
	 * The upgraded-site case. The channel image used to be composed into
	 * finished HTML at fetch time and written to newsfeed_data and to the feed
	 * cache, so a site poisoned before the patch went on serving the stored
	 * bytes for as long as the feed stayed inside its update interval, and for
	 * ever if the remote feed went away.
	 */
	public function aFeedRowWrittenBeforeTheFixDoesNotRenderItsStoredMarkup(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to render composed markup left in newsfeed_data by an older version');

		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=newsfeedstale'
			.'&data='.urlencode(base64_encode(\Helper\P8Fixture::staleNewsfeedData())));
		$I->dontSeeInSource('P8_FAIL');
		$I->seeInSource('P8_OK newsfeedstale');

		$I->dontSeeInSource("P8NFSTALE=1' onmouseover=");

		// The stored row is still the row being rendered, so the assertion above
		// is about the image and not about the feed having been re-fetched.
		$I->seeInSource('P8 stale channel title');
		$I->seeInSource('P8 stale item');
	}

	/**
	 * Positive control. The page still has to render the feed: a benign item's
	 * title and link both survive, so a fix that stopped rendering the feed, or
	 * stripped it down to nothing, cannot pass.
	 *
	 * The ampersands are why these are exact bytes. The item anchor used to be
	 * run through str_replace('&', '&amp;') as a whole after it was composed,
	 * which encoded the href correctly by accident and double-encoded anything
	 * toHTML() had already turned into an entity. Both halves are now encoded for
	 * the context they are in, once each.
	 */
	public function aBenignFeedItemStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('Still render a benign remote feed item in full');

		$this->showFeed($I);

		$I->seeInSource('P8 newsfeed benign R&amp;D item');
		$I->seeInSource("href='https://example.com/benign?a=1&amp;b=2'");
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function showFeed(AcceptanceTester $I)
	{
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=newsfeed');
		$I->dontSeeInSource('P8_FAIL');
		$I->seeInSource('P8_OK newsfeed');
	}
}
