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
class SiteDb extends \Codeception\Module\Db
{
    public function _getDbHostname()
    {
        return $this->dsnParameter('host');
    }

    public function _getDbPort()
    {
        return $this->dsnParameter('port');
    }

    public function _getDbName()
    {
        return $this->dsnParameter('dbname');
    }

    private function dsnParameter($name)
    {
        $matches = [];
        $matched = preg_match('~' . $name . '=([^;]+)~s', $this->config['dsn'], $matches);
        return $matched ? $matches[1] : false;
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
