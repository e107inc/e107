<?php
namespace Helper;
include_once(__DIR__ . "/../../../lib/prepare_cpanel.php");

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
	public function _beforeSuite($settings = array())
	{
		$secrets = $settings['secrets'];
		if ($secrets['cpanel']['enabled'] === '1')
		{
			$prepare = new \Prepare_cPanel($secrets['cpanel']);
			$retcode = $prepare->start();
			if ($retcode === true)
			{
				$domain = $prepare->getDomain();
		   		$this->getModule('PhpBrowser')->_reconfigure(array('url' => "http://${domain}"));
			}
		}
	}
}
