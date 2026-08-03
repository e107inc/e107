<?php

/**
 * email.php:61 takes the Referer header, strips tags and decodes it, then :252
 * writes it into a single-quoted value attribute:
 *
 *   <input type='hidden' name='referer' value='".$referrer."' />
 *
 * strip_tags() is not an encoder. It removes tags; it leaves the apostrophe
 * alone, and the apostrophe is all a payload needs to close that attribute and
 * start an event handler. urldecode() and html_entity_decode() run first, so a
 * payload can also arrive percent- or entity-encoded and be decoded into place.
 *
 * Referer is attacker-chosen for any visitor the attacker can get to follow a
 * link, so this is a reflected cross-site scripting hole on a page that ships
 * enabled for members.
 */
class EmailRefererCest
{
	/** The page has to be reached with a source in the query string. */
	const PAGE = '/email.php?referer';

	const PAYLOAD = "https://example.com/?a=P8XSSREF' autofocus onfocus='alert(1)";
	const PAYLOAD_RAW = "P8XSSREF' autofocus onfocus=";
	const PAYLOAD_ENCODED =
		'https://example.com/?a=P8XSSREF&#039; autofocus onfocus=&#039;alert(1)';

	const BENIGN = 'https://example.com/news.php?item=7';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\P8Fixture::PROBE_FILE, \Helper\P8Fixture::probeSource());
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');
		$this->loginAsMember($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteHeader('Referer');
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=cleanup');
		$I->deleteAppFile(\Helper\P8Fixture::PROBE_FILE);
	}

	public function theRefererIsEncodedForItsAttribute(AcceptanceTester $I)
	{
		$I->wantTo('Encode the Referer header before it lands in a value attribute');

		$I->haveHttpHeader('Referer', self::PAYLOAD);
		$I->amOnPage(self::PAGE);

		$I->dontSeeInSource(self::PAYLOAD_RAW);
		$I->seeInSource(self::PAYLOAD_ENCODED);
	}

	/**
	 * The same payload delivered percent-encoded, because :61 urldecodes before
	 * it renders. A fix that encoded the raw header and left the decode in place
	 * would still hand the attacker the attribute.
	 */
	public function aPercentEncodedRefererIsEncodedForItsAttribute(AcceptanceTester $I)
	{
		$I->wantTo('Encode a percent-encoded Referer, which email.php decodes before rendering');

		$I->haveHttpHeader('Referer', str_replace("'", '%27', self::PAYLOAD));
		$I->amOnPage(self::PAGE);

		$I->dontSeeInSource(self::PAYLOAD_RAW);
		$I->seeInSource(self::PAYLOAD_ENCODED);
	}

	/**
	 * Positive control. The hidden field is what carries the referring URL into
	 * the message body (:149-150), so it still has to hold the real value.
	 */
	public function anOrdinaryRefererStillReachesTheHiddenField(AcceptanceTester $I)
	{
		$I->wantTo('Still carry an ordinary Referer into the hidden field');

		$I->haveHttpHeader('Referer', self::BENIGN);
		$I->amOnPage(self::PAGE);

		$I->seeInSource("name='referer' value='".self::BENIGN."'");
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function loginAsMember(AcceptanceTester $I)
	{
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=member');
		$I->see('P8_OK member');

		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', \Helper\P8Fixture::MEMBER_NAME);
		$I->fillField('userpass', \Helper\P8Fixture::MEMBER_PASS);
		$I->click('userlogin');
	}
}
