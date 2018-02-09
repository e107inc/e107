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
}
