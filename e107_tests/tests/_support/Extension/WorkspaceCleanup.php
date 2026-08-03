<?php
namespace Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;

/**
 * Removes what a test run leaves in the app root, so a run no longer has to
 * be followed by `e107_tests/bin/e107-tests clean`.
 *
 * Enabled for the acceptance and unit suites, because both write into the
 * tree: acceptance installs e107 into it, and several unit tests copy theme
 * and plugin fixtures out of tests/_data. Under the local deployer the app
 * root is the developer's own worktree, so anything left behind turns up in
 * `git status` and makes `git add -A` unsafe.
 */
class WorkspaceCleanup extends Extension
{
	/**
	 * SUITE_AFTER is dispatched from SuiteManager::run()'s finally block, so
	 * it fires on a failing suite as well as a passing one. What it cannot
	 * survive is a fatal or a killed process, which is why the same sweep runs
	 * on the way in: leftovers then heal themselves on the next run instead of
	 * waiting for someone to remember the CLI.
	 *
	 * The priorities put the sweep outside the modules. Helper\E107Base parks
	 * e107_config.php at e107_config.php.bak in its _beforeSuite and renames it
	 * back in its _afterSuite, so the sweep has to be ahead of it on the way in
	 * and behind it on the way out.
	 */
	public static $events = [
		Events::SUITE_BEFORE => ['beforeSuite', 100],
		Events::SUITE_AFTER  => ['afterSuite', -100],
	];

	/**
	 * Artifacts of an acceptance run whose names are fixed, relative to the
	 * app root.
	 *
	 * An explicit list, not a scan: the app root is the whole of e107, and
	 * walking it to find a handful of known names would cost more than the
	 * rest of the sweep by orders of magnitude. e107 ships none of these names
	 * and git tracks none of them, so nothing here needs an "is it tracked?"
	 * question either.
	 */
	private static $artifacts = [
		'e107Install.log',
		'e107_system/e107Install.log',
		'NUL',                     // a redirect to the Windows null device, taken literally
		'e107_system/000000test',  // site_path the sample e107_config.php installs under
		'e107_media/000000test',
		'e107_files/public',       // absence is load-bearing; @see DownloadTraversalCest
		'e107_themes/basic-light', // fixtures copied out of tests/_data
		'e107_themes/testcore',
		'e107_themes/testkubrick',
		'e107_plugins/thing',      // downloaded by e_marketplaceTest
		'e107_tests_forum_fixture_probe.php', // @see Helper\ForumFixture
		'e107_tests_forum_canary.txt',        // @see ForumAttachmentCest
		'e107_tests_plugin_install_probe.php', // @see Helper\Acceptance::havePluginInstalled()
		'e107_tests_preauth_probe.php',        // @see AdminPreAuthCest
		'e107_tests_preauth_canary.txt',       // @see AdminPreAuthCest
		'e107_plugins/faqs/admin_e107_tests_preauth_dispatcher.php', // @see AdminPreAuthCest
		'e107_plugins/faqs/e107_tests_preauth_dispatcher.php',       // @see AdminPreAuthCest
	];

	/** @var \Deployer|null */
	private $appDeployer;

	/** @var string|false bytes of e107.htaccess as the suite found them */
	private $htaccess = false;

	public function beforeSuite(SuiteEvent $event)
	{
		if (!$this->appRunsInPlace())
		{
			return;
		}
		$this->restoreConfigBackup();
		$this->htaccess = @file_get_contents(APP_PATH.'/e107.htaccess');
		$this->sweep();
	}

	public function afterSuite(SuiteEvent $event)
	{
		if (!$this->appRunsInPlace())
		{
			return;
		}
		$this->restoreConfigBackup();
		$this->restoreHtaccess();
		$this->sweep();
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

	private function sweep()
	{
		$started = microtime(true);
		$paths = $this->artifacts();
		$this->deployer()->removeAppPaths($paths);
		codecept_debug(sprintf('WorkspaceCleanup: sweep of %d path(s) took %.1f ms',
			count($paths), (microtime(true) - $started) * 1000));
	}

	/**
	 * A real install puts its state under a directory named for a hash of the
	 * database it installed into, so that name only exists once the test
	 * config is resolved.
	 *
	 * @see \e107::makeSiteHash()
	 * @return string[]
	 */
	private function artifacts()
	{
		$params = unserialize(PARAMS_SERIALIZED);
		$dbname = isset($params['db']['dbname']) ? $params['db']['dbname'] : '';
		$hash = substr(md5($dbname.'.'.\Helper\E107Base::E107_MYSQL_PREFIX), 0, 10);

		return array_merge(self::$artifacts, [
			"e107_system/$hash",
			"e107_media/$hash",
		]);
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
		codecept_debug('WorkspaceCleanup: restoring e107_config.php from an interrupted run');
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
		codecept_debug('WorkspaceCleanup: restoring e107.htaccess');
		try
		{
			$this->deployer()->writeAppFile('e107.htaccess', $this->htaccess);
		}
		catch (\Exception $e)
		{
			// Housekeeping must never be the reason a suite stops, which is
			// the same rule Deployer::removeAppPaths() follows.
			codecept_debug('WorkspaceCleanup: could not restore e107.htaccess: '.$e->getMessage());
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
