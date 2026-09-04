<?php
namespace Helper;

use Codeception\Exception\ModuleConfigException;
use Codeception\Module;

/**
 * Takes over WebDriver's wait() so a blind sleep fails the test that asked for it (#6262).
 */
class NoBlindWaits extends Module
{
	public function _initialize()
	{
		if ($this->moduleContainer->moduleForAction('wait') !== $this)
		{
			throw new ModuleConfigException(__CLASS__, 'List this module after WebDriver, so its wait() is the one the actor reaches.');
		}
	}

	/**
	 * @param int|float $timeout
	 * @return void
	 */
	public function wait($timeout)
	{
		throw new \LogicException(sprintf(
			'wait(%s) is a blind sleep. Wait on the condition instead: waitForElement(), waitForElementVisible(), '
			. 'waitForText() or waitForJS() for what the browser can see, \Test\Poll::until() for anything else.',
			$timeout
		));
	}
}
