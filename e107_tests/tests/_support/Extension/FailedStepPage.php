<?php

namespace Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

/**
 * Lists among a failed test's artefacts the page the browser held when the test's last failing step failed.
 */
class FailedStepPage extends Extension
{
	public static $events = [
		Events::TEST_START => 'startTest',
		Events::STEP_AFTER => 'afterStep',
		Events::TEST_FAIL  => 'failedTest',
		Events::TEST_ERROR => 'failedTest',
	];

	/** @var string|null the address and body of the reply the browser held when this test's last failing step failed */
	private $page;

	public function startTest(TestEvent $event)
	{
		$this->page = null;
	}

	public function afterStep(StepEvent $event)
	{
		if (!$event->getStep()->hasFailed() || !$this->hasModule('PhpBrowser'))
		{
			return;
		}

		$client = $this->getModule('PhpBrowser')->client;
		$crawler = $client ? $this->crawlerOf($client) : null;

		if ($crawler !== null)
		{
			$this->page = '<!-- '.$crawler->getUri()." -->\n".$client->getInternalResponse()->getContent();
		}
	}

	public function failedTest(FailEvent $event)
	{
		if ($this->page === null)
		{
			return;
		}

		$file = codecept_output_dir().preg_replace('#\W#', '.', Descriptor::getTestSignatureUnique($event->getTest())).'.failed-step.html';
		file_put_contents($file, $this->page);
		$event->getTest()->getMetadata()->addReport('page at the failing step', $file);
	}

	/**
	 * @param \Symfony\Component\BrowserKit\AbstractBrowser $client
	 * @return \Symfony\Component\DomCrawler\Crawler|null the page the browser built from its last reply, null before its first
	 */
	private function crawlerOf($client)
	{
		try
		{
			return $client->getCrawler();
		}
		catch (\BadMethodCallException $e)
		{
			return null;
		}
	}
}
