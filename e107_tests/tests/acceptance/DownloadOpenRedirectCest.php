<?php

/**
 * GHSA-wcc5-8jrf-6q26: the download plugin's request handler must not be an open
 * redirect. request.php used to 302 to any user-supplied http(s)/ftp URL placed in
 * the query string (request.php?https://evil.example), enabling phishing under the
 * trust of the site's domain. The protocol-prefix redirect branch was removed;
 * legitimate external downloads still flow through the admin-configured download_url
 * served by numeric id, never the raw query string.
 */
class DownloadOpenRedirectCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	public function doesNotRedirectToExternalHost(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to open-redirect from the download request handler (GHSA-wcc5-8jrf-6q26)');

		// .invalid keeps a regression from emitting real outbound traffic; stop
		// following redirects so the 302 itself is inspected rather than chased.
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/download/request.php?https://open-redirect-canary.invalid/phish');
		$I->seeNoRedirectTo('open-redirect-canary.invalid');
	}
}
