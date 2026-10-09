<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

// Codeception 5 types \Codeception\Module\Db::$requiredFields and _initialize(),
// neither of which PHP 5.6 can spell, so this overrides neither. codeception.yml
// always supplies dsn, user and password, and _initialize() only connects; the
// dump is still read and loaded by _beforeSuite().
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

	public function _getDbUsername()
	{
		return $this->config['user'];
	}

	public function _getDbPassword()
	{
		return $this->config['password'];
	}

	private function dsnParameter($name)
	{
		$matches = [];
		$matched = preg_match('~' . $name . '=([^;]+)~s', $this->config['dsn'], $matches);
		if (!$matched)
		{
			return false;
		}

		return $matches[1];
	}
}
