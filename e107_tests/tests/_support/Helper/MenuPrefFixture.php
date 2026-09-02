<?php

namespace Helper;

use Codeception\Module as CodeceptionModule;

/**
 * The shared `menu` preference row, borrowed for a test and put back.
 *
 * Every menu keeps its settings in that one row: the online and lastseen menus
 * read flat keys off it, the login menu owns the login_menu subtree, the banner
 * menu owns three more. A Cest about one menu's configuration screen therefore
 * writes where the other menus live, and it cannot put the row back by
 * remembering what it wrote, because the save handler under test may store keys
 * the Cest never posted. That is usually the defect being measured.
 *
 * So a Cest declares the names it is about to disturb. Those are stashed on the
 * same row and cleared, so the test starts from a state it stated rather than
 * from whatever the run before it left, and afterwards each one goes back: a
 * name that had no value is removed rather than blanked. Both halves run
 * through a probe in the docroot, so the row is read and written by the
 * application's own preference handler rather than by guessing at how a
 * preference is encoded.
 *
 * A run killed between the two halves leaves the stash behind. The next seed
 * finds it and restores from it before stashing again, so a crashed run costs
 * the run it crashed and nothing after it.
 *
 * The probe filename is registered in {@see \Extension\WorkspaceCleanup} so a
 * crashed run does not leave the file behind either.
 */
class MenuPrefFixture extends CodeceptionModule
{
	const PROBE_FILE = 'e107_tests_menu_prefs_probe.php';

	/** Where the stash lives, which is the row it is a stash of. */
	const BACKUP_KEY = 'e107_tests_menu_prefs_backup';

	/** @var bool */
	private $probeWritten = false;

	/**
	 * Stash the named preferences, clear them, then write $seed over them.
	 *
	 * @param array $owned names this test may disturb; a subtree is named by its root, e.g. 'login_menu'
	 * @param array $seed name => value pairs to write before the test runs, 'login_menu/new_news' style paths included
	 * @return void
	 */
	public function haveMenuPrefs(array $owned, array $seed = array())
	{
		$this->probe('seed', array('owned' => $owned, 'seed' => $seed));
	}

	/**
	 * Put back everything the last {@see MenuPrefFixture::haveMenuPrefs()}
	 * stashed. Safe to call when there is no stash.
	 *
	 * @return void
	 */
	public function restoreMenuPrefs()
	{
		$this->probe('restore');
	}

	/**
	 * The whole `menu` row, read in a fresh process, because a value observed
	 * in the process that wrote it proves nothing about what was stored.
	 *
	 * @return array
	 */
	public function grabMenuPrefs()
	{
		$body = $this->probe('read');

		if (!preg_match('/PROBE_OK (.+)/', $body, $matches))
		{
			throw new \RuntimeException('The menu preference probe published no row: '.trim($body));
		}

		$row = json_decode($matches[1], true);

		return is_array($row) ? $row : array();
	}

	/**
	 * Remove the probe from the docroot. Call from a Cest's _after().
	 *
	 * @return void
	 */
	public function dropMenuPrefProbe()
	{
		if (!$this->probeWritten)
		{
			return;
		}

		$this->app()->deleteAppFile(self::PROBE_FILE);
		$this->probeWritten = false;
	}

	// -----------------------------------------------------------------
	// the probe
	// -----------------------------------------------------------------

	/**
	 * @param string $act
	 * @param array $payload
	 * @return string probe output
	 */
	private function probe($act, array $payload = array())
	{
		if (!$this->probeWritten)
		{
			$this->app()->writeAppFile(self::PROBE_FILE, $this->probeSource());
			$this->probeWritten = true;
		}

		$browser = $this->browser();
		$browser->amOnPage('/'.self::PROBE_FILE.'?act='.$act.'&payload='.urlencode(json_encode($payload)));

		$body = $browser->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Menu preference probe failed for "'.$act.'": '.trim(strip_tags($body)));
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

		throw new \RuntimeException('MenuPrefFixture needs Helper\Acceptance or Helper\Webdriver');
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

		throw new \RuntimeException('MenuPrefFixture needs PhpBrowser or WebDriver');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return str_replace('%BACKUP_KEY%', self::BACKUP_KEY, <<<'PHP'
<?php
// Fixture for the menu configuration Cests. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

$config = e107::getConfig('menu');
$backupKey = '%BACKUP_KEY%';
$act = isset($_GET['act']) ? $_GET['act'] : 'read';
$payload = isset($_GET['payload']) ? json_decode($_GET['payload'], true) : array();

function e107_tests_restore_menu_prefs($config, $backupKey)
{
	$stash = $config->getPref($backupKey);

	if(!is_array($stash) || !isset($stash['owned']))
	{
		return false;
	}

	foreach($stash['owned'] as $name)
	{
		if(array_key_exists($name, $stash['values']))
		{
			$config->setPref($name, $stash['values'][$name]);
		}
		else
		{
			$config->removePref($name);
		}
	}

	$config->removePref($backupKey);

	return true;
}

if($act === 'seed')
{
	e107_tests_restore_menu_prefs($config, $backupKey);

	$owned = isset($payload['owned']) ? $payload['owned'] : array();
	$values = array();

	foreach($owned as $name)
	{
		$found = $config->getPref($name);
		if($found !== null)
		{
			$values[$name] = $found;
		}
	}

	$config->setPref($backupKey, array('owned' => $owned, 'values' => $values));

	foreach($owned as $name)
	{
		$config->removePref($name);
	}

	$seed = isset($payload['seed']) ? $payload['seed'] : array();
	foreach($seed as $name => $value)
	{
		$config->setPref($name, $value);
	}

	$config->save(false, true, false);
	echo "PROBE_OK seed\n";
	return;
}

if($act === 'restore')
{
	if(e107_tests_restore_menu_prefs($config, $backupKey))
	{
		$config->save(false, true, false);
	}

	echo "PROBE_OK restore\n";
	return;
}

echo "PROBE_OK ".json_encode($config->getPref())."\n";
PHP
		);
	}
}
