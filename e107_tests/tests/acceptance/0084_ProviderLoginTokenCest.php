<?php

/**
 * index.php:127 starts a social login handshake for whoever asks:
 *
 *   if(vartrue($_GET['provider']) && !isset($_SESSION['E:SOCIAL'])
 *      && e107::getUserProvider()->isSocialLoginEnabled() && (e_ADMIN_AREA !== true))
 *   {
 *       $hybridauth = e107::getHybridAuth();
 *       ...
 *       $adapter = $hybridauth->authenticate($prov);
 *
 * The provider never comes back here: e_user_provider::generateCallbackUrl()
 * (e107_handlers/user_handler.php:1790-1800) registers system/xup/login as the
 * only callback, so this endpoint is an initiation and nothing else, and the
 * proof it has to demand need not survive a round trip through the provider.
 *
 * Left alone it reaches $sql->createQueryBuilder()->insert('user') at :172
 * and new userlogin(...) at :185 on a session already connected with the
 * provider: Hybridauth\Adapter\OAuth2::authenticate() returns at its
 * isConnected() test (vendor/hybridauth/hybridauth/src/Adapter/OAuth2.php:
 * 311-313) before it looks at any authorisation state.
 *
 * REDIRECTS ARE NOT FOLLOWED HERE. The handshake leaves the site, so a followed
 * redirect would try to reach the provider from the test container. The Location
 * header is the evidence.
 *
 * The fixture switches social login on for the whole site, so the probe answers
 * nobody who cannot show the secret this run minted for it, and it loads
 * class2.php before it looks at what it was asked to do.
 */
class ProviderLoginTokenCest
{
	const PROBE_FILE = 'e107_tests_xup_token_probe.php';

	/**
	 * The block sits after index.php:107-108, where anything eFront calls
	 * legacy is included and exited from, and on a default install / and
	 * /index.php are both news.php on release/v2.3.x. The site's own not-found
	 * route is not legacy on either branch, and the URL is the attacker's to
	 * choose.
	 */
	const ROUTE = '/index.php?route=system/error/notfound';

	/** The provider index.php hands to Hybridauth, whatever ?provider= says. */
	const PROVIDER = 'Facebook';

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
	 * The forgery. A page on another site puts this URL in front of a visitor
	 * and e107 begins an authentication the visitor never asked for.
	 */
	public function aTokenlessProviderLoginIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse to begin a social login handshake asked for by another site');

		$I->amOnPage(self::ROUTE.'&provider='.self::PROVIDER);

		$I->seeNoRedirectTo('facebook.com');
		$I->assertSame('', $this->authorizationState($I),
			'A tokenless request must not leave an authorisation state behind');
	}

	/**
	 * Positive control, and the measure of whether the refusal above is worth
	 * anything: this is the request the unfixed code accepted from anyone.
	 */
	public function aTokenedProviderLoginStillBeginsTheHandshake(AcceptanceTester $I)
	{
		$I->wantTo('still begin a social login handshake asked for from this site');

		$I->amOnPage(self::ROUTE.'&provider='.self::PROVIDER.'&e-token='.urlencode($this->token));

		$I->assertNotSame('', $this->authorizationState($I),
			'A tokened request has to reach the provider handshake');
	}

	/**
	 * The probe turns social login on and off for the whole site and hands out
	 * a token, so a caller that cannot show this run's secret has to get
	 * nothing at all. A probe left in the docroot by a run that died is
	 * otherwise an anonymous switch for the site's social login configuration.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=teardown');

		$I->seeResponseCodeIs(403);
		$I->assertSame('ENABLED=1 FACEBOOK=1', $this->providerConfig($I),
			'A refused caller must not have switched social login off');
	}

	/**
	 * What Hybridauth stored for the provider on the last request, read back
	 * through the probe because it lives in the PHP session rather than in any
	 * response. Empty when no handshake was begun.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function authorizationState(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=state'));
		$I->seeInSource('PROBE_OK state');

		preg_match('#^STATE:(.*)$#m', $I->grabPageSource(), $match);

		return isset($match[1]) ? trim($match[1]) : '';
	}

	/**
	 * The social login configuration the fixture put in place, read back
	 * through the probe because it lives in the site's preferences.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function providerConfig(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=config'));
		$I->seeInSource('PROBE_OK config');

		preg_match('#^CONFIG:(.*)$#m', $I->grabPageSource(), $match);

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
{{E107_TEST_PROBE_GUARD}}

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

header('Content-Type: text/plain');

require_once(e_PLUGIN.'social/includes/social_login_config.php');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$manager = new social_login_config(e107::getConfig('core'));

switch(\$act)
{
	case 'setup':
		\$manager->setProviderConfig('Facebook', array(
			'enabled' => 1,
			'keys' => array('id' => 'e107testsclientid', 'secret' => 'e107testsclientsecret'),
		));
		\$manager->saveConfig();
		\$manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, true);
		echo "PROBE_OK setup\\n";
		echo 'TOKEN:'.defset('e_TOKEN')."\\n";
		break;

	case 'state':
		\$state = '';
		\$store = isset(\$_SESSION['HYBRIDAUTH::STORAGE']) ? \$_SESSION['HYBRIDAUTH::STORAGE'] : array();
		foreach(\$store as \$key => \$value)
		{
			if(strpos(\$key, 'authorization_state') !== false) \$state = \$value;
		}
		echo "PROBE_OK state\\n";
		echo 'STATE:'.\$state."\\n";
		break;

	case 'config':
		echo "PROBE_OK config\\n";
		echo 'CONFIG:ENABLED='.(\$manager->isFlagActive(social_login_config::ENABLE_BIT_GLOBAL) ? 1 : 0)
			.' FACEBOOK='.(\$manager->isProviderEnabled('Facebook') ? 1 : 0)."\\n";
		break;

	case 'teardown':
		\$manager->setFlag(social_login_config::ENABLE_BIT_GLOBAL, false);
		\$manager->forgetProvider('Facebook');
		\$manager->saveConfig();
		unset(\$_SESSION['HYBRIDAUTH::STORAGE']);
		echo "PROBE_OK teardown\\n";
		break;
}
PHP;
	}
}
