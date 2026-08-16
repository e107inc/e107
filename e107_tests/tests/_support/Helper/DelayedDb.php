<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

// Codeception 5 types \Codeception\Module\Db::$requiredFields and _initialize(),
// neither of which PHP 5.6 can spell, so this overrides neither. codeception.yml
// always supplies dsn, user and password, and _initialize() only connects; the
// dump is still read and loaded by _beforeSuite().
class DelayedDb extends \Codeception\Module\Db
{
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
