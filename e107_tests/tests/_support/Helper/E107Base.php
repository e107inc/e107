<?php
namespace Helper;
include_once(codecept_root_dir() . "lib/preparers/PreparerFactory.php");

use Codeception\Lib\ModuleContainer;
use PreparerFactory;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

abstract class E107Base extends Base
{
    const APP_PATH_E107_CONFIG = APP_PATH . "/e107_config.php";
    const E107_MYSQL_PREFIX = 'e107_';
    protected $preparer = null;
    private $configBackedUp = false; // Track if we've backed up the config

    /** @var resource|null Open file handle for the deployment target lock */
    private $deploymentLockHandle;

    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->preparer = PreparerFactory::create();
    }

    public function _beforeSuite($settings = [])
    {
        $this->acquireDeploymentLock();
        $this->backupLocalE107Config();
        $this->preparer->snapshot();
        parent::_beforeSuite($settings);
        $this->writeLocalE107Config();
    }

    protected function backupLocalE107Config()
    {
        if (file_exists(self::APP_PATH_E107_CONFIG)) {
            rename(self::APP_PATH_E107_CONFIG, APP_PATH . '/e107_config.php.bak');
            $this->configBackedUp = true; // Mark as backed up
        }
    }

    protected function renderLocalE107Config()
    {
        $twig = new Environment(new ArrayLoader([
            'e107_config.php' => file_get_contents(codecept_data_dir() . "/e107_config.php.sample")
        ]));

        $db = $this->getModule('\Helper\DelayedDb');

        return $twig->render('e107_config.php', [
            'mySQLserver'    => $db->_getDbHostname(),
            'mySQLuser'      => $db->_getDbUsername(),
            'mySQLpassword'  => $db->_getDbPassword(),
            'mySQLdefaultdb' => $db->_getDbName(),
            'mySQLprefix'    => self::E107_MYSQL_PREFIX,
        ]);
    }

    protected function writeLocalE107Config()
    {
        file_put_contents(self::APP_PATH_E107_CONFIG, $this->renderLocalE107Config());
    }

    public function _afterSuite()
    {
        parent::_afterSuite();
        $this->revokeLocalE107Config();
        $this->preparer->rollback();
        $this->restoreLocalE107Config();
        $this->releaseDeploymentLock();
        $this->workaroundOldPhpUnitPhpCodeCoverage();
    }

    /**
     * Destructor: Ensures cleanup even on crashes or fatal errors.
     */
    public function __destruct()
    {
        // Only restore if we backed up and haven't already restored
        if ($this->configBackedUp && file_exists(APP_PATH . '/e107_config.php.bak')) {
            $this->revokeLocalE107Config();
            $this->restoreLocalE107Config();
        }
        $this->releaseDeploymentLock();
    }

    protected function revokeLocalE107Config()
    {
        if (file_exists(self::APP_PATH_E107_CONFIG)) {
            unlink(self::APP_PATH_E107_CONFIG);
        }
    }

    protected function restoreLocalE107Config()
    {
        if (file_exists(APP_PATH . "/e107_config.php.bak")) {
            rename(APP_PATH . '/e107_config.php.bak', self::APP_PATH_E107_CONFIG);
            $this->configBackedUp = false; // Reset flag after restoration
        }
    }

    /**
     * Acquire a blocking exclusive lock on the deployment target so that
     * parallel acceptance/webdriver runs against the same URL are serialized.
     * No-op for unit/functional suites (no deployment target).
     */
    private function acquireDeploymentLock()
    {
        $url = $this->getDeploymentTargetUrl();
        if ($url === null)
        {
            return;
        }

        $lockPath = sys_get_temp_dir() . '/e107-acceptance-' . md5($url) . '.lock';
        $this->deploymentLockHandle = fopen($lockPath, 'w');
        if ($this->deploymentLockHandle === false)
        {
            return;
        }

        codecept_debug('E107Base: Acquiring deployment lock for ' . $url);
        flock($this->deploymentLockHandle, LOCK_EX);
        fwrite($this->deploymentLockHandle, json_encode(array(
            'pid' => getmypid(),
            'url' => $url,
            'acquired' => time(),
        )));
        fflush($this->deploymentLockHandle);
        codecept_debug('E107Base: Deployment lock acquired');
    }

    private function releaseDeploymentLock()
    {
        if ($this->deploymentLockHandle !== null)
        {
            flock($this->deploymentLockHandle, LOCK_UN);
            fclose($this->deploymentLockHandle);
            $this->deploymentLockHandle = null;
            codecept_debug('E107Base: Deployment lock released');
        }
    }

    /**
     * @return string|null The acceptance/webdriver target URL, or null for
     *                     suites that don't use a browser module.
     */
    private function getDeploymentTargetUrl()
    {
        foreach (array('PhpBrowser', 'WebDriver') as $moduleName)
        {
            try
            {
                $module = $this->getModule($moduleName);
                $url = $module->_getConfig('url');
                if ($url !== null && $url !== '')
                {
                    return $url;
                }
            }
            catch (\Exception $e)
            {
                // Module not enabled for this suite
            }
        }
        return null;
    }

    /**
     * Workaround for phpunit/php-code-coverage < 6.0.8
     * @see https://github.com/sebastianbergmann/php-code-coverage/commit/f4181f5c0a2af0180dadaeb576c6a1a7548b54bf
     */
    protected function workaroundOldPhpUnitPhpCodeCoverage()
    {
        $composer_installed_file = codecept_absolute_path("vendor/composer/installed.json");
        $composer_installed = json_decode(file_get_contents($composer_installed_file));
        if (isset($composer_installed->packages)) {
            // Composer 2 format for the installed packages manifest
            $composer_installed = $composer_installed->packages;
        }
        $installed_phpunit_php_code_coverage = current(array_filter($composer_installed, function ($element) {
            return $element->name == 'phpunit/php-code-coverage';
        }));
        if (version_compare($installed_phpunit_php_code_coverage->version_normalized, '6.0.8', '>='))
            return;

        @mkdir(codecept_output_dir(), 0755, true);
    }
}
