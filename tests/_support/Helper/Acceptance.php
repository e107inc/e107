<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends Base
{
	protected $deployer_components = ['db', 'fs'];

	public function _beforeSuite($settings = array())
	{
		return parent::_beforeSuite($settings);
	}

	public function _afterSuite()
	{
		return parent::_afterSuite();
	}
}
