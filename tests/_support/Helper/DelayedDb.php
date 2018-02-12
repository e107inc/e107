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

	public function getConfig()
	{
		return $this->config;
	}

	public function getDbHostname()
	{
		$matches = [];
	        $matched = preg_match('~host=([^;]+)~s', $this->config['dsn'], $matches);
	        if (!$matched)
		{
	        	return false;
	        }

	        return $matches[1];	
	}

	public function getDbName()
	{
		$matches = [];
	        $matched = preg_match('~dbname=([^;]+)~s', $this->config['dsn'], $matches);
	        if (!$matched)
		{
	        	return false;
	        }

	        return $matches[1];	
	}

	public function getDbUsername()
	{
		return $this->config['user'];
	}

	public function getDbPassword()
	{
		return $this->config['password'];
	}
}
