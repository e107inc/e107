<?php

/**
 * The bulk re-key the upgrade routine performs on stored sessions.
 *
 * Keying e107_session by the visitor's session id verbatim turned every row of
 * that table into a live credential, so the rows are keyed by a digest of the
 * id instead. A returning visitor's own row is adopted lazily by
 * e_session_db::read(), which 0075_SessionAuthTokenCest covers; every other row
 * is converted in bulk by update_20x_to_latest(), which nothing exercised at
 * all. That is the path an upgraded site actually takes, and it has to hold for
 * a table holding more rows than the routine reads at a time.
 *
 * The expected storage key is spelled out here rather than asked of
 * e_session_db, so that a broken derivation makes these counts disagree instead
 * of agreeing with themselves.
 *
 * update_routines.php turns away anyone who is not a main admin at its own top,
 * so every probe request is made as one. Running it applies whatever else the
 * site has outstanding, which on a site installed from this tree is nothing.
 */
class SessionRekeyUpgradeCest
{
	const PROBE_FILE = 'e107_tests_session_rekey_probe.php';

	/** More rows than the routine converts in one pass, so the loop has to come back for more. */
	const SEEDED_ROWS = 250;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->loginAsAdmin();
		$this->probe($I, 'seed&rows='.self::SEEDED_ROWS);
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'teardown');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function everySessionKeyedByItsRawIdIsRekeyedInPlace(AcceptanceTester $I)
	{
		$I->wantTo('re-key every stored session id and keep what its row held');

		$I->assertSame(
			array('raw' => self::SEEDED_ROWS, 'hashed' => 0, 'intact' => 0, 'needed' => 1),
			$this->state($I),
			'The seeded table has to look like a site that has not been upgraded yet.'
		);

		$this->probe($I, 'upgrade');

		$I->assertSame(
			array('raw' => 0, 'hashed' => self::SEEDED_ROWS, 'intact' => self::SEEDED_ROWS, 'needed' => 0),
			$this->state($I),
			'Every seeded row has to move to the digest of its own id, contents untouched.'
		);
	}

	public function asecondUpgradeRunLeavesTheRekeyedSessionsAlone(AcceptanceTester $I)
	{
		$I->wantTo('leave the re-keyed sessions alone when the upgrade runs again');

		$this->probe($I, 'upgrade');
		$this->probe($I, 'upgrade');

		$I->assertSame(
			array('raw' => 0, 'hashed' => self::SEEDED_ROWS, 'intact' => self::SEEDED_ROWS, 'needed' => 0),
			$this->state($I),
			'A second run has nothing to convert and must not disturb what the first one wrote.'
		);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return array fixture rows still raw, re-keyed, and re-keyed with their contents intact, plus whether the upgrade is still pending
	 */
	private function state(AcceptanceTester $I)
	{
		$body = $this->probe($I, 'state&rows='.self::SEEDED_ROWS);

		if(!preg_match('/PROBE_OK (\{.*\})/', $body, $matches))
		{
			throw new RuntimeException('The session probe published no counts: '.trim(strip_tags($body)));
		}

		return json_decode($matches[1], true);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query probe action and its parameters
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act='.$query);

		$body = $I->grabPageSource();

		if(strpos($body, 'PROBE_OK') === false)
		{
			throw new RuntimeException('The session probe failed for "'.$query.'": '.trim(strip_tags($body)));
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
// Fixture for 0077_SessionRekeyUpgradeCest. Removed again in the Cest's _after().
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$rows = isset($_GET['rows']) ? (int) $_GET['rows'] : 0;
$sql = e107::getDb();

$fixtureUser = 987654;
$fixturePrefix = 'e107testsrekey';
$fixture = array();

for($i = 0; $i < $rows; $i++)
{
	$fixture[$fixturePrefix.$i] = array(
		'session_expires' => 2000000000 + $i,
		'session_user'    => $fixtureUser,
		'session_data'    => base64_encode('e107 tests session row '.$i),
	);
}

if($act === 'seed' || $act === 'teardown')
{
	$sql->delete('session', "session_user=".$fixtureUser);
	$sql->delete('session', "session_id LIKE '".$fixturePrefix."%'");

	if($act === 'seed')
	{
		foreach($fixture as $fixtureId => $fixtureRow)
		{
			$fixtureRow['session_id'] = $fixtureId;
			$sql->insert('session', $fixtureRow);
		}
	}

	echo "PROBE_OK ".json_encode(array('act' => $act))."\n";
	exit;
}

require_once(e_ADMIN.'update_routines.php');

if($act === 'upgrade')
{
	update_20x_to_latest('do');
	echo "PROBE_OK ".json_encode(array('act' => $act))."\n";
	exit;
}

if($act === 'state')
{
	$raw = 0;
	$hashed = 0;
	$intact = 0;

	foreach($fixture as $fixtureId => $expected)
	{
		if($sql->select('session', 'session_id', "session_id='".$fixtureId."'"))
		{
			$raw++;
		}

		$storageKey = 'sha256$'.hash('sha256', $fixtureId);

		if($sql->select('session', 'session_user, session_data, session_expires', "session_id='".$storageKey."'"))
		{
			$hashed++;
			$stored = $sql->fetch();

			if((int) $stored['session_user'] === $expected['session_user']
				&& $stored['session_data'] === $expected['session_data']
				&& (int) $stored['session_expires'] === $expected['session_expires'])
			{
				$intact++;
			}
		}
	}

	echo "PROBE_OK ".json_encode(array(
		'raw'    => $raw,
		'hashed' => $hashed,
		'intact' => $intact,
		'needed' => update_20x_to_latest('check') ? 0 : 1,
	))."\n";
	exit;
}

echo "unknown action\n";
PHP;
	}
}
