<?php

/**
 * The other half of flipping go()'s default: the shipped callers that mean to
 * leave the site have to keep working.
 *
 * 0049a proves the $allowOffsite mechanism on a synthetic probe, which says
 * nothing about whether any real caller passes it. Ten call sites in core and
 * the bundled plugins were opted in by a manual sweep, and a sweep is exactly
 * the kind of evidence that rots: a later refactor of leavesThisSite(), a
 * conflict resolution during the v2.3.x backport, or a dropped argument turns
 * a banner click-through, an external download mirror or the marketplace into
 * a silent bounce to the home page with nothing red anywhere.
 *
 * The banner click-through is the cheapest of the ten to stand up, and it
 * exercises e107::redirect()'s new third argument end to end through a page
 * that ships, rather than through a fixture.
 *
 * REDIRECTS ARE NOT FOLLOWED HERE. The destination is deliberately off site;
 * the Location header is the evidence and following it would measure the test
 * client instead.
 */
class BannerOffsiteOptInCest
{
	/**
	 * A host that cannot resolve, so nothing here can emit real outbound
	 * traffic even if a client follows the redirect.
	 */
	const OFFSITE = 'https://offsite-canary.invalid/promo';

	/** @var int */
	private $bannerId = 0;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->havePluginInstalled('banner');

		$this->bannerId = (int) $I->haveInDatabase('e107_banner', array(
			'banner_clientname'  => 'P19 canary',
			'banner_image'       => 'canary.png',
			'banner_clickurl'    => self::OFFSITE,
			'banner_impurchased' => 0,
			'banner_startdate'   => 0,
			'banner_enddate'     => 0,
			'banner_active'      => 1,
			'banner_clicks'      => 0,
			'banner_impressions' => 0,
			'banner_ip'          => '',
			'banner_tooltip'     => '',
			'banner_description' => '',
			'banner_campaign'    => 'p19',
			'banner_keywords'    => '',
		));

		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		// Left installed, the plugin's own version rows make the next run's
		// InstallCest find an outstanding update on the reinstalled core.
		$I->dropPluginInstall('banner');
		$I->dropPluginProbe();
	}

	/**
	 * The click-through target is the site owner's decision, not the visitor's,
	 * so it is one of the destinations that may leave the site. If this goes red
	 * the opt-in has been dropped and every banner on every install now lands on
	 * the home page.
	 */
	public function aBannerClickStillLeavesTheSite(AcceptanceTester $I)
	{
		$I->wantTo('Still follow a banner click-through off site after the default flipped');

		$I->resetAllCookies();
		$I->amOnPage('/e107_plugins/banner/banner.php?'.$this->bannerId);

		$I->seeRedirectTo('offsite-canary.invalid');
	}
}
