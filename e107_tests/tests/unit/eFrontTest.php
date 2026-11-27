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
 * Records what {@see eFront} and the dispatch loop under it do, on the frontfixture module that fixtures/FrontControllerFixture.php defines.
 */
class eFrontTest extends \Test\Unit
{
	const REPORT = 'E_FRONT=';

	/** @var string statements that define an error controller in the override location, with a not-found action and no other */
	private static $override = "class override_system_error_controller extends eController { public function actionNotfound() { return 'the not-found page of an override'; } } ";

	/** @var callable */
	private $restoreUrlConfig;

	/** @var string */
	private $legacy;

	/** @var array the query, which a dispatched action's request parameters are copied into */
	private $query;

	protected function _before()
	{
		require_once(__DIR__.'/fixtures/FrontControllerFixture.php');

		$this->restoreUrlConfig = $this->withUrlConfig(array('frontfixture' => 'core'));
		$this->legacy = eFront::isLegacy();
		$this->query = $_GET;
		eFront::isLegacy('');
		$_GET = array();
		FrontControllerFixture::$calls = array();
	}

	protected function _after()
	{
		$_GET = $this->query;
		eFront::isLegacy($this->legacy);
		call_user_func($this->restoreUrlConfig);
	}

	public function testTheFrontControllerIsOneSharedObject()
	{
		self::assertSame(eFront::instance(), eFront::instance());
		self::assertSame(e107::getInstance(), eFront::app());
	}

	public function testInitGivesItARequestADispatcherARouterAndTheSharedResponse()
	{
		$front = $this->front();

		self::assertSame($front, $front->init());
		self::assertInstanceOf('eRequest', $front->getRequest());
		self::assertInstanceOf('eDispatcher', $front->getDispatcher());
		self::assertInstanceOf('eRouter', $front->getRouter());
		self::assertSame(e107::getSingleton('eResponse'), $front->getResponse());
		self::assertFalse($front->getRequest()->routed);
	}

	public function testTheLegacyScriptIsOneValueForTheWholeRequest()
	{
		self::assertSame('', eFront::isLegacy());
		self::assertSame('news.php', eFront::isLegacy('news.php'));
		self::assertSame('news.php', eFront::isLegacy());
		self::assertSame('news.php', eFront::isLegacy(null));
		self::assertSame('', eFront::isLegacy(''));
	}

	public function testALegacyScriptNamedWithAPathConstantIsResolved()
	{
		self::assertSame(e107::getParser()->replaceConstants('{e_PLUGIN}forum/forum.php'), eFront::isLegacy('{e_PLUGIN}forum/forum.php'));
		self::assertStringEndsWith('forum/forum.php', eFront::isLegacy());
		self::assertStringNotContainsString('{', eFront::isLegacy());
	}

	public function testDispatchCallsTheControllerHooksAroundTheAction()
	{
		$request = new eRequest('frontfixture/lifecycle/index');
		$this->front($request, new eResponse())->dispatch();

		self::assertSame(array('lifecycle:init', 'lifecycle:preAction', 'lifecycle:actionIndex', 'lifecycle:postAction', 'lifecycle:shutdown'),
			FrontControllerFixture::$calls);
		self::assertTrue($request->isDispatched());
		self::assertSame('frontfixture/lifecycle/index', $request->getRoute());
		self::assertSame(array(), $request->getRouteHistory());
	}

	public function testTheBodyIsWhatTheActionReturnedThenWhatItPrinted()
	{
		$response = new eResponse();
		$this->front(new eRequest('frontfixture/lifecycle/index'), $response)->dispatch();

		self::assertSame('returnedprinted', $response->getBody());
	}

	public function testTheRequestParametersReachTheActionThroughTheQuery()
	{
		$_GET = array('kept' => 'yes');
		$request = new eRequest('frontfixture/lifecycle/query');
		$request->setRequestParams(array('id' => 7));
		$response = new eResponse();
		$this->front($request, $response)->dispatch();

		self::assertSame('{"parameters":{"id":7},"query":{"kept":"yes","id":7}}', $response->getBody());
	}

	public function testAControllerWritesToTheResponseThroughItsHelpers()
	{
		$response = new eResponse();
		$this->front(new eRequest('frontfixture/lifecycle/helpers'), $response)->dispatch();

		self::assertSame('<b>Bold</b> title &raquo; Plain', $response->getTitle());
		self::assertSame('Bold title - Meta', $response->getMetaTitle());
		self::assertSame('About', $response->getMetaDescription());
		self::assertSame('added', $response->getBody());
	}

	public function testAForwardRunsTheNextActionInTheSameDispatch()
	{
		$request = new eRequest('frontfixture/forward/action');
		$request->setRequestParams(array('id' => 7));
		$response = new eResponse();
		$this->front($request, $response)->dispatch();

		self::assertSame(array('forward:init', 'forward:preAction', 'forward:actionAction', 'forward:shutdown',
			'forward:init', 'forward:preAction', 'forward:actionTarget', 'forward:postAction', 'forward:shutdown'),
			FrontControllerFixture::$calls);
		self::assertSame('before the forward, target reached', $response->getBody());
		self::assertSame('frontfixture/forward/target', $request->getRoute());
		self::assertSame(array('frontfixture/forward/action'), $request->getRouteHistory());
		self::assertSame(array(), $request->getRequestParams());
		self::assertTrue($request->isDispatched());
	}

	/**
	 * @dataProvider forwards
	 * @param string $action the forward controller's action
	 * @param string $route where the request ends up
	 * @param string $body what the target wrote; the query still holds what the forwarding action's parameters put there
	 */
	public function testAForwardNamesAnActionAControllerOrAModule($action, $route, $body)
	{
		$request = new eRequest('frontfixture/forward/'.$action);
		$request->setRequestParams(array('held' => 1));
		$response = new eResponse();
		$this->front($request, $response)->dispatch();

		self::assertSame($route, $request->getRoute());
		self::assertSame($body, $response->getBody());
	}

	/**
	 * @return array
	 */
	public function forwards()
	{
		return array(
			'an action of the same controller' => array('action', 'frontfixture/forward/target', 'before the forward, target reached'),
			'a controller and action of the same module' => array('controller', 'frontfixture/lifecycle/index', 'returnedprinted'),
			'a module, controller and action, with new parameters' => array('module', 'frontfixture/lifecycle/query', '{"parameters":{"id":7},"query":{"held":1,"id":7}}'),
			'an asterisk in a three part route keeps the module and controller' => array('wildcard', 'frontfixture/forward/target', 'target reached'),
			'parameters given as a query string' => array('string', 'frontfixture/lifecycle/query', '{"parameters":{"id":"7","page":"2"},"query":{"held":1,"id":"7","page":"2"}}'),
			'false keeps the parameters held' => array('keep', 'frontfixture/lifecycle/query', '{"parameters":{"held":1},"query":{"held":1}}'),
		);
	}

	/**
	 * Recorded, not endorsed: a two part route tests its second part for the asterisk and sets the controller from its first, and a longer route is not a forward at all.
	 *
	 * @dataProvider oddForwards
	 * @param string $to
	 * @param string $route where the request ends up
	 * @param bool $dispatched whether the request still counts as dispatched, so that nothing follows
	 */
	public function testAForwardOfTwoPartsWithAnAsteriskOrOfMoreThanThreePartsGoesAstray($to, $route, $dispatched)
	{
		FrontControllerFixture::$forwardTo = $to;
		$request = new eRequest('frontfixture/forward/to');
		$controller = new core_frontfixture_forward_controller($request, new eResponse());

		$controller->run();

		self::assertSame($route, $request->getRoute());
		self::assertSame($dispatched, $request->isDispatched());
	}

	/**
	 * @return array
	 */
	public function oddForwards()
	{
		return array(
			'an asterisk for the controller becomes the controller' => array('*/target', 'frontfixture/-/target', false),
			'an asterisk for the action keeps the controller and becomes the action' => array('lifecycle/*', 'frontfixture/forward/-', false),
			'four parts are dropped' => array('frontfixture/lifecycle/index/extra', 'frontfixture/forward/to', true),
		);
	}

	public function testAForwardFromInitSkipsTheActionAndItsHooks()
	{
		$request = new eRequest('frontfixture/early/index');
		$this->front($request, new eResponse())->dispatch();

		self::assertSame(array('early:init', 'early:shutdown',
			'lifecycle:init', 'lifecycle:preAction', 'lifecycle:actionIndex', 'lifecycle:postAction', 'lifecycle:shutdown'),
			FrontControllerFixture::$calls);
		self::assertSame('frontfixture/lifecycle/index', $request->getRoute());
	}

	public function testAForwardFromPreActionSkipsTheActionAndPostAction()
	{
		$this->front(new eRequest('frontfixture/gate/index'), new eResponse())->dispatch();

		self::assertSame(array('gate:init', 'gate:preAction', 'gate:shutdown',
			'lifecycle:init', 'lifecycle:preAction', 'lifecycle:actionIndex', 'lifecycle:postAction', 'lifecycle:shutdown'),
			FrontControllerFixture::$calls);
	}

	public function testTheHundredAndFirstTurnOfTheLoopIsRefused()
	{
		$front = $this->front(new eRequest('frontfixture/forward/loop'), new eResponse());

		try
		{
			$front->dispatch();
			self::fail('A route that forwards to itself was dispatched without end.');
		}
		catch(eException $refused)
		{
			self::assertSame('Too much dispatch loops', $refused->getMessage());
			self::assertSame(1, $refused->getCode());
		}

		self::assertSame(100, count(array_keys(FrontControllerFixture::$calls, 'forward:actionLoop', true)));
	}

	public function testALegacyRouteIsLeftToTheCaller()
	{
		eFront::isLegacy('news.php');
		$request = new eRequest('frontfixture/lifecycle/index');
		$response = new eResponse();
		$this->front($request, $response)->dispatch();

		self::assertSame(array(), FrontControllerFixture::$calls);
		self::assertSame('', $response->getBody());
		self::assertTrue($request->isDispatched());
	}

	public function testDispatchMakesTheResponseAndDispatcherItLacks()
	{
		$front = $this->front(new eRequest('frontfixture/lifecycle/index'));
		$front->dispatch();

		self::assertInstanceOf('eDispatcher', $front->getDispatcher());
		self::assertInstanceOf('eResponse', $front->getResponse());
		self::assertSame('returnedprinted', $front->getResponse()->getBody());
	}

	/**
	 * Recorded, not endorsed: the branch for a response that was passed in tests the request and stores the request again.
	 */
	public function testDispatchKeepsTheRequestAndDispatcherItIsGivenButNotTheResponse()
	{
		$front = $this->front();
		$request = new eRequest('frontfixture/lifecycle/index');
		$response = new eResponse();
		$dispatcher = new eDispatcher();

		$front->dispatch($request, $response, $dispatcher);

		self::assertSame($request, $front->getRequest());
		self::assertSame($dispatcher, $front->getDispatcher());
		self::assertNull($front->getResponse());
		self::assertSame('returnedprinted', $response->getBody());
	}

	public function testARequestPassedToDispatchIsUsedOnceAndTheOneHeldIsKept()
	{
		$held = new eRequest('frontfixture/forward/target');
		$front = $this->front($held, new eResponse());
		$passed = new eRequest('frontfixture/lifecycle/index');

		$front->dispatch($passed);

		self::assertSame($held, $front->getRequest());
		self::assertTrue($passed->isDispatched());
		self::assertFalse($held->isDispatched());
		self::assertSame('returnedprinted', $front->getResponse()->getBody());
	}

	public function testRunTakesARequestInPlaceOfTheOneHeld()
	{
		$front = $this->front(new eRequest('frontfixture/forward/target'), new eResponse());
		$request = new eRequest('frontfixture/lifecycle/index');

		$front->run($request);

		self::assertSame($request, $front->getRequest());
		self::assertSame('returnedprinted', $front->getResponse()->getBody());
	}

	public function testRunTakesARouteForARequestThatIsAlreadyRouted()
	{
		$request = new eRequest('frontfixture/forward/target');
		$front = $this->front($request, new eResponse());

		$front->run('frontfixture/lifecycle/index');

		self::assertSame('frontfixture/lifecycle/index', $request->getRoute());
		self::assertSame('returnedprinted', $front->getResponse()->getBody());
	}

	public function testRunWithARouteLosesItToTheAddressUnlessTheRequestIsAlreadyRouted()
	{
		$request = new eRequest();
		$request->setPathInfo('frontfixture/forward/target');
		$front = $this->front($request, new eResponse());

		$front->run('frontfixture/lifecycle/index');

		self::assertSame('frontfixture/forward/target', $request->getRoute());
		self::assertSame('target reached', $front->getResponse()->getBody());
	}

	public function testAControllerRunsOnItsOwnAndHandsBackItsResponse()
	{
		$response = new eResponse();
		$controller = new core_frontfixture_lifecycle_controller(new eRequest('frontfixture/lifecycle/index'), $response);

		ob_start();
		$returned = $controller->run();
		$printed = ob_get_clean();

		self::assertSame($response, $returned);
		self::assertSame('returned', $response->getBody());
		self::assertSame('printed', $printed);
	}

	/**
	 * @dataProvider failures
	 * @param string $route
	 * @param string $text what the front controller used to print in place of a page
	 * @param string[] $printed what the action printed before it failed
	 * @param string $landed the error route the request ends on
	 * @param int $status
	 * @param string $subtitle the LAN constant the page's subtitle comes from
	 */
	public function testAFailedDispatchIsForwardedToAnErrorPageAndWhatItPrintedIsDropped($route, $text, array $printed, $landed, $status, $subtitle)
	{
		$report = $this->runRouteInChild($route);

		self::assertTrue($report['returned']);
		self::assertSame($landed, $report['route']);
		self::assertSame($route, end($report['history']));
		$this->assertErrorPage($report, $status, $subtitle);
		self::assertSame(array(htmlspecialchars($route.' - '.$text, ENT_QUOTES, 'UTF-8')), $report['debug']);

		foreach(array_merge(array($text), $printed) as $dropped)
		{
			self::assertStringNotContainsString($dropped, $this->printedBesidesTheReport($report));
			self::assertStringNotContainsString($dropped, $report['body']);
		}
	}

	/**
	 * @return array
	 */
	public function failures()
	{
		$notFound = array('system/error/notfound', 404, 'LAN_ERROR_7');
		$serverError = array('system/error/servererror', 500, 'LAN_ERROR_10');

		return array(
			'an unknown controller' => array_merge(array('frontfixture/missing/index', "Invalid controller 'missing'", array()), $notFound),
			'an unknown action' => array_merge(array('frontfixture/lifecycle/missing', 'Action "Missing" does not exist', array()), $notFound),
			'an action that throws with the code 404' => array_merge(array('frontfixture/lifecycle/gone', 'The fixture has no such item', array()), $notFound),
			'an action that throws after printing' => array_merge(array('frontfixture/lifecycle/giveup', 'The fixture gave up', array('half a page')), $serverError),
			'an action that throws with a buffer of its own left open' => array_merge(array('frontfixture/lifecycle/buffered', 'The fixture gave up', array('half a page', 'and a buffer left open')), $serverError),
			'an action that throws with a buffer PHP will not remove left open' => array_merge(array('frontfixture/lifecycle/stubborn', 'The fixture gave up', array()), $serverError),
			'an action that throws markup, kept as text' => array_merge(array('frontfixture/lifecycle/markup', '<b>gave</b> "up"', array()), $serverError),
			'a loop without end' => array_merge(array('frontfixture/forward/loop', 'Too much dispatch loops', array()), $serverError),
			'a class that is not a controller' => array_merge(array('frontfixture/stranger/index', 'Controller core_frontfixture_stranger_controller is not an instance of eController', array()), $serverError),
		);
	}

	public function testAFailureWhileTheAddressIsRoutedIsForwardedToTheServerErrorPageAndNotToItsLegacyScript()
	{
		$report = $this->dispatchInChild("\$front->setRouter(new FrontThrowingRouterFixture()); ".self::running('new eRequest()'));

		self::assertTrue($report['returned']);
		self::assertSame('system/error/servererror', $report['route']);
		self::assertSame('', $report['legacy']);
		$this->assertErrorPage($report, 500, 'LAN_ERROR_10');
	}

	public function testANotFoundRouteThatCannotBeDispatchedIsAServerError()
	{
		$report = $this->runRouteInChild('frontfixture/lifecycle/missing', "\$front->getRouter()->notFoundRoute = 'frontfixture/missing/index'; ");

		self::assertTrue($report['returned']);
		self::assertSame('system/error/servererror', $report['route']);
		self::assertSame(array('frontfixture/lifecycle/missing', 'frontfixture/missing/index'), $report['history']);
		$this->assertErrorPage($report, 500, 'LAN_ERROR_10');
	}

	public function testANotFoundRouteTheRouterChoseIsNotTriedASecondTimeWhenItFails()
	{
		$report = $this->runUnansweredAddressInChild("e107::getConfig()->setPref('url_config/system', ''); ");

		self::assertFalse($report['returned']);
		self::assertSame(500, $report['status']);
		self::assertSame(array('system/error/notfound', 'system/error/notfound', 'system/error/servererror'), $report['history']);
		self::assertSame(array(htmlspecialchars("system/error/notfound - Invalid controller 'error'", ENT_QUOTES, 'UTF-8')), $report['debug']);
	}

	public function testAServerErrorPageThatFailsEndsTheRequestInPlainTextAndIsNotAnsweredWithTheNotFoundPage()
	{
		$report = $this->runRouteInChild('frontfixture/lifecycle/giveup',
			"\$front->getRouter()->notFoundRoute = 'frontfixture/lifecycle/index'; e107::getConfig()->setPref('url_config/system', ''); ");

		self::assertFalse($report['returned']);
		self::assertSame(500, $report['status']);
		self::assertContains("system/error/servererror - Invalid controller 'error'", $report['printed']);
		self::assertSame('system/error/servererror', $report['route']);
		self::assertSame('', $report['body']);
		self::assertSame(array('frontfixture/lifecycle/giveup - The fixture gave up'), $report['debug']);
	}

	public function testAnotherRequestOnTheSameFrontControllerStartsAfresh()
	{
		$report = $this->runRouteInChild('frontfixture/lifecycle/missing',
			"\$front->getRouter()->notFoundRoute = 'frontfixture/lifecycle/index'; ".self::running("new eRequest('frontfixture/missing/index')"));

		self::assertTrue($report['returned']);
		self::assertSame('frontfixture/lifecycle/index', $report['route']);
		self::assertSame(array('frontfixture/lifecycle/missing'), $report['history']);
		self::assertFalse($report['status']);
	}

	public function testAnOverrideErrorControllerStillAnswersForARouteThatNamesNothing()
	{
		$report = $this->runRouteInChild('frontfixture/missing/index', self::$override);

		self::assertTrue($report['returned']);
		self::assertSame('system/error/notfound', $report['route']);
		self::assertSame('the not-found page of an override', $report['body']);
		self::assertFalse($report['status']);
	}

	/**
	 * @dataProvider routesToTheServerErrorPage
	 * @param string $route
	 */
	public function testAnOverrideErrorControllerWithNoServerErrorActionLeavesTheLastResort($route)
	{
		$report = $this->runRouteInChild($route, self::$override);

		self::assertFalse($report['returned']);
		self::assertSame(500, $report['status']);
		self::assertContains('system/error/servererror - Action "Servererror" does not exist', $report['printed']);
	}

	/**
	 * @return array
	 */
	public function routesToTheServerErrorPage()
	{
		return array(
			'an action that throws' => array('frontfixture/lifecycle/giveup'),
			'the page\'s own route' => array('system/error/servererror'),
		);
	}

	public function testTheErrorControllerWritesToTheResponseItWasGiven()
	{
		$report = $this->dispatchInChild("\$class = new ReflectionClass('eFront'); \$own = \$class->newInstanceWithoutConstructor(); "
			."\$response = new eResponse(); \$own->setRouter(\$front->getRouter())->setRequest(new eRequest('system/error/forbidden'))->setResponse(\$response); "
			."\$own->dispatch(); echo \"\\nOWN=\", json_encode(array(\$response->getTitle(), \$response->getRenderMod(), strpos(\$response->getBody(), LAN_ERROR_4) !== false)), \"\\n\";");

		self::assertSame(array($report['LAN_ERROR'], 'error_page_403', true), self::reportedAfter($report['printed'], 'OWN=', 'the child with a front controller of its own'));
		self::assertSame(403, $report['status']);
	}

	public function testAModuleWithNoIndexControllerIsRedirectedToTheAdminScriptByItsRelativePath()
	{
		$report = $this->runRouteInChild('frontfixture/index/index', "e107::setRegistry('core/e107/singleton/redirection', new FrontRedirectionFixture()); ");
		$redirect = self::reportedAfter($report['printed'], 'REDIRECT=', 'the child that was redirected');

		self::assertSame($report['e_ADMIN'].'admin.php', $redirect);
		self::assertStringStartsNotWith('/', $redirect);
		self::assertStringNotContainsString('://', $redirect);
		self::assertFalse($report['returned']);
		self::assertStringNotContainsString('Invalid controller', implode("\n", $report['printed']));
	}

	public function testAnAddressNoModuleAnswersIsRoutedToTheNotFoundPage()
	{
		$report = $this->runUnansweredAddressInChild();

		self::assertTrue($report['returned']);
		self::assertSame('system/error/notfound', $report['route']);
		self::assertSame(array('system/error/notfound'), $report['history']);
		$this->assertErrorPage($report, 404, 'LAN_ERROR_7');
		self::assertSame('', $report['legacy']);
		self::assertSame(array(), $report['debug']);
	}

	/**
	 * @dataProvider requestsNothingAnswers
	 * @param string|null $route null for an address no module answers
	 */
	public function testTheNotFoundRouteIsTheRoutersToRename($route)
	{
		$rename = "\$front->getRouter()->notFoundRoute = 'frontfixture/lifecycle/index'; ";
		$report = $route === null ? $this->runUnansweredAddressInChild($rename) : $this->runRouteInChild($route, $rename);

		self::assertSame('frontfixture/lifecycle/index', $report['route']);
		self::assertSame('returnedprinted', $report['body']);
		self::assertFalse($report['status']);
	}

	/**
	 * @return array
	 */
	public function requestsNothingAnswers()
	{
		return array(
			'an address no module answers' => array(null),
			'an unknown controller' => array('frontfixture/missing/index'),
		);
	}

	/**
	 * @dataProvider errorRoutes
	 * @param string $route
	 * @param string $landed the route the request ends on
	 * @param int $status
	 * @param string $subtitle the LAN constant the page's subtitle comes from
	 */
	public function testTheSystemErrorControllerAnswersWithAStatusAndAPage($route, $landed, $status, $subtitle)
	{
		$report = $this->runRouteInChild($route);

		self::assertSame($landed, $report['route']);
		self::assertSame($route === $landed ? array() : array($route), $report['history']);
		$this->assertErrorPage($report, $status, $subtitle);
	}

	/**
	 * @return array
	 */
	public function errorRoutes()
	{
		return array(
			'not found' => array('system/error/notfound', 'system/error/notfound', 404, 'LAN_ERROR_7'),
			'404 forwards to not found' => array('system/error/404', 'system/error/notfound', 404, 'LAN_ERROR_7'),
			'forbidden' => array('system/error/forbidden', 'system/error/forbidden', 403, 'LAN_ERROR_4'),
			'403 forwards to forbidden' => array('system/error/403', 'system/error/forbidden', 403, 'LAN_ERROR_4'),
			'server error' => array('system/error/servererror', 'system/error/servererror', 500, 'LAN_ERROR_10'),
		);
	}

	/**
	 * @param array $report as {@see eFrontTest::dispatchInChild()} returns it
	 * @return string what the child wrote, less the report line, which repeats the debug messages
	 */
	private function printedBesidesTheReport(array $report)
	{
		return implode("\n", preg_grep('/^'.preg_quote(self::REPORT, '/').'/', $report['printed'], PREG_GREP_INVERT));
	}

	/**
	 * @param array $report as {@see eFrontTest::dispatchInChild()} returns it
	 * @param int $status
	 * @param string $subtitle the LAN constant the page's subtitle comes from
	 * @return void
	 */
	private function assertErrorPage(array $report, $status, $subtitle)
	{
		self::assertSame($status, $report['status']);
		self::assertSame($report['LAN_ERROR'], $report['title']);
		self::assertSame('error_page_'.$status, $report['mode']);
		self::assertStringContainsString($report[$subtitle], $report['body']);
	}

	/**
	 * @param eRequest|null $request
	 * @param eResponse|null $response
	 * @return eFront one apart from the site's, holding the router {@see \Test\Unit::withUrlConfig()} built
	 */
	private function front($request = null, $response = null)
	{
		$class = new ReflectionClass('eFront');
		$front = $class->newInstanceWithoutConstructor();
		$front->setRouter(e107::getUrl()->router());

		if($request !== null)
		{
			$front->setRequest($request);
		}

		if($response !== null)
		{
			$front->setResponse($response);
		}

		return $front;
	}

	/**
	 * @param string $route
	 * @param string $arrange statements run first
	 * @return array what {@see eFrontTest::dispatchInChild()} reports once the site's front controller has run a request already routed to $route
	 */
	private function runRouteInChild($route, $arrange = '')
	{
		return $this->dispatchInChild($arrange.self::running("new eRequest('".$route."')"));
	}

	/**
	 * @param string $arrange statements run first
	 * @return array what {@see eFrontTest::dispatchInChild()} reports once the site's front controller has run a request for an address no module answers
	 */
	private function runUnansweredAddressInChild($arrange = '')
	{
		return $this->dispatchInChild($arrange."\$request = new eRequest(); \$request->setPathInfo('no-such-module/thing'); ".self::running('$request'));
	}

	/**
	 * @param string $request a PHP expression for the request
	 * @return string statements that make the site's front controller run that request
	 */
	private static function running($request)
	{
		return "\$front->setRequest(".$request."); \$front->run(); ";
	}

	/**
	 * Runs $act on the site's front controller in a booted child, where the dispatcher may end the request, and reports what was left behind.
	 *
	 * @param string $act statements run on $front once it is initialised and the frontfixture module is routable
	 * @return array printed (the lines written), returned (whether $act came back), status (false when none was set), route, history, title, mode, body, legacy, debug (the debug system messages), e_ADMIN and the error page's LAN strings
	 */
	private function dispatchInChild($act)
	{
		$php = "require_once(".var_export(__DIR__.'/fixtures/FrontControllerFixture.php', true)."); "
			."e107::coreLan('error'); "
			."e107::getConfig()->setPref('url_main_module', '')->setPref('url_error_redirect', 0); "
			."\$front = eFront::instance(); \$front->init(); "
			."e107::getConfig()->setPref('url_config/frontfixture', 'core'); "
			."\$returned = false; "
			."register_shutdown_function(function () use (\$front, &\$returned) { "
			."\$request = \$front->getRequest(); \$response = \$front->getResponse(); "
			."echo \"\\n".self::REPORT."\", json_encode(array('returned' => \$returned, 'status' => http_response_code(), "
			."'route' => \$request->getRoute(), 'history' => \$request->getRouteHistory(), 'title' => \$response->getTitle(), "
			."'mode' => \$response->getRenderMod(), 'body' => \$response->getBody(), 'legacy' => eFront::isLegacy(), "
			."'debug' => e107::getMessage()->get('debug', 'default', true) ?: array(), "
			."'e_ADMIN' => e_ADMIN, 'LAN_ERROR' => LAN_ERROR, 'LAN_ERROR_4' => LAN_ERROR_4, 'LAN_ERROR_7' => LAN_ERROR_7, 'LAN_ERROR_10' => LAN_ERROR_10)), \"\\n\"; }); "
			."echo \"\\n\"; "
			.$act
			." \$returned = true;";

		list($output) = $this->runInBootedCli($php, self::BUFFERED);
		$report = self::reportedAfter($output, self::REPORT, 'the child that ran the front controller');
		$report['printed'] = $output;

		return $report;
	}
}
