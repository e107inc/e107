<?php
namespace Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Helper\AppFileRegistry;

/**
 * Holds the app tree for the length of a run and takes back what a run wrote into it, including a run that died; enabled on every suite that reaches an app.
 */
class WorkspaceGuard extends Extension
{
	/**
	 * Outside the modules on both edges: {@see \Helper\E107Base} parks e107_config.php in _beforeSuite and restores it in _afterSuite.
	 * TEST_AFTER runs after every module's _after(), which Codeception dispatches in registration order, so a fixture module can still use its probe in teardown.
	 */
	public static $events = [
		Events::SUITE_BEFORE => ['beforeSuite', 100],
		Events::TEST_BEFORE  => ['beforeTest', 100],
		Events::TEST_AFTER   => ['afterTest', -100],
		Events::SUITE_AFTER  => ['afterSuite', -100],
	];

	/** @var \Deployer|null */
	private $appDeployer;

	/** @var string|false bytes of e107.htaccess as the suite found them */
	private $htaccess = false;

	/** @var resource|null the exclusive hold this run has on the app tree */
	private $lock;

	public function beforeSuite(SuiteEvent $event)
	{
		if (!$this->appRunsInPlace())
		{
			return;
		}
		$this->acquireWorkspaceLock();
		AppFileRegistry::enable();
		AppFileRegistry::recover($this->deployer());
		AppFileRegistry::scope(AppFileRegistry::SCOPE_SUITE);
		$this->restoreConfigBackup();
		$this->htaccess = @file_get_contents(APP_PATH.'/e107.htaccess');
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
	}

	public function afterSuite(SuiteEvent $event)
	{
		if (!$this->appRunsInPlace())
		{
			return;
		}
		AppFileRegistry::reap(AppFileRegistry::SCOPE_TEST, $this->deployer());
		AppFileRegistry::reap(AppFileRegistry::SCOPE_SUITE, $this->deployer());
		$this->restoreConfigBackup();
		$this->restoreHtaccess();
		$this->releaseWorkspaceLock();
	}

	/**
	 * Hold the app tree for the length of the suite.
	 *
	 * The sweep deletes e107_system/<site_path> and e107_media/<site_path>,
	 * which is another run's live state whenever two runs share one tree. What
	 * that produces does not look like a locking problem: it looks like
	 * ordinary flakes, scattered over unrelated tests, with counts that move
	 * every time. It has cost this project two wrong conclusions, one of them
	 * a whole afternoon spent believing a correct fix had made things worse.
	 *
	 * Helper\E107Base already takes a lock, but it is keyed on the browser
	 * URL and returns early without one, so the unit suite, which has no
	 * browser module and sweeps the same two directories, was never covered.
	 * This one is keyed on the tree the sweep actually deletes.
	 *
	 * @return void
	 */
	private function acquireWorkspaceLock()
	{
		$path = sys_get_temp_dir().'/e107-workspace-'.md5(APP_PATH).'.lock';

		$this->lock = fopen($path, 'w');
		if ($this->lock === false)
		{
			$this->lock = null;
			codecept_debug('WorkspaceGuard: cannot open '.$path.', running unlocked');

			return;
		}

		if (!flock($this->lock, LOCK_EX | LOCK_NB))
		{
			codecept_debug('WorkspaceGuard: another run holds '.APP_PATH.', waiting for it to finish');
			flock($this->lock, LOCK_EX);
		}

		ftruncate($this->lock, 0);
		fwrite($this->lock, json_encode([
			'pid'      => getmypid(),
			'app'      => APP_PATH,
			'acquired' => time(),
		]));
		fflush($this->lock);
	}

	/**
	 * Released after the closing sweep, and after Helper\E107Base has given
	 * back the deployment lock it took second. Both are always taken in that
	 * order, which is what keeps a pair of runs from deadlocking on each other.
	 *
	 * @return void
	 */
	private function releaseWorkspaceLock()
	{
		if ($this->lock === null)
		{
			return;
		}

		flock($this->lock, LOCK_UN);
		fclose($this->lock);
		$this->lock = null;
	}

	/**
	 * Whether the app under test is the tree the developer is working in.
	 *
	 * Only then is there anything to sweep. A deploying deployer (sftp,
	 * cpanel) is handed an isolated, disposable git worktree by
	 * PreparerFactory and serves the app from somewhere else entirely, so the
	 * developer's tree is never written to. Sweeping anyway would be pointless
	 * on a good day and fatal on a bad one: it turns housekeeping into ssh
	 * calls, and the continuous integration image that runs the unit suite has
	 * no sshpass.
	 *
	 * @return bool
	 */
	private function appRunsInPlace()
	{
		return $this->deployer() instanceof \NoopDeployer;
	}

	/**
	 * Put e107_config.php back from the copy Helper\E107Base parked at
	 * e107_config.php.bak. That file outlives only a run that died before
	 * E107Base could restore it, and it holds the config the tree had before
	 * the suite, so it always wins over whatever the installer wrote.
	 *
	 * Host-side on purpose: E107Base backs the file up and restores it with
	 * rename() on APP_PATH whatever the deployer is, so the .bak never exists
	 * anywhere else.
	 *
	 * @return void
	 */
	private function restoreConfigBackup()
	{
		$backup = APP_PATH.'/e107_config.php.bak';
		if (!file_exists($backup))
		{
			return;
		}
		codecept_debug('WorkspaceGuard: restoring e107_config.php from an interrupted run');
		@unlink(APP_PATH.'/e107_config.php');
		@rename($backup, APP_PATH.'/e107_config.php');
	}

	/**
	 * install.php renames e107.htaccess to .htaccess when the app root has no
	 * .htaccess yet, which leaves a tracked file deleted. Write the bytes back
	 * instead of shelling out to `git restore`: no git dependency, and it
	 * still works when the app is deployed somewhere git has never been.
	 *
	 * The comparison reads APP_PATH because that is the tree every deployer
	 * builds the app from, so it holds the bytes the run started with. The
	 * write goes through the deployer, so it lands wherever the app actually
	 * lives.
	 *
	 * @return void
	 */
	private function restoreHtaccess()
	{
		if ($this->htaccess === false)
		{
			return; // the tree never had one
		}
		if (@file_get_contents(APP_PATH.'/e107.htaccess') === $this->htaccess)
		{
			return;
		}
		codecept_debug('WorkspaceGuard: restoring e107.htaccess');
		try
		{
			$this->deployer()->writeAppFile('e107.htaccess', $this->htaccess);
		}
		catch (\Exception $e)
		{
			// Housekeeping must never be the reason a suite stops, which is
			// the same rule Deployer::removeAppPaths() follows.
			codecept_debug('WorkspaceGuard: could not restore e107.htaccess: '.$e->getMessage());
		}
	}

	/**
	 * A deployer of its own: the modules keep theirs protected, and the file
	 * operations the sweep needs hold no state worth sharing.
	 *
	 * @return \Deployer
	 */
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
