<?php
namespace Helper;

/**
 * WebDriver-suite counterpart to \Helper\Acceptance.
 *
 * The acceptance suite installs e107 itself, so it no-ops the config write.
 * The WebDriver suite instead boots e107 from the dump loaded by \Helper\SiteDb,
 * which supplies the schema and data but not e107_config.php (the install marker
 * and DB credentials) or the .htaccess an install leaves. This helper writes both
 * so the served app connects to the populated database instead of redirecting to
 * the installer, and its SEF links resolve.
 */
class Webdriver extends E107Base
{
    /**
     * Start every test with e107's request counters clear.
     *
     * @param \Codeception\TestInterface $test
     * @return void
     */
    public function _before(\Codeception\TestInterface $test)
    {
        parent::_before($test);
        $this->resetFloodProtection();
    }

    protected function writeLocalE107Config()
    {
        // The browser reaches the app through the deployment target's docroot,
        // a separate location from the local checkout under the SFTP deployer,
        // so the generated config must be written there rather than to APP_PATH.
        $this->deployer->writeAppFile('e107_config.php', $this->renderLocalE107Config());
        // The config turns e_MOD_REWRITE on, so links take their SEF form and
        // need the rewrite rules install.php puts in place from e107.htaccess.
        if (is_file(APP_PATH.'/e107.htaccess'))
        {
            $this->deployer->writeAppFile('.htaccess', file_get_contents(APP_PATH.'/e107.htaccess'));
        }
    }
}
