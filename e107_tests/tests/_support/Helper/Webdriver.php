<?php
namespace Helper;

/**
 * WebDriver-suite counterpart to \Helper\Acceptance.
 *
 * The acceptance suite installs e107 itself, so it no-ops the config write.
 * The WebDriver suite instead boots e107 from the dump loaded by \Helper\DelayedDb,
 * which supplies the schema and data but not e107_config.php (the install marker
 * and DB credentials). This helper writes that file so the served app connects to
 * the populated database instead of redirecting to the installer.
 */
class Webdriver extends E107Base
{
    protected function writeLocalE107Config()
    {
        // The browser reaches the app through the deployment target's docroot,
        // a separate location from the local checkout under the SFTP deployer,
        // so the generated config must be written there rather than to APP_PATH.
        $this->deployer->writeAppFile('e107_config.php', $this->renderLocalE107Config());
    }
}
