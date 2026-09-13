<?php

/**
 * e107_core/controllers/system/xup.php:37 takes ?back= verbatim and :63 hands
 * it to the redirector:
 *
 *   $this->backUrl = isset($_GET['back']) ? $_GET['back'] : null;
 *   ...
 *   e107::getRedirect()->redirect(true === $this->backUrl ? SITEURL : $this->backUrl);
 *
 * Nothing between the two asks whether the destination is on this site, so
 * ?route=system/xup/login&back=<anywhere> is an open redirect on a route that
 * needs no account. An open redirect off a login route is worth more than the
 * usual, because the URL it decorates is the site's own and the page it lands
 * on can imitate the login the visitor was expecting.
 *
 * redirection_class::verifyDestination() (e107_handlers/redirection_class.php:
 * 576-622) already encodes the same-origin rule this route needs; today it is
 * only reachable from the signed login-destination flow.
 *
 * REDIRECTS ARE NOT FOLLOWED HERE. PhpBrowser chases them without a cap, so a
 * followed redirect measures the test client rather than the application, and
 * an e107 that cannot serve a request answers with a relative Location of
 * install.php, which loops. The status line and the Location header are the
 * evidence.
 */
class XupBackRedirectCest
{
	const ROUTE = '/?route=system/xup/login';
	const OFFSITE = 'https://evil.example.invalid/phish';

	/** @var string SITEURL as the application computes it */
	private $siteUrl = '';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\OutputEncodingFixture::PROBE_FILE, \Helper\OutputEncodingFixture::probeSource());
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');

		// Asked of the application rather than assumed, because a refusal is
		// measured as "bounced home" and home is whatever SITEURL says it is.
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=constants');
		$I->see('P8_OK constants');
		preg_match('#^SITEURL:(.*)$#m', $I->grabPageSource(), $match);
		$this->siteUrl = trim($match[1]);

		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->deleteAppFile(\Helper\OutputEncodingFixture::PROBE_FILE);
	}

	public function anAbsoluteOffsiteBackUrlIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to bounce a visitor to another site through the xup back parameter');

		$I->amOnPage(self::ROUTE.'&back='.urlencode(self::OFFSITE));

		$I->seeNoRedirectTo('evil.example.invalid');
		// Measured as "bounced home", not as "answered with nothing at all":
		// redirect(null) falls through to SITEURL (redirection_class.php:844-847).
		$I->seeRedirectTo($this->siteUrl);
	}

	/**
	 * A protocol-relative target is the same hole spelled without a scheme, and
	 * it is the form a naive "does it start with http" check misses.
	 */
	public function aProtocolRelativeBackUrlIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a protocol-relative xup back parameter');

		$I->amOnPage(self::ROUTE.'&back='.urlencode('//evil.example.invalid/phish'));

		$I->seeNoRedirectTo('evil.example.invalid');
		// Measured as "bounced home", not as "answered with nothing at all":
		// redirect(null) falls through to SITEURL (redirection_class.php:844-847).
		$I->seeRedirectTo($this->siteUrl);
	}

	/**
	 * And the same again with a backslash, which several browsers normalise to
	 * a forward slash before they resolve the host. verifyDestination() already
	 * collapses these (redirection_class.php:592-593).
	 */
	public function aBackslashSmuggledBackUrlIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a backslash-smuggled xup back parameter');

		$I->amOnPage(self::ROUTE.'&back='.urlencode('/\\evil.example.invalid/phish'));

		$I->seeNoRedirectTo('evil.example.invalid');
		// Measured as "bounced home", not as "answered with nothing at all":
		// redirect(null) falls through to SITEURL (redirection_class.php:844-847).
		$I->seeRedirectTo($this->siteUrl);
	}

	/**
	 * And with a tab. A URL parser deletes every ASCII tab, LF and CR from its
	 * input before it looks for an authority, and PHP's header() rejects only LF
	 * and CR, so "/<TAB>/host" is a rooted path to a string predicate and an
	 * off-site authority to the browser that reads the Location. %09 is legal in
	 * a request line and PHP urldecodes it, so this arrives raw.
	 */
	public function aTabSmuggledBackUrlIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a tab-smuggled xup back parameter');

		$I->amOnPage(self::ROUTE.'&back=/%09/evil.example.invalid/phish');

		$I->seeNoRedirectTo('evil.example.invalid');
		$I->seeRedirectTo($this->siteUrl);
	}

	/**
	 * Positive control. The parameter exists so a social login can return the
	 * visitor to the page they came from, so an on-site destination has to keep
	 * working or the fix has broken the feature.
	 */
	public function anOnSiteBackUrlStillRedirects(AcceptanceTester $I)
	{
		$I->wantTo('Still return a visitor to an on-site destination');

		$I->amOnPage(self::ROUTE.'&back='.urlencode('/news.php?extend.7'));

		$I->seeRedirectTo('/news.php?extend.7');
	}
}
