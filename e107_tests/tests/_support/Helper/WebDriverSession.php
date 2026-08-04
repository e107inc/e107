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
	public function _before(TestInterface $test)
	{
		if (!$this->hasModule('WebDriver'))
		{
			return;
		}

		$webDriver = $this->getModule('WebDriver');

		$this->dismissAnyOpenDialog($webDriver);

		$webDriver->amOnPage('/');
		$webDriver->webDriver->manage()->deleteAllCookies();
	}

	/**
	 * Answer a native dialog left standing by the test before this one.
	 *
	 * The suite asks Chrome not to dismiss confirm() and alert() by itself
	 * (unhandledPromptBehavior: ignore), because a test about what the visitor
	 * answered cannot ask the question if the browser has already answered it.
	 * The cost is that a dialog nobody answered outlives its test, and one
	 * browser serves the whole run: every later command then fails with
	 * UnexpectedAlertOpen, in tests that have nothing to do with it. Clearing
	 * it here keeps that blast radius at one test.
	 *
	 * @param \Codeception\Module\WebDriver $webDriver
	 * @return void
	 */
	private function dismissAnyOpenDialog($webDriver)
	{
		try
		{
			$webDriver->webDriver->switchTo()->alert()->dismiss();
		}
		catch (\Exception $e)
		{
			// Nothing open, which is the ordinary case.
		}
	}
}
