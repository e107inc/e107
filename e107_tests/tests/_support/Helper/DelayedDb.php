<?php
namespace Helper;

// `Codeception\Module\Db` gained typed properties / return types in Codeception 5.x
// (e.g. `protected array $requiredFields` and `public function _initialize(): void`).
// On PHP 5.6 / 7.0 cells we run Codeception 4.x where those declarations are absent.
// Overriding either of them in a single source file is therefore unsolvable: the
// typed form fails to parse on 5.6, and the untyped form violates the LSP contract
// against the 5.x parent. We sidestep both by NOT overriding those members. Required
// field enforcement is redundant anyway because codeception.yml always supplies the
// dsn/user/password keys; the previous debug log line on initialise is cosmetic.
class DelayedDb extends \Codeception\Module\Db
{
    // Codeception still has the deferred-init plumbing we used historically.
    // Kept as a no-arg wrapper so callers can opt back in if they need to
    // postpone connection bring-up; new code should just rely on Codeception's
    // own _initialize() lifecycle.
    public function _delayedInitialize()
    {
        return parent::_initialize();
    }

    /**
     * The e107 database driver the suite runs on, from the DSN's scheme.
     *
     * @return string 'mysql' or 'sqlite'
     */
    public function _getDbDriver()
    {
        $colon = strpos($this->config['dsn'], ':');
        return $colon === false ? 'mysql' : strtolower((string) substr($this->config['dsn'], 0, $colon));
    }

    /**
     * @return string|false the server's host name; false on a driver without a server
     */
    public function _getDbHostname()
    {
        return $this->dsnParameter('host');
    }

    public function _getDbPort()
    {
        return $this->dsnParameter('port');
    }

    /**
     * The database e107 connects to, which for SQLite is the database file's absolute path.
     *
     * @return string|false
     */
    public function _getDbName()
    {
        if ($this->_getDbDriver() === 'sqlite')
        {
            // Codeception reads the DSN's path relative to the project directory.
            return \Codeception\Configuration::projectDir() . substr($this->config['dsn'], strlen('sqlite:'));
        }

        return $this->dsnParameter('dbname');
    }

    private function dsnParameter($name)
    {
        $matches = [];
        $matched = preg_match('~' . $name . '=([^;]+)~s', $this->config['dsn'], $matches);
        return $matched ? $matches[1] : false;
    }

    /**
     * Whether e107 gives SQLite its MySQL compatibility functions; see db.mysql_compat in config.sample.yml.
     *
     * @return bool
     */
    public function _getDbMysqlCompat()
    {
        return !empty($this->config['mysql_compat']);
    }

    public function _getDbUsername()
    {
        return $this->config['user'];
    }

    public function _getDbPassword()
    {
        return $this->config['password'];
    }
}
