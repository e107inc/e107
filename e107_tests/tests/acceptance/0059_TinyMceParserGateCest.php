<?php

/**
 * e107_plugins/tinymce4/plugins/e107/parser.php carries no authorisation check
 * of any kind: no USER test, no check_class(), and getperms() only inside a
 * main-admin debug branch. It is web reachable on every install whether or not
 * tinymce4 is installed or configured as the editor, and because cookieless
 * requests are exempt from the CSRF token check by design, a bare
 * `curl -d content=...` gets a full framework boot and a parse.
 *
 * Its sibling dialog.php:10 has exactly the gate it lacks.
 *
 * WHY THESE TESTS ASSERT ON THE RESPONSE BODY. An authorisation test normally
 * has to read a side effect back, because e_admin_dispatcher::checkAccess()
 * rewrites the action to e403 while still constructing the controller and
 * running its init(), so "the page said Access Denied" passes on vulnerable
 * code. That hazard does not apply here: parser.php is not dispatched through
 * the admin UI, it writes nothing, and the parse it performs IS the whole
 * effect of the request. The parsed markup in the body is the side effect, not
 * a rendering of one.
 *
 * WHY EVERY COOKIE-BEARING CASE CARRIES A TOKEN. Without one the CSRF handler
 * refuses the POST before parser.php is reached, so an assertion that nothing
 * was parsed would pass on today's unfixed code for a reason that has nothing
 * to do with authorisation. Each of these tests therefore fetches a token for
 * whoever is asking and sends it, leaving authorisation as the only thing left
 * to decide the request.
 *
 * @see 0046_TinyMceConfigGateCest for the main-admin gate in wysiwyg_class.php
 */
class TinyMceParserGateCest
{
	const PARSER = '/e107_plugins/tinymce4/plugins/e107/parser.php';

	/**
	 * Bbcode whose parse is unmistakable. If any of these bytes come back, the
	 * request reached e107TinyMceParser::toHTML() and was served.
	 */
	const BBCODE_IN = '[b]ENCPARSECANARY[/b]';
	const PARSED_MARKER = 'ENCPARSECANARY</strong>';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\OutputEncodingFixture::PROBE_FILE, \Helper\OutputEncodingFixture::probeSource());
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');
		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=cleanup');
		$I->deleteAppFile(\Helper\OutputEncodingFixture::PROBE_FILE);
	}

	/**
	 * The headline: no account, no cookie, no token, a full parse.
	 */
	public function anUnauthenticatedCookielessPostIsNotParsed(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a cookieless POST to the TinyMce parser endpoint');

		$I->resetAllCookies();
		$I->sendPostRequest(self::PARSER, array(
			'content' => self::BBCODE_IN,
			'mode'    => 'tohtml',
		));

		$I->dontSeeInSource(self::PARSED_MARKER);
		$I->dontSeeInSource('ENCPARSECANARY');
	}

	/**
	 * The same endpoint reached by a visitor with a session and a valid token,
	 * so the refusal cannot be credited to the CSRF handler.
	 */
	public function aGuestWithAValidTokenIsNotParsed(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a guest POST that carries a valid CSRF token');

		$I->resetAllCookies();
		$token = $this->grabProbeToken($I);

		$I->sendPostRequest(self::PARSER, array(
			'content' => self::BBCODE_IN,
			'mode'    => 'tohtml',
			'e-token' => $token,
		));

		$I->dontSeeInSource('ENCPARSECANARY');
	}

	/**
	 * A logged-in member is not enough either. post_html ships as 254
	 * (administrators) and getperms('P') is an admin permission, so the gate
	 * this endpoint needs turns an ordinary member away as well.
	 */
	public function aMemberWithoutHtmlPostingRightsIsNotParsed(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a POST from a member who may not post HTML');

		$this->loginAsMember($I);
		$token = $this->grabProbeToken($I);

		$I->sendPostRequest(self::PARSER, array(
			'content' => self::BBCODE_IN,
			'mode'    => 'tohtml',
			'e-token' => $token,
		));

		$I->dontSeeInSource('ENCPARSECANARY');
	}

	/**
	 * Positive control for all three refusals. The editor loads for whoever
	 * satisfies check_class($pref['post_html']) || getperms('P')
	 * (e107_plugins/tinymce4/e_footer.php:16), so that identity must still get
	 * a parse, or the gate has broken the feature it is protecting.
	 */
	public function anAdministratorIsStillParsed(AcceptanceTester $I)
	{
		$I->wantTo('Still parse for an administrator, who may post HTML');

		$token = $this->loginAsAdminAndGrabToken($I);

		$I->sendPostRequest(self::PARSER, array(
			'content' => self::BBCODE_IN,
			'mode'    => 'tohtml',
			'e-token' => $token,
		));

		$I->seeInSource(self::PARSED_MARKER);
	}

	/**
	 * The other direction of the same feature: content on its way back to the
	 * database. Both modes have to keep working for the identity that is
	 * allowed to use the editor.
	 */
	public function anAdministratorCanStillConvertBackToBbcode(AcceptanceTester $I)
	{
		$I->wantTo('Still convert editor HTML back to bbcode for an administrator');

		$token = $this->loginAsAdminAndGrabToken($I);

		$I->sendPostRequest(self::PARSER, array(
			'content' => '<p>ENCPARSECANARY</p>',
			'mode'    => 'tobbcode',
			'e-token' => $token,
		));

		$I->seeInSource('[html]<p>ENCPARSECANARY</p>[/html]');
	}

	/**
	 * Regression control for the removal of parser.php's
	 * define('e_ADMIN_AREA', true).
	 *
	 * The flag decides which theme, language and library scope a request runs
	 * under. Removing it must not change what the editor is handed back, so
	 * these bytes are recorded from the tree as it stands and have to survive
	 * the deletion. If they do change, the change is a response change and
	 * belongs in the changelog rather than quietly in this file.
	 */
	public function theParseOutputIsUnchangedByTheAdminAreaFlag(AcceptanceTester $I)
	{
		$I->wantTo('Pin the parse output that removing e_ADMIN_AREA must not alter');

		$token = $this->loginAsAdminAndGrabToken($I);

		$I->sendPostRequest(self::PARSER, array(
			'content' => '[b]bold[/b] [quote=Bob]quoted[/quote]',
			'mode'    => 'tohtml',
			'e-token' => $token,
		));

		// bold: the bbcode class list is userclass driven, never flag driven
		$I->seeInSource("<strong class='bbcode bold bbcode-b'>bold</strong>");

		// quote: BOOTSTRAP is resolved through e107::getTheme(), and which
		// theme that is depends on e_ADMIN_AREA in the general case. The
		// blockquote branch of quote.bb is the answer on the installed theme.
		$I->seeInSource('<blockquote><p>quoted</p>');
		$I->dontSeeInSource('LAN_WROTE');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string the CSRF token belonging to the current session
	 */
	private function grabProbeToken(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=constants');
		$source = $I->grabPageSource();
		$I->stopFollowingRedirects();

		$matches = array();
		if(!preg_match('/TOKEN:(\S+)/', $source, $matches))
		{
			throw new \RuntimeException('The probe published no CSRF token');
		}

		return $matches[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function loginAsAdminAndGrabToken(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->loginAsAdmin();
		$I->stopFollowingRedirects();

		return $this->grabProbeToken($I);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function loginAsMember(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.\Helper\OutputEncodingFixture::PROBE_FILE.'?p8=member');
		$I->see('P8_OK member');

		$I->resetAllCookies();
		$I->amOnPage('/login.php');
		$I->fillField('username', \Helper\OutputEncodingFixture::MEMBER_NAME);
		$I->fillField('userpass', \Helper\OutputEncodingFixture::MEMBER_PASS);
		$I->click('userlogin');
		$I->stopFollowingRedirects();
	}
}
