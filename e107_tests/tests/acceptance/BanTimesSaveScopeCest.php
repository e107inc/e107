<?php

/**
 * Who can save the ban messages and ban durations, which the ban list reserves to the main administrator.
 */
class BanTimesSaveScopeCest
{
	const PROBE_FILE = 'e107_tests_ban_times_probe.php';

	const LIST_PAGE = '/e107_admin/banlist.php?mode=main&action=list';

	/** Posted as every ban message; the reset puts the messages and durations the test found back. */
	const MESSAGE = 'p7rpbanmessage';

	/** Posted as every ban duration, in hours. */
	const DURATION = 8760;

	/** @var array the ban messages and durations before the test */
	private $stored;

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$this->stored = $I->grabProbeJson('act=dump');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->probe('act=restore&prefs=' . rawurlencode(json_encode($this->stored)));
	}

	public function aBanModeratorCannotRewriteTheBanMessagesAndDurations(AcceptanceTester $I)
	{
		$I->loginAsDelegatedAdmin('p7rpbantimes', '4');

		$this->postTheBanTimes($I);

		$stored = $I->grabProbeJson('act=dump');
		$I->assertNotContains(self::MESSAGE, (array) $stored['ban_messages'], 'A ban moderator replaced the ban messages.');
		$I->assertNotContains(self::DURATION, array_map('intval', (array) $stored['ban_durations']), 'A ban moderator replaced the ban durations.');
	}

	public function theMainAdministratorStillSavesTheBanMessagesAndDurations(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$this->postTheBanTimes($I);

		$stored = $I->grabProbeJson('act=dump');
		$I->assertContains(self::MESSAGE, (array) $stored['ban_messages'], 'The main administrator could not save the ban messages.');
		$I->assertContains(self::DURATION, array_map('intval', (array) $stored['ban_durations']), 'The main administrator could not save the ban durations.');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function postTheBanTimes(AcceptanceTester $I)
	{
		$post = array(
			'update_ban_prefs' => '1',
			'e-token'          => $I->grabFreshAdminToken(self::LIST_PAGE),
		);

		for($i = 1; $i <= 9; $i++)
		{
			$post['ban_text_' . $i] = self::MESSAGE;
			$post['ban_time_' . $i] = (string) self::DURATION;
		}

		$I->sendPostRequest(self::LIST_PAGE, $post);
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for BanTimesSaveScopeCest.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$core = e107::getConfig('core');
$core->load(null, true);

switch(isset($_GET['act']) ? $_GET['act'] : '')
{
	case 'dump':
		echo "PROBE_OK\n".json_encode(array(
			'ban_messages'  => $core->get('ban_messages'),
			'ban_durations' => $core->get('ban_durations'),
		))."\n";
		break;

	case 'restore':
		$prefs = json_decode((string) $_GET['prefs'], true);
		foreach(array('ban_messages', 'ban_durations') as $key)
		{
			if($prefs[$key] === null)
			{
				$core->remove($key);
			}
			else
			{
				$core->set($key, $prefs[$key]);
			}
		}
		$core->save(false, true, false);
		(new banlistManager)->writeBanMessageFile();
		echo "PROBE_OK\n";
		break;

	default:
		echo "PROBE_UNKNOWN_ACTION\n";
}
PHP;
	}
}
