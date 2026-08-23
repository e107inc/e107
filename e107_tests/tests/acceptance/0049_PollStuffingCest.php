<?php

/**
 * P6 item 6. poll_class.php:332-336 counts whatever arrives.
 *
 *   foreach ($_POST['votea'] as $vote)
 *   {
 *       $vote = intval($vote);
 *       $votes[($vote-1)] ++;
 *   }
 *
 * There is no array_unique(), so the same option repeated in the POST is
 * counted once per repetition. There is no range check, so an index outside the
 * option list creates a slot for an option that does not exist. And
 * poll_allow_multiple is never consulted here, so a single-choice poll accepts
 * an array of choices. One anonymous POST, no cookie and no token, decides the
 * result.
 *
 * The poll is seeded and read back through a probe rather than the Db module,
 * because the polls table only exists while the plugin is installed and the
 * module's own row cleanup would run after this Cest drops it again.
 */
class PollStuffingCest
{
	const PROBE_FILE = 'e107_tests_p6_poll_reset.php';

	/** The seeded poll always has exactly this many options. */
	const OPTION_COUNT = 3;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->havePluginInstalled('poll');
		$I->amOnPage('/'.self::PROBE_FILE.'?act=reset');
		$I->seeInSource('PROBE_OK');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=reset');
		$I->dropPluginInstall('poll');
		$I->dropPluginProbe();
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $multiple poll_allow_multiple
	 * @param int $phantom tallies to seed beyond the options the poll has, as a
	 *   poll stuffed before the fix carries
	 * @return int poll id
	 */
	private function havePoll(AcceptanceTester $I, $multiple, $phantom = 0)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=poll&multiple='.(int) $multiple.'&phantom='.(int) $phantom);
		$out = $I->grabPageSource();

		$matched = preg_match('/POLL_ID=(\d+)/', $out, $m);
		$I->assertSame(1, $matched, 'the fixture must be able to seed a poll');

		return (int) $m[1];
	}

	/**
	 * The vote tallies as the application stored them.
	 *
	 * poll_votes is a chr(1) separated list with a trailing separator, so the
	 * empty tail is dropped. Anything left beyond OPTION_COUNT is a tally
	 * against an option that was never on the ballot.
	 *
	 * @param AcceptanceTester $I
	 * @param int $pollId
	 * @return int[]
	 */
	private function tallies(AcceptanceTester $I, $pollId)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=votes&id='.$pollId);

		$matched = preg_match('/VOTES=(\S*)/', $I->grabPageSource(), $m);
		$I->assertSame(1, $matched, 'the fixture must be able to read the tallies back');

		$parts = explode(chr(1), base64_decode($m[1]));

		while(!empty($parts) && end($parts) === '')
		{
			array_pop($parts);
		}

		return array_map('intval', $parts);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $params
	 * @return void
	 */
	private function vote(AcceptanceTester $I, array $params)
	{
		// Cookieless: the seeded poll stores by cookie, so a cleared jar is what
		// makes the application treat this as a visitor who has not voted, and
		// it is also what exempts the request from the core CSRF check.
		$I->resetAllCookies();
		$I->sendPostRequest('/e107_plugins/poll/poll.php',
			array_merge(array('pollvote' => 'Vote'), $params));
	}

	public function repeatingOneOptionCountsAsOneVote(AcceptanceTester $I)
	{
		$I->wantTo('count a repeated option once, not once per repetition');

		$pollId = $this->havePoll($I, 0);

		$this->vote($I, array('votea' => array_fill(0, 50, 1)));

		$tallies = $this->tallies($I, $pollId);

		$I->assertLessThanOrEqual(1, array_sum($tallies),
			'50 copies of the same option in one POST recorded '.array_sum($tallies).' votes');
	}

	public function anOptionOutsideTheBallotIsNotCounted(AcceptanceTester $I)
	{
		$I->wantTo('refuse a vote for an option that is not on the ballot');

		$pollId = $this->havePoll($I, 0);

		$this->vote($I, array('votea' => array(999)));

		$tallies = $this->tallies($I, $pollId);

		$I->assertLessThanOrEqual(self::OPTION_COUNT, count($tallies),
			'a vote for option 999 left '.count($tallies).' tallies on a '.self::OPTION_COUNT.' option poll');
		$I->assertSame(0, array_sum($tallies),
			'a vote for an option that does not exist must not be counted');
	}

	public function aSingleChoicePollDoesNotAcceptSeveralChoices(AcceptanceTester $I)
	{
		$I->wantTo('honour poll_allow_multiple when counting a vote');

		$pollId = $this->havePoll($I, 0);

		$this->vote($I, array('votea' => array(1, 2)));

		$tallies = $this->tallies($I, $pollId);

		$I->assertLessThanOrEqual(1, array_sum($tallies),
			'a single choice poll recorded '.array_sum($tallies).' votes from one POST');
	}

	/**
	 * Stopping the stuffing is half the job: a poll poisoned before the fix has
	 * a tally longer than its ballot, every reader sums the whole of it, and
	 * nothing else in the plugin ever shortens it.
	 */
	public function aPoisonedTallyIsRepairedByTheNextHonestVote(AcceptanceTester $I)
	{
		$I->wantTo('shorten a tally stuffed before the fix back to the ballot the poll has');

		$pollId = $this->havePoll($I, 0, 5);

		$this->vote($I, array('votea' => 2));

		$tallies = $this->tallies($I, $pollId);

		$I->assertSame(self::OPTION_COUNT, count($tallies),
			'a poll with '.self::OPTION_COUNT.' options kept '.count($tallies).' tallies');
		$I->assertSame(array(0, 1, 0), $tallies,
			'the repaired tally must hold the honest vote and nothing else');
	}

	/**
	 * Positive control. Counting nothing would satisfy all three tests above.
	 */
	public function aSingleHonestVoteIsStillCounted(AcceptanceTester $I)
	{
		$I->wantTo('keep counting an ordinary vote');

		$pollId = $this->havePoll($I, 0);

		$this->vote($I, array('votea' => 2));

		$tallies = $this->tallies($I, $pollId);

		$I->assertSame(self::OPTION_COUNT, count($tallies), 'the ballot must keep its shape');
		$I->assertSame(array(0, 1, 0), $tallies, 'the chosen option must be the one that gained a vote');
	}

	/**
	 * Positive control for the multiple-choice half: a poll that does allow
	 * several answers must still accept them.
	 */
	public function aMultipleChoicePollStillAcceptsSeveralChoices(AcceptanceTester $I)
	{
		$I->wantTo('keep counting several choices on a multiple choice poll');

		$pollId = $this->havePoll($I, 1);

		$this->vote($I, array('votea' => array(1, 3)));

		$tallies = $this->tallies($I, $pollId);

		$I->assertSame(array(1, 0, 1), $tallies,
			'both chosen options must gain a vote and nothing else may');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$options = self::OPTION_COUNT;

		return <<<PHP
<?php
// Fixture for 0038_PollStuffingCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

\$sql = e107::getDb();
\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';

switch(\$act)
{
	case 'reset':
		\$sql->delete('polls');
		echo "PROBE_OK\n";
		break;

	case 'poll':
		// poll_menu.php serves the newest poll of type 1, so there must only be
		// one for a test to know which poll it voted in.
		\$sql->delete('polls');

		\$optionText = '';
		\$votes = '';
		for(\$i = 1; \$i <= $options; \$i++)
		{
			\$optionText .= 'P6 option '.\$i.chr(1);
			\$votes .= '0'.chr(1);
		}

		// Tallies against options that were never on the ballot, which is what
		// the unbounded increment left behind.
		for(\$i = 0, \$iMax = isset(\$_GET['phantom']) ? (int) \$_GET['phantom'] : 0; \$i < \$iMax; \$i++)
		{
			\$votes .= '9'.chr(1);
		}

		\$sql->insert('polls', array(
			'poll_datestamp'       => time(),
			'poll_start_datestamp' => 0,
			'poll_end_datestamp'   => 0,
			'poll_admin_id'        => 1,
			'poll_title'           => 'P6 poll',
			'poll_options'         => \$optionText,
			'poll_votes'           => \$votes,
			'poll_ip'              => '',
			'poll_type'            => 1,
			'poll_comment'         => 0,
			'poll_allow_multiple'  => isset(\$_GET['multiple']) ? (int) \$_GET['multiple'] : 0,
			'poll_result_type'     => 0,
			'poll_vote_userclass'  => 0,
			'poll_storage_method'  => 0,
		));

		\$id = \$sql->lastInsertId();

		echo "PROBE_OK POLL_ID=".\$id."\n";
		break;

	case 'votes':
		\$row = \$sql->select('polls', 'poll_votes', 'poll_id = '.(int) \$_GET['id'].' LIMIT 1') ? \$sql->fetch() : false;
		// base64 so the chr(1) separators survive the response intact.
		echo "PROBE_OK VOTES=".base64_encode(\$row ? \$row['poll_votes'] : '')."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
