<?php

/**
 * The status and the page a visitor gets where no page is served, from index.php and from error.php.
 */
class FrontControllerStatusCest
{
	const NOT_FOUND = 'Error 404 - Document Not Found';
	const FORBIDDEN = 'Error 403 - Access forbidden';

	const ROUTE_THEME = 'routelayout';
	const NOT_FOUND_BOX_IN_ITS_LAYOUT = "div.route-box[data-layout='notfound'][data-mode='error_page_404'][data-caption='Error']";

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->restoreSitePrefs();
	}

	public function anAddressNoModuleAnswersIsTheNotFoundPage(AcceptanceTester $I)
	{
		$this->seeErrorPageAtEach($I, array('/e107-tests-no-such-address', '/index.php/e107-tests-no-such-address', '/index.php?route=e107-tests-no-such-module/thing'),
			404, self::NOT_FOUND);
	}

	public function theErrorControllerAnswersWithTheStatusOfItsAction(AcceptanceTester $I)
	{
		$this->seeErrorPageAtEach($I, array('/system/error/notfound', '/system/error/404', '/index.php?route=system/error/notfound'), 404, self::NOT_FOUND);
		$this->seeErrorPageAtEach($I, array('/system/error/forbidden', '/system/error/403', '/index.php?route=system/error/forbidden'), 403, self::FORBIDDEN);
	}

	public function withPathInfoOnTheRouteIsReadFromBehindTheEntryScript(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('url_disable_pathinfo', 0);

		$this->seeErrorPageAtEach($I, array('/index.php/system/error/forbidden'), 403, self::FORBIDDEN);
		$this->seeErrorPageAtEach($I, array('/index.php/e107-tests-no-such-module/thing'), 404, self::NOT_FOUND);
		$this->seePlainTextAtEach($I, array('/index.php/system/e107-tests-no-such-controller'),
			"system/e107-tests-no-such-controller/index - Invalid controller 'e107_tests_no_such_controller'");
	}

	public function theNotFoundPageIsDrawnInOneBoxInTheLayoutGivenToItsRoute(AcceptanceTester $I)
	{
		$I->haveThemeFixture(self::ROUTE_THEME);
		$I->haveSiteTheme(self::ROUTE_THEME);

		$I->amOnPage('/e107-tests-no-such-address');

		$I->seeResponseCodeIs(404);
		$I->seeElement(self::NOT_FOUND_BOX_IN_ITS_LAYOUT);
		$I->seeNumberOfElements('div.route-box[data-mode^="error_page_"]', 1);
	}

	public function withTheErrorRedirectOnAnUnansweredAddressIsSentToTheNotFoundAddress(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('url_error_redirect', 1);
		$I->stopFollowingRedirects();

		$I->amOnPage('/e107-tests-no-such-address');

		$I->seeResponseCodeIs(302);
		$I->seeRedirectTo('system/error/404');
		$I->seeRedirectTo('type=routeError');

		$I->startFollowingRedirects();

		$this->seeErrorPageAtEach($I, array('/e107-tests-no-such-address'), 404, self::NOT_FOUND);
	}

	public function anUnknownControllerIsAnsweredInPlainTextWithStatus200(AcceptanceTester $I)
	{
		$this->seePlainTextAtEach($I, array('/system/e107-tests-no-such-controller', '/index.php?route=system/e107-tests-no-such-controller'),
			"system/e107-tests-no-such-controller/index - Invalid controller 'e107_tests_no_such_controller'");
	}

	public function anUnknownActionIsAnsweredInPlainTextWithStatus200(AcceptanceTester $I)
	{
		$this->seePlainTextAtEach($I, array('/system/error/e107-tests-no-such-action'),
			'system/error/e107-tests-no-such-action - Action "E107TestsNoSuchAction" does not exist');
	}

	public function aFrontPageThatPointsAtItselfIsAnsweredInPlainTextWithStatus200(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('frontpage/0', 'route:index/index/index');

		$this->seePlainTextAtEach($I, array('/'), 'index/index/index - Infinite loop detected while dispatching front page.');
	}

	public function aFrontPageThatPointsAtAMissingScriptIsAnsweredInPlainTextWithStatus200(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('frontpage/0', 'e107-tests-gone.php');

		$this->seePlainTextAtEach($I, array('/'), 'Bad request - destination unreachable - ./e107-tests-gone.php');
	}

	public function aFrontPageThatPointsAtAnAddressNoModuleAnswersIsTheNotFoundPage(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('frontpage/0', 'url:e107-tests-no-such-module/thing');

		$this->seeErrorPageAtEach($I, array('/'), 404, self::NOT_FOUND);
	}

	public function theErrorScriptAnswersWithTheStatusItIsAskedFor(AcceptanceTester $I)
	{
		$pages = array(
			'/error.php?400' => array(400, 'Error 400 - Bad Request'),
			'/error.php?401' => array(401, 'Error 401 - Authentication Failed'),
			'/error.php?403' => array(403, self::FORBIDDEN),
			'/error.php?404' => array(404, self::NOT_FOUND),
			'/error.php?500' => array(500, 'Error 500 - Internal server error'),
			'/error.php?999' => array(501, 'Error - Unknown (999)'),
		);

		foreach($pages as $address => $page)
		{
			$this->seeErrorPageAtEach($I, array($address), $page[0], $page[1]);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string[] $addresses
	 * @param int $status
	 * @param string $subtitle
	 * @return void
	 */
	private function seeErrorPageAtEach(AcceptanceTester $I, array $addresses, $status, $subtitle)
	{
		foreach($addresses as $address)
		{
			$I->amOnPage($address);

			$I->seeResponseCodeIs($status);
			$I->see($subtitle);
			$I->seeInSource('</html>');
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string[] $addresses
	 * @param string $text the whole body
	 * @return void
	 */
	private function seePlainTextAtEach(AcceptanceTester $I, array $addresses, $text)
	{
		foreach($addresses as $address)
		{
			$I->amOnPage($address);

			$I->seeResponseCodeIs(200);
			$I->assertSame($text, $I->grabResponseBody());
		}
	}
}
