<?php

/**
 * Who can write a core preference through the options save of an admin page, read back from the stored SitePrefs row.
 */
class AdminOptionsSaveScopeCest
{
	const PROBE_FILE = 'e107_tests_options_scope_probe.php';

	/** Posted as the site name; the reset puts back the name the test found. */
	const SITENAME_ATTACK = 'p7rpoptionsite';

	/** A core preference no install has, posted beside the site name. */
	const NEW_PREF = 'p7rpoptionkey';

	/** What a fresh install leaves in admin_page_perpage. */
	const PERPAGE_SAFE = 10;

	/** A value of admin_page_perpage the custom page options may store. */
	const PERPAGE_SAVED = 13;

	/** @var string the site name before the test, which the reset puts back */
	private $sitename;

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$stored = $I->grabProbeJson('act=dump');
		$this->sitename = $stored['sitename'];
		$this->reset($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$this->reset($I);
	}

	public function aCustomPagesAdministratorCannotSetACorePreferenceThroughTheChapterOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, '5', '/e107_admin/cpage.php?mode=cat&action=list',
			'/e107_admin/cpage.php?mode=cat&action=prefs');
	}

	public function aBanAdministratorCannotSetACorePreferenceThroughTheBanListOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, '4', '/e107_admin/banlist.php?mode=main&action=list',
			'/e107_admin/banlist.php?mode=main&action=prefs');
	}

	public function aNewsCategoryEditorCannotSetACorePreferenceThroughTheCategoryOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, 'H4', '/e107_admin/newspost.php?mode=cat&action=create',
			'/e107_admin/newspost.php?mode=cat&action=prefs');
	}

	public function aSubmittedNewsAdministratorCannotSetACorePreferenceThroughTheSubmittedNewsOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, 'N', '/e107_admin/newspost.php?mode=sub&action=list',
			'/e107_admin/newspost.php?mode=sub&action=prefs');
	}

	public function aMediaAdministratorCannotSetACorePreferenceThroughTheMediaCategoryOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, 'A', '/e107_admin/image.php?mode=cat&action=list',
			'/e107_admin/image.php?mode=cat&action=prefs');
	}

	public function aLogAdministratorCannotSetACorePreferenceThroughTheAuditLogOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, 'S', '/e107_admin/admin_log.php?mode=audit&action=list',
			'/e107_admin/admin_log.php?mode=audit&action=prefs');
	}

	public function aLogAdministratorCannotSetACorePreferenceThroughTheRollingLogOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, 'S', '/e107_admin/admin_log.php?mode=rolling&action=list',
			'/e107_admin/admin_log.php?mode=rolling&action=prefs');
	}

	public function aScheduledTasksAdministratorCannotSetACorePreferenceThroughTheTaskOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, 'U', '/e107_admin/cron.php?mode=main&action=list',
			'/e107_admin/cron.php?mode=main&action=prefs');
	}

	public function anyAdministratorCannotSetACorePreferenceThroughTheDocumentationOptions(AcceptanceTester $I)
	{
		$this->dontSeeTheOptionsSaveSetACorePreference($I, 'C', '/e107_admin/cache.php',
			'/e107_admin/docs.php?mode=main&action=prefs');
	}

	public function theMainAdministratorStillSavesTheCustomPageOptionsAndNothingElse(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$I->sendPostRequest('/e107_admin/cpage.php?mode=page&action=prefs', array(
			'etrigger_save'      => 'save',
			'admin_page_perpage' => (string) self::PERPAGE_SAVED,
			'sitename'           => self::SITENAME_ATTACK,
			'e-token'            => $I->grabFreshAdminToken('/e107_admin/cpage.php?mode=page&action=list'),
		));

		$stored = $I->grabProbeJson('act=dump');

		$I->assertSame(self::PERPAGE_SAVED, (int) $stored['admin_page_perpage'], 'The custom page options did not save their own preference.');
		$I->assertNotSame(self::SITENAME_ATTACK, $stored['sitename'], 'The custom page options saved the site name.');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $perms the one permission the administrator holds
	 * @param string $tokenPage a page that administrator may load, for the form token
	 * @param string $route the options route to post to
	 * @return void
	 */
	private function dontSeeTheOptionsSaveSetACorePreference(AcceptanceTester $I, $perms, $tokenPage, $route)
	{
		$I->loginAsDelegatedAdmin('p7rpopt'.preg_replace('/\W/', '', $perms), $perms);

		$I->sendPostRequest($route, array(
			'etrigger_save' => 'save',
			'sitename'      => self::SITENAME_ATTACK,
			self::NEW_PREF  => self::SITENAME_ATTACK,
			'e-token'       => $I->grabFreshAdminToken($tokenPage),
		));

		$stored = $I->grabProbeJson('act=dump');

		$I->assertNotSame(self::SITENAME_ATTACK, $stored['sitename'], 'The options save of '.$route.' set the site name.');
		$I->assertFalse($stored['new_pref'], 'The options save of '.$route.' added a core preference.');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function reset(AcceptanceTester $I)
	{
		$I->probe('act=reset&sitename='.rawurlencode($this->sitename));
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$attack = self::SITENAME_ATTACK;
		$newPref = self::NEW_PREF;
		$perPage = self::PERPAGE_SAFE;

		return <<<PHP
<?php
// Fixture for AdminOptionsSaveScopeCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$core = e107::getConfig('core');

switch(isset(\$_GET['act']) ? \$_GET['act'] : '')
{
	case 'reset':
		\$core->load(null, true);

		if(\$core->get('sitename') === '{$attack}')
		{
			\$core->set('sitename', (string) \$_GET['sitename']);
		}

		foreach(array('{$newPref}', 'e-token', 'etrigger_save') as \$key)
		{
			if(\$core->isData(\$key))
			{
				\$core->remove(\$key);
			}
		}

		\$core->set('admin_page_perpage', {$perPage})->save(false, true, false);

		echo "PROBE_OK\n";
		break;

	case 'dump':
		// What the row holds, not what the preference cache still answers with.
		\$row = e107::getDb()->retrieve('core', 'e107_value', "e107_name = 'SitePrefs'");
		\$stored = e107::unserialize(\$row);

		echo "PROBE_OK\n".json_encode(array(
			'sitename'           => \$stored['sitename'],
			'new_pref'           => array_key_exists('{$newPref}', \$stored),
			'admin_page_perpage' => \$stored['admin_page_perpage'],
		))."\n";
		break;

	default:
		echo "PROBE_UNKNOWN_ACTION\n";
}
PHP;
	}
}
