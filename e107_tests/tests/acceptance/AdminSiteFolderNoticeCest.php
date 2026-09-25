<?php

/**
 * The dashboard notice and the Multi-Site page for a site whose files sit in the folder
 * e107 v2.3.4 to v2.3.12 used when the configuration carried no site_path, and the merge that
 * brings those files back into the site's own folder.
 *
 * A probe seeds and inspects the folders through the deployer; the tests drive the admin pages
 * as the main administrator. Every assertion names the seeded hash, because the installed site
 * may keep other site folders beside its own.
 *
 * @see e107_admin/admin.php   admin_start::checkSiteFolders()
 * @see e107_admin/db.php      system_tools::siteFoldersForm()
 * @see https://github.com/e107inc/e107/issues/6498
 */
class AdminSiteFolderNoticeCest
{
	const PROBE_FILE = 'e107_tests_sitefolder_probe.php';

	const DASHBOARD = '/e107_admin/admin.php';

	const PAGE = '/e107_admin/db.php?mode=multisite';

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('act=teardown');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->probe('act=teardown');
	}

	public function theDashboardWarnsTheMainAdminAndLinksToTheMultiSitePage(AcceptanceTester $I)
	{
		$I->wantTo('See a dashboard warning that names the known-bad folder and links to the Multi-Site page');

		$seeded = $I->grabProbeJson('act=seed');

		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);
		$I->see($seeded['known_bad']);
		$I->see('Files were saved under the wrong site folder');
		$I->seeElement("a[href$='db.php?mode=multisite']");
	}

	public function theDashboardStaysQuietWhenTheKnownBadFolderHoldsNothing(AcceptanceTester $I)
	{
		$I->wantTo('See no warning for a known-bad folder that holds only what the site regenerates');

		$I->grabProbeJson('act=seedcache');

		$I->loginAsAdmin();
		$I->amOnPage(self::DASHBOARD);
		$I->dontSee('Files were saved under the wrong site folder');
	}

	public function theMultiSitePageListsTheActivePairAndTheKnownBadPairWithTheirBadges(AcceptanceTester $I)
	{
		$I->wantTo('See the site folders table with the active and the known-bad pair marked');

		$seeded = $I->grabProbeJson('act=seed');

		$I->loginAsAdmin();
		$I->amOnPage(self::PAGE);
		$I->see($seeded['active']);
		$I->see('In use by this site');
		$I->see($seeded['known_bad']);
		$I->see('Known bad');
		$I->seeElement("button[name='merge_site_folder'][value='".$seeded['known_bad']."']");
	}

	public function mergingMovesTheFilesIntoTheActiveFolderAndClearsTheNotice(AcceptanceTester $I)
	{
		$I->wantTo('Merge the known-bad folder into the site folder from the Multi-Site page');

		$seeded = $I->grabProbeJson('act=seed');

		$I->loginAsAdmin();
		$I->amOnPage(self::PAGE);
		$I->click("button[name='merge_site_folder'][value='".$seeded['known_bad']."']");
		$I->see('2 file(s) moved from '.$seeded['known_bad'].' into '.$seeded['active']);

		$state = $I->grabProbeJson('act=state');
		$I->assertFalse($state['known_bad_exists'], 'the known-bad pair is gone once everything moved');
		$I->assertSame('seeded image', $state['active_image'], 'the image sits under the site folder');
		$I->assertSame('seeded upload', $state['active_upload'], 'the pending upload sits under the site folder');

		$I->amOnPage(self::DASHBOARD);
		$I->dontSee('Files were saved under the wrong site folder');
	}

	public function aPairThatHoldsOnlyWhatTheSiteRegeneratesIsOfferedForRemovalAndRemoved(AcceptanceTester $I)
	{
		$I->wantTo('Remove a known-bad folder that holds nothing worth merging');

		$seeded = $I->grabProbeJson('act=seedcache');

		$I->loginAsAdmin();
		$I->amOnPage(self::PAGE);
		$I->seeElement("button[name='remove_site_folder'][value='".$seeded['known_bad']."']");
		$I->click("button[name='remove_site_folder'][value='".$seeded['known_bad']."']");
		$I->see('The folder '.$seeded['known_bad'].' was removed');

		$state = $I->grabProbeJson('act=state');
		$I->assertFalse($state['known_bad_exists']);
	}

	public function aCollisionIsListedAndTheSiteFolderKeepsItsOwnFile(AcceptanceTester $I)
	{
		$I->wantTo('See a file the site folder already holds listed rather than overwritten');

		$seeded = $I->grabProbeJson('act=seedcollision');

		$I->loginAsAdmin();
		$I->amOnPage(self::PAGE);
		$I->click("button[name='merge_site_folder'][value='".$seeded['known_bad']."']");
		$I->see('These files stayed in '.$seeded['known_bad']);
		$I->see('images/2026-07/collision.jpg');

		$state = $I->grabProbeJson('act=state');
		$I->assertSame('the original', $state['active_collision'], 'the site folder keeps its own bytes');
		$I->assertTrue($state['known_bad_exists'], 'the known-bad pair stays while it holds the collision');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for AdminSiteFolderNoticeCest.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$active = e107::getInstance()->getSitePath();
$knownBad = e107::getInstance()->makeSiteHash('', '');
$media = e_ROOT.e107::getFolder('media_base');
$system = e_ROOT.e107::getFolder('system_base');

$seeded = array(
	'image' => $media.$knownBad.'/images/2026-07/seeded.jpg',
	'upload' => $system.$knownBad.'/temp/seeded.zip',
	'cache' => $system.$knownBad.'/cache/content/S_seeded.cache.php',
	'placeholder' => $media.$knownBad.'/images/index.html',
	'collision' => $media.$knownBad.'/images/2026-07/collision.jpg',
	'active_image' => $media.$active.'/images/2026-07/seeded.jpg',
	'active_upload' => $system.$active.'/temp/seeded.zip',
	'active_collision' => $media.$active.'/images/2026-07/collision.jpg',
);

function sitefolder_probe_write($path, $content)
{
	if(!is_dir(dirname($path)))
	{
		mkdir(dirname($path), 0755, true);
	}

	file_put_contents($path, $content);
}

function sitefolder_probe_remove_tree($dir)
{
	if(!is_dir($dir))
	{
		return;
	}

	foreach(scandir($dir) as $entry)
	{
		if($entry === '.' || $entry === '..')
		{
			continue;
		}

		$path = $dir.'/'.$entry;
		is_dir($path) && !is_link($path) ? sitefolder_probe_remove_tree($path) : unlink($path);
	}

	rmdir($dir);
}

switch(isset($_GET['act']) ? $_GET['act'] : '')
{
	case 'seed':
		sitefolder_probe_write($seeded['image'], 'seeded image');
		sitefolder_probe_write($seeded['upload'], 'seeded upload');
		sitefolder_probe_write($seeded['cache'], '<?php return array();');
		sitefolder_probe_write($seeded['placeholder'], '');
		break;

	case 'seedcache':
		sitefolder_probe_write($seeded['cache'], '<?php return array();');
		sitefolder_probe_write($seeded['placeholder'], '');
		break;

	case 'seedcollision':
		sitefolder_probe_write($seeded['collision'], 'from the wrong folder');
		sitefolder_probe_write($seeded['active_collision'], 'the original');
		break;

	case 'teardown':
		sitefolder_probe_remove_tree($media.$knownBad);
		sitefolder_probe_remove_tree($system.$knownBad);
		foreach(array('active_image', 'active_upload', 'active_collision') as $key)
		{
			if(is_file($seeded[$key]))
			{
				unlink($seeded[$key]);
			}
		}
		break;

	case 'state':
		break;

	default:
		echo 'unknown act';
		exit;
}

echo "PROBE_OK\n";
echo json_encode(array(
	'active' => $active,
	'known_bad' => $knownBad,
	'known_bad_exists' => is_dir($media.$knownBad) || is_dir($system.$knownBad),
	'active_image' => is_file($seeded['active_image']) ? file_get_contents($seeded['active_image']) : null,
	'active_upload' => is_file($seeded['active_upload']) ? file_get_contents($seeded['active_upload']) : null,
	'active_collision' => is_file($seeded['active_collision']) ? file_get_contents($seeded['active_collision']) : null,
));
PHP;
	}
}
