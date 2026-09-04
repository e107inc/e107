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
		'e107_core/templates/e107_tests_corescan_template.php', // @see e107TemplateSourceTest
		'e107_themes/tpstate1_legacy',    // @see Helper\ThemeFixture
		'e107_themes/tpstate3_plain',
		'e107_themes/tpstate3_rootfpw',
		'e107_themes/tpstate3_globalfpw',
		'e107_themes/tpstate4_legacybs',
		'e107_themes/tpstate4_globalfpw',
		'e107_tests_theme_fixture_probe.php', // @see Helper\ThemeFixture
		'e107_plugins/thing',      // downloaded by e_marketplaceTest
		// Plugin folders a unit test creates and removes itself. A test that
		// fails part way leaves one behind, and the next run's plugin scan
		// detects it, so the leftover turns one failure into a different one.
		'e107_plugins/nofollow',      // @see pluginsTest::testRemotePlugin
		'e107_plugins/temptest',      // @see e_pluginTest::testIgnoringOfInvalidPlugin
		'e107_plugins/temptest5709',  // @see e107pluginTest
		'e107_plugins/temptest6024',  // @see SingleEntryErrorPageCest
		'e107_plugins/temptest6109',  // @see lancheckTest
		'e107_tests_token_injection_probe.php', // @see CsrfTokenInjectionCest
		'e107_tests_csrf_failclosed_probe.php', // @see CsrfFailClosedCest
		'e107_tests_csrf_clienthalf_reset.php',  // @see CsrfClientHalfCest
		'e107_tests_csrf_matrix_probe.php',      // @see CsrfModeMatrixCest
		'e107_languages/e107_tests_lancheck_target.php', // @see lancheckTest
		'e107_tests_forum_fixture_probe.php', // @see Helper\ForumFixture
		'e107_tests_forum_canary.txt',        // @see ForumAttachmentCest
		'e107_tests_pm_fixture_probe.php',    // @see PmAttachmentCest
		'e107_tests_pm_storage_probe.php',    // @see PmAttachmentStorageCest
		'e107_plugins/pm/attachments',        // legacy attachment path; @see PmAttachmentStorageCest
		'e107_tests_plugin_install_probe.php', // @see Helper\Acceptance::havePluginInstalled()
		'e107_tests_thumb_probe.php',          // @see ThumbnailContainmentCest
		'e107_tests_p16_probe.php',            // @see ThumbMediaUserclassCest
		'e107_tests_p18_probe.php',            // @see ForumAttachmentServingCest
		'e107_themes/bootstrap5/images/e107_tests_p16_theme.png',       // @see ThumbMediaUserclassCest
		'e107_themes/bootstrap5/images/e107_tests_p16_themepublic.png', // @see ThumbMediaUserclassCest
		'e107_plugins/pm/attachments',         // @see ThumbnailContainmentCest
		'e107_tests_preauth_probe.php',        // @see AdminPreAuthCest
		'e107_tests_preauth_canary.txt',       // @see AdminPreAuthCest
		'e107_plugins/faqs/admin_e107_tests_preauth_dispatcher.php', // @see AdminPreAuthCest
		'e107_plugins/faqs/e107_tests_preauth_dispatcher.php',       // @see AdminPreAuthCest
		'e107_tests_routeperms_probe.php',     // @see AdminRoutePermsCest
		'e107_tests_confirmtoken_probe.php',   // @see AdminConfirmTokenCest
		'e107_tests_themefront_probe.php',     // @see ThemeHandlerFrontEndCest
		'e107_tests_comment_authz_probe.php',  // @see CommentEditAuthzCest
		'e107_tests_p3_hop.php',              // @see e_fileOutboundRequestTest
		'e107_tests_rss_import_probe.php',    // @see RssImportCest
		'e107_tests_rss_fixtures',            // @see RssImportCest
		'e107_media/.htaccess',               // written at runtime; @see e_file::blockScriptExecution()
		'e107_tests_p6_unsubscribe_reset.php',  // @see NewsletterUnsubscribeCest
		'e107_tests_p6_cron_probe.php',         // @see CronMisconfigMailCest
		'e107_plugins/e107_tests_cronprobe',    // @see CronMisconfigMailCest
		'e107_tests_p6_contact_probe.php',      // @see ContactFormCest
		'e107_tests_p6_gsitemap_reset.php',     // @see GsitemapFuncCest
		'e107_tests_p6_rss_reset.php',          // @see RssCommentsFeedCest
		'e107_tests_p6_poll_reset.php',         // @see PollStuffingCest
		'e107_tests_p6_download_reset.php',     // @see DownloadMirrorActiveCest
		'e107_themes/bootstrap5/online_template.php', // @see ForumNamesOutsideTheFeedCest, OnlineMemberListLinkCest
		'e107_tests_install_prefs_probe.php',  // @see InstallPrefDuplicatesCest
		'e107_tests_5928_pref_probe.php',      // @see MissingCorePrefCest
		'e107_tests_xup_token_probe.php',      // @see ProviderLoginTokenCest
		'e107_tests_xup_route_probe.php',      // @see XupProviderLoginTokenCest
		'e107_tests_encoding_probe.php',            // @see Helper\OutputEncodingFixture
		'e107_tests_encoding_feed.xml',
		'e107_tests_encoding_addons.xml',
		'e107_tests_encoding_newsfeed.xml',
		'e107_tests_encoding_tinymce_canary.xml',
		'e107_tests_captcha_probe.php',        // @see CaptchaLifecycleCest
		'e107_tests_redirect_probe.php',           // @see Helper\RedirectFixture
		'e107_tests_no_token_probe.php',       // @see ForcedLogoutCsrfCest
		'e107_tests_menu_prefs_probe.php',        // @see Helper\MenuPrefFixture
		'e107_tests_online_memberlist_probe.php',  // @see OnlineMemberListLinkCest
		'e107_tests_chatbox_request_self_probe.php', // @see ChatboxRequestSelfCest
		'e107_tests_containment_unguarded.php',    // @see ProbeContainmentCest
		'e107_tests_cli_bootstrap_probe.php',  // @see CliBootstrapHttpRefusalCest
		'e107_tests_cli_only_entry.php',       // @see CliBootstrapHttpRefusalCest
		'e107_tests_cli_only_canary.txt',      // @see CliBootstrapHttpRefusalCest
		'e107_tests_sessionfixation_probe.php', // @see SessionFixationCest
		'e107_tests_pm_send_probe.php',            // @see PmAttachmentSendCest
		'e107_tests_host_arming_probe.php',        // @see HostAllowListArmingCest
		'e107_tests_host_arming_prefs.php',        // @see HostAllowListArmingCest
		'e107_tests_p9_csrf_probe.php',        // @see AdminMiscCsrfCest
		'e107_tests_rate_csrf.php',            // @see RateVoteCsrfCest
		'e107_tests_signup_csrf.php',          // @see SignupTestMailCsrfCest
		'e107_tests_upload_csrf.php',          // @see MediaUploadCsrfCest
		'e107_tests_lan_fallback.php',         // @see CsrfRefusalLanFallbackCest
		'e107_tests_security_level.php',       // @see SecurityLevelZeroCsrfCest
		'e107_themes/e107_tests_p84_themecopy',    // @see AdminGetCsrfPluginThemeMenuCest
		'e107_plugins/admin_menu/admin_menu_sql.php', // @see AdminGetCsrfPluginThemeMenuCest
		'e107_plugins/forum/forum_update_check.php', // @see ForumUpgradeCsrfCest
		'e107_tests_admin_password_probe.php',       // @see AdminPasswordChangeCest
		'e107_tests_ghsa7v5h_probe.php',       // @see SessionAuthTokenCest
		'e107_tests_cookie_mode_probe.php',    // @see CookieModeRemovedCest
		'e107_tests_session_rekey_probe.php',  // @see SessionRekeyUpgradeCest
	];

	/** Files a test moves aside for one test method, as parked name to the name it belongs back at. */
	private static $parked = [
		'e107_images/e107_tests_5999_adminlogo.png' => 'e107_images/adminlogo.png', // @see admin_shortcodesLogoTest
		'e107_languages/English/lan_rate.php.bak' => 'e107_languages/English/lan_rate.php', // @see CsrfRefusalLanFallbackCest
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
		$this->restoreConfigBackup();
		$this->restoreParked();
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
		$this->restoreParked();
		$this->restoreHtaccess();
		$this->sweep();
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
			codecept_debug('WorkspaceCleanup: cannot open '.$path.', running unlocked');

			return;
		}

		if (!flock($this->lock, LOCK_EX | LOCK_NB))
		{
			codecept_debug('WorkspaceCleanup: another run holds '.APP_PATH.', waiting for it to finish');
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
	 * Put back anything a test parked and did not live to restore, on APP_PATH as {@see Extension\WorkspaceCleanup::restoreConfigBackup()} does.
	 *
	 * @return void
	 */
	private function restoreParked()
	{
		foreach (self::$parked as $parked => $original)
		{
			if (!file_exists(APP_PATH.'/'.$parked))
			{
				continue;
			}
			codecept_debug('WorkspaceCleanup: restoring '.$original.' from an interrupted run');
			@unlink(APP_PATH.'/'.$original);
			@rename(APP_PATH.'/'.$parked, APP_PATH.'/'.$original);
		}
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
