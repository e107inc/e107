<?php

namespace Extension;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;
use Helper\Outbound;
use Sandbox\Boundary;

/**
 * Notes when each test started and which hosts outside the stack it declared, so the suite runner can hold them against what the network boundary recorded.
 *
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class OutboundLedger extends Extension
{
	/** Before every module's _before(), so what a fixture makes e107 do counts as its test's, and after every _after(), so a declaration made there counts too. */
	public static $events = [
		Events::TEST_BEFORE => ['beforeTest', 100],
		Events::TEST_AFTER  => ['afterTest', -100],
	];

	/** @var float */
	private $started;

	public function beforeTest(TestEvent $event)
	{
		$this->started = microtime(true);
		Outbound::$expected = [];
	}

	public function afterTest(TestEvent $event)
	{
		include_once codecept_root_dir().'lib/sandbox/Boundary.php';
		$test = [
			'name'   => Descriptor::getTestFullName($event->getTest()),
			'start'  => $this->started,
			'expect' => array_values(array_unique(Outbound::$expected)),
		];
		file_put_contents(codecept_output_dir().Boundary::TESTS, json_encode($test, JSON_UNESCAPED_SLASHES)."\n", FILE_APPEND);
		Outbound::$expected = [];
	}
}
