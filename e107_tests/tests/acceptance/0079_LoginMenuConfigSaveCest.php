<?php

/**
 * What saving the login menu's configuration screen does to the shared `menu`
 * preference row.
 *
 * Every menu keeps its settings in that one row: the online and lastseen menus
 * read flat keys off it, and the login menu owns the login_menu subtree. The
 * save handler used to call {@see e_pref::reset()}, which empties the object,
 * and save() then wrote the emptied object wholesale, so pressing Save wrote a
 * row holding login_menu and nothing else. It also copied $_POST['pref'] wholesale, storing any key the
 * request carried under login_menu/.
 *
 * Both faults are asserted through the application's own preference handler,
 * read back by a probe in a fresh process, because a value observed in the
 * process that wrote it proves nothing about what was stored.
 *
 * @see e107_plugins/login_menu/config.php the save handler under test
 */
class LoginMenuConfigSaveCest
{
	/**
	 * Registered in Extension\WorkspaceCleanup so a crashed run does not leave
	 * it in the docroot.
	 */
	const PROBE_FILE = 'e107_tests_login_menu_prefs_probe.php';

	const CONFIG_PATH = '/e107_plugins/login_menu/config.php';

	/** The statistics rows render only for an installed core plugin, and this is the one a stock docroot can gain cheaply. */
	const STATS_PLUGIN = 'chatbox_menu';

	/**
	 * Flat keys the lastseen menu reads off the shared row, as the probe's
	 * seed action stores them.
	 */
	const SIBLING_PREFS = array(
		'online_ls_amount'  => '15',
		'online_ls_caption' => 'e107 tests last seen',
	);

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->resetAllCookies();
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'teardown');
		$I->deleteAppFile(self::PROBE_FILE);
		$I->dropPluginInstall(self::STATS_PLUGIN);
		$I->dropPluginProbe();
	}

	public function savingTheLoginMenuKeepsEveryOtherMenusPreferences(AcceptanceTester $I)
	{
		$I->wantTo('save the login menu configuration without losing another menu\'s settings');

		$this->probe($I, 'seed');

		$this->saveConfig($I, array('pref' => array('new_news' => '1')));

		$menu = $this->grabMenuRow($I);
		$login = isset($menu['login_menu']) ? $menu['login_menu'] : array();

		$I->assertSame('1', isset($login['new_news']) ? $login['new_news'] : null,
			'The save must have landed for the next assertion to mean anything.');

		foreach(self::SIBLING_PREFS as $key => $value)
		{
			$I->assertSame($value, isset($menu[$key]) ? $menu[$key] : null,
				'Saving the login menu configuration must not discard another menu\'s "'.$key.'" preference.');
		}
	}

	public function savingTheLoginMenuStoresOnlyItsOwnKeys(AcceptanceTester $I)
	{
		$I->wantTo('see the save store only the keys the screen owns');

		$this->probe($I, 'seed');

		$this->saveConfig($I, array('pref' => array(
			'new_news' => '1',
			'smuggled' => 'stored',
		)));

		$menu = $this->grabMenuRow($I);
		$login = isset($menu['login_menu']) ? $menu['login_menu'] : array();

		$I->assertSame('1', isset($login['new_news']) ? $login['new_news'] : null,
			'The save must have landed for the next assertion to mean anything.');

		$keys = array_keys($login);
		sort($keys);

		$I->assertSame(
			array('external_links', 'external_stats', 'new_comments', 'new_members', 'new_news'),
			$keys,
			'The login_menu subtree must hold exactly the five keys the screen owns.');
	}

	public function savingAStatisticsCheckboxTicksItOnTheFormThatComesBack(AcceptanceTester $I)
	{
		$I->wantTo('see the box I just ticked come back ticked');

		$this->probe($I, 'seed');
		$I->havePluginInstalled(self::STATS_PLUGIN);

		$this->saveConfig($I, array(
			'pref'           => array('new_news' => '1'),
			'external_stats' => array(self::STATS_PLUGIN => '1'),
		));

		$form = $I->grabPageSource();

		$menu = $this->grabMenuRow($I);
		$login = isset($menu['login_menu']) ? $menu['login_menu'] : array();

		$I->assertSame(self::STATS_PLUGIN, isset($login['external_stats']) ? $login['external_stats'] : null,
			'The save must have landed for the next assertion to mean anything.');

		$I->assertStringContainsString(
			'name="external_stats['.self::STATS_PLUGIN.']" value="1" checked="checked"',
			$form,
			'The form returned by the save must show the statistics box ticked, or the next save writes the pre-save state back.');
	}

	// -----------------------------------------------------------------
	// helpers
	// -----------------------------------------------------------------

	/**
	 * Submit the configuration form the way the browser does, with the fields
	 * under test layered on top.
	 *
	 * @param AcceptanceTester $I
	 * @param array $fields
	 * @return void
	 */
	private function saveConfig(AcceptanceTester $I, array $fields)
	{
		$fields['update_menu'] = 'Save';
		$fields['e-token'] = $I->grabFreshAdminToken(self::CONFIG_PATH);

		$I->sendPostRequest(self::CONFIG_PATH, $fields);
	}

	/**
	 * The shared `menu` preference row, read back in a fresh process.
	 *
	 * @param AcceptanceTester $I
	 * @return array
	 */
	private function grabMenuRow(AcceptanceTester $I)
	{
		$body = $this->probe($I, 'menu');

		if(!preg_match('/PROBE_OK (.+)/', $body, $matches))
		{
			throw new RuntimeException('The probe published no menu preferences: '.trim($body));
		}

		$row = json_decode($matches[1], true);

		return is_array($row) ? $row : array();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act='.$query);

		$body = $I->grabPageSource();

		if(strpos($body, 'PROBE_OK') === false)
		{
			throw new RuntimeException('Preference probe failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0079_LoginMenuConfigSaveCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

// The `menu` row is shared with every other menu, so seeding it has to be
// undoable: these are the branches the seed and the save under test both write.
$owned = array('online_ls_amount', 'online_ls_caption', 'login_menu');

$seed = array(
	'online_ls_amount'     => '15',
	'online_ls_caption'    => 'e107 tests last seen',
	'login_menu/new_forum' => '1',
);

$backupKey = 'e107_tests_6069_backup';

$config = e107::getConfig('menu');
$act = isset($_GET['act']) ? $_GET['act'] : 'menu';

if($act === 'seed')
{
	$backup = array();
	foreach($owned as $name)
	{
		$found = $config->getPref($name);
		if($found !== null)
		{
			$backup[$name] = $found;
		}
	}
	foreach($seed as $name => $value)
	{
		$config->setPref($name, $value);
	}
	$config->setPref($backupKey, $backup)->save(false, true, false);
	echo "PROBE_OK seed\n";
	return;
}

if($act === 'teardown')
{
	$backup = $config->getPref($backupKey);
	if($backup === null)
	{
		// The seed never ran, so the row holds nothing this test put there.
		echo "PROBE_OK teardown\n";
		return;
	}

	foreach($owned as $name)
	{
		if(array_key_exists($name, $backup))
		{
			$config->setPref($name, $backup[$name]);
		}
		else
		{
			$config->removePref($name);
		}
	}
	$config->removePref($backupKey)->save(false, true, false);
	echo "PROBE_OK teardown\n";
	return;
}

echo "PROBE_OK ".json_encode($config->getPref())."\n";
PHP;
	}
}
