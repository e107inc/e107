<?php
namespace Helper;

use Codeception\Module;
use Codeception\TestInterface;

/**
 * Clear the browser session before each WebDriver test.
 *
 * WebDriver keeps one browser for the whole run, so authentication and UI state
 * would otherwise leak between tests. Loading the app first puts the browser on
 * the app's domain so its cookies can be cleared.
 */
class WebDriverSession extends Module
{
	/**
     * @param \Codeception\TestInterface $test
     */
    public function _before($test)
	{
		if (!$this->hasModule('WebDriver'))
		{
			return;
		}

		$webDriver = $this->getModule('WebDriver');
		$webDriver->amOnPage('/');
		$webDriver->webDriver->manage()->deleteAllCookies();
	}
}
