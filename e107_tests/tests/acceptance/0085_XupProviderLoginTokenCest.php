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

	/** The handshake record loginNeedsToken() reads for the OAuth2 provider. */
	const OAUTH2_HANDSHAKE = 'facebook.authorization_state';

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
	 * Seeding that store is exactly how a returning leg is recognised, so a
	 * caller that cannot show this run's secret has to get nothing at all. A
	 * probe left in the docroot by a run that died would otherwise let another
	 * site plant a handshake record in a visitor's session and walk the very
	 * refusal the first case pins straight past the guard.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$this->clearStore($I);
		$I->amOnPage('/'.self::PROBE_FILE.'?act=seed&key='.urlencode(self::OAUTH2_HANDSHAKE).'&value=e107tests-forged');

		$I->seeResponseCodeIs(403);
		$I->assertSame('', $this->storedValue($I, self::OAUTH2_HANDSHAKE),
			'A refused caller must not have planted a handshake record');
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
		\$manager->saveConfig();
		\$manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, true);
		echo "PROBE_OK setup\\n";
		echo 'TOKEN:'.defset('e_TOKEN')."\\n";
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
		echo "PROBE_OK peek\\n";
		echo 'VALUE:'.(isset(\$store[\$key]) ? \$store[\$key] : '')."\\n";
		break;

	case 'teardown':
		\$manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, false);
		\$manager->forgetProvider('Facebook');
		\$manager->forgetProvider('Twitter');
		\$manager->saveConfig();
		unset(\$_SESSION['HYBRIDAUTH::STORAGE']);
		echo "PROBE_OK teardown\\n";
		break;
}
PHP;
	}
}
