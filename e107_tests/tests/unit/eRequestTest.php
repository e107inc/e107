<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Records what {@see eRequest} does with a route, its parameters, the base path and the legacy constants.
 */
class eRequestTest extends \Test\Unit
{
	const REPORT = 'E_REQUEST=';

	/** @var array the constant index.php defines before class2.php boots */
	private static $singleEntry = array('e_SINGLE_ENTRY' => true);

	public function testARequestWithNoRouteIsTheIndexRouteAndHasNoModule()
	{
		$request = new eRequest();

		self::assertSame('index/index/index', $request->getRoute());
		self::assertSame(array('index', 'index', 'index'), $request->getRoute(true));
		self::assertNull($request->getModule());
		self::assertNull($request->getController());
		self::assertNull($request->getAction());
		self::assertFalse($request->routed);
	}

	public function testARouteGivenToTheConstructorCountsAsRouted()
	{
		$request = new eRequest('news/list');

		self::assertSame('news/list/index', $request->getRoute());
		self::assertTrue($request->routed);
	}

	/**
	 * @dataProvider routes
	 * @param string $given
	 * @param string $expected
	 */
	public function testARouteIsReadAsModuleControllerAndAction($given, $expected)
	{
		$request = new eRequest();

		self::assertSame($request, $request->setRoute($given));
		self::assertSame($expected, $request->getRoute());
		self::assertSame(explode('/', $expected), $request->getRoute(true));
		self::assertFalse($request->routed);
	}

	/**
	 * @return array
	 */
	public function routes()
	{
		return array(
			'all three parts' => array('news/view/item', 'news/view/item'),
			'a missing action is index' => array('news/view', 'news/view/index'),
			'a missing controller is index too' => array('news', 'news/index/index'),
			'parts after the third are dropped' => array('a/b/c/d/e', 'a/b/c'),
			'outer slashes are trimmed' => array('/news/view/', 'news/view/index'),
			'an empty route is the index route' => array('', 'index/index/index'),
			'an empty middle part is index' => array('news//item', 'news/index/item'),
		);
	}

	public function testAnEmptyRouteStillNamesTheIndexModule()
	{
		$request = new eRequest();
		$request->setRoute('');

		self::assertSame('index', $request->getModule());
		self::assertSame('index', $request->getController());
		self::assertSame('index', $request->getAction());
	}

	public function testRouteNamesAreLowerCasedAndDashed()
	{
		$request = new eRequest();
		$request->setRoute('News_Items/View Item/Read-More');

		self::assertSame('news-items/view-item/read-more', $request->getRoute());
		self::assertSame('news-items', $request->getModule());
		self::assertSame('view-item', $request->getController());
		self::assertSame('read-more', $request->getAction());
	}

	public function testEachNameHasAnIdentifierForm()
	{
		$request = new eRequest();
		$request->setModule('news items')->setController('view-item')->setAction('read_more now');

		self::assertSame('news_items', $request->getModuleName());
		self::assertSame('view_item', $request->getControllerName());
		self::assertSame('ReadMoreNow', $request->getActionName());
		self::assertSame('actionReadMoreNow', $request->getActionMethodName());
	}

	public function testAnythingButLettersDigitsUnderscoreAndDashBecomesADash()
	{
		$request = new eRequest();

		self::assertSame('a-b-c--d-e-f-g', $request->sanitize('a b<c>"d\'e/f.g'));
		self::assertSame('keep_this-1', $request->sanitize('keep_this-1'));
		self::assertSame('-env', $request->setController('.env')->getController());
		self::assertSame('_env', $request->getControllerName());
	}

	public function testSanitizeTakesItsOwnPatternAndReplacement()
	{
		$request = new eRequest();

		self::assertSame('a#b#', $request->sanitize('a1b2', '/[0-9]/', '#'));
	}

	public function testRequestParametersAreKeptPerKey()
	{
		$request = new eRequest();

		self::assertSame(array(), $request->getRequestParams());
		self::assertNull($request->getRequestParam('id'));
		self::assertSame('none', $request->getRequestParam('id', 'none'));
		self::assertFalse($request->isRequestParam('id'));

		self::assertSame($request, $request->setRequestParam('id', 7));
		self::assertSame(7, $request->getRequestParam('id', 'none'));
		self::assertTrue($request->isRequestParam('id'));
	}

	public function testAParameterSetToNullReadsAsAbsent()
	{
		$request = new eRequest();
		$request->setRequestParam('id', null);

		self::assertFalse($request->isRequestParam('id'));
		self::assertSame('none', $request->getRequestParam('id', 'none'));
		self::assertSame(array('id' => null), $request->getRequestParams());
	}

	public function testSettingAllParametersReplacesTheOnesHeld()
	{
		$request = new eRequest();
		$request->setRequestParam('id', 7);

		self::assertSame($request, $request->setRequestParams(array('page' => 2)));
		self::assertSame(array('page' => 2), $request->getRequestParams());
	}

	public function testPopulatingCopiesTheParametersOverTheQuery()
	{
		$get = $_GET;
		$_GET = array('id' => 'from the address', 'kept' => 'yes');

		try
		{
			$request = new eRequest();
			$request->setRequestParams(array('id' => 7, 'page' => 2));

			self::assertSame($request, $request->populateRequestParams());
			self::assertSame(array('id' => 7, 'kept' => 'yes', 'page' => 2), $_GET);
		}
		finally
		{
			$_GET = $get;
		}
	}

	public function testTheRouteHistoryIsReadWholeByEndOrByIndex()
	{
		$request = new eRequest();

		self::assertSame(array(), $request->getRouteHistory());
		self::assertNull($request->getRouteHistory('last'));
		self::assertNull($request->getRouteHistory('first'));
		self::assertNull($request->getRouteHistory(0));

		self::assertSame($request, $request->addRouteHistory('a/b/c'));
		$request->addRouteHistory('d/e/f');

		self::assertSame(array('a/b/c', 'd/e/f'), $request->getRouteHistory());
		self::assertSame('a/b/c', $request->getRouteHistory('first'));
		self::assertSame('d/e/f', $request->getRouteHistory('last'));
		self::assertSame('d/e/f', $request->getRouteHistory(1));
		self::assertNull($request->getRouteHistory(9));
		self::assertNull($request->getRouteHistory('middle'));
		self::assertSame(1, $request->findRouteHistory('d/e/f'));
		self::assertFalse($request->findRouteHistory('x/y/z'));
	}

	public function testSettingARouteDoesNotRecordIt()
	{
		$request = new eRequest('news/list');
		$request->setRoute('news/view/item');

		self::assertSame(array(), $request->getRouteHistory());
	}

	public function testTheBasePathEndsAtTheEntryScriptUnlessPathInfoIsOff()
	{
		$restore = $this->withCorePrefs(array('url_disable_pathinfo' => 0));

		try
		{
			$request = new eRequest();

			self::assertSame('index.php', $request->singleEntry);
			self::assertSame(e_HTTP.'index.php/', $request->getBasePath());
			self::assertTrue(eRequest::isFrontPage('index.php', e_HTTP.'index.php/'));
			self::assertFalse(eRequest::isFrontPage('index.php', e_HTTP));
			self::assertTrue(eRequest::isFrontPage('front.php', e_HTTP.'front.php/'));
		}
		finally
		{
			$restore();
		}
	}

	public function testTheBasePathIsTheWebRootWhenPathInfoIsOff()
	{
		$restore = $this->withCorePrefs(array('url_disable_pathinfo' => 1));

		try
		{
			$request = new eRequest();

			self::assertSame(e_HTTP, $request->getBasePath());
			self::assertTrue(eRequest::isFrontPage('index.php', e_HTTP));
			self::assertFalse(eRequest::isFrontPage('index.php', e_HTTP.'index.php/'));
		}
		finally
		{
			$restore();
		}
	}

	public function testABasePathThatWasSetIsNotWorkedOut()
	{
		$request = new eRequest();

		self::assertSame($request, $request->setBasePath('/shop/'));
		self::assertSame('/shop/', $request->getBasePath());
	}

	public function testABasePathOnceReadIsKeptWhenThePreferenceChanges()
	{
		$restore = $this->withCorePrefs(array('url_disable_pathinfo' => 0));

		try
		{
			$request = new eRequest();
			$read = $request->getBasePath();
			e107::getConfig()->set('url_disable_pathinfo', 1);

			self::assertSame(e_HTTP.'index.php/', $read);
			self::assertSame($read, $request->getBasePath());
		}
		finally
		{
			$restore();
		}
	}

	public function testTheRequestInfoDefaultsToTheRequestedPath()
	{
		$request = new eRequest();

		self::assertSame(e_REQUEST_HTTP, $request->getRequestInfo());
		self::assertSame($request, $request->setRequestInfo('/shop/cart'));
		self::assertSame('/shop/cart', $request->getRequestInfo());
	}

	/**
	 * @dataProvider pathInfos
	 * @param string $basePath
	 * @param string $requestInfo
	 * @param string $expected
	 */
	public function testThePathInfoIsTheRequestInfoPastTheBasePath($basePath, $requestInfo, $expected)
	{
		$request = new eRequest();
		$request->setBasePath($basePath)->setRequestInfo($requestInfo);

		self::assertSame($expected, $request->getPathInfo());
	}

	/**
	 * @return array
	 */
	public function pathInfos()
	{
		return array(
			'a path under the web root' => array('/', '/news/list', 'news/list'),
			'a path under the entry script' => array('/index.php/', '/index.php/news/list/all', 'news/list/all'),
			'a path under a subdirectory' => array('/shop/', '/shop/news', 'news'),
			'the base path itself' => array('/index.php/', '/index.php/', ''),
			'the entry script alone' => array('/', '/index.php', ''),
			'the entry script with a trailing slash' => array('/', '/index.php/', ''),
		);
	}

	/**
	 * Recorded, not endorsed: the request info is cut by the base path's length without being compared with it.
	 */
	public function testARequestInfoOutsideTheBasePathLosesAsManyCharactersAllTheSame()
	{
		$request = new eRequest();
		$request->setBasePath('/index.php/')->setRequestInfo('/system/error/forbidden');

		self::assertSame('or/forbidden', $request->getPathInfo());
	}

	public function testAPathInfoThatWasSetIsNotWorkedOutAgain()
	{
		$request = new eRequest();
		$request->setBasePath('/')->setRequestInfo('/news/list');

		self::assertSame($request, $request->setPathInfo('forum/view'));
		self::assertSame('forum/view', $request->getPathInfo());
	}

	public function testTheDispatchedFlagStartsOffAndIsCastToABoolean()
	{
		$request = new eRequest();

		self::assertFalse($request->isDispatched());
		self::assertSame($request, $request->setDispatched(1));
		self::assertTrue($request->isDispatched());
		self::assertFalse($request->setDispatched('')->isDispatched());
	}

	public function testTheLegacyQueryStringIsDecodedStrippedOfBracesAndEscaped()
	{
		$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
		$_SERVER['QUERY_STRING'] = 'a=1&b={x}%7By%7D&c=<i>&d=%22q%22';

		try
		{
			self::assertSame('a=1&amp;b=xy&amp;c=&amp;lt;i&amp;gt;&amp;d=&amp;quot;q&amp;quot;', eRequest::getQueryString());

			$_SERVER['QUERY_STRING'] = '';
			self::assertSame('', eRequest::getQueryString());
		}
		finally
		{
			if($query === null)
			{
				unset($_SERVER['QUERY_STRING']);
			}
			else
			{
				$_SERVER['QUERY_STRING'] = $query;
			}
		}
	}

	public function testOnTheSingleEntryPointTheLegacyConstantsWaitForTheRequest()
	{
		$report = $this->legacyReport(self::$singleEntry, "\$_SERVER['QUERY_STRING'] = 'id=7&name=a%20b'; \$_GET = array('kept' => 'yes'); ",
			"\$request->setLegacyQstring()->setLegacyPage();");

		self::assertSame(array('e_QUERY' => false, 'e_PAGE' => false), $report['before']);
		self::assertSame('id=7&amp;name=a b', $report['e_QUERY']);
		self::assertSame('index.php', $report['e_PAGE']);
		self::assertSame('id=7&amp;name=a b', $report['QUERY_STRING']);
		self::assertSame(array('kept' => 'yes', 'id' => '7', 'name' => 'a b'), $report['GET']);
	}

	public function testALegacyQueryStringWithoutAnEqualsSignIsNotReadIntoTheQuery()
	{
		$report = $this->legacyReport(self::$singleEntry, "\$_GET = array('kept' => 'yes'); ", "\$request->setLegacyQstring('extend.2')->setLegacyPage();");

		self::assertSame('extend.2', $report['e_QUERY']);
		self::assertSame('extend.2', $report['QUERY_STRING']);
		self::assertSame(array('kept' => 'yes'), $report['GET']);
	}

	public function testTheLegacyPageIsTheScriptTheRouteIncludes()
	{
		$report = $this->legacyReport(self::$singleEntry, "eFront::isLegacy('{e_BASE}news.php'); ", "\$request->setLegacyQstring('')->setLegacyPage();");

		self::assertSame('news.php', $report['e_PAGE']);
		self::assertSame('', $report['e_QUERY']);
	}

	public function testALegacyPageGivenByNameWinsOverTheRoute()
	{
		$report = $this->legacyReport(self::$singleEntry, "eFront::isLegacy('{e_BASE}news.php'); ", "\$request->setLegacyQstring('')->setLegacyPage('{e_PLUGIN}forum/forum.php');");

		self::assertSame('forum.php', $report['e_PAGE']);
	}

	public function testLegacyConstantsThatExistAreNeitherDefinedAgainNorFollowedByTheQuery()
	{
		$report = $this->legacyReport(array(), "error_reporting(E_ALL); \$_SERVER['QUERY_STRING'] = 'untouched=1'; \$_GET = array('kept' => 'yes'); ",
			"\$same = \$request->setLegacyQstring('other=2')->setLegacyPage('other.php') === \$request;");

		self::assertSame(array('e_QUERY' => true, 'e_PAGE' => true), $report['before']);
		self::assertSame('untouched=1', $report['QUERY_STRING']);
		self::assertSame(array('kept' => 'yes'), $report['GET']);
		self::assertStringNotContainsString('already defined', $report['printed']);
	}

	/**
	 * Runs $act on a new request in a booted child and reports the legacy constants and the query it left.
	 *
	 * @param array $constants defined before class2.php boots; {@see eRequestTest::$singleEntry} leaves e_QUERY and e_PAGE for the request to define, as index.php does
	 * @param string $arrange statements run before the request is built
	 * @param string $act statements run on $request
	 * @return array before (which constants existed), e_QUERY, e_PAGE, QUERY_STRING, GET and printed (everything the child wrote)
	 */
	private function legacyReport(array $constants, $arrange, $act)
	{
		list($output) = $this->runInBootedCliDefining($constants,
			$arrange."\$request = new eRequest(); "
			."\$before = array('e_QUERY' => defined('e_QUERY'), 'e_PAGE' => defined('e_PAGE')); "
			.$act
			." echo \"\\n".self::REPORT."\", json_encode(array('before' => \$before, 'e_QUERY' => e_QUERY, 'e_PAGE' => e_PAGE, "
			."'QUERY_STRING' => \$_SERVER['QUERY_STRING'], 'GET' => \$_GET)), \"\\n\";",
			self::BUFFERED);

		$report = self::reportedAfter($output, self::REPORT, 'the child that set the legacy constants');
		$report['printed'] = implode("\n", $output);

		return $report;
	}
}
