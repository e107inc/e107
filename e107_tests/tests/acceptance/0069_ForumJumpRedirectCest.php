<?php

/**
 * The forum jump is an unauthenticated open redirect.
 *
 * e107_plugins/forum/forum_class.php:98-103, at the very top of
 * e107forum::__construct():
 *
 *     if (!empty($_POST['fjsubmit']) && !empty($_POST['forumjump']))
 *     {
 *         $url = e107::getParser()->filter($_POST['forumjump'],'url');
 *         e107::getRedirect()->go($_POST['forumjump']);
 *         exit;
 *     }
 *
 * The filtered value is assigned to $url and then never read. The raw post
 * value is what reaches go(), and go() validates nothing, so an anonymous
 * visitor who posts fjsubmit and forumjump to any page that constructs an
 * e107forum is bounced wherever they asked. An open redirect is worth more
 * than usual here because the URL that carries it is the site's own, and the
 * page it lands on can imitate whatever the visitor was expecting.
 *
 * The reachable set is wider than the forum plugin. top.php requires
 * forum_class.php and constructs the object without asking whether the forum
 * plugin is installed at all, so the redirect fires on a core page. That is
 * what topPhpIsTheSameHole() measures. The plugin is installed for every test
 * in this file; that the answer does not change when it is not is read off the
 * source of top.php, which has no e107::isInstalled('forum') gate, rather than
 * measured here.
 *
 * REDIRECTS ARE NOT FOLLOWED HERE. PhpBrowser chases them without a cap, so a
 * followed redirect measures the test client rather than the application, and
 * an e107 that cannot serve a request answers with a relative Location of
 * install.php, which loops. The Location header is the evidence.
 */
class ForumJumpRedirectCest
{
	/** @var array forum fixture ids */
	private $ids;

	/** @var string SITEURL as the application computes it */
	private $siteUrl = '';

	/** @var string the jump destination the viewforum shortcode emits */
	private $jumpFull = '';

	/** @var string the jump destination the view and post shortcodes emit */
	private $jumpShort = '';

	/**
	 * @var string the forum index, where the call-site fix sends a refused jump.
	 *
	 * Distinct from SITEURL on purpose. go()'s own default sends a refused
	 * destination to SITEURL, so a refusal asserted as "landed on SITEURL" is
	 * satisfied by either layer and attributes nothing to the call site. This
	 * one is only produced by the fix in e107forum::__construct().
	 */
	private $jumpIndex = '';

	public function _before(AcceptanceTester $I)
	{
		// Every request in this file arrives from the same bridge address and
		// e107 bans one after 50 in a window.
		$I->resetForumFloodProtection();

		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$I->purgeForumPermCache();

		$I->writeAppFile(\Helper\P19Fixture::PROBE_FILE, \Helper\P19Fixture::probeSource());

		// Asked of the application, not derived. A refusal is measured as
		// "bounced back onto this site", and where that is depends on SITEURL.
		$I->amOnPage('/'.\Helper\P19Fixture::PROBE_FILE.'?p19=constants');
		$I->see('P19_OK constants');
		$source = $I->grabPageSource();
		preg_match('#^SITEURL:(.*)$#m', $source, $match);
		$this->siteUrl = trim($match[1]);

		// The legitimate jump destinations, taken from the same call the four
		// sc_forumjump() implementations make. Two of them ask for the full
		// form and two for the short one, and both have to keep working.
		$I->amOnPage('/'.\Helper\P19Fixture::PROBE_FILE.'?p19=jumpurl&id='.$this->ids['forumA']);
		$I->see('P19_OK jumpurl');
		$source = $I->grabPageSource();
		preg_match('#^FULL:(.*)$#m', $source, $match);
		$this->jumpFull = trim($match[1]);
		preg_match('#^SHORT:(.*)$#m', $source, $match);
		$this->jumpShort = trim($match[1]);
		preg_match("#^INDEX:'(.*)'$#m", $source, $match);
		$this->jumpIndex = isset($match[1]) ? trim($match[1]) : '';
		// var_export() of false has no quotes, so an unregistered e_url config
		// leaves this empty rather than silently matching every Location.
		$I->assertNotEmpty($this->jumpIndex, 'e107::url(forum, index) must resolve');
		$I->assertStringNotContainsString($this->jumpIndex, $this->siteUrl,
			'The forum index must be distinguishable from SITEURL, or a refusal '
			.'measured against it cannot tell the call-site fix from go() backstop');

		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->deleteAppFile(\Helper\P19Fixture::PROBE_FILE);
		$I->dropForumProbe();
	}

	/**
	 * No account, no cookie, no token.
	 */
	public function anAnonymousVisitorIsNotJumpedOffSite(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to bounce an anonymous visitor off site through the forum jump');

		$I->resetAllCookies();
		$I->sendPostRequest('/e107_plugins/forum/forum.php', array(
			'fjsubmit'  => 'Go',
			'forumjump' => \Helper\P19Fixture::OFFSITE,
		));

		$I->seeNoRedirectTo(\Helper\P19Fixture::OFFSITE_HOST);
		// Measured as "landed on the forum index", not as "answered with nothing
		// at all" and not as "landed on SITEURL": only the call-site fix produces
		// the forum index, so reverting it fails here even though go()'s default
		// would still refuse the destination.
		$I->seeRedirectTo($this->jumpIndex);
	}

	/**
	 * A protocol-relative destination is the same hole spelled without a
	 * scheme, and it is the form a naive "does it start with http" check
	 * misses.
	 */
	public function aProtocolRelativeJumpIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a protocol-relative forum jump');

		$I->resetAllCookies();
		$I->sendPostRequest('/e107_plugins/forum/forum.php', array(
			'fjsubmit'  => 'Go',
			'forumjump' => \Helper\P19Fixture::OFFSITE_PROTOCOL_RELATIVE,
		));

		$I->seeNoRedirectTo(\Helper\P19Fixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->jumpIndex);
	}

	/**
	 * And with a backslash, which several browsers normalise to a forward
	 * slash before they resolve the authority.
	 */
	public function aBackslashSmuggledJumpIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a backslash-smuggled forum jump');

		$I->resetAllCookies();
		$I->sendPostRequest('/e107_plugins/forum/forum.php', array(
			'fjsubmit'  => 'Go',
			'forumjump' => \Helper\P19Fixture::OFFSITE_BACKSLASH,
		));

		$I->seeNoRedirectTo(\Helper\P19Fixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->jumpIndex);
	}

	/**
	 * A tab is deleted by a URL parser before it looks for an authority, and
	 * PHP's header() lets it through, so "/<TAB>/host" is a rooted path to a
	 * string predicate and an off-site authority to the browser that reads the
	 * Location. This is the spelling that walks past a fix written for the
	 * backslash alone.
	 */
	public function aTabSmuggledJumpIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a tab-smuggled forum jump');

		$I->resetAllCookies();
		$I->sendPostRequest('/e107_plugins/forum/forum.php', array(
			'fjsubmit'  => 'Go',
			'forumjump' => \Helper\P19Fixture::OFFSITE_TAB,
		));

		$I->seeNoRedirectTo(\Helper\P19Fixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->jumpIndex);
	}

	/**
	 * The topic page constructs the object too, so it carries the same hole.
	 * e_QUERY has to be non-empty or forum_viewtopic.php bounces to the forum
	 * index before it gets that far.
	 */
	public function theTopicPageIsTheSameHole(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the forum jump on the topic page as well as the index');

		$I->resetAllCookies();
		$I->sendPostRequest(
			'/e107_plugins/forum/forum_viewtopic.php?'.$this->ids['threadA'],
			array(
				'fjsubmit'  => 'Go',
				'forumjump' => \Helper\P19Fixture::OFFSITE,
			)
		);

		$I->seeNoRedirectTo(\Helper\P19Fixture::OFFSITE_HOST);
		// seeNoRedirectTo() is satisfied by no Location header at all, so a 500,
		// a flood ban or a page that stopped constructing the forum would pass it
		// while measuring nothing. Pair it with where the refusal has to land.
		$I->seeRedirectTo($this->jumpIndex);
	}

	/**
	 * The reachable set is not confined to the forum plugin. top.php pulls in
	 * forum_class.php and constructs an e107forum for ?0.active without ever
	 * asking whether the forum plugin is installed, so the jump fires on a core
	 * page. If this ever stops being reachable the test should be deleted, not
	 * weakened: it exists to record how far the constructor's side effect
	 * reaches.
	 */
	public function topPhpIsTheSameHole(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the forum jump on a core page that merely constructs the forum');

		$I->resetAllCookies();
		$I->sendPostRequest('/top.php?0.active', array(
			'fjsubmit'  => 'Go',
			'forumjump' => \Helper\P19Fixture::OFFSITE,
		));

		$I->seeNoRedirectTo(\Helper\P19Fixture::OFFSITE_HOST);
		$I->seeRedirectTo($this->jumpIndex);
	}

	/**
	 * Positive control for the page above rather than for the fix. If top.php
	 * ever stops reaching the jump block, topPhpIsTheSameHole() goes on passing
	 * for the wrong reason and this fails instead.
	 */
	public function topPhpStillReachesTheJump(AcceptanceTester $I)
	{
		$I->wantTo('Still jump from a core page that merely constructs the forum');

		$I->resetAllCookies();
		$I->sendPostRequest('/top.php?0.active', array(
			'fjsubmit'  => 'Go',
			'forumjump' => $this->jumpShort,
		));

		$I->seeRedirectTo($this->jumpShort);
	}

	/**
	 * Positive control. The jump is a real feature: a dropdown of the forums
	 * the visitor may read, which posts the chosen forum's URL back. A fix that
	 * refused everything would satisfy every assertion above and break the
	 * feature, so the absolute form the viewforum shortcode emits has to still
	 * be honoured.
	 */
	public function aLegitimateAbsoluteJumpStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('Still jump to a forum on this site when asked with a full URL');

		$I->resetAllCookies();
		$I->sendPostRequest('/e107_plugins/forum/forum.php', array(
			'fjsubmit'  => 'Go',
			'forumjump' => $this->jumpFull,
		));

		$I->seeRedirectTo($this->jumpFull);
	}

	/**
	 * The other positive control, and the sharper one. Two of the four
	 * sc_forumjump() implementations call e107::url() without asking for the
	 * full form, so what they put in the option list is a site-relative path.
	 * verifyDestinationUrl() only accepts a relative path that is site-rooted,
	 * so if that short form is not rooted this test is what says so.
	 */
	public function aLegitimateShortJumpStillWorks(AcceptanceTester $I)
	{
		$I->wantTo('Still jump to a forum on this site when asked with the short URL');

		$I->resetAllCookies();
		$I->sendPostRequest('/e107_plugins/forum/forum.php', array(
			'fjsubmit'  => 'Go',
			'forumjump' => $this->jumpShort,
		));

		$I->seeRedirectTo($this->jumpShort);
	}
}
