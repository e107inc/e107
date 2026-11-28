<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;

/**
 * While Use SSL only is on, a page asks the browser to fetch its http:// images, scripts and stylesheets over https://, wherever the address came from.
 *
 * @see https://github.com/e107inc/e107/issues/6860
 */
class UseSslOnlyHeaderCest
{
	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
		$I->dropForumProbe();
	}

	public function anHttpsPageAsksForTheUpgradeWhileUseSslOnlyIsOn(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('ssl_enabled', 1);

		$I->assertSame('upgrade-insecure-requests', $this->policyOfAnHttpsPage($I));
	}

	public function anHttpsPageAsksForNoUpgradeWhileUseSslOnlyIsOff(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('ssl_enabled', 0);

		$I->assertSame('', $this->policyOfAnHttpsPage($I));
	}

	/**
	 * The site answers on both schemes; the certificate is self-signed.
	 *
	 * @param AcceptanceTester $I
	 * @return string the Content-Security-Policy the news page is sent with over https://
	 */
	private function policyOfAnHttpsPage(AcceptanceTester $I)
	{
		return $I->executeInGuzzle(function (Client $client)
		{
			$https = $client->getConfig('base_uri')->withScheme('https');

			return $client->request('GET', UriResolver::resolve($https, new Uri('news.php')), array(
				'verify'          => false,
				'http_errors'     => false,
				'allow_redirects' => false,
			))->getHeaderLine('Content-Security-Policy');
		});
	}
}
