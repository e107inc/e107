<?php
namespace Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;
use Helper\AppFileRegistry;
use Sandbox\UpperLayer;

/**
 * Takes back what each test wrote into the app, and reports a test that left anything else in its sandbox, which fails the run; enabled on every suite that reaches an app.
 */
class SandboxGuard extends Extension
{
	/** TEST_AFTER runs after every module's _after(), which Codeception dispatches in registration order, so a fixture module can still use its probe in teardown. */
	public static $events = [
		Events::SUITE_BEFORE => ['beforeSuite', 100],
		Events::TEST_BEFORE  => ['beforeTest', 100],
		Events::TEST_AFTER   => ['afterTest', -100],
	];

	/** @var \Deployer|null */
	private $appDeployer;

	/** @var UpperLayer|null */
	private $upperLayer;

	/** @var array<string,true> changes already charged to an earlier test */
	private $reported = [];

	public function beforeSuite(SuiteEvent $event)
	{
		if (!$this->appRunsInPlace())
		{
			return;
		}
		AppFileRegistry::enable();
		AppFileRegistry::scope(AppFileRegistry::SCOPE_SUITE);

		$params = unserialize(PARAMS_SERIALIZED);
		if (isset($params['sandbox']['upper']))
		{
			include_once(codecept_root_dir().'lib/sandbox/UpperLayer.php');
			$this->upperLayer = new UpperLayer($params['sandbox']['upper'], $params['sandbox']['start'], APP_PATH, self::siteFolders($params['db']['dbname']));
		}
	}

	public function beforeTest(TestEvent $event)
	{
		if (!$this->appRunsInPlace())
		{
			return;
		}
		AppFileRegistry::scope(AppFileRegistry::SCOPE_TEST);
	}

	public function afterTest(TestEvent $event)
	{
		if (!$this->appRunsInPlace())
		{
			return;
		}
		AppFileRegistry::reap(AppFileRegistry::SCOPE_TEST, $this->deployer());

		if ($this->upperLayer !== null)
		{
			$this->audit($event->getTest());
		}
	}

	/**
	 * Charge to $test whatever its sandbox gained that no harness, installer or fixture wrote.
	 *
	 * @param object $test the event's test
	 * @return void
	 */
	private function audit($test)
	{
		$signature = Descriptor::getTestSignature($test);
		$found = [];
		foreach ($this->upperLayer->violations(AppFileRegistry::written()) as $kind => $paths)
		{
			foreach ($paths as $path)
			{
				if (!isset($this->reported["$kind $path"]))
				{
					$this->reported["$kind $path"] = true;
					$found[$kind][] = $path;
				}
			}
		}
		if (empty($found))
		{
			return;
		}

		file_put_contents(codecept_output_dir().UpperLayer::VIOLATIONS,
			json_encode(['test' => $signature, 'name' => $this->runFailedName($test)] + $found, JSON_UNESCAPED_SLASHES)."\n", FILE_APPEND);
		$this->writeln("\n  $signature left its sandbox changed:");
		foreach ($found as $kind => $paths)
		{
			$this->writeln("    $kind: ".implode(', ', $paths));
		}
	}

	/**
	 * The site folders a sandbox's e107 may fill: the one the harness's configs pin, and the one e107 names after the sandbox's own database when a config pins none, as an install does ({@see e107::makeSiteHash()}).
	 *
	 * @param string $database
	 * @return string[]
	 */
	private static function siteFolders($database)
	{
		return [\Helper\E107Base::INSTALL_SITE_PATH, substr(md5($database.'.'.\Helper\E107Base::E107_MYSQL_PREFIX), 0, 10)];
	}

	/**
	 * How Codeception's RunFailed lists $test, so `-g failed` runs it again.
	 *
	 * @param object $test the event's test
	 * @return string
	 */
	private function runFailedName($test)
	{
		$name = Descriptor::getTestFullName($test);
		$root = realpath(codecept_root_dir()).DIRECTORY_SEPARATOR;

		return strpos($name, $root) === 0 ? (string) substr($name, strlen($root)) : $name;
	}

	/**
	 * Whether the app under test is the tree the suite runs in, rather than one a deploying deployer copied elsewhere.
	 *
	 * @return bool
	 */
	private function appRunsInPlace()
	{
		return $this->deployer() instanceof \NoopDeployer;
	}

	/** @return \Deployer */
	private function deployer()
	{
		if ($this->appDeployer === null)
		{
			include_once(codecept_root_dir().'lib/deployers/DeployerFactory.php');
			$this->appDeployer = \DeployerFactory::create();
		}
		return $this->appDeployer;
	}
}
