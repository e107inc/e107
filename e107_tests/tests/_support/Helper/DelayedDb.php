<?php
namespace Helper;

class DelayedDb extends \Codeception\Module\Db
{
    /**
     * @var mixed[]
     */
    protected $requiredFields = ['dsn', 'user', 'password']; // Enforce required config
    /**
     * @return void
     */
    public function _initialize()
    {
        // Call parent directly instead of deferring
        parent::_initialize();
        codecept_debug("DelayedDb initialized with DSN: " . $this->config['dsn']);
    }

    // Keep this for manual triggering if needed
    public function _delayedInitialize()
    {
        return parent::_initialize();
    }

    public function _getDbHostname()
    {
        $matches = [];
        $matched = preg_match('~host=([^;]+)~s', $this->config['dsn'], $matches);
        return $matched ? $matches[1] : false;
    }

    public function _getDbName()
    {
        $matches = [];
        $matched = preg_match('~dbname=([^;]+)~s', $this->config['dsn'], $matches);
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