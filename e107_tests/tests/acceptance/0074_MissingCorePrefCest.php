<?php

/**
 * Admin > Database > Preferences editor deletes any core preference the
 * administrator picks, and url_config used to take the front end down when
 * gone: e_url::isLegacy() ran array_keys() on null. lan_global_list was the
 * other member of this class until the global language list stopped being a
 * preference. Every other core preference was swept the same way on
 * 2026-08-17 and none fatals a front-end page.
 */
class MissingCorePrefCest
{
	const PROBE_FILE = 'e107_tests_5928_pref_probe.php';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage('/'.self::PROBE_FILE.'?act=setup');
		$I->seeInSource('PROBE_OK');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=teardown');
		$I->seeInSource('PROBE_OK');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function theFrontPageStillAnswersWithoutUrlConfig(AcceptanceTester $I)
	{
		$I->wantTo('keep the front end up when url_config has been deleted');

		$this->seePageWithoutAFatal($I, '/');
		$this->seePageWithoutAFatal($I, '/index.php');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $page
	 */
	private function seePageWithoutAFatal(AcceptanceTester $I, $page)
	{
		$I->amOnPage($page);
		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource('Fatal error');
		$I->dontSeeInSource('Uncaught');
		$I->seeElement('body');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0074_MissingCorePrefCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$config = e107::getConfig('core');
$prefs = array('url_config');

switch($act)
{
	case 'setup':
		foreach($prefs as $name)
		{
			$config->set('e107_tests_5928_backup_'.$name, $config->get($name));
			$config->remove($name);
		}
		$config->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	case 'teardown':
		foreach($prefs as $name)
		{
			$config->set($name, $config->get('e107_tests_5928_backup_'.$name));
			$config->remove('e107_tests_5928_backup_'.$name);
		}
		$config->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
