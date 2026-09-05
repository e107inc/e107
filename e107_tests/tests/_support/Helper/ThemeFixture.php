<?php

namespace Helper;

use Codeception\Module as CodeceptionModule;

/** A theme out of tests/_data and the site switched onto it, put back in _after(); every fixture name is registered in {@see \Extension\WorkspaceCleanup}. */
class ThemeFixture extends CodeceptionModule
{
	const PROBE_FILE = 'e107_tests_theme_fixture_probe.php';

	/** What a fixture theme may be made of; anything else in the fixture directory is not deployed. */
	private static $files = array('theme.php', 'theme.xml', 'fpw_template.php', 'login_template.php', 'online_menu_template.php', 'templates/fpw_template.php', 'templates/online/online_menu_template.php', 'templates/usersettings_template.php');

	/** @var bool */
	private $probeWritten = false;

	/** @var string[] */
	private $written = array();

	/** @var string|null the site theme as the first switch of a test found it */
	private $restore = null;

	/**
	 * Put a fixture theme from tests/_data into e107_themes.
	 *
	 * @param string $name the fixture directory name
	 */
	public function haveThemeFixture($name)
	{
		foreach (self::$files as $file)
		{
			$source = codecept_data_dir().$name.'/'.$file;

			if (!is_file($source))
			{
				continue;
			}

			$relative = 'e107_themes/'.$name.'/'.$file;
			$this->app()->writeAppFile($relative, file_get_contents($source));
			$this->written[] = $relative;
		}
	}

	/**
	 * Make $name the site theme.
	 *
	 * @param string $name theme directory name
	 */
	public function haveSiteTheme($name)
	{
		$body = $this->probe('settheme', $name);

		if($this->restore === null && preg_match('/PREVIOUS=(\S+)/', $body, $match))
		{
			$this->restore = $match[1];
		}
	}

	/**
	 * Put the site back on the theme this test found, then take the fixtures out.
	 *
	 * @param \Codeception\TestInterface $test
	 */
	public function _after(\Codeception\TestInterface $test)
	{
		if($this->restore !== null)
		{
			$this->haveSiteTheme($this->restore);
			$this->restore = null;
		}

		$this->dropThemeFixtures();
	}

	/** Take the fixture themes back out and remove the probe. */
	public function dropThemeFixtures()
	{
		foreach ($this->written as $relative)
		{
			$this->app()->deleteAppFile($relative);
		}

		$this->written = array();

		if (!$this->probeWritten)
		{
			return;
		}

		$this->probe('clearcache', '');
		$this->app()->deleteAppFile(self::PROBE_FILE);
		$this->probeWritten = false;
	}

	/**
	 * @param string $act
	 * @param string $name
	 * @return string probe output
	 */
	private function probe($act, $name)
	{
		if (!$this->probeWritten)
		{
			$this->app()->writeAppFile(self::PROBE_FILE, $this->probeSource());
			$this->probeWritten = true;
		}

		$browser = $this->browser();
		$browser->amOnPage('/'.self::PROBE_FILE.'?act='.$act.'&name='.urlencode($name));

		$body = $browser->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Theme fixture probe failed for "'.$act.' '.$name.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * @return \Helper\Acceptance|\Helper\Webdriver
	 */
	private function app()
	{
		foreach (array('\Helper\Acceptance', '\Helper\Webdriver') as $name)
		{
			if ($this->hasModule($name))
			{
				return $this->getModule($name);
			}
		}

		throw new \RuntimeException('ThemeFixture needs Helper\Acceptance or Helper\Webdriver');
	}

	/**
	 * @return \Codeception\Module\PhpBrowser|\Codeception\Module\WebDriver
	 */
	private function browser()
	{
		foreach (array('PhpBrowser', 'WebDriver') as $name)
		{
			if ($this->hasModule($name))
			{
				return $this->getModule($name);
			}
		}

		throw new \RuntimeException('ThemeFixture needs PhpBrowser or WebDriver');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

e107::coreLan('admin', true);

$act = isset($_GET['act']) ? $_GET['act'] : '';
$name = preg_replace('/[^A-Za-z0-9_-]/', '', isset($_GET['name']) ? $_GET['name'] : '');

if($act === 'settheme')
{
	$previous = e107::getPref('sitetheme');

	e107::getTheme()->clearCache();

	$handler = new themeHandler();
	$handler->setTheme($name, false);

	e107::getTheme()->clearCache();

	echo "PROBE_OK THEME=".e107::getPref('sitetheme')
		." LAYOUT=".e107::getPref('sitetheme_deflayout')
		." PREVIOUS=".$previous."\n";
	return;
}

if($act === 'clearcache')
{
	e107::getTheme()->clearCache();
	echo "PROBE_OK cleared\n";
	return;
}

echo "unknown action\n";
PHP;
	}
}
