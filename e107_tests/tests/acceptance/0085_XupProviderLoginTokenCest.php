<?php

/**
 * core_system_xup_controller::actionLogin() hands whatever ?provider= names
 * straight to Hybridauth, and e_user_provider::login() reaches
 * $this->hybridauth->authenticate($this->getProvider()) from there. On a
 * session already connected with the provider that call returns at
 * Hybridauth\Adapter\OAuth2::authenticate()'s isConnected() test without any
 * handshake, and e107 goes on to find or create a user row and log them in.
 *
 * This route cannot simply demand a token of everything that arrives, because
 * e_user_provider::generateCallbackUrl() registers it as the provider's own
 * return address. The token is asked of the leg that begins a login, and the
 * leg the provider sends back is recognised by the handshake record Hybridauth
 * put in the session on the way out.
 *
 * Both legs are exercised here, and the returning ones for both adapter
 * families that keep such a record: OAuth2 under authorization_state, OAuth1
 * under request_token.
 *
 * The five OpenID providers keep no such record, so nothing in the session
 * tells their two legs apart and neither is asked for a token. What is left is
 * a forced login, and what refuses it is the browser's own account of the
 * request: another site asking for this address as anything but a document is
 * neither a sign-in a visitor started nor a provider bringing one home. The
 * three OpenID cases hold everything else still and vary only the fetch
 * metadata, because that pair of headers is the whole of the decision; a
 * fourth sends none at all, which is the answer given by a browser too old to
 * have any and has to stay the answer it was.
 *
 * REDIRECTS ARE NOT FOLLOWED. A begun handshake leaves the site, and a
 * returning leg is answered with a redirect of e107's own; the evidence is the
 * session store, read back through the probe.
 *
 * That session store is the thing e_user_provider::loginNeedsToken() reads to
 * tell the two legs apart, and the fixture writes it, so the probe answers
 * nobody who cannot show the secret this run minted for it and loads class2.php
 * before it looks at what it was asked to do.
 */
class XupProviderLoginTokenCest
{
	const PROBE_FILE = 'e107_tests_xup_route_probe.php';

	/** The route that is both the start of a social login and its callback. */
	const ROUTE = '/index.php?route=system/xup/login';

	const OAUTH2_PROVIDER = 'Facebook';
	const OAUTH1_PROVIDER = 'Twitter';

	/** An OpenID provider: it records no handshake and cannot be given a token. */
	const OPENID_PROVIDER = 'Steam';

	/** The handshake record loginNeedsToken() reads for the OAuth2 provider. */
	const OAUTH2_HANDSHAKE = 'facebook.authorization_state';

	/**
	 * The account the profile seeded into Hybridauth's store resolves to, and
	 * its login name. That store is what Adapter\OpenID::isConnected() reads,
	 * above every other test its authenticate() makes.
	 */
	const OPENID_USER = 'e107tests OpenID';
	const OPENID_LOGINNAME = 'e107tests_openid';

	/** @var string a CSRF token minted for this client */
	private $token = '';

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('social');

		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage($this->probeUrl('act=setup'));
		$I->seeInSource('PROBE_OK setup');

		preg_match('#^TOKEN:(.*)$#m', $I->grabPageSource(), $match);
		$this->token = trim($match[1]);

		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage($this->probeUrl('act=teardown'));
		$I->seeInSource('PROBE_OK teardown');
		$I->deleteAppFile(self::PROBE_FILE);
		$I->dropPluginProbe();
	}

	/**
	 * The forgery: a page on another site puts this URL in front of a visitor
	 * and e107 begins an authentication the visitor never asked for.
	 */
	public function aTokenlessLoginIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse to begin a social login asked for by another site');

		$this->clearStore($I);
		$I->amOnPage(self::ROUTE.'&provider='.self::OAUTH2_PROVIDER);

		$I->seeNoRedirectTo('facebook.com');
		$I->assertSame('', $this->storedValue($I, self::OAUTH2_HANDSHAKE),
			'A tokenless request must not leave a handshake behind');
	}

	/**
	 * The positive control, and the measure of whether the refusal above is
	 * worth anything: this is the request the unguarded route took from anyone.
	 *
	 * The redirect_uri inside the address handed to the provider is watched as
	 * well. It is the callback every site has already filed with Facebook,
	 * Google and the rest, a provider matches it exactly, and putting the
	 * token on it would be a migration rather than a patch.
	 */
	public function aTokenedLoginBeginsTheHandshake(AcceptanceTester $I)
	{
		$I->wantTo('still begin a social login asked for from this site');

		$this->clearStore($I);
		$I->amOnPage(self::ROUTE.'&provider='.self::OAUTH2_PROVIDER.'&e-token='.urlencode($this->token));

		$I->seeRedirectTo('redirect_uri=');
		$I->seeNoRedirectTo('e-token');

		$I->assertNotSame('', $this->storedValue($I, self::OAUTH2_HANDSHAKE),
			'A tokened request has to reach the provider handshake');
	}

	/**
	 * The buttons a visitor actually clicks. They are built from the same
	 * address as the callback, so a token that only the guard knows about
	 * would refuse every genuine sign-in on the site.
	 */
	public function theSignInButtonsCarryTheToken(AcceptanceTester $I)
	{
		$I->wantTo('hand a visitor sign-in buttons that the guard will accept');

		$I->amOnPage($this->probeUrl('act=buttons'));
		$I->seeInSource('PROBE_OK buttons');

		preg_match('#^BUTTONS:(.*)$#m', $I->grabPageSource(), $match);
		$buttons = isset($match[1]) ? $match[1] : '';

		$I->assertStringContainsString('route=system/xup/login', $buttons,
			'The sign-in buttons have to reach the social login route');
		$I->assertStringContainsString('e-token=', $buttons,
			'The sign-in buttons have to carry the token the route now asks for');
	}

	/**
	 * The leg that no token can reach. The provider is answering a handshake
	 * this session began, and it arrives with nothing but what the provider
	 * chose to put on the callback URL.
	 *
	 * The state deliberately does not match the one seeded, so
	 * Hybridauth\Adapter\OAuth2::authenticateFinish() rejects it before any
	 * request leaves the container. That it was rejected AT ALL is the finding:
	 * a refused request never reaches Hybridauth and leaves the seed untouched.
	 */
	public function anOauth2ProviderReturningIsNotAskedForAToken(AcceptanceTester $I)
	{
		$I->wantTo('let an OAuth2 provider finish a handshake this session began');

		$this->clearStore($I);
		$this->seedStore($I, self::OAUTH2_HANDSHAKE, 'e107tests-seeded-state');

		$I->amOnPage(self::ROUTE.'&provider='.self::OAUTH2_PROVIDER.'&code=e107tests&state=e107tests-elsewhere');

		$I->assertSame('', $this->storedValue($I, self::OAUTH2_HANDSHAKE),
			'The returning leg has to reach Hybridauth, which consumes the handshake');
	}

	/**
	 * The same leg for the other adapter family, which records a handshake
	 * under request_token instead. ?denied= is refused by
	 * Hybridauth\Adapter\OAuth1::authenticateFinish() before any request leaves
	 * the container, and refused is the proof that it got there.
	 */
	public function anOauth1ProviderReturningIsNotAskedForAToken(AcceptanceTester $I)
	{
		$I->wantTo('let an OAuth1 provider finish a handshake this session began');

		$this->clearStore($I);
		$this->seedStore($I, 'twitter.request_token', 'e107tests-seeded-request-token');

		$I->amOnPage(self::ROUTE.'&provider='.self::OAUTH1_PROVIDER.'&denied=e107tests');

		$I->assertSame('', $this->storedValue($I, 'twitter.request_token'),
			'The returning leg has to reach Hybridauth, which consumes the handshake');
	}

	/**
	 * The OpenID leg a visitor starts, from a link on a page of this site.
	 * Nothing about it may change: the whole cost of the guard is meant to
	 * fall on somebody else.
	 */
	public function anOpenIdLoginStartedFromThisSiteStillSignsIn(AcceptanceTester $I)
	{
		$I->wantTo('still sign in with an OpenID provider from a link on this site');

		$this->reconnectOpenId($I);
		$this->fetchMetadata($I, 'same-origin', 'document');
		$I->amOnPage(self::ROUTE.'&provider='.self::OPENID_PROVIDER);

		$I->assertSame(self::OPENID_USER, $this->signedInAs($I),
			'A sign-in started from this site has to reach the provider');
	}

	/**
	 * The forgery, and the forced login it wins. A page on another site loads
	 * this address as an image, and a visitor whose session still holds the
	 * profile of an earlier OpenID sign-in is signed straight back in as that
	 * identity, on a shared machine as readily as anywhere else.
	 *
	 * The attacker holds no session, no token and no account, and cannot read
	 * the response; that a request shaped like this one signs anybody in at all
	 * is the whole finding.
	 */
	public function anOpenIdLoginAnotherSiteAsksForInTheBackgroundIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse a social login another site asked for in the background');

		$this->reconnectOpenId($I);
		$this->fetchMetadata($I, 'cross-site', 'image');
		$I->amOnPage(self::ROUTE.'&provider='.self::OPENID_PROVIDER);

		$I->assertSame('', $this->signedInAs($I),
			'An image another site asked for must not sign anybody in');
	}

	/**
	 * The leg that matters most, and the one a guard built out of these headers
	 * can most easily break: an OpenID provider sending the visitor back is a
	 * navigation from another site to a document, and it carries nothing this
	 * site issued. Refusing it would take every OpenID sign-in down with it.
	 */
	public function anOpenIdProviderReturningAsANavigationStillSignsIn(AcceptanceTester $I)
	{
		$I->wantTo('let an OpenID provider send the visitor back to this site');

		$this->reconnectOpenId($I);
		$this->fetchMetadata($I, 'cross-site', 'document');
		$I->amOnPage(self::ROUTE.'&provider='.self::OPENID_PROVIDER);

		$I->assertSame(self::OPENID_USER, $this->signedInAs($I),
			'A provider returning the visitor has to be let through');
	}

	/**
	 * A browser that predates fetch metadata says nothing at all, and silence
	 * is not a claim to have come from somewhere else. e_core_session::attest()
	 * falls through on it rather than refusing, and so does this: an OpenID
	 * sign-in was never gated and a silent client must not be the one visitor
	 * who finds it is.
	 */
	public function anOpenIdLoginWithoutFetchMetadataStillSignsIn(AcceptanceTester $I)
	{
		$I->wantTo('leave a browser that sends no fetch metadata as it was');

		$this->reconnectOpenId($I);
		$I->amOnPage(self::ROUTE.'&provider='.self::OPENID_PROVIDER);

		$I->assertSame(self::OPENID_USER, $this->signedInAs($I),
			'A client that says nothing has to be let through');
	}

	/**
	 * Seeding that store is exactly how a returning leg is recognised, so a
	 * caller that cannot show this run's secret has to get nothing at all. A
	 * probe left in the docroot by a run that died would otherwise let another
	 * site plant a handshake record in a visitor's session and walk the very
	 * refusal the first case pins straight past the guard.
	 *
	 * Planting an OpenID profile is worse still, because that is the state the
	 * forced login needs and the probe writes it in one request, so the gate is
	 * pinned on that action as well as on the one that seeds a handshake.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$this->clearStore($I);
		$I->amOnPage('/'.self::PROBE_FILE.'?act=seed&key='.urlencode(self::OAUTH2_HANDSHAKE).'&value=e107tests-forged');

		$I->seeResponseCodeIs(403);
		$I->assertSame('', $this->storedValue($I, self::OAUTH2_HANDSHAKE),
			'A refused caller must not have planted a handshake record');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=connect');

		$I->seeResponseCodeIs(403);
		$I->assertSame('', $this->storedValue($I, strtolower(self::OPENID_PROVIDER).'.user'),
			'A refused caller must not have planted an OpenID profile');
	}

	/**
	 * Sign the client out and give it back the profile of an earlier OpenID
	 * sign-in, so that every OpenID case below starts from the one session
	 * state the risk needs and differs only in what the browser says.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function reconnectOpenId(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=signout'));
		$I->seeInSource('PROBE_OK signout');
		$I->amOnPage($this->probeUrl('act=connect'));
		$I->seeInSource('PROBE_OK connect');
	}

	/**
	 * Say what the browser would have said. The harness sends no Sec-Fetch-*
	 * of its own, so a case that does not set these is the silent client.
	 *
	 * @param AcceptanceTester $I
	 * @param string $site
	 * @param string $dest
	 * @return void
	 */
	private function fetchMetadata(AcceptanceTester $I, $site, $dest)
	{
		$I->haveHttpHeader('Sec-Fetch-Site', $site);
		$I->haveHttpHeader('Sec-Fetch-Dest', $dest);
	}

	/**
	 * Who the client is signed in as, read back through the probe because a
	 * refused request and an accepted one are answered by the same redirect.
	 *
	 * The headers are dropped first: they belong to the request under test and
	 * would otherwise ride along on every request that reads its result.
	 *
	 * @param AcceptanceTester $I
	 * @return string the display name, or '' when nobody is signed in
	 */
	private function signedInAs(AcceptanceTester $I)
	{
		$I->deleteHeader('Sec-Fetch-Site');
		$I->deleteHeader('Sec-Fetch-Dest');

		$I->amOnPage($this->probeUrl('act=whoami'));
		$I->seeInSource('PROBE_OK whoami');

		preg_match('#^USER:(.*)$#m', $I->grabPageSource(), $match);

		return isset($match[1]) ? trim($match[1]) : '';
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function clearStore(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=clear'));
		$I->seeInSource('PROBE_OK clear');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	private function seedStore(AcceptanceTester $I, $key, $value)
	{
		$I->amOnPage($this->probeUrl('act=seed&key='.urlencode($key).'&value='.urlencode($value)));
		$I->seeInSource('PROBE_OK seed');
	}

	/**
	 * What Hybridauth's session store holds for one key, read back through the
	 * probe because it lives in the PHP session and never in any response.
	 *
	 * @param AcceptanceTester $I
	 * @param string $key
	 * @return string
	 */
	private function storedValue(AcceptanceTester $I, $key)
	{
		$I->amOnPage($this->probeUrl('act=peek&key='.urlencode($key)));
		$I->seeInSource('PROBE_OK peek');

		preg_match('#^VALUE:(.*)$#m', $I->grabPageSource(), $match);

		return isset($match[1]) ? trim($match[1]) : '';
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private function probeUrl($query)
	{
		$url = '/'.self::PROBE_FILE.'?probe='.$this->secret;

		return ($query === '') ? $url : $url.'&'.$query;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$secret = $this->secret;
		$provider = self::OPENID_PROVIDER;
		$identifier = self::OPENID_LOGINNAME;
		$username = self::OPENID_USER;
		$loginname = self::OPENID_LOGINNAME;
		$xup = self::OPENID_PROVIDER.'_'.self::OPENID_LOGINNAME;

		return <<<PHP
<?php
require_once(__DIR__.'/class2.php');

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

header('Content-Type: text/plain');

require_once(e_PLUGIN.'social/includes/social_login_config.php');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$key = isset(\$_GET['key']) ? \$_GET['key'] : '';
\$manager = new social_login_config(e107::getConfig('core'));

switch(\$act)
{
	case 'setup':
		\$manager->setProviderConfig('Facebook', array(
			'enabled' => 1,
			'keys' => array('id' => 'e107testsclientid', 'secret' => 'e107testsclientsecret'),
		));
		\$manager->setProviderConfig('Twitter', array(
			'enabled' => 1,
			'keys' => array('key' => 'e107testsconsumerkey', 'secret' => 'e107testsconsumersecret'),
		));
		\$manager->setProviderConfig('Steam', array('enabled' => 1));
		\$manager->saveConfig();
		\$manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, true);
		e107::getDb()->delete('user', "user_loginname='$loginname'");
		e107::getDb()->insert('user', array(
			'user_name' => '$username',
			'user_loginname' => '$loginname',
			'user_login' => '$username',
			'user_password' => '',
			'user_email' => '$loginname@example.invalid',
			'user_hideemail' => 1,
			'user_join' => time(),
			'user_ban' => 0,
			'user_xup' => '$xup',
		));
		echo "PROBE_OK setup\\n";
		echo 'TOKEN:'.defset('e_TOKEN')."\\n";
		break;

	case 'signout':
		e107::getUser()->logout();
		echo "PROBE_OK signout\\n";
		break;

	case 'connect':
		\$profile = new Hybridauth\\User\\Profile();
		\$profile->identifier = '$identifier';
		\$profile->displayName = '$username';
		\$store = new Hybridauth\\Storage\\Session();
		\$store->set('$provider.user', \$profile);
		echo "PROBE_OK connect\\n";
		break;

	case 'whoami':
		echo "PROBE_OK whoami\\n";
		echo 'USER:'.(e107::getUser()->isUser() ? e107::getUser()->getName() : '')."\\n";
		break;

	case 'clear':
		unset(\$_SESSION['HYBRIDAUTH::STORAGE']);
		echo "PROBE_OK clear\\n";
		break;

	case 'buttons':
		\$buttons = e107::getScBatch('signup')->sc_signup_xup_login(array());
		echo "PROBE_OK buttons\\n";
		echo 'BUTTONS:'.str_replace(array("\\r", "\\n"), ' ', \$buttons)."\\n";
		break;

	case 'seed':
		\$_SESSION['HYBRIDAUTH::STORAGE'][\$key] = isset(\$_GET['value']) ? \$_GET['value'] : '';
		echo "PROBE_OK seed\\n";
		break;

	case 'peek':
		\$store = isset(\$_SESSION['HYBRIDAUTH::STORAGE']) ? \$_SESSION['HYBRIDAUTH::STORAGE'] : array();
		\$held = isset(\$store[\$key]) ? \$store[\$key] : '';
		echo "PROBE_OK peek\\n";
		echo 'VALUE:'.(is_scalar(\$held) ? \$held : 'held')."\\n";
		break;

	case 'teardown':
		\$manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, false);
		\$manager->forgetProvider('Facebook');
		\$manager->forgetProvider('Twitter');
		\$manager->forgetProvider('Steam');
		\$manager->saveConfig();
		e107::getUser()->logout();
		e107::getDb()->delete('user', "user_loginname='$loginname'");
		unset(\$_SESSION['HYBRIDAUTH::STORAGE']);
		echo "PROBE_OK teardown\\n";
		break;
}
PHP;
	}
}
