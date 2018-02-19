<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class DelayedDb extends \Codeception\Module\Db
{
	protected $requiredFields = [];

	public function _initialize()
	{
		// Noop
	}

	public function _delayedInitialize()
	{
		return parent::_initialize();
	}

	public function _getDbHostname()
	{
		$matches = [];
		$matched = preg_match('~host=([^;]+)~s', $this->config['dsn'], $matches);
		if (!$matched)
		{
			return false;
		}

		return $matches[1];
	}

	public function _getDbName()
	{
		$matches = [];
		$matched = preg_match('~dbname=([^;]+)~s', $this->config['dsn'], $matches);
		if (!$matched)
		{
			return false;
		}

		return $matches[1];
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
