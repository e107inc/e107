<?php

/**
 * The systemic half of P19: redirect_class::go() validates nothing.
 *
 * e107_handlers/redirection_class.php:817 ends in
 *
 *     header('Location: '.$url, $replace);
 *
 * with no question asked of $url along the way. Every caller in the tree that
 * hands go() a value derived from the request is therefore an open redirect,
 * and the forum jump is simply the one that was found. Fixing the forum jump
 * alone leaves the next caller to be written exactly as unsafe.
 *
 * The probe here is that next caller: it passes ?dest= straight to go(). It
 * measures go()'s own default rather than any one call site's care.
 *
 * Two of these four are controls that pass before the fix as well as after,
 * and they are what stops the refusal being satisfied by a blanket refusal:
 *
 *  - an on-site destination must still be emitted;
 *  - a document-relative destination must still be emitted, because most of
 *    what core passes to go() is exactly that (e_PLUGIN."download/download.php"
 *    at download.php:25, e107::url() without the full option) and it cannot
 *    leave the site;
 *  - the opt-in must still reach an off-site destination, because there are
 *    legitimate ones (the marketplace, the banner click-through, an external
 *    download mirror, the ban page).
 *
 * REDIRECTS ARE NOT FOLLOWED HERE, for the reasons in 0049.
 */
class RedirectGoOffsiteCest
{
	/** @var string SITEURL as the application computes it */
	private $siteUrl = '';

	/** @var string SITEURLBASE as the application computes it */
	private $siteUrlBase = '';

	public function _before(AcceptanceTester $I)
	{
		// Every request in this file arrives from the same bridge address and
		// e107 bans one after the configured ceiling. Reset here rather than
		// relying on a neighbouring Cest having sorted first and done it.
		$I->resetForumFloodProtection();

		$I->writeAppFile(\Helper\RedirectFixture::PROBE_FILE, \Helper\RedirectFixture::probeSource());

		$I->amOnPage('/'.\Helper\RedirectFixture::PROBE_FILE.'?p19=constants');
		$I->see('P19_OK constants');
		$source = $I->grabPageSource();
		preg_match('#^SITEURL:(.*)$#m', $source, $match);
		$this->siteUrl = trim($match[1]);
		preg_match('#^SITEURLBASE:(.*)$#m', $source, $match);
		$this->siteUrlBase = trim($match[1]);

		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->deleteAppFile(\Helper\RedirectFixture::PROBE_FILE);
	}

	/**
	 * @param string $dest
	 * @param bool $external
	 * @return string the probe URL
	 */
	private function probe($dest, $external = false)
	{
		return '/'.\Helper\RedirectFixture::PROBE_FILE.'?p19=go&dest='.rawurlencode($dest)
			.($external ? '&external=1' : '');
	}

	public function goRefusesAnAbsoluteOffsiteDestination(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an off-site destination handed to go() by default');

		$I->resetAllCookies();
		$I->amOnPage($this->probe(\Helper\RedirectFixture::OFFSITE));

		$I->seeNoRedirectTo(\Helper\RedirectFixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->siteUrl);
	}

	public function goRefusesAProtocolRelativeDestination(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a protocol-relative destination handed to go()');

		$I->resetAllCookies();
		$I->amOnPage($this->probe(\Helper\RedirectFixture::OFFSITE_PROTOCOL_RELATIVE));

		$I->seeNoRedirectTo(\Helper\RedirectFixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->siteUrl);
	}

	public function goRefusesABackslashSmuggledDestination(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a backslash-smuggled destination handed to go()');

		$I->resetAllCookies();
		$I->amOnPage($this->probe(\Helper\RedirectFixture::OFFSITE_BACKSLASH));

		$I->seeNoRedirectTo(\Helper\RedirectFixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->siteUrl);
	}

	/**
	 * A tab is deleted by a URL parser before it looks for an authority and
	 * survives PHP's header() check, so a predicate that reads character 0 of the
	 * raw string calls this a rooted path while the client reads an authority.
	 */
	public function goRefusesATabSmuggledDestination(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a tab-smuggled destination handed to go()');

		$I->resetAllCookies();
		$I->amOnPage($this->probe(\Helper\RedirectFixture::OFFSITE_TAB));

		$I->seeNoRedirectTo(\Helper\RedirectFixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->siteUrl);
	}

	/**
	 * And leading whitespace, which every HTTP client strips from a header value
	 * before it reads one. This is the spelling that reaches login.php's cookie
	 * destination and rate.php's query string, neither of which has a call-site
	 * gate: the framework default is all that stands there.
	 */
	public function goRefusesALeadingSpaceDestination(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a destination handed to go() behind leading whitespace');

		$I->resetAllCookies();
		$I->amOnPage($this->probe(\Helper\RedirectFixture::OFFSITE_LEADING_SPACE));

		$I->seeNoRedirectTo(\Helper\RedirectFixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->siteUrl);
	}

	/**
	 * The second defect the package closes, measured where it exists.
	 *
	 * verifyDestinationUrl() used to accept an absolute destination whose string
	 * merely began with SITEURLBASE. SITEURLBASE carries no trailing slash, so on
	 * a root install (this container: "http://web") the destination
	 * "http://web.evil.example/phish" prefix-matched and was returned unchanged.
	 * The unit suite cannot see it: there SITEURLBASE is "https://localhost/e107"
	 * and carries a path, so the prefix test already failed for an unrelated
	 * reason.
	 */
	public function verifyDestinationUrlComparesTheHostNotThePrefix(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a host that merely starts with this site\'s own');

		$I->assertStringNotContainsString('/', preg_replace('#^https?://#', '', $this->siteUrlBase),
			'SITEURLBASE must carry no path here or the prefix defect is not reachable');

		$I->amOnPage('/'.\Helper\RedirectFixture::PROBE_FILE.'?p19=verify&dest='
			.rawurlencode($this->siteUrlBase.'.evil.example.invalid/phish'));
		$I->see('P19_OK verify');
		$I->see('VERIFY:false');
	}

	/**
	 * Control. An absolute destination on this site is not an open redirect and
	 * must be emitted unchanged.
	 */
	public function goStillRedirectsToAnAbsoluteOnSiteDestination(AcceptanceTester $I)
	{
		$I->wantTo('Still redirect to an absolute destination on this site');

		$I->resetAllCookies();
		$I->amOnPage($this->probe($this->siteUrl.'news.php?extend.7'));

		$I->seeRedirectTo('news.php?extend.7');
	}

	/**
	 * Control, and the one that catches a fix written as a bare
	 * verifyDestinationUrl() gate. That method only accepts a relative path
	 * that starts with "/", but most of what core hands go() is relative to the
	 * document rather than to the root: download.php:25 passes
	 * e_PLUGIN."download/download.php" and e107::url() returns the same shape
	 * unless the full option is given. None of it can leave the site, so none
	 * of it may be refused.
	 */
	public function goStillRedirectsToADocumentRelativeDestination(AcceptanceTester $I)
	{
		$I->wantTo('Still redirect to a destination relative to the document');

		$I->resetAllCookies();
		$I->amOnPage($this->probe('e107_plugins/forum/forum.php?f=rules'));

		$I->seeRedirectTo('e107_plugins/forum/forum.php?f=rules');
	}

	/**
	 * Control. Off-site redirects that are the site's own decision rather than
	 * the visitor's have to keep working, or the fix has broken the
	 * marketplace, the banner click-through and the external download mirror.
	 */
	public function goStillRedirectsOffSiteWhenAskedExplicitly(AcceptanceTester $I)
	{
		$I->wantTo('Still redirect off site when the caller opts in');

		$I->resetAllCookies();
		$I->amOnPage($this->probe(\Helper\RedirectFixture::OFFSITE, true));

		$I->seeRedirectTo(\Helper\RedirectFixture::OFFSITE_HOST);
	}
}
