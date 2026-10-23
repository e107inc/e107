<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use Codeception\Events;

/**
 * Which page {@see \Extension\FailedStepPage} lists among a failed test's artefacts, and when it writes one.
 */
class failedStepPageTest extends \Test\Unit
{
	/** @var \Symfony\Component\EventDispatcher\EventDispatcher the extension's listeners, after those of an extension registered before it */
	private $dispatcher;

	/** @var callable|null what the earlier extension does as a test begins */
	private $earlierExtension;

	/** @var \Symfony\Component\BrowserKit\Request|null */
	private $request;

	/** @var \Symfony\Component\BrowserKit\Response|null */
	private $response;

	/** @var \Symfony\Component\DomCrawler\Crawler|null */
	private $crawler;

	protected function _before()
	{
		$module = $this->createMock('Codeception\Module\PhpBrowser');

		$browser = $this->createMock('Codeception\Lib\Connector\Guzzle');
		$browser->method('getInternalRequest')->willReturnCallback(function () { return $this->request; });
		$browser->method('getInternalResponse')->willReturnCallback(function () { return $this->response; });
		$browser->method('getCrawler')->willReturnCallback(function () { return $this->crawler; });
		$module->client = $browser;

		$suite = $this->createMock('Codeception\Suite');
		$suite->method('getModules')->willReturn(array('PhpBrowser' => $module));

		$extension = new \Extension\FailedStepPage(array(), array());
		$extension->receiveModuleContainer(new \Codeception\Event\SuiteEvent($suite));

		$this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$this->dispatcher->addListener(Events::TEST_BEFORE, function () {
			if ($this->earlierExtension)
			{
				call_user_func($this->earlierExtension);
			}
		}, 100);
		$this->dispatcher->addSubscriber($extension);
	}

	protected function _after()
	{
		foreach (glob(codecept_output_dir().'failedStepPageTestCest.*') ?: array() as $file)
		{
			unlink($file);
		}
	}

	/**
	 * A failure the test caught, as the installer's own does, is not the failure the test is reported for.
	 */
	public function testACaughtStepFailureFollowedByARealOneListsTheRealOnesPage()
	{
		$test = $this->begin($this->cest('aTest'));
		$this->reply('/install.php?stage=4', 'installer step 4');
		$this->step($test, true);
		$this->reply('/forum_post.php?f=move', 'the move page');
		$this->step($test, true);
		$this->reply('/probe.php?act=pref', 'PROBE_OK pref');
		$this->step($test, false);

		self::assertSame("<!-- http://sb1.web/forum_post.php?f=move -->\nthe move page", $this->listedPage($test, Events::TEST_FAIL));
	}

	/**
	 * A test that ends before any step, because an extension or module failed as it began, lists no page rather than the previous test's.
	 */
	public function testAnErrorBeforeAnyStepOfATestListsNoPage()
	{
		$first = $this->begin($this->cest('aTest'));
		$this->reply('/forum_post.php?f=move', 'the move page');
		$this->step($first, true);
		$this->end($first, Events::TEST_FAIL);

		$this->earlierExtension = function () { throw new \RuntimeException('an extension failed as the test began'); };
		$second = $this->cest('anotherTest');

		try
		{
			$this->begin($second);
		}
		catch (\RuntimeException $e)
		{
			self::assertSame('an extension failed as the test began', $e->getMessage());
		}

		self::assertNull($this->listedPage($second, Events::TEST_ERROR));
	}

	/**
	 * A batch of passing tests keeps no output, so a test that passes writes no page, even one that caught a step's failure.
	 */
	public function testATestThatCatchesAStepFailureAndPassesWritesNothing()
	{
		$test = $this->begin($this->cest('aTest'));
		$this->reply('/install.php?stage=4', 'installer step 4');
		$this->step($test, true);
		$this->reply('/install.php?stage=5', 'installer step 5');
		$this->step($test, false);
		$this->end($test, Events::TEST_SUCCESS);

		self::assertSame(array(), glob(codecept_output_dir().'failedStepPageTestCest.*') ?: array());
	}

	/**
	 * A request that times out, or is answered with nothing, leaves the browser holding the page before it, which is listed with its own address.
	 */
	public function testAPageFollowedByAFailingStepWhoseRequestGotNoReplyListsThatPagesOwnAddress()
	{
		$test = $this->begin($this->cest('aTest'));
		$this->reply('/forum_post.php?f=move', 'the move page');
		$this->step($test, false);
		$this->request = new \Symfony\Component\BrowserKit\Request('http://sb1.web/forum_post.php?f=move&posted', 'POST');
		$this->step($test, true);

		self::assertSame("<!-- http://sb1.web/forum_post.php?f=move -->\nthe move page", $this->listedPage($test, Events::TEST_FAIL));
	}

	/**
	 * A redirect the step followed to a request that got no reply is listed with the redirect's own address, not the one it pointed to.
	 */
	public function testARedirectWhoseFollowingRequestGotNoReplyListsTheRedirectsOwnAddress()
	{
		$test = $this->begin($this->cest('aTest'));
		$this->reply('/login.php', 'redirecting');
		$this->request = new \Symfony\Component\BrowserKit\Request('http://sb1.web/index.php', 'GET');
		$this->step($test, true);

		self::assertSame("<!-- http://sb1.web/login.php -->\nredirecting", $this->listedPage($test, Events::TEST_FAIL));
	}

	/**
	 * @param string $method
	 * @return \Codeception\Test\Cest
	 */
	private function cest($method)
	{
		return new \Codeception\Test\Cest(new failedStepPageTestCest(), $method, __FILE__);
	}

	/**
	 * @param \Codeception\Test\Cest $test
	 * @return \Codeception\Test\Cest the same test, begun as the suite begins each one
	 */
	private function begin($test)
	{
		$this->fire(Events::TEST_START, new \Codeception\Event\TestEvent($test));
		$this->fire(Events::TEST_BEFORE, new \Codeception\Event\TestEvent($test));

		return $test;
	}

	/**
	 * @param \Codeception\Test\Cest $test
	 * @param string $outcome the event the test's result is announced by
	 */
	private function end($test, $outcome)
	{
		$this->fire($outcome, $outcome === Events::TEST_SUCCESS ? new \Codeception\Event\TestEvent($test) : $this->failure($test));
		$this->fire(Events::TEST_AFTER, new \Codeception\Event\TestEvent($test));
		$this->fire(Events::TEST_END, new \Codeception\Event\TestEvent($test));
	}

	/**
	 * @param \Codeception\Test\Cest $test
	 * @param string $outcome TEST_FAIL or TEST_ERROR
	 * @return string|null what the page listed for the test holds, null when none is listed
	 */
	private function listedPage($test, $outcome)
	{
		$this->end($test, $outcome);
		$reports = $test->getMetadata()->getReports();

		return isset($reports['page at the failing step']) ? file_get_contents($reports['page at the failing step']) : null;
	}

	/**
	 * The browser gets a reply to a request, and builds a page from it as BrowserKit does.
	 *
	 * @param string $path
	 * @param string $body
	 */
	private function reply($path, $body)
	{
		$this->request = new \Symfony\Component\BrowserKit\Request('http://sb1.web'.$path, 'GET');
		$this->response = new \Symfony\Component\BrowserKit\Response($body);
		$this->crawler = new \Symfony\Component\DomCrawler\Crawler(null, $this->request->getUri());
	}

	/**
	 * @param \Codeception\Test\Cest $test
	 * @param bool $failed
	 */
	private function step($test, $failed)
	{
		$step = $this->createMock('Codeception\Step');
		$step->method('hasFailed')->willReturn($failed);

		$this->fire(Events::STEP_AFTER, new \Codeception\Event\StepEvent($test, $step));
	}

	/**
	 * @param \Codeception\Test\Cest $test
	 * @return \Codeception\Event\FailEvent
	 */
	private function failure($test)
	{
		$event = $this->createMock('Codeception\Event\FailEvent');
		$event->method('getTest')->willReturn($test);

		return $event;
	}

	/**
	 * Run an event's listeners in the order the dispatcher does, an exception from one ending the run as it does there.
	 *
	 * @param string $name
	 * @param \Codeception\Event\TestEvent|\Codeception\Event\StepEvent $event
	 */
	private function fire($name, $event)
	{
		foreach ($this->dispatcher->getListeners($name) as $listener)
		{
			call_user_func($listener, $event, $name, $this->dispatcher);
		}
	}
}

/**
 * The Cest the tests above are about.
 */
class failedStepPageTestCest
{
	public function aTest()
	{
	}

	public function anotherTest()
	{
	}
}
