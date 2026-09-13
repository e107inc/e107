<?php

namespace Helper;

use Codeception\Module as CodeceptionModule;

/** A module that drives the app under test through whichever browser and app helper the suite enabled. */
abstract class AppFixture extends CodeceptionModule
{
	/**
	 * @return Acceptance|Webdriver
	 */
	protected function app()
	{
		foreach (array('\Helper\Acceptance', '\Helper\Webdriver') as $name)
		{
			if ($this->hasModule($name))
			{
				return $this->getModule($name);
			}
		}

		throw new \RuntimeException(get_class($this).' needs Helper\Acceptance or Helper\Webdriver');
	}

	/**
	 * @return \Codeception\Module\PhpBrowser|\Codeception\Module\WebDriver
	 */
	protected function browser()
	{
		foreach (array('PhpBrowser', 'WebDriver') as $name)
		{
			if ($this->hasModule($name))
			{
				return $this->getModule($name);
			}
		}

		throw new \RuntimeException(get_class($this).' needs PhpBrowser or WebDriver');
	}

	/**
	 * @return DelayedDb
	 */
	protected function db()
	{
		return $this->getModule('\Helper\DelayedDb');
	}
}
