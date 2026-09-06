<?php

/**
 * Admin > Schedule Tasks > Setup, and the command it hands out.
 *
 * cronSetupTest covers the strings themselves. What it cannot answer is
 * whether the command shown to an administrator is one their server can
 * actually run, so the URL is read off the rendered page here and requested,
 * and the same URL with the token altered is requested too. A page that
 * offered a URL cron.php refuses would pass every unit test in the suite.
 */
class CronSetupCest
{
	const SETUP_PAGE = '/e107_admin/cron.php?mode=main&action=setup';
	const LIST_PAGE = '/e107_admin/cron.php';
	const TOKEN_PATTERN = '#https?://[^\s\'"]+/cron\.php\?token=([0-9a-f]{40})#';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	/**
	 * Runs first, and provokes the refusal it then looks for, so that it does
	 * not depend on whether anything earlier in the suite ran cron.php.
	 */
	public function theListPageSendsTheAdministratorToSetup(AcceptanceTester $I)
	{
		$I->wantTo('be pointed at the Setup tab when cron.php is turning requests away');

		$I->amOnPage('/cron.php');
		$I->assertSame(403, $I->grabResponseCode(), 'a request with no token must be refused');

		$I->amOnPage(self::LIST_PAGE);
		$I->see('request(s) to cron.php have been refused');
		$I->see('They carried no token.');
		$I->see('Copy the command again from the Setup tab.');

		$I->assertMatchesRegularExpression(
			"#<a href='[^']*cron\.php\?mode=main&action=setup'>Setup</a> tab\.#",
			$I->grabPageSource(),
			'the warning must carry a link to the Setup tab'
		);
	}

	public function theSetupPageOffersTheOptionsBestFirst(AcceptanceTester $I)
	{
		$I->wantTo('read the three ways of scheduling cron.php, the recommended one first');

		$I->amOnPage(self::SETUP_PAGE);
		$source = $I->grabPageSource();

		$I->assertMatchesRegularExpression(self::TOKEN_PATTERN, $source,
			'an external cron service needs the whole URL, so it must carry the site URL and the token');
		$I->assertStringContainsString('e-copyable-btn', $source,
			'every command must come with a copy button');
		$I->assertStringContainsString('.e-copyable-text{', $source,
			'the copy button needs its styles on the page');
		$I->assertStringContainsString('navigator.clipboard.writeText', $source,
			'the copy button needs its script on the page');

		$I->see('Web request');
		$I->see('PHP command line');
		$I->see('Shell script');
		$I->see('Recommended');
		$I->see('Detected environment');

		$http = strpos($source, 'curl -fsS');
		$cli = strpos($source, 'cron.php token=');

		$I->assertNotFalse($http, 'the web request option must be offered');
		$I->assertNotFalse($cli, 'the command line option must be offered');
		$I->assertLessThan($cli, $http, 'the recommended web request option must come first');

		$I->assertDoesNotMatchRegularExpression('#href=[\'"][^\'"]*cron\.php\?token=#', $source,
			'the URL carries a secret, so it must be shown as text and never linked');
	}

	public function generatingANewTokenChangesTheCommand(AcceptanceTester $I)
	{
		$I->wantTo('replace the token and be told to copy the new command');

		$I->amOnPage(self::SETUP_PAGE);
		$before = $this->token($I, $I->grabPageSource());

		$I->submitForm('#cron-token', ['generate_pwd' => 1]);

		$after = $this->token($I, $I->grabPageSource());

		$I->assertNotSame($before, $after, 'the page must show the token it has just generated');
		$I->see('A new cron token has been generated.');
	}

	public function theUrlOnThePageRunsTheScheduler(AcceptanceTester $I)
	{
		$I->wantTo('schedule the URL the Setup tab gave me and have cron.php accept it');

		$I->amOnPage(self::SETUP_PAGE);
		$token = $this->token($I, $I->grabPageSource());

		$I->amOnPage('/cron.php?token='.$token);
		$I->assertSame(200, $I->grabResponseCode(), 'the URL shown on the page must be accepted');
		$I->assertStringContainsString('OK', $I->grabResponseBody());
		$I->assertStringNotContainsString('Refused', $I->grabResponseBody());

		$I->amOnPage('/cron.php?token='.$this->alter($token));
		$I->assertSame(403, $I->grabResponseCode(), 'a token that does not match must be refused');
		$I->assertStringContainsString('Refused', $I->grabResponseBody());
		$I->assertStringNotContainsString('OK', $I->grabResponseBody());
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $source
	 * @return string the 40 hex characters of the token shown on the page
	 */
	private function token(AcceptanceTester $I, $source)
	{
		$matches = array();

		$I->assertSame(1, preg_match(self::TOKEN_PATTERN, $source, $matches),
			'the page must show a cron URL carrying a 40 character token');

		return $matches[1];
	}

	/**
	 * @param string $token
	 * @return string $token with one character changed
	 */
	private function alter($token)
	{
		return substr($token, 0, -1).($token[strlen($token) - 1] === 'a' ? 'b' : 'a');
	}
}
