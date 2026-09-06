<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * rate_voters concatenates one marker per voter: ".{uid}." for a thumb and
 * ".{uid}<chr(1)>{rating}." for a star vote. The unit suite runs as USERID 1,
 * so the fixtures below use 11 for the other voter: its marker shares a prefix
 * with USERID's and must not be mistaken for it.
 */
class rate_classTest extends \Test\Unit
{
	const RATE_TABLE = 'ratetest';
	const OTHER_USER = 11;

	/** @var rater */
	protected $rater;

	protected function _before()
	{
		e107::getDb()->truncate('rate');

		try
		{
			$this->rater = $this->make('rater');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	protected function _after()
	{
		e107::getDb()->truncate('rate');
	}

	/**
	 * Regression test: render() must not emit a "Cannot use bool as array" warning
	 * when there is no rating row for the requested table+id. getrating() legitimately
	 * returns false in that case and the list() destructure at the top of render()
	 * used to blow up on PHP 8.5.
	 */
	public function testRenderOnMissingRatingReturnsNoWarning()
	{
		$errors = array();
		set_error_handler(function ($severity, $message, $file, $line) use (&$errors)
		{
			$errors[] = compact('severity', 'message', 'file', 'line');
			return true;
		});

		try
		{
			$html = $this->rater->render('nonexistent_table_for_test', 99999);
		}
		finally
		{
			restore_error_handler();
		}

		self::assertSame(array(), $errors, 'render() must not emit warnings for missing ratings');
		self::assertIsString($html);
	}

	/**
	 * getrating() has multiple return types by design (array, bool, string). All callers
	 * that destructure with list() must tolerate a non-array return. This documents that
	 * contract so a future refactor does not silently regress callers.
	 */
	public function testGetRatingReturnsFalseWhenNoRow()
	{
		$result = $this->rater->getrating('nonexistent_table_for_test', 99999);
		self::assertFalse($result);
	}

	/**
	 * The first voter's marker starts at offset 0, which strpos() reports as 0
	 * and the old `== true` test read as "no previous vote".
	 */
	public function testFirstVoterCannotRepeatTheSameRating()
	{
		$voters = ".".USERID.chr(1)."5.";
		$this->seedRate(1, $voters, 1, 5);

		$result = $this->rater->submitVote(self::RATE_TABLE, 1, 5);
		$row = $this->fetchRate(1);

		self::assertTrue($this->rater->checkrated(self::RATE_TABLE, 1));
		self::assertStringStartsWith(RATELAN_9, $result);
		self::assertSame(RATELAN_9."|".$this->rater->renderVotes(1, 2.5), $result);
		self::assertSame(1, (int) $row['rate_votes']);
		self::assertSame(5, (int) $row['rate_rating']);
		self::assertSame($voters, $row['rate_voters']);
	}

	/**
	 * The stored marker carries the rating, so a re-vote with a different score
	 * used to look like a different voter.
	 */
	public function testRepeatVoteWithADifferentRatingIsRejected()
	{
		$voters = ".".self::OTHER_USER.chr(1)."3.".".".USERID.chr(1)."5.";
		$this->seedRate(2, $voters, 2, 8);

		$result = $this->rater->submitVote(self::RATE_TABLE, 2, 9);
		$row = $this->fetchRate(2);

		self::assertSame(RATELAN_9."|".$this->rater->renderVotes(2, 2.0), $result);
		self::assertSame(2, (int) $row['rate_votes']);
		self::assertSame(8, (int) $row['rate_rating']);
		self::assertSame($voters, $row['rate_voters']);
	}

	public function testAnotherVoterSharingAnIdPrefixDoesNotBlockTheVote()
	{
		$voters = ".".self::OTHER_USER.chr(1)."5.";
		$this->seedRate(3, $voters, 1, 5);

		$result = $this->rater->submitVote(self::RATE_TABLE, 3, 7);
		$row = $this->fetchRate(3);

		self::assertStringStartsWith(RATELAN_3, $result);
		self::assertSame(2, (int) $row['rate_votes']);
		self::assertSame(12, (int) $row['rate_rating']);
		self::assertSame($voters.".".USERID.chr(1)."7.", $row['rate_voters']);
	}

	public function testFirstVoteOnAnUnratedItemIsRecorded()
	{
		$result = $this->rater->submitVote(self::RATE_TABLE, 4, 6);
		$row = $this->fetchRate(4);

		self::assertStringStartsWith(RATELAN_3, $result);
		self::assertSame(1, (int) $row['rate_votes']);
		self::assertSame(6, (int) $row['rate_rating']);
		self::assertSame(".".USERID.chr(1)."6.", $row['rate_voters']);
	}

	/**
	 * Thumbs and stars share rate_voters, and a thumb has always closed the star
	 * vote off. That stays true.
	 */
	public function testAPriorThumbStillBlocksAStarVote()
	{
		$voters = ".".self::OTHER_USER.chr(1)."3.".".".USERID.".";
		$this->seedRate(5, $voters, 2, 3);

		$this->rater->submitVote(self::RATE_TABLE, 5, 9);
		$row = $this->fetchRate(5);

		self::assertSame(2, (int) $row['rate_votes']);
		self::assertSame(3, (int) $row['rate_rating']);
		self::assertSame($voters, $row['rate_voters']);
	}

	/**
	 * preg_match() answers false on a PCRE error, which has to count as a vote
	 * already cast rather than as a clear run.
	 */
	public function testAPcreFailureIsTreatedAsAlreadyVoted()
	{
		$hasVoted = new ReflectionMethod('rater', 'hasVoted');
		$hasVoted->setAccessible(true);
		$backtrackLimit = ini_get('pcre.backtrack_limit');
		ini_set('pcre.backtrack_limit', '0');

		try
		{
			$voted = $hasVoted->invoke($this->rater, ".".USERID.chr(1)."5.", USERID);
			$pcreError = preg_last_error();
		}
		finally
		{
			ini_set('pcre.backtrack_limit', $backtrackLimit);
		}

		self::assertNotSame(PREG_NO_ERROR, $pcreError, 'preg_match() did not fail on the fixture.');
		self::assertTrue($voted);
	}

	/**
	 * @param int $itemid
	 * @param string $voters
	 * @param int $votes
	 * @param int $rating
	 */
	private function seedRate($itemid, $voters, $votes, $rating)
	{
		$data = array(
			'rate_table'  => self::RATE_TABLE,
			'rate_itemid' => $itemid,
			'rate_rating' => $rating,
			'rate_votes'  => $votes,
			'rate_voters' => $voters,
			'rate_up'     => 0,
			'rate_down'   => 0
		);

		self::assertNotEmpty(e107::getDb()->createQueryBuilder()->insert('rate')->insertGetId($data),
			'Could not seed a rate row.');
	}

	/**
	 * @param int $itemid
	 * @return array
	 */
	private function fetchRate($itemid)
	{
		$row = e107::getDb()->createQueryBuilder()
			->select('*')->from('rate')
			->where('rate_table', self::RATE_TABLE)->where('rate_itemid', (int) $itemid)
			->fetchRow();

		self::assertNotEmpty($row, 'No rate row for item '.$itemid.'.');

		return $row;
	}
}
